import { createApp } from 'vue';
import SetupGuide from './components/SetupGuide.vue';
import SetupGuideList from './components/SetupGuideList.vue';

/**
 * Mount the setup guide over the still version partials/setup-guide.blade.php printed.
 *
 * A page has at most one host. Mounting replaces its children with the live shape, which is
 * the same size and in the same place, so nothing on the page moves. If the payload cannot be
 * read the still version simply stays: a plain link to the guide on the dashboard.
 */
export function mountSetupGuide() {
    const host = document.querySelector('[data-setup-guide]');
    const json = document.querySelector('[data-setup-guide-json]');

    if (! host || ! json) {
        return;
    }

    let guide = null;

    try {
        guide = JSON.parse(json.textContent || 'null');
    } catch (error) {
        guide = null;
    }

    if (! guide || ! guide.surface) {
        return;
    }

    // `steps` is the dashboard's card for an account with suggestions and no guide to show:
    // the same rows in the same card, without the guide (SetupGuide::listPayload()).
    createApp(guide.surface === 'steps' ? SetupGuideList : SetupGuide, { guide }).mount(host);
}
