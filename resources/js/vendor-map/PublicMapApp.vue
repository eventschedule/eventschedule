<!--
  The visitors' map of a market, fair or expo (/{schedule}/map): the page a shopper opens from the
  QR at the entrance or a vendor's "Share my stall".

  Everything arrives in the page (vendor-map/public.blade.php writes it as JSON beside the mount):
  a market's shoppers share one mobile network, so a fetch per visitor would meet a rate limit.
  The server also prints the whole list and the map before this starts, in the same classes, so
  the page is useful on a slow phone and to a screen reader from the first byte; this replaces
  that markup with the same layout, and adds search, the category chips, zoom and the cards.

  The map is the market as blocks of equal stalls (SchematicMap.vue, VendorMapSchematic on the
  server). A laptop shows it whole. A phone shows a big market area by area (AreaMap.vue): the
  areas first, then one area close up, scrolled and pinched. On a phone it sits under the header and the
  search and the list follow; from a laptop up the list is a panel at the start side and the map
  stands beside it. The list is FIRST in the document in both: the
  phone puts the map on top with CSS order, so reading order and tab order start at the search.
-->
<template>
  <div class="vm-page" :class="{ 'is-phone': phone }">
    <header class="vm-head">
      <a :href="boot.back.url" class="vm-back">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" :style="rtl ? 'transform: scaleX(-1)' : null"><path d="M15 5l-7 7 7 7" /></svg>
        <bdi>{{ boot.back.label }}</bdi>
      </a>
      <p v-if="map.dateLabel && boot.status !== 'closed'" class="gk-dateline vm-dateline">{{ boot.dateText }}</p>
      <h1 class="vm-title"><bdi>{{ map.name }}</bdi></h1>
      <div v-if="boot.closed" class="gk-note gk-note-warn vm-closed" role="status">
        <svg class="gk-note-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" /></svg>
        <div class="gk-note-body">
          <p class="vm-closed-title">{{ boot.closed.title }}</p>
          <p v-if="boot.closed.next">{{ boot.closed.next }}</p>
        </div>
      </div>
    </header>

    <section class="vm-side" :aria-label="t.vendors">
      <div v-if="rows.length" ref="sheetHead" class="vm-sheethead">
        <div class="vm-search">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
          <input ref="searchEl" v-model="query" type="search" class="vm-search-input" :placeholder="t.search" :aria-label="t.searchLabel"
                 :aria-describedby="'vm-results'" autocomplete="off" enterkeyhint="search" @keydown.esc="query = ''">
          <button v-if="query" type="button" class="vm-search-clear" :aria-label="t.clear" @click="clearSearch">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
          </button>
        </div>
        <div v-if="chips.length > 1" class="vm-chips" role="group" :aria-label="t.categories">
          <button type="button" class="gk-chip vm-chip" :aria-pressed="category === '' ? 'true' : 'false'" @click="category = ''">{{ t.all }}</button>
          <button v-for="c in chips" :key="c.slug" type="button" class="gk-chip vm-chip" :aria-pressed="category === c.slug ? 'true' : 'false'" @click="toggleCategory(c.slug)">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-for="(d, i) in c.icon" :key="i" :d="d" /></svg>
            {{ c.label }}
          </button>
        </div>
      </div>
      <p id="vm-results" class="vm-sr" aria-live="polite" aria-atomic="true">{{ announced }}</p>

      <PublicVendorCard v-if="selectedRow" ref="card" :key="selectedRow.id" class="vm-card-slot" :row="selectedRow" :category-labels="map.categories"
                        :area-name="areaName(selectedRow.area)" :area-color="areaColor(selectedRow.area)" :t="t" :rtl="rtl" @close="closeCard" />

      <template v-if="rows.length">
        <h2 class="vm-listtitle">{{ boot.listTitle }}</h2>
        <template v-if="visibleGroups.length">
          <div v-for="g in visibleGroups" :key="g.key" class="vm-group">
            <h3 v-if="showGroupNames" class="vm-group-name">
              <i class="vm-dot" :style="{ '--area': g.color || null }" aria-hidden="true"></i>
              <bdi>{{ g.name }}</bdi>
            </h3>
            <ul class="vm-list" role="list">
              <li v-for="r in g.rows" :key="r.id">
                <button type="button" class="vm-row" :class="{ 'is-absent': r.state === 'absent', 'is-on': String(selectedId) === String(r.id) }"
                        :aria-current="String(selectedId) === String(r.id) ? 'true' : null" :data-row="r.id" @click="openFromList(r, $event)">
                  <span class="vm-row-num" :style="{ '--area': areaColor(r.area) || null }" dir="ltr" aria-hidden="true">{{ r.label }}</span>
                  <img v-if="r.vendor.url && r.vendor.photo" class="vm-row-pic" :src="r.vendor.photo" alt="" loading="lazy" decoding="async" width="40" height="40">
                  <span v-else class="vm-row-pic vm-row-initials" aria-hidden="true">{{ initials(r.vendor.name) }}</span>
                  <span class="vm-row-text">
                    <span class="vm-row-name"><span class="vm-sr">{{ (t.spot || ':number').replace(':number', r.label) }}, </span><bdi>{{ r.vendor.name }}</bdi></span>
                    <span v-if="r.state === 'absent'" class="vm-row-sub vm-row-absent">{{ absentText(r) }}</span>
                    <span v-else-if="rowSub(r)" class="vm-row-sub"><bdi>{{ rowSub(r) }}</bdi></span>
                  </span>
                </button>
              </li>
            </ul>
          </div>
        </template>
        <div v-else class="vm-none">
          <p>{{ t.noResults }}</p>
          <button type="button" class="gk-btn gk-btn-secondary gk-btn-sm" @click="clearAll">{{ t.clearSearch }}</button>
        </div>
      </template>
      <div v-else class="vm-empty">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9.5 5 4h14l2 5.5M3 9.5h18M3 9.5a3 3 0 0 0 6 0m0 0a3 3 0 0 0 6 0m0 0a3 3 0 0 0 6 0M5 12.5V20h14v-7.5M10 20v-4h4v4" /></svg>
        <p>{{ boot.emptyText }}</p>
      </div>
    </section>

    <section class="vm-mapwrap" :aria-label="t.map">
      <!-- A phone shows a big market area by area; a laptop has room for all of it. -->
      <AreaMap v-if="phone && layered" class="vm-schematic" :schematic="boot.schematic" :selected-id="selectedId" :focus-tick="focusTick" :highlight-ids="highlightIds"
               :show-here="boot.showHere" :here-label="t.here" :rtl="rtl" :spot-label="spotAriaById" :selectable="selectableId" :t="t"
               @select="openFromMap" />
      <SchematicMap v-else class="vm-schematic" :schematic="boot.schematic" :selected-id="selectedId" :highlight-ids="highlightIds"
                    :show-here="boot.showHere" :here-label="t.here" :rtl="rtl" :spot-label="spotAriaById" :selectable="selectableId"
                    @select="openFromMap" />
    </section>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SchematicMap from './SchematicMap.vue';
import AreaMap from './AreaMap.vue';
import PublicVendorCard from './PublicVendorCard.vue';
import { categoryIcon } from './MapCanvasIcons';

const props = defineProps({
    boot: { type: Object, required: true },
});

const map = props.boot.map;
const t = props.boot.t;
const rtl = !!props.boot.rtl;

const card = ref(null);
const searchEl = ref(null);
const sheetHead = ref(null);

// More than one area, or more stalls than read as numbers at a phone's width: areas first.
const layered = (() => {
    const zones = [];
    (props.boot.schematic?.sections || []).forEach((sec) => {
        if (sec.type === 'full') zones.push(sec.zone);
        else if (sec.type === 'stack') zones.push(...sec.zones);
        else zones.push(...sec.a, ...sec.b);
    });
    const stalls = zones.reduce((n, z) => n + z.cells.filter(Boolean).length, 0);
    return zones.length > 1 || stalls > 30;
})();

const query = ref('');
const category = ref('');
const selectedId = ref(props.boot.selected ?? null);

const hasVendor = (s) => !!s.vendor && (s.state === 'present' || s.state === 'absent');

const areaById = {};
(map.areas || []).forEach((a) => { areaById[a.id] = a; });
const areaName = (id) => areaById[id]?.name || '';
const areaColor = (id) => areaById[id]?.color || '';

const byNumber = (a, b) => String(a.label).localeCompare(String(b.label), undefined, { numeric: true, sensitivity: 'base' });

/** Every stall with somebody on it (here, or not here this time): never an empty one. */
const rows = (map.spots || []).filter(hasVendor).slice().sort(byNumber);

/**
 * Lower case, no Hebrew points or Latin accents, Hebrew final letters as their ordinary form and
 * no geresh or quotes: so "לחם" finds "לחמים" and "creme" finds "Crème".
 */
const FINALS = { 'ך': 'כ', 'ם': 'מ', 'ן': 'נ', 'ף': 'פ', 'ץ': 'צ' };
function norm(text) {
    return String(text || '')
        .normalize('NFKD')
        .replace(/[̀-֑ͯ-ׇ]/g, '')
        .toLocaleLowerCase()
        .replace(/[ךםןףץ]/g, (c) => FINALS[c])
        .replace(/[׳״'"`.\-]/g, '')
        .trim();
}

const haystacks = new Map(rows.map((r) => [r.id, norm([
    r.vendor.name,
    r.vendor.sells,
    ...(r.vendor.categories || []).map((c) => map.categories[c] || ''),
].join(' '))]));

const matches = computed(() => {
    const words = norm(query.value).split(/\s+/).filter(Boolean);
    return rows.filter((r) => {
        if (category.value && !(r.vendor.categories || []).includes(category.value)) return false;
        if (!words.length) return true;
        const hay = haystacks.get(r.id);
        const number = norm(r.label);
        return words.every((w) => hay.includes(w) || number === w || (/^\d/.test(w) && number.startsWith(w)));
    });
});

const filtering = computed(() => !!(category.value || norm(query.value)));
const highlightIds = computed(() => (filtering.value ? matches.value.map((r) => r.id) : null));

const showGroupNames = (map.areas || []).length > 0;
const visibleGroups = computed(() => {
    const groups = [];
    const keyed = {};
    (map.areas || []).forEach((a) => {
        keyed[a.id] = { key: 'a' + a.id, name: a.name, color: a.color, rows: [] };
        groups.push(keyed[a.id]);
    });
    const rest = { key: 'rest', name: t.otherArea, color: null, rows: [] };
    matches.value.forEach((r) => (keyed[r.area] || rest).rows.push(r));
    groups.push(rest);
    return groups.filter((g) => g.rows.length);
});

/** The categories somebody here today sells, in the kind's own order. */
const chips = Object.keys(map.categories || {})
    .filter((slug) => rows.some((r) => r.state === 'present' && (r.vendor.categories || []).includes(slug)))
    .map((slug) => ({ slug, label: map.categories[slug], icon: categoryIcon(slug) }));

function toggleCategory(slug) {
    category.value = category.value === slug ? '' : slug;
}

const selectedRow = computed(() => rows.find((r) => String(r.id) === String(selectedId.value)) || null);

function initials(name) {
    const words = String(name || '').trim().split(/\s+/).filter(Boolean);
    return words.slice(0, 2).map((w) => Array.from(w)[0] || '').join('').toLocaleUpperCase();
}

function rowSub(r) {
    const cats = (r.vendor.categories || []).map((c) => map.categories[c]).filter(Boolean).join(', ');
    return [cats, r.vendor.sells].filter(Boolean).join(' · ');
}

function absentText(r) {
    return r.vendor.next ? t.absentNext.replace(':date', r.vendor.next) : t.absent;
}

function spotAria(s) {
    const parts = [(t.spot || ':number').replace(':number', s.label)];
    if (s.vendor) {
        parts.push(s.vendor.name);
        const sub = rowSub(s);
        if (sub) parts.push(sub);
        if (s.state === 'absent') parts.push(absentText(s));
    }
    return parts.join(', ');
}

const spotById = new Map((map.spots || []).map((sp) => [String(sp.id), sp]));
const spotAriaById = (id) => { const sp = spotById.get(String(id)); return sp ? spotAria(sp) : String(id); };
const selectableId = (id) => { const sp = spotById.get(String(id)); return !!sp && hasVendor(sp); };


/* What a screen reader hears after a search or a chip: "12 results". */
const announced = ref('');
let announceTimer = null;
watch(matches, (list) => {
    clearTimeout(announceTimer);
    if (!filtering.value) { announced.value = ''; return; }
    announceTimer = setTimeout(() => {
        const n = list.length;
        announced.value = (props.boot.results && props.boot.results[n]) || String(n);
    }, 450);
});

/* The address keeps the stall that is open, so the page can be shared as it is. */
function rememberInAddress(row) {
    try {
        const url = new URL(window.location.href);
        if (row) url.searchParams.set('s', row.label); else url.searchParams.delete('s');
        window.history.replaceState(window.history.state, '', url.toString());
    } catch (e) { /* an address we cannot rewrite is still the right page */ }
}

let returnTo = null;
const reduced = typeof window !== 'undefined' && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function bringCardIntoView() {
    nextTick(() => {
        const el = card.value?.$el;
        if (!el) return;
        // The sticky search bar is all that sits over the page now (the map scrolls with it).
        const offset = phone.value ? (sheetHead.value?.offsetHeight || 0) + 8 : 16;
        const top = el.getBoundingClientRect().top;
        if (top >= offset && top < window.innerHeight * 0.7) return;
        window.scrollTo({ top: Math.max(0, top + window.scrollY - offset), behavior: reduced ? 'auto' : 'smooth' });
    });
}

function openFromMap(id) {
    const row = rows.find((r) => String(r.id) === String(id));
    if (!row) return;
    returnTo = null;
    selectedId.value = row.id;
    rememberInAddress(row);
    bringCardIntoView();
}

// Bumped on every tap in the list: the same row tapped again after "All areas" opens its area.
const focusTick = ref(0);

function openFromList(row, evt) {
    returnTo = evt?.currentTarget || null;
    selectedId.value = row.id;
    focusTick.value++;
    rememberInAddress(row);
    nextTick(() => card.value?.focus());
    bringCardIntoView();
}

function closeCard() {
    if (selectedId.value == null) return;
    const back = returnTo;
    selectedId.value = null;
    returnTo = null;
    rememberInAddress(null);
    if (back && document.contains(back)) nextTick(() => back.focus());
}

function clearSearch() {
    query.value = '';
    searchEl.value?.focus();
}

function clearAll() {
    category.value = '';
    clearSearch();
}

/* ---- A phone or a laptop ---- */
const PHONE = '(max-width: 63.9375rem)';
const mq = typeof window !== 'undefined' && window.matchMedia ? window.matchMedia(PHONE) : null;
const phone = ref(mq ? mq.matches : false);

function onMedia(e) { phone.value = e.matches; }

onMounted(() => {
    mq?.addEventListener?.('change', onMedia);
});

onBeforeUnmount(() => {
    mq?.removeEventListener?.('change', onMedia);
    clearTimeout(announceTimer);
});
</script>
