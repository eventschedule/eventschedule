{{--
    Cookie consent banner. Hidden on first render; resources/js/cookie-consent.js
    shows it when localStorage has no 'cookie_consent' entry, and Allow/Decline
    write the choice + flip Consent Mode v2.

    consent_required() covers Google Analytics, ads, Stay22 and COOKIE_CONSENT_BANNER.
    Where it is false nothing on the page needs consent: the UTM attribution cookies are
    then never written either, so there is nothing to ask about.
--}}
{{-- The domain the consent cookie must be written on, so the choice spans the install the same
     way the attribution cookies it gates do. Empty on a custom domain (where ResolveCustomDomain
     nulls session.domain) and on a bare selfhost, which is what keeps the cookie host-only there.

     Emitted UNCONDITIONALLY, outside the banner. cookie-consent.js re-asserts the stored choice
     on every page load, and the banner is not rendered for an admin - so hanging the domain off
     the banner meant those page loads wrote a second, host-only cookie_consent beside the
     domain-scoped one. Two same-named cookies are both sent, PHP keeps whichever comes last, and
     a later withdrawal through the banner clears only one of them: consent state becomes
     order-dependent, which is the whole thing this was meant to make deterministic. --}}
<meta name="cookie-domain" content="{{ config('session.domain') }}">
@if (consent_required() && (! auth()->user() || ! auth()->user()->isAdmin()))
<div data-cookie-consent
     hidden
     role="region"
     aria-live="polite"
     aria-label="{{ __('messages.cookie_consent_banner_label') }}"
     class="fixed inset-x-4 bottom-4 sm:left-auto sm:right-4 sm:max-w-md z-50
            rounded-xl border border-gray-200 dark:border-gray-700
            bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 shadow-lg p-4">
    <p class="text-sm leading-relaxed mb-3">
        {{ __('messages.cookie_consent_message') }}
        <x-link :href="policy_url('cookies')">{{ __('messages.cookie_consent_learn_more') }}</x-link>
    </p>
    <div class="flex justify-end items-center gap-2">
        <button type="button" data-cookie-consent-action="denied"
                class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            {{ __('messages.cookie_consent_decline') }}
        </button>
        <x-brand-button type="button" data-cookie-consent-action="granted">
            {{ __('messages.cookie_consent_accept') }}
        </x-brand-button>
    </div>
</div>
@endif
