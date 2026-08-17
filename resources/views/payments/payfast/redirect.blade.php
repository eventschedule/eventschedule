{{--
    The interstitial that posts the buyer to Payfast.

    A page rather than a redirect because Payfast's Custom integration takes a POST of signed fields.
    In practice nobody reads it: layouts/app.blade.php submits any form[data-submit-on-load] as soon
    as the page is ready. The visible button is the fallback for a buyer with JavaScript disabled.

    The passphrase is deliberately absent. It is the shared secret that makes an ITN signature
    meaningful, so it belongs in the signature calculation only, never in a field the browser can read.
--}}
<x-app-layout :title="__('messages.redirecting_to_payment')">

    <div class="max-w-lg mx-auto px-4 py-16 text-center">
        <div class="ap-card rounded-xl p-8">
            <svg class="w-10 h-10 mx-auto text-[var(--brand-blue)] animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>

            <h1 class="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('messages.redirecting_to_payment') }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.redirecting_to_payment_help') }}
            </p>

            <form method="post" action="{{ $action }}" data-submit-on-load class="mt-6">
                @foreach ($fields as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <input type="hidden" name="signature" value="{{ $signature }}">

                <x-brand-button type="submit">
                    {{ __('messages.continue_to_payment') }}
                </x-brand-button>
            </form>
        </div>
    </div>

</x-app-layout>
