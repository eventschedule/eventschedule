@php
    $giftCards = collect($giftCards ?? []);
    $outstanding = $giftCards->where('status', 'active')->groupBy('currency_code')
        ->map(fn ($cards, $currency) => \App\Utils\MoneyUtils::format($cards->sum(fn ($c) => (float) $c['remaining_amount']), $currency))
        ->values();
    $giftCardStatusStyles = [
        'active' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'unpaid' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'amount_mismatch' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'depleted' => 'bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300',
        'expired' => 'bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300',
        'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'refunded' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    ];
@endphp

@if ($giftCards->isEmpty())
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.no_gift_cards_yet') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_gift_cards_yet_help') }}</p>
    </div>
@else
<div class="ap-card rounded-xl overflow-hidden">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-1 p-4 border-b border-gray-200 dark:border-[#2d2d30] text-sm">
        <span class="text-gray-700 dark:text-gray-300"><span class="font-semibold">{{ $giftCards->count() }}</span> {{ __('messages.gift_cards') }}</span>
        @if ($outstanding->isNotEmpty())
        <span class="text-gray-700 dark:text-gray-300"><span class="font-semibold">{{ $outstanding->implode(' + ') }}</span> {{ __('messages.gift_card_outstanding_balance') }}</span>
        @endif
    </div>

    <div class="divide-y divide-gray-200 dark:divide-[#2d2d30]">
        @foreach ($giftCards as $card)
            <details class="group">
                <summary class="flex items-center gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#252526] transition-colors list-none">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <div class="flex-1 min-w-0">
                        <div class="font-mono font-medium text-gray-900 dark:text-gray-100 truncate" dir="ltr">{{ $card['code'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $card['recipient_name'] }} &middot; {{ $card['recipient_email'] }}</div>
                    </div>
                    <div class="hidden md:block text-sm text-gray-500 dark:text-gray-400 w-28 text-end">{{ $card['created_at'] }}</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200 w-32 text-end">
                        {{ \App\Utils\MoneyUtils::format($card['remaining_amount'], $card['currency_code']) }}
                        <span class="text-gray-400 dark:text-gray-500">/ {{ \App\Utils\MoneyUtils::format($card['amount'], $card['currency_code']) }}</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $giftCardStatusStyles[$card['status']] ?? '' }}">{{ __('messages.gift_card_status_'.$card['status']) }}</span>
                </summary>

                <div class="px-4 pb-4 ps-12">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-700 dark:text-gray-300 mb-3">
                        <div><span class="text-gray-500 dark:text-gray-400">{{ __('messages.purchaser') }}:</span> {{ $card['purchaser_name'] }} (<a href="mailto:{{ $card['purchaser_email'] }}" class="hover:underline">{{ $card['purchaser_email'] }}</a>)</div>
                        <div><span class="text-gray-500 dark:text-gray-400">{{ __('messages.payment') }}:</span> {{ __('messages.'.$card['payment_method']) }}</div>
                        @if ($card['expires_at'])
                        <div><span class="text-gray-500 dark:text-gray-400">{{ __('messages.expires') }}:</span> {{ $card['expires_at'] }}</div>
                        @endif
                        <div><span class="text-gray-500 dark:text-gray-400">{{ __('messages.schedule') }}:</span> {{ $card['schedule'] }}</div>
                    </div>

                    @if ($card['message'])
                    <p class="text-sm italic text-gray-600 dark:text-gray-400 mb-3">"{{ $card['message'] }}"</p>
                    @endif

                    @if (count($card['redemptions']) > 0)
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    <th class="text-start font-medium pb-1">{{ __('messages.event') }}</th>
                                    <th class="text-start font-medium pb-1">{{ __('messages.date') }}</th>
                                    <th class="text-end font-medium pb-1">{{ __('messages.amount') }}</th>
                                    <th class="text-end font-medium pb-1">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 dark:text-gray-300">
                                @foreach ($card['redemptions'] as $redemption)
                                    <tr class="border-t border-gray-100 dark:border-[#2d2d30]">
                                        <td class="py-1.5 pe-4">{{ $redemption['event'] }}</td>
                                        <td class="py-1.5 pe-4 whitespace-nowrap">{{ $redemption['date'] }}</td>
                                        <td class="py-1.5 pe-4 whitespace-nowrap text-end">-{{ \App\Utils\MoneyUtils::format($redemption['amount'], $card['currency_code']) }}</td>
                                        <td class="py-1.5 whitespace-nowrap text-end text-gray-500 dark:text-gray-400">{{ __('messages.'.$redemption['status']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_redemptions_yet') }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                        <a href="{{ $card['view_url'] }}" target="_blank" class="text-[var(--brand-blue)] hover:underline">{{ __('messages.view_gift_card') }}</a>
                        @if ($card['can_mark_paid'])
                        <button type="button" class="text-[var(--brand-blue)] hover:underline"
                            data-gift-card-action="mark_paid" data-gift-card-id="{{ $card['id'] }}"
                            data-confirm-message="{{ __('messages.gift_card_mark_paid_confirm') }}">
                            {{ __('messages.mark_paid') }}
                        </button>
                        @endif
                        @if ($card['can_resend'])
                        <button type="button" class="text-[var(--brand-blue)] hover:underline"
                            data-gift-card-resend data-gift-card-id="{{ $card['id'] }}">
                            {{ __('messages.resend_email') }}
                        </button>
                        @endif
                        @if ($card['can_refund'])
                        <button type="button" class="text-red-600 dark:text-red-400 hover:underline"
                            data-gift-card-action="refund" data-gift-card-id="{{ $card['id'] }}"
                            data-confirm-message="{{ __('messages.gift_card_refund_confirm') }}">
                            {{ __('messages.refund') }}
                        </button>
                        @endif
                        @if ($card['can_cancel'])
                        <button type="button" class="text-red-600 dark:text-red-400 hover:underline"
                            data-gift-card-action="cancel" data-gift-card-id="{{ $card['id'] }}"
                            data-confirm-message="{{ __('messages.gift_card_cancel_confirm') }}">
                            {{ __('messages.cancel') }}
                        </button>
                        @endif
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</div>
@endif
