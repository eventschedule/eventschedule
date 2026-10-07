<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'growth'])

    @php
        $activation = $data['activation'];
        $pressure = $data['free_pressure'];
        $money = $data['monetization'];
        $accounts = max(1, $activation['accounts']);
        // The onboarding funnel itself lives on /admin/users; this page starts where
        // that one stops, so the two are not maintained twice.
        $pct = fn ($n) => round($n / $accounts * 100, 1);
        // A percentage is marked left-to-right, as on the dashboard: in a right-to-left language
        // the sign otherwise lands on the wrong side of the number.
        $ltr = fn ($text) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.e($text).'</span>');
        $share = fn ($part, $whole) => $ltr(($whole > 0 ? round($part / $whole * 100, 1) : 0).'%');

        $icons = \App\Utils\RealtimeIcons::PATHS;
        $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
        $caption = 'mt-0.5 text-xs text-gray-500 dark:text-gray-400 text-center';
    @endphp

    {{-- The full payload is pulled with `php artisan app:pull-growth` (docs/GROWTH_DATA.md), not downloaded here. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.growth_description') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">
        {{-- Activation: how far the whole verified base actually gets --}}
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-admin-stat-tile :label="__('messages.growth_signups')" :icon="$icons['signup']"
                tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ number_format($activation['accounts']) }}</span>
                </div>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.funnel_stage_reached_schedule')" :icon="$icons['direct']"
                tint="bg-purple-50 dark:bg-purple-500/10" ink="text-purple-500" glow="rgba(168, 85, 247, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ $ltr($pct($activation['reached_schedule_form']).'%') }}</span>
                    <span class="{{ $caption }}">{{ number_format($activation['reached_schedule_form']) }}</span>
                </div>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.growth_saved_schedule')" :icon="$icons['schedule']"
                tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ $ltr($pct($activation['saved_schedule']).'%') }}</span>
                    <span class="{{ $caption }}">{{ number_format($activation['saved_schedule']) }}</span>
                </div>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.growth_saved_event')" :icon="$icons['events']"
                tint="bg-amber-50 dark:bg-amber-500/10" ink="text-amber-500" glow="rgba(245, 158, 11, 0.15)">
                <div class="flex flex-col items-center">
                    <span class="{{ $figure }}">{{ $ltr($pct($activation['saved_event']).'%') }}</span>
                    <span class="{{ $caption }}">{{ number_format($activation['saved_event']) }}</span>
                </div>
            </x-admin-stat-tile>
        </div>

        <div class="page-grid2">
            {{-- Free schedules by peak monthly paid tickets. Paid selling is Pro/Enterprise now,
                 so this reads as a conversion list: who has sold before and is sitting on free.
                 Amber is a schedule that sold; the bucket of nothing is grey. --}}
            <x-page-card :title="__('messages.growth_free_pressure')" :lead="__('messages.growth_free_pressure_help')">
                @php
                    $bucketMax = max(1, max($pressure['peak_month_paid_tickets']));
                @endphp
                <div class="insight-bars">
                    @foreach ($pressure['peak_month_paid_tickets'] as $bucket => $count)
                        {{-- Cast first: PHP turns the array key '0' into the integer 0, so compared
                             as it came the bucket of nothing was drawn as one that had sold. --}}
                        @php $sold = (string) $bucket !== '0'; @endphp
                        <div>
                            <div class="insight-bar-head">
                                <span class="insight-bar-name" dir="ltr">{{ $bucket }}</span>
                                <span class="insight-bar-figure"><b>{{ number_format($count) }}</b></span>
                            </div>
                            <div class="insight-bar"><i class="{{ $sold ? 'is-warn' : 'is-quiet' }}" style="width: {{ $count > 0 ? max(2, round($count / $bucketMax * 100, 1)) : 0 }}%"></i></div>
                        </div>
                    @endforeach
                </div>
                <x-slot name="foot">
                    <p class="mt-4">{{ __('messages.growth_free_pressure_foot', ['sold' => number_format($pressure['ever_sold_paid']), 'total' => number_format($pressure['free_schedules'])]) }}</p>
                </x-slot>
            </x-page-card>

            {{-- Monetization --}}
            <x-page-card :title="__('messages.revenue')">
                <dl class="page-kv">
                    @foreach ([
                        'free' => __('messages.free'),
                        'pro' => __('messages.pro'),
                        'enterprise' => __('messages.enterprise'),
                    ] as $tier => $label)
                        <div>
                            <dt>{{ $label }}</dt>
                            <dd>{{ number_format($money['plan_counts'][$tier] ?? 0) }}</dd>
                        </div>
                    @endforeach
                </dl>
                <dl class="page-kv">
                    <div>
                        <dt>MRR</dt>
                        <dd>{{ plan_price($money['mrr']) }}</dd>
                    </div>
                    <div>
                        <dt>ARR</dt>
                        <dd>{{ plan_price($money['arr']) }}</dd>
                    </div>
                    <div>
                        <dt>ARPU</dt>
                        <dd>{{ $money['arpu'] === null ? __('messages.funnel_na') : plan_price($money['arpu']) }}</dd>
                    </div>
                    {{-- Left out of MRR, ARR and ARPU above: a trial has not paid anything yet. --}}
                    <div>
                        <dt>@lang('messages.trialing_subscriptions')</dt>
                        <dd>{{ number_format($money['trialing_subscriptions']) }}</dd>
                    </div>
                </dl>
            </x-page-card>
        </div>

        <div class="page-grid2">
            {{-- The two scheduled owner emails, so a bad first day after a deploy is visible here
                 rather than only in a query against schedule_nudges and owner_digests. --}}
            <x-page-card flush :title="__('messages.growth_nudges')" :lead="__('messages.growth_nudges_help')">
                @if (empty($data['nudges']))
                    <div class="insight-pad">
                        <x-page-notice tone="warn">{{ __('messages.growth_nudges_never_run') }}</x-page-notice>
                    </div>
                @else
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.growth_nudge_key')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_nudge_total')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_nudge_last_7_days')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_nudge_last_sent')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['nudges'] as $key => $row)
                                <tr>
                                    <td class="c-main c-mono" dir="ltr">{{ $key }}</td>
                                    <td class="c-num c-strong" data-label="{{ __('messages.growth_nudge_total') }}">{{ number_format($row['total']) }}</td>
                                    <td class="c-num" data-label="{{ __('messages.growth_nudge_last_7_days') }}">{{ number_format($row['last_7_days']) }}</td>
                                    <td class="c-num c-quiet">{{ $row['last_sent_at'] ? \Carbon\Carbon::parse($row['last_sent_at'])->diffForHumans() : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- The other scheduled owner email, so both can be watched in one place. --}}
                <x-slot name="foot">
                    <p class="font-semibold text-gray-900 dark:text-white">@lang('messages.growth_digests')</p>
                    @php $digests = array_slice($data['owner_digests'] ?? [], 0, 2, true); @endphp
                    @if ($digests)
                    <dl class="page-kv mt-2">
                        @foreach ($digests as $week => $row)
                            <div>
                                <dt class="font-mono text-xs" dir="ltr">{{ $week }}</dt>
                                <dd>{{ trans_choice('messages.growth_digests_owners', $row['owners'], ['count' => number_format($row['owners'])]) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    @else
                    <p class="mt-1">@lang('messages.growth_digests_none')</p>
                    @endif
                </x-slot>
            </x-page-card>

            {{-- Churn and the selling trial: the two ends of the plan, each new on 2026-09-28. --}}
            <x-page-card :title="__('messages.growth_churn_and_trials')">
                @php
                    $churn = $data['churn'];
                    $trials = $money['ticket_trials'];
                @endphp
                <dl class="page-kv">
                    <div>
                        <dt>@lang('messages.growth_cancelled')</dt>
                        <dd>{{ number_format($churn['cancelled']) }}</dd>
                    </div>
                    @foreach (collect($churn['by_reason'])->take(4) as $reason => $count)
                        <div class="is-sub">
                            <dt>{{ $reason === 'none' ? __('messages.growth_no_reason') : __('messages.cancel_reason_'.$reason) }}</dt>
                            <dd>{{ number_format($count) }}</dd>
                        </div>
                    @endforeach
                    <div>
                        <dt>@lang('messages.growth_resumed')</dt>
                        <dd>{{ number_format($churn['resumed']) }}</dd>
                    </div>
                </dl>
                <dl class="page-kv">
                    @foreach (['started', 'running', 'sold_during', 'converted', 'expired_unconverted'] as $key)
                        <div>
                            <dt>@lang('messages.growth_trials_'.$key)</dt>
                            <dd>{{ number_format($trials[$key]) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </x-page-card>
        </div>

        {{-- Acquisition: which landing pages produce signups that go on to SELL. The two ticket
             columns are the point of this table: activation stopping at "saved an event" ranked
             pages by an outcome that does not predict revenue, and the marketing site has ~156
             of them, so 12 rows could never rank it either. --}}
        <x-page-card flush :title="__('messages.growth_acquisition')" :lead="__('messages.growth_acquisition_lead')">
            @php $landing = array_slice($data['acquisition']['by_landing_path'], 0, 25); @endphp
            @if (empty($landing))
                <x-page-empty compact :title="__('messages.growth_no_data')" />
            @else
                <div class="page-scroll">
                    <table class="page-table is-wide">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.landing_page')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_signups')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_saved_schedule')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_saved_event')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_saved_ticket')</th>
                                <th scope="col" class="c-num">@lang('messages.growth_saved_paid_ticket')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($landing as $row)
                                <tr>
                                    <td class="c-main c-mono c-wrap"><bdi dir="ltr">{{ $row['key'] }}</bdi></td>
                                    <td class="c-num c-strong" data-label="{{ __('messages.growth_signups') }}">{{ number_format($row['signups']) }}</td>
                                    <td class="c-num" data-label="{{ __('messages.growth_saved_schedule') }}">{{ $share($row['saved_schedule'], $row['signups']) }}</td>
                                    <td class="c-num" data-label="{{ __('messages.growth_saved_event') }}">{{ $share($row['saved_event'], $row['signups']) }}</td>
                                    <td class="c-num" data-label="{{ __('messages.growth_saved_ticket') }}">{{ $share($row['saved_ticket'], $row['signups']) }}</td>
                                    <td class="c-num" data-label="{{ __('messages.growth_saved_paid_ticket') }}">{{ $share($row['saved_paid_ticket'], $row['signups']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-page-card>

        {{-- The homepage headline A/B test (App\Utils\HeroExperiment). It allocates traffic and
             picks its winner by itself; the only control is the reset, which starts it over. --}}
        @if (config('app.is_nexus'))
            <x-page-card flush id="hero-test" class="scroll-mt-4" :title="__('messages.hero_test')" :lead="__('messages.hero_test_help')">
                @if ($heroTest !== null)
                <x-slot name="aside">
                    <form method="POST" action="{{ route('admin.growth.hero_test_reset') }}"
                          data-confirm="{{ __('messages.hero_test_reset_confirm') }}">
                        @csrf
                        <input type="hidden" name="range" value="{{ $range }}">
                        <x-danger-button class="!py-2">@lang('messages.hero_test_reset')</x-danger-button>
                    </form>
                </x-slot>
                @endif

                @if ($heroTest === null)
                    <x-page-empty compact :title="__('messages.hero_test_unavailable')" />
                @else
                    @php
                        // Challengers can share the control's headline, so the key is what tells them apart.
                        $heroHeadline = fn ($key) => (collect($heroTest['rows'])->firstWhere('key', $key)['headline'] ?? $key).' ('.$key.')';
                        $heroPct = fn ($value) => $value === null ? __('messages.funnel_na') : $ltr(round($value * 100, 1).'%');
                    @endphp
                    <div class="insight-pad">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            @if ($heroTest['phase'] === 'winner')
                                {{ __('messages.hero_test_phase_winner', ['variant' => $heroHeadline($heroTest['winner']['key']), 'date' => $heroTest['winner']['date']]) }}
                            @elseif ($heroTest['phase'] === 'candidate')
                                {{ __('messages.hero_test_phase_candidate', ['variant' => $heroHeadline($heroTest['candidate']['key']), 'date' => $heroTest['lock_date']]) }}
                            @elseif ($heroTest['phase'] === 'clicks')
                                @lang('messages.hero_test_phase_clicks')
                            @else
                                @lang('messages.hero_test_phase_signups')
                            @endif
                        </p>
                        @if ($heroTest['reset_at'])
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.hero_test_since', ['date' => $heroTest['reset_at']]) }}</p>
                        @endif
                    </div>

                    <div class="page-scroll">
                        <table class="page-table is-wide">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('messages.hero_test_headline')</th>
                                    <th scope="col" class="c-num">@lang('messages.hero_test_share')</th>
                                    <th scope="col" class="c-num">@lang('messages.hero_test_visitors')</th>
                                    <th scope="col" class="c-num">@lang('messages.click_rate')</th>
                                    <th scope="col" class="c-num">@lang('messages.growth_signups')</th>
                                    <th scope="col" class="c-num">@lang('messages.hero_test_signup_rate')</th>
                                    <th scope="col" class="c-num">@lang('messages.hero_test_chance_best_clicks')</th>
                                    <th scope="col" class="c-num">@lang('messages.hero_test_chance_best')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($heroTest['rows'] as $row)
                                    <tr>
                                        {{-- The copy is English whatever the reader's language, so it is
                                             laid out left to right. --}}
                                        <td class="c-main hero-copy">
                                            <span class="c-strong block" dir="ltr">{{ $row['headline'] }}</span>
                                            <span class="c-sub" dir="ltr">{{ $row['subtitle'] }}</span>
                                            <span class="c-sub">
                                                <span class="event-chip c-mono" dir="ltr">{{ $row['key'] }}</span>
                                                @if ($row['is_default'])
                                                    <span class="event-chip">@lang('messages.hero_test_default')</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="c-num c-strong" data-label="{{ __('messages.hero_test_share') }}">{{ $heroPct($row['share']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.hero_test_visitors') }}">{{ number_format($row['visitors']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.click_rate') }}">{{ $heroPct($row['click_rate']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.growth_signups') }}">{{ number_format($row['signups']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.hero_test_signup_rate') }}">{{ $heroPct($row['signup_rate']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.hero_test_chance_best_clicks') }}">{{ $heroPct($row['p_best_clicks']) }}</td>
                                        <td class="c-num" data-label="{{ __('messages.hero_test_chance_best') }}">{{ $heroPct($row['p_best']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-page-card>
        @endif
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
        <style {!! nonce_attr() !!}>
            /* The headline's copy keeps a floor, so the eight columns scroll inside the card
               rather than squeeze it to a word a line; and its eight headings may break. */
            @media (min-width: 640px) {
              #hero-test .hero-copy {
                min-width: 18rem;
                max-width: 26rem;
              }
              #hero-test .page-table th {
                white-space: normal;
                vertical-align: bottom;
              }
            }
        </style>
    </x-slot>
</x-app-admin-layout>
