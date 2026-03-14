@if ($type === 'carpool_ride_requested')
{{ __('messages.carpool_email_ride_requested_heading') }}
@elseif ($type === 'carpool_request_approved')
{{ __('messages.carpool_email_request_approved_heading') }}
@elseif ($type === 'carpool_request_declined')
{{ __('messages.carpool_email_request_declined_heading') }}
@elseif ($type === 'carpool_offer_cancelled')
{{ __('messages.carpool_email_offer_cancelled_heading') }}
@elseif ($type === 'carpool_request_cancelled')
{{ __('messages.carpool_email_request_cancelled_heading') }}
@elseif ($type === 'carpool_reminder')
{{ __('messages.carpool_email_reminder_heading') }}
@endif

{{ __('messages.hello') }} {{ $recipient?->name }},

{{ $event->name }}
@if ($offer->event_date)
{{ $event->getStartDateTime($offer->event_date?->format('Y-m-d'), true)?->translatedFormat('F j, Y') }}
@endif

{{ __('messages.carpool_direction') }}: {{ $offer->directionLabel() }}
{{ __('messages.carpool_city') }}: {{ $offer->city }}
@if ($offer->departure_time)
{{ __('messages.carpool_departure_time') }}: {{ \Carbon\Carbon::parse($offer->departure_time)->format('H:i') }}
@endif
@if ($offer->meeting_point)
{{ __('messages.carpool_meeting_point') }}: {{ $offer->meeting_point }}
@endif

@if ($type === 'carpool_ride_requested' && $carpoolRequest)
{{ __('messages.carpool_email_ride_requested_body', ['name' => $carpoolRequest->user->name]) }}
@if ($carpoolRequest->message)
"{{ $carpoolRequest->message }}"
@endif
@endif
@if ($type === 'carpool_request_approved')
{{ __('messages.carpool_email_request_approved_body') }}

{{ __('messages.carpool_driver') }}: {{ $offer->user->name }}
{{ __('messages.email') }}: {{ $offer->user->email }}
@if ($offer->user->phone)
{{ __('messages.phone') }}: {{ $offer->user->phone }}
@endif
@endif
@if ($type === 'carpool_request_declined')
{{ __('messages.carpool_email_request_declined_body') }}
@endif
@if ($type === 'carpool_offer_cancelled')
{{ __('messages.carpool_email_offer_cancelled_body', ['name' => $offer->user->name]) }}
@endif
@if ($type === 'carpool_request_cancelled')
{{ __('messages.carpool_email_request_cancelled_body', ['name' => $carpoolRequest->user->name]) }}
@endif
@if ($type === 'carpool_reminder')
{{ __('messages.carpool_email_reminder_body') }}
@if ($carpoolRequest && $carpoolRequest->status === 'approved' && $recipient?->id !== $offer->user_id)
{{ __('messages.carpool_driver') }}: {{ $offer->user->name }}
{{ __('messages.email') }}: {{ $offer->user->email }}
@if ($offer->user->phone)
{{ __('messages.phone') }}: {{ $offer->user->phone }}
@endif
@endif
@endif

{{ __('messages.thank_you_for_using') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
