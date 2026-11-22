@php
    // The schedule the ticket was bought on, as on the ticket itself.
    $themeRole = $sale?->sellingRole();
@endphp
<x-app-layout :title="__('messages.payment_plan') . ($event ? ' - ' . $event->name : '')">

    <x-slot name="meta">
        @include('partials.private-page-meta')
    </x-slot>

    <x-slot name="head">
        <link href="/vendor/manrope/manrope.css" rel="stylesheet">
        @include('partials.guest-theme', ['role' => $themeRole, 'otherRole' => null, 'selectedGroup' => null])
        @include('partials.guest-kit-styles')
        @include('partials.guest-stage-styles')
        @include('partials.guest-ticket-styles')
    </x-slot>

    <main id="main-content" class="gk-tkpage" tabindex="-1">
      @if ($sale && $event)
        {{-- The way back, on the ground above the sheet, as on every guest page. --}}
        <div class="gk-topbar gk-tk-topbar">
          <a class="gk-tk-back" href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
            <span>{{ __('messages.view_ticket') }}</span>
          </a>
        </div>
      @endif

      {{-- One sheet (partials/guest-ticket-styles): what the plan is for, large, then the plan. --}}
      <div class="gk-tk-wrap gk-stage gk-tk-sheet">
        <section class="gk-tk-body-sheet">
          <header class="gk-tk-head">
            @if ($themeRole && $themeRole->profile_image_url)
              <img class="gk-tk-head-logo" src="{{ $themeRole->profile_image_url }}" alt="">
            @endif
            @if ($themeRole)
              <p class="gk-dateline">{{ $themeRole->translatedName() }}</p>
            @endif
            {{-- No Vue mount on this page; the values are user-controlled and stay inside
                 Blade's escaping and nothing else. --}}
            <h1 class="gk-title">{{ $event?->name }}</h1>
            @if ($sale)
              <p class="gk-subtitle"><bdi>{{ $sale->name }}</bdi></p>
            @endif
          </header>

          <div class="gk-tk-body-rows">
            @if (session('error'))
              <div class="gk-note gk-note-bad" role="alert">@include('ticket.partials.icon', ['icon' => 'alert'])<div class="gk-note-body">{{ session('error') }}</div></div>
            @endif

            @if (request()->boolean('paid'))
              <div class="gk-note gk-note-ok" role="status">@include('ticket.partials.icon', ['icon' => 'check'])<div class="gk-note-body">{{ __('messages.thank_you') }}</div></div>
            {{-- Gated on the card actually being stored, not on the query string. Stripe redirects
                 here whether or not anything reached us, and this used to confirm a swap that a
                 doc-following install never received - leaving the buyer reassured while the cron
                 kept declining the card they had just replaced. When it is false the card panel
                 below still shows the truth, so silence is the honest answer. --}}
            @elseif (request()->boolean('updated') && $cardStored)
              <div class="gk-note gk-note-ok" role="status">@include('ticket.partials.icon', ['icon' => 'check'])<div class="gk-note-body">{{ __('messages.update_payment_card') }}</div></div>
            @endif

            @if ($plan->status === 'cancelled')
              <p class="gk-story">{{ __('messages.installment_status_cancelled') }}</p>
            @else
              @if ($plan->isDelinquent())
                <div class="gk-note gk-note-warn">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div class="gk-note-body"><strong>{{ __('messages.ticket_payment_overdue') }}</strong><br>{{ __('messages.ticket_on_hold_sub', ['amount' => \App\Utils\MoneyUtils::format($plan->amountRemaining(), $plan->currency)]) }}</div>
                </div>
              @endif

              {{-- showCard: this page is opened with the PLAN's secret, which is the buyer's own,
                   so the card on file is shown here and never on the ticket, which a door scans. --}}
              @include('partials.installment-plan-panel', ['plan' => $plan, 'variant' => 'stage', 'showCard' => true])
            @endif
          </div>
        </section>
      </div>
    </main>
</x-app-layout>
