<?php

namespace App\Http\Controllers;

use App\Models\AccountBlock;
use App\Models\BlocklistEntry;
use App\Models\Role;
use App\Models\User;
use App\Services\AccountBlockService;
use App\Services\AuditService;
use App\Services\Blocklist;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * /admin/blocked: shutting an account out, and the list of what new accounts are refused for.
 *
 * Two pages. The list page finds an account, shows the ones that are blocked and holds the list
 * (Blocklist). The account page is where a block is decided: it shows what the operator needs to
 * see first, which is what the account owns, where it signed up from and who else shares that
 * address or its email domain. The last two are the price of the two optional entries, so the
 * numbers stand beside the switches that add them.
 *
 * What a block does is AccountBlockService's to say; nothing here writes blocked_at.
 */
class AdminBlockController extends Controller
{
    public function __construct(private AccountBlockService $blocks) {}

    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $search = trim((string) (is_array($request->input('search')) ? '' : $request->input('search')));
        $found = collect();

        if ($search !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';

            $found = User::query()
                ->where('email', '!=', DemoService::DEMO_EMAIL)
                ->where(fn ($q) => $q
                    ->where('email', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('signup_ip', $search)
                    ->orWhereHas('createdRoles', fn ($role) => $role
                        ->where('subdomain', 'like', $like)
                        ->orWhere('subdomain_before_delete', 'like', $like)
                        ->orWhere('name', 'like', $like)))
                ->withCount(['createdRoles as schedules_count' => fn ($role) => $role->owned()->where('is_deleted', false)])
                ->orderByDesc('id')
                ->limit(25)
                ->get();
        }

        $type = in_array($request->input('type'), Blocklist::TYPES, true) ? $request->input('type') : null;

        return view('admin.blocked', [
            'search' => $search,
            'found' => $found,
            'blocked' => AccountBlock::with(['user', 'blocker'])
                ->whereHas('user')
                ->latest('id')
                ->paginate(20, ['*'], 'accounts')
                ->withQueryString(),
            'entries' => BlocklistEntry::with('accountBlock.user')
                ->when($type, fn ($q) => $q->where('type', $type))
                ->latest('id')
                ->paginate(25, ['*'], 'entries')
                ->withQueryString(),
            'type' => $type,
            'blockedCount' => User::whereNotNull('blocked_at')->count(),
            'entryCount' => BlocklistEntry::count(),
            'refusedCount' => (int) BlocklistEntry::sum('refused_count'),
        ]);
    }

    public function account(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        $block = AccountBlock::with(['blocker', 'entries'])->where('user_id', $user->id)->first();

        // Everything this account owns, taken down or not: the page says which is which.
        $schedules = Role::where('user_id', $user->id)
            ->owned()
            ->withCount('events')
            ->orderBy('is_deleted')
            ->orderBy('name')
            ->get();

        $domain = str_contains((string) $user->email, '@') ? substr(strrchr($user->email, '@'), 1) : null;
        $range = Blocklist::visitorRange($user->signup_ip);

        // Who else was made from the same address: very often the same person again. Named, and
        // linked, because the next thing an operator does is look at them.
        $sameAddress = $user->signup_ip
            ? User::where('signup_ip', $user->signup_ip)->where('id', '!=', $user->id)->orderByDesc('id')->limit(10)->get()
            : collect();

        return view('admin.blocked-account', [
            'user' => $user,
            'block' => $block,
            'refusal' => $this->blocks->refusal($user, $request->user()),
            'schedules' => $schedules,
            'takenDown' => $block ? ($block->role_ids ?? []) : [],
            'domain' => $domain,
            'sameDomainCount' => $domain ? max(0, (int) Blocklist::existingAccounts(Blocklist::DOMAIN, strtolower($domain)) - 1) : 0,
            'range' => $range,
            'sameAddress' => $sameAddress,
            'sameAddressCount' => $user->signup_ip ? User::where('signup_ip', $user->signup_ip)->where('id', '!=', $user->id)->count() : 0,
        ]);
    }

    public function block(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = User::findOrFail(UrlUtils::decodeId($hash));

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $this->blocks->block(
                $user,
                $request->user(),
                $validated['note'] ?? null,
                $request->boolean('block_address'),
                $request->boolean('block_domain'),
            );
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', __($e->getMessage()));
        }

        $back = redirect()->route('admin.blocked.account', ['hash' => $hash])
            ->with('success', __('messages.block_done', ['name' => $user->name ?: $user->email])
                .($result['taken']->isNotEmpty() ? ' '.__('messages.block_done_schedules', ['count' => number_format($result['taken']->count())]) : ''));

        // A schedule Stripe would not let go of is still up. Said by name: it is the one thing
        // left for the operator to do by hand.
        return $result['failed']->isEmpty()
            ? $back
            : $back->with('error', __('messages.block_schedules_left_up', ['schedules' => $result['failed']->pluck('name')->implode(', ')]));
    }

    public function unblock(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = User::findOrFail(UrlUtils::decodeId($hash));

        if (! $user->isBlocked()) {
            return redirect()->route('admin.blocked.account', ['hash' => $hash]);
        }

        $result = $this->blocks->unblock($user, $request->user());

        $back = redirect()->route('admin.blocked.account', ['hash' => $hash])
            ->with('success', __('messages.unblock_done', ['name' => $user->name ?: $user->email])
                .($result['restored']->isNotEmpty() ? ' '.__('messages.unblock_done_schedules', ['count' => number_format($result['restored']->count())]) : ''));

        return $result['renamed']->isEmpty()
            ? $back
            : $back->with('warning', __('messages.unblock_names_taken', ['schedules' => $result['renamed']->pluck('name')->implode(', ')]));
    }

    public function storeEntry(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', Blocklist::TYPES)],
            'value' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $entry = Blocklist::add($validated['type'], $validated['value'], $validated['note'] ?? null, $request->user()->id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->to(route('admin.blocked').'#list')
                ->withInput()
                ->withErrors(['value' => __($e->getMessage())]);
        }

        // `message` and `warning`, which the layout toasts: the redirect lands on the list,
        // far below where the page would print a notice.
        if (! $entry->wasRecentlyCreated) {
            return redirect()->to(route('admin.blocked').'#list')
                ->with('warning', __('messages.blocklist_already_listed', ['value' => $entry->value]));
        }

        AuditService::log(AuditService::ADMIN_BLOCKLIST_ADD, $request->user()->id, 'App\\Models\\BlocklistEntry', $entry->id, null, ['type' => $entry->type, 'value' => $entry->value]);

        // How many accounts already match. They are not touched, and a large number is how a
        // mail provider's domain is told from a spammer's.
        $existing = Blocklist::existingAccounts($entry->type, $entry->type === Blocklist::EMAIL ? $entry->value : $entry->match_key);

        return redirect()->to(route('admin.blocked').'#list')->with(
            'message',
            __('messages.blocklist_added', ['value' => $entry->value])
                .($existing ? ' '.__('messages.blocklist_existing_accounts', ['count' => number_format($existing)]) : '')
        );
    }

    public function removeEntry(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $entry = BlocklistEntry::findOrFail(UrlUtils::decodeId($hash));

        DB::transaction(function () use ($entry, $request) {
            AuditService::log(AuditService::ADMIN_BLOCKLIST_REMOVE, $request->user()->id, 'App\\Models\\BlocklistEntry', $entry->id, ['type' => $entry->type, 'value' => $entry->value]);
            $entry->delete();
        });

        return redirect()->to(route('admin.blocked').'#list')
            ->with('message', __('messages.blocklist_removed', ['value' => $entry->value]));
    }
}
