<template>
    <div class="vmc-map" :dir="'ltr'">
        <svg :viewBox="viewBox" role="group" :aria-label="label" preserveAspectRatio="xMidYMid meet">
            <rect :width="map.width" :height="map.height" class="vmc-map-ground" rx="12" />
            <g v-for="l in map.landmarks" :key="'l' + l.x + '-' + l.y" :transform="turn(l)">
                <rect :x="l.x" :y="l.y" :width="l.w" :height="l.h" rx="6" class="vmc-map-landmark" />
                <text v-if="l.label" :x="l.x + l.w / 2" :y="l.y + l.h / 2" class="vmc-map-landmark-text"
                      text-anchor="middle" dominant-baseline="central">{{ l.label }}</text>
            </g>
            <g v-for="s in map.spots" :key="s.id" :transform="turn(s)"
               :class="['vmc-map-spot', 'is-' + stateOf(s), { 'is-picked': s.id === selected }]"
               role="button" tabindex="0" :aria-label="spotName(s)"
               @click="$emit('pick', s.id)" @keydown.enter.prevent="$emit('pick', s.id)" @keydown.space.prevent="$emit('pick', s.id)">
                <rect :x="s.x" :y="s.y" :width="s.w" :height="s.h" rx="4" :style="areaStyle(s)" />
                <text :x="s.x + s.w / 2" :y="s.y + s.h / 2" text-anchor="middle" dominant-baseline="central"
                      :font-size="fontSize(s)">{{ s.label }}</text>
            </g>
        </svg>
    </div>
</template>

<script>
/**
 * A plain drawing of the vendor map for the vendor link: numbers only, tap one to pick it. The
 * visitors' map (MapCanvas) is a separate, richer renderer; this one needs nothing but the
 * public payload and the list's flags (named, waiting, has a page).
 */
export default {
    name: 'ClaimMap',
    props: {
        map: { type: Object, required: true },
        flags: { type: Object, default: () => ({}) },
        selected: { type: Number, default: null },
        label: { type: String, default: '' },
        names: { type: Object, default: () => ({}) },
    },
    emits: ['pick'],
    computed: {
        viewBox() {
            return `0 0 ${this.map.width || 1000} ${this.map.height || 700}`;
        },
        areaColors() {
            const out = {};
            (this.map.areas || []).forEach((a) => { out[a.id] = a.color; });
            return out;
        },
    },
    methods: {
        turn(item) {
            if (!item.rotation) {
                return null;
            }
            return `rotate(${item.rotation} ${item.x + item.w / 2} ${item.y + item.h / 2})`;
        },
        stateOf(s) {
            const f = this.flags[s.id] || {};
            if (f.waiting) return 'waiting';
            if (f.page) return 'taken';
            if (f.name) return 'named';
            return 'free';
        },
        areaStyle(s) {
            const color = this.areaColors[s.area];
            return color ? { stroke: color } : null;
        },
        fontSize(s) {
            return Math.max(10, Math.min(s.w, s.h) * 0.42);
        },
        spotName(s) {
            return this.names[s.id] || s.label;
        },
    },
};
</script>

<style scoped>
.vmc-map { background: var(--vmc-card); border: 1px solid var(--vmc-line); border-radius: 16px; padding: 8px; }
.vmc-map svg { display: block; width: 100%; height: auto; max-height: 70vh; touch-action: manipulation; }
.vmc-map-ground { fill: var(--vmc-bg); }
.vmc-map-landmark { fill: var(--vmc-line); }
.vmc-map-landmark-text { fill: var(--vmc-ink-3); font-size: 14px; }
.vmc-map-spot { cursor: pointer; }
.vmc-map-spot rect { fill: var(--vmc-card); stroke: var(--vmc-line-strong); stroke-width: 2; }
.vmc-map-spot text { fill: var(--vmc-ink); font-weight: 700; pointer-events: none; }
.vmc-map-spot.is-named rect { fill: var(--vmc-soft); }
.vmc-map-spot.is-taken rect, .vmc-map-spot.is-waiting rect { fill: var(--vmc-line); }
.vmc-map-spot.is-taken text, .vmc-map-spot.is-waiting text { fill: var(--vmc-ink-3); }
.vmc-map-spot.is-picked rect { stroke: var(--vmc-accent-text); stroke-width: 5; }
.vmc-map-spot:focus { outline: none; }
.vmc-map-spot:focus-visible rect { stroke: var(--vmc-accent-text); stroke-width: 5; }
</style>
