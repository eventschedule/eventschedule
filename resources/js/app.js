// Every page of the app: what both bundles share, then the Markdown editors, in the order this file
// ran in before it was split (the editors' DOMContentLoaded handler ahead of the widgets'). A guest
// page with no editor on it is served resources/js/guest.js instead, the same without the editors.
import './app-head';
import './editors';
import './app-tail';
