import { createApp } from 'vue';
import PublicMapApp from './vendor-map/PublicMapApp.vue';

/**
 * The visitors' map (/{schedule}/map, vendor-map/public.blade.php).
 *
 * The page arrives complete: the list and the map are printed by the server, and everything this
 * app needs is in the JSON beside the mount. It starts only on the element that carries
 * data-vue-root, which a description's sanitizer strips (MarkdownUtils::RESERVED_IDS), and it
 * renders with its own compiled template: the server's markup inside the element is replaced,
 * never compiled, so a vendor's name in it is only ever text.
 */
function start() {
    const host = document.querySelector('#vendor-map-public[data-vue-root]');
    const json = document.getElementById('vendor-map-public-data');

    if (!host || !json || host.dataset.mounted) {
        return;
    }

    let boot = null;
    try {
        boot = JSON.parse(json.textContent || 'null');
    } catch (e) {
        boot = null;
    }

    if (!boot || !boot.map) {
        return;
    }

    host.dataset.mounted = '1';
    createApp(PublicMapApp, { boot }).mount('#vendor-map-public[data-vue-root]');
    host.classList.add('is-live');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
