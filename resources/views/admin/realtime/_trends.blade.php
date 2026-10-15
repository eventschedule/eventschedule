{{-- Trends: the last 24 hours, 7 days or 30 days, each figure against the same span before it. The
     tiles choose what the chart draws; page views and visits are drawn by part of the site, in the
     colours the rest of the page gives them. RealtimeTrends says where each number comes from and
     why there is no "visitors" here. --}}
@php
    $segShell = 'inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1';
    $segItem = 'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
@endphp
<section v-if="trends" class="ap-card rounded-xl p-4 sm:p-6" aria-labelledby="realtime-trends-heading">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-baseline gap-2">
            <h2 id="realtime-trends-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.trends }}</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">@{{ trends.unit === 'hour' ? msg.byHour : msg.byDay }}</span>
        </div>
        <div class="{{ $segShell }}" role="group" :aria-label="msg.trends">
            <button v-for="option in rangeOptions" :key="option.value" type="button" @click="setRange(option.value)"
                    :aria-pressed="trendRange === option.value ? 'true' : 'false'"
                    class="{{ $segItem }}"
                    :class="trendRange === option.value
                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">@{{ option.label }}</button>
        </div>
    </div>

    {{-- The tiles are the chart's switch: one is always pressed. --}}
    <div class="realtime-tiles mt-4 grid gap-2" role="group" :aria-label="msg.trends" :aria-busy="trendsLoading ? 'true' : 'false'">
        <button v-for="tile in trendTiles" :key="tile.key" type="button" @click="setMetric(tile.key)"
                :aria-pressed="trendMetric === tile.key ? 'true' : 'false'"
                class="realtime-stat flex flex-col rounded-xl px-3 py-2.5 text-start transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                :class="[trendMetric === tile.key ? 'realtime-stat-on' : '', trendsLoading ? 'opacity-60' : '']">
            <span class="text-sm font-medium leading-tight text-gray-700 dark:text-gray-300">@{{ tile.label }}</span>
            <span class="mt-1.5 flex flex-wrap items-baseline gap-x-2">
                <span class="dashboard-stat-value text-2xl font-bold leading-7 tabular-nums"
                      :class="tile.value === null ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-white'">@{{ tile.value === null ? '-' : fmt(tile.value) }}</span>
                <span v-if="tile.change !== null" class="text-xs font-semibold tabular-nums"
                      :class="tile.change > 0 ? 'text-green-700 dark:text-green-400' : (tile.change < 0 ? 'text-red-700 dark:text-red-400' : 'text-gray-500 dark:text-gray-400')">@{{ pct(tile.change) }}</span>
            </span>
            <span class="mt-0.5 text-xs leading-snug text-gray-500 dark:text-gray-400">@{{ tile.before }}</span>
        </button>
    </div>

    <p v-if="trendIsTraffic && (trends.collecting || trends.since)" class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        @{{ trends.collecting ? msg.trendCollecting : msg.trendSince.replace(':time', trends.since) }}
    </p>

    <div class="mt-4 relative h-52">
        <canvas ref="trendChart" role="img" :aria-label="trendChartLabel"></canvas>
    </div>

    {{-- The legend, for the two that are drawn by part of the site. --}}
    <ul v-if="trendIsTraffic" class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
        <li v-for="surface in trends.surfaces" :key="surface" class="inline-flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 shrink-0 rounded-sm realtime-swatch" :class="surfaceTone(surface).text" aria-hidden="true"></span>@{{ msg.surface[surface] }}
        </li>
    </ul>
</section>
