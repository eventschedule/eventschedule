{{-- The two checks a partner boost needs before it launches, in either order, each in words on a
     row of its own: approval of the ad as it stands now, and payment of the budget as it stands
     now. They are not a step line, because neither waits for the other.

     For the partner ($bpIsPartner) each row carries its answers, the one that goes on last:
     Decline, then Approve; Payment received, or withdrawing a payment recorded before the
     ad launched. The buttons open the dialogs of boost/partials/partner-dialogs. For the Business
     the rows say where things stand, and never name a version.

     Expects $bpCampaign, $bpIsPartner and $bpPageName. --}}
@php
    $bpDecZone = $bpCampaign->role?->timezone ?: config('app.timezone');
    $bpDecName = $bpCampaign->event?->translatedName() ?? $bpCampaign->event_name;
    $bpDecBudget = \App\Utils\MoneyUtils::format($bpCampaign->user_budget, $bpCampaign->currency_code);
    $bpDecApproved = $bpCampaign->isApprovedCurrent();
    $bpDecPaid = $bpCampaign->isPaidCurrent();
    $bpDecPaidAmount = $bpCampaign->payment_amount !== null
        ? \App\Utils\MoneyUtils::format($bpCampaign->payment_amount, $bpCampaign->payment_currency ?: $bpCampaign->currency_code)
        : null;
    $bpDecCanDecline = $bpIsPartner && in_array($bpCampaign->status, ['pending_approval', 'awaiting_payment', 'ready'], true);
    $bpDecCanApprove = $bpIsPartner && $bpCampaign->status === 'pending_approval';
    $bpDecCanPay = $bpIsPartner && ! $bpDecPaid && in_array($bpCampaign->status, ['pending_approval', 'awaiting_payment'], true);
    $bpDecCanWithdraw = $bpIsPartner && $bpCampaign->payment_confirmed_at && in_array($bpCampaign->status, ['pending_approval', 'awaiting_payment', 'ready', 'declined'], true);
    $bpDecDate = fn ($at) => $at ? $at->copy()->tz($bpDecZone)->translatedFormat('M j, Y') : '';
@endphp
<div class="bp-checks">
    <div class="bp-check">
        <span class="bp-check-name">{{ __('messages.boost_partner_check_approval') }}</span>
        <span class="bp-check-state">
            @if ($bpDecApproved)
            <span class="event-status is-on">{{ __('messages.boost_partner_approved_mark') }}</span>
            @if ($bpIsPartner)
            {{ __('messages.boost_partner_approved_version_on', ['version' => $bpCampaign->version, 'date' => $bpDecDate($bpCampaign->approved_at)]) }}
            @else
            {{ __('messages.boost_partner_approved_by_on', ['page' => $bpPageName, 'date' => $bpDecDate($bpCampaign->approved_at)]) }}
            @endif
            @elseif ($bpCampaign->status === 'declined')
            <span class="event-status is-warn">{{ __('messages.boost_partner_status_declined') }}</span>
            @elseif ($bpCampaign->status === 'pending_approval')
            <span class="event-status is-warn">{{ __('messages.waiting') }}</span>
            @if ($bpIsPartner)
            {{ __('messages.boost_partner_version_waiting', ['version' => $bpCampaign->version]) }}
            @else
            {{ __('messages.boost_partner_waiting_for_page', ['page' => $bpPageName]) }}
            @endif
            @else
            <span class="event-status">{{ __('messages.boost_partner_not_approved') }}</span>
            @endif
        </span>
        @if ($bpDecCanDecline || $bpDecCanApprove)
        <span class="bp-check-actions">
            @if ($bpDecCanDecline)
            <button type="button" data-bp-open="decline" data-action="{{ route('boost.partner.decline', ['hash' => $bpCampaign->hashedId()]) }}" data-name="{{ $bpDecName }}"
                class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.decline') }}
            </button>
            @endif
            @if ($bpDecCanApprove)
            <x-brand-button data-bp-open="approve" :data-action="route('boost.partner.approve', ['hash' => $bpCampaign->hashedId()])" :data-name="$bpDecName"
                :data-version="$bpCampaign->version" :data-version-label="__('messages.boost_partner_version', ['version' => $bpCampaign->version])" :data-budget="$bpDecBudget" :data-currency="$bpCampaign->currency_code"
                :data-paid="$bpDecPaid ? '1' : '0'">
                {{ __('messages.boost_partner_approve_version', ['version' => $bpCampaign->version]) }}
            </x-brand-button>
            @endif
        </span>
        @endif
    </div>

    <div class="bp-check">
        <span class="bp-check-name">{{ __('messages.payment') }}</span>
        <span class="bp-check-state">
            @if ($bpDecPaid)
            <span class="event-status is-on">{{ __('messages.boost_partner_paid_mark') }}</span>
            {{ __('messages.boost_partner_paid_line', ['amount' => $bpDecPaidAmount, 'date' => $bpDecDate($bpCampaign->payment_confirmed_at)]) }}
            @if (filled($bpCampaign->payment_reference))
            <span class="block text-xs">{{ __('messages.boost_partner_payment_reference') }}: <span dir="ltr">{{ $bpCampaign->payment_reference }}</span></span>
            @endif
            @elseif ($bpCampaign->payment_confirmed_at)
            <span class="event-status is-warn">{{ __('messages.boost_partner_payment_again') }}</span>
            {{ __('messages.boost_partner_payment_old_budget', ['amount' => $bpDecPaidAmount, 'budget' => $bpDecBudget]) }}
            @else
            <span class="event-status">{{ __('messages.boost_partner_unpaid_mark') }}</span>
            @if ($bpIsPartner)
            {{ __('messages.boost_partner_payment_not_recorded', ['budget' => $bpDecBudget]) }}
            @else
            {{ __('messages.boost_partner_payment_not_received', ['page' => $bpPageName, 'budget' => $bpDecBudget]) }}
            @endif
            @endif
        </span>
        @if ($bpDecCanPay || $bpDecCanWithdraw)
        <span class="bp-check-actions">
            @if ($bpDecCanWithdraw)
            <form method="POST" action="{{ route('boost.partner.payment_withdraw', ['hash' => $bpCampaign->hashedId()]) }}" data-confirm="{{ __('messages.boost_partner_withdraw_payment_confirm') }}">
                @csrf
                <button type="submit" class="event-link">{{ __('messages.boost_partner_withdraw_payment') }}</button>
            </form>
            @endif
            @if ($bpDecCanPay)
            <x-brand-button data-bp-open="payment" :data-action="route('boost.partner.payment', ['hash' => $bpCampaign->hashedId()])" :data-name="$bpDecName"
                :data-budget="$bpDecBudget" :data-currency="$bpCampaign->currency_code">
                {{ __('messages.boost_partner_payment_received') }}
            </x-brand-button>
            @endif
        </span>
        @endif
    </div>
</div>
