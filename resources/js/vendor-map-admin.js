import { createApp } from 'vue';
import VendorMapAdmin from './vendor-map/VendorMapAdmin.vue';

// The admin tab's own app. Mounted only on the element carrying data-vue-root
// (MarkdownUtils::RESERVED_IDS).
const el = document.querySelector('#vendor-map-admin[data-vue-root]');
if (el) {
    createApp(VendorMapAdmin, JSON.parse(el.dataset.props)).mount(el);
}
