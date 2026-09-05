// FROZEN SNAPSHOT - NOT LIVE CONFIGURATION. Do not edit to match the real filter.
//
// This is resources/views/layouts/sentry.blade.php as it stood before its ignore list
// was split into ignoreMessages (matched against the exception value and type) and
// ignoreAnywhere (matched against the whole serialized event). Every term used to be
// matched against the whole event, so a genuine crash was discarded whenever a string
// like 'Load failed' appeared in an unrelated breadcrumb.
//
// SentryJsFilterTest runs a payload through both this and the current filter, so the
// behaviour change is asserted rather than merely described. It is meant to drift.
window.sentryOnLoad = function () {
    Sentry.init({
        // Third-party scripts we neither ship nor control. Cloudflare injects its Web Analytics
        // beacon same-origin on proxied customer domains, so its crashes arrive with a real message
        // and a full stack rather than the opaque 'Script error.' the list below already drops.
        // Meta's in-app browser injects its own telemetry under iabjs://, which throws on teardown
        // because it postMessages across an Android bridge that native has already torn down.
        // denyUrls matches only the throwing frame, so our own errors are still reported when a
        // third party merely triggers them.
        denyUrls: [
            /\/beacon\.min\.js/i,        // Cloudflare Web Analytics
            /\/cdn-cgi\//i,              // Cloudflare Rocket Loader, email decode, RUM
            /cloudflareinsights\.com/i,
            /^chrome-extension:\/\//i,
            /^moz-extension:\/\//i,
            /^safari-(web-)?extension:\/\//i,
            /^chrome:\/\//i,
            /^iabjs:\/\//i,              // Meta in-app browser (Instagram, Facebook, Messenger, Threads)
        ],
        beforeSend: function (event) {
            var str = JSON.stringify(event);
            var ignore = [
                'Script error.',
                'Vue failed to load',
                'Non-Error promise rejection',
                '`) captured as promise rejection',
                'Share canceled',
                'Unexpected token',
                'ResizeObserver loop',
                'webkit.messageHandlers',
                'WKWebView',
                'contentWindow',
                'Java object is gone',
                'Java exception was raised',
                'cloudflare-static',
                'Turnstile',
                'Loading chunk',
                'ChunkLoadError',
                'Network Error',
                'NetworkError',
                'Failed to fetch',
                'Load failed',
            ];
            for (var i = 0; i < ignore.length; i++) {
                if (str.indexOf(ignore[i]) !== -1) {
                    return null;
                }
            }
            if (str.indexOf('"value":"undefined"') !== -1) {
                return null;
            }
            return event;
        }
    });
};
