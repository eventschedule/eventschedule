{{-- What our own tickets cost, and whether any are left, on a card of the schedule's list (a
     guest page, from a tablet up; role/partials/calendar includes it inside the card's v-for,
     whose item is `event`). The card said "Free entry" for a sign-up and the price an owner
     typed for an event sold elsewhere, and nothing at all about tickets sold here: a visitor
     opened the event to learn what it cost, or that it had sold out.

     The same shape as the two badges beside it. The facts are Event::cardTicketFields(): a
     price somebody can still pay, sold out and "few left" by day, never a number of seats, and
     nothing once the night's sales are over (cardHasTickets). --}}
<div v-if="cardHasTickets(event)" class="flex items-center gap-4" data-card-tickets>
    <div data-reveal-tile class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
        <svg width="24" height="24" viewBox="0 0 20 20" fill="{{ $accentColor }}" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
    </div>
    <div class="flex flex-col">
        <span class="text-lg font-semibold text-gray-900 dark:text-white">
            <span v-if="rowSoldOut(event)">{{ __('messages.sold_out') }}</span>
            <span v-else-if="event.ticket_free">{{ $label('free_entry') }}</span>
            <bdi v-else v-text="rowPrice(event)"></bdi>
        </span>
        <span v-if="!rowSoldOut(event) && rowLow(event)" class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.few_left') }}</span>
    </div>
</div>
