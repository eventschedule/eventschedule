import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        hmr: {
            host: "192.168.10.10",
        },
        cors: {
            origin: '*',
        },
        host: "192.168.10.10",
        watch: {
            usePolling: true,
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/countrySelect.min.css',
                'resources/js/app.js',
                'resources/js/jquery-3.3.1.min.js',
                'resources/js/countrySelect.min.js',
            ],
            refresh: true,
        }),
    ],
});
