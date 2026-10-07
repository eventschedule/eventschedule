/**
 * Loads Leaflet and its cluster plugin from public/vendor, once per page, the first time a map
 * is wanted (never on a page that only might show one). Local files on purpose: a selfhosted
 * install must not call a CDN. The addresses come from the server, with the version in them.
 *
 * Resolves when window.L is ready AND its stylesheets have arrived; a failed load is forgotten,
 * so a later press can try again.
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

    // The stylesheet is waited for as well as the script: Leaflet lays its panes out with it, and
    // reads the default pin's picture path from it ONCE, so a map built before it arrived had a
    // broken pin for the rest of the page's life. A stylesheet that fails is not waited on.
    const sheet = (href) => new Promise((resolve) => {
        const el = document.createElement('link');
        el.rel = 'stylesheet';
        el.href = href;
        el.onload = resolve;
        el.onerror = resolve;
        document.head.appendChild(el);
    });

    const sheets = Promise.all([urls.leafletCss, urls.clusterCss].filter(Boolean).map(sheet));

    assets = (window.L ? Promise.resolve() : script(urls.leaflet))
        .then(() => (! urls.cluster || window.L.markerClusterGroup ? null : script(urls.cluster)))
        .then(() => sheets)
        .then(() => {
            // Said outright too: Leaflet's own way of finding it looks for a stylesheet whose
            // address ENDS in leaflet.css, which the version on ours does not.
            if (urls.images) {
                window.L.Icon.Default.imagePath = urls.images;
            }
        })
        .catch((e) => {
            assets = null;
            throw e;
        });

    return assets;
};
