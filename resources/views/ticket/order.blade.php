@php
    // The schedule the order was placed on: the page wears its colour, as each ticket does.
    $themeRole = $sales->first()?->sellingRole() ?? $role;
@endphp
<x-app-layout :title="__('messages.your_tickets') . ($themeRole ? ' | ' . $themeRole->translatedName() : '')">

    <x-slot name="meta">
        @include('partials.private-page-meta')
    </x-slot>

    <x-slot name="footCode">
        @include('partials.site-foot-code')
        @include('partials.cart-clear')
    </x-slot>

    <x-slot name="head">
        @include('partials.site-head-code')

        {{-- The order belongs to the schedule that sold it; a null schedule deliberately renders nothing. --}}
        @include('partials.web-app-manifest', ['manifestRole' => $themeRole])

        <link href="/vendor/manrope/manrope.css" rel="stylesheet">
        @include('partials.guest-theme', ['role' => $themeRole, 'otherRole' => null, 'selectedGroup' => null])
        @include('partials.guest-ticket-styles')
    </x-slot>

    <main id="main-content" class="gk-tkpage" tabindex="-1">
      <div class="gk-tk-wrap">
        <section class="gk-tk-card">
          <header class="gk-tk-top {{ ($themeRole && $themeRole->profile_image_url) ? 'gk-tk-top-logo' : '' }}">
            @if ($themeRole && $themeRole->profile_image_url)
              <img src="{{ $themeRole->profile_image_url }}" alt="">
            @endif
            <div>
              <h1 class="gk-tk-title">{{ __('messages.your_tickets') }}</h1>
              <span class="gk-tk-sub">{{ __('messages.order_includes_events', ['count' => $sales->count()]) }}</span>
            </div>
          </header>

          <div class="gk-tk-body">
            <ul class="gk-tk-legs">
              @foreach ($sales as $sale)
                @php
                    $legEvent = $sale->event;
                    // A leg the organizer cancelled, refunded or let expire is still part of what
                    // the buyer purchased, so it stays listed - but it is no longer a ticket, and
                    // linking it would hand out a QR for a seat that has already been released.
                    // A leg that is deleted outright rather than released is not listed at all: both
                    // queries above filter is_deleted, which is the owner erasing the record.
                    //
                    // A cancelled EVENT counts too: the sale keeps its paid status, so without this
                    // the buyer saw a live ticket for an event that is not happening.
                    $isReleased = in_array($sale->status, ['cancelled', 'refunded', 'expired'])
                        || $legEvent->is_cancelled;
                    // One day is a date, not a range from a day to itself.
                    $legDate = $legEvent->is_multi_day
                        ? $legEvent->getDateRangeDisplay($sale->event_date)
                        : $legEvent->getStartDateTime($sale->event_date, true)->format('F j, Y');
                @endphp
                {{-- The row is a list item holding the link, not the link itself, so the wallet
                     badge below can be a sibling. An anchor inside an anchor is invalid HTML and
                     browsers recover from it by closing the outer one early. --}}
                <li class="gk-tk-leg {{ $isReleased ? 'gk-tk-leg-off' : '' }}">
                  @if ($isReleased)
                    <div class="gk-tk-leg-main">
                      <span><b>{{ $legEvent->translatedName() }}</b><small>{{ $legDate }}</small></span>
                      <span class="gk-tk-quiet">{{ $legEvent->is_cancelled && $sale->status === 'paid' ? __('messages.event_cancelled_heading') : __('messages.'.$sale->status) }}</span>
                    </div>
                  @else
                    <a class="gk-tk-leg-main" href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($legEvent->id), 'secret' => $sale->secret]) }}">
                      <span><b>{{ $legEvent->translatedName() }}</b><small>{{ $legDate }}</small></span>
                      <span class="gk-tk-leg-go">
                        {{ __('messages.view_ticket') }}
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                      </span>
                    </a>
                  @endif
                  {{-- Saves a multi-event buyer opening every leg just to add its pass. canOffer()
                       already returns false for a released leg, so this follows $isReleased without
                       restating it. The condensed badge is Google's own narrow variant, for exactly
                       this kind of list. --}}
                  @if (\App\Services\Wallet\GoogleWalletService::canOffer($sale, $legEvent))
                    <div class="gk-tk-leg-extra gk-tk-noprint">
                      @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $legEvent, 'condensed' => true])
                    </div>
                  @endif
                </li>
              @endforeach
            </ul>

            {{-- Each event is scanned with its own code, so there is no single QR for the order. --}}
            <p class="gk-tk-quiet">{{ __('messages.order_ticket_per_event_help') }}</p>
          </div>
        </section>
      </div>
    </main>

</x-app-layout>
