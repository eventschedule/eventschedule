import { createApp } from 'vue';
import SeatingBoxOffice from './components/SeatingBoxOffice.vue';

const el = document.getElementById('seating-box-office');
if (el) {
    createApp(SeatingBoxOffice, JSON.parse(el.dataset.props)).mount(el);
}
