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
use App\Services\SpamWatch;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * /admin/blocked: shutting an account out, and the list of what new accounts are refused for.
 *
 * Two pages. The list page shows the accounts that may be spam (SpamWatch), finds an account,
 * shows the ones that are blocked and holds the list (Blocklist). The account page is where a block is decided: it shows what the operator needs to
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

        $suspects = SpamWatch::suspects()
            ->withCount(['createdRoles as schedules_count' => fn ($role) => $role->owned()->where('is_deleted', false)])
            ->orderByDesc('users.id')
            ->paginate(20, ['users.*'], 'suspects')
            ->withQueryString();

        // Not spam on the last row of the last page comes back to a page that no longer exists.
        if ($suspects->isEmpty() && $suspects->currentPage() > 1) {
            return redirect()->to($suspects->url($suspects->lastPage()).'#suspects');
        }

        return view('admin.blocked', [
            'suspects' => $suspects,
            'spamMatches' => SpamWatch::matches($suspects->getCollection()),
            'spamWords' => SpamWatch::words(),
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
            // Only for an account the list could hold, so the card and its Not spam are never
            // offered for an administrator, the demo or a blocked account.
            'spam' => SpamWatch::canBeFlagged($user) ? (SpamWatch::matches(collect([$user]), 10)[$user->id] ?? null) : null,
            'spamClearance' => SpamWatch::clearance($user),
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

        // Letting an account back in says it is not spam, so a watched word in its name does
        // not put it straight back on the list the block took it off.
        if ((SpamWatch::matches(collect([$user]), 1)[$user->id]['total'] ?? 0) > 0) {
            SpamWatch::clear($user, $request->user());
        }

        $back = redirect()->route('admin.blocked.account', ['hash' => $hash])
            ->with('success', __('messages.unblock_done', ['name' => $user->name ?: $user->email])
                .($result['restored']->isNotEmpty() ? ' '.__('messages.unblock_done_schedules', ['count' => number_format($result['restored']->count())]) : ''));

        return $result['renamed']->isEmpty()
            ? $back
            : $back->with('warning', __('messages.unblock_names_taken', ['schedules' => $result['renamed']->pluck('name')->implode(', ')]));
    }

    /** Not spam: a watched word is in its name or in something it made, and that is all right. */
    public function notSpam(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        SpamWatch::clear($user, $request->user());

        $done = __('messages.spam_watch_not_spam_done', ['name' => $user->name ?: $user->email]);

        // From the list, back to the list where it was (the layout toasts `message`); from the
        // account's page, back to it.
        return $request->input('from') === 'list'
            ? redirect()->to($this->suspectsUrl())->with('message', $done)
            : redirect()->route('admin.blocked.account', ['hash' => $hash])->with('success', $done);
    }

    public function flagAgain(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = User::findOrFail(UrlUtils::decodeId($hash));
        SpamWatch::unclear($user, $request->user());

        return redirect()->route('admin.blocked.account', ['hash' => $hash])
            ->with('success', __('messages.spam_watch_flag_again_done', ['name' => $user->name ?: $user->email]));
    }

    public function saveWords(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Room for MAX_WORDS words of MAX_LENGTH characters with their commas; acceptable() is the
        // limit that is meant.
        $request->validate(['words' => ['nullable', 'string', 'max:3000']]);

        $words = SpamWatch::parse((string) $request->input('words'));

        if (! SpamWatch::acceptable($words)) {
            return redirect()->to(route('admin.blocked').'#suspects')
                ->withInput()
                ->withErrors(['words' => __('messages.spam_watch_words_invalid', [
                    'min' => SpamWatch::MIN_LENGTH,
                    'max' => SpamWatch::MAX_LENGTH,
                    'count' => SpamWatch::MAX_WORDS,
                ])]);
        }

        SpamWatch::saveWords($words, $request->user());

        return redirect()->to(route('admin.blocked').'#suspects')
            ->with('message', $words === [] ? __('messages.spam_watch_words_off') : __('messages.spam_watch_words_saved'));
    }

    /** The list page as it was (its search, its page of each list), at the accounts that may be spam. */
    private function suspectsUrl(): string
    {
        $list = route('admin.blocked');
        $previous = explode('#', url()->previous())[0];

        return ($previous === $list || str_starts_with($previous, $list.'?') ? $previous : $list).'#suspects';
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
