{{-- The three breakdown cards share one anatomy, so they are one template over `cards`; the unit in
     each header names the window, since nothing else on a card said "30 minutes". Surfaces was a
     fourth: it repeated the board and Trends, and the count of embedded calendars it held moved to
     the Now card. Every row is a
     filter toggle; the only other way to filter is removing a chip. A top page that has an address
     carries the arrow that opens it beside the toggle, never inside it (a link in a button is not
     a link), and a page without one keeps the space, so the bars of one card share a scale. --}}
<section v-for="card in cards" :key="card.id" class="ap-card rounded-xl flex flex-col" :aria-labelledby="'realtime-card-' + card.id">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-2 flex items-baseline justify-between gap-2">
        <h2 :id="'realtime-card-' + card.id" class="shrink-0 text-base font-semibold text-gray-900 dark:text-white">@{{ card.title }}</h2>
        <span class="min-w-0 text-end text-xs text-gray-500 dark:text-gray-400">@{{ card.unit }}</span>
    </div>

    <p v-if="!card.rows.length" class="px-5 pb-5 text-sm text-gray-500 dark:text-gray-400">@{{ card.loading ? msg.loading : msg.nothingYet }}</p>

    <ul v-else class="px-2 pb-2 max-h-80 overflow-y-auto">
        <li v-for="row in (expandedCards[card.id] ? card.rows : card.rows.slice(0, 8))" :key="row.key" class="flex flex-wrap items-center">
            <component :is="row.filterable ? 'button' : 'div'"
                       :type="row.filterable ? 'button' : undefined"
                       @click="row.filterable && toggleFilter(card.filter, row.key)"
                       :aria-pressed="row.filterable ? (isActive(card.filter, row.key) ? 'true' : 'false') : undefined"
                       :aria-label="row.filterable ? fill(isActive(card.filter, row.key) ? msg.removeFilter : msg.showOnly, rowLabel(card, row)) : undefined"
                       class="group relative flex-1 min-w-0 flex items-center gap-3 px-3 py-2 text-sm text-start rounded-md transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]"
                       :class="row.filterable ? 'cursor-pointer' : 'cursor-default text-gray-500 dark:text-gray-400'">
                <span aria-hidden="true" class="absolute inset-y-0.5 start-0 rounded-md transition-all duration-200"
                      :class="isActive(card.filter, row.key) ? 'bg-[var(--brand-blue-a20)]' : (row.filterable ? 'bg-[var(--brand-blue-a10)] group-hover:bg-[var(--brand-blue-a20)]' : 'bg-gray-100 dark:bg-white/[0.04]')"
                      :style="{ width: barWidth(card, row) }"></span>

                <span class="relative flex items-center gap-2 min-w-0 flex-1">
                    <span v-if="card.id === 'countries'" class="iti__flag shrink-0" :class="'iti__' + row.key.toLowerCase()" aria-hidden="true"></span>
                    <svg v-else-if="row.icon && icons[row.icon]" class="w-4 h-4 shrink-0"
                         :class="card.id === 'pages' ? surfaceTone(row.icon).text : 'text-gray-500 dark:text-gray-400'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[row.icon]" />
                    </svg>
                    <span class="realtime-cut truncate" dir="auto" :class="isActive(card.filter, row.key) ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-900 dark:text-gray-100'">@{{ rowLabel(card, row) }}</span>
                    {{-- A top page's area is its icon's colour already: in the narrow card of three, the
                         name gets the room. --}}
                    <span v-if="row.sub" class="hidden sm:inline truncate text-xs text-gray-500 dark:text-gray-400" :class="card.id === 'pages' ? 'xl:hidden' : ''">@{{ row.sub }}</span>
                </span>

                {{-- On its own small ground, so it reads where the bar behind it ends. --}}
                <span v-if="row.now > 0" class="realtime-now-pill relative inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.nNow.replace(':count', fmt(row.now)) }}
                </span>
                <span class="relative tabular-nums text-end text-gray-700 dark:text-gray-300">@{{ fmt(row.views) }}</span>
                <svg v-if="row.filterable" class="relative w-4 h-4 shrink-0 text-gray-400 transition-all duration-200"
                     :class="isActive(card.filter, row.key) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100'"
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons.funnel" />
                </svg>
            </component>
            <template v-if="card.id === 'pages'">
                <x-admin-page-link v-if="row.admin_url" v-bind:href="row.admin_url" v-bind:aria-label="fill(msg.openInAdmin, row.admin_label)" v-bind:title="fill(msg.openInAdmin, row.admin_label)" class="ms-1" />
                <span v-else-if="row.icon === 'gp'" class="w-6 h-6 ms-1 shrink-0" aria-hidden="true"></span>
                <x-public-page-link v-if="row.url" v-bind:href="row.url" v-bind:aria-label="fill(msg.openPublicPage, rowLabel(card, row))" v-bind:title="fill(msg.openPublicPage, rowLabel(card, row))" class="ms-1" />
                <span v-else class="w-6 h-6 ms-1 shrink-0" aria-hidden="true"></span>
            </template>
            {{-- Beside a country, a menu that refuses new sign-ups from it for a while, posted to
                 /admin/blocked's own add and back here. Red while the country is refused. It opens
                 under the row inside the list, not over it: the list scrolls, and a menu laid over
                 it would be cut at its edge. --}}
            <template v-if="card.id === 'countries' && row.blockable">
                <button type="button" class="realtime-block-toggle w-6 h-6 ms-1 shrink-0 inline-flex items-center justify-center rounded-md transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                        :class="row.blocked ? 'text-red-600 dark:text-red-400' : 'text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        @click.stop="blockMenu = blockMenu === row.key ? null : row.key"
                        :aria-expanded="blockMenu === row.key ? 'true' : 'false'"
                        :aria-controls="'realtime-block-' + row.key"
                        :aria-label="fill(row.blocked ? msg.countryRefused : msg.blockCountry, rowLabel(card, row))"
                        :title="fill(row.blocked ? msg.countryRefused : msg.blockCountry, rowLabel(card, row))">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.block" /></svg>
                </button>
                <div v-if="blockMenu === row.key" :id="'realtime-block-' + row.key" class="realtime-block-menu basis-full mx-1 mt-1 mb-2 p-3 rounded-lg bg-gray-50 dark:bg-white/[0.04]"
                     @click.stop @keydown.escape="closeBlockMenu(row.key)">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">@{{ fill(row.blocked ? msg.countryRefused : msg.blockCountry, rowLabel(card, row)) }}</p>
                    <p v-if="row.blocked" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">@{{ refusedUntil(row) }}</p>
                    <form method="POST" action="{{ route('admin.blocked.entry.store') }}" class="mt-2 flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="type" value="country">
                        <input type="hidden" name="value" :value="row.key">
                        <input type="hidden" name="from" value="dashboard">
                        <button v-for="duration in msg.durations" :key="duration.key" type="submit" name="for" :value="duration.key"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:border-red-400 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">@{{ duration.label }}</button>
                    </form>
                    <a href="{{ route('admin.blocked') }}#list" class="mt-2 inline-block text-xs font-medium text-[var(--brand-blue)] hover:underline">@{{ msg.openBlocked }}</a>
                </div>
            </template>
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
