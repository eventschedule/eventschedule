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
                // The host app's own injected globals, in webviews that probe for a bridge and
                // never define it. Same reasoning as the extension family above: a document
                // cannot reach any of these, so page code cannot produce the name.
                '__gCrWeb',                   // Chrome on iOS
                '_AutofillCallbackHandler',   // iOS WKWebView autofill
                'instantSearchSDKJSBridge',   // Edge on iOS
                'msDiscoverChatAvailable',    // Edge on iOS
            ];

            // Terms that name the SOURCE rather than the message, so the giveaway is a frame path
            // somewhere in the event and the match has to stay against the whole thing.
            var ignoreAnywhere = [
                'cloudflare-static',      // Cloudflare Rocket Loader
                'Turnstile',
                '"value":"undefined"',
            ];

            // Frame FUNCTION names, matched against that field and nothing else. A third party
            // that injects its script INTO our document, rather than loading it from a URL of its
            // own, leaves frames carrying OUR page URL, so denyUrls above is blind to it - which
            // is how EVENTSCHEDULE-JS-36 arrived with the iabjs:// entry already live: on iOS
            // Meta's in-app browser injects via evaluateJavaScript, and WebKit attributes every
            // frame to the document. Its message is a bare InvalidAccessError, which our own code
            // could raise, so ignoreMessages would over-filter. The function name is the only
            // giveaway left, and reading just that field keeps a breadcrumb, a console message, a
            // request URL or an owner-authored event slug from ever tripping it.
            //
            // Substrings, and deliberately the STEM where there is one: a minified injector ships
            // several near-identical entry points and renames them between releases, so whole
            // names would catch the one code path we happened to be sent and miss the rest.
            // Frames from our own bundle are exempted in the loop below, which is what makes
            // stems safe to use.
            var ignoreFrameFunctions = [
                // Meta's in-app browser (Instagram, Facebook, Messenger, Threads), which is what
                // EVENTSCHEDULE-JS-36 was. mutationObserverCallback is the OUTERMOST frame of
                // that stack, so it also catches the paths that never reach the login-field
                // logger or the bridge.
                'logLoginField',
                'ToBridge',
                'sendPostMessage',
                'mutationObserverCallback',
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

            for (var w = 0; w < values.length; w++) {
                var frames = (values[w] && values[w].stacktrace && values[w].stacktrace.frames) || [];
                if (! Array.isArray(frames)) {
                    continue;
                }
                for (var f = 0; f < frames.length; f++) {
                    var frame = frames[f];
                    if (! frame || typeof frame.function !== 'string') {
                        continue;
                    }
                    // Everything we write is bundled by Vite under /build/assets/, so a frame
                    // from there is OURS whatever it happens to be called. Without this the day
                    // someone writes a sendPostMessage() of our own its crashes stop arriving,
                    // and a filter that has gone quiet looks exactly like a filter that is
                    // working. An injected script cannot forge this: it is served from the
                    // document, never from our build output.
                    var path = frame.filename || frame.abs_path || '';
                    if (typeof path === 'string' && path.indexOf('/build/assets/') !== -1) {
                        continue;
                    }
                    for (var k = 0; k < ignoreFrameFunctions.length; k++) {
                        if (frame.function.indexOf(ignoreFrameFunctions[k]) !== -1) {
                            return null;
                        }
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
