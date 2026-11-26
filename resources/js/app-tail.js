// The second half of what every page of the app runs (resources/js/app.js and resources/js/guest.js):
// the widgets mounted where their host is on the page, and the password reveal toggle.
import { mountAccessibilityWidget } from './accessibility-widget-boot';
import { mountStay22Map } from './stay22-boot';
import { mountConsentEmbeds } from './consent-embed-boot';

// The setup guide is only ever on a new organizer's admin pages, and this bundle is also the
// guest portal's: its code is fetched when its host is on the page, and not before. A chunk that
// fails to load (a deploy in between, a flaky connection) leaves the still version the server
// printed, which is a working link, so the failure is swallowed rather than surfaced.
const mountSetupGuide = () => {
    if (! document.querySelector('[data-setup-guide]')) {
        return;
    }

    import('./setup-guide-boot.js')
        .then((module) => module.mountSetupGuide())
        .catch(() => {});
};

// The venue map on a schedule's guest page (components/venue-map.blade.php). A chunk of its own
// for the same reason: this file is on every page and the map is on a few. If the chunk fails to
// load, the band is simply not drawn and the page is whole without it.
const mountVenueMap = () => {
    if (! document.getElementById('es-venue-map-host')) {
        return;
    }

    import('./venue-map-boot.js')
        .then((module) => module.mountVenueMap())
        // The host keeps the band's height from the first paint so the list does not move when
        // the band arrives. When it is not going to arrive, that height is given back.
        .catch(() => document.getElementById('es-venue-map-host')?.classList.add('is-mounted'));
};

// The owner's list of venues on the schedule form (Page content > Venue map), with its dialog for
// placing a pin. Only where the form drew its host.
const mountVenueMapEditor = () => {
    if (! document.getElementById('es-venue-map-editor')) {
        return;
    }

    import('./venue-map-editor-boot.js')
        .then((module) => module.mountVenueMapEditor())
        .catch(() => {});
};

// Tickets > Ticket presets on the schedule form. Only where the form drew its list.
const mountTicketPresetsEditor = () => {
    if (! document.getElementById('ticket-preset-items')) {
        return;
    }

    import('./ticket-presets-editor-boot.js')
        .then((module) => module.mountTicketPresetsEditor())
        .catch(() => {});
};

const mountVueWidgets = () => {
    mountAccessibilityWidget();
    // Each bails out immediately when its host element is absent, so this stays cheap on
    // the pages that render none.
    mountStay22Map();
    mountConsentEmbeds();
    mountSetupGuide();
    mountVenueMap();
    mountVenueMapEditor();
    mountTicketPresetsEditor();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountVueWidgets);
} else {
    mountVueWidgets();
}
/**
 * Reveal toggle for <x-password-input>.
 *
 * Delegated from the document and registered once, rather than a script beside each field: the app
 * uses Vue's full build with the runtime template compiler, so several of the ten password fields
 * sit inside an element Vue mounts and compiles - and an inline <script> in a Vue template is not
 * a script, it is markup Vue tries to compile. Delegation also covers a field rendered into a
 * modal after this runs.
 *
 * No inline on* handler anywhere, which the CSP forbids.
 */
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');

    if (! button) {
        return;
    }

    const field = button.parentElement.querySelector('[data-password-field]');

    if (! field) {
        return;
    }

    const reveal = field.type === 'password';
    field.type = reveal ? 'text' : 'password';
    button.setAttribute('aria-label', reveal ? button.dataset.labelHide : button.dataset.labelShow);

    button.querySelectorAll('[data-password-icon]').forEach((icon) => {
        icon.classList.toggle('hidden', (icon.dataset.passwordIcon === 'show') === reveal);
    });
});
