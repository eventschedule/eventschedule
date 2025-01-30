<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="description" content="The simple and free way to share your event schedule">

    <meta property="og:title" content="Event Schedule">
    <meta property="og:description" content="The simple and free way to share your event schedule">
    <meta property="og:image" content="https://eventschedule.com/images/background.jpg">
    <meta property="og:url" content="https://eventschedule.com">
    <meta property="og:site_name" content="Event Schedule">

    <meta name="twitter:title" content="Event Schedule">
    <meta name="twitter:description" content="The simple and free way to share your event schedule">
    <meta name="twitter:image" content="https://eventschedule.com/images/background.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image:alt" content="Event Schedule">

    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data:;
        frame-src 'self';
    ">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Event Schedule</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{ isset($head) ? $head : '' }}
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 bg-gray-100 dark:bg-gray-900">
        <div>
            <a href="https://eventschedule.com">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </div>

        <div
            class="w-full sm:max-w-sm mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>