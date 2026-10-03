{{--
    Google Analytics 4, loaded only with consent ("basic" Consent Mode).

    gtag.js is not requested at all until the visitor grants the analytics category, so a visitor
    who declines, never answers or sends Global Privacy Control sends Google nothing - not even
    the cookieless pings "advanced" Consent Mode would send, which carry the page address and the
    visitor's IP and which several EU regulators treat as needing consent anyway. The consent
    defaults are still pushed first, so the moment the script does load it starts from the
    visitor's actual choice. Ad signals follow the marketing category, separately.

    window.esConsent (partials/consent-state.blade.php, included just before this) answers the
    question; cookie-consent.js announces a change made on the page with 'es:consent-change'.

    What Google is told about the page is redacted for every page, not a list of known ones:
      - the path, with every secret route parameter (ticket and order secrets, reset, confirm and
        unsubscribe tokens, ...) replaced by its name, the same rule /admin/realtime uses
        (RealtimeTracker::redactedPath). Null when the route has none, so an edge-cached
        marketing page renders identically for everyone and the browser uses its own path;
      - the query string, reduced to utm_* tags. Everything else goes, including the ?email= that
        sign-up, login and reset links carry (base64, which Google's own email redaction misses)
        and the signature on account unsubscribe links;
      - the referrer, reduced to its origin, because the page someone came FROM can be a ticket
        link too.

    No embed: an embedded calendar is a page on someone else's site, where storage is partitioned
    and this banner is not the right place to ask. No <noscript> fallback: GA4 has no image-pixel
    beacon, so an image tag here would either 404 or bypass consent entirely.
--}}
@if (config('services.google.analytics') && ! request()->embed && (! auth()->user() || ! auth()->user()->isAdmin()))
    @php
        $gaId = config('services.google.analytics');
        $gaRedactedPath = request()->route() ? \App\Utils\RealtimeTracker::redactedPath(request()) : null;
    @endphp
    <script {!! nonce_attr() !!}>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            try {
                dataLayer.push(arguments);
            } catch (e) {
                console.warn('Analytics data could not be cloned:', e);
            }
        }
        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied'
        });
        gtag('set', 'ads_data_redaction', true);
        (function () {
            var id = @json($gaId);
            var redactedPath = @json($gaRedactedPath);
            // From this script element rather than printed into it, so the page body carries no
            // per-request value. document.currentScript is only set while this runs, so read now.
            var nonce = (document.currentScript && document.currentScript.nonce) || '';
            var loaded = false;

            var pageLocation = function () {
                var l = window.location;
                var keep = [];
                try {
                    new URLSearchParams(l.search).forEach(function (value, key) {
                        if (/^utm_/.test(key)) {
                            keep.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
                        }
                    });
                } catch (e) {}

                return l.origin + (redactedPath || l.pathname) + (keep.length ? '?' + keep.join('&') : '');
            };

            var pageReferrer = function () {
                try {
                    return document.referrer ? new URL(document.referrer).origin + '/' : '';
                } catch (e) {
                    return '';
                }
            };

            var apply = function () {
                var consent = window.esConsent;
                var analytics = !!(consent && consent.has('analytics'));
                var marketing = !!(consent && consent.has('marketing'));

                gtag('consent', 'update', {
                    ad_storage: marketing ? 'granted' : 'denied',
                    ad_user_data: marketing ? 'granted' : 'denied',
                    ad_personalization: marketing ? 'granted' : 'denied',
                    analytics_storage: analytics ? 'granted' : 'denied'
                });

                if (!analytics || loaded) {
                    return;
                }

                loaded = true;
                gtag('js', new Date());
                gtag('config', id, { page_location: pageLocation(), page_referrer: pageReferrer() });

                var script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
                script.nonce = nonce;
                document.head.appendChild(script);
            };

            try {
                apply();
            } catch (e) {}
            document.addEventListener('es:consent-change', function () {
                try {
                    apply();
                } catch (e) {}
            });
        })();
    </script>
@endif
