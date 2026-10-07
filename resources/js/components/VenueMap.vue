<template>
  <section id="gp-map" class="gk-panel gk-panel-flush gk-map bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm" :aria-label="t.map" :dir="rtl ? 'rtl' : 'ltr'">
    <!-- Closed, the whole band opens the map - unless the press has to be the one beside the
         sentence that says who will see the visitor's IP address. Open, only its buttons act. -->
    <div class="gk-map-band" :class="{ 'is-open': inline, 'is-ask': asking }" @click="bandClick" @pointerenter="load" @touchstart.passive="load" @focusin="load">
      <span class="gk-map-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
      </span>
      <h2 class="gk-map-title">{{ t.map }}</h2>
      <p v-if="asking" :id="askId" class="gk-map-sub gk-map-sub-ask">
        {{ t.streets_note }}
        <button type="button" class="gk-link gk-map-plain" @click.stop="open(false)">{{ t.open_plain }}</button>
      </p>
      <p v-else class="gk-map-sub">{{ townsLine }}</p>
      <ul class="gk-map-stack" aria-hidden="true">
        <li v-for="(logo, i) in band.logos" :key="logo" :class="{ 'gk-map-wide': i > 2 }"><img :src="logo" alt="" loading="lazy" @load="fit($event)"></li>
        <li v-if="rest > 0" class="gk-map-more gk-map-wide" dir="ltr">+{{ rest }}</li>
        <li v-if="restNarrow > 0" class="gk-map-more gk-map-narrow" dir="ltr">+{{ restNarrow }}</li>
      </ul>
      <div class="gk-map-acts">
        <button v-if="inline" ref="grow" type="button" class="gk-btn gk-btn-secondary gk-btn-sm gk-map-toggle" @click.stop="grow">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4H4v5M15 4h5v5M9 20H4v-5M15 20h5v-5" /></svg>
          {{ t.larger }}
        </button>
        <button ref="toggle" type="button" class="gk-btn gk-btn-secondary gk-btn-sm gk-map-toggle" :aria-expanded="isOpen ? 'true' : 'false'" :aria-describedby="asking ? askId : null" @click.stop="toggle">
          {{ inline ? t.hide_map : t.show_map }}
        </button>
      </div>
    </div>

    <!-- The full-window map: the phone's only map, and the laptop's larger one. On the body, not
         in here: this panel has a backdrop-filter, which would make it the containing block of
         anything fixed. It comes BEFORE the body below in this template on purpose: a Teleport
         looks its target up when it is mounted, so the slot has to be in the document by then. -->
    <Teleport to="body">
      <div v-show="sheet" id="gp-map-sheet" ref="sheet" class="gk-map-sheet" role="dialog" aria-modal="true" :aria-label="t.map" :dir="rtl ? 'rtl' : 'ltr'">
        <div class="gk-map-sheetbar">
          <h2><bdi>{{ name }}</bdi></h2>
          <button ref="close" type="button" class="gk-btn gk-btn-quiet gk-btn-icon" :aria-label="t.close" @click="close">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" /></svg>
          </button>
        </div>
        <div id="es-venue-map-slot" class="gk-map-sheetslot"></div>
      </div>
    </Teleport>
    <!-- One body, in the page or in the sheet. Teleport MOVES it between the two, so the map
         underneath is the same map and keeps its place. -->
    <Teleport to="#es-venue-map-slot" :disabled="!sheet">
      <div v-show="isOpen" class="gk-map-body" :dir="rtl ? 'rtl' : 'ltr'">
        <div class="gk-map-canvas">
          <div ref="view" class="gk-map-view" :class="{ 'has-streets': streetsShown }">
            <!-- No bound class here: Leaflet writes its own classes on this element, and a class
                 Vue binds would replace them all on its next change. -->
            <div ref="leaflet" class="gk-map-leaflet"></div>
            <p v-if="loading || failed" class="gk-map-wait" role="status">
              {{ failed ? t.failed : t.loading }}
              <button v-if="failed" type="button" class="gk-link gk-map-plain" @click="retry">{{ t.try_again }}</button>
            </p>
            <p v-else-if="empty" class="gk-map-wait" role="status">{{ t.none }}</p>
            <div v-if="askOnMap" class="gk-map-askcard">
              <p :id="askId + '-map'">{{ t.streets_note }}</p>
              <button type="button" class="gk-btn gk-btn-secondary gk-btn-sm gk-map-toggle" :aria-describedby="askId + '-map'" @click="showStreets">{{ t.show_streets }}</button>
            </div>
          </div>
        </div>

        <aside ref="side" class="gk-map-side" @mouseenter="syncFilter" @focusin="syncFilter" @touchstart.passive="syncFilter">
          <template v-if="!current">
            <div class="gk-map-when" role="group">
              <button v-for="w in ['all', 'today', 'week']" :key="w" type="button" class="gk-chip gk-map-whenchip" :aria-pressed="when === w ? 'true' : 'false'" @click="setWhen(w)">{{ t['when_' + w] }}</button>
            </div>
            <p v-if="hasQuiet" class="gk-map-legend"><i></i>{{ t['dots_' + when] }}</p>
            <button v-if="followed" type="button" class="gk-btn gk-btn-quiet gk-btn-sm gk-map-back" @click="whole">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" :style="rtl ? 'transform:scaleX(-1)' : null"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5l-7 7 7 7" /></svg>
              {{ t.whole_map }}
            </button>
            <template v-for="group in groups" :key="group.town">
              <h3 class="gk-map-sidehead">
                <button v-if="group.pinned" type="button" class="gk-map-townbtn" @click="zoomTown(group.town)">
                  <bdi>{{ group.town || t.venues }}</bdi>
                  <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="12" cy="12" r="6" /><path stroke-linecap="round" d="M12 2v4M12 18v4M2 12h4M18 12h4" /></svg>
                </button>
                <bdi v-else>{{ group.town || t.venues }}</bdi>
              </h3>
              <ul class="gk-map-list">
                <li v-for="v in group.venues" :key="v.key">
                  <button type="button" class="gk-map-item" :class="{ 'is-hot': hotKey === v.key }" :data-venue="v.key" @click="select(v.key)" @mouseenter="hot(v.key, true)" @mouseleave="hot(v.key, false)">
                    <span class="gk-map-face"><img v-if="v.logo && !v.wide" :src="v.logo" alt="" loading="lazy" @load="fit($event, v)"><template v-else>{{ initial(v) }}</template></span>
                    <span class="gk-map-itemtext">
                      <strong><bdi>{{ v.name }}</bdi></strong>
                      <small v-if="v.next"><span dir="auto">{{ v.next.when }}</span> · <bdi>{{ v.next.name }}</bdi></small>
                      <small v-else>{{ v.upcoming ? '' : t.nothing }}</small>
                    </span>
                    <span v-if="v.lat === null" class="gk-map-nopinmark">{{ t.no_pin }}</span>
                  </button>
                </li>
              </ul>
            </template>
            <!-- "Nothing then" only when a day filter is what emptied the list: panned away from
                 every venue, the Whole map row above already says where they are. -->
            <p v-if="venues && !groups.length && !followed" class="gk-map-nopin gk-map-empty">{{ when === 'all' ? t.none : t.none_when }}</p>
          </template>

          <template v-else>
            <div class="gk-map-venue">
              <button type="button" class="gk-btn gk-btn-quiet gk-btn-sm gk-map-back" @click="back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" :style="rtl ? 'transform:scaleX(-1)' : null"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5l-7 7 7 7" /></svg>
                {{ t.venues }}
              </button>
              <div class="gk-map-who">
                <span class="gk-map-face gk-map-face-lg"><img v-if="current.logo" :key="current.key" :src="current.logo" alt="" @load="fit($event)"><template v-else>{{ initial(current) }}</template></span>
                <h3 ref="name" tabindex="-1"><bdi>{{ current.name }}</bdi></h3>
              </div>
              <p class="gk-map-addr"><bdi>{{ current.address }}</bdi><span v-if="current.approx" class="gk-chip">{{ t.approx }}</span></p>
              <p v-if="current.directions || current.url" class="gk-map-links">
                <a v-if="current.directions" class="gk-link" target="_blank" rel="noopener noreferrer" :href="current.directions">{{ t.directions }}
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-8 8M11 7H6v11h11v-5" /></svg>
                </a>
                <a v-if="current.url" class="gk-link" :href="current.url">{{ t.venue_page }}</a>
              </p>
              <p v-if="current.lat === null" class="gk-map-nopin">{{ current.why === 'no_address' ? t.no_street : t.not_found }}</p>
              <p class="gk-map-label">{{ t.coming_up }}</p>
              <ul v-if="current.events.length" class="gk-map-next">
                <li v-for="e in current.events" :key="e.url">
                  <a :href="e.url" data-funnel="list_tap"><small dir="auto">{{ e.when }}</small><b class="gk-link" :dir="e.dir">{{ e.name }}</b></a>
                </li>
              </ul>
              <p v-else-if="!current.more" class="gk-map-nopin">{{ t.nothing }}</p>
              <template v-if="nearby.length">
                <p class="gk-map-label gk-map-label-gap">{{ t.nearby }}</p>
                <ul class="gk-map-list gk-map-list-flush">
                  <li v-for="n in nearby" :key="n.v.key">
                    <button type="button" class="gk-map-item" @click="select(n.v.key)">
                      <span class="gk-map-face"><img v-if="n.v.logo && !n.v.wide" :src="n.v.logo" alt="" loading="lazy" @load="fit($event, n.v)"><template v-else>{{ initial(n.v) }}</template></span>
                      <span class="gk-map-itemtext">
                        <strong><bdi>{{ n.v.name }}</bdi></strong>
                        <small><span dir="ltr">{{ n.far }}</span><template v-if="n.v.next"> · <span dir="auto">{{ n.v.next.when }}</span></template></small>
                      </span>
                    </button>
                  </li>
                </ul>
              </template>
            </div>
            <div v-if="filtered === current.key || (current.more && listed[current.key])" class="gk-map-cta">
              <button v-if="filtered === current.key" type="button" class="gk-btn gk-btn-secondary gk-btn-block" @click="clearFilter">{{ t.clear_filter }}</button>
              <button v-else type="button" class="gk-btn gk-btn-primary gk-btn-block" @click="seeAll">{{ t.see_all }}</button>
            </div>
          </template>
        </aside>
      </div>
    </Teleport>

  </section>
</template>

<script>
import { CONSENT_EVENT, hasConsent } from '../cookie-consent';
import { loadLeaflet } from '../leaflet-loader';

/**
 * The venue map on a schedule's guest page: a band that opens a map of the schedule's venues,
 * each with its logo on its pin, beside a list of them and a panel for the one that is chosen.
 *
 * WHO IS ASKED FOR WHAT. The venues, their positions and their names are this install's own data
 * (GET /api/venue-map) and are drawn at once. The street images under them come from a third
 * party (props.tiles), which sees the visitor's IP address, so they load only with the marketing
 * cookie category granted, or after a press on a control that says so where it is pressed: Show
 * map beside the sentence on the band, or Show streets on the map. The same rule as ConsentEmbed,
 * and like it the press is not remembered, and withdrawing consent takes the streets away.
 *
 * Mounted on an empty host by venue-map-boot.js with every value as a prop, and everything a
 * venue's owner typed is printed by Vue as text or built with textContent: nothing here is ever
 * compiled as a template or assigned as HTML.
 *
 * The list of events below the map is not this component's, and nothing in it was changed for the
 * map. "See all events here" sets the list's OWN venue filter (window.calendarVueApp.selectedVenue,
 * the field its Venue select is bound to) and NO other filter, and is offered only for a venue
 * that has an event passing the filters the visitor already has, so the press never ends on an
 * empty list. Everything read from the list is read through list(), which answers null unless
 * the list still has that shape; VenueMapPageTest fails the build if one of those names leaves
 * role/partials/calendar.
 *
 * HISTORY. Only the full-window map owns a history entry, and only one: pushed when it opens (a
 * phone, or Larger map), so Back closes it. Everything else REWRITES the current entry
 * (replaceState, keeping whatever state is on it): opening the map in the page, choosing a venue,
 * going back to the venues, hiding. The list has entries of its own - it pushes one on a month
 * change and rewrites the current one when a filter changes - and a map that pushed and then
 * travelled back through its own steps took the list's month and filters back with it. So the
 * map in the page never travels. The address still says #gp-map or #gp-map/<venue>, which is
 * what lets a venue be linked and Back from an event page return to it.
 */

const HASH = '#gp-map';

export default {
    name: 'VenueMap',
    props: {
        url: { type: String, required: true },
        schedule: { type: String, default: '' },
        name: { type: String, default: '' },
        rtl: { type: Boolean, default: false },
        miles: { type: Boolean, default: false },
        startsOpen: { type: Boolean, default: false },
        band: { type: Object, required: true },
        tiles: { type: Object, default: null },
        credit: { type: String, default: '' },
        creditUrl: { type: String, default: null },
        assets: { type: Object, required: true },
        t: { type: Object, required: true },
    },
    data() {
        return {
            venues: null,
            loading: false,
            failed: false,
            isOpen: false,
            big: false,
            phone: window.matchMedia('(max-width: 47.9375rem)').matches,
            consent: hasConsent('marketing'),
            optedIn: false,
            streetsShown: false,
            selected: null,
            when: 'all',
            followed: false,
            inView: null,
            filtered: '',
            listed: {},
            // No venue in the answer has a position: said in words, and no map is built.
            empty: false,
            hotKey: null,
            askId: 'gp-map-ask',
        };
    },
    computed: {
        sheet() {
            return this.isOpen && (this.phone || this.big);
        },
        inline() {
            return this.isOpen && !this.sheet;
        },
        streetsWanted() {
            return !!this.tiles && (this.consent || this.optedIn);
        },
        // The band carries the sentence while pressing Show map would be the visitor's choice.
        asking() {
            return !!this.tiles && !this.streetsWanted && !this.isOpen;
        },
        askOnMap() {
            return this.isOpen && !!this.tiles && !this.streetsWanted && !this.loading && !this.failed;
        },
        rest() {
            return this.band.total - this.band.logos.length;
        },
        restNarrow() {
            return this.band.total - Math.min(3, this.band.logos.length);
        },
        townsLine() {
            return this.band.towns.join(' · ') + (this.band.more_towns ? ' ' + this.t.and_more : '');
        },
        current() {
            return this.selected && this.venues ? this.venues.find((v) => v.key === this.selected) || null : null;
        },
        hasQuiet() {
            return !!this.venues && this.venues.some((v) => v.lat !== null && !this.active(v));
        },
        // Venues by town: towns with something on first, then by name; within a town, soonest first
        // (the order the server sent them in). After a zoom or a pan, only the venues in view.
        groups() {
            if (!this.venues) {
                return [];
            }

            const shown = this.venues.filter((v) => {
                if (this.when !== 'all' && !this.active(v)) {
                    return false;
                }

                return !this.followed || (v.lat !== null && this.inView && this.inView[v.key]);
            });

            const byTown = new Map();
            shown.forEach((v) => {
                if (!byTown.has(v.town)) {
                    byTown.set(v.town, []);
                }
                byTown.get(v.town).push(v);
            });

            return Array.from(byTown.entries())
                .map(([town, venues]) => ({
                    town,
                    venues: venues.slice().sort((a, b) => (a.next ? 0 : 1) - (b.next ? 0 : 1)),
                    pinned: venues.some((v) => v.lat !== null),
                    live: venues.some((v) => v.next || v.upcoming),
                }))
                .sort((a, b) => (a.live ? 0 : 1) - (b.live ? 0 : 1) || a.town.localeCompare(b.town));
        },
        nearby() {
            const from = this.current;

            if (!from || from.lat === null) {
                return [];
            }

            return this.venues
                .filter((v) => v.key !== from.key && v.lat !== null && (v.next || v.upcoming))
                .map((v) => ({ v, km: this.km(from, v) }))
                .sort((a, b) => a.km - b.km)
                .slice(0, 2)
                .map((n) => ({ v: n.v, far: this.far(n.km) }));
        },
    },
    watch: {
        // The page behind a full-window map does not scroll, as behind the app's other overlays.
        sheet(on) {
            document.body.style.overflow = on ? 'hidden' : '';
            this.$nextTick(() => {
                // Closed, the map is not on the screen and has no size: fitted then, the venues
                // were "fitted" to a box of nothing, at street zoom on one spot, and that view was
                // remembered as the whole map.
                if (!this.isOpen) {
                    return;
                }

                this.resize();

                // More room, or less: the venues are fitted to it again, unless the visitor has
                // chosen a venue or moved the map, which is theirs to keep.
                if (this.map && !this.selected && !this.followed) {
                    this.fitAll();
                    this.buildMarkers();
                    this.placeLabels();
                }

                if (on) {
                    this.$refs.close?.focus();
                }
            });
        },
        streetsWanted(on) {
            on ? this.addTiles() : this.removeTiles();
        },
    },
    mounted() {
        this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches || this.widgetReducesMotion();
        this.markers = {};
        this.phoneQuery = window.matchMedia('(max-width: 47.9375rem)');
        this.onPhone = () => {
            this.phone = this.phoneQuery.matches;
            this.$nextTick(this.resize);
        };
        // The older call too, as the list's own code keeps it: a browser without the newer one
        // would throw here, and the band would never be drawn.
        if (this.phoneQuery.addEventListener) {
            this.phoneQuery.addEventListener('change', this.onPhone);
        } else if (this.phoneQuery.addListener) {
            this.phoneQuery.addListener(this.onPhone);
        }
        document.addEventListener(CONSENT_EVENT, this.onConsent);
        document.addEventListener('keydown', this.onKey);
        window.addEventListener('popstate', this.fromAddress);
        window.addEventListener('pageshow', this.onPageShow);

        // The list's Calendar / List switch changes the width of the column the map stands in,
        // and Leaflet only hears of the WINDOW changing size.
        if (window.ResizeObserver) {
            this.sizeWatch = new ResizeObserver(() => this.resize());
            this.sizeWatch.observe(this.$refs.view);
        }

        // A map on arrival is for visitors who have allowed cookies: the others would land on
        // pins with no streets, so they get the band and its one press. A phone always starts
        // with the band. A visitor who hid it stays hidden.
        if (this.hashState()) {
            this.fromAddress();
        } else if (this.startsOpen && !this.phone && (this.consent || !this.tiles) && !this.hid()) {
            this.show(true);
        }
    },
    beforeUnmount() {
        if (this.phoneQuery.removeEventListener) {
            this.phoneQuery.removeEventListener('change', this.onPhone);
        } else if (this.phoneQuery.removeListener) {
            this.phoneQuery.removeListener(this.onPhone);
        }
        document.removeEventListener(CONSENT_EVENT, this.onConsent);
        document.removeEventListener('keydown', this.onKey);
        window.removeEventListener('popstate', this.fromAddress);
        window.removeEventListener('pageshow', this.onPageShow);
        this.sizeWatch?.disconnect();
        clearTimeout(this.labelTimer);
        clearTimeout(this.followTimer);
        clearTimeout(this.closeTimer);
        clearInterval(this.listTimer);
        document.body.style.overflow = '';
        this.map?.remove();
    },
    methods: {
        // ---------- what a venue is ----------
        active(v) {
            if (this.when === 'today') {
                return v.soon === 'today';
            }

            return this.when === 'week' ? !!v.soon : !!(v.next || v.upcoming);
        },
        initial(v) {
            return Array.from(v.name || '?')[0] || '?';
        },
        // A logo fills its disc when it is roughly square; a wider or taller one is fitted whole
        // on white, and one too wide to read at pin size gives way to the venue's initial.
        // A logo fills its disc when it is roughly square; a wider or taller one is fitted whole
        // on white, and one too wide to read at pin size gives way to the venue's initial. Read
        // from the picture itself as it arrives, wherever it is drawn: asked for up front, a
        // finger that brushed the band downloaded every venue's logo.
        fit(e, v) {
            const img = e.target;
            const ratio = img.naturalWidth / (img.naturalHeight || 1);

            // toggle, not add: a reused element kept the last venue's look.
            img.classList.toggle('is-fit', ratio > 1.25 || ratio < 0.8);

            if (v && v.wide === undefined) {
                v.wide = ratio > 2 || ratio < 0.5;

                if (v.wide && this.map) {
                    this.redraw(v.key);
                }
            }
        },
        // The accessibility widget's own switch, as the list reads it: its class lands on <html>
        // only once the widget's module has run.
        widgetReducesMotion() {
            try {
                return localStorage.getItem('es_a11y_reduce_motion') === '1';
            } catch (e) {
                return false;
            }
        },
        km(a, b) {
            const r = Math.PI / 180;
            const dLat = (b.lat - a.lat) * r;
            const dLon = (b.lon - a.lon) * r;
            const h = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * r) * Math.cos(b.lat * r) * Math.sin(dLon / 2) ** 2;

            return 2 * 6371 * Math.asin(Math.sqrt(h));
        },
        far(km) {
            const n = this.miles ? km * 0.621371 : km;

            return (n < 10 ? n.toFixed(1) : Math.round(n)) + ' ' + (this.miles ? this.t.mi : this.t.km);
        },

        // ---------- loading ----------
        load() {
            if (this.ready) {
                return this.ready;
            }

            this.loading = true;
            this.failed = false;

            // The page's own sub-schedule, the one the band was drawn for. Not whichever the list
            // is on now: the band said "12 venues" of this one.
            const query = new URLSearchParams();
            if (this.schedule) {
                query.set('schedule', this.schedule);
            }
            const lang = new URLSearchParams(location.search).get('lang');
            if (lang) {
                query.set('lang', lang);
            }

            const data = fetch(this.url + (query.toString() ? (this.url.indexOf('?') < 0 ? '?' : '&') + query : ''), { headers: { Accept: 'application/json' } })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('venue map ' + response.status);
                    }

                    return response.json();
                })
                .then((json) => {
                    this.venues = Array.isArray(json.venues) ? json.venues : [];
                    this.empty = !this.venues.some((v) => v.lat !== null);
                });

            this.ready = Promise.all([data, loadLeaflet(this.assets)])
                .then(() => {
                    this.loading = false;
                })
                .catch(() => {
                    this.ready = null;
                    this.loading = false;
                    this.failed = true;
                    throw new Error('venue map');
                });

            // Whoever only wanted it warm (a pointer over the band) must not see a rejection.
            this.ready.catch(() => {});

            return this.ready;
        },
        // A real second try: the first one's promise is not handed back.
        retry() {
            this.ready = null;
            this.failed = false;
            this.load().then(() => this.$nextTick(this.build)).catch(() => {});
        },
        build() {
            try {
                this.ensureMap();
            } catch (e) {
                this.failed = true;
                console.error(e);
            }
        },

        // ---------- opening and closing ----------
        bandClick() {
            if (!this.isOpen && !this.asking) {
                this.open(true);
            }
        },
        toggle() {
            this.inline ? this.close() : this.open(true);
        },
        // What the address says is open: null, or { key } with an empty key for the map alone.
        hashState() {
            const hash = location.hash;

            if (hash !== HASH && hash.indexOf(HASH + '/') !== 0) {
                return null;
            }

            try {
                return { key: decodeURIComponent(hash.slice(HASH.length + 1)) };
            } catch (e) {
                // "#gp-map/%" is somebody's typo, not a venue.
                return { key: '' };
            }
        },
        // Say in the address what is open, WITHOUT a history entry: the current one is rewritten,
        // and whatever state the list put on it is kept.
        address(hash) {
            history.replaceState(history.state, '', hash || (location.pathname + location.search));
        },
        // Whether the entry we are on is the one the full-window map pushed.
        ownsEntry() {
            return !!(history.state && history.state.esMap);
        },
        pushEntry(hash) {
            history.pushState(Object.assign({}, history.state, { esMap: 1 }), '', hash);
        },
        // A press on the band. withStreets: the press was Show map, which beside the sentence is
        // the visitor's choice.
        open(withStreets) {
            if (withStreets && this.tiles && !this.consent) {
                this.optedIn = this.asking || this.optedIn;
            }

            if (this.isOpen) {
                return;
            }

            this.addressed = true;

            // On a phone the map is the whole window, and Back must close it.
            if (this.phone) {
                this.pushEntry(HASH);
            } else {
                this.address(HASH);
            }

            this.show(false);
        },
        show(quietly) {
            const built = !!this.map;

            this.isOpen = true;
            this.forget();
            this.syncFilter();

            this.load()
                .then(() => this.$nextTick(() => {
                    this.build();

                    // Opened again: the whole map, as on the first opening.
                    if (built && this.map && !this.selected) {
                        this.fitAll();
                        this.buildMarkers();
                        this.placeLabels();
                    }

                    // Its top comes to the top of the window, so its foot is never under whatever
                    // sits at the bottom of the screen.
                    if (this.inline && !quietly) {
                        this.$el.scrollIntoView({ behavior: this.reduced ? 'auto' : 'smooth', block: 'start' });
                    }
                }))
                .catch(() => {});
        },
        // The larger map is the full window, so it takes the one entry too.
        grow() {
            this.pushEntry(location.hash || HASH);
            this.big = true;
        },
        // X, Hide map, and "See all events here" from the full window.
        close() {
            if (this.closing) {
                return;
            }

            // Hiding the map, not shrinking the larger one back into the page.
            if (this.phone || !this.big) {
                this.remember();
            }

            if (this.sheet && this.ownsEntry()) {
                // Back over the one entry the full-window map pushed; fromAddress() does the
                // rest. The flag keeps a second tap from travelling again, off the page.
                this.closing = true;
                this.closeTimer = setTimeout(() => {
                    if (this.closing) {
                        this.closing = false;
                        this.address('');
                        this.hide();
                    }
                }, 1000);
                history.back();

                return;
            }

            // Nothing of ours in history (the map in the page, or one a link opened): the address
            // is rewritten where it stands and nothing travels.
            this.address('');
            this.hide();
        },
        hide() {
            const previous = this.selected;

            this.isOpen = false;
            this.big = false;
            this.selected = null;
            this.addressed = false;
            this.followed = false;
            this.inView = null;

            // Its pin was drawn as the chosen one, and stayed so for the next opening.
            if (previous && this.map) {
                this.redraw(previous);
            }

            this.$nextTick(() => this.$refs.toggle?.focus({ preventScroll: true }));
            this.settle();
        },
        // What was waiting for the full window to close ("See all events here").
        settle() {
            if (this.afterClose) {
                const then = this.afterClose;
                this.afterClose = null;
                // One tick after the list's own popstate handler has read the address.
                setTimeout(then, 0);
            }
        },
        // The address says what is open: #gp-map, or #gp-map/<venue>. Back and Forward land here,
        // and so does a link to a venue on the map (called with no event, on arrival).
        fromAddress(event) {
            const arriving = !(event instanceof Event);
            const wasClosing = this.closing;
            const at = this.hashState();

            this.closing = false;
            clearTimeout(this.closeTimer);

            if (!at) {
                // Not the map's address. That closes a map the address had opened. A map that
                // started open by the owner's setting has no address, and is not closed by
                // somebody else's Back (the list's month, a gallery photo).
                if (this.isOpen && (this.addressed || wasClosing)) {
                    this.hide();
                }

                return;
            }

            this.addressed = true;
            // Larger map is open exactly while we stand on the entry it pushed.
            this.big = this.ownsEntry() && !this.phone;

            if (!this.isOpen) {
                // A link to the map on a laptop brings it into view; Back and Forward leave the
                // page where it is.
                this.show(!arriving);
            }

            if (wasClosing) {
                // The larger map closed back into the page. It is the same map, left as it was:
                // the entry we came back to still names whatever was chosen when Larger map was
                // pressed, so it is rewritten to say what is chosen now, not read.
                this.address(this.selected ? HASH + '/' + encodeURIComponent(this.selected) : HASH);
                this.$nextTick(() => this.$refs.grow?.focus());
                this.settle();

                return;
            }

            this.load().then(() => this.$nextTick(() => this.choose(at.key || null, true))).catch(() => {});
        },
        // Consent withdrawn on another page is not heard by a page kept in the back/forward cache.
        onPageShow(e) {
            if (e.persisted) {
                this.onConsent();
            }
        },
        hid() {
            try {
                return localStorage.getItem('es_map_hidden_' + this.url) === '1';
            } catch (e) {
                return false;
            }
        },
        remember() {
            if (!this.startsOpen) {
                return;
            }

            try {
                localStorage.setItem('es_map_hidden_' + this.url, '1');
            } catch (e) { /* a private window: the map simply opens again next time */ }
        },
        forget() {
            try {
                localStorage.removeItem('es_map_hidden_' + this.url);
            } catch (e) { /* nothing to forget */ }
        },
        onKey(e) {
            if (!this.isOpen || e.defaultPrevented) {
                return;
            }

            if (e.key === 'Tab' && this.sheet) {
                this.trap(e);

                return;
            }

            // Escape is the map's only when it was pressed in the map. Heard anywhere, the Escape
            // that closed the list's filter panel or a photo also stepped the map back.
            if (e.key !== 'Escape' || !(this.sheet || this.$el.contains(e.target))) {
                return;
            }

            if (this.selected) {
                this.back();
            } else if (this.sheet) {
                this.close();
            }
        },
        // Focus stays inside the full-window map while it is open. Heard on the document, as the
        // lightbox hears it: on the sheet alone, focus that had fallen to the page was free to
        // walk the page behind a dialog that says it is modal.
        trap(e) {
            const sheet = this.$refs.sheet;
            const stops = Array.from(sheet.querySelectorAll('button, a[href], [tabindex="0"]')).filter((el) => el.offsetParent !== null);

            if (!stops.length) {
                return;
            }

            const first = stops[0];
            const last = stops[stops.length - 1];

            if (!sheet.contains(document.activeElement)) {
                e.preventDefault();
                (e.shiftKey ? last : first).focus();
            } else if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        },
        onConsent() {
            this.consent = hasConsent('marketing');

            // Withdrawal takes the streets away again (GDPR Article 7(3)), press or no press.
            if (!this.consent) {
                this.optedIn = false;
            }
        },
        showStreets() {
            this.optedIn = true;
        },

        // ---------- the map ----------
        ensureMap() {
            const L = window.L;

            // Nothing in the answer has a position (the owner switched the map off after this
            // page loaded, or took venues off it): said in words. Leaflet throws on a map that
            // was never given a view, which used to read as "could not be loaded" for good.
            if (!L || !this.venues || !this.isOpen || this.empty) {
                return;
            }

            if (this.map) {
                this.resize();

                return;
            }

            this.map = L.map(this.$refs.leaflet, {
                zoomControl: false,
                attributionControl: false,
                scrollWheelZoom: false,
                minZoom: 3,
                maxZoom: 18,
                zoomSnap: 0.25,
                zoomAnimation: !this.reduced,
                fadeAnimation: !this.reduced,
                markerZoomAnimation: !this.reduced,
            });

            L.control.zoom({ position: this.rtl ? 'topright' : 'topleft', zoomInTitle: this.t.zoom_in, zoomOutTitle: this.t.zoom_out }).addTo(this.map);
            L.control.scale({ imperial: this.miles, metric: !this.miles, position: this.rtl ? 'bottomright' : 'bottomleft' }).addTo(this.map);
            this.creditControl = L.control.attribution({ prefix: false, position: this.rtl ? 'bottomleft' : 'bottomright' }).addTo(this.map);
            this.setCredit();

            this.fitAll();
            this.buildMarkers();

            this.map.on('move', this.moveGround);
            this.map.on('zoomend moveend', () => {
                this.placeLabels();
                this.follow();
            });
            // The wheel belongs to the page until the map has been clicked.
            this.map.on('click', () => this.map.scrollWheelZoom.enable());
            this.$refs.leaflet.addEventListener('mouseleave', () => this.map.scrollWheelZoom.disable());

            if (this.streetsWanted) {
                this.addTiles();
            }

            this.moveGround();
            this.placeLabels();
        },
        // Only while it is on the screen: a hidden map has no size to be told about.
        resize() {
            if (this.map && this.isOpen) {
                this.map.invalidateSize();
                this.placeLabels();
            }
        },
        addTiles() {
            if (!this.map || this.tileLayer || !this.tiles) {
                return;
            }

            const layer = window.L.tileLayer(this.tiles.url, { maxZoom: 19 });

            // The opening view sits between two whole zoom steps, where the browser scales the
            // street images and rounds each one's edge its own way: a hairline of the ground shows
            // between them. A pixel of overlap covers it.
            const initTile = layer._initTile;
            layer._initTile = function (tile) {
                initTile.call(this, tile);
                const size = this.getTileSize();
                tile.style.width = (size.x + 1) + 'px';
                tile.style.height = (size.y + 1) + 'px';
            };

            this.tileLayer = layer.addTo(this.map);
            this.streetsShown = true;
            this.setCredit();
            this.placeLabels();
        },
        removeTiles() {
            if (this.map && this.tileLayer) {
                this.map.removeLayer(this.tileLayer);
            }

            this.tileLayer = null;
            this.streetsShown = false;
            this.setCredit();
        },
        // The pins' positions are the map service's data too, so its credit is on the map with or
        // without streets, and says which of the two it is for.
        setCredit() {
            if (!this.creditControl) {
                return;
            }

            const el = this.creditControl.getContainer();
            el.textContent = '';

            if (!this.tileLayer) {
                el.appendChild(document.createTextNode(this.t.credit_pins + ' '));
            }

            const name = document.createElement(this.creditUrl ? 'a' : 'bdi');
            name.dir = 'ltr';
            name.textContent = this.credit;

            if (this.creditUrl) {
                name.href = this.creditUrl;
                name.target = '_blank';
                name.rel = 'noopener noreferrer';
            }

            el.appendChild(name);
        },
        // The plain ground moves with the map, so a drag reads as a drag before any street is there.
        moveGround() {
            const origin = this.map.getPixelBounds().min;
            this.$refs.view.style.backgroundPosition = (-origin.x) + 'px ' + (-origin.y) + 'px';
        },
        logo(v) {
            if (v.logo && !v.wide) {
                const img = document.createElement('img');
                img.src = v.logo;
                img.alt = '';
                img.addEventListener('load', (e) => this.fit(e, v));

                return img;
            }

            const letter = document.createElement('i');
            letter.textContent = this.initial(v);

            return letter;
        },
        label(lines) {
            const label = document.createElement('span');
            label.className = 'gk-pin-label';

            lines.forEach((text, i) => {
                const el = document.createElement(i ? 'small' : 'bdi');
                el.textContent = text;
                label.appendChild(el);
            });

            return label;
        },
        pinIcon(v, on) {
            const box = document.createElement('span');
            box.className = 'gk-pinwrap';

            const pin = document.createElement('span');
            pin.className = 'gk-pin' + (on ? ' is-on' : '');
            pin.setAttribute('data-pin', v.key);
            pin.appendChild(this.logo(v));
            box.appendChild(pin);

            // The town under the name only until there are streets to say where this is.
            const label = this.label(on || !v.town ? [v.name] : [v.name, v.town]);
            if (on) {
                label.classList.add('is-on');
            }
            box.appendChild(label);

            return window.L.divIcon({ html: box, className: 'gk-pinbox', iconSize: [40, 46], iconAnchor: [20, 46] });
        },
        // A venue with nothing in the chosen window: a small quiet mark, still pressable, never named.
        quietIcon(v) {
            const dot = document.createElement('span');
            dot.className = 'gk-pin-quiet';
            dot.setAttribute('data-pin', v.key);

            return window.L.divIcon({ html: dot, className: 'gk-pinbox', iconSize: [18, 18], iconAnchor: [9, 9] });
        },
        // Venues too close to tell apart: two of them (a logo, or an initial where there is none
        // to use), how many there are, and the one or two towns most of them are in. Never a
        // number in the name, and never a name cut inside a word.
        clusterIcon(cluster) {
            const kids = cluster.getAllChildMarkers().map((m) => m.options.venue);
            const count = new Map();
            kids.forEach((v) => count.set(v.town, (count.get(v.town) || 0) + 1));
            const towns = Array.from(count.entries()).filter(([town]) => town).sort((a, b) => b[1] - a[1]).map(([town]) => town);

            const box = document.createElement('span');
            box.className = 'gk-pinwrap gk-pinwrap-cluster';

            const pill = document.createElement('span');
            pill.className = 'gk-cluster-dot';
            kids.slice().sort((a, b) => (b.logo && !b.wide ? 1 : 0) - (a.logo && !a.wide ? 1 : 0)).slice(0, 2).forEach((v) => pill.appendChild(this.logo(v)));
            const n = document.createElement('b');
            n.textContent = cluster.getChildCount();
            pill.appendChild(n);
            box.appendChild(pill);

            if (towns.length) {
                box.appendChild(this.label([towns.slice(0, 2).map((town) => town.replace(/ /g, ' ')).join(' · ')]));
            }

            return window.L.divIcon({ html: box, className: 'gk-cluster', iconSize: [80, 38], iconAnchor: [40, 19] });
        },
        buildMarkers() {
            const L = window.L;

            if (this.cluster) {
                this.map.removeLayer(this.cluster);
            }
            if (this.quiet) {
                this.map.removeLayer(this.quiet);
            }

            this.markers = {};
            // The grouping distance is a little more than a pill's width, ON SCREEN: two pills, or
            // a pill and a pin, never touch. Groups are worked out per whole zoom step, and the
            // opening view may sit between two steps: when it is drawn from the step above, what
            // is 96px apart there is as little as 68px apart here, so that one step groups wider
            // by the same factor.
            const reach = 96;
            const fit = this.fitZoom;
            const radius = (level) => (typeof fit === 'number' && Math.round(fit) === level && fit < level ? Math.ceil(reach * Math.pow(2, level - fit)) : reach);
            this.cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: radius, spiderfyOnMaxZoom: true, animate: !this.reduced, iconCreateFunction: this.clusterIcon });
            this.quiet = L.layerGroup();

            this.venues.filter((v) => v.lat !== null).forEach((v) => {
                const on = this.selected === v.key;
                const live = this.active(v) || on;
                // Out of the tab order: the list beside the map holds every venue, placed or not.
                const marker = L.marker([v.lat, v.lon], { icon: live ? this.pinIcon(v, on) : this.quietIcon(v), title: v.name, alt: v.name, riseOnHover: true, keyboard: false, venue: v, zIndexOffset: on ? 1000 : live ? 0 : -500 });
                marker.on('click', () => this.select(v.key));
                marker.on('mouseover', () => this.hot(v.key, true));
                marker.on('mouseout', () => this.hot(v.key, false));
                this.markers[v.key] = marker;
                (live ? this.cluster : this.quiet).addLayer(marker);
            });

            this.map.addLayer(this.quiet);
            this.map.addLayer(this.cluster);
            this.cluster.on('animationend', this.placeLabels);
        },
        redraw(key) {
            const marker = this.markers[key];
            const v = this.venues.find((x) => x.key === key);

            if (!marker || !v) {
                return;
            }

            const on = this.selected === key;
            marker.setIcon(on || this.active(v) ? this.pinIcon(v, on) : this.quietIcon(v));
            marker.setZIndexOffset(on ? 1000 : this.active(v) ? 0 : -500);
        },
        // The opening view fits the venues as closely as it can; after that, zoom moves in whole
        // steps so the street names stay sharp.
        fitAll() {
            const pinned = this.venues.filter((v) => v.lat !== null);
            const shown = pinned.filter(this.active);
            const points = (shown.length ? shown : pinned).map((v) => [v.lat, v.lon]);

            if (!points.length) {
                return;
            }

            const ask = this.askOnMap || (!!this.tiles && !this.streetsWanted);
            this.map.options.zoomSnap = 0.25;
            this.map.fitBounds(points, {
                paddingTopLeft: this.phone ? [34, 56] : [56, 66],
                paddingBottomRight: this.phone ? [34, ask ? 156 : 40] : [56, ask ? 132 : 52],
                maxZoom: 15,
                animate: false,
            });
            this.map.options.zoomSnap = 1;
            this.fitZoom = this.map.getZoom();
            this.home = { center: this.map.getCenter(), zoom: this.fitZoom };
        },
        // A name goes under its pin, beside it when that is taken, above it when that is too, and
        // nowhere otherwise. The chosen venue is placed first, then groups, then single pins.
        // Names never cross the map's edge, and a quiet mark under a pin or a pill is not drawn.
        placeLabels() {
            clearTimeout(this.labelTimer);
            this.labelTimer = setTimeout(() => {
                if (!this.map) {
                    return;
                }

                const frame = this.map.getContainer().getBoundingClientRect();
                const hit = (r, k, pad) => !(r.right + pad < k.left || r.left - pad > k.right || r.bottom + pad < k.top || r.top - pad > k.bottom);
                const pane = this.map.getPanes().markerPane;

                const items = Array.from(pane.querySelectorAll('.leaflet-marker-icon')).map((el) => {
                    const label = el.querySelector('.gk-pin-label');
                    const dot = el.querySelector('.gk-pin, .gk-cluster-dot');
                    if (label) {
                        label.style.visibility = '';
                    }
                    const on = !!(label && label.classList.contains('is-on'));

                    return { label, on, dot: dot ? dot.getBoundingClientRect() : null, weight: on ? 9000 : el.classList.contains('gk-cluster') ? 1000 + (parseInt(dot.lastChild.textContent, 10) || 0) : 0 };
                }).sort((a, b) => b.weight - a.weight);

                pane.querySelectorAll('.gk-pin-quiet').forEach((quiet) => {
                    quiet.style.visibility = '';
                    const r = quiet.getBoundingClientRect();
                    if (items.some((o) => o.dot && hit(r, o.dot, 2))) {
                        quiet.style.visibility = 'hidden';
                    }
                });

                const kept = [];
                items.forEach((it) => {
                    if (!it.label) {
                        return;
                    }

                    const placed = ['', 'is-after', 'is-before', 'is-above'].some((side) => {
                        it.label.classList.remove('is-after', 'is-before', 'is-above');
                        if (side) {
                            it.label.classList.add(side);
                        }
                        const r = it.label.getBoundingClientRect();
                        const outside = r.left < frame.left + 2 || r.right > frame.right - 2 || r.bottom > frame.bottom - 2 || r.top < frame.top + 2;
                        const clash = outside || kept.some((k) => hit(r, k, 4)) || items.some((o) => o !== it && o.dot && hit(r, o.dot, 1));
                        if (!clash) {
                            kept.push(r);
                        }

                        return !clash;
                    });

                    if (!placed) {
                        it.label.classList.remove('is-after', 'is-before', 'is-above');
                        if (it.on) {
                            kept.push(it.label.getBoundingClientRect());
                        } else {
                            it.label.style.visibility = 'hidden';
                        }
                    }
                });
            }, this.reduced ? 0 : 320);
        },
        // The list holds the venues in view. "Whole map" is its first row whenever some are out of it.
        follow() {
            clearTimeout(this.followTimer);
            this.followTimer = setTimeout(() => {
                if (!this.map || !this.venues || !this.isOpen) {
                    return;
                }

                const bounds = this.map.getBounds();
                const inView = {};
                let out = false;

                this.venues.forEach((v) => {
                    if (v.lat === null) {
                        return;
                    }
                    inView[v.key] = bounds.contains([v.lat, v.lon]);
                    out = out || (!inView[v.key] && this.active(v));
                });

                this.inView = inView;
                this.followed = out;
            }, this.reduced ? 0 : 200);
        },
        whole() {
            if (this.home) {
                this.map.setView(this.home.center, this.home.zoom, { animate: !this.reduced });
            }
        },
        zoomTown(town) {
            const points = this.venues.filter((v) => v.town === town && v.lat !== null).map((v) => [v.lat, v.lon]);

            if (points.length && this.map) {
                this.map.fitBounds(points, { padding: [70, 70], maxZoom: 16, animate: !this.reduced });
            }
        },
        setWhen(when) {
            this.when = when;

            if (this.map) {
                this.fitAll();
                this.buildMarkers();
                this.placeLabels();
            }
        },
        hot(key, on) {
            this.hotKey = on ? key : null;
            const pin = this.$refs.leaflet.querySelector('[data-pin="' + (window.CSS && CSS.escape ? CSS.escape(key) : key) + '"]');
            if (pin) {
                pin.classList.toggle('is-hot', on);
            }
        },

        // ---------- choosing a venue ----------
        select(key) {
            if (key === this.selected) {
                return;
            }

            this.addressed = true;
            this.address(HASH + '/' + encodeURIComponent(key));
            this.choose(key);
        },
        back() {
            this.address(HASH);
            this.choose(null);
        },
        choose(key, quietly) {
            const previous = this.selected;

            if (key && !(this.venues || []).some((v) => v.key === key)) {
                key = null;
            }

            this.selected = key;
            this.syncFilter();

            if (previous) {
                this.redraw(previous);
            }

            const marker = key ? this.markers[key] : null;

            if (marker && this.map) {
                this.redraw(key);
                const centre = () => {
                    this.map.panTo(marker.getLatLng(), { animate: !this.reduced && !quietly });
                    this.placeLabels();
                };

                if (this.cluster.hasLayer(marker)) {
                    this.cluster.zoomToShowLayer(marker, centre);
                } else {
                    // A quiet venue is not in a group: go to it, close enough to see where it is.
                    this.map.setView(marker.getLatLng(), Math.max(this.map.getZoom(), 13), { animate: !this.reduced && !quietly });
                    this.placeLabels();
                }
            } else {
                this.placeLabels();
            }

            this.$nextTick(() => {
                this.$refs.side.scrollTop = 0;

                if (quietly) {
                    return;
                }

                if (key) {
                    this.$refs.name?.focus({ preventScroll: true });
                } else if (previous) {
                    this.$refs.side.querySelector('[data-venue="' + (window.CSS && CSS.escape ? CSS.escape(previous) : previous) + '"]')?.focus({ preventScroll: true });
                }
            });
        },

        // ---------- the list of events below ----------
        // The list's own app, or null when it is not on the page, is still loading, or no longer
        // has the fields this component reads.
        list() {
            const list = window.calendarVueApp;

            return list && typeof list.selectedVenue === 'string' && Array.isArray(list.eventsForFilters) && !list.isLoadingEvents ? list : null;
        },
        // What the list is filtered to, and which venues it is holding events for, read when it
        // matters: the two are separate apps, and a visitor can clear the list's chip, change its
        // month or choose a category without this one hearing of it.
        syncFilter() {
            const list = this.list();
            const listed = {};

            if (list) {
                const passes = typeof list.passesFilters === 'function';

                list.eventsForFilters.forEach((event) => {
                    // Under every filter the visitor already has, the venue's own left aside:
                    // the button is offered only where pressing it shows something, because it
                    // never changes another filter to make that so.
                    if (event.venue_subdomain && (!passes || list.passesFilters(event, { venue: true }))) {
                        listed[event.venue_subdomain] = true;
                    }
                });
            }

            this.listed = listed;
            this.filtered = list ? list.selectedVenue : '';

            // A link to a venue is read before the list has loaded its events: asked again until
            // it has, or "See all events here" would wait for the pointer to wander over the panel.
            clearInterval(this.listTimer);
            if (!list && this.isOpen && window.calendarVueApp) {
                let tries = 0;
                this.listTimer = setInterval(() => {
                    if (this.list() || ++tries > 40 || !this.isOpen) {
                        clearInterval(this.listTimer);
                        if (this.isOpen) {
                            this.syncFilter();
                        }
                    }
                }, 250);
            }
        },
        seeAll() {
            const venue = this.current;

            if (!venue) {
                return;
            }

            const act = () => {
                const list = this.list();

                if (list) {
                    // The venue filter and nothing else: the category, Free, Online, the search
                    // and the custom fields are the visitor's, and stay as they chose them.
                    // A day pressed in the phone's month is not one of them: "all events here"
                    // is not all of them on one day.
                    if (typeof list.phoneDay === 'string') {
                        list.phoneDay = '';
                    }

                    list.selectedVenue = venue.key;
                    this.filtered = venue.key;
                }

                const events = document.getElementById('gp-events');

                if (events) {
                    events.setAttribute('tabindex', '-1');
                    events.scrollIntoView({ behavior: this.reduced ? 'auto' : 'smooth', block: 'start' });
                    // The list, never the chip that would undo what was just asked for.
                    events.focus({ preventScroll: true });
                }
            };

            if (this.sheet) {
                // The full window closes first. Where that goes through history, the list reads
                // the address on the same event, so the filter is set one tick after it (settle()).
                this.afterClose = act;
                this.close();
            } else {
                act();
            }
        },
        clearFilter() {
            const list = this.list();

            if (list) {
                list.selectedVenue = '';
            }

            this.filtered = '';
        },
    },
};
</script>
