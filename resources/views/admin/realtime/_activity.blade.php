{{-- Activity: the last 24 hours from the audit log. The day's counts are buttons that filter the
     feed beneath them. At xl it is a rail beside the overview and the sign-ups, as tall as the two. --}}
<section class="ap-card rounded-xl relative xl:row-span-2 xl:min-h-[32rem]" aria-labelledby="realtime-activity-heading">
    <div class="xl:absolute xl:inset-0 flex flex-col p-4 sm:p-6">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="realtime-activity-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.activity }}</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last24h }}</span>
        </div>

        {{-- The label has a line of its own and wraps: beside the number it was cut to "Sch..." in
             the rail. A count of zero has nothing to list, so it is not a button to press. --}}
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-2 gap-2" role="group" :aria-label="msg.activity">
            <button v-for="stat in p.activity.stats" :key="stat.type" type="button" @click="toggleFeed(stat.type)"
                    :disabled="!stat.count && feed !== stat.type"
                    :aria-pressed="feed === stat.type ? 'true' : 'false'"
                    class="realtime-stat flex flex-col rounded-xl px-3 py-2.5 text-start transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                    :class="[feed === stat.type ? 'realtime-stat-on' : '', bumped[stat.type] ? 'realtime-bump' : '', stat.wide ? 'col-span-2 sm:col-span-4 xl:col-span-2' : '']">
                <span class="flex items-center gap-2">
                    <span class="w-7 h-7 shrink-0 rounded-lg flex items-center justify-center" :class="activityTone(stat.type).bg" aria-hidden="true">
                        <svg class="w-4 h-4" :class="activityTone(stat.type).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons[stat.type] || icons.other" />
                        </svg>
                    </span>
                    <span class="dashboard-stat-value text-2xl font-bold leading-7 tabular-nums text-gray-900 dark:text-white">@{{ fmt(stat.count) }}</span>
                </span>
                <span class="mt-1.5 text-sm font-medium leading-tight text-gray-700 dark:text-gray-300">@{{ stat.label }}</span>
            </button>
        </div>

        <div v-if="feed" class="mt-3">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700 ps-3 pe-1 py-1 text-sm">
                <span class="font-semibold text-gray-900 dark:text-white">@{{ feedLabel }}</span>
                <button type="button" @click="toggleFeed(feed)" :aria-label="fill(msg.removeFilter, feedLabel)"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:text-white dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </span>
        </div>

        <p v-if="!feedItems.length" class="mt-4 text-sm text-gray-500 dark:text-gray-400">@{{ msg.activityEmpty }}</p>

        <ul v-else class="mt-3 flex-1 min-h-0 overflow-y-auto -mx-2 space-y-1">
            <li v-for="(item, index) in feedItems" :key="item.key"
                :class="[index >= 5 && !activityAll ? 'hidden xl:flex' : 'flex', fresh[item.key] ? 'realtime-fresh' : '']"
                class="items-start gap-3 rounded-lg px-2 py-2 transition-all duration-200">
                <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center" :class="activityTone(item.type).bg" aria-hidden="true">
                    <svg class="w-5 h-5" :class="activityTone(item.type).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.type] || icons.other" />
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <component :is="item.url ? 'a' : 'p'" :href="item.url || undefined"
                               class="block text-sm"
                               :class="[item.recent ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400', item.url ? 'hover:underline' : '']">@{{ item.text }}</component>
                    {{-- The time sits under the sentence, not beside it: in the rail a time column
                         left the sentence about 130px and most of them ran to three lines. --}}
                    <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="tabular-nums">@{{ ago(item.ago) }}</span>
                        {{-- A button only while that person is in the Visitors list to open. --}}
                        <component v-if="item.on_site" :is="canShow(item.person_id) ? 'button' : 'span'" :type="canShow(item.person_id) ? 'button' : undefined"
                                   @click="showPerson(item.person_id)"
                                   class="inline-flex flex-wrap items-center gap-x-2 gap-y-0.5 rounded text-start focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                                   :class="[item.on_site === 'now' ? 'text-green-700 dark:text-green-400' : '', canShow(item.person_id) ? 'hover:underline' : '']">
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap"><span v-if="item.on_site === 'now'" class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ item.on_site === 'now' ? msg.onSiteNow : msg.seenRecently }}</span>
                            <span v-if="item.surface" class="inline-flex items-center gap-1 whitespace-nowrap text-gray-600 dark:text-gray-400"><svg class="w-3.5 h-3.5 shrink-0" :class="surfaceTone(item.surface).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.surface] || icons.other" /></svg>@{{ item.surface_label }}</span>
                        </component>
                    </p>
                </div>
            </li>
        </ul>

        <button v-if="feedItems.length > 5" type="button" @click="activityAll = !activityAll"
                class="xl:hidden mt-3 self-center text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">
            @{{ activityAll ? msg.showLess : msg.showAll }}
        </button>
    </div>
</section>
