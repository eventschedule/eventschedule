{{-- The Gift cards tab of ticket/sales: every card sold on a schedule this person runs, what is
     left on it, and where it was spent. One .page-table whose rows open (ticket/sales owns the
     script and the actions' data attributes), where the old tab was a stack of disclosure rows. --}}
@php
    $giftCards = collect($giftCards ?? []);
    // Per currency, never one sum: this tab runs across schedules.
    $outstanding = $giftCards->where('status', 'active')->groupBy('currency_code')
        ->map(fn ($cards, $currency) => \App\Utils\MoneyUtils::format($cards->sum(fn ($c) => (float) $c['remaining_amount']), $currency))
        ->values();
    $giftCardTones = [
        'active' => 'is-on',
        'unpaid' => 'is-warn',
        'amount_mismatch' => 'is-warn',
        'cancelled' => 'is-bad',
        'refunded' => 'is-bad',
    ];
@endphp

@if ($giftCards->isEmpty())
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.no_gift_cards_yet')" :text="__('messages.no_gift_cards_yet_help')"
        icon="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
</div>
@else
<div class="page-stack">
    <div class="ap-card rounded-xl page-stats is-auto">
        <div class="page-stat">
            <div class="page-stat-value">{{ $giftCards->count() }}</div>
            <div class="page-stat-label">{{ __('messages.gift_cards') }}</div>
        </div>
        @if ($outstanding->isNotEmpty())
        <div class="page-stat">
            <div class="page-stat-value">{{ $outstanding->implode(' + ') }}</div>
            <div class="page-stat-label">{{ __('messages.gift_card_outstanding_balance') }}</div>
        </div>
        @endif
    </div>

    <div class="ap-card rounded-xl overflow-hidden">
        <div class="page-scroll">
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.gift_card') }}</th>
                        <th scope="col">{{ __('messages.date') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.balance') }}</th>
                        <th scope="col">{{ __('messages.status') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($giftCards as $card)
                    <tr>
                        <td class="c-main">
                            <div class="sales-customer">
                                <button type="button" class="sales-open" data-toggle-row="gift-{{ $loop->index }}" aria-expanded="false">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    <span class="sr-only">{{ __('messages.details') }}</span>
                                </button>
                                <div class="page-person-text">
                                    <span class="c-strong c-mono" dir="ltr">{{ $card['code'] }}</span>
                                    <span class="c-sub"><bdi>{{ $card['recipient_name'] }}</bdi> &middot; <span dir="ltr">{{ $card['recipient_email'] }}</span></span>
                                </div>
                            </div>
                        </td>
                        <td class="c-date">{{ $card['created_at'] }}</td>
                        <td class="c-num" data-label="{{ __('messages.balance') }}">
                            <span class="c-strong">{{ \App\Utils\MoneyUtils::format($card['remaining_amount'], $card['currency_code']) }}</span>
                            <span class="c-quiet">/ {{ \App\Utils\MoneyUtils::format($card['amount'], $card['currency_code']) }}</span>
                        </td>
                        <td><span class="event-status {{ $giftCardTones[$card['status']] ?? '' }}">{{ __('messages.gift_card_status_'.$card['status']) }}</span></td>
                        <td class="c-actions">
                            <a href="{{ $card['view_url'] }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view_gift_card') }}</a>
                        </td>
                    </tr>
                    <tr class="detail-row-gift-{{ $loop->index }} sales-detail hidden">
                        <td colspan="5" class="c-main">
                            <div class="sales-detail-body">
                                @if ($card['message'])
                                <p class="sales-note"><bdi>&ldquo;{{ $card['message'] }}&rdquo;</bdi></p>
                                @endif

                                <dl class="sales-fields">
                                    <div>
                                        <dt>{{ __('messages.purchaser') }}:</dt>
                                        <dd><bdi>{{ $card['purchaser_name'] }}</bdi> (<a href="mailto:{{ $card['purchaser_email'] }}" class="event-link" dir="ltr">{{ $card['purchaser_email'] }}</a>)</dd>
                                    </div>
                                    <div>
                                        <dt>{{ __('messages.payment') }}:</dt>
                                        <dd>{{ __('messages.'.$card['payment_method']) }}</dd>
                                    </div>
                                    @if ($card['expires_at'])
                                    <div>
                                        <dt>{{ __('messages.expires') }}:</dt>
                                        <dd>{{ $card['expires_at'] }}</dd>
                                    </div>
                                    @endif
                                    <div>
                                        <dt>{{ __('messages.schedule') }}:</dt>
                                        <dd><bdi>{{ $card['schedule'] }}</bdi></dd>
                                    </div>
                                </dl>

                                @if (count($card['redemptions']) > 0)
                                <ul class="sales-lines">
                                    @foreach ($card['redemptions'] as $redemption)
                                    <li>
                                        <span><bdi>{{ $redemption['event'] }}</bdi></span>
                                        <span>{{ $redemption['date'] }}</span>
                                        <span>-{{ \App\Utils\MoneyUtils::format($redemption['amount'], $card['currency_code']) }}</span>
                                        <x-sale-status :status="$redemption['status']" />
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="c-quiet mt-2">{{ __('messages.no_redemptions_yet') }}</p>
                                @endif

                                @if ($card['can_mark_paid'] || $card['can_resend'] || $card['can_refund'] || $card['can_cancel'])
                                <div class="sales-detail-actions">
                                    @if ($card['can_mark_paid'])
                                    <button type="button" class="event-link"
                                        data-gift-card-action="mark_paid" data-gift-card-id="{{ $card['id'] }}"
                                        data-confirm-message="{{ __('messages.gift_card_mark_paid_confirm') }}">
                                        {{ __('messages.mark_paid') }}
                                    </button>
                                    @endif
                                    @if ($card['can_resend'])
                                    <button type="button" class="event-link"
                                        data-gift-card-resend data-gift-card-id="{{ $card['id'] }}">
                                        {{ __('messages.resend_email') }}
                                    </button>
                                    @endif
                                    @if ($card['can_refund'])
                                    <button type="button" class="event-link is-danger"
                                        data-gift-card-action="refund" data-gift-card-id="{{ $card['id'] }}"
                                        data-confirm-message="{{ __('messages.gift_card_refund_confirm') }}">
                                        {{ __('messages.refund') }}
                                    </button>
                                    @endif
                                    @if ($card['can_cancel'])
                                    <button type="button" class="event-link is-danger"
                                        data-gift-card-action="cancel" data-gift-card-id="{{ $card['id'] }}"
                                        data-confirm-message="{{ __('messages.gift_card_cancel_confirm') }}">
                                        {{ __('messages.cancel') }}
                                    </button>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
