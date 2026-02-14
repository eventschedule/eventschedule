import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

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
                'resources/js/marketing.js',
                'resources/js/countrySelect.min.js',
                'resources/js/newsletter-builder.js',
                'resources/js/color-picker.js',
                //'resources/js/leaflet.js',
                'resources/css/app.css',
                'resources/css/marketing-app.css',
                'resources/css/marketing.css',
                'resources/css/countrySelect.min.css',
                //'resources/css/leaflet.css',
            ],
            refresh: true,
        }),
        vue(),
    ],
});
