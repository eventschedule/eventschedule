{{--
    The vendor link (VendorSpotController::show()). The whole page is a Vue app
    (resources/js/vendor-map-claim.js, resources/js/vendor-map/Claim*.vue) mounted on an EMPTY
    element and given everything as JSON: the names on the map are typed by other people, and a
    server-rendered name inside a Vue mount would be compiled as a template.
--}}
<x-app-layout :title="$ogTitle">
    <x-slot name="meta">
        @include('vendor-map.claim-meta', ['ogTitle' => $ogTitle, 'ogImage' => $ogImage])
    </x-slot>

    <x-slot name="head">
        @include('vendor-map.schematic-styles')
        @include('vendor-map.claim-styles')
        @if (! empty($props['turnstileSiteKey']))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
        @endif
        @vite('resources/js/vendor-map-claim.js')
    </x-slot>

    <main id="main-content" class="vmc">
        <div id="vendor-claim-app" data-vue-root></div>
        <noscript>
            <div class="vmc-wrap"><p class="vmc-note vmc-note-warn">{{ __('messages.vm_claim_needs_js') }}</p></div>
        </noscript>
    </main>

    <script type="application/json" id="vendor-claim-props">@json($props)</script>
</x-app-layout>
