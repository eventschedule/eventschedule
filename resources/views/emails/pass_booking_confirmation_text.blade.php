{{ __('messages.pass_booking_confirmation') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ __('messages.pass_booking_confirmation_intro') }}

{{ $bookedEvent->name }}
{{ __('messages.date') }}: {{ $dateLabel }}
{{ __('messages.attendee') }}: {{ $sale->name }}
@if (! empty($cancelDeadlineLabel) && ! empty($cancelDeadlinePassed))
{{ $lateCancelPolicy === 'block' ? __('messages.pass_cancel_email_closed', ['minutes' => \App\Services\PassBookingService::CANCEL_GRACE_MINUTES]) : __('messages.pass_cancel_email_no_credit', ['minutes' => \App\Services\PassBookingService::CANCEL_GRACE_MINUTES]) }}
@elseif (! empty($cancelDeadlineLabel))
{{ $lateCancelPolicy === 'block' ? __('messages.pass_cancel_email_deadline_block', ['deadline' => $cancelDeadlineLabel]) : __('messages.pass_cancel_email_deadline_forfeit', ['deadline' => $cancelDeadlineLabel]) }}
@endif

{{ __('messages.manage_my_pass') }}: {{ $manageUrl }}
@php $ticketNotes = $bookedEvent->parsedTicketNotesText($date, $role); @endphp
@if ($ticketNotes && trim($ticketNotes) !== '')

{{ __('messages.important_information') }}:
{{ $ticketNotes }}
@endif
