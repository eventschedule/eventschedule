@php
    // The Vendor map tab: a Vue island over server-made props. Everything the island may show is
    // decided by VendorMapPresenter (a phone number reaches this page only because its viewers
    // are the schedule's editors).
    $vmProps = \App\Services\VendorMaps\VendorMapPresenter::adminProps($role);
    $vmProps['preferKind'] = in_array(request('kind'), \App\Services\VendorMaps\VendorMapKinds::KINDS, true) ? request('kind') : null;
    $vmMap = \App\Models\VendorMap::where('role_id', $role->id)->first();
    $vmStructure = $vmMap ? \App\Services\VendorMaps\VendorMapStructure::toArray($vmMap) : null;
    $vmDay = null;
    if ($vmMap && $vmProps['hasSpots'] && $vmProps['date']) {
        $vmMap->setRelation('role', $role);
        $vmDay = \App\Services\VendorMaps\VendorMapPresenter::dayForEditors($vmMap, $vmProps['date']);
    }
    // A draft from the last day that never reached the designer (a phone that slept, a tab that
    // closed): the photo step comes back to it.
    $vmResume = null;
    if ($vmMap && ! $vmProps['hasSpots']) {
        $latest = $vmMap->drafts()->where('created_at', '>=', now()->subDay())->latest('id')->first();
        if ($latest) {
            $vmResume = [
                'draft' => \App\Utils\UrlUtils::encodeId($latest->id),
                'status' => $latest->status,
                'images' => collect($latest->images)->map(fn ($path, $i) => [
                    'url' => route('vendor_map.private_image', ['subdomain' => $role->subdomain, 'kind' => 'draft', 'id' => \App\Utils\UrlUtils::encodeId($latest->id), 'index' => $i], false),
                    'pdf' => str_ends_with($path, '.pdf'),
                ])->values()->all(),
            ];
        }
    }
    $vmIsland = [
        'initial' => $vmProps,
        't' => \App\Services\VendorMaps\VendorMapStrings::get(\App\Services\VendorMaps\VendorMapStrings::ADMIN),
        'csrfToken' => csrf_token(),
        'structure' => $vmStructure,
        'day' => $vmDay,
        'resume' => $vmResume,
    ];
@endphp

<div class="page-head is-flush">
    <p class="page-lead">{{ \App\Services\VendorMaps\VendorMapKinds::say($vmProps['kind'], 'kind_name') }}: {{ __('messages.vm_tab_lead') }}</p>
    @if ($vmMap && $vmResume && $vmResume['status'] === 'ready')
        <div class="page-actions">
            <a href="{{ route('vendor_map.design', ['subdomain' => $role->subdomain, 'draft' => $vmResume['draft']]) }}" class="event-link">{{ __('messages.vm_map_ready') }}</a>
        </div>
    @endif
</div>

<div id="vendor-map-admin" data-vue-root data-props="{{ json_encode($vmIsland) }}"></div>

@vite('resources/js/vendor-map-admin.js')
