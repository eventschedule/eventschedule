{{-- The browser error SDK, after layouts.sentry has defined window.sentryOnLoad.

     Served from public/vendor/sentry rather than Sentry's CDN loader (CLAUDE.md: no CDNs), so a
     visitor's browser contacts Sentry only to report an error, never just to load the page. The
     bundle is @sentry/browser 8.55.2 errors-only, exactly what the loader served: no tracing, no
     session replay. SENTRY_JS_DSN still overrides it with a CDN loader URL, for an install that
     configured one before this.

     $afterLoad: start once the page has loaded (the marketing layout, where nothing should compete
     with the first paint); otherwise at once. --}}
@if (config('app.sentry_js_dsn'))
<script {!! nonce_attr() !!}>
    (function () {
        // Read while this runs: the page body carries no per-request value.
        var nonce = (document.currentScript && document.currentScript.nonce) || '';
        var start = function () {
            var script = document.createElement('script');
            script.src = @json(config('app.sentry_js_dsn'));
            script.crossOrigin = 'anonymous';
            script.nonce = nonce;
            document.head.appendChild(script);
        };
        @if ($afterLoad ?? false)
            window.addEventListener('load', start);
        @else
            start();
        @endif
    })();
</script>
@else
<script {!! nonce_attr() !!}>
    (function () {
        // Read while this runs: the page body carries no per-request value.
        var nonce = (document.currentScript && document.currentScript.nonce) || '';
        var start = function () {
            var script = document.createElement('script');
            script.src = @json(asset('vendor/sentry/bundle-8.55.2.min.js'));
            script.nonce = nonce;
            script.onload = function () {
                try {
                    if (window.sentryOnLoad) {
                        window.sentryOnLoad();
                    }
                } catch (e) {}
            };
            document.head.appendChild(script);
        };
        @if ($afterLoad ?? false)
            window.addEventListener('load', start);
        @else
            start();
        @endif
    })();
</script>
@endif
