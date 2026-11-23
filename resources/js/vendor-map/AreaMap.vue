<!--
  The visitors' map on a phone, in two levels. A market of seventy stalls does not fit a phone's
  width as numbers, so the phone first shows the market's AREAS where they are (the same sections
  VendorMapSchematic lays out), each a block with its name, its stall numbers, how many are here
  today and a pattern of dots for its rows; a tap opens that area alone, at a size a thumb can
  hit, scrolled with one finger and pinched or +/- to zoom, with chips to step to the next area.
  Choosing a stall from the list, the search or a shared link (?s=14) opens its area with the
  stall marked. A laptop and the printed sign keep the whole map (SchematicMap.vue).

  The frame is dir="ltr" whatever the page: it is a place, not a sentence. Words follow the page.
-->
<template>
  <div ref="root" class="vmo">
    <!-- The whole market: areas only. -->
    <template v-if="openKey === null">
      <p v-if="t.tapArea" class="vmo-hint" :dir="rtl ? 'rtl' : 'ltr'">{{ t.tapArea }}</p>
      <!-- The areas where they are, at their size: the market as its people know it. -->
      <div v-if="plan" class="vms vmo-plan" :class="{ 'has-picture': !!schematic.picture }" dir="ltr" :style="{ aspectRatio: plan.w + ' / ' + plan.h }">
        <!-- The owner's picture of the market, when they chose to show it, laid over the whole plan. -->
        <img v-if="schematic.picture && schematic.frame" class="vmo-picture" :src="schematic.picture" alt="" decoding="async"
             :style="pos([0, 0, schematic.frame[0], schematic.frame[1]], 0, 0, false)" />
        <span v-for="(pl, pi) in schematic.places || []" :key="'p' + pi" class="vmo-place" :class="'is-' + pl.kind" :style="pos(grown(pl.box, 58, 22, [...tileBoxes.values()]))">
          <bdi>{{ pl.label }}</bdi>
        </span>
        <AreaCard v-for="z in zones" :key="z.key" :z="z" :tile-style="tileStyle(z)" :tall="isTall(z)" />
        <span v-if="showHere && schematic.hereAt" class="vmo-here" :style="dot(schematic.hereAt)" :title="hereLabel" aria-hidden="true"></span>
      </div>
      <p v-if="plan && showHere && schematic.hereAt" class="vms-here vmo-here-legend" :dir="rtl ? 'rtl' : 'ltr'"><i aria-hidden="true"></i><b>{{ hereLabel }}</b><span v-if="schematic.entrance">· {{ schematic.entrance }}</span></p>
      <div v-else-if="!plan" class="vms vmo-board" dir="ltr">
        <template v-for="(section, si) in schematic.sections" :key="si">
          <template v-if="section.type === 'full'">
            <AreaCard :z="section.zone" />
          </template>
          <template v-else-if="section.type === 'stack'">
            <AreaCard v-for="(z, zi) in section.zones" :key="zi" :z="z" />
          </template>
          <div v-else class="vms-cols" :style="{ gridTemplateColumns: 'minmax(0, 1fr) minmax(0, 1fr)' }">
            <div class="vms-col"><AreaCard v-for="(z, zi) in section.a" :key="'a' + zi" :z="z" /></div>
            <div class="vms-col"><AreaCard v-for="(z, zi) in section.b" :key="'b' + zi" :z="z" /></div>
          </div>
        </template>
      </div>
      <div v-if="!plan && (schematic.amenities.length || (showHere && schematic.here))" class="vms-foot" :dir="rtl ? 'rtl' : 'ltr'">
        <p v-if="showHere && schematic.here" class="vms-here"><i aria-hidden="true"></i><b>{{ hereLabel }}</b><span v-if="schematic.entrance">· {{ schematic.entrance }}</span></p>
        <ul v-if="schematic.amenities.length" class="vms-amen">
          <li v-for="(a, ai) in schematic.amenities" :key="ai"><bdi>{{ a.label }}</bdi></li>
        </ul>
      </div>
    </template>

    <!-- One area, close up. -->
    <template v-else-if="openZone">
      <div class="vmo-bar" :dir="rtl ? 'rtl' : 'ltr'">
        <button type="button" class="vmo-back" @click="close">
          <svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" :style="rtl ? 'transform: scaleX(-1)' : null"><path d="M12.5 4.5L7 10l5.5 5.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
          {{ t.allAreas }}
        </button>
        <div class="vmo-zoom" role="group" :aria-label="t.map">
          <button type="button" :aria-label="t.zoomOut" :disabled="zoom <= ZMIN" @click="setZoom(zoom / 1.25)">&minus;</button>
          <button type="button" :aria-label="t.zoomIn" :disabled="zoom >= ZMAX" @click="setZoom(zoom * 1.25)">+</button>
        </div>
      </div>
      <div v-if="zones.length > 1" class="vmo-tabs" :dir="rtl ? 'rtl' : 'ltr'">
        <button v-for="z in zones" :key="z.key" type="button" class="vmo-tab" :class="{ 'is-on': z.key === openKey }"
                :style="{ '--zc': safeColor(z.color) }" :aria-pressed="z.key === openKey ? 'true' : 'false'" @click="open(z.key)">
          <i aria-hidden="true"></i><bdi>{{ z.name || z.range }}</bdi>
          <b v-if="matchCount(z)" class="vmo-badge">{{ matchCount(z) }}</b>
        </button>
      </div>
      <div ref="viewport" class="vmo-view" @touchstart="pinchStart" @touchmove="pinchMove" @touchend="pinchEnd" @touchcancel="pinchEnd">
        <div class="vmo-inner" :style="{ '--z': zoom, width: `max(100%, calc(${openZone.cols} * 3.5rem * ${zoom} + 2rem))` }">
          <SchematicMap :schematic="{ sections: [{ type: 'full', zone: openZone }], amenities: [], here: false }"
                        :selected-id="selectedId" :highlight-ids="highlightIds" :rtl="rtl"
                        :spot-label="spotLabel" :selectable="selectable" :pressable="pressable" @select="(id) => emit('select', id)" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { choose } from '../plural';
import { computed, defineComponent, h, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SchematicMap from './SchematicMap.vue';

const props = defineProps({
    schematic: { type: Object, required: true },
    selectedId: { type: [Number, String], default: null },
    highlightIds: { type: Array, default: null },
    showHere: { type: Boolean, default: false },
    hereLabel: { type: String, default: '' },
    rtl: { type: Boolean, default: false },
    spotLabel: { type: Function, default: (id) => String(id) },
    selectable: { type: Function, default: () => true },
    t: { type: Object, required: true },
    pressable: { type: Boolean, default: true },
    focusTick: { type: Number, default: 0 },
    // What the line under an area says (the vendor link counts free places, not who is here).
    footText: { type: Function, default: null },
});

const emit = defineEmits(['select']);

const safeColor = (c) => (/^#[0-9a-fA-F]{6}$/.test(c || '') ? c : '#64748B');

const choice = (str, n) => choose(str, n);

/** Every area, in reading order, with a key of its own (an area without a name has no id). */
const zones = computed(() => {
    const out = [];
    const add = (z) => out.push({ ...z, key: z.id != null ? 'a' + z.id : 'r' + z.range });
    (props.schematic.sections || []).forEach((sec) => {
        if (sec.type === 'full') add(sec.zone);
        else if (sec.type === 'stack') sec.zones.forEach(add);
        else { sec.a.forEach(add); sec.b.forEach(add); }
    });
    return out;
});
/** The plan's box: every area and landmark, with a margin. Null when the areas have no places. */
const plan = computed(() => {
    const boxes = zones.value.map((z) => z.box).filter(Boolean);
    if (!boxes.length || boxes.length !== zones.value.length) return null;
    const all = [...boxes, ...(props.schematic.places || []).map((p) => p.box)];
    const pad = 16;
    const x0 = Math.min(...all.map((b) => b[0])) - pad;
    const y0 = Math.min(...all.map((b) => b[1])) - pad;
    const x1 = Math.max(...all.map((b) => b[2])) + pad;
    const y1 = Math.max(...all.map((b) => b[3])) + pad;
    const w = Math.max(1, x1 - x0);
    const hgt = Math.max(1, y1 - y0);
    // A long, shallow market (two rows down a street) would be a sliver at a phone's width:
    // its areas are listed as blocks instead.
    if (hgt / w < 0.35) return null;
    return { x0, y0, w, h: hgt };
});
/** A box on the plan in percent, grown about its middle to a size its words fit in. */
const pos = (b, minW = 0, minH = 0, clamp = true) => {
    if (!plan.value) return null;
    let x = (b[0] - plan.value.x0) / plan.value.w * 100;
    let y = (b[1] - plan.value.y0) / plan.value.h * 100;
    let w = (b[2] - b[0]) / plan.value.w * 100;
    let hgt = (b[3] - b[1]) / plan.value.h * 100;
    if (w < minW) { x -= (minW - w) / 2; w = minW; }
    if (hgt < minH) { y -= (minH - hgt) / 2; hgt = minH; }
    if (clamp) {
        x = Math.min(Math.max(0, x), 100 - w);
        y = Math.min(Math.max(0, y), 100 - hgt);
    }
    return { left: x + '%', top: y + '%', width: w + '%', height: hgt + '%' };
};
/** The plan's width on screen, to size things in pixels. */
const planPx = ref(360);
const measure = () => { if (root.value) planPx.value = root.value.clientWidth || planPx.value; };
onMounted(() => { measure(); window.addEventListener('resize', measure, { passive: true }); });
onBeforeUnmount(() => window.removeEventListener('resize', measure));
const unitsFor = (px) => (plan.value ? px / Math.max(1, planPx.value) * plan.value.w : px);

/**
 * A box grown about its middle towards a size its words need, but only into free space: never
 * over another area. Small places do the same against the areas.
 */
function grown(box, minPx, minHpx, others = zones.value.map((z) => z.box).filter(Boolean)) {
    let [x0, y0, x1, y1] = box;
    const wantW = unitsFor(minPx);
    const wantH = unitsFor(minHpx);
    const gap = unitsFor(6);
    if (x1 - x0 < wantW) {
        const rows = others.filter((o) => o[1] < y1 && o[3] > y0);
        const left = Math.max(plan.value ? plan.value.x0 : -Infinity, ...rows.filter((o) => o[2] <= x0).map((o) => o[2] + gap));
        const right = Math.min(plan.value ? plan.value.x0 + plan.value.w : Infinity, ...rows.filter((o) => o[0] >= x1).map((o) => o[0] - gap));
        const grow = Math.min(wantW, right - left) - (x1 - x0);
        if (grow > 0) {
            x0 -= grow / 2; x1 += grow / 2;
            if (x0 < left) { x1 += left - x0; x0 = left; }
            if (x1 > right) { x0 -= x1 - right; x1 = right; }
        }
    }
    if (y1 - y0 < wantH) {
        const cols = others.filter((o) => o[0] < x1 && o[2] > x0);
        const top = Math.max(plan.value ? plan.value.y0 : -Infinity, ...cols.filter((o) => o[3] <= y0).map((o) => o[3] + gap));
        const bottom = Math.min(plan.value ? plan.value.y0 + plan.value.h : Infinity, ...cols.filter((o) => o[1] >= y1).map((o) => o[1] - gap));
        const grow = Math.min(wantH, bottom - top) - (y1 - y0);
        if (grow > 0) {
            y0 -= grow / 2; y1 += grow / 2;
            if (y0 < top) { y1 += top - y0; y0 = top; }
            if (y1 > bottom) { y0 -= y1 - bottom; y1 = bottom; }
        }
    }
    return [x0, y0, x1, y1];
}
/** Every area's tile, the narrowest grown first, each against the tiles already grown. */
const tileBoxes = computed(() => {
    const out = new Map();
    zones.value.forEach((z) => out.set(z.key, z.box));
    [...zones.value].sort((a, b) => (a.box[2] - a.box[0]) - (b.box[2] - b.box[0])).forEach((z) => {
        const others = [...zones.value.filter((o) => o.key !== z.key).map((o) => out.get(o.key)), ...(props.schematic.places || []).map((p) => p.box)];
        out.set(z.key, grown(z.box, 96, 72, others));
    });
    return out;
});
const tileBox = (z) => tileBoxes.value.get(z.key) || z.box;
const tileStyle = (z) => pos(tileBox(z));
/** A tall, thin area (a line of stalls) is named along its length. */
const isTall = (z) => {
    const b = tileBox(z);
    const wPx = (b[2] - b[0]) / (plan.value?.w || 1) * planPx.value;
    return wPx < 84 && (b[3] - b[1]) > (b[2] - b[0]) * 1.8;
};

const dot = (p) => (plan.value ? { left: ((p[0] - plan.value.x0) / plan.value.w * 100) + '%', top: ((p[1] - plan.value.y0) / plan.value.h * 100) + '%' } : null);

const keyOf = (z) => (z.id != null ? 'a' + z.id : 'r' + z.range);

const openKey = ref(null);
const openZone = computed(() => zones.value.find((z) => z.key === openKey.value) || null);

const ZMIN = 0.7;
const ZMAX = 2.2;
const zoom = ref(1);
const viewport = ref(null);
const root = ref(null);

function setZoom(z) {
    zoom.value = Math.min(ZMAX, Math.max(ZMIN, Math.round(z * 100) / 100));
}

function open(key) {
    openKey.value = key;
    // Start with the whole width of the area in view when it fits at a readable size; pinch for more.
    const z = zones.value.find((x) => x.key === key);
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
    const room = (root.value?.clientWidth || 360) - 1.5 * rem - 2;
    if (z) zoom.value = Math.min(1, Math.max(ZMIN, room / (z.cols * 3.5 * rem + 2 * rem)));
    nextTick(showSelected);
}

function close() {
    openKey.value = null;
    zoom.value = 1;
}

/** The marked stall in view, without moving the page. */
function showSelected() {
    const el = viewport.value?.querySelector('.vms-c.is-on');
    if (!el || !viewport.value) return;
    const v = viewport.value.getBoundingClientRect();
    const r = el.getBoundingClientRect();
    viewport.value.scrollLeft += (r.left + r.width / 2) - (v.left + v.width / 2);
    viewport.value.scrollTop += (r.top + r.height / 2) - (v.top + v.height / 2);
}

const zoneOf = (id) => zones.value.find((z) => z.cells.some((c) => c && String(c.id) === String(id)));

// A stall chosen anywhere (the list, the search, ?s=14) opens its area; a tap on the same stall
// again (after "All areas") does too.
function showChosen(id) {
    if (id == null) return;
    const z = zoneOf(id);
    if (!z) return;
    // Already open: keep the zoom, just bring the stall into view.
    if (z.key === openKey.value) nextTick(showSelected);
    else open(z.key);
}
watch(() => props.selectedId, showChosen, { immediate: true });
watch(() => props.focusTick, () => showChosen(props.selectedId));

const matchCount = (z) => (Array.isArray(props.highlightIds)
    ? z.cells.filter((c) => c && props.highlightIds.some((id) => String(id) === String(c.id))).length
    : 0);

/* Two fingers zoom; one finger scrolls as the browser does. */
let pinch = null;
const dist = (e) => Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
function pinchStart(e) {
    if (e.touches.length === 2) pinch = { d: dist(e), z: zoom.value };
}
function pinchMove(e) {
    if (!pinch || e.touches.length !== 2) return;
    e.preventDefault();
    // Zoom about the point between the fingers: what is under them stays under them.
    const v = viewport.value;
    const rect = v.getBoundingClientRect();
    const mx = (e.touches[0].clientX + e.touches[1].clientX) / 2 - rect.left;
    const my = (e.touches[0].clientY + e.touches[1].clientY) / 2 - rect.top;
    const before = zoom.value;
    setZoom(pinch.z * dist(e) / Math.max(1, pinch.d));
    const k = zoom.value / before;
    if (k !== 1) nextTick(() => { v.scrollLeft = (v.scrollLeft + mx) * k - mx; v.scrollTop = (v.scrollTop + my) * k - my; });
}
function pinchEnd(e) {
    if (e.touches.length < 2) pinch = null;
}

/** An area on the overview: its name, numbers, who is here, and its rows as dots. */
const AreaCard = defineComponent({
    props: { z: { type: Object, required: true }, tileStyle: { type: Object, default: null }, tall: { type: Boolean, default: false } },
    setup(p) {
        return () => {
            const z = p.z;
            const cells = z.cells.filter(Boolean);
            const here = cells.filter((c) => c.state === 'present').length;
            const found = matchCount(z);
            const dim = Array.isArray(props.highlightIds) && !found;
            const marked = props.selectedId != null && cells.some((c) => String(c.id) === String(props.selectedId));
            const foot = props.footText ? props.footText(z) : choice(props.t.areaHere, here);
            const label = [z.name, z.range, foot].filter(Boolean).join(', ');
            return h('button', {
                type: 'button',
                class: ['vmo-card', { 'is-dim': dim, 'is-marked': marked, 'vmo-tile': !!p.tileStyle, 'is-tall': p.tall }],
                style: { '--zc': safeColor(z.color), ...(p.tileStyle || {}) },
                'aria-label': label,
                onClick: () => open(keyOf(z)),
            }, [
                h('span', { class: 'vmo-card-head', dir: props.rtl ? 'rtl' : 'ltr' }, [
                    z.name ? h('b', [h('bdi', z.name)]) : null,
                    z.range ? h('span', { class: 'vms-range' }, z.range) : null,
                ]),
                h('span', { class: 'vmo-dots', style: { gridTemplateColumns: `repeat(${z.cols}, 0.5rem)` }, 'aria-hidden': 'true' },
                    z.cells.map((c, i) => h('i', { key: i, class: c ? ['is-' + c.state, {
                        'is-hit': Array.isArray(props.highlightIds) && props.highlightIds.some((id) => String(id) === String(c.id)),
                        'is-on': props.selectedId != null && String(props.selectedId) === String(c.id),
                    }] : 'is-gap' }))),
                h('span', { class: 'vmo-card-foot', dir: props.rtl ? 'rtl' : 'ltr' }, [
                    h('span', foot),
                    found ? h('b', { class: 'vmo-badge' }, String(found)) : null,
                    h('svg', { viewBox: '0 0 20 20', width: 16, height: 16, 'aria-hidden': 'true', class: 'vmo-go', style: props.rtl ? 'transform: scaleX(-1)' : null },
                        [h('path', { d: 'M7.5 4.5L13 10l-5.5 5.5', fill: 'none', stroke: 'currentColor', 'stroke-width': 2, 'stroke-linecap': 'round', 'stroke-linejoin': 'round' })]),
                ]),
            ]);
        };
    },
});
</script>
