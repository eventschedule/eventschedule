<?php

namespace App\Utils;

/**
 * What ticketing platforms take out of one event's ticket sales, at their published US rates.
 *
 * Every fee calculator on the marketing site reads this: <x-marketing.fee-calculator> on /compare,
 * the savings panel on /pricing and the door-money band on /for-talent. Each renders its first paint
 * from cost(), and the scripts that recompute as a visitor types are handed rates() as a data-rates
 * attribute and run the same formula (marketing/partials/ticket-fee-math.blade.php), so a keystroke
 * cannot change the answer the page was rendered with. Before this class the rates were retyped in
 * each of those views: the /compare script hardcoded every one of them while its comment said it read
 * them from the controller, and /pricing and /for-talent had dropped Eventbrite's payment processing
 * fee, so on the same 200 tickets at 25 dollars they said 543.00 where /compare's own maths said 688.00.
 *
 * THE MODEL is what the ORGANIZER pays for one event: fees absorbed rather than passed to buyers,
 * charged on face value, one ticket per order (so a fixed per-charge processing fee lands on every
 * ticket). A platform that processes payments itself carries that as 'processing' and 'stripe' =>
 * false; one that settles into the organizer's own Stripe account pays Stripe's rate on top, which is
 * the default. Every figure is a published US price in USD, and the calculators stay in dollars
 * whatever currency this installation quotes its own plans in: a comparison in one currency or none.
 *
 * Rate keys, all optional apart from 'name':
 *   percent, fixed    the platform's own fee on each ticket: a share of the price plus a fixed amount
 *   processing        the platform's own payment processing, as a share of the order
 *   stripe            false when the platform processes the payment itself, so Stripe's rate is not added
 *   monthly           a subscription, counted once for the event
 *   plans             alternative plans, of which the cheaper one for the event is used (Luma)
 *   label             the rate in words, for a calculator card
 *   basis             the sentence a calculator's footnote uses to say how the figure was worked out
 *
 * The same rates are also written out as TEXT in MarketingController, in the /compare grid
 * (getHubComparisonData()) and in each platform's getComparisonData() entry. A rate that changes here
 * changes there too.
 */
final class TicketFees
{
    /** The event /pricing and /for-talent open on: 200 tickets at 25 dollars. */
    public const EXAMPLE_TICKETS = 200;

    public const EXAMPLE_PRICE = 25;

    /** The platforms /compare's calculator shows, in the order it shows them. Ours comes first. */
    public const COMPARE_PLATFORMS = ['eventschedule', 'eventbrite', 'luma', 'ticket-tailor'];

    /**
     * Every rate, keyed by platform, plus Stripe's under 'stripe'.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function rates(): array
    {
        return [
            // Stripe's standard US card rate, "2.9% + 30¢" per successful charge (stripe.com/pricing,
            // checked 2026-09-24). Charged per ticket here, because the model is one ticket per order.
            'stripe' => ['percent' => 0.029, 'fixed' => 0.30, 'label' => '2.9% + $0.30 per ticket'],

            // No platform fee on any plan. A ticket with a price on it is Pro, so the subscription is
            // counted; an event that only takes free registrations costs nothing at all. From the same
            // PlatformPricing reader /pricing uses, so the two cannot quote different plans.
            'eventschedule' => [
                'name' => 'Event Schedule',
                'monthly' => PlatformPricing::proMonthly(),
                'label' => '0% platform fee',
                'basis' => 'Our own figure includes the Pro subscription, because that is what a priced ticket takes; an event that only collects free registrations carries no monthly cost at all.',
            ],

            // eventbrite.com/organizer/pricing, checked 2026-09-24: "3.7% + $1.79 service fee per
            // ticket" and a separate "2.9% payment processing fee per order", with no cap on either.
            // Eventbrite processes the payment itself, so Stripe's rate is not added.
            'eventbrite' => [
                'name' => 'Eventbrite',
                'percent' => 0.037,
                'fixed' => 1.79,
                'processing' => 0.029,
                'stripe' => false,
                'label' => '3.7% + $1.79 per ticket, plus 2.9% per order',
                'basis' => 'Eventbrite is shown with the 2.9% payment processing fee per order that its pricing page lists on top of the service fee.',
            ],

            // luma.com/pricing, checked 2026-09-24: a "5% platform fee for paid events" on the free
            // plan, 0% on Luma Plus at "$59 Per month, billed annually", and Stripe's fee on top of both.
            'luma' => [
                'name' => 'Luma',
                'plans' => [
                    ['percent' => 0.05],
                    ['monthly' => 59],
                ],
                'label' => '5% on the free plan, 0% on Plus at $59/mo',
                'basis' => 'Luma is shown at whichever of its free and Plus plans is cheaper for the event, with Plus at its annual-billing price.',
            ],

            // Carried over unchanged from the earlier /compare rates, NOT re-checked on 2026-09-24:
            // tickettailor.com answers 403 to an automated fetch, its archived pricing page renders in
            // pounds (0.22 to 0.60 GBP a ticket), and third-party summaries of its US prices disagree.
            // Check the band by hand before quoting it anywhere new. The processor's fee is on top.
            'ticket-tailor' => [
                'name' => 'Ticket Tailor',
                'fixed' => 0.44,
                'range' => '$0.28-$0.60 per ticket',
                'label' => '$0.28-$0.60 per ticket',
                'basis' => 'Ticket Tailor publishes $0.28-$0.60 per ticket depending on volume, so the midpoint is used here.',
            ],
        ];
    }

    /**
     * What one platform takes from an event of $tickets tickets at $price each, in USD.
     *
     * @param  array<string, array<string, mixed>>|null  $rates  rates(), when the caller already has it
     */
    public static function cost(string $platform, int|float $tickets, int|float $price, ?array $rates = null): float
    {
        $rates ??= self::rates();

        return self::costOf($rates[$platform], $rates['stripe'], $tickets, $price);
    }

    /**
     * The cost of one rate. The script in marketing/partials/ticket-fee-math.blade.php is this
     * function line for line, in the same order of operations, so the browser's recompute lands on
     * the same doubles the server rendered. Change one and you have changed both.
     *
     * @param  array<string, mixed>  $rate
     * @param  array{percent: float, fixed: float}  $stripe
     */
    public static function costOf(array $rate, array $stripe, int|float $tickets, int|float $price): float
    {
        $tickets = (float) $tickets;
        $price = (float) $price;

        // Nothing sold, or nothing charged: no platform takes a fee on a free ticket, and a free
        // event needs no paid plan of ours either.
        if ($tickets <= 0 || $price <= 0) {
            return 0.0;
        }

        if (! empty($rate['plans'])) {
            $base = $rate;
            unset($base['plans']);

            return min(array_map(
                fn (array $plan) => self::costOf(array_merge($base, $plan), $stripe, $tickets, $price),
                $rate['plans']
            ));
        }

        $revenue = $tickets * $price;
        $perTicket = $price * (float) ($rate['percent'] ?? 0) + (float) ($rate['fixed'] ?? 0);

        $cost = $tickets * $perTicket + $revenue * (float) ($rate['processing'] ?? 0) + (float) ($rate['monthly'] ?? 0);

        if ($rate['stripe'] ?? true) {
            $cost += $revenue * (float) $stripe['percent'] + $tickets * (float) $stripe['fixed'];
        }

        return $cost;
    }

    /**
     * The rates a calculator's script needs: the listed platforms and Stripe, and nothing a
     * visitor's browser has no use for.
     *
     * @param  array<int, string>  $platforms
     * @return array<string, array<string, mixed>>
     */
    public static function forScript(array $platforms, ?array $rates = null): array
    {
        $rates ??= self::rates();
        $keep = ['percent', 'fixed', 'processing', 'stripe', 'monthly', 'plans'];

        $out = ['stripe' => array_intersect_key($rates['stripe'], array_flip(['percent', 'fixed']))];

        foreach ($platforms as $platform) {
            $out[$platform] = array_intersect_key($rates[$platform], array_flip($keep));
        }

        return $out;
    }
}
