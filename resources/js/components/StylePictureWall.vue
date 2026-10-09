<template>
    <div class="st-wall" role="radiogroup" :aria-label="label" @keydown="onKey">
        <div class="st-choices">
            <button
                v-for="choice in choices"
                :key="'choice-' + choice.value"
                type="button"
                class="st-tile"
                role="radio"
                :data-value="choice.value"
                :class="{ 'is-on': isOn(choice.value), 'has-picture': choice.upload && !! upload }"
                :aria-checked="isOn(choice.value) ? 'true' : 'false'"
                :tabindex="isOn(choice.value) ? 0 : -1"
                :title="caption(choice)"
                :aria-label="caption(choice)"
                @click="press(choice)"
            >
                <span v-if="choice.upload && upload" class="st-tile-thumb" :style="{ backgroundImage: cssUrl(upload) }"></span>
                <span class="st-tile-over">
                    <span v-if="choice.wash" class="st-tile-wash" :style="{ background: wash }"></span>
                    <svg v-else-if="! (choice.upload && upload)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="choice.icon" />
                    </svg>
                    <span class="st-tile-cap">{{ caption(choice) }}</span>
                </span>
            </button>
        </div>
        <div v-if="current === ''" class="st-own">
            <template v-if="upload">
                <button type="button" class="event-link" data-own="change" @click="chooseFile">{{ labels.change }}</button>
                <button type="button" class="event-link is-danger" data-own="remove" @click="removeOwn">{{ labels.remove }}</button>
            </template>
            <button v-else type="button" class="event-link" data-own="choose" @click="chooseFile">{{ labels.chooseFile }}</button>
        </div>
        <div ref="grid" class="st-tiles" :class="{ 'is-folded': folded, 'is-tall': kind === 'background' }">
            <button
                v-for="picture in pictures"
                :key="picture.value"
                type="button"
                class="st-tile"
                role="radio"
                :data-value="picture.value"
                :class="{ 'is-on': isOn(picture.value) }"
                :aria-checked="isOn(picture.value) ? 'true' : 'false'"
                :tabindex="isOn(picture.value) ? 0 : -1"
                :title="picture.label"
                :aria-label="picture.label"
                @click="pick(picture.value)"
            >
                <img :src="thumb(picture.value)" alt="" loading="lazy" decoding="async" />
            </button>
        </div>
    </div>
    <div class="st-more">
        <b class="st-picked">{{ pickedName }}</b>
        <button v-if="pictures.length > 4" type="button" class="event-link" data-own="more" @click="folded = ! folded">{{ folded ? labels.showAll : labels.showLess }}</button>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';
import { state, setField, cssUrl, headerThumb, backgroundThumb, HEADER_KEYWORDS } from '../style/state.js';

// The Header Image and the background's Image fields of the Style tab. The <select> stays in the
// form and carries the value: the name of a built-in picture, one of the header's words that
// name no picture (none, gradient, logos), or nothing for a picture of the owner's own. This
// shows the choices that are not a built-in picture on a line of their own, then the pictures.
//
// A picture of the owner's own lives in the page's file field and its blocks, which are out of
// sight while this runs (class st-native). This presses them: the Upload tile opens the chooser,
// and it becomes the choice once there is a picture, so a chooser that was cancelled leaves the
// choice alone. Change and Remove stand under the choices. Remove does what it did before: a
// picture waiting for Save is cleared, a stored one is deleted at once, after the page's question.

const props = defineProps({
    field: { type: String, required: true },
    kind: { type: String, required: true },
    label: { type: String, default: '' },
    labels: { type: Object, required: true },
});

const ICONS = {
    none: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
    logos: 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
    upload: 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
};

// The page's own pieces for a picture of the owner's own, by the ids they have always had.
const OWN = {
    header: {
        input: 'header_image_url',
        clear: () => window.clearHeaderFileInput(),
        pending: 'header_image_url_preview_clear',
        stored: '#delete_header_image_button [data-delete-image-url]',
        // With no picture left, the header has none.
        fallback: 'none',
    },
    background: {
        input: 'background_image_url',
        clear: () => window.clearRoleFileInput('background_image_url', 'background_image_preview', 'background_image_url_filename'),
        pending: 'background_image_preview_clear',
        stored: '#background_image_existing [data-delete-image-url]',
        fallback: null,
    },
};

const grid = ref(null);
const choices = ref([]);
const pictures = ref([]);
const folded = ref(true);

const own = OWN[props.kind];

const current = computed(() => (props.kind === 'header' ? state.headerImage : state.backgroundImage));
const upload = computed(() => (props.kind === 'header' ? state.headerUpload : state.backgroundUpload));
const wash = computed(() => 'linear-gradient(to bottom, ' + state.accent + ', color-mix(in srgb, ' + state.accent + ' 12%, transparent))');

// The line under the wall names a built-in picture. The other choices are named on their tiles.
const pickedName = computed(() => {
    const picture = pictures.value.find((entry) => entry.value === current.value);

    return picture ? picture.label : '';
});

function thumb(name) {
    return props.kind === 'header' ? headerThumb(name) : backgroundThumb(name);
}

function isOn(optionValue) {
    return optionValue === current.value;
}

function caption(choice) {
    if (choice.upload) {
        return upload.value ? props.labels.custom : props.labels.uploadImage;
    }

    return choice.label;
}

function readOptions() {
    const select = document.getElementById(props.field);
    const all = select ? Array.prototype.map.call(select.options, (option) => ({ value: option.value, label: option.textContent.trim() })) : [];
    choices.value = all
        .filter((option) => option.value === '' || (props.kind === 'header' && HEADER_KEYWORDS.indexOf(option.value) !== -1))
        .map((option) => ({
            value: option.value,
            label: option.label,
            upload: option.value === '',
            wash: option.value === 'gradient',
            icon: option.value === '' ? ICONS.upload : (ICONS[option.value] || ICONS.none),
        }));
    pictures.value = all.filter((option) => ! choices.value.some((choice) => choice.value === option.value));
}

function pick(next) {
    setField(props.field, next);
}

function chooseFile() {
    const input = document.getElementById(own.input);
    if (input) {
        input.click();
    }
}

function press(choice) {
    // Upload with nothing uploaded yet opens the chooser, and is not the choice until a picture
    // arrives.
    if (choice.upload && ! upload.value) {
        chooseFile();

        return;
    }
    pick(choice.value);
}

function isPending() {
    const block = document.getElementById(own.pending);

    return !! block && block.style.display !== 'none';
}

function removeOwn() {
    if (isPending()) {
        own.clear();
        // The stored picture, where there is one, is the owner's picture again; with none left the
        // field goes back to having no picture.
        nextTick(leaveIfEmpty);

        return;
    }
    const stored = document.querySelector(own.stored);
    if (stored) {
        stored.click();
    }
}

function leaveIfEmpty() {
    if (own.fallback !== null && current.value === '' && ! upload.value) {
        setField(props.field, own.fallback);
    }
}

// The arrow keys step from one to the next and choose it, as the two arrow buttons beside the
// old dropdown did.
function onKey(event) {
    const rtl = window.getComputedStyle(grid.value).direction === 'rtl';
    const step = { ArrowRight: rtl ? -1 : 1, ArrowLeft: rtl ? 1 : -1, ArrowDown: 1, ArrowUp: -1 }[event.key];
    const all = Array.prototype.slice.call(grid.value.parentElement.querySelectorAll('.st-tile'));
    const at = all.indexOf(document.activeElement);
    if (! step || at === -1) {
        return;
    }
    const next = all[Math.max(0, Math.min(all.length - 1, at + step))];
    if (! next) {
        return;
    }
    event.preventDefault();
    folded.value = false;
    next.focus();
    const choice = choices.value.find((entry) => entry.value === next.dataset.value);
    if (choice && choice.upload && ! upload.value) {
        return;
    }
    pick(next.dataset.value);
}

// What is chosen is never left behind the fold. Measured only while the wall is on screen: inside
// a closed row everything measures nothing.
let unfoldedFor = null;

function unfoldForChosen() {
    if (! grid.value || ! grid.value.clientHeight || unfoldedFor === current.value) {
        return;
    }
    unfoldedFor = current.value;
    const on = grid.value.querySelector('.st-tile.is-on');
    if (on && folded.value && on.offsetTop - grid.value.offsetTop > grid.value.clientHeight - 8) {
        folded.value = false;
    }
}

function onRow() {
    nextTick(unfoldForChosen);
}

// A picture that has just arrived is the choice.
watch(upload, (now, before) => {
    if (now && now !== before && current.value !== '') {
        pick('');
    }
});

watch(current, () => nextTick(unfoldForChosen));
watch(() => state.background, () => nextTick(unfoldForChosen));
watch(() => state.headerStyle, () => nextTick(unfoldForChosen));

onMounted(() => {
    readOptions();
    const select = document.getElementById(props.field);
    if (select) {
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');
    }
    document.addEventListener('formkit:row', onRow);
    document.addEventListener('style:image-removed', leaveIfEmpty);
    nextTick(unfoldForChosen);
});

onBeforeUnmount(() => {
    document.removeEventListener('formkit:row', onRow);
    document.removeEventListener('style:image-removed', leaveIfEmpty);
});
</script>
