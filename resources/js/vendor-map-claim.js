import { createApp } from 'vue';
import ClaimApp from './vendor-map/ClaimApp.vue';

/**
 * The vendor link (/{schedule}/spot/{token}): resources/views/vendor-map/claim.blade.php.
 *
 * The host element is empty and every value arrives as JSON, never as server-rendered markup: the
 * names on a market's map are typed by other people, and this app's Vue build compiles any text
 * inside a mount as a template. Mounted by element, on the one carrying data-vue-root.
 */
function mountVendorClaim() {
    const host = document.querySelector('#vendor-claim-app[data-vue-root]');
    const json = document.getElementById('vendor-claim-props');

    if (!host || !json || host.dataset.mounted) {
        return;
    }

    let props = {};
    try {
        props = JSON.parse(json.textContent || '{}');
    } catch (e) {
        return;
    }

    host.dataset.mounted = '1';
    createApp(ClaimApp, { config: props }).mount(host);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountVendorClaim);
} else {
    mountVendorClaim();
}
