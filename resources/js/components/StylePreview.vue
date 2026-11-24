<template>
    <div class="st-pv-top">
        <span class="st-pv-title">{{ labels.preview }}</span>
        <span class="st-pv-tools">
            <span class="st-seg" role="group">
                <button type="button" :title="labels.light" :aria-label="labels.light" :aria-pressed="mode === 'light' ? 'true' : 'false'" @click="state.previewMode = 'light'">{{ labels.light }}</button>
                <button type="button" :title="labels.dark" :aria-label="labels.dark" :aria-pressed="mode === 'dark' ? 'true' : 'false'" @click="state.previewMode = 'dark'">{{ labels.dark }}</button>
            </span>
            <button type="button" class="st-pv-toggle" :aria-label="labels.preview" :aria-expanded="opened ? 'true' : 'false'" @click="opened = ! opened">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
        </span>
    </div>

    <div
        id="style-preview"
        class="st-pv"
        aria-hidden="true"
        :data-mode="mode"
        :data-header="state.headerStyle"
        :data-layout="state.layout"
        :data-view="view"
        :data-open="opened ? 'true' : 'false'"
        :style="{ background: ground }"
    >
        <Transition :name="'st-aur-fade'">
            <div
                v-if="state.background === 'aurora' && aurora"
                :key="auroraKey"
                class="gp-aurora"
                :data-aurora-mirror="aurora.mirror"
                :data-aurora-motion="state.auroraMotion ? 'on' : 'off'"
                :style="aurora.vars"
            >
                <div class="gp-aurora-m"><i v-for="(light, index) in aurora.lights" :key="index" :style="light"></i></div>
                <div class="gp-aurora-grain"></div>
            </div>
        </Transition>
        <div class="st-pv-page" :dir="rtl ? 'rtl' : 'ltr'">
            <div v-if="state.headerStyle !== 'banner'" class="st-pv-bar st-pv-first">
                <img v-if="state.logo" class="st-pv-logo" :src="state.logo" alt="" />
                <div class="st-pv-name" :style="{ fontFamily: nameFont }">{{ name }}</div>
                <span class="st-pv-acts"><span class="st-pv-follow" :style="followStyle">{{ follow }}</span><span class="st-pv-share" :title="words.share"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 0L8 7m4-4l4 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg></span></span>
            </div>
            <div v-else class="st-pv-head st-pv-first" :class="{ 'has-stage': !! picture || wall.length > 0 }">
                <div v-if="picture" class="st-pv-stage" :style="{ backgroundImage: cssUrl(picture) }"></div>
                <div v-else-if="wall.length" class="st-pv-wall">
                    <span v-for="(logo, index) in wall" :key="index" :style="{ backgroundImage: cssUrl(logo) }"></span>
                </div>
                <div v-else-if="state.headerImage === 'gradient'" class="st-pv-wash" :style="{ background: wash }"></div>
                <div class="st-pv-body">
                    <div class="st-pv-row">
                        <img v-if="state.logo" class="st-pv-logo" :src="state.logo" alt="" />
                        <span v-else></span>
                        <span class="st-pv-acts"><span class="st-pv-follow" :style="followStyle">{{ follow }}</span><span class="st-pv-share" :title="words.share"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 0L8 7m4-4l4 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg></span></span>
                    </div>
                    <div class="st-pv-name" :style="{ fontFamily: nameFont }">{{ name }}</div>
                </div>
            </div>

            <!-- The list's heading, as the page has it (the 2026-11 redesign): "Upcoming events" and
                 how many, then the List or Calendar switch (the page's Filter is left out: the drawing is
                 too narrow for it). -->
            <div class="st-pv-listhead st-pv-tools-row">
                <span class="st-pv-listname">{{ words.upcoming }}<small v-if="events.length">{{ events.length }}</small></span>
                <span class="st-pv-switch"><i :class="{ 'is-on': state.layout !== 'calendar' }">{{ words.list }}</i><i :class="{ 'is-on': state.layout === 'calendar' }">{{ words.calendar }}</i></span>
            </div>

            <div class="st-pv-list" :class="{ 'is-cards': state.layout !== 'calendar' }">
                <div v-if="state.layout === 'calendar'" class="st-pv-month">
                    <div class="st-pv-month-title">{{ month.title }}</div>
                    <div class="st-pv-grid">
                        <div v-for="(cell, index) in month.cells" :key="index" class="st-pv-cell" :class="{ 'is-out': ! cell.day }">
                            <template v-if="cell.day">{{ cell.day }}<u v-for="mark in cell.marks" :key="mark" :style="{ background: colors.fill }"></u></template>
                        </div>
                    </div>
                </div>
                <template v-else>
                    <!-- The schedule's list as the public page draws it: each day under a heading of
                         its own, then a card an event with its time, its picture, its name and an
                         outlined button in the accent. A card with no event yet keeps grey bars
                         where its words would be. -->
                    <div v-for="(card, index) in cards" :key="index" class="st-pv-each">
                        <div v-if="card.name && (index === 0 || cards[index - 1].date !== card.date)" class="st-pv-day">
                            <span v-if="card.weekday" :style="{ color: colors.readable }">{{ card.weekday }}</span>
                            {{ card.month }} {{ card.day }}
                        </div>
                        <div class="st-pv-card">
                            <bdi v-if="card.time" class="st-pv-clock" dir="ltr">{{ card.time }}</bdi>
                            <div v-if="card.image" class="st-pv-card-img" :style="{ backgroundImage: cssUrl(card.image) }"></div>
                            <div class="st-pv-card-text">
                                <template v-if="card.name">
                                    <div class="st-pv-card-title">{{ card.name }}</div>
                                    <span class="st-pv-go" :style="{ color: colors.readable, borderColor: colors.readable }">{{ words.details }}</span>
                                </template>
                                <span v-else class="st-pv-lines"><i style="width: 70%"></i><i style="width: 45%"></i></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="st-pv-fade"></div>
    </div>

    <div v-if="guestUrl" class="st-pv-foot">
        <a class="event-link st-pv-view" :href="guestUrl" target="_blank" rel="noopener">
            {{ labels.viewSchedule }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
        </a>
        <span v-if="state.dirty" class="st-pv-unsaved">{{ labels.notSaved }}</span>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue';
import { state, theme, cssUrl, fontStack, backgroundCss, headerPicture, toRgb, config, auroraConfig, auroraTokens, previewMode } from '../style/state.js';
import { auroraVars, lightStyles, writeKeyframes, triplet, decode } from '../style/aurora.js';

// The Style tab's preview: a small drawing of the schedule's public page as the fields on the
// tab have it now. The header (the banner card with its picture, accent wash or logo wall, or
// the slim bar of the Compact style), then the schedule's own next events as cards, or this
// month when the layout is Calendar.
//
// It follows the fields, not what is saved; "Not saved yet" stands beside the link to the real
// page while the tab has changes waiting. A visitor's page follows the visitor's own device, so
// the preview has a light and dark switch of its own and leaves the admin's theme alone.
//
// Everything a person typed (the schedule's name, an event's name) is drawn as text. The colours
// of the main button and the icons are the ones the page works out from the accent
// (theme(), a port of GuestTheme), not the accent as typed.

const props = defineProps({
    events: { type: Array, default: () => [] },
    followWords: { type: Object, default: () => ({}) },
    // The page's other words (Share, Details, Upcoming events, List, Calendar) per language.
    pageWords: { type: Object, default: () => ({}) },
    guestUrl: { type: String, default: '' },
    labels: { type: Object, required: true },
});

// Under 1280px the preview is a strip; this opens the whole of it.
const opened = ref(false);

// Which theme the drawing is in: the admin's own until one is chosen here (state.previewMode, which
// the Aurora swatches are drawn in too).
const mode = computed(() => previewMode());
const rtl = computed(() => state.lang === 'he' || state.lang === 'ar');
const name = computed(() => state.name || props.labels.preview);
const nameFont = computed(() => fontStack(state.fontLabel));
const follow = computed(() => props.followWords[state.lang] || props.followWords.en || 'Follow');
const words = computed(() => ({
    share: 'Share', details: 'Details', upcoming: 'Upcoming events', list: 'List', calendar: 'Calendar',
    ...(props.pageWords.en || {}),
    ...(props.pageWords[state.lang] || {}),
}));

const colors = computed(() => {
    const worked = theme(state.accent);

    return mode.value === 'dark'
        ? { fill: worked.fillDark, onFill: worked.onFillDark, readable: worked.readableDark, glow: worked.glow }
        : { fill: worked.fill, onFill: worked.onFill, readable: worked.readable, glow: worked.glow };
});

const followStyle = computed(() => ({ background: colors.value.fill, color: colors.value.onFill }));

// The accent wash of a header with no picture, as the page paints it: the accent as a light.
const wash = computed(() => {
    const [r, g, b] = toRgb(colors.value.glow);
    const light = (alpha) => 'rgb(' + r + ' ' + g + ' ' + b + ' / ' + alpha + ')';

    return 'linear-gradient(to bottom, ' + light(0.44) + ', ' + light(0.17) + ' 38%, ' + light(0) + ')';
});

const picture = computed(() => headerPicture());

// An empty wall draws no header at all, as on the page.
const wall = computed(() => (state.headerStyle === 'banner' && state.headerImage === 'logos' && ! picture.value ? state.logoWall : []));

// Aurora, drawn as the page draws it (partials/aurora-layer), in the preview's own mode. A drift
// of a minute looks still in a box this size, so for six seconds after the row opens or the drift
// is switched on it runs eight times as fast, then settles; never for a visitor who asked their
// system for less motion.
// The speed is the running animations' own rate, so a light carries on from where it is rather
// than jumping to where a new duration would put it.
const speed = ref(1);
let settle = null;

function applySpeed() {
    nextTick(() => {
        document.querySelectorAll('#style-preview > .gp-aurora .gp-aurora-m > i').forEach((light) => {
            if (light.getAnimations) {
                light.getAnimations().forEach((animation) => {
                    animation.playbackRate = speed.value;
                });
            }
        });
    });
}

function showDrift() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    speed.value = 8;
    applySpeed();
    window.clearTimeout(settle);
    settle = window.setTimeout(() => {
        speed.value = 1;
        applySpeed();
    }, 6000);
}

watch(() => state.background === 'aurora' && state.openRow === 'background', (open) => {
    if (open) {
        showDrift();
    }
});
watch(() => state.auroraMotion, (moving) => {
    if (moving && state.background === 'aurora') {
        showDrift();
    }
});
onBeforeUnmount(() => window.clearTimeout(settle));

const aurora = computed(() => {
    const compositions = auroraConfig().compositions;
    if (state.background !== 'aurora' || ! compositions.length) {
        return null;
    }
    writeKeyframes(compositions);
    const t = auroraTokens(state.auroraPalette, mode.value);

    return {
        ground: 'rgb(' + triplet(t.ground) + ')',
        vars: auroraVars(t),
        lights: lightStyles(state.auroraSeed, compositions),
        mirror: String(decode(state.auroraSeed, compositions.length).mirror),
    };
});

// A new arrangement fades in over the old one; a change of colour is simply drawn. The new layer
// starts at normal speed, or at the quick one while that is showing.
const auroraKey = computed(() => 'aurora-' + state.auroraSeed);
watch(auroraKey, applySpeed);

// The ground the page stands on. A built-in picture is drawn from its small copy at once, with
// the full one over it when that has arrived: the small one alone is soft at this size.
const ground = computed(() => {
    if (aurora.value) {
        return aurora.value.ground;
    }
    if (state.background === 'image' && state.backgroundImage) {
        const full = config('backgroundThumbs').replace(/\/thumbs$/, '') + '/' + state.backgroundImage + '.webp';

        return cssUrl(full) + ' center / cover no-repeat, ' + backgroundCss();
    }

    return backgroundCss();
});

// On a narrow screen the strip shows the part the open row changes.
const view = computed(() => (state.openRow === 'animation' ? 'events' : (state.openRow === 'background' ? 'background' : 'header')));

// Up to three of the schedule's own events; two empty cards where it has none yet.
const cards = computed(() => {
    const own = props.events.slice(0, 3);

    return own.length ? own : [{ name: '', image: null, month: '', day: '', weekday: '', time: '' }, { name: '', image: null, month: '', day: '', weekday: '', time: '' }];
});

// The month the first event is in (this month where there is none), as it falls, with the
// schedule's events on their days.
const month = computed(() => {
    const first = props.events[0] && props.events[0].date ? new Date(props.events[0].date + 'T12:00:00') : new Date();
    const year = first.getFullYear();
    const at = first.getMonth();
    let title = '';
    try {
        title = new Intl.DateTimeFormat(state.lang || 'en', { month: 'long', year: 'numeric' }).format(first);
    } catch (error) {
        title = props.events[0] ? props.events[0].month : '';
    }
    const marks = {};
    props.events.forEach((event) => {
        const day = event.date ? new Date(event.date + 'T12:00:00') : null;
        if (day && day.getFullYear() === year && day.getMonth() === at) {
            marks[day.getDate()] = Math.min(2, (marks[day.getDate()] || 0) + 1);
        }
    });
    const cells = [];
    for (let pad = new Date(year, at, 1).getDay(); pad > 0; pad--) {
        cells.push({ day: 0, marks: 0 });
    }
    for (let day = 1, last = new Date(year, at + 1, 0).getDate(); day <= last; day++) {
        cells.push({ day, marks: marks[day] || 0 });
    }

    return { title, cells };
});
</script>
