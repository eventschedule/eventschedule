import { createApp } from 'vue';
import SeatingPlanDesigner from './components/SeatingPlanDesigner.vue';

const el = document.getElementById('seating-designer');
if (el) {
    const props = JSON.parse(el.dataset.props);
    createApp(SeatingPlanDesigner, props).mount(el);
}
