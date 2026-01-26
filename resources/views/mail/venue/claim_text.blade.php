{{ $subject }}

{{ __('messages.hello') }},

{{ $event->name }}
{{ $event->localStartsAt(true) }}
@if($role)
{{ $role->name }}
@endif

{{ __('messages.view_event') }}: {{ $event->getGuestUrl() }}

{{ __('messages.claim_email_line1') }}

{{ __('messages.sign_up') }}: {{ route('sign_up', ['email' => base64_encode($venue->email)]) }}

{{ strip_tags(__('messages.claim_email_line2', ['click_here' => __('messages.click_here')])) }}: {{ route('role.show_unsubscribe', ['email' => base64_encode($venue->email)]) }}

{{ __('messages.thanks') }},
{{ config('app.name') }}
