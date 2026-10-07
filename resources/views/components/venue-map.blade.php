@props(['role', 'band', 'group' => null])

{{-- The venue map on a schedule's guest page: a band that opens a map of the schedule's venues
     (resources/js/components/VenueMap.vue, mounted by venue-map-boot.js).

     $band is App\Services\VenueMap::band(): what the closed band shows. The page draws this
     component only when that is not null, which is when the owner has switched the map on, every
     venue has been asked about once, and at least two of them have a position.

     The host is EMPTY and everything the component is told rides in the JSON beside it, a venue's
     town and the schedule's name included: text printed inside a Vue mount is compiled as a
     template (see CLAUDE.md, "Guard user data inside Vue mounts"). The venues themselves are
     fetched when the visitor reaches for the band, from role.venue_map.

     The host keeps the band's height from the start, so the list below does not move when the
     component arrives. --}}
@php
    $tiles = map_tiles();
    $lookup = map_lookup();

    $mapProps = [
        'url' => route('role.venue_map', ['subdomain' => $role->subdomain]),
        'schedule' => $group?->slug ?: '',
        'name' => $role->translatedName(),
        'rtl' => is_rtl(),
        // The scale bar and "0.8 km": miles where the road signs are in miles.
        'miles' => in_array(strtoupper((string) $role->country_code), ['US', 'GB', 'LR', 'MM'], true),
        'startsOpen' => \App\Services\VenueMap::startsOpen($role),
        'band' => $band,
        // null: this install has no street images, and the map is pins on a plain ground.
        'tiles' => $tiles ? ['url' => $tiles['url']] : null,
        // The credit names whose data the positions (and the streets) are, and links to them.
        'credit' => (string) config('services.map.attribution'),
        'creditUrl' => preg_match('~^https?://~i', (string) config('services.map.attribution_url')) ? config('services.map.attribution_url') : null,
        'assets' => [
            'leaflet' => asset('vendor/leaflet/leaflet.js').'?v=1.9.4',
            'leafletCss' => asset('vendor/leaflet/leaflet.css').'?v=1.9.4',
            'cluster' => asset('vendor/leaflet-markercluster/leaflet.markercluster.js').'?v=1.5.3',
            'clusterCss' => asset('vendor/leaflet-markercluster/MarkerCluster.css').'?v=1.5.3',
        ],
        't' => [
            'map' => __('messages.map'),
            'venues' => __('messages.venues'),
            'close' => __('messages.close'),
            'directions' => __('messages.directions'),
            'try_again' => __('messages.try_again'),
            'clear_filter' => __('messages.clear_filter'),
            'show_map' => __('messages.venue_map_show'),
            'hide_map' => __('messages.venue_map_hide'),
            'larger' => __('messages.venue_map_larger'),
            'streets_note' => __('messages.venue_map_streets_note', ['provider' => $tiles['name'] ?? '']),
            'open_plain' => __('messages.venue_map_open_plain'),
            'show_streets' => __('messages.venue_map_show_streets'),
            'loading' => __('messages.venue_map_loading'),
            'failed' => __('messages.venue_map_failed'),
            'none' => __('messages.venue_map_none'),
            'when_all' => __('messages.venue_map_any_day'),
            'when_today' => __('messages.today'),
            'when_week' => __('messages.venue_map_next_7_days'),
            'dots_all' => __('messages.venue_map_dots_all'),
            'dots_today' => __('messages.venue_map_dots_today'),
            'dots_week' => __('messages.venue_map_dots_week'),
            'whole_map' => __('messages.venue_map_whole'),
            'nothing' => __('messages.venue_map_nothing'),
            'no_pin' => __('messages.venue_map_no_pin'),
            'none_when' => __('messages.venue_map_none_when'),
            'approx' => __('messages.venue_map_approx'),
            'venue_page' => __('messages.venue_map_venue_page'),
            'no_street' => __('messages.venue_map_no_street'),
            'not_found' => __('messages.venue_map_not_found'),
            'coming_up' => __('messages.venue_map_coming_up'),
            'nearby' => __('messages.venue_map_nearby'),
            'see_all' => __('messages.venue_map_see_all'),
            'and_more' => __('messages.venue_map_and_more'),
            'km' => __('messages.venue_map_km'),
            'mi' => __('messages.venue_map_mi'),
            'zoom_in' => __('messages.venue_map_zoom_in'),
            'zoom_out' => __('messages.venue_map_zoom_out'),
            // The pins' positions are the lookup service's data, credited with or without streets.
            'credit_pins' => __('messages.venue_map_credit_pins'),
        ],
    ];

    // Bare flags, spelled out: the default escaping keeps "<" out of the script block, and
    // slashes stay slashes so the addresses read as addresses.
    $mapJson = json_encode($mapProps, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

@once
    @include('partials.guest-map-styles')
@endonce

<div id="es-venue-map-host" class="gk-map-host" data-venue-map></div>
{{-- Without scripts no band will ever be drawn here, so no room is kept for one. --}}
<noscript><style {!! nonce_attr() !!}>.gk-map-host { min-height: 0; }</style></noscript>
{{-- The room the host keeps before the component arrives is the room the component will take,
     decided here the way the component decides it (VenueMap.vue, mounted()):
       - the band is taller while it carries the sentence about street images, which is while the
         visitor has not allowed the marketing category (window.esConsent is in the head for
         exactly this: an inline reader before the modules load);
       - a map that starts open is 30rem taller still, for a visitor on a larger screen who has
         allowed that category (or on an install with no street images) and has not hidden it.
         Kept only for the band, the list below jumped that far on every load. --}}
<script {!! nonce_attr() !!}>
    (function () {
        var host = document.getElementById('es-venue-map-host');
        var consent = window.esConsent;
        var allowed = !! (consent && consent.has('marketing'));
        var tiles = @json((bool) $tiles);

        if (tiles && ! allowed) {
            host.classList.add('is-ask');
        }

        if (@json(\App\Services\VenueMap::startsOpen($role)) && (allowed || ! tiles) && ! location.hash && ! window.matchMedia('(max-width: 47.9375rem)').matches) {
            var hidden = false;
            try { hidden = localStorage.getItem('es_map_hidden_' + @json($mapProps['url'])) === '1'; } catch (e) {}
            if (! hidden) {
                host.classList.add('is-open');
            }
        }
    })();
</script>
<script type="application/json" id="es-venue-map-json" {!! nonce_attr() !!}>{!! $mapJson !!}</script>
