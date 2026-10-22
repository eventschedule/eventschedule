{{-- Right now: how many people are on the site. The headline counts only visitors who accepted
     cookies (the others send one page view and no heartbeat), so the traffic it leaves out is said
     beside it, with page views per minute in the same two colours: this card took in the old "Last
     30 minutes" card, whose consent split sat three cards below the number. Where each of them is,
     is the board under it (_board). --}}
<section id="realtime-now" class="ap-card rounded-xl p-4 sm:p-6" aria-labelledby="realtime-now-heading">
    <div class="realtime-now-head" :class="splitTotal > 0 ? '' : 'is-quiet'">
        {{-- One line: the icon, the number, and what it counts with how it splits. --}}
        <div class="flex items-center gap-4 min-w-0">
            <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons.signal" />
                </svg>
            </div>
            <span class="dashboard-stat-value text-4xl font-bold leading-none tabular-nums text-gray-900 dark:text-white">@{{ fmt(p.overview.now) }}</span>
            <div class="min-w-0">
                <h2 id="realtime-now-heading" class="text-base font-semibold text-gray-900 dark:text-white">
                    @{{ msg.visitorsRightNow }}
                    <span v-if="p.filtered" class="ms-1 text-sm font-normal text-gray-500 dark:text-gray-400">@{{ msg.ofTotal.replace(':count', fmt(p.overview.now_total)) }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <template v-if="p.overview.now > 0">@{{ p.overview.phrases.split }}</template>
                    <template v-else-if="p.overview.last_seen_ago !== null">@{{ msg.lastVisitor.replace(':time', ago(p.overview.last_seen_ago, true)) }}</template>
                    <template v-else>@{{ msg.noVisitorsHour }}</template>
                </p>
            </div>
        </div>

        {{-- Page views per minute, both kinds of visitor. --}}
        <div class="realtime-now-chart min-w-0">
            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">@{{ msg.viewsPerMinute }}</h3>
                {{-- The chart marks each sign-up with this dot, above the minute it happened in. --}}
                <span v-if="hasSignupMarks" class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.signedUpMark }}
                </span>
            </div>
            <div class="relative h-24">
                <div class="absolute inset-0"><canvas ref="chart" role="img" :aria-label="chartSummary"></canvas></div>
            </div>
        </div>

        {{-- What the headline leaves out. The two swatches are the chart's two bar colours. --}}
        <div v-if="splitTotal > 0" class="realtime-now-consent min-w-0">
            <div class="flex h-2 rounded-full overflow-hidden bg-gray-300 dark:bg-gray-600" role="img" :aria-label="splitLabel">
                <span class="bg-[var(--brand-blue)] transition-all duration-200" :style="{ width: splitShare + '%' }"></span>
            </div>
            <p class="mt-2 text-xs leading-snug text-gray-700 dark:text-gray-300">@{{ p.overview.phrases.declined }}</p>
            <p class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 shrink-0 rounded-sm bg-[var(--brand-blue)]" aria-hidden="true"></span>@{{ msg.acceptedCookies }}</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 shrink-0 rounded-sm bg-gray-300 dark:bg-gray-600" aria-hidden="true"></span>@{{ msg.notAccepted }}</span>
            </p>
        </div>
    </div>

    {{-- Calendars embedded on other websites have no page here to sit on, and the Surfaces card
         that counted them is gone. --}}
    <p v-if="p.overview.phrases.embed" class="mt-4 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.embed" /></svg>@{{ p.overview.phrases.embed }}
    </p>
</section>
