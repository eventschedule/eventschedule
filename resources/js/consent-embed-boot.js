import { createApp } from 'vue';
import ConsentEmbed from './components/ConsentEmbed.vue';

/**
 * Mounts a ConsentEmbed on every [data-consent-embed] host the page rendered
 * (components/consent-embed.blade.php). The host is empty and its props arrive as JSON in the
 * attribute, so no server-rendered text inside it is ever compiled as a Vue template - the
 * same arrangement as stay22-boot.js.
 */
export function mountConsentEmbeds() {
    document.querySelectorAll('[data-consent-embed]').forEach((host) => {
        if (host.dataset.consentEmbedMounted) {
            return;
        }

        let props;

        try {
            props = JSON.parse(host.getAttribute('data-consent-embed') || '{}');
        } catch (e) {
            return;
        }

        if (!props.src) {
            return;
        }

        host.dataset.consentEmbedMounted = '1';
        createApp(ConsentEmbed, props).mount(host);
    });
}
