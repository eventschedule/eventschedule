<x-app-guest-layout :role="$role">

@php
    $accentColor = $role->accent_color ?? '#4E81FA';
    $contrastColor = accent_contrast_color($accentColor);
    $currency = strtoupper($role->gift_card_currency_code ?: 'USD');
    $turnstileActive = \App\Utils\TurnstileUtils::isActiveForRequest();
@endphp

@if ($turnstileActive)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer {!! nonce_attr() !!}></script>
@endif

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
    <div class="max-w-lg mx-auto">

        {{-- Schedule branding --}}
        <div class="text-center mb-6">
            @if ($role->profile_image_url)
            <a href="{{ $role->getGuestUrl() }}">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" class="w-16 h-16 rounded-xl mx-auto object-cover shadow-sm">
            </a>
            @endif
            <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-gray-100">🎁 {{ __('messages.gift_cards') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.gift_card_purchase_subtitle', ['schedule' => $role->name]) }}</p>
        </div>

        @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3 text-sm text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <form method="post" action="{{ route('gift_card.purchase.store', ['subdomain' => $role->subdomain]) }}">
                @csrf

                {{-- Honeypot --}}
                <div style="display:none" aria-hidden="true">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
                </div>

                {{-- Amount --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.amount') }}</label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($role->gift_card_amounts as $index => $amount)
                        <label class="cursor-pointer">
                            <input type="radio" name="amount" value="{{ $amount }}" class="peer sr-only"
                                {{ (string) old('amount', $role->gift_card_amounts[0] ?? '') === (string) $amount ? 'checked' : '' }} required>
                            <div class="text-center rounded-lg border-2 border-gray-200 dark:border-gray-600 px-2 py-3 text-lg font-semibold text-gray-900 dark:text-gray-100 transition-all duration-200 peer-checked:border-[var(--gift-accent)] peer-checked:bg-[var(--gift-accent)] peer-checked:text-[var(--gift-contrast)]">
                                {{ \App\Utils\MoneyUtils::format($amount, $currency) }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                {{-- Buyer --}}
                <div class="mb-4">
                    <label for="purchaser_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.name') }}</label>
                    <input type="text" id="purchaser_name" name="purchaser_name" value="{{ old('purchaser_name') }}" required maxlength="255"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--gift-accent)] focus:ring-[var(--gift-accent)]">
                    <x-input-error :messages="$errors->get('purchaser_name')" class="mt-2" />
                </div>
                <div class="mb-6">
                    <label for="purchaser_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.email') }}</label>
                    <input type="email" id="purchaser_email" name="purchaser_email" value="{{ old('purchaser_email') }}" required maxlength="255"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--gift-accent)] focus:ring-[var(--gift-accent)]">
                    <x-input-error :messages="$errors->get('purchaser_email')" class="mt-2" />
                </div>

                {{-- Send to myself --}}
                <div class="mb-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="send_to_self" name="send_to_self" value="1" {{ old('send_to_self') ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600" style="accent-color: {{ $accentColor }}">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.gift_card_send_to_self') }}</span>
                    </label>
                </div>

                {{-- Recipient --}}
                <div id="recipient-fields">
                    <div class="mb-4">
                        <label for="recipient_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.recipient_name') }}</label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" required maxlength="255"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--gift-accent)] focus:ring-[var(--gift-accent)]">
                        <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
                    </div>
                    <div class="mb-4">
                        <label for="recipient_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.recipient_email') }}</label>
                        <input type="email" id="recipient_email" name="recipient_email" value="{{ old('recipient_email') }}" required maxlength="255"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--gift-accent)] focus:ring-[var(--gift-accent)]">
                        <x-input-error :messages="$errors->get('recipient_email')" class="mt-2" />
                    </div>
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.gift_card_personal_message') }} <span class="text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></label>
                        <textarea id="message" name="message" rows="3" maxlength="500" dir="auto"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--gift-accent)] focus:ring-[var(--gift-accent)]"
                            placeholder="{{ __('messages.gift_card_message_placeholder') }}">{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" class="mt-2" />
                    </div>
                </div>

                {{-- Expectations --}}
                <div class="mb-6 rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-sm text-gray-600 dark:text-gray-400 space-y-1">
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

                @if ($turnstileActive)
                <div class="mb-6">
                    <div class="cf-turnstile" data-sitekey="{{ \App\Utils\TurnstileUtils::getSiteKey() }}"></div>
                    <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
                </div>
                @endif

                <button type="submit"
                    style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}"
                    class="w-full inline-flex items-center justify-center rounded-lg px-4 py-3 text-base font-semibold border-2 shadow-sm transition-all duration-200 hover:scale-[1.02] hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[var(--gift-accent)]">
                    {{ __('messages.buy_gift_card') }}
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-sm">
            <a href="{{ $role->getGuestUrl() }}" class="hover:underline" style="color: {{ $accentColor }}">&larr; {{ __('messages.gift_card_back_to_schedule', ['schedule' => $role->name]) }}</a>
        </p>
    </div>
</div>

<style {!! nonce_attr() !!}>
    :root {
        --gift-accent: {{ $accentColor }};
        --gift-contrast: {{ $contrastColor }};
    }
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
