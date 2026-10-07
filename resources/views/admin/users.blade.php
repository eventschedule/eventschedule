<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'users'])

    {{-- Who signed up, how far they got and where they came from, in that order: the four
         headline numbers, the onboarding funnel with its three, then where sign-ups come from,
         then the people themselves. What each figure counts is the controller's and
         GrowthExportService's to say; nothing here works a number out again. --}}
    @php
        $isNexus = (bool) config('app.is_nexus');
        // Filtered here rather than in GrowthExportService, so the export keeps one shape.
        // Marketing visitors are counted only on the nexus, the one install with a marketing
        // site, so off it 'visited' is a bar that can never fill. The plan stages are about
        // buying a plan, which a plain selfhost has none of. Both sit at an end of the funnel
        // with no step ratio drawn across them, so dropping them changes no other bar.
        $funnelStages = array_values(array_filter($funnel['stages'], fn ($stage) => ! (
            (! $isNexus && $stage['key'] === 'visited')
            || (! config('app.hosted') && $stage['group'] === 'plan')
        )));
        $funnelStageLabel = fn ($key) => __('messages.funnel_stage_' . $key);
        $biggestDropToKey = $funnel['biggest_drop']['to_key'] ?? null;
        // Sign-up page views are tracked on every install, so off the nexus too a missing bar
        // means the window starts before tracking did, not that nothing is tracked.
        $trafficNote = null;
        if (! $funnel['traffic_tracked']) {
            $trafficNote = $funnel['tracking_started_at']
                ? __('messages.funnel_tracking_began', ['date' => \Illuminate\Support\Carbon::parse($funnel['tracking_started_at'])->format('M j, Y')])
                : __('messages.funnel_tracking_pending');
        }

        $icons = \App\Utils\RealtimeIcons::PATHS;
        $bolt = 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z';
        $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
        $caption = 'mt-0.5 text-xs text-gray-500 dark:text-gray-400 text-center';
        // A signed figure or a percentage is marked left-to-right, as on the dashboard: in a
        // right-to-left language the sign otherwise lands after the number.
        $ltr = fn ($text) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.e($text).'</span>');
        $signed = fn ($value, $unit = '') => $ltr(($value >= 0 ? '+' : '').$value.$unit);
        $tone = fn ($value) => $value >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400';

        $signupTotal = $emailUsers + $googleUsers + $hybridUsers;
        $share = fn ($part, $whole) => $ltr(($whole > 0 ? round(($part / $whole) * 100, 1) : 0).'%');
        // The colours the two sign-up charts draw each method in.
        $methods = [
            [__('messages.email'), $emailUsers, $emailUsersInPeriod, 'var(--brand-blue)'],
            [__('messages.google'), $googleUsers, $googleUsersInPeriod, '#EF4444'],
            [__('messages.hybrid'), $hybridUsers, $hybridUsersInPeriod, '#F59E0B'],
        ];
        $signupsInPeriod = $usersWithUtmInPeriod + $usersWithoutUtmInPeriod;
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_users_lead') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-admin-stat-tile :label="__('messages.total_users')" :icon="$icons['users']"
                tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($totalUsers) }}</span>
                    <span class="{{ $caption }}">{{ $signed(number_format($usersInPeriod)) }} @lang('messages.in_period')</span>
                </div>
                <x-slot:footer>
                    <span class="font-medium {{ $tone($usersChangePercent) }}">{{ $signed($usersChangePercent, '%') }}</span> @lang('messages.vs_previous_period')
                </x-slot:footer>
            </x-admin-stat-tile>

            {{-- The record ActiveDays keeps, as on the dashboard. "Estimate" while the window still
                 reaches back before counting began: those days are sign-ins and event edits only,
                 and run low. --}}
            <x-admin-stat-tile :label="__('messages.active_users_7_days')" :icon="$bolt"
                tint="bg-green-50 dark:bg-green-500/10" ink="text-green-500" glow="rgba(34, 197, 94, 0.15)">
                <span class="{{ $figure }}">{{ number_format($activeUsers7Days) }}</span>
                @if ($activeUsers7Estimate)
                    <x-slot:footer>@lang('messages.admin_dash_estimate')</x-slot:footer>
                @endif
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.active_users_30_days')" :icon="$bolt"
                tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)">
                <span class="{{ $figure }}">{{ number_format($activeUsers30Days) }}</span>
                @if ($activeUsers30Estimate)
                    <x-slot:footer>@lang('messages.admin_dash_estimate')</x-slot:footer>
                @endif
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.newsletter_subscribers')" :icon="$icons['email']"
                tint="bg-purple-50 dark:bg-purple-500/10" ink="text-purple-500" glow="rgba(168, 85, 247, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($newsletterSubscribed) }}</span>
                    <span class="{{ $caption }}">{{ number_format($newsletterUnsubscribed) }} @lang('messages.unsubscribed')</span>
                </div>
            </x-admin-stat-tile>
        </div>

        {{-- ===================== Onboarding Funnel ===================== --}}
        <div class="page-subhead">
            <h2>@lang('messages.funnel_onboarding_title')</h2>
            <p>{{ $isNexus ? __('messages.funnel_onboarding_subtitle') : __('messages.funnel_onboarding_subtitle_signup') }}</p>
        </div>

        {{-- North-star, biggest leak, overall. The overall one starts from marketing visitors,
             so it exists on the nexus only. --}}
        <div class="grid grid-cols-1 {{ $isNexus ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }} gap-4">
            {{-- North-star: Signup to first event, with period-over-period change --}}
            <x-admin-stat-tile :label="__('messages.funnel_north_star')" :icon="$icons['events']"
                tint="bg-green-50 dark:bg-green-500/10" ink="text-green-500" glow="rgba(16, 185, 129, 0.15)">
                <span class="{{ $figure }}">{{ $funnel['first_event_conv'] === null ? __('messages.funnel_na') : $ltr($funnel['first_event_conv'].'%') }}</span>
                <x-slot:footer>
                    @if($funnel['first_event_conv_change'] !== null)
                        <span class="font-medium {{ $tone($funnel['first_event_conv_change']) }}">{{ $signed($funnel['first_event_conv_change']) }} @lang('messages.funnel_pts')</span>
                        @lang('messages.vs_previous_period')
                    @else
                        {{ number_format($funnel['cohort_size']) }} @lang('messages.signups_total')
                    @endif
                </x-slot:footer>
            </x-admin-stat-tile>

            {{-- Biggest onboarding leak. Amber, as the same step is in the funnel below. --}}
            <x-admin-stat-tile :label="__('messages.funnel_biggest_leak')" :icon="$icons['funnel']"
                tint="bg-amber-50 dark:bg-amber-500/10" ink="text-amber-500" glow="rgba(245, 158, 11, 0.15)">
                @if($funnel['biggest_drop'])
                    <div class="flex flex-col items-center">
                        <span class="{{ $figure }} funnel-leak-figure">{{ $ltr('-'.$funnel['biggest_drop']['drop_pct'].'%') }}</span>
                        <span class="{{ $caption }}">{{ $funnelStageLabel($funnel['biggest_drop']['from_key']) }} <span class="insight-arrow" aria-hidden="true">&rarr;</span> {{ $funnelStageLabel($funnel['biggest_drop']['to_key']) }}</span>
                    </div>
                    <x-slot:footer>{{ number_format($funnel['biggest_drop']['lost']) }} @lang('messages.funnel_users_lost')</x-slot:footer>
                @else
                    <div class="flex flex-col items-center">
                        <span class="{{ $figure }}">{{ __('messages.funnel_na') }}</span>
                        <span class="{{ $caption }}">@lang('messages.funnel_no_leak')</span>
                    </div>
                @endif
            </x-admin-stat-tile>

            {{-- Overall visitor to first event --}}
            @if ($isNexus)
            <x-admin-stat-tile :label="__('messages.funnel_visitor_to_event')" :icon="$icons['wp']"
                tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ $funnel['visitor_to_event_conv'] === null ? __('messages.funnel_na') : $ltr($funnel['visitor_to_event_conv'].'%') }}</span>
                    @if($funnel['visitor_to_event_conv'] === null && $trafficNote)
                        <span class="{{ $caption }}">{{ $trafficNote }}</span>
                    @endif
                </div>
            </x-admin-stat-tile>
            @endif
        </div>

        {{-- The funnel beside what explains it: how its rates moved, and how people signed up. --}}
        <div class="page-grid2">
            <x-page-card :title="__('messages.funnel_stages_title')"
                :lead="$funnel['cohort_size'] > 0 ? __('messages.funnel_cohort_of', ['count' => number_format($funnel['cohort_size'])]) : __('messages.funnel_no_signups_period')">
                <div class="funnel">
                    @php $prevGroup = null; @endphp
                    @foreach($funnelStages as $i => $stage)
                        @php
                            // The email-code counters are anonymous daily counters too, so they
                            // draw like traffic rather than like the per-user cohort bars.
                            $isTraffic = in_array($stage['group'], ['traffic', 'email_code'], true);
                            $count = $stage['count'];
                            $label = $funnelStageLabel($stage['key']);
                            $ariaCount = $count === null ? __('messages.funnel_na') : number_format($count);
                            $barWidth = ($count !== null && $count > 0) ? max(2, $stage['width']) : 0;
                        @endphp

                        {{-- Drop connector (users lost from the previous stage) --}}
                        @if($stage['drop_count'] !== null && $stage['drop_count'] > 0)
                            @php $isBiggest = $biggestDropToKey === $stage['key']; @endphp
                            <div class="funnel-drop {{ $isBiggest ? 'is-biggest' : '' }}">
                                &darr; {{ number_format($stage['drop_count']) }} @lang('messages.funnel_lost')
                                @if($stage['step_conv'] !== null)<span dir="ltr">({{ round(max(0, 100 - $stage['step_conv']), 1) }}%)</span>@endif
                                @if($isBiggest) &middot; @lang('messages.funnel_biggest_leak') @endif
                            </div>
                        @endif

                        {{-- Group labels, driven off the group CHANGE rather than hardcoded
                             indices: the funnel gained ticket and plan stages, and index-based
                             headers silently filed them under "Signups this period" with the
                             cohort tooltip attached to a different population. Emitted AFTER the
                             connector above: a "N lost" line measures the transition INTO this
                             group, so it belongs above the heading, not under it. --}}
                        @if($stage['group'] !== $prevGroup)
                            <div class="funnel-group {{ $prevGroup === null ? '' : 'is-next' }}">@lang('messages.funnel_group_' . $stage['group'])</div>
                        @endif
                        @php $prevGroup = $stage['group']; @endphp

                        {{-- Stage: label + count above, bar (track + fill) below --}}
                        <div>
                            <div class="funnel-stage-head">
                                <span class="funnel-stage-name">
                                    {{ $label }}
                                    @if($stage['group'] === 'email_code')
                                        <span class="funnel-info" title="{{ __('messages.funnel_tooltip_email_code') }}">&#9432;</span>
                                    @elseif($isTraffic)
                                        <span class="funnel-info" title="{{ __('messages.funnel_tooltip_traffic') }}">&#9432;</span>
                                    @elseif($stage['key'] === 'account')
                                        <span class="funnel-info" title="{{ __('messages.funnel_tooltip_cohort') }}">&#9432;</span>
                                    @elseif(in_array($stage['key'], ['reached_schedule', 'reached_event'], true))
                                        <span class="funnel-info" title="{{ __('messages.funnel_tooltip_click_steps') }}">&#9432;</span>
                                    @endif
                                </span>
                                <span class="funnel-stage-count">
                                    {{ $ariaCount }}
                                    @if($stage['step_conv'] !== null)
                                        <small dir="ltr">({{ $stage['step_conv'] }}%)</small>
                                    @endif
                                </span>
                            </div>
                            <div class="funnel-track" role="img"
                                 aria-label="{{ $label }}: {{ $ariaCount }}{{ $stage['step_conv'] !== null ? ' (' . $stage['step_conv'] . '%)' : '' }}">
                                @if($count === null)
                                    <div class="funnel-none">{{ $stage['group'] === 'email_code' ? __('messages.funnel_na') : ($trafficNote ?? __('messages.funnel_na')) }}</div>
                                @else
                                    <div class="funnel-fill"
                                         style="width: {{ $barWidth }}%; {{ $isTraffic ? 'background: var(--brand-blue-light);' : 'background: linear-gradient(90deg, var(--brand-button-bg-light), var(--brand-button-bg));' }}"></div>
                                @endif
                            </div>

                            {{-- Verified attendee-intent signups (follow/ticket/...) excluded from the cohort --}}
                            @if($stage['key'] === 'account' && ! empty($funnel['excluded_intents']) && $funnel['excluded_intents']->isNotEmpty())
                                <p class="funnel-note">
                                    @lang('messages.funnel_excluded_intents'):
                                    {{ $funnel['excluded_intents']->map(fn ($total, $intent) => number_format($total) . ' ' . __('messages.signup_intent_' . $intent))->implode(', ') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-page-card>

            <div class="page-stack users-beside-funnel {{ count($funnelTrend['labels']) >= 2 ? 'has-chart' : '' }}">
                {{-- Conversion over time --}}
                <x-page-card :title="__('messages.funnel_over_time')">
                    @if(count($funnelTrend['labels']) >= 2)
                        <div class="h-64 users-over-time">
                            <canvas id="onboardingFunnelChart"></canvas>
                        </div>
                        <x-slot name="foot">
                            <p class="mt-3">@lang('messages.funnel_period_in_progress')</p>
                        </x-slot>
                    @else
                        <x-page-empty compact :title="__('messages.funnel_not_enough_history')" />
                    @endif
                </x-page-card>

                {{-- Signup Method in Period --}}
                <x-page-card flush :title="__('messages.signups_by_method')">
                    <x-slot name="aside"><span class="insight-when">@lang('messages.selected_period')</span></x-slot>
                    <div class="insight-pad">
                        <div class="h-48">
                            <canvas id="signupMethodTrendChart"></canvas>
                        </div>
                    </div>
                    <div class="page-stats insight-strip-top">
                        @foreach ($methods as [$label, $allTime, $inPeriod, $color])
                        <div class="page-stat">
                            <div class="page-stat-value">{{ number_format($inPeriod) }}</div>
                            <div class="page-stat-label"><span class="insight-dot" style="background: {{ $color }}"></span>{{ $label }}</div>
                        </div>
                        @endforeach
                    </div>
                </x-page-card>

                {{-- Signup Method Donut Chart --}}
                <x-page-card :title="__('messages.signup_method_breakdown')">
                    <x-slot name="aside"><span class="insight-when">@lang('messages.all_time')</span></x-slot>
                    <div class="insight-donut">
                        <div class="insight-donut-chart">
                            <canvas id="signupMethodChart"></canvas>
                        </div>
                        <dl class="page-kv">
                            @foreach ($methods as [$label, $allTime, $inPeriod, $color])
                            <div>
                                <dt><span class="insight-dot" style="background: {{ $color }}"></span>{{ $label }}</dt>
                                <dd>{{ number_format($allTime) }}<small>{{ $share($allTime, $signupTotal) }}</small></dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                    <x-slot name="foot">
                        <p class="mt-4">@lang('messages.hybrid') = @lang('messages.hybrid_description')</p>
                    </x-slot>
                </x-page-card>
            </div>
        </div>

        {{-- Where sign-ups came from --}}
        <div class="page-grid2">
            <x-page-card :title="__('messages.utm_attribution')">
                <x-slot name="aside"><span class="insight-when">@lang('messages.selected_period')</span></x-slot>
                @if($signupsInPeriod > 0)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ number_format($usersWithUtmInPeriod) }} @lang('messages.from_campaigns')
                        ({{ $share($usersWithUtmInPeriod, $signupsInPeriod) }}
                        @lang('messages.of') {{ number_format($signupsInPeriod) }} @lang('messages.signups_total'))
                    </p>

                    @if($utmSourcesInPeriod->count() > 0)
                        <div class="h-48">
                            <canvas id="utmSourcesChart"></canvas>
                        </div>
                    @else
                        <x-page-empty compact :title="__('messages.no_utm_data')" />
                    @endif
                @else
                    <x-page-empty compact :title="__('messages.no_utm_data')" />
                @endif
            </x-page-card>

            <x-page-card flush :title="__('messages.top_campaigns')">
                <x-slot name="aside"><span class="insight-when">@lang('messages.all_time')</span></x-slot>
                @if($topUtmCampaigns->count() > 0)
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.campaign')</th>
                                <th scope="col">@lang('messages.source')</th>
                                <th scope="col">@lang('messages.medium')</th>
                                <th scope="col" class="c-num">@lang('messages.users')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topUtmCampaigns as $campaign)
                                <tr>
                                    <td class="c-main c-strong c-wrap"><bdi>{{ $campaign->utm_campaign }}</bdi></td>
                                    <td class="c-wrap"><bdi>{{ $campaign->utm_source }}</bdi></td>
                                    <td class="c-quiet c-wrap"><bdi>{{ $campaign->utm_medium }}</bdi></td>
                                    <td class="c-num c-strong" data-label="{{ __('messages.users') }}">{{ number_format($campaign->count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-page-empty compact :title="__('messages.no_utm_data')" />
                @endif
            </x-page-card>
        </div>

        {{-- Top UTM Sources & Top Referrers (All Time). Either may have nothing to draw, and the
             one that is left takes the row. --}}
        @if($topUtmSources->count() > 0 || $topReferrerDomains->count() > 0)
        <div class="page-grid2">
            @if($topUtmSources->count() > 0)
                <x-page-card :title="__('messages.top_sources')">
                    <x-slot name="aside"><span class="insight-when">@lang('messages.all_time')</span></x-slot>
                    <div class="h-64">
                        <canvas id="utmTopSourcesChart"></canvas>
                    </div>
                </x-page-card>
            @endif

            @if($topReferrerDomains->count() > 0)
                <x-page-card :title="__('messages.top_referrers')">
                    <x-slot name="aside"><span class="insight-when">@lang('messages.all_time')</span></x-slot>
                    <div class="h-64">
                        <canvas id="topReferrersChart"></canvas>
                    </div>
                </x-page-card>
            @endif
        </div>
        @endif

        {{-- Onboarding progress (per-user work queue) --}}
        <x-page-card flush :title="__('messages.onboarding_progress_title')" :lead="__('messages.onboarding_progress_subtitle')">
            @if($onboardingProgress->count() > 0)
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.name')</th>
                            <th scope="col">@lang('messages.funnel_progress')</th>
                            <th scope="col">@lang('messages.funnel_furthest')</th>
                            <th scope="col">@lang('messages.funnel_signed_up')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($onboardingProgress as $u)
                            @php
                                $steps = [
                                    'account' => true,
                                    'reached_schedule' => $u->schedule_form_viewed_at !== null || $u->schedules_count > 0,
                                    'saved_schedule' => $u->schedules_count > 0,
                                    'reached_event' => $u->event_form_viewed_at !== null || $u->events_count > 0,
                                    'saved_event' => $u->events_count > 0,
                                ];
                                $furthestKey = 'account';
                                foreach ($steps as $stepKey => $reached) {
                                    if ($reached) { $furthestKey = $stepKey; }
                                }
                                $isStuck = $u->schedules_count > 0 && $u->events_count == 0;
                            @endphp
                            <tr class="{{ $isStuck ? 'is-flagged' : '' }}">
                                <td class="c-main c-strong c-wrap">
                                    <a href="mailto:{{ $u->email }}" class="event-link" title="{{ $u->email }}"><bdi>{{ $u->name ?: $u->email }}</bdi></a>
                                </td>
                                <td>
                                    <span class="onboard-steps" role="img" aria-label="{{ __('messages.funnel_stage_' . $furthestKey) }}">
                                        @foreach($steps as $stepKey => $reached)
                                            <i class="{{ $reached ? 'is-on' : '' }}"
                                               title="{{ __('messages.funnel_stage_' . $stepKey) }}{{ $reached ? '' : ' (' . __('messages.funnel_not_reached') . ')' }}"></i>
                                        @endforeach
                                    </span>
                                </td>
                                <td>
                                    {{ __('messages.funnel_stage_' . $furthestKey) }}
                                    @if($isStuck)
                                        <span class="event-status is-warn ms-2">@lang('messages.onboarding_stuck')</span>
                                    @endif
                                </td>
                                <td class="c-date">{{ $u->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($onboardingProgress->hasPages())
                <x-slot name="foot">{{ $onboardingProgress->links() }}</x-slot>
                @endif
            @else
                <x-page-empty compact :title="__('messages.no_data_available')" />
            @endif
        </x-page-card>

        {{-- Recent Signups. A sign-up with no campaign leaves those cells empty, so the ones that
             do carry one can be found; what the person came to do sits under their name, the
             medium under its source, and the content and the term under their campaign. Ten
             columns ran off the side of a laptop; these six fit one. --}}
        <x-page-card flush :title="__('messages.recent_signups')">
            @if($recentSignups->count() > 0)
                <div class="page-scroll">
                    <table class="page-table is-wide">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.name')</th>
                                <th scope="col">@lang('messages.source')</th>
                                <th scope="col">@lang('messages.campaign')</th>
                                <th scope="col">@lang('messages.referrer')</th>
                                <th scope="col">@lang('messages.landing_page')</th>
                                <th scope="col">@lang('messages.date')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSignups as $signup)
                                @php
                                    $campaignExtra = collect([$signup->utm_content, $signup->utm_term])->filter()->implode(' · ');
                                    $referrerHost = $signup->referrer_url ? parse_url($signup->referrer_url, PHP_URL_HOST) : null;
                                @endphp
                                <tr>
                                    <td class="c-main c-wrap">
                                        <span class="c-strong"><bdi>{{ $signup->name }}</bdi></span>
                                        @if($signup->signup_intent)
                                        <span class="c-sub"><span class="event-chip" title="{{ __('messages.signup_intent') }}">{{ __('messages.signup_intent_' . $signup->signup_intent) }}</span></span>
                                        @endif
                                    </td>
                                    <td data-label="{{ __('messages.source') }}">@if($signup->utm_source || $signup->utm_medium)<bdi>{{ $signup->utm_source }}</bdi><span class="c-sub"><bdi>{{ $signup->utm_medium }}</bdi></span>@endif</td>
                                    <td data-label="{{ __('messages.campaign') }}">@if($signup->utm_campaign || $campaignExtra)<bdi>{{ $signup->utm_campaign }}</bdi><span class="c-sub"><bdi>{{ $campaignExtra }}</bdi></span>@endif</td>
                                    <td class="c-quiet" data-label="{{ __('messages.referrer') }}" title="{{ $signup->referrer_url }}" dir="ltr">{{ $referrerHost }}</td>
                                    <td class="c-quiet c-mono" data-label="{{ __('messages.landing_page') }}" title="{{ $signup->landing_page }}" dir="ltr">{{ Str::limit($signup->landing_page ?? '', 30) }}</td>
                                    <td class="c-date">{{ $signup->created_at->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($recentSignups->hasPages())
                <x-slot name="foot">{{ $recentSignups->links() }}</x-slot>
                @endif
            @else
                <x-page-empty compact :title="__('messages.no_data_available')" />
            @endif
        </x-page-card>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
        <style {!! nonce_attr() !!}>
            /* The funnel's section opens inside the page's stack, which already keeps its blocks
               apart. */
            .page-stack > .page-subhead {
              margin: 0.75rem 0 -0.25rem;
            }
            /* The funnel: a stage's name and count, then its bar. A bar's width is the stage's
               share and nothing else; the track behind it is the whole. */
            .funnel {
              display: grid;
              gap: 0.375rem;
            }
            .funnel-group {
              display: flex;
              align-items: center;
              gap: 0.5rem;
              padding-bottom: 0.125rem;
              font-size: 0.6875rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .funnel-group.is-next {
              padding-top: 0.75rem;
            }
            .funnel-group::after {
              content: "";
              flex: 1;
              height: 1px;
              background: rgb(var(--ap-border));
            }
            .funnel-drop {
              font-size: 0.75rem;
              text-align: center;
              color: rgb(var(--ap-ink-3));
            }
            /* The one drop the page calls the biggest leak, in the colour its tile wears. */
            .funnel-drop.is-biggest,
            .funnel-leak-figure {
              font-weight: 600;
              color: #b45309 !important;
            }
            .dark .funnel-drop.is-biggest,
            .dark .funnel-leak-figure {
              color: #fbbf24 !important;
            }
            .funnel-leak-figure {
              font-weight: 700;
            }
            .funnel-stage-head {
              display: flex;
              align-items: baseline;
              justify-content: space-between;
              gap: 1rem;
              margin-bottom: 0.25rem;
              font-size: 0.875rem;
            }
            .funnel-stage-name {
              min-width: 0;
              font-weight: 500;
              color: rgb(var(--ap-ink));
            }
            .funnel-stage-count {
              flex: none;
              font-weight: 600;
              font-variant-numeric: tabular-nums;
              white-space: nowrap;
              color: rgb(var(--ap-ink));
            }
            .funnel-stage-count small {
              font-size: 0.8125rem;
              font-weight: 400;
              color: rgb(var(--ap-ink-3));
            }
            .funnel-info {
              color: rgb(var(--ap-ink-4));
              cursor: help;
            }
            .funnel-track {
              height: 1.5rem;
              overflow: hidden;
              border-radius: 0.5rem;
              background: var(--ap-tint-2);
            }
            .funnel-fill {
              height: 100%;
              border-radius: 0.5rem;
            }
            /* A stage nothing was counted for is not a stage at zero: it has no bar at all. */
            .funnel-none {
              display: flex;
              align-items: center;
              justify-content: center;
              height: 100%;
              border: 1px dashed rgb(var(--ap-border-strong));
              border-radius: 0.5rem;
              padding: 0 0.5rem;
              background: rgb(var(--ap-surface));
              font-size: 0.75rem;
              text-align: center;
              color: rgb(var(--ap-ink-3));
            }
            .funnel-note {
              margin: 0.25rem 0 0;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }

            /* Beside the funnel, which is the tall one: the first chart takes the height the
               other two cards leave, so no card ends in an empty half. Its canvas is taken out of
               the flow, or the chart's own height would be what the card is measured by. */
            @media (min-width: 1024px) {
              /* With no chart to grow yet, the three keep their own heights. */
              .users-beside-funnel {
                align-content: start;
              }
              .users-beside-funnel.has-chart {
                grid-template-rows: minmax(0, 1fr) auto auto;
                align-content: stretch;
              }
              .users-beside-funnel.has-chart > .page-card:first-child {
                display: flex;
                flex-direction: column;
              }
              .users-beside-funnel .users-over-time {
                position: relative;
                flex: 1 1 16rem;
                height: auto;
                min-height: 16rem;
              }
              .users-beside-funnel .users-over-time canvas {
                position: absolute;
                inset: 0;
              }
            }

            /* How far one person got: five steps, the ones reached filled. */
            .onboard-steps {
              display: inline-flex;
              gap: 0.25rem;
              vertical-align: middle;
            }
            .onboard-steps i {
              width: 1.5rem;
              height: 0.5rem;
              border-radius: 999px;
              background: var(--ap-tint-2);
            }
            .onboard-steps i.is-on {
              background: var(--brand-button-bg);
            }
            /* The amber rows the card's own line promises: a schedule, and no event yet. */
            .page-table tr.is-flagged {
              background: rgba(245, 158, 11, 0.08);
            }
        </style>
    </x-slot>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // The portal's own palette, read from its tokens. This used to ask the operating
            // system whether it was dark, so a light portal on a dark machine drew its charts
            // with black gridlines; and a hex could not follow the six palettes.
            const apStyle = getComputedStyle(document.documentElement);
            const apColor = (token, fallback) => {
                const value = apStyle.getPropertyValue(token).trim();
                return value ? 'rgb(' + value.split(/\s+/).join(', ') + ')' : fallback;
            };
            const isDarkMode = document.documentElement.classList.contains('dark');

            const textColor = apColor('--ap-ink-3', isDarkMode ? '#9CA3AF' : '#6B7280');
            const gridColor = apColor('--ap-border', isDarkMode ? '#2d2d30' : '#E5E7EB');
            const brandBlue = getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim();

            // Signup Method Donut Chart
            const signupMethodCtx = document.getElementById('signupMethodChart').getContext('2d');
            new Chart(signupMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: [@json(__('messages.email')), @json(__('messages.google')), @json(__('messages.hybrid'))],
                    datasets: [{
                        data: [{{ $emailUsers }}, {{ $googleUsers }}, {{ $hybridUsers }}],
                        backgroundColor: [brandBlue, '#EF4444', '#F59E0B'],
                        borderColor: apColor('--ap-surface', isDarkMode ? '#252526' : '#FFFFFF'),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Signup Method Trend Chart (Stacked Bar)
            const signupMethodTrendCtx = document.getElementById('signupMethodTrendChart').getContext('2d');
            new Chart(signupMethodTrendCtx, {
                type: 'bar',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [
                        {
                            label: @json(__('messages.email')),
                            data: @json($trendData['emailUsers']),
                            backgroundColor: brandBlue,
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.google')),
                            data: @json($trendData['googleUsers']),
                            backgroundColor: '#EF4444',
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.hybrid')),
                            data: @json($trendData['hybridUsers']),
                            backgroundColor: '#F59E0B',
                            stack: 'signups'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                boxWidth: 12,
                                padding: 8
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor,
                                precision: 0
                            }
                        }
                    }
                }
            });

            // UTM Sources Bar Chart (selected period)
            @if($utmSourcesInPeriod->count() > 0)
                const utmSourcesCtx = document.getElementById('utmSourcesChart').getContext('2d');
                new Chart(utmSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($utmSourcesInPeriod->pluck('utm_source')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($utmSourcesInPeriod->pluck('count')->toArray()),
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // UTM Top Sources Bar Chart (all time)
            @if($topUtmSources->count() > 0)
                const utmTopSourcesCtx = document.getElementById('utmTopSourcesChart').getContext('2d');
                new Chart(utmTopSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($topUtmSources->pluck('utm_source')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($topUtmSources->pluck('count')->toArray()),
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // Top Referrer Domains Bar Chart (all time)
            @if($topReferrerDomains->count() > 0)
                const topReferrersCtx = document.getElementById('topReferrersChart').getContext('2d');
                new Chart(topReferrersCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($topReferrerDomains->pluck('domain')->toArray()),
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: @json($topReferrerDomains->pluck('count')->toArray()),
                            backgroundColor: '#10B981'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // Onboarding funnel: conversion rates over time
            const onboardingCanvas = document.getElementById('onboardingFunnelChart');
            if (onboardingCanvas) {
                const funnelIsRtl = @json(is_rtl());
                const funnelLastIndex = @json($funnelTrend['last_index']);
                const dashLast = (ctx) => ctx.p1DataIndex === funnelLastIndex ? [6, 6] : undefined;
                const funnelDatasets = [];
                @if($funnelTrend['has_traffic'])
                    funnelDatasets.push({
                        label: @json(__('messages.funnel_visitor_to_signup')),
                        data: @json($funnelTrend['visitor_to_signup']),
                        borderColor: brandBlue,
                        backgroundColor: brandBlue,
                        spanGaps: true,
                        tension: 0.3,
                        segment: { borderDash: dashLast },
                    });
                @endif
                funnelDatasets.push({
                    label: @json(__('messages.funnel_signup_to_schedule')),
                    data: @json($funnelTrend['signup_to_schedule']),
                    borderColor: '#F59E0B',
                    backgroundColor: '#F59E0B',
                    spanGaps: true,
                    tension: 0.3,
                    segment: { borderDash: dashLast },
                });
                funnelDatasets.push({
                    label: @json(__('messages.funnel_signup_to_event')),
                    data: @json($funnelTrend['signup_to_event']),
                    borderColor: '#10B981',
                    backgroundColor: '#10B981',
                    spanGaps: true,
                    tension: 0.3,
                    segment: { borderDash: dashLast },
                });

                new Chart(onboardingCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($funnelTrend['labels']),
                        datasets: funnelDatasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: textColor, boxWidth: 12, padding: 8 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (c) => c.dataset.label + ': ' + (c.parsed.y === null ? 'n/a' : c.parsed.y + '%')
                                }
                            }
                        },
                        scales: {
                            x: {
                                reverse: funnelIsRtl,
                                grid: { color: gridColor },
                                ticks: { color: textColor }
                            },
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                grid: { color: gridColor },
                                ticks: { color: textColor, callback: (v) => v + '%' }
                            }
                        }
                    }
                });
            }
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
