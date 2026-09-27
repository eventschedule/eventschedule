import { createApp } from 'vue';
import ListAnimationPicker from './components/ListAnimationPicker.vue';

document.querySelectorAll('.vue-list-animation-picker').forEach(el => {
    createApp(ListAnimationPicker, JSON.parse(el.dataset.props)).mount(el);
});
