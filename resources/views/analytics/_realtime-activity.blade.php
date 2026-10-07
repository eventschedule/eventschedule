{{-- Activity: what people did on the viewer's schedules in the last 24 hours, newest first
     (ScheduleActivity::rail()). The day's counts are buttons that filter the list beneath them.

     Its own markup, like the rest of the tab: /admin/realtime has a rail too, and none of that
     page belongs in a document an organizer can open.

     A row is two lines. The thing first (an event, or what happened to a schedule), with the
     money at the end of the line where there is some; under it what it is, made of labels and
     numbers ("Sale · Tickets 2 · 5 min. ago"). No row is a sentence with a count inside it, so no
     language needs a plural form, and no row names anyone: it links to the list page where the
     person is.

     From a laptop up the rail is as tall as its rows, stays in view while the left side scrolls
     and scrolls inside itself on a long day (`.rt-rail` in analytics/_realtime). Below that it
     shows five rows and a way to the rest.

     Titles are in <bdi>, so a Hebrew event in an English rail keeps its own direction, and money
     is `dir="ltr"`, so a signed or coded amount does not turn around in a Hebrew one. --}}
<section class="rt-rail ap-card rounded-xl flex flex-col p-4 sm:p-5" aria-labelledby="schedule-realtime-activity">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5">
        <h2 id="schedule-realtime-activity" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.activity }}</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last24h }}</span>
    </div>

    {{-- A day with nothing in it has no counts to press, and four zeros say less than one line. --}}
    {{-- Four counts, or five on a schedule that takes bookings. The fifth takes the whole of its
         row, so the grid never ends on an empty cell. --}}
    <div v-if="hasActivity" class="mt-3 grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-2 gap-2" role="group" :aria-label="msg.activity">
        <button v-for="(stat, index) in a.stats" :key="stat.type" type="button" @click="toggleFeed(stat.type)"
                :disabled="!stat.count && feed !== stat.type"
                :aria-pressed="feed === stat.type ? 'true' : 'false'"
                :data-feed="stat.type"
                class="rt-stat flex flex-col rounded-xl px-3 py-2.5 text-start transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                :class="[feed === stat.type ? 'rt-stat-on' : '', bumped['tile:' + stat.type] ? 'rt-pulse' : '', a.stats.length % 2 && index === a.stats.length - 1 ? 'col-span-2 sm:col-span-4 xl:col-span-2' : '']">
            <span class="dashboard-stat-value text-2xl font-bold leading-7 tabular-nums" :class="stat.count ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'">@{{ number(stat.count) }}</span>
            {{-- One long word in some languages ("Registreerumised"), in a cell about 100px wide. --}}
            <span class="mt-1 text-sm font-medium leading-tight break-words text-gray-700 dark:text-gray-300" style="overflow-wrap: anywhere">@{{ msg.tile[stat.type] }}</span>
        </button>
    </div>

    <div v-if="feed" class="mt-3">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700 ps-3 pe-1 py-1 text-sm">
            <span class="font-semibold text-gray-900 dark:text-white">@{{ msg.tile[feed] }}</span>
            <button type="button" @click="toggleFeed(feed)" :aria-label="msg.removeFilter.replace(':label', msg.tile[feed])"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:text-white dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </span>
    </div>

    {{-- "Nothing happened" is a claim. When the read itself failed, say that instead. --}}
    <p v-if="!feedItems.length" class="mt-3 text-sm text-gray-500 dark:text-gray-400" style="text-wrap: pretty">@{{ a.failed ? msg.activityFailed : msg.activityEmpty }}</p>

    {{-- While a mouse or the keyboard is in the list, a refresh waits: a row must not move from
         under a pointer that is about to press it. A mouse only (a tap on a phone is an "enter"
         with no "leave" after it), keyboard focus only (not the focus a mouse click leaves on a
         link), and never for longer than a minute of stillness: see hold in
         analytics/_realtime-script. --}}
    <ul v-else class="rt-rail-list mt-2 -mx-2" @pointerenter="onPointer(true, $event)" @pointerleave="onPointer(false, $event)" @pointermove="onPointerMove" @focusin="onFocusIn" @focusout="onFocusOut" @keydown="onHoldKey">
        <li v-for="(item, index) in feedItems" :key="item.key" :data-kind="item.kind"
            :class="[index >= 5 && !activityAll ? 'hidden xl:block' : '', freshRows[item.key] ? 'schedule-realtime-fresh' : '']"
            class="rounded-lg">
            <component :is="item.url ? 'a' : 'div'" :href="item.url || undefined"
                       class="flex items-start gap-3 rounded-lg px-2 py-2.5"
                       :class="item.url ? 'transition-all duration-200 hover:bg-gray-50 dark:hover:bg-white/[0.04] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]' : ''">
                <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center" :class="tone(item.kind).bg" aria-hidden="true">
                    <svg class="w-5 h-5" :class="tone(item.kind).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="activityIcons[item.kind]" />
                    </svg>
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-baseline justify-between gap-3">
                        <bdi class="min-w-0 truncate text-sm font-medium text-gray-900 dark:text-white">@{{ item.title || msg.kind[item.kind] }}</bdi>
                        <span v-if="item.amount" dir="ltr" class="shrink-0 text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ item.amount }}</span>
                    </span>
                    {{-- One line, always: what it is on one side, cut short if the rail is narrow
                         (the schedule's name goes first), and when on the other, never cut. --}}
                    <span class="mt-0.5 flex items-baseline justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span class="min-w-0 truncate">
                            <template v-for="(part, at) in lineTwo(item)" :key="at">
                                <span v-if="at" aria-hidden="true"> &middot; </span>
                                <bdi v-if="part.name">@{{ part.text }}</bdi>
                                <span v-else>@{{ part.text }}</span>
                            </template>
                        </span>
                        <span class="shrink-0 whitespace-nowrap tabular-nums">@{{ since(item.ago) }}</span>
                    </span>
                </span>
            </component>
        </li>
    </ul>

    {{-- Drawn only when it holds something: with five rows or fewer and no more in the day it
         was an empty box under the list. --}}
    <div v-if="feedItems.length > 5 || moreThanShown" class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
        {{-- Below a laptop the list starts at five rows, and "the newest 20" would not be what is
             on screen: there the sentence waits until every row is shown. It also waits while
             rows are held back (moreThanShown): the counts have moved on and the rows have not. --}}
        <p v-if="moreThanShown" class="text-xs text-gray-500 dark:text-gray-400" :class="activityAll ? '' : 'hidden xl:block'">@{{ newest(feedItems.length, feedTotal) }}</p>
        {{-- At the end of the line whether the sentence is beside them or not. --}}
        <span class="ms-auto flex items-center gap-x-4">
            <button v-if="feedItems.length > 5" type="button" @click="activityAll = !activityAll"
                    class="xl:hidden py-1.5 text-sm font-medium text-[var(--brand-blue)] hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                @{{ activityAll ? msg.showLess : msg.showAll }}
            </button>
            {{-- Where the names are. Only while the list is one that Sales holds the rest of. --}}
            <a v-if="moreThanShown && (!feed || feed === 'sale' || feed === 'registration')" :href="salesUrl"
               class="py-1.5 text-sm font-medium text-[var(--brand-blue)] hover:underline rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ msg.allSales }}</a>
        </span>
    </div>
</section>
