<!--
  MapCanvas: the vendor map's drawing, shared by the visitors' page, the organizer's tab and the
  designer. Presentational: it draws what it is given and says what was pressed; it never fetches,
  saves or decides anything.

  Props
    width, height      Number   The map's frame, in map units (vendor_maps.width / height).
    spots              Array    [{ id, label, area, x, y, w, h, rotation, shape, state, vendor }]
                                x, y = the spot's top-left corner, w, h its size, rotation in degrees
                                about its centre. shape (optional) = [[x, y], ...] absolute map
                                units, drawn instead of the rectangle (rotation is then not applied;
                                x/y/w/h still place the number). state = 'present' | 'absent' |
                                'free' | 'pending'. vendor = { name, categories: [slug], photo, ... }
                                or null.
    areas              Array    [{ id, name, color }]: a spot is tinted by its area's colour, and an
                                area with spots has its name drawn over them.
    landmarks          Array    [{ kind: 'entrance'|'restroom'|'parking'|'stage'|'text', label,
                                x, y, w, h, rotation }]
    here               Object   { x, y } in map units, or null: the "You are here" marker.
    selectedId         Number|String|null   The spot drawn as picked. Changing it pulses that
                                spot once and brings it into view.
    highlightIds       Array|null  When an array, spots NOT in it are dimmed (a search, a filter).
    showNames          Boolean  Draw vendors' names where they fit (measured, cut with an
                                ellipsis). Default true.
    categoryIcons      Boolean  Draw a small category icon where there is room. Default true.
    rtl                Boolean  The page reads right to left. The drawing never mirrors; only the
                                zoom buttons move to the other side and the labels isolate.
    labels             Object   Words, all optional: { map, spot (':number' in it), absent, here,
                                zoomIn, zoomOut, fit, landmarks: { entrance, restroom, parking,
                                stage } }.
    spotLabel          Function (spot) => string, the spoken name of a spot. Default: "Stall 14,
                                Avi's Vegetables, Eggs and dairy" built from labels.spot, the
                                vendor's name and categoryLabels.
    categoryLabels     Object   { slug: label } for the default spot label.
    selectable         Function (spot) => boolean, which spots are buttons. Default: every spot.
                                The visitors' map passes "has a vendor": an empty spot is not a
                                thing a shopper can open, and seventy tab stops of nothing is noise.
    controls           Boolean  Draw the -/+/Fit buttons. Default true.
    wheelNeedsModifier Boolean  Only zoom on the wheel with Ctrl/Cmd held, for a map inside a
                                long page. Default false.

  Events
    select(spotId)     A selectable spot was pressed (click, tap, Enter or Space).
    deselect()         The ground was pressed (a tap, not the end of a drag).

  Exposed (template ref)
    focusSpot(id, { zoom = true })  Zoom to a spot and its neighbours, and pulse it once.
    fit()                            Frame the whole map.

  Zoom and pan are seat-map-viewport.js, imported unchanged, as the seating picker uses it.
-->
<template>
  <div class="vmc" :class="{ 'is-rtl': rtl }">
    <svg ref="svgEl" class="vmc-svg" v-bind="bind" :viewBox="viewBox" role="group" :aria-label="labels.map || null" @click="onGroundClick">
      <defs>
        <pattern :id="hatchId" width="7" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
          <line x1="0" y1="0" x2="0" y2="7" class="vmc-hatch-line" />
        </pattern>
      </defs>
      <!-- Where names are measured: at the root, so its units are the canvas's pixels. -->
      <text ref="measureEl" class="vmc-measure" x="-9999" y="-9999" font-size="10" aria-hidden="true"></text>

      <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
        <rect class="vmc-frame" :x="0" :y="0" :width="width" :height="height" :rx="12 / z" data-ground="1" />

        <g v-for="lm in drawnLandmarks" :key="lm.key" :transform="lm.transform" aria-hidden="true">
          <rect v-if="lm.box" class="vmc-lm-box" :x="lm.x" :y="lm.y" :width="lm.w" :height="lm.h" :rx="6 / z" data-ground="1" />
          <g v-if="lm.icon" :transform="`translate(${lm.iconX} ${lm.iconY}) scale(${lm.iconScale})`">
            <path v-for="(d, i) in lm.icon" :key="i" :d="d" class="vmc-lm-icon" :stroke-width="1.6" />
          </g>
          <text v-if="lm.text" :class="lm.box ? 'vmc-lm-label' : 'vmc-lm-text'" :x="lm.textX" :y="lm.textY" :font-size="lm.fontSize" text-anchor="middle" dominant-baseline="central">{{ lm.text }}</text>
        </g>

        <text v-for="a in areaNames" :key="'a' + a.id" class="vmc-area-name" :x="a.x" :y="a.y" :font-size="11 / z" :stroke-width="3 / z" text-anchor="middle" aria-hidden="true">{{ a.name }}</text>

        <g v-for="s in drawn" :key="s.id"
           class="vmc-spot"
           :class="[ 'is-' + s.state, { 'is-button': s.button, 'is-selected': s.selected, 'is-dim': s.dim } ]"
           :style="{ '--area': s.color }"
           :transform="s.transform"
           :role="s.button ? 'button' : null"
           :tabindex="s.button ? 0 : null"
           :aria-label="s.button ? s.aria : null"
           :aria-current="s.button && s.selected ? 'true' : null"
           :aria-hidden="s.button ? null : 'true'"
           :data-spot="s.id"
           @click.stop="pick(s)"
           @keydown.enter.prevent="pick(s)"
           @keydown.space.prevent="pick(s)"
           @focus="revealSpot(s)">
          <path v-if="s.poly" class="vmc-box" :d="s.poly" />
          <rect v-else class="vmc-box" :x="s.x" :y="s.y" :width="s.w" :height="s.h" :rx="s.rx" />
          <template v-if="s.state === 'absent'">
            <path v-if="s.poly" class="vmc-hatch" :d="s.poly" :fill="`url(#${hatchId})`" />
            <rect v-else class="vmc-hatch" :x="s.x" :y="s.y" :width="s.w" :height="s.h" :rx="s.rx" :fill="`url(#${hatchId})`" />
          </template>
          <g :transform="s.textTransform">
            <g v-if="s.icon" :transform="`translate(${s.iconX} ${s.iconY}) scale(${s.iconScale})`">
              <path v-for="(d, i) in s.icon" :key="i" :d="d" class="vmc-icon" :stroke-width="1.8" />
            </g>
            <text class="vmc-num" :x="s.cx" :y="s.numY" :font-size="s.numSize" text-anchor="middle" dominant-baseline="central" direction="ltr">{{ s.label }}</text>
            <text v-if="s.name" class="vmc-name" :x="s.cx" :y="s.nameY" :font-size="s.nameSize" text-anchor="middle" dominant-baseline="central">{{ s.name }}</text>
          </g>
        </g>

        <!-- The pulse: once, over the spot, keyed so a second pick of the same spot pulses again. -->
        <g v-if="pulse" :key="pulse.key" :transform="pulse.transform" aria-hidden="true">
          <rect class="vmc-pulse" :x="pulse.x" :y="pulse.y" :width="pulse.w" :height="pulse.h" :rx="pulse.rx" />
        </g>

        <g v-if="hereMark" aria-hidden="true">
          <circle class="vmc-here-halo" :cx="here.x" :cy="here.y" :r="18 / z" />
          <circle class="vmc-here-dot" :cx="here.x" :cy="here.y" :r="7 / z" />
          <template v-if="hereMark.text">
            <rect class="vmc-here-pill" :x="hereMark.pillX" :y="hereMark.pillY" :width="hereMark.pillW" :height="hereMark.pillH" :rx="hereMark.pillH / 2" />
            <text class="vmc-here-text" :x="here.x" :y="hereMark.pillY + hereMark.pillH / 2" :font-size="11 / z" text-anchor="middle" dominant-baseline="central">{{ hereMark.text }}</text>
          </template>
        </g>
      </g>
    </svg>

    <div v-if="controls" class="vmc-tools">
      <button type="button" class="vmc-tool" :aria-label="labels.zoomIn || '+'" @click="zoomBy(0.25)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
      </button>
      <button type="button" class="vmc-tool" :aria-label="labels.zoomOut || '-'" @click="zoomBy(-0.25)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14" /></svg>
      </button>
      <button type="button" class="vmc-tool" :aria-label="labels.fit || 'Fit'" :title="labels.fit || null" @click="fit()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 4H4v5M15 4h5v5M9 20H4v-5M15 20h5v-5" /></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useMapViewport } from '../seat-map-viewport';
import { categoryIcon, landmarkIcon } from './MapCanvasIcons';
import './MapCanvas.css';

const props = defineProps({
    width: { type: Number, default: 1000 },
    height: { type: Number, default: 700 },
    spots: { type: Array, default: () => [] },
    areas: { type: Array, default: () => [] },
    landmarks: { type: Array, default: () => [] },
    here: { type: Object, default: null },
    selectedId: { type: [Number, String], default: null },
    highlightIds: { type: Array, default: null },
    showNames: { type: Boolean, default: true },
    categoryIcons: { type: Boolean, default: true },
    rtl: { type: Boolean, default: false },
    labels: { type: Object, default: () => ({}) },
    spotLabel: { type: Function, default: null },
    categoryLabels: { type: Object, default: () => ({}) },
    selectable: { type: Function, default: null },
    controls: { type: Boolean, default: true },
    wheelNeedsModifier: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'deselect']);

const svgEl = ref(null);
const measureEl = ref(null);
const hatchId = `vmc-hatch-${Math.random().toString(36).slice(2, 8)}`;

/** Everything drawn, in map units: the spots, the landmarks and the marker, never less than the spots. */
function contentBounds() {
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
    const add = (x, y, w = 0, h = 0) => {
        if (!Number.isFinite(x) || !Number.isFinite(y)) return;
        minX = Math.min(minX, x); minY = Math.min(minY, y);
        maxX = Math.max(maxX, x + w); maxY = Math.max(maxY, y + h);
    };
    props.spots.forEach((s) => add(+s.x, +s.y, +s.w || 0, +s.h || 0));
    props.landmarks.forEach((l) => add(+l.x, +l.y, +l.w || 0, +l.h || 0));
    if (props.here) add(+props.here.x - 30, +props.here.y - 40, 60, 60);
    if (!Number.isFinite(minX)) {
        return { minX: 0, minY: 0, w: props.width || 1000, h: props.height || 700 };
    }
    const pad = 24;
    return { minX: minX - pad, minY: minY - pad - 14, w: Math.max(1, maxX - minX + pad * 2), h: Math.max(1, maxY - minY + pad * 2 + 14) };
}

const { zoom, pan, canvas, bind, fit, fitTo, zoomBy, observe, revealPoint } = useMapViewport({
    svgEl,
    contentBounds,
    wheelNeedsModifier: props.wheelNeedsModifier,
});

const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);

/**
 * The zoom the labels are laid out for. It follows the live zoom a moment after it settles, so a
 * pinch scales the drawing smoothly and the names are measured once, not on every frame.
 */
const z = ref(1);
let settleTimer = null;
watch(zoom, (value) => {
    clearTimeout(settleTimer);
    settleTimer = setTimeout(() => { z.value = value || 1; }, 90);
}, { immediate: true });

// Names are measured in the page's own font, so the measuring element has to exist and the font
// has to have arrived. Both bump this, and the cache is thrown away with it.
const measureTick = ref(0);
const widths = new Map();

function textWidth(text) {
    if (widths.has(text)) return widths.get(text);
    const el = measureEl.value;
    let w = text.length * 5.6;
    if (el && typeof el.getComputedTextLength === 'function') {
        el.textContent = text;
        try { w = el.getComputedTextLength(); } catch (e) { /* not laid out: keep the estimate */ }
    }
    widths.set(text, w);
    return w;
}

/** The name as it fits in maxW screen pixels at fontPx, cut with an ellipsis, or null. */
function fitText(text, maxW, fontPx) {
    if (!text || maxW <= 0) return null;
    const scale = fontPx / 10;
    if (textWidth(text) * scale <= maxW) return text;
    const chars = Array.from(text);
    let lo = 2, hi = chars.length - 1, best = null;
    while (lo <= hi) {
        const mid = (lo + hi) >> 1;
        const candidate = chars.slice(0, mid).join('').trimEnd() + '…';
        if (textWidth(candidate) * scale <= maxW) { best = candidate; lo = mid + 1; } else { hi = mid - 1; }
    }
    return best;
}

const areaById = computed(() => {
    const out = {};
    props.areas.forEach((a) => { out[a.id] = a; });
    return out;
});

const highlightSet = computed(() => (Array.isArray(props.highlightIds) ? new Set(props.highlightIds.map(String)) : null));

function defaultAria(s) {
    const parts = [(props.labels.spot || ':number').replace(':number', s.label)];
    if (s.vendor && s.vendor.name) parts.push(s.vendor.name);
    const cats = (s.vendor?.categories || []).map((c) => props.categoryLabels[c]).filter(Boolean);
    if (cats.length) parts.push(cats.join(', '));
    if (s.vendor?.sells) parts.push(s.vendor.sells);
    if (s.state === 'absent' && props.labels.absent) parts.push(props.labels.absent);
    return parts.join(', ');
}

/**
 * How a spot's words are turned against the spot. Never upside down (a spot turned past a quarter
 * has its words turned back), and a spot standing on its side has them level, across its width:
 * a number read sideways is a number misread.
 */
function textTurn(rotation) {
    const r = ((Number(rotation) || 0) % 360 + 540) % 360 - 180;
    if (Math.abs(r) > 45 && Math.abs(r) < 135) return { turn: -r, across: true };
    return { turn: Math.abs(r) > 90 ? 180 : 0, across: false };
}

const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));

const drawn = computed(() => {
    measureTick.value; // eslint-disable-line no-unused-expressions
    const zz = z.value || 1;
    const out = props.spots.map((s) => {
        const x = +s.x || 0, y = +s.y || 0, w = Math.max(1, +s.w || 1), h = Math.max(1, +s.h || 1);
        const cx = x + w / 2, cy = y + h / 2;
        const rotation = Number(s.rotation) || 0;
        const poly = Array.isArray(s.shape) && s.shape.length >= 3
            ? 'M' + s.shape.map((p) => `${+p[0]} ${+p[1]}`).join('L') + 'Z'
            : null;
        const area = areaById.value[s.area];
        const vendor = s.vendor || null;
        const button = props.selectable ? !!props.selectable(s) : true;

        // Laid out in screen pixels, then turned back into map units. A spot on its side is
        // laid out across: its height is the room a word has.
        const { turn, across } = poly ? { turn: 0, across: false } : textTurn(rotation);
        const sw = (across ? h : w) * zz, sh = (across ? w : h) * zz;
        const numPx = clamp(Math.min(sw, sh) * 0.42, 8, 13);
        const namePx = 11;
        const iconPx = 13;
        const gap = 2;
        let name = null;
        if (props.showNames && vendor && vendor.name && sw >= 34 && sh >= numPx + namePx + gap + 6) {
            name = fitText(String(vendor.name), sw - 8, namePx);
            if (name && Array.from(name).length < 4) name = null;
        }
        let icon = null;
        const cat = vendor?.categories?.[0];
        if (props.categoryIcons && cat && sw >= 26) {
            const need = numPx + iconPx + gap + 6 + (name ? namePx + gap : 0);
            if (sh >= need) icon = categoryIcon(cat);
        }

        const block = (icon ? iconPx + gap : 0) + numPx + (name ? gap + namePx : 0);
        let top = cy * zz - block / 2;
        const iconY = icon ? top : 0;
        if (icon) top += iconPx + gap;
        const numY = top + numPx / 2;
        top += numPx;
        const nameY = name ? top + gap + namePx / 2 : 0;

        return {
            id: s.id,
            label: s.label,
            state: s.state || 'free',
            x, y, w, h, cx, cy, poly,
            rx: Math.min(6, w / 4, h / 4),
            color: area?.color || null,
            transform: poly || !rotation ? null : `rotate(${rotation} ${cx} ${cy})`,
            textTransform: turn ? `rotate(${turn} ${cx} ${cy})` : null,
            numSize: numPx / zz,
            numY: numY / zz,
            name,
            nameSize: namePx / zz,
            nameY: nameY / zz,
            icon,
            iconScale: iconPx / 24 / zz,
            iconX: cx - iconPx / 2 / zz,
            iconY: iconY / zz,
            button,
            selected: props.selectedId != null && String(props.selectedId) === String(s.id),
            dim: highlightSet.value ? !highlightSet.value.has(String(s.id)) : false,
            aria: props.spotLabel ? props.spotLabel(s) : defaultAria(s),
        };
    });

    // In the order of their numbers, so Tab walks the map the way its signs read.
    return out.sort((a, b) => String(a.label).localeCompare(String(b.label), undefined, { numeric: true, sensitivity: 'base' }));
});

const drawnLandmarks = computed(() => {
    const zz = z.value || 1;
    return props.landmarks.map((l, i) => {
        const x = +l.x || 0, y = +l.y || 0, w = Math.max(1, +l.w || 60), h = Math.max(1, +l.h || 40);
        const cx = x + w / 2, cy = y + h / 2;
        const isText = l.kind === 'text';
        const icon = isText ? null : landmarkIcon(l.kind);
        const word = (l.label || '').trim() || (isText ? '' : (props.labels.landmarks?.[l.kind] || ''));
        const iconPx = clamp(Math.min(w, h) * zz * 0.45, 12, 20);
        const fontPx = isText ? 12 : 10;
        const roomForWord = isText || (h * zz >= iconPx + fontPx + 8 && w * zz >= 40);
        const blockPx = (icon && icon.length ? iconPx : 0) + (roomForWord && word ? fontPx + 3 : 0);
        const top = cy * zz - blockPx / 2;
        const text = roomForWord && word ? fitText(word, Math.max(w * zz - 6, isText ? 200 : 0), fontPx) : null;

        return {
            key: i,
            box: !isText,
            x, y, w, h,
            transform: l.rotation ? `rotate(${+l.rotation} ${cx} ${cy})` : null,
            icon: icon && icon.length ? icon : null,
            iconScale: iconPx / 24 / zz,
            iconX: cx - iconPx / 2 / zz,
            iconY: top / zz,
            text,
            fontSize: fontPx / zz,
            textX: cx,
            textY: (icon && icon.length ? top + iconPx + 3 + fontPx / 2 : cy * zz) / zz,
        };
    });
});

/** Each area's name, over the spots it holds, where there is more than one area to tell apart. */
const areaNames = computed(() => {
    if (props.areas.length < 2) return [];
    const zz = z.value || 1;
    const boxes = {};
    props.spots.forEach((s) => {
        if (s.area == null) return;
        const b = boxes[s.area] || (boxes[s.area] = { minX: Infinity, minY: Infinity, maxX: -Infinity });
        b.minX = Math.min(b.minX, +s.x); b.maxX = Math.max(b.maxX, +s.x + (+s.w || 0)); b.minY = Math.min(b.minY, +s.y);
    });
    return props.areas
        .filter((a) => boxes[a.id] && a.name)
        .map((a) => ({ id: a.id, name: a.name, x: (boxes[a.id].minX + boxes[a.id].maxX) / 2, y: boxes[a.id].minY - 7 / zz }));
});

const hereMark = computed(() => {
    if (!props.here || !Number.isFinite(+props.here.x)) return null;
    const zz = z.value || 1;
    const text = props.labels.here || '';
    if (!text) return { text: null };
    const wPx = textWidth(text) * 1.1 + 16;
    const hPx = 20;
    return {
        text,
        pillW: wPx / zz,
        pillH: hPx / zz,
        pillX: +props.here.x - wPx / 2 / zz,
        pillY: +props.here.y - (hPx + 14) / zz,
    };
});

const pulse = ref(null);
let pulseCount = 0;

function pulseSpot(id) {
    const s = props.spots.find((x) => String(x.id) === String(id));
    if (!s) { pulse.value = null; return; }
    const x = +s.x, y = +s.y, w = +s.w, h = +s.h;
    pulseCount += 1;
    pulse.value = {
        key: pulseCount,
        x, y, w, h,
        rx: Math.min(6, w / 4, h / 4),
        transform: s.rotation && !s.shape ? `rotate(${+s.rotation} ${x + w / 2} ${y + h / 2})` : null,
    };
}

function revealSpot(s) {
    revealPoint(s.cx, s.cy, 56);
}

function focusSpot(id, { zoom: closer = true } = {}) {
    const s = props.spots.find((x) => String(x.id) === String(id));
    if (!s) return;
    const x = +s.x, y = +s.y, w = +s.w || 1, h = +s.h || 1;
    if (closer) {
        // The stall and a few of its neighbours, so a shopper sees where it is, not only what.
        const side = Math.max(w, h) * 5;
        fitTo({ minX: x + w / 2 - side / 2, minY: y + h / 2 - side / 2, w: side, h: side });
    } else {
        revealPoint(x + w / 2, y + h / 2, 56);
    }
    pulseSpot(id);
}

function pick(s) {
    if (!s.button) return;
    emit('select', s.id);
}

function onGroundClick(evt) {
    // A click on the svg itself, the frame or a landmark: the ground. A drag that panned the map
    // ends in no click (the viewport takes the pointer once a press moves).
    const target = evt.target;
    if (target === svgEl.value || target?.dataset?.ground) emit('deselect');
}

watch(() => props.selectedId, (id, before) => {
    if (id == null) { pulse.value = null; return; }
    if (String(id) === String(before)) return;
    pulseSpot(id);
    const s = props.spots.find((x) => String(x.id) === String(id));
    if (s) nextTick(() => revealPoint(+s.x + (+s.w) / 2, +s.y + (+s.h) / 2, 56));
});

let fontsDone = false;
/*
 * The shared viewport refits when the box's HEIGHT settles (a seat map's height follows its
 * content). Here the box is laid out by the page, and on a phone or beside a side panel it is the
 * WIDTH that settles a frame late: fitted to the first width, the map's edge was cut off. So a
 * change of width refits too, until the visitor has moved the map themselves (fit's auto mode).
 */
let widthWatch = null;
let lastWidth = 0;
onMounted(() => {
    observe();
    if (typeof ResizeObserver !== 'undefined' && svgEl.value) {
        widthWatch = new ResizeObserver((entries) => {
            const w = Math.round(entries[0]?.contentRect?.width || 0);
            if (w && lastWidth && w !== lastWidth) fit({ auto: true });
            lastWidth = w;
        });
        widthWatch.observe(svgEl.value);
    }
    measureTick.value += 1;
    nextTick(() => fit());
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            if (fontsDone) return;
            fontsDone = true;
            widths.clear();
            measureTick.value += 1;
        }).catch(() => {});
    }
});

onBeforeUnmount(() => {
    clearTimeout(settleTimer);
    widthWatch?.disconnect();
});

defineExpose({ focusSpot, fit: () => fit() });
</script>
