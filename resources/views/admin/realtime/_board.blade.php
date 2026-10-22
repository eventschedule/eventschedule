{{-- Where each visitor right now is, under the Now card (_now): one column per part of the site,
     its header the Surface filter, each page somebody is on with who is on it. A face opens that
     person's row in Visitors, the page's name filters the whole page to it (as a row of Top pages
     does), the sliders open a schedule's page in the admin, and the arrow opens the page itself.
     Sign up & log in is a doorway, never a column of its own: it stacks under the app
     (boardColumns), which left a column half empty beside a marketing site that cut its page names
     short. RealtimeDashboard::nowBoard() builds it.

     A card of its own since the dashboard put Revenue beside the Now card: in two thirds of the
     width, three columns cut a page's name to a few letters once the admin link joined the arrow.

     With nobody on the site the line under the number already says so; three columns saying
     "Nobody here right now" said it again. A filter keeps the board, whose headers undo it. --}}
<section v-if="boardHasAnyone || p.filtered" id="realtime-board" class="ap-card rounded-xl p-4 sm:p-6" :aria-label="msg.visitorsRightNow">
    <div class="realtime-board grid gap-3" :class="'is-' + boardColumns.length">
        <div v-for="column in boardColumns" :key="column[0].key" class="realtime-board-col flex flex-col gap-3 min-w-0">
            <div v-for="area in column" :key="area.key" class="realtime-area rounded-xl flex flex-col min-w-0"
                 :class="[isActive('surface', area.key) ? 'realtime-area-on' : '', filters.surface && !isActive('surface', area.key) ? 'realtime-area-dim' : '']">
                <button type="button" @click="toggleFilter('surface', area.key)" :aria-pressed="isActive('surface', area.key) ? 'true' : 'false'"
                        class="realtime-area-head w-full flex items-center gap-2.5 px-3 pt-3 pb-2 rounded-xl text-start transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]">
                    <span class="w-8 h-8 shrink-0 rounded-lg flex items-center justify-center" :class="surfaceTone(area.key).bg" aria-hidden="true">
                        <svg class="w-4 h-4" :class="surfaceTone(area.key).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="icons[area.key] || icons.other" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-300">@{{ area.label }}</span>
                    <span class="dashboard-stat-value text-2xl font-bold leading-7 tabular-nums" :class="area.count ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'">@{{ fmt(area.count) }}</span>
                </button>

                <ul v-if="boardPages(area.key).length" class="px-1.5 pb-2">
                    <li v-for="page in boardPages(area.key)" :key="page.key" class="realtime-board-row flex items-center gap-2 rounded-lg px-1.5 py-0.5"
                        :class="isActive('page', page.key) ? 'realtime-board-row-on' : ''">
                        <button type="button" @click="toggleFilter('page', page.key)" :aria-pressed="isActive('page', page.key) ? 'true' : 'false'"
                                :title="page.label" :aria-label="fill(isActive('page', page.key) ? msg.removeFilter : msg.showOnly, page.label)" dir="auto"
                                class="realtime-cut min-w-0 flex-1 py-1 text-sm truncate rounded hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                                :class="isActive('page', page.key) ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-900 dark:text-gray-100'">@{{ page.label }}</button>
                        {{-- Who is on it. A face is a button only while that person has a row in
                             Visitors to open (the list is capped, and may be filtered). --}}
                        <span class="realtime-faces shrink-0 flex items-center">
                            <component v-for="face in page.people" :key="face.id" :is="canShow(face.id) ? 'button' : 'span'"
                                       :type="canShow(face.id) ? 'button' : undefined" @click="showPerson(face.id)"
                                       :title="faceTitle(face)" :aria-label="faceTitle(face)" :role="canShow(face.id) ? undefined : 'img'"
                                       class="realtime-face" :class="face.kind === 'user' ? 'realtime-face-user' : ''">
                                <template v-if="face.kind === 'user'">@{{ face.initials }}</template>
                                <span v-else-if="face.country" class="iti__flag" :class="'iti__' + face.country.toLowerCase()" aria-hidden="true"></span>
                                <svg v-else class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.wp" /></svg>
                            </component>
                        </span>
                        <span class="w-6 shrink-0 text-end text-sm font-semibold tabular-nums text-gray-900 dark:text-white">@{{ fmt(page.count) }}</span>
                        {{-- A schedule's page: its schedule in the admin, then the page itself. --}}
                        <x-admin-page-link v-if="page.admin_url" v-bind:href="page.admin_url" v-bind:aria-label="fill(msg.openInAdmin, page.admin_label)" v-bind:title="fill(msg.openInAdmin, page.admin_label)" />
                        <span v-else-if="area.key === 'gp'" class="w-6 h-6 shrink-0" aria-hidden="true"></span>
                        <x-public-page-link v-if="page.url" v-bind:href="page.url" v-bind:aria-label="fill(msg.openPublicPage, page.label)" v-bind:title="fill(msg.openPublicPage, page.label)" />
                        <span v-else-if="area.key === 'wp' || area.key === 'gp'" class="w-6 h-6 shrink-0" aria-hidden="true"></span>
                    </li>
                </ul>
                <p v-else class="px-3 pb-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nobodyHere }}</p>
                <p v-if="boardMore(area.key)" class="px-3 pb-3 text-xs text-gray-500 dark:text-gray-400">@{{ boardMore(area.key) }}</p>
            </div>
        </div>
    </div>
</section>
