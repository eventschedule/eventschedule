// The first half of what every page of the app runs (resources/js/app.js and resources/js/guest.js):
// the bootstrap, cookie consent, Alpine and the date picker. Split out
// so a guest page that has no editor can leave EasyMDE out (guest.js); see app.js for the order.
import './bootstrap';
import './cookie-consent';

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
    'pt-PT': Portuguese,
    he: Hebrew,
    nl: Dutch,
    ar: Arabic,
    ru: Russian
};

//import Toastify from 'toastify-js';
//import 'toastify-js/src/toastify.css';
