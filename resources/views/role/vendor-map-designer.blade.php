<x-app-admin-layout wide>
    @php
        // Fully qualified: this block is compiled inside a component slot's closure, where a
        // `use` import is a syntax error (see role/seating-designer).
        $designerProps = $props + [
            't' => \App\Services\VendorMaps\VendorMapStrings::get(\App\Services\VendorMaps\VendorMapStrings::DESIGNER),
            'csrfToken' => csrf_token(),
        ];

        // What the AI found, said with the kind's own count ("We found 38 stalls"), worked out
        // here because the plural needs the translator.
        $draftRow = $props['draft'] ? \App\Models\VendorMapDraft::where('vendor_map_id', \App\Models\VendorMap::where('role_id', $role->id)->value('id'))
            ->find(\App\Utils\UrlUtils::decodeId($props['draft'])) : null;
        if ($draftRow && $draftRow->status === 'ready' && is_array($draftRow->result)) {
            $found = count($draftRow->result['spots'] ?? []);
            // The names line only when the AI read some: it is asked to divide the space, and a
            // sign it could not read is not news.
            $namesRead = (int) ($draftRow->result['names_read'] ?? 0);
            $designerProps['strings']['spots_found_count'] = \App\Services\VendorMaps\VendorMapKinds::count($props['kind'], 'spots_found', $found)
                .($namesRead > 0 ? ' '.trans_choice('messages.vm_names_read', $namesRead, ['count' => $namesRead]) : '');
        }
    @endphp

    <div id="vendor-map-designer" data-vue-root data-props="{{ json_encode($designerProps) }}"></div>

    @vite('resources/js/vendor-map-designer.js')
</x-app-admin-layout>
