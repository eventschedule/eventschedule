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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
        $resourceLookups = $this->resourceLookups($users);

        return view('settings.users.index', [
            'users' => $users,
            'search' => (string) $request->string('search'),
            'canManageRoles' => $request->user()->hasPermission('users.manage'),
            'resourceLookups' => $resourceLookups,
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
            'password_mode' => ['required', Rule::in(['set', 'invite', 'defer'])],
            'password' => [
                Rule::requiredIf(fn ($input) => $input->password_mode === 'set'),
                'nullable',
                'string',
                'min:8',
                'confirmed',
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

        $passwordMode = $validated['password_mode'];

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make(
            $passwordMode === 'set' ? $validated['password'] : Str::random(40)
        );
        $user->timezone = $validated['timezone'];
        $user->language_code = $validated['language_code'];
        $user->email_verified_at = now();
        $user->save();

        if ($canManageRoles) {
            $this->syncRoles($user, $validated['roles'] ?? []);
        }

        $this->syncScopes($user, $validated);

        $statusKey = 'messages.user_created';

        if ($passwordMode === 'invite') {
            $inviteStatus = Password::sendResetLink(['email' => $user->email]);
            $statusKey = $inviteStatus === Password::RESET_LINK_SENT
                ? 'messages.user_invite_sent'
                : 'messages.user_invite_failed';
        } elseif ($passwordMode === 'defer') {
            $statusKey = 'messages.user_created_password_deferred';
        }

        return redirect()->route('settings.users.index')
            ->with('status', __($statusKey));
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
            'password_mode' => ['required', Rule::in(['set', 'invite', 'defer'])],
            'password' => [
                Rule::requiredIf(fn ($input) => $input->password_mode === 'set'),
                'nullable',
                'string',
                'min:8',
                'confirmed'
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

        $passwordMode = $validated['password_mode'];

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

        $statusKey = 'messages.user_updated';

        if ($passwordMode === 'invite') {
            $inviteStatus = Password::sendResetLink(['email' => $user->email]);
            $statusKey = $inviteStatus === Password::RESET_LINK_SENT
                ? 'messages.user_invite_sent'
                : 'messages.user_invite_failed';
        } elseif ($passwordMode === 'set' && ! empty($validated['password'])) {
            $statusKey = 'messages.user_password_updated';
        }

        return redirect()->route('settings.users.index')
            ->with('status', __($statusKey));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserPermission($request->user(), 'users.manage');

        if ($request->user()->is($user)) {
            return redirect()->route('settings.users.edit', $user)
                ->with('status', __('messages.cannot_delete_self'));
        }

        $user->delete();
        $this->authorization->forgetUserPermissions($user);

        return redirect()->route('settings.users.index')
            ->with('status', __('messages.user_deleted'));
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

    protected function resourceLookups($users): array
    {
        $idsByType = [
            'venue' => [],
            'curator' => [],
            'talent' => [],
        ];

        foreach ($users as $user) {
            $idsByType['venue'] = array_merge($idsByType['venue'], $user->getResourceScopeIds('venue'));
            $idsByType['curator'] = array_merge($idsByType['curator'], $user->getResourceScopeIds('curator'));
            $idsByType['talent'] = array_merge($idsByType['talent'], $user->getResourceScopeIds('talent'));
        }

        return collect($idsByType)->map(function (array $ids, string $type) {
            if (empty($ids)) {
                return collect();
            }

            return DomainRole::query()
                ->where('type', $type)
                ->whereIn('id', array_unique($ids))
                ->orderBy('name')
                ->get(['id', 'name', 'subdomain'])
                ->keyBy('id');
        })->all();
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

        $user->setResourceScopeIds('venue', $user->venue_scope === 'selected' ? ($validated['venue_ids'] ?? []) : []);
        $user->setResourceScopeIds('curator', $user->curator_scope === 'selected' ? ($validated['curator_ids'] ?? []) : []);
        $user->setResourceScopeIds('talent', $user->talent_scope === 'selected' ? ($validated['talent_ids'] ?? []) : []);

        $user->save();
    }
}
