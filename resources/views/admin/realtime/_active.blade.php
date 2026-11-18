{{-- Hosted only: active users, the panel under Revenue and beside Sign-ups at the top of the
     dashboard (it was the foot of the Revenue card until 2026-11). People active in the seven days
     up to each date (App\Services\ActiveDays), the chart the growth panel shows on a selfhosted
     install. Its numbers arrive with Revenue's (fetchRevenue in _script). --}}
<section class="realtime-panel-active ap-card rounded-xl flex flex-col" aria-labelledby="realtime-active-heading" :aria-busy="revenue ? 'false' : 'true'">
    <div v-if="!revenue" class="p-4 sm:p-5" aria-hidden="true">
        <div class="rounded-xl realtime-skeleton" style="height: 9rem"></div>
    </div>
    <div v-else-if="revenue.active" class="flex-1 flex flex-col p-4 sm:p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <h2 id="realtime-active-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.activeUsers }}</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last12Weeks }}</span>
        </div>
        <p class="mt-1 flex flex-wrap items-baseline gap-x-2">
            <span class="dashboard-stat-value text-2xl font-bold leading-7 text-gray-900 dark:text-white">@{{ fmt(revenue.active.active_7d) }}</span>
            <span v-if="revenue.active.change !== null" dir="ltr" class="text-sm font-medium" :class="revenue.active.change >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400'">@{{ signedPct(revenue.active.change) }}</span>
        </p>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            <span class="whitespace-nowrap">@{{ activeLine(revenue.active) }}</span>
            <span class="mx-1" aria-hidden="true">&middot;</span>
            <span class="whitespace-nowrap">@{{ msg.active30 }} @{{ fmt(revenue.active.active_30d) }}</span>
        </p>
        <div class="relative flex-1 mt-3" style="min-height: 6rem">
            <div class="absolute inset-0"><canvas ref="activeChart" role="img" :aria-label="msg.activeUsers"></canvas></div>
        </div>
    </div>
    <p v-else class="p-4 sm:p-5 text-sm text-gray-500 dark:text-gray-400"><span id="realtime-active-heading">@{{ msg.activeUsers }}</span> · @{{ msg.cardFailed }}</p>
</section>
