{{-- At the door today: the events on the viewer's schedules that are on (ScheduleActivity::door()),
     each as a small ledger. Only while there is one; on any other day the card is not drawn.

     It is the first thing on a phone, because the day it matters is the day the owner is standing
     at a door with a phone. Each event is one link, at least a thumb tall, that opens Check-in on
     that event, or Sales where there is no arrival count to open (a sign-up, or a plan without
     check-in).

     What the bar shows is arrivals against tickets SOLD, which is what a door watches, and not
     tickets sold against the room. Each line is a label and a number: no sentence here has a
     count inside it, so no language needs a plural form.

     Inside #schedule-realtime, so everything visible comes through Vue's own interpolation; an
     event's name is wrapped in <bdi> so a Hebrew title in an English card keeps its own direction
     without moving the time beside it. --}}
<section v-if="a.door.events.length" class="rt-door ap-card rounded-xl p-4 sm:p-5" aria-labelledby="schedule-realtime-door">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
        <h2 id="schedule-realtime-door" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.doorToday }}</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.doorUpdates }}</span>
    </div>

    <ul class="mt-1 -mx-2">
        <li v-for="event in a.door.events" :key="event.key">
            <a :href="event.url" class="block rounded-xl px-2 py-3 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-white/[0.04] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                <span class="flex items-baseline justify-between gap-3">
                    <bdi class="min-w-0 truncate text-sm font-semibold text-gray-900 dark:text-white">@{{ event.name }}</bdi>
                    <span class="shrink-0 text-sm tabular-nums text-gray-700 dark:text-gray-300">@{{ event.day ? event.day + ', ' + event.time : event.time }}</span>
                </span>
                {{-- An inline bdi in a block span: a block-level bdi would align itself by its own
                     content's direction, away from the lines around it. --}}
                <span v-if="event.schedule" class="block truncate text-xs text-gray-500 dark:text-gray-400"><bdi>@{{ event.schedule }}</bdi></span>

                <dl class="mt-2">
                    <template v-if="event.checked_in !== null">
                        <div class="flex items-baseline justify-between gap-3 py-0.5">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.checkedIn }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white" :class="bumped['door:' + event.key] ? 'schedule-realtime-bump' : ''">@{{ arrivedOf(event) }}</dd>
                        </div>
                        <div class="my-1.5 h-1.5 rounded-full overflow-hidden bg-gray-100 dark:bg-white/[0.08]" aria-hidden="true">
                            <div class="rt-door-bar h-full rounded-full bg-green-500" :style="{ width: doorShare(event) }"></div>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 py-0.5">
                            <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.last30 }}</dt>
                            <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ number(event.recent) }}</dd>
                        </div>
                    </template>
                    {{-- With an arrival count above, "sold" is already said there, so this line
                         is only worth its row when there is a room to sell it against. --}}
                    <div v-if="event.checked_in === null || event.capacity !== null" class="flex items-baseline justify-between gap-3 py-0.5">
                        <dt class="text-sm text-gray-700 dark:text-gray-300">@{{ event.signups ? msg.registered : msg.sold }}</dt>
                        <dd class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ event.capacity !== null ? countOf(event.sold, event.capacity) : number(event.sold) }}</dd>
                    </div>
                </dl>
            </a>
        </li>
    </ul>

    <p v-if="a.door.more" class="mt-1 text-xs text-gray-500 dark:text-gray-400">@{{ msg.doorMore.replace(':count', number(a.door.more)) }}</p>
</section>
