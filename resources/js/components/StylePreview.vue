<template>
    <div class="st-pv-top">
        <span class="st-pv-title">{{ labels.preview }}</span>
        <span class="st-pv-tools">
            <span class="st-seg" role="group">
                <button type="button" :title="labels.light" :aria-label="labels.light" :aria-pressed="mode === 'light' ? 'true' : 'false'" @click="chosenMode = 'light'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                </button>
                <button type="button" :title="labels.dark" :aria-label="labels.dark" :aria-pressed="mode === 'dark' ? 'true' : 'false'" @click="chosenMode = 'dark'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>
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
        <div class="st-pv-page" :dir="rtl ? 'rtl' : 'ltr'">
            <div v-if="state.headerStyle !== 'banner'" class="st-pv-bar st-pv-first">
                <img v-if="state.logo" class="st-pv-logo" :src="state.logo" alt="" />
                <div class="st-pv-name" :style="{ fontFamily: nameFont }">{{ name }}</div>
                <span class="st-pv-follow" :style="followStyle">{{ follow }}</span>
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
                        <span class="st-pv-follow" :style="followStyle">{{ follow }}</span>
                    </div>
                    <div class="st-pv-name" :style="{ fontFamily: nameFont }">{{ name }}</div>
                    <div class="st-pv-facts"><i style="width: 34%"></i><i style="width: 22%"></i></div>
                </div>
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
                    <div v-if="cards[0].name" class="st-pv-day">{{ cards[0].month }} {{ cards[0].day }}</div>
                    <div v-for="(card, index) in cards" :key="index" class="st-pv-card">
                        <div class="st-pv-card-text">
                            <div v-if="card.name" class="st-pv-card-title">{{ card.name }}</div>
                            <span v-else class="st-pv-lines"><i style="width: 70%"></i><i style="width: 45%"></i></span>
                            <div class="st-pv-when">
                                <span class="st-pv-date"><small>{{ card.month }}</small><b>{{ card.day }}</b></span>
                                <span class="st-pv-icon" :style="{ color: colors.readable }">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z" />
                                    </svg>
                                </span>
                                <span class="st-pv-lines"><i style="width: 80%"></i><i style="width: 50%"></i></span>
                            </div>
                        </div>
                        <div v-if="card.image" class="st-pv-card-img" :style="{ backgroundImage: cssUrl(card.image) }"></div>
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
import { ref, computed } from 'vue';
import { state, theme, cssUrl, fontStack, backgroundCss, headerPicture, toRgb, config } from '../style/state.js';

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
    guestUrl: { type: String, default: '' },
    labels: { type: Object, required: true },
});

// Which theme the drawing is in: the admin's own until one is chosen here.
const chosenMode = ref('');
// Under 1280px the preview is a strip; this opens the whole of it.
const opened = ref(false);

const mode = computed(() => chosenMode.value || (state.dark ? 'dark' : 'light'));
const rtl = computed(() => state.lang === 'he' || state.lang === 'ar');
const name = computed(() => state.name || props.labels.preview);
const nameFont = computed(() => fontStack(state.fontLabel));
const follow = computed(() => props.followWords[state.lang] || props.followWords.en || 'Follow');

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

// The ground the page stands on. A built-in picture is drawn from its small copy at once, with
// the full one over it when that has arrived: the small one alone is soft at this size.
const ground = computed(() => {
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

    return own.length ? own : [{ name: '', image: null, month: '', day: '' }, { name: '', image: null, month: '', day: '' }];
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
