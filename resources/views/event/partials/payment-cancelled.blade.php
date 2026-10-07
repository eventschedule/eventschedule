{{-- Said once, on the page a cancelled payment comes back to (PaymentGatewayDriver::flashCancelled()).

     A buyer who backed out of the payment provider used to land on an empty form with no word on
     whether they had been charged. Included INSIDE the ticket form's panel where there is one,
     because opening the form scrolls to it and a line above would be scrolled out of sight; and on
     its own where the page has no ticket form to say it in (the gate closed while they were away).

     Two sentences, because one of them is a claim about money: a payment on a page we do not run
     (the organizer's own payment link) gets "not completed", never "nothing was charged". --}}
@php $paymentCancelled = \App\Services\Payments\PaymentGatewayDriver::cancelledFor($event); @endphp
@if ($paymentCancelled)
<div data-payment-cancelled role="status" class="mb-6 flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3">
    <svg class="w-5 h-5 flex-shrink-0 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
    </svg>
    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ! empty($paymentCancelled['charge_unknown']) ? __('messages.payment_not_completed') : __('messages.payment_cancelled_not_charged') }}</span>
</div>
@endif
