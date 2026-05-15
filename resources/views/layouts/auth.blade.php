<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">

<head>
    <meta name="robots" content="noindex, nofollow">

    <meta name="description" content="The simple and free way to share your event schedule">
    <meta property="og:title" content="Event Schedule">
    <meta property="og:description" content="The simple and free way to share your event schedule">
    <meta property="og:image" content="{{ asset('images/social/home.png') }}">
    <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta property="og:site_name" content="Event Schedule">
    <meta name="twitter:title" content="Event Schedule">
    <meta name="twitter:description" content="The simple and free way to share your event schedule">
    <meta name="twitter:image" content="{{ asset('images/social/home.png') }}">
    <meta name="twitter:image:alt" content="Event Schedule">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    @include('partials.google-analytics')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Event Schedule</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Detection -->
    <script {!! nonce_attr() !!}>
        // Apply dark mode based on localStorage theme preference, falling back to system preference
        (function() {
            var storedTheme;
            try { storedTheme = localStorage.getItem('theme'); } catch (e) {}

            function getEffectiveTheme() {
                if (storedTheme === 'dark') return 'dark';
                if (storedTheme === 'light') return 'light';
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function applyTheme() {
                if (getEffectiveTheme() === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            // Apply immediately to prevent flash
            applyTheme();

            // Watch for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
        })();
    </script>

    {{ isset($head) ? $head : '' }}
</head>

<body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[var(--brand-button-bg)] focus:px-4 focus:py-3 focus:text-base focus:text-white focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] ltr:focus:left-4 rtl:focus:right-4">
        {{ __('accessibility.skip_to_main') }}
    </a>

    {{ isset($abovePage) ? $abovePage : '' }}

    <div id="main-content" tabindex="-1" class="min-h-screen flex flex-col sm:justify-center items-center pt-10 bg-gray-100 dark:bg-gray-900">
        <a href="{{ marketing_url() }}">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
        </a>

        <div class="flex flex-col lg:flex-row lg:gap-8 w-full max-w-md mx-auto">
            <div class="auth-card w-full sm:max-w-md sm:min-w-[28rem] mt-6 px-6 py-4 overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <div class="pt-20"></div>
    </div>

    @include('partials.cookie-banner')
</body>

</html>
