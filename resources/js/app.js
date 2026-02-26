import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;

// Import flatpickr locales for all supported languages
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import { German } from 'flatpickr/dist/l10n/de.js';
import { French } from 'flatpickr/dist/l10n/fr.js';
import { Italian } from 'flatpickr/dist/l10n/it.js';
import { Portuguese } from 'flatpickr/dist/l10n/pt.js';
import { Hebrew } from 'flatpickr/dist/l10n/he.js';
import { Dutch } from 'flatpickr/dist/l10n/nl.js';
import { Arabic } from 'flatpickr/dist/l10n/ar.js';
import { Russian } from 'flatpickr/dist/l10n/ru.js';

// Map Laravel locale codes to flatpickr locale objects
window.flatpickrLocales = {
    en: null,
    es: Spanish,
    de: German,
    fr: French,
    it: Italian,
    pt: Portuguese,
    he: Hebrew,
    nl: Dutch,
    ar: Arabic,
    ru: Russian
};

//import Toastify from 'toastify-js';
//import 'toastify-js/src/toastify.css';

import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

document.addEventListener('DOMContentLoaded', () => {
    const t = window.editorTranslations || {};
    document.querySelectorAll('.html-editor').forEach(element => {
        const easyMDE = new EasyMDE({
            element: element,
            toolbar: [
                {
                    name: "bold",
                    action: EasyMDE.toggleBold,
                    className: "editor-button-text",
                    title: t.bold || "Bold",
                    text: "B"
                },
                {
                    name: "italic",
                    action: EasyMDE.toggleItalic,
                    className: "editor-button-text",
                    title: t.italic || "Italic",
                    text: "I"
                },
                {
                    name: "heading",
                    action: EasyMDE.toggleHeadingSmaller,
                    className: "editor-button-text",
                    title: t.heading || "Heading",
                    text: "H"
                },
                "|",
                {
                    name: "link",
                    action: function(editor) {
                        EasyMDE.drawLink(editor);
                    },
                    className: "editor-button-text",
                    title: t.link || "Link",
                    text: "🔗"
                },
                {
                    name: "quote",
                    action: EasyMDE.toggleBlockquote,
                    className: "editor-button-text",
                    title: t.quote || "Quote",
                    text: "\""
                },
                {
                    name: "unordered-list",
                    action: EasyMDE.toggleUnorderedList,
                    className: "editor-button-text",
                    title: t.unorderedList || "Unordered List",
                    text: "UL"
                },
                {
                    name: "ordered-list",
                    action: EasyMDE.toggleOrderedList,
                    className: "editor-button-text",
                    title: t.orderedList || "Ordered List",
                    text: "OL"
                },
                "|",
                {
                    name: "preview",
                    action: EasyMDE.togglePreview,
                    className: "editor-button-text no-disable",
                    title: t.preview || "Toggle Preview",
                    text: "👁"
                },
                {
                    name: "guide",
                    action: "https://www.markdownguide.org/basic-syntax/",
                    className: "editor-button-text",
                    title: t.guide || "Markdown Guide",
                    text: "?"
                }
            ],
            minHeight: "200px",
            spellChecker: true,
            nativeSpellcheck: true,
            status: false,
        });

        easyMDE.codemirror.on('change', function() {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
});

// Make EasyMDE available globally for Vue components (tiny version for agenda descriptions)
window.initTinyMDE = function(element, onChange) {
    const t = window.editorTranslations || {};
    const easyMDE = new EasyMDE({
        element: element,
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "editor-button-text", title: t.bold || "Bold", text: "B" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "editor-button-text", title: t.italic || "Italic", text: "I" },
            { name: "link", action: (editor) => EasyMDE.drawLink(editor), className: "editor-button-text", title: t.link || "Link", text: "🔗" },
            "|",
            { name: "quote", action: EasyMDE.toggleBlockquote, className: "editor-button-text", title: t.quote || "Quote", text: "\"" },
            { name: "unordered-list", action: EasyMDE.toggleUnorderedList, className: "editor-button-text", title: t.unorderedList || "Unordered List", text: "UL" },
            { name: "ordered-list", action: EasyMDE.toggleOrderedList, className: "editor-button-text", title: t.orderedList || "Ordered List", text: "OL" },
            "|",
            { name: "preview", action: EasyMDE.togglePreview, className: "editor-button-text no-disable", title: t.preview || "Toggle Preview", text: "👁" },
            { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "editor-button-text", title: t.guide || "Markdown Guide", text: "?" }
        ],
        minHeight: "80px",
        spellChecker: false,
        status: false,
    });

    if (onChange) {
        easyMDE.codemirror.on('change', onChange);
    }

    return easyMDE;
};