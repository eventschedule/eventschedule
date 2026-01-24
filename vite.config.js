import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        /*
        hmr: {
            host: "192.168.10.10",
        },
        host: "192.168.10.10",
        */
        cors: {
            origin: '*',
        },
        watch: {
            usePolling: true,
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/countrySelect.min.js',
                //'resources/js/leaflet.js',
                'resources/css/app.css',
                'resources/css/marketing.css',
                'resources/css/countrySelect.min.css',
                //'resources/css/leaflet.css',
            ],
            refresh: true,
        }),
    ],
});
