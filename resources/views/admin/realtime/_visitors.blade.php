{{-- The visitors who accepted cookies, right now and earlier in the last 30 minutes. A row expands
     in place (one at a time) to that person's last hour. Rows never filter: one tap target each. --}}
@php
    $segShell = 'inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1';
    $segItem = 'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
    $rowGrid = 'grid grid-cols-[minmax(0,1fr)_auto] md:grid-cols-[minmax(0,14rem)_minmax(0,1fr)_7rem] xl:grid-cols-[minmax(0,16rem)_minmax(0,1fr)_10rem_7rem] gap-x-4 items-start';
@endphp
<section class="ap-card rounded-xl" aria-labelledby="realtime-visitors-heading">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <h2 id="realtime-visitors-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.visitors }}</h2>
            <p v-if="p.overview.phrases.consent" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">@{{ p.overview.phrases.consent }}</p>
        </div>
        <div class="{{ $segShell }}" role="group" :aria-label="msg.visitors">
            <button v-for="option in whoOptions" :key="option.value" type="button" @click="setWho(option.value)"
                    :aria-pressed="who === option.value ? 'true' : 'false'"
                    class="{{ $segItem }} inline-flex items-center gap-1"
                    :class="who === option.value
                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">
                <span>@{{ option.label }}</span><span class="font-normal tabular-nums text-gray-500 dark:text-gray-400">@{{ fmt(option.count) }}</span>
            </button>
        </div>
    </div>

    <div v-if="!visitorRows.now.length && !visitorRows.earlier.length" class="px-5 py-10 flex flex-col items-center text-center">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.users" />
        </svg>
        <template v-if="p.state === 'waiting'">
            <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.waitingTitle }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.waitingBody }}</p>
        </template>
        <template v-else-if="p.filtered">
            <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.noMatch }}</p>
            <button type="button" @click="clearFilters" class="mt-3 text-sm font-medium text-[var(--brand-blue)] hover:underline">@{{ msg.clearFilters }}</button>
        </template>
        <template v-else>
            <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.noVisitors30 }}</p>
            <p v-if="p.overview.last_seen_ago !== null" class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.lastVisitor.replace(':time', ago(p.overview.last_seen_ago, true)) }}</p>
            <p v-if="!admins" class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.adminsHiddenTip }}</p>
        </template>
    </div>

    <div v-else @pointerenter="onPointer(true, $event)" @pointerleave="onPointer(false, $event)" @focusin="onFocusIn" @focusout="onFocusOut">
        <template v-for="group in ['now', 'earlier']" :key="group">
            <div v-if="group === 'now' || visitorRows.earlier.length"
                 class="{{ $rowGrid }} px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                 style="background: var(--ap-tint-sunken)">
                <span>@{{ group === 'now' ? msg.rightNowGroup : msg.earlierGroup }} · @{{ fmt(group === 'now' ? p.visitors.now_total : p.visitors.earlier_total) }}</span>
                <span class="hidden md:block">@{{ msg.colPage }}</span>
                <span class="hidden xl:block">@{{ msg.colSource }}</span>
                <span class="text-end">@{{ msg.colActive }}</span>
            </div>

            <p v-if="group === 'now' && !visitorRows.now.length" class="px-4 sm:px-5 py-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.noOneNow }}</p>

            <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                <li v-for="person in visitorRows[group]" :key="person.id" :class="fresh[person.id] ? 'realtime-fresh' : ''">
                    <button type="button" @click="toggleExpand(person)" :aria-expanded="expanded === person.id ? 'true' : 'false'" :aria-controls="panelId(person)"
                            class="{{ $rowGrid }} w-full text-start px-4 sm:px-5 py-3 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-black/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]">
                        {{-- Visitor --}}
                        <span class="flex items-start gap-3 min-w-0">
                            <span v-if="person.kind === 'user'" aria-hidden="true"
                                  class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-semibold bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]">@{{ person.initials }}</span>
                            <span v-else aria-hidden="true" class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700">
                                <span v-if="person.country" class="iti__flag" :class="'iti__' + person.country.toLowerCase()"></span>
                                <svg v-else class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.wp" /></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-sm font-medium truncate" :class="person.now ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'">
                                        <bdi>@{{ person.kind === 'user' ? (person.name || person.email) : (countryName(person.country) || msg.anonymous) }}</bdi>
                                    </span>
                                    <span v-for="badge in person.badges" :key="badge"
                                          class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap"
                                          :class="badge === 'new' ? 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'">@{{ badgeLabel(badge) }}</span>
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">
                                    <bdi v-if="person.kind === 'user'" dir="ltr">@{{ person.email }}</bdi>
                                    <template v-else>@{{ deviceLine(person) }}</template>
                                </span>
                                <span v-if="person.stuck_text" class="mt-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">@{{ person.stuck_text }}</span>
                                {{-- Below md the page moves under the name. --}}
                                <span class="md:hidden mt-1 block text-xs text-gray-600 dark:text-gray-300 truncate"><bdi>@{{ person.page.label }}</bdi> · @{{ person.page.surface_label }}</span>
                            </span>
                        </span>

                        {{-- Page --}}
                        <span class="hidden md:block min-w-0">
                            <span class="block text-sm truncate" :class="person.now ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300'"><bdi>@{{ person.page.label }}</bdi></span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate">@{{ person.page.surface_label }}</span>
                        </span>

                        {{-- Came from --}}
                        <span class="hidden xl:block min-w-0">
                            <span class="block text-sm text-gray-700 dark:text-gray-300 truncate"><bdi>@{{ person.source.name || person.source.channel_label || '' }}</bdi></span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 truncate"><bdi>@{{ person.source.campaign || (person.source.name ? person.source.channel_label : '') }}</bdi></span>
                        </span>

                        {{-- Active --}}
                        <span class="text-end">
                            <template v-if="person.now">
                                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.now }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">@{{ msg.onPage.replace(':time', duration(person.on_page_secs)) }}</span>
                            </template>
                            <span v-else class="text-sm text-gray-500 dark:text-gray-400">@{{ msg.left.replace(':time', ago(person.left_ago, true)) }}</span>
                        </span>
                    </button>

                    {{-- Expanded: the person's last hour. A pressed well, no height animation. --}}
                    <div v-if="expanded === person.id" :id="panelId(person)" class="ps-4 sm:ps-16 pe-4 sm:pe-5 pb-4 pt-1 transition-opacity duration-150"
                         style="background: var(--ap-tint-sunken); box-shadow: var(--ap-inset-pressed)">
                        <p v-if="!details" class="pt-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.loading }}</p>
                        <div v-if="details && (details.email || details.chat_url)" class="flex flex-wrap gap-2 pt-3">
                            <a v-if="details.email" :href="'mailto:' + details.email"
                               class="ap-secondary-btn inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ msg.email }}</a>
                            <a v-if="details.chat_url" :href="details.chat_url"
                               class="ap-secondary-btn inline-flex items-center px-3 py-1.5 text-sm font-semibold rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">@{{ msg.openChat }}</a>
                        </div>
                        <div v-if="details && details.schedules.length" class="pt-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@{{ msg.schedules }}</p>
                            <ul class="mt-1 flex flex-wrap gap-x-4 gap-y-1">
                                <li v-for="schedule in details.schedules" :key="schedule.admin_url" class="text-sm">
                                    <x-link v-bind:href="schedule.admin_url"><bdi>@{{ schedule.name }}</bdi></x-link>
                                    <span v-if="schedule.plan" class="ms-1 text-xs text-gray-500 dark:text-gray-400">@{{ schedule.plan }}</span>
                                </li>
                            </ul>
                        </div>
                        <ol v-if="details" class="pt-3 max-h-72 overflow-y-auto space-y-1.5">
                            <li v-for="entry in details.timeline" :key="entry.key" class="flex items-baseline gap-3 text-sm">
                                <span class="w-16 shrink-0 whitespace-nowrap text-xs tabular-nums text-gray-500 dark:text-gray-400">@{{ clock(entry.at_ago) }}</span>
                                <template v-if="entry.marker">
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-gray-900 dark:text-white">
                                        <svg class="w-4 h-4 text-[var(--brand-blue)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons.signup" /></svg>
                                        @{{ entry.label }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="min-w-0 flex-1 truncate text-gray-700 dark:text-gray-300">
                                        <x-link v-if="entry.url" v-bind:href="entry.url" target="_blank" class="inline-block max-w-full truncate align-bottom"><bdi>@{{ entry.label }}</bdi></x-link>
                                        <bdi v-else>@{{ entry.label }}</bdi>
                                        <span class="text-xs text-gray-500 dark:text-gray-400"> · @{{ entry.surface_label }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs tabular-nums" :class="entry.current ? 'text-green-700 dark:text-green-400 font-medium' : 'text-gray-500 dark:text-gray-400'">@{{ entry.current ? msg.now : duration(entry.secs) }}</span>
                                </template>
                            </li>
                        </ol>
                    </div>
                </li>
            </ul>
        </template>

        <div v-if="p.visitors.more_text || p.visitors.capped_text || p.truncated" class="px-5 py-3 text-center border-t border-gray-100 dark:border-white/[0.06]">
            <button v-if="p.visitors.more_text" type="button" @click="showAllVisitors"
                    class="text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">@{{ p.visitors.more_text }}</button>
            <p v-if="p.visitors.capped_text" class="text-xs text-gray-500 dark:text-gray-400">@{{ p.visitors.capped_text }}</p>
            <p v-if="p.truncated" class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.truncated }}</p>
        </div>
    </div>
</section>
