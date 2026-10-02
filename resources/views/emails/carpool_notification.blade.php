@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);

    $heading = match ($type) {
        'carpool_ride_requested' => __('messages.carpool_email_ride_requested_heading'),
        'carpool_request_approved' => __('messages.carpool_email_request_approved_heading'),
        'carpool_request_declined' => __('messages.carpool_email_request_declined_heading'),
        'carpool_offer_cancelled' => __('messages.carpool_email_offer_cancelled_heading'),
        'carpool_request_cancelled' => __('messages.carpool_email_request_cancelled_heading'),
        'carpool_reminder' => __('messages.carpool_email_reminder_heading'),
        default => '',
    };
    $tone = match ($type) {
        'carpool_request_approved' => 'success',
        'carpool_request_declined' => 'warning',
        'carpool_offer_cancelled', 'carpool_request_cancelled' => 'danger',
        default => null,
    };

    $offerDate = $offer->event_date?->format('Y-m-d');
    $showDriver = $type === 'carpool_request_approved'
        || ($type === 'carpool_reminder' && $carpoolRequest && $carpoolRequest->status === 'approved' && $recipient?->id !== $offer->user_id);
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$heading.' · '.$event->name">
<x-email.heading :eyebrow="$heading" :tone="$tone" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $recipient?->name }},</x-email.text>

@if ($type === 'carpool_ride_requested' && $carpoolRequest)
<x-email.text>{{ __('messages.carpool_email_ride_requested_body', ['name' => $carpoolRequest->user->name]) }}</x-email.text>
@if ($carpoolRequest->message)
<x-email.quote :text="$carpoolRequest->message" />
@endif
@elseif ($type === 'carpool_request_approved')
<x-email.text>{{ __('messages.carpool_email_request_approved_body') }}</x-email.text>
@elseif ($type === 'carpool_request_declined')
<x-email.text>{{ __('messages.carpool_email_request_declined_body') }}</x-email.text>
@elseif ($type === 'carpool_offer_cancelled')
<x-email.text>{{ __('messages.carpool_email_offer_cancelled_body', ['name' => $offer->user->name]) }}</x-email.text>
@elseif ($type === 'carpool_request_cancelled')
<x-email.text>{{ __('messages.carpool_email_request_cancelled_body', ['name' => $carpoolRequest->user->name]) }}</x-email.text>
@elseif ($type === 'carpool_reminder')
<x-email.text>{{ __('messages.carpool_email_reminder_body') }}</x-email.text>
@endif

@if ($showDriver)
<x-email.details panel>
<x-email.item :label="__('messages.carpool_driver')">{{ $offer->user->name }}</x-email.item>
@if ($offer->user->phone)
<x-email.item :label="__('messages.phone')" ltr>{{ $offer->user->phone }}</x-email.item>
@endif
<x-email.item :label="__('messages.email')" ltr wide>{{ $offer->user->email }}</x-email.item>
</x-email.details>
@endif

<x-email.event :event="$event" :date="$offerDate" :role="$role ?? null" :calendar="false" />

<x-email.section :label="__('messages.carpool')" />
<x-email.details>
<x-email.item :label="__('messages.carpool_direction')">{{ $offer->directionLabel() }}</x-email.item>
<x-email.item :label="__('messages.carpool_city')">{{ $offer->city }}</x-email.item>
@if ($offer->departure_time)
<x-email.item :label="__('messages.carpool_departure_time')" ltr>{{ \Carbon\Carbon::parse($offer->departure_time)->format('H:i') }}</x-email.item>
@endif
@if ($offer->meeting_point)
<x-email.item :label="__('messages.carpool_meeting_point')" wide>{{ $offer->meeting_point }}</x-email.item>
@endif
</x-email.details>

@if ($unsubscribeUrl)
<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
@endif
</x-email.layout>
