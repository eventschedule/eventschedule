{{-- Sign-ups: everyone who signed up in the last 24 hours, when they came, and how far each has
     got since. Read from the audit log and the accounts themselves, so it needs no tracking and
     ignores the page's filters, like Activity. --}}
@php
    // "Came from" is a column where the card is wide enough for four, and a line under the email
    // where it is not: on a phone, and beside the rail between xl and 1400px.
    $signupCols = 'grid-cols-[minmax(0,1fr)_auto] md:grid-cols-[minmax(0,1.35fr)_minmax(0,0.8fr)_minmax(0,1.15fr)_5.5rem] xl:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)_5.5rem] min-[1400px]:grid-cols-[minmax(0,1.35fr)_minmax(0,0.8fr)_minmax(0,1.15fr)_5.5rem] gap-x-4';
    $sourceColumn = 'hidden md:block xl:hidden min-[1400px]:block';
    $sourceInline = 'md:hidden xl:block min-[1400px]:hidden';
@endphp
<section class="ap-card rounded-xl xl:col-span-2 flex flex-col" aria-labelledby="realtime-signups-heading">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3">
        <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
            <div class="flex items-baseline gap-2 min-w-0">
                <h2 id="realtime-signups-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.signups }}</h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last24h }}</span>
            </div>
            {{-- When they came: one bar per hour, the hour that just passed last. --}}
            <div v-if="signups.rows.length" class="w-full sm:w-56 shrink-0" role="img" :aria-label="msg.perHour">
                <div class="flex items-end gap-0.5 h-7">
                    <span v-for="(bar, index) in hourBars" :key="index" class="flex-1 flex items-end h-full" :title="bar.title">
                        <span class="w-full rounded-sm transition-all duration-200"
                              :class="bar.count ? 'bg-[var(--brand-blue)]' : 'bg-gray-200 dark:bg-gray-700'"
                              :style="{ height: bar.count ? bar.height + '%' : '2px' }"></span>
                    </span>
                </div>
                <div class="mt-1 flex justify-between text-[11px] tabular-nums text-gray-500 dark:text-gray-400" aria-hidden="true">
                    <span>@{{ ago(86399, true) }}</span><span>@{{ msg.axisNow }}</span>
                </div>
            </div>
        </div>

        {{-- How far these people have got: one cell per step, its bar the share who reached it.
             Cells, not a sentence: as a sentence it wrapped and left an arrow starting a line. --}}
        <ol v-if="signups.rows.length" class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-3">
            <li v-for="(step, index) in signups.steps" :key="step.key" class="min-w-0">
                <p class="flex items-baseline gap-1.5">
                    <span class="text-lg font-semibold leading-6 tabular-nums text-gray-900 dark:text-white">@{{ fmt(step.count) }}</span>
                    <span v-if="index > 0" class="text-xs tabular-nums text-gray-500 dark:text-gray-400">@{{ stepShare(step) }}%</span>
                </p>
                <p class="text-xs leading-tight text-gray-700 dark:text-gray-300">@{{ step.label }}</p>
                <div class="mt-1.5 h-1.5 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700" aria-hidden="true">
                    <div class="h-full rounded-full bg-[var(--brand-button-bg)] transition-all duration-200" :style="{ width: stepShare(step) + '%' }"></div>
                </div>
            </li>
        </ol>
    </div>

    <template v-if="signups.rows.length">
        <div class="hidden md:grid {{ $signupCols }} px-4 sm:px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
             style="background: var(--ap-tint-sunken)">
            <span>@{{ msg.colName }}</span>
            <span class="{{ $sourceColumn }}">@{{ msg.colSource }}</span>
            <span>@{{ msg.colProgress }}</span>
            <span class="text-end">@{{ msg.signedUpMark }}</span>
        </div>

        {{-- At xl the list scrolls inside the card, so an arrival never pushes the Visitors list
             while the admin is pointing at it. Below xl it is five rows and a Show all. --}}
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06] xl:max-h-[26rem] overflow-y-auto border-t border-gray-100 dark:border-white/[0.06] md:border-t-0">
            <li v-for="(row, index) in signups.rows" :key="row.key" class="{{ $signupCols }} px-4 sm:px-5 py-3 items-start"
                :class="[index >= 5 && !signupsAll ? 'hidden xl:grid' : 'grid', fresh[row.key] ? 'realtime-fresh' : '']">
                {{-- Who --}}
                <div class="flex items-start gap-3 min-w-0">
                    <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-xs font-semibold bg-[var(--brand-blue-a10)] text-[var(--brand-blue)]" aria-hidden="true">@{{ row.initials }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><bdi>@{{ row.name }}</bdi></p>
                        <a v-if="row.email" :href="'mailto:' + row.email" :title="row.email" class="block text-xs text-gray-500 dark:text-gray-400 truncate hover:underline"><bdi dir="ltr">@{{ row.email }}</bdi></a>
                        <p v-if="row.source" class="{{ $sourceInline }} text-xs text-gray-500 dark:text-gray-400 truncate"><bdi>@{{ row.source.primary }}</bdi><span v-if="row.source.secondary"> · <bdi dir="ltr">@{{ row.source.secondary }}</bdi></span></p>
                        <component v-if="row.on_site === 'now'" :is="canShow(row.person_id) ? 'button' : 'span'" :type="canShow(row.person_id) ? 'button' : undefined"
                                   @click="showPerson(row.person_id)"
                                   class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-green-700 dark:text-green-400 rounded text-start focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                                   :class="canShow(row.person_id) ? 'hover:underline' : ''">
                            {{-- Two pieces that wrap whole: the note, and which part of the site. --}}
                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ msg.onSiteNow }}</span>
                            <span v-if="row.surface" class="inline-flex items-center gap-1 whitespace-nowrap text-gray-600 dark:text-gray-400"><svg class="w-3.5 h-3.5 shrink-0" :class="surfaceTone(row.surface).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="icons[row.surface] || icons.other" /></svg>@{{ row.surface_label }}</span>
                        </component>
                    </div>
                </div>

                {{-- Came from --}}
                <div class="{{ $sourceColumn }} min-w-0">
                    <template v-if="row.source">
                        <p class="text-sm text-gray-700 dark:text-gray-300 truncate"><bdi>@{{ row.source.primary }}</bdi></p>
                        <p v-if="row.source.secondary" class="text-xs text-gray-500 dark:text-gray-400 truncate"><bdi dir="ltr">@{{ row.source.secondary }}</bdi></p>
                    </template>
                    <p v-else class="text-sm text-gray-400 dark:text-gray-500" :title="msg.sourceUnknown">
                        <span aria-hidden="true">-</span><span class="sr-only">@{{ msg.sourceUnknown }}</span>
                    </p>
                </div>

                {{-- Progress: schedule, event, ticket type. The segments carry their step as a
                     title; the text beside them is what the person has, or the step they lack. --}}
                <div class="min-w-0 col-start-1 md:col-start-auto ps-11 md:ps-0 mt-2 md:mt-0">
                    <p v-if="row.intent_label" class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">@{{ row.intent_label }}</p>
                    <template v-else>
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1 shrink-0" role="img" :aria-label="row.stage_label">
                                <span v-for="(title, n) in signups.stage_titles" :key="n" class="h-2 w-6 rounded-full transition-all duration-200" :title="title"
                                      :class="n < row.stage ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700'"></span>
                            </span>
                            <span v-if="!row.stuck_text" class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">@{{ row.status_text }}</span>
                        </div>
                        <x-link v-if="row.schedule" v-bind:href="row.schedule.url" class="block truncate mt-1 text-sm"><bdi>@{{ row.schedule.name }}</bdi></x-link>
                        <p v-if="row.stuck_text" class="mt-1 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">@{{ row.stuck_text }}</p>
                    </template>
                </div>

                {{-- Signed up --}}
                <div class="text-end row-start-1 col-start-2 md:row-start-auto md:col-start-auto">
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">@{{ ago(row.ago) }}</p>
                    <p class="text-xs tabular-nums text-gray-500 dark:text-gray-400 whitespace-nowrap">@{{ clock(row.ago) }}</p>
                </div>
            </li>
        </ul>

        <div v-if="signups.rows.length > 5 || signups.more_text" class="px-5 py-3 text-center border-t border-gray-100 dark:border-white/[0.06]"
             :class="signups.more_text ? '' : 'xl:hidden'">
            <button v-if="signups.rows.length > 5" type="button" @click="signupsAll = !signupsAll"
                    class="xl:hidden text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">@{{ signupsAll ? msg.showLess : msg.showAll }}</button>
            <p v-if="signups.more_text" class="text-xs text-gray-500 dark:text-gray-400">@{{ signups.more_text }}</p>
        </div>
    </template>

    <div v-else class="flex-1 px-5 py-10 flex flex-col items-center justify-center text-center">
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.signup" />
        </svg>
        <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.noSignups24 }}</p>
        <p v-if="signups.last_ago !== null && signups.last_ago !== undefined" class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.lastSignup.replace(':time', ago(signups.last_ago, true)) }}</p>
    </div>
</section>
