{{-- Plain text, so unescaped: {{ }} would print entities such as &#039; at the reader. $host is
     FederatedInstance::displayHost(), so plain-text clients do not auto-link it. --}}
{!! __('messages.federation_address_confirmed_subject') !!}

{!! __('messages.federation_address_confirmed_intro') !!}

{!! $host !!}

{!! __('messages.federation_address_confirmed_next') !!}
