{{-- The headline numbers and the page views per minute chart. --}}
<section class="ap-card rounded-xl p-4 sm:p-6 xl:col-span-2" aria-labelledby="realtime-overview-heading">
    <div class="grid md:grid-cols-[minmax(0,15rem)_minmax(0,1fr)] gap-6">
        <div class="flex flex-col">
            <div class="flex items-center gap-3 mb-3">
                <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons.signal" />
                    </svg>
                </div>
                <h2 id="realtime-overview-heading" class="text-sm font-medium text-gray-500 dark:text-gray-400">@{{ msg.visitorsRightNow }}</h2>
            </div>

            <p class="flex items-baseline gap-2">
                <span class="dashboard-stat-value text-4xl font-bold tabular-nums text-gray-900 dark:text-white">@{{ fmt(p.overview.now) }}</span>
                <span v-if="p.filtered" class="text-sm text-gray-500 dark:text-gray-400">@{{ msg.ofTotal.replace(':count', fmt(p.overview.now_total)) }}</span>
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <template v-if="p.overview.now > 0">@{{ p.overview.phrases.split }}</template>
                <template v-else-if="p.overview.last_seen_ago !== null">@{{ msg.lastVisitor.replace(':time', ago(p.overview.last_seen_ago, true)) }}</template>
                <template v-else>@{{ msg.noVisitorsHour }}</template>
            </p>

            <div class="card-highlight mt-4 pt-4 space-y-1 text-sm">
                <p class="font-medium text-gray-700 dark:text-gray-300">@{{ msg.last30 }}</p>
                <p class="text-gray-500 dark:text-gray-400">@{{ p.overview.phrases.window }}</p>
                <p v-if="p.overview.phrases.unidentified" class="text-gray-500 dark:text-gray-400">@{{ p.overview.phrases.unidentified }}</p>
            </div>
        </div>

        <div class="flex flex-col min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.viewsPerMinute }}</h3>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-[var(--brand-blue)]" aria-hidden="true"></span>@{{ msg.acceptedCookies }}</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-gray-300 dark:bg-gray-600" aria-hidden="true"></span>@{{ msg.notIdentified }}</span>
                </div>
            </div>
            <div class="relative h-36 sm:h-44 xl:h-52">
                <canvas ref="chart" role="img" :aria-label="chartSummary"></canvas>
            </div>
        </div>
    </div>
</section>
