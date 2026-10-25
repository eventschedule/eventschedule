<x-app-admin-layout>
    <style {!! nonce_attr() !!}>[v-cloak] { display: none; }</style>
    {{-- The country flags of the board and the visitors list. --}}
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">

    {{-- The admin's front page: the live view (Realtime until 2026-10, a page of its own) with
         the slower cards of the old dashboard under it. Nothing on it is read with the page:
         the live part polls from the moment it starts, the Revenue card in its corner is asked
         once the page is up, and each panel of cards below the first time it nears the screen.
         AdminDashboardController says what each request reads. --}}

    {{-- Ahead of the stack, not inside it: as the stack's second child the navigation took the
         stack's 1rem gap, and the tabs stood 16px lower here than on every other admin page. --}}
    {{-- No Refresh button: the page updates itself every ten seconds. --}}
    @include('admin.partials._navigation', ['active' => 'dashboard', 'refresh' => false])

    <div class="space-y-4">

        {{-- Everything waiting on an admin, from AdminAlertService, as chips. Only there when there
             is something to do, and outside the Vue mount: a row can name a schedule. --}}
        @if ($adminAlerts->isNotEmpty())
            <x-needs-attention :items="$adminAlerts" layout="chips" :limit="4" />
        @endif

        {{--
            Everything inside #realtime-app is a Vue template, and Vue's runtime compiler treats any
            mustache in a text node as code - including one inside a translated string, which an
            operator can override. So every visible string comes from the MSG object and Vue's own
            interpolation, and every name, email, title and schedule arrives in the JSON payload.
            Blade output appears only in attributes and in the static icon map.
        --}}
        @php
            $realtimeMsg = [
                'title' => __('messages.realtime'),
                'live' => __('messages.realtime_live'),
                'reconnecting' => __('messages.realtime_reconnecting'),
                'stopped' => __('messages.realtime_stopped'),
                'lastUpdated' => __('messages.realtime_last_updated'),
                'showAdmins' => __('messages.realtime_show_admins'),
                'clearFilters' => __('messages.realtime_clear_filters'),
                'removeFilter' => __('messages.realtime_remove_filter'),
                'filterSurface' => __('messages.realtime_filter_surface'),
                'filterPage' => __('messages.realtime_filter_page'),
                'filterSource' => __('messages.realtime_filter_source'),
                'filterCountry' => __('messages.realtime_filter_country'),
                'visitorsRightNow' => __('messages.realtime_visitors_right_now'),
                'ofTotal' => __('messages.realtime_of_total'),
                'last30Short' => __('messages.realtime_last_30_min'),
                'notFiltered' => __('messages.realtime_not_filtered'),
                'nMore' => __('messages.realtime_n_more'),
                'viewsPerMinute' => __('messages.realtime_views_per_minute'),
                'acceptedCookies' => __('messages.realtime_accepted_cookies'),
                'notAccepted' => __('messages.realtime_not_accepted'),
                'thisMinute' => __('messages.realtime_this_minute'),
                'axisNow' => __('messages.realtime_axis_now'),
                'visitors' => __('messages.realtime_visitors'),
                'all' => __('messages.realtime_all'),
                'signedIn' => __('messages.realtime_signed_in'),
                'anonymous' => __('messages.realtime_anonymous'),
                'rightNowGroup' => __('messages.realtime_right_now_group'),
                'earlierGroup' => __('messages.realtime_earlier_group'),
                'noOneNow' => __('messages.realtime_no_one_now'),
                'colVisitor' => __('messages.realtime_col_visitor'),
                'colPage' => __('messages.realtime_col_page'),
                'colSource' => __('messages.realtime_col_source'),
                'colActive' => __('messages.realtime_col_active'),
                'now' => __('messages.realtime_now'),
                'onPage' => __('messages.realtime_on_page'),
                'left' => __('messages.realtime_left'),
                'badgeNew' => __('messages.realtime_badge_new'),
                'badgeDemo' => __('messages.realtime_badge_demo'),
                'badgePro' => __('messages.realtime_badge_pro'),
                'badgeEnterprise' => __('messages.realtime_badge_enterprise'),
                'email' => __('messages.realtime_email'),
                'openChat' => __('messages.realtime_open_chat'),
                'openPublicPage' => __('messages.admin_open_public_page'),
                'openInAdmin' => __('messages.admin_open_in_admin'),
                'nobodyHere' => __('messages.realtime_nobody_here'),
                'trends' => __('messages.realtime_trends'),
                'byHour' => __('messages.realtime_by_hour'),
                'byDay' => __('messages.realtime_by_day'),
                'range' => ['24h' => __('messages.realtime_range_24h'), '7d' => __('messages.realtime_range_7d'), '30d' => __('messages.realtime_range_30d')],
                'before' => ['24h' => __('messages.realtime_trend_before_24h'), '7d' => __('messages.realtime_trend_before_7d'), '30d' => __('messages.realtime_trend_before_30d')],
                'noComparison' => __('messages.realtime_trend_no_comparison'),
                'trendSince' => __('messages.realtime_trend_since'),
                'trendCollecting' => __('messages.realtime_trend_collecting'),
                'tileSignups' => __('messages.realtime_signups'),
                'tileSchedules' => __('messages.schedules'),
                'tileEvents' => __('messages.events'),
                'tileOrders' => __('messages.realtime_orders'),
                'surface' => collect(\App\Services\RealtimeRollup::SURFACES)->mapWithKeys(fn ($surface) => [$surface => __('messages.realtime_surface_'.$surface)])->all(),
                'activity' => __('messages.realtime_activity'),
                'last24h' => __('messages.realtime_last_24_hours'),
                'activityEmpty' => __('messages.realtime_activity_empty'),
                'signups' => __('messages.realtime_signups'),
                'perHour' => __('messages.realtime_signups_per_hour'),
                'noSignups24' => __('messages.realtime_no_signups_24'),
                'lastSignup' => __('messages.realtime_last_signup'),
                'signedUpMark' => __('messages.realtime_signed_up_marker'),
                'sourceUnknown' => __('messages.realtime_source_unknown'),
                'colName' => __('messages.name'),
                'colProgress' => __('messages.funnel_progress'),
                'onSiteNow' => __('messages.realtime_on_site_now'),
                'seenRecently' => __('messages.realtime_seen_recently'),
                'showAll' => __('messages.realtime_show_all'),
                'showLess' => __('messages.realtime_show_less'),
                'topPages' => __('messages.realtime_top_pages'),
                'sources' => __('messages.realtime_sources'),
                'countries' => __('messages.realtime_countries'),
                'geoCredit' => __('messages.realtime_geo_credit'),
                'views' => __('messages.realtime_views'),
                'visits' => __('messages.realtime_visits'),
                'unitViews30' => __('messages.realtime_unit_views_30'),
                'unitVisits30' => __('messages.realtime_unit_visits_30'),
                'nNow' => __('messages.realtime_n_now'),
                'showOnly' => __('messages.realtime_show_only'),
                'trackingOffTitle' => __('messages.realtime_tracking_off_title'),
                'trackingOffBody' => __('messages.realtime_tracking_off_body'),
                'turnOn' => __('messages.realtime_turn_on'),
                'mentionPolicy' => __('messages.realtime_mention_policy'),
                'waitingTitle' => __('messages.realtime_waiting_title'),
                // Edge-cached marketing pages exist on the nexus only; elsewhere every page reports
                // as soon as it is opened.
                'waitingBody' => config('app.is_nexus') ? __('messages.realtime_waiting_body') : __('messages.realtime_waiting_body_app'),
                'noVisitors30' => __('messages.realtime_no_visitors_30'),
                'adminsHiddenTip' => __('messages.realtime_admins_hidden_tip'),
                'noMatch' => __('messages.realtime_no_match'),
                'lastVisitor' => __('messages.realtime_last_visitor'),
                'noVisitorsHour' => __('messages.realtime_no_visitors_hour'),
                'reauth' => __('messages.realtime_reauth'),
                'confirmPassword' => __('messages.realtime_confirm_password'),
                'reload' => __('messages.realtime_reload'),
                'truncated' => __('messages.realtime_truncated'),
                'schedules' => __('messages.realtime_schedules'),
                'nothingYet' => __('messages.realtime_nothing_yet'),
                'loading' => __('messages.loading'),
                'revenue' => __('messages.revenue'),
                'details' => __('messages.details'),
                'paying' => __('messages.admin_dash_paying'),
                'inTrial' => __('messages.admin_dash_in_trial'),
                'ifConvert' => __('messages.admin_dash_if_convert'),
                'atRisk' => __('messages.admin_dash_at_risk'),
                // :label is what fill() swaps for the amount.
                'ofMrr' => __('messages.admin_dash_of_mrr', ['amount' => ':label']),
                'cardFailed' => __('messages.admin_dash_card_failed'),
            ];
            $realtimeConfig = [
                'url' => route('admin.realtime.data'),
                'trendsUrl' => route('admin.realtime.trends'),
                // Hosted only, where there is billing: elsewhere the corner is the Now card's.
                'revenueUrl' => config('app.hosted') ? route('admin.dashboard.revenue').($sample ? '?sample=1' : '') : null,
                'pageUrl' => route('admin.dashboard'),
                'settingsUrl' => route('admin.settings').'#realtime',
                'legalUrl' => Route::has('admin.legal') ? route('admin.legal') : null,
                'isNexus' => (bool) config('app.is_nexus'),
                'locale' => app()->getLocale(),
                // The one clock for the whole page: Trends buckets its hours and days in it, and
                // every time the script prints is told in it too.
                'timezone' => \App\Services\RealtimeTrends::viewerZone(auth()->user()->timezone),
                // The range Trends opens on, from the address (?trend=), so a bookmark keeps it.
                'trend' => \App\Services\RealtimeTrends::range(request()->query('trend')),
                'rtl' => is_rtl(),
                'icons' => \App\Utils\RealtimeIcons::PATHS,
            ];
        @endphp

        <div id="realtime-app" v-cloak class="space-y-4">
            @include('admin.realtime._states')

            <template v-if="p.state !== 'off'">
                @include('admin.realtime._toolbar')
            </template>

            {{-- The Now card and, on hosted, Revenue in the corner. From xl the two take the three
                 columns of the Today grid below (realtime-top in _script), so the corner card stands
                 over the Activity rail, and the board of who is where runs the full width under
                 both. With tracking off, the card that says how to turn it on takes the Now card's
                 place. --}}
            <div class="realtime-top grid grid-cols-1 gap-4" @if (config('app.hosted')) data-corner @endif>
                @include('admin.realtime._off')
                <section v-else-if="p.state === 'loading'" class="realtime-now-skeleton ap-card rounded-xl p-4 sm:p-6" :aria-label="msg.loading" :aria-busy="status === 'reauth' || status === 'stopped' ? 'false' : 'true'" :class="status === 'reauth' || status === 'stopped' ? 'is-still' : ''">
                    <div class="flex items-center gap-4">
                        <span class="w-9 h-9 rounded-xl realtime-skeleton"></span>
                        <span class="w-16 h-9 rounded-lg realtime-skeleton"></span>
                        <span class="w-48 h-4 rounded realtime-skeleton"></span>
                    </div>
                    <div class="mt-5 h-24 rounded-xl realtime-skeleton"></div>
                </section>
                <template v-else>
                    @include('admin.realtime._now')
                </template>

                @if (config('app.hosted'))
                    @include('admin.realtime._revenue')
                @endif
            </div>

            <template v-if="p.state === 'ok' || p.state === 'waiting'">
                @include('admin.realtime._board')

                {{-- Activity first in the single column below xl; at xl Sign-ups takes two columns
                     and Activity is the rail beside it (realtime-today in _script). --}}
                <div class="realtime-today grid grid-cols-1 gap-4" :class="activityAll ? 'is-open' : ''">
                    @include('admin.realtime._activity')
                    @include('admin.realtime._signups')
                </div>

                @include('admin.realtime._trends')

                <div v-if="hasAnyone" class="realtime-breakdowns grid gap-4">
                    @include('admin.realtime._breakdown')
                </div>

                @include('admin.realtime._visitors')
            </template>
        </div>

        {{-- The old dashboard's cards, three panels fetched as they near the screen (the cards'
             script). Outside the Vue mount on purpose: they print schedule, event and people
             names as text, and everything inside #realtime-app is a Vue template. Each
             placeholder keeps roughly its panel's height, so nothing jumps as they land. --}}
        <div id="admin-dashboard" class="space-y-4"
             data-panel-url="{{ route('admin.dashboard.panel', ['panel' => '__panel__']) }}"
             data-failed="{{ __('messages.admin_dash_card_failed') }}"
             data-retry="{{ __('messages.retry') }}"
             data-reauth="{{ __('messages.realtime_reauth') }}"
             data-reauth-action="{{ __('messages.realtime_confirm_password') }}"
             @if ($sample) data-sample @endif>
            @foreach (['recent' => '36rem', 'growth' => '52rem', 'platform' => '16rem'] as $panel => $height)
                <section data-dash-panel="{{ $panel }}" aria-busy="true" aria-label="{{ __('messages.loading') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-4" style="min-height: {{ $height }}">
                    <div class="ap-card rounded-xl realtime-skeleton-card"></div>
                    <div class="ap-card rounded-xl realtime-skeleton-card hidden lg:block"></div>
                </section>
            @endforeach
        </div>
    </div>

    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    @include('admin.realtime._script')
    @include('admin.dashboard._cards-script')
</x-app-admin-layout>
