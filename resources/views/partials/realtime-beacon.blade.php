{{-- The /admin/realtime beacon. Included once by each shell: layouts/app (admin and guest portal),
     layouts/marketing and layouts/auth. Inline rather than a public/js file because those are cached
     for years; vanilla rather than Vue because it is a background beacon with no UI.

     Two modes, chosen here from the visitor's cookie choice and enforced again by the server:
       full        granted the analytics category: page view, heartbeats while visible, end on
                   hide/close.
       count-only  declined analytics, never answered, or Global Privacy Control: ONE page view at the
                   moment of engagement, carrying nothing that links it to the visitor or to
                   their other page views.
     Accepting mid-page upgrades the current page view in place (same key). Withdrawing - a
     "Decline", saving the banner with analytics switched off, or a withdrawal in another tab (the
     storage event, re-checked before any identified send) - sends a revoke that strips identity
     from what the server still holds. An embedded calendar is always count-only. The choice is
     read through window.esConsent (partials/consent-state.blade.php), which also applies GPC.

     It must never throw: errors in an inline script are reported against the page URL, which
     Sentry's denyUrls cannot filter, so every entry point is wrapped.

     The context is built in a php block rather than inline, because the json directive splits its
     argument on commas, and the URL is host-less (see RealtimeTracker::beaconUrl). On an
     edge-cached marketing page the context is the same for every anonymous visitor; `u` is only
     filled for a signed-in render, which is never cached.

     Whether a schedule's owner may see a visitor as a row of their own Realtime tab is NOT
     decided here. The server reads it off the cookie choice this request carries
     (RealtimeTracker::consentCoversOrganizers()), which records whether the notice that was
     answered said so. A page deciding it by comparing dates listed people who had answered an
     older notice after the wording changed. --}}
@php
    $realtimeContext = \App\Utils\RealtimeTracker::context(request(), $surface ?? 'gp', $role ?? null, $event ?? null);
    $realtimeUrl = $realtimeContext ? \App\Utils\RealtimeTracker::beaconUrl() : null;
@endphp
@if ($realtimeContext)
<script {!! nonce_attr() !!}>
(function () {
    try {
        var ctx = @json($realtimeContext);
        var url = @json($realtimeUrl);
        var w = window, d = document, nav = w.navigator;
        if (!nav.sendBeacon && !w.fetch) return;

        var consented = function () {
            try { return !!(w.esConsent && w.esConsent.has('analytics')); } catch (e) { return false; }
        };
        var newKey = function () {
            var out = '';
            try {
                var bytes = new Uint8Array(16);
                (w.crypto || w.msCrypto).getRandomValues(bytes);
                for (var i = 0; i < bytes.length; i++) out += ('0' + bytes[i].toString(16)).slice(-2);
            } catch (e) {
                out = '';
                for (var j = 0; j < 32; j++) out += Math.floor(Math.random() * 16).toString(16);
            }
            return out;
        };

        var framed = false;
        try { framed = w.top !== w.self; } catch (e) { framed = true; }
        // An embedded calendar is never a person on our site and is stored count-only whatever its
        // consent, so it never runs in full mode: no load-time page view, no heartbeats.
        var embed = ctx.em === 1 || framed;
        var key = newKey();
        var full = !embed && consented();
        var heartbeatMs = Math.max(30, ctx.hb || 60) * 1000;

        var navType = 'navigate';
        try {
            var entry = w.performance && w.performance.getEntriesByType && w.performance.getEntriesByType('navigation')[0];
            if (entry && entry.type) navType = entry.type;
        } catch (e) {}

        // hostname, not host: a port would fail the server's host validation.
        var referrerHost = '';
        try { if (d.referrer) referrerHost = new URL(d.referrer).hostname; } catch (e) {}

        var utm = {};
        try {
            var params = new URLSearchParams(w.location.search);
            ['source', 'medium', 'campaign'].forEach(function (name) {
                var value = params.get('utm_' + name);
                if (value) utm[name] = value.slice(0, 100);
            });
        } catch (e) {}

        var send = function (message) {
            var body;
            try {
                message.k = key;
                if (message.t !== 'end') message.c = ctx;
                body = JSON.stringify(message);
            } catch (e) { return; }
            // sendBeacon in its own try, so a browser that throws on it still gets the fetch.
            try {
                if (nav.sendBeacon && nav.sendBeacon(url, new Blob([body], { type: 'application/json' }))) return;
            } catch (e) {}
            try {
                if (w.fetch) {
                    w.fetch(url, { method: 'POST', body: body, keepalive: true, headers: { 'Content-Type': 'application/json' } })
                        .catch(function () {});
                }
            } catch (e) {}
        };

        var pageViewSent = false, countSent = false, engaged = false, ended = false;
        var lastInput = Date.now(), lastBeat = 0, visibleTimer = null, heartbeatTimer = null;

        var pageView = function (mode) {
            send({ t: 'pv', m: mode, r: referrerHost, n: navType, u: utm, ti: (d.title || '').slice(0, 150), fr: framed ? 1 : 0 });
        };
        var live = function () { return full && pageViewSent; };
        var stopHeartbeats = function () {
            if (heartbeatTimer) { clearInterval(heartbeatTimer); heartbeatTimer = null; }
        };
        var withdraw = function () {
            if (!full) return;
            full = false;
            stopHeartbeats();
            send({ t: 'revoke' });
        };
        // The consent event only fires in the tab where the choice was made, so every other tab
        // re-reads the stored choice before it identifies anyone again.
        var stillConsented = function () {
            if (full && !consented()) withdraw();
            return full;
        };
        var beat = function () {
            if (!stillConsented() || !live() || ended || d.visibilityState !== 'visible') return;
            lastBeat = Date.now();
            send({ t: 'hb', g: engaged ? 1 : 0 });
        };
        var startHeartbeats = function () {
            if (heartbeatTimer || !full) return;
            heartbeatTimer = setInterval(function () {
                try { if (Date.now() - lastInput < 300000) beat(); } catch (e) {}
            }, heartbeatMs);
        };

        var engage = function () {
            if (engaged || d.visibilityState !== 'visible') return;
            engaged = true;
            if (live()) {
                beat();
            } else if (!full && !countSent) {
                countSent = true;
                pageView('c');
            }
        };

        var start = function () {
            if (d.visibilityState !== 'visible' || d.prerendering) return;
            // Re-read: a tab opened in the background may first be shown after a withdrawal made
            // in another tab, and must then arrive count-only.
            if (full && !pageViewSent && stillConsented()) {
                pageViewSent = true;
                pageView('f');
                startHeartbeats();
            }
            if (!visibleTimer && !engaged) {
                visibleTimer = setTimeout(function () { try { visibleTimer = null; engage(); } catch (e) {} }, 5000);
            }
        };

        // No `scroll`: scroll restoration on Back and #hash jumps fire it without anyone engaging.
        ['pointerdown', 'keydown', 'touchstart', 'wheel'].forEach(function (name) {
            w.addEventListener(name, function () {
                try {
                    var idleFor = Date.now() - lastInput;
                    lastInput = Date.now();
                    if (d.visibilityState !== 'visible') return;
                    engage();
                    if (idleFor > heartbeatMs && Date.now() - lastBeat > heartbeatMs) beat();
                } catch (e) {}
            }, { passive: true, capture: true });
        });

        d.addEventListener('visibilitychange', function () {
            try {
                if (d.visibilityState === 'visible') {
                    start();
                    if (live() && ended && stillConsented()) {
                        ended = false;
                        beat();
                    }
                } else {
                    // A page seen for under five seconds is not engaged, so the timer stops with it.
                    if (visibleTimer) { clearTimeout(visibleTimer); visibleTimer = null; }
                    if (live() && !ended) {
                        ended = true;
                        send({ t: 'end' });
                    }
                }
            } catch (e) {}
        });

        d.addEventListener('prerenderingchange', function () { try { start(); } catch (e) {} });

        // A withdrawal in another tab of this site, applied now rather than on the next heartbeat.
        // key is null when the whole storage was cleared.
        w.addEventListener('storage', function (event) {
            try {
                if ((event.key === 'cookie_consent' || event.key === null) && !consented()) withdraw();
            } catch (e) {}
        });

        w.addEventListener('pagehide', function () {
            try {
                if (live() && !ended) {
                    ended = true;
                    send({ t: 'end' });
                }
            } catch (e) {}
        });

        // A back/forward-cache restore: visibilitychange usually beat already, so only when ended.
        w.addEventListener('pageshow', function (event) {
            try {
                if (event.persisted && live() && ended && stillConsented()) {
                    ended = false;
                    beat();
                }
            } catch (e) {}
        });

        // cookie-consent.js announces a choice made on this page; re-read rather than trusting the
        // event's payload, so this tab and the stored choice can never disagree.
        d.addEventListener('es:consent-change', function () {
            try {
                var granted = consented();
                if (granted && !full && !embed) {
                    full = true;
                    if (d.visibilityState === 'visible') {
                        // Same key: the server upgrades this page view's count-only row in place.
                        pageViewSent = true;
                        pageView('f');
                        startHeartbeats();
                        if (engaged) beat();
                    }
                } else if (!granted) {
                    withdraw();
                }
            } catch (e) {}
        });

        start();
    } catch (e) {}
})();
</script>
@endif
