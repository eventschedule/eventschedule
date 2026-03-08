import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gray: {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#3e3e42',
                    700: '#2d2d30',
                    800: '#252526',
                    900: '#1e1e1e',
                },
                green: {
                    400: '#5edd8d',
                },
                red: {
                    400: '#f68787',
                },
                amber: {
                    400: '#fcc73d',
                },
                blue: {
                    400: '#77b0f7',
                },
                indigo: {
                    400: '#969ff6',
                },
                purple: {
                    400: '#ca9af9',
                },
            },
        },
    },

    plugins: [
        forms,
    ],
};
