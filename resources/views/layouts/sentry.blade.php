window.sentryOnLoad = function () {
    Sentry.init({
        beforeSend: function (event) {
            var str = JSON.stringify(event);
            var ignore = [
                'Script error.',
                'Vue failed to load',
                'Non-Error promise rejection',
                'Share canceled',
                'Unexpected token',
                'ResizeObserver loop',
                'webkit.messageHandlers',
                'contentWindow',
                'Java object is gone',
                'cloudflare-static',
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
