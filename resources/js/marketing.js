import './cookie-consent';

// No Alpine here on purpose: nothing in the marketing/docs/blog render tree uses
// an Alpine directive, so starting it only cost a full-document walk plus ~56 KB.
// The authenticated app (resources/js/app.js) still loads it.
import { createApp } from 'vue';
import { mountAccessibilityWidget } from './accessibility-widget-boot';
import { bootSupportChat } from './support-chat-boot';

window.Vue = { createApp };

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountAccessibilityWidget);
} else {
    mountAccessibilityWidget();
}

bootSupportChat();
