import { createApp } from 'vue';
import TicketPresetsEditor from './components/TicketPresetsEditor.vue';

/**
 * Mounts Tickets > Ticket presets on the schedule form, if the page drew it.
 *
 * The same contract as venue-map-editor-boot.js: an EMPTY host and every value (preset names among
 * them) through the JSON blob, because server-rendered text inside a Vue mount is compiled as a
 * template. The host is the list itself (#ticket-preset-items), which the form's list watcher
 * observes, so the rows the editor draws are its direct children. The form around it is not a Vue
 * app and stays that way: "+ Add preset" is the page's own link, and calls in.
 */
export function mountTicketPresetsEditor() {
    const host = document.getElementById('ticket-preset-items');
    const jsonEl = document.getElementById('ticket-preset-items-json');

    if (!host || !jsonEl || host.dataset.mounted) {
        return;
    }

    let props = {};

    try {
        props = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        return;
    }

    host.dataset.mounted = '1';
    host.textContent = '';
    const editor = createApp(TicketPresetsEditor, props).mount(host);
    // The form's list watcher takes its snapshot of the rows once they are drawn.
    host.dispatchEvent(new Event('island:mounted'));

    // Disabled in the markup: until the editor is here, the button has nothing to add to.
    document.querySelectorAll('[data-action="add-ticket-preset"]').forEach((button) => {
        button.disabled = false;
        button.addEventListener('click', () => editor.add());
    });
}
