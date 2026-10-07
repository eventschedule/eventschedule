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
        @include('partials.guest-ticket-styles')
    </x-slot>

    <main id="main-content" class="gk-tkpage" tabindex="-1">
      <div class="gk-tk-wrap">

        @if ($sale && $event)
          <a class="gk-tk-back" href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 19l-7-7 7-7"/></svg>
            <span>{{ __('messages.view_ticket') }}</span>
          </a>
        @endif

        <section class="gk-tk-card">
          <header class="gk-tk-top {{ ($themeRole && $themeRole->profile_image_url) ? 'gk-tk-top-logo' : '' }}">
            @if ($themeRole && $themeRole->profile_image_url)
              <img src="{{ $themeRole->profile_image_url }}" alt="">
            @endif
            <div>
              {{-- No Vue mount on this page; the values are user-controlled and stay inside
                   Blade's escaping and nothing else. --}}
              <h1 class="gk-tk-title">{{ $event?->name }}</h1>
              @if ($sale)
                <span class="gk-tk-sub"><bdi>{{ $sale->name }}</bdi></span>
              @endif
            </div>
          </header>

          <div class="gk-tk-body">
            @if (session('error'))
              <div class="gk-tk-msg gk-tk-msg-bad" role="alert">@include('ticket.partials.icon', ['icon' => 'alert'])<div>{{ session('error') }}</div></div>
            @endif

            @if (request()->boolean('paid'))
              <div class="gk-tk-msg gk-tk-msg-ok" role="status">@include('ticket.partials.icon', ['icon' => 'check'])<div>{{ __('messages.thank_you') }}</div></div>
            {{-- Gated on the card actually being stored, not on the query string. Stripe redirects
                 here whether or not anything reached us, and this used to confirm a swap that a
                 doc-following install never received - leaving the buyer reassured while the cron
                 kept declining the card they had just replaced. When it is false the card panel
                 below still shows the truth, so silence is the honest answer. --}}
            @elseif (request()->boolean('updated') && $cardStored)
              <div class="gk-tk-msg gk-tk-msg-ok" role="status">@include('ticket.partials.icon', ['icon' => 'check'])<div>{{ __('messages.update_payment_card') }}</div></div>
            @endif

            @if ($plan->status === 'cancelled')
              <p>{{ __('messages.installment_status_cancelled') }}</p>
            @else
              @if ($plan->isDelinquent())
                <div class="gk-tk-msg gk-tk-msg-warn">
                  @include('ticket.partials.icon', ['icon' => 'alert'])
                  <div><strong>{{ __('messages.ticket_payment_overdue') }}</strong><br>{{ __('messages.ticket_on_hold_sub', ['amount' => \App\Utils\MoneyUtils::format($plan->amountRemaining(), $plan->currency)]) }}</div>
                </div>
              @endif

              {{-- showCard: this page is opened with the PLAN's secret, which is the buyer's own,
                   so the card on file is shown here and never on the ticket, which a door scans. --}}
              @include('partials.installment-plan-panel', ['plan' => $plan, 'variant' => 'dark', 'showCard' => true])
            @endif
          </div>
        </section>
      </div>
    </main>
</x-app-layout>
