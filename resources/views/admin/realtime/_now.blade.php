{{-- Right now: how many people are on the site, and where each of them is. The headline counts only
     visitors who accepted cookies (the others send one page view and no heartbeat), so the traffic
     it leaves out is said beside it, with page views per minute in the same two colours: this card
     took in the old "Last 30 minutes" card, whose consent split sat three cards below the number.

     Under it, one column per part of the site, its header the Surface filter, each page somebody is
     on with who is on it: a face opens that person's row in Visitors, the page's name filters the
     whole page to it (as a row of Top pages does), and the arrow opens the page itself. Sign up &
     log in is a doorway, never a column of its own: it stacks under the app (boardColumns), which
     left a column half empty beside a marketing site that cut its page names short.
     RealtimeDashboard::nowBoard() builds it. --}}
<section id="realtime-now" class="ap-card rounded-xl p-4 sm:p-6" aria-labelledby="realtime-now-heading">
    <div class="realtime-now-head" :class="splitTotal > 0 ? '' : 'is-quiet'">
        {{-- One line: the icon, the number, and what it counts with how it splits. --}}
        <div class="flex items-center gap-4 min-w-0">
            <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons.signal" />
                </svg>
            </div>
            <span class="dashboard-stat-value text-4xl font-bold leading-none tabular-nums text-gray-900 dark:text-white">@{{ fmt(p.overview.now) }}</span>
            <div class="min-w-0">
                <h2 id="realtime-now-heading" class="text-base font-semibold text-gray-900 dark:text-white">
                    @{{ msg.visitorsRightNow }}
                    <span v-if="p.filtered" class="ms-1 text-sm font-normal text-gray-500 dark:text-gray-400">@{{ msg.ofTotal.replace(':count', fmt(p.overview.now_total)) }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <template v-if="p.overview.now > 0">@{{ p.overview.phrases.split }}</template>
                    <template v-else-if="p.overview.last_seen_ago !== null">@{{ msg.lastVisitor.replace(':time', ago(p.overview.last_seen_ago, true)) }}</template>
                    <template v-else>@{{ msg.noVisitorsHour }}</template>
                </p>
            </div>
        </div>

        {{-- Page views per minute, both kinds of visitor. --}}
        <div class="realtime-now-chart min-w-0">
            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-xs font-medium text-gray-600 dark:text-gray-400">@{{ msg.viewsPerMinute }}</h3>
                {{-- The chart marks each sign-up with this dot, above the minute it happened in. --}}
                <span v-if="hasSignupMarks" class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.signedUpMark }}
                </span>
            </div>
            <div class="relative h-24">
                <div class="absolute inset-0"><canvas ref="chart" role="img" :aria-label="chartSummary"></canvas></div>
            </div>
        </div>

        {{-- What the headline leaves out. The two swatches are the chart's two bar colours. --}}
        <div v-if="splitTotal > 0" class="realtime-now-consent min-w-0">
            <div class="flex h-2 rounded-full overflow-hidden bg-gray-300 dark:bg-gray-600" role="img" :aria-label="splitLabel">
                <span class="bg-[var(--brand-blue)] transition-all duration-200" :style="{ width: splitShare + '%' }"></span>
            </div>
            <p class="mt-2 text-xs leading-snug text-gray-700 dark:text-gray-300">@{{ p.overview.phrases.declined }}</p>
            <p class="mt-1.5 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 shrink-0 rounded-sm bg-[var(--brand-blue)]" aria-hidden="true"></span>@{{ msg.acceptedCookies }}</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 shrink-0 rounded-sm bg-gray-300 dark:bg-gray-600" aria-hidden="true"></span>@{{ msg.notAccepted }}</span>
            </p>
        </div>
    </div>

    {{-- With nobody on the site the line under the number already says so; three columns saying
         "Nobody here right now" said it again. A filter keeps the board, whose headers undo it. --}}
    <div v-if="boardHasAnyone || p.filtered" class="realtime-board mt-5 grid gap-3" :class="'is-' + boardColumns.length">
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
                        <x-public-page-link v-if="page.url" v-bind:href="page.url" v-bind:aria-label="fill(msg.openPublicPage, page.label)" v-bind:title="fill(msg.openPublicPage, page.label)" />
                        <span v-else-if="area.key === 'wp' || area.key === 'gp'" class="w-6 h-6 shrink-0" aria-hidden="true"></span>
                    </li>
                </ul>
                <p v-else class="px-3 pb-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.nobodyHere }}</p>
                <p v-if="boardMore(area.key)" class="px-3 pb-3 text-xs text-gray-500 dark:text-gray-400">@{{ boardMore(area.key) }}</p>
            </div>
        </div>
    </div>

    {{-- Calendars embedded on other websites have no page here to sit on, and the Surfaces card
         that counted them is gone. --}}
    <p v-if="p.overview.phrases.embed" class="mt-4 flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.embed" /></svg>@{{ p.overview.phrases.embed }}
    </p>
</section>
