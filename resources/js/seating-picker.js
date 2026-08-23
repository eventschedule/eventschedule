import { createApp } from 'vue';
import SeatingPicker from './components/SeatingPicker.vue';

/**
 * One app per allocated ticket row.
 *
 * The ticket form runs on the GLOBAL Vue build with the runtime template compiler, so an SFC
 * bundled here cannot be registered as one of its components - they are two different Vue
 * runtimes. Instead the form renders an EMPTY placeholder (nothing in its vdom below that node, so
 * it never patches the children away) and this mounts into it. The two talk through the
 * es-seats-changed event, the same way the multi-event cart already does.
 */
function mountAll() {
    document.querySelectorAll('.seating-picker-mount:not([data-seating-mounted])').forEach((el) => {
        if (!el.dataset.props) return;
        el.setAttribute('data-seating-mounted', '1');
        try {
            createApp(SeatingPicker, JSON.parse(el.dataset.props)).mount(el);
        } catch (e) {
            el.removeAttribute('data-seating-mounted');
        }
    });
}

// The placeholders are rendered by the parent app, which mounts after this module runs - so watch
// for them rather than scanning once and giving up.
mountAll();
if (typeof MutationObserver !== 'undefined') {
    new MutationObserver(mountAll).observe(document.documentElement, { childList: true, subtree: true });
}
