import './cookie-consent';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import { createApp } from 'vue';
import { mountAccessibilityWidget } from './accessibility-widget-boot';

window.Vue = { createApp };

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountAccessibilityWidget);
} else {
    mountAccessibilityWidget();
}
