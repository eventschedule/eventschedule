{{ __('messages.pass_booking_confirmation') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.pass_booking_confirmation_intro') }}

{{ $bookedEvent->name }}
{{ __('messages.date') }}: {{ $dateLabel }}
{{ __('messages.attendee') }}: {{ $sale->name }}

{{ __('messages.manage_my_pass') }}: {{ $manageUrl }}
@php $ticketNotes = $bookedEvent->parsedTicketNotesText($date, $role); @endphp
@if ($ticketNotes && trim($ticketNotes) !== '')

{{ __('messages.important_information') }}:
{{ $ticketNotes }}
@endif
