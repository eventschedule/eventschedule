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
                // The gray ramp and white resolve through CSS variables so the
                // whole app follows the active theme without touching a single
                // template. The variables are defined in resources/css/app.css
                // (and mirrored as a fallback in marketing-app.css); a page that
                // sets no data-theme gets today's values verbatim.
                //
                // Channels are space-separated so `<alpha-value>` works and
                // opacity utilities like `bg-gray-800/50` keep functioning.
                //
                // Careful: @tailwindcss/forms inlines colors.gray.500 into an
                // SVG data URI for the native <select> chevron, and a data URI
                // cannot resolve a page-level var(). app.css re-declares that
                // background-image with a literal stroke - do not remove it.
                white: 'rgb(var(--ap-white) / <alpha-value>)',
                gray: {
                    50: 'rgb(var(--ap-gray-50) / <alpha-value>)',
                    100: 'rgb(var(--ap-gray-100) / <alpha-value>)',
                    200: 'rgb(var(--ap-gray-200) / <alpha-value>)',
                    300: 'rgb(var(--ap-gray-300) / <alpha-value>)',
                    400: 'rgb(var(--ap-gray-400) / <alpha-value>)',
                    500: 'rgb(var(--ap-gray-500) / <alpha-value>)',
                    600: 'rgb(var(--ap-gray-600) / <alpha-value>)',
                    700: 'rgb(var(--ap-gray-700) / <alpha-value>)',
                    800: 'rgb(var(--ap-gray-800) / <alpha-value>)',
                    900: 'rgb(var(--ap-gray-900) / <alpha-value>)',
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
