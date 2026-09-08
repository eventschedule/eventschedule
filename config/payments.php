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
        'paypal' => App\Services\Payments\Gateways\PayPalGateway::class,
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

    'paypal' => [

        /*
         * Installation-wide credentials, read by PayPalGateway::platformCredentials().
         *
         * Selfhost only, and a DEFAULT rather than an override: an owner who has connected their own
         * PayPal account keeps using it. See PaymentGatewayDriver::credentialsFor().
         *
         * The webhook id is optional where Payfast's passphrase is mandatory. Payfast needs its
         * passphrase or the ITN signature proves nothing; PayPal's gate is a server-side re-fetch of
         * the capture, so a missing webhook id costs only the late-settlement path.
         */
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'sandbox' => env('PAYPAL_SANDBOX', false),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

        /*
         * The currencies PayPal settles, minus two.
         *
         * PayPal publishes 25. HUF and TWD are deliberately LEFT OUT, and the reason is not that
         * PayPal refuses them - it is that this app and PayPal disagree about them, which is worse.
         *
         * PayPal treats HUF, JPY and TWD as zero-decimal and errors on any fractional amount.
         * MoneyUtils::$zeroDecimalCurrencies is STRIPE's list: it holds JPY but not HUF or TWD. So
         * for those two the app prices, stores and displays fractions (sales.payment_amount is
         * decimal(13,3), and a percentage promo or a gift-card split routinely lands one there)
         * while PayPal can only be sent a whole number.
         *
         * Rounding on the way out does not rescue it. SaleSettlementService::AMOUNT_TOLERANCE is a
         * flat 0.01 and is not currency-scaled, so a 1500.50 HUF sale charged as 1501 reconciles
         * 0.50 out and lands in `amount_mismatch`: money captured, ticket withheld, admin alert.
         * Every discounted HUF or TWD sale, silently.
         *
         * JPY is fine and included - there the two lists agree, so nothing fractional is ever stored.
         *
         * Re-adding these two means teaching settlement a per-currency tolerance (the idiom already
         * exists at SaleRefundService::toleranceFor()) and reconciling the two decimal tables. That
         * is a money-handling change in its own right, not a line in this config.
         */
        'currencies' => [
            'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD',
            'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK',
            'SGD', 'THB', 'USD',
        ],

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
