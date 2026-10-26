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
    /** How long a country is refused when the form does not say: a spammer's week, not for good. */
    public const COUNTRY_DURATION = '7d';

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
        // Only a country an entry can name: the switch would otherwise add nothing and say nothing.
        $country = Blocklist::countryOf($user->signup_ip);
        $country = Blocklist::isCountry($country) ? $country : null;
        $refusal = $this->blocks->refusal($user, $request->user());

        // Who else was made from the same address: very often the same person again. Named, and
        // linked, because the next thing an operator does is look at them.
        $sameAddress = $user->signup_ip
            ? User::where('signup_ip', $user->signup_ip)->where('id', '!=', $user->id)->orderByDesc('id')->limit(10)->get()
            : collect();

        return view('admin.blocked-account', [
            'user' => $user,
            'block' => $block,
            'refusal' => $refusal,
            'schedules' => $schedules,
            'takenDown' => $block ? ($block->role_ids ?? []) : [],
            'domain' => $domain,
            'sameDomainCount' => $domain ? max(0, (int) Blocklist::existingAccounts(Blocklist::DOMAIN, strtolower($domain)) - 1) : 0,
            'range' => $range,
            'country' => $country,
            // This account is one of them when it is recent enough to be counted.
            // Only where the switch is offered: it is the switch's price, and a walk over a month.
            'sameCountryCount' => $country && ! $user->isBlocked() && ! $refusal
                ? max(0, Blocklist::recentSignupsFrom($country) - ($user->created_at?->gte(now()->subDays(Blocklist::RECENT_DAYS)) ? 1 : 0))
                : 0,
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
            'country_for' => ['nullable', 'string', 'in:'.implode(',', array_keys(Blocklist::DURATIONS))],
        ]);

        try {
            $result = $this->blocks->block(
                $user,
                $request->user(),
                $validated['note'] ?? null,
                $request->boolean('block_address'),
                $request->boolean('block_domain'),
                $request->boolean('block_country'),
                Blocklist::expiryFor($validated['country_for'] ?? self::COUNTRY_DURATION),
            );
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', __($e->getMessage()));
        }

        $back = redirect()->route('admin.blocked.account', ['hash' => $hash])
            ->with('success', __('messages.block_done', ['name' => $user->name ?: $user->email])
                .($result['taken']->isNotEmpty() ? ' '.__('messages.block_done_schedules', ['count' => number_format($result['taken']->count())]) : ''));

        // An entry typed in by hand that the block asked for longer keeps its own end; said, or
        // the operator takes the country as refused for the month they picked.
        if ($result['kept']->isNotEmpty()) {
            $back->with('warning', __('messages.block_entries_kept', ['entries' => $result['kept']
                ->map(fn (BlocklistEntry $entry) => ($entry->type === Blocklist::COUNTRY ? Blocklist::countryName($entry->value) : $entry->value).' ('.$this->until($entry, $request).')')
                ->implode(', ')]));
        }

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
            'for' => ['nullable', 'string', 'in:'.implode(',', array_keys(Blocklist::DURATIONS))],
            'from' => ['nullable', 'string', 'in:dashboard'],
        ]);

        $duration = $validated['for'] ?? ($validated['type'] === Blocklist::COUNTRY ? self::COUNTRY_DURATION : 'forever');

        try {
            $entry = Blocklist::add($validated['type'], $validated['value'], $validated['note'] ?? null, $request->user()->id, null, Blocklist::expiryFor($duration));
        } catch (\InvalidArgumentException $e) {
            return $this->afterAdding($request)
                ->withInput()
                ->withErrors(['value' => __($e->getMessage())]);
        }

        // `message` and `warning`, which the layout toasts: the redirect lands on the list,
        // far below where the page would print a notice, or on the dashboard's live view.
        $shown = $entry->type === Blocklist::COUNTRY ? Blocklist::countryName($entry->value) : $entry->value;

        if ($entry->renewal) {
            AuditService::log(AuditService::ADMIN_BLOCKLIST_ADD, $request->user()->id, 'App\\Models\\BlocklistEntry', $entry->id, null, ['type' => $entry->type, 'value' => $entry->value, 'expires_at' => $entry->expires_at?->toIso8601String(), 'renewal' => $entry->renewal]);

            return $this->afterAdding($request)
                ->with('message', __('messages.blocklist_'.$entry->renewal, ['value' => $shown]).' '.$this->until($entry, $request).'.');
        }

        // Said with the end it keeps: a shorter length asked for does not shorten it.
        if (! $entry->wasRecentlyCreated) {
            return $this->afterAdding($request)
                ->with('warning', __('messages.blocklist_already_listed', ['value' => $shown]).($entry->expires_at ? ' '.$this->until($entry, $request).'.' : ''));
        }

        AuditService::log(AuditService::ADMIN_BLOCKLIST_ADD, $request->user()->id, 'App\\Models\\BlocklistEntry', $entry->id, null, ['type' => $entry->type, 'value' => $entry->value, 'expires_at' => $entry->expires_at?->toIso8601String()]);

        // How many accounts already match. They are not touched, and a large number is how a
        // mail provider's domain is told from a spammer's. A country cannot be counted in SQL,
        // so its figure is the accounts made from there lately, which is what it would refuse.
        if ($entry->type === Blocklist::COUNTRY) {
            $recent = Blocklist::recentSignupsFrom($entry->match_key);
            $existing = $recent ? ' '.__('messages.blocklist_country_recent', ['count' => number_format($recent), 'days' => Blocklist::RECENT_DAYS]) : '';
        } else {
            $count = Blocklist::existingAccounts($entry->type, $entry->type === Blocklist::EMAIL ? $entry->value : $entry->match_key);
            $existing = $count ? ' '.__('messages.blocklist_existing_accounts', ['count' => number_format($count)]) : '';
        }

        return $this->afterAdding($request)->with(
            'message',
            __('messages.blocklist_added', ['value' => $shown])
                .($entry->expires_at ? ' '.$this->until($entry, $request).'.' : '')
                .$existing
        );
    }

    /**
     * Where an add goes back to: the list, or the dashboard's live view when it came from the
     * menu beside a country there (admin/realtime/_breakdown).
     */
    private function afterAdding(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->to($request->input('from') === 'dashboard' ? route('admin.dashboard') : route('admin.blocked').'#list');
    }

    /** "Until Nov 2, 2026", in the operator's own time zone, or "Until removed". */
    private function until(BlocklistEntry $entry, Request $request): string
    {
        return $entry->expires_at
            ? __('messages.blocklist_until', ['date' => $entry->expires_at->copy()->setTimezone($request->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y')])
            : __('messages.blocklist_for_forever');
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
            ->with('message', __('messages.blocklist_removed', ['value' => $entry->type === Blocklist::COUNTRY ? Blocklist::countryName($entry->value) : $entry->value]));
    }
}
