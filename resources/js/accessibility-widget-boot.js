import { createApp } from 'vue';
import AccessibilityWidget from './components/AccessibilityWidget.vue';

const LEGACY_HIDE_LAUNCHER_KEY = 'es_a11y_hide_launcher';

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
    let i18n = {};
    try {
        i18n = JSON.parse(jsonEl.textContent || '{}');
    } catch (e) {
        i18n = {};
    }
    const declarationUrl = i18n.declarationUrl || '';
    delete i18n.declarationUrl;
    const rtl = host.getAttribute('data-rtl') === '1';
    createApp(AccessibilityWidget, { i18n, declarationUrl, rtl }).mount(host);
}
