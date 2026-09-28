{{ $subject }}

{{ __('messages.hello') }}@if (trim((string) $recipient->name) !== '') {{ $recipient->firstName() }}@endif,

{{ str_replace(':venue', $role->name, __('messages.request_accepted_body')) }}

{{ $event->name }}
{{ $eventDate }}
@if($event->getVenueDisplayName())
{{ $event->getVenueDisplayName() }}
@endif

{{ __('messages.view_event') }}: {{ $event->getGuestUrl(false, null, true) }}

{{ __('messages.request_decision_why', ['schedule' => $role->name]) }}
{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}{{-- Raw: plain text has no entity decoding, so an escaped &amp; breaks the signed link. --}}

{{ __('messages.thanks') }},
{{ config('app.name') }}
