{{-- So far today, against before (RealtimeSoFar; brought over from Stackapella's Growth page,
     2026-11-12): visits and sign-ups as running totals through the day, today against yesterday
     and a usual day of the same weekday, each cut at the same clock time, with the day projected
     from what is in so far. Two small multiples, each its own y-axis from 0. The chart itself is
     the so-far-chart component (_so-far-script); this is its frame. Asked with the page and once a
     minute after. --}}
<section class="ap-card rounded-xl p-4 sm:p-6" aria-labelledby="so-far-heading" :aria-busy="soFar ? 'false' : 'true'" data-so-far>
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 id="so-far-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.soFar.title }}</h2>
        <span v-if="soFar" class="text-xs text-gray-500 dark:text-gray-400" data-so-far-clock>@{{ soFarClock }}</span>
    </div>

    <div v-if="!soFar" class="mt-4 grid gap-6 lg:grid-cols-2" aria-hidden="true">
        <div class="so-far-skeleton rounded-xl realtime-skeleton"></div>
        <div class="so-far-skeleton rounded-xl realtime-skeleton hidden lg:block"></div>
    </div>

    <template v-else>
        <div class="so-far-grid-pair mt-4 grid lg:grid-cols-2">
            <so-far-chart :chart="soFar" metric="visits" :label="msg.visits"></so-far-chart>
            <so-far-chart :chart="soFar" metric="signups" :label="msg.tileSignups"></so-far-chart>
        </div>
        <p class="so-far-how mt-5 text-xs leading-relaxed text-gray-500 dark:text-gray-400">@{{ msg.soFar.how }}</p>
    </template>
</section>
