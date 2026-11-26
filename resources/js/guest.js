// A guest page with no editor on it (layouts/app-guest decides, from what the page drew): what
// resources/js/app.js runs, without EasyMDE. That was a third of a megabyte of script parsed on every
// schedule and event page, where nobody edits anything.
import './app-head';
import './app-tail';
