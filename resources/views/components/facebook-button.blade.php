{{--
    The "Continue with Facebook" button. Same outline style as <x-google-button> so the two sit
    together as equals; the mark is Facebook's own "f" in its brand blue, unaltered.

    The CALLER decides whether Facebook is configured - `@if (facebook_login_enabled())` - because
    a caller usually wraps a divider and other copy in the same condition.
--}}
@props(['href' => null, 'lastUsed' => false])

<a href="{{ $href ?: route('auth.facebook') }}" data-social-login
    {{ $attributes->merge(['class' => 'w-full inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{-- me-2 rather than mr-2: this sits beside text in ar/he too. --}}
    <svg class="w-5 h-5 me-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path fill="#1877F2" d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/>
    </svg>
    {{ $slot }}
    @if ($lastUsed)
        <x-last-used-chip />
    @endif
</a>
