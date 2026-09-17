{{-- Plain text, so unescaped: {{ }} would print entities such as &#039; at the reader. $host is
     FederatedInstance::displayHost(), so plain-text clients do not auto-link it. --}}
{!! $approved ? __('messages.federation_approved_subject') : __('messages.federation_suspended_subject') !!}

{!! $approved ? __('messages.federation_reapproved_intro') : __('messages.federation_suspended_intro') !!}

{!! $host !!}

{!! $approved ? __('messages.federation_approved_next') : __('messages.federation_suspended_next') !!}
