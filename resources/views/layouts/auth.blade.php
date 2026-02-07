<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">

<head>
    
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

    @if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script {!! nonce_attr() !!}>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        try {
            dataLayer.push(arguments);
        } catch (e) {
            // Handle DataCloneError silently
            console.warn('Analytics data could not be cloned:', e);
        }
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Event Schedule</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark Mode Detection -->
    <script {!! nonce_attr() !!}>
        // Apply dark mode based on system preference
        (function() {
            function applySystemTheme() {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
            
            // Apply immediately to prevent flash
            applySystemTheme();
            
            // Watch for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applySystemTheme);
        })();
    </script>

    {{ isset($head) ? $head : '' }}
</head>

<body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
    {{ isset($abovePage) ? $abovePage : '' }}

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 bg-gray-100 dark:bg-gray-900">
        <a href="{{ marketing_url() }}">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
        </a>

        <div class="flex flex-col lg:flex-row lg:gap-8 w-full max-w-md mx-auto">
            <div class="w-full sm:max-w-md sm:min-w-[28rem] mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        <div class="pt-20"></div>
    </div>
</body>

</html>
