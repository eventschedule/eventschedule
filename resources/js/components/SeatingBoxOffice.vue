<template>
    <div class="space-y-4" :class="{ 'pb-28 xl:pb-0': single }">
        <!-- Summary + lookup -->
        <div class="ap-card rounded-xl p-4 flex flex-wrap items-center gap-4">
            <!-- Which show, and WHICH NIGHT. This used to live in a header slot the admin layout
                 never renders, so the console named neither - and on a thirty-date run the only
                 thing telling a staffer whose seat they were about to release was the URL. -->
            <div class="w-full">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ eventName }}</h1>
                <p v-if="dateLabel" class="text-sm text-gray-500 dark:text-gray-400">{{ dateLabel }}</p>
            </div>
            <a :href="backUrl" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:underline">&larr; {{ t.back }}</a>
            <a :href="reportUrl" class="text-sm font-medium text-[var(--brand-blue)] hover:underline">{{ t.report }}</a>

            <!-- Keyboard-first: staff on a phone type faster than they click. -->
            <div class="flex-1 min-w-[16rem]">
                <label for="bo-lookup" class="sr-only">{{ t.lookup }}</label>
                <input id="bo-lookup" v-model="lookup" type="search" :placeholder="t.lookupPlaceholder"
                    @keydown.enter.prevent="runLookup"
                    class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
            </div>

            <dl class="flex flex-wrap items-center gap-4 text-sm">
                <!-- How FULL, first. The bar carried four raw integers and no total, so nobody could
                     tell a busy night from a quiet one without doing the arithmetic. -->
                <div class="flex items-center gap-2">
                    <span class="text-base font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ soldPercent }}%</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ t.sold_label }}</span>
                    <span class="h-1.5 w-20 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden" aria-hidden="true">
                        <span class="block h-full rounded-full bg-[var(--brand-button-bg)]" :style="{ width: soldPercent + '%' }"></span>
                    </span>
                </div>
                <div v-for="k in ['sold', 'blocked', 'held', 'available']" :key="k" class="flex gap-1">
                    <dt class="text-gray-500 dark:text-gray-400">{{ t['count_' + k] }}:</dt>
                    <dd class="font-medium tabular-nums text-gray-800 dark:text-gray-200">{{ counts[k] || 0 }}</dd>
                </div>
            </dl>
        </div>

        <p v-if="error" class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200">
            {{ error }}
        </p>
        <!-- Guidance, not a failure. The exchange prompt used to render in the amber strip above,
             so being told what to click next looked like being told something had gone wrong. -->
        <p v-if="notice" class="rounded-lg border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 p-3 text-sm text-blue-800 dark:text-blue-200">
            {{ notice }}
        </p>

        <div class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-4">
            <!-- Map -->
            <div class="ap-card rounded-xl p-2">
                <div v-if="levels.length > 1 || sections.length" class="flex flex-wrap items-center gap-2 px-1 pb-2">
                    <!-- Only worth showing when there is somewhere else to go. -->
                    <div v-if="levels.length > 1" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                        <button v-for="lvl in levels" :key="lvl.id" type="button" @click="activeLevelId = lvl.id"
                            :aria-pressed="lvl.id === activeLevel?.id"
                            class="px-2 py-1 rounded text-xs transition-all duration-200"
                            :class="lvl.id === activeLevel?.id
                                ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                                : 'text-gray-500 dark:text-gray-400'">{{ lvl.name || t.level }}</button>
                    </div>
                    <!-- A tablet has no shift key, and shift-click was the only way to pick more
                         than one seat - so every bulk action was unreachable by touch. -->
                    <button type="button" id="bo-multi" @click="multiSelect = !multiSelect" :aria-pressed="multiSelect"
                        class="px-2 py-1 rounded-lg text-xs transition-all duration-200"
                        :class="multiSelect
                            ? 'bg-[var(--brand-button-bg)] text-white'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300'">
                        {{ t.multiSelect }}
                    </button>

                    <div class="ms-auto flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                        <button type="button" @click="zoomBy(-0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomOut">&minus;</button>
                        <span class="px-1 text-xs tabular-nums text-gray-500 dark:text-gray-400">{{ Math.round(zoom * 100) }}%</span>
                        <button type="button" @click="zoomBy(0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomIn">+</button>
                        <button type="button" @click="fit" class="px-2 py-1 rounded text-xs text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200">{{ t.fit }}</button>
                    </div>
                </div>
                <svg v-if="sections.length" ref="svgEl" v-bind="bind" :viewBox="viewBox" class="w-full select-none touch-none"
                    :style="{ height: mapHeight, cursor: 'grab' }"
                    role="group" :aria-label="t.mapLabel">
                    <defs>
                        <pattern id="boBlocked" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <line x1="0" y1="0" x2="0" y2="4" stroke="#6b7280" stroke-width="1.6" />
                        </pattern>
                    </defs>
                    <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                    <!-- Behind the seats and inert: staff must never lose a press on a seat to a
                         label, and the one thing this drawing has to say is which way the room faces. -->
                    <g v-for="d in decorations" :key="`dec-${d.id}`" pointer-events="none"
                        :transform="`translate(${d.x} ${d.y}) rotate(${d.rotation || 0})`">
                        <rect v-if="d.kind === 'stage'" :width="d.width" :height="d.height" rx="4"
                            class="fill-gray-200 dark:fill-gray-700" />
                        <text :x="d.width / 2" :y="d.height / 2" text-anchor="middle" dy="4"
                            :font-size="d.kind === 'stage' ? 13 : 11"
                            :class="d.kind === 'stage'
                                ? 'fill-gray-600 dark:fill-gray-300 uppercase tracking-widest'
                                : 'fill-gray-400 dark:fill-gray-500'">{{ d.label }}</text>
                    </g>

                    <g v-for="s in sections" :key="s.id" :transform="sectionTransform(s)">
                        <text :x="0" :y="-10" font-size="12" class="fill-gray-500 dark:fill-gray-400">{{ s.name }}</text>
                        <g v-for="seat in s.seats" :key="seat.id" :transform="`translate(${seatX(s, seat)} ${seatY(s, seat)})`">
                            <!-- Drawn disc only. The press belongs to the larger invisible target
                                 below, exactly as in the guest picker - this is the one surface used
                                 standing up, on a tablet, in a lobby, and it was the only one still
                                 asking a thumb to hit a 9-unit circle. -->
                            <circle r="9" :fill="fillFor(seat)" :stroke="strokeFor(seat)"
                                stroke-width="1.5" pointer-events="none" />
                            <circle :id="`bo-seat-${seat.id}`" r="15" fill="transparent"
                                class="cursor-pointer bo-seat-target"
                                role="button"
                                :tabindex="seat.id === focusedId ? 0 : -1"
                                :aria-label="labelFor(s, seat)"
                                :aria-pressed="isSelected(seat)"
                                @focus="focusedId = seat.id"
                                @click="onSeatClick($event, s, seat)"
                                @keydown="onSeatKey($event, s, seat)" />
                            <!-- Status is never colour alone: blocked and sold each carry a mark. -->
                            <circle v-if="seat.state === 'blocked'" r="9" fill="url(#boBlocked)" opacity="0.6" pointer-events="none" />
                            <!-- Arrived beats sold: a tick, not a cross. Shape rather than colour,
                                 like every other state on this map, and it answers the one question
                                 the door actually asks - is this person already inside? -->
                            <text v-else-if="seat.state === 'sold' && seat.arrived" text-anchor="middle" dy="3.5"
                                font-size="10" fill="#ffffff" pointer-events="none">&#10003;</text>
                            <text v-else-if="seat.state === 'sold'" text-anchor="middle" dy="3.5" font-size="9"
                                fill="#ffffff" pointer-events="none">&#10005;</text>
                            <text v-if="seat.kind === 'wheelchair'" text-anchor="middle" dy="4" font-size="10"
                                fill="#1f2937" pointer-events="none">&#9855;</text>
                        </g>
                    </g>
                    </g>
                </svg>
                <p v-else class="p-4 text-sm text-gray-500 dark:text-gray-400">{{ t.noSeats }}</p>
                <p class="px-2 pb-1 text-xs text-gray-400 dark:text-gray-500">{{ t.mapHint }}</p>
            </div>

            <!-- Inspector -->
            <div class="ap-card rounded-xl p-4 space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.sections }}</h3>
                    <ul class="mt-2 space-y-1">
                        <li v-for="s in sections" :key="s.id">
                            <button type="button" @click="selectSection(s)"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-start hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-200">
                                <span class="w-3 h-3 rounded-sm shrink-0" :style="{ backgroundColor: s.color }"></span>
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ s.name }}</span>
                                <span class="ms-auto text-xs text-gray-400">{{ s.seats.length }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- One sold seat: who has it, and what can be done with it -->
                <div v-if="single && single.state === 'sold'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ seatName(single) }}</h4>
                    <!-- Enough to act on without leaving the screen. It was name, email and
                         paid/unpaid, so reaching a customer meant going to the Sales page and
                         finding their order by name - at a counter, with them standing there. -->
                    <div v-if="single.booker" class="space-y-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ single.booker.name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <a v-if="single.booker.email" :href="'mailto:' + single.booker.email"
                               class="text-[var(--brand-blue)] hover:underline">{{ single.booker.email }}</a>
                            <template v-if="single.booker.phone">
                                &middot;
                                <a :href="'tel:' + single.booker.phone" class="text-[var(--brand-blue)] hover:underline">{{ single.booker.phone }}</a>
                            </template>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <template v-if="single.booker.ticket">{{ single.booker.ticket }} &middot; </template>{{ single.booker.status }}<template v-if="boughtOn"> &middot; {{ boughtOn }}</template>
                        </p>
                        <p v-if="single.arrived" class="text-xs font-medium text-green-700 dark:text-green-400">&#10003; {{ t.arrived }}</p>
                        <p v-if="single.booker.sales_url">
                            <a :href="single.booker.sales_url" class="text-xs text-[var(--brand-blue)] hover:underline">{{ t.viewOrder }}</a>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="button" @click="startExchange"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">
                            {{ exchangeFrom ? t.exchangeChoose : t.exchange }}
                        </button>
                        <button v-if="exchangeFrom" type="button" @click="cancelExchange"
                            class="px-3 py-2 rounded-md text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-all duration-200">
                            {{ t.cancelExchange }}
                        </button>
                        <!-- A bordered danger button, not underlined red text: this is the most
                             destructive control on the screen and it used to be the least
                             substantial thing next to a properly drawn button. -->
                        <button type="button" @click="releaseSeat"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">
                            {{ t.releaseSeat }}
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ t.releaseHelp }}</p>
                </div>

                <!-- A staff hold -->
                <div v-else-if="single && single.state === 'blocked'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ seatName(single) }}</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ t['kind_' + single.hold_kind] || single.hold_kind }}</p>
                    <p v-if="single.hold_note" class="text-xs text-gray-500 dark:text-gray-400">{{ single.hold_note }}</p>
                    <button type="button" @click="unblock"
                        class="px-3 py-2 rounded-md text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">
                        {{ t.unblock }}
                    </button>
                </div>

                <!-- Selection: hold seats back -->
                <div v-if="selected.length" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ selected.length }} {{ selected.length === 1 ? t.seatSelected : t.seatsSelected }}
                    </h4>
                    <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.holdReason }}
                        <select id="bo-hold-kind" v-model="holdKind" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                            <option v-for="k in holdKinds" :key="k" :value="k">{{ t['kind_' + k] }}</option>
                        </select>
                    </label>
                    <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.internalNote }}
                        <input id="bo-hold-note" v-model="holdNote" type="text" maxlength="255"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                    </label>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ t.internalNoteHelp }}</p>
                    <div class="flex gap-2">
                        <button id="bo-block" type="button" @click="block" :disabled="busy"
                            class="px-3 py-2 rounded-md text-xs font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200">
                            {{ t.blockSeats }}
                        </button>
                        <button type="button" @click="unblock" :disabled="busy"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200">
                            {{ t.unblock }}
                        </button>
                        <button type="button" @click="selected = []" class="text-xs text-gray-500 hover:underline">{{ t.clear }}</button>
                    </div>

                    <!-- Bulk release: a party refunding together. Only when the selection actually
                         holds sold seats, and it names how many, because this is the one action
                         here that takes a seat off somebody who paid for it. -->
                    <div v-if="soldSelected.length > 1" class="pt-2">
                        <button id="bo-release-many" type="button" @click="releaseMany" :disabled="busy"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 disabled:opacity-50 transition-all duration-200">
                            {{ (t.releaseSeats || '').replace(':count', String(soldSelected.length)) }}
                        </button>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t.releaseHelp }}</p>
                    </div>

                    <!-- The row is the unit staff work in: holding back the front row, releasing a
                         block. Picking twelve seats one at a time is not that. -->
                    <div v-if="single" class="pt-1">
                        <button type="button" @click="selectRowOf(single)"
                            class="text-xs font-medium text-[var(--brand-blue)] hover:underline">{{ t.selectRow }}</button>
                    </div>

                    <!-- Phone / counter booking. Staff picked the seats on the map, so these exact
                         seats are what the caller gets - no best-available. -->
                    <div class="pt-2">
                        <button type="button" @click="booking = !booking"
                            class="text-xs font-medium text-[var(--brand-blue)] hover:underline">
                            {{ booking ? t.clear : t.bookByPhone }}
                        </button>
                    </div>
                    <div v-if="booking" class="space-y-2">
                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.buyerName }}
                            <input v-model="buyer.name" type="text" maxlength="100"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </label>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.buyerEmail }}
                            <input v-model="buyer.email" type="email" maxlength="100"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </label>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.buyerPhone }}
                            <input v-model="buyer.phone" type="tel" maxlength="50"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </label>
                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ t.amount }}
                            <input v-model="buyer.amount" type="number" min="0" step="0.01" inputmode="decimal"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </label>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ t.amountHint }}</p>
                        <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <input v-model="buyer.paid" type="checkbox"
                                class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                            {{ buyer.paid ? t.markPaid : t.markUnpaid }}
                        </label>
                        <button type="button" @click="bookSeats" :disabled="busy || !buyer.name"
                            class="w-full px-3 py-2 rounded-md text-xs font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200">
                            {{ selected.length === 1 ? t.bookSeat : t.bookSeats.replace(':count', selected.length) }}
                        </button>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="background:#e5e7eb;border-color:#9ca3af"></span>{{ t.count_available }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#dc2626"></span>{{ t.count_sold }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="border-color:#6b7280;background:repeating-linear-gradient(45deg,#9ca3af,#9ca3af 1.5px,#6b7280 1.5px,#6b7280 3px)"></span>{{ t.count_blocked }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#f59e0b"></span>{{ t.count_held }}</span>
                    <span v-if="arrivedCount" class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full flex items-center justify-center text-[8px] text-white" style="background:#dc2626">&#10003;</span>
                        {{ (t.countArrived || '').replace(':count', String(arrivedCount)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Small screens: the door loop, pinned.
             The inspector stacks BELOW the map once the two columns collapse, so acting on a seat
             meant tap, scroll down, act, scroll back - and door staff are the people most likely to
             be holding a phone. This puts the selected seat and its actions where the thumb already
             is, and leaves the full inspector untouched above the breakpoint.

             position: fixed is safe in the AP: the accessibility widget, with its
             --es-a11y-cta-clearance juggling, is loaded by app-guest.blade.php only. -->
        <div v-if="single" class="xl:hidden fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg p-3"
             style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px))">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ seatName(single) }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        <span v-if="single.state === 'sold' && single.booker">{{ single.booker.name }}</span>
                        <span v-else>{{ t['count_' + single.state] || single.state }}</span>
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <template v-if="single.state === 'sold'">
                        <button type="button" @click="startExchange"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200">
                            {{ exchangeFrom ? t.exchangeChoose : t.exchange }}
                        </button>
                        <button v-if="exchangeFrom" type="button" @click="cancelExchange"
                            class="px-3 py-2 rounded-md text-xs font-medium text-gray-500 dark:text-gray-400 transition-all duration-200">
                            {{ t.cancelExchange }}
                        </button>
                        <button v-else type="button" @click="releaseSeat"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 transition-all duration-200">
                            {{ t.releaseSeat }}
                        </button>
                    </template>
                    <button v-else-if="single.state === 'blocked'" type="button" @click="unblock" :disabled="busy"
                        class="px-3 py-2 rounded-md text-xs font-medium border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 disabled:opacity-50 transition-all duration-200">
                        {{ t.unblock }}
                    </button>
                    <button v-else type="button" @click="block" :disabled="busy"
                        class="px-3 py-2 rounded-md text-xs font-medium text-white bg-[var(--brand-button-bg)] disabled:opacity-50 transition-all duration-200">
                        {{ t.blockSeats }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, nextTick, ref, watch } from 'vue';
import { loadMap, startPolling } from '../seat-map-store';
import { useMapViewport } from '../seat-map-viewport';
import { sectionTransform, toCanvasFrame } from '../seat-map-geometry';

const props = defineProps({
    eventId: { type: String, required: true },
    date: { type: String, default: '' },
    stateUrl: { type: String, required: true },
    blockUrl: { type: String, required: true },
    unblockUrl: { type: String, required: true },
    releaseUrl: { type: String, required: true },
    exchangeUrl: { type: String, required: true },
    bookUrl: { type: String, required: true },
    eventName: { type: String, default: '' },
    // Preformatted server-side, where the locale lives.
    dateLabel: { type: String, default: '' },
    backUrl: { type: String, default: '' },
    reportUrl: { type: String, default: '' },
    csrfToken: { type: String, required: true },
    strings: { type: Object, default: () => ({}) },
});

const t = props.strings;
const holdKinds = ['house', 'production', 'accessibility', 'box_office'];

const booking = ref(false);
const buyer = ref({ name: '', email: '', phone: '', amount: '', paid: true });

const map = ref(null);
const selected = ref([]);
const focusedId = ref(null);
const exchangeFrom = ref(null);
const holdKind = ref('house');
const holdNote = ref('');
/** Tap-to-add mode, for a device with no modifier keys. */
const multiSelect = ref(false);
const lookup = ref('');
const error = ref('');
const notice = ref('');
const busy = ref(false);

/**
 * Levels are separate spaces, so they are drawn one at a time.
 *
 * Drawing them together superimposed a balcony on the stalls: every level's first section is seeded
 * at the same origin, so two levels land on top of each other rather than side by side.
 */
const levels = computed(() => ((map.value && map.value.levels) || []).filter(l => (l.sections || []).length));
const activeLevelId = ref(null);
const activeLevel = computed(() => levels.value.find(l => l.id === activeLevelId.value) || levels.value[0] || null);

/** Only what is on screen. */
const sections = computed(() => (activeLevel.value && activeLevel.value.sections) || []);

/** The stage and any labels on the level being drawn. Never interactive. */
const decorations = computed(() => (activeLevel.value && activeLevel.value.decorations) || []);

/** Every section on every level - what the counts, the lookup and a seat's name are built from. */
const allSections = computed(() => levels.value.flatMap(l => l.sections || []));

const counts = computed(() => (map.value && map.value.counts) || {});
const allSeats = computed(() => allSections.value.flatMap(s => s.seats.map(seat => ({ seat, section: s }))));

/** The seats being drawn, in reading order - what the keyboard walks. */
const shownSeats = computed(() => sections.value.flatMap(s => s.seats.map(seat => ({ seat, section: s }))));

function levelOf(seatId) {
    return levels.value.find(l => (l.sections || []).some(s => s.seats.some(x => x.id === seatId))) || null;
}
const single = computed(() => (selected.value.length === 1
    ? allSeats.value.find(x => x.seat.id === selected.value[0])?.seat ?? null
    : null));

const svgEl = ref(null);

/** Bounding box of the active level, in content units. */
/** The four corners of a decoration in canvas space, rotation included. */
function decorationCorners(d) {
    const w = Number(d.width) || 0;
    const h = Number(d.height) || 0;
    const deg = Number(d.rotation) || 0;
    const pts = [[0, 0], [w, 0], [w, h], [0, h]];
    if (! deg) return pts.map(([x, y]) => [d.x + x, d.y + y]);

    const r = (deg * Math.PI) / 180;
    const cos = Math.cos(r);
    const sin = Math.sin(r);

    return pts.map(([x, y]) => [d.x + x * cos - y * sin, d.y + x * sin + y * cos]);
}

function contentBounds() {
    const xs = [], ys = [];
    // A stage above row A must be inside the frame, or Fit puts it off screen.
    decorations.value.forEach(d => decorationCorners(d).forEach(([x, y]) => { xs.push(x); ys.push(y); }));
    sections.value.forEach(s => s.seats.forEach(seat => {
        const [cx, cy] = toCanvasFrame(s, seatX(s, seat), seatY(s, seat));
        xs.push(cx); ys.push(cy);
    }));
    if (!xs.length) return null;
    const pad = 20;
    const minX = Math.min(...xs) - pad, minY = Math.min(...ys) - pad - 14;
    return { minX, minY, w: Math.max(1, Math.max(...xs) - minX + pad), h: Math.max(1, Math.max(...ys) - minY + pad) };
}

const { zoom, pan, canvas, bind, fit, zoomBy, observe, revealPoint } = useMapViewport({ svgEl, contentBounds });

/**
 * The canvas is the viewBox, and zoom/pan move the content inside it - so a wide, shallow room no
 * longer letterboxes inside a tall fixed box. The height still follows the room's proportions so a
 * single row does not get a 30rem tall panel to sit in.
 */
const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);
const mapHeight = computed(() => {
    const b = contentBounds();
    if (!b) return '20rem';
    const ratio = Math.min(1.1, Math.max(0.3, b.h / b.w));
    return `${Math.round(Math.min(640, Math.max(240, canvas.w * ratio)))}px`;
});

// Same relative-to-parent geometry as the designer and the picker, including the fallback for a
// plan whose seats carry no coordinates.
const degenerate = computed(() => {
    const out = new Set();
    sections.value.forEach(s => {
        const xs = new Set(s.seats.map(x => x.x)), ys = new Set(s.seats.map(x => x.y));
        if (s.seats.length > 1 && xs.size === 1 && ys.size === 1) out.add(s.id);
    });
    return out;
});
function rowsOf(s) {
    const g = new Map();
    s.seats.forEach(seat => { const k = seat.row || ''; if (!g.has(k)) g.set(k, []); g.get(k).push(seat); });
    return [...g.entries()].map(([k, seats]) => ({ key: k, seats }));
}
function seatX(s, seat) {
    if (degenerate.value.has(s.id)) return (rowsOf(s).find(r => r.seats.includes(seat))?.seats.indexOf(seat) ?? 0) * 26;
    const tb = seat.table_id ? (s.tables || []).find(x => x.id === seat.table_id) : null;
    return tb ? tb.x + seat.x : seat.x;
}
function seatY(s, seat) {
    if (degenerate.value.has(s.id)) return rowsOf(s).findIndex(r => r.seats.includes(seat)) * 30;
    const tb = seat.table_id ? (s.tables || []).find(x => x.id === seat.table_id) : null;
    return tb ? tb.y + seat.y : seat.y;
}

function isSelected(seat) { return selected.value.includes(seat.id); }
function fillFor(seat) {
    if (isSelected(seat)) return 'var(--brand-blue)';
    if (seat.state === 'sold') return '#dc2626';
    if (seat.state === 'blocked') return '#9ca3af';
    if (seat.state === 'held') return '#f59e0b';
    return '#e5e7eb';
}
function strokeFor(seat) { return isSelected(seat) ? 'var(--brand-blue)' : '#6b7280'; }

function seatName(seat) {
    const owner = allSeats.value.find(x => x.seat.id === seat.id);
    const bits = [owner ? owner.section.name : ''];
    if (seat.row) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row));
    if (seat.seat) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat));
    return bits.filter(Boolean).join(', ');
}
function labelFor(s, seat) {
    return `${seatName(seat)}, ${t['count_' + seat.state] || seat.state}`;
}

function onSeatClick(evt, section, seat) {
    if (exchangeFrom.value) {
        // Second click of an exchange: pick the destination. Clicking the ARMED seat instead means
        // "no, not this after all", so it disarms rather than falling through to plain selection.
        if (seat.id !== exchangeFrom.value) {
            doExchange(exchangeFrom.value, seat.id);
        } else {
            cancelExchange();
        }

        return;
    }
    // A tablet has no shift key, so shift-click being the ONLY way to select more than one seat
    // made every bulk action - hold back, and any counter booking of more than one seat -
    // unreachable by touch. `multi` is a mode the staff member turns on.
    if (evt.shiftKey || evt.metaKey || evt.ctrlKey || multiSelect.value) {
        const i = selected.value.indexOf(seat.id);
        if (i >= 0) selected.value.splice(i, 1); else selected.value.push(seat.id);
    } else {
        selected.value = [seat.id];
    }
}

/**
 * Every seat in the same row as this one, which is the unit staff actually work in - or the same
 * TABLE, for a cabaret section, where the row is not the unit and every seat shares row_position 0.
 *
 * Comparing on a field the payload does not carry makes every seat match, which silently turned
 * this into "select the whole section" - one click from taking a 400-seat stalls off sale.
 */
function selectRowOf(seat) {
    const section = allSections.value.find(sec => sec.seats.some(x => x.id === seat.id));
    if (! section) return;

    const target = section.seats.find(x => x.id === seat.id);
    if (! target) return;

    const sameGroup = target.table_id
        ? (x) => x.table_id === target.table_id
        : (x) => ! x.table_id && x.row_position === target.row_position;

    selected.value = section.seats.filter(sameGroup).map(x => x.id);
}

function onSeatKey(evt, section, seat) {
    if (evt.key === 'Enter' || evt.key === ' ') { evt.preventDefault(); onSeatClick(evt, section, seat); return; }
    const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[evt.key];
    if (!step) return;
    evt.preventDefault();
    // The seats being drawn, not every seat in the house: arrowing must not walk onto a level
    // that is not on screen.
    const list = shownSeats.value;
    const i = list.findIndex(x => x.seat.id === seat.id);
    const next = list[Math.min(list.length - 1, Math.max(0, i + step))];
    if (!next) return;
    focusedId.value = next.seat.id;
    reveal(next);
    nextTick(() => document.getElementById(`bo-seat-${next.seat.id}`)?.focus());
}

function selectSection(s) { selected.value = s.seats.map(x => x.id); }

/** "C14", "row C seat 14", or a customer name. */
function runLookup() {
    const q = lookup.value.trim().toLowerCase();
    if (!q) return;

    const compact = q.match(/^([a-z]+)\s*-?\s*(\d+)$/);
    const spelled = q.match(/row\s+([a-z0-9]+)\s+seat\s+(\d+)/);
    const row = spelled ? spelled[1] : (compact ? compact[1] : null);
    const seatNo = spelled ? spelled[2] : (compact ? compact[2] : null);

    // A RANGE, which staff ask for constantly: "hold back C1 to C12". Without it that is twelve
    // separate lookups or twelve clicks.
    const range = q.match(/^([a-z]+)\s*-?\s*(\d+)\s*(?:-|to|\u2013)\s*(?:[a-z]+\s*-?\s*)?(\d+)$/);

    let hits = [];

    if (range) {
        const [, rangeRow, from, to] = range;
        const lo = Math.min(Number(from), Number(to));
        const hi = Math.max(Number(from), Number(to));

        hits = allSeats.value.filter(x => (x.seat.row || '').toLowerCase() === rangeRow
            && Number(x.seat.seat) >= lo && Number(x.seat.seat) <= hi);
    } else if (row && seatNo) {
        hits = allSeats.value.filter(x => (x.seat.row || '').toLowerCase() === row && String(x.seat.seat) === seatNo);
    }

    if (! hits.length) {
        // EVERY match, not the first. A name matching a party of three showed one seat of it with
        // nothing to say the other two existed - so staff released one seat of an order believing
        // they had the lot.
        hits = allSeats.value.filter(x => x.seat.booker
            && ((x.seat.booker.name || '').toLowerCase().includes(q) || (x.seat.booker.email || '').toLowerCase().includes(q)));
    }

    if (! hits.length) { error.value = t.lookupNothing; return; }

    const hit = hits[0];

    error.value = '';
    // Say how many, so a party is never mistaken for a single seat. The whole set is selected, so
    // the bulk actions apply to it immediately.
    notice.value = hits.length > 1
        ? (t.lookupMatches || '').replace(':count', String(hits.length))
        : '';
    // The hit may be on another level, which is not drawn - switch to it, or the staffer is told
    // the seat was found and then shown a map without it.
    const lvl = levelOf(hit.seat.id);
    if (lvl && lvl.id !== activeLevel.value?.id) activeLevelId.value = lvl.id;

    selected.value = hits.map(x => x.seat.id);
    focusedId.value = hit.seat.id;
    nextTick(() => {
        reveal(hit);
        document.getElementById(`bo-seat-${hit.seat.id}`)?.focus();
    });
}

/** Pan a seat into view, by its position on the canvas rather than by scrolling the page. */
function reveal(entry) {
    if (!entry) return;
    const s = entry.section;
    revealPoint(...toCanvasFrame(s, seatX(s, entry.seat), seatY(s, entry.seat)));
}

async function post(url, body) {
    busy.value = true;
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
            credentials: 'same-origin',
            body: JSON.stringify(Object.assign({ date: props.date }, body)),
        });
        const data = await res.json().catch(() => ({}));
        // Every mutation hands back the refreshed map, so a refusal arrives with current truth.
        if (data.state) applyState(data.state);
        else if (res.ok) applyState(data);
        // A 422 from validate() carries `errors`, not `error` - show the real reason rather than
        // the generic one, or a bad email reads as "something went wrong".
        const firstInvalid = data.errors ? Object.values(data.errors)[0]?.[0] : null;
        error.value = res.ok ? '' : (data.error || firstInvalid || t.actionFailed);
        return res.ok;
    } catch (e) {
        error.value = t.actionFailed;
        return false;
    } finally {
        busy.value = false;
    }
}

function applyState(state) {
    if (!state || !state.levels) return;
    map.value = state;
}

const block = () => post(props.blockUrl, { seat_ids: selected.value, kind: holdKind.value, note: holdNote.value });
const unblock = () => post(props.unblockUrl, { seat_ids: single.value ? [single.value.id] : selected.value });
/**
 * Releasing takes a seat off somebody who paid for it, so it asks first - and names them, because
 * the panel is already showing who they are. window.confirm(), the same guard `data-confirm` uses
 * across the app; an empty translation still asks rather than silently going ahead.
 */
/**
 * The sold seats in the current selection, which is what Release now acts on.
 *
 * Release was single-only (`if (!single.value) return`), so refunding a party of six was six
 * click-confirm cycles - while Hold back and Book have always been bulk.
 */
/** The purchase date in the reader's own locale, or '' - the payload sends ISO. */
const boughtOn = computed(() => {
    const iso = single.value?.booker?.bought_at;
    if (! iso) return '';

    const d = new Date(iso);

    return isNaN(d.getTime()) ? '' : d.toLocaleDateString();
});

/** How many ticket holders are already inside. */
/** How full the house is. Recomputed locally so it follows the poll rather than the last load. */
const soldPercent = computed(() => {
    const total = Number(counts.value.total) || 0;

    return total ? Math.round((Number(counts.value.sold) || 0) / total * 100) : 0;
});

const arrivedCount = computed(() => allSeats.value.filter(x => x.seat.arrived).length);

const soldSelected = computed(() => allSeats.value
    .filter(x => selected.value.includes(x.seat.id) && x.seat.state === 'sold')
    .map(x => x.seat));

function releaseSeat() {
    if (! single.value && soldSelected.value.length > 1) return releaseMany();
    if (! single.value) return;

    const text = (t.confirmRelease || '')
        .split(':seat').join(seatName(single.value))
        .split(':name').join(single.value.booker?.name || '');

    if (! window.confirm(text || '?')) return;

    return post(props.releaseUrl, { seat_id: single.value.id });
}

async function releaseMany() {
    const seats = soldSelected.value;
    if (! seats.length) return false;

    const text = (t.confirmReleaseMany || '')
        .split(':count').join(String(seats.length));

    if (text && ! window.confirm(text)) {
        return false;
    }

    const ok = await post(props.releaseUrl, { seat_ids: seats.map(s => s.id) });
    // Acting on seats that are no longer in the state the panel showed is how a staff member
    // repeats an action by mistake; the selection has served its purpose either way.
    if (ok) selected.value = [];

    return ok;
}

async function bookSeats() {
    if (!selected.value.length || !buyer.value.name) return;

    // Blank amount posts nothing, so the server charges the list price; a typed 0 comps the seats.
    const amount = String(buyer.value.amount).trim();
    const ok = await post(props.bookUrl, {
        seat_ids: selected.value,
        name: buyer.value.name,
        email: buyer.value.email || null,
        phone: buyer.value.phone || null,
        status: buyer.value.paid ? 'paid' : 'unpaid',
        amount: amount === '' ? null : Number(amount),
    });

    if (ok) {
        booking.value = false;
        buyer.value = { name: '', email: '', phone: '', amount: '', paid: true };
        selected.value = [];
    }
}

function startExchange() {
    if (!single.value) return;
    exchangeFrom.value = single.value.id;
    notice.value = t.exchangePrompt;
}

/**
 * Disarm.
 *
 * Once armed, the NEXT click on any other seat moves a booking - so a staffer who armed it by
 * mistake and then clicked a seat to look at it had already moved somebody. There was no way out:
 * Escape did nothing, and the button re-armed the same seat.
 */
function cancelExchange() {
    exchangeFrom.value = null;
    notice.value = '';
}

function onExchangeKey(evt) {
    if (evt.key === 'Escape' && exchangeFrom.value) cancelExchange();
}
async function doExchange(fromId, toId) {
    exchangeFrom.value = null;
    notice.value = '';
    await post(props.exchangeUrl, { from_seat_id: fromId, to_seat_id: toId });
}

onMounted(async () => {
    const data = await loadMap(props.stateUrl, props.eventId, props.date);
    if (!data) { error.value = t.loadFailed; return; }
    map.value = data;
    activeLevelId.value = levels.value[0]?.id ?? null;
    focusedId.value = shownSeats.value[0]?.seat.id ?? null;
    await nextTick();
    observe();
    fit();
    // Faster than the guest picker: staff are watching a live door.
    startPolling(props.stateUrl, props.eventId, props.date, map.value, () => {}, 3000);
    window.addEventListener('keydown', onExchangeKey);
});
// Each level has its own extent, so a switch has to reframe or the new level lands off screen.
watch(activeLevelId, () => nextTick(fit));

onBeforeUnmount(() => window.removeEventListener('keydown', onExchangeKey));
</script>
