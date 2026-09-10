{{ $event->name }}
{{ $heading }}

{{ $body }}

{{-- The date is a TERNARY, not an @if, and that is load-bearing.

     Written as `@if (...)...@endif` immediately followed by another `@if`, Blade will not
     compile the second directive: its pattern requires a non-word-boundary before the `@`,
     and the position between the `f` of `endif` and the next `@` IS a word boundary. The
     `@if` is left as literal text while its `@endif` still compiles, so the view becomes PHP
     with an unmatched `endif` and every render throws a ParseError. On `sync` - the selfhost
     default - that surfaces as a 500 on the owner's cancel/reschedule request, because
     SendQueuedEmail::handle() has no catch and RoleMailerService only catches mailer
     exceptions.

     Separating them with a Blade comment does NOT fix it - comments are stripped before the
     directive pass, so the two directives end up adjacent again. Nor does building the line
     in an @php block, because @endphp followed directly by an echo is the same trap.
     Collapsing to a ternary removes the adjacency instead of hiding it, and matches
     emails/event_announcement_text.blade.php.

     The starts_at guard has to stay: the cancelled and changed kinds reach this view from
     EventChangeNotifier with no isDue() check, and getStartDateTime() throws on a dateless
     event. --}}{{ $event->starts_at ? ($event->is_multi_day ? $event->getDateRangeDisplay() : $event->getStartDateTime($interest->event_date ?: null, true)?->translatedFormat('F j, Y')) : '' }}@if ($event->venue && $event->venue->name) - {{ $event->venue->name }}@endif

{{ $button }}: {{ $eventUrl }}

--
{{ __('messages.event_interest_why_receiving', ['event' => $event->name]) }}
{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
