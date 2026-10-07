{{-- What a server-drawn card or row of a guest page says about an event's tickets on one date:
     the next-event card above a schedule's month (role/show-guest) and the rows of an event
     page's other events (event/partials/more-events).

     One definition, and it is the answer the schedule list's own script gives a row (rowSoldOut,
     rowFree, rowPrice, rowLow and rowSoldElsewhere in role/partials/calendar): Sold out; or Free
     entry or the price, with Few left; or, for an event sold somewhere else, the price its owner
     typed and the code that takes something off it. Never a number of seats.

     $chipEvent with its tickets LOADED by the caller (Event::cardTicketFields() reads them, and
     a list must load them once, not once a row), $chipDate the occurrence, $chipRole the
     schedule whose page this is (its own word for "free entry"). Prints nothing when there is
     nothing to say. Not inside a Vue mount: Blade's escaping is what guards the owner's text. --}}
@php
    $chipFacts = $chipEvent->cardTicketFields();
    // A series' occurrence that has begun is no longer selling (Event::passesSellingWindow()).
    // An event on one date needs no such test: its facts are already empty by then.
    $chipOver = $chipEvent->days_of_week && $chipDate && ! $chipEvent->passesSellingWindow($chipDate);
    $chipGone = ! $chipOver && in_array($chipDate, $chipFacts['sold_out_dates'], true);
    $chipFree = ($chipFacts['ticket_free'] && ! $chipOver) || $chipEvent->rsvp_enabled;
    $chipPrice = $chipOver ? null : $chipFacts['ticket_from'];
    $chipLow = ! $chipOver && in_array($chipDate, $chipFacts['low_stock_dates'], true);
    // Sold somewhere else, at a price the owner typed: not beside a price of our own.
    $chipElsewhere = $chipEvent->registrationHref() && $chipEvent->ticket_price !== null
        && ! $chipFacts['ticket_free'] && ! $chipEvent->rsvp_enabled && ! $chipFacts['ticket_from'];
@endphp
@if ($chipGone || $chipFree || $chipPrice || $chipElsewhere)
<span class="gk-row-chips">
  @if ($chipGone)
    <span class="gk-chip gk-chip-out">{{ __('messages.sold_out') }}</span>
  @elseif ($chipElsewhere)
    @if ((float) $chipEvent->ticket_price == 0.0)
      <span class="gk-chip gk-chip-free">{{ $chipRole->customLabel('free_entry') }}</span>
    @else
      <span class="gk-chip"><bdi>{{ \App\Utils\MoneyUtils::format($chipEvent->ticket_price, $chipEvent->ticket_currency_code) }}</bdi></span>
      @if ($chipEvent->coupon_code)
        <span class="gk-chip gk-chip-accent">{{ __('messages.coupon_code') }}: <bdi>{{ $chipEvent->coupon_code }}</bdi>@if ($chipEvent->couponDiscountLabel()) (<bdi>{{ $chipEvent->couponDiscountLabel() }}</bdi>)@endif</span>
      @endif
    @endif
  @else
    @if ($chipFree)
      <span class="gk-chip gk-chip-free">{{ $chipRole->customLabel('free_entry') }}</span>
    @elseif ($chipPrice)
      <span class="gk-chip"><bdi>{{ $chipPrice }}</bdi></span>
    @endif
    @if ($chipLow)
      <span class="gk-chip gk-chip-few">{{ __('messages.few_left') }}</span>
    @endif
  @endif
</span>
@endif
