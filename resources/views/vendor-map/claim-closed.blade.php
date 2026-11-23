{{-- The vendor link when it is not open: an old or rotated link, the organizer closed it, the plan
     lapsed, or the event is over. A page, never a 404: the person holding it did nothing wrong. --}}
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
            <h1 class="vmc-title">{{ __('messages.vm_claim_closed_title') }}</h1>
            <p class="vmc-lead">{{ __('messages.vm_claim_closed_body', ['name' => \App\Services\VendorMaps\VendorMapPresenter::ownerFirstName($role)]) }}</p>
            <a class="vmc-btn" href="{{ $role->getGuestUrl() }}">{{ __('messages.vm_claim_open_market_page') }}</a>
        </div>
    </main>
</x-app-layout>
