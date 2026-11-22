{{--
  The buyer's payment plan.

  Rendered on the ticket page as well as the standalone pay page, so a buyer who is up to date can
  see their schedule, pay early or swap cards WITHOUT waiting for something to go wrong. Routing
  all self-service through the failed-charge email would leave the healthy buyer - who has the
  ticket page bookmarked from their confirmation email - with nothing.

  Expects: $plan (SaleInstallmentPlan), $variant ('dark' on the ticket, which is a dark object
  whatever the mode; 'stage' on the pay page, which is a guest sheet (partials/guest-stage-styles,
  whose colours its classes take from partials/guest-ticket-styles); 'light' elsewhere) and $showCard (the pay page only: whether the card
  on file is named; it defaults to the light variant). Both hosts pass a plan that the URL secret
  has already authenticated.
--}}
@php
    $variant = $variant ?? 'dark';
    $dark = $variant === 'dark';
    $stage = $variant === 'stage';

    $next = $plan->nextDueInstallment();
    $paidCount = $plan->paidCount();
    $remaining = $plan->amountRemaining();
    $currency = $plan->currency;

    $label = $stage ? 'gk-plan-lab' : ($dark ? 'text-white/60' : 'text-gray-500 dark:text-gray-400');
    $value = $stage ? 'gk-plan-val' : ($dark ? 'text-white' : 'text-gray-900 dark:text-gray-100');
    $muted = $stage ? 'gk-plan-mut' : ($dark ? 'text-white/40' : 'text-gray-400 dark:text-gray-500');
    $rule = $stage ? 'gk-plan-rule' : ($dark ? 'border-white/10' : 'border-gray-200 dark:border-gray-700');
    // Type: the sheet reads at a page's size, the ticket's panel at a ticket's.
    $text = $stage ? 'gk-plan-text' : 'text-[13px]';
    $small = $stage ? 'gk-plan-small' : 'text-[12px]';
    $paidMark = $stage ? 'gk-plan-ok' : 'text-green-400';
    $quietButton = $stage ? 'gk-btn gk-btn-secondary gk-btn-lg'
        : 'inline-flex items-center px-[14px] py-[8px] rounded-lg border '.($dark ? 'border-white/20 text-white/80 hover:bg-white/10' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800').' text-[13px] font-semibold transition-colors';
@endphp

<div class="{{ $stage ? 'gk-plan' : ($dark ? 'glass rounded-2xl p-[20px] sm:p-[24px]' : 'ap-card rounded-xl p-6') }}">
    <div class="flex items-baseline justify-between gap-3 {{ $stage ? 'gk-plan-head' : 'mb-[12px]' }}">
        <h2 class="{{ $stage ? 'gk-plan-title' : 'text-[15px] font-bold' }} {{ $value }}">{{ __('messages.payment_plan') }}</h2>
        <span class="{{ $small }} {{ $label }}">
            {{ __('messages.installments_progress', ['paid' => $paidCount, 'count' => $plan->installment_count]) }}
        </span>
    </div>

    <div class="flex items-center justify-between {{ $text }} {{ $stage ? 'gk-plan-line' : 'mb-[14px]' }}">
        <span class="{{ $label }}">{{ __('messages.installments_collected') }}</span>
        <span class="{{ $value }}">{{ \App\Utils\MoneyUtils::format($plan->amount_paid, $currency) }}</span>
    </div>
    <div class="flex items-center justify-between {{ $text }} {{ $stage ? 'gk-plan-line' : 'mb-[14px]' }}">
        <span class="{{ $label }}">{{ __('messages.installments_outstanding') }}</span>
        <span class="{{ $value }} font-semibold">{{ \App\Utils\MoneyUtils::format($remaining, $currency) }}</span>
    </div>

    <div class="border-t {{ $rule }} {{ $stage ? 'gk-plan-rows' : 'pt-[12px]' }}">
        @foreach ($plan->installments as $row)
            <div class="flex items-center justify-between {{ $text }} {{ $stage ? 'gk-plan-row' : 'py-[4px]' }}">
                <span class="{{ $row->status === 'paid' ? $muted : $label }}">
                    {{ $row->due_at?->translatedFormat('j M Y') }}
                </span>
                <span class="flex items-center gap-[10px]">
                    <span class="{{ $row->status === 'paid' ? $muted : $value }}">
                        {{ \App\Utils\MoneyUtils::format($row->amount, $currency) }}
                    </span>
                    <span class="{{ $stage ? 'gk-plan-state' : 'text-[11px] w-[70px] text-right' }} {{ $row->status === 'paid' ? $paidMark : $muted }}">
                        @if ($row->status === 'paid')
                            {{ __('messages.paid') }}
                        @elseif ($row->status === 'cancelled')
                            {{ __('messages.cancelled') }}
                        @elseif ($row->status === 'awaiting_customer')
                            {{ __('messages.installment_confirm_payment') }}
                        @elseif ($row->status === 'processing' || $row->status === 'awaiting_reconciliation')
                            {{ __('messages.installment_status_processing') }}
                        @elseif ($row->status === 'failed')
                            {{ __('messages.installment_status_overdue') }}
                        @else
                            {{ __('messages.scheduled') }}
                        @endif
                    </span>
                </span>
            </div>
        @endforeach
    </div>

    @if ($next && $plan->status !== 'cancelled')
        <p class="{{ $small }} {{ $label }} mt-[14px]">
            {{ __('messages.installment_next', [
                'amount' => \App\Utils\MoneyUtils::format($next->amount, $currency),
                'date' => $next->due_at?->translatedFormat('j M Y'),
            ]) }}
        </p>

        {{-- Card details only on the plan page, never on the ticket page.

             The ticket QR encodes a ticket.view URL, so anyone who scans the ticket - door staff
             with a phone camera, or whoever the buyer forwarded it to - lands on that page. The
             schedule and the payment buttons are the buyer's own affordance and lead to an
             authenticated Stripe session, but the card brand and last four are payment details
             that nobody at the door needs to see. The plan page is reached with the PLAN's secret,
             which is the buyer's. --}}
        @if (($showCard ?? ! $dark) && $plan->card_brand && $plan->card_last4)
            <p class="{{ $small }} {{ $muted }} mt-[2px]">
                {{ __('messages.installment_card_on_file', ['brand' => ucfirst($plan->card_brand), 'last4' => $plan->card_last4]) }}
            </p>
        @endif

        {{-- A card that expires before the last payment is a guaranteed future decline and the
             only one we can see coming, so say so while it is still cheap to fix. --}}
        @if ($plan->cardExpiresBeforeFinalPayment())
            <div class="{{ $stage ? 'gk-note gk-note-warn mt-[10px]' : 'mt-[10px] rounded-lg p-3 text-[12px] bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-amber-800 dark:text-amber-200' }}">
                {{ __('messages.installment_card_expiring') }}
            </div>
        @endif

        <div class="flex flex-wrap {{ $stage ? 'gk-plan-actions' : 'gap-[8px] mt-[14px]' }} print:hidden">
            <form method="POST" action="{{ route('installment.pay', ['plan_id' => \App\Utils\UrlUtils::encodeId($plan->id), 'secret' => $plan->secret]) }}">
                @csrf
                <x-honeypot />
                <input type="hidden" name="mode" value="next">
                {{-- On the ticket and its pay page (dark) the one filled button is the schedule's
                     colour, as every other is there; on a light host it is the platform's. --}}
                <button type="submit" class="{{ $stage ? 'gk-btn gk-btn-primary gk-btn-lg' : ($dark ? 'gk-tk-btn gk-tk-btn-fill' : 'inline-flex items-center px-[14px] py-[8px] rounded-lg bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] text-white text-[13px] font-semibold transition-colors') }}">
                    {{ __('messages.pay_next_installment') }}
                </button>
            </form>

            @if ($remaining > (float) $next->amount)
                <form method="POST" action="{{ route('installment.pay', ['plan_id' => \App\Utils\UrlUtils::encodeId($plan->id), 'secret' => $plan->secret]) }}">
                    @csrf
                    <x-honeypot />
                    <input type="hidden" name="mode" value="payoff">
                    <button type="submit" class="{{ $quietButton }}">
                        {{ __('messages.pay_off_balance', ['amount' => \App\Utils\MoneyUtils::format($remaining, $currency)]) }}
                    </button>
                </form>
            @endif

            {{-- Offered proactively, not only after a failure: "my card was stolen" should not
                 mean "I lose my seat". --}}
            <form method="POST" action="{{ route('installment.pay', ['plan_id' => \App\Utils\UrlUtils::encodeId($plan->id), 'secret' => $plan->secret]) }}">
                @csrf
                <x-honeypot />
                <input type="hidden" name="mode" value="update_card">
                <button type="submit" class="{{ $quietButton }}">
                    {{ __('messages.update_payment_card') }}
                </button>
            </form>
        </div>
    @endif
</div>
