{{-- Plain text: printed raw, because escaping here would turn "You're" into You&#039;re. --}}
@php
    $occurrence = $sale->event_date;
    $isRsvp = $sale->isRsvp();
    $start = $event->starts_at ? $event->getStartDateTime($occurrence, true) : null;
@endphp
{!! $eyebrow !!}: {!! $event->name !!}

{!! __('messages.hello') !!} {!! $sale->name !!},

{!! $headline !!}. {!! __('messages.ticket_reminder_see_you') !!}

@if ($start)
{!! __('messages.date') !!}: {!! $event->is_multi_day ? $event->getDateRangeDisplay($occurrence) : $start->localDate() !!}
{!! __('messages.time') !!}: {!! $event->getStartEndTime($occurrence, (bool) ($role?->use_24_hour_time)) !!}
@endif
@if ($event->venue && trim((string) $event->venue->name) !== '')
{!! trim((string) $event->venue->name) !!}
@endif
@if ($event->venue && trim((string) $event->venue->bestAddress()) !== '')
{!! trim((string) $event->venue->bestAddress()) !!}
@endif
@if ($event->event_url)
{!! __('messages.online') !!}: {!! $event->event_url !!}
@endif

{!! $isRsvp ? __('messages.view_registration') : __('messages.view_your_tickets') !!}: {!! $ticketUrl !!}
@if ($appleWalletUrl ?? null)
{!! __('messages.add_to_apple_wallet') !!}: {!! $appleWalletUrl !!}
@endif
@if ($googleWalletUrl)
{!! __('messages.add_to_google_wallet') !!}: {!! $googleWalletUrl !!}
@endif
@php($ticketNotes = $event->parsedTicketNotesText($sale->event_date, $role))
@if ($ticketNotes && trim($ticketNotes) !== '')

{!! __('messages.important_information') !!}:
{!! $ticketNotes !!}
@endif
@if ($canCancel)

{!! __('messages.ticket_reminder_cant_make_it') !!} {!! $ticketUrl !!}
@endif

{!! $isRsvp ? __('messages.ticket_reminder_why_rsvp', ['event' => $event->name]) : __('messages.ticket_reminder_why', ['event' => $event->name]) !!}
@if ($event->user?->email)
{!! __('messages.event_support_contact') !!}: {!! $event->user->email !!}
@endif
