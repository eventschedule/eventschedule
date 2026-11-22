{{-- noindex: the URL carries the gift card's secret. --}}
<x-app-guest-layout :role="$role" :page-title="__('messages.view_gift_card')" :no-index="true">

@php
    $displayStatus = $giftCard->displayStatus();
    $isPending = $giftCard->status === 'unpaid';
@endphp

{{-- The page on the stage: a narrow sheet, the card itself raised on it as the one object, then
     its message and how to use it as flat sections divided by hairlines. --}}
<style {!! nonce_attr() !!}>
    .gk-p-giftview { max-width: 40rem; margin-top: .875rem; }
    @media (min-width: 40rem) { .gk-p-giftview { padding-inline: clamp(1.5rem, 4vw, 2.75rem); } }
    .gk-gv-head { display: grid; gap: .625rem; margin-bottom: 2rem; }
    .gk-gv-logo { display: block; width: 3.5rem; height: 3.5rem; margin-bottom: .375rem; }
    .gk-gv-logo img { display: block; width: 100%; height: 100%; border-radius: .875rem; object-fit: cover; }
    .gk-p-giftview .gk-title { font-size: clamp(2rem, 1.5rem + 2vw, 3rem); }
    .gk-gv-status { margin-bottom: 1.25rem; }
    .gk-gv-status-name { margin: 0 0 .25rem; font-size: .8125rem; font-weight: 700; }
    .gk-gv-status p { margin: 0; }
    .gk-gv-status a { color: inherit; text-decoration: underline; }
    .gk-gv-card { text-align: center; }
    .gk-gv-amount { font-size: clamp(2.5rem, 2rem + 2vw, 3.25rem); font-weight: 800; line-height: 1.05; letter-spacing: -.02em; color: var(--es-accent-readable); font-variant-numeric: tabular-nums; }
    .gk-gv-sub { margin-top: .375rem; font-size: .9375rem; color: var(--gk-ink-3); }
    .gk-gv-code-name { margin-top: 1.75rem; font-size: .8125rem; font-weight: 500; color: var(--gk-ink-3); }
    .gk-gv-code { margin-top: .375rem; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 1.625rem; font-weight: 700; letter-spacing: .12em; color: var(--gk-ink); overflow-wrap: anywhere; user-select: all; }
    .gk-gv-copy { margin-top: .875rem; min-height: 2.75rem; cursor: pointer; }
    .gk-gv-copy svg { width: 1.125rem; height: 1.125rem; }
    .gk-gv-sec { margin-top: 2.25rem; }
    .gk-gv-quote { margin: 0; font-size: 1.1875rem; font-style: italic; line-height: 1.55; color: var(--gk-ink); overflow-wrap: anywhere; }
    .gk-gv-from { margin-top: .5rem; font-size: .9375rem; color: var(--gk-ink-3); }
    .gk-gv-steps { display: grid; gap: .5rem; margin: 0; padding-inline-start: 1.25rem; list-style: decimal; font-size: 1rem; line-height: 1.55; color: var(--gk-ink-2); }
    .gk-gv-actions .gk-cta-main { max-width: none; }
    .gk-gv-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-top: 2.25rem; padding-top: 2rem; border-top: 1px solid var(--gk-line); }
</style>

<div class="gk-stage gk-p-giftview" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">

        {{-- Schedule branding --}}
        <header class="gk-gv-head">
            @if ($role->profile_image_url)
            <a href="{{ $role->getGuestUrl() }}" class="gk-gv-logo">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}">
            </a>
            @endif
            <p class="gk-dateline">{{ $role->name }}</p>
            <h1 class="gk-title">🎁 {{ __('messages.gift_card') }}</h1>
        </header>

        {{-- Status banner --}}
        @if ($displayStatus !== 'active')
        @php
            $statusCopy = match ($displayStatus) {
                'unpaid' => $giftCard->payment_method === 'cash'
                    ? __('messages.gift_card_pending_cash')
                    : __('messages.gift_card_pending_payment'),
                'cancelled' => __('messages.gift_card_status_cancelled'),
                'refunded' => __('messages.gift_card_status_refunded'),
                'amount_mismatch' => __('messages.gift_card_pending_payment'),
                'expired' => __('messages.gift_card_expired', ['date' => $giftCard->expires_at?->format('M j, Y')]),
                'depleted' => __('messages.gift_card_depleted'),
                default => '',
            };
            $isWarning = in_array($displayStatus, ['unpaid', 'amount_mismatch']);
        @endphp
        <div class="gk-note gk-gv-status {{ $isWarning ? 'gk-note-warn' : 'gk-note-bad' }}">
            <div class="gk-note-body">
            <p class="gk-gv-status-name">{{ __('messages.gift_card_status_'.$displayStatus) }}</p>
            <p>{{ $statusCopy }}</p>
            @if ($isPending && $giftCard->payment_method === 'cash' && $role->user)
            <p class="mt-1">{{ __('messages.event_support_contact') }}: <a href="mailto:{{ $role->user->email }}">{{ $role->user->email }}</a></p>
            @endif
            </div>
        </div>
        @endif

        {{-- Card --}}
        <div class="gk-card gk-band gk-gv-card">
            <div class="gk-gv-amount">
                {{ \App\Utils\MoneyUtils::format($giftCard->remaining_amount, $giftCard->currency_code) }}
            </div>
            @if ((float) $giftCard->remaining_amount !== (float) $giftCard->amount)
            <div class="gk-gv-sub">
                {{ __('messages.gift_card_original_value', ['amount' => \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code)]) }}
            </div>
            @endif

            <div class="gk-gv-code-name">{{ __('messages.gift_card_code') }}</div>
            <div dir="ltr" class="gk-gv-code" id="gift-card-code" data-code="{{ $giftCard->code }}">
                {{ $giftCard->formattedCode() }}
            </div>
            <button type="button" id="copy-code-button" class="gk-btn gk-btn-secondary gk-gv-copy">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                <span id="copy-code-label">{{ __('messages.copy') }}</span>
            </button>

            @if ($giftCard->expires_at)
            <div class="gk-gv-sub mt-4">
                {{ __('messages.gift_card_valid_until', ['date' => $giftCard->expires_at->format('M j, Y')]) }}
            </div>
            @endif
        </div>

        {{-- Personal message --}}
        @if ($giftCard->message)
        <figure class="gk-sec gk-gv-sec m-0">
            <blockquote class="gk-gv-quote" dir="auto">"{{ $giftCard->message }}"</blockquote>
            <figcaption class="gk-gv-from">- {{ $giftCard->purchaser_name }}</figcaption>
        </figure>
        @endif

        {{-- How to redeem --}}
        <section class="gk-sec gk-sec-rule gk-gv-sec">
            <h2 class="gk-label">{{ __('messages.gift_card_how_to_redeem') }}</h2>
            <ol class="gk-gv-steps">
                <li>{{ __('messages.gift_card_redeem_step_1', ['schedule' => $role->name]) }}</li>
                <li>{{ __('messages.gift_card_redeem_step_2') }}</li>
                <li>{{ __('messages.gift_card_redeem_step_3') }}</li>
            </ol>
        </section>

        {{-- The way on: buying another is the quiet one, the schedule's events the large one, last. --}}
        <div class="gk-gv-actions">
            @if ($role->canSellGiftCards())
            <a href="{{ route('gift_card.purchase', ['subdomain' => $role->subdomain]) }}" class="gk-cta-alt">
                {{ __('messages.gift_card_buy_another') }}
            </a>
            @endif
            <a href="{{ $role->getGuestUrl() }}" class="gk-btn gk-btn-primary gk-cta-main">
                {{ __('messages.gift_card_browse_events', ['schedule' => $role->name]) }}
            </a>
        </div>
</div>

<script {!! nonce_attr() !!}>
    (function () {
        var button = document.getElementById('copy-code-button');
        var codeEl = document.getElementById('gift-card-code');
        var label = document.getElementById('copy-code-label');
        if (!button || !codeEl) return;

        button.addEventListener('click', function () {
            var code = codeEl.dataset.code || '';
            var done = function () {
                if (!label) return;
                var original = label.textContent;
                label.textContent = @json(__('messages.copied'));
                setTimeout(function () { label.textContent = original; }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(done).catch(function () {});
            } else {
                var range = document.createRange();
                range.selectNodeContents(codeEl);
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                try { document.execCommand('copy'); done(); } catch (e) {}
                selection.removeAllRanges();
            }
        });
    })();
</script>

</x-app-guest-layout>
