{{-- The manage page's add-to-calendar choices (x-appointment-add-to-calendar), as a link row. Only
     for a CONFIRMED booking: a pending one has nothing booked yet, so an entry would mislead. --}}
@php
    $apptCalendarLinks = [
        'Google Calendar' => \App\Utils\IcsUtils::googleUrl($event, $sale),
        'Outlook' => \App\Utils\IcsUtils::outlookUrl($event, $sale),
        __('messages.appointments_calendar_file') => route('appointments.ical', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]),
    ];
@endphp
<x-email.text variant="small" :gap="24">{{ $role ? $role->customLabel('add_to_calendar') : __('messages.add_to_calendar') }}:&nbsp;
@foreach ($apptCalendarLinks as $calLabel => $calUrl)
<x-email.link :href="$calUrl">{{ $calLabel }}</x-email.link>@if (! $loop->last)&nbsp;&middot;&nbsp;@endif
@endforeach
</x-email.text>
