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
            // Terms that name the ERROR ITSELF, matched against the exception value and type (and
            // event.message, for captureMessage events) but deliberately NOT against the rest of
            // the event. Matching the whole serialized event, as this used to, discards a genuine
            // error whenever one of these strings turns up in a breadcrumb, a console message, a
            // request URL or a frame path - and 'Load failed' or 'Network Error' is an entirely
            // plausible fragment of an owner-authored event slug.
            var ignoreMessages = [
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
                'Loading chunk',
                'ChunkLoadError',
                'Network Error',
                'NetworkError',
                'Failed to fetch',
                'Load failed',
                // A visitor's browser extension, injected into the page. denyUrls above matches
                // only the throwing frame, and these arrive with no JS frame at all, so none of
                // the extension-scheme regexes can fire. Page code cannot produce any of these
                // strings - browser.storage and chrome.runtime are not reachable from a document -
                // so nothing of ours is at risk of being swallowed.
                'Invalid call to browser.storage',
                'Invalid call to chrome.storage',
                'Extension context invalidated',
                'Receiving end does not exist',
                'The message port closed before a response',
                'chrome.runtime',
                'browser.runtime',
            ];

            // Terms that name the SOURCE rather than the message, so the giveaway is a frame path
            // somewhere in the event and the match has to stay against the whole thing.
            var ignoreAnywhere = [
                'cloudflare-static',      // Cloudflare Rocket Loader
                'Turnstile',
                '"value":"undefined"',
            ];

            var haystacks = [];
            if (typeof event.message === 'string') {
                haystacks.push(event.message);
            }
            var values = (event.exception && event.exception.values) || [];
            for (var v = 0; v < values.length; v++) {
                if (values[v] && typeof values[v].value === 'string') {
                    haystacks.push(values[v].value);
                }
                if (values[v] && typeof values[v].type === 'string') {
                    haystacks.push(values[v].type);
                }
            }
            for (var h = 0; h < haystacks.length; h++) {
                for (var i = 0; i < ignoreMessages.length; i++) {
                    if (haystacks[h].indexOf(ignoreMessages[i]) !== -1) {
                        return null;
                    }
                }
            }

            var str = JSON.stringify(event);
            for (var j = 0; j < ignoreAnywhere.length; j++) {
                if (str.indexOf(ignoreAnywhere[j]) !== -1) {
                    return null;
                }
            }

            return event;
        }
    });
};
