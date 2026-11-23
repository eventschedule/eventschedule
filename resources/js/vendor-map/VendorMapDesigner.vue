<template>
    <div class="vmd" :dir="rtl ? 'rtl' : 'ltr'">
        <!-- Toolbar. The way back names where it goes; the forward action (Save) is last. -->
        <header class="vmd-bar">
            <a :href="urls.back" class="vmd-back">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                <span>{{ t.back_to_tab }}</span>
            </a>
            <div class="vmd-tools" role="toolbar" :aria-label="t.tools">
                <button type="button" class="vmd-tool" @click="addSpot()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="5" y="7" width="14" height="10" rx="2" /><path d="M12 10v4M10 12h4" stroke-linecap="round" /></svg>
                    <span>{{ t.add_spot }}</span>
                </button>
                <button type="button" class="vmd-tool" @click="rowDialog = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2.5" y="9" width="5" height="6" rx="1" /><rect x="9.5" y="9" width="5" height="6" rx="1" /><rect x="16.5" y="9" width="5" height="6" rx="1" /></svg>
                    <span>{{ t.add_row }}</span>
                </button>
                <button type="button" class="vmd-tool" :class="{ 'is-on': numbering }" :aria-pressed="numbering ? 'true' : 'false'" @click="toggleNumbering">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 6h2v6M5 12h4M14 7h5M14 12h5M14 17h5M5 15.5c0-1 .8-1.5 1.6-1.5S8 14.6 8 15.3c0 1.4-3 1.8-3 3.7h3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span>{{ t.number_in_order }}</span>
                </button>
                <div class="vmd-menu-wrap">
                    <button type="button" class="vmd-tool" :aria-expanded="landmarkMenu ? 'true' : 'false'" @click="landmarkMenu = !landmarkMenu">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 21s-6-5.3-6-11a6 6 0 1112 0c0 5.7-6 11-6 11z" /><circle cx="12" cy="10" r="2.2" /></svg>
                        <span>{{ t.add_landmark }}</span>
                    </button>
                    <div v-if="landmarkMenu" class="vmd-menu" role="menu">
                        <button v-for="k in landmarkKinds" :key="k" type="button" role="menuitem" @click="addLandmark(k)">{{ t['landmark_' + k] }}</button>
                        <button type="button" role="menuitem" @click="placingHere = true; landmarkMenu = false">{{ t.you_are_here }}</button>
                    </div>
                </div>
                <button type="button" class="vmd-tool" :disabled="!history.length" @click="undo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M9 15L3 9l6-6M3 9h11a6 6 0 010 12h-3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span>{{ t.undo }}</span>
                </button>
            </div>
            <span v-if="dirty && !saving" class="vmd-status"><i aria-hidden="true"></i>{{ t.draft_not_saved }}</span>
            <button v-if="!(reveal.done && reveal.summary)" type="button" class="vmd-save" :disabled="saving || !dirty" @click="save()">
                <span v-if="saving" class="vmd-spinner" aria-hidden="true"></span>
                {{ saving ? t.saving : (dirty ? t.save : t.saved) }}
            </button>
        </header>

        <!-- After the AI: what it found, and the two ways on. -->
        <div v-if="reveal.done && reveal.summary" class="vmd-found" role="status">
            <span class="vmd-found-badge" aria-hidden="true">{{ model.spots.length }}</span>
            <div class="vmd-found-text">
                <strong>{{ reveal.summary }}</strong>
                <span>{{ choice(t.need_names, model.spots.length - namedCount) }}</span>
            </div>
            <div class="vmd-found-actions">
                <button v-if="namedCount" type="button" class="vmd-ghost" @click="checking = true; selectedKey = null">{{ t.check_names }}</button>
                <button type="button" class="vmd-primary" :disabled="saving" @click="save({ fromReveal: true })">{{ t.looks_right_save }}</button>
            </div>
        </div>

        <div v-if="blocksHint && reveal.done" class="vmd-note" role="status">{{ t.drag_areas }}</div>

        <div v-if="clash" class="vmd-note is-warn" role="alert">
            <span>{{ t.revision_clash }}</span>
            <button type="button" class="vmd-ghost" @click="saveAnyway">{{ t.save_mine_anyway }}</button>
        </div>
        <div v-if="error" class="vmd-note is-error" role="alert">{{ error }}</div>
        <div v-if="numbering" class="vmd-note" role="status">{{ t.numbering_help.replace(':next', String(nextNumber)) }}
            <button type="button" class="vmd-ghost" @click="toggleNumbering">{{ t.done }}</button>
        </div>
        <div v-if="placingHere" class="vmd-note" role="status">{{ t.here_help }}
            <button type="button" class="vmd-ghost" @click="placingHere = false">{{ t.cancel }}</button>
        </div>

        <div class="vmd-body">
            <!-- The plan. Drawn from above; never mirrored in RTL (it is geography). -->
            <div class="vmd-stage" ref="stageEl">
                <svg ref="svgEl" v-bind="bind" :viewBox="`0 0 ${canvas.w} ${canvas.h}`" class="vmd-svg"
                     role="application" :aria-label="t.map_label" dir="ltr"
                     @pointerdown.self="onStagePress">
                    <defs>
                        <pattern id="vmd-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                            <path d="M20 0H0V20" fill="none" class="vmd-gridline" />
                        </pattern>
                    </defs>
                    <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                        <rect x="0" y="0" :width="model.width" :height="model.height" class="vmd-ground" @pointerdown="onGroundPress" />
                        <rect x="0" y="0" :width="model.width" :height="model.height" fill="url(#vmd-grid)" pointer-events="none" />
                        <image v-if="backgroundUrl && showBackground" :href="backgroundUrl" x="0" y="0" :width="model.width" :height="model.height"
                               preserveAspectRatio="xMidYMid meet" :opacity="bgOpacity" pointer-events="none" />

                        <!-- Areas: a soft tint around their spots, with the name. -->
                        <!-- An area's name is its handle: dragging it moves the whole block, stalls and all
                             (a market photographed area by area is put together this way). -->
                        <g v-for="a in areaBoxes" :key="'ab' + a.key">
                            <rect :x="a.x" :y="a.y" :width="a.w" :height="a.h" rx="14" :fill="a.color" fill-opacity="0.08" :stroke="a.color" stroke-opacity="0.45" stroke-dasharray="5 4" pointer-events="none" />
                            <g class="vmd-area-handle" :transform="`translate(${a.x + 6} ${a.y + 4})`" @pointerdown.stop="startAreaDrag($event, a)">
                                <title>{{ t.drag_areas }}</title>
                                <rect :width="Math.max(40, a.name.length * 6.6 + 30)" height="19" rx="9.5" :stroke="a.color" />
                                <path d="M8 6.5h4M8 9.5h4M8 12.5h4" :stroke="a.color" stroke-width="1.3" stroke-linecap="round" />
                                <text x="18" y="13.5" class="vmd-area-label" :fill="a.color" style="unicode-bidi: plaintext">{{ a.name }}</text>
                            </g>
                        </g>

                        <!-- Landmarks. -->
                        <g v-for="l in model.landmarks" :key="'l' + l.key" class="vmd-landmark"
                           :class="{ 'is-selected': selectedLandmark === l.key }"
                           :transform="`translate(${l.x} ${l.y}) rotate(${l.rotation || 0} ${l.w / 2} ${l.h / 2})`"
                           @pointerdown.stop="startDrag($event, 'landmark', l)">
                            <rect :width="l.w" :height="l.h" rx="8" />
                            <text :x="l.w / 2" :y="l.h / 2 + 4" text-anchor="middle" style="unicode-bidi: plaintext">{{ l.label || t['landmark_' + l.kind] }}</text>
                        </g>

                        <!-- Spots. -->
                        <g v-for="(s, i) in model.spots" :key="s.key"
                           class="vmd-spot" :class="{ 'is-selected': selectedKey === s.key, 'is-revealing': reveal.running, 'is-unsure': checking && s.name && s.confidence !== undefined && s.confidence < 0.6 }"
                           :style="reveal.running ? { animationDelay: (Math.min(i, 60) * 45) + 'ms' } : null"
                           :transform="`translate(${s.x} ${s.y}) rotate(${s.rotation || 0} ${s.w / 2} ${s.h / 2})`"
                           role="button" tabindex="0" :aria-label="spotAria(s)"
                           @keydown.enter.prevent="select(s)" @keydown.delete.prevent="removeSpot(s)"
                           @pointerdown.stop="onSpotPress($event, s)">
                            <!-- A stall with a name is filled; one that still needs a name is dashed. -->
                            <rect :width="s.w" :height="s.h" rx="6" class="vmd-spot-box" :class="{ 'is-named': !!s.name }" :style="spotStyle(s)" />
                            <text :x="s.w / 2" :y="s.h / 2 + 5" text-anchor="middle" class="vmd-spot-num" :style="spotInk(s)">{{ s.label }}</text>
                        </g>

                        <!-- You are here. -->
                        <g v-if="model.here" :transform="`translate(${model.here.x} ${model.here.y})`" class="vmd-here" @pointerdown.stop="startDrag($event, 'here', model.here)">
                            <circle r="9" />
                            <circle r="3.5" class="vmd-here-dot" />
                        </g>
                    </g>
                </svg>

                <div class="vmd-zoom" role="group" :aria-label="t.zoom">
                    <button type="button" @click="zoomBy(1.25)" :aria-label="t.zoom_in">+</button>
                    <button type="button" @click="zoomBy(0.8)" :aria-label="t.zoom_out">&minus;</button>
                    <button type="button" @click="fit()" class="vmd-fit">{{ t.fit }}</button>
                </div>

                <label v-if="backgroundUrl" class="vmd-bg">
                    <input type="checkbox" v-model="showBackground" />
                    <span>{{ t.trace_photo }}</span>
                    <input v-if="showBackground" type="range" min="0.1" max="1" step="0.05" v-model.number="bgOpacity" :aria-label="t.photo_opacity" />
                </label>
                <label v-if="backgroundUrl && showBackground" class="vmd-bg vmd-bg-public">
                    <input type="checkbox" v-model="pictureForShoppers" @change="dirty = true" />
                    <span>{{ t.picture_public }}</span>
                    <button v-if="background" type="button" class="vmd-bg-remove" @click.prevent="removeBackground = true; pictureForShoppers = false; showBackground = false; backgroundUrl = null; dirty = true">{{ t.remove }}</button>
                </label>
            </div>

            <!-- The panel: a side column on a computer, a sheet on a phone. -->
            <aside class="vmd-panel" :class="{ 'is-open': !!selectedSpot || !!selectedLandmarkObj || checking || areaPanel }">
                <!-- One spot. -->
                <template v-if="selectedSpot">
                    <div class="vmd-panel-head">
                        <h2>{{ strings.spot.replace(':number', selectedSpot.label) }}</h2>
                        <button type="button" class="vmd-x" :aria-label="t.close" @click="selectedKey = null">&times;</button>
                    </div>
                    <div class="vmd-fields">
                        <label class="vmd-field"><span>{{ t.number }}</span>
                            <input type="text" maxlength="20" :value="selectedSpot.label" @change="setField(selectedSpot, 'label', $event.target.value.trim() || selectedSpot.label)" />
                        </label>
                        <label class="vmd-field"><span>{{ t.name_on_spot }}</span>
                            <input v-if="!selectedSpot.name_locked" type="text" maxlength="120" :value="selectedSpot.name || ''" dir="auto" :placeholder="t.name_placeholder" @change="setField(selectedSpot, 'name', $event.target.value.trim())" />
                            <span v-else class="vmd-locked" dir="auto">{{ selectedSpot.name }} <small>{{ t.name_locked }}</small></span>
                        </label>
                        <label class="vmd-field"><span>{{ t.category }}</span>
                            <select :value="selectedSpot.category || ''" @change="setField(selectedSpot, 'category', $event.target.value || null)">
                                <option value="">{{ t.no_category }}</option>
                                <option v-for="(label, slug) in categories" :key="slug" :value="slug">{{ label }}</option>
                            </select>
                        </label>
                        <label class="vmd-field"><span>{{ t.area }}</span>
                            <select :value="selectedSpot.area ?? ''" @change="setArea(selectedSpot, $event.target.value)">
                                <option value="">{{ t.no_area }}</option>
                                <option v-for="a in model.areas" :key="a.key" :value="a.key">{{ a.name }}</option>
                                <option value="__new">{{ t.new_area }}</option>
                            </select>
                        </label>
                        <div class="vmd-pair">
                            <label class="vmd-field"><span>{{ t.width }}</span><input type="number" min="8" max="600" :value="selectedSpot.w" @change="setField(selectedSpot, 'w', clampNum($event.target.value, 8, 600))" /></label>
                            <label class="vmd-field"><span>{{ t.depth }}</span><input type="number" min="8" max="600" :value="selectedSpot.h" @change="setField(selectedSpot, 'h', clampNum($event.target.value, 8, 600))" /></label>
                        </div>
                        <div class="vmd-rotate">
                            <span>{{ t.turn }}</span>
                            <button type="button" @click="setField(selectedSpot, 'rotation', ((selectedSpot.rotation || 0) - 15))" :aria-label="t.turn_left">&#8634;</button>
                            <output>{{ selectedSpot.rotation || 0 }}&deg;</output>
                            <button type="button" @click="setField(selectedSpot, 'rotation', ((selectedSpot.rotation || 0) + 15))" :aria-label="t.turn_right">&#8635;</button>
                        </div>
                    </div>
                    <div class="vmd-panel-actions">
                        <button type="button" class="vmd-ghost" @click="moving = selectedSpot.key">{{ t.move_to }}</button>
                        <button type="button" class="vmd-ghost" @click="duplicate(selectedSpot)">{{ t.duplicate }}</button>
                        <button type="button" class="vmd-danger" @click="removeSpot(selectedSpot)">{{ t.remove }}</button>
                    </div>
                    <p v-if="moving" class="vmd-hint">{{ t.move_help }}</p>
                </template>

                <!-- One landmark. -->
                <template v-else-if="selectedLandmarkObj">
                    <div class="vmd-panel-head">
                        <h2>{{ t['landmark_' + selectedLandmarkObj.kind] }}</h2>
                        <button type="button" class="vmd-x" :aria-label="t.close" @click="selectedLandmark = null">&times;</button>
                    </div>
                    <div class="vmd-fields">
                        <label class="vmd-field"><span>{{ t.label }}</span>
                            <input type="text" maxlength="80" dir="auto" :value="selectedLandmarkObj.label || ''" @change="setField(selectedLandmarkObj, 'label', $event.target.value.trim())" />
                        </label>
                        <div class="vmd-pair">
                            <label class="vmd-field"><span>{{ t.width }}</span><input type="number" min="8" max="1000" :value="selectedLandmarkObj.w" @change="setField(selectedLandmarkObj, 'w', clampNum($event.target.value, 8, 1000))" /></label>
                            <label class="vmd-field"><span>{{ t.depth }}</span><input type="number" min="8" max="1000" :value="selectedLandmarkObj.h" @change="setField(selectedLandmarkObj, 'h', clampNum($event.target.value, 8, 1000))" /></label>
                        </div>
                    </div>
                    <div class="vmd-panel-actions">
                        <button type="button" class="vmd-danger" @click="removeLandmark(selectedLandmarkObj)">{{ t.remove }}</button>
                    </div>
                </template>

                <!-- Checking the names the AI read: the unsure ones first. -->
                <template v-else-if="checking">
                    <div class="vmd-panel-head">
                        <h2>{{ t.check_names }}</h2>
                        <button type="button" class="vmd-x" :aria-label="t.close" @click="checking = false">&times;</button>
                    </div>
                    <p class="vmd-hint">{{ t.check_names_lead }}</p>
                    <ul class="vmd-names">
                        <li v-for="s in namesToCheck" :key="'n' + s.key" :class="{ 'is-unsure': s.confidence !== undefined && s.confidence < 0.6 }">
                            <button type="button" class="vmd-name-num" @click="select(s); revealSpot(s)">{{ s.label }}</button>
                            <input type="text" dir="auto" maxlength="120" :value="s.name" :disabled="s.name_locked" @change="setField(s, 'name', $event.target.value.trim())" :aria-label="strings.spot.replace(':number', s.label)" />
                            <span v-if="s.confidence !== undefined && s.confidence < 0.6" class="vmd-tag">{{ t.check_this_one }}</span>
                            <button v-if="!s.name_locked" type="button" class="vmd-x" :aria-label="t.clear_name" @click="clearName(s)">&times;</button>
                        </li>
                    </ul>
                    <div class="vmd-panel-actions">
                        <button type="button" class="vmd-primary" :disabled="saving" @click="checking = false; save()">{{ t.save }}</button>
                    </div>
                </template>

                <!-- Areas. -->
                <template v-else-if="areaPanel">
                    <div class="vmd-panel-head">
                        <h2>{{ t.areas }}</h2>
                        <button type="button" class="vmd-x" :aria-label="t.close" @click="areaPanel = false">&times;</button>
                    </div>
                    <ul class="vmd-areas">
                        <li v-for="a in model.areas" :key="'ar' + a.key">
                            <input type="color" :value="a.color" @change="setField(a, 'color', $event.target.value)" :aria-label="t.color" />
                            <input type="text" dir="auto" maxlength="80" :value="a.name" @change="setField(a, 'name', $event.target.value.trim() || a.name)" />
                            <button type="button" class="vmd-x" :aria-label="t.remove" @click="removeArea(a)">&times;</button>
                        </li>
                    </ul>
                    <button type="button" class="vmd-ghost" @click="newArea()">{{ t.new_area }}</button>
                </template>

                <!-- Nothing chosen: what is here. -->
                <template v-else>
                    <div class="vmd-summary">
                        <div class="vmd-stats">
                            <div class="vmd-stats-top">
                                <strong>{{ choice(strings.spots_count, model.spots.length) }}</strong>
                                <span>{{ (t.named_count || ':count').replace(':count', String(namedCount)) }}</span>
                            </div>
                            <div class="vmd-meter" aria-hidden="true"><i :style="{ width: (model.spots.length ? Math.round(namedCount / model.spots.length * 100) : 0) + '%' }"></i></div>
                        </div>

                        <p class="vmd-panel-label">{{ t.areas }}</p>
                        <ul class="vmd-area-list">
                            <li v-for="a in areaCounts" :key="'ac' + a.key">
                                <button type="button" @click="areaPanel = true">
                                    <i :style="{ background: a.color }" aria-hidden="true"></i>
                                    <bdi>{{ a.name }}</bdi>
                                    <b>{{ a.count }}</b>
                                </button>
                            </li>
                            <li><button type="button" class="vmd-area-new" @click="areaPanel = true">{{ t.new_area }}</button></li>
                        </ul>

                        <ul class="vmd-legend">
                            <li><i class="is-named" aria-hidden="true"></i>{{ t.has_name }}</li>
                            <li><i aria-hidden="true"></i>{{ t.needs_name }}</li>
                        </ul>
                        <button v-if="namedCount" type="button" class="vmd-ghost" @click="checking = true">{{ t.check_names }}</button>

                        <p class="vmd-hint vmd-hint-foot">{{ model.spots.length ? t.tap_to_edit : t.empty_lead }}</p>
                    </div>
                </template>
            </aside>
        </div>

        <!-- Add a row of N. -->
        <div v-if="rowDialog" class="vmd-dialog" role="dialog" aria-modal="true" :aria-label="t.add_row" @keydown.esc="rowDialog = false">
            <div class="vmd-dialog-card">
                <h2>{{ t.add_row }}</h2>
                <div class="vmd-pair">
                    <label class="vmd-field"><span>{{ t.how_many }}</span><input type="number" min="1" max="60" v-model.number="row.count" /></label>
                    <label class="vmd-field"><span>{{ t.first_number }}</span><input type="number" min="1" max="9999" v-model.number="row.start" /></label>
                </div>
                <fieldset class="vmd-field">
                    <legend>{{ t.numbering_direction }}</legend>
                    <label class="vmd-radio"><input type="radio" value="start" v-model="row.from" /> {{ rtl ? t.from_right : t.from_left }}</label>
                    <label class="vmd-radio"><input type="radio" value="end" v-model="row.from" /> {{ rtl ? t.from_left : t.from_right }}</label>
                </fieldset>
                <label class="vmd-field"><span>{{ t.area }}</span>
                    <select v-model="row.area">
                        <option value="">{{ t.no_area }}</option>
                        <option v-for="a in model.areas" :key="'ra' + a.key" :value="a.key">{{ a.name }}</option>
                    </select>
                </label>
                <div class="vmd-dialog-actions">
                    <button type="button" class="vmd-ghost" @click="rowDialog = false">{{ t.cancel }}</button>
                    <button type="button" class="vmd-primary" @click="addRow()">{{ t.add }}</button>
                </div>
            </div>
        </div>

        <!-- One toast, with Undo when there is something to undo. -->
        <div v-if="toast" class="vmd-toast" role="status" aria-live="polite">
            <span>{{ toast.text }}</span>
            <button v-if="toast.undo" type="button" @click="undo(); toast = null">{{ t.undo }}</button>
        </div>
    </div>
</template>

<script setup>
import { choose } from '../plural';
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useMapViewport } from '../seat-map-viewport';

const props = defineProps({
    structure: { type: Object, required: true },
    kind: { type: String, default: 'market' },
    draft: { type: String, default: null },
    categories: { type: Object, default: () => ({}) },
    strings: { type: Object, default: () => ({}) },
    t: { type: Object, default: () => ({}) },
    background: { type: String, default: null },
    backgroundPublic: { type: Boolean, default: false },
    rtl: { type: Boolean, default: false },
    urls: { type: Object, required: true },
    csrfToken: { type: String, required: true },
});

const landmarkKinds = ['entrance', 'restroom', 'parking', 'stage', 'text'];
const AREA_COLORS = ['#4E81FA', '#0EA5E9', '#10B981', '#F59E0B', '#EF4444', '#14B8A6', '#84CC16', '#F97316'];

let seq = 0;
const key = () => 'k' + (++seq);

const model = reactive({ width: 1000, height: 700, revision: 0, areas: [], spots: [], landmarks: [], here: null });
const history = ref([]);
const dirty = ref(false);
const saving = ref(false);
const clash = ref(false);
const error = ref('');
const toast = ref(null);
let toastTimer = null;

const selectedKey = ref(null);
const selectedLandmark = ref(null);
const moving = ref(null);
const numbering = ref(false);
const nextNumber = ref(1);
const placingHere = ref(false);
const landmarkMenu = ref(false);
const rowDialog = ref(false);
const areaPanel = ref(false);
const checking = ref(false);
const row = reactive({ count: 10, start: 1, from: 'start', area: '' });

const backgroundUrl = ref(props.background);
const showBackground = ref(!!props.background);
const bgOpacity = ref(0.5);
// The owner's choice to show the picture under the shoppers' map (off unless chosen).
const pictureForShoppers = ref(!!props.backgroundPublic);
// Hiding the photo only hides it; it is removed only when the owner says so.
const removeBackground = ref(false);
const draftBackground = ref(null);

const reveal = reactive({ running: false, done: false, summary: '' });

// ---- loading ----

function load(structure) {
    model.width = structure.width || 1000;
    model.height = structure.height || 700;
    model.revision = structure.revision || 0;
    const areaKey = {};
    model.areas = (structure.areas || []).map((a) => {
        const k = 'a' + a.id;
        areaKey[a.id] = k;
        return { id: a.id, key: k, name: a.name, color: a.color };
    });
    model.spots = (structure.spots || []).map((s) => ({
        id: s.id, key: key(), label: s.label, area: s.area_id ? areaKey[s.area_id] ?? null : null,
        x: s.x, y: s.y, w: s.w, h: s.h, rotation: s.rotation || 0, shape: s.shape,
        name: s.name || '', category: s.category || null, name_locked: !!s.name_locked,
    }));
    model.landmarks = (structure.landmarks || []).map((l) => ({ ...l, key: key() }));
    model.here = structure.here ? { ...structure.here } : null;
}

/** The AI's draft, laid on an EMPTY plan (or, after asking, over the current one). */
const blocksHint = ref(false);

function loadDraft(result, images) {
    blocksHint.value = (result.blocks || 0) > 1;
    const areaKeyByName = {};
    model.areas = (result.areas || []).map((a, i) => {
        const k = key();
        areaKeyByName[a.name.toLowerCase()] = k;
        return { key: k, name: a.name, color: AREA_COLORS[i % AREA_COLORS.length], box: a };
    });
    const inBox = (s) => {
        const cx = s.x + s.w / 2;
        const cy = s.y + s.h / 2;
        const a = model.areas.find((ar) => ar.box && cx >= ar.box.x && cx <= ar.box.x + ar.box.w && cy >= ar.box.y && cy <= ar.box.y + ar.box.h);
        return a ? a.key : null;
    };
    model.width = result.width || 1000;
    model.height = result.height || 700;
    model.spots = (result.spots || []).map((s) => {
        const spot = {
            key: key(), label: s.label, x: s.x, y: s.y, w: s.w, h: s.h, rotation: s.rotation || 0,
            name: s.name || '', sells: s.sells || '', category: s.category || null, confidence: s.confidence,
            name_locked: false, fromDraft: true,
        };
        spot.area = (s.area && areaKeyByName[s.area.toLowerCase()]) || inBox(spot);
        return spot;
    });
    model.landmarks = (result.landmarks || []).map((l) => ({ ...l, rotation: 0, key: key() }));
    dirty.value = true;

    // One photo (not a PDF) can be traced over; several aisle photos sit under nothing.
    const pictures = (images || []).filter((i) => !i.pdf);
    if (pictures.length === 1 && (images || []).length === 1) {
        backgroundUrl.value = pictures[0].url;
        draftBackground.value = props.draft + ':0';
        // A new photo is not shown to shoppers until the owner says so for this one.
        pictureForShoppers.value = false;
        showBackground.value = true;
        bgOpacity.value = 0.45;
    }

    const names = result.names_read ?? model.spots.filter((s) => s.name).length;
    reveal.summary = props.strings.spots_found_count
        ? props.strings.spots_found_count
        : (props.t.found_summary || '').replace(':spots', String(model.spots.length)).replace(':names', String(names));
    reveal.running = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    setTimeout(() => { reveal.running = false; reveal.done = true; }, reveal.running ? Math.min(model.spots.length, 60) * 45 + 700 : 0);
}

// ---- viewport ----

const svgEl = ref(null);
const stageEl = ref(null);
const dragging = ref(null);

function contentBounds() {
    const items = [...model.spots, ...model.landmarks];
    if (!items.length) return { minX: 0, minY: 0, w: model.width, h: model.height };
    let minX = Infinity; let minY = Infinity; let maxX = -Infinity; let maxY = -Infinity;
    for (const s of items) {
        minX = Math.min(minX, s.x); minY = Math.min(minY, s.y);
        maxX = Math.max(maxX, s.x + s.w); maxY = Math.max(maxY, s.y + s.h);
    }
    minX = Math.min(minX, 0); minY = Math.min(minY, 0);
    maxX = Math.max(maxX, model.width); maxY = Math.max(maxY, model.height);
    return { minX, minY, w: maxX - minX, h: maxY - minY };
}

const { zoom, pan, canvas, bind, fit, zoomBy, observe, revealPoint, toContent } = useMapViewport({
    svgEl, contentBounds, canPan: () => !dragging.value, panFromChildren: false,
});

// ---- selection and editing ----

const selectedSpot = computed(() => model.spots.find((s) => s.key === selectedKey.value) || null);
const selectedLandmarkObj = computed(() => model.landmarks.find((l) => l.key === selectedLandmark.value) || null);
const namedCount = computed(() => model.spots.filter((s) => s.name).length);
// The names the AI read, the ones it was least sure of first. A spot it read no name on is not
// a name to check (it was listed first and buried the real ones).
const namesToCheck = computed(() => [...model.spots]
    .filter((s) => s.name)
    .sort((a, b) => (a.confidence ?? 1) - (b.confidence ?? 1) || String(a.label).localeCompare(String(b.label), undefined, { numeric: true })));
const countLine = computed(() => (props.t.count_line || ':spots / :names')
    .replace(':spots', String(model.spots.length)).replace(':names', String(namedCount.value)));

const areaCounts = computed(() => model.areas.map((a) => ({
    key: a.key, name: a.name, color: a.color, count: model.spots.filter((s) => s.area === a.key).length,
})));

const areaBoxes = computed(() => model.areas.map((a) => {
    const spots = model.spots.filter((s) => s.area === a.key);
    if (!spots.length) return null;
    const pad = 14;
    let minX = Infinity; let minY = Infinity; let maxX = -Infinity; let maxY = -Infinity;
    for (const s of spots) {
        minX = Math.min(minX, s.x); minY = Math.min(minY, s.y);
        maxX = Math.max(maxX, s.x + s.w); maxY = Math.max(maxY, s.y + s.h);
    }
    return { key: a.key, name: a.name, color: a.color, x: minX - pad, y: minY - pad - 10, w: maxX - minX + pad * 2, h: maxY - minY + pad * 2 + 10 };
}).filter(Boolean));

function snapshot() {
    history.value.push(JSON.stringify({ areas: model.areas, spots: model.spots, landmarks: model.landmarks, here: model.here, width: model.width, height: model.height }));
    if (history.value.length > 60) history.value.shift();
    dirty.value = true;
}

function undo() {
    const last = history.value.pop();
    if (!last) return;
    const state = JSON.parse(last);
    model.areas = state.areas; model.spots = state.spots; model.landmarks = state.landmarks; model.here = state.here;
    // A plan an area drag grew comes back to its size too.
    if (state.width) model.width = state.width;
    if (state.height) model.height = state.height;
    dirty.value = true;
}

function setField(obj, field, value) {
    if (obj[field] === value) return;
    snapshot();
    obj[field] = value;
}

function clampNum(v, min, max) {
    return Math.max(min, Math.min(max, Math.round(Number(v) || min)));
}

function select(s) {
    selectedLandmark.value = null;
    areaPanel.value = false;
    selectedKey.value = s.key;
}

function revealSpot(s) {
    revealPoint(s.x + s.w / 2, s.y + s.h / 2);
}

function nextLabel() {
    const nums = model.spots.map((s) => parseInt(s.label, 10)).filter((n) => !Number.isNaN(n));
    return String(nums.length ? Math.max(...nums) + 1 : 1);
}

function centre() {
    const p = toContent(
        (svgEl.value?.getBoundingClientRect().left ?? 0) + (svgEl.value?.getBoundingClientRect().width ?? 0) / 2,
        (svgEl.value?.getBoundingClientRect().top ?? 0) + (svgEl.value?.getBoundingClientRect().height ?? 0) / 2,
    );
    return { x: Math.max(0, Math.min(model.width - 60, p.x)), y: Math.max(0, Math.min(model.height - 40, p.y)) };
}

function addSpot(at = null) {
    snapshot();
    const c = at || centre();
    const s = { key: key(), label: nextLabel(), area: null, x: Math.round(c.x - 25), y: Math.round(c.y - 18), w: 50, h: 36, rotation: 0, name: '', category: null, name_locked: false };
    model.spots.push(s);
    select(s);
}

function addRow() {
    snapshot();
    const n = Math.max(1, Math.min(60, row.count || 1));
    const c = centre();
    const w = 50; const h = 36; const gap = 6;
    const total = n * w + (n - 1) * gap;
    const x0 = Math.max(0, Math.round(c.x - total / 2));
    const fromStart = row.from === 'start';
    // "From the right" in a right-to-left language reads in the order people read.
    const leftToRight = props.rtl ? !fromStart : fromStart;
    for (let i = 0; i < n; i++) {
        const idx = leftToRight ? i : n - 1 - i;
        model.spots.push({
            key: key(), label: String((row.start || 1) + i), area: row.area || null,
            x: x0 + idx * (w + gap), y: Math.round(c.y - h / 2), w, h, rotation: 0, name: '', category: null, name_locked: false,
        });
    }
    row.start = (row.start || 1) + n;
    rowDialog.value = false;
}

function duplicate(s) {
    snapshot();
    const copy = { ...s, key: key(), id: undefined, label: nextLabel(), x: s.x + s.w + 6, name: '', name_locked: false, category: null };
    model.spots.push(copy);
    select(copy);
}

function removeSpot(s) {
    snapshot();
    model.spots = model.spots.filter((x) => x.key !== s.key);
    selectedKey.value = null;
    showToast((props.t.spot_removed || '').replace(':number', s.label), true);
}

function clearName(s) {
    setField(s, 'name', '');
    showToast(props.t.name_cleared, true);
}

function addLandmark(kind) {
    snapshot();
    landmarkMenu.value = false;
    const c = centre();
    const l = { key: key(), kind, label: '', x: Math.round(c.x - 40), y: Math.round(c.y - 20), w: kind === 'text' ? 120 : 80, h: 40, rotation: 0 };
    model.landmarks.push(l);
    selectedKey.value = null;
    selectedLandmark.value = l.key;
}

function removeLandmark(l) {
    snapshot();
    model.landmarks = model.landmarks.filter((x) => x.key !== l.key);
    selectedLandmark.value = null;
    showToast(props.t.removed, true);
}

function newArea() {
    snapshot();
    const a = { key: key(), name: (props.t.area_default || 'Area :n').replace(':n', String(model.areas.length + 1)), color: AREA_COLORS[model.areas.length % AREA_COLORS.length] };
    model.areas.push(a);
    return a;
}

function setArea(s, value) {
    if (value === '__new') {
        const a = newArea();
        s.area = a.key;
        areaPanel.value = false;
        return;
    }
    setField(s, 'area', value || null);
}

function removeArea(a) {
    snapshot();
    model.areas = model.areas.filter((x) => x.key !== a.key);
    model.spots.forEach((s) => { if (s.area === a.key) s.area = null; });
}

function toggleNumbering() {
    numbering.value = !numbering.value;
    if (numbering.value) {
        snapshot();
        nextNumber.value = 1;
        selectedKey.value = null;
    }
}

// ---- pointer ----

function onSpotPress(evt, s) {
    if (numbering.value) {
        // Taken numbers move out of the way, so walking order never makes two "5"s.
        const clash = model.spots.find((x) => x !== s && x.label === String(nextNumber.value));
        if (clash) clash.label = s.label;
        s.label = String(nextNumber.value++);
        dirty.value = true;
        return;
    }
    if (moving.value) {
        const from = model.spots.find((x) => x.key === moving.value);
        if (from && from !== s) {
            snapshot();
            const { x, y } = from;
            from.x = s.x; from.y = s.y; s.x = x; s.y = y;
        }
        moving.value = null;
        return;
    }
    select(s);
    startDrag(evt, 'spot', s);
}

function onGroundPress(evt) {
    const p = toContent(evt.clientX, evt.clientY);
    if (placingHere.value) {
        snapshot();
        model.here = { x: Math.round(p.x), y: Math.round(p.y) };
        placingHere.value = false;
        return;
    }
    if (moving.value) {
        const from = model.spots.find((x) => x.key === moving.value);
        if (from) {
            snapshot();
            from.x = Math.round(p.x - from.w / 2); from.y = Math.round(p.y - from.h / 2);
        }
        moving.value = null;
        return;
    }
    selectedKey.value = null;
    selectedLandmark.value = null;
}

function onStagePress() {
    landmarkMenu.value = false;
}

function startDrag(evt, type, obj) {
    if (type === 'landmark') {
        selectedKey.value = null;
        selectedLandmark.value = obj.key;
    }
    const start = toContent(evt.clientX, evt.clientY);
    const origin = { x: obj.x, y: obj.y };
    let moved = false;
    dragging.value = { type, obj };
    evt.target.setPointerCapture?.(evt.pointerId);

    const move = (e) => {
        const p = toContent(e.clientX, e.clientY);
        const dx = p.x - start.x; const dy = p.y - start.y;
        if (!moved && Math.hypot(dx, dy) < 3 / zoom.value) return;
        if (!moved) { snapshot(); moved = true; }
        // A 5-unit grid keeps rows straight without a ruler.
        obj.x = Math.round((origin.x + dx) / 5) * 5;
        obj.y = Math.round((origin.y + dy) / 5) * 5;
    };
    const up = () => {
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
        window.removeEventListener('pointercancel', up);
        dragging.value = null;
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
    window.addEventListener('pointercancel', up);
}

/** Moves an area's block: its stalls, and the landmarks inside its box when the drag began. */
function startAreaDrag(evt, a) {
    const start = toContent(evt.clientX, evt.clientY);
    const inBox = (t) => {
        const cx = t.x + t.w / 2; const cy = t.y + t.h / 2;
        return cx >= a.x && cx <= a.x + a.w && cy >= a.y && cy <= a.y + a.h;
    };
    const items = [
        ...model.spots.filter((sp) => sp.area === a.key),
        ...model.landmarks.filter(inBox),
    ].map((o) => ({ o, x: o.x, y: o.y }));
    let moved = false;
    evt.target.setPointerCapture?.(evt.pointerId);

    const move = (e) => {
        const p = toContent(e.clientX, e.clientY);
        const dx = Math.round((p.x - start.x) / 5) * 5; const dy = Math.round((p.y - start.y) / 5) * 5;
        if (!moved && Math.hypot(dx, dy) < 3 / zoom.value) return;
        if (!moved) { snapshot(); moved = true; }
        // Not past the top or the left edge: the saved plan starts at 0.
        const minX = Math.min(...items.map((it) => it.x)); const minY = Math.min(...items.map((it) => it.y));
        const ddx = Math.max(dx, -minX); const ddy = Math.max(dy, -minY);
        items.forEach((it) => { it.o.x = it.x + ddx; it.o.y = it.y + ddy; });
        dirty.value = true;
    };
    const up = () => {
        window.removeEventListener('pointermove', move);
        window.removeEventListener('pointerup', up);
        window.removeEventListener('pointercancel', up);
        if (!moved) return;
        // The plan grows to hold a block dragged past its edge (up to the 4000 the map keeps).
        const right = Math.max(...model.spots.map((sp) => sp.x + sp.w), ...model.landmarks.map((l) => l.x + l.w), 0);
        const bottom = Math.max(...model.spots.map((sp) => sp.y + sp.h), ...model.landmarks.map((l) => l.y + l.h), 0);
        model.width = Math.min(4000, Math.max(model.width, right + 40));
        model.height = Math.min(4000, Math.max(model.height, bottom + 40));
    };
    window.addEventListener('pointermove', move);
    window.addEventListener('pointerup', up);
    window.addEventListener('pointercancel', up);
}

function onKey(evt) {
    if ((evt.ctrlKey || evt.metaKey) && evt.key.toLowerCase() === 'z') {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
        evt.preventDefault();
        undo();
    }
    if ((evt.ctrlKey || evt.metaKey) && evt.key.toLowerCase() === 's') {
        evt.preventDefault();
        save();
    }
}

// ---- drawing helpers ----

function spotStyle(s) {
    const area = s.area ? model.areas.find((a) => a.key === s.area) : null;
    if (!area) return null;
    return s.name ? { stroke: area.color, fill: area.color + '38' } : { stroke: area.color };
}

function spotInk(s) {
    const area = s.area ? model.areas.find((a) => a.key === s.area) : null;
    return area ? { fill: 'color-mix(in srgb, ' + area.color + ' 45%, rgb(var(--ap-ink)))' } : null;
}

const choice = (str, n) => choose(str, n);

function clip(name, w) {
    const max = Math.max(3, Math.floor(w / 6.2));
    return name.length > max ? name.slice(0, max - 1) + '…' : name;
}

function spotAria(s) {
    return props.strings.spot.replace(':number', s.label) + (s.name ? ', ' + s.name : '');
}

function showToast(text, withUndo = false) {
    clearTimeout(toastTimer);
    toast.value = { text, undo: withUndo };
    toastTimer = setTimeout(() => { toast.value = null; }, 6000);
}

// ---- saving ----

function payload() {
    const areaRef = (k) => {
        const a = model.areas.find((x) => x.key === k);
        if (!a) return {};
        return a.id ? { area_id: a.id } : { area_key: a.key };
    };
    return {
        revision: model.revision,
        width: model.width,
        height: model.height,
        areas: model.areas.map((a) => (a.id ? { id: a.id, name: a.name, color: a.color } : { key: a.key, name: a.name, color: a.color })),
        spots: model.spots.map((s) => ({
            ...(s.id ? { id: s.id } : {}),
            label: s.label, x: s.x, y: s.y, w: s.w, h: s.h, rotation: s.rotation || 0, shape: s.shape || null,
            ...areaRef(s.area),
            ...(s.name_locked ? {} : { name: s.name || '', category: s.category || null, ...(s.sells ? { sells: s.sells } : {}) }),
        })),
        landmarks: model.landmarks.map((l) => ({ ...(l.id ? { id: l.id } : {}), kind: l.kind, label: l.label, x: l.x, y: l.y, w: l.w, h: l.h, rotation: l.rotation || 0 })),
        here: model.here,
        ...(draftBackground.value && showBackground.value ? { background_from_draft: draftBackground.value } : {}),
        ...(removeBackground.value ? { clear_background: true } : {}),
        background_public: !removeBackground.value && pictureForShoppers.value,
    };
}

async function save({ fromReveal = false } = {}) {
    if (saving.value) return;
    saving.value = true;
    error.value = '';
    try {
        const res = await fetch(props.urls.save, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload()),
        });
        const data = await res.json().catch(() => ({}));
        if (res.status === 422 && data.errors?.revision) {
            clash.value = true;
            return;
        }
        if (!res.ok) {
            error.value = Object.values(data.errors || {})[0]?.[0] || props.t.save_failed;
            return;
        }
        load(data.structure);
        draftBackground.value = null;
        history.value = [];
        dirty.value = false;
        clash.value = false;
        reveal.done = false;
        showToast(data.renumbered ? (props.t.saved_renumbered || '').replace(':count', String(data.renumbered)) : props.t.saved_toast);
        if (fromReveal) {
            setTimeout(() => { window.location.href = props.urls.back; }, 900);
        }
    } catch (e) {
        error.value = props.t.save_failed;
    } finally {
        saving.value = false;
    }
}

/** Another editor saved meanwhile: take their revision number and send ours over it. */
async function saveAnyway() {
    try {
        const res = await fetch(props.urls.structure, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        model.revision = data.revision;
        clash.value = false;
        await save();
    } catch (e) {
        error.value = props.t.save_failed;
    }
}

function beforeUnload(evt) {
    if (dirty.value && !saving.value) {
        evt.preventDefault();
        evt.returnValue = '';
    }
}

onMounted(async () => {
    load(props.structure);
    observe();
    window.addEventListener('keydown', onKey);
    window.addEventListener('beforeunload', beforeUnload);
    if (props.draft) {
        try {
            const res = await fetch(props.urls.draft, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (data.status === 'ready' && data.result && data.draft === props.draft) {
                if (model.spots.length && !window.confirm(props.t.replace_with_draft)) return;
                loadDraft(data.result, data.images);
                await nextTick();
                fit();
            }
        } catch (e) { /* the plan as saved stays */ }
    }
    await nextTick();
    fit();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKey);
    window.removeEventListener('beforeunload', beforeUnload);
});

watch(() => props.structure, (s) => load(s));
</script>

<style scoped>
.vmd { display: flex; flex-direction: column; gap: 0.75rem; min-height: calc(100vh - 7rem); }
.vmd-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.vmd-back { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.875rem; font-weight: 500; color: var(--brand-blue); }
.vmd-tools { display: flex; gap: 0.25rem; flex-wrap: wrap; flex: 1; justify-content: center; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 0.875rem; padding: 0.25rem; }
.vmd-tool { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.7rem; border-radius: 0.625rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); transition: all 0.2s; }
.vmd-tool svg { width: 1.15rem; height: 1.15rem; }
.vmd-tool:hover:not(:disabled) { background: rgb(var(--ap-surface-hover)); color: rgb(var(--ap-ink)); }
.vmd-tool.is-on { background: rgb(var(--ap-surface-active)); box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.18); color: rgb(var(--ap-ink)); }
.vmd-tool:disabled { opacity: 0.4; }
.vmd-menu-wrap { position: relative; }
.vmd-menu { position: absolute; inset-inline-start: 0; top: calc(100% + 0.25rem); z-index: 20; min-width: 11rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 0.75rem; box-shadow: var(--ap-shadow-card); padding: 0.25rem; display: flex; flex-direction: column; }
.vmd-menu button { text-align: start; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; color: rgb(var(--ap-ink)); }
.vmd-menu button:hover { background: rgb(var(--ap-surface-hover)); }
.vmd-save, .vmd-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 600; color: #fff; background: var(--brand-button-bg); transition: all 0.2s; }
.vmd-save:hover:not(:disabled), .vmd-primary:hover:not(:disabled) { background: var(--brand-button-bg-hover); }
.vmd-save:disabled, .vmd-primary:disabled { opacity: 0.55; }
.vmd-ghost { padding: 0.55rem 0.85rem; border-radius: 0.625rem; border: 1px solid rgb(var(--ap-border-strong)); font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink)); background: rgb(var(--ap-surface)); transition: all 0.2s; }
.vmd-ghost:hover { background: rgb(var(--ap-surface-hover)); }
.vmd-danger { padding: 0.55rem 0.85rem; border-radius: 0.625rem; font-size: 0.875rem; font-weight: 500; color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.35); }
.vmd-spinner { width: 1rem; height: 1rem; border: 2px solid rgba(255, 255, 255, 0.4); border-top-color: #fff; border-radius: 999px; animation: vmd-spin 0.8s linear infinite; }
@keyframes vmd-spin { to { transform: rotate(360deg); } }

.vmd-found { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 1rem 1.25rem; border-radius: 1rem; background: linear-gradient(135deg, rgba(78, 129, 250, 0.12), rgba(14, 165, 233, 0.1)); border: 1px solid rgba(78, 129, 250, 0.3); animation: vmd-rise 0.5s ease backwards; }
.vmd-found-text { display: flex; flex-direction: column; gap: 0.15rem; color: rgb(var(--ap-ink)); }
.vmd-found-text strong { font-size: 1.125rem; }
.vmd-found-text span { font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
.vmd-found-actions { display: flex; gap: 0.5rem; }
@keyframes vmd-rise { from { opacity: 0; transform: translateY(8px); } }

.vmd-note { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.65rem 0.9rem; border-radius: 0.75rem; font-size: 0.875rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); color: rgb(var(--ap-ink)); }
.vmd-note.is-warn { background: #fffbeb; border-color: #fcd34d; color: #92400e; }
.vmd-note.is-error { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
:global(.dark) .vmd-note.is-warn { background: rgba(120, 53, 15, 0.25); border-color: rgba(252, 211, 77, 0.4); color: #fde68a; }
:global(.dark) .vmd-note.is-error { background: rgba(127, 29, 29, 0.25); border-color: rgba(252, 165, 165, 0.4); color: #fecaca; }

.vmd-body { display: grid; grid-template-columns: minmax(0, 1fr) 20rem; gap: 0.75rem; flex: 1; min-height: 32rem; }
.vmd-stage { position: relative; border-radius: 1rem; overflow: hidden; border: 1px solid rgb(var(--ap-border)); background: rgb(var(--ap-bg)); min-height: 28rem; }
.vmd-svg { width: 100%; height: 100%; min-height: 28rem; display: block; touch-action: none; user-select: none; }
.vmd-ground { fill: rgb(var(--ap-surface)); }
.vmd-gridline { stroke: rgb(var(--ap-border)); stroke-width: 0.5; opacity: 0.6; }
.vmd-area-label { font-size: 12px; font-weight: 600; }
.vmd-spot { cursor: grab; }
.vmd-spot-box { fill: rgb(var(--ap-surface)); stroke: rgb(var(--ap-ink-3)); stroke-width: 1.5; stroke-dasharray: 4 3; transition: stroke 0.15s; }
.vmd-spot-box.is-named { stroke-dasharray: none; fill: rgba(78, 129, 250, 0.16); }
.vmd-spot:hover .vmd-spot-box { stroke: var(--brand-blue); }
.vmd-spot.is-selected .vmd-spot-box { stroke: var(--brand-blue); stroke-width: 3; fill: rgba(78, 129, 250, 0.12); }
.vmd-spot.is-unsure .vmd-spot-box { stroke: #d97706; stroke-width: 2.5; }
.vmd-spot:focus-visible { outline: none; }
.vmd-spot:focus-visible .vmd-spot-box { stroke: var(--brand-blue); stroke-width: 3; }
.vmd-spot-num { font-size: 13px; font-weight: 700; fill: rgb(var(--ap-ink)); pointer-events: none; }
.vmd-spot-name { font-size: 9px; fill: rgb(var(--ap-ink-2)); pointer-events: none; }
.vmd-spot.is-revealing { animation: vmd-pop 0.45s cubic-bezier(0.2, 0.8, 0.2, 1.2) backwards; transform-box: fill-box; }
@keyframes vmd-pop { from { opacity: 0; } 60% { opacity: 1; } }
.vmd-landmark rect { fill: rgba(14, 165, 233, 0.1); stroke: #0ea5e9; stroke-width: 1.2; stroke-dasharray: 4 3; }
.vmd-landmark text { font-size: 11px; font-weight: 600; fill: #0369a1; pointer-events: none; }
:global(.dark) .vmd-landmark text { fill: #7dd3fc; }
.vmd-landmark { cursor: grab; }
.vmd-landmark.is-selected rect { stroke-width: 2.5; stroke-dasharray: none; }
.vmd-here circle { fill: rgba(239, 68, 68, 0.2); stroke: #ef4444; stroke-width: 2; cursor: grab; }
.vmd-here .vmd-here-dot { fill: #ef4444; stroke: none; }

.vmd-zoom { position: absolute; bottom: 0.75rem; inset-inline-end: 0.75rem; display: flex; gap: 0.25rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 0.75rem; padding: 0.2rem; box-shadow: var(--ap-shadow-card); }
.vmd-zoom button { min-width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; font-size: 1.1rem; color: rgb(var(--ap-ink)); }
.vmd-zoom button:hover { background: rgb(var(--ap-surface-hover)); }
.vmd-zoom .vmd-fit { font-size: 0.8rem; padding: 0 0.6rem; }
.vmd-bg { position: absolute; top: 0.75rem; inset-inline-start: 0.75rem; display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.7rem; border-radius: 0.75rem; background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); font-size: 0.8125rem; color: rgb(var(--ap-ink)); }

.vmd-panel { background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 1rem; padding: 1rem; overflow-y: auto; max-height: calc(100vh - 10rem); display: flex; flex-direction: column; gap: 0.85rem; }
.vmd-panel-head { display: flex; align-items: center; justify-content: space-between; }
.vmd-panel-head h2 { font-size: 1.05rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vmd-x { width: 2rem; height: 2rem; border-radius: 0.5rem; font-size: 1.25rem; line-height: 1; color: rgb(var(--ap-ink-3)); flex-shrink: 0; }
.vmd-x:hover { background: rgb(var(--ap-surface-hover)); color: rgb(var(--ap-ink)); }
.vmd-fields { display: flex; flex-direction: column; gap: 0.7rem; }
.vmd-field { display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); border: 0; padding: 0; margin: 0; }
.vmd-field input, .vmd-field select { padding: 0.55rem 0.7rem !important; border-radius: 0.6rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); font-size: 0.9375rem !important; }
.vmd-pair { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
.vmd-radio { display: flex; align-items: center; gap: 0.5rem; color: rgb(var(--ap-ink)); font-size: 0.875rem; }
.vmd-locked { font-size: 0.9375rem; color: rgb(var(--ap-ink)); }
.vmd-locked small { display: block; font-size: 0.75rem; color: rgb(var(--ap-ink-3)); }
.vmd-rotate { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); }
.vmd-rotate button { width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; border: 1px solid rgb(var(--ap-border-strong)); color: rgb(var(--ap-ink)); }
.vmd-rotate output { min-width: 3rem; text-align: center; color: rgb(var(--ap-ink)); }
.vmd-panel-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: auto; }
.vmd-hint { font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
.vmd-summary { display: flex; flex-direction: column; gap: 0.6rem; align-items: flex-start; }
.vmd-count { font-size: 1rem; font-weight: 600; color: rgb(var(--ap-ink)); }
.vmd-names, .vmd-areas { display: flex; flex-direction: column; gap: 0.4rem; }
.vmd-names li, .vmd-areas li { display: flex; align-items: center; gap: 0.5rem; padding: 0.3rem; border-radius: 0.6rem; }
.vmd-names li.is-unsure { background: #fffbeb; }
:global(.dark) .vmd-names li.is-unsure { background: rgba(120, 53, 15, 0.25); }
.vmd-names input, .vmd-areas input[type="text"] { flex: 1; min-width: 0; padding: 0.45rem 0.6rem !important; border-radius: 0.5rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); font-size: 0.9rem !important; }
.vmd-name-num { min-width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; font-weight: 700; background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); }
.vmd-tag { font-size: 0.7rem; font-weight: 600; color: #b45309; white-space: nowrap; }
:global(.dark) .vmd-tag { color: #fbbf24; }
.vmd-areas input[type="color"] { width: 2rem; height: 2rem; border: 0; padding: 0; background: none; }

.vmd-dialog { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.4); padding: 1rem; }
.vmd-dialog-card { width: 100%; max-width: 26rem; background: rgb(var(--ap-surface)); border-radius: 1.25rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.9rem; box-shadow: var(--ap-shadow-card); }
.vmd-dialog-card h2 { font-size: 1.125rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vmd-dialog-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }

.vmd-toast { position: fixed; bottom: 1.25rem; left: 50%; transform: translateX(-50%); z-index: 70; display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1rem; border-radius: 0.875rem; background: #111827; color: #fff; font-size: 0.875rem; box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5); animation: vmd-rise 0.25s ease backwards; }
.vmd-toast button { font-weight: 700; color: #93c5fd; }

@media (max-width: 1023px) {
    .vmd-body { grid-template-columns: 1fr; }
    .vmd-tools { justify-content: flex-start; order: 3; flex-basis: 100%; overflow-x: auto; flex-wrap: nowrap; }
    .vmd-tool span { white-space: nowrap; }
    .vmd-panel { position: fixed; inset-inline: 0; bottom: 0; z-index: 50; max-height: 60vh; border-radius: 1.25rem 1.25rem 0 0; transform: translateY(100%); transition: transform 0.25s ease; box-shadow: 0 -10px 30px -12px rgba(0, 0, 0, 0.35); }
    .vmd-panel.is-open { transform: none; }
    .vmd-stage, .vmd-svg { min-height: 60vh; }
}
@media (prefers-reduced-motion: reduce) {
    .vmd-spot.is-revealing, .vmd-found, .vmd-toast { animation: none; }
    .vmd-panel { transition: none; }
}
/* The design of 2026-11-23: one bar, a counted banner, and a panel that says what is named. */
.vmd-bar { background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 0.875rem; padding: 0.35rem 0.5rem 0.35rem 0.85rem; flex-wrap: nowrap; }
.vmd-back { padding-inline-end: 0.85rem; border-inline-end: 1px solid rgb(var(--ap-border)); white-space: nowrap; }
.vmd-tools { background: none; border: 0; padding: 0; justify-content: flex-start; flex-wrap: nowrap; overflow-x: auto; }
.vmd-tool { font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink)); white-space: nowrap; }
.vmd-tool:disabled { color: rgb(var(--ap-ink-3)); }
.vmd-status { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); white-space: nowrap; }
.vmd-status i { width: 0.45rem; height: 0.45rem; border-radius: 999px; background: #f59e0b; }
.vmd-found { background: rgb(var(--ap-surface)); border-color: rgb(var(--ap-border)); justify-content: flex-start; }
.vmd-found-text { flex: 1; min-width: 14rem; }
.vmd-found-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 2.6rem; height: 2.6rem; padding: 0 0.5rem; border-radius: 0.6rem; background: rgba(78, 129, 250, 0.12); color: var(--brand-blue); font-size: 1.15rem; font-weight: 700; }
.vmd-stats-top { display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem; }
.vmd-stats-top strong { font-size: 1rem; color: rgb(var(--ap-ink)); }
.vmd-stats-top span { font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); }
.vmd-stats { width: 100%; }
.vmd-meter { margin-top: 0.45rem; height: 0.35rem; border-radius: 999px; background: rgb(var(--ap-border)); overflow: hidden; }
.vmd-meter i { display: block; height: 100%; background: var(--brand-blue); border-radius: 999px; }
.vmd-panel-label { margin-top: 0.4rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgb(var(--ap-ink-3)); }
.vmd-area-list { width: 100%; display: flex; flex-direction: column; gap: 0.1rem; }
.vmd-area-list button { width: 100%; display: flex; align-items: center; gap: 0.6rem; padding: 0.35rem 0.25rem; border-radius: 0.5rem; font-size: 0.875rem; color: rgb(var(--ap-ink)); text-align: start; }
.vmd-area-list button:hover { background: rgb(var(--ap-surface-hover)); }
.vmd-area-list i { width: 0.65rem; height: 0.65rem; border-radius: 0.2rem; flex-shrink: 0; }
.vmd-area-list bdi { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.vmd-area-list b { font-weight: 500; color: rgb(var(--ap-ink-2)); }
.vmd-area-list .vmd-area-new { color: var(--brand-blue); font-weight: 600; font-size: 0.8125rem; }
.vmd-legend { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-2)); }
.vmd-legend li { display: flex; align-items: center; gap: 0.5rem; }
.vmd-legend i { width: 0.85rem; height: 0.85rem; border-radius: 0.2rem; border: 1.5px dashed rgb(var(--ap-ink-3)); }
.vmd-legend i.is-named { border: 1.5px solid var(--brand-blue); background: rgba(78, 129, 250, 0.2); }
.vmd-hint-foot { margin-top: auto; padding-top: 1rem; }
@media (min-width: 641px) { .vmd-tool svg { display: none; } }
.vmd-summary { flex: 1; }
@media (max-width: 640px) { .vmd-bar { flex-wrap: wrap; } .vmd-back { border: 0; } }
.vmd-area-handle { cursor: move; }
.vmd-area-handle > rect { fill: rgb(var(--ap-surface)); stroke-width: 1.2; }
.vmd-area-handle:hover > rect { stroke-width: 2; }
.vmd-bg-public { top: 3.4rem; }
.vmd-bg-remove { margin-inline-start: .5rem; font-size: .8125rem; color: rgb(var(--ap-ink-2)); text-decoration: underline; text-underline-offset: 2px; }
</style>
