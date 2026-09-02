{{--
    Google Analytics 4 with Consent Mode v2.

    Default-denied for every consent flag. The same inline tick checks
    localStorage and replays a 'granted' update for returning visitors who
    accepted, so the first beacon on the next visit already fires with
    cookies. The banner (resources/views/partials/cookie-banner.blade.php)
    and resources/js/cookie-consent.js are what flip the localStorage flag.

    No <noscript> fallback: GA4 has no image-pixel beacon, so an image tag
    here would either 404 or bypass Consent Mode entirely.
--}}
@if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
    @php
        $gaId = config('services.google.analytics');

        // gtag('config') sends page_location, which is the FULL current URL. On these routes the
        // URL is the credential: /sub/u/{token} and /nl/u/{token} carry a bearer token that
        // suppresses an address, and sub/u is CSRF-exempt for RFC 8058, so anyone holding one can
        // unsubscribe that person everywhere with ?all=1. Consent Mode denies storage, not the hit
        // itself, so the token would reach Google either way.
        //
        // Redacted to the route's own URI pattern - "sub/u/{token}" - which is what an analytics
        // report actually wants anyway: one row per page instead of one per recipient.
        $tokenRoutes = [
            'subscriber.show_confirm', 'subscriber.confirm',
            'subscriber.show_unsubscribe', 'subscriber.unsubscribe',
            'newsletter.show_unsubscribe', 'newsletter.unsubscribe',
        ];
        $gaRedactedLocation = in_array(request()->route()?->getName(), $tokenRoutes, true)
            ? url(request()->route()->uri())
            : null;
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
        try {
            if (navigator.globalPrivacyControl === true) {
                localStorage.setItem('cookie_consent', 'denied');
            } else if (localStorage.getItem('cookie_consent') === 'granted') {
                gtag('consent', 'update', {
                    ad_storage: 'granted',
                    ad_user_data: 'granted',
                    ad_personalization: 'granted',
                    analytics_storage: 'granted'
                });
            }
        } catch (e) {}
        gtag('js', new Date());
        @if ($gaRedactedLocation)
            gtag('config', @json($gaId), { page_location: @json($gaRedactedLocation) });
        @else
            gtag('config', @json($gaId));
        @endif
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}" {!! nonce_attr() !!}></script>
@endif
