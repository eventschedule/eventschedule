<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The setup guide: the object that walks a new organizer from a saved first schedule to a live,
 * shared one with a few events on it. It replaced the three-circle step band on the first-run
 * screens (components/step-indicator, which the guest-submit flow still uses).
 *
 * WHO HAS ONE. Only somebody whose first own schedule was saved through the wizard
 * (RoleController::store() calls start()). The guide is pinned to that schedule in
 * users.setup_guide and lasts LIFETIME_DAYS. Nothing here derives eligibility from "owns a young
 * schedule": that reaches accounts the guest-submit flow minted, people matched to an
 * auto-created venue, claims, the API and restores, none of whom chose to set anything up.
 *
 * TWO QUESTIONS, ASKED SEPARATELY. state() is "what does this person's guide say", independent of
 * the page; surface() is "which shape, if any, belongs on this page". They are split because
 * the layout's view composer runs after the page body has rendered, so anything that needs the
 * guide outside the layout (the dashboard, "Dismiss all", the publish redirect) must be able to
 * ask for it directly. "Dismiss all" is the one that matters: a guide it could not see would
 * have permanently dismissed the Next steps rows the guide was hiding, and silenced their mail.
 *
 * NO FRACTIONS. The ring is the count; the words say how close ("One step from live", "Two more
 * to go"). Nothing built here prints "N of M", and SetupGuideTest holds that.
 */
class SetupGuide
{
    public const LIFETIME_DAYS = 30;

    /** After this the guide stops volunteering itself: one line on the dashboard, a ring elsewhere. */
    public const QUIET_HOURS = 72;

    /** The steps of the second stretch, in order. Each can be turned down; the first three cannot. */
    public const SKIPPABLE = ['events', 'share', 'tickets'];

    /** How many events make "a page worth sharing". One that repeats also counts. */
    public const EVENTS_GOAL = 3;

    /**
     * How long a finished guide stays to offer the next event when nobody dismisses it. Not "for
     * the session": sessions slide (SESSION_LIFETIME is a day of INACTIVITY), so somebody who
     * opens the app every morning would keep "is set up" on their dashboard for a month.
     */
    public const FINISHED_HOURS = 12;

    /**
     * The only pages that can carry a shape. surface() reads this before it asks what the guide
     * says, so every other admin page costs the guide one ownership check (line()) and not the
     * half-dozen queries state() runs.
     */
    private const ROUTES = ['home', 'event.create', 'event.edit', 'event.clone', 'role.edit', 'role.audit_log', 'role.view_admin'];

    private const MEMO = 'setup_guide.state';

    /**
     * Begin a guide for $role. Called once, by the wizard's save of a first owned schedule.
     *
     * One whole object, never a merge: somebody who deletes their first schedule and starts again
     * is "first" again, and a merged write would hand the new schedule the old one's hidden,
     * shared and skipped answers. A guide that was FINISHED is the exception and is left alone -
     * they have been through it.
     */
    public static function start(User $user, Role $role): void
    {
        $fresh = [
            'role_id' => (int) $role->id,
            'started_at' => now()->toIso8601String(),
        ];

        // Base query, as every write here is: Model::save(), quiet or not, bumps users.updated_at,
        // which the admin active-users metric reads.
        $started = DB::table($user->getTable())
            ->where('id', $user->id)
            ->where(fn ($q) => $q->whereNull('setup_guide')
                ->orWhereRaw("JSON_EXTRACT(setup_guide, '$.completed_at') IS NULL"))
            ->update(['setup_guide' => json_encode($fresh)]);

        if ($started) {
            self::remember($user, $fresh);
        }
    }

    /** Record that something happened, once. A second call keeps the first time. */
    public static function stamp(User $user, string $key): void
    {
        $path = '$.'.$key;

        // COALESCE is not decoration: JSON_SET(NULL, ...) is NULL in MySQL, so a write to a
        // column that was never initialised would silently store nothing.
        DB::update(
            'UPDATE '.$user->getTable().' SET setup_guide = JSON_SET(COALESCE(setup_guide, JSON_OBJECT()), ?, ?)'
            .' WHERE id = ? AND JSON_EXTRACT(COALESCE(setup_guide, JSON_OBJECT()), ?) IS NULL',
            [$path, now()->toIso8601String(), $user->id, $path]
        );

        self::reload($user);
    }

    /** Take a stamp back (Undo after hiding). */
    public static function clear(User $user, string $key): void
    {
        DB::update(
            'UPDATE '.$user->getTable().' SET setup_guide = JSON_REMOVE(setup_guide, ?) WHERE id = ? AND setup_guide IS NOT NULL',
            ['$.'.$key, $user->id]
        );

        self::reload($user);
    }

    /**
     * Turn a second-stretch step down, or take that answer back.
     *
     * The list is read and written back under a row lock: two answers given a moment apart are
     * two requests, and without it each would write the list it read and the later one would
     * drop the earlier answer. Written as a whole document rather than with CAST(? AS JSON),
     * which MariaDB (a database the selfhost docs name as supported) does not have.
     */
    public static function skip(User $user, string $step, bool $skipped = true): void
    {
        if (! in_array($step, self::SKIPPABLE, true)) {
            return;
        }

        DB::transaction(function () use ($user, $step, $skipped) {
            $row = DB::table($user->getTable())->where('id', $user->id);
            $raw = (clone $row)->lockForUpdate()->value('setup_guide');
            $guide = is_string($raw) ? (json_decode($raw, true) ?: []) : [];
            $current = $guide['skipped'] ?? [];

            $guide['skipped'] = $skipped
                ? array_values(array_unique(array_merge($current, [$step])))
                : array_values(array_diff($current, [$step]));

            $row->update(['setup_guide' => json_encode($guide)]);
        });

        self::reload($user);
    }

    /**
     * The one place the "Turn off suggestions" switch is written, whichever door was used: the
     * dashboard's card or the toggle in Account settings.
     *
     * Turning it back on marks a guide whose schedule went live in the meantime as already
     * celebrated. Nothing could stamp that while every shape was gone, and the confetti for a
     * page that has been live for weeks is not a reward, it is a glitch.
     */
    public static function suggest(User $user, bool $on): void
    {
        if ($on === $user->wantsSuggestions()) {
            return;
        }

        $at = $on ? null : now();

        DB::table($user->getTable())->where('id', $user->id)->update(['suggestions_off_at' => $at]);

        // On the model in hand and out of the memo BEFORE asking what the guide says: asked
        // first, pinned() would still answer for a switch that is off.
        $user->setAttribute('suggestions_off_at', $at);
        $user->syncOriginalAttribute('suggestions_off_at');
        self::flush();

        if ($on && (self::state($user)['celebrate'] ?? false)) {
            self::stamp($user, 'celebrated_at');
        }
    }

    /**
     * The schedule this person answered "No tickets needed" for, or null.
     *
     * Read from the stored column and not from state(), so the answer outlives the guide: it is
     * still an answer after the guide finished, lapsed or was hidden. Deliberately not copied
     * into dismissed_next_steps. One home for it means the guide's Undo cannot delete a
     * dismissal an X made, a stale tab cannot write for a schedule that is gone, and the growth
     * export's dismissal counts keep meaning "an X or Dismiss all". SendActivationNudges::base()
     * asks the same question in SQL.
     */
    public static function declinedTicketsFor(?User $user): ?int
    {
        $guide = $user?->setup_guide;

        return is_array($guide) && ! empty($guide['role_id']) && in_array('tickets', $guide['skipped'] ?? [], true)
            ? (int) $guide['role_id']
            : null;
    }

    /** A schedule's face in the guide and in a suggestion row: its photo, or its first letter. */
    public static function face(Role $role): array
    {
        return [
            'initial' => self::initial($role->name),
            'photo' => $role->profile_image_url ? $role->getProfileImageUrl(96) : '',
        ];
    }

    /**
     * What this person's guide says right now, or null when they have none to show.
     *
     * Memoized on the REQUEST, not in a static or the container: a feature test makes several
     * requests against one application instance, and a guide remembered from the first would
     * answer for the rest.
     */
    public static function state(?User $user = null): ?array
    {
        $request = request();

        if ($request->attributes->has(self::MEMO)) {
            return $request->attributes->get(self::MEMO);
        }

        $state = self::resolve($user ?? $request->user());
        $request->attributes->set(self::MEMO, $state);

        return $state;
    }

    /** Forget the memo, after a write that changes the answer within the same request. */
    public static function flush(): void
    {
        request()->attributes->remove(self::MEMO);
    }

    /**
     * Which shape belongs on this page: section, panel, strip, dock, pill, ring or none.
     *
     * An explicit list that defaults to none. "Every page of the schedule" would float the pill
     * over the seating designer, the box office and the scanner, and over the import list's own
     * "Add N events" button, which is exactly where the guide sends people.
     */
    public static function surface(?Request $request = null, ?array $state = null): string
    {
        $request ??= request();
        $route = $request->route();
        $name = $route?->getName();

        // By page first, and only then by what the guide says: most admin pages can never carry
        // a shape, and none of them should pay for resolving one. The installation's own admin
        // pages are refused by path as well as by name: the blog editor lives under /admin but
        // is named blog.*.
        if (! in_array($name, self::ROUTES, true) || $request->is('admin', 'admin/*')) {
            return 'none';
        }

        $state ??= self::state();

        if (! $state || ! empty($state['hidden'])) {
            return 'none';
        }

        if ($name === 'home') {
            return 'section';
        }

        // Everything else is a page of ONE schedule, and the guide keeps to its own.
        if ($route->parameter('subdomain') !== $state['subdomain']) {
            return 'none';
        }

        if ($name === 'event.create') {
            return $state['live'] ? 'ring' : 'dock';
        }

        if (in_array($name, ['role.edit', 'event.edit', 'event.clone'], true)) {
            return 'ring';
        }

        if ($name === 'role.audit_log') {
            return 'pill';
        }

        if ($name !== 'role.view_admin') {
            return 'none';
        }

        $tab = $route->parameter('tab');

        if ($tab === 'availability') {
            return 'ring';
        }

        if ($tab !== 'schedule') {
            return 'pill';
        }

        // The Schedule tab is where an event save and an import land, and so where the app
        // already says "that worked". The guide speaks there, in the page, and nowhere else on
        // that view: one stage rather than a panel, a pill and a toast.
        //
        // It speaks when the schedule has just gone live, and after every event that lands
        // while the page still wants events or has not been shared - which includes the save
        // that completes "add more events", whose forward button is then "Copy link".
        if ($state['celebrate'] || (self::landed() && in_array($state['current'], ['events', 'share'], true))) {
            return is_array(session('events_imported')) ? 'strip' : 'panel';
        }

        return 'pill';
    }

    /**
     * The sidebar's question, asked on every admin page: is there a guide to go to, and is it
     * hidden? Answered from the stored column and one ownership check. It never resolves the
     * guide (state() counts events, reads tickets and plans), because nearly every page that asks
     * this shows no shape at all.
     */
    public static function line(?User $user = null): ?array
    {
        $request = request();

        // The page already asked: say the same thing it was told.
        if ($request->attributes->has(self::MEMO)) {
            $state = $request->attributes->get(self::MEMO);

            return $state && empty($state['retired']) ? ['hidden' => ! empty($state['hidden'])] : null;
        }

        $user ??= $request->user();
        $guide = self::pinned($user);

        if (! $guide || ! empty($guide['completed_at'])) {
            return null;
        }

        if (! empty($guide['dismissed_at'])) {
            return ['hidden' => true];
        }

        return self::schedule($user, $guide, false) ? ['hidden' => false] : null;
    }

    /**
     * Whether $event is on its schedule's public page: page()'s own test, for one event in hand.
     * A draft, an unlisted or an internal event saved on a live schedule is not "on your page",
     * and the panel that says so must not answer it.
     */
    public static function onPage(Event $event): bool
    {
        return ! $event->is_draft && ! $event->is_private && ! $event->is_internal && ! $event->appointment_type_id;
    }

    /**
     * What the component is handed: the state, the shape for this page, and every string it can
     * show. The strings go over raw, placeholders and all, and the component fills in the
     * schedule's and the event's names itself, each inside a bidi isolate, so a Hebrew name in an
     * English sentence (or the reverse) cannot reorder the words around it.
     */
    public static function payload(array $state, string $surface): array
    {
        $route = request()->route()?->getName();

        return array_merge($state, [
            'surface' => $surface,
            't' => self::strings(),
            // The page that IS the current step shows "You are here" rather than a button back to
            // itself.
            'here' => $state['current'] === 'event' && $route === 'event.create',
            // Arriving from the wizard's save: the moment the schedule step is acknowledged.
            'arrived' => $surface === 'dock' && (bool) session('onboarding_event_redirect'),
            // An event (or an import) has just landed on the page.
            'saved' => in_array($surface, ['panel', 'strip'], true),
            // Its name, for "... is on your page". Asked for separately because the strip shows
            // the NEXT three events, and the one just added may be a fourth, months away.
            'landed' => $surface === 'panel' ? self::latestName((int) $state['role_id']) : null,
            // Below 1280px the pill has no room, and only the Schedule tab keeps a chip.
            'chip' => $surface === 'pill' && $route === 'role.view_admin'
                && request()->route('tab') === 'schedule' && ! $state['quiet'],
            'locale' => app()->getLocale(),
            // This install's own copy: never a CDN.
            'confetti' => asset('vendor/canvas-confetti/confetti.browser.min.js'),
        ]);
    }

    /**
     * The dashboard's card for somebody with suggestions and no guide to show: the same rows,
     * the same strings, no guide state. `surface` is `steps`, which the boot script reads to
     * mount the list rather than the guide.
     */
    public static function listPayload($rows, bool $dismissedBefore): array
    {
        return [
            'surface' => 'steps',
            't' => self::strings(),
            'suggestions' => self::suggestions($rows, null, $dismissedBefore),
            'locale' => app()->getLocale(),
        ];
    }

    /**
     * The dashboard's suggestions (HomeController::getNextStepItems(), already through
     * withoutCoveredSteps()) as the card wants them: each a row, split into the ones about the
     * showing guide's own schedule and the rest, with the urls the card posts to.
     *
     * The guide's own schedule is not repeated in a list under it with a second circle and a
     * second name: its suggestion is one plain line inside the guide. Everybody else's is a row.
     */
    public static function suggestions($rows, ?array $state, bool $dismissedBefore): array
    {
        $own = $state && empty($state['hidden']) ? UrlUtils::encodeId($state['role_id']) : null;

        $rows = collect($rows)
            ->filter(fn ($item) => ! empty($item['dismiss_schedule']))
            ->map(fn ($item) => [
                'key' => $item['dismiss_schedule'].':'.$item['type'],
                'type' => $item['type'],
                'schedule' => $item['dismiss_schedule'],
                'title' => (string) $item['title'],
                'name' => (string) $item['subtitle'],
                'url' => $item['url'],
                'photo' => $item['photo'] ?? '',
                'initial' => $item['initial'] ?? '',
            ])
            ->values();

        return [
            'own' => $rows->filter(fn ($row) => $row['schedule'] === $own)->values()->all(),
            'others' => $rows->reject(fn ($row) => $row['schedule'] === $own)->values()->all(),
            // Somebody who has dismissed a suggestion before and is looking at suggestions again
            // is the person for whom they came back: the list offers them the switch outright.
            'dismissed_before' => $dismissedBefore,
            // Only with something to list: DashboardNextStepsTest holds that a dashboard with
            // nothing to suggest does not say "Next steps" anywhere, a payload included.
            't' => $rows->isEmpty() ? [] : [
                'next_steps' => __('messages.next_steps'),
                'dismiss_all' => __('messages.next_steps_dismiss_all'),
                'dismiss_for' => trans('messages.next_step_dismiss'),
                'show_more' => trans('messages.pending_action_show_more'),
            ],
            'urls' => [
                'dismiss' => route('home.next_steps_dismiss'),
                'dismiss_all' => route('home.next_steps_dismiss_all'),
                'restore' => route('home.next_steps_restore'),
                'switch' => route('home.suggestions'),
                'settings' => route('profile.edit', ['highlight' => 'suggestions']).'#section-profile',
            ],
        ];
    }

    /**
     * Every string the component can show. They go over raw, placeholders and all.
     */
    private static function strings(): array
    {
        $strings = [];

        foreach ((array) trans('messages') as $key => $value) {
            if (is_string($value) && str_starts_with($key, 'setup_guide_')) {
                $strings[substr($key, strlen('setup_guide_'))] = $value;
            }
        }

        // Wording the product already has for the same thing, so the guide and the page it
        // points at never call one button two names.
        return $strings + [
            'step_event' => __('messages.next_step_add_first_event'),
            'add_another' => __('messages.add_another_event'),
            'add_tickets' => __('messages.add_tickets'),
            'publish' => __('messages.publish'),
            'add_to_website' => __('messages.import_panel_embed'),
            'skip' => __('messages.skip_for_now'),
            'draft' => __('messages.draft'),
            'done' => __('messages.done'),
            'dismiss' => __('messages.dismiss'),
            'more' => __('messages.and_n_more'),
            'settings' => __('messages.settings'),
        ];
    }

    /**
     * Whether the page's success toast should stay quiet because the guide is about to say the
     * same thing: arriving at the first-event form from the wizard, and the going-live view.
     * The session flash itself is never removed before this point, so a test asserting on the
     * redirect still sees it.
     */
    public static function speaksForToast(?Request $request = null): bool
    {
        $surface = self::surface($request);

        if ($surface === 'dock') {
            return (bool) session('onboarding_event_redirect');
        }

        // Only for the redirect that brought the event the panel is about. The panel also shows
        // on any first visit to the Schedule tab after going live, and a toast that arrives with
        // that visit ("Schedule updated") is somebody else's news: the panel does not say it.
        return in_array($surface, ['panel', 'strip'], true) && self::landed();
    }

    /** An event save, a publish or an import redirected here: each flashes its own marker. */
    private static function landed(): bool
    {
        return is_array(session('events_imported'))
            || (bool) session('setup_guide_saved')
            || is_array(session('first_event_created'));
    }

    /**
     * The furthest point a stored guide has reached, for the growth export: null when the person
     * never had one, else started, live, shared, embedded or finished. One of our own words,
     * never anything the person typed.
     */
    public static function stage(mixed $guide): ?string
    {
        if (! is_array($guide) || empty($guide['role_id'])) {
            return null;
        }

        foreach (['completed_at' => 'finished', 'embedded_at' => 'embedded', 'shared_at' => 'shared', 'celebrated_at' => 'live'] as $stamp => $stage) {
            if (! empty($guide[$stamp])) {
                return $stage;
            }
        }

        return 'started';
    }

    /** Whether $role is the schedule this user's guide is walking, without asking the database. */
    public static function belongsTo(?User $user, Role $role): bool
    {
        $guide = $user?->setup_guide;

        return is_array($guide)
            && (int) ($guide['role_id'] ?? 0) === (int) $role->id
            && empty($guide['completed_at'])
            && empty($guide['dismissed_at']);
    }

    /** Whether saving or publishing just made this user's guide schedule live for the first time. */
    public static function shouldCelebrate(User $user, Role $role): bool
    {
        self::flush();
        $state = self::state($user);

        return $state
            && empty($state['hidden'])
            && $state['role_id'] === (int) $role->id
            && $state['celebrate'];
    }

    /**
     * The dashboard's suggestions, minus the ones the guide speaks for. The dashboard has ONE
     * card for "what next", and inside one card a second voice asking the guide's own question
     * (or one it has just been answered) reads as the app not listening.
     *
     *   - "No tickets needed" is an answer, for good: that schedule's tickets row never comes
     *     back, guide or no guide (declinedTicketsFor()).
     *   - A HIDDEN guide holds every row about its schedule. Hiding it used to release the rows
     *     it was holding back, so the thing just dismissed was replaced on the spot. Nothing is
     *     written as dismissed: when the guide's 30 days are up the schedule is suggested to
     *     like any other, and no reminder email is silenced by hiding a widget. The stub does
     *     not re-check ownership, so somebody who handed the schedule on but still edits it
     *     loses those rows until then; the check would cost a query on every dashboard load.
     *   - A SHOWING guide holds what it is asking, or has been answered: every "add or publish
     *     an event" row before the schedule is live; "add your next date" while its "Add more
     *     events" step is open; and the tickets row whatever state its tickets step is in. The
     *     guide ticks that step for an event that takes sign-ups, which the suggestion does not
     *     count, so held only while the step was open it came back under the tick. A row saying
     *     people are waiting to buy always gets through: the guide does not know to say that.
     *
     * "Dismiss all" goes through this too, so it never dismisses a row nobody was shown.
     */
    public static function withoutCoveredSteps($items)
    {
        if ($declined = self::declinedTicketsFor(request()->user())) {
            $schedule = UrlUtils::encodeId($declined);

            $items = $items->reject(fn ($item) => $item['type'] === 'next_step_tickets'
                && ($item['dismiss_schedule'] ?? null) === $schedule)->values();
        }

        $state = self::state();

        if (! $state) {
            return $items;
        }

        $schedule = UrlUtils::encodeId($state['role_id']);

        if (! empty($state['hidden'])) {
            return $items->reject(fn ($item) => ($item['dismiss_schedule'] ?? null) === $schedule)->values();
        }

        $events = $state['steps']['events'] ?? null;
        $covered = match (true) {
            ! $state['live'] => ['next_step_first_event', 'next_step_publish_event', 'next_step_next_event'],
            $events && ! $events['done'] && ! $events['skipped'] => ['next_step_next_event'],
            default => [],
        };

        return $items->reject(function ($item) use ($schedule, $covered, $state) {
            if (($item['dismiss_schedule'] ?? null) !== $schedule) {
                return false;
            }

            if ($item['type'] === 'next_step_tickets') {
                return isset($state['steps']['tickets']) && empty($item['waiting']);
            }

            return in_array($item['type'], $covered, true);
        })->values();
    }

    /**
     * The stored guide, unless the column alone already rules it out. No queries: this is the
     * half of resolve() that every admin page can afford (line()).
     */
    private static function pinned(?User $user): ?array
    {
        // The switch first: off, there is no shape anywhere and no line in the sidebar.
        if (! $user || ! $user->wantsSuggestions() || is_demo_mode() || session('pending_request')) {
            return null;
        }

        $guide = $user->setup_guide;

        if (! is_array($guide) || empty($guide['role_id']) || empty($guide['started_at'])) {
            return null;
        }

        // A finished guide stays a while in the session it finished in, so the last thing it
        // does is offer the next event rather than vanish. Dismissing it forgets the session key.
        if (! empty($guide['completed_at']) && (
            (int) session('setup_guide_finished') !== (int) $guide['role_id']
            || Carbon::parse($guide['completed_at'])->lt(now()->subHours(self::FINISHED_HOURS))
        )) {
            return null;
        }

        if (Carbon::parse($guide['started_at'])->lt(now()->subDays(self::LIFETIME_DAYS))) {
            return null;
        }

        return $guide;
    }

    /**
     * The guide's schedule, if this person still owns it. Asked fresh rather than read from the
     * per-request userRoles singleton: this is the one place that must never answer for a
     * schedule that was deleted or handed over since.
     */
    private static function schedule(User $user, array $guide, bool $withPlan = true): ?Role
    {
        $role = $user->roles()
            ->when($withPlan, fn ($query) => $query->with('subscriptions'))
            ->where('roles.id', $guide['role_id'])
            ->wherePivot('level', 'owner')
            ->first();

        return $role && $role->isClaimed() ? $role : null;
    }

    private static function resolve(?User $user): ?array
    {
        $guide = self::pinned($user);

        if (! $guide) {
            return null;
        }

        $finished = ! empty($guide['completed_at']);
        $started = Carbon::parse($guide['started_at']);

        // Hidden: the sidebar line still needs to know, and it needs nothing else.
        if (! empty($guide['dismissed_at']) && ! $finished) {
            return ['hidden' => true, 'role_id' => (int) $guide['role_id']];
        }

        $role = self::schedule($user, $guide);

        if (! $role) {
            return null;
        }

        $skipped = array_values(array_intersect($guide['skipped'] ?? [], self::SKIPPABLE));
        $page = self::page($role);
        $live = $page['total'] > 0;

        // A curator that only lists other people's events owns none of them and can price none.
        $asksTickets = ! $role->isCurator() || $page['owns'];
        $hasTickets = $live && $asksTickets && ! in_array('tickets', $skipped, true) && self::hasTickets($role);

        $steps = [
            'event' => ['done' => $live, 'skipped' => false],
            'events' => [
                'done' => $live && ($page['total'] >= self::EVENTS_GOAL || $page['repeating'] > 0),
                'skipped' => in_array('events', $skipped, true),
            ],
            'share' => [
                'done' => ! empty($guide['shared_at']) || ! empty($guide['embedded_at']),
                'skipped' => in_array('share', $skipped, true),
            ],
        ];

        if ($asksTickets) {
            $steps['tickets'] = ['done' => $hasTickets, 'skipped' => in_array('tickets', $skipped, true)];
        }

        $current = null;
        $remaining = 0;

        foreach ($steps as $key => $step) {
            if ($step['done'] || $step['skipped']) {
                continue;
            }

            $current ??= $key;
            $remaining++;
        }

        // An import undone can take a live schedule back to no events. The guide then asks for a
        // first event again, quietly: celebrated_at stays, so the confetti does not.
        if (! $live) {
            $current = 'event';
            $remaining = 1;
        }

        $subdomain = $role->subdomain;
        $url = $role->getCanonicalUrl();
        $owned = $page['events']->first(fn (Event $event) => (int) $event->creator_role_id === (int) $role->id);

        return [
            'hidden' => false,
            'role_id' => (int) $role->id,
            'subdomain' => $subdomain,
            'type' => $role->type,
            'name' => (string) $role->name,
            ...self::face($role),
            'url' => $url,
            'address' => preg_replace('#^https?://#i', '', rtrim($url, '/')),
            'live' => $live,
            'celebrate' => $live && empty($guide['celebrated_at']) && ! $finished,
            'quiet' => $started->lt(now()->subHours(self::QUIET_HOURS)),
            'finished' => $finished || ($live && $current === null),
            'retired' => $finished,
            'embedded' => ! empty($guide['embedded_at']),
            'shared' => ! empty($guide['shared_at']),
            'steps' => $steps,
            'current' => $current,
            'remaining' => $remaining,
            'total' => $page['total'],
            'more' => max(0, $page['total'] - $page['events']->count()),
            'events' => $page['events']->map(fn (Event $event) => self::tile($event, $role))->all(),
            'draft' => $live ? null : self::draft($role),
            'ticket_event' => $owned ? [
                'name' => (string) $owned->name,
                'url' => route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($owned->id)]).'#section-tickets',
            ] : null,
            // Asked while the step is skipped too: the row still names itself ("Tickets or free
            // entry?" or the sign-up wording), and Undo reopens the question in the same words.
            'can_sell' => $asksTickets && ! $hasTickets
                ? ($role->canSellPaidTickets() || $role->isEligibleForTicketTrial())
                : false,
            'urls' => [
                'endpoint' => route('home.setup_guide'),
                'dashboard' => route('home').'#setup-guide',
                'add' => route('event.create', ['subdomain' => $subdomain]),
                'import' => route('event.show_import_ai', ['subdomain' => $subdomain]),
                'schedule' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']),
                'embed' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']).'#embed',
                'style' => route('role.edit', ['subdomain' => $subdomain]).'#section-style',
                'repeat' => $owned
                    ? route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($owned->id)]).'#section-recurring'
                    : null,
            ],
        ];
    }

    /**
     * What is on the schedule's public page: how many published events, whether one repeats, and
     * the three to show. "Published" is the guest page's own test - not a draft, not unlisted,
     * not internal - and never an appointment booking, which is an events row named for the
     * guest who booked it.
     */
    private static function page(Role $role): array
    {
        $listed = fn () => Event::query()
            ->join('event_role', 'event_role.event_id', '=', 'events.id')
            ->where('event_role.role_id', $role->id)
            ->where(fn ($q) => $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->orWhere('event_role.is_accepted', true))
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->whereNull('events.appointment_type_id');

        $counts = $listed()->toBase()
            ->selectRaw('COUNT(DISTINCT events.id) as total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN events.days_of_week IS NOT NULL THEN events.id END) as repeating')
            ->selectRaw('COUNT(DISTINCT CASE WHEN events.creator_role_id = ? THEN events.id END) as owns', [$role->id])
            ->first();

        $total = (int) ($counts->total ?? 0);

        // Upcoming first, then undated, then past. A repeating event's starts_at is only its
        // anchor, so it counts as upcoming whatever that date is.
        //
        // events.* on purpose, creator_role_id included: a narrowed select without it makes
        // BelongsTo short-circuit on the null key, and the date would then be rendered in the
        // application's timezone instead of the schedule's, with no query and no error.
        $events = $total === 0 ? collect() : $listed()
            ->select('events.*')
            ->orderByRaw('CASE WHEN events.starts_at IS NULL THEN 1 WHEN events.days_of_week IS NULL AND events.starts_at < ? THEN 2 ELSE 0 END', [now('UTC')->format('Y-m-d H:i:s')])
            ->orderBy('events.starts_at')
            ->orderBy('events.id')
            ->limit(self::EVENTS_GOAL)
            ->get();

        // The schedule's own events already have their timezone in hand; only an event accepted
        // from somebody else's schedule has to look its own up.
        $events->each(function (Event $event) use ($role) {
            if ((int) $event->creator_role_id === (int) $role->id) {
                $event->setRelation('creatorRole', $role);
            }
        });

        return [
            'total' => $total,
            'repeating' => (int) ($counts->repeating ?? 0),
            'owns' => (int) ($counts->owns ?? 0) > 0,
            'events' => $events,
        ];
    }

    /** One slot of "Your page": the date as the SCHEDULE sees it, and the name. */
    private static function tile(Event $event, Role $role): array
    {
        $repeats = $event->days_of_week !== null;
        $date = null;

        if ($event->starts_at) {
            if ($repeats) {
                $next = $event->nextOccurrenceFrom();
                $date = $next ? Carbon::parse($next) : null;
            } else {
                $date = $event->getStartDateTime(null, true);
            }
        }

        return [
            'id' => UrlUtils::encodeId($event->id),
            'name' => (string) $event->name,
            'month' => $date?->translatedFormat('M'),
            'day' => $date?->format('j'),
            'repeats' => $repeats,
            // Which tile is newest, for the one that tears off after a save.
            'created' => $event->created_at?->timestamp,
            'url' => route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
        ];
    }

    /** The name of the event most recently added to the schedule's public page. */
    private static function latestName(int $roleId): ?string
    {
        $name = Event::query()
            ->join('event_role', 'event_role.event_id', '=', 'events.id')
            ->where('event_role.role_id', $roleId)
            ->where(fn ($q) => $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->orWhere('event_role.is_accepted', true))
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_internal', false)
            ->whereNull('events.appointment_type_id')
            ->orderByDesc('events.id')
            ->value('events.name');

        return $name === null ? null : (string) $name;
    }

    /** The draft a not-yet-live schedule could publish, soonest first. */
    private static function draft(Role $role): ?array
    {
        $draft = Event::query()
            ->where('creator_role_id', $role->id)
            ->where('is_draft', true)
            ->where('is_internal', false)
            ->whereNull('appointment_type_id')
            ->orderByRaw('starts_at IS NULL')
            ->orderBy('starts_at')
            ->first(['id', 'name', 'creator_role_id', 'starts_at']);

        if (! $draft) {
            return null;
        }

        $hash = UrlUtils::encodeId($draft->id);

        return [
            'name' => (string) $draft->name,
            'edit' => route('event.edit', ['subdomain' => $role->subdomain, 'hash' => $hash]),
            'publish' => route('event.publish', ['subdomain' => $role->subdomain, 'hash' => $hash]),
        ];
    }

    /**
     * A ticket type or registration on an event the schedule can price. Registration counts:
     * "take sign-ups" is events.rsvp_enabled with no ticket row at all, and the nudge mail
     * already treats it as answered (SendActivationNudges).
     */
    private static function hasTickets(Role $role): bool
    {
        return DB::table('event_role')
            ->join('events', 'events.id', '=', 'event_role.event_id')
            ->where('event_role.role_id', $role->id)
            ->where(fn ($q) => $q->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->when(! $role->isCurator(), fn ($q) => $q->orWhere('event_role.is_accepted', true)))
            ->where(fn ($q) => $q->where('events.rsvp_enabled', true)
                ->orWhereExists(fn ($t) => $t->selectRaw('1')->from('tickets')
                    ->whereColumn('tickets.event_id', 'events.id')
                    ->where('tickets.is_deleted', false)
                    ->where('tickets.is_addon', false)))
            ->exists();
    }

    /** The first LETTER, not the first byte: a Hebrew or Japanese name is not cut in half. */
    private static function initial(?string $name): string
    {
        return mb_strtoupper(mb_substr(trim((string) $name), 0, 1));
    }

    /** Re-read the column and put it on the model in hand, so the rest of the request agrees. */
    private static function reload(User $user): array
    {
        $raw = DB::table($user->getTable())->where('id', $user->id)->value('setup_guide');
        $guide = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

        self::remember($user, $guide);

        return $guide;
    }

    /** Keep the in-memory user in step with a base-query write, without marking it dirty. */
    private static function remember(User $user, array $guide): void
    {
        $user->setAttribute('setup_guide', $guide ?: null);
        $user->syncOriginalAttribute('setup_guide');
        self::flush();
    }
}
