{{--
    The accommodation map: hotels and rentals near the event's venue, via Stay22.

    Renders nothing at all unless the operator enabled the integration, the schedule owner
    opted in, the venue has usable coordinates, the occurrence is not in the past, and an
    affiliate ID resolves. Stay22Service::embedFor() owns every one of those gates - a Blade
    template is the wrong place for that decision, and it needs request state the view does
    not otherwise carry.

    CLAUDE.md forbids CDNs, and this is unavoidably a remote third party. The reconciliation
    is the same one written into components/adsense-unit.blade.php: it is off by default at
    two independent levels (the STAY22_ENABLED env gate and the per-schedule toggle), and it
    loads nothing whatsoever until the visitor has either accepted cookies or explicitly
    clicked to show the map. Do not try to vendor an affiliate iframe locally.

    The host div below is EMPTY on purpose, and every string reaches Vue as a JSON prop
    rather than as server-rendered markup. The app runs Vue's runtime template compiler with
    CSP 'unsafe-eval' on, so a server-rendered text node inside a Vue mount is compiled as a
    template - and the venue name is user-controlled. Props are rendered by Vue itself and
    are safe, which is why this component needs no v-pre and no <x-user-text>. See the same
    warning at the top of partials/promo-slot.blade.php.
--}}
@props(['role', 'event' => null, 'date' => null, 'accentColor' => null, 'passwordGate' => false])

@php
    $stay22Url = \App\Services\Stay22Service::embedFor(
        $role,
        $event,
        $date,
        request(),
        $accentColor,
        $passwordGate ?? false
    );
@endphp

@if ($stay22Url)
@php
    $stay22VenueName = $event->venue?->translatedName();

    $stay22Payload = [
        'url' => $stay22Url,
        'heading' => __('messages.stay22_heading'),
        'frameTitle' => $stay22VenueName
            ? __('messages.stay22_map_title', ['venue' => $stay22VenueName])
            : __('messages.stay22_map_title_generic'),
        'consentBody' => __('messages.stay22_consent_body'),
        'gpcBody' => __('messages.stay22_gpc_body'),
        'consentButton' => __('messages.stay22_consent_button'),
        'linkLabel' => __('messages.stay22_open_external'),
        'disclosure' => __('messages.stay22_affiliate_disclosure'),
        'rtl' => $role->isRtl(),
    ];
@endphp

{{-- JSON_HEX_TAG is load-bearing: without it a "</script>" inside a translated string
     closes this tag early. Same flag set as partials/accessibility-widget.blade.php. --}}
<script type="application/json" id="es-stay22-json" {!! nonce_attr() !!}>{!! json_encode($stay22Payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
<div id="es-stay22-host"></div>

@once
<style {!! nonce_attr() !!}>
    /* Stay22 asks for 450px minimum on desktop and tablet. The wrapper owns the height so
       the iframe can be width:100% without a layout shift when it swaps in. */
    .es-stay22-frame { height: 450px; }
    .es-stay22-frame iframe { display: block; width: 100%; height: 100%; border: 0; }

    /* An affiliate placement has no business on a printed page. */
    @media print { .es-stay22 { display: none !important; } }
</style>
@endonce
@endif
