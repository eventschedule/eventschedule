<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    @php
        $isTicketMode = request()->get('tickets') === 'true';
        $isRsvpMode = request()->get('rsvp') === 'true';
        $eventDate = $date ?? $event->saleEventDateFromStartsAt();
    @endphp

    {{-- The widget on the stage (partials/guest-stage-styles): one small sheet over the
         schedule's background, the date line in the accent, the event's name large, what the
         tickets cost as rows between hairlines and one large button. Smaller than a page's stage
         and rounded at every width, because it is a card inside somebody else's page. --}}
    <style {!! nonce_attr() !!}>
        .gk-p-tembed { max-width: 36rem; margin: 0 auto; padding: 1.5rem 1.5rem 1.25rem; border-radius: 1.25rem; box-shadow: 0 1.25rem 2.5rem -1.25rem rgb(0 0 0 / .4); }
        @media (min-width: 40rem) { .gk-p-tembed { margin: 0 auto; padding: 1.75rem 2rem 1.5rem; border-radius: 1.5rem; } }
        .gk-p-tembed .gk-title { margin-top: .375rem; font-size: clamp(1.625rem, 1.3rem + 1.4vw, 2.25rem); }
        .gk-p-tembed .gk-title a { color: inherit; text-decoration: none; }
        .gk-p-tembed .gk-title a:hover { text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 4px; }
        .gk-tembed-body { margin-top: 1.25rem; }
        .gk-tembed-go { display: flex; width: 100%; max-width: none; margin-top: 1.5rem; }
        .gk-tembed-help { margin: .75rem 0 0; text-align: center; font-size: .84375rem; color: var(--gk-ink-3); }
        .gk-tembed-none { padding: 1.5rem 0 .5rem; text-align: center; color: var(--gk-ink-2); }
        .gk-tembed-none a { display: inline-block; margin-top: 1rem; font-weight: 600; color: var(--es-accent-readable); text-decoration: underline; text-underline-offset: 3px; }
        .gk-tembed-foot { margin-top: 1.25rem; padding-top: .875rem; border-top: 1px solid var(--gk-line); text-align: end; }
        .gk-tembed-foot a { font-size: .75rem; color: var(--gk-ink-3); text-decoration: none; }
        .gk-tembed-foot a:hover { color: var(--gk-ink); }
    </style>

    @php
        // The widget used to hold the form itself, posted from the frame. A frame on the
        // organizer's own site is cross-site and is never sent the SameSite=lax session cookie, so
        // that post came back 419 in every browser, and a payment page refuses to be framed
        // anyway. So the widget lists what is on offer and its one button opens the event's page in
        // a new tab with the form open, where the buyer finishes (App\Support\EmbedEventView).
        $widgetDate = \App\Models\Event::isOccurrenceDate($date ?? null) ? $date : null;
        $widgetButtonClass = 'gk-btn gk-btn-primary gk-cta-main gk-tembed-go';
    @endphp
    <div class="pt-4 pb-4 px-4" data-embed-root>
        <div class="ticket-embed-card gk-stage gk-p-tembed">

            {{-- Header: the date line, then the name. Both in the tokens the layout prints, as the
                 button below takes them, so the card is in one schedule's colours from top to
                 bottom: the header used to paint this schedule's raw accent while the layout
                 painted the event's other schedule's background. --}}
            <div class="gk-tembed-head">
                <p class="gk-dateline">
                    <span>{{ $event->is_multi_day ? $event->getDateRangeDisplay($eventDate) : $event->getStartDateTime($eventDate, true)->format('l, F j, Y') }}</span>
                    @if ($event->getStartEndTime($eventDate))
                        <span class="gk-dateline-dot" aria-hidden="true">&middot;</span>
                        <span>{{ $event->getStartEndTime($eventDate) }}</span>
                    @endif
                </p>
                <h1 class="gk-title">
                    <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top">{{ $event->name }}</a>
                </h1>
            </div>

            {{-- Body --}}
            <div class="gk-tembed-body">

                @if (session('error'))
                <div class="gk-note gk-note-bad mb-4">
                    {{ session('error') }}
                </div>
                @endif

                @include('event.partials.payment-cancelled')

                @if ($isTicketMode)
                    @if ($event->hasProTicketingPlan() && $event->canSellTickets($eventDate))
                        @include('event.partials.embed-ticket-list', ['embedTicketDate' => $eventDate])
                        @php
                            $widgetLabel = $event->areTicketsFree() ? $role->customLabel('get_tickets') : $role->customLabel('buy_tickets');
                        @endphp
                        <a href="{{ \App\Support\EmbedEventView::pageUrl($event, $subdomain, $widgetDate, 'tickets') }}" target="_blank" rel="noopener"
                           data-embed-checkout="tickets" aria-label="{{ $widgetLabel }} ({{ __('messages.opens_in_new_tab') }})"
                           class="{{ $widgetButtonClass }}">
                            {{ $widgetLabel }}
                            <svg class="h-5 w-5 {{ $role->isRtl() ? 'scale-x-[-1]' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        </a>
                        <p class="gk-tembed-help">{{ __('messages.embed_events_help') }}</p>
                    @else
                        <div class="gk-tembed-none">
                            <p>{{ __('messages.tickets_not_available_embed') }}</p>
                            <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top">
                                {{ __('messages.view_event') }}
                            </a>
                        </div>
                    @endif
                @elseif ($isRsvpMode)
                    {{-- canAcceptRsvp(), the rule TicketController::rsvp() applies, and not
                         rsvp_enabled alone: the form used to be drawn for a cancelled or finished
                         event and then refuse what was sent. A FULL event still passes, and the
                         form shows its waitlist. --}}
                    @if ($event->canAcceptRsvp($eventDate))
                        @php
                            $widgetLabel = $event->isRsvpFull($eventDate) ? __('messages.join_waitlist') : $role->customLabel('register');
                        @endphp
                        <a href="{{ \App\Support\EmbedEventView::pageUrl($event, $subdomain, $widgetDate, 'rsvp') }}" target="_blank" rel="noopener"
                           data-embed-checkout="rsvp" aria-label="{{ $widgetLabel }} ({{ __('messages.opens_in_new_tab') }})"
                           class="{{ $widgetButtonClass }}">
                            {{ $widgetLabel }}
                            <svg class="h-5 w-5 {{ $role->isRtl() ? 'scale-x-[-1]' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        </a>
                        <p class="gk-tembed-help">{{ __('messages.embed_events_help') }}</p>
                    @else
                        <div class="gk-tembed-none">
                            <p>{{ __('messages.registration_not_available_embed') }}</p>
                            <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top">
                                {{ __('messages.view_event') }}
                            </a>
                        </div>
                    @endif
                @endif

            </div>

            {{-- Powered by footer --}}
            @if ($role->showBranding())
            <div class="gk-tembed-foot">
                <a href="https://eventschedule.com" target="_blank" rel="noopener noreferrer">
                    {{ \App\Models\Role::creditChipTitle() }}
                </a>
            </div>
            @endif

        </div>
    </div>

</x-app-guest-layout>
