<template>
    <div class="st-grad-box">
        <div class="st-hues" aria-hidden="true">
            <button
                v-for="(hue, index) in hues"
                :key="hue.color"
                type="button"
                class="st-hue"
                tabindex="-1"
                :class="{ 'is-on': index === hueInView }"
                :style="{ background: hue.color }"
                @click="jump(hue)"
            ></button>
        </div>
        <div
            ref="grid"
            class="st-grads"
            role="radiogroup"
            :aria-label="label"
            @scroll.passive="onScroll"
            @keydown="onKey"
            @mouseover="onHover"
            @mouseleave="hovered = ''"
        >
            <button
                type="button"
                class="st-grad is-custom"
                role="radio"
                data-value=""
                :class="{ 'is-on': state.gradient === '' }"
                :aria-checked="state.gradient === '' ? 'true' : 'false'"
                :tabindex="state.gradient === '' ? 0 : -1"
                :title="labels.custom"
                :aria-label="labels.custom"
                :style="{ backgroundImage: customCss }"
                @click="pick('')"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
            <button
                v-for="gradient in gradients"
                :key="gradient.value"
                type="button"
                class="st-grad"
                role="radio"
                :data-value="gradient.value"
                :data-bucket="gradient.bucket"
                :class="{ 'is-on': gradient.value === state.gradient }"
                :aria-checked="gradient.value === state.gradient ? 'true' : 'false'"
                :tabindex="gradient.value === state.gradient ? 0 : -1"
                :title="gradient.name"
                :aria-label="gradient.name"
                :style="{ backgroundImage: gradient.css }"
                @click="pick(gradient.value)"
            ></button>
        </div>
        <div class="st-grad-foot">
            <b>{{ hovered || chosenName }}</b>
            <a v-if="creditUrl" class="st-credit event-link" :href="creditUrl" target="_blank" rel="noopener noreferrer">{{ credit }}</a>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { state, setField, gradientStops, toRgb, toHsl } from '../style/state.js';

// The Colors field of a gradient background. The <select> stays in the form and carries the
// value (a list of hex colours, or nothing for a gradient of the owner's own); this shows every
// gradient the select offers as a swatch, in the order of the rainbow, so one is chosen by what
// it looks like and not by a name such as "Omolon".
//
// The first swatch is always the owner's own two colours. The dots above the grid are a shortcut
// for the hand (each takes the grid to its colour, and the one whose colours are at the top is
// ringed); the grid itself is the control, and the arrow keys step through it and choose.

const props = defineProps({
    field: { type: String, default: 'background_colors' },
    label: { type: String, default: '' },
    labels: { type: Object, required: true },
    credit: { type: String, default: '' },
    creditUrl: { type: String, default: '' },
});

const grid = ref(null);
const gradients = ref([]);
const hovered = ref('');
const hueInView = ref(0);

// Where each dot takes the grid: the first gradient filed at or after its place round the wheel.
// A hundred is where the greys are.
const hues = [
    { color: '#EF4444', bucket: 0 },
    { color: '#F97316', bucket: 1 },
    { color: '#EAB308', bucket: 2 },
    { color: '#22C55E', bucket: 3 },
    { color: '#14B8A6', bucket: 6 },
    { color: '#3B82F6', bucket: 7 },
    { color: '#A855F7', bucket: 9 },
    { color: '#EC4899', bucket: 11 },
    { color: '#6B7280', bucket: 100 },
];

const customCss = computed(() => 'linear-gradient(135deg, ' + state.custom1 + ', ' + state.custom2 + ')');

const chosenName = computed(() => (state.gradient === '' ? props.labels.custom : state.gradientLabel));

// Filed by the hue of the stop that holds the most colour, lightest first within a hue. A grey is
// told by how little colour it holds (chroma), not by saturation, which a near-white inflates:
// by saturation a cream is a colour, and sat among the reds.
function file(option) {
    const stops = gradientStops(option.value);
    if (! stops.length) {
        return null;
    }
    const chroma = stops.map((stop) => {
        const rgb = toRgb(stop);

        return (Math.max(rgb[0], rgb[1], rgb[2]) - Math.min(rgb[0], rgb[1], rgb[2])) / 255;
    });
    const mean = chroma.reduce((sum, part) => sum + part, 0) / chroma.length;
    const strongest = toHsl(stops[chroma.indexOf(Math.max.apply(null, chroma))]);
    const light = stops.reduce((sum, stop) => sum + toHsl(stop)[2], 0) / stops.length;
    const bucket = mean < 0.2 ? 100 : Math.floor((((strongest[0] * 360) + 15) % 360) / 30);

    return {
        value: option.value,
        name: option.textContent.trim(),
        css: 'linear-gradient(135deg, ' + stops.join(', ') + ')',
        bucket,
        order: bucket * 10 + (1 - light) * 9,
    };
}

function readOptions() {
    const select = document.getElementById(props.field);
    gradients.value = select ? Array.prototype.slice.call(select.options)
        .filter((option) => option.value !== '')
        .map(file)
        .filter(Boolean)
        .sort((a, b) => a.order - b.order) : [];
}

function pick(next) {
    setField(props.field, next);
}

function onHover(event) {
    const swatch = event.target.closest ? event.target.closest('.st-grad') : null;
    hovered.value = swatch ? swatch.title : '';
}

function swatches() {
    return Array.prototype.slice.call(grid.value.querySelectorAll('.st-grad'));
}

// The dot of the colours that are at the top of the grid now.
function markHue() {
    if (! grid.value) {
        return;
    }
    const top = grid.value.scrollTop;
    let bucket = 0;
    swatches().some((swatch) => {
        if (swatch.dataset.bucket !== undefined && swatch.offsetTop - grid.value.offsetTop + swatch.offsetHeight > top) {
            bucket = parseInt(swatch.dataset.bucket, 10);

            return true;
        }

        return false;
    });
    let at = 0;
    hues.forEach((hue, index) => {
        if (hue.bucket <= bucket) {
            at = index;
        }
    });
    hueInView.value = at;
}

let framed = false;

function onScroll() {
    if (framed) {
        return;
    }
    framed = true;
    window.requestAnimationFrame(() => {
        framed = false;
        markHue();
    });
}

function jump(hue) {
    const first = gradients.value.find((gradient) => gradient.bucket >= hue.bucket);
    const swatch = first ? swatches().find((el) => el.dataset.value === first.value) : null;
    if (swatch) {
        const still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        grid.value.scrollTo({ top: Math.max(0, swatch.offsetTop - grid.value.offsetTop - 10), behavior: still ? 'auto' : 'smooth' });
    }
}

function onKey(event) {
    const all = swatches();
    const at = all.indexOf(document.activeElement);
    if (at === -1 || all.length < 2) {
        return;
    }
    const perLine = all.filter((swatch) => swatch.offsetTop === all[0].offsetTop).length || 1;
    const rtl = window.getComputedStyle(grid.value).direction === 'rtl';
    const step = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1, ArrowDown: perLine, ArrowUp: -perLine }[event.key];
    if (! step) {
        return;
    }
    const next = all[Math.max(0, Math.min(all.length - 1, at + step))];
    if (next) {
        event.preventDefault();
        next.focus();
        setField(props.field, next.dataset.value);
    }
}

// What is chosen is brought into the grid's window. Measured only while the grid is on screen:
// inside a closed row everything measures nothing.
function showChosen() {
    if (! grid.value || ! grid.value.clientHeight) {
        return;
    }
    const on = grid.value.querySelector('.st-grad.is-on');
    if (on) {
        grid.value.scrollTop = Math.max(0, on.offsetTop - grid.value.offsetTop - grid.value.clientHeight / 2 + on.offsetHeight / 2);
    }
    markHue();
}

function onRow(event) {
    if (event.detail && event.detail.group === 'style' && event.detail.tab === 'background' && event.detail.open) {
        nextTick(showChosen);
    }
}

// The grid is shown when Gradient becomes the background's type as well as when the row opens.
watch(() => state.background, () => nextTick(showChosen));

onMounted(() => {
    readOptions();
    const select = document.getElementById(props.field);
    if (select) {
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
    }
    document.addEventListener('formkit:row', onRow);
    nextTick(showChosen);
});

onBeforeUnmount(() => {
    document.removeEventListener('formkit:row', onRow);
});
</script>
