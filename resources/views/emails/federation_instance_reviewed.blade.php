@php
    $theme = \App\Utils\EmailTheme::account();
    $subject = $approved ? __('messages.federation_approved_subject') : __('messages.federation_suspended_subject');
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$approved ? __('messages.federation_reapproved_intro') : __('messages.federation_suspended_intro')">
<x-email.heading>{{ $subject }}</x-email.heading>

<x-email.text>{{ $approved ? __('messages.federation_reapproved_intro') : __('messages.federation_suspended_intro') }}</x-email.text>

{{-- Only the host, and never as a link: the install's name and site_url are whatever its
     registration sent. See FederatedInstance::displayHost(). --}}
<x-email.callout :tone="$approved ? 'success' : 'warning'"><strong dir="ltr">{{ $host }}</strong></x-email.callout>

<x-email.text variant="small">{{ $approved ? __('messages.federation_approved_next') : __('messages.federation_suspended_next') }}</x-email.text>
</x-email.layout>
