/**
 * Loads Leaflet and its cluster plugin from public/vendor, once per page, the first time a map
 * is wanted (never on a page that only might show one). Local files on purpose: a selfhosted
 * install must not call a CDN. The four addresses come from the server, with the version in them.
 *
 * Resolves when window.L is ready; a failed load is forgotten, so a later press can try again.
 */
let assets = null;

export const loadLeaflet = (urls) => {
    if (assets) {
        return assets;
    }

    const script = (src) => new Promise((resolve, reject) => {
        const el = document.createElement('script');
        el.src = src;
        el.onload = resolve;
        el.onerror = reject;
        document.head.appendChild(el);
    });

    [urls.leafletCss, urls.clusterCss].filter(Boolean).forEach((href) => {
        const el = document.createElement('link');
        el.rel = 'stylesheet';
        el.href = href;
        document.head.appendChild(el);
    });

    assets = (window.L ? Promise.resolve() : script(urls.leaflet))
        .then(() => (! urls.cluster || window.L.markerClusterGroup ? null : script(urls.cluster)))
        .catch((e) => {
            assets = null;
            throw e;
        });

    return assets;
};
