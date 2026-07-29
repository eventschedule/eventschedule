@php
    $adsenseClient = \App\Services\AdsService::setting('adsense_client_id');
    $adsenseSlot = \App\Services\AdsService::setting('adsense_slot_id');
    $adsenseNpa = \App\Services\AdsService::requestNonPersonalizedAds(request());
@endphp

{{--
    A Google AdSense display unit.

    The label must read "Advertisement" (or "Sponsored Links") - AdSense policy restricts the
    wording next to an ad unit, which is why this does not share the "Promoted" string used by
    the native card.

    The loader lives here rather than in the layout head on purpose: a page that fills with a
    native promotion instead then makes ZERO requests to Google. That is a real privacy and
    performance difference for a feature whose whole premise is opt-in third-party script.

    CLAUDE.md forbids CDNs so that selfhosted installs never call external servers. AdSense is
    unavoidably remote; the reconciliation is that it is off by default and the operator has to
    switch it on deliberately, exactly like the OneSignal SDK. Do not "fix" this to a local file.
--}}
<div class="es-ad-slot w-full max-w-3xl mx-auto px-4 sm:px-0 mt-8">
    <p class="mb-1 text-center text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500">
        {{ __('messages.advertisement') }}
    </p>

    <ins class="adsbygoogle block"
         style="display:block"
         data-ad-client="{{ $adsenseClient }}"
         data-ad-slot="{{ $adsenseSlot }}"
         data-ad-format="auto"
         data-full-width-responsive="true"></ins>
</div>

@once
    <style {!! nonce_attr() !!}>
        /* Reserve height so a late-filling unit does not shove the footer down. */
        .es-ad-slot ins.adsbygoogle { min-height: 100px; }
        @media (min-width: 768px) { .es-ad-slot ins.adsbygoogle { min-height: 90px; } }
        /* AdSense sets data-ad-status="unfilled" when nothing serves. Collapsing with :has()
           needs no JavaScript; on a browser without :has() the cost is an empty band, not a
           broken page. */
        .es-ad-slot:has(ins.adsbygoogle[data-ad-status="unfilled"]) { display: none; }
    </style>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClient }}"
            crossorigin="anonymous" {!! nonce_attr() !!}></script>
@endonce

<script {!! nonce_attr() !!}>
    window.adsbygoogle = window.adsbygoogle || [];
    @if ($adsenseNpa)
        /* Set before push(), per Google's documented order. */
        window.adsbygoogle.requestNonPersonalizedAds = 1;
    @endif
    window.adsbygoogle.push({});
</script>
