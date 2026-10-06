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
 * Where it is kept. The `cookie_consent` COOKIE is the record: "analytics.marketing.<t>",
 * "analytics.<t>" or "denied.<t>", with <t> the Unix second the choice was made. One more token,
 * "org" ("analytics.org.<t>"), is not a category: it records that the notice this choice was made
 * on said a schedule's organizer sees visits to its pages (partials/cookie-banner). Only such a
 * choice may put a visitor on an organizer's live page (RealtimeTracker::consentCoversOrganizers());
 * what the notice SHOWED is recorded, because the time of a click says nothing about which notice
 * was clicked (a page cached before the wording changed, a tab left open). cookie-consent.js
 * writes it on config('session.domain') when there is one, so on the hosted service a single
 * choice covers the marketing site, the app and every schedule subdomain: a withdrawal made on one
 * of them is a withdrawal on all of them. The server reads the same cookie through
 * consent_granted() in app/helpers.php, which ignores the trailing <t>.
 *
 * localStorage holds this origin's copy, {"v":2,"t":<ms>,"c":[...],"o":0|1}, kept in step with the cookie
 * on every page load. It is the fallback for when the cookie is gone but the choice is not: Safari
 * caps a cookie written by script at seven days, and someone may clear cookies but not site data.
 *
 * Version 1 stored a bare "granted" or "denied" from a banner that only ever said "analytics".
 * "granted" is honoured as analytics ONLY, never marketing, and only until LEGACY_UNTIL; every
 * version-1 value makes the banner ask again. A choice older than twelve months has lapsed and is
 * asked for again too.
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

    // A version-1 "granted" was given to a banner that named analytics only, and is not dated, so
    // it cannot lapse after twelve months like a current choice. It stops counting on this date
    // (three months after the two-category banner shipped) for anyone who never answered again.
    var LEGACY_UNTIL = Date.UTC(2027, 0, 4);

    var gpc = function () {
        try {
            return w.navigator.globalPrivacyControl === true;
        } catch (e) {
            return false;
        }
    };

    var onlyCategories = function (list) {
        return list.filter(function (category) {
            return CATEGORIES.indexOf(category) !== -1;
        });
    };

    // The two version-1 values, or undefined for anything else.
    var legacy = function (raw) {
        if (raw === 'granted') {
            return Date.now() < LEGACY_UNTIL ? { v: 1, t: 0, c: ['analytics'] } : null;
        }
        if (raw === 'denied') {
            return { v: 1, t: 0, c: [] };
        }

        return undefined;
    };

    /** This origin's localStorage copy. */
    var parse = function (raw) {
        if (!raw) {
            return null;
        }
        var old = legacy(raw);
        if (old !== undefined) {
            return old;
        }
        try {
            var state = JSON.parse(raw);
            if (!state || typeof state !== 'object' || !Array.isArray(state.c)) {
                return null;
            }

            return {
                v: parseInt(state.v, 10) || 0,
                t: Number(state.t) || 0,
                c: onlyCategories(state.c),
                o: state.o === 1 ? 1 : 0,
            };
        } catch (e) {
            return null;
        }
    };

    /** One value of the cookie: "<categories>.<t>", "denied.<t>", or a version-1 value. */
    var parseCookie = function (value) {
        if (!value) {
            return null;
        }
        var old = legacy(value);
        if (old !== undefined) {
            return old;
        }
        var parts = value.split('.');
        var seconds = parseInt(parts.pop(), 10);
        if (!(seconds > 0)) {
            return null;
        }

        // "org" is the marker cookie-consent.js adds, not a category, so it never reaches `c`.
        return { v: VERSION, t: seconds * 1000, c: onlyCategories(parts), o: parts.indexOf('org') !== -1 ? 1 : 0 };
    };

    var readStored = function () {
        try {
            return parse(w.localStorage.getItem(KEY));
        } catch (e) {
            return null;
        }
    };

    // Every cookie_consent the browser sends, newest dated choice first. There can be two: a
    // host-only one left by an older build beside the install-wide one.
    var readCookie = function () {
        var best = null;
        try {
            var pairs = w.document.cookie.split(';');
            for (var i = 0; i < pairs.length; i++) {
                var pair = pairs[i].replace(/^\s+/, '');
                if (pair.indexOf(KEY + '=') !== 0) {
                    continue;
                }
                var state = parseCookie(decodeURIComponent(pair.slice(KEY.length + 1)));
                if (state && (!best || state.v > best.v || (state.v === best.v && state.t > best.t))) {
                    best = state;
                }
            }
        } catch (e) {
            return null;
        }

        return best;
    };

    // A dated choice beats a version-1 one wherever it is kept, and the install-wide cookie beats
    // this origin's copy.
    var read = function () {
        var cookie = readCookie();
        if (cookie && cookie.v >= VERSION) {
            return cookie;
        }
        var stored = readStored();
        if (stored && stored.v >= VERSION) {
            return stored;
        }

        return cookie || stored;
    };

    // Version 1 choices carry no timestamp; they lapse by being asked again (and at LEGACY_UNTIL).
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
        LEGACY_UNTIL: LEGACY_UNTIL,
        CATEGORIES: CATEGORIES,
        gpc: gpc,
        parse: parse,
        parseCookie: parseCookie,
        readCookie: readCookie,
        readStored: readStored,
        read: read,
        lapsed: lapsed,
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
