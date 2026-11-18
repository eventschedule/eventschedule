@php
    $theme = \App\Utils\EmailTheme::account();
    $subject = __('messages.federation_address_confirmed_subject');
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="__('messages.federation_address_confirmed_intro')">
<x-email.heading>{{ $subject }}</x-email.heading>

<x-email.text>{{ __('messages.federation_address_confirmed_intro') }}</x-email.text>

{{-- Only the host, and never as a link. See FederatedInstance::displayHost(). --}}
<x-email.callout tone="success"><strong dir="ltr">{{ $host }}</strong></x-email.callout>

<x-email.text variant="small">{{ __('messages.federation_address_confirmed_next') }}</x-email.text>
</x-email.layout>
