{{-- Activity: the last 24 hours from the audit log. The day's counts are one row of chips that filter
     the feed beneath them: as big tiles they repeated the Trends figures above, took 231px of the
     rail and left a lonely "1 Upgrades" tile on a row of its own. At xl it is a rail beside Sign-ups,
     as tall as that card; a feed longer than the rail says how much more there is and opens it. --}}
{{-- A quiet day has nothing to fit: the rail is then its own height, not a 32rem card of blank. --}}
<section class="realtime-rail ap-card rounded-xl relative" :class="feedItems.length ? 'xl:min-h-[32rem]' : ''" aria-labelledby="realtime-activity-heading">
    <div class="flex flex-col p-4 sm:p-6" :class="activityAll || !feedItems.length ? '' : 'xl:absolute xl:inset-0'">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="realtime-activity-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.activity }}</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last24h }}<template v-if="p.filtered"> · @{{ msg.notFiltered }}</template></span>
        </div>

        {{-- A count of zero has nothing to list, so it is not a chip to press. --}}
        <div class="mt-3 flex flex-wrap gap-1.5" role="group" :aria-label="msg.activity">
            <button type="button" @click="setFeed(null)" :aria-pressed="feed ? 'false' : 'true'"
                    class="realtime-chip" :class="feed ? '' : 'realtime-chip-on'">@{{ msg.all }}</button>
            <button v-for="stat in p.activity.stats" :key="stat.type" type="button" @click="toggleFeed(stat.type)"
                    :disabled="!stat.count && feed !== stat.type"
                    :aria-pressed="feed === stat.type ? 'true' : 'false'"
                    class="realtime-chip" :class="[feed === stat.type ? 'realtime-chip-on' : '', bumped[stat.type] ? 'realtime-bump' : '']">
                <svg class="w-3.5 h-3.5 shrink-0" :class="activityTone(stat.type).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons[stat.type] || icons.other" />
                </svg>
                <span>@{{ stat.label }}</span>
                <span class="font-semibold tabular-nums text-gray-900 dark:text-white">@{{ fmt(stat.count) }}</span>
            </button>
        </div>

        <p v-if="!feedItems.length" class="mt-4 text-sm text-gray-500 dark:text-gray-400">@{{ msg.activityEmpty }}</p>

        <div v-else class="relative mt-3 flex-1 min-h-0 flex flex-col">
            <ul ref="feedList" class="flex-1 min-h-0 -mx-2 space-y-1" :class="activityAll ? '' : 'realtime-clip'">
                <li v-for="(item, index) in feedItems" :key="item.key"
                    :class="[index >= 5 && !activityAll ? 'hidden xl:flex' : 'flex', fresh[item.key] ? 'realtime-fresh' : '']"
                    class="items-start gap-3 rounded-lg px-2 py-2 transition-all duration-200">
                    <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center" :class="activityTone(item.type).bg" aria-hidden="true">
                        <svg class="w-5 h-5" :class="activityTone(item.type).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.type] || icons.other" />
                        </svg>
                    </span>
                    <div class="flex-1 min-w-0">
                        {{-- Every row in the one ink: greyed after half an hour, a row still marked "On
                             the site now" looked disabled. A dot by the time says it is new instead. --}}
                        <component :is="item.url ? 'a' : 'p'" :href="item.url || undefined"
                                   class="block text-sm text-gray-900 dark:text-white"
                                   :class="item.url ? 'hover:underline' : ''">@{{ item.text }}</component>
                        {{-- The time sits under the sentence, not beside it: in the rail a time column
                             left the sentence about 130px and most of them ran to three lines. --}}
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1.5 tabular-nums"><span v-if="item.recent" class="w-1.5 h-1.5 rounded-full bg-[var(--brand-blue)]" aria-hidden="true"></span>@{{ ago(item.ago) }}</span>
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
                    {{-- The sentence opens the schedule in the admin; the arrow at the row's end, the
                         event's or the schedule's guest page (components/public-page-link). After the
                         last word it wrapped to a line of its own whenever the sentence filled one. --}}
                    <x-public-page-link v-if="item.public_url" v-bind:href="item.public_url" v-bind:aria-label="fill(msg.openPublicPage, item.public_label)" v-bind:title="fill(msg.openPublicPage, item.public_label)" />
                </li>
            </ul>
            {{-- At xl the rail is as tall as Sign-ups: what does not fit fades out, and says so. --}}
            <div v-if="hiddenBelow.feed && !activityAll" class="realtime-fade hidden xl:block" aria-hidden="true"></div>
        </div>

        <button v-if="feedItems.length > 5 || activityAll" type="button" @click="activityAll = !activityAll"
                class="mt-3 self-center text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded"
                :class="activityAll || hiddenBelow.feed ? '' : 'xl:hidden'">
            <span class="xl:hidden">@{{ activityAll ? msg.showLess : msg.showAll }}</span>
            <span class="hidden xl:inline">@{{ activityAll ? msg.showLess : nMore(hiddenBelow.feed) }}</span>
        </button>
    </div>
</section>
