import { createApp } from 'vue';
import Stay22Map from './components/Stay22Map.vue';

/**
 * Mounts the accommodation map, if the current page rendered one.
 *
 * The host element is empty in the server response and every string arrives through the
 * JSON blob rather than as server-rendered markup. That is not incidental: the app runs
 * Vue's runtime template compiler with CSP 'unsafe-eval' enabled, so any server-rendered
 * text node inside a Vue mount is compiled as a template and becomes a script-injection
 * sink for user-controlled data (the venue name, here). Passing values as props and letting
 * Vue render them is safe, and keeps this component free of v-pre / <x-user-text>.
 */
export function mountStay22Map() {
    const host = document.getElementById('es-stay22-host');
    const jsonEl = document.getElementById('es-stay22-json');

    if (!host || !jsonEl) {
        return;
    }

    let props = {};

    try {
        props = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        return;
    }

    if (!props.url) {
        return;
    }

    createApp(Stay22Map, props).mount(host);
}
