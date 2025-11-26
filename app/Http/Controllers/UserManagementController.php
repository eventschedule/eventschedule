<?php

namespace App\Http\Controllers;

use App\Models\SystemRole;
use App\Models\Role as DomainRole;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private AuthorizationService $authorization)
    {
    }

    protected function ensureUserPermission(?User $user, string $permission): void
    {
        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, __('messages.access_denied'));
        }
    }

    public function index(Request $request): View
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        $users = $this->queryUsers($request);

        return view('settings.users.index', [
            'users' => $users,
            'search' => (string) $request->string('search'),
            'canManageRoles' => $request->user()->hasPermission('users.manage'),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        return view('settings.users.create-modern', $this->formOptions($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        $canManageRoles = $request->user()->hasPermission('users.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'timezone' => ['required', 'timezone'],
            'language_code' => ['required', 'string', Rule::in(array_keys($this->languageOptions()))],
            'roles' => $canManageRoles ? ['array'] : ['prohibited'],
            'roles.*' => $canManageRoles ? ['integer', Rule::exists('auth_roles', 'id')] : ['prohibited'],
            'venue_scope' => ['required', Rule::in(['all', 'selected'])],
            'curator_scope' => ['required', Rule::in(['all', 'selected'])],
            'talent_scope' => ['required', Rule::in(['all', 'selected'])],
            'venue_ids' => ['array'],
            'venue_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'venue')],
            'curator_ids' => ['array'],
            'curator_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'curator')],
            'talent_ids' => ['array'],
            'talent_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'talent')],
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->timezone = $validated['timezone'];
        $user->language_code = $validated['language_code'];
        $user->email_verified_at = now();
        $user->save();

        if ($canManageRoles) {
            $this->syncRoles($user, $validated['roles'] ?? []);
        }

        $this->syncScopes($user, $validated);

        return redirect()->route('settings.users.index')
            ->with('status', __('messages.user_created'));
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        return view('settings.users.create-modern', array_merge(
            ['managedUser' => $user->load('systemRoles')],
            $this->formOptions($request)
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        $canManageRoles = $request->user()->hasPermission('users.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->getKey())],
            'password' => [
                'nullable',
                Rule::when(
                    fn ($input) => filled($input->password),
                    ['string', 'min:8', 'confirmed']
                ),
            ],
            'timezone' => ['required', 'timezone'],
            'language_code' => ['required', 'string', Rule::in(array_keys($this->languageOptions()))],
            'roles' => $canManageRoles ? ['array'] : ['prohibited'],
            'roles.*' => $canManageRoles ? ['integer', Rule::exists('auth_roles', 'id')] : ['prohibited'],
            'venue_scope' => ['required', Rule::in(['all', 'selected'])],
            'curator_scope' => ['required', Rule::in(['all', 'selected'])],
            'talent_scope' => ['required', Rule::in(['all', 'selected'])],
            'venue_ids' => ['array'],
            'venue_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'venue')],
            'curator_ids' => ['array'],
            'curator_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'curator')],
            'talent_ids' => ['array'],
            'talent_ids.*' => ['integer', Rule::exists('roles', 'id')->where('type', 'talent')],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->timezone = $validated['timezone'];
        $user->language_code = $validated['language_code'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($canManageRoles) {
            $this->syncRoles($user, $validated['roles'] ?? []);
        }

        $this->syncScopes($user, $validated);

        return redirect()->route('settings.users.index')
            ->with('status', __('messages.user_updated'));
    }

    protected function queryUsers(Request $request): LengthAwarePaginator
    {
        $search = trim((string) $request->string('search'));

        return User::query()
            ->with('systemRoles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    protected function availableRoles()
    {
        return SystemRole::query()
            ->whereIn('slug', ['superadmin', 'admin', 'viewer'])
            ->orderBy('name')
            ->get();
    }

    protected function languageOptions(): array
    {
        return collect(config('app.supported_languages', ['en']))
            ->mapWithKeys(function ($language) {
                $label = function_exists('locale_get_display_language')
                    ? locale_get_display_language($language, app()->getLocale())
                    : null;

                return [$language => $label ?: strtoupper($language)];
            })
            ->toArray();
    }

    protected function resourceOptions(string $type)
    {
        return DomainRole::query()
            ->where('type', $type)
            ->orderBy('name')
            ->get(['id', 'name', 'subdomain']);
    }

    protected function formOptions(Request $request): array
    {
        $activeLabel = __('messages.active');
        $inactiveLabel = __('messages.inactive');

        return [
            'timezones' => \Carbon\CarbonTimeZone::listIdentifiers(),
            'languageOptions' => $this->languageOptions(),
            'availableRoles' => $this->availableRoles(),
            'canManageRoles' => $request->user()->hasPermission('users.manage'),
            'statusOptions' => [
                'active' => $activeLabel !== 'messages.active' ? $activeLabel : 'Active',
                'inactive' => $inactiveLabel !== 'messages.inactive' ? $inactiveLabel : 'Inactive',
            ],
            'resourceOptions' => [
                'venues' => $this->resourceOptions('venue'),
                'curators' => $this->resourceOptions('curator'),
                'talent' => $this->resourceOptions('talent'),
            ],
        ];
    }

    protected function syncRoles(User $user, array $roleIds): void
    {
        $ids = SystemRole::query()
            ->whereIn('id', $roleIds)
            ->pluck('id')
            ->all();

        $user->systemRoles()->sync($ids);
        $this->authorization->forgetUserPermissions($user);
    }

    protected function syncScopes(User $user, array $validated): void
    {
        $user->venue_scope = $validated['venue_scope'] ?? 'all';
        $user->curator_scope = $validated['curator_scope'] ?? 'all';
        $user->talent_scope = $validated['talent_scope'] ?? 'all';

        $user->venue_ids = $user->venue_scope === 'selected' ? ($validated['venue_ids'] ?? []) : [];
        $user->curator_ids = $user->curator_scope === 'selected' ? ($validated['curator_ids'] ?? []) : [];
        $user->talent_ids = $user->talent_scope === 'selected' ? ($validated['talent_ids'] ?? []) : [];

        $user->save();
    }
}
