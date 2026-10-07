{{-- The orders of the Sales tab. It is drawn into #sales-table by ticket/sales, and again by the
     same address fetched (X-Requested-With) each time the filter, the sort or "Include past
     events" changes, so nothing here may depend on the page around it.

     ONE list for every width (.page-table): a table from a tablet up, a stack of rows on a phone.
     It used to be drawn twice, the second time as cards with an Alpine menu of their own, and the
     two had drifted: the cards said "Refund" where the table said "Refund ticket", and "Cancel"
     where the table said "Cancel ticket". --}}
@php
    $salesMenuItem = 'flex w-full items-center px-4 py-2.5 text-sm text-start text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors';
    $salesMenuDanger = 'flex w-full items-center px-4 py-2.5 text-sm text-start text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 focus:bg-red-50 dark:focus:bg-red-900/20 focus:outline-none transition-colors';
@endphp

@if ($sales->count() > 0)
<div class="ap-card rounded-xl overflow-hidden">
    <div class="page-scroll">
        <table class="page-table is-wide is-hover sales-list">
            <thead>
                <tr>
                    <x-page-sort column="name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.customer') }}</x-page-sort>
                    <x-page-sort column="event_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.event') }}</x-page-sort>
                    <x-page-sort column="payment_amount" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.total') }}</x-page-sort>
                    <x-page-sort column="transaction_reference" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.transaction_reference') }}</x-page-sort>
                    <x-page-sort column="status" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.status') }}</x-page-sort>
                    <x-page-sort column="created_at" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.date') }}</x-page-sort>
                    <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                @php
                    $isGroupedPrimary = $sale->isPrimarySale();
                    $guestCount = $groupCounts[$sale->id] ?? 0;
                    $hasGuests = $isGroupedPrimary && $guestCount > 0;
                    $encodedSaleId = \App\Utils\UrlUtils::encodeId($sale->id);

                    $hasEventCustomFields = $sale->event->custom_fields && count($sale->event->custom_fields) > 0;
                    $hasTicketCustomFields = false;
                    foreach ($sale->saleTickets as $st) {
                        if ($st->ticket && $st->ticket->custom_fields && count($st->ticket->custom_fields) > 0) {
                            $hasTicketCustomFields = true;
                            break;
                        }
                    }
                    $hasAnyCustomValues = false;
                    if ($hasEventCustomFields) {
                        $eventFallbackIdx = 1;
                        foreach ($sale->event->custom_fields as $fk => $fc) {
                            $idx = $fc['index'] ?? $eventFallbackIdx;
                            $eventFallbackIdx++;
                            if ($idx >= 1 && $idx <= 10 && $sale->{"custom_value{$idx}"}) {
                                $hasAnyCustomValues = true;
                                break;
                            }
                        }
                    }
                    if (! $hasAnyCustomValues && $hasTicketCustomFields) {
                        foreach ($sale->saleTickets as $st) {
                            if (! $st->ticket || ! $st->ticket->custom_fields) {
                                continue;
                            }
                            $ticketFallbackIdx = 1;
                            foreach ($st->ticket->custom_fields as $fk => $fc) {
                                $idx = $fc['index'] ?? $ticketFallbackIdx;
                                $ticketFallbackIdx++;
                                if ($idx >= 1 && $idx <= 10 && $st->{"custom_value{$idx}"}) {
                                    $hasAnyCustomValues = true;
                                    break 2;
                                }
                            }
                        }
                    }

                    $rowAmount = $sale->legTotalPayment();
                    $rowDiscount = $sale->legTotalDiscount();
                    $rowGiftCard = $sale->legTotalGiftCard();

                    // The gateway owns the format of its own reference, so it also owns whether
                    // there is anywhere to link to. Null means "no dashboard page" (an Invoice
                    // Ninja 'sub:' subscription id, an unknown legacy method, or a marker with no
                    // driver at all) and falls through to plain text.
                    $referenceUrl = payment_gateways()->get($sale->payment_method)?->referenceUrl($sale);

                    // Confirmed only. refundedTotal() also counts claims we have not heard back
                    // on, and showing those as refunded would tell an owner a customer was paid
                    // when the gateway never said so.
                    $refundedSoFar = $sale->refundedConfirmedTotal();

                    // Asked of the driver, not inferred from payment_method: a sale marked paid by
                    // hand carries the translated string manual_payment in transaction_reference,
                    // so 'stripe' is not evidence Stripe holds anything. Prefixed names because
                    // a php block shares the view's scope with the loop above.
                    $refundDriver = payment_gateways()->get($sale->payment_method);
                    $refundViaGateway = $sale->status === 'paid'
                        && $refundDriver?->supportsRefunds()
                        && $refundDriver->refundReferenceFor($sale) !== null;
                    $refundRemaining = $refundViaGateway ? $sale->refundableRemaining() : 0.0;
                    $refundAskAmount = $refundViaGateway && $refundDriver->supportsPartialRefunds() && $refundRemaining > 0
                        // A payment plan refunds leg by leg and only in full, so there is no
                        // amount to ask for.
                        && ! $sale->installmentPlan;
                    // 'rsvp' is a provenance marker with no driver behind it (config/payments.php
                    // says so), so it can never be a capability question. A free registration took
                    // no money, and Cancel Ticket below is what the action would really be.
                    $refundShow = $sale->status === 'paid'
                        && $sale->payment_method !== 'rsvp'
                        && (! $refundViaGateway || $refundRemaining > 0);
                @endphp
                <tr>
                    <td class="c-main">
                        <div class="sales-customer">
                            @if ($hasAnyCustomValues || $hasGuests)
                            <button type="button" class="sales-open" data-toggle-row data-sale-id="{{ $encodedSaleId }}" aria-expanded="false">
                                <svg class="w-4 h-4 transition-transform duration-200 rtl:-scale-x-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="sr-only">{{ __('messages.details') }}</span>
                            </button>
                            @endif
                            <div class="page-person-text">
                                <span class="c-strong"><bdi>{{ $sale->name }}</bdi></span>
                                @if ($hasGuests)
                                <span class="event-chip">+ {{ $guestCount }} {{ __('messages.guests') }}</span>
                                @endif
                                <span class="c-sub">
                                    <a href="mailto:{{ $sale->email }}" class="event-link" dir="ltr" title="{{ $sale->email }}">{{ $sale->email }}</a>
                                    @if ($sale->phone)
                                    <a href="tel:{{ $sale->phone }}" class="event-link" dir="ltr">{{ $sale->phone }}</a>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="c-wrap c-event">
                        <a href="{{ $sale->getEventUrl() }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $sale->event->name }}</bdi></a>
                    </td>
                    <td class="c-total" @unless ($sale->isRsvp()) data-label="{{ __('messages.total') }}" @endunless>
                        @if ($sale->isRsvp())
                        <span class="c-strong">{{ __('messages.registered') }}</span>
                        @else
                        <span class="c-strong">{{ number_format($rowAmount, 2, '.', ',') }}</span>
                        <span class="c-quiet">{{ $sale->event->ticket_currency_code }}</span>
                        @endif
                        @if ($sale->promo_code_id && $sale->promoCode)
                        <span class="event-chip">{{ $sale->promoCode->code }} -{{ number_format($rowDiscount, 2, '.', ',') }}</span>
                        @endif
                        @if ($rowGiftCard > 0)
                        <span class="event-chip">{{ __('messages.gift_card') }} -{{ number_format($rowGiftCard, 2, '.', ',') }}</span>
                        @endif
                    </td>
                    <td class="c-quiet c-wrap c-reference">
                        @if ($sale->transaction_reference == __('messages.manual_payment'))
                            {{ __('messages.manual_payment') }}
                        @elseif ($sale->payment_method == 'import')
                            {{ __('messages.manual_import') }}
                        @elseif ($sale->payment_method == 'box_office')
                            {{ __('messages.seating_box_office_sale') }}
                        @elseif ($referenceUrl)
                            <a href="{{ $referenceUrl }}" target="_blank" rel="noopener" class="event-link c-mono c-clip" dir="ltr" title="{{ $sale->transaction_reference }}">{{ $sale->transaction_reference }}</a>
                        @elseif ($sale->transaction_reference)
                            <span class="c-mono c-clip" dir="ltr" title="{{ $sale->transaction_reference }}">{{ $sale->transaction_reference }}</span>
                        @endif
                    </td>
                    <td class="c-status">
                        <x-sale-status :status="$sale->status" />
                        @if ($sale->feedback)
                        <span class="sales-rating" title="{{ __('messages.feedback') }}: {{ $sale->feedback->rating }}/5">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                            <span class="sr-only">{{ __('messages.feedback') }}:</span> {{ $sale->feedback->rating }}/5
                        </span>
                        @endif
                        {{-- Outside the status mark on purpose. A partially refunded sale stays `paid`, but a
                             sale can also reach `refunded` while a claim is still unconfirmed - refundableRemaining()
                             counts pending and awaiting_reconciliation rows against the ceiling - and hiding the
                             warning behind `paid` made it vanish at exactly the moment it matters. Reads the
                             eager-loaded relation, so this costs no query per row. --}}
                        @if ($refundedSoFar > 0)
                        <span class="c-sub">{{ __('messages.refunded_so_far') }}: {{ \App\Utils\MoneyUtils::format($refundedSoFar, $sale->event?->ticket_currency_code) }}</span>
                        @endif
                        @if ($sale->hasUnconfirmedRefund())
                        <div class="sales-warning mt-2 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
                            <svg class="h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            </svg>
                            <span class="text-xs text-amber-800 dark:text-amber-200">{{ __('messages.refund_awaiting_confirmation') }}</span>
                        </div>
                        @endif
                    </td>
                    <td class="c-date">{{ $sale->created_at->translatedFormat('M j, Y') }}</td>
                    <td class="c-actions">
                        {{-- Three dots, named for a screen reader: the word "Actions" on every
                             row cost the list a column's width on a laptop. --}}
                        <button type="button" class="page-tool sales-menu" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" id="sale-actions-menu-button-{{ $encodedSaleId }}" aria-expanded="false" aria-haspopup="true" title="{{ __('messages.actions') }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                            </svg>
                            <span class="sr-only">{{ __('messages.actions') }}</span>
                        </button>
                        <div id="sale-actions-pop-up-menu-{{ $encodedSaleId }}" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-56 rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="sale-actions-menu-button-{{ $encodedSaleId }}" tabindex="-1">
                            <div class="py-1" role="none" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}">
                                <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" target="_blank" rel="noopener" class="{{ $salesMenuItem }}" role="menuitem">
                                    {{ __('messages.view_ticket') }}
                                </a>
                                <button type="button" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" data-resend-email="{{ $encodedSaleId }}" class="{{ $salesMenuItem }}" role="menuitem">
                                    {{ __('messages.send_email') }}
                                </button>
                                @if ($sale->status === 'unpaid')
                                <button type="button" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" data-sale-action="mark_paid" data-sale-id="{{ $encodedSaleId }}" class="{{ $salesMenuItem }}" role="menuitem">
                                    {{ __('messages.mark_paid') }}
                                </button>
                                @endif
                                @if ($refundShow)
                                <button type="button" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" data-sale-action="refund" data-sale-id="{{ $encodedSaleId }}" @if($refundAskAmount) data-refund-remaining="{{ number_format($refundRemaining, 3, '.', '') }}" data-refund-decimals="{{ \App\Utils\MoneyUtils::decimalsFor($sale->event?->ticket_currency_code) }}" data-refund-remaining-formatted="{{ \App\Utils\MoneyUtils::format($refundRemaining, $sale->event?->ticket_currency_code) }}" @endif class="{{ $salesMenuItem }}" role="menuitem">
                                    {{-- Honest label. A rail that cannot send money back gets "Mark as
                                         Refunded", because the old wording promised a refund and only
                                         ever changed a status. An appointment booking is not a ticket, so
                                         it gets the plain "Refund". --}}
                                    {{ $refundViaGateway ? ($sale->event?->appointment_type_id ? __('messages.refund') : __('messages.refund_ticket')) : __('messages.mark_as_refunded') }}
                                </button>
                                @endif
                                @if (in_array($sale->status, ['unpaid', 'paid']))
                                <button type="button" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" data-sale-action="cancel" data-sale-id="{{ $encodedSaleId }}" class="{{ $salesMenuItem }}" role="menuitem">
                                    {{ __('messages.cancel_ticket') }}
                                </button>
                                @endif
                                @if (! $sale->is_deleted)
                                <div class="my-1 border-t border-gray-100 dark:border-gray-700" role="none"></div>
                                <button type="button" data-popup-toggle="sale-actions-pop-up-menu-{{ $encodedSaleId }}" data-sale-action="delete" data-sale-id="{{ $encodedSaleId }}" class="{{ $salesMenuDanger }}" role="menuitem">
                                    {{ __('messages.delete') }}
                                </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @if ($hasAnyCustomValues)
                <tr class="custom-fields-row detail-row-{{ $encodedSaleId }} sales-detail hidden">
                    <td colspan="7" class="c-main">
                        <dl class="sales-fields sales-detail-body">
                            {{-- What the buyer answered on the event's own questions. --}}
                            @if ($hasEventCustomFields)
                                @php $eventFallbackIndex = 1; @endphp
                                @foreach ($sale->event->custom_fields as $fieldKey => $fieldConfig)
                                    @php
                                        $index = $fieldConfig['index'] ?? $eventFallbackIndex;
                                        $eventFallbackIndex++;
                                    @endphp
                                    @if ($index >= 1 && $index <= 10 && $sale->{"custom_value{$index}"})
                                    <div>
                                        <dt><bdi>{{ $fieldConfig['name'] }}</bdi>:</dt>
                                        <dd><bdi>{{ $sale->{"custom_value{$index}"} }}</bdi></dd>
                                    </div>
                                    @endif
                                @endforeach
                            @endif

                            {{-- And on each ticket type's. --}}
                            @foreach ($sale->saleTickets as $saleTicket)
                                @if ($saleTicket->ticket && $saleTicket->ticket->custom_fields && count($saleTicket->ticket->custom_fields) > 0)
                                <div class="sales-fields-group">
                                    <dt><bdi>{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</bdi></dt>
                                </div>
                                    @php $ticketFallbackIndex = 1; @endphp
                                    @foreach ($saleTicket->ticket->custom_fields as $fieldKey => $fieldConfig)
                                        @php
                                            $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                                            $ticketFallbackIndex++;
                                        @endphp
                                        @if ($index >= 1 && $index <= 10 && $saleTicket->{"custom_value{$index}"})
                                        <div class="is-nested">
                                            <dt><bdi>{{ $fieldConfig['name'] }}</bdi>:</dt>
                                            <dd><bdi>{{ $saleTicket->{"custom_value{$index}"} }}</bdi></dd>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </dl>
                    </td>
                </tr>
                @endif
                @if ($hasGuests)
                    @foreach ($sale->guestSales as $guest)
                    <tr class="guest-row detail-row-{{ $encodedSaleId }} sales-detail hidden">
                        <td class="c-main">
                            <div class="sales-customer">
                                <span class="sales-guest-mark" aria-hidden="true">&#8627;</span>
                                <div class="page-person-text">
                                    <span class="c-strong"><bdi>{{ $guest->name }}</bdi></span>
                                    <span class="c-sub">
                                        <a href="mailto:{{ $guest->email }}" class="event-link" dir="ltr" title="{{ $guest->email }}">{{ $guest->email }}</a>
                                        @if ($guest->phone)
                                        <a href="tel:{{ $guest->phone }}" class="event-link" dir="ltr">{{ $guest->phone }}</a>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        {{-- A guest has no event, total or reference of their own, so what can be
                             done for them takes that room: at the end of the row, beside the
                             menu's column, two links ran off the card. --}}
                        <td colspan="3" class="c-guest-actions">
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($guest->event_id), 'secret' => $guest->secret]) }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view_ticket') }}</a>
                            <button type="button" data-id="{{ \App\Utils\UrlUtils::encodeId($guest->id) }}" class="event-link js-resend-email">{{ __('messages.send_email') }}</button>
                        </td>
                        <td class="c-status"><x-sale-status :status="$guest->status" /></td>
                        <td class="c-date">{{ $guest->created_at->translatedFormat('M j, Y') }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($sales->hasPages())
<div class="page-pager">
    {{ $sales->links() }}
</div>
@endif
@elseif (($planBlockedRoles ?? collect())->isNotEmpty())
{{-- The notice above already explains the empty table. Falling through to the ordinary empty
     state here would contradict it: "No sales found. Create events to start selling tickets."
     is the wrong advice for someone whose schedule has plenty of both. --}}
@else
{{-- Three ways to have nothing here, and the old page gave the first one's advice for all of
     them: nothing matches what was typed, nothing is coming up (earlier orders are a switch
     away), or nothing has been sold at all. --}}
<div class="ap-card rounded-xl">
    @if (filled(request()->query('filter')))
    <x-page-empty :title="__('messages.no_results_found')" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
    @elseif (request()->query('include_past') != 1)
    <x-page-empty :title="__('messages.no_sales')" :text="__('messages.no_sales_description').' '.__('messages.no_sales_past_hint', ['label' => __('messages.include_past_events')])" icon="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
    @else
    <x-page-empty :title="__('messages.no_sales')" :text="__('messages.no_sales_description')" icon="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
    @endif
</div>
@endif
