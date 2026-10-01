{{-- The four breakdown cards share one anatomy, so they are one template over `cards`. Every row is a
     filter toggle; the only other way to filter is removing a chip. --}}
<section v-for="card in cards" :key="card.id" class="ap-card rounded-xl flex flex-col" :aria-labelledby="'realtime-card-' + card.id">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-2 flex items-baseline justify-between gap-2">
        <h2 :id="'realtime-card-' + card.id" class="text-base font-semibold text-gray-900 dark:text-white">@{{ card.title }}</h2>
        <span class="text-xs text-gray-500 dark:text-gray-400">@{{ card.unit }}</span>
    </div>

    <p v-if="!card.rows.length" class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nothingYet }}</p>

    <ul v-else class="px-2 pb-2 max-h-80 overflow-y-auto">
        <li v-for="row in (expandedCards[card.id] ? card.rows : card.rows.slice(0, 8))" :key="row.key">
            <component :is="row.filterable ? 'button' : 'div'"
                       :type="row.filterable ? 'button' : undefined"
                       @click="row.filterable && toggleFilter(card.filter, row.key)"
                       :aria-pressed="row.filterable ? (isActive(card.filter, row.key) ? 'true' : 'false') : undefined"
                       :aria-label="row.filterable ? fill(isActive(card.filter, row.key) ? msg.removeFilter : msg.showOnly, rowLabel(card, row)) : undefined"
                       class="group relative w-full flex items-center gap-3 px-3 py-2 text-sm text-start rounded-md transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]"
                       :class="row.filterable ? 'cursor-pointer' : 'cursor-default text-gray-500 dark:text-gray-400'">
                <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md transition-all duration-200"
                      :class="isActive(card.filter, row.key) ? 'bg-[var(--brand-blue-a20)]' : (row.filterable ? 'bg-[var(--brand-blue-a10)] group-hover:bg-[var(--brand-blue-a20)]' : 'bg-gray-100 dark:bg-white/[0.04]')"
                      :style="{ width: barWidth(card, row) }"></span>

                <span class="relative flex items-center gap-2 min-w-0 flex-1">
                    <span v-if="card.id === 'countries'" class="iti__flag shrink-0" :class="'iti__' + row.key.toLowerCase()" aria-hidden="true"></span>
                    <svg v-else-if="row.icon && icons[row.icon]" class="w-4 h-4 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[row.icon]" />
                    </svg>
                    <span class="truncate" :class="isActive(card.filter, row.key) ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-900 dark:text-gray-100'"><bdi>@{{ rowLabel(card, row) }}</bdi></span>
                    <span v-if="row.sub" class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400">@{{ row.sub }}</span>
                </span>

                <span v-if="row.now > 0" class="relative inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.nNow.replace(':count', fmt(row.now)) }}
                </span>
                <span class="relative tabular-nums text-end text-gray-700 dark:text-gray-300">@{{ fmt(row.views) }}</span>
                <svg v-if="row.filterable" class="relative w-4 h-4 shrink-0 text-gray-400 transition-all duration-200"
                     :class="isActive(card.filter, row.key) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100'"
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons.funnel" />
                </svg>
            </component>
        </li>
    </ul>

    <div class="mt-auto px-5 pb-4 flex items-center justify-between gap-2">
        <button v-if="card.rows.length > 8" type="button" @click="expandedCards[card.id] = !expandedCards[card.id]"
                class="text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">
            @{{ expandedCards[card.id] ? msg.showLess : msg.showAll }}
        </button>
        <span v-else></span>
        <span v-if="card.id === 'countries'" class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.geoCredit }}</span>
    </div>
</section>
