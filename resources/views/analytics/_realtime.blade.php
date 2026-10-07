    {{-- The Realtime tab of /analytics: a schedule owner's live view of their own guest pages
         (AnalyticsController::index(), ScheduleRealtime; the poll is RealtimeController::data()).

         Two things side by side. The live traffic of the last half hour (ScheduleRealtime: page
         views, the people here who accepted cookies, where they came from), and what people DID
         on these schedules in the last day (ScheduleActivity: sales, registrations, bookings,
         followers and requests in a rail, and arrivals at an event that is on). The second is the
         owner's own records and is why the tab is not a card of zeros on a quiet afternoon. It
         names nobody either: a row links to the list page where the person is.

         A tab and not a page of its own: it answers the same question as the tabs beside it, for
         the last half hour instead of the last thirty days, and one sidebar entry for "how are my
         pages doing" is enough. The schedule it is narrowed to is the page's own picker
         (`role_id`); the event picker and the date range do not apply here and are not shown.

         Its own markup and its own script, sharing none with /admin/realtime: that page is about
         people across the whole install, and nothing of it belongs in a document an organizer can
         open. tests/Feature/ScheduleRealtimeTest.php reads this tab's HTML for the admin page's
         strings.

         Everything inside #schedule-realtime is a Vue template, and Vue's runtime compiler treats a
         mustache in a text node as code, including one inside a translated string an operator can
         override, and including an event's name. So every visible string comes from the MSG object
         and every name arrives in the JSON payload, both rendered by Vue's own interpolation. Blade
         output appears only in attributes and in static icon paths. --}}
    <style {!! nonce_attr() !!}>
        @keyframes schedule-realtime-fresh { from { background-color: var(--brand-blue-a10); } to { background-color: transparent; } }
        .schedule-realtime-fresh { animation: schedule-realtime-fresh 2s ease-out 1; }
        @keyframes schedule-realtime-bump { 0% { transform: scale(1); } 35% { transform: scale(1.08); } 100% { transform: scale(1); } }
        .schedule-realtime-bump { animation: schedule-realtime-bump 0.5s ease-out 1; display: inline-block; }
        /* The same pulse for a count button, which is a flex column and must stay one: the class
           above makes its element inline-block so that a bare number can scale. */
        .rt-pulse { animation: schedule-realtime-bump 0.5s ease-out 1; }
        @media (prefers-reduced-motion: reduce) { .schedule-realtime-fresh, .schedule-realtime-bump, .rt-pulse { animation: none; } }

        /* The page is one grid. On a phone and a tablet it is a single column in the order a door
           needs: the door card, the live numbers, Activity, the list of people, the breakdowns.
           `.rt-side` is display: contents there, so its two cards are the page's own items and can
           be ordered apart.

           From 1280, which is a laptop with the sidebar open and about 940px of page: the left
           two thirds hold the live numbers, the people and the four breakdowns two up; the right
           third holds the door card and Activity, beside ALL of it. The breakdowns used to run
           under both columns, and whenever the right side was the taller (two visitors, a full
           rail) the difference opened as a blank band between the people and the breakdowns. Now
           any slack can only be at the foot of the page, and the rail has the whole left column
           to stay in view beside.

           The list of people keeps the full width of its column until a wide screen (1760), and
           only there takes the breakdowns beside it: narrower than that, three columns in seven
           twelfths left the page's name about 75px, and in German none.

           When no page has been opened the left side is one short card: then the two sides share
           the page evenly, so the rail is not a narrow strip beside an empty two thirds. */
        .rt-page { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
        .rt-side { display: contents; }
        .rt-door { order: 1; }
        .rt-ov { order: 2; }
        .rt-rail { order: 3; }
        .rt-grid { order: 4; }

        .rt-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; min-width: 0; }
        .rt-right { display: contents; }
        .rt-ps, .rt-cd { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); min-width: 0; }
        @media (min-width: 768px) {
            .rt-ps, .rt-cd { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1280px) {
            .rt-page { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); grid-template-rows: auto 1fr; }
            .rt-page.rt-quiet { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
            /* Nothing on either side: no visit in half an hour, nothing in the day, nobody at a
               door. Then the page is two short cards of words standing as a pair, and a pair
               is the same height: both take the height of the taller. One row only (the second
               is empty, and its gap alone would leave the right card a rem taller), and the rail
               gives up being sticky, which a card that fits the window has no use for. Not when
               the rail has rows: stretching the quiet card to a day's activity would be a tall
               empty box. */
            .rt-page.rt-still { align-items: stretch; grid-template-rows: auto; }
            .rt-page.rt-still .rt-side { grid-row: 1; }
            .rt-page.rt-still .rt-rail { flex: 1 1 auto; position: static; max-height: none; }
            .rt-ov { grid-column: 1; grid-row: 1; }
            .rt-grid { grid-column: 1; grid-row: 2; }
            .rt-side { display: flex; flex-direction: column; gap: 1rem; grid-column: 2; grid-row: 1 / span 2; min-width: 0; align-self: stretch; }
            /* Activity is as tall as its rows and no taller than the window: it stays in view
               while the left side scrolls, and a long day scrolls inside it. 4rem is the header. */
            .rt-rail { position: sticky; top: 5rem; max-height: calc(100vh - 6rem); }
            .rt-rail-list { overflow-y: auto; min-height: 0; }
        }
        /* On a wide screen the four breakdowns stand in a column of their own beside the people,
           who take both rows. Not two beside and two below: the people and the first two then
           shared a row, and whichever was the shorter left a blank band above the other two. */
        @media (min-width: 1760px) {
            .rt-grid { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); grid-template-rows: auto 1fr; }
            .rt-v { grid-column: 1; grid-row: 1 / span 2; }
            .rt-ps { grid-column: 2; grid-row: 1; grid-template-columns: minmax(0, 1fr); }
            .rt-cd { grid-column: 2; grid-row: 2; grid-template-columns: minmax(0, 1fr); }
        }

        /* The count buttons over Activity: a recessed cell, pressed while it filters the list. On
           the --ap-* tokens, so every palette gets its own shade. */
        .rt-stat { background: var(--ap-tint-sunken); }
        .rt-stat:not(:disabled):hover { background: var(--ap-tint-2); }
        .rt-stat:disabled { cursor: default; }
        .rt-stat-on, .rt-stat-on:not(:disabled):hover { background: var(--brand-blue-a10); box-shadow: var(--ap-inset-pressed); }

        /* The half-hour ledger sits under the two figures, except where the card puts its chart
           below both (a laptop): there it stands beside them, and the rule between moves with it. */
        .rt-ledger { margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid var(--ap-hairline); }
        @media (min-width: 1280px) and (max-width: 1535.98px) {
            .rt-ledger { margin-top: 0; padding-top: 0; border-top: 0; padding-inline-start: 1.5rem; border-inline-start: 1px solid var(--ap-hairline); }
        }

        /* Arrivals against tickets sold. It eases to its new width; with reduced motion it jumps. */
        .rt-door-bar { transition: width 0.6s ease-out; }
        @media (prefers-reduced-motion: reduce) { .rt-door-bar { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">

    @php
        $scheduleRealtimeMsg = [
            'live' => __('messages.realtime_live'),
            'reconnecting' => __('messages.realtime_reconnecting'),
            'stopped' => __('messages.realtime_stopped'),
            'reload' => __('messages.realtime_reload'),
            'rightNow' => __('messages.realtime_right_now_group'),
            'views5' => __('messages.realtime_owner_views_5m'),
            'visitorsNow' => __('messages.realtime_owner_visitors_now'),
            'last30' => __('messages.realtime_last_30_minutes'),
            'pageViews' => __('messages.realtime_views'),
            'embedViews' => __('messages.realtime_owner_embed_views'),
            'lastView' => __('messages.realtime_owner_last_view'),
            'viewsPerMinute' => __('messages.realtime_views_per_minute'),
            'axisNow' => __('messages.realtime_axis_now'),
            'visitors' => __('messages.realtime_visitors'),
            'note' => __('messages.realtime_owner_note'),
            'earlier' => __('messages.realtime_earlier_group'),
            'colPage' => __('messages.realtime_col_page'),
            'colTime' => __('messages.realtime_owner_time_on_page'),
            'nobodyNow' => __('messages.realtime_owner_nobody_now'),
            'left' => __('messages.realtime_left'),
            'eventPage' => __('messages.realtime_owner_event_page'),
            'schedulePage' => __('messages.realtime_owner_schedule_page'),
            'emptyTitle' => __('messages.realtime_owner_empty_title'),
            'emptyBody' => __('messages.realtime_owner_empty_body'),
            'copyLink' => __('messages.copy_link'),
            'copied' => __('messages.copied'),
            'topPages' => __('messages.realtime_top_pages'),
            'sources' => __('messages.realtime_sources'),
            'countries' => __('messages.realtime_countries'),
            'devices' => __('messages.realtime_owner_devices'),
            'unitViews' => __('messages.realtime_owner_unit_views'),
            'unitVisits' => __('messages.realtime_owner_unit_visits'),
            'other' => __('messages.other'),
            'nothingYet' => __('messages.realtime_nothing_yet'),
            'showingNewest' => __('messages.realtime_showing_newest'),
            'capped' => __('messages.realtime_owner_capped', ['count' => number_format(\App\Services\ScheduleRealtime::FETCH_CAP)]),
            'quiet' => __('messages.dash_quiet_now'),
            'saleMark' => __('messages.realtime_owner_sale_mark'),
            'sales' => __('messages.sales'),
            'registrations' => __('messages.realtime_registrations'),
            'checkoutsStarted' => __('messages.realtime_checkouts_started'),
            'checkoutsPaid' => __('messages.realtime_checkouts_paid'),
            'nowCount' => __('messages.realtime_owner_now_count'),
            'activity' => __('messages.realtime_activity'),
            'last24h' => __('messages.realtime_last_24_hours'),
            'activityEmpty' => __('messages.realtime_owner_activity_empty'),
            'activityFailed' => __('messages.realtime_owner_activity_failed'),
            'removeFilter' => __('messages.realtime_remove_filter'),
            'showAll' => __('messages.realtime_show_all'),
            'showLess' => __('messages.realtime_show_less'),
            'allSales' => __('messages.dash_all_sales'),
            'countOf' => __('messages.dash_count_of_total'),
            'doorToday' => __('messages.realtime_door_today'),
            'doorUpdates' => __('messages.realtime_door_updates'),
            'doorMore' => __('messages.realtime_door_more'),
            'checkedIn' => __('messages.realtime_door_checked_in'),
            'registered' => __('messages.realtime_door_registered'),
            'sold' => __('messages.realtime_door_sold'),
            // What a row of Activity is, by kind. Line two of a row is made of these and of the
            // labels below: a label and a number, never a sentence with a count inside it.
            'kind' => [
                'sale' => __('messages.realtime_act_sale'),
                'registration' => __('messages.realtime_act_registration'),
                'booking' => __('messages.realtime_act_booking'),
                'follower' => __('messages.dash_new_follower'),
                'subscriber' => __('messages.realtime_act_subscriber'),
                'request' => __('messages.realtime_act_request'),
                'waitlist' => __('messages.waitlist'),
                'interest' => __('messages.realtime_act_interest'),
                'comment' => __('messages.comment'),
                'photo' => __('messages.realtime_act_photo'),
                'video' => __('messages.realtime_act_video'),
            ],
            'tile' => [
                'sale' => __('messages.sales'),
                'registration' => __('messages.realtime_registrations'),
                'booking' => __('messages.bookings'),
                'follower' => __('messages.followers'),
                'request' => __('messages.requests'),
            ],
            'unit' => ['tickets' => __('messages.tickets'), 'guests' => __('messages.guests')],
            // Not `note`: that is the sentence under "Visitors", and a second key of the same
            // name would silently replace it.
            'rowNote' => ['waiting' => __('messages.realtime_act_waiting'), 'approval' => __('messages.realtime_act_pending')],
            'device' => [
                'mobile' => __('messages.mobile'),
                'desktop' => __('messages.desktop'),
                'tablet' => __('messages.tablet'),
                'other' => __('messages.other'),
            ],
        ];
        $scheduleRealtimeConfig = [
            'url' => route('analytics.realtime.data'),
            'activityUrl' => route('analytics.realtime.activity'),
            'salesUrl' => route('sales'),
            'locale' => app()->getLocale(),
            'rtl' => is_rtl(),
            'selected' => $realtime['selected'],
            // The link an owner with one schedule is offered when nobody has come by. With several
            // there is no one page to send people to, and the empty state says so without one.
            'shareUrl' => count($realtime['schedules']) === 1 ? auth()->user()->manageableRoles()->first()?->getGuestUrl() : null,
        ];
        $deviceIcons = [
            'mobile' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
            'desktop' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
            'tablet' => 'M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z',
            'other' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
        ];
        // One icon and one tint to a kind of Activity row. The class names are written out whole:
        // Tailwind keeps only the classes it can read in a file.
        $paths = \App\Utils\RealtimeIcons::PATHS;
        $activityIcons = [
            'sale' => $paths['order'],
            'registration' => 'M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75',
            'booking' => $paths['schedule'],
            'follower' => $paths['signup'],
            'subscriber' => $paths['email'],
            'request' => 'M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3',
            'waitlist' => $paths['trial'],
            'interest' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
            'comment' => $paths['support'],
            'photo' => 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
            'video' => 'M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z',
        ];
        $neutralTone = ['bg' => 'bg-gray-100 dark:bg-white/[0.06]', 'text' => 'text-gray-500 dark:text-gray-400'];
        $activityTones = [
            'sale' => ['bg' => 'bg-green-50 dark:bg-green-500/10', 'text' => 'text-green-600 dark:text-green-400'],
            'registration' => ['bg' => 'bg-blue-50 dark:bg-blue-500/10', 'text' => 'text-blue-600 dark:text-blue-400'],
            'booking' => ['bg' => 'bg-cyan-50 dark:bg-cyan-500/10', 'text' => 'text-cyan-600 dark:text-cyan-400'],
            'follower' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
            'subscriber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
            'request' => ['bg' => 'bg-orange-50 dark:bg-orange-500/10', 'text' => 'text-orange-600 dark:text-orange-400'],
            'other' => $neutralTone,
        ];
    @endphp

    {{-- Outside the Vue mount: a schedule the plan closes to a team member is missing from this tab,
         and the notice Sales and Check-in show says why. It renders nothing when there is none. --}}
    @include('partials.team-access-notice', ['roles' => $realtime['planBlockedRoles'] ?? collect()])

    <div id="schedule-realtime" v-cloak>
        {{-- When the tab stops listening it is the whole tab that stopped, so it is said once, above
             everything, in the panel every other warning in the admin portal uses. Not for one
             missed answer: on a venue's Wi-Fi that is ordinary, and a panel that comes and goes
             moves the door card from under a thumb. One miss is said beside "Live"; the panel
             waits for the second. --}}
        <div v-if="status === 'stopped' || (status === 'reconnecting' && failures > 1)" class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3" role="status" aria-live="polite">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            <p class="text-sm text-amber-700 dark:text-amber-300">
                <template v-if="status === 'reconnecting'">@{{ msg.reconnecting }}</template>
                <template v-else>
                    @{{ msg.stopped }}
                    <button type="button" @click="reload" class="ms-1 font-semibold underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ msg.reload }}</button>
                </template>
            </p>
        </div>

        <div class="rt-page" :class="[hasTraffic ? '' : 'rt-quiet', still ? 'rt-still' : '']">
            {{-- Right now. Two figures, because they answer two questions: page views count
                 everybody, and a visitor can only be told from another once they have accepted
                 cookies. Beside them a ledger, one label and one number to a row. When nobody has
                 come by in half an hour the card says so in one line, and not in a row of zeros. --}}
            <section class="rt-ov ap-card rounded-xl p-4 sm:p-6 grid gap-6 md:grid-cols-[minmax(0,17rem)_minmax(0,1fr)] xl:grid-cols-1 2xl:grid-cols-[minmax(0,17rem)_minmax(0,1fr)]" aria-labelledby="schedule-realtime-now">
                <div class="min-w-0">
                    {{-- It wraps: "Verbindung wird wiederhergestellt" does not fit beside the
                         heading in a 17rem column. --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-4">
                        <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['signal'] }}" /></svg>
                        </div>
                        <h2 id="schedule-realtime-now" class="text-sm font-medium text-gray-500 dark:text-gray-400">@{{ msg.rightNow }}</h2>
                        <span v-if="status === 'live'" class="ms-auto inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 shrink-0 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.live }}
                        </span>
                        {{-- After one missed answer. After a second the panel above says it, and
                             saying it twice is noise. --}}
                        <span v-else-if="status === 'reconnecting' && failures < 2" class="ms-auto inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="w-2 h-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>@{{ msg.reconnecting }}
                        </span>
                    </div>

                    <div v-if="empty">
                        <p class="dashboard-stat-value text-2xl font-bold text-gray-900 dark:text-white">@{{ msg.quiet }}</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.emptyTitle }}</p>
                    </div>

                    <div v-else class="grid xl:grid-cols-2 2xl:grid-cols-1">
                        <div class="grid grid-cols-2 self-start">
                            <div class="flex flex-col items-center min-w-0 px-2">
                                <span class="dashboard-stat-value text-3xl font-bold tabular-nums" :class="[p.overview.views_5m ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400', bumped.views ? 'schedule-realtime-bump' : '']">@{{ number(p.overview.views_5m) }}</span>
                                <span class="mt-0.5 text-xs leading-tight text-center text-gray-500 dark:text-gray-400">@{{ msg.views5 }}</span>
                            </div>
                            <div class="flex flex-col items-center min-w-0 px-2" style="border-inline-start: 1px solid var(--ap-hairline)">
                                <span class="dashboard-stat-value text-3xl font-bold tabular-nums" :class="[p.overview.visitors_now ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400', bumped.visitors ? 'schedule-realtime-bump' : '']">@{{ number(p.overview.visitors_now) }}</span>
                                <span class="mt-0.5 text-xs leading-tight text-center text-gray-500 dark:text-gray-400">@{{ msg.visitorsNow }}</span>
                            </div>
                        </div>
                        <div class="rt-ledger min-w-0">
                            <h3 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@{{ msg.last30 }}</h3>
                            <dl>
                                <div class="flex items-baseline justify-between gap-3 py-1.5">
                                    <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.pageViews }}</dt>
                                    <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.overview.views_30m) }}</dd>
                                </div>
                                <div v-if="p.overview.embed_views_30m" class="flex items-baseline justify-between gap-3 py-1.5">
                                    <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.embedViews }}</dt>
                                    <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.overview.embed_views_30m) }}</dd>
                                </div>
                                {{-- A checkout that was begun, and how many of those are paid by now:
                                     the nearest a page can come to "someone is buying". Only while
                                     there is one. --}}
                                <template v-if="p.checkouts.started">
                                    <div class="flex items-baseline justify-between gap-3 py-1.5">
                                        <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.checkoutsStarted }}</dt>
                                        <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.checkouts.started) }}</dd>
                                    </div>
                                    <div class="flex items-baseline justify-between gap-3 py-1.5">
                                        <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.checkoutsPaid }}</dt>
                                        <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.checkouts.paid) }}</dd>
                                    </div>
                                </template>
                                <div v-if="p.overview.last_view_ago !== null" class="flex items-baseline justify-between gap-3 py-1.5">
                                    <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.lastView }}</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">@{{ ago(p.overview.last_view_ago) }}</dd>
                                </div>
                            </dl>
                            <p v-if="p.truncated" class="pt-2 text-xs text-gray-500 dark:text-gray-400">@{{ msg.capped }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col min-w-0">
                    {{-- Nobody in half an hour: the room the chart would take says what the tab is
                         for, and with one schedule hands over the link that would change it. --}}
                    <div v-if="empty" class="flex flex-col items-start">
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md" style="text-wrap: pretty">@{{ msg.emptyBody }}</p>
                        <div v-if="shareUrl" class="mt-3 flex flex-wrap items-center gap-2 max-w-full">
                            <code class="max-w-full truncate rounded-lg px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300" style="background: var(--ap-tint-sunken)" dir="ltr">@{{ shareLabel }}</code>
                            <button type="button" @click="copyLink" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ copied ? msg.copied : msg.copyLink }}</button>
                        </div>
                    </div>
                    {{-- v-show, not v-if: the chart is drawn once on this canvas, and Vue would put
                         a NEW canvas back. --}}
                    <div v-show="!empty" class="flex flex-col flex-1 min-w-0">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.viewsPerMinute }}</h3>
                            {{-- The key to the green dot, only while the chart carries one. --}}
                            <span v-if="hasMarks" class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.saleMark }}
                            </span>
                        </div>
                        <div class="relative flex-1 min-h-[11rem]"><div class="absolute inset-0"><canvas ref="chart" role="img" :aria-label="msg.viewsPerMinute"></canvas></div></div>
                    </div>
                </div>
            </section>

            <div class="rt-side">
                @include('analytics._realtime-door')
                @include('analytics._realtime-activity')
            </div>

            {{-- Only when a page was opened or somebody is listed. A half hour of nothing but
                 views of an embedded calendar has its one figure in the ledger above, and five
                 cards saying "nothing" would bury it. --}}
            <div v-if="hasTraffic" class="rt-grid">
                <section class="rt-v ap-card rounded-xl overflow-hidden" aria-labelledby="schedule-realtime-visitors">
                    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3">
                        <h2 id="schedule-realtime-visitors" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.visitors }}</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" style="text-wrap: pretty">@{{ msg.note }}</p>
                    </div>

                    <div class="rt-row px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-sunken)">
                        <span>@{{ msg.rightNow }} &middot; @{{ number(p.visitors.now_total) }}</span>
                        <span class="hidden md:block">@{{ msg.colPage }}</span>
                        <span class="text-end">@{{ msg.colTime }}</span>
                    </div>
                    <p v-if="!p.visitors.now.length" class="px-4 sm:px-5 py-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nobodyNow }}</p>
                    <ul v-else class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        <li v-for="v in p.visitors.now" :key="v.id" class="rt-row px-4 sm:px-5 py-2.5" :class="fresh[v.id] ? 'schedule-realtime-fresh' : ''">
                            @include('analytics._realtime-visitor', ['now' => true, 'deviceIcons' => $deviceIcons])
                        </li>
                    </ul>
                    <p v-if="p.visitors.now_total > p.visitors.now.length" class="px-4 sm:px-5 py-2 text-xs text-gray-500 dark:text-gray-400">@{{ newest(p.visitors.now.length, p.visitors.now_total) }}</p>

                    <template v-if="p.visitors.earlier.length">
                        <div class="rt-row px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-sunken)">
                            <span class="col-span-full">@{{ msg.earlier }} &middot; @{{ number(p.visitors.earlier_total) }}</span>
                        </div>
                        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            <li v-for="v in p.visitors.earlier" :key="v.id" class="rt-row px-4 sm:px-5 py-2.5">
                                @include('analytics._realtime-visitor', ['now' => false, 'deviceIcons' => $deviceIcons])
                            </li>
                        </ul>
                        <p v-if="p.visitors.earlier_total > p.visitors.earlier.length" class="px-4 sm:px-5 py-2 text-xs text-gray-500 dark:text-gray-400">@{{ newest(p.visitors.earlier.length, p.visitors.earlier_total) }}</p>
                    </template>
                </section>

                {{-- Top pages, countries and devices each add up to the page views above them (the
                     rest is folded into one "Other" row). Sources count visits: only the page view a
                     visit began on carries a source that is this schedule's to know. --}}
                <div class="rt-right">
                    <div v-for="group in cardGroups" :key="group.id" :class="group.id">
                        <section v-for="card in group.cards" :key="card.id" class="ap-card rounded-xl flex flex-col" :aria-labelledby="'schedule-realtime-' + card.id">
                            <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                <h2 :id="'schedule-realtime-' + card.id" class="text-base font-semibold text-gray-900 dark:text-white">@{{ card.title }}</h2>
                                <span class="text-xs text-gray-500 dark:text-gray-400">@{{ card.unit }}</span>
                            </div>
                            <p v-if="!card.rows.length" class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nothingYet }}</p>
                            <ul v-else class="px-2 pb-3">
                                <li v-for="row in card.rows" :key="row.key" class="relative flex items-center gap-3 px-3 py-2 text-sm rounded-md">
                                    <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md transition-all duration-200" :class="row.other ? 'bg-gray-100 dark:bg-white/[0.04]' : 'bg-[var(--brand-blue-a10)]'" :style="{ width: barWidth(card, row) }"></span>
                                    <span class="relative flex items-center gap-2 min-w-0 flex-1">
                                        <span v-if="card.id === 'countries' && !row.other" class="iti__flag shrink-0" :class="'iti__' + row.key.toLowerCase()" aria-hidden="true"></span>
                                        <svg v-if="card.id === 'devices' && !row.other" class="w-4 h-4 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="deviceIcons[row.key] || deviceIcons.other" /></svg>
                                        <bdi class="truncate" :class="row.other ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100'">@{{ rowLabel(card, row) }}</bdi>
                                        <bdi v-if="row.sub" class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400">@{{ row.sub }}</bdi>
                                        {{-- Who is on this page at this moment, of the visitors who
                                             can be told apart. The figures beside the pages add up
                                             to "visitors now" above. --}}
                                        <span v-if="card.id === 'pages' && row.now" class="shrink-0 inline-flex items-center gap-1 whitespace-nowrap text-xs font-medium text-green-700 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ nowCount(row.now) }}
                                        </span>
                                    </span>
                                    <span class="relative tabular-nums text-gray-700 dark:text-gray-300">@{{ number(row.views) }}</span>
                                </li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style {!! nonce_attr() !!}>
        /* One row of the list of people, and the band above it: who, the page, how long. On a phone
           the page moves under the country, so a row is two columns. */
        .rt-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 1rem; align-items: center; }
        @media (min-width: 768px) { .rt-row { grid-template-columns: minmax(0, 11rem) minmax(0, 1fr) auto; } }
    </style>

    {{-- No script tags for Vue or Chart.js here: analytics/index loads Vue in its head and
         Chart.js at its foot, and the script below starts on DOMContentLoaded, after both. --}}
    @include('analytics._realtime-script', [
        'deviceIcons' => $deviceIcons,
        'payload' => $realtime['payload'],
        'activity' => $realtime['activity'],
        'activityIcons' => $activityIcons,
        'activityTones' => $activityTones,
    ])
