<?php

namespace App\Utils;

/**
 * What each plan includes, one row per question people ask about it.
 *
 * Two pages print this as a table: the rate card on /faq and "Compare plans" on /pricing. They
 * read the one list here so that they cannot disagree, which is the same reason every fee
 * calculator reads App\Utils\TicketFees.
 *
 * Every row is a claim about a tier. docs/FEATURES.md is the reference: a gate that moves there
 * moves here in the same commit. The prices are the ones this installation advertises
 * (PlatformPricing), printed in its own currency by plan_price().
 */
class PlanRateCard
{
    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string}> label, Free, Pro, Enterprise
     */
    public static function rows(): array
    {
        $proMonthly = PlatformPricing::proMonthly();
        $proYearly = PlatformPricing::proYearly();
        $entMonthly = PlatformPricing::enterpriseMonthly();
        $entYearly = PlatformPricing::enterpriseYearly();

        return [
            ['What it costs', plan_price(0).', permanently', plan_price($proMonthly).' / month or '.plan_price($proYearly).' / year', plan_price($entMonthly).' / month or '.plan_price($entYearly).' / year'],
            ['Events on your schedule', 'Unlimited', 'Unlimited', 'Unlimited'],
            ['Public page, embed and QR code', 'Yes', 'Yes', 'Yes'],
            ['Two-way Google, Outlook and CalDAV sync', 'Yes', 'Yes', 'Yes'],
            ['Built-in analytics', 'Yes', 'Yes', 'Yes'],
            ['Free registration with a capacity limit', 'Yes', 'Yes', 'Yes'],
            ['Newsletter emails a month (each recipient counts as one)', '10', '100', '1,000'],
            ['Sell tickets that carry a price', 'No', 'Yes', 'Yes'],
            ['Platform fee on ticket sales', 'Zero', 'Zero', 'Zero'],
            ['Stripe and PayPal checkout, with refunds', 'No', 'Yes', 'Yes'],
            ['Scan tickets at the door', 'Yes', 'Yes', 'Yes'],
            ['Live check-in dashboard, waitlist, promo codes and passes', 'No', 'Yes', 'Yes'],
            ['Appointment booking', '1 type', 'Unlimited types', 'Unlimited types'],
            ['Charge for an appointment booking', 'No', 'Yes', 'Yes'],
            ['Advanced scheduling (overrides, buffers, approvals)', 'No', 'Yes', 'Yes'],
            ['Remove Event Schedule branding', 'No', 'Yes', 'Yes'],
            ['Team members', '1', '1', 'Up to 5'],
            ['Reserved seating for venue schedules', 'No', 'No', 'Yes'],
            ['Custom domain, Internal and Unlisted events', 'No', 'No', 'Yes'],
        ];
    }

    /**
     * Whether a cell says the plan INCLUDES the thing, which is what earns it the affirmative
     * ink on both pages. Denials and ceilings do not, so no limit can read as a feature being
     * sold: that covers "No", a bare quantity (a 10-email allowance, a one-member cap, "1 type")
     * and an "Up to 5". "Unlimited", "Zero" and a price are inclusions.
     */
    public static function includes(string $cell): bool
    {
        return ! (
            $cell === 'No'
            || preg_match('/^\d[\d,]*(\s|$)/', $cell)
            || str_starts_with($cell, 'Up to ')
        );
    }
}
