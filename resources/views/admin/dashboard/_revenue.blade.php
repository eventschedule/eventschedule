{{-- Hosted only. What Stripe is billing (RecurringRevenue), split by plan, with what the trials
     would add shown beside it and never inside it.

     Three things are called a trial in this app, and each is named apart here: "Trial" in the
     table is a Stripe trial with a card on file; under "Outside Stripe" are a Pro trial started
     without a card and a selling trial, which is a free schedule trying paid tickets. --}}
@php
    $cols = 'grid grid-cols-[minmax(0,1fr)_3rem_4.75rem_2.75rem_4.75rem] gap-x-2 sm:gap-x-3 items-baseline px-4 sm:px-5';
    $terms = ['month' => __('messages.monthly'), 'year' => __('messages.yearly')];
    $totals = $revenue['totals'] ?? null;
@endphp

<section id="dash-revenue" class="ap-card rounded-xl flex flex-col scroll-mt-20">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">@lang('messages.revenue')</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">Stripe &middot; @lang('messages.subscriptions')</span>
    </div>

    @if (! $revenue)
        @include('admin.dashboard._failed')
    @else
        {{-- The comparison the card exists for, before the table that breaks it down. --}}
        <div class="px-4 sm:px-5 pb-4 grid grid-cols-2 gap-2">
            <div class="rounded-xl px-3 py-2.5" style="background: var(--ap-tint-sunken)">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_paying') <span class="text-gray-900 dark:text-white tabular-nums">{{ number_format($totals['billing_count']) }}</span></p>
                <p class="mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white">{{ plan_price($totals['mrr']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="whitespace-nowrap">MRR</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">ARR {{ plan_price($totals['arr']) }}</span>
                </p>
            </div>
            <div class="rounded-xl px-3 py-2.5" style="background: var(--ap-tint-sunken)">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_in_trial') <span class="text-gray-900 dark:text-white tabular-nums">{{ number_format($totals['trialing_count']) }}</span></p>
                <p class="mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white"><span dir="ltr">+{{ plan_price($totals['trial_mrr']) }}</span></p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.admin_dash_if_convert')</p>
            </div>
        </div>

        <div class="{{ $cols }} py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" style="background: var(--ap-tint-sunken)">
            <span>@lang('messages.plan')</span>
            <span class="text-end">@lang('messages.admin_dash_paying')</span>
            <span class="text-end">MRR</span>
            <span class="text-end">@lang('messages.trial')</span>
            <span class="text-end">@lang('messages.admin_dash_would_add')</span>
        </div>
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]">
            @foreach ($revenue['plans'] as $plan)
                <li class="{{ $cols }} py-2 text-sm">
                    <span class="flex flex-wrap items-baseline gap-x-1.5 min-w-0 text-gray-900 dark:text-gray-100">
                        {{ __('messages.'.$plan['tier']) }}
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $terms[$plan['term']] }}</span>
                    </span>
                    <span class="tabular-nums text-end text-gray-700 dark:text-gray-300">{{ number_format($plan['billing_count']) }}</span>
                    <span class="tabular-nums text-end text-gray-900 dark:text-white">{{ plan_price($plan['mrr']) }}</span>
                    <span class="tabular-nums text-end {{ $plan['trialing_count'] ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">{{ number_format($plan['trialing_count']) }}</span>
                    <span class="tabular-nums text-end text-gray-500 dark:text-gray-400">
                        @if ($plan['trialing_count'])
                            <span dir="ltr">+{{ plan_price($plan['trial_mrr']) }}</span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500">-</span>
                        @endif
                    </span>
                </li>
            @endforeach

            {{-- A subscription on a price config no longer names: a customer being charged, booked
                 at zero until STRIPE_PRICE_* points at it again. Shown only when there is one. --}}
            @if ($revenue['unrecognized']['billing_count'] + $revenue['unrecognized']['trialing_count'] > 0)
                <li class="{{ $cols }} py-2 text-sm">
                    <span class="truncate text-amber-700 dark:text-amber-400">@lang('messages.admin_dash_unrecognized_price')</span>
                    <span class="tabular-nums text-end text-gray-700 dark:text-gray-300">{{ number_format($revenue['unrecognized']['billing_count']) }}</span>
                    <span class="tabular-nums text-end text-gray-400 dark:text-gray-500">-</span>
                    <span class="tabular-nums text-end text-gray-700 dark:text-gray-300">{{ number_format($revenue['unrecognized']['trialing_count']) }}</span>
                    <span class="tabular-nums text-end text-gray-400 dark:text-gray-500">-</span>
                </li>
            @endif

            <li class="{{ $cols }} py-2 text-sm font-semibold">
                <span class="text-gray-900 dark:text-white">@lang('messages.total')</span>
                <span class="tabular-nums text-end text-gray-900 dark:text-white">{{ number_format($totals['billing_count']) }}</span>
                <span class="tabular-nums text-end text-gray-900 dark:text-white">{{ plan_price($totals['mrr']) }}</span>
                <span class="tabular-nums text-end text-gray-900 dark:text-white">{{ number_format($totals['trialing_count']) }}</span>
                <span class="tabular-nums text-end text-gray-700 dark:text-gray-300"><span dir="ltr">+{{ plan_price($totals['trial_mrr']) }}</span></span>
            </li>
        </ul>

        <dl class="px-4 sm:px-5 divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
            {{-- Inside the MRR above, not added to it: a renewal Stripe is retrying, or a
                 subscription paid up to a date and then gone. Money first. --}}
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-0.5 py-2">
                <dt class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <span class="w-2 h-2 rounded-full {{ $totals['at_risk_count'] ? 'bg-amber-500' : 'bg-gray-300 dark:bg-gray-600' }}" aria-hidden="true"></span>@lang('messages.admin_dash_at_risk')
                </dt>
                <dd class="basis-full sm:basis-auto text-sm text-gray-500 dark:text-gray-400 sm:text-end">
                    <span class="whitespace-nowrap">{!! __('messages.admin_dash_of_mrr', ['amount' => '<span class="text-gray-900 dark:text-white tabular-nums">'.e(plan_price($totals['at_risk_mrr'])).'</span>']) !!}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.past_due') {{ number_format($totals['past_due_count']) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_cancelling') {{ number_format($totals['cancelling_count']) }}</span>
                </dd>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-0.5 py-2">
                <dt class="text-sm text-gray-700 dark:text-gray-300">@lang('messages.admin_dash_outside_stripe')</dt>
                <dd class="basis-full sm:basis-auto text-sm text-gray-500 dark:text-gray-400 sm:text-end">
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_pro_granted') {{ number_format($revenue['outside']['granted']) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_pro_trial_no_card') {{ number_format($revenue['outside']['trial']) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_selling_trial') {{ number_format($revenue['outside']['selling']) }}</span>
                </dd>
            </div>
            {{-- The label and its value stay together: BoostMarkupCurrencyTest reads the amount
                 from the HTML that follows this label. --}}
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-0.5 py-2">
                <dt class="text-sm text-gray-700 dark:text-gray-300">@lang('messages.boost_markup_revenue')</dt>
                <dd class="basis-full sm:basis-auto text-sm text-gray-500 dark:text-gray-400 sm:text-end">
                    <span class="text-gray-900 dark:text-white tabular-nums whitespace-nowrap">{{ \App\Utils\MoneyUtils::format($revenue['boost']['markup'], $revenue['boost']['currency']) }}</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">@lang('messages.admin_dash_last_30_days')</span>
                </dd>
            </div>
        </dl>

        <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-between gap-3" style="border-top: 1px solid var(--ap-hairline)">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @lang('messages.admin_dash_mrr_note')
                @if ($totals['trialing_count'] > 0)
                    {{ __('messages.recurring_revenue_excludes_trials', ['count' => number_format($totals['trialing_count'])]) }}
                @endif
            </p>
            <x-link :href="route('admin.revenue')" class="text-sm font-medium whitespace-nowrap">@lang('messages.revenue')</x-link>
        </div>
    @endif
</section>
