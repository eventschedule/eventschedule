{{-- The last 30 minutes: page views per minute, and how they split by the cookie choice. Who is
     here right now, and where, is the board above (_now). --}}
<section class="ap-card rounded-xl p-4 sm:p-6 xl:col-span-2" aria-labelledby="realtime-last30-heading">
    {{-- The last 30 minutes: the total first, then how it splits. The two rows are also the chart's
         legend, so their swatches are the chart's two bar colours. --}}
    <div class="grid md:grid-cols-[minmax(0,15rem)_minmax(0,1fr)] gap-6">
        <div>
            <div class="flex items-baseline justify-between gap-2">
                <h2 id="realtime-last30-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.last30 }}</h2>
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
