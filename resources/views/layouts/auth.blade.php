<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">

<head>
    <meta name="robots" content="noindex, nofollow">

    <meta name="description" content="The simple and free way to share your event schedule">
    <meta property="og:title" content="Event Schedule">
    <meta property="og:description" content="The simple and free way to share your event schedule">
    <meta property="og:image" content="{{ asset('images/social/home.jpg') }}">
    <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta property="og:site_name" content="Event Schedule">
    <meta name="twitter:title" content="Event Schedule">
    <meta name="twitter:description" content="The simple and free way to share your event schedule">
    <meta name="twitter:image" content="{{ asset('images/social/home.jpg') }}">
    <meta name="twitter:image:alt" content="Event Schedule">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-96.png') }}" sizes="96x96">

    @include('partials.consent-state')
    @include('partials.google-analytics')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // The schedule whose own mail led here, or null: see App\View\Components\AuthLayout.
        $authSchedule = (($schedule ?? null) instanceof \App\Models\Role && $schedule->exists) ? $schedule : null;
    @endphp
    <title>{{ $authSchedule ? $authSchedule->translatedName() : 'Event Schedule' }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.theme-script', ['variants' => true])

    {{ isset($head) ? $head : '' }}
</head>

<body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[var(--brand-button-bg)] focus:px-4 focus:py-3 focus:text-base focus:text-white focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] ltr:focus:left-4 rtl:focus:right-4">
        {{ __('accessibility.skip_to_main') }}
    </a>

    {{ isset($abovePage) ? $abovePage : '' }}

    <div id="main-content" tabindex="-1" class="min-h-screen flex flex-col sm:justify-center items-center pt-10 bg-gray-100 dark:bg-gray-900">
        @if ($authSchedule)
            {{-- The schedule's own logo and name, and the way back to its page. No platform logo:
                 this is the schedule speaking to its own audience (docs/BRANDING_MATRIX.md). --}}
            <a href="{{ $authSchedule->getGuestUrl() }}" data-auth-schedule class="flex flex-col items-center gap-3 px-4 text-center">
                @if ($authSchedule->profile_image_url)
                    <img src="{{ $authSchedule->profile_image_url }}" alt="" class="h-20 w-20 rounded-2xl object-cover shadow-sm">
                @endif
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100" dir="auto">{{ $authSchedule->translatedName() }}</span>
            </a>
        @else
        <a href="{{ marketing_url() }}">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
        </a>
        @endif

        <div class="flex flex-col lg:flex-row lg:gap-8 w-full max-w-md mx-auto">
            <div class="auth-card w-full sm:max-w-md sm:min-w-[28rem] mt-6 px-6 py-4 overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>

        {{-- The way back into the cookie banner (GDPR Art. 7(3): withdrawing consent must be as
             easy as giving it), on the pages that asked for it. --}}
        @if (cookie_banner_visible())
        <p class="mt-8 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 px-4 text-xs text-gray-500 dark:text-gray-400">
            <a href="{{ policy_url('privacy') }}" class="hover:text-gray-700 dark:hover:text-gray-200 hover:underline">{{ __('messages.privacy_policy') }}</a>
            <span aria-hidden="true">&middot;</span>
            <button type="button" data-cookie-consent-reopen class="hover:text-gray-700 dark:hover:text-gray-200 hover:underline">{{ __('messages.cookie_consent_manage') }}</button>
        </p>
        @endif

        {{-- The credit a schedule's own page carries, where it carries one (Role::creditChipUrl()). --}}
        @if ($authSchedule && ($authCreditUrl = $authSchedule->creditChipUrl()))
        <p class="mt-8 flex justify-center px-4">
            {{-- Per the AAL license, please do not remove the link to Event Schedule --}}
            <a href="{{ $authCreditUrl }}" target="_blank" rel="noopener" title="{{ __('messages.powered_by_event_schedule') }}"
               class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm ring-1 ring-black/5 transition-colors hover:bg-white hover:text-gray-900">
                <span aria-hidden="true" class="flex h-4 w-4 items-center justify-center rounded-[5px] bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-[8px] font-black leading-none text-white">ES</span>
                <span>Event Schedule</span>
            </a>
        </p>
        @endif

        <div class="pt-20"></div>
    </div>

    @include('partials.cookie-banner')

    {{-- The sign-in pages are their own surface on /admin/realtime; this layout's other pages
         (unsubscribe, manage, transfer accept) are reached from emails by guests. --}}
    @include('partials.realtime-beacon', [
        'surface' => request()->routeIs('login', 'sign_up', 'register', 'password.*', 'verification.*', 'two-factor.*') ? 'auth' : 'gp',
    ])
</body>

</html>
