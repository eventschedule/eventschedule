@php
    // The schedule that sold this ticket: its name in the tab, its logo and its colour on the
    // page. Not $role, which is the event's performer and null for a venue's or a curator's event.
    $themeRole = $sale->sellingRole() ?? $role;
    $eventDate = $sale->event_date;
    $eventName = $event->translatedName();

    $isUnpaid = $sale->status === 'unpaid';
    $installmentPlan = $sale->installmentPlan;
    // The ticket is real and one payment behind: the sale is still `paid`.
    $planOnHold = $installmentPlan && $installmentPlan->isDelinquent();
    // A cancelled EVENT keeps its sales paid, so the sale alone would show a working code for
    // something that is not happening (ticket/order already treats it as released).
    $eventCancelled = (bool) $event->is_cancelled;
    // Will this code be accepted at the door.
    $valid = $sale->status === 'paid' && ! $planOnHold && ! $eventCancelled;

    // Arriving from a checkout: redirectToPurchaseLanding() flashes the legs that were bought.
    $fresh = collect(session('cart_purchased', []))
        ->contains(fn ($leg) => ($leg['event_id'] ?? null) === \App\Utils\UrlUtils::encodeId($event->id));
    // Back from a payment provider before its confirmation has reached us. Not "unpaid": the
    // buyer has just paid, and a NOT PAID stamp across their code is the last thing to show them.
    // Read from the session's own note of the landing (PaymentGatewayDriver::markLanded()), so a
    // reload, or this ticket opened from the order page of a several-event checkout, says the
    // same. The page asks where the sale stands until those seconds run out (the script at the
    // foot) and then shows whatever is true.
    $confirmingFor = ($isUnpaid && ! $event->is_cancelled && payment_gateways()->awaitsConfirmation($sale->payment_method, $sale))
        ? \App\Services\Payments\PaymentGatewayDriver::landedSecondsLeft($sale)
        : 0;
    $confirming = $confirmingFor > 0;
    // Inside an organizer's frame the event page refuses to be framed without embed=true, so a
    // link to it leaves the frame for a tab of its own.
    $isEmbed = request()->boolean('embed');
    $outOfFrame = $isEmbed ? ' target="_blank" rel="noopener noreferrer"' : '';

    $canShowPayNow = $isUnpaid && ! $confirming
        && payment_gateways()->canResumePayment($sale->payment_method, $sale)
        && (! $sale->group_id || $sale->isPrimarySale());
    $payAtDoor = $isUnpaid && ! $confirming && payment_gateways()->usesPaymentInstructions($sale->payment_method);

    $headerPassTicket = $sale->saleTickets->first(fn ($st) => $st->ticket && $st->ticket->is_pass);
    $admits = $headerPassTicket ? $headerPassTicket->ticket->admitsPerEvent() : ($sale->isRsvp() ? 1 : $sale->legTotalQuantity());

    // The zone, because a ticket is read by people who travelled. Carbon prints an offset for a
    // zone with no abbreviation of its own.
    $zone = $event->getStartDateTime($eventDate, true)->format('T');
    $zone = preg_match('/^[+-]/', $zone) ? 'GMT'.$zone : $zone;

    $eventUrl = $sale->getEventUrl();
    $venue = $event->venue;
    $venueAddress = $venue ? $venue->bestAddress() : null;
    $joinHref = $event->event_url ? $event->eventUrlHref() : null;
    $qrUrl = route('ticket.qr_code', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
    $ticketNotes = $event->parsedTicketNotesHtml($eventDate);

    // The same order as the message at the top, so the two never disagree: a cancelled event
    // used to say "this ticket is not valid" above a code stamped UNPAID and "Payment required
    // to enter".
    $stamp = $eventCancelled ? __('messages.cancelled')
        : ($planOnHold ? __('messages.ticket_on_hold') : ($isUnpaid ? __('messages.unpaid') : __('messages.void')));

    // Self-cancel is for a free place only. A gift-card order is a purchase: cancelling it would
    // be an instant refund to the card, so that stays the owner's.
    $canCancel = $sale->status === 'paid' && ! $eventCancelled
        && ($sale->isRsvp() || ($sale->payment_amount == 0 && $sale->groupTotalGiftCard() == 0))
        && (! $sale->group_id || $sale->isPrimarySale());
@endphp
<x-app-layout :title="$eventName . ' - ' . __('messages.tickets') . ($themeRole ? ' | ' . $themeRole->translatedName() : '')">

    <x-slot name="meta">
        @include('partials.private-page-meta')
    </x-slot>

    <x-slot name="footCode">
        @include('partials.site-foot-code')
        @include('partials.cart-clear')
    </x-slot>

    <x-slot name="head">
        @include('partials.site-head-code')

        {{-- Use the schedule's logo as the favicon (Pro/Enterprise). --}}
        @if ($themeRole && $themeRole->isPro() && $themeRole->profile_image_url)
            <link rel="icon" href="{{ $themeRole->profile_image_url }}">
            <link rel="apple-touch-icon" href="{{ $themeRole->profile_image_url }}">
        @endif

        {{-- A ticket belongs to the schedule that sold it, so this page names their app, not ours.
             Renders nothing when there is no schedule to name, which is the safe half of the trade. --}}
        @include('partials.web-app-manifest', ['manifestRole' => $themeRole])

        <link href="/vendor/manrope/manrope.css" rel="stylesheet">
        {{-- The selling schedule's colours and the ticket's own sheet. This page stays on the
             private shell (no social tags, canonical, structured data, pixel, cart or owner CSS,
             and nothing reported to the owner's Realtime view) and takes only the colours. --}}
        @include('partials.guest-theme', ['role' => $themeRole, 'otherRole' => null, 'selectedGroup' => null])
        @include('partials.guest-ticket-styles')
    </x-slot>

    <main id="main-content" class="gk-tkpage" tabindex="-1" data-sale-status="{{ $sale->status }}" data-ticket="{{ \App\Utils\UrlUtils::encodeId($sale->id) }}">
      <div class="gk-tk-wrap">

        <a href="{{ $eventUrl }}" class="gk-tk-back"{!! $outOfFrame !!}>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
          <span>{{ __('messages.view_event') }}</span>
        </a>

        <article class="gk-ticket {{ ($fresh && $valid) ? 'gk-ticket-fresh' : '' }}" id="ticket">
          <div class="gk-ticket-a">

            <header class="gk-tk-top {{ ($themeRole && $themeRole->profile_image_url) ? 'gk-tk-top-logo' : '' }}">
              @if ($themeRole && $themeRole->profile_image_url)
                <img src="{{ $themeRole->profile_image_url }}" alt="">
              @endif
              <div>
                <h1 class="gk-tk-title">{{ $eventName }}</h1>
                @if ($venue)
                  <span class="gk-tk-sub">{{ $venue->translatedName() ?: $venue->shortAddress() }}</span>
                @elseif ($event->event_url)
                  <span class="gk-tk-sub">{{ __('messages.online') }}</span>
                @endif
              </div>
            </header>

            <div class="gk-tk-note" aria-live="polite">
              {{-- A redirect that came back with an error and no section of its own to say it in.
                   The pass bookings and the payment plan each print theirs beside the action
                   that raised it. --}}
              @if (session('error') && ! $passBookable && ! $installmentPlan)
                <div class="gk-tk-msg gk-tk-msg-bad gk-tk-noprint" role="alert">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div>{{ session('error') }}</div>
                </div>
              @endif

              @if ($eventCancelled)
                <div class="gk-tk-msg gk-tk-msg-bad" data-ticket-state="event-cancelled">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div>
                    <strong>{{ __('messages.event_cancelled_heading') }}</strong><br>{{ __('messages.this_ticket_is_not_valid') }}
                    {{-- The buyer who opens a cancelled event's ticket is looking for their money. --}}
                    @if ($sale->status === 'refunded')
                      <br>{{ __('messages.this_ticket_is_refunded') }}
                    @endif
                  </div>
                </div>
              @elseif ($confirming)
                <div class="gk-tk-msg" data-ticket-state="confirming" data-confirming="{{ $confirmingFor }}" data-status-url="{{ route('ticket.status', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret], false) }}">
                  @include('ticket.partials.icon', ['icon' => 'clock'])
                  <div><strong>{{ __('messages.ticket_confirming_payment') }}</strong><br>{{ __('messages.ticket_confirming_payment_hint') }}</div>
                </div>
              @elseif ($isUnpaid)
                <div class="gk-tk-msg gk-tk-msg-warn" data-ticket-state="unpaid">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div>
                    <strong>{{ __('messages.unpaid') }}</strong><br>{{ __('messages.this_ticket_is_not_paid') }}
                    @if ($payAtDoor && ! $canShowPayNow)
                      <br>{{ __('messages.pay_at_the_door') }}
                    @endif
                  </div>
                </div>
                @if ($canShowPayNow)
                  <a href="{{ $eventUrl }}" class="gk-tk-btn gk-tk-btn-fill gk-tk-btn-block"{!! $outOfFrame !!}>{{ __('messages.complete_payment') }}</a>
                @endif
              @elseif ($sale->status !== 'paid')
                <div class="gk-tk-msg gk-tk-msg-bad" data-ticket-state="{{ $sale->status }}">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div>
                    <strong>{{ __('messages.' . $sale->status) }}</strong><br>
                    {{ match ($sale->status) {
                        'cancelled' => __('messages.this_ticket_is_cancelled'),
                        'refunded' => __('messages.this_ticket_is_refunded'),
                        'expired' => __('messages.this_reservation_has_expired'),
                        default => __('messages.this_ticket_is_not_valid'),
                    } }}
                  </div>
                </div>
              @elseif ($planOnHold)
                {{-- ON HOLD is its own tier: the ticket is real and one payment behind, and paying
                     restores it at once. --}}
                <div class="gk-tk-msg gk-tk-msg-warn" data-ticket-state="on-hold">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div><strong>{{ __('messages.ticket_payment_overdue') }}</strong><br>{{ __('messages.ticket_on_hold_sub', ['amount' => \App\Utils\MoneyUtils::format($installmentPlan->amountRemaining(), $installmentPlan->currency)]) }}</div>
                </div>
                <a href="{{ route('installment.view', ['plan_id' => \App\Utils\UrlUtils::encodeId($installmentPlan->id), 'secret' => $installmentPlan->secret]) }}" class="gk-tk-btn gk-tk-btn-fill gk-tk-btn-block gk-tk-noprint">{{ __('messages.pay_now') }}</a>
              @else
                {{-- Arriving. In the page for every good ticket and shown once: by the server on
                     the redirect from checkout, or by the script after a payment that needed a
                     moment to confirm. --}}
                <div class="gk-tk-hero gk-tk-noprint" data-ticket-state="going" data-hero @if (! $fresh) hidden @endif>
                  <span class="gk-tk-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                  <h2>{{ __('messages.ticket_you_are_going') }}</h2>
                </div>
              @endif
            </div>

            {{-- The code. --}}
            @if ($valid)
              <button type="button" class="gk-tk-qr" data-door-open aria-label="{{ __('messages.ticket_enlarge_code') }}">
                <img src="{{ $qrUrl }}" alt="">
              </button>
              <p class="gk-tk-qr-note">{{ __('messages.ticket_show_at_door') }}<span>{{ __('messages.ticket_admits', ['count' => $admits]) }}</span></p>
              <button type="button" class="gk-tk-btn gk-tk-enlarge gk-tk-noprint" data-door-open>
                @include('ticket.partials.icon', ['icon' => 'zoom'])
                {{ __('messages.ticket_enlarge_code') }}
              </button>
            @else
              <div class="gk-tk-qr gk-tk-qr-void">
                <img src="{{ $qrUrl }}" alt="">
                @if (! $confirming)
                  <span class="gk-tk-stamp {{ $planOnHold ? 'gk-tk-stamp-hold' : '' }}">{{ $stamp }}</span>
                @endif
              </div>
              @if (! $confirming)
              <p class="gk-tk-qr-note">
                @if ($eventCancelled)
                  {{ __('messages.this_ticket_is_not_valid') }}
                @elseif ($planOnHold)
                  {{ __('messages.installment_on_hold_door') }}
                @elseif ($isUnpaid)
                  {{ __('messages.payment_required_to_enter') }}
                @else
                  {{ __('messages.this_ticket_is_not_valid') }}
                @endif
              </p>
              @endif
            @endif

            {{-- What the organizer wants a ticket holder to know, directly under the code. --}}
            @if ($ticketNotes && trim(strip_tags($ticketNotes)) !== '')
              <div class="gk-tk-info">
                <h2 class="gk-tk-label">{{ __('messages.important_information') }}</h2>
                <div class="custom-content">{!! \App\Utils\UrlUtils::convertUrlsToLinks($ticketNotes) !!}</div>
              </div>
            @endif
          </div>

          <div class="gk-ticket-b">
            {{-- What to do next. Not for a ticket that is over. --}}
            @if (! $eventCancelled && ! in_array($sale->status, ['cancelled', 'refunded', 'expired']))
              <div class="gk-tk-tiles gk-tk-noprint">
                <button type="button" class="gk-tk-tile" data-calendar-toggle aria-expanded="false" aria-controls="ticket-calendar">
                  @include('ticket.partials.icon', ['icon' => 'calendar'])
                  <span>{{ __('messages.add_to_calendar') }}</span>
                </button>
                {{-- The EVENT's public address, never this page's: the ticket's link is the ticket. --}}
                @if ($eventUrl)
                <button type="button" class="gk-tk-tile" data-invite data-url="{{ $eventUrl }}" data-title="{{ $eventName }}" data-copied="{{ __('messages.link_copied') }}" aria-controls="ticket-invite">
                  @include('ticket.partials.icon', ['icon' => 'share'])
                  <span data-invite-label>{{ __('messages.ticket_invite_friends') }}</span>
                </button>
                @endif
                @if ($venueAddress)
                  <a class="gk-tk-tile" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($venueAddress) }}" target="_blank" rel="noopener noreferrer">
                    @include('ticket.partials.icon', ['icon' => 'pin'])
                    <span>{{ __('messages.directions') }}</span>
                  </a>
                @endif
              </div>
              {{-- Where neither the share sheet nor the clipboard is given (a frame on another
                   site, a page not served over https), the link itself, to copy by hand. The
                   button used to do nothing at all there. --}}
              @if ($eventUrl)
                <label class="gk-tk-menu gk-tk-noprint" id="ticket-invite" hidden>
                  <span class="gk-tk-quiet">{{ __('messages.ticket_invite_friends') }}</span>
                  <input type="text" readonly value="{{ $eventUrl }}" dir="ltr">
                </label>
              @endif
              <div class="gk-tk-menu gk-tk-noprint" id="ticket-calendar" hidden>
                <a href="{{ $event->getGoogleCalendarUrl($eventDate) }}" target="_blank" rel="noopener noreferrer">Google Calendar</a>
                {{-- The calendar file is the event page's, and a locked event gives it only to a
                     session that has entered the password: opened from the email, this was a 404. --}}
                @if (! $event->isPasswordProtected() || session()->has('event_password_'.$event->id))
                  <a href="{{ $event->getAppleCalendarUrl($eventDate, $sale->subdomain) }}"{!! $outOfFrame !!}>Apple Calendar</a>
                @endif
                <a href="{{ $event->getMicrosoftCalendarUrl($eventDate) }}" target="_blank" rel="noopener noreferrer">{{ __('messages.microsoft_calendar') }}</a>
              </div>
            @endif

            <dl class="gk-tk-facts">
              <div class="gk-tk-fact">
                <dt><span class="gk-tk-ico gk-tk-ico-a" aria-hidden="true">@include('ticket.partials.icon', ['icon' => 'calendar'])</span>{{ __('messages.date') }}</dt>
                  <dd>
                    {{ $event->is_multi_day ? $event->getDateRangeDisplay($eventDate) : $event->getStartDateTime($eventDate, true)->translatedFormat('F j, Y') }}
                    @if ($time = $event->getStartEndTime($eventDate, $event->use24HourTime()))
                      <small><bdi>{{ $time }}</bdi> <bdi>{{ $zone }}</bdi></small>
                    @endif
                  </dd>
              </div>

              @if ($venue || $event->event_url)
              <div class="gk-tk-fact">
                <dt><span class="gk-tk-ico gk-tk-ico-b" aria-hidden="true">@include('ticket.partials.icon', ['icon' => $venue ? 'pin' : 'link'])</span>{{ $venue ? __('messages.venue') : __('messages.online') }}</dt>
                  <dd>
                    @if ($venue)
                      {{ $venue->translatedName() ?: $venue->shortAddress() }}
                      @if ($venueAddress)
                        <small><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($venueAddress) }}" target="_blank" rel="noopener noreferrer">{{ $venue->shortAddress() }}</a></small>
                      @endif
                    @endif
                    {{-- The whole join link is the ticket holder's to see. Only a web link is
                         linked (eventUrlHref()); free-text join instructions read as text. --}}
                    @if ($event->event_url)
                      @if ($joinHref)
                        <small><a href="{{ $joinHref }}" target="_blank" rel="noopener noreferrer">{{ \App\Utils\UrlUtils::clean($event->event_url) }}</a></small>
                      @else
                        <small>{{ \App\Utils\UrlUtils::clean($event->event_url) }}</small>
                      @endif
                    @endif
                  </dd>
              </div>
              @endif

              <div class="gk-tk-fact">
                <dt><span class="gk-tk-ico gk-tk-ico-c" aria-hidden="true">@include('ticket.partials.icon', ['icon' => 'person'])</span>{{ __('messages.attendee') }}</dt>
                  <dd>
                    <bdi>{{ $sale->name }}</bdi>
                    <small>{{ __('messages.guests') }}: {{ $admits }}</small>
                  </dd>
              </div>

              @php
                  // For a grouped primary buyer, aggregate seats across the whole group so they see what they paid for
                  if ($sale->isPrimarySale() && $sale->group_id) {
                      $groupSaleTickets = \App\Models\SaleTicket::whereIn('sale_id',
                          \App\Models\Sale::where('group_id', $sale->group_id)->where('is_deleted', false)->pluck('id')
                      )->with('ticket')->get();
                      $aggregated = [];
                      foreach ($groupSaleTickets as $st) {
                          if (! $st->ticket) continue;
                          $key = $st->ticket_id;
                          if (! isset($aggregated[$key])) {
                              // seat_labels is carried here rather than looked up in the loop below: these
                              // rows are stdClass totals for the whole group, not SaleTicket models, so
                              // seatLabels() cannot be called on them.
                              $aggregated[$key] = (object) ['ticket' => $st->ticket, 'quantity' => 0, 'seat_labels' => []];
                          }
                          $aggregated[$key]->quantity += $st->quantity;
                          $aggregated[$key]->seat_labels = array_merge($aggregated[$key]->seat_labels, $st->seatLabels());
                      }
                      $aggregatedCollection = collect(array_values($aggregated));
                      $regularTickets = $aggregatedCollection->filter(fn($st) => ! $st->ticket->is_addon);
                      $addonTickets = $aggregatedCollection->filter(fn($st) => $st->ticket->is_addon);
                  } else {
                      $regularTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && !$st->ticket->is_addon);
                      $addonTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && $st->ticket->is_addon);
                  }

                  // The two branches hand back different types - real SaleTicket models, or the stdClass
                  // totals a grouped primary buyer gets - and only one of them answers seatLabels().
                  $seatLabelsFor = fn ($st) => $st instanceof \App\Models\SaleTicket
                      ? $st->seatLabels()
                      : ($st->seat_labels ?? []);

                  $ticketDiscountTotal = $sale->isRsvp() ? 0 : $sale->legTotalDiscount();
                  $ticketGiftCardTotal = $sale->isRsvp() ? 0 : $sale->legTotalGiftCard();
              @endphp
              @if ($sale->isRsvp() || $regularTickets->count() > 0)
              <div class="gk-tk-fact">
                <dt><span class="gk-tk-ico gk-tk-ico-d" aria-hidden="true">@include('ticket.partials.icon', ['icon' => 'ticket'])</span>{{ __('messages.tickets') }}</dt>
                  @if ($sale->isRsvp())
                    <dd>{{ __('messages.registered') }}</dd>
                  @else
                    @foreach ($regularTickets as $saleTicket)
                      <dd>
                        <bdi>{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</bdi> <bdi>&times;&nbsp;{{ $saleTicket->quantity }}</bdi>
                        @if ($saleTicket->ticket->is_pass)
                          <span class="gk-tk-pill">{{ __('messages.season_pass') }} · {{ __('messages.pass_valid_all_dates') }}</span>
                        @endif
                        @php $seatLabels = $seatLabelsFor($saleTicket); @endphp
                        @if (count($seatLabels))
                          {{-- A literal dot: an entity here is escaped by the braces and printed as "&middot;". --}}
                          <small>{{ implode(' · ', $seatLabels) }}</small>
                        @endif
                      </dd>
                    @endforeach
                  @endif
              </div>
              @endif
            </dl>

            @if ($ticketDiscountTotal > 0 || $ticketGiftCardTotal > 0)
              <div class="gk-tk-sec">
                <ul class="gk-tk-rows">
                  @if ($ticketDiscountTotal > 0)
                    <li class="gk-tk-row gk-tk-good"><span class="gk-tk-good">{{ __('messages.discount') }}@if ($sale->promoCode) (<bdi>{{ $sale->promoCode->code }}</bdi>)@endif</span><span><bdi>-{{ \App\Utils\MoneyUtils::format($ticketDiscountTotal, $event->ticket_currency_code) }}</bdi></span></li>
                  @endif
                  @if ($ticketGiftCardTotal > 0)
                    <li class="gk-tk-row gk-tk-good"><span class="gk-tk-good">{{ __('messages.gift_card') }}</span><span><bdi>-{{ \App\Utils\MoneyUtils::format($ticketGiftCardTotal, $event->ticket_currency_code) }}</bdi></span></li>
                  @endif
                </ul>
              </div>
            @endif

            @php
                $passSaleTicket = $sale->isRsvp() ? null : $sale->saleTickets->first(fn ($st) => $st->ticket && $st->ticket->is_pass);
            @endphp
            @if ($passSaleTicket)
              @php
                  $passTicket = $passSaleTicket->ticket;
                  $passUsed = $passSaleTicket->passUsageCount();
                  // Only sub-schedule / specific-events scopes list individual events;
                  // all_events renders a label and per_occurrence (season pass) lists none.
                  $coveredEvents = in_array($passTicket->pass_scope, ['sub_schedule', 'specific_events'])
                      ? \App\Models\Event::whereIn('id', $passTicket->coveredEventIds($role))->orderBy('starts_at')->limit(50)->get()
                      : collect();
              @endphp
              <div class="gk-tk-sec">
                <h2 class="gk-tk-label">{{ __('messages.subscription') }}</h2>
                <ul class="gk-tk-rows">
                  <li class="gk-tk-row">
                    <span>{{ __('messages.visits_used') }}</span>
                    <span>
                      @if ($passTicket->pass_usage_type === 'total' && $passTicket->pass_max_uses)
                        {{ $passUsed }} / {{ $passTicket->pass_max_uses }}
                      @elseif ($passTicket->pass_usage_type === 'unlimited' || $passTicket->pass_usage_type === 'per_occurrence')
                        {{ __('messages.pass_unlimited_visits') }}
                      @else
                        {{ $passUsed }}
                      @endif
                    </span>
                  </li>
                  @if ($passTicket->admitsPerEvent() > 1)
                    <li class="gk-tk-row"><span>{{ __('messages.pass_admits_per_event') }}</span><span>{{ $passTicket->admitsPerEvent() }}</span></li>
                    <li class="gk-tk-quiet">{{ __('messages.pass_admits_includes_holder') }}</li>
                  @endif
                  @if ($passSaleTicket->pass_expires_at)
                    <li class="gk-tk-row"><span>{{ __('messages.pass_valid_until') }}</span><span>{{ $passSaleTicket->pass_expires_at->format('M j, Y') }}</span></li>
                  @endif
                  @if ($passTicket->pass_scope === 'all_events')
                    <li class="gk-tk-quiet">{{ __('messages.pass_scope_all_events') }}</li>
                  @elseif ($coveredEvents->count() > 0)
                    <li>
                      <div class="gk-tk-quiet">{{ __('messages.covered_events') }}</div>
                      <ul class="gk-tk-rows">
                        @foreach ($coveredEvents as $ce)
                          <li><bdi>{{ $ce->name }}</bdi>@if ($ce->starts_at) <span class="gk-tk-quiet">&middot; {{ \Carbon\Carbon::parse($ce->saleEventDateFromStartsAt())->format('M j, Y') }}</span>@endif</li>
                        @endforeach
                      </ul>
                    </li>
                  @endif
                </ul>
              </div>

              @if (! empty($passBookable))
                @php
                    $openOccurrences = collect($bookableOccurrences)->reject(fn ($o) => $o['booked'])->values();
                    $passCancelCutoff = ($passPolicyTicket ?? null)?->pass_cancel_cutoff_hours;
                @endphp
                <div class="gk-tk-sec gk-tk-noprint" id="pass-booking">
                  <h2 class="gk-tk-label">{{ __('messages.book_your_dates') }}</h2>

                  @if (! is_null($passCancelCutoff))
                    <p class="gk-tk-quiet">
                      {{ $passCancelCutoff == 0 ? __('messages.pass_cancel_window_until_start') : __('messages.pass_cancel_window_hours', ['hours' => $passCancelCutoff]) }}
                      {{ $passPolicyTicket->passLateCancelPolicy() === 'block' ? __('messages.pass_late_cancel_note_block') : __('messages.pass_late_cancel_note_forfeit') }}
                    </p>
                  @endif

                  @if (session('message'))
                    <div class="gk-tk-msg gk-tk-msg-ok" role="status">@include('ticket.partials.icon', ['icon' => 'check'])<div>{{ session('message') }}</div></div>
                  @endif
                  @if (session('error'))
                    <div class="gk-tk-msg gk-tk-msg-bad" role="alert">@include('ticket.partials.icon', ['icon' => 'alert'])<div>{{ session('error') }}</div></div>
                  @endif

                  @if (count($bookedOccurrences) > 0)
                    <p class="gk-tk-quiet">{{ __('messages.your_booked_dates') }}</p>
                    <ul class="gk-tk-rows">
                      @foreach ($bookedOccurrences as $b)
                        @php
                            $pastCutoff = ! empty($b['past_cutoff']);
                            $latePolicy = $b['late_policy'] ?? null;
                        @endphp
                        <li class="gk-tk-book">
                          <span>{{ $b['date_label'] ?: $b['date'] }}@if ($b['event_name'] !== $event->name) <span class="gk-tk-quiet">&middot; <bdi>{{ $b['event_name'] }}</bdi></span>@endif
                            @if (! empty($b['seat_label']))
                              <small>{{ $b['seat_label'] }}</small>
                            @endif
                            {{-- Not while the deadline itself has passed (undo grace): a past
                                 instant must not be presented as a live cutoff. --}}
                            @if (! empty($b['cancel_deadline_label']) && ! $pastCutoff && empty($b['deadline_past']))
                              <small>{{ __('messages.pass_cancel_deadline_note', ['deadline' => $b['cancel_deadline_label']]) }}</small>
                            @endif
                          </span>
                          @if ($pastCutoff && $latePolicy === 'block')
                            <span class="gk-tk-quiet">{{ __('messages.pass_cancel_closed') }}</span>
                          @else
                            <form action="{{ route('pass.cancel_booking', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}" method="POST" data-confirm="{{ ($pastCutoff && $latePolicy === 'forfeit') ? __('messages.pass_forfeit_warning') : __('messages.are_you_sure') }}">
                              @csrf
                              <input type="hidden" name="book_event_id" value="{{ $b['event_id'] }}">
                              <input type="hidden" name="date" value="{{ $b['date'] }}">
                              @if ($pastCutoff && $latePolicy === 'forfeit')
                                {{-- The ack tells the server the forfeit warning was actually shown;
                                     without it a stale pre-deadline page gets a confirm bounce instead
                                     of a silent forfeit. --}}
                                <input type="hidden" name="forfeit_ack" value="1">
                                <button type="submit" class="gk-tk-book-warn">{{ __('messages.pass_cancel_no_credit') }}</button>
                              @else
                                <button type="submit">{{ __('messages.cancel') }}</button>
                              @endif
                            </form>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  @endif

                  @if ($openOccurrences->count() > 0)
                    <p class="gk-tk-quiet">{{ __('messages.available_dates') }}</p>
                    <ul class="gk-tk-rows">
                      @foreach ($openOccurrences as $o)
                        <li class="gk-tk-book">
                          <span>{{ $o['date_label'] ?: $o['date'] }}@if ($o['event_name'] !== $event->name) <span class="gk-tk-quiet">&middot; <bdi>{{ $o['event_name'] }}</bdi></span>@endif
                            @if (! is_null($o['seats_left'])) <small>{{ $o['sold_out'] ? __('messages.sold_out') : trans_choice('messages.seats_left', $o['seats_left'], ['count' => $o['seats_left']]) }}</small>@endif
                          </span>
                          @if ($o['sold_out'])
                            <span class="gk-tk-quiet">{{ __('messages.sold_out') }}</span>
                          @else
                            <form action="{{ route('pass.book', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}" method="POST">
                              @csrf
                              <input type="hidden" name="book_event_id" value="{{ $o['event_id'] }}">
                              <input type="hidden" name="date" value="{{ $o['date'] }}">
                              <button type="submit" class="gk-tk-book-go">{{ __('messages.book') }}</button>
                            </form>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  @elseif (count($bookedOccurrences) === 0)
                    <p class="gk-tk-quiet">{{ __('messages.no_dates_to_book') }}</p>
                  @endif
                </div>
              @endif
            @endif

            @if (! $sale->isRsvp() && $addonTickets->count() > 0)
              <div class="gk-tk-sec">
                <h2 class="gk-tk-label">{{ __('messages.add_ons') }}</h2>
                <ul class="gk-tk-rows">
                  @foreach ($addonTickets as $saleTicket)
                    <li class="gk-tk-row">
                      <span>
                        <bdi>{{ $saleTicket->ticket->type ?: __('messages.add_on') }}</bdi>
                        @if ($saleTicket->ticket->url)
                          {{-- Owner-typed: linked only when it is a web link (safeHref()). --}}
                          @if ($addonHref = \App\Utils\UrlUtils::safeHref($saleTicket->ticket->url))
                            <br><a href="{{ $addonHref }}" target="_blank" rel="noopener noreferrer" class="gk-tk-quiet">{{ $saleTicket->ticket->url }}</a>
                          @else
                            <br><span class="gk-tk-quiet">{{ $saleTicket->ticket->url }}</span>
                          @endif
                        @endif
                      </span>
                      <span><bdi>&times;&nbsp;{{ $saleTicket->quantity }}</bdi></span>
                    </li>
                  @endforeach
                </ul>
              </div>
            @endif

            {{-- Payment plan. The page is already authenticated by $sale->secret, so the panel
                 simply renders here for anyone holding the ticket link. The error slot is
                 unconditional: the only other session('error') block below the code is inside
                 the pass bookings, which an ordinary installment ticket does not have, so every
                 bail from InstallmentController::pay() used to reload the page and say nothing. --}}
            @if ($installmentPlan && session('error'))
              <div class="gk-tk-sec gk-tk-noprint">
                <div class="gk-tk-msg gk-tk-msg-bad" role="alert">@include('ticket.partials.icon', ['icon' => 'alert'])<div>{{ session('error') }}</div></div>
              </div>
            @endif
            @if ($installmentPlan && $installmentPlan->status !== 'cancelled')
              <div class="gk-tk-sec">
                @include('partials.installment-plan-panel', ['plan' => $installmentPlan, 'variant' => 'dark'])
              </div>
            @endif

            {{-- What the buyer answered to the schedule's own questions. --}}
            @php
              $hasEventCustomFields = $event->custom_fields && count($event->custom_fields) > 0;
              $hasTicketCustomFields = false;
              foreach ($sale->saleTickets as $st) {
                if ($st->ticket && $st->ticket->custom_fields && count($st->ticket->custom_fields) > 0) {
                  $hasTicketCustomFields = true;
                  break;
                }
              }
            @endphp
            @if ($hasEventCustomFields || $hasTicketCustomFields)
              <div class="gk-tk-sec">
                <h2 class="gk-tk-label">{{ __('messages.details') }}</h2>
                <ul class="gk-tk-rows">
                  @if ($hasEventCustomFields)
                    @php $eventFallbackIndex = 1; @endphp
                    @foreach ($event->custom_fields as $fieldKey => $fieldConfig)
                      @php
                        $index = $fieldConfig['index'] ?? $eventFallbackIndex;
                        $eventFallbackIndex++;
                      @endphp
                      @if ($index >= 1 && $index <= 10 && $sale->{"custom_value{$index}"})
                        <li class="gk-tk-row"><span><bdi>{{ $fieldConfig['name'] }}</bdi></span><span><bdi>{{ $sale->{"custom_value{$index}"} }}</bdi></span></li>
                      @endif
                    @endforeach
                  @endif
                  @foreach ($sale->saleTickets as $saleTicket)
                    @if ($saleTicket->ticket && $saleTicket->ticket->custom_fields && count($saleTicket->ticket->custom_fields) > 0)
                      <li class="gk-tk-quiet"><bdi>{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</bdi></li>
                      @php $ticketFallbackIndex = 1; @endphp
                      @foreach ($saleTicket->ticket->custom_fields as $fieldKey => $fieldConfig)
                        @php
                          $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                          $ticketFallbackIndex++;
                        @endphp
                        @if ($index >= 1 && $index <= 10 && $saleTicket->{"custom_value{$index}"})
                          <li class="gk-tk-row"><span><bdi>{{ $fieldConfig['name'] }}</bdi></span><span><bdi>{{ $saleTicket->{"custom_value{$index}"} }}</bdi></span></li>
                        @endif
                      @endforeach
                    @endif
                  @endforeach
                </ul>
              </div>
            @endif

            {{-- Google's badge keeps its own white ground (partials/wallet-buttons says why). --}}
            @if (\App\Services\Wallet\GoogleWalletService::canOffer($sale, $event))
              {{-- No inline display here: it would outrank the print sheet's "not on paper". --}}
              <div class="gk-tk-sec gk-tk-center gk-tk-noprint">
                @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $event])
              </div>
            @endif

            @if ($canCancel)
              <div class="gk-tk-actions gk-tk-noprint">
                <button type="button" class="gk-tk-btn gk-tk-btn-danger" data-cancel-toggle aria-expanded="false" aria-controls="ticket-cancel">
                  {{ $sale->isRsvp() ? __('messages.cancel_registration') : __('messages.cancel_ticket') }}
                </button>
              </div>
              {{-- Asked in the page, in the page's own words and buttons, not in a browser box
                   that says "OK" and "Cancel" about a cancellation. --}}
              <form id="ticket-cancel" class="gk-tk-confirm gk-tk-noprint" role="group" aria-labelledby="ticket-cancel-question" hidden action="{{ route('rsvp.cancel', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}" method="POST">
                @csrf
                <input type="hidden" name="secret" value="{{ $sale->secret }}">
                {{-- The group's name, so a screen reader hears the question when focus arrives
                     on "Keep my place". --}}
                <p id="ticket-cancel-question">{{ __('messages.are_you_sure') }}</p>
                <div>
                  <button type="button" class="gk-tk-btn" data-cancel-keep>{{ __('messages.ticket_keep') }}</button>
                  <button type="submit" class="gk-tk-btn gk-tk-btn-danger">{{ $sale->isRsvp() ? __('messages.cancel_registration') : __('messages.cancel_ticket') }}</button>
                </div>
              </form>
              {{-- Without scripts nothing can open the question, so it is simply there. --}}
              <noscript><style {!! nonce_attr() !!}>.gk-tk-confirm[hidden] { display: grid; } [data-cancel-toggle] { display: none; }</style></noscript>
            @endif

            <div class="gk-tk-foot">
              @php
                $termsUrl = $event->terms_url ?: (config('app.hosted')
                  ? policy_url('terms')
                  : policy_url('terms', '/self-hosting-terms-of-service'));
                // The event's own terms link is owner-typed free text: linked only through
                // safeHref(), and shown as text when it is no web link.
                $termsHref = $event->terms_url ? \App\Utils\UrlUtils::safeHref($event->terms_url) : $termsUrl;
                // The mail goes to whoever made the event, so that is who is named: on a ticket
                // bought from a curator's page the selling schedule is somebody else. The address
                // is printed too, for paper and for a phone with no mail app.
                $contactEmail = $event->user?->email;
                $organizer = ($event->creatorRole ?? $themeRole)?->translatedName() ?: $contactEmail;
              @endphp
              @if ($contactEmail)
                <a href="mailto:{{ $contactEmail }}">{{ __('messages.ticket_contact_organizer', ['name' => $organizer]) }}</a>
                @if ($organizer !== $contactEmail)
                  <span class="gk-tk-foot-addr"><bdi>{{ $contactEmail }}</bdi></span>
                @endif
              @endif
              @if ($termsHref)
                <a href="{{ $termsHref }}" target="_blank" rel="noopener noreferrer">{{ __('messages.terms_and_conditions') }}</a>
              @else
                <span>{{ __('messages.terms_and_conditions') }}: {{ Str::limit($event->terms_url, 60) }}</span>
              @endif
            </div>
          </div>
        </article>

        @if ($sale->status === 'paid' && ! $eventCancelled)
          <div class="gk-tk-noprint" style="width: 100%;">
            @include('partials.push-optin', ['pushEmail' => $sale->email, 'pushName' => $event->name])
          </div>
        @endif
      </div>

    </main>

    {{-- The door view. Outside the ticket: the ticket has a filter, and a fixed element inside a
         filtered one is no longer fixed to the screen. And outside <main>: the page is a stacking
         context of its own, so in there the door sat UNDER the cookie notice whatever z-index it
         was given, on the first open of a ticket link at the door. --}}
      @if ($valid)
        <div class="gk-door" id="ticket-door" role="dialog" aria-modal="true" aria-label="{{ __('messages.ticket_your_code') }}" hidden>
          <button type="button" class="gk-door-close" data-door-close>{{ __('messages.close') }}</button>
          <div class="gk-door-code"><img src="{{ $qrUrl }}" alt=""></div>
          <p class="gk-door-who"><b><bdi>{{ $sale->name }}</bdi></b>{{ __('messages.ticket_admits', ['count' => $admits]) }}</p>
          <p class="gk-door-hint">{{ __('messages.ticket_brightness_hint') }}</p>
          {{-- For a door with no signal: the code as a picture on the phone. --}}
          <a class="gk-door-save" href="{{ $qrUrl }}" download="ticket.png">{{ __('messages.ticket_save_code') }}</a>
        </div>
      @endif

    <script {!! nonce_attr() !!}>
    (function () {
        function each(selector, fn) { Array.prototype.forEach.call(document.querySelectorAll(selector), fn); }

        try {
            {{-- The door view: the code on white, the screen kept awake, Escape and Close to leave,
                 and the keyboard kept inside it while it is open. --}}
            var door = document.getElementById('ticket-door');
            var opener = null;
            var wake = null;

            function keepAwake() {
                if (navigator.wakeLock && navigator.wakeLock.request) {
                    navigator.wakeLock.request('screen').then(function (lock) { wake = lock; }).catch(function () {});
                }
            }

            {{-- On the document, not on the door: after a tap on its white ground nothing in it
                 has focus, and keys heard only inside it were not heard at all. --}}
            function doorKeys(e) {
                if (e.key === 'Escape') { closeDoor(); return; }
                if (e.key !== 'Tab') { return; }
                var stops = door.querySelectorAll('button, a[href]');
                var first = stops[0], last = stops[stops.length - 1];
                if (! door.contains(document.activeElement)) { e.preventDefault(); first.focus(); }
                else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (! e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }

            {{-- A phone lets go of the wake lock when the page is hidden, and the holder of a
                 ticket at a door switches away and back. --}}
            function doorSeen() {
                if (! document.hidden && ! door.hidden) { keepAwake(); }
            }

            function closeDoor() {
                door.hidden = true;
                document.documentElement.style.overflow = '';
                document.removeEventListener('keydown', doorKeys);
                document.removeEventListener('visibilitychange', doorSeen);
                if (wake) { try { wake.release(); } catch (e) {} wake = null; }
                if (opener) { opener.focus(); }
            }

            if (door) {
                each('[data-door-open]', function (button) {
                    button.addEventListener('click', function () {
                        opener = button;
                        door.hidden = false;
                        document.documentElement.style.overflow = 'hidden';
                        door.querySelector('[data-door-close]').focus();
                        document.addEventListener('keydown', doorKeys);
                        document.addEventListener('visibilitychange', doorSeen);
                        keepAwake();
                    });
                });
                door.querySelector('[data-door-close]').addEventListener('click', closeDoor);
            }

            {{-- A button that shows or hides the block it controls. --}}
            function toggles(selector) {
                each(selector, function (button) {
                    var target = document.getElementById(button.getAttribute('aria-controls'));
                    if (! target) { return; }
                    button.addEventListener('click', function () {
                        target.hidden = ! target.hidden;
                        button.setAttribute('aria-expanded', target.hidden ? 'false' : 'true');
                        if (! target.hidden) {
                            var stop = target.querySelector('a[href], button');
                            if (stop) { stop.focus(); }
                        }
                    });
                });
            }
            toggles('[data-calendar-toggle]');
            toggles('[data-cancel-toggle]');
            each('[data-cancel-keep]', function (button) {
                button.addEventListener('click', function () {
                    document.getElementById('ticket-cancel').hidden = true;
                    var toggle = document.querySelector('[data-cancel-toggle]');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                });
            });

            {{-- Invite friends: the phone's own share sheet, or the event's link on the clipboard,
                 or (where a browser gives neither, as inside a frame on another site) the link
                 itself, shown and selected. Closing the share sheet is not a failure. --}}
            each('[data-invite]', function (button) {
                var url = button.getAttribute('data-url');
                var label = button.querySelector('[data-invite-label]');
                var was = label.textContent;
                var byHand = function () {
                    var box = document.getElementById('ticket-invite');
                    if (! box) { return; }
                    box.hidden = false;
                    var field = box.querySelector('input');
                    field.focus();
                    field.select();
                };
                var copy = function () {
                    if (! (navigator.clipboard && navigator.clipboard.writeText)) { byHand(); return; }
                    navigator.clipboard.writeText(url).then(function () {
                        label.textContent = button.getAttribute('data-copied');
                        setTimeout(function () { label.textContent = was; }, 2000);
                    }).catch(byHand);
                };
                button.addEventListener('click', function () {
                    if (! navigator.share) { copy(); return; }
                    navigator.share({ title: button.getAttribute('data-title'), url: url }).catch(function (error) {
                        if (! error || error.name !== 'AbortError') { copy(); }
                    });
                });
            });

            {{-- Arriving after a payment that needed a moment (ticket/partials/confirming-script
                 leaves the note): show it once, on the ticket the note is for. --}}
            var hero = document.querySelector('[data-hero]');
            var arrived = 'es_ticket_arrived_' + document.getElementById('main-content').getAttribute('data-ticket');
            try {
                if (sessionStorage.getItem(arrived)) {
                    sessionStorage.removeItem(arrived);
                    if (hero) {
                        hero.hidden = false;
                        document.getElementById('ticket').classList.add('gk-ticket-fresh');
                    }
                }
            } catch (e) {}
        } catch (e) {}
    })();
    </script>
    @if ($confirming)
      @include('ticket.partials.confirming-script')
    @endif
</x-app-layout>
