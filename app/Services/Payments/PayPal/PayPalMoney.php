<?php

namespace App\Services\Payments\PayPal;

/**
 * Amount formatting for the PayPal REST API.
 *
 * PayPal wants `amount.value` as a STRING at the currency's own precision, and rejects the request
 * outright when a currency that takes no decimals is sent with any.
 *
 * This deliberately does NOT use MoneyUtils::decimalsFor(). That list is Stripe's - its own docblock
 * says so - and the two disagree exactly where it matters:
 *
 *     currency   MoneyUtils   PayPal
 *     HUF        2            0
 *     JPY        0            0
 *     TWD        2            0
 *
 * So routing PayPal through MoneyUtils sends "1500.00" for a Hungarian or Taiwanese event and PayPal
 * refuses it at ORDER CREATION - after TicketController has committed the sale rows and
 * SaleTicket::created has already incremented `sold`. It passes every test written in USD and fails
 * in production for the two currencies nobody tested. Leave MoneyUtils alone; it is right for Stripe.
 */
class PayPalMoney
{
    /**
     * PayPal's own list, and all of it: "This currency does not support decimals. If you pass a
     * decimal amount, an error occurs."
     */
    private const ZERO_DECIMAL = ['HUF', 'JPY', 'TWD'];

    public static function decimals(string $currencyCode): int
    {
        return in_array(strtoupper($currencyCode), self::ZERO_DECIMAL, true) ? 0 : 2;
    }

    /**
     * The string PayPal expects, with no thousands separator.
     *
     * Rounding rather than truncating, and the caller must reconcile against THIS value rather than
     * against its own unrounded total: sales.payment_amount is decimal(13,3) and percentage promos
     * routinely produce a third decimal, while SaleSettlementService::AMOUNT_TOLERANCE is a flat
     * 0.01. On a zero-decimal currency the gap between 1000.50 and the 1001 we are obliged to send is
     * 0.50, which would land every such sale in `amount_mismatch`.
     */
    /**
     * The figure we will actually charge, as a float.
     *
     * Internal to value(). It is deliberately NOT offered as a reconciliation helper: settle() is
     * handed the amount the gateway REPORTS, never an expected one, so there is no seam at which a
     * driver could reconcile against this. Keeping the two currencies where that gap bites off the
     * allowlist is what stands in for it - see config/payments.php.
     */
    private static function rounded(float $amount, string $currencyCode): float
    {
        return round($amount, self::decimals($currencyCode));
    }

    public static function value(float $amount, string $currencyCode): string
    {
        return number_format(self::rounded($amount, $currencyCode), self::decimals($currencyCode), '.', '');
    }
}
