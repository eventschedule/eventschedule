{{-- noindex: a form, not content. Every schedule has one, and none is worth a search result. --}}
<x-app-guest-layout :role="$role" :page-title="__('messages.gift_card')" :no-index="true">

@php
    $currency = strtoupper($role->gift_card_currency_code ?: 'USD');
    $turnstileActive = \App\Utils\TurnstileUtils::isActiveForRequest();
@endphp

@if ($turnstileActive)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer {!! nonce_attr() !!}></script>
@endif

{{-- The way back, on the background above the sheet, as on every guest page. --}}
<div class="gk-topbar gk-gf-topbar">
    <a href="{{ $role->getGuestUrl() }}" class="gk-glass">
        <svg class="h-4 w-4 {{ $role->isRtl() ? 'scale-x-[-1]' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        <span>{{ __('messages.gift_card_back_to_schedule', ['schedule' => $role->name]) }}</span>
    </a>
</div>

{{-- One sheet (partials/guest-stage-styles): the schedule small, the page's name large, and the
     form to fill in as the one raised card on it. --}}
<div class="gk-stage gk-p-gift" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">

        {{-- Schedule branding --}}
        <header class="gk-gf-head">
            @if ($role->profile_image_url)
            <a href="{{ $role->getGuestUrl() }}" class="gk-gf-logo">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}">
            </a>
            @endif
            <p class="gk-dateline">{{ $role->name }}</p>
            <h1 class="gk-title">🎁 {{ __('messages.gift_cards') }}</h1>
            <p class="gk-subtitle">{{ __('messages.gift_card_purchase_subtitle', ['schedule' => $role->name]) }}</p>
        </header>

        @if (session('error'))
        <div class="gk-note gk-note-bad gk-gf-gap">
            {{ session('error') }}
        </div>
        @endif

        <div class="gk-card gk-band gk-gf-form">
            <form method="post" action="{{ route('gift_card.purchase.store', ['subdomain' => $role->subdomain]) }}">
                @csrf

                <x-honeypot />

                {{-- Amount --}}
                <fieldset class="gk-gf-field">
                    <legend class="gk-gf-lab">{{ __('messages.amount') }}</legend>
                    <div class="gk-gf-amounts">
                        @foreach ($role->gift_card_amounts as $index => $amount)
                        <label class="gk-gf-amount">
                            <input type="radio" name="amount" value="{{ $amount }}" class="peer sr-only"
                                {{ (string) old('amount', $role->gift_card_amounts[0] ?? '') === (string) $amount ? 'checked' : '' }} required>
                            <span class="gk-gf-amount-face">
                                {{ \App\Utils\MoneyUtils::format($amount, $currency) }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </fieldset>

                {{-- Buyer --}}
                <div class="gk-gf-field">
                    <label for="purchaser_name" class="gk-gf-lab">{{ __('messages.name') }}</label>
                    <input type="text" id="purchaser_name" name="purchaser_name" value="{{ old('purchaser_name') }}" required maxlength="255"
                        class="block w-full gk-gf-input" autocomplete="name">
                    <x-input-error :messages="$errors->get('purchaser_name')" class="mt-2" />
                </div>
                <div class="gk-gf-field">
                    <label for="purchaser_email" class="gk-gf-lab">{{ __('messages.email') }}</label>
                    <input type="email" id="purchaser_email" name="purchaser_email" value="{{ old('purchaser_email') }}" required maxlength="255"
                        class="block w-full gk-gf-input" autocomplete="email">
                    <x-input-error :messages="$errors->get('purchaser_email')" class="mt-2" />
                </div>

                {{-- Send to myself --}}
                <div class="gk-gf-field">
                    <label class="gk-gf-check">
                        <input type="checkbox" id="send_to_self" name="send_to_self" value="1" {{ old('send_to_self') ? 'checked' : '' }}
                            class="gk-gf-box">
                        <span>{{ __('messages.gift_card_send_to_self') }}</span>
                    </label>
                </div>

                {{-- Recipient --}}
                <div id="recipient-fields">
                    <div class="gk-gf-field">
                        <label for="recipient_name" class="gk-gf-lab">{{ __('messages.recipient_name') }}</label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" required maxlength="255"
                            class="block w-full gk-gf-input">
                        <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
                    </div>
                    <div class="gk-gf-field">
                        <label for="recipient_email" class="gk-gf-lab">{{ __('messages.recipient_email') }}</label>
                        <input type="email" id="recipient_email" name="recipient_email" value="{{ old('recipient_email') }}" required maxlength="255"
                            class="block w-full gk-gf-input">
                        <x-input-error :messages="$errors->get('recipient_email')" class="mt-2" />
                    </div>
                    <div class="gk-gf-field">
                        <label for="message" class="gk-gf-lab">{{ __('messages.gift_card_personal_message') }} <span class="gk-gf-optional">({{ __('messages.optional') }})</span></label>
                        <textarea id="message" name="message" rows="3" maxlength="500" dir="auto"
                            class="block w-full gk-gf-input"
                            placeholder="{{ __('messages.gift_card_message_placeholder') }}">{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>
                </div>

                {{-- Expectations --}}
                <div class="gk-note gk-gf-gap">
                    <div class="gk-note-body gk-gf-notes">
                    <p id="delivery-note-recipient">
                        {{ $role->gift_card_payment_method === 'cash'
                            ? __('messages.gift_card_delivery_note_cash')
                            : __('messages.gift_card_delivery_note') }}
                    </p>
                    @if ($role->gift_card_valid_days)
                    <p>{{ __('messages.gift_card_validity_note', ['days' => $role->gift_card_valid_days]) }}</p>
                    @else
                    <p>{{ __('messages.gift_card_never_expires') }}</p>
                    @endif
                    <p>{{ __('messages.gift_card_works_any_event', ['schedule' => $role->name]) }}</p>
                    </div>
                </div>

                @if ($turnstileActive)
                <div class="gk-gf-field">
                    <div class="cf-turnstile" data-sitekey="{{ \App\Utils\TurnstileUtils::getSiteKey() }}"></div>
                    <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
                </div>
                @endif

                <button type="submit" class="gk-btn gk-btn-primary gk-cta-main gk-gf-buy">
                    {{ __('messages.buy_gift_card') }}
                </button>
            </form>
        </div>
</div>

{{-- On body, where the tokens themselves are declared (partials/guest-theme). On :root they
     read a variable that does not exist there yet, and every use of them drew nothing: the
     chosen amount had no fill and no field showed a focus ring. --}}
<style {!! nonce_attr() !!}>
    body {
        --gift-accent: var(--es-accent);
        --gift-contrast: var(--es-accent-text);
    }
</style>

{{-- The page on the stage: a narrow sheet, the form as the one raised card on it, its fields in
     the stage's own colour and every colour a token, so light, dark and any accent follow. --}}
<style {!! nonce_attr() !!}>
    .gk-gf-topbar { max-width: 40rem; }
    .gk-p-gift { max-width: 40rem; margin-top: .875rem; }
    @media (min-width: 40rem) { .gk-p-gift { margin-top: .875rem; padding-inline: clamp(1.5rem, 4vw, 2.75rem); } }
    .gk-gf-head { display: grid; gap: .625rem; margin-bottom: 2rem; }
    .gk-gf-logo { display: block; width: 3.5rem; height: 3.5rem; margin-bottom: .375rem; }
    .gk-gf-logo img { display: block; width: 100%; height: 100%; border-radius: .875rem; object-fit: cover; }
    .gk-p-gift .gk-title { font-size: clamp(2rem, 1.5rem + 2vw, 3rem); }
    .gk-p-gift .gk-subtitle { font-size: clamp(1.0625rem, .95rem + .5vw, 1.25rem); }
    .gk-gf-gap { margin-bottom: 1.25rem; }
    .gk-gf-field { margin: 0 0 1.25rem; padding: 0; border: 0; min-width: 0; }
    .gk-gf-lab { display: block; margin-bottom: .375rem; padding: 0; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-2); }
    .gk-gf-optional { color: var(--gk-ink-3); }
    .gk-gf-input { box-sizing: border-box; min-height: 3.125rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 20%, transparent); border-radius: .75rem; background-color: var(--gk-stage); color: var(--gk-ink); box-shadow: none; }
    .gk-gf-input::placeholder { color: var(--gk-ink-3); opacity: 1; }
    .gk-gf-input:focus { border-color: var(--gift-accent); box-shadow: 0 0 0 1px var(--gift-accent); outline: none; }
    .gk-gf-amounts { display: grid; grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr)); gap: .625rem; }
    .gk-gf-amount { cursor: pointer; }
    .gk-gf-amount-face { display: flex; align-items: center; justify-content: center; min-height: 3.5rem; padding: 0 .75rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 22%, transparent); border-radius: .875rem; color: var(--gk-ink); font-size: 1.1875rem; font-weight: 700; font-variant-numeric: tabular-nums; transition: background-color var(--gk-swap); }
    .gk-gf-amount:hover .gk-gf-amount-face { background: var(--gk-well); }
    .gk-gf-amount input:checked + .gk-gf-amount-face { border-color: transparent; background: var(--gift-accent); color: var(--gift-contrast); }
    .gk-gf-amount input:focus-visible + .gk-gf-amount-face { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-gf-check { display: inline-flex; align-items: center; gap: .625rem; font-size: .9375rem; color: var(--gk-ink-2); cursor: pointer; }
    .gk-gf-box { width: 1.125rem; height: 1.125rem; border: 1px solid color-mix(in srgb, var(--gk-ink) 38%, transparent); border-radius: .3125rem; color: var(--es-accent-readable); }
    .gk-gf-box:not(:checked) { background-color: var(--gk-stage); }
    .gk-gf-box:focus { box-shadow: none; outline: none; }
    .gk-gf-box:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    .gk-gf-notes { display: grid; gap: .25rem; color: var(--gk-ink-2); }
    .gk-gf-notes p { margin: 0; }
    .gk-gf-buy { display: flex; width: 100%; max-width: none; margin-top: .5rem; cursor: pointer; }
</style>

<script {!! nonce_attr() !!}>
    (function () {
        var checkbox = document.getElementById('send_to_self');
        var fields = document.getElementById('recipient-fields');
        if (!checkbox || !fields) return;

        function sync() {
            var hidden = checkbox.checked;
            fields.style.display = hidden ? 'none' : '';
            ['recipient_name', 'recipient_email'].forEach(function (id) {
                var input = document.getElementById(id);
                if (input) input.required = !hidden;
            });
        }

        checkbox.addEventListener('change', sync);
        sync();
    })();
</script>

</x-app-guest-layout>
