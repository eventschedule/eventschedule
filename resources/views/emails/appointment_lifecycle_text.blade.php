{{ __('messages.hello') }} {{ $sale->name }},

{{ $intro }}

{{ $type?->name ?? $event->name }}
@if ($apptHost = $event->appointmentHostName())
{{ __('messages.appointments_with_host', ['name' => $apptHost]) }}
@endif
@include('emails.partials.appointment_datetime_text')

@if ($rebookUrl)
{{ __('messages.appointments_book_again') }}: {{ $rebookUrl }}
@else
{{ __('messages.appointments_manage_link_hint') }}
{{ $manageUrl }}
@endif
