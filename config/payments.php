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
    ],

];
