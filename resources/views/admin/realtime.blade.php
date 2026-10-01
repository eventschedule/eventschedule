<x-app-admin-layout>
    <style {!! nonce_attr() !!}>[v-cloak] { display: none; }</style>
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'realtime'])

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
                'last30' => __('messages.realtime_last_30_minutes'),
                'viewsPerMinute' => __('messages.realtime_views_per_minute'),
                'acceptedCookies' => __('messages.realtime_accepted_cookies'),
                'notIdentified' => __('messages.realtime_not_identified'),
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
                'activity' => __('messages.realtime_activity'),
                'last24h' => __('messages.realtime_last_24_hours'),
                'activityEmpty' => __('messages.realtime_activity_empty'),
                'onSiteNow' => __('messages.realtime_on_site_now'),
                'seenRecently' => __('messages.realtime_seen_recently'),
                'showAll' => __('messages.realtime_show_all'),
                'showLess' => __('messages.realtime_show_less'),
                'topPages' => __('messages.realtime_top_pages'),
                'sources' => __('messages.realtime_sources'),
                'countries' => __('messages.realtime_countries'),
                'surfaces' => __('messages.realtime_surfaces'),
                'geoCredit' => __('messages.realtime_geo_credit'),
                'views' => __('messages.realtime_views'),
                'visits' => __('messages.realtime_visits'),
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
            ];
            $realtimeConfig = [
                'url' => route('admin.realtime.data'),
                'pageUrl' => route('admin.realtime'),
                'settingsUrl' => route('admin.settings').'#realtime',
                'legalUrl' => Route::has('admin.legal') ? route('admin.legal') : null,
                'isNexus' => (bool) config('app.is_nexus'),
                'locale' => app()->getLocale(),
                'rtl' => is_rtl(),
                'icons' => \App\Utils\RealtimeIcons::PATHS,
            ];
        @endphp

        <div id="realtime-app" v-cloak class="space-y-4">
            @include('admin.realtime._states')

            <template v-if="p.state !== 'off'">
                @include('admin.realtime._toolbar')

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                    @include('admin.realtime._overview')
                    @include('admin.realtime._activity')
                </div>

                @include('admin.realtime._visitors')

                <div v-if="hasAnyone" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @include('admin.realtime._breakdown')
                </div>
            </template>
        </div>
    </div>

    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    @include('admin.realtime._script')
</x-app-admin-layout>
