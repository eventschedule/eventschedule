{{-- So far, against before (RealtimeSoFar; brought over from Stackapella's Growth page,
     2026-11-12): visits and sign-ups as running totals through the day, today against yesterday
     and a usual day of the same weekday, each cut at the same clock time, with the day projected
     from what is in so far. Two small multiples, each its own y-axis from 0. The chart itself is
     the so-far-chart component (_so-far-script); this is its frame. Asked with the page and once a
     minute after. The period switch (Stackapella's too) picks today by the hour, or the last 7
     days, the last 4 weeks or this month day by day (so-far-columns), each against the period
     before cut at the same clock time; the choice is remembered in this browser. --}}
@php
    $segShell = 'inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1';
    $segItem = 'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
@endphp
<section class="ap-card rounded-xl p-4 sm:p-6" aria-labelledby="so-far-heading" :aria-busy="soFar ? 'false' : 'true'" data-so-far>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h2 id="so-far-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.soFar.title }}</h2>
            <p v-if="soFar" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" data-so-far-clock>@{{ soFarClock }}</p>
        </div>
        <div class="{{ $segShell }}" role="group" :aria-label="msg.soFar.periods" data-so-far-periods>
            <button v-for="(name, key) in msg.soFar.period" :key="key" type="button" @click="setSoFarPeriod(key)"
                    :aria-pressed="soFarPeriod === key ? 'true' : 'false'" :data-period="key"
                    class="{{ $segItem }}"
                    :class="soFarPeriod === key
                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">@{{ name }}</button>
        </div>
    </div>

    <div v-if="!soFar" class="mt-4 grid gap-6 lg:grid-cols-2" aria-hidden="true">
        <div class="so-far-skeleton rounded-xl realtime-skeleton"></div>
        <div class="so-far-skeleton rounded-xl realtime-skeleton hidden lg:block"></div>
    </div>

    <template v-else>
        <div class="so-far-grid-pair mt-4 grid lg:grid-cols-2" :class="soFarLoading ? 'opacity-60' : ''" :aria-busy="soFarLoading ? 'true' : 'false'">
            <template v-if="soFar.period === 'today'">
                <so-far-chart :chart="soFar" metric="visits" :label="msg.visits"></so-far-chart>
                <so-far-chart :chart="soFar" metric="signups" :label="msg.tileSignups"></so-far-chart>
            </template>
            <template v-else>
                <so-far-columns :key="'visits-' + soFar.period" :chart="soFar" metric="visits" :label="msg.visits"></so-far-columns>
                <so-far-columns :key="'signups-' + soFar.period" :chart="soFar" metric="signups" :label="msg.tileSignups"></so-far-columns>
            </template>
        </div>
        <p v-if="soFar.period === 'today'" class="so-far-how mt-5 text-xs leading-relaxed text-gray-500 dark:text-gray-400">@{{ msg.soFar.how }}</p>
    </template>
</section>
