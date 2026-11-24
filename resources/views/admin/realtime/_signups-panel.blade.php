{{-- Sign-ups in the last 24 hours: the count, one bar per hour, and how far those people have
     got. One of the four panels at the top (it was the foot of the Now card until 2026-11); the
     Sign-ups card further down (_signups) keeps the list of who they are. Read from the audit log,
     so it ignores the page's filters and tracking. --}}
<section class="realtime-panel-signups ap-card rounded-xl p-4 sm:p-6" aria-labelledby="realtime-signups-panel-heading">
    <div class="grid grid-cols-1 sm:grid-cols-[auto_minmax(0,1fr)] gap-x-6 gap-y-3 items-end">
        <div class="flex items-center gap-3 min-w-0">
            <span class="dashboard-stat-value text-3xl font-bold leading-none tabular-nums text-gray-900 dark:text-white">@{{ fmt(signups.total || 0) }}</span>
            <div class="min-w-0">
                <h2 id="realtime-signups-panel-heading" class="text-sm font-semibold text-gray-900 dark:text-white">@{{ msg.signups }}</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @{{ msg.last24h }}<template v-if="lastSignupAgo !== null"> · @{{ msg.lastSignup.replace(':time', ago(lastSignupAgo, true)) }}</template><template v-if="p.filtered"> · @{{ msg.notFiltered }}</template>
                </p>
            </div>
        </div>

        {{-- When they came: one bar per hour, the hour that just passed last. --}}
        <div class="min-w-0" role="img" :aria-label="msg.perHour + ': ' + fmt(signups.total || 0)">
            <h3 class="mb-1 text-xs font-medium text-gray-600 dark:text-gray-400" aria-hidden="true">@{{ msg.perHour }}</h3>
            <div class="flex items-end gap-0.5 h-12">
                <span v-for="(bar, index) in hourBars" :key="index" class="flex-1 flex items-end h-full" :title="bar.title">
                    <span class="w-full rounded-sm transition-all duration-200"
                          :class="bar.count ? 'bg-[var(--brand-blue)]' : 'bg-gray-200 dark:bg-gray-700'"
                          :style="{ height: bar.count ? bar.height + '%' : '2px' }"></span>
                </span>
            </div>
            <div class="mt-1 flex justify-between text-[11px] tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">
                <span>@{{ ago(86399, true) }}</span><span>@{{ msg.axisNow }}</span>
            </div>
        </div>
    </div>

    {{-- How far these people have got: the steps after the account itself, which is the count
         above, each with the share who reached it. Cells, not a sentence: as a sentence it
         wrapped and left an arrow starting a line. --}}
    <ol v-if="signups.total" class="mt-4 grid grid-cols-3 gap-x-4 gap-y-3">
        <li v-for="step in signups.steps.slice(1)" :key="step.key" class="min-w-0">
            <p class="flex items-baseline gap-1.5">
                <span class="text-lg font-semibold leading-6 tabular-nums text-gray-900 dark:text-white">@{{ fmt(step.count) }}</span>
                <span class="text-xs tabular-nums text-gray-500 dark:text-gray-400">@{{ stepShare(step) }}%</span>
            </p>
            <p class="text-xs leading-tight text-gray-700 dark:text-gray-300">@{{ step.label }}</p>
            <div class="mt-1.5 h-1.5 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700" aria-hidden="true">
                <div class="h-full rounded-full bg-[var(--brand-button-bg)] transition-all duration-200" :style="{ width: stepShare(step) + '%' }"></div>
            </div>
        </li>
    </ol>
</section>
