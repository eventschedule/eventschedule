import { createApp } from 'vue';
import ColorPicker from './components/ColorPicker.vue';

function mountColorPicker(el) {
    const props = JSON.parse(el.dataset.props);
    createApp(ColorPicker, props).mount(el);
}

document.querySelectorAll('.vue-color-picker').forEach(mountColorPicker);

window.mountColorPicker = mountColorPicker;
