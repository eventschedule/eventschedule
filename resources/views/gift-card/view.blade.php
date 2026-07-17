<x-app-guest-layout :role="$role">

@php
    $accentColor = $role->accent_color ?? '#4E81FA';
    $contrastColor = accent_contrast_color($accentColor);
    $displayStatus = $giftCard->displayStatus();
    $isPending = $giftCard->status === 'unpaid';
@endphp

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
    <div class="max-w-lg mx-auto">

        {{-- Schedule branding --}}
        <div class="text-center mb-6">
            @if ($role->profile_image_url)
            <a href="{{ $role->getGuestUrl() }}">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" class="w-16 h-16 rounded-xl mx-auto object-cover shadow-sm">
            </a>
            @endif
            <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-gray-100">🎁 {{ __('messages.gift_card') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $role->name }}</p>
        </div>

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
        <div class="mb-4 rounded-lg border p-3 text-sm {{ $isWarning
            ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700 text-amber-800 dark:text-amber-400'
            : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700 text-red-700 dark:text-red-400' }}">
            <p class="font-semibold uppercase tracking-wide text-xs mb-1">{{ __('messages.gift_card_status_'.$displayStatus) }}</p>
            <p>{{ $statusCopy }}</p>
            @if ($isPending && $giftCard->payment_method === 'cash' && $role->user)
            <p class="mt-1">{{ __('messages.event_support_contact') }}: <a href="mailto:{{ $role->user->email }}" class="underline">{{ $role->user->email }}</a></p>
            @endif
        </div>
        @endif

        {{-- Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center">
            <div class="text-4xl font-bold" style="color: {{ $accentColor }}">
                {{ \App\Utils\MoneyUtils::format($giftCard->remaining_amount, $giftCard->currency_code) }}
            </div>
            @if ((float) $giftCard->remaining_amount !== (float) $giftCard->amount)
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.gift_card_original_value', ['amount' => \App\Utils\MoneyUtils::format($giftCard->amount, $giftCard->currency_code)]) }}
            </div>
            @endif

            <div class="mt-6 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('messages.gift_card_code') }}</div>
            <div dir="ltr" class="mt-1 font-mono text-2xl font-bold tracking-widest text-gray-900 dark:text-gray-100 select-all" id="gift-card-code" data-code="{{ $giftCard->code }}">
                {{ $giftCard->formattedCode() }}
            </div>
            <button type="button" id="copy-code-button"
                class="mt-2 inline-flex items-center gap-1 rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                </svg>
                <span id="copy-code-label">{{ __('messages.copy') }}</span>
            </button>

            @if ($giftCard->expires_at)
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.gift_card_valid_until', ['date' => $giftCard->expires_at->format('M j, Y')]) }}
            </div>
            @endif
        </div>

        {{-- Personal message --}}
        @if ($giftCard->message)
        <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 italic text-gray-700 dark:text-gray-300">
            "{{ $giftCard->message }}"
            <div class="mt-2 not-italic text-sm text-gray-500 dark:text-gray-400">- {{ $giftCard->purchaser_name }}</div>
        </div>
        @endif

        {{-- How to redeem --}}
        <div class="mt-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.gift_card_how_to_redeem') }}</h2>
            <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                <li>{{ __('messages.gift_card_redeem_step_1', ['schedule' => $role->name]) }}</li>
                <li>{{ __('messages.gift_card_redeem_step_2') }}</li>
                <li>{{ __('messages.gift_card_redeem_step_3') }}</li>
            </ol>
        </div>

        <div class="mt-6 text-center space-y-2">
            <a href="{{ $role->getGuestUrl() }}"
                style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                class="inline-flex items-center justify-center rounded-lg px-4 py-3 text-base font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg">
                {{ __('messages.gift_card_browse_events', ['schedule' => $role->name]) }}
            </a>
            @if ($role->canSellGiftCards())
            <p class="text-sm">
                <a href="{{ route('gift_card.purchase', ['subdomain' => $role->subdomain]) }}" class="hover:underline" style="color: {{ $accentColor }}">
                    {{ __('messages.gift_card_buy_another') }}
                </a>
            </p>
            @endif
        </div>
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
