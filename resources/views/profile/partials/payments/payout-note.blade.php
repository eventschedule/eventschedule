{{-- Where the money from a ticket goes and when it reaches the bank, under a payment method that
     is connected. An organizer paying their own vendors from ticket money asked when payouts
     happen, and nothing in the app said. We hold nothing: each provider pays out on its own
     schedule, so the line says so and links to where that schedule can be read. Only for the
     owner's OWN account: money taken through an account the install supplies is not theirs to
     look up. Expects $gateway, $gatewayKey and $user. --}}
@php
    $ownAccount = $gatewayKey === 'stripe'
        ? config('app.hosted') && $user->stripe_completed_at
        : $gateway->hasOwnCredentials($user);

    $payout = ! $ownAccount ? null : match ($gatewayKey) {
        'stripe' => ['text' => __('messages.payout_note_stripe'), 'url' => 'https://dashboard.stripe.com/payouts', 'link' => __('messages.payout_link', ['provider' => 'Stripe'])],
        'paypal' => ['text' => __('messages.payout_note_paypal'), 'url' => 'https://www.paypal.com/myaccount/money', 'link' => __('messages.payout_link', ['provider' => 'PayPal'])],
        'razorpay' => ['text' => __('messages.payout_note_settlement', ['provider' => 'Razorpay']), 'url' => 'https://dashboard.razorpay.com/app/settlements', 'link' => __('messages.payout_link', ['provider' => 'Razorpay'])],
        'payfast' => ['text' => __('messages.payout_note_settlement', ['provider' => 'Payfast']), 'url' => 'https://my.payfast.io/login', 'link' => __('messages.payout_link', ['provider' => 'Payfast'])],
        'invoiceninja' => ['text' => __('messages.payout_note_invoiceninja'), 'url' => null, 'link' => null],
        default => null,
    };
@endphp
@if ($payout)
    <p class="event-hint mt-4" data-payout-note="{{ $gatewayKey }}">
        {{ $payout['text'] }}
        @if ($payout['url'])
        <x-link href="{{ $payout['url'] }}" target="_blank">{{ $payout['link'] }}</x-link>
        @endif
    </p>
@endif
