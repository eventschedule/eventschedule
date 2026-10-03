{{--
    Cookie consent banner. Hidden on first render; resources/js/cookie-consent.js
    shows it when localStorage has no 'cookie_consent' entry, and Allow/Decline
    write the choice + flip Consent Mode v2.

    cookie_banner_required() covers Google Analytics, ads, Stay22, COOKIE_CONSENT_BANNER and
    /admin/realtime being on (it identifies visitors only after they accept).
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
{{-- Centred along the bottom edge rather than tucked into a corner: the corners belong to the
     support chat launcher and the accessibility widget. mx-auto between inset-x-4 does the
     centring, so the entrance animation is free to use transform. The bottom offset clears the
     iPhone home indicator where the layout opts into viewport-fit=cover. --}}
@if (cookie_banner_required() && (! auth()->user() || ! auth()->user()->isAdmin()))
<div data-cookie-consent
     hidden
     role="region"
     aria-live="polite"
     aria-label="{{ __('messages.cookie_consent_banner_label') }}"
     class="fixed inset-x-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-50 mx-auto max-w-3xl
            rounded-2xl border border-gray-200/80 dark:border-white/10
            bg-white/95 dark:bg-gray-800/95 backdrop-blur-md
            text-gray-800 dark:text-gray-200 shadow-xl shadow-black/10 dark:shadow-black/40
            p-4 sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
        <div class="flex items-start gap-3 sm:flex-1 sm:items-center">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/10 text-[var(--brand-blue)]" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5" />
                    <path d="M8.5 8.5v.01" />
                    <path d="M16 15.5v.01" />
                    <path d="M12 12v.01" />
                    <path d="M11 17v.01" />
                    <path d="M7 14v.01" />
                </svg>
            </span>
            <p class="text-sm leading-relaxed">
                {{ __('messages.cookie_consent_message') }}
                <x-link :href="policy_url('cookies')">{{ __('messages.cookie_consent_learn_more') }}</x-link>
            </p>
        </div>
        {{-- Two equal halves on a phone, so declining is never the smaller target. --}}
        <div class="grid grid-cols-2 gap-2 sm:flex sm:shrink-0">
            <button type="button" data-cookie-consent-action="denied"
                    class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.cookie_consent_decline') }}
            </button>
            <x-brand-button type="button" data-cookie-consent-action="granted">
                {{ __('messages.cookie_consent_accept') }}
            </x-brand-button>
        </div>
    </div>
</div>
@endif
