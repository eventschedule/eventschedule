{{ $subject }}

{{ __('messages.hello') }},

{{ str_replace(':venue', $role->name, __('messages.request_declined')) }}

{{ $event->name }}
{{ $event->localStartsAt(true) }}
@if($event->getVenueDisplayName())
{{ $event->getVenueDisplayName() }}
@endif

{{ strip_tags(__('messages.claim_email_line2', ['click_here' => __('messages.click_here')])) }}: {{ route('role.show_unsubscribe', ['email' => base64_encode($creatorRole->email)]) }}

{{ __('messages.thanks') }},
{{ config('app.name') }}
