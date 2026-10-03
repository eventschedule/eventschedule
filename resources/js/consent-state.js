/*
 * The visitor's cookie choice, readable synchronously by every script on the page.
 *
 * This one file is the only parser of the stored choice. It is inlined into <head> by
 * partials/consent-state.blade.php, so the inline Google Analytics and realtime-beacon partials
 * can read it before any module has loaded, and imported by cookie-consent.js, where the import
 * is a no-op because the inline copy already defined window.esConsent. Keep it ES5 and free of
 * import/export so it stays valid in both places.
 *
 * Two categories, each a separate choice:
 *   analytics  Google Analytics, identified realtime page views, the homepage headline test.
 *   marketing  Advertising and attribution (GA ad signals, utm_* and es_attribution cookies, the
 *              Meta Pixel and Conversions API, AdSense) and third-party content that sets its own
 *              cookies (Stay22, the Google Maps embed, YouTube).
 *
 * Stored in localStorage under `cookie_consent` as {"v":2,"t":<ms>,"c":["analytics",...]} and
 * mirrored by cookie-consent.js into a cookie of the same name ("analytics,marketing", "denied",
 * ...) for the server, which reads it through consent_granted() in app/helpers.php.
 *
 * Version 1 stored a bare "granted" or "denied" from a banner that only ever said "analytics".
 * "granted" is honoured as analytics ONLY, never marketing, and every version-1 value makes the
 * banner ask again. A choice older than twelve months has lapsed and is asked for again too.
 *
 * Global Privacy Control counts as declining everything, whatever is stored.
 */
(function (w) {
    if (w.esConsent) {
        return;
    }

    var KEY = 'cookie_consent';
    var VERSION = 2;
    var MAX_AGE_MS = 365 * 24 * 60 * 60 * 1000;
    var CATEGORIES = ['analytics', 'marketing'];

    var gpc = function () {
        try {
            return w.navigator.globalPrivacyControl === true;
        } catch (e) {
            return false;
        }
    };

    var parse = function (raw) {
        if (raw === 'granted') {
            return { v: 1, t: 0, c: ['analytics'] };
        }
        if (raw === 'denied') {
            return { v: 1, t: 0, c: [] };
        }
        if (!raw) {
            return null;
        }
        try {
            var state = JSON.parse(raw);
            if (!state || typeof state !== 'object' || !Array.isArray(state.c)) {
                return null;
            }

            return {
                v: parseInt(state.v, 10) || 0,
                t: Number(state.t) || 0,
                c: state.c.filter(function (category) {
                    return CATEGORIES.indexOf(category) !== -1;
                }),
            };
        } catch (e) {
            return null;
        }
    };

    var read = function () {
        try {
            return parse(w.localStorage.getItem(KEY));
        } catch (e) {
            return null;
        }
    };

    // Version 1 choices carry no timestamp; they lapse by being asked again, not by age.
    var lapsed = function (state) {
        return state.v >= VERSION && Date.now() - state.t > MAX_AGE_MS;
    };

    var granted = function () {
        if (gpc()) {
            return [];
        }
        var state = read();

        return state && !lapsed(state) ? state.c.slice() : [];
    };

    w.esConsent = {
        KEY: KEY,
        VERSION: VERSION,
        MAX_AGE_MS: MAX_AGE_MS,
        CATEGORIES: CATEGORIES,
        gpc: gpc,
        parse: parse,
        read: read,
        /** True once the visitor has made a current choice, so the banner stays closed. */
        answered: function () {
            var state = read();

            return gpc() || (!!state && state.v === VERSION && !lapsed(state));
        },
        /** The categories in force right now. */
        granted: granted,
        has: function (category) {
            return granted().indexOf(category) !== -1;
        },
    };
})(window);
