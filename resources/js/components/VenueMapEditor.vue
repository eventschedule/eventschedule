<template>
  <div class="venue-map-editor">
    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ t.venues }}</h3>
    <p v-if="!list.length" class="event-hint">{{ t.none }}</p>
    <template v-else>
      <p class="event-hint">{{ t.help }}</p>
      <p v-if="tiles" class="event-hint">{{ t.streets_note }}</p>
      <!-- The map is on the page only with two pins: taking one of two off takes the map off. -->
      <p v-if="placed < 2" class="event-hint font-medium text-gray-700 dark:text-gray-300" role="status">{{ t.needs_two }}</p>
      <ul class="divide-y divide-gray-200 dark:divide-gray-700 border-y border-gray-200 dark:border-gray-700">
        <li v-for="venue in list" :key="venue.id" class="py-3" :data-venue-state="venue.hidden ? 'hidden' : venue.state" :data-venue="venue.key">
          <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
            <span class="min-w-0 break-words" :class="venue.hidden ? 'opacity-60' : ''">
              <span class="text-sm font-medium text-gray-900 dark:text-gray-100"><bdi>{{ venue.name }}</bdi></span>
              <span v-if="venue.address" class="block text-xs text-gray-500 dark:text-gray-400"><bdi>{{ venue.address }}</bdi></span>
            </span>
            <span class="event-status" :class="{ 'is-on': onMap(venue) }">{{ stateWord(venue) }}</span>
          </div>

          <p v-if="fix(venue)" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ fix(venue) }}</p>

          <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
            <template v-if="venue.hidden">
              <button type="button" class="event-link" :disabled="busy === venue.id" @click="save(venue, { hidden: false })">{{ t.put_back }}</button>
            </template>
            <template v-else>
              <button v-if="tiles" type="button" class="event-link" :disabled="busy === venue.id" @click="place(venue)">{{ venue.lat === null ? t.place : t.move }}</button>
              <a v-if="venue.edit_url" class="event-link" :href="venue.edit_url">{{ t.edit_venue }}</a>
              <button type="button" class="event-link" :disabled="busy === venue.id" @click="save(venue, { hidden: true })">{{ t.take_off }}</button>
            </template>
            <span v-if="saved === venue.id" class="text-xs text-gray-500 dark:text-gray-400" role="status">{{ t.saved }}</span>
            <span v-if="failed === venue.id" class="text-xs text-red-600 dark:text-red-400" role="alert">{{ t.failed }}</span>
          </div>
        </li>
      </ul>
    </template>

    <!-- The dialog a pin is placed in. On the body: the form's panes clip, and the save bar is fixed. -->
    <Teleport to="body">
      <!-- overflow-y-auto with a min-h-full wrapper: on a short window (a phone on its side) the
           card is taller than the screen, and centred without scrolling its Save was out of reach. -->
      <div v-if="open" class="fixed inset-0 z-[70] overflow-y-auto" :dir="rtl ? 'rtl' : 'ltr'">
        <div class="fixed inset-0 bg-black/50" @click="close"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
        <div id="es-venue-pin-dialog" ref="dialog" class="ap-card relative w-full max-w-2xl rounded-xl p-5" role="dialog" aria-modal="true" :aria-label="title">
          <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100"><bdi>{{ title }}</bdi></h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            <template v-if="open.address"><bdi>{{ open.address }}</bdi>. </template>{{ t.drag }}
          </p>

          <div class="relative mt-4 h-80 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
            <!-- No bound class: Leaflet writes its own classes on this element. -->
            <div ref="map" class="absolute inset-0"></div>
            <p v-if="mapFailed" class="absolute inset-x-4 top-1/2 -translate-y-1/2 text-center text-sm text-gray-700 dark:text-gray-300" role="status">{{ t.map_failed }}</p>
          </div>

          <!-- For a keyboard: the arrow keys move the map, and this puts the pin where it is looking. -->
          <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
            <button type="button" class="event-link" :disabled="!mapReady" @click="dropAtCentre">{{ t.pin_centre }}</button>
            <p v-if="dialogError" class="text-sm text-red-600 dark:text-red-400" role="alert">{{ dialogError }}</p>
          </div>

          <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <button v-if="open.by_hand && open.found" type="button" class="event-link" :disabled="busy === open.id" @click="forget">{{ t.use_found }}</button>
            <span v-else></span>
            <div class="flex flex-wrap gap-3">
              <button ref="cancel" type="button" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]" @click="close">{{ t.cancel }}</button>
              <!-- Live only once the pin has been put somewhere: pressed after only LOOKING at a
                   pin, it stored the looked-up position as one placed by hand, and the pin then
                   ignored the venue's address being corrected. -->
              <button type="button" class="inline-flex items-center justify-center px-4 py-3 rounded-lg font-semibold text-base text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] disabled:opacity-50" :disabled="!moved || busy === open.id" @click="savePin">{{ t.save_position }}</button>
            </div>
          </div>
        </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { loadLeaflet } from '../leaflet-loader';

/**
 * Engagement > Venue map > Venues, on the schedule form: every venue the schedule's map holds,
 * where it stands, and what its owner can do about it. Take a venue off the map and put it back;
 * move a pin that landed in the wrong place; place one by hand where the address search found
 * nothing (a kibbutz, a field, a venue with no street).
 *
 * Each change is saved at once, to the venue's own mark (VenueMapController::mark), and is no
 * part of the schedule form's Save: the row says "Saved" where it happened.
 *
 * An island with an empty host and everything as props (venue-map-editor-boot.js): the schedule
 * form around it is server-rendered and is not a Vue app, and a venue's name printed inside a
 * mount would be compiled as a template.
 *
 * The streets in the dialog come from the same third party as the guest map's. The sentence that
 * says so stands above the list, beside the buttons that load them.
 */
export default {
    name: 'VenueMapEditor',
    props: {
        venues: { type: Array, required: true },
        markUrl: { type: String, required: true },
        csrf: { type: String, default: '' },
        tiles: { type: Object, default: null },
        credit: { type: String, default: '' },
        creditUrl: { type: String, default: null },
        // How many venues have a pin on the map now: under two, there is no map on the page.
        placedNow: { type: Number, default: 0 },
        assets: { type: Object, required: true },
        rtl: { type: Boolean, default: false },
        t: { type: Object, required: true },
    },
    data() {
        return {
            list: this.venues.map((venue) => ({ ...venue })),
            busy: null,
            saved: null,
            failed: null,
            open: null,
            pin: null,
            moved: false,
            mapReady: false,
            mapFailed: false,
            dialogError: '',
            placed: this.placedNow,
        };
    },
    computed: {
        title() {
            return this.open ? this.t.where.replace(':name', () => this.open.name) : '';
        },
    },
    mounted() {
        document.addEventListener('keydown', this.onKey);
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.onKey);
        this.destroyMap();
    },
    methods: {
        onMap(venue) {
            return !venue.hidden && venue.lat !== null;
        },
        stateWord(venue) {
            if (venue.hidden) {
                return this.t.state_hidden;
            }

            if (venue.by_hand) {
                return this.t.state_by_hand;
            }

            // A full address and no country is not "no street address".
            if (venue.state === 'no_address' && venue.why === 'no_country') {
                return this.t.state_no_country;
            }

            return this.t['state_' + venue.state] || venue.state;
        },
        // What can be done about a venue with no pin. Nothing is said of one the owner took off.
        fix(venue) {
            if (venue.hidden || venue.lat !== null || venue.state === 'waiting') {
                return '';
            }

            const what = venue.why === 'no_country' ? this.t.fix_no_country : (venue.state === 'no_address' ? this.t.fix_no_address : this.t.fix_not_found);
            const who = venue.edit_url ? '' : (venue.claimed ? this.t.owner_only : this.t.in_its_events);

            return [what, who, this.tiles ? this.t.or_place : ''].filter(Boolean).join(' ');
        },
        request(venue, method, body) {
            this.busy = venue.id;
            this.saved = null;
            this.failed = null;
            this.dialogError = '';

            return fetch(this.markUrl.replace('__VENUE__', encodeURIComponent(venue.id)), {
                method,
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: body ? JSON.stringify(body) : undefined,
            })
                .then((response) => {
                    if (!response.ok) {
                        const error = new Error('mark ' + response.status);
                        // Signed out, or a form left open past its token: trying again cannot work.
                        error.expired = response.status === 401 || response.status === 419;
                        throw error;
                    }

                    return response.json();
                })
                .then((json) => {
                    const at = this.list.findIndex((v) => v.id === venue.id);

                    if (at >= 0 && json.venue) {
                        this.list.splice(at, 1, json.venue);
                    }

                    if (typeof json.placed === 'number') {
                        this.placed = json.placed;
                    }

                    this.saved = venue.id;

                    return json.venue;
                })
                .catch((e) => {
                    this.failed = venue.id;
                    // Said where the person is looking: with the dialog open the row is behind it.
                    this.dialogError = e.expired ? this.t.expired : this.t.failed;
                    throw e;
                })
                .finally(() => {
                    this.busy = null;
                });
        },
        save(venue, body) {
            this.request(venue, 'PUT', body).catch(() => {});
        },

        // ---------- placing a pin ----------
        place(venue) {
            this.open = venue;
            this.pin = venue.lat !== null ? [venue.lat, venue.lon] : null;
            this.moved = false;
            this.mapReady = false;
            this.mapFailed = false;
            this.dialogError = '';
            this.returnTo = document.activeElement;

            loadLeaflet({ leaflet: this.assets.leaflet, leafletCss: this.assets.leafletCss, images: this.assets.images })
                .then(() => this.$nextTick(this.buildMap))
                .catch(() => {
                    this.mapFailed = true;
                });

            this.$nextTick(() => this.$refs.cancel?.focus());
        },
        buildMap() {
            const L = window.L;

            if (!L || !this.open || !this.$refs.map) {
                return;
            }

            this.destroyMap();

            // Where to look first: the venue's own pin; else the other venues of this map, which
            // are the same part of the world; else nowhere in particular.
            const others = this.list.filter((v) => v.lat !== null && !v.hidden).map((v) => [v.lat, v.lon]);
            this.map = L.map(this.$refs.map, { attributionControl: false, zoomControl: true, scrollWheelZoom: true, maxZoom: 19 });

            if (this.pin) {
                this.map.setView(this.pin, 16);
            } else if (others.length) {
                this.map.fitBounds(others, { padding: [30, 30], maxZoom: 13 });
            } else {
                this.map.setView([20, 0], 2);
            }

            // Loaded on the press that opened this dialog, beside the sentence that names the service.
            L.tileLayer(this.tiles.url, { maxZoom: 19 }).addTo(this.map);

            const credit = L.control.attribution({ prefix: false }).addTo(this.map).getContainer();
            const name = document.createElement(this.creditUrl ? 'a' : 'span');
            name.textContent = this.credit;
            if (this.creditUrl) {
                name.href = this.creditUrl;
                name.target = '_blank';
                name.rel = 'noopener noreferrer';
            }
            credit.textContent = '';
            credit.appendChild(name);

            if (this.pin) {
                this.drop(this.pin);
            }

            this.map.on('click', (e) => this.put([e.latlng.lat, e.latlng.lng]));
            this.mapReady = true;
        },
        // The pin put somewhere by the person: only this makes Save live.
        put(at) {
            this.drop(at);
            this.moved = true;
        },
        dropAtCentre() {
            if (this.map) {
                const centre = this.map.getCenter();
                this.put([centre.lat, centre.lng]);
            }
        },
        // The pin: dragged, or put where the map is clicked.
        drop(at) {
            const L = window.L;
            this.pin = [at[0], at[1]];

            if (this.marker) {
                this.marker.setLatLng(at);

                return;
            }

            this.marker = L.marker(at, { draggable: true, autoPan: true, keyboard: true, title: this.open.name, alt: this.open.name }).addTo(this.map);
            this.marker.on('dragend', () => {
                const where = this.marker.getLatLng();
                this.pin = [where.lat, where.lng];
                this.moved = true;
            });
        },
        savePin() {
            const venue = this.open;

            if (!venue || !this.pin || !this.moved) {
                return;
            }

            this.request(venue, 'PUT', { lat: this.pin[0], lon: this.pin[1] }).then(this.close).catch(() => {});
        },
        forget() {
            this.request(this.open, 'DELETE').then(this.close).catch(() => {});
        },
        close() {
            this.destroyMap();
            this.open = null;
            this.pin = null;
            this.$nextTick(() => this.returnTo?.focus?.());
        },
        destroyMap() {
            if (this.map) {
                this.map.remove();
            }

            this.map = null;
            this.marker = null;
            this.mapReady = false;
        },
        // Escape and Tab while the dialog is open, heard on the document: bound to the dialog
        // itself they stopped working the moment focus left it (a click on its heading).
        onKey(e) {
            if (!this.open) {
                return;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                this.close();
            } else if (e.key === 'Tab') {
                this.trap(e);
            }
        },
        // Focus stays in the dialog while it is open.
        trap(e) {
            if (!this.$refs.dialog) {
                return;
            }

            const stops = Array.from(this.$refs.dialog.querySelectorAll('button:not([disabled]), a[href], [tabindex="0"]')).filter((el) => el.offsetParent !== null);

            if (!stops.length) {
                return;
            }

            const first = stops[0];
            const last = stops[stops.length - 1];

            if (!this.$refs.dialog.contains(document.activeElement)) {
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
    },
};
</script>
