{{-- The "Not coming" link from an approval mail that no longer describes the stall: the vendor
     moved, stopped, or was taken off. It acts on nothing and says where to go instead. --}}
<x-app-layout :title="$role->translatedName()">
    <x-slot name="meta">
        @include('vendor-map.claim-meta', ['ogTitle' => null, 'ogImage' => null])
    </x-slot>

    <x-slot name="head">
        @include('vendor-map.claim-styles')
    </x-slot>

    <main id="main-content" class="vmc">
        <div class="vmc-wrap">
            <p class="vmc-kicker"><bdi>{{ $role->translatedName() }}</bdi></p>
            <h1 class="vmc-title">{{ __('messages.vm_away_stale_title') }}</h1>
            <p class="vmc-lead">{{ __('messages.vm_away_stale_body', ['name' => \App\Services\VendorMaps\VendorMapPresenter::ownerFirstName($role)]) }}</p>
            <a class="vmc-btn" href="{{ $role->getGuestUrl() }}">{{ __('messages.vm_claim_open_market_page') }}</a>
        </div>
    </main>
</x-app-layout>
