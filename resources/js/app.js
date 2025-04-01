import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

//import Toastify from 'toastify-js';
//import 'toastify-js/src/toastify.css';

import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.html-editor').forEach(element => {
        const easyMDE = new EasyMDE({ 
            element: element,
            toolbar: [
                'bold', 'italic', 'heading', '|',
                'quote', 'unordered-list', 'ordered-list', '|',
                'link', '|',
                'preview', 'guide'
            ],
            minHeight: "200px",
            spellChecker: true,
            nativeSpellcheck: true,
            status: false,
        });
    });
});