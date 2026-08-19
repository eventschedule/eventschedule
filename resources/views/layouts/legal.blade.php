{{--
    Standalone shell for an operator-authored legal document (issue #116).

    Deliberately NOT <x-marketing-layout>: that shell's header and footer link out
    to eventschedule.com's Features / Pricing / Docs, which is wrong on a selfhost
    policy page, and marketing.css does not define the `custom-content` rules the
    rendered markdown needs. Using one shell on every install keeps the prose
    styling in a single place. The trade-off is that a white-label nexus operator
    who writes their own policy gets this clean branded page rather than their WP
    chrome; swap in the marketing layout behind config('app.is_nexus') if that
    ever matters more than the duplication it would cost.

    Guest-facing, so it stays on the :root/.dark fallback: no theme variants.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $title }} - {{ config('app.name') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    {{-- A policy link gets pasted into email and chat often enough to be worth a
         preview card. No image: the social images are generated per marketing page
         and there is none for a document the operator wrote. --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $title }} - {{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $title }} - {{ config('app.name') }}">

    @include('partials.google-analytics')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.theme-script', ['variants' => false])

    @include('partials.custom-content-styles')

    <style {!! nonce_attr() !!}>
        /* Tables are enabled for these documents only (cookie lists, retention
           schedules), and the shared markdown rules just revert borders, so the
           readable version is scoped here rather than added to every schedule
           description in the app. */
        .legal-prose table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9375rem; }
        .legal-prose th, .legal-prose td { border: 1px solid rgb(209 213 219); padding: 0.5rem 0.75rem; text-align: start; vertical-align: top; }
        .legal-prose th { background-color: rgb(243 244 246); font-weight: 600; }
        .dark .legal-prose th, .dark .legal-prose td { border-color: rgb(55 65 81); }
        .dark .legal-prose th { background-color: rgb(31 41 55); }

        .legal-prose blockquote { border-inline-start: 3px solid rgb(209 213 219); padding-inline-start: 1rem; margin: 1rem 0; font-style: italic; }
        .dark .legal-prose blockquote { border-color: rgb(55 65 81); }

        .legal-prose ul, .legal-prose ol { padding-inline-start: 1.5rem; margin: 0.75rem 0; }
        .legal-prose li { margin: 0.25rem 0; }
        .legal-prose hr { margin: 1.5rem 0; border-top: 1px solid rgb(209 213 219); }
        .dark .legal-prose hr { border-color: rgb(55 65 81); }
    </style>

    {{-- Deliberately NOT partials/site-head-code / site-foot-code. Those inject the
         operator's raw header and footer code ungated by consent, and the marketing
         layout that served /privacy before this feature never included them. The one
         page that discloses tracking is the wrong page to start tracking on. --}}
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[var(--brand-button-bg)] focus:px-4 focus:py-3 focus:text-base focus:text-white focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] ltr:focus:left-4 rtl:focus:right-4">
        {{ __('accessibility.skip_to_main') }}
    </a>

    <div class="min-h-screen flex flex-col">
        <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4">
                <a href="{{ url('/') }}" class="inline-flex items-center">
                    <img class="h-8 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}">
                    <img class="h-8 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}">
                </a>
            </div>
        </header>

        <main id="main-content" tabindex="-1" class="flex-1">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-gray-200 dark:border-gray-800 mt-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-6 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ url('/') }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors duration-200">
                    &larr; {{ __('messages.home') }}
                </a>
            </div>
        </footer>
    </div>

    @include('partials.cookie-banner')

</body>

</html>
