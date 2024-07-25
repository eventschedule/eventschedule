import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();


import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

document.addEventListener('DOMContentLoaded', () => {
    const easyMDE = new EasyMDE({ 
        element: document.getElementById('description'),
        toolbar: [
            'bold', 'italic', 'heading', '|',
            'quote', 'unordered-list', 'ordered-list', '|',
            'link', 'image', '|',
            'preview', 'guide'
        ],
    });
});