import { createApp } from 'vue';
import VenueMapEditor from './components/VenueMapEditor.vue';

/**
 * Mounts the list of venues in Engagement > Venue map on the schedule form, if the page drew one.
 *
 * The same contract as venue-map-boot.js: an EMPTY host, and every value (venue names and
 * addresses among them) through the JSON blob, because server-rendered text inside a Vue mount is
 * compiled as a template. The schedule form around the host is not a Vue app and stays that way.
 */
export function mountVenueMapEditor() {
    const host = document.getElementById('es-venue-map-editor');
    const jsonEl = document.getElementById('es-venue-map-editor-json');

    if (!host || !jsonEl || host.dataset.mounted) {
        return;
    }

    let props = {};

    try {
        props = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        return;
    }

    if (!Array.isArray(props.venues) || !props.markUrl) {
        return;
    }

    host.dataset.mounted = '1';
    createApp(VenueMapEditor, props).mount(host);
}
