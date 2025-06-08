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
                {
                    name: "bold",
                    action: EasyMDE.toggleBold,
                    className: "editor-button-text",
                    title: "Bold",
                    text: "B"
                },
                {
                    name: "italic",
                    action: EasyMDE.toggleItalic,
                    className: "editor-button-text",
                    title: "Italic",
                    text: "I"
                },
                {
                    name: "heading",
                    action: EasyMDE.toggleHeadingSmaller,
                    className: "editor-button-text",
                    title: "Heading",
                    text: "H"
                },
                "|",
                {
                    name: "quote",
                    action: EasyMDE.toggleBlockquote,
                    className: "editor-button-text",
                    title: "Quote",
                    text: "\""
                },
                {
                    name: "unordered-list",
                    action: EasyMDE.toggleUnorderedList,
                    className: "editor-button-text",
                    title: "Unordered List",
                    text: "UL"
                },
                {
                    name: "ordered-list",
                    action: EasyMDE.toggleOrderedList,
                    className: "editor-button-text",
                    title: "Ordered List",
                    text: "OL"
                },
                "|",
                {
                    name: "preview",
                    action: EasyMDE.togglePreview,
                    className: "editor-button-text no-disable",
                    title: "Toggle Preview",
                    text: "👁"
                },
                {
                    name: "guide",
                    action: "https://www.markdownguide.org/basic-syntax/",
                    className: "editor-button-text",
                    title: "Markdown Guide",
                    text: "?"
                }
            ],
            minHeight: "200px",
            spellChecker: true,
            nativeSpellcheck: true,  
            status: false,
        });
    });
});