{{--
    The monetization slot: a paid promotion, an AdSense unit, or nothing at all.

    Resolution (including recording the impression) happens in AdsService, not here - a Blade
    template is the wrong place to write to the database, and the decision needs request state
    the view does not otherwise carry.

    Placement matters: this partial is included from layouts/app-guest.blade.php OUTSIDE
    #main-content, and therefore outside the #calendar-app Vue mount. That is the only reason
    the advertiser-supplied headline below does not need v-pre. If this is ever moved inside
    {{ $slot }}, those headlines become a Vue template-injection sink - the app runs Vue's
    runtime compiler and CSP 'unsafe-eval' is intentionally enabled, so nothing would stop it.
    Use <x-user-text> if that move ever happens.
--}}
{{--
    $passwordGate cannot currently be true here, and that is deliberate belt-and-braces rather
    than dead code: a password-protected event renders event/password-prompt.blade.php, which
    sets :password-gate but never :ad-slot, so this partial is not included at all. The guard
    exists so that adding :ad-slot to a gated view later cannot quietly start showing ads (and
    charging advertisers) on a page whose content the visitor has not been allowed to see.
--}}
@php
    $promoSlot = app(\App\Services\AdsService::class)
        ->resolveSlot($role, $event ?? null, request(), $passwordGate ?? false);
@endphp

@if ($promoSlot)
    {{-- role/aria-label make this a landmark a screen-reader user can skip past, rather than
         an unexplained block of links between the content and the footer. --}}
    <div class="es-promo-slot" role="complementary" aria-label="{{ __('messages.promoted_content') }}">
        @if ($promoSlot['type'] === 'native')
            <x-promo-card :promo="$promoSlot['promo']" />
        @else
            <x-adsense-unit />
        @endif
    </div>

    @once
    <style {!! nonce_attr() !!}>
        /* The event page pins a CTA bar to the bottom of the viewport on small screens
           (#mobile-cta-bar, sm:hidden fixed bottom-0). The branding footer below absorbs most
           of the overlap, but on a short page the slot can still end up underneath it, so
           reserve room. --es-a11y-cta-clearance is set to the bar's measured height by
           show-guest.blade.php (not by the accessibility widget, which only consumes it), so
           the 0px fallback applies on pages with no bar and for the first paint before that
           script runs. */
        .es-promo-slot { margin-bottom: calc(var(--es-a11y-cta-clearance, 0px) + 1rem); }
        @media (min-width: 640px) { .es-promo-slot { margin-bottom: 0; } }
    </style>
    @endonce
@endif
