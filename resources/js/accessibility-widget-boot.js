import { createApp } from 'vue';
import AccessibilityWidget from './components/AccessibilityWidget.vue';

const LEGACY_HIDE_LAUNCHER_KEY = 'es_a11y_hide_launcher';
const HIDE_WIDGET_KEY = 'es_a11y_hide_widget';

export function mountAccessibilityWidget() {
    try {
        localStorage.removeItem(LEGACY_HIDE_LAUNCHER_KEY);
    } catch (e) {
        // ignore
    }

    const host = document.getElementById('es-a11y-widget-host');
    const jsonEl = document.getElementById('es-a11y-json');
    if (!host || !jsonEl) {
        return;
    }
    const authenticated = host.getAttribute('data-authenticated') === '1';
    if (authenticated) {
        try {
            if (localStorage.getItem(HIDE_WIDGET_KEY) === '1') {
                return;
            }
        } catch (e) {
            // ignore
        }
    }
    let i18n = {};
    try {
        i18n = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        i18n = {};
    }
    const declarationUrl = i18n.declarationUrl || '';
    delete i18n.declarationUrl;
    const rtl = host.getAttribute('data-rtl') === '1';
    createApp(AccessibilityWidget, { i18n, declarationUrl, rtl, isAuthenticated: authenticated }).mount(host);
}
