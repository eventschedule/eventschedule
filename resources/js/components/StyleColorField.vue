<template>
    <input
        ref="box"
        type="text"
        class="st-hex"
        dir="ltr"
        maxlength="7"
        spellcheck="false"
        autocomplete="off"
        :aria-label="label"
        :class="{ 'is-bad': bad }"
        :value="typed"
        @input.stop="onType"
        @change.stop
        @blur="settle"
        @keydown.enter.prevent="settle"
    />
    <div v-if="presets.length" class="st-swatches">
        <button
            v-for="color in presets"
            :key="color"
            type="button"
            class="st-swatch"
            :class="{ 'is-on': color.toLowerCase() === current }"
            :style="{ background: color }"
            :title="color"
            :aria-label="color"
            :aria-pressed="color.toLowerCase() === current ? 'true' : 'false'"
            @click="take(color)"
        ></button>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { normalizeHex, setField } from '../style/state.js';

// The hex of a colour field on the Style tab, and a few colours to start from. The field itself
// (the native colour input, drawn as a square well by the tab's stylesheet) is server-rendered
// just before this island and carries the value; this is a second way of setting it.
//
// Typing here is not a change to the form until it spells a colour: the box keeps its own events
// to itself (the save bar takes any input event inside the form as a change), and Enter settles
// the box, where it would otherwise submit the form.

const props = defineProps({
    field: { type: String, required: true },
    label: { type: String, default: '' },
    presets: { type: Array, default: () => [] },
});

const box = ref(null);
const current = ref('');
const typed = ref('');
const bad = ref(false);

function fieldValue() {
    const el = document.getElementById(props.field);

    return el ? (normalizeHex(el.value) || '') : '';
}

// What the field holds, shown in the box unless somebody is typing in it.
function sync() {
    current.value = fieldValue();
    if (document.activeElement !== box.value) {
        typed.value = current.value.toUpperCase();
    }
}

function take(color) {
    const next = normalizeHex(color);
    if (next) {
        setField(props.field, next);
        current.value = next;
        typed.value = next.toUpperCase();
        bad.value = false;
    }
}

// Six digits spell a colour the moment they are typed. Three do too (#fa0), but only once the
// typing has stopped: on the way to six, every colour passes through three.
function onType(event) {
    const text = event.target.value.trim();
    typed.value = event.target.value;
    const digits = text.replace(/^#/, '');
    bad.value = digits.length >= 6 && ! /^[0-9a-f]{6}$/i.test(digits);
    if (/^[0-9a-f]{6}$/i.test(digits)) {
        setField(props.field, '#' + digits.toLowerCase());
        current.value = '#' + digits.toLowerCase();
    }
}

// Read from the box itself, not from what the last keystroke left: a box can be filled without
// one (a browser's autofill, text dragged in).
function settle() {
    const next = normalizeHex(box.value ? box.value.value : typed.value);
    if (next && next !== current.value) {
        setField(props.field, next);
    }
    bad.value = false;
    current.value = fieldValue();
    typed.value = current.value.toUpperCase();
}

function onFieldEvent(event) {
    if (event.target && event.target.id === props.field) {
        sync();
    }
}

onMounted(() => {
    sync();
    document.addEventListener('input', onFieldEvent);
    document.addEventListener('change', onFieldEvent);
    document.addEventListener('style:sync', sync);
});

onBeforeUnmount(() => {
    document.removeEventListener('input', onFieldEvent);
    document.removeEventListener('change', onFieldEvent);
    document.removeEventListener('style:sync', sync);
});
</script>
