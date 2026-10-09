<template>
    <div class="list-animation-picker">
        <div class="relative">
            <div
                ref="stage"
                class="rounded-xl bg-gray-50 dark:bg-gray-900 p-4 pb-10 overflow-hidden"
                aria-hidden="true"
                :dir="rtl ? 'rtl' : 'ltr'"
                :data-list-anim="stageAnimation"
                :data-list-rtl="rtl ? '' : null"
                :style="{ '--es-accent': accent, '--lr-side': rtl ? '-1' : '1' }"
            >
                <div class="space-y-2.5">
                    <div
                        v-for="(card, i) in cards"
                        :key="i"
                        ref="cardEls"
                        data-list-reveal
                        data-list-revealed="done"
                        :style="{ '--deal-dir': i % 2 ? '1' : '-1' }"
                    >
                        <div class="flex h-14 items-center rounded-lg bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                            <div
                                data-reveal-date
                                class="ms-2.5 flex flex-shrink-0 w-9 h-9 flex-col items-center justify-center rounded-md border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
                            >
                                <span class="text-[8px] font-bold uppercase leading-none">{{ card.month }}</span>
                                <span class="mt-0.5 text-sm font-bold leading-none">{{ card.day }}</span>
                            </div>
                            <div data-reveal-body class="flex flex-1 min-w-0 flex-col justify-center gap-1.5 px-2.5">
                                <div data-reveal-title class="min-w-0">
                                    <h3
                                        v-if="card.name"
                                        class="truncate text-sm font-semibold leading-tight text-gray-900 dark:text-gray-100"
                                    >{{ card.name }}</h3>
                                    <h3 v-else class="h-2.5 w-28 rounded-full bg-gray-200 dark:bg-gray-700"></h3>
                                </div>
                                <div class="h-1.5 w-20 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                            </div>
                            <div data-reveal-media class="w-20 flex-shrink-0 self-stretch">
                                <a class="block w-full h-full" tabindex="-1">
                                    <img v-if="card.image" :src="card.image" alt="" class="w-full h-full object-cover" />
                                    <span v-else class="block w-full h-full" :style="{ background: placeholderFill(i) }"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button
                v-if="motionOk && selected !== 'none'"
                type="button"
                class="absolute bottom-2 end-2 inline-flex items-center gap-1 rounded-lg bg-white/90 dark:bg-gray-800/90 px-2.5 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 shadow-sm transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]"
                @click="play"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ labels.replay }}
            </button>
        </div>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            <template v-if="selected === 'none'">{{ labels.pick }}</template>
            <template v-else>
                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ designs[selected]?.name }}</span>
                <span> &middot; {{ designs[selected]?.desc }}</span>
            </template>
        </p>
        <p v-if="!motionOk && selected !== 'none'" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ labels.deviceOff }}</p>

        <div
            v-if="selected !== 'none' && (layout === 'calendar' || switchedToList)"
            class="mt-3 flex items-start gap-2 rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3 text-sm text-gray-600 dark:text-gray-300"
        >
            <svg class="w-5 h-5 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div v-if="switchedToList">{{ labels.nowList }}</div>
            <div v-else>
                {{ labels.calendarNote }}
                <button
                    type="button"
                    class="ms-1 font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded"
                    @click="switchToList"
                >{{ labels.useList }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue';

// The preview for the "Event animation" setting on the schedule edit page. It drives the SAME
// attributes and stylesheet as the guest page (resources/css/list-reveal.css), with the owner's
// own events, so what plays here is what their visitors will see.
//
// The cards are drawn as the page draws them: an event's name in the page's text face and its
// date on a plain tile. They used to borrow the schedule's font and put the date on the accent,
// which the page does for neither (the font is the schedule's NAME's), and the Style tab's
// preview, standing beside this one, drew the same events differently. The accent is still what
// the Curtain and Shine sweeps and a stand-in poster are made of.
//
// The radios themselves are server-rendered Blade (so posting, old() and validation work without
// JS); this island listens to them, and to the accent color and default layout fields.

const props = defineProps({
    saved: { type: String, default: 'none' },
    accentColor: { type: String, default: '#4E81FA' },
    rtl: { type: Boolean, default: false },
    guestUrl: { type: String, default: '' },
    layout: { type: String, default: 'list' },
    events: { type: Array, default: () => [] },
    designs: { type: Object, required: true },
    labels: { type: Object, required: true },
});

const stage = ref(null);
const cardEls = ref([]);
const selected = ref(props.saved);
const accent = ref(props.accentColor || '#4E81FA');
const layout = ref(props.layout);
const switchedToList = ref(false);

const reduceQuery = typeof window.matchMedia === 'function'
    ? window.matchMedia('(prefers-reduced-motion: reduce)')
    : null;
const motionOk = ref(computeMotionOk());

function computeMotionOk() {
    return !(reduceQuery && reduceQuery.matches)
        && !document.documentElement.classList.contains('es-a11y-reduce-motion');
}

// Four cards: the owner's events first, placeholders for the rest.
const cards = computed(() => {
    const list = props.events.slice(0, 4).map(e => ({ ...e }));
    while (list.length < 4) {
        list.push({ name: '', image: null, month: '', day: '' });
    }
    return list;
});

const stageAnimation = computed(() => (motionOk.value && selected.value !== 'none') ? selected.value : null);

const supportsColorMix = typeof CSS !== 'undefined' && CSS.supports && CSS.supports('color', 'color-mix(in srgb, red 50%, blue)');

// A stand-in poster for an event with no image: a gradient through the accent color, so the
// Curtain and Shine sweeps (which are the accent color too) still read against it. A browser
// without color-mix() would drop the whole value and leave a blank block, so it gets the plain
// accent instead.
function placeholderFill(i) {
    const c = accent.value;
    if (!supportsColorMix) return c;
    const angle = [135, 160, 115, 145][i % 4];
    return `linear-gradient(${angle}deg, color-mix(in srgb, ${c} 65%, #000) 0%, ${c} 50%, color-mix(in srgb, ${c} 55%, #fff) 100%)`;
}

const timers = [];

function clearTimers() {
    while (timers.length) clearTimeout(timers.pop());
}

// The same wave the guest page's directive writes (role/partials/calendar.blade.php,
// stampOffsets()): keep the two tables identical, or the preview stops matching the page.
const ROW_OFFSETS = [0, 50, 95, 135, 170, 200, 225, 245];

function stampOffsets(card) {
    card.querySelectorAll('[data-reveal-body]').forEach(body => {
        Array.from(body.children).forEach((row, i) => {
            row.style.setProperty('--lr-t', ROW_OFFSETS[Math.min(i, ROW_OFFSETS.length - 1)] + 'ms');
        });
    });
}

function clearOffsets(card) {
    card.querySelectorAll('[data-reveal-body] > *').forEach(row => row.style.removeProperty('--lr-t'));
}

function settleAll() {
    clearTimers();
    cardEls.value.forEach(el => {
        el.setAttribute('data-list-revealed', 'done');
        el.style.removeProperty('--reveal-delay');
        clearOffsets(el);
    });
}

// Replay: drop every card back to its hidden state, flush styles so that state is what the
// transitions start from, then reveal them in the guest page's later-batch cascade.
function play() {
    if (!stageAnimation.value) {
        settleAll();
        return;
    }
    clearTimers();
    const els = cardEls.value;
    els.forEach(el => el.removeAttribute('data-list-revealed'));
    if (els.length) void els[0].offsetWidth;
    els.forEach((el, i) => {
        const delay = i * 70;
        stampOffsets(el);
        el.style.setProperty('--reveal-delay', delay + 'ms');
        el.setAttribute('data-list-revealed', 'in');
        timers.push(setTimeout(() => {
            el.setAttribute('data-list-revealed', 'done');
            el.style.removeProperty('--reveal-delay');
            clearOffsets(el);
        }, delay + 1500));
    });
}

function syncPreviewLink() {
    const link = document.getElementById('list-animation-preview-link');
    const unsaved = document.getElementById('list-animation-unsaved');
    const wrap = document.getElementById('list-animation-preview');
    if (link && props.guestUrl) {
        const url = new URL(props.guestUrl, window.location.origin);
        url.searchParams.set('layout', 'list');
        url.searchParams.set('list_animation', selected.value);
        link.href = url.toString();
    }
    if (wrap) wrap.hidden = selected.value === 'none' || !props.guestUrl;
    if (unsaved) unsaved.hidden = selected.value === props.saved;
}

function switchToList() {
    const radio = document.getElementById('event_layout_list');
    if (radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change', { bubbles: true }));
    }
    switchedToList.value = true;
}

function onChange(e) {
    const t = e.target;
    if (!t) return;
    if (t.name === 'list_animation' && t.checked) {
        selected.value = t.value;
    } else if (t.name === 'event_layout' && t.checked) {
        layout.value = t.value;
        if (t.value !== 'list') switchedToList.value = false;
    }
}

function onInput(e) {
    if (e.target && e.target.id === 'accent_color' && /^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        accent.value = e.target.value;
    }
}

function onMotionChange() {
    motionOk.value = computeMotionOk();
}

watch(selected, () => {
    syncPreviewLink();
    nextTick(play);
});

watch(motionOk, () => nextTick(settleAll));

let stageObserver = null;
let classObserver = null;

onMounted(() => {
    document.addEventListener('change', onChange);
    document.addEventListener('input', onInput);
    if (reduceQuery && reduceQuery.addEventListener) reduceQuery.addEventListener('change', onMotionChange);
    // The accessibility widget toggles its reduce-motion class on <html> at any time.
    classObserver = new MutationObserver(onMotionChange);
    classObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    // Start from what the form actually shows, not the saved values: after a failed validation
    // the radios and fields are re-checked from old(), which the props do not know about.
    const checked = document.querySelector('input[name="list_animation"]:checked');
    if (checked) selected.value = checked.value;
    const accentInput = document.getElementById('accent_color');
    if (accentInput && /^#[0-9A-Fa-f]{6}$/.test(accentInput.value)) accent.value = accentInput.value;
    const layoutInput = document.querySelector('input[name="event_layout"]:checked');
    if (layoutInput) layout.value = layoutInput.value;

    syncPreviewLink();

    // Play once the first time the stage scrolls into view (it lives on a tab, so often later).
    if ('IntersectionObserver' in window && stage.value) {
        stageObserver = new IntersectionObserver(entries => {
            if (entries.some(entry => entry.isIntersecting)) {
                stageObserver.disconnect();
                stageObserver = null;
                play();
            }
        }, { threshold: 0.4 });
        stageObserver.observe(stage.value);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('change', onChange);
    document.removeEventListener('input', onInput);
    if (reduceQuery && reduceQuery.removeEventListener) reduceQuery.removeEventListener('change', onMotionChange);
    if (classObserver) classObserver.disconnect();
    if (stageObserver) stageObserver.disconnect();
    clearTimers();
});
</script>
