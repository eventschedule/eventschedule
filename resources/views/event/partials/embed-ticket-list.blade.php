{{-- An event's ticket types and what each costs, to read and not to pick: inside an embed, which is
     a frame on the organizer's own website. It has no session (a cross-site frame is never sent the
     SameSite=lax cookie), so a form posted from it could not complete, and picking quantities there
     would miss what the event's own page asks for (seats, add-ons, promo codes, a date, limits).
     The button beside it opens that page in a new tab with the form open.

     The rows a visitor of the event's page could be shown (Event::ticketPriceSummary() picks from
     the same ones): this plan's sellable types, those on sale, or every one where the owner chose
     "show unavailable tickets". Add-ons are never a way in. v-pre: a ticket's name is the owner's. --}}
@php
    $embedTicketDate = $embedTicketDate ?? ($date ?? null);
    $embedTicketRows = ($event->tickets_enabled && $event->canSellTickets($embedTicketDate))
        ? $event->tickets->reject(fn ($ticket) => $ticket->is_addon)
            ->filter(fn ($ticket) => $ticket->setRelation('event', $event)->isSellable())
        : collect();
    $embedTicketShown = $embedTicketRows->filter(fn ($ticket) => ! $ticket->isSalesEnded() && ! $ticket->isSalesNotStarted());
    if ($embedTicketShown->isEmpty() && $event->show_unavailable_tickets) {
        $embedTicketShown = $embedTicketRows;
    }
@endphp
@if ($embedTicketShown->isNotEmpty())
<ul class="divide-y divide-gray-200 dark:divide-gray-700" data-embed-tickets v-pre>
    @foreach ($embedTicketShown as $embedTicket)
        @php
            $embedTicketLeft = $embedTicket->availableQuantity($embedTicketDate);
            $embedTicketOff = $embedTicket->isSalesEnded() || $embedTicket->isSalesNotStarted() || ($embedTicketLeft !== null && $embedTicketLeft <= 0);
        @endphp
        <li class="flex items-baseline justify-between gap-4 py-2.5 {{ $embedTicketOff ? 'opacity-60' : '' }}" data-embed-ticket>
            <span class="min-w-0 text-base font-medium text-gray-900 dark:text-gray-100"><bdi>{{ $embedTicket->type ?: __('messages.ticket') }}</bdi></span>
            <span class="flex-shrink-0 text-base text-gray-700 dark:text-gray-300">
                @if ((float) $embedTicket->price <= 0)
                    {{ __('messages.free') }}
                @else
                    <bdi>{{ \App\Utils\MoneyUtils::format($embedTicket->price, $event->ticket_currency_code) }}</bdi>
                @endif
                @if ($embedTicketLeft !== null && $embedTicketLeft <= 0 && ! $embedTicket->isSalesEnded() && ! $embedTicket->isSalesNotStarted())
                    <span class="text-sm text-gray-500 dark:text-gray-400">&middot; {{ __('messages.sold_out') }}</span>
                @endif
            </span>
        </li>
    @endforeach
</ul>
@endif
