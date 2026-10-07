import { createApp } from 'vue';
import VenueMap from './components/VenueMap.vue';

/**
 * Mounts the venue map's band, if the schedule page rendered one.
 *
 * The host element is empty in the server response and every string arrives through the JSON
 * blob rather than as server-rendered markup. That is not incidental: the app runs Vue's runtime
 * template compiler with CSP 'unsafe-eval' enabled, so any server-rendered text node inside a
 * Vue mount is compiled as a template and becomes a script-injection sink for user-controlled
 * data (a venue's name and town, here). Passing values as props and letting Vue render them is
 * safe, and keeps this component free of v-pre / <x-user-text>.
 */
export function mountVenueMap() {
    const host = document.getElementById('es-venue-map-host');
    const jsonEl = document.getElementById('es-venue-map-json');

    if (!host || !jsonEl || host.dataset.mounted) {
        return;
    }

    let props = {};

    try {
        props = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        props = {};
    }

    if (!props.url || !props.band) {
        // No band is coming: the room kept for it is given back, not left as a blank gap.
        host.classList.add('is-mounted');

        return;
    }

    host.dataset.mounted = '1';
    createApp(VenueMap, props).mount(host);
    // The height the host kept for the band is the band's own from here on.
    host.classList.add('is-mounted');
}
