{{ __('messages.hello') }} {{ $sale->name }},

{{ $intro }}

{{ $type?->name ?? $event->name }}
@include('emails.partials.appointment_datetime_text')

@if ($rebookUrl)
{{ __('messages.appointments_book_again') }}: {{ $rebookUrl }}
@else
{{ __('messages.appointments_manage_link_hint') }}
{{ $manageUrl }}
@endif
