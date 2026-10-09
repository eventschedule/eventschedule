<?php

namespace App\Services\Blog;

use App\Utils\PlatformPricing;

/**
 * What a blog post may say about the product, as the prompt receives it (config/blog_facts.php).
 */
class BlogFacts
{
    /** The headings the prompts refer to ("listed under the Pro or Enterprise heading"). */
    private const HEADINGS = [
        'about' => 'What it is',
        'free' => 'On every plan (Free included)',
        'pro' => 'On the Pro plan and above (not on Free)',
        'enterprise' => 'On the Enterprise plan only',
        'not' => 'What Event Schedule does not do (do not suggest it does)',
    ];

    /**
     * The feature labels on the audience cards (config/sub_audiences.php, `features`), as the
     * facts they mean. The old prompt handed the model the bare labels ("Ticket sales, Fan
     * newsletters") and it wrote whatever those words suggested.
     */
    public const LABELS = [
        'Ticket sales' => 'paid-tickets',
        'Multiple ticket types' => 'paid-tickets',
        'Ticket management' => 'paid-tickets',
        'Schedule sharing' => 'what',
        'Fan newsletters' => 'newsletter',
        'Embeddable calendar' => 'embed',
        'Recurring events' => 'recurring',
        'Registration' => 'rsvp',
        'Online event links' => 'online',
        'Google Calendar sync' => 'sync',
        'iCal feeds' => 'calendar-add',
        'Sub-schedules' => 'sub-schedules',
        'QR code check-in' => 'scan',
        'Event creation' => 'import',
        'Natural language' => 'import',
        'Any language' => 'import',
        'REST API' => 'api',
        'OpenAPI spec' => 'api',
        'Webhooks' => 'api',
        'Client libraries' => 'api',
        'Bot integration' => 'api',
        'Automation' => 'api',
    ];

    /**
     * The fact ids behind a list of card labels.
     *
     * @param  array<int, string>  $labels
     * @return list<string>
     */
    public static function forLabels(array $labels): array
    {
        $ids = [];

        foreach ($labels as $label) {
            if (isset(self::LABELS[$label])) {
                $ids[self::LABELS[$label]] = true;
            }
        }

        return array_keys($ids);
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return (array) config('blog_facts', []);
    }

    /** @return list<string> */
    public static function ids(): array
    {
        return array_keys(self::all());
    }

    /**
     * Every fact under its plan's heading, one a line, each starting with its id in brackets,
     * with this install's own prices in place of the placeholders.
     */
    public static function forPrompt(): string
    {
        $lines = [];

        foreach (self::HEADINGS as $tier => $heading) {
            $rows = array_filter(self::all(), fn ($fact) => ($fact['tier'] ?? null) === $tier);

            if ($rows === []) {
                continue;
            }

            $lines[] = ($lines === [] ? '' : "\n").$heading;

            foreach ($rows as $id => $fact) {
                $lines[] = '['.$id.'] '.strtr((string) $fact['says'], self::prices());
            }
        }

        return implode("\n", $lines);
    }

    /**
     * The price placeholders of config/blog_facts.php, filled from the installation's own plan
     * prices and currency: a price is never typed into a fact.
     *
     * @return array<string, string>
     */
    public static function prices(): array
    {
        return [
            ':free' => plan_price(0),
            ':pro_monthly' => plan_price(PlatformPricing::proMonthly()),
            ':pro_yearly' => plan_price(PlatformPricing::proYearly()),
            ':enterprise_monthly' => plan_price(PlatformPricing::enterpriseMonthly()),
            ':enterprise_yearly' => plan_price(PlatformPricing::enterpriseYearly()),
        ];
    }

    /** The ids a model returned, cleaned of brackets and case, keeping only real ones. */
    public static function known(array $ids): array
    {
        $real = array_flip(self::ids());
        $kept = [];

        foreach ($ids as $id) {
            $id = strtolower(trim((string) $id, "[] \t\n"));

            if (isset($real[$id])) {
                $kept[$id] = true;
            }
        }

        return array_keys($kept);
    }
}
