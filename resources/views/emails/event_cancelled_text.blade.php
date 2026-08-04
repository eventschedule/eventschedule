{{ __('messages.event_cancelled_heading') }}

{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,

{{ __('messages.event_cancelled_body', ['event' => $event->name]) }}
@if (! empty($partOfOrder))
{{ __('messages.event_cancelled_rest_of_order_stands') }}
@endif
@if (! empty($note))

{{ __('messages.organizer_note') }}:
{{ $note }}
@endif

{{ __('messages.event_cancelled_refund_guidance') }}

{{ __('messages.thank_you_for_using') }}
