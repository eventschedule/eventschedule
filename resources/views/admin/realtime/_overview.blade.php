{{-- The headline numbers: who is here right now and where, then the last 30 minutes. --}}
<section class="ap-card rounded-xl p-4 sm:p-6 xl:col-span-2" aria-labelledby="realtime-overview-heading">
    {{-- Right now: how many people, and which part of the site each of them is in. --}}
    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-4">
        <div class="shrink-0">
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
        </div>

        {{-- Where they are: the page's own Surface filter, as buttons. The counts are people right
             now, and keep every area's number while one of them is the filter. --}}
        <div class="flex-1 min-w-full sm:min-w-[23rem] flex gap-2" role="group" :aria-label="msg.surfaces">
            <button v-for="surface in p.overview.now_by_surface" :key="surface.key" type="button" @click="toggleFilter('surface', surface.key)"
                    :aria-pressed="isActive('surface', surface.key) ? 'true' : 'false'"
                    class="realtime-stat flex-1 min-w-0 flex flex-col rounded-xl px-3 py-2.5 text-start transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                    :class="isActive('surface', surface.key) ? 'realtime-stat-on' : ''">
                <span class="flex items-center gap-2">
                    <span class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center" :class="surfaceTone(surface.key).bg" aria-hidden="true">
                        <svg class="w-4 h-4" :class="surfaceTone(surface.key).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons[surface.key] || icons.other" />
                        </svg>
                    </span>
                    <span class="dashboard-stat-value text-2xl font-bold leading-7 tabular-nums" :class="surface.count ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'">@{{ fmt(surface.count) }}</span>
                </span>
                <span class="mt-1.5 text-sm font-medium leading-tight text-gray-700 dark:text-gray-300">@{{ surface.label }}</span>
            </button>
        </div>
    </div>

    {{-- The last 30 minutes: the total first, then how it splits. The two rows are also the chart's
         legend, so their swatches are the chart's two bar colours. --}}
    <div class="card-highlight mt-5 pt-5 grid md:grid-cols-[minmax(0,15rem)_minmax(0,1fr)] gap-6">
        <div>
            <div class="flex items-baseline justify-between gap-2">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.last30 }}</p>
                <p class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ p.overview.phrases.views_total }}</p>
            </div>
            <template v-if="splitTotal > 0">
                <div class="mt-3 flex h-2 rounded-full overflow-hidden bg-gray-300 dark:bg-gray-600" role="img" :aria-label="splitLabel">
                    <span class="bg-[var(--brand-blue)] transition-all duration-200" :style="{ width: splitShare + '%' }"></span>
                </div>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-sm bg-[var(--brand-blue)]" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <dt class="text-gray-900 dark:text-white">@{{ msg.acceptedCookies }}</dt>
                            <dd class="text-xs text-gray-500 dark:text-gray-400">@{{ p.overview.phrases.accepted }}</dd>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="mt-1.5 w-2.5 h-2.5 shrink-0 rounded-sm bg-gray-300 dark:bg-gray-600" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <dt class="text-gray-900 dark:text-white">@{{ msg.notAccepted }}</dt>
                            <dd class="text-xs text-gray-500 dark:text-gray-400">@{{ p.overview.phrases.not_accepted }}</dd>
                        </div>
                    </div>
                </dl>
            </template>
        </div>

        <div class="flex flex-col min-w-0">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.viewsPerMinute }}</h3>
                {{-- The chart marks each sign-up with this dot, above the minute it happened in. --}}
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.signedUpMark }}
                </span>
            </div>
            <div class="relative flex-1 min-h-[10rem]">
                <div class="absolute inset-0"><canvas ref="chart" role="img" :aria-label="chartSummary"></canvas></div>
            </div>
        </div>
    </div>
</section>
