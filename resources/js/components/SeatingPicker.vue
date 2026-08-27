<template>
    <div class="mt-3">
        <!-- Choosing the seats IS choosing the quantity. There is no "how many?" step in front of
             this any more: it asked a question the next click answers. -->
        <div ref="rootEl" class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ t.pickYourSeats }}</p>

            <!-- The fast path, first, because most buyers do not want to choose a seat - they want
                 N seats together. This is what the docs and the feature page have always described.
                 It is an affordance over the same hold the map uses, so the two cannot disagree. -->
            <div v-if="!loading && mySections.length" class="mb-3 flex flex-wrap items-end gap-2">
                <div>
                    <label for="seatpick-party" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.howMany }}</label>
                    <select id="seatpick-party" v-model.number="partySize"
                        class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option v-for="n in maxSelectable" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
                <!-- Only when there is a choice to make. With one band the server picks it anyway. -->
                <div v-if="priceKey.length > 1">
                    <label for="seatpick-band" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.band }}</label>
                    <select id="seatpick-band" v-model="bestBandId"
                        class="mt-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option v-for="row in priceKey" :key="row.id" :value="row.id" :disabled="row.soldOut">
                            {{ row.label }}<template v-if="row.price"> &middot; {{ row.price }}</template>
                        </option>
                    </select>
                </div>
                <button type="button" id="seatpick-best" @click="findBestSeats" :disabled="finding || soldOut"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] disabled:opacity-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)]">
                    {{ t.findBest }}
                </button>
                <span class="text-xs text-gray-500 dark:text-gray-400 self-center">{{ t.orPick }}</span>
                <!-- accessibility_only shipped in the payload from the start and nothing used it, so
                     finding a wheelchair space meant spotting a 10-unit glyph across the whole map -
                     and it is illegible below the zoom at which seat numbers appear. -->
                <button v-if="accessibleSeat" type="button" id="seatpick-accessible" @click="goToAccessible"
                    class="ms-auto self-center inline-flex items-center gap-1 text-xs text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">
                    <span aria-hidden="true">&#9855;</span>{{ t.showAccessible }}
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-3 mb-3">
                <div class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button type="button" @click="mode = 'map'" :aria-pressed="mode === 'map'"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="mode === 'map'
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ t.mapView }}</button>
                    <button type="button" @click="mode = 'list'" :aria-pressed="mode === 'list'"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="mode === 'list'
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ t.listView }}</button>
                </div>
                <!-- Only worth showing when there is somewhere else to go. -->
                <div v-if="myLevels.length > 1" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button v-for="lvl in myLevels" :key="lvl.id" type="button" @click="activeLevelId = lvl.id"
                        :aria-pressed="lvl.id === activeLevel?.id"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="lvl.id === activeLevel?.id
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ lvl.name || t.level }}</button>
                </div>

                <div v-show="mode === 'map'" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button type="button" @click="zoomBy(-0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomOut">&minus;</button>
                    <button type="button" @click="zoomBy(0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomIn">+</button>
                    <button type="button" @click="fit" class="px-2 py-1 rounded text-xs text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200">{{ t.fit }}</button>
                </div>

            </div>

            <!-- Where to look. A fitted 1,200-seat house is a grey field: seat numbers vanish below
                 0.75 zoom and there is no way in short of pinching around hunting for a gap. Each
                 chip zooms to one section and says how much of it is left, which is the question
                 ahead of "which seat" - and it doubles as the sold-out signal a section never had. -->
            <div v-if="mode === 'map' && sectionJumps.length > 1" class="mb-3 flex flex-wrap gap-1.5">
                <button v-for="jump in sectionJumps" :key="jump.id" type="button"
                    @click="zoomToSection(jump.id)" :disabled="!jump.free"
                    class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-1 text-xs transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="jump.id === zoomedSectionId
                        ? 'border-[var(--brand-blue)] text-[var(--brand-blue)] font-medium'
                        : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)]'">
                    <span class="w-2.5 h-2.5 rounded-full border shrink-0" :style="{ background: jump.color, borderColor: jump.stroke }"></span>
                    {{ jump.name }}
                    <span class="text-gray-400 dark:text-gray-500">{{ jump.free ? jump.free : t.soldOut }}</span>
                </button>
                <button v-if="zoomedSectionId" type="button" @click="showWholeMap"
                    class="rounded-lg px-2 py-1 text-xs text-[var(--brand-blue)] hover:underline">{{ t.wholeMap }}</button>
            </div>

            <!-- A lapsed hold is a thing that happened TO the buyer, so it says so rather than
                 letting the seats quietly leave the selection. -->
            <div v-if="lapsed" class="mb-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200" role="status">
                {{ t.holdLapsed }}
                <button type="button" @click="pickAgain" class="ms-2 font-medium underline">{{ t.pickAgain }}</button>
            </div>

            <!-- A refusal: the server would not do what was asked. Red, and it clears on the next
                 successful hold. -->
            <p v-if="error" id="seatpick-error" role="status" aria-live="polite"
                class="mb-3 rounded-lg border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-2 text-sm text-red-800 dark:text-red-200">
                {{ error }}
            </p>

            <!-- A warning: the seats ARE held, this is just not an arrangement the venue will
                 sell. It travels with the map state, so it survives a reload, and it blocks
                 checkout - which is why it has to name the seat and offer the fix rather than
                 leaving the buyer to hunt for a single gap on a three-hundred-seat map. -->
            <div v-if="warning" id="seatpick-warning" role="status" aria-live="polite"
                class="mb-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-2 text-sm text-amber-800 dark:text-amber-200">
                <p>{{ warning.message }}</p>
                <button v-if="strandedSeat" type="button" id="seatpick-fix"
                    class="mt-2 inline-flex items-center gap-1 rounded-lg border border-amber-300 dark:border-amber-600 bg-white dark:bg-amber-900/40 px-3 py-1.5 font-medium text-amber-900 dark:text-amber-100 transition-colors hover:bg-amber-100 dark:hover:bg-amber-900/70 focus:outline-none focus:ring-2 focus:ring-amber-500"
                    @click="takeStrandedSeat">
                    {{ (t.addSeat || 'Add :seat').replace(':seat', strandedSeat.label) }}
                    <span v-if="strandedSeat.price"> &middot; {{ strandedSeat.price }}</span>
                </button>
            </div>

            <p v-if="loading" class="text-sm text-gray-500 dark:text-gray-400">{{ t.loading }}</p>

            <template v-else-if="mySections.length">
                <!-- MAP -->
                <div v-show="mode === 'map'">
                    <!-- pan-y, not none: this map sits inside a long checkout form, and taking
                         every touch would mean a buyer swiping up from the map stays put instead of
                         scrolling on. Vertical swipes scroll the page, horizontal drag pans, and
                         zooming has the -/+/Fit buttons. The full-page tools keep touch-action:none. -->
                    <svg ref="svgEl" v-bind="bind" :viewBox="viewBox" class="w-full select-none touch-pan-y"
                        :style="{ height: mapHeight, cursor: 'grab' }" role="group" :aria-label="t.mapLabel">
                        <defs>
                            <pattern id="seatTakenHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                <line x1="0" y1="0" x2="0" y2="4" stroke="#6b7280" stroke-width="1.6" />
                            </pattern>
                            <!-- Restricted view. Same 45-degree hatch the designer draws it with,
                                 lighter, so the organizer and the buyer see the same seat. -->
                            <pattern id="seatRestrictedHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                <line x1="0" y1="0" x2="0" y2="4" stroke="#6b7280" stroke-width="1" opacity="0.55" />
                            </pattern>
                        </defs>
                        <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                        <!-- Drawn first so it can never sit over a seat, and pointer-events none
                             throughout so it can never take a press meant for one. This is what
                             tells the buyer which way the room faces. -->
                        <g v-for="d in myDecorations" :key="`dec-${d.id}`" pointer-events="none"
                            :transform="`translate(${d.x} ${d.y}) rotate(${d.rotation || 0})`">
                            <rect v-if="d.kind === 'stage'" :width="d.width" :height="d.height" rx="4"
                                class="fill-gray-200 dark:fill-gray-700" />
                            <text :x="d.width / 2" :y="d.height / 2" text-anchor="middle" dy="4"
                                :font-size="d.kind === 'stage' ? 13 : 11"
                                :class="d.kind === 'stage'
                                    ? 'fill-gray-600 dark:fill-gray-300 uppercase tracking-widest'
                                    : 'fill-gray-400 dark:fill-gray-500'">{{ d.label }}</text>
                        </g>

                        <g v-for="s in mySections" :key="s.id" :transform="sectionTransform(s)">
                            <text :x="0" :y="-10" font-size="12" class="fill-gray-500 dark:fill-gray-400">
                                {{ s.name }}<tspan v-if="priceForSection(s)"> &middot; {{ priceForSection(s) }}</tspan>
                            </text>
                            <!-- Row labels sit beside the row, the way the list view already names them. -->
                            <template v-if="showSeatLabels">
                                <text v-for="row in rowsOf(s)" :key="`rl-${row.key}`"
                                    :x="rowLabelX(s, row) - 16" :y="rowLabelY(s, row) + 4"
                                    text-anchor="end" font-size="9" class="fill-gray-400 dark:fill-gray-500">{{ row.gutter }}</text>
                            </template>
                            <!-- The table itself. The designer draws these and the picker never did,
                                 so a cabaret plan reached the buyer as floating rings of anonymous
                                 seats with no way to tell one table from the next - while the
                                 ticket and the confirmation email both say "Table 4, Seat 3".
                                 Inert, like the decorations: a press belongs to a chair. -->
                            <g v-for="tb in (s.tables || [])" :key="`tb-${tb.id}`" pointer-events="none"
                                :transform="`translate(${tb.x} ${tb.y}) rotate(${tb.rotation || 0})`">
                                <circle v-if="tb.shape === 'round'" :r="tb.width / 2" :fill="s.color" fill-opacity="0.18"
                                    :stroke="s.color" stroke-opacity="0.5"
                                    :stroke-dasharray="tb.booking_mode === 'whole' ? '5 3' : null" />
                                <rect v-else :x="-tb.width / 2" :y="-tb.height / 2" :width="tb.width" :height="tb.height" rx="4"
                                    :fill="s.color" fill-opacity="0.18" :stroke="s.color" stroke-opacity="0.5"
                                    :stroke-dasharray="tb.booking_mode === 'whole' ? '5 3' : null" />
                                <text text-anchor="middle" dy="4" font-size="11"
                                    class="fill-gray-600 dark:fill-gray-300">{{ tb.label }}</text>
                            </g>

                            <g v-for="seat in s.seats" :key="seat.id"
                                :transform="`translate(${seatX(s, seat)} ${seatY(s, seat)})`">
                                <!-- The drawn disc, and nothing else: it takes no pointer events so
                                     the larger invisible target below owns every press. -->
                                <circle r="9" :fill="fillFor(s, seat)" :stroke="strokeFor(s, seat)" stroke-width="1.5"
                                    :stroke-dasharray="seat.kind === 'companion' ? '3 2' : null"
                                    pointer-events="none" />

                                <!-- A 9-unit disc is a few CSS pixels at a fitted zoom, which is a
                                     hard target with a mouse and hopeless with a thumb. Grow the
                                     TARGET, not the drawing. -->
                                <!-- The id lives on THIS one, not on the drawn disc: the disc takes
                                     no pointer events, so anything driving a click needs the target. -->
                                <circle :id="`seat-${uid}-${seat.id}`" r="15" fill="transparent"
                                    class="seatpick-target"
                                    :class="isBlocked(seat) ? '' : 'cursor-pointer'"
                                    role="button"
                                    :tabindex="seat.id === focusedSeatId ? 0 : -1"
                                    :aria-label="priced(s, seat)"
                                    :aria-pressed="isSelected(seat)"
                                    :aria-disabled="isBlocked(seat)"
                                    @focus="focusedSeatId = seat.id; hovered = { s, seat }"
                                    @blur="hovered = null"
                                    @mouseenter="hovered = { s, seat }"
                                    @mouseleave="hovered = null"
                                    @click="toggle(seat)"
                                    @keydown="onSeatKey($event, seat)" />
                                <!-- Taken seats carry a HATCH as well as a darker fill: status must
                                     never be encoded by colour alone, and two greys is exactly that. -->
                                <circle v-if="seat.state === 'taken'" r="9" fill="url(#seatTakenHatch)"
                                    opacity="0.55" pointer-events="none" />
                                <circle v-else-if="seat.kind === 'restricted_view'" r="9" fill="url(#seatRestrictedHatch)"
                                    pointer-events="none" />
                                <!-- pointer-events none, like every other overlay here: without it a
                                     press landing on the glyph targets the <text>, which has no
                                     handler, so wheelchair seats were dead to the mouse. -->
                                <!-- The glyph sits ABOVE the number rather than replacing it: a
                                     wheelchair space never showed its seat number at all, so it
                                     could not be read out to somebody on the phone. -->
                                <text v-if="seat.kind === 'wheelchair'" text-anchor="middle"
                                    :dy="showSeatLabels && seat.seat ? -1 : 4" font-size="10"
                                    fill="#1f2937" pointer-events="none">&#9855;</text>
                                <text v-if="showSeatLabels && seat.seat" text-anchor="middle"
                                    :dy="seat.kind === 'wheelchair' ? 9 : 3.5"
                                    :font-size="seat.kind === 'wheelchair' ? 7 : 9"
                                    :fill="labelInkFor(seat)" pointer-events="none">{{ seat.seat }}</text>
                            </g>
                        </g>
                        </g>
                    </svg>
                    <!-- What you are about to click. Without it, on a fitted map a mouse user cannot
                     tell one grey disc from another without zooming or switching to the list. -->
                <p class="mt-1 min-h-[1.25rem] text-xs" aria-hidden="true">
                    <span v-if="hoveredLabel" class="text-gray-600 dark:text-gray-300">{{ hoveredLabel }}</span>
                    <span v-else-if="!showSeatLabels" class="text-gray-400 dark:text-gray-500">{{ t.zoomForNumbers }}</span>
                    <!-- Keyboard advice belongs to keyboard users, not to everyone with a mouse. -->
                    <span v-else-if="keyboardActive" class="text-gray-400 dark:text-gray-500">{{ t.keyboardHint }}</span>
                </p>
                <p v-if="wheelBlocked" class="mt-1 text-xs text-gray-500 dark:text-gray-400" role="status">{{ t.wheelHint }}
                </p>
                </div>

                <!-- LIST: a complete alternative path, not a summary. A purchase can be finished
                     here with no map at all, which is what makes this usable with a screen reader. -->
                <div v-show="mode === 'list'" class="space-y-3">
                    <!-- Every level, not just the one on the map. The map shows one level at a time
                         because a balcony is not beside the stalls; the list has no such problem and
                         staying complete is the whole point of it. -->
                    <div v-for="lvl in myLevels" :key="`lvl-${lvl.id}`">
                        <h3 v-if="myLevels.length > 1" class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ lvl.name || t.level }}</h3>
                    <div v-for="s in lvl.sections" :key="s.id">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ s.name }}</h4>
                        <div v-for="row in rowsOf(s)" :key="row.key" class="mt-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400 me-2">{{ row.label }}</span>
                            <button v-for="seat in row.seats" :key="seat.id" type="button"
                                @click="toggle(seat)"
                                :disabled="isBlocked(seat)"
                                :aria-label="priced(s, seat)"
                                :aria-pressed="isSelected(seat)"
                                class="inline-flex items-center justify-center w-11 h-11 m-0.5 rounded text-xs border transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                                :class="isSelected(seat)
                                    ? 'bg-[var(--brand-button-bg)] text-white border-transparent'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-[var(--brand-blue)]'">
                                {{ seat.seat || '&middot;' }}<span v-if="kindMark(seat)" aria-hidden="true"
                                    class="ms-0.5 text-[10px] leading-none">{{ kindMark(seat) }}</span>
                            </button>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Legend. Status is never colour alone: taken seats are also dimmed and
                     disabled, and every seat states itself in its aria-label. -->
                <!-- Which colour is which price. The seats have always been tinted by band; with
                     one band per map there was nothing for that to mean. -->
                <div v-if="priceKey.length > 1" class="mt-3 flex flex-wrap gap-4 text-xs">
                    <span v-for="row in priceKey" :key="row.id" class="flex items-center gap-1"
                        :class="row.soldOut ? 'text-gray-400 dark:text-gray-500 line-through' : 'text-gray-600 dark:text-gray-300'">
                        <span class="w-3 h-3 rounded-full border" :style="{ background: row.color, borderColor: row.stroke }"></span>
                        {{ row.label }}<template v-if="row.price"> &middot; {{ row.price }}</template>
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="background:#e5e7eb;border-color:#9ca3af"></span>{{ t.legendAvailable }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:var(--brand-blue)"></span>{{ t.legendSelected }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border"
                        style="border-color:#6b7280;background:repeating-linear-gradient(45deg,#9ca3af,#9ca3af 1.5px,#6b7280 1.5px,#6b7280 3px)"></span>{{ t.legendTaken }}</span>
                    <!-- Only when the map actually has one, or it is a key to nothing. -->
                    <span v-if="hasUnavailable" class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border"
                        style="background:#9ca3af;border-color:#6b7280"></span>{{ t.legendUnavailable }}</span>
                    <span v-if="hasKind('restricted_view')" class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border"
                        style="border-color:#9ca3af;background:repeating-linear-gradient(45deg,#e5e7eb,#e5e7eb 1px,#9ca3af 1px,#9ca3af 2px)"></span>{{ t.restrictedView }}</span>
                    <span v-if="hasKind('companion')" class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border border-dashed"
                        style="background:#e5e7eb;border-color:#6b7280"></span>{{ t.companion }}</span>
                    <span v-if="hasKind('wheelchair')" class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border"
                        style="background:#bfdbfe;border-color:#9ca3af"></span>{{ t.wheelchair }}</span>
                </div>
            </template>

            <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ t.noSeats }}</p>

            <!-- The basket only appears once there is something in it, so without this the panel
                 offers no instruction at all. -->
            <p v-if="!selectedSeats.length && !loading" class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                {{ t.clickToChoose }}
            </p>
        </div>

        <!-- The basket: what you picked, what each band costs, what it comes to, and how long it
             is yours - in one panel, beside the seats, rather than a wall of pills and an unrelated
             total line. The parent form still owns the authoritative total; this is the running one. -->
        <div v-if="selectedSeats.length" id="seatpick-basket" aria-live="polite"
            class="mt-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ t.yourSeats }}</p>

            <div v-for="group in basket" :key="group.id" class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1">
                <span class="w-3 h-3 self-center rounded-full border shrink-0"
                    :style="{ background: group.color, borderColor: group.stroke }"></span>
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ group.label }}</span>
                <span v-if="group.price" class="text-xs text-gray-500 dark:text-gray-400">{{ group.price }}</span>

                <span class="flex flex-wrap gap-1">
                    <!-- One chip per seat, each droppable on its own: the comma-joined list this
                         replaced left "clear the lot and start again" as the only way to change
                         your mind. Within a band the seat alone is enough. -->
                    <span v-for="seat in group.seats" :key="seat.id"
                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 ps-2.5 pe-1 py-0.5 text-xs text-gray-700 dark:text-gray-300">
                        {{ seat.short }}
                        <button type="button" @click="removeSeat(seat.id)"
                            :aria-label="(t.removeSeat || 'Remove :seat').replace(':seat', seat.label)"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">&times;</button>
                    </span>
                </span>

                <span v-if="group.price" class="ms-auto text-sm text-gray-700 dark:text-gray-300">{{ money(group.subtotal) }}</span>
            </div>

            <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                <span id="seatpick-total" class="font-medium text-gray-900 dark:text-gray-100">{{ runningTotalLabel }}</span>
                <span v-if="countdown" id="seatpick-countdown" class="ms-auto"
                    :class="countdownSoon ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400'">
                    {{ t.holdingFor }} {{ countdown }}
                    <button v-if="countdownSoon" type="button" @click="pushHold"
                        class="ms-1 text-[var(--brand-blue)] hover:underline">{{ t.moreTime }}</button>
                </span>
            </div>
        </div>

        <!-- No waitlist call to action here on purpose: when EVERY band is gone the parent form
             hides the ticket rows and renders its own (Pro-gated) waitlist panel. This line only
             appears while other bands are still buyable, where a waitlist would be the wrong offer
             and the panel would not render anyway. -->
        <p v-else-if="soldOut" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t.soldOut }}</p>

        <!-- What the checkout POST actually claims. -->
        <input v-for="id in selected" :key="id" type="hidden" name="seat_ids[]" :value="id">
        <!-- One line per band, derived from the seats themselves. A single instance means these
             agree with the hold by construction. -->
        <input v-for="x in tickets" :key="`q-${x.id}`" type="hidden"
            :name="`tickets[${x.id}]`" :value="quantities[x.id] || 0">
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { loadMap, startPolling } from '../seat-map-store';
import { useMapViewport } from '../seat-map-viewport';
import { sectionTransform, toCanvasFrame } from '../seat-map-geometry';

let instanceSeq = 0;

const props = defineProps({
    /**
     * EVERY allocated ticket on the event, not one.
     *
     * One picker per band meant each instance posted only its own seats, and acquire() replaces the
     * session token's whole selection - so picking in a second band silently released the first,
     * and checkout then failed the books-balance guard. One instance posts one complete selection,
     * which is what the server was always built for.
     *
     * Each entry: { id, type, price, priceLabel, quantity }.
     */
    tickets: { type: Array, required: true },
    /** Ceiling for the whole order, on top of each band's own cap. */
    perOrderMax: { type: Number, default: 20 },
    eventId: { type: String, required: true },
    date: { type: String, default: '' },
    stateUrl: { type: String, required: true },
    holdUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
    strings: { type: Object, default: () => ({}) },
});

const t = props.strings;
// Distinguishes this picker's seat elements from the other bands' on the same page.
const uid = ++instanceSeq;
/**
 * 'map' | 'list'. There is no quantity mode any more.
 *
 * List is the default on a small screen: the map is at its worst in a narrow column, and the list
 * is the same choice without the spatial reasoning - grouped by section and row, every seat a real
 * button. The toggle still reaches either from either.
 */
const mode = ref(typeof window !== 'undefined' && window.innerWidth < 640 ? 'list' : 'map');
const rootEl = ref(null);
const focusedSeatId = ref(null);
const hovered = ref(null);
// The keyboard hint appears once somebody actually uses the keyboard in the map.
const keyboardActive = ref(false);

/** Section, row, seat and price - what the buyer is about to take. */
const hoveredLabel = computed(() => {
    const h = hovered.value;
    if (! h) return '';

    return priced(h.s, h.seat);
});
const selected = ref([]);
/** The party size for the best-available path. Not the selection - that is `selected`. */
const partySize = ref(2);
// Seeded, not null: the select is bound to this, so leaving it empty rendered a blank dropdown
// beside a button that quietly used the first band anyway.
const bestBandId = ref(
    (props.tickets.find((x) => (Number(x.quantity) || 0) > 0) || props.tickets[0] || {}).id ?? null
);
const finding = ref(false);
const map = ref(null);
const loading = ref(false);
const error = ref('');
/**
 * Advisory, not a refusal: the seats ARE held. Replaced from every hold response - including with
 * null - so a warning the buyer has since fixed disappears on its own.
 */
const warning = ref(null);
const expiresAt = ref(null);
const now = ref(Date.now());
let tick = null;

const ticketById = computed(() => Object.fromEntries(props.tickets.map((x) => [x.id, x])));

/** Every band's remaining stock added up, capped by what one order may take. */
const maxSelectable = computed(() => Math.min(
    Math.max(1, Number(props.perOrderMax) || 20),
    props.tickets.reduce((n, x) => n + Math.max(0, Number(x.quantity) || 0), 0),
));
const soldOut = computed(() => maxSelectable.value === 0);

/** How many the buyer says they want. A filter, not a gate - editable at any point. */

/** seat id -> { section, seat, ticket }, so a seat can name its own band anywhere. */
const seatIndex = computed(() => {
    const out = {};
    (map.value?.levels || []).forEach((lvl) => (lvl.sections || []).forEach((sec) => {
        const ticket = ticketById.value[sec.ticket_id] || null;
        (sec.seats || []).forEach((seat) => { out[seat.id] = { section: sec, seat, ticket }; });
    }));

    return out;
});

const ticketFor = (seatId) => seatIndex.value[seatId]?.ticket || null;

/**
 * The seat the advisory is about, when this picker can actually offer to take it.
 *
 * Not every stranded seat is offerable - it can be a wheelchair space, a seat somebody else has
 * since taken, or one in a band this form is not selling - so the button appears only when an
 * ordinary click on that seat would work. Otherwise the wording has to stand on its own.
 */
const strandedSeat = computed(() => {
    const id = warning.value?.seat_ids?.[0];
    const entry = id ? seatIndex.value[id] : null;

    if (! entry || isBlocked(entry.seat) || selected.value.includes(id)) return null;

    return { id, label: warning.value.label, price: entry.ticket?.priceLabel || '' };
});

/** Take it. toggle() already owns the per-band caps, the whole-table grouping and the hold. */
function takeStrandedSeat() {
    const entry = strandedSeat.value ? seatIndex.value[strandedSeat.value.id] : null;
    if (entry) toggle(entry.seat);
}

/** Each section labels its own band's price now that several bands share one map. */
const priceForSection = (sec) => ticketById.value[sec.ticket_id]?.priceLabel || '';

/** What the ticket form needs: a count per band, derived from the seats themselves. */
const quantities = computed(() => {
    const out = {};
    props.tickets.forEach((x) => { out[x.id] = 0; });
    selected.value.forEach((id) => {
        const ticket = ticketFor(id);
        if (ticket) out[ticket.id] = (out[ticket.id] || 0) + 1;
    });

    return out;
});

/**
 * The whole venue, every band on one map.
 *
 * This used to filter to `s.ticket_id === props.ticket.id`, which is why two price bands were never
 * on one map and a buyer had to choose a price before seeing any seats. Sections are still shown one
 * LEVEL at a time - levels are separate spaces and every level's first section is seeded at the same
 * origin, so drawing them together superimposes the balcony on the stalls.
 *
 * Standing sections are kept: they have a band and a ticket but no seats, and dropping them left a
 * hole in the room on any mixed seated-and-standing house.
 */
const myLevels = computed(() => {
    if (!map.value) return [];

    return (map.value.levels || [])
        .map((lvl) => ({
            id: lvl.id,
            name: lvl.name,
            // Kept whole even though the SECTIONS are filtered to this form's bands: the stage is
            // where the room faces, not something this ticket owns, and a buyer looking at the
            // circle needs it as much as one looking at the stalls.
            decorations: lvl.decorations || [],
            sections: (lvl.sections || []).filter((s) => ticketById.value[s.ticket_id]),
        }))
        .filter((lvl) => lvl.sections.length);
});

/**
 * The first takeable wheelchair space anywhere this form sells, or null.
 *
 * Every level, not just the one on screen - accessible seating is commonly its own section on the
 * ground floor while the buyer is looking at the circle.
 */
const accessibleSeat = computed(() => {
    for (const lvl of myLevels.value) {
        for (const sec of lvl.sections) {
            if (! sec.accessibility_only) continue;

            const seat = sec.seats.find((x) => x.kind === 'wheelchair' && ! isBlocked(x));
            if (seat) return { levelId: lvl.id, section: sec, seat };
        }
    }

    return null;
});

/** Show it, switching level if it is elsewhere, and put the keyboard on it. */
function goToAccessible() {
    const hit = accessibleSeat.value;
    if (! hit) return;

    if (hit.levelId !== activeLevel.value?.id) activeLevelId.value = hit.levelId;

    nextTick(() => {
        revealPoint(...toCanvasFrame(hit.section, seatX(hit.section, hit.seat), seatY(hit.section, hit.seat)));
        focusedSeatId.value = hit.seat.id;
        nextTick(() => document.getElementById(`seat-${uid}-${hit.seat.id}`)?.focus());
    });
}

/** The table a seat belongs to, if any. */
function tableOf(s, seat) {
    return seat.table_id ? (s.tables || []).find((x) => x.id === seat.table_id) || null : null;
}

/**
 * The next seat in this direction that can actually be taken.
 *
 * focusableSeats holds every seat regardless of state, so arrowing walked through sold ones - a
 * keyboard user crossed a block of seats none of which would respond to Enter. Falls back to the
 * immediate neighbour when everything ahead is blocked, so focus still moves.
 */
function nextTakeable(list, from, step) {
    for (let i = from + step; i >= 0 && i < list.length; i += step) {
        if (! isBlocked(list[i])) return list[i];
    }

    return list[from + step] || null;
}

/** The nearest takeable seat one row away, matched on horizontal position. */
function seatInAdjacentRow(seat, step) {
    const owner = mySections.value.find((s) => s.seats.includes(seat));
    if (! owner) return null;

    const rows = rowsOf(owner);
    const index = rows.findIndex((r) => r.seats.includes(seat));
    const target = rows[index + step];
    if (! target || ! target.seats.length) return null;

    const x = seatX(owner, seat);
    const free = target.seats.filter((s) => ! isBlocked(s));

    return (free.length ? free : target.seats)
        .reduce((best, s) => (Math.abs(seatX(owner, s) - x) < Math.abs(seatX(owner, best) - x) ? s : best));
}

/**
 * A one-glyph mark for the list view, which rendered the seat number and nothing else.
 *
 * The list is the complete alternative path - a purchase can be finished there with no map at all -
 * so a wheelchair space, a companion seat and a restricted view were all indistinguishable from an
 * ordinary seat for anyone using it. aria-hidden because labelFor() already says it in words.
 */
function kindMark(seat) {
    if (seat.kind === 'wheelchair') return '\u267F';
    if (seat.kind === 'companion') return '\u00B7';
    if (seat.kind === 'restricted_view') return '\u2298';
    return '';
}

/** Whether this level draws a seat of that kind, so the legend only keys what is on screen. */
function hasKind(kind) {
    return mySections.value.some((s) => s.seats.some((seat) => seat.kind === kind));
}

/** Whether anything on this level is rule-blocked, so the legend only keys what is drawn. */
const hasUnavailable = computed(() => mySections.value.some((sec) => (sec.seats || []).some((x) => x.state === 'unavailable')));

/**
 * One row per band: the price key under the map, and the best-available list above it.
 *
 * Built from the TICKETS, not from the map. Deriving it from the drawn sections meant it was empty
 * until the map had been fetched - and the map is only fetched when the buyer opens it, so the
 * band rows in quantity mode never appeared at all.
 */
const bandColors = computed(() => {
    const out = {};
    myLevels.value.forEach((lvl) => lvl.sections.forEach((sec) => {
        if (sec.ticket_id && !out[sec.ticket_id]) out[sec.ticket_id] = sec.color;
    }));

    return out;
});

const priceKey = computed(() => props.tickets.map((ticket) => {
    const color = bandColors.value[ticket.id];

    return {
        id: ticket.id,
        label: ticket.type,
        price: ticket.priceLabel || '',
        color: tint(color) || '#e5e7eb',
        stroke: color || '#9ca3af',
        soldOut: (Number(ticket.quantity) || 0) === 0,
    };
}));

const activeLevelId = ref(null);
const activeLevel = computed(() => myLevels.value.find((l) => l.id === activeLevelId.value) || myLevels.value[0] || null);

/** Only what is on screen. */
const mySections = computed(() => (activeLevel.value && activeLevel.value.sections) || []);

/** One chip per section on this level: its colour, its name, and how many seats are left in it. */
const sectionJumps = computed(() => mySections.value.map((sec) => ({
    id: sec.id,
    name: sec.name,
    color: tint(sec.color) || '#e5e7eb',
    stroke: sec.color || '#9ca3af',
    free: sec.seats.filter((seat) => seat.state === 'available').length,
})));

const zoomedSectionId = ref(null);

/** Frame one section, so a big house is entered rather than searched. */
function zoomToSection(id) {
    const sec = mySections.value.find((x) => x.id === id);
    if (! sec || ! sec.seats.length) return;

    zoomedSectionId.value = id;

    const xs = [], ys = [];
    sec.seats.forEach((seat) => {
        const [cx, cy] = toCanvasFrame(sec, seatX(sec, seat), seatY(sec, seat));
        xs.push(cx); ys.push(cy);
    });

    fitTo({
        minX: Math.min(...xs) - 30,
        minY: Math.min(...ys) - 40,
        w: Math.max(1, Math.max(...xs) - Math.min(...xs) + 60),
        h: Math.max(1, Math.max(...ys) - Math.min(...ys) + 70),
    });
}

function showWholeMap() {
    zoomedSectionId.value = null;
    fit();
}

/** The stage and any labels on the level being drawn. Never interactive. */
const myDecorations = computed(() => (activeLevel.value && activeLevel.value.decorations) || []);

/** Every section this ticket prices, on every level - what a held seat is resolved against. */
const allMySections = computed(() => myLevels.value.flatMap((l) => l.sections));

const svgEl = ref(null);

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

/** Bounding box of the level on screen, in content units. */
function contentBounds() {
    const xs = [], ys = [];
    // A stage sits above row A, so leaving it out means Fit frames it off screen.
    myDecorations.value.forEach((d) => {
        decorationCorners(d).forEach(([x, y]) => { xs.push(x); ys.push(y); });
    });
    mySections.value.forEach((s) => s.seats.forEach((seat) => {
        const [cx, cy] = toCanvasFrame(s, seatX(s, seat), seatY(s, seat));
        xs.push(cx); ys.push(cy);
    }));
    if (!xs.length) return null;
    const pad = 18;
    const minX = Math.min(...xs) - pad, minY = Math.min(...ys) - pad - 14;
    return { minX, minY, w: Math.max(1, Math.max(...xs) - minX + pad), h: Math.max(1, Math.max(...ys) - minY + pad) };
}

const { zoom, pan, canvas, bind, fit, fitTo, zoomBy, observe, revealPoint, wheelBlocked } = useMapViewport({ svgEl, contentBounds, wheelNeedsModifier: true });

/**
 * The canvas IS the viewBox, and zoom/pan move the content inside it. Fitting the viewBox to the
 * content instead letterboxed a wide, shallow room in a tall box and left no way to get closer than
 * whatever the column happened to be - which on a phone is nothing like close enough.
 */
const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);
const mapHeight = computed(() => {
    const b = contentBounds();
    if (!b) return '18rem';
    const ratio = Math.min(1.1, Math.max(0.28, b.h / b.w));
    return `${Math.round(Math.min(512, Math.max(160, canvas.w * ratio)))}px`;
});

/**
 * Seat numbers are only drawn once they are big enough to read.
 *
 * A 9-unit seat at 0.4 zoom is under 4 pixels: printing a label in it turns the map into grey mush,
 * which is why the list view existed as the only place to see a seat number at all.
 */
const showSeatLabels = computed(() => zoom.value >= 0.75);

/**
 * Sections whose seats carry no usable geometry.
 *
 * The designer's row builder assigns x/y, but a plan built any other way - the API, a future
 * importer, hand-inserted rows - can arrive with every seat at the origin. Rendered literally that
 * is 36 seats stacked in one place and a map nobody can use, so fall back to laying the section out
 * from row_position and position, which every seat has.
 */
const degenerateSections = computed(() => {
    const out = new Set();
    allMySections.value.forEach((s) => {
        const xs = new Set(s.seats.map((x) => x.x));
        const ys = new Set(s.seats.map((x) => x.y));
        if (s.seats.length > 1 && xs.size === 1 && ys.size === 1) out.add(s.id);
    });
    return out;
});

const GAP_X = 26;
const GAP_Y = 30;

function seatX(s, seat) {
    if (degenerateSections.value.has(s.id)) {
        return (rowsOf(s).find((r) => r.seats.includes(seat))?.seats.indexOf(seat) ?? 0) * GAP_X;
    }
    const tb = seat.table_id ? (s.tables || []).find((x) => x.id === seat.table_id) : null;
    return tb ? tb.x + seat.x : seat.x;
}
function seatY(s, seat) {
    if (degenerateSections.value.has(s.id)) {
        const rows = rowsOf(s);
        return rows.findIndex((r) => r.seats.includes(seat)) * GAP_Y;
    }
    const tb = seat.table_id ? (s.tables || []).find((x) => x.id === seat.table_id) : null;
    return tb ? tb.y + seat.y : seat.y;
}

/** Every seat this picker can move focus through, in reading order. */
const focusableSeats = computed(() => {
    const out = [];
    mySections.value.forEach((s) => s.seats.forEach((seat) => out.push(seat)));
    return out;
});

/**
 * Roving tabindex: ONE tab stop into the map, arrows to move within it.
 *
 * A tab stop per seat means a 2,000-seat house is 2,000 stops between the map and the checkout
 * button, which is worse for a keyboard user than the map not being focusable at all.
 */
function onSeatKey(evt, seat) {
    keyboardActive.value = true;
    if (evt.key === 'Enter' || evt.key === ' ') {
        evt.preventDefault();
        toggle(seat);
        return;
    }

    const list = focusableSeats.value;
    const i = list.findIndex((x) => x.id === seat.id);
    let next = null;

    // Up and Down used to be the same +-1 step as Left and Right, so walking from Row A Seat 1 to
    // Row B Seat 1 meant arrowing through every remaining seat in Row A. They now move by ROW.
    if (evt.key === 'ArrowUp' || evt.key === 'ArrowDown') {
        next = seatInAdjacentRow(seat, evt.key === 'ArrowDown' ? 1 : -1);
    } else if (evt.key === 'ArrowLeft' || evt.key === 'ArrowRight') {
        next = nextTakeable(list, i, evt.key === 'ArrowRight' ? 1 : -1);
    } else if (evt.key === 'Home' || evt.key === 'End') {
        const ordered = evt.key === 'Home' ? list : [...list].reverse();
        next = ordered.find((x) => ! isBlocked(x)) || null;
    }

    if (! next) return;

    evt.preventDefault();

    focusedSeatId.value = next.id;
    const owner = mySections.value.find((s) => s.seats.includes(next));
    if (owner) revealPoint(...toCanvasFrame(owner, seatX(owner, next), seatY(owner, next)));
    nextTick(() => document.getElementById(`seat-${uid}-${next.id}`)?.focus());
}

function isSelected(seat) { return selected.value.includes(seat.id); }
/**
 * An available seat wears its section's colour, so a buyer can see the bands on the map.
 *
 * The designer sets a colour per section and the box office honours it; this map used to paint
 * every available seat the same grey, so the one person choosing between two price bands was the
 * only one who could not see them. Taken seats keep the flat grey AND the hatch over the top -
 * status is never colour alone.
 */
/**
 * Anything the buyer cannot take: sold to somebody else, or refused outright by the accessibility
 * rules however free it looks. Grouped so a seat can never be drawn as ordinary and then rejected
 * on click, which is what a misplaced wheelchair space used to do from the centre of a row.
 */
function isBlocked(seat) {
    return seat.state === 'taken' || seat.state === 'unavailable';
}

function fillFor(s, seat) {
    if (isSelected(seat)) return 'var(--brand-blue)';
    if (isBlocked(seat)) return '#9ca3af';
    if (seat.kind === 'wheelchair') return '#bfdbfe';
    return tint(s.color) || '#e5e7eb';
}
function strokeFor(s, seat) {
    if (isSelected(seat)) return 'var(--brand-blue)';
    if (isBlocked(seat)) return '#6b7280';
    return s.color || '#9ca3af';
}

/** A pale wash of the section colour, light enough to read a seat number on. */
function tint(hex) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
    if (!m) return null;
    const n = parseInt(m[1], 16);
    const mix = (c) => Math.round(c + (255 - c) * 0.72);
    return `rgb(${mix((n >> 16) & 255)} ${mix((n >> 8) & 255)} ${mix(n & 255)})`;
}

/** The first seat of a row, which is what the row label is pinned to. */
function rowLabelX(s, row) { return row.seats.length ? seatX(s, row.seats[0]) : 0; }
function rowLabelY(s, row) { return row.seats.length ? seatY(s, row.seats[0]) : 0; }

/** Dark enough to read on the tint above; the label is drawn inside the seat. */
function labelInkFor(seat) {
    return isSelected(seat) ? '#ffffff' : '#374151';
}

/**
 * The seat's label with its price.
 *
 * The hover line is aria-hidden, so price used to reach sighted users only - a screen-reader user
 * could hear the seat and its state but never what it costs.
 */
function priced(s, seat) {
    const price = ticketById.value[s.ticket_id]?.priceLabel || '';
    const label = labelFor(s, seat);

    return price ? `${label}, ${price}` : label;
}

function labelFor(s, seat) {
    const bits = [s.name];
    // "Table 4" where the seat belongs to one, matching SeatingSeat::label() - which is what the
    // ticket and the confirmation email print. Without it the buyer chose "Seat 3" and was later
    // told they had "Table 4, Seat 3", with nothing on the map connecting the two.
    const table = tableOf(s, seat);
    if (table) bits.push(table.label);
    if (seat.row) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row));
    if (seat.seat) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat));
    // Every kind the designer can draw. Companion had only a dashed outline and restricted view had
    // nothing at all, so a buyer could pay full price for a restricted seat with no disclosure
    // anywhere on the page, and a screen reader user was never told either.
    if (seat.kind === 'wheelchair') bits.push(t.wheelchair || '');
    if (seat.kind === 'companion') bits.push(t.companion || '');
    if (seat.kind === 'restricted_view') bits.push(t.restrictedView || '');
    // Said before the click, not only as a refusal afterwards.
    if (table && table.booking_mode === 'whole') bits.push(t.wholeTable || '');
    if (seat.state === 'unavailable') bits.push(t.legendUnavailable || '');
    else bits.push(seat.state === 'taken' ? (t.legendTaken || '') : (isSelected(seat) ? (t.legendSelected || '') : (t.legendAvailable || '')));
    return bits.filter(Boolean).join(', ');
}

const selectedSeats = computed(() => {
    // The level only earns a place in the label when there is more than one: "Row C, Seat 14" is
    // otherwise the same string in the stalls and in the balcony.
    const multiLevel = myLevels.value.length > 1;
    const out = [];

    myLevels.value.forEach((lvl) => lvl.sections.forEach((sec) => sec.seats.forEach((seat) => {
        if (! isSelected(seat)) return;

        const bits = [];
        if (seat.row) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row));
        if (seat.seat) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat));

        // A level is commonly named after the section it holds - the demo house has a Stalls level
        // whose only seated section is Stalls - and naming both gave "Stalls, Stalls, Row A, Seat 4"
        // wherever the full label is shown.
        const levelName = multiLevel && lvl.name !== sec.name ? lvl.name : null;

        out.push({
            id: seat.id,
            ticketId: sec.ticket_id,
            // Commas, matching the aria-label and the list view. The old label ran the section and
            // the row together - "Stalls Row C Seat 14".
            label: [levelName, sec.name, ...bits].filter(Boolean).join(', '),
            // Inside a band's row the band name is already the heading, so only the seat is needed.
            short: bits.join(', ') || String(seat.id),
        });
    })));

    return out;
});

/**
 * The selection as a basket: one row per band, with its price, its seats and its subtotal.
 *
 * A flat strip of pills repeated the band and row on every chip and wrapped into a wall by the
 * fourth seat, and the money sat in an unrelated line underneath.
 */
const basket = computed(() => {
    const groups = new Map();

    selectedSeats.value.forEach((seat) => {
        const ticket = ticketById.value[seat.ticketId];

        if (! groups.has(seat.ticketId)) {
            const key = priceKey.value.find((r) => r.id === seat.ticketId);
            groups.set(seat.ticketId, {
                id: seat.ticketId,
                label: ticket?.type || '',
                price: ticket?.priceLabel || '',
                color: key?.color || '#e5e7eb',
                stroke: key?.stroke || '#9ca3af',
                seats: [],
                subtotal: 0,
            });
        }

        const group = groups.get(seat.ticketId);
        group.seats.push(seat);
        group.subtotal += Number(ticket?.price) || 0;
    });

    return [...groups.values()];
});

function rowsOf(s) {
    const groups = new Map();
    const labels = new Map();

    s.seats.forEach((seat) => {
        // A table seat carries no row_label, so keying on the row alone collapsed EVERY table in a
        // section into one unlabelled group - a wall of buttons with the numbers 1..8 repeating
        // once per table and nothing to say which table any of them belonged to.
        const table = tableOf(s, seat);
        const key = table ? `t${table.id}` : (seat.row || '');

        if (! groups.has(key)) {
            groups.set(key, []);
            labels.set(key, table
                ? table.label
                : (seat.row ? (t.rowPattern || 'Row :row').replace(':row', seat.row) : ''));
        }

        groups.get(key).push(seat);
    });

    return [...groups.entries()].map(([key, seats]) => ({
        key: `${s.id}-${key}`,
        label: labels.get(key) || '',
        // The bare row letter for the map's gutter. Empty for a table, whose own shape already
        // carries its name - and deriving this from the key printed the internal "t7" once table
        // seats started grouping by table rather than by row.
        gutter: key.startsWith('t') && seats.some((x) => x.table_id) ? '' : key,
        seats,
    }));
}

async function post(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    return { ok: res.ok, status: res.status, data: await res.json().catch(() => ({})) };
}

/**
 * Every refusal goes through here.
 *
 * The bare `error.value = ...` it replaces wrote into a <p> that lives in the map branch, so on the
 * quantity path - the one most buyers take - the message was assigned and never rendered.
 */
function showError(message) {
    error.value = message || '';
}

function base() {
    return { event_id: props.eventId, date: props.date };
}

/**
 * Copy a fresh payload INTO the existing map object rather than replacing the ref.
 *
 * startPolling() captures the object it is handed and mutates that one (seat-map-store.js:57-67),
 * and its poller is keyed by (event, date) so it can never be re-registered. Assigning
 * `map.value = data.state` therefore pointed the component at a new object while the poller kept
 * updating the old one: from that moment the component's seat states were frozen, every newly
 * picked seat read back as `available`, and the next poll silently dropped it. That is "the seat
 * does not stick", permanently, after the first refusal.
 */
function mergeState(next) {
    const current = map.value;
    if (! current) { map.value = next; return; }

    const states = new Map();
    (next.levels || []).forEach((lvl) => (lvl.sections || []).forEach((sec) => {
        (sec.seats || []).forEach((seat) => states.set(seat.id, seat.state));
    }));

    current.version = next.version;
    (current.levels || []).forEach((lvl) => (lvl.sections || []).forEach((sec) => {
        (sec.seats || []).forEach((seat) => {
            if (states.has(seat.id)) seat.state = states.get(seat.id);
        });
    }));
}

/** Mark what we now hold, so the local map tells the truth between polls. */
function markMine(ids) {
    const held = new Set(ids);
    (map.value?.levels || []).forEach((lvl) => (lvl.sections || []).forEach((sec) => {
        (sec.seats || []).forEach((seat) => {
            if (held.has(seat.id)) seat.state = 'mine';
            else if (seat.state === 'mine') seat.state = 'available';
        });
    }));
}

/** What the map says this session actually holds - the only trustworthy answer after a refusal. */
function heldByServer() {
    const out = [];
    (map.value?.levels || []).forEach((lvl) => (lvl.sections || []).forEach((sec) => {
        (sec.seats || []).forEach((seat) => { if (seat.state === 'mine') out.push(seat.id); });
    }));

    return out;
}

/**
 * The poller was started only from load(), which only ran from openMap() - so a hold taken on the
 * quantity path lapsed in complete silence and the stale ids sat in seat_ids[] until checkout
 * rejected them. Any hold at all is now watched.
 */
let polling = false;
function ensurePolling() {
    if (polling || !map.value) return;
    polling = true;
    startPolling(props.stateUrl, props.eventId, props.date, map.value, onPolled);
}

/**
 * One request shape for both paths: an explicit seat list, or a party size the server picks for.
 *
 * Best available has been implemented server-side since the picker shipped - hold() takes
 * ticket_id + quantity and runs BestAvailableService - and nothing ever called it. The docs and the
 * feature page both promised the buyer a "how many seats" step, so the product was advertising a
 * control that did not exist, and every buyer had to pick seat by seat.
 */
async function sendHold(ids, attempt = 0) {
    return requestHold({ seat_ids: ids }, attempt);
}

async function requestHold(payload, attempt = 0) {
    const { ok, status, data } = await post(props.holdUrl, { ...base(), ...payload });

    if (!ok) {
        showError(data.error || t.holdFailed);

        // 409 carries the refreshed map, so a seat somebody else took greys out immediately and only
        // THAT seat drops out - the rest of the choice survives.
        //
        // Retry only when the refresh actually removed something. The old bound was `attempt < 3`
        // on the assumption that every pass drops a taken seat, which is true for
        // seating_seat_taken and false for every RULE refusal (orphan, accessible, whole-table):
        // those drop nothing, so the identical body went out four times before giving up.
        if (status === 409 && data.state) {
            mergeState(data.state);
            const before = selected.value.length;
            selected.value = selected.value.filter((id) => seatState(id) !== 'taken');

            if (selected.value.length < before && attempt < 3) {
                // Always by seat id, even when this began as a best-available request: repeating
                // that would quietly hand the buyer a different block from the one just shown.
                return await requestHold({ seat_ids: selected.value }, attempt + 1);
            }
        }

        // Whatever the reason, the server is not holding what this screen is showing. Fall back to
        // what it IS holding, or the seat stays blue, stays in seat_ids[] and stays in the form's
        // quantity while nothing is reserved - and checkout fails at the very last click.
        selected.value = heldByServer();
        // The warning described a selection that was never accepted, so it is stale now.
        warning.value = null;
        emitChange();

        // False, so a caller can tell a refusal from a short answer. findBestSeats() used to read
        // an empty selection as "no block big enough" and overwrite the real reason - sold out, a
        // rule refusal, a 500 - with its own generic one.
        return false;
    }

    // The server clamps per band and simply DROPS the surplus ids, so a selection could come back
    // quietly smaller than the one that was sent - seats vanishing off the map with no word.
    const asked = Array.isArray(payload.seat_ids) ? payload.seat_ids.length : null;

    selected.value = data.held || [];
    warning.value = data.warning || null;
    followSelection();

    // Cleared BEFORE the branch below, not after it. Setting the message and then blanking it two
    // statements later meant the seats vanished off the map with no word at all - which is the
    // exact failure this branch exists to prevent.
    error.value = '';

    if (asked !== null && selected.value.length < asked) {
        const dropped = String(asked - selected.value.length);

        // Two different things can shorten a selection, and blaming the wrong one is worse than
        // saying nothing: another buyer taking the seat, or OUR OWN per-band limit refusing the
        // surplus. The server says which by echoing the cap it applied.
        showError(data.capped
            ? (t.someSeatsCapped || '').replace(':count', dropped).replace(':max', String(data.capped))
            : (t.someSeatsGone || '').replace(':count', dropped));
    }
    // The local map still calls these free until the next poll, and heldByServer() reads it.
    markMine(selected.value);
    expiresAt.value = data.expires_at ? Date.parse(data.expires_at) : null;
    emitChange();

    return true;
}

/**
 * Let the server choose. BestAvailableService scores contiguous runs by section, then row, then
 * distance from the centre of the row, honours gangways, and never offers a wheelchair space or a
 * lone companion seat - so this is a better choice than most buyers would make by hand.
 *
 * The result lands as an ordinary selection, so it can then be adjusted seat by seat. One selection
 * model, one hold, and the map below stays the authority on what is taken.
 */
async function findBestSeats() {
    const ticket = bestBandId.value
        ? ticketById.value[bestBandId.value]
        : props.tickets.find((x) => (Number(x.quantity) || 0) > 0);

    if (! ticket || finding.value) return;

    const wanted = Math.max(1, Math.min(Number(partySize.value) || 1, maxSelectable.value));

    finding.value = true;
    try {
        const held = await requestHold({ ticket_id: ticket.id, quantity: wanted });

        // pick() returns fewer ids than asked - or none - when it cannot find a block that satisfies
        // the rules. Silence there reads as a broken button, so say what happened and leave the map
        // open for them to choose by hand.
        //
        // Only when the request SUCCEEDED, though: on a refusal requestHold has already put the
        // server's own reason on screen, and this would replace it with a vaguer one.
        if (held && selected.value.length < wanted) {
            showError((t.bestNone || '').replace(':count', String(wanted)));
        }
    } finally {
        finding.value = false;
    }
}

/**
 * Show the level the selection is actually on.
 *
 * Best available can land in a band on another level - ask for four in the Circle while looking at
 * the Stalls and the seats are held, the basket fills, and the map still shows the stalls. The
 * buyer is told they have seats and cannot see one of them.
 */
function followSelection() {
    if (! selected.value.length) return;

    const owner = myLevels.value.find((lvl) =>
        lvl.sections.some((sec) => sec.seats.some((seat) => selected.value.includes(seat.id))));

    if (owner && owner.id !== activeLevel.value?.id) {
        activeLevelId.value = owner.id;
        nextTick(() => fit());
    }
}

function seatState(id) {
    for (const s of allMySections.value) {
        const seat = s.seats.find((x) => x.id === id);
        if (seat) return seat.state;
    }
    return 'taken';
}

/**
 * Every free seat of a whole-table-only table, or just this seat.
 *
 * The server refuses a partial take (WholeTableRule), so clicking one chair at such a table has to
 * mean the table - otherwise the buyer's only feedback is a rejection they cannot act on.
 */
function tableGroup(seat) {
    if (! seat.table_id) return [seat];

    const section = allMySections.value.find((sec) => (sec.tables || []).some((x) => x.id === seat.table_id));
    const table = section && (section.tables || []).find((x) => x.id === seat.table_id);
    if (! table || table.booking_mode !== 'whole') return [seat];

    return section.seats.filter((s) => s.table_id === seat.table_id);
}

function toggle(seat) {
    if (isBlocked(seat)) return;

    const group = tableGroup(seat);
    const already = selected.value.includes(seat.id);

    if (already) {
        // Deselecting any chair of a whole table drops the whole table with it.
        selected.value = selected.value.filter((id) => ! group.some((s) => s.id === id));
    } else {
        // A whole table cannot be taken at all if part of it has gone.
        if (group.some((s) => isBlocked(s))) { showError(t.wholeTableGone); return; }

        const additions = group.filter((s) => ! selected.value.includes(s.id)).map((s) => s.id);
        if (selected.value.length + additions.length > maxSelectable.value) {
            showError(t.maxReached);
            return;
        }
        // A band's own stock is a ceiling separate from the order's. Without this a buyer could take
        // three from a band with two left, and only the checkout balance check would say so.
        const ticket = ticketFor(seat.id);
        if (ticket && (quantities.value[ticket.id] || 0) + additions.length > (Number(ticket.quantity) || 0)) {
            showError((t.bandFull || t.maxReached || '').replace(':band', ticket.type));
            return;
        }
        // The band's own per-order ceiling, which the server applies and the client did not - so the
        // surplus was accepted here, dropped there, and the seats simply left the selection.
        const bandCap = Number(ticket?.maxPerOrder) || 0;
        if (bandCap && (quantities.value[ticket.id] || 0) + additions.length > bandCap) {
            showError((t.bandMax || t.maxReached || '').replace(':band', ticket.type).replace(':max', String(bandCap)));
            return;
        }
        selected.value.push(...additions);
    }

    sendHold(selected.value);
}


async function load() {
    if (map.value) return;

    loading.value = true;
    try {
        // Shared across every band's picker on the page, and fetched at most once.
        const data = await loadMap(props.stateUrl, props.eventId, props.date);
        if (!data) { showError(t.loadFailed); return; }
        map.value = data;

        // Seats already held by this session read back as "mine" and stay selected across a reload.
        const mine = [];
        allMySections.value.forEach((s) => s.seats.forEach((seat) => { if (seat.state === 'mine') mine.push(seat.id); }));
        // Set BEFORE emitChange, so the form's first look at this selection already carries the
        // verdict. Why the state endpoint has it at all: the seats survive a reload, so the reason
        // they cannot be checked out has to survive it too.
        warning.value = data.warning || null;

        if (mine.length) { selected.value = mine; emitChange(); }

        activeLevelId.value = myLevels.value[0]?.id ?? null;
        focusedSeatId.value = focusableSeats.value.find((x) => ! isBlocked(x))?.id
            ?? focusableSeats.value[0]?.id ?? null;

        ensurePolling();
    } finally {
        loading.value = false;
    }

    // AFTER loading clears, not before: the template renders a "loading" line in place of the map
    // while it is true, so measuring inside the try above found no <svg> at all. canvas.w then kept
    // its 900 default and the map was sized for a canvas three times the width of the real one.
    await nextTick();
    observe();
    fit();
}

const lapsed = ref(false);

/**
 * What the picked seats come to, band by band.
 *
 * The form's total is authoritative and lives hundreds of lines further down, below the promo and
 * installment blocks - and its subtotal row only renders when a discount exists, so with no promo
 * there was no per-line money anywhere on the page.
 */
const runningTotal = computed(() => props.tickets.reduce(
    (sum, x) => sum + (quantities.value[x.id] || 0) * (Number(x.price) || 0),
    0,
));

const runningTotalLabel = computed(() => {
    const n = selected.value.length;
    const seats = (n === 1 ? t.seatCountOne : t.seatCountMany) || ':count';

    return `${seats.replace(':count', String(n))} · ${money(runningTotal.value)}`;
});

/**
 * Formatted from the band's own preformatted label so the currency stays the event's, without a
 * second money formatter in the codebase - the repo has rules about that for good reason.
 */
function money(amount) {
    const sample = props.tickets.find((x) => x.priceLabel && Number(x.price) > 0);
    if (! sample) return String(amount);

    const unit = Number(sample.price) || 1;
    const label = sample.priceLabel;
    const digits = label.match(/[\d.,]+/);
    if (! digits) return String(amount);

    const scaled = (amount / unit) * parseFloat(digits[0].replace(/,/g, ''));

    return label.replace(digits[0], scaled.toLocaleString(undefined, {
        minimumFractionDigits: digits[0].includes('.') ? 2 : 0,
        maximumFractionDigits: 2,
    }));
}

/**
 * The shared poller mutated the map in place; drop anything this picker has lost.
 *
 * Losing every seat at once is a lapsed hold, not a race with another buyer, and it used to happen
 * in total silence: the countdown stopped at 0:00 and the seats simply left the selection. Say so,
 * so the buyer knows to pick again rather than wondering what they did wrong.
 */
function onPolled(data) {
    const before = selected.value.length;
    selected.value = selected.value.filter((id) => seatState(id) === 'mine');

    // Losing SOME of them used to be silent: they left the map and the chips with no word, and a
    // quietly smaller quantity went to the form. Only a total loss ever said anything.
    if (before && selected.value.length && selected.value.length < before) {
        showError((t.someSeatsGone || '').replace(':count', String(before - selected.value.length)));
        emitChange();
    }

    // The server recomputes this against the room as it now is - a neighbour's purchase can strand
    // a seat beside a selection that was fine a moment ago, and can equally undo one. The key is
    // absent on a quiet tick, which means "unchanged", not "no warning".
    if (data && Object.prototype.hasOwnProperty.call(data, 'warning')) {
        warning.value = data.warning || null;
    }

    if (before && !selected.value.length) {
        lapsed.value = true;
        expiresAt.value = null;
        emitChange();
    }
}

/** Clear the lapsed notice and go again from the map the poll has already refreshed. */
function pickAgain() {
    lapsed.value = false;
    error.value = '';
    warning.value = null;
}

const countdown = computed(() => {
    if (!expiresAt.value || !selected.value.length) return '';
    const left = Math.max(0, Math.floor((expiresAt.value - now.value) / 1000));
    const m = Math.floor(left / 60), s = left % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
});
const countdownSoon = computed(() => expiresAt.value && (expiresAt.value - now.value) < 120000);

/**
 * Say it when the clock runs out, without waiting for the poll to notice.
 *
 * Hold expiry is evaluated at READ time and sweepExpiredHolds() does not touch state_version, so a
 * hold that simply lapses produces no diff - and the `?since=` poll only returns seats whose
 * version moved. onPolled() therefore fires only when some OTHER buyer, or the box office, happens
 * to move the map. Left to itself the countdown sat at 0:00 with the seats still blue, still in
 * seat_ids[], still counted in the form's quantities, and the buyer found out at checkout.
 *
 * This does not touch the server: the seats are already free the moment they lapse. It only stops
 * the screen from claiming otherwise.
 */
watch(now, () => {
    if (! expiresAt.value || ! selected.value.length) return;
    if (now.value < expiresAt.value) return;

    expiresAt.value = null;
    selected.value = [];
    warning.value = null;
    lapsed.value = true;
    // The SHARED store too, or the seats stay drawn as mine and heldByServer() - documented as the
    // only trustworthy answer after a refusal - hands them straight back on the next failed hold.
    markMine([]);
    emitChange();
});

async function pushHold() { await sendHold(selected.value); }

/** Drop one seat without clearing the lot - what the comma-joined list gave no way to do. */
async function removeSeat(id) {
    const next = selected.value.filter((x) => x !== id);
    selected.value = next;
    await sendHold(next);
}

/**
 * The parent ticket form owns the running total and its own validation, so tell it what changed
 * rather than reaching into it. Mirrors the es-cart-add event the cart already uses.
 */
/**
 * One event carrying EVERY band's count.
 *
 * It used to carry a single ticketId, which was all a per-band instance could know. The form applies
 * the whole map in one pass and recalculates once.
 */
function emitChange() {
    document.dispatchEvent(new CustomEvent('es-seats-changed', {
        detail: {
            quantities: { ...quantities.value },
            seatIds: [...selected.value],
            // The ticket form gates Add to cart and Checkout on this. The seats stay held either
            // way - the buyer is being asked to rearrange, not to start again.
            blocked: !! warning.value,
            reason: warning.value?.message || '',
            // For the cart, which stored quantities alone: a buyer who picked Row C 12-15 and put
            // the event in their basket saw "4 tickets" and nothing else, with no sign that the
            // seats were on a twelve-minute clock.
            seatLabels: selectedSeats.value.map((x) => x.label),
            expiresAt: expiresAt.value,
        },
    }));
}

// A verdict that lands after the click - the hold is a round trip - still has to reach the form.
watch(warning, emitChange);

// Each level has its own extent, so a switch has to reframe or the new level lands off screen.
watch(activeLevelId, () => {
    // The zoom chip belongs to the level that was on screen. Left set, the strip claims you are
    // zoomed into a section of a level you have left.
    zoomedSectionId.value = null;
    nextTick(fit);
});

/**
 * Fetch the map the first time this picker is actually on screen - not on mount.
 *
 * The ticket form is server-rendered into the page and merely hidden with `display: none` until the
 * buyer presses Buy Tickets, so the component mounts on every event page view. Fetching there would
 * pull the whole house - up to 6,000 seats - for every visitor, which is why the map used to load
 * only when the quantity step handed over. With that step gone there is no "open" moment left, so
 * observe visibility instead: `display: none` never intersects, and showing the form does.
 *
 * An IntersectionObserver rather than the page's own `event-form-shown` event (which Turnstile and
 * intl-tel-input in the same form use): the embed has no such event, and this needs no knowledge of
 * how the parent decides to reveal itself.
 */
let visibility = null;

onMounted(() => {
    tick = setInterval(() => { now.value = Date.now(); }, 1000);

    if (typeof IntersectionObserver === 'undefined') { load(); return; }

    visibility = new IntersectionObserver((entries) => {
        if (! entries.some((e) => e.isIntersecting)) return;
        visibility?.disconnect();
        visibility = null;
        load();
    });

    if (rootEl.value) visibility.observe(rootEl.value);
});

onBeforeUnmount(() => {
    clearInterval(tick);
    visibility?.disconnect();
});
</script>
