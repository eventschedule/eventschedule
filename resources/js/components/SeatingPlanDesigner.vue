<template>
    <div class="space-y-4">

        <!-- Toolbar -->
        <div class="ap-card rounded-xl p-4 flex flex-wrap items-center gap-3">
            <a :href="backUrl" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:underline">&larr; {{ t.back }}</a>

            <!-- Hidden when editing a single date. saveOccurrenceStructure() only reads `levels`,
                 so the field looked editable and silently discarded whatever was typed into it. -->
            <input v-if="nameEditable" v-model="planName" type="text" maxlength="255" @input="dirty = true"
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm w-64"
                :aria-label="t.planName" />
            <span v-else class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ planName }}</span>

            <div class="flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                <button type="button" @click="zoomBy(-0.15)" class="px-2 py-1 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomOut">&minus;</button>
                <span class="px-2 text-xs tabular-nums text-gray-500 dark:text-gray-400">{{ Math.round(zoom * 100) }}%</span>
                <button type="button" @click="zoomBy(0.15)" class="px-2 py-1 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomIn">+</button>
                <button type="button" @click="fitToView" class="px-2 py-1 rounded-lg text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200">{{ t.fit }}</button>
            </div>

            <div class="ms-auto flex items-center gap-3">
                <span v-if="issues.length" class="text-sm text-amber-600 dark:text-amber-400">
                    {{ issues.length }} {{ issues.length === 1 ? t.issue : t.issues }}
                </span>
                <span v-if="dirty" id="seating-dirty" class="text-sm text-gray-500 dark:text-gray-400">{{ t.unsaved }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ totalSeats }} {{ t.seats }}</span>
                <button id="seating-save" type="button" @click="save" :disabled="saving"
                    class="px-4 py-3 text-base rounded-md font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                    {{ saving ? t.saving : t.save }}
                </button>
            </div>
        </div>

        <!-- What is already committed. Restructuring a room that is on sale is the one edit the
             server can refuse - a sold seat cannot be deleted - and without this the organizer
             found that out on Save, after doing the work. -->
        <div v-if="usageNotice" class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 flex items-start gap-2">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <p class="text-sm text-amber-800 dark:text-amber-200">{{ usageNotice }}</p>
        </div>

        <div v-if="error" id="seating-error" class="rounded-lg border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-300">
            {{ error }}
        </div>

        <!-- Empty state: presets rather than a blank canvas -->
        <div v-if="!levels.length" class="ap-card rounded-xl p-8">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ t.startFrom }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t.startFromHelp }}</p>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <button v-for="p in presets" :key="p.key" type="button" :data-preset="p.key" @click="applyPreset(p.key)"
                    class="text-start rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-[var(--brand-blue)] hover:shadow-md transition-all duration-200">
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">{{ p.label }}</span>
                    <span class="block mt-1 text-xs text-gray-500 dark:text-gray-400">{{ p.help }}</span>
                </button>
            </div>
            <button type="button" @click="addLevel()" class="mt-4 text-sm text-[var(--brand-blue)] hover:underline">{{ t.blankCanvas }}</button>
        </div>

        <!-- Three columns only past 1400px. At xl the canvas got ~290px once the AP sidebar was
             subtracted, so "Fit" landed on 41% and the plan you are drawing was the smallest thing
             on the screen. Below that the canvas takes the full width and the panels stack. -->
        <div v-else class="grid grid-cols-1 min-[1400px]:grid-cols-[17rem_1fr_19rem] gap-4">

            <!-- Left: levels and sections -->
            <div class="ap-card rounded-xl p-4 space-y-4">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.levels }}</h3>
                        <button type="button" @click="addLevel()" class="text-sm text-[var(--brand-blue)] hover:underline">{{ t.add }}</button>
                    </div>
                    <ul class="mt-2 space-y-1">
                        <li v-for="(lvl, i) in levels" :key="lvl.id">
                            <button type="button" @click="selectLevel(i)"
                                class="w-full text-start px-3 py-2 rounded-lg text-sm transition-all duration-200"
                                :class="i === activeLevel ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)] dark:shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'">
                                {{ lvl.name }}
                                <span class="text-xs text-gray-400">({{ seatsInLevel(lvl) }})</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div v-if="level">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.levelName }}</label>
                    <input v-model="level.name" type="text" maxlength="100" @input="dirty = true"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    <button v-if="levels.length > 1" type="button" @click="removeLevel(activeLevel)"
                        class="mt-2 text-xs text-red-600 dark:text-red-400 hover:underline">{{ t.removeLevel }}</button>
                </div>

                <div v-if="level">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.sections }}</h3>
                    </div>
                    <ul class="mt-2 space-y-1">
                        <li v-for="s in level.sections" :key="s.id">
                            <button type="button" @click="selectSection(s)"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-start transition-all duration-200"
                                :class="isSelectedSection(s) ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-800'">
                                <span class="w-3 h-3 rounded-sm shrink-0" :style="{ backgroundColor: s.color }"></span>
                                <span class="truncate text-gray-700 dark:text-gray-300">{{ s.name }}</span>
                                <span class="ms-auto text-xs text-gray-400">{{ s.kind === 'standing' ? s.capacity : s.seats.length }}</span>
                            </button>
                        </li>
                    </ul>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" @click="addSection('seated')" class="text-xs text-[var(--brand-blue)] hover:underline">{{ t.addSeated }}</button>
                        <button type="button" @click="addSection('table')" class="text-xs text-[var(--brand-blue)] hover:underline">{{ t.addTables }}</button>
                        <button type="button" @click="addSection('standing')" class="text-xs text-[var(--brand-blue)] hover:underline">{{ t.addStanding }}</button>
                    </div>
                </div>
            </div>

            <!-- Centre: the canvas -->
            <div class="ap-card rounded-xl p-2 overflow-hidden">
                <svg v-if="level" ref="svgEl" v-bind="viewportBind" class="w-full select-none" :viewBox="viewBox"
                    :style="{ height: canvasHeight, touchAction: 'none' }"
                    @mousedown="onCanvasDown" @mousemove="onMove" @mouseup="endDrag" @mouseleave="endDrag">
                    <defs>
                        <pattern id="restrictedHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <line x1="0" y1="0" x2="0" y2="4" stroke="currentColor" stroke-width="1.5" opacity="0.6" />
                        </pattern>
                    </defs>

                    <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                        <g v-for="s in level.sections" :key="s.id" :transform="`translate(${s.x} ${s.y}) rotate(${s.rotation})`">
                            <rect :x="sectionBox(s).x" :y="sectionBox(s).y" :width="sectionBox(s).w" :height="sectionBox(s).h" rx="8"
                                :fill="s.color" fill-opacity="0.10" :stroke="s.color" stroke-opacity="0.5"
                                style="cursor: move" @mousedown.stop="startSectionDrag($event, s)" />
                            <text :x="sectionBox(s).x + 2" :y="sectionBox(s).y - 8"
                                class="fill-gray-600 dark:fill-gray-300" font-size="13">{{ s.name }}</text>

                            <template v-if="s.kind === 'standing'">
                                <text :x="16" :y="50" class="fill-gray-500 dark:fill-gray-400" font-size="12">
                                    {{ s.capacity }} {{ t.standingCapacity }}
                                </text>
                            </template>

                            <g v-for="tb in s.tables" :key="tb.id" :transform="`translate(${tb.x} ${tb.y})`">
                                <circle v-if="tb.shape === 'round'" :r="tb.width / 2" :fill="s.color" fill-opacity="0.25"
                                    :stroke="s.color" style="cursor: move" @mousedown.stop="startTableDrag($event, tb)" />
                                <rect v-else :x="-tb.width / 2" :y="-tb.height / 2" :width="tb.width" :height="tb.height" rx="4"
                                    :fill="s.color" fill-opacity="0.25" :stroke="s.color" style="cursor: move"
                                    @mousedown.stop="startTableDrag($event, tb)" />
                                <text text-anchor="middle" dy="4" font-size="11" class="fill-gray-700 dark:fill-gray-200">{{ tb.label }}</text>
                            </g>

                            <g v-for="seat in s.seats" :key="seat.id"
                                :transform="`translate(${seatX(s, seat)} ${seatY(s, seat)})`"
                                style="cursor: pointer" @mousedown.stop="onSeatDown($event, seat, s)"
                                tabindex="0" role="button"
                                :aria-label="seatAriaLabel(s, seat)"
                                :aria-pressed="selectedSeats.includes(seat.id)"
                                @keydown="onSeatKey($event, seat, s)">
                                <!-- Shape carries the kind, never colour alone. -->
                                <rect v-if="seat.kind === 'wheelchair'" x="-9" y="-9" width="18" height="18" rx="3"
                                    :fill="seatFill(seat)" :stroke="seatStroke(seat)" stroke-width="1.5" />
                                <circle v-else r="8" :fill="seatFill(seat)" :stroke="seatStroke(seat)" stroke-width="1.5"
                                    :stroke-dasharray="seat.kind === 'companion' ? '3 2' : null" />
                                <circle v-if="seat.kind === 'restricted_view'" r="8" fill="url(#restrictedHatch)"
                                    class="text-gray-700 dark:text-gray-200" />
                                <text v-if="seat.kind === 'wheelchair'" text-anchor="middle" dy="4" font-size="10"
                                    class="fill-gray-900 dark:fill-gray-900">&#9855;</text>
                                <!-- No dark: variant. The seat itself is light in BOTH themes, so a
                                     dark-mode text colour put light grey on a light grey disc and the
                                     numbers vanished entirely. -->
                                <text v-else-if="seat.seat_label && zoom > 0.7" text-anchor="middle" dy="3.5" font-size="9"
                                    fill="#4b5563">{{ seat.seat_label }}</text>
                                <line v-if="seat.aisle_after" x1="13" y1="-10" x2="13" y2="10"
                                    class="stroke-gray-400 dark:stroke-gray-500" stroke-width="2" stroke-dasharray="2 2" />
                            </g>
                        </g>
                    </g>
                </svg>
                <p class="px-2 pb-1 text-xs text-gray-400 dark:text-gray-500">{{ t.canvasHint }}</p>
            </div>

            <!-- Right: inspector -->
            <div class="ap-card rounded-xl p-4 space-y-4">
                <template v-if="section">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.section }}</h3>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.name }}</label>
                        <input v-model="section.name" @input="dirty = true" type="text" maxlength="100"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.band }}</label>
                        <input v-model="section.band" @input="dirty = true" type="text" maxlength="100"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t.bandHelp }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.colour }}</label>
                            <input v-model="section.color" @input="dirty = true" type="color"
                                class="mt-1 h-9 w-16 rounded border border-gray-300 dark:border-gray-700 bg-transparent" />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mt-5">
                            <input type="checkbox" v-model="section.accessibility_only" @change="dirty = true"
                                class="rounded border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                            {{ t.accessibilityOnly }}
                        </label>
                    </div>

                    <div v-if="section.kind === 'standing'">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.capacity }}</label>
                        <input v-model.number="section.capacity" @input="dirty = true" type="number" min="0" max="65535"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    </div>

                    <!-- Row builder -->
                    <div v-if="section.kind === 'seated'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ t.addRows }}</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.rows }}
                                <input v-model.number="rowForm.rows" type="number" min="1" max="60" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.seatsPerRow }}
                                <input v-model.number="rowForm.perRow" type="number" min="1" max="80" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.rowLabels }}
                                <select v-model="rowForm.rowStyle" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                    <option value="alpha">A, B, C</option>
                                    <option value="numeric">1, 2, 3</option>
                                </select>
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.curve }}
                                <input v-model.number="rowForm.curve" type="number" min="0" max="120" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400 col-span-2">{{ t.aisleAfterSeats }}
                                <input v-model="rowForm.aisles" type="text" placeholder="6, 14"
                                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                        </div>
                        <button type="button" @click="generateRows"
                            class="w-full px-3 py-2 rounded-md text-sm font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            {{ t.generateRows }}
                        </button>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ t.generateRowsHelp }}</p>
                    </div>

                    <!-- Table builder -->
                    <div v-if="section.kind === 'table'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ t.addTablesTitle }}</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.tables }}
                                <input v-model.number="tableForm.count" type="number" min="1" max="60" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.seatsPerTable }}
                                <input v-model.number="tableForm.seats" type="number" min="1" max="24" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.shape }}
                                <select v-model="tableForm.shape" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                    <option value="round">{{ t.round }}</option>
                                    <option value="rect">{{ t.rectangular }}</option>
                                </select>
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.booking }}
                                <select v-model="tableForm.mode" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                    <option value="seat">{{ t.bookSeat }}</option>
                                    <option value="whole">{{ t.bookWhole }}</option>
                                    <option value="either">{{ t.bookEither }}</option>
                                </select>
                            </label>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                            <input type="checkbox" v-model="tableForm.numbered" class="rounded border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                            {{ t.numberSeats }}
                        </label>
                        <button type="button" @click="generateTables"
                            class="w-full px-3 py-2 rounded-md text-sm font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            {{ t.generateTables }}
                        </button>
                    </div>

                    <button type="button" @click="removeSection(section)" class="text-xs text-red-600 dark:text-red-400 hover:underline">{{ t.removeSection }}</button>
                </template>

                <template v-if="selectedSeats.length">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ selectedSeats.length }} {{ selectedSeats.length === 1 ? t.seatSelected : t.seatsSelected }}
                        </h4>
                        <div class="flex flex-wrap gap-1">
                            <button v-for="k in seatKinds" :key="k" type="button" @click="applyKind(k)"
                                class="px-2 py-1 rounded-md text-xs border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">
                                {{ t['kind_' + k] }}
                            </button>
                        </div>
                        <button type="button" @click="toggleAisle" class="text-xs text-[var(--brand-blue)] hover:underline">{{ t.toggleAisle }}</button>
                        <button type="button" @click="removeSelectedSeats" class="block text-xs text-red-600 dark:text-red-400 hover:underline">{{ t.removeSeats }}</button>
                    </div>
                </template>

                <!-- Validation -->
                <div v-if="issues.length" class="border-t border-gray-200 dark:border-gray-700 pt-3">
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <ul class="text-xs text-amber-800 dark:text-amber-200 space-y-1">
                                <li v-for="(issue, i) in issues" :key="i">{{ issue }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { useMapViewport } from '../seat-map-viewport';

const props = defineProps({
    planName: { type: String, default: '' },
    nameEditable: { type: Boolean, default: true },
    usage: { type: Object, default: () => ({ events: 0, sold: 0 }) },
    structureUrl: { type: String, required: true },
    saveUrl: { type: String, required: true },
    backUrl: { type: String, default: '' },
    csrfToken: { type: String, required: true },
    strings: { type: Object, default: () => ({}) },
});

const t = props.strings;
const seatKinds = ['standard', 'wheelchair', 'companion', 'restricted_view'];

const planName = ref(props.planName);
const levels = ref([]);
const activeLevel = ref(0);
const selectedSectionId = ref(null);
const selectedSeats = ref([]);
const dirty = ref(false);
const saving = ref(false);
const error = ref('');
const svgEl = ref(null);

// Client-side ids are negative so the server, which treats any id it does not already own as new,
// can never mistake one for a real row - and a hand-edited payload cannot adopt somebody else's.
let tempId = -1;
const nextId = () => tempId--;

const rowForm = reactive({ rows: 10, perRow: 12, rowStyle: 'alpha', curve: 0, aisles: '' });
const tableForm = reactive({ count: 8, seats: 8, shape: 'round', mode: 'either', numbered: true });

const level = computed(() => levels.value[activeLevel.value] || null);
const section = computed(() => {
    if (!level.value) return null;
    return level.value.sections.find((s) => s.id === selectedSectionId.value) || null;
});
// The viewBox tracks the rendered ELEMENT rather than the level, so one design unit is one CSS
// pixel. Tying it to level.width/height meant the browser scaled the whole map down a second time
// to fit the centre column - a 1200-unit level in a ~525px box rendered everything at 44%, and the
// toolbar's zoom percentage was then a lie.
// Panning must never fight an element drag: a mousedown on a section, table or seat starts that
// drag instead, and the viewport stays out of the way until it ends.
const { zoom, pan, canvas, bind: viewportBind, fit: fitToView, zoomBy, measure: measureCanvas, observe: observeCanvas } =
    useMapViewport({ svgEl, contentBounds, canPan: () => !drag.mode });

const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);

const usageNotice = computed(() => {
    const events = Number(props.usage?.events || 0);
    const sold = Number(props.usage?.sold || 0);

    // Only :sold is interpolated: the events count is deliberately not, because a number sitting
    // next to a count-noun needs singular/plural agreement in every one of the twelve languages,
    // and "in use by 1 events" is what that costs when it is skipped.
    if (sold > 0) return (t.inUseSold || '').replace(':sold', sold);
    if (events > 0) return t.inUse || '';
    return '';
});

/** Tall enough to work in, proportioned to the room rather than fixed at 34rem. */
const canvasHeight = computed(() => {
    const b = contentBounds();
    if (!b) return '34rem';
    const ratio = Math.min(1.0, Math.max(0.45, b.h / b.w));
    return `${Math.round(Math.min(704, Math.max(384, canvas.w * ratio)))}px`;
});
const totalSeats = computed(() => levels.value.reduce((n, l) => n + seatsInLevel(l), 0));

function seatsInLevel(lvl) {
    return lvl.sections.reduce((n, s) => n + s.seats.length, 0);
}

// ---- geometry. Every coordinate is relative to its immediate parent, so dragging a section
// moves its rows and dragging a table moves its chairs with no cascade.
function seatX(s, seat) {
    const tb = seat.table_id ? s.tables.find((x) => x.id === seat.table_id) : null;
    return tb ? tb.x + seat.x : seat.x;
}
function seatY(s, seat) {
    const tb = seat.table_id ? s.tables.find((x) => x.id === seat.table_id) : null;
    return tb ? tb.y + seat.y : seat.y;
}
/**
 * The section's background box, derived from what it actually CONTAINS rather than from fixed
 * offsets. A curved row lifts its outer seats well above the first row's baseline, so a box
 * anchored at a constant -26 clipped them and ran the section label straight through seat 1.
 */
function sectionBox(s) {
    if (s.kind === 'standing') return { x: 0, y: 0, w: 240, h: 90 };

    const xs = [], ys = [];
    s.seats.forEach((seat) => { xs.push(seatX(s, seat)); ys.push(seatY(s, seat)); });
    s.tables.forEach((tb) => {
        xs.push(tb.x - tb.width / 2, tb.x + tb.width / 2);
        ys.push(tb.y - tb.height / 2, tb.y + tb.height / 2);
    });

    if (!xs.length) return { x: -16, y: -16, w: 160, h: 90 };

    const pad = 16;
    const minX = Math.min(...xs) - pad, maxX = Math.max(...xs) + pad;
    const minY = Math.min(...ys) - pad, maxY = Math.max(...ys) + pad;
    return { x: minX, y: minY, w: Math.max(80, maxX - minX), h: Math.max(60, maxY - minY) };
}

function seatFill(seat) {
    if (selectedSeats.value.includes(seat.id)) return 'var(--brand-blue)';
    if (seat.kind === 'wheelchair') return '#bfdbfe';
    if (seat.kind === 'companion') return 'transparent';
    return '#e5e7eb';
}
function seatStroke(seat) {
    return selectedSeats.value.includes(seat.id) ? 'var(--brand-blue)' : '#9ca3af';
}

// ---- selection
function selectLevel(i) {
    activeLevel.value = i;
    selectedSeats.value = [];
    selectedSectionId.value = levels.value[i]?.sections[0]?.id ?? null;
    fitToView();
}
function selectSection(s) {
    selectedSectionId.value = s.id;
    selectedSeats.value = [];
}
function isSelectedSection(s) {
    return s.id === selectedSectionId.value;
}

// ---- drag
const drag = reactive({ mode: null, id: null, startX: 0, startY: 0, originX: 0, originY: 0 });

function svgPoint(evt) {
    const svg = svgEl.value;
    if (!svg) return { x: 0, y: 0 };
    const rect = svg.getBoundingClientRect();
    const vb = svg.viewBox.baseVal;
    const scaleX = vb.width / rect.width;
    const scaleY = vb.height / rect.height;
    return {
        x: ((evt.clientX - rect.left) * scaleX - pan.x) / zoom.value,
        y: ((evt.clientY - rect.top) * scaleY - pan.y) / zoom.value,
    };
}

function startSectionDrag(evt, s) {
    selectSection(s);
    const p = svgPoint(evt);
    Object.assign(drag, { mode: 'section', id: s.id, startX: p.x, startY: p.y, originX: s.x, originY: s.y });
}
function startTableDrag(evt, tb) {
    const p = svgPoint(evt);
    Object.assign(drag, { mode: 'table', id: tb.id, startX: p.x, startY: p.y, originX: tb.x, originY: tb.y });
}
/**
 * Selecting a seat also makes its SECTION the active one.
 *
 * Without that, every action reads section.value.seats while the selection can hold ids from a
 * different section: applyKind, toggleAisle, removeSelectedSeats and the drag branch of onMove all
 * quietly matched nothing, while seatFill/seatStroke still drew the seat as selected. A selection
 * that looks live and does nothing is worse than either supporting or refusing it.
 *
 * Shift-extending across sections is therefore dropped rather than half-supported: a shift-click
 * in another section starts a fresh selection there.
 */
function onSeatDown(evt, seat, owner) {
    const crossSection = owner && owner.id !== selectedSectionId.value;
    if (crossSection) selectedSectionId.value = owner.id;

    if (evt.shiftKey && !crossSection) {
        const i = selectedSeats.value.indexOf(seat.id);
        if (i >= 0) selectedSeats.value.splice(i, 1);
        else selectedSeats.value.push(seat.id);
    } else {
        selectedSeats.value = [seat.id];
    }
    const p = svgPoint(evt);
    Object.assign(drag, { mode: 'seat', id: seat.id, startX: p.x, startY: p.y, originX: seat.x, originY: seat.y });
}

/**
 * Finding 8: the canvas was mouse-only, which is a poor look on the feature that sells wheelchair
 * spaces. Seats are focusable and describe themselves; Enter/Space selects, arrows nudge. The row
 * and table builders in the inspector remain the primary keyboard construction path.
 */
function seatAriaLabel(s, seat) {
    const bits = [s.name];
    if (seat.row_label) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row_label));
    if (seat.seat_label) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat_label));
    bits.push(t['kind_' + seat.kind] || seat.kind);
    return bits.filter(Boolean).join(', ');
}

function onSeatKey(evt, seat, owner) {
    const nudge = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] }[evt.key];

    if (evt.key === 'Enter' || evt.key === ' ') {
        evt.preventDefault();
        onSeatDown({ shiftKey: evt.shiftKey, clientX: 0, clientY: 0 }, seat, owner);
        drag.mode = null;
        return;
    }

    if (nudge) {
        evt.preventDefault();
        if (owner && owner.id !== selectedSectionId.value) selectedSectionId.value = owner.id;
        if (!selectedSeats.value.includes(seat.id)) selectedSeats.value = [seat.id];
        const step = evt.shiftKey ? 10 : 1;
        seat.x += nudge[0] * step;
        seat.y += nudge[1] * step;
        dirty.value = true;
    }
}
// Panning is the viewport's job; a press on bare canvas only drops the seat selection.
function onCanvasDown() {
    selectedSeats.value = [];
}
function onMove(evt) {
    if (!drag.mode) return;
    const p = svgPoint(evt);
    const dx = Math.round(p.x - drag.startX);
    const dy = Math.round(p.y - drag.startY);

    if (drag.mode === 'section' && section.value) {
        section.value.x = drag.originX + dx;
        section.value.y = drag.originY + dy;
    } else if (drag.mode === 'table' && section.value) {
        const tb = section.value.tables.find((x) => x.id === drag.id);
        if (tb) { tb.x = drag.originX + dx; tb.y = drag.originY + dy; }
    } else if (drag.mode === 'seat' && section.value) {
        const seat = section.value.seats.find((x) => x.id === drag.id);
        if (seat) { seat.x = drag.originX + dx; seat.y = drag.originY + dy; }
    }
    dirty.value = true;
}
function endDrag() {
    drag.mode = null;
}

/**
 * The bounding box of everything drawn on the active level, in section coordinates.
 * Used by fitToView so the map fills the canvas rather than sitting tiny in a corner - a
 * 16-seat row occupies about a third of a 1200-unit level, and at a fixed zoom of 1 that
 * renders unreadably small once the viewBox is scaled down into the centre column.
 */
function contentBounds() {
    const lvl = level.value;
    if (!lvl || !lvl.sections.length) return null;

    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    lvl.sections.forEach((s) => {
        const b = sectionBox(s);
        // -20 on the top edge leaves room for the section label, which sits above the box.
        minX = Math.min(minX, s.x + b.x);
        minY = Math.min(minY, s.y + b.y - 20);
        maxX = Math.max(maxX, s.x + b.x + b.w);
        maxY = Math.max(maxY, s.y + b.y + b.h);
    });

    if (!isFinite(minX)) return null;
    return { minX, minY, w: Math.max(1, maxX - minX), h: Math.max(1, maxY - minY) };
}



// ---- structure editing
function addLevel(name) {
    levels.value.push({
        id: nextId(),
        name: name || `${t.level} ${levels.value.length + 1}`,
        position: levels.value.length,
        width: 1200,
        height: 800,
        sections: [],
    });
    activeLevel.value = levels.value.length - 1;
    dirty.value = true;
}
function removeLevel(i) {
    levels.value.splice(i, 1);
    activeLevel.value = Math.max(0, i - 1);
    dirty.value = true;
}
function addSection(kind, attrs = {}) {
    if (!level.value) addLevel();
    const s = Object.assign({
        id: nextId(),
        name: kind === 'standing' ? t.standing : kind === 'table' ? t.tablesLabel : t.seating,
        color: '#4E81FA',
        kind,
        capacity: kind === 'standing' ? 100 : null,
        band: '',
        accessibility_only: false,
        x: 60 + level.value.sections.length * 40,
        y: 80 + level.value.sections.length * 40,
        rotation: 0,
        position: level.value.sections.length,
        tables: [],
        seats: [],
    }, attrs);
    level.value.sections.push(s);
    selectSection(s);
    dirty.value = true;
    return s;
}
function removeSection(s) {
    const i = level.value.sections.indexOf(s);
    if (i >= 0) level.value.sections.splice(i, 1);
    selectedSectionId.value = null;
    dirty.value = true;
}

function rowLabel(i) {
    if (rowForm.rowStyle === 'numeric') return String(i + 1);
    // A..Z then AA, AB - the label is cosmetic, row_position is what actually orders.
    let n = i, out = '';
    do { out = String.fromCharCode(65 + (n % 26)) + out; n = Math.floor(n / 26) - 1; } while (n >= 0);
    return out;
}

function generateRows() {
    const s = section.value;
    if (!s) return;
    const aisles = String(rowForm.aisles || '').split(',').map((x) => parseInt(x.trim(), 10)).filter((x) => x > 0);
    const gapX = 26, gapY = 30;
    const mid = (rowForm.perRow - 1) / 2;

    // Appended, not replaced, so a second block of rows can sit below the first.
    const startRow = s.seats.reduce((m, x) => Math.max(m, x.row_position), 0);

    for (let r = 0; r < rowForm.rows; r++) {
        const rp = startRow + r + 1;
        let extra = 0;
        for (let c = 0; c < rowForm.perRow; c++) {
            const curveY = rowForm.curve ? Math.round(rowForm.curve * Math.pow((c - mid) / (mid || 1), 2)) : 0;
            s.seats.push({
                id: nextId(),
                table_id: null,
                row_label: rowLabel(rp - 1),
                row_position: rp,
                seat_label: String(c + 1),
                x: c * gapX + extra,
                y: (rp - 1) * gapY - curveY,
                kind: 'standard',
                aisle_after: aisles.includes(c + 1),
                position: c + 1,
            });
            if (aisles.includes(c + 1)) extra += 18;
        }
    }
    fitToView();
    dirty.value = true;
}

function generateTables() {
    const s = section.value;
    if (!s) return;
    const perRow = Math.ceil(Math.sqrt(tableForm.count));
    const spacing = 170;
    const startIndex = s.tables.length;

    for (let i = 0; i < tableForm.count; i++) {
        const tb = {
            id: nextId(),
            label: `${t.tableLabel} ${startIndex + i + 1}`,
            shape: tableForm.shape,
            seat_count: tableForm.seats,
            booking_mode: tableForm.mode,
            x: (i % perRow) * spacing,
            y: Math.floor(i / perRow) * spacing,
            rotation: 0,
            width: 110,
            height: tableForm.shape === 'round' ? 110 : 80,
        };
        s.tables.push(tb);

        const radius = (tableForm.shape === 'round' ? tb.width / 2 : Math.max(tb.width, tb.height) / 2) + 18;
        for (let k = 0; k < tableForm.seats; k++) {
            const angle = (2 * Math.PI * k) / tableForm.seats - Math.PI / 2;
            s.seats.push({
                id: nextId(),
                table_id: tb.id,
                row_label: null,
                row_position: startIndex + i + 1,
                // Blank means "any seat at this table" - the guest books a chair, not a number.
                seat_label: tableForm.numbered ? String(k + 1) : null,
                x: Math.round(Math.cos(angle) * radius),
                y: Math.round(Math.sin(angle) * radius),
                kind: 'standard',
                aisle_after: false,
                position: k + 1,
            });
        }
    }
    fitToView();
    dirty.value = true;
}

function applyKind(kind) {
    const s = section.value;
    if (!s) return;
    s.seats.forEach((seat) => { if (selectedSeats.value.includes(seat.id)) seat.kind = kind; });
    dirty.value = true;
}
function toggleAisle() {
    const s = section.value;
    if (!s) return;
    s.seats.forEach((seat) => { if (selectedSeats.value.includes(seat.id)) seat.aisle_after = !seat.aisle_after; });
    dirty.value = true;
}
function removeSelectedSeats() {
    const s = section.value;
    if (!s) return;
    const locked = s.seats.filter((x) => selectedSeats.value.includes(x.id) && x.locked);
    if (locked.length) { error.value = t.cannotRemoveSold; return; }
    s.seats = s.seats.filter((x) => !selectedSeats.value.includes(x.id));
    selectedSeats.value = [];
    dirty.value = true;
}

// ---- presets: a blank canvas is the fastest way to lose somebody on their first plan
const presets = computed(() => [
    { key: 'theatre', label: t.presetTheatre, help: t.presetTheatreHelp },
    { key: 'cabaret', label: t.presetCabaret, help: t.presetCabaretHelp },
    { key: 'rows', label: t.presetRows, help: t.presetRowsHelp },
    { key: 'mixed', label: t.presetMixed, help: t.presetMixedHelp },
]);

function applyPreset(key) {
    levels.value = [];
    tempId = -1;

    if (key === 'theatre') {
        addLevel(t.presetStalls);
        let s = addSection('seated', { name: t.presetStalls, band: t.presetStalls, color: '#4E81FA' });
        Object.assign(rowForm, { rows: 12, perRow: 18, rowStyle: 'alpha', curve: 40, aisles: '6, 12' });
        generateRows();
        addLevel(t.presetBalcony);
        s = addSection('seated', { name: t.presetBalcony, band: t.presetBalcony, color: '#0EA5E9' });
        Object.assign(rowForm, { rows: 5, perRow: 16, rowStyle: 'alpha', curve: 30, aisles: '8' });
        generateRows();
    } else if (key === 'cabaret') {
        addLevel(t.presetFloor);
        addSection('table', { name: t.presetTables, band: t.presetTables, color: '#22D3EE' });
        Object.assign(tableForm, { count: 12, seats: 8, shape: 'round', mode: 'either', numbered: true });
        generateTables();
    } else if (key === 'rows') {
        addLevel(t.presetFloor);
        addSection('seated', { name: t.presetSeating, band: t.presetSeating });
        Object.assign(rowForm, { rows: 8, perRow: 10, rowStyle: 'numeric', curve: 0, aisles: '' });
        generateRows();
    } else if (key === 'mixed') {
        addLevel(t.presetFloor);
        addSection('seated', { name: t.presetSeated, band: t.presetSeated, color: '#4E81FA' });
        Object.assign(rowForm, { rows: 6, perRow: 14, rowStyle: 'alpha', curve: 20, aisles: '7' });
        generateRows();
        addSection('standing', { name: t.presetStanding, band: t.presetStanding, color: '#22D3EE', capacity: 200, x: 60, y: 420 });
    }

    activeLevel.value = 0;
    fitToView();
    dirty.value = true;
}

// ---- validation
const issues = computed(() => {
    const out = [];
    levels.value.forEach((lvl) => {
        lvl.sections.forEach((s) => {
            if (!s.name || !s.name.trim()) out.push(t.issueUnnamedSection);
            if (s.kind === 'seated' && !s.seats.length) out.push(`${s.name}: ${t.issueNoSeats}`);
            if (s.kind === 'standing' && !(s.capacity > 0)) out.push(`${s.name}: ${t.issueNoCapacity}`);
            if (!s.band || !s.band.trim()) out.push(`${s.name}: ${t.issueNoBand}`);

            const seen = new Set();
            s.seats.forEach((seat) => {
                if (!seat.seat_label) return;
                const key = `${seat.table_id || ''}|${seat.row_label || ''}|${seat.seat_label}`;
                if (seen.has(key)) out.push(`${s.name}: ${t.issueDuplicateSeat} ${seat.row_label || ''} ${seat.seat_label}`);
                seen.add(key);
            });
        });
    });
    return [...new Set(out)];
});

// ---- persistence
async function load() {
    const res = await fetch(props.structureUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
    if (!res.ok) { error.value = t.loadFailed; return; }
    const data = await res.json();
    levels.value = (data.levels || []).map((l) => Object.assign({}, l, {
        sections: (l.sections || []).map((s) => Object.assign({}, s, {
            band: s.band || '',
            tables: s.tables || [],
            seats: s.seats || [],
        })),
    }));
    if (levels.value.length) selectedSectionId.value = levels.value[0].sections[0]?.id ?? null;
    fitToView();
}

async function save() {
    saving.value = true;
    error.value = '';
    const previousSectionName = section.value?.name ?? null;
    try {
        const res = await fetch(props.saveUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ name: planName.value, levels: levels.value }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { error.value = data.error || t.saveFailed; return; }
        // Re-seed from the server so temporary ids become real ones; a second save would
        // otherwise create everything again.
        levels.value = (data.levels || []).map((l) => Object.assign({}, l, {
            sections: (l.sections || []).map((s) => Object.assign({}, s, {
                band: s.band || '', tables: s.tables || [], seats: s.seats || [],
            })),
        }));
        // Keep the section the user was working on. Ids change when temporary ones become real,
        // so match by NAME within the active level before falling back to the first section.
        const sections = levels.value[activeLevel.value]?.sections ?? [];
        const stillThere = previousSectionName
            ? sections.find((x) => x.name === previousSectionName)
            : null;
        selectedSectionId.value = (stillThere ?? sections[0])?.id ?? null;
        selectedSeats.value = [];
        dirty.value = false;
    } catch (e) {
        error.value = t.saveFailed;
    } finally {
        saving.value = false;
    }
}

/**
 * A seat map is slow to build and there is no autosave, so an accidental navigation used to throw
 * the whole thing away while the toolbar sat there saying "Unsaved changes" and doing nothing.
 */
function guardUnload(e) {
    if (!dirty.value) return;
    e.preventDefault();
    e.returnValue = '';
    return '';
}

onBeforeUnmount(() => window.removeEventListener('beforeunload', guardUnload));

onMounted(async () => {
    window.addEventListener('beforeunload', guardUnload);
    observeCanvas();
    await load();
    // The svg only has a size once v-else has rendered it, which is after load() sets levels.
    await nextTick();
    observeCanvas();
    fitToView();
});
</script>
