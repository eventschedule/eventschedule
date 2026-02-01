import { createApp } from 'vue';
import NewsletterBuilder from './components/NewsletterBuilder.vue';

const el = document.getElementById('newsletter-builder');
if (el) {
    const props = JSON.parse(el.dataset.props);
    const app = createApp(NewsletterBuilder, props);
    app.mount(el);
}
