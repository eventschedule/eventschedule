import { createApp } from 'vue';
import VendorMapDesigner from './vendor-map/VendorMapDesigner.vue';

// Mounted on the element carrying data-vue-root, so a description printed elsewhere on a page
// can never stand in for it (MarkdownUtils::RESERVED_IDS).
const el = document.querySelector('#vendor-map-designer[data-vue-root]');
if (el) {
    createApp(VendorMapDesigner, JSON.parse(el.dataset.props)).mount(el);
}
