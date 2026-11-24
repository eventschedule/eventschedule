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

    @php
        // A page may put a bar across the top (the logo at one end, its own slot at the other)
        // instead of the centred logo: sign-up does, for "Already registered? Log in". The card
        // is then centred in the room below the bar. Never under a schedule's own branding.
        $authTopBar = ! $authSchedule && isset($topBar) && trim((string) $topBar) !== '';
    @endphp
    {{-- The page's own ground (--ap-bg: gray-50 in a light palette, gray-900 in a dark one), a
         step below the card, as the 2026-11 design draws it. gray-100 is the palette's
         surface-alt, which left the card barely standing out from the page. --}}
    <div id="main-content" tabindex="-1" class="min-h-screen flex flex-col items-center bg-gray-50 dark:bg-gray-900 {{ $authTopBar ? 'px-4 pt-6 sm:px-6 sm:pt-7' : 'sm:justify-center pt-10' }}">
        @if ($authTopBar)
        <header class="flex w-full max-w-[57.5rem] flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <a href="{{ marketing_url() }}" class="rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                <img class="h-9 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}"/>
                <img class="h-9 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}"/>
            </a>
            {{ $topBar }}
        </header>
        <div class="flex w-full flex-1 flex-col items-center justify-start py-6 sm:justify-center sm:py-10">
        @elseif ($authSchedule)
            {{-- The schedule's own logo and name, and the way back to its page. No platform logo:
                 this is the schedule speaking to its own audience (docs/BRANDING_MATRIX.md). --}}
            {{-- A schedule nobody has claimed has no public page, and getGuestUrl() answers ''
                 for it: a link to '' reloads the page it is on. Its name stands unlinked. --}}
            @php $authScheduleUrl = $authSchedule->getGuestUrl(); @endphp
            @if ($authScheduleUrl)
            <a href="{{ $authScheduleUrl }}" data-auth-schedule class="flex flex-col items-center gap-3 px-4 text-center">
            @else
            <div data-auth-schedule class="flex flex-col items-center gap-3 px-4 text-center">
            @endif
                @if ($authSchedule->profile_image_url)
                    <img src="{{ $authSchedule->profile_image_url }}" alt="" class="h-20 w-20 rounded-2xl object-cover shadow-sm">
                @endif
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100" dir="auto">{{ $authSchedule->translatedName() }}</span>
            @if ($authScheduleUrl)
            </a>
            @else
            </div>
            @endif
        @else
        <a href="{{ marketing_url() }}">
            <x-application-logo class="w-20 h-20 fill-current text-gray-500 dark:text-gray-400" />
        </a>
        @endif

        <div class="flex flex-col lg:flex-row lg:gap-8 w-full {{ $authTopBar ? 'max-w-[28.75rem]' : 'max-w-md' }} mx-auto">
            <div class="auth-card w-full overflow-hidden {{ $authTopBar ? 'auth-card-roomy rounded-[1.25rem] p-6 sm:p-9' : 'sm:max-w-md sm:min-w-[28rem] mt-6 px-6 py-4 sm:rounded-lg' }}">
                {{ $slot }}
            </div>
        </div>

        {{-- Under the card: the privacy policy, and the way back into the cookie banner (GDPR
             Art. 7(3): withdrawing consent must be as easy as giving it). The policy is always
             there on hosted, as the 2026-11 design draws it; off hosted only beside the banner,
             because an install that wrote no policy of its own would otherwise point its users
             at eventschedule.com's (issue #116). "Cookie preferences" only where there is a
             banner to reopen: without one the button would do nothing. --}}
        @php $authCookieReopen = cookie_banner_visible(); @endphp
        @if ($authCookieReopen || config('app.hosted'))
        <p class="{{ $authTopBar ? 'mt-5 gap-x-4 text-[0.8125rem] text-gray-600 dark:text-gray-400' : 'mt-8 gap-x-2 text-xs text-gray-500 dark:text-gray-400' }} flex flex-wrap items-center justify-center gap-y-1 px-4">
            <a href="{{ policy_url('privacy') }}" class="hover:text-gray-900 dark:hover:text-gray-200 hover:underline">{{ __('messages.privacy_policy') }}</a>
            @if ($authCookieReopen)
            @unless ($authTopBar)<span aria-hidden="true">&middot;</span>@endunless
            <button type="button" data-cookie-consent-reopen class="hover:text-gray-900 dark:hover:text-gray-200 hover:underline">{{ __('messages.cookie_consent_manage') }}</button>
            @endif
        </p>
        @endif

        {{-- The credit a schedule's own page carries, where it carries one (Role::creditChipUrl()). --}}
        @if ($authSchedule && ($authCreditUrl = $authSchedule->creditChipUrl()))
        <p class="mt-8 flex justify-center px-4">
            {{-- Per the AAL license, please do not remove the link to Event Schedule --}}
            <a href="{{ $authCreditUrl }}" target="_blank" rel="noopener" title="{{ \App\Models\Role::creditChipTitle() }}"
               class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm ring-1 ring-black/5 transition-colors hover:bg-white hover:text-gray-900">
                <span aria-hidden="true" class="flex h-4 w-4 items-center justify-center rounded-[5px] bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-[8px] font-black leading-none text-white">ES</span>
                <span>Event Schedule</span>
            </a>
        </p>
        @endif

        @if ($authTopBar)
        </div>
        @else
        <div class="pt-20"></div>
        @endif
    </div>

    @include('partials.cookie-banner')

    {{-- The sign-in pages are their own surface on /admin/realtime; this layout's other pages
         (unsubscribe, manage, transfer accept) are reached from emails by guests. --}}
    @include('partials.realtime-beacon', [
        'surface' => request()->routeIs('login', 'sign_up', 'register', 'password.*', 'verification.*', 'two-factor.*') ? 'auth' : 'gp',
    ])
</body>

</html>
