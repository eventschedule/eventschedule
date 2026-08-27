<template>
    <div class="space-y-4">

        <!-- Toolbar: what this page IS, and what is happening to it. The zoom cluster used to live
             here, two cards away from the thing it zooms; it is on the map card now. -->
        <div class="ap-card rounded-xl p-4 flex flex-wrap items-center gap-3">
            <a :href="backUrl" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:underline">&larr; {{ t.back }}</a>

            <!-- The page named neither the plan nor, on one date, the night - there is no heading
                 in the host view and the admin layout renders no header slot, so this is the only
                 place it can go. The box office reached the same conclusion. -->
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
                    {{ isOccurrence ? planName : t.planLabel }}
                </p>
                <!-- Editable only on the template. saveOccurrenceStructure() reads `levels` alone,
                     so on one date the field looked editable and silently discarded what was typed. -->
                <label v-if="nameEditable" for="seating-plan-name" class="sr-only">{{ t.planName }}</label>
                <input v-if="nameEditable" id="seating-plan-name" ref="nameInput" v-model="planName" type="text"
                    maxlength="255" @input="dirty = true" :placeholder="t.planNamePlaceholder"
                    class="mt-0.5 w-full max-w-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                <h1 v-else class="mt-0.5 text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">{{ subtitle || planName }}</h1>
            </div>

            <div class="ms-auto flex items-center gap-3">
                <div class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button id="seating-undo" type="button" @click="undo" :disabled="!canUndo" :title="t.undo" :aria-label="t.undo"
                        class="px-2 py-1 rounded text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 disabled:opacity-40 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                    </button>
                    <button id="seating-redo" type="button" @click="redo" :disabled="!canRedo" :title="t.redo" :aria-label="t.redo"
                        class="px-2 py-1 rounded text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 disabled:opacity-40 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 15 6-6m0 0-6-6m6 6H9a6 6 0 0 0 0 12h3" />
                        </svg>
                    </button>
                </div>

                <!-- Clickable, unlike the count it replaces: it now takes you to the first problem
                     rather than telling you a number and leaving you to find it. -->
                <button v-if="issues.length" type="button" @click="goToIssue(issues[0])"
                    class="text-sm text-amber-600 dark:text-amber-400 hover:underline">
                    {{ issues.length }} {{ issues.length === 1 ? t.issue : t.issues }}
                </button>
                <span v-if="dirty" id="seating-dirty" class="text-sm text-gray-500 dark:text-gray-400">{{ t.unsaved }}</span>
                <span v-else-if="savedAt" id="seating-saved" class="text-sm text-green-600 dark:text-green-400">{{ t.saved }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ seatCountLabel }}</span>
                <!-- loading/loadFailed as well as saving: `levels` is [] until the fetch lands, and
                     this payload is the whole structure, so a click in that window saved an empty
                     plan over a real one. The preset half of this race was fixed; Save was not. -->
                <button id="seating-save" type="button" @click="save" :disabled="saving || loading || loadFailed"
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

        <!-- role=alert: this used to appear silently at the top of the page while the button that
             raised it sat at the bottom of the rail, so nothing announced it and nothing scrolled. -->
        <div v-if="error" id="seating-error" ref="errorEl" role="alert"
            class="rounded-lg border border-red-200 dark:border-red-700 bg-red-50 dark:bg-red-900/20 p-3 text-sm text-red-700 dark:text-red-300 flex items-start gap-2">
            <span class="flex-1">{{ error }}</span>
            <!-- A stale-revision refusal offered only Dismiss, which leaves the tab holding work it
                 can never save - the whole payload is the structure, so retrying is exactly the
                 overwrite the check exists to prevent. Reloading is the only way forward, and it
                 warns first because the unsaved work is real. -->
            <button v-if="stale" type="button" @click="reloadForTheirs"
                class="shrink-0 font-medium text-red-700 dark:text-red-300 hover:underline text-xs">{{ t.reload }}</button>
            <button type="button" @click="error = ''; stale = false" class="shrink-0 text-red-600 dark:text-red-400 hover:underline text-xs">{{ t.dismiss }}</button>
        </div>

        <!-- Nothing is offered until the plan has actually arrived: the empty state below keys off
             `!levels.length`, which is true from the first paint, so without this a preset clicked
             early was built and then wiped by the fetch landing behind it. -->
        <div v-if="loading" id="seating-loading" class="ap-card rounded-xl p-8 text-sm text-gray-500 dark:text-gray-400">
            {{ t.loading }}
        </div>

        <!-- A failed load must never fall through to the empty state below. `levels` is [] either
             way, so the preset gallery used to render over a plan that is very much still on the
             server - and saving a preset from it deletes the real structure. -->
        <div v-else-if="loadFailed" id="seating-load-failed" class="ap-card rounded-xl p-8 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">{{ t.loadFailed }}</p>
            <button type="button" @click="load"
                class="mt-4 px-4 py-3 text-base rounded-md font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                {{ t.retry }}
            </button>
        </div>

        <!-- Empty state: presets rather than a blank canvas -->
        <div v-else-if="!levels.length" class="ap-card rounded-xl p-8">
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

        <!-- One rail, not two. Two rails needed 1400px to avoid squeezing the canvas to ~290px, and
             below that everything stacked - which put the canvas of a drawing tool BELOW THE FOLD on
             a 1280 laptop. The box office solved this first: a single 22rem rail fits at xl and
             leaves the map ~560px. Level switcher and zoom live on the map card, as they do there. -->
        <div v-else class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-4">

            <!-- The map. self-start so the grid does not stretch it to the rail's height, which is
                 what would otherwise leave `sticky` nothing to move within. -->
            <!-- top-20, not top-4: layouts/app-admin.blade.php pins a 64px opaque header at
                 `sticky top-0 z-40`, so a card stuck at 16px slid its own top 48px - the level
                 switcher and the zoom cluster - underneath it. z-30 keeps the card above the page
                 and below that header. -->
            <!-- max-h + flex column is what makes the rest of this card reachable. A sticky box
                 taller than its slot pins its top and never moves again, so its bottom cannot be
                 scrolled to - and the bottom is where the seat actions and the hint live. The
                 canvas is the flexible child, so it gives up height instead of the controls. -->
            <div class="ap-card rounded-xl p-2 relative flex flex-col xl:sticky xl:top-20 xl:z-30 xl:self-start
                        xl:max-h-[calc(100vh-6rem)]">
                <div class="flex flex-wrap items-center gap-2 px-1 pb-2 shrink-0">
                    <div v-if="levels.length > 1" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                        <button v-for="(lvl, i) in levels" :key="lvl.id" type="button" @click="selectLevel(i)"
                            :aria-pressed="i === activeLevel"
                            class="px-2 py-1 rounded text-xs transition-all duration-200"
                            :class="i === activeLevel
                                ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                                : 'text-gray-500 dark:text-gray-400'">{{ lvl.name || t.level }}
                            <span class="text-gray-400">{{ seatsInLevel(lvl) }}</span>
                        </button>
                    </div>
                    <button type="button" @click="addLevel()"
                        class="px-2 py-1 rounded-lg text-xs font-medium text-[var(--brand-blue)] hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200">
                        + {{ t.level }}
                    </button>

                    <div class="ms-auto flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                        <!-- Multiplicative, like the wheel. A flat +-0.15 was a 50% jump at 0.3x
                             and 5% at 3x, and neither button ever showed it had stopped working. -->
                        <button type="button" @click="stepZoom(-1)" :disabled="zoom <= MIN_ZOOM"
                            class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 disabled:opacity-40 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent transition-all duration-200" :aria-label="t.zoomOut">&minus;</button>
                        <span class="px-1 text-xs tabular-nums text-gray-500 dark:text-gray-400">{{ Math.round(zoom * 100) }}%</span>
                        <button type="button" @click="stepZoom(1)" :disabled="zoom >= MAX_ZOOM"
                            class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 disabled:opacity-40 disabled:hover:bg-transparent dark:disabled:hover:bg-transparent transition-all duration-200" :aria-label="t.zoomIn">+</button>
                        <button type="button" @click="fitToView()" class="px-2 py-1 rounded text-xs text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200">{{ t.fit }}</button>
                    </div>
                </div>

                <!-- Above the map, not below it. Below a canvas up to 704px tall this was
                     unreachable - including the zoomForNumbers warning, which explains a
                     disappearance the user is looking at right now. -->
                <p class="px-2 pb-2 text-xs text-gray-400 dark:text-gray-500 shrink-0">
                    {{ t.canvasHint }}
                    <span v-if="zoom <= 0.7 && totalSeats">{{ t.zoomForNumbers }}</span>
                </p>

                <!-- Over the canvas, not after it: the svg renders on `level`, not on section
                     count, so an empty level drew 544px of blank box with the explanation below
                     the fold. -->
                <p v-if="level && !level.sections.length"
                    class="absolute inset-x-0 top-1/2 -translate-y-1/2 px-6 text-center text-sm text-gray-500 dark:text-gray-400 pointer-events-none">
                    {{ t.emptyLevel }}
                </p>

                <svg v-if="level" ref="svgEl" v-bind="canvasBind" class="w-full select-none seat-canvas"
                    role="group" :aria-label="planName" :viewBox="viewBox"
                    :style="{ height: canvasHeight, touchAction: 'none' }">
                    <defs>
                        <pattern id="restrictedHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                            <line x1="0" y1="0" x2="0" y2="4" stroke="currentColor" stroke-width="1.5" opacity="0.6" />
                        </pattern>
                    </defs>

                    <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                        <!-- Before the sections, so a stage never covers a seat. This is the only
                             surface where a decoration is interactive; everywhere else it is inert. -->
                        <g v-for="d in (level.decorations || [])" :key="`dec-${d.id}`"
                            :transform="`translate(${d.x} ${d.y}) rotate(${d.rotation || 0})`"
                            style="cursor: move" @pointerdown.stop="startDecorationDrag($event, d)">
                            <rect v-if="d.kind === 'stage'" :width="d.width" :height="d.height" rx="4"
                                class="fill-gray-300 dark:fill-gray-600"
                                :stroke="d.id === selectedDecorationId ? 'var(--brand-blue)' : 'none'" stroke-width="2" />
                            <rect v-else :width="d.width" :height="d.height" rx="4"
                                fill="transparent"
                                :stroke="d.id === selectedDecorationId ? 'var(--brand-blue)' : 'none'"
                                stroke-width="2" stroke-dasharray="4 3" />
                            <text :x="d.width / 2" :y="d.height / 2" text-anchor="middle" dy="4"
                                :font-size="d.kind === 'stage' ? 14 : 12"
                                :class="d.kind === 'stage'
                                    ? 'fill-gray-700 dark:fill-gray-200 uppercase tracking-widest'
                                    : 'fill-gray-500 dark:fill-gray-400'">{{ d.label }}</text>
                        </g>

                        <g v-for="s in level.sections" :key="s.id" :transform="`translate(${s.x} ${s.y}) rotate(${s.rotation})`">
                            <rect :x="sectionBox(s).x" :y="sectionBox(s).y" :width="sectionBox(s).w" :height="sectionBox(s).h" rx="8"
                                :fill="s.color" fill-opacity="0.10" :stroke="s.color" stroke-opacity="0.5"
                                style="cursor: move" @pointerdown.stop="startSectionDrag($event, s)" />
                            <text :x="sectionBox(s).x + 2" :y="sectionBox(s).y - 8"
                                class="fill-gray-600 dark:fill-gray-300" font-size="13">{{ s.name }}</text>

                            <template v-if="s.kind === 'standing'">
                                <text :x="16" :y="50" class="fill-gray-500 dark:fill-gray-400" font-size="12">
                                    {{ s.capacity }} {{ t.standingCapacity }}
                                </text>
                            </template>

                            <g v-for="tb in s.tables" :key="tb.id" :transform="`translate(${tb.x} ${tb.y}) rotate(${tb.rotation || 0})`">
                                <circle v-if="tb.shape === 'round'" :r="tb.width / 2" :fill="s.color"
                                    :fill-opacity="tb.id === selectedTableId ? 0.45 : 0.25"
                                    :stroke="s.color" style="cursor: move" @pointerdown.stop="startTableDrag($event, tb, s)" />
                                <rect v-else :x="-tb.width / 2" :y="-tb.height / 2" :width="tb.width" :height="tb.height" rx="4"
                                    :fill="s.color" :fill-opacity="tb.id === selectedTableId ? 0.45 : 0.25" :stroke="s.color" style="cursor: move"
                                    @pointerdown.stop="startTableDrag($event, tb, s)" />
                                <text text-anchor="middle" dy="4" font-size="11" class="fill-gray-700 dark:fill-gray-200">{{ tb.label }}</text>
                            </g>

                            <g v-for="seat in s.seats" :key="seat.id"
                                :transform="`translate(${seatX(s, seat)} ${seatY(s, seat)})`"
                                style="cursor: pointer" @pointerdown.stop="onSeatDown($event, seat, s)"
                                class="seat-node"
                                :data-seat-id="seat.id"
                                :tabindex="seat.id === tabbableSeatId ? 0 : -1"
                                role="button"
                                :aria-label="seatAriaLabel(s, seat)"
                                :aria-pressed="selectedSeats.includes(seat.id)"
                                @focus="focusedSeatId = seat.id"
                                @keydown="onSeatKey($event, seat, s)">
                                <!-- Shape carries the kind, never colour alone. -->
                                <rect v-if="seat.kind === 'wheelchair'" x="-9" y="-9" width="18" height="18" rx="3"
                                    :fill="seatFill(seat)" :stroke="seatStroke(seat)" :stroke-width="seatStrokeWidth(seat)" />
                                <circle v-else r="8" :fill="seatFill(seat)" :stroke="seatStroke(seat)" :stroke-width="seatStrokeWidth(seat)"
                                    :stroke-dasharray="seat.kind === 'companion' ? '3 2' : null" />
                                <!-- No dark: variant, and not by omission: currentColor inside a
                                     <pattern> resolves against the pattern's own inherited colour in
                                     <defs>, not against the shape referencing it, so the class here
                                     never did anything. The disc under it is light in both themes. -->
                                <circle v-if="seat.kind === 'restricted_view'" r="8" fill="url(#restrictedHatch)" />
                                <text v-if="seat.kind === 'wheelchair'" text-anchor="middle" dy="4" font-size="10"
                                    :class="seat.locked ? 'fill-white' : 'fill-gray-900 dark:fill-gray-900'">&#9855;</text>
                                <!-- The same mark the box office puts on a sold seat. -->
                                <text v-else-if="seat.locked" text-anchor="middle" dy="3.5" font-size="9"
                                    fill="#ffffff">&#10005;</text>
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


                <!-- Seat actions belong NEXT TO THE SEATS. In the rail they sat below a section
                     inspector tall enough to push them off the screen you just clicked on. -->
                <!-- shrink-0 so the flex column takes height off the canvas, never off these
                     controls. aria-live because it appears on selection and announced nothing. -->
                <div v-if="selectedSeats.length" id="seating-seat-bar" aria-live="polite"
                    class="mt-2 shrink-0 rounded-xl bg-gray-50 dark:bg-[#252526] border border-gray-200 dark:border-gray-700 p-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                        {{ selectedSeats.length }} {{ selectedSeats.length === 1 ? t.seatSelected : t.seatsSelected }}
                    </span>
                    <div class="flex flex-wrap gap-1">
                        <button v-for="k in seatKinds" :key="k" type="button" @click="applyKind(k)"
                            :aria-pressed="k === selectedKind"
                            class="px-2 py-1 rounded-md text-xs border transition-all duration-200"
                            :class="k === selectedKind
                                ? 'border-[var(--brand-blue)] bg-[var(--brand-blue)]/10 text-[var(--brand-blue)] font-medium'
                                : 'border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)]'">
                            {{ t['kind_' + k] }}
                        </button>
                    </div>
                    <button type="button" @click="toggleAisle"
                        class="px-2 py-1 rounded-md text-xs border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">{{ t.toggleAisle }}</button>

                    <!-- Labels were generator-only: once a block of rows existed, neither the seat
                         number nor the row letter could be changed. A house that renumbers one row,
                         or adds a "BOX" beside the stalls, had to delete the section and start over
                         - and the box office lookup resolves against exactly these strings. -->
                    <label class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ t.seatLabel }}
                        <input id="seating-seat-label" :value="oneSeat ? (oneSeat.seat_label || '') : ''"
                            :disabled="!oneSeat" type="text" maxlength="10" size="4"
                            @input="renameSeat($event.target.value)"
                            @focus="beginFieldEdit" @blur="endFieldEdit"
                            class="w-16 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-xs disabled:opacity-50" />
                    </label>
                    <label class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ t.rowLabel }}
                        <input id="seating-row-label" :value="selectedRowLabel"
                            :disabled="selectedRowLabel === null" type="text" maxlength="10" size="4"
                            @input="renameRow($event.target.value)"
                            @focus="beginFieldEdit" @blur="endFieldEdit"
                            class="w-16 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-xs disabled:opacity-50" />
                    </label>
                    <button type="button" @click="removeSelectedSeats"
                        class="ms-auto px-2 py-1 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">{{ t.removeSeats }}</button>
                </div>

            </div>

            <!-- The rail: one column, everything that is not the map. -->
            <div class="space-y-4">

                <!-- Level, then its sections. -->
            <div class="ap-card rounded-xl p-4 space-y-4">
                <div v-if="level">
                    <label for="seating-level-name" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.levelName }}</label>
                    <input id="seating-level-name" v-model="level.name" type="text" maxlength="100"
                        @input="dirty = true" @focus="beginFieldEdit" @blur="endFieldEdit"
                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                </div>

                <div v-if="level">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.sections }}</h3>
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
                    <!-- Real buttons. These are the primary creative actions in the tool and were
                         the quietest controls on the screen, while "Remove this level" was a
                         bordered danger button. -->
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button v-for="a in addActions" :key="a.kind" type="button" @click="addSection(a.kind)"
                            class="px-3 py-2 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                            + {{ a.label }}
                        </button>
                        <!-- Not sellable inventory, so kept visually apart from the three that are. -->
                        <button v-for="a in decorationActions" :key="a.kind" type="button" @click="addDecoration(a.kind)"
                            class="px-3 py-2 rounded-md text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                            + {{ a.label }}
                        </button>
                    </div>

                <!-- In the rail, where the box office keeps its own. Under the map it trailed a 704px
                         canvas and was never on screen. Only shown when the level has seats to key. -->
                    <div v-if="levelHasSeats"
                        class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="background:#e5e7eb;border-color:#9ca3af"></span>{{ t.kind_standard }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm border" style="background:#bfdbfe;border-color:#9ca3af"></span>{{ t.kind_wheelchair }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border border-dashed" style="background:#e5e7eb;border-color:#6b7280"></span>{{ t.kind_companion }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="border-color:#9ca3af;background:repeating-linear-gradient(45deg,#9ca3af,#9ca3af 1.5px,transparent 1.5px,transparent 3px)"></span>{{ t.kind_restricted_view }}</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:#dc2626"></span>{{ t.soldSeat }}</span>
                    </div>

                    <!-- Destructive, so it goes last and stays quiet. -->
                    <button v-if="levels.length > 1" type="button" @click="removeLevel(activeLevel)"
                        class="mt-4 text-xs font-medium text-red-600 dark:text-red-400 hover:underline">{{ t.removeLevel }}</button>
                </div>
            </div>

            <div class="ap-card rounded-xl p-4 space-y-4">
                <template v-if="section">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.section }}</h3>

                    <div>
                        <label for="seating-section-name" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.name }}</label>
                        <input id="seating-section-name" v-model="section.name" type="text" maxlength="100"
                            @input="dirty = true" @focus="beginFieldEdit" @blur="endFieldEdit"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    </div>

                    <div>
                        <label for="seating-section-band" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.band }}</label>
                        <!-- A band must match tickets.seating_band EXACTLY or the section is never
                             priced, and this was an unguarded text box. Free entry still works; the
                             list just stops a typo being the usual way to create a new band. -->
                        <input id="seating-section-band" v-model="section.band" type="text" maxlength="100"
                            @input="dirty = true" @focus="beginFieldEdit" @blur="endFieldEdit"
                            list="seating-bands"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                        <datalist id="seating-bands">
                            <option v-for="b in knownBands" :key="b" :value="b"></option>
                        </datalist>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t.bandHelp }}</p>
                    </div>

                    <div>
                        <label for="seating-section-rotation" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.rotation }}</label>
                        <div class="mt-1 flex items-center gap-2">
                            <button type="button" @click="rotateSection(-15)" :aria-label="t.rotateLeft"
                                class="px-2 py-1 rounded-md text-xs border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">&#8630;</button>
                            <input id="seating-section-rotation" :value="section.rotation || 0"
                                @change="setRotation($event.target.value)" type="number" min="-360" max="360" step="5"
                                class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            <span class="text-xs text-gray-400 dark:text-gray-500">&deg;</span>
                            <button type="button" @click="rotateSection(15)" :aria-label="t.rotateRight"
                                class="px-2 py-1 rounded-md text-xs border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-[var(--brand-blue)] transition-all duration-200">&#8631;</button>
                        </div>
                    </div>

                    <!-- Stacked, not side by side: at 22rem the toggle's label wrapped into the
                         swatches and the row read as one broken control. -->
                    <div class="space-y-3">
                        <div>
                            <label for="seating-section-colour" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.colour }}</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input :value="section.color" @change="setSectionColor($event.target.value)" type="color"
                                    id="seating-section-colour"
                                    class="h-9 w-12 rounded border border-gray-300 dark:border-gray-700 bg-transparent" />
                                <div class="flex gap-1">
                                    <button v-for="(c, i) in SECTION_COLORS" :key="c" type="button"
                                        @click="setSectionColor(c)" :aria-label="`${t.colour} ${i + 1}`"
                                        class="w-5 h-5 rounded-sm border border-black/10 dark:border-white/20"
                                        :class="section.color === c ? 'ring-2 ring-offset-1 ring-[var(--brand-blue)] dark:ring-offset-gray-800' : ''"
                                        :style="{ backgroundColor: c }"></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" role="switch" :aria-checked="!!section.accessibility_only"
                            @click="toggleAccessibilityOnly"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                            <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-200"
                                :class="section.accessibility_only ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700'">
                                <span class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transition-transform duration-200"
                                    :class="section.accessibility_only ? 'translate-x-[1.375rem]' : 'translate-x-0.5'"></span>
                            </span>
                            {{ t.accessibilityOnly }}
                        </button>
                    </div>

                    <div v-if="section.kind === 'standing'">
                        <label for="seating-section-capacity" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.capacity }}</label>
                        <input id="seating-section-capacity" :value="section.capacity"
                            @change="setCapacity($event.target.value)" type="number" min="0" max="65535"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    </div>

                    <!-- Row builder. Eight fields used once per section, so it is behind a
                         disclosure - open by default only while the section has nothing in it. -->
                    <div v-if="section.kind === 'seated'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <button type="button" @click="toggleBuilder" :aria-expanded="showBuilder"
                            class="w-full flex items-center justify-between text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ t.addRows }}
                            <svg class="w-4 h-4 transition-transform duration-200" :class="showBuilder ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <template v-if="showBuilder">
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
                            <label class="text-xs text-gray-500 dark:text-gray-400" :title="t.curveHelp">{{ t.curve }}
                                <input v-model.number="rowForm.curve" type="number" min="0" max="120" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                                <span class="mt-1 block font-normal text-gray-400 dark:text-gray-500">{{ t.curveHelp }}</span>
                            </label>
                            <label class="text-xs text-gray-500 dark:text-gray-400 col-span-2">{{ t.aisleAfterSeats }}
                                <input v-model="rowForm.aisles" type="text" placeholder="6, 14"
                                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            </label>

                            <!-- Everything a house needs to match the numbers screwed to its own
                                 seats. Collapsed, because the defaults reproduce exactly what this
                                 generated before and most rooms never open it. -->
                            <details class="col-span-2 mt-1">
                                <summary class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-400">{{ t.numbering }}</summary>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.seatNumbering }}
                                        <select v-model="rowForm.seatStyle" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                            <option value="seq">1, 2, 3</option>
                                            <option value="oddEven">5, 3, 1 &middot; 2, 4, 6</option>
                                            <option value="rtl">3, 2, 1</option>
                                        </select>
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.firstSeat }}
                                        <input v-model.number="rowForm.seatStart" type="number" min="0" max="9999"
                                            :disabled="rowForm.seatStyle === 'oddEven'"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm disabled:opacity-50" />
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.firstRow }}
                                        <input v-model.number="rowForm.rowStart" type="number" min="1" max="9999"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.rowPrefix }}
                                        <input v-model="rowForm.rowPrefix" type="text" maxlength="4" placeholder="AA"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 col-span-2">{{ t.skipLetters }}
                                        <input v-model="rowForm.skipLetters" type="text" maxlength="26" placeholder="I, O"
                                            :disabled="rowForm.rowStyle === 'numeric'"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm disabled:opacity-50" />
                                        <span class="mt-1 block font-normal text-gray-400 dark:text-gray-500">{{ t.skipLettersHelp }}</span>
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.seatPitch }}
                                        <input v-model.number="rowForm.seatPitch" type="number" min="12" max="200"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                                    </label>
                                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.rowPitch }}
                                        <input v-model.number="rowForm.rowPitch" type="number" min="12" max="200"
                                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                                    </label>
                                    <!-- What Generate will actually produce, before pressing it. -->
                                    <p class="col-span-2 text-xs text-gray-400 dark:text-gray-500">
                                        {{ t.numberingPreview }} <span class="font-mono">{{ numberingPreview }}</span>
                                    </p>
                                </div>
                            </details>
                        </div>
                        <button type="button" @click="generateRows"
                            class="w-full px-3 py-2 rounded-md text-sm font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            {{ t.generateRows }}
                        </button>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ t.generateRowsHelp }}</p>
                        </template>
                    </div>

                    <!-- Table builder -->
                    <div v-if="section.kind === 'table'" class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <button type="button" @click="toggleBuilder" :aria-expanded="showBuilder"
                            class="w-full flex items-center justify-between text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ t.addTablesTitle }}
                            <svg class="w-4 h-4 transition-transform duration-200" :class="showBuilder ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <template v-if="showBuilder">
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
                        <button type="button" role="switch" :aria-checked="tableForm.numbered"
                            @click="tableForm.numbered = !tableForm.numbered"
                            class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                            <span class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-200"
                                :class="tableForm.numbered ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700'">
                                <span class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transition-transform duration-200"
                                    :class="tableForm.numbered ? 'translate-x-[1.375rem]' : 'translate-x-0.5'"></span>
                            </span>
                            {{ t.numberSeats }}
                        </button>
                        <button type="button" @click="generateTables"
                            class="w-full px-3 py-2 rounded-md text-sm font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            {{ t.generateTables }}
                        </button>
                        </template>
                    </div>

                    <button id="seating-remove-section" type="button" @click="removeSection(section)" class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">{{ t.removeSection }}</button>
                </template>

                <!-- One table at a time. Everything here was fixed at generation before: getting a
                     booking mode wrong meant regenerating the section and losing every other
                     table's position. -->
                <template v-if="selectedTable">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 space-y-2">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ t.tableSelected }}</h4>
                        <label for="seating-table-label" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.name }}</label>
                        <input id="seating-table-label" :value="selectedTable.label" maxlength="100" type="text"
                            @change="updateTable({ label: $event.target.value })"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />

                        <label for="seating-table-shape" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.shape }}</label>
                        <select id="seating-table-shape" :value="selectedTable.shape"
                            @change="updateTable({ shape: $event.target.value, height: $event.target.value === 'round' ? selectedTable.width : 80 })"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                            <option value="round">{{ t.round }}</option>
                            <option value="rect">{{ t.rectangular }}</option>
                        </select>

                        <label for="seating-table-rotation" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.rotation }}</label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="updateTable({ rotation: (Number(selectedTable.rotation) || 0) - 15 })"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">&#8634;</button>
                            <input id="seating-table-rotation" :value="selectedTable.rotation || 0" type="number" min="-360" max="360" step="5"
                                @input="updateTable({ rotation: clampInt($event.target.value, -360, 360) ?? 0 })"
                                class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            <span class="text-xs text-gray-500 dark:text-gray-400">&deg;</span>
                            <button type="button" @click="updateTable({ rotation: (Number(selectedTable.rotation) || 0) + 15 })"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">&#8635;</button>
                        </div>

                        <label for="seating-table-mode" class="block text-xs text-gray-500 dark:text-gray-400">{{ t.booking }}</label>
                        <select id="seating-table-mode" :value="selectedTable.booking_mode"
                            @change="updateTable({ booking_mode: $event.target.value })"
                            class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                            <option value="seat">{{ t.bookSeat }}</option>
                            <option value="whole">{{ t.bookWhole }}</option>
                            <option value="either">{{ t.bookEither }}</option>
                        </select>

                        <button type="button" @click="removeTable"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">{{ t.removeTable }}</button>
                    </div>
                </template>

                <!-- The decoration inspector. Mutually exclusive with the section one above: a
                     decoration belongs to the level, so nothing here is about a section. -->
                <template v-if="decoration">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ decoration.kind === 'stage' ? t.stage : t.textLabel }}
                    </h3>

                    <div>
                        <label for="seating-decoration-label" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.label }}</label>
                        <input id="seating-decoration-label" :value="decoration.label" type="text" maxlength="100"
                            @input="updateDecoration({ label: $event.target.value })"
                            @focus="beginFieldEdit" @blur="endFieldEdit"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="seating-decoration-width" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.width }}</label>
                            <input id="seating-decoration-width" :value="decoration.width" type="number" min="10" max="20000"
                                @input="updateDecoration({ width: clampInt($event.target.value, 10, 20000) ?? 10 })"
                                @focus="beginFieldEdit" @blur="endFieldEdit"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </div>
                        <div>
                            <label for="seating-decoration-height" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.height }}</label>
                            <input id="seating-decoration-height" :value="decoration.height" type="number" min="10" max="20000"
                                @input="updateDecoration({ height: clampInt($event.target.value, 10, 20000) ?? 10 })"
                                @focus="beginFieldEdit" @blur="endFieldEdit"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        </div>
                    </div>

                    <div>
                        <label for="seating-decoration-rotation" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ t.rotation }}</label>
                        <div class="mt-1 flex items-center gap-2">
                            <button type="button" @click="updateDecoration({ rotation: ((decoration.rotation || 0) - 15) })"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">&#8634;</button>
                            <input id="seating-decoration-rotation" :value="decoration.rotation || 0" type="number" min="-360" max="360" step="5"
                                @input="updateDecoration({ rotation: clampInt($event.target.value, -360, 360) ?? 0 })"
                                @focus="beginFieldEdit" @blur="endFieldEdit"
                                class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                            <span class="text-xs text-gray-500 dark:text-gray-400">&deg;</span>
                            <button type="button" @click="updateDecoration({ rotation: ((decoration.rotation || 0) + 15) })"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-300">&#8635;</button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button id="seating-remove-decoration" type="button" @click="removeDecoration"
                            class="px-3 py-2 rounded-md text-xs font-medium border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200">{{ t.removeDecoration }}</button>
                    </div>
                </template>

                <!-- Nothing selected: an empty 22rem card said nothing at all. -->
                <p v-if="!section && !decoration" class="text-sm text-gray-500 dark:text-gray-400">{{ t.nothingSelected }}</p>

            </div>

            <!-- How this room sells, as opposed to how it is laid out. On the template these are
                 the defaults every new date inherits; on one date they are that date's own. -->
            <div class="ap-card rounded-xl p-4 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.rules }}</h3>

                <label class="flex items-start gap-2">
                    <input id="seating-orphan-enabled" type="checkbox" v-model="rules.orphan_rule_enabled"
                        @change="dirty = true"
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                    <span class="text-xs">
                        <span class="block font-medium text-gray-700 dark:text-gray-300">{{ t.orphanRule }}</span>
                        <span class="block text-gray-500 dark:text-gray-400">{{ t.orphanRuleHelp }}</span>
                    </span>
                </label>

                <div v-if="rules.orphan_rule_enabled" class="grid grid-cols-2 gap-3">
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.orphanGap }}
                        <input id="seating-orphan-gap" :value="rules.orphan_rule_min_gap" type="number" min="1" max="4"
                            @input="rules.orphan_rule_min_gap = clampInt($event.target.value, 1, 4) ?? 1; dirty = true"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        <span class="mt-1 block font-normal text-gray-400 dark:text-gray-500">{{ t.orphanGapHelp }}</span>
                    </label>
                    <label class="text-xs text-gray-500 dark:text-gray-400">{{ t.orphanLift }}
                        <input id="seating-orphan-lift" :value="rules.orphan_rule_lift_pct" type="number" min="0" max="100"
                            @input="rules.orphan_rule_lift_pct = clampInt($event.target.value, 0, 100) ?? 90; dirty = true"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" />
                        <span class="mt-1 block font-normal text-gray-400 dark:text-gray-500">{{ t.orphanLiftHelp }}</span>
                    </label>
                </div>

                <p v-if="!isOccurrence" class="text-xs text-gray-400 dark:text-gray-500">{{ t.rulesTemplateNote }}</p>
            </div>

            <!-- Validation. Advisory - you can save with these outstanding - but each row now
                 takes you to the section it is about instead of naming one and leaving you to
                 hunt for it across levels. -->
            <div v-if="issues.length" class="ap-card rounded-xl p-4">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <ul class="text-xs text-amber-800 dark:text-amber-200 space-y-1">
                            <li v-for="issue in issues" :key="issue.key">
                                <button type="button" @click="goToIssue(issue)" class="text-start hover:underline">{{ issue.text }}</button>
                            </li>
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
import { toCanvasFrame, toSectionFrame } from '../seat-map-geometry';

const props = defineProps({
    planName: { type: String, default: '' },
    nameEditable: { type: Boolean, default: true },
    // Flashed by seating.store on a brand new plan. The seating tab has no name field any more,
    // so this box is where a plan gets named.
    focusName: { type: Boolean, default: false },
    usage: { type: Object, default: () => ({ events: 0, sold: 0 }) },
    isOccurrence: { type: Boolean, default: false },
    // On one date, the night being edited. The page had no heading of any kind, so on a thirty
    // date run nothing but the URL said which one you were about to restructure.
    subtitle: { type: String, default: '' },
    structureUrl: { type: String, required: true },
    saveUrl: { type: String, required: true },
    backUrl: { type: String, default: '' },
    csrfToken: { type: String, required: true },
    strings: { type: Object, default: () => ({}) },
});

const t = props.strings;
const seatKinds = ['standard', 'wheelchair', 'companion', 'restricted_view'];
// The WP brand ramp. Cycled so two sections are never the same colour by default, which made them
// indistinguishable on the canvas until somebody opened the colour picker.
const SECTION_COLORS = ['#4E81FA', '#0EA5E9', '#22D3EE', '#F59E0B', '#10B981', '#8B5CF6'];
// Short labels: the buttons carry a "+" so "+ Add seating" would stutter.
const decorationActions = [
    { kind: 'stage', label: t.stage },
    { kind: 'text', label: t.textLabel },
];

const addActions = [
    { kind: 'seated', label: t.seating },
    { kind: 'table', label: t.tablesLabel },
    { kind: 'standing', label: t.standing },
];

const planName = ref(props.planName);
const nameInput = ref(null);
const levels = ref([]);
const activeLevel = ref(0);
const selectedSectionId = ref(null);
const selectedDecorationId = ref(null);
const selectedSeats = ref([]);
const dirty = ref(false);
const loading = ref(true);
const saving = ref(false);
// The only sign a save had worked was "Unsaved changes" disappearing, which is a thing NOT
// happening. Brief and self-clearing; the dirty marker still carries the ongoing state.
const savedAt = ref(0);
const error = ref('');
// Distinct from `error`: a plan that FAILED to load is not the same as a plan that is empty, and
// the template below has to be able to tell them apart. See the retry card.
const loadFailed = ref(false);
const errorEl = ref(null);
const viewportH = ref(typeof window === 'undefined' ? 900 : window.innerHeight);
// The owner's updated_at when this structure was read. Sent back on save so a second editor
// cannot silently overwrite the first - the payload is the WHOLE structure, and anything the
// server does not recognise is removed.
const revision = ref(null);
const svgEl = ref(null);
// Roving tabindex: ONE tab stop into the map, arrows move within it. Every seat used to be
// tabindex="0", so a 1,200-seat house was 1,200 presses of Tab to get past the canvas. The guest
// picker and the box office both learned this; the designer was the last one still doing it.
const focusedSeatId = ref(null);
// A table was created in bulk and then immutable: no rename, no delete, no shape or booking-mode
// change. Selecting one is what makes any of that reachable.
const selectedTableId = ref(null);
const selectedTable = computed(() => {
    const s = section.value;
    if (!s || selectedTableId.value === null) return null;

    return s.tables.find((x) => x.id === selectedTableId.value) ?? null;
});

// Client-side ids are negative so the server, which treats any id it does not already own as new,
// can never mistake one for a real row - and a hand-edited payload cannot adopt somebody else's.
let tempId = -1;
const nextId = () => tempId--;

/**
 * Undo.
 *
 * Every edit here is permanent for the session: a mis-clicked "Generate rows" adds thousands of
 * seats, a stray drag moves somebody's row, and the only recovery was a reload that threw away
 * everything else too. The docs had to carry a warning callout telling organizers to delete the
 * extras by hand rather than press the button twice - which is this gap written out in prose.
 *
 * Coarse and cheap: `levels` is a plain serialisable tree, so one structural clone per OPERATION
 * (drags coalesced on endDrag, not per frame) is enough, bounded so a long session cannot grow
 * without limit. Selection is deliberately not restored - only the document is.
 */
const UNDO_LIMIT = 25;
/** A save was refused because somebody else got there first; only a reload moves this on. */
/**
 * The single-seat rule's settings for whatever this designer is editing.
 *
 * Enforced on every guest selection since the feature shipped, with no writer and no screen - the
 * user guide documents the absence as a limitation. On the template these are the defaults every
 * new date inherits; on one date they are that date's own.
 */
const rules = reactive({ orphan_rule_enabled: true, orphan_rule_min_gap: 1, orphan_rule_lift_pct: 90 });

const stale = ref(false);
const undoStack = ref([]);
const redoStack = ref([]);
const canUndo = computed(() => undoStack.value.length > 0);
const canRedo = computed(() => redoStack.value.length > 0);

// Mirrors SeatingStructureService::MAX_SEATS. Duplicated deliberately: the server is still the
// authority and still refuses, this only stops the organizer finding out the expensive way.
const MAX_SEATS = 6000;
const MAX_LEVELS = 12;
const MAX_SECTIONS = 200;
const MAX_TABLES = 500;
const MAX_DECORATIONS = 200;

/**
 * The banner sits at the top of the page while the buttons that raise it are at the bottom of the
 * rail. role="alert" covered screen readers; a sighted user pressed Generate and saw nothing happen.
 */
function showError(message) {
    error.value = message;
    nextTick(() => errorEl.value?.scrollIntoView({ block: 'nearest', behavior: 'smooth' }));
}

/**
 * NaN and out-of-range in one place - the min/max attributes on the inputs enforce nothing.
 *
 * An EMPTY field is rejected rather than clamped: Number('') is 0, so clamping would quietly turn
 * "I have not filled this in yet" into the minimum and generate a row the organizer never asked
 * for. A typed 0 is out of range and clamps normally.
 */
function clampInt(value, min, max) {
    if (value === '' || value === null || value === undefined) return null;

    const n = Math.round(Number(value));
    if (! Number.isFinite(n)) return null;

    return Math.min(max, Math.max(min, n));
}

const snapshot = () => JSON.parse(JSON.stringify(levels.value));
// The structure as last read or written, so "is this dirty?" can be answered by comparison rather
// than by a flag that only ever gets set.
let savedSnapshot = '[]';

// A drag is one operation, not one per mousemove frame: hold the before-state at the press and
// keep it only if the pointer actually moved something.
let dragSnapshot = null;

/**
 * Call BEFORE mutating. A redo branch is discarded the moment a new edit is made, as everywhere.
 *
 * Re-entrant: applyPreset calls addLevel, addSection and generateRows, each of which checkpoints,
 * so one preset click pushed SEVEN snapshots - seven Ctrl+Z presses to reverse, replaying the
 * construction backwards, and seven of the 25 slots gone. Nested calls now join the outer entry.
 */
let checkpointDepth = 0;

/** The one place history is written. endDrag and the field editors below share it. */
function pushHistory(snap) {
    undoStack.value.push(snap);
    if (undoStack.value.length > UNDO_LIMIT) undoStack.value.shift();
    redoStack.value = [];
}

function checkpoint() {
    if (checkpointDepth > 0) return;
    pushHistory(snapshot());
}

/**
 * Text fields, which mutate on every keystroke.
 *
 * Snapshot on focus, commit on blur only if something changed - one undo entry per edit rather than
 * one per character. Without this, Ctrl+Z covered every structural change but silently skipped
 * every rename, which is not a distinction a user has any reason to expect.
 */
let fieldEdit = null;
function beginFieldEdit() {
    fieldEdit = { snap: snapshot(), before: JSON.stringify(levels.value) };
}
function endFieldEdit() {
    if (! fieldEdit) return;
    if (JSON.stringify(levels.value) !== fieldEdit.before) pushHistory(fieldEdit.snap);
    fieldEdit = null;
}

/** Everything inside is one undoable operation, however many checkpointing helpers it calls. */
function asOneOperation(fn) {
    checkpoint();
    checkpointDepth++;
    try {
        return fn();
    } finally {
        checkpointDepth--;
    }
}

function applyHistory(from, to) {
    if (!from.value.length) return;
    to.value.push(snapshot());
    levels.value = from.value.pop();
    // The ids in the restored tree are real, but whatever was selected may not be in it.
    activeLevel.value = Math.min(activeLevel.value, Math.max(0, levels.value.length - 1));
    const sections = levels.value[activeLevel.value]?.sections ?? [];
    if (! sections.some((x) => x.id === selectedSectionId.value)) {
        selectedSectionId.value = sections[0]?.id ?? null;
    }
    selectedSeats.value = [];
    // Not unconditionally true: undoing all the way back to what was saved leaves the plan clean,
    // and saying otherwise arms the beforeunload guard over nothing.
    dirty.value = JSON.stringify(levels.value) !== savedSnapshot;
}

const undo = () => applyHistory(undoStack, redoStack);
const redo = () => applyHistory(redoStack, undoStack);

function onKeydown(evt) {
    if (! (evt.metaKey || evt.ctrlKey)) return;

    // Cmd+Z inside a text field means "undo my typing" everywhere else in the OS. Hijacking it
    // rolled back the last STRUCTURAL edit instead - and since text edits were not checkpointed,
    // the typed characters stayed too, so the user lost a generate they never touched.
    const el = evt.target;
    if (el && (el.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName))) {
        // Save is still ours: no field implements Cmd+S.
        if ((evt.key || '').toLowerCase() !== 's') return;
    }

    const key = (evt.key || '').toLowerCase();

    if (key === 's') { evt.preventDefault(); if (!saving.value && !loading.value && !loadFailed.value) save(); return; }
    if (key === 'z') { evt.preventDefault(); evt.shiftKey ? redo() : undo(); return; }
    if (key === 'y') { evt.preventDefault(); redo(); }
}

const rowForm = reactive({
    rows: 10, perRow: 12, rowStyle: 'alpha', curve: 0, aisles: '',
    // Defaults chosen to reproduce exactly what this generated before, so an existing plan drawn
    // with the old builder comes out identical.
    rowStart: 1, rowPrefix: '', skipLetters: '', seatStyle: 'seq', seatStart: 1,
    seatPitch: 26, rowPitch: 30,
});
/** "A1 A2 A3 ... / B1 ..." for the settings as they stand, so Generate is not a guess. */
const numberingPreview = computed(() => {
    const perRow = Math.min(6, Math.max(1, Number(rowForm.perRow) || 1));
    const seats = [];

    for (let c = 0; c < perRow; c++) {
        seats.push(seatLabelFor(c, Number(rowForm.perRow) || perRow));
    }

    const more = (Number(rowForm.perRow) || 0) > perRow ? '...' : '';

    return `${rowLabel(0)}: ${seats.join(' ')}${more}   ${rowLabel(1)}: ...`;
});

const tableForm = reactive({ count: 8, seats: 8, shape: 'round', mode: 'either', numbered: true });

const level = computed(() => levels.value[activeLevel.value] || null);
const section = computed(() => {
    if (!level.value) return null;
    return level.value.sections.find((s) => s.id === selectedSectionId.value) || null;
});
const decoration = computed(() => {
    if (! level.value) return null;
    return (level.value.decorations || []).find((d) => d.id === selectedDecorationId.value) || null;
});
// The viewBox tracks the rendered ELEMENT rather than the level, so one design unit is one CSS
// pixel. Tying it to level.width/height meant the browser scaled the whole map down a second time
// to fit the centre column - a 1200-unit level in a ~525px box rendered everything at 44%, and the
// toolbar's zoom percentage was then a lie.
// Panning must never fight an element drag: a mousedown on a section, table or seat starts that
// drag instead, and the viewport stays out of the way until it ends.
const { zoom, pan, canvas, bind: viewportBind, fit: fitToView, zoomBy, observe: observeCanvas, revealPoint } =
    useMapViewport({ svgEl, contentBounds, canPan: () => !drag.mode, panFromChildren: false });

const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);

// Mirrors seat-map-viewport's own clamps, so the buttons can show when they have hit them.
const MIN_ZOOM = 0.2;
const MAX_ZOOM = 3;
const stepZoom = (dir) => zoomBy(zoom.value * (dir > 0 ? 0.12 : -0.107));

/**
 * The single tab stop. Falls back to a selected seat, then to the first one - without a fallback a
 * roving tabindex with nothing yet focused makes the whole map unreachable from the keyboard.
 */
/**
 * Open while the section is empty, closed once it has content, and pinned per SECTION once clicked.
 *
 * It was one shared ref holding null/true/false, which broke twice: `!null` is `true`, so the first
 * click on an auto-open builder set it to the value it already had and nothing moved; and a pin on
 * one section followed you to every other section for the rest of the session.
 */
const builderPinned = ref({});
const autoOpen = (s) => !s || (s.kind === 'table' ? !s.tables.length : !s.seats.length);
const showBuilder = computed(() => {
    const s = section.value;
    const pinned = s ? builderPinned.value[s.id] : undefined;

    return pinned === undefined ? autoOpen(s) : pinned;
});

function toggleBuilder() {
    const s = section.value;
    if (!s) return;
    builderPinned.value = { ...builderPinned.value, [s.id]: !showBuilder.value };
}

/** Every band already used anywhere in this plan, so the field can offer them back. */
const knownBands = computed(() => [...new Set(
    levels.value.flatMap((l) => l.sections.map((x) => (x.band || '').trim())).filter(Boolean),
)].sort());

const tabbableSeatId = computed(() => {
    const all = (level.value?.sections ?? []).flatMap((x) => x.seats);
    if (! all.length) return null;
    if (focusedSeatId.value && all.some((x) => x.id === focusedSeatId.value)) return focusedSeatId.value;

    return selectedSeats.value.find((id) => all.some((x) => x.id === id)) ?? all[0].id;
});

const usageNotice = computed(() => {
    const events = Number(props.usage?.events || 0);
    const sold = Number(props.usage?.sold || 0);

    // Only :sold is interpolated: the events count is deliberately not, because a number sitting
    // next to a count-noun needs singular/plural agreement in every one of the twelve languages,
    // and "in use by 1 events" is what that costs when it is skipped.
    //
    // On ONE DATE the plan-scoped wording is simply false - the Blade banner above already says you
    // are editing a single date, and this used to sit under it claiming the plan was "in use by
    // other events". Two amber panels, one of them wrong.
    if (sold > 0) {
        return ((props.isOccurrence ? t.dateHasSold : t.inUseSold) || '').replace(':sold', sold);
    }

    if (events > 0) return t.inUse || '';
    return '';
});

/** Tall enough to work in, proportioned to the room rather than fixed at 34rem. */
/**
 * Bounded by the WINDOW as well as by the content.
 *
 * It used to be `min(704, max(384, canvas.w * ratio))` - no viewport term at all, so a wider window
 * meant a taller canvas, and everything below the map went further off screen the bigger the screen
 * got. The card's own budget now wins whenever it is the smaller of the two.
 */
const canvasHeight = computed(() => {
    // Header, toolbar, the card's chrome and a margin - the same 6rem the card's max-height reserves.
    const budget = Math.max(260, viewportH.value - 320);
    const b = contentBounds();

    // Below xl the two columns collapse and the whole rail - section inspector, row builder,
    // selling rules, validation - stacks UNDERNEATH the canvas. At the full 704px that puts every
    // control a tablet has off screen, so the map gives up height rather than the tools.
    const ceiling = stacked.value ? 380 : 704;

    if (!b) return `${Math.min(Math.min(544, ceiling), budget)}px`;

    const ratio = Math.min(1.0, Math.max(0.45, b.h / b.w));

    return `${Math.round(Math.min(ceiling, budget, Math.max(320, canvas.w * ratio)))}px`;
});

/** True while the rail is below the map rather than beside it (Tailwind's xl breakpoint). */
const stacked = ref(typeof window !== 'undefined' && window.innerWidth < 1280);
const totalSeats = computed(() => levels.value.reduce((n, l) => n + seatsInLevel(l), 0));
/**
 * Interpolated, not concatenated. This file already documents why - the events count deliberately
 * avoids sitting next to a count-noun - and then rendered "1 Seats" three lines from that comment.
 */
const seatCountLabel = computed(() => (t.seatCount || ':count').replace(':count', String(totalSeats.value)));

/** The legend keys seat states, so a standing-only level has nothing for it to explain. */
const levelHasSeats = computed(() => (level.value?.sections ?? []).some((s) => s.seats.length));

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
    // Scaled, not fixed: a 20-person and a 2,000-person standing area drew identically, which
    // made the map lie about the shape of the room. Bounded so it stays drawable either way.
    if (s.kind === 'standing') {
        const cap = Math.max(0, Number(s.capacity) || 0);
        const w = Math.round(Math.min(560, Math.max(160, 140 + Math.sqrt(cap) * 11)));

        return { x: 0, y: 0, w, h: Math.round(w * 0.4) };
    }

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

/**
 * A sold seat looks sold.
 *
 * The structure payload has carried `locked` all along, and the only thing reading it was the guard
 * in removeSelectedSeats - so the one screen where sold seats exist, "Modify this date only", drew
 * them identically to empty ones. An organizer restructuring a room that is already selling could
 * not see the seats that were about to make Save fail.
 *
 * Red matches the box office, so the same seat reads the same on both screens. Template seats are
 * never locked: the status lives on the per-date snapshots.
 */
function seatFill(seat) {
    if (selectedSeats.value.includes(seat.id)) return 'var(--brand-blue)';
    if (seat.locked) return '#dc2626';
    if (seat.kind === 'wheelchair') return '#bfdbfe';
    // NOT transparent. The seat number is drawn in #4b5563 on the assumption - true of every other
    // kind - that the disc under it is light in both themes. A transparent companion disc put that
    // grey on the dark page background at 2.3:1, so the numbers were invisible in dark mode. The
    // dashed stroke is what marks a companion seat; the missing fill never was.
    if (seat.kind === 'companion') return '#e5e7eb';
    return '#e5e7eb';
}
function seatStroke(seat) {
    if (selectedSeats.value.includes(seat.id)) return 'var(--brand-blue)';
    return seat.locked ? '#7f1d1d' : '#9ca3af';
}

/** Thicker for a sold seat: status is never carried by colour alone. */
function seatStrokeWidth(seat) {
    return seat.locked ? 2.5 : 1.5;
}

// ---- selection
function selectLevel(i) {
    activeLevel.value = i;
    selectedSeats.value = [];
    focusedSeatId.value = null;
    selectedSectionId.value = levels.value[i]?.sections[0]?.id ?? null;
    fitToView({ auto: true });
}
function selectSection(s) {
    selectedSectionId.value = s.id;
    selectedSeats.value = [];
    // The two inspectors are mutually exclusive, and a section is selected on load - so without
    // clearing it here the decoration inspector could never appear at all.
    selectedDecorationId.value = null;
}

/** The mirror of selectSection. Picking a stage has to release the section, or the rail keeps
 *  showing the section's row builder while the thing you just clicked is a stage. */
function selectDecoration(d) {
    selectedDecorationId.value = d.id;
    selectedSectionId.value = null;
    selectedSeats.value = [];
}
function isSelectedSection(s) {
    return s.id === selectedSectionId.value;
}

/**
 * The canvas listens for pointer events TWICE - once for element dragging, once for pan and pinch -
 * so the two have to be composed rather than both declared.
 *
 * These were mouse events until touch support: `@mousedown` beside the viewport's `onPointerdown`
 * were different events and coexisted happily. Once the drag moved to pointer events they collided
 * on the same prop, one silently replaced the other, and dragging stopped working with a mouse as
 * well as a finger - while the seat still SELECTED, so the screen looked alive.
 *
 * Element drag runs first: on bare canvas it clears the selection and the viewport then arms a pan,
 * and on a child the child's own `.stop` means neither of these ever runs.
 */
const canvasBind = computed(() => ({
    ...viewportBind,
    onPointerdown: (evt) => { onCanvasDown(evt); viewportBind.onPointerdown?.(evt); },
    onPointermove: (evt) => { onMove(evt); viewportBind.onPointermove?.(evt); },
    onPointerup: (evt) => { endDrag(evt); viewportBind.onPointerup?.(evt); },
    onPointercancel: (evt) => { endDrag(evt); viewportBind.onPointercancel?.(evt); },
    onPointerleave: (evt) => { endDrag(evt); viewportBind.onPointerleave?.(evt); },
}));

// ---- drag
// `seats` holds the origin of EVERY seat being moved, so a multi-seat selection travels together.
// `moved` distinguishes a drag from a plain click, which is what lets a click on an already
// selected seat collapse the selection on release rather than destroying it on press.
const drag = reactive({ mode: null, id: null, startX: 0, startY: 0, originX: 0, originY: 0, seats: [], moved: false });

/**
 * Pointer position in CANVAS units.
 *
 * Deleted by the same over-greedy cut that took startSectionDrag and startTableDrag, and unlike
 * those it is called only from SCRIPT - so tools/check-vue-bindings.mjs, which inspects template
 * identifiers, could not see the hole. Every drag threw "svgPoint is not defined" on the first
 * pointerdown, which is silent: the seat still selected, so the screen looked alive.
 */
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
    dragSnapshot = snapshot();
    selectSection(s);
    const p = svgPoint(evt);
    // moved:false matters as much as the rest: endDrag pushes an undo entry on it, so leaving it
    // set from a PREVIOUS drag makes every later click on a section forge a no-op snapshot.
    Object.assign(drag, { mode: 'section', id: s.id, startX: p.x, startY: p.y, originX: s.x, originY: s.y, moved: false });
}
function startTableDrag(evt, tb, owner) {
    dragSnapshot = snapshot();
    selectedTableId.value = tb.id;
    selectedSeats.value = [];
    // onMove resolves the table through the SELECTED section, so without this a table in any other
    // section silently refuses to move while still marking the plan dirty and pushing an undo entry.
    if (owner && owner.id !== selectedSectionId.value) selectedSectionId.value = owner.id;
    const p = svgPoint(evt);
    Object.assign(drag, { mode: 'table', id: tb.id, startX: p.x, startY: p.y, originX: tb.x, originY: tb.y, moved: false });
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

    // Pressing a seat that is already part of the selection must KEEP that selection, or dragging
    // a block of twenty seats throws nineteen of them away before the drag has even started.
    // A press that turns out to be a click (no movement) collapses to this one seat in endDrag.
    selectedTableId.value = null;
    const inSelection = !crossSection && selectedSeats.value.includes(seat.id);

    if (evt.shiftKey && !crossSection) {
        const i = selectedSeats.value.indexOf(seat.id);
        if (i >= 0) selectedSeats.value.splice(i, 1);
        else selectedSeats.value.push(seat.id);
    } else if (!inSelection) {
        selectedSeats.value = [seat.id];
    }

    dragSnapshot = snapshot();
    const p = svgPoint(evt);
    const moving = (owner.seats || []).filter((x) => selectedSeats.value.includes(x.id));
    Object.assign(drag, {
        mode: 'seat',
        id: seat.id,
        startX: p.x,
        startY: p.y,
        originX: seat.x,
        originY: seat.y,
        moved: false,
        collapseTo: inSelection && !evt.shiftKey && selectedSeats.value.length > 1 ? seat.id : null,
        seats: moving.map((x) => ({ id: x.id, x: x.x, y: x.y })),
    });
}

/**
 * The canvas was mouse-only, which is a poor look on the feature that sells wheelchair spaces.
 *
 * Arrows MOVE FOCUS, as they do in the guest picker and the box office - with a roving tabindex,
 * arrows that nudged instead left a keyboard user able to reach exactly one seat in the house.
 * Nudging is still the designer's own job, on Shift (1 unit) and Shift+Alt (10), and it moves the
 * whole selection rather than only the seat under focus.
 */
function seatAriaLabel(s, seat) {
    const bits = [s.name];
    if (seat.row_label) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row_label));
    if (seat.seat_label) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat_label));
    bits.push(t['kind_' + seat.kind] || seat.kind);
    if (seat.locked) bits.push(t.soldSeat || '');

    return bits.filter(Boolean).join(', ');
}

function onSeatKey(evt, seat, owner) {
    const step = { ArrowLeft: [-1, 0], ArrowRight: [1, 0], ArrowUp: [0, -1], ArrowDown: [0, 1] }[evt.key];

    if (evt.key === 'Enter' || evt.key === ' ') {
        evt.preventDefault();
        onSeatDown({ shiftKey: evt.shiftKey, clientX: 0, clientY: 0 }, seat, owner);
        drag.mode = null;
        return;
    }

    if (! step) return;
    evt.preventDefault();

    if (evt.shiftKey) {
        if (owner && owner.id !== selectedSectionId.value) selectedSectionId.value = owner.id;
        if (!selectedSeats.value.includes(seat.id)) selectedSeats.value = [seat.id];
        // One entry per press. A nudge is a discrete, deliberate act, unlike a drag's frames.
        checkpoint();
        const size = evt.altKey ? 10 : 1;
        const sec = owner || section.value;
        (sec?.seats ?? []).forEach((x) => {
            if (! selectedSeats.value.includes(x.id)) return;
            x.x += step[0] * size;
            x.y += step[1] * size;
        });
        dirty.value = true;
        return;
    }

    moveFocus(owner, seat, step);
}

/**
 * Nearest seat in the pressed direction, across the whole level rather than one section - a row
 * that ends at a gangway should hand off to the next section, not dead-end.
 */
function moveFocus(owner, seat, [sx, sy]) {
    const here = { x: seatX(owner, seat), y: seatY(owner, seat) };
    let best = null;
    let bestScore = Infinity;

    (level.value?.sections ?? []).forEach((sec) => {
        sec.seats.forEach((other) => {
            if (other.id === seat.id) return;
            const dx = seatX(sec, other) - here.x;
            const dy = seatY(sec, other) - here.y;
            const along = dx * sx + dy * sy;
            if (along <= 0) return;
            // Distance along the press, plus a heavy penalty for drifting off that line.
            const across = Math.abs(dx * sy - dy * sx);
            const score = along + across * 3;
            if (score < bestScore) { bestScore = score; best = { sec, other }; }
        });
    });

    if (! best) return;
    focusedSeatId.value = best.other.id;
    // Otherwise arrowing off the visible area gives a focus ring nobody can see. The viewport
    // exports this for exactly that; the designer was the only consumer not using it.
    const [rx, ry] = toCanvasFrame(best.sec, seatX(best.sec, best.other), seatY(best.sec, best.other));
    revealPoint(rx, ry);
    nextTick(() => {
        svgEl.value?.querySelector(`[data-seat-id="${best.other.id}"]`)?.focus();
    });
}
// Panning is the viewport's job; a press on bare canvas only drops the seat selection.
function onCanvasDown() {
    selectedSeats.value = [];
    selectedTableId.value = null;
}
function onMove(evt) {
    if (!drag.mode) return;
    const p = svgPoint(evt);
    const dx = Math.round(p.x - drag.startX);
    const dy = Math.round(p.y - drag.startY);

    if (drag.mode === 'section' && section.value) {
        // The section's own translate is applied BEFORE its rotate, so this one is already in the
        // right frame.
        section.value.x = drag.originX + dx;
        section.value.y = drag.originY + dy;
    } else if (drag.mode === 'decoration') {
        // A decoration hangs off the LEVEL, not a section, so its translate is already in canvas
        // space and needs no counter-rotation.
        const d = (level.value?.decorations || []).find((x) => x.id === drag.id);
        if (d) { d.x = drag.originX + dx; d.y = drag.originY + dy; }
    } else if (drag.mode === 'table' && section.value) {
        // Everything inside a section lives in the section's ROTATED frame, so a canvas-space
        // delta has to be counter-rotated or the thing slides off at an angle to the cursor.
        const [ldx, ldy] = toSectionFrame(section.value, dx, dy);
        const tb = section.value.tables.find((x) => x.id === drag.id);
        if (tb) { tb.x = drag.originX + ldx; tb.y = drag.originY + ldy; }
    } else if (drag.mode === 'seat' && section.value) {
        const [ldx, ldy] = toSectionFrame(section.value, dx, dy);
        // Every seat in the selection, not just the one under the cursor.
        drag.seats.forEach((origin) => {
            const seat = section.value.seats.find((x) => x.id === origin.id);
            if (seat) { seat.x = origin.x + ldx; seat.y = origin.y + ldy; }
        });
    }
    if (dx || dy) drag.moved = true;
    dirty.value = true;
}
function endDrag() {
    if (drag.moved && dragSnapshot) pushHistory(dragSnapshot);
    dragSnapshot = null;

    // A press on an already-selected seat kept the whole selection so it could be dragged. If it
    // turned out to be a plain click, honour what a click means everywhere else and collapse.
    if (drag.mode === 'seat' && !drag.moved && drag.collapseTo) {
        selectedSeats.value = [drag.collapseTo];
    }
    drag.mode = null;
    drag.seats = [];
    drag.collapseTo = null;
    drag.moved = false;
}

/**
 * The bounding box of everything drawn on the active level, in section coordinates.
 * Used by fitToView so the map fills the canvas rather than sitting tiny in a corner - a
 * 16-seat row occupies about a third of a 1200-unit level, and at a fixed zoom of 1 that
 * renders unreadably small once the viewBox is scaled down into the centre column.
 */
/**
 * The four corners of a section's box in CANVAS space, rotation included.
 *
 * -20 on the top edge leaves room for the section label, which sits above the box.
 */
function sectionFootprint(s) {
    const b = sectionBox(s);
    const corners = [
        [b.x, b.y - 20], [b.x + b.w, b.y - 20],
        [b.x + b.w, b.y + b.h], [b.x, b.y + b.h],
    ];
    const deg = Number(s.rotation) || 0;
    if (! deg) return corners.map(([x, y]) => [s.x + x, s.y + y]);

    const r = (deg * Math.PI) / 180;
    const cos = Math.cos(r);
    const sin = Math.sin(r);

    return corners.map(([x, y]) => [s.x + x * cos - y * sin, s.y + x * sin + y * cos]);
}

/** The four corners of a decoration in canvas space, rotation included. */
function decorationFootprint(d) {
    const w = Number(d.width) || 0;
    const h = Number(d.height) || 0;
    const corners = [[0, 0], [w, 0], [w, h], [0, h]];
    const deg = Number(d.rotation) || 0;
    if (! deg) return corners.map(([x, y]) => [d.x + x, d.y + y]);

    const r = (deg * Math.PI) / 180;
    const cos = Math.cos(r);
    const sin = Math.sin(r);

    return corners.map(([x, y]) => [d.x + x * cos - y * sin, d.y + x * sin + y * cos]);
}

function contentBounds() {
    const lvl = level.value;
    if (!lvl || (!lvl.sections.length && !(lvl.decorations || []).length)) return null;

    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    // A stage sits ABOVE row A, so leaving decorations out of the framing puts it off screen the
    // moment Fit is pressed - which reads as "my stage disappeared".
    (lvl.decorations || []).forEach((d) => {
        decorationFootprint(d).forEach(([x, y]) => {
            minX = Math.min(minX, x); minY = Math.min(minY, y);
            maxX = Math.max(maxX, x); maxY = Math.max(maxY, y);
        });
    });
    lvl.sections.forEach((s) => {
        // The ROTATED footprint. Taking the raw box would let Fit clip a turned section, since a
        // rotated rectangle needs a bigger axis-aligned box than the one it started as.
        sectionFootprint(s).forEach(([x, y]) => {
            minX = Math.min(minX, x);
            minY = Math.min(minY, y);
            maxX = Math.max(maxX, x);
            maxY = Math.max(maxY, y);
        });
    });

    if (!isFinite(minX)) return null;
    return { minX, minY, w: Math.max(1, maxX - minX), h: Math.max(1, maxY - minY) };
}



// ---- structure editing
function addLevel(name) {
    if (wouldExceedCap(levels.value.length, 1, MAX_LEVELS, t.tooManyLevels)) return null;
    checkpoint();
    levels.value.push({
        id: nextId(),
        name: name || `${t.level} ${levels.value.length + 1}`,
        position: levels.value.length,
        // Persisted for the API's benefit; the client has not read them since the viewBox started
        // tracking the rendered element rather than the level.
        width: 1200,
        height: 800,
        decorations: [],
        sections: [],
    });
    activeLevel.value = levels.value.length - 1;
    dirty.value = true;
}
/**
 * The sold-seat rule, applied before the work rather than after it.
 *
 * removeSelectedSeats has always refused outright, and the server refuses too - but removing the
 * SECTION or the LEVEL that holds those same seats did not, so an organizer could restructure a
 * live room and only meet the rejection at Save. Deleting three seats one at a time being stricter
 * than deleting the section around them is the wrong way round.
 */
function hasSoldSeats(sections) {
    return sections.some((s) => (s.seats || []).some((seat) => seat.locked));
}

function removeLevel(i) {
    const lvl = levels.value[i];
    if (! lvl) return;

    if (hasSoldSeats(lvl.sections || [])) { showError(t.cannotRemoveSoldHere); return; }
    if (! confirmRemoval(t.confirmRemoveLevel, { ':level': lvl.name || '', ':count': seatsInLevel(lvl) })) return;
    checkpoint();

    levels.value.splice(i, 1);
    activeLevel.value = Math.max(0, i - 1);
    selectedSectionId.value = levels.value[activeLevel.value]?.sections[0]?.id ?? null;
    selectedSeats.value = [];
    dirty.value = true;
}
/**
 * A stage or a text label. Seeded ABOVE the seats (a negative y) because that is where a stage is
 * in every room that has one, and because it makes the orientation read immediately.
 */
function addDecoration(kind) {
    // OUTSIDE asOneOperation, which opens by checkpointing. Refusing the add from inside it still
    // leaves a history entry identical to the state already on screen, so the first Undo afterwards
    // appears to do nothing. addLevel() is the shape to copy: cap first, checkpoint second.
    // (countDecorations only reads levels.value, and adding a level below adds no decorations, so
    // the count is the same either side of it.)
    if (wouldExceedCap(countDecorations(), 1, MAX_DECORATIONS, t.tooManyDecorations)) return;

    const lvl = level.value;
    if (! lvl) { addLevel(); }

    asOneOperation(() => {
        const target = level.value;
        target.decorations = target.decorations || [];

        const n = target.decorations.length;
        target.decorations.push({
            id: nextId(),
            kind,
            label: kind === 'stage' ? t.stage : t.textLabel,
            x: kind === 'stage' ? 60 : 60 + n * 30,
            y: kind === 'stage' ? -80 : 20 + n * 30,
            width: kind === 'stage' ? 320 : 120,
            height: kind === 'stage' ? 40 : 24,
            rotation: 0,
            position: n,
        });
        selectDecoration(target.decorations[target.decorations.length - 1]);
        dirty.value = true;
    });

    nextTick(() => fitToView());
}

function updateDecoration(patch) {
    const d = decoration.value;
    if (! d) return;
    Object.assign(d, patch);
    dirty.value = true;
}

function removeDecoration() {
    const lvl = level.value;
    const d = decoration.value;
    if (! lvl || ! d) return;

    checkpoint();
    lvl.decorations = (lvl.decorations || []).filter((x) => x.id !== d.id);
    selectedDecorationId.value = null;
    dirty.value = true;
}

function startDecorationDrag(evt, d) {
    selectDecoration(d);
    dragSnapshot = snapshot();
    const p = svgPoint(evt);
    Object.assign(drag, {
        mode: 'decoration', id: d.id,
        startX: p.x, startY: p.y, originX: d.x, originY: d.y,
        seats: [], moved: false, collapseTo: null,
    });
}

function addSection(kind, attrs = {}) {
    // Checked before asOneOperation for the same reason as addDecoration: that wrapper checkpoints
    // on entry, so a refusal from inside it leaves a no-op entry on the undo stack.
    if (wouldExceedCap(countSections(), 1, MAX_SECTIONS, t.tooManySections)) return null;

    return asOneOperation(() => addSectionInner(kind, attrs));
}

function addSectionInner(kind, attrs = {}) {
    if (wouldExceedCap(countSections(), 1, MAX_SECTIONS, t.tooManySections)) return null;
    checkpoint();
    if (!level.value) addLevel();
    // Every manually added section used to be born the same blue, overlapping the last one, with
    // an empty band - which raised two validation issues the instant it appeared. Name it, band it
    // and colour it from the ramp, exactly as the presets do.
    const name = kind === 'standing' ? t.standing : kind === 'table' ? t.tablesLabel : t.seating;
    const used = level.value.sections.length;
    const s = Object.assign({
        id: nextId(),
        name,
        color: SECTION_COLORS[used % SECTION_COLORS.length],
        kind,
        capacity: kind === 'standing' ? 100 : null,
        band: name,
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
    if (hasSoldSeats([s])) { showError(t.cannotRemoveSoldHere); return; }
    if (! confirmRemoval(t.confirmRemoveSection, { ':section': s.name || '', ':count': s.seats.length })) return;
    checkpoint();

    const i = level.value.sections.indexOf(s);
    if (i >= 0) level.value.sections.splice(i, 1);
    selectedSectionId.value = null;
    // Or the seat panel stays on screen listing seats that no longer exist, with every control in
    // it reading a null section and silently doing nothing - the exact failure the comment on
    // cross-section selection above calls worse than either supporting or refusing it.
    selectedSeats.value = [];
    dirty.value = true;
}

/**
 * The alphabet used for row letters.
 *
 * Most houses skip I (reads as 1) and O (reads as 0) on the physical signage, and a plan that does
 * not skip them has labels that no longer match the letters screwed to the seats - which is exactly
 * what the box office "row C seat 14" lookup has to resolve against.
 */
function rowAlphabet() {
    const skip = String(rowForm.skipLetters || '').toUpperCase().replace(/[^A-Z]/g, '');

    return 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('').filter((c) => ! skip.includes(c));
}

function rowLabel(i) {
    const start = Math.max(1, clampInt(rowForm.rowStart, 1, 9999) ?? 1);

    if (rowForm.rowStyle === 'numeric') return String(start + i);

    // A..Z then AA, AB, over whatever alphabet is in force. The label is cosmetic; row_position is
    // what actually orders, which is why a skipped letter costs nothing downstream.
    const alpha = rowAlphabet();
    const base = alpha.length;
    let n = i + (start - 1), out = '';

    do { out = alpha[n % base] + out; n = Math.floor(n / base) - 1; } while (n >= 0);

    return (rowForm.rowPrefix || '') + out;
}

/**
 * The number printed on the seat itself.
 *
 * `seq`      1, 2, 3 ... from the left. What this always did, and the only thing it could do.
 * `oddEven`  continental: odd numbers rising to the left of centre, even to the right - the
 *            standard in most European houses, and impossible to represent before.
 * `rtl`      counted from the right, which is how a great many rooms are numbered.
 *
 * @param {number} c zero-based index of the seat within its row
 */
function seatLabelFor(c, perRow) {
    const start = Math.max(0, clampInt(rowForm.seatStart, 0, 9999) ?? 1);

    if (rowForm.seatStyle === 'rtl') return String(start + (perRow - 1 - c));

    if (rowForm.seatStyle === 'oddEven') {
        const mid = Math.floor(perRow / 2);

        // Left half counts outward in odd numbers, right half in even ones.
        return c < mid
            ? String(1 + (mid - 1 - c) * 2)
            : String(2 + (c - mid) * 2);
    }

    return String(start + c);
}

/**
 * The server caps a plan at MAX_SEATS and refuses the whole save past it. The designer knew the
 * running total all along and said nothing, so the way an organizer found out was by losing a save.
 */
function wouldExceedSeatCap(extra) {
    if (totalSeats.value + extra <= MAX_SEATS) return false;
    showError((t.planTooLarge || '').replace(':max', String(MAX_SEATS)));

    return true;
}

/** Seats were the only one of the server's four caps mirrored here; the other three still cost a save. */
const countSections = () => levels.value.reduce((n, l) => n + l.sections.length, 0);
const countTables = () => levels.value.reduce((n, l) => n + l.sections.reduce((m, x) => m + x.tables.length, 0), 0);
const countDecorations = () => levels.value.reduce((n, l) => n + (l.decorations || []).length, 0);

function wouldExceedCap(current, extra, max, message) {
    if (current + extra <= max) return false;
    showError((message || '').replace(':max', String(max)));

    return true;
}

function generateRows() {
    const s = section.value;
    if (!s) return;

    // A cleared field is NaN, and the loop below simply never ran - nothing happened and nothing
    // said why. Clamp to the same bounds the inputs advertise, which were decorative until now.
    const rows = clampInt(rowForm.rows, 1, 60);
    const perRow = clampInt(rowForm.perRow, 1, 80);
    if (! rows || ! perRow) { showError(t.generateNeedsNumbers); return; }
    if (wouldExceedSeatCap(rows * perRow)) return;

    rowForm.rows = rows;
    rowForm.perRow = perRow;
    rowForm.curve = clampInt(rowForm.curve, 0, 120) ?? 0;
    error.value = '';
    checkpoint();
    const aisles = String(rowForm.aisles || '').split(',').map((x) => parseInt(x.trim(), 10)).filter((x) => x > 0);
    // Seat pitch and row pitch, which were hard-coded - so a room with wide seats or tight rows
    // could not be drawn to its own proportions.
    const gapX = clampInt(rowForm.seatPitch, 12, 200) ?? 26;
    const gapY = clampInt(rowForm.rowPitch, 12, 200) ?? 30;
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
                seat_label: seatLabelFor(c, rowForm.perRow),
                x: c * gapX + extra,
                y: (rp - 1) * gapY - curveY,
                kind: 'standard',
                aisle_after: aisles.includes(c + 1),
                position: c + 1,
            });
            if (aisles.includes(c + 1)) extra += 18;
        }
    }
    fitToView({ auto: true });
    dirty.value = true;
}

function generateTables() {
    const s = section.value;
    if (!s) return;

    const count = clampInt(tableForm.count, 1, 60);
    const seats = clampInt(tableForm.seats, 1, 24);
    if (! count || ! seats) { showError(t.generateNeedsNumbers); return; }
    if (wouldExceedSeatCap(count * seats)) return;
    // A table costs a row of its own: 60 tables of one seat, nine times over, blows MAX_TABLES
    // while the seat cap sits untouched.
    if (wouldExceedCap(countTables(), count, MAX_TABLES, t.tooManyTables)) return;

    tableForm.count = count;
    tableForm.seats = seats;
    error.value = '';
    checkpoint();
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
    fitToView({ auto: true });
    dirty.value = true;
}

/**
 * window.confirm(), the same guard `data-confirm` uses across the app.
 *
 * Everything here deletes seats, and until now every one of them did it on a single click of a red
 * text link. A missing translation must NOT turn into a silent yes, so an empty message still asks.
 */
function confirmRemoval(message, replacements = {}) {
    let text = message || '';
    Object.entries(replacements).forEach(([token, value]) => { text = text.split(token).join(value); });

    // Never `|| '?'`: a missing translation used to put a native dialog reading literally "?" in
    // front of an irreversible delete. Fall back to the app's own generic confirmation instead.
    return window.confirm(text.trim() || t.areYouSure || 'Are you sure?');
}

/**
 * The kind the selection currently is, or null when it is mixed.
 *
 * The four buttons used to render identically, so the panel said what you could change the seats
 * TO and never what they were. Mixed shows nothing rather than guessing at one of them.
 */
const selectedKind = computed(() => {
    const s = section.value;
    if (!s || !selectedSeats.value.length) return null;

    const kinds = new Set(
        s.seats.filter((seat) => selectedSeats.value.includes(seat.id)).map((seat) => seat.kind || 'standard')
    );

    return kinds.size === 1 ? [...kinds][0] : null;
});

function applyKind(kind) {
    const s = section.value;
    if (!s) return;
    checkpoint();
    s.seats.forEach((seat) => { if (selectedSeats.value.includes(seat.id)) seat.kind = kind; });
    dirty.value = true;
}
/** The one selected seat, or null - a seat number is per seat, so a multi-selection has none. */
const oneSeat = computed(() => {
    if (selectedSeats.value.length !== 1 || ! section.value) return null;

    return section.value.seats.find((x) => x.id === selectedSeats.value[0]) || null;
});

/**
 * The row label shared by the whole selection, or null when it spans more than one row.
 *
 * A row letter belongs to the ROW, so editing it from a single seat has to move every seat in that
 * row - otherwise a row ends up half A and half B, which the orphan rule and the box office lookup
 * both read as two different rows.
 */
const selectedRowLabel = computed(() => {
    if (! section.value || ! selectedSeats.value.length) return null;

    const rows = new Set(section.value.seats
        .filter((x) => selectedSeats.value.includes(x.id))
        .map((x) => x.row_position));

    if (rows.size !== 1) return null;

    const first = section.value.seats.find((x) => x.row_position === [...rows][0]);

    return first ? (first.row_label || '') : null;
});

function renameSeat(value) {
    const seat = oneSeat.value;
    if (! seat) return;

    seat.seat_label = String(value).slice(0, 10);
    dirty.value = true;
}

function renameRow(value) {
    if (! section.value || selectedRowLabel.value === null) return;

    const target = section.value.seats.find((x) => selectedSeats.value.includes(x.id));
    if (! target) return;

    const label = String(value).slice(0, 10);
    section.value.seats.forEach((seat) => {
        if (seat.row_position === target.row_position) seat.row_label = label;
    });
    dirty.value = true;
}

/** Take their version. Warns first - `dirty` is real work that reloading throws away. */
function reloadForTheirs() {
    if (dirty.value && ! window.confirm(t.confirmReload || '')) return;

    // guardUnload would otherwise ask a second time, about the same decision.
    dirty.value = false;
    window.location.reload();
}

function toggleAisle() {
    const s = section.value;
    if (!s) return;
    checkpoint();
    s.seats.forEach((seat) => { if (selectedSeats.value.includes(seat.id)) seat.aisle_after = !seat.aisle_after; });
    dirty.value = true;
}
function removeSelectedSeats() {
    const s = section.value;
    if (!s) return;
    const locked = s.seats.filter((x) => selectedSeats.value.includes(x.id) && x.locked);
    if (locked.length) { showError(t.cannotRemoveSold); return; }

    // After the sold-seat guard, never before it: asking and then refusing is worse than refusing.
    if (! confirmRemoval(t.confirmRemoveSeats, { ':count': selectedSeats.value.length })) return;

    checkpoint();
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
    return asOneOperation(() => applyPresetInner(key));
}

function applyPresetInner(key) {
    checkpoint();
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
    // addSection() selects as it goes, so on a two-level preset the selection ends up on the
    // BALCONY section while the active level is reset to the stalls - and `section` only ever
    // searches the active level, so the first thing a new user saw was an empty inspector.
    selectedSectionId.value = levels.value[0]?.sections[0]?.id ?? null;
    fitToView({ auto: true });
    dirty.value = true;
}

// ---- validation
/**
 * Advisory, never blocking - but each entry now knows WHICH section it is about, so the panel can
 * take you there. Keyed by section and rule rather than deduped by message text: two unnamed
 * sections are two problems, and collapsing them to one line under-reported the count.
 */
const issues = computed(() => {
    const out = [];
    const push = (levelIndex, s, rule, text) => out.push({ key: `${s.id}|${rule}`, levelIndex, sectionId: s.id, text });

    levels.value.forEach((lvl, levelIndex) => {
        lvl.sections.forEach((s) => {
            if (!s.name || !s.name.trim()) push(levelIndex, s, 'name', t.issueUnnamedSection);
            if (s.kind === 'seated' && !s.seats.length) push(levelIndex, s, 'seats', `${s.name}: ${t.issueNoSeats}`);
            if (s.kind === 'standing' && !(s.capacity > 0)) push(levelIndex, s, 'capacity', `${s.name}: ${t.issueNoCapacity}`);
            if (!s.band || !s.band.trim()) push(levelIndex, s, 'band', `${s.name}: ${t.issueNoBand}`);

            // A wheelchair space is only sellable out of an accessibility-only section, so one
            // drawn anywhere else is bookable by NOBODY. The docs warn about this twice; nothing
            // in the product did, and it is the only issue here that silently costs a sale.
            if (! s.accessibility_only && s.seats.some((seat) => seat.kind === 'wheelchair')) {
                push(levelIndex, s, 'wheelchair', `${s.name}: ${t.issueWheelchairOutside}`);
            }

            const seen = new Set();
            const dupes = new Set();
            s.seats.forEach((seat) => {
                if (!seat.seat_label) return;
                const key = `${seat.table_id || ''}|${seat.row_label || ''}|${seat.seat_label}`;
                if (seen.has(key) && !dupes.has(key)) {
                    dupes.add(key);
                    push(levelIndex, s, `dup:${key}`, `${s.name}: ${t.issueDuplicateSeat} ${seat.row_label || ''} ${seat.seat_label}`);
                }
                seen.add(key);
            });
        });
    });
    return out;
});

/**
 * The one control that decides whether wheelchair seats are sellable at all, so of everything that
 * was missing a checkpoint this is the one that mattered.
 */
function toggleAccessibilityOnly() {
    const s = section.value;
    if (!s) return;
    checkpoint();
    s.accessibility_only = !s.accessibility_only;
    dirty.value = true;
}

function setCapacity(value) {
    const s = section.value;
    if (!s) return;
    // max="65535" was decorative, exactly as clampInt's docblock says these attributes are.
    const next = clampInt(value, 0, 65535);
    if (next === null || next === s.capacity) return;
    checkpoint();
    s.capacity = next;
    dirty.value = true;
}

/**
 * Rotation was rendered (`rotate(${s.rotation})`), stored, round-tripped and range-validated by the
 * server all along - and no control ever wrote it, so every section sat at 0 degrees and an angled
 * side block could not be drawn at all.
 */
function setRotation(value) {
    const s = section.value;
    if (!s) return;
    const next = clampInt(value, -360, 360);
    if (next === null || next === (s.rotation || 0)) return;
    checkpoint();
    s.rotation = next;
    dirty.value = true;
    fitToView({ auto: true });
}

function rotateSection(delta) {
    const s = section.value;
    if (!s) return;
    let next = ((Number(s.rotation) || 0) + delta) % 360;
    if (next > 180) next -= 360;
    if (next < -180) next += 360;
    setRotation(next);
}

function updateTable(patch) {
    const tb = selectedTable.value;
    if (!tb) return;
    checkpoint();
    Object.assign(tb, patch);
    dirty.value = true;
}

/**
 * Reuses hasSoldSeats, as every other delete path does. Without it the designer drops a seat
 * somebody holds a ticket for, and the server refuses the whole save naming a seat that is no
 * longer on screen to find.
 */
function removeTable() {
    const s = section.value;
    const tb = selectedTable.value;
    if (!s || !tb) return;

    const seats = s.seats.filter((x) => x.table_id === tb.id);
    if (seats.some((x) => x.locked)) { showError(t.cannotRemoveSoldHere); return; }
    if (! confirmRemoval(t.confirmRemoveTable, { ':table': tb.label || '', ':count': seats.length })) return;

    checkpoint();
    s.seats = s.seats.filter((x) => x.table_id !== tb.id);
    s.tables = s.tables.filter((x) => x.id !== tb.id);
    selectedTableId.value = null;
    dirty.value = true;
}

function setSectionColor(color) {
    const s = section.value;
    if (!s) return;
    checkpoint();
    s.color = color;
    dirty.value = true;
}

/** Naming a problem and leaving the organizer to find it across twelve levels is half a feature. */
function goToIssue(issue) {
    if (!issue) return;
    activeLevel.value = issue.levelIndex;
    selectedSectionId.value = issue.sectionId;
    selectedSeats.value = [];

    // Selecting it is not "going to" it: if the section was already selected and sits off-canvas,
    // the click produced nothing observable at all. Bring it into frame.
    nextTick(() => {
        const s = section.value;
        if (!s) return;
        const b = sectionBox(s);
        revealPoint(s.x + b.x + b.w / 2, s.y + b.y + b.h / 2);
    });
}

// ---- persistence
/**
 * Fetch the plan. Nothing is offered until this lands.
 *
 * The empty state renders on `!levels.length`, which is true from the first paint - so the preset
 * picker appeared BEFORE this fetch resolved, and clicking one built a layout that the assignment
 * below then silently wiped. The organizer saw their chosen layout appear and vanish, with the
 * toolbar still claiming unsaved changes.
 */
async function load() {
    loading.value = true;
    loadFailed.value = false;
    error.value = '';

    try {
        const res = await fetch(props.structureUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        if (!res.ok) { loadFailed.value = true; return; }

        const data = await res.json();
        revision.value = data.revision ?? null;
        if (data.rules) Object.assign(rules, data.rules);

        levels.value = (data.levels || []).map((l) => Object.assign({}, l, {
            sections: (l.sections || []).map((s) => Object.assign({}, s, {
                band: s.band || '',
                tables: s.tables || [],
                seats: s.seats || [],
            })),
        }));

        if (levels.value.length) selectedSectionId.value = levels.value[0].sections[0]?.id ?? null;
        // Nothing before the load is worth undoing to - and after a save the ids in an older
        // snapshot no longer exist, so restoring one would post seats the server has renumbered.
        savedSnapshot = JSON.stringify(levels.value);
        undoStack.value = [];
        redoStack.value = [];
        fitToView();
    } catch (e) {
        // There was no catch here at all, so an offline fetch threw, set nothing, and left the
        // empty-state preset gallery rendering over a plan that still exists on the server.
        loadFailed.value = true;
    } finally {
        loading.value = false;
    }
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
            body: JSON.stringify({
                name: planName.value,
                levels: levels.value,
                revision: revision.value,
                ...rules,
            }),
        });
        const data = await res.json().catch(() => ({}));
        // 409: somebody else saved this plan since we read it. Never retry - the payload is the
        // WHOLE structure, so retrying is precisely the overwrite the check exists to prevent.
        if (res.status === 409) { error.value = data.error || t.staleRevision; stale.value = true; return; }
        if (!res.ok) { error.value = data.error || t.saveFailed; return; }
        revision.value = data.revision ?? revision.value;
        if (data.rules) Object.assign(rules, data.rules);
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
        // Temporary ids became real ones in the response, so every snapshot above still refers to
        // rows that no longer exist under those numbers. Undoing into one would re-create them.
        savedSnapshot = JSON.stringify(levels.value);
        undoStack.value = [];
        redoStack.value = [];
        dirty.value = false;
        savedAt.value = Date.now();
        setTimeout(() => { if (Date.now() - savedAt.value >= 2500) savedAt.value = 0; }, 2600);
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

const onResize = () => { viewportH.value = window.innerHeight; stacked.value = window.innerWidth < 1280; };

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', guardUnload);
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('resize', onResize);
});

onMounted(async () => {
    window.addEventListener('beforeunload', guardUnload);
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('resize', onResize);
    observeCanvas();
    await load();
    // The svg only has a size once v-else has rendered it, which is after load() sets levels.
    await nextTick();
    observeCanvas();
    fitToView();

    // Select, not just focus: the value is "Untitled plan", so the first keystroke should replace
    // it. Neither call fires @input, so this must NOT arm the unsaved guard.
    if (props.focusName && props.nameEditable) {
        nameInput.value?.focus();
        nameInput.value?.select();
    }
});
</script>

<style scoped>
/*
 * Keyboard focus on a seat was completely invisible: an SVG <g> takes no useful default outline,
 * and nothing else changed. Paired with the roving tabindex, this is the only thing telling a
 * keyboard user where they are in a 1,200-seat house.
 */
.seat-node:focus {
    outline: none;
}
.seat-node:focus-visible > :first-child {
    stroke: var(--brand-blue);
    stroke-width: 3;
    paint-order: stroke;
}
.seat-canvas:focus-within .seat-node:focus > :first-child {
    stroke: var(--brand-blue);
    stroke-width: 3;
}
</style>
