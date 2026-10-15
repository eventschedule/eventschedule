{{-- The three pages of a boost partner's Boost section, as one strip of tabs: Requests (with
     how many wait on an answer), Campaigns and Statements. While the partner's Page and ad
     account are not connected and confirmed, every one of them says so first and leads to the
     Boosts tab of the settings, because nothing approved there can launch until then.

     Expects $partner (BoostPartner) and $tab (requests, campaigns or statements). --}}
@php
    $bpNavWaiting = $partner->pendingRequestCount();
    $bpNavState = $partner->connectionState();
@endphp

<x-page-tabs id="boost-partner-tabs" strip :label="__('messages.boost')" :tabs="[
    ['label' => __('messages.requests'), 'href' => route('boost.partner.requests'), 'current' => $tab === 'requests', 'count' => $bpNavWaiting, 'waiting' => true],
    ['label' => __('messages.campaigns'), 'href' => route('boost.partner.campaigns'), 'current' => $tab === 'campaigns'],
    ['label' => __('messages.boost_partner_tab_statements'), 'href' => route('boost.partner.statements'), 'current' => $tab === 'statements'],
]" />

@if ($bpNavState !== 'connected')
<x-page-notice :tone="$bpNavState === 'needs_attention' ? 'error' : 'warn'" class="mb-4">
    {{ __('messages.boost_partner_connect_notice_'.$bpNavState) }}
    <x-slot name="action">
        <a href="{{ route('profile.edit') }}#section-boost-account" class="event-link">{{ __('messages.boost_partner_open_settings') }}</a>
    </x-slot>
</x-page-notice>
@endif
