{{--
    The "Continue with Google" button, as a component.

    The markup below was already copy-pasted across seven views when this was added
    (auth/login, auth/register, profile/partials/google-calendar-form,
    profile/partials/update-password-form, marketing/google-calendar, marketing/calendar-sync,
    event/guest-submit). The subscriber surfaces use this instead of making it nine. The existing
    seven can adopt it whenever one of them is next touched.

    The CALLER decides whether Google is configured - `@if (config('services.google.client_id'))` -
    because a caller usually wraps a divider and other copy in the same condition.
--}}
@props(['href' => null])

<a href="{{ $href ?: route('auth.google') }}"
    {{ $attributes->merge(['class' => 'w-full inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{-- me-2 rather than mr-2: this sits beside text in ar/he too. --}}
    <svg class="w-5 h-5 me-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
    </svg>
    {{ $slot }}
</a>
