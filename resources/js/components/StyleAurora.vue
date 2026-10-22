<template>
    <div class="st-aur-box">
        <div
            ref="grid"
            class="st-aur-grid"
            role="radiogroup"
            :aria-label="labels.palette"
            @keydown="onKey"
            @mouseover="onHover"
            @mouseleave="hovered = ''"
        >
            <button
                v-for="palette in shown"
                :key="palette.key"
                type="button"
                class="st-aur-sw"
                role="radio"
                :data-value="palette.key"
                :class="{ 'is-on': palette.key === state.auroraPalette }"
                :aria-checked="palette.key === state.auroraPalette ? 'true' : 'false'"
                :tabindex="palette.key === state.auroraPalette ? 0 : -1"
                :title="palette.name"
                :aria-label="palette.name"
                :style="{ background: swatch(palette.key) }"
                @click="pick(palette.key)"
            >
                <svg v-if="palette.key === 'custom' && state.auroraPalette !== 'custom'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </button>
        </div>
        <div class="st-grad-foot">
            <b>{{ hovered || chosenName }}</b>
        </div>
    </div>
    <div class="st-aur-tools">
        <button type="button" class="event-link" @click="shuffle">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            {{ labels.shuffle }}
        </button>
        <button type="button" class="event-link" :disabled="history.length === 0" @click="back">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="rtl:-scale-x-100" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
            </svg>
            {{ labels.back }}
        </button>
        <span class="sr-only" aria-live="polite">{{ announced }}</span>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';
import { state, setField, auroraTokens, auroraConfig, headerPicture } from '../style/state.js';
import { staticGradient, nextSeed } from '../style/aurora.js';

// Aurora's palette and arrangement. Two fields stay in the form and carry the values: the
// aurora_palette <select> and the hidden aurora_seed. This shows every palette as a still frame
// of the arrangement chosen, in the light or dark the preview is drawn in, so a palette is chosen
// by how it looks; Shuffle picks a new arrangement that looks different, and Back walks the ones
// tried on this visit (never saved anywhere: a page load starts the list again).
//
// Match header is offered only while the header has a picture. The colours it shows are the
// header's as far as this page can read them; Save samples the saved picture on the server.

const props = defineProps({
    field: { type: String, default: 'aurora_palette' },
    seedField: { type: String, default: 'aurora_seed' },
    // [{ key, name }], in the order to show them.
    palettes: { type: Array, default: () => [] },
    labels: { type: Object, required: true },
});

const grid = ref(null);
const hovered = ref('');
const history = ref([]);
const announced = ref('');

const shown = computed(() => props.palettes.filter((palette) => palette.key !== 'header'
    || headerPicture() !== '' || state.auroraPalette === 'header'));

const chosenName = computed(() => {
    const palette = props.palettes.find((p) => p.key === state.auroraPalette);

    return palette ? palette.name : '';
});

function swatch(key) {
    const compositions = auroraConfig().compositions;

    return compositions.length ? staticGradient(auroraTokens(key), state.auroraSeed, compositions) : '';
}

// A palette carries an accent: while the schedule's accent is still the one the old palette
// brought (a new schedule starts that way), it moves to the new one, so the buttons keep
// matching the light. An accent the owner chose is left alone.
function pick(key) {
    const palettes = auroraConfig().palettes;
    const was = palettes[state.auroraPalette];
    const next = palettes[key];
    if (was && next && was.accent && next.accent && String(was.accent).toLowerCase() === String(state.accent).toLowerCase()) {
        // Lower case: a colour input keeps only that, and setField() says nothing when the field
        // did not take the value as given.
        setField('accent_color', String(next.accent).toLowerCase());
    }
    setField(props.field, key);
}

// Said again on every press: a live region speaks only when its text changes.
function say(words) {
    announced.value = '';
    nextTick(() => {
        announced.value = words;
    });
}

function onHover(event) {
    const button = event.target.closest ? event.target.closest('.st-aur-sw') : null;
    hovered.value = button ? button.title : '';
}

function onKey(event) {
    const all = Array.prototype.slice.call(grid.value.querySelectorAll('.st-aur-sw'));
    const at = all.indexOf(document.activeElement);
    if (at === -1 || all.length < 2) {
        return;
    }
    const perLine = all.filter((button) => button.offsetTop === all[0].offsetTop).length || 1;
    const rtl = window.getComputedStyle(grid.value).direction === 'rtl';
    const step = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1, ArrowDown: perLine, ArrowUp: -perLine }[event.key];
    if (! step) {
        return;
    }
    const next = all[Math.max(0, Math.min(all.length - 1, at + step))];
    if (next) {
        event.preventDefault();
        next.focus();
        pick(next.dataset.value);
    }
}

function shuffle() {
    const next = nextSeed(state.auroraSeed, auroraConfig().compositions.length || 8);
    history.value = history.value.concat([state.auroraSeed]).slice(-10);
    setField(props.seedField, String(next));
    say(props.labels.shuffled);
}

function back() {
    if (! history.value.length) {
        return;
    }
    const previous = history.value[history.value.length - 1];
    history.value = history.value.slice(0, -1);
    setField(props.seedField, String(previous));
    say(props.labels.backDone);
}
</script>
