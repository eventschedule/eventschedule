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
{{-- Rows between hairlines, in the stage's inks (partials/guest-stage-styles), on the event
     page's sheet and on the widget's alike. Its own small sheet of style, since it is the only
     place these rows are drawn. --}}
<style {!! nonce_attr() !!}>
    .gk-etl { margin: 0; padding: 0; list-style: none; border-block: 1px solid var(--gk-line); }
    .gk-etl-row { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-block: .875rem; }
    .gk-etl-row + .gk-etl-row { border-top: 1px solid var(--gk-line); }
    .gk-etl-off { opacity: .6; }
    .gk-etl-name { min-width: 0; font-size: 1.0625rem; font-weight: 600; color: var(--gk-ink); overflow-wrap: anywhere; }
    .gk-etl-price { flex: none; font-size: 1.0625rem; font-weight: 600; color: var(--gk-ink); font-variant-numeric: tabular-nums; }
    .gk-etl-out { font-size: .875rem; font-weight: 500; color: var(--gk-ink-3); }
</style>
<ul class="gk-etl" data-embed-tickets v-pre>
    @foreach ($embedTicketShown as $embedTicket)
        @php
            $embedTicketLeft = $embedTicket->availableQuantity($embedTicketDate);
            $embedTicketOff = $embedTicket->isSalesEnded() || $embedTicket->isSalesNotStarted() || ($embedTicketLeft !== null && $embedTicketLeft <= 0);
        @endphp
        <li class="gk-etl-row {{ $embedTicketOff ? 'gk-etl-off' : '' }}" data-embed-ticket>
            <span class="gk-etl-name"><bdi>{{ $embedTicket->type ?: __('messages.ticket') }}</bdi></span>
            <span class="gk-etl-price">
                @if ((float) $embedTicket->price <= 0)
                    {{ __('messages.free') }}
                @else
                    <bdi>{{ \App\Utils\MoneyUtils::format($embedTicket->price, $event->ticket_currency_code) }}</bdi>
                @endif
                @if ($embedTicketLeft !== null && $embedTicketLeft <= 0 && ! $embedTicket->isSalesEnded() && ! $embedTicket->isSalesNotStarted())
                    <span class="gk-etl-out">&middot; {{ __('messages.sold_out') }}</span>
                @endif
            </span>
        </li>
    @endforeach
</ul>
@endif
