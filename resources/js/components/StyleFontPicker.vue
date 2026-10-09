<template>
    <button
        ref="button"
        id="style-font-button"
        type="button"
        class="st-font-btn"
        aria-haspopup="listbox"
        aria-controls="style-font-panel"
        :aria-expanded="open ? 'true' : 'false'"
        @click="toggle"
    >
        <span class="st-font-sample" :style="{ fontFamily: fontStack(state.fontLabel) }">{{ sample }}</span>
        <span class="st-font-name">{{ state.fontLabel }}</span>
        <svg class="st-chev" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
    <div v-show="open" id="style-font-panel" ref="panel" class="st-font-panel" @keydown="onKey">
        <div class="st-search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
                type="text"
                autocomplete="off"
                :placeholder="labels.search"
                :aria-label="labels.search"
                :value="needle"
                @input.stop="onSearch"
                @change.stop
                @keydown.enter.prevent
            />
        </div>
        <div ref="list" class="st-font-list" role="listbox" :style="{ maxHeight: listHeight + 'px' }">
            <button
                v-for="font in shown"
                :key="font.value"
                type="button"
                class="st-font-row"
                role="option"
                :class="{ 'is-on': font.value === state.font }"
                :aria-selected="font.value === state.font ? 'true' : 'false'"
                :data-value="font.value"
                @click="choose(font)"
            >
                <span class="st-font-sample" :style="{ fontFamily: fontStack(font.label) }">{{ sample }}</span>
                <span class="st-font-name">{{ font.label }}</span>
            </button>
            <div v-if="! shown.length" class="st-empty">{{ labels.noResults }}</div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { state, setField, loadFont, fontStack, room, bringIntoRoom } from '../style/state.js';

// The Font Family field of the Style tab. The <select> stays in the form and carries the value;
// this shows the schedule's own name in the chosen face and, opened, in every face the select
// offers. The faces are this install's own files (public/vendor/fonts), asked for as their rows
// come into view.
//
// The list stays open while faces are tried, and Up and Down step from one to the next and
// choose it, which is what the two arrow buttons beside the old dropdown were for.

const props = defineProps({
    field: { type: String, default: 'font_family' },
    labels: { type: Object, required: true },
});

const button = ref(null);
const panel = ref(null);
const list = ref(null);
const open = ref(false);
const needle = ref('');
const listHeight = ref(272);

const sample = computed(() => state.name || props.labels.preview);

const shown = computed(() => {
    const wanted = needle.value.trim().toLowerCase();

    return wanted ? state.fonts.filter((font) => font.label.toLowerCase().indexOf(wanted) !== -1) : state.fonts;
});

let rowsInView = null;

// A face is fetched when its row is on screen, or about to be: 234 stylesheets are not asked for
// to show sixteen rows.
function watchRows() {
    if (rowsInView) {
        rowsInView.disconnect();
        rowsInView = null;
    }
    if (! open.value || ! list.value) {
        return;
    }
    const rows = list.value.querySelectorAll('.st-font-row');
    if (! ('IntersectionObserver' in window)) {
        rows.forEach((row) => loadFont(row.dataset.value));

        return;
    }
    rowsInView = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                loadFont(entry.target.dataset.value);
                rowsInView.unobserve(entry.target);
            }
        });
    }, { root: list.value, rootMargin: '120px 0px' });
    rows.forEach((row) => rowsInView.observe(row));
}

// As tall as the room between the top bar and the save bar allows, sixteen faces at the most, so
// its last row is never under the bar.
function fit() {
    const label = document.querySelector('label[for="style-font-button"]') || button.value;
    const above = button.value.getBoundingClientRect().bottom - label.getBoundingClientRect().top;
    const space = room();
    listHeight.value = Math.max(132, Math.min(704, space.floor - space.top - above - 58));

    return label;
}

function toggle() {
    open.value = ! open.value;
    if (! open.value) {
        return;
    }
    needle.value = '';
    const label = fit();
    nextTick(() => {
        const on = list.value.querySelector('.st-font-row.is-on');
        if (on) {
            list.value.scrollTop = Math.max(0, on.offsetTop - list.value.offsetTop - 88);
        }
        watchRows();
        bringIntoRoom(label, panel.value);
    });
}

function onSearch(event) {
    needle.value = event.target.value;
}

function choose(font) {
    setField(props.field, font.value);
}

function onKey(event) {
    if (event.key === 'Escape') {
        open.value = false;
        button.value.focus();

        return;
    }
    if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
        return;
    }
    const rows = Array.prototype.slice.call(list.value.querySelectorAll('.st-font-row'));
    let at = rows.indexOf(document.activeElement);
    if (at === -1) {
        at = rows.findIndex((row) => row.dataset.value === state.font);
    }
    const next = rows[Math.max(0, Math.min(rows.length - 1, at + (event.key === 'ArrowDown' ? 1 : -1)))];
    if (next) {
        event.preventDefault();
        next.focus();
        setField(props.field, next.dataset.value);
    }
}

watch(shown, () => nextTick(watchRows));

onMounted(() => {
    // The field's own label names the button now: the <select> it named is out of sight.
    const label = document.querySelector('label[for="' + props.field + '"]');
    if (label) {
        label.setAttribute('for', 'style-font-button');
    }
    const select = document.getElementById(props.field);
    if (select) {
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
    }
});

onBeforeUnmount(() => {
    if (rowsInView) {
        rowsInView.disconnect();
    }
});
</script>
