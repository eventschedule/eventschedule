<x-app-admin-layout>
    {{-- A schedule owner's live view of their own guest pages (RealtimeController, ScheduleRealtime).

         Its own page and its own script, sharing no markup with /admin/realtime: that page is about
         people across the whole install, and nothing of it belongs in a document an organizer can
         open. tests/Feature/ScheduleRealtimeTest.php reads this page's HTML for the admin page's
         strings.

         Everything inside #schedule-realtime is a Vue template, and Vue's runtime compiler treats a
         mustache in a text node as code, including one inside a translated string an operator can
         override, and including an event's name. So every visible string comes from the MSG object
         and every name arrives in the JSON payload, both rendered by Vue's own interpolation. Blade
         output appears only in attributes and in static icon paths. --}}
    <style {!! nonce_attr() !!}>
        [v-cloak] { display: none; }

        @keyframes schedule-realtime-fresh { from { background-color: var(--brand-blue-a10); } to { background-color: transparent; } }
        .schedule-realtime-fresh { animation: schedule-realtime-fresh 2s ease-out 1; }
        @keyframes schedule-realtime-bump { 0% { transform: scale(1); } 35% { transform: scale(1.08); } 100% { transform: scale(1); } }
        .schedule-realtime-bump { animation: schedule-realtime-bump 0.5s ease-out 1; display: inline-block; }
        @media (prefers-reduced-motion: reduce) { .schedule-realtime-fresh, .schedule-realtime-bump { animation: none; } }

        /* The list of people is three short columns, so it shares its row with the breakdowns rather
           than stretching across the pane. Below 1024 there is no room beside it: the list is full
           width and the breakdowns go two up. From 1024 it takes seven twelfths beside two of them,
           with the other two as a pair underneath. On a wide screen it takes half and the four sit
           two by two. `.rt-right` is display: contents until then, so its two groups are the grid's
           own children and can be placed apart. */
        .rt-grid { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
        .rt-right { display: contents; }
        .rt-ps, .rt-cd { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1fr); min-width: 0; }
        @media (min-width: 768px) {
            .rt-ps, .rt-cd { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .rt-grid { grid-template-columns: minmax(0, 7fr) minmax(0, 5fr); }
            .rt-v { grid-column: 1; grid-row: 1; }
            .rt-ps { grid-column: 2; grid-row: 1; grid-template-columns: minmax(0, 1fr); }
            .rt-cd { grid-column: 1 / -1; grid-row: 2; }
        }
        @media (min-width: 1536px) {
            .rt-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
            .rt-right { display: flex; flex-direction: column; gap: 1rem; grid-column: 2; grid-row: 1; min-width: 0; }
            .rt-ps, .rt-cd { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-column: auto; grid-row: auto; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">

    @php
        $scheduleRealtimeMsg = [
            'title' => __('messages.realtime'),
            'allSchedules' => __('messages.all_schedules'),
            'live' => __('messages.realtime_live'),
            'updates' => __('messages.realtime_owner_updates'),
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
            'device' => [
                'mobile' => __('messages.mobile'),
                'desktop' => __('messages.desktop'),
                'tablet' => __('messages.tablet'),
                'other' => __('messages.other'),
            ],
        ];
        $scheduleRealtimeConfig = [
            'url' => route('realtime.data'),
            'pageUrl' => route('realtime'),
            'locale' => app()->getLocale(),
            'rtl' => is_rtl(),
            'schedules' => $schedules,
            'selected' => $selected,
            // The link an owner with one schedule is offered when nobody has come by. With several
            // there is no one page to send people to, and the empty state says so without one.
            'shareUrl' => count($schedules) === 1 ? auth()->user()->manageableRoles()->first()?->getGuestUrl() : null,
        ];
        $deviceIcons = [
            'mobile' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
            'desktop' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
            'tablet' => 'M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z',
            'other' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
        ];
    @endphp

    <div id="schedule-realtime" v-cloak class="space-y-4">
        {{-- Title, the schedule it is narrowed to, and whether the page is still listening. --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">@{{ msg.title }}</h1>
                <select v-if="schedules.length > 1" v-model="selected" @change="changeSchedule" :aria-label="msg.allSchedules"
                        class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm font-semibold py-1.5 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <option value="">@{{ msg.allSchedules }}</option>
                    <option v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">@{{ schedule.name }}</option>
                </select>
                <span v-else-if="schedules.length === 1" class="text-sm text-gray-500 dark:text-gray-400">@{{ schedules[0].name }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300" role="status" aria-live="polite">
                <template v-if="status === 'live'">
                    <span class="w-2 h-2 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.live }}
                    <span class="font-normal text-gray-500 dark:text-gray-400">@{{ msg.updates }}</span>
                </template>
                <template v-else-if="status === 'reconnecting'">
                    <span class="w-2 h-2 rounded-full bg-amber-500" aria-hidden="true"></span>@{{ msg.reconnecting }}
                </template>
                <template v-else>
                    <span class="w-2 h-2 rounded-full bg-gray-400" aria-hidden="true"></span>@{{ msg.stopped }}
                    <button type="button" @click="reload" class="font-semibold text-[var(--brand-blue)] hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ msg.reload }}</button>
                </template>
            </div>
        </div>

        {{-- Two figures, because they answer two questions. Page views count everybody; a visitor
             can only be told from another once they have accepted cookies. Under them a ledger:
             one label and one number to a row, nothing to add up in a sentence. --}}
        <section class="ap-card rounded-xl p-4 sm:p-6 grid md:grid-cols-[minmax(0,17rem)_minmax(0,1fr)] gap-6">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['signal'] }}" /></svg>
                    </div>
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">@{{ msg.rightNow }}</h2>
                </div>
                <div class="grid grid-cols-2">
                    <div class="flex flex-col items-center min-w-0 px-2">
                        <span class="dashboard-stat-value text-3xl font-bold tabular-nums" :class="[p.overview.views_5m ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400', bumped.views ? 'schedule-realtime-bump' : '']">@{{ number(p.overview.views_5m) }}</span>
                        <span class="mt-0.5 text-xs leading-tight text-center text-gray-500 dark:text-gray-400">@{{ msg.views5 }}</span>
                    </div>
                    <div class="flex flex-col items-center min-w-0 px-2" style="border-inline-start: 1px solid var(--ap-hairline)">
                        <span class="dashboard-stat-value text-3xl font-bold tabular-nums" :class="[p.overview.visitors_now ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400', bumped.visitors ? 'schedule-realtime-bump' : '']">@{{ number(p.overview.visitors_now) }}</span>
                        <span class="mt-0.5 text-xs leading-tight text-center text-gray-500 dark:text-gray-400">@{{ msg.visitorsNow }}</span>
                    </div>
                </div>
                <div class="mt-5 pt-2" style="border-top: 1px solid var(--ap-hairline)">
                    <h3 class="pt-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@{{ msg.last30 }}</h3>
                    <dl>
                        <div class="flex items-baseline justify-between gap-3 py-1.5">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.pageViews }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.overview.views_30m) }}</dd>
                        </div>
                        <div v-if="p.overview.embed_views_30m" class="flex items-baseline justify-between gap-3 py-1.5">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.embedViews }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(p.overview.embed_views_30m) }}</dd>
                        </div>
                        <div v-if="p.overview.last_view_ago !== null" class="flex items-baseline justify-between gap-3 py-1.5">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.lastView }}</dt>
                            <dd class="text-sm font-semibold text-gray-900 dark:text-white">@{{ ago(p.overview.last_view_ago) }}</dd>
                        </div>
                    </dl>
                    <p v-if="p.truncated" class="pt-2 text-xs text-gray-500 dark:text-gray-400">@{{ msg.capped }}</p>
                </div>
            </div>
            <div class="flex flex-col min-w-0">
                <h3 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.viewsPerMinute }}</h3>
                <div class="relative flex-1 min-h-[11rem]"><div class="absolute inset-0"><canvas ref="chart" role="img" :aria-label="msg.viewsPerMinute"></canvas></div></div>
            </div>
        </section>

        <div class="rt-grid">
            <section class="rt-v ap-card rounded-xl overflow-hidden" aria-labelledby="schedule-realtime-visitors">
                <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3">
                    <h2 id="schedule-realtime-visitors" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.visitors }}</h2>
                    <p v-if="!empty" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" style="text-wrap: pretty">@{{ msg.note }}</p>
                </div>

                {{-- Nothing in half an hour: say what the page is for, and with one schedule hand
                     over the link that would change it. --}}
                <div v-if="empty" class="px-5 py-10 flex flex-col items-center text-center" style="border-top: 1px solid var(--ap-hairline)">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['users'] }}" /></svg>
                    <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.emptyTitle }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm">@{{ msg.emptyBody }}</p>
                    <div v-if="shareUrl" class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <code class="max-w-full truncate rounded-lg px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300" style="background: var(--ap-tint-sunken)" dir="ltr">@{{ shareLabel }}</code>
                        <button type="button" @click="copyLink" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ copied ? msg.copied : msg.copyLink }}</button>
                    </div>
                </div>

                <template v-else>
                    <div class="rt-row px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-sunken)">
                        <span>@{{ msg.rightNow }} &middot; @{{ number(p.visitors.now_total) }}</span>
                        <span class="hidden md:block">@{{ msg.colPage }}</span>
                        <span class="text-end">@{{ msg.colTime }}</span>
                    </div>
                    <p v-if="!p.visitors.now.length" class="px-4 sm:px-5 py-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nobodyNow }}</p>
                    <ul v-else class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        <li v-for="v in p.visitors.now" :key="v.id" class="rt-row px-4 sm:px-5 py-2.5" :class="fresh[v.id] ? 'schedule-realtime-fresh' : ''">
                            @include('realtime._visitor', ['now' => true, 'deviceIcons' => $deviceIcons])
                        </li>
                    </ul>
                    <p v-if="p.visitors.now_total > p.visitors.now.length" class="px-4 sm:px-5 py-2 text-xs text-gray-500 dark:text-gray-400">@{{ newest(p.visitors.now.length, p.visitors.now_total) }}</p>

                    <template v-if="p.visitors.earlier.length">
                        <div class="rt-row px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-sunken)">
                            <span class="col-span-full">@{{ msg.earlier }} &middot; @{{ number(p.visitors.earlier_total) }}</span>
                        </div>
                        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            <li v-for="v in p.visitors.earlier" :key="v.id" class="rt-row px-4 sm:px-5 py-2.5">
                                @include('realtime._visitor', ['now' => false, 'deviceIcons' => $deviceIcons])
                            </li>
                        </ul>
                        <p v-if="p.visitors.earlier_total > p.visitors.earlier.length" class="px-4 sm:px-5 py-2 text-xs text-gray-500 dark:text-gray-400">@{{ newest(p.visitors.earlier.length, p.visitors.earlier_total) }}</p>
                    </template>
                </template>
            </section>

            {{-- Top pages, countries and devices each add up to the page views above them (the
                 rest is folded into one "Other" row). Sources count visits: only the page view a
                 visit began on carries a source that is this schedule's to know. --}}
            <div v-if="!empty" class="rt-right">
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
                                    <span class="truncate" :class="row.other ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-gray-100'">@{{ rowLabel(card, row) }}</span>
                                    <span v-if="row.sub" class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400">@{{ row.sub }}</span>
                                </span>
                                <span class="relative tabular-nums text-gray-700 dark:text-gray-300">@{{ number(row.views) }}</span>
                            </li>
                        </ul>
                    </section>
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

    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    @include('realtime._script', ['deviceIcons' => $deviceIcons])
</x-app-admin-layout>
