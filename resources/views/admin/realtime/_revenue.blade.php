{{-- Hosted only: the Revenue card in the dashboard's top corner. The two figures of the old
     dashboard's Revenue card, what Stripe is billing and what the trials would add (shown beside
     it, never inside it), and the at-risk line when there is something at risk. Details opens
     /admin/revenue (the boost markup, subscription health). It stands beside the Now card's head only
     (the board runs under both), so it keeps to that height: the two figures side by side.

     Asked once the page is up (fetchRevenue in _script) and again with Trends every five minutes,
     never with the ten-second poll: billing does not move by the second. The amounts arrive
     formatted (plan_price(), AdminDashboardController::revenue()). --}}
<section id="dash-revenue" class="realtime-corner ap-card rounded-xl flex flex-col" aria-labelledby="dash-revenue-heading" :aria-busy="revenue ? 'false' : 'true'">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 id="dash-revenue-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.revenue }} <span class="ms-1 text-xs font-normal text-gray-500 dark:text-gray-400">Stripe</span></h2>
        <x-link :href="route('admin.revenue')" class="text-sm font-medium whitespace-nowrap">@{{ msg.details }}</x-link>
    </div>

    <div v-if="!revenue" class="px-4 sm:px-5 pb-4 grid grid-cols-2 gap-2" aria-hidden="true">
        <div class="rounded-xl realtime-skeleton" style="height: 5.5rem"></div>
        <div class="rounded-xl realtime-skeleton" style="height: 5.5rem"></div>
    </div>

    <p v-else-if="revenue.failed" class="mx-4 sm:mx-5 mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>@{{ msg.cardFailed }}</span>
    </p>

    <template v-else>
        <div class="px-4 sm:px-5 pb-4 grid grid-cols-2 gap-2">
            <div class="rounded-xl px-3 py-2.5" style="background: var(--ap-tint-sunken)">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">@{{ msg.paying }} <span class="text-gray-900 dark:text-white tabular-nums">@{{ fmt(revenue.billing_count) }}</span></p>
                <p class="mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white">@{{ revenue.mrr }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="whitespace-nowrap">MRR</span>
                    <span aria-hidden="true">&middot;</span>
                    <span class="whitespace-nowrap">ARR @{{ revenue.arr }}</span>
                </p>
            </div>
            <div class="rounded-xl px-3 py-2.5" style="background: var(--ap-tint-sunken)">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">@{{ msg.inTrial }} <span class="text-gray-900 dark:text-white tabular-nums">@{{ fmt(revenue.trialing_count) }}</span></p>
                <p class="mt-1 dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white"><span dir="ltr">+@{{ revenue.trial_mrr }}</span></p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@{{ msg.ifConvert }}</p>
            </div>
        </div>

        {{-- Inside the MRR above, not added to it: a renewal Stripe is retrying, or a
             subscription paid up to a date and then gone. --}}
        <p v-if="revenue.at_risk_count > 0" class="mx-4 sm:mx-5 mb-4 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <span class="w-2 h-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
            <span>@{{ msg.atRisk }} <span class="whitespace-nowrap text-gray-500 dark:text-gray-400">@{{ fill(msg.ofMrr, revenue.at_risk_mrr) }}</span></span>
        </p>
    </template>

</section>
