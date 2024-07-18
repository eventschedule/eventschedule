import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();


import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.css";

// Example: Initialize Flatpickr on elements with the class 'datepicker'
document.addEventListener('DOMContentLoaded', function () {
    flatpickr('.datepicker');
});