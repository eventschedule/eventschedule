{{--
    Cookie consent banner. Hidden on first render; resources/js/cookie-consent.js
    shows it when localStorage has no 'cookie_consent' entry, and Allow/Decline
    write the choice + flip Consent Mode v2.
--}}
@if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
<div data-cookie-consent
     hidden
     role="region"
     aria-live="polite"
     aria-label="{{ __('messages.cookie_consent_banner_label') }}"
     class="fixed inset-x-4 bottom-4 sm:left-auto sm:right-4 sm:max-w-md z-50
            rounded-xl border border-gray-200 dark:border-[#2d2d30]
            bg-white dark:bg-[#1e1e1e] text-gray-800 dark:text-gray-200 shadow-lg p-4">
    <p class="text-sm leading-relaxed mb-3">
        {{ __('messages.cookie_consent_message') }}
        <x-link :href="marketing_url('/privacy')">{{ __('messages.cookie_consent_learn_more') }}</x-link>
    </p>
    <div class="flex justify-end items-center gap-2">
        <x-secondary-button type="button" data-cookie-consent-action="denied">
            {{ __('messages.cookie_consent_decline') }}
        </x-secondary-button>
        <x-brand-button type="button" data-cookie-consent-action="granted">
            {{ __('messages.cookie_consent_accept') }}
        </x-brand-button>
    </div>
</div>
@endif
