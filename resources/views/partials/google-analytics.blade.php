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
    @php $gaId = config('services.google.analytics'); @endphp
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
        gtag('config', @json($gaId));
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}" {!! nonce_attr() !!}></script>
@endif
