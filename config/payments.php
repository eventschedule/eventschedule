<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment gateways
    |--------------------------------------------------------------------------
    |
    | The registry read by App\Services\Payments\PaymentGatewayManager. Each key is the value stored
    | in events.payment_method and sales.payment_method, and array order is the order owners see in
    | the event Payment dropdown.
    |
    | Adding a gateway is a driver class plus a line here. It should need no new route, controller or
    | blade: the generic payments/{gateway}/... routes and the credentialFields() settings tab cover
    | anything that redirects out and notifies back. If a new gateway seems to need more than this,
    | the seam is in the wrong place - widen PaymentGatewayDriver rather than special-casing.
    |
    | Not listed here, on purpose: 'rsvp' and 'import' also appear in sales.payment_method, but they
    | record where a row came from rather than how it gets paid, so they have no driver.
    | PaymentGatewayManager::get() returns null for them and callers must cope.
    |
    */

    'gateways' => [
        'cash' => App\Services\Payments\Gateways\CashGateway::class,
        'stripe' => App\Services\Payments\Gateways\StripeGateway::class,
        'invoiceninja' => App\Services\Payments\Gateways\InvoiceNinjaGateway::class,
        'payment_url' => App\Services\Payments\Gateways\PaymentUrlGateway::class,
        'payfast' => App\Services\Payments\Gateways\PayfastGateway::class,
    ],

    'payfast' => [

        /*
         * The hosts an ITN may legitimately come from, resolved at check time rather than pinned as
         * addresses because Payfast changes them - that is their own guidance. A literal IP here is
         * used as-is, which is what lets tests point this at the test client instead of stubbing
         * gethostbynamel().
         */
        'itn_hosts' => [
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        ],

        /*
         * Installation-wide credentials, read by PayfastGateway::platformCredentials().
         *
         * Selfhost only, and a DEFAULT rather than an override: an owner who has connected their own
         * Payfast account keeps using it. See PaymentGatewayDriver::credentialsFor().
         */
        'merchant_id' => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'passphrase' => env('PAYFAST_PASSPHRASE'),
        'sandbox' => env('PAYFAST_SANDBOX', false),
        'payment_types' => env('PAYFAST_PAYMENT_TYPES'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Default payment method for a new event
    |--------------------------------------------------------------------------
    |
    | What the event form and the API start a new event on. 'cash' is the historical default and the
    | events.payment_method column default; an operator whose install runs on one gateway can point
    | this at it so owners are not selecting the same thing every time.
    |
    | Only honoured when the gateway is genuinely usable for that event - connected for the owner and
    | able to settle the event's currency - so a typo or a stale value degrades to cash rather than
    | producing an event nobody can buy from. See PaymentGatewayManager::defaultMethodFor().
    |
    | `?:` rather than an env() default: .env.example ships the key blank, and a blank value wins over
    | the second argument, so env('DEFAULT_PAYMENT_METHOD', 'cash') would yield '' on a fresh install.
    |
    */

    'default_method' => env('DEFAULT_PAYMENT_METHOD') ?: 'cash',

];
