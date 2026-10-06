<?php

namespace App\Services;

use App\Utils\SignupSource;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/**
 * Invented data in the shape AdminDashboard::build() returns, for the one caller that must not show
 * the real thing: the documentation screenshot (GenerateDocScreenshots). That command renders the
 * developer's database, and the dashboard lists people, schedules and events by name - which would
 * otherwise be published in a public docs image, or (the dashboard leaves demo content out) be a
 * page of empty states.
 *
 * Nothing here is read from a database. The names are made up, the images are the demo set that
 * ships in public/images/demo, and every figure is chosen so the rows of a card add up.
 */
class AdminDashboardSample
{
    public static function data(bool $empty = false): array
    {
        $now = now()->toImmutable();
        $hosted = (bool) config('app.hosted');

        if ($empty) {
            return self::blank($now, $hosted);
        }

        $organizers = [2, 3, 1, 4, 2, 3, 5, 2, 1, 3, 4, 2, 3, 6, 2, 1, 2, 4, 3, 2, 5, 3, 1, 2, 4, 3, 4, 2, 3, 4];
        $others = [1, 2, 0, 1, 3, 1, 2, 1, 0, 2, 1, 1, 2, 3, 1, 0, 1, 2, 1, 1, 2, 2, 0, 1, 2, 1, 2, 1, 2, 2];
        $total = array_sum($organizers);

        $channels = [
            ['search', 27, [['google.com', 25], ['bing.com', 2]]],
            ['ai', 19, [['chatgpt.com', 17], ['perplexity.ai', 2]]],
            ['direct', 9, []],
            ['social', 6, [['facebook.com', 4], ['instagram.com', 2]]],
            ['referral', 4, []],
            ['email', 3, [['newsletter', 3]]],
            ['other', 3, [['whatsonlinz.example', 2]]],
            ['unrecorded', 15, []],
        ];

        return [
            'signups' => [
                'organizers' => ['last_24h' => 5, 'last_30d' => $total, 'previous_30d' => 74, 'change' => 16.2],
                'others' => ['total' => array_sum($others), 'by_intent' => [
                    ['intent' => 'ticket', 'count' => 22], ['intent' => 'follow', 'count' => 11], ['intent' => 'request', 'count' => 8],
                ]],
                'days' => self::days($now, $organizers, $others),
                'methods' => ['email' => 52, 'google' => 31, 'both' => 3, 'other' => 0],
                'sources' => [
                    'total' => $total,
                    'unrecorded' => 15,
                    'channels' => array_map(fn ($row) => [
                        'channel' => $row[0],
                        'label' => SignupSource::label($row[0]),
                        'count' => $row[1],
                        'share' => (int) round($row[1] / $total * 100),
                        'names' => array_map(fn ($name) => ['name' => $name[0], 'count' => $name[1]], $row[2]),
                    ], $channels),
                    'landing' => [
                        ['path' => '/', 'count' => 31], ['path' => '/features/embed-calendar', 'count' => 12],
                        ['path' => '/features/embed-tickets', 'count' => 9], ['path' => '/pricing', 'count' => 6],
                        ['path' => '/luma-alternative', 'count' => 4],
                    ],
                ],
                'latest' => array_map(fn ($row) => [
                    'name' => $row[0],
                    'initials' => RealtimeDashboard::initials($row[0]),
                    'source' => $row[1] ? ['primary' => $row[1], 'secondary' => $row[2], 'channel' => 'other'] : null,
                    'stage' => $row[3],
                    'schedule' => $row[4] ? ['name' => $row[4], 'url' => '#'] : null,
                    'created_at' => Carbon::instance($now->subMinutes($row[5])),
                ], [
                    ['Tomas Aguilar', 'chatgpt.com', '/features/embed-calendar', 0, null, 35],
                    ['Jonas Reiter', 'google.com', '/luma-alternative', 3, 'Kulturhaus Linz', 120],
                    ['Priya Nair', null, null, 2, 'The Vinyl Room', 300],
                    ['Marta Soler', 'instagram.com', null, 2, 'Marta Soler Trio', 540],
                    ['Dana Whitlock', 'google.com', '/pricing', 1, 'Riverside Bowl', 840],
                    ['Sem de Vries', __('messages.realtime_referred_by', ['name' => 'Li Wei']), null, 3, 'Open Stage Utrecht', 1500],
                    ['Li Wei', 'chatgpt.com', null, 2, 'Science After Dark', 1700],
                    ['Aoife Brennan', null, null, 1, 'Back Room Comedy', 2900],
                ]),
            ],
            'active' => self::active($now),
            'revenue' => $hosted ? self::revenue() : null,
            'events' => [
                'total' => 1284, 'in_person' => 842, 'online' => 263, 'hybrid' => 97, 'no_location' => 82,
                'one_off' => 1041, 'recurring' => 243, 'next_24h' => 14, 'next_7' => 96, 'next_30' => 402,
                'countries' => [
                    ['code' => 'us', 'name' => 'United States', 'count' => 312], ['code' => 'de', 'name' => 'Germany', 'count' => 148],
                    ['code' => 'gb', 'name' => 'United Kingdom', 'count' => 121], ['code' => 'nl', 'name' => 'Netherlands', 'count' => 77],
                    ['code' => 'ro', 'name' => 'Romania', 'count' => 54],
                ],
                'all_time' => 12480, 'new_30d' => 318, 'new_change' => 12.0,
            ],
            'federation' => self::federation($now),
            'schedules' => ['total' => 1936, 'new_30d' => 41, 'rows' => self::schedules($now, $hosted)],
            'recentEvents' => ['rows' => self::events($now)],
            'system' => [
                'jobs_waiting' => 3, 'jobs_failed' => 0,
                'domains' => $hosted ? ['total' => 16, 'pending' => 2] : null,
                'accounts' => 4812,
            ],
            'firstRun' => false,
        ];
    }

    private static function blank(CarbonImmutable $now, bool $hosted): array
    {
        return [
            'signups' => [
                'organizers' => ['last_24h' => 0, 'last_30d' => 0, 'previous_30d' => 0, 'change' => null],
                'others' => ['total' => 0, 'by_intent' => []],
                'days' => self::days($now, array_fill(0, 30, 0), array_fill(0, 30, 0)),
                'methods' => ['email' => 0, 'google' => 0, 'both' => 0, 'other' => 0],
                'sources' => ['total' => 0, 'unrecorded' => 0, 'channels' => [], 'landing' => []],
                'latest' => [],
            ],
            'active' => ['available' => true, 'active_7d' => 1, 'active_30d' => 1, 'organizers_7d' => 1, 'previous_7d' => null,
                'change' => null, 'exact' => false, 'exact_30d' => false, 'exact_from' => null, 'exact_from_label' => null, 'series' => []],
            'revenue' => $hosted ? array_replace_recursive(self::revenue(), ['totals' => array_fill_keys(array_keys(self::revenue()['totals']), 0)]) : null,
            'events' => ['total' => 0, 'in_person' => 0, 'online' => 0, 'hybrid' => 0, 'no_location' => 0, 'one_off' => 0, 'recurring' => 0,
                'next_24h' => 0, 'next_7' => 0, 'next_30' => 0, 'countries' => [], 'all_time' => 0, 'new_30d' => 0, 'new_change' => null],
            'federation' => ['mode' => 'off'],
            'schedules' => ['total' => 0, 'new_30d' => 0, 'rows' => []],
            'recentEvents' => ['rows' => []],
            'system' => ['jobs_waiting' => 0, 'jobs_failed' => 0, 'domains' => $hosted ? ['total' => 0, 'pending' => 0] : null, 'accounts' => 1],
            'firstRun' => true,
        ];
    }

    private static function days(CarbonImmutable $now, array $organizers, array $others): array
    {
        $start = $now->startOfDay()->subDays(29);

        return array_map(fn ($index) => [
            'date' => $start->addDays($index)->toDateString(),
            'label' => $start->addDays($index)->translatedFormat('M j'),
            'organizers' => $organizers[$index],
            'others' => $others[$index],
        ], range(0, 29));
    }

    /** Twelve weeks: nine estimated from the security log, three counted. */
    private static function active(CarbonImmutable $now): array
    {
        $days = ActiveDays::SERIES_DAYS;
        $exactFrom = $now->startOfDay()->subDays(20);
        $series = [];

        for ($index = 0; $index < $days; $index++) {
            $date = $now->startOfDay()->subDays($days - 1 - $index);
            $exact = $date->gte($exactFrom);
            $value = (int) round(90 + $index * 0.44 + 6 * sin($index / 3.3) + 2 * sin($index * 1.7));

            $series[] = [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('M j'),
                'active' => $index === $days - 1 ? 128 : ($exact ? $value : (int) round($value * 0.7)),
                'exact' => $exact,
            ];
        }

        return [
            'available' => true, 'active_7d' => 128, 'active_30d' => 346, 'organizers_7d' => 96, 'previous_7d' => 117, 'exact_30d' => true,
            'change' => 9.4, 'exact' => true, 'exact_from' => $exactFrom->toDateString(),
            'exact_from_label' => $exactFrom->translatedFormat('M j'), 'series' => $series,
        ];
    }

    private static function revenue(): array
    {
        $plan = fn ($tier, $term, $billing, $mrr, $trials, $trialMrr) => [
            'tier' => $tier, 'term' => $term, 'billing_count' => $billing, 'mrr' => $mrr, 'arr' => $mrr * 12,
            'trialing_count' => $trials, 'trial_mrr' => $trialMrr,
        ];

        return [
            'plans' => [
                'pro_month' => $plan('pro', 'month', 14, 70.0, 3, 15.0),
                'pro_year' => $plan('pro', 'year', 9, 37.5, 1, 4.17),
                'enterprise_month' => $plan('enterprise', 'month', 4, 60.0, 1, 15.0),
                'enterprise_year' => $plan('enterprise', 'year', 2, 25.0, 0, 0.0),
            ],
            'unrecognized' => ['billing_count' => 0, 'trialing_count' => 0],
            'totals' => [
                'mrr' => 192.5, 'arr' => 2310.0, 'billing_count' => 29, 'trialing_count' => 5, 'unrecognized_count' => 0,
                'trial_mrr' => 34.17, 'past_due_count' => 2, 'cancelling_count' => 1, 'at_risk_count' => 3, 'at_risk_mrr' => 15.0,
            ],
            'outside' => ['granted' => 11, 'trial' => 3, 'selling' => 6],
            'boost' => ['markup' => 42.0, 'currency' => \App\Utils\PlatformCurrency::code()],
        ];
    }

    private static function federation(CarbonImmutable $now): array
    {
        if (config('app.is_nexus')) {
            return [
                'mode' => 'hub',
                'installs' => [
                    'by_status' => ['approved' => 17, 'pending' => 2, 'suspended' => 1],
                    'active_30d' => 12,
                    'by_version' => ['v1.0.134' => 9, 'v1.0.133' => 4, 'v1.0.132' => 2, 'v1.0.131' => 1, 'v1.0.129' => 1],
                ],
                'listings' => ['live' => 286, 'schedules' => 38, 'online' => 41, 'hybrid' => 24, 'in_person' => 221],
                'clicks' => ['total' => 1932, 'previous' => 1637, 'change' => 18.0, 'top' => [
                    ['id' => 1, 'name' => 'Kulturhaus Linz', 'host' => 'events.kulturhaus-linz.example', 'listings' => 64, 'clicks' => 612],
                    ['id' => 2, 'name' => 'Brighton Folk Club', 'host' => 'whatson.brightonfolk.example', 'listings' => 22, 'clicks' => 401],
                    ['id' => 3, 'name' => 'Riga Jazz Nights', 'host' => 'rigajazz.example', 'listings' => 31, 'clicks' => 288],
                    ['id' => 4, 'name' => 'Open Stage Utrecht', 'host' => 'agenda.openstage.example', 'listings' => 18, 'clicks' => 207],
                    ['id' => 5, 'name' => 'Cine Club Porto', 'host' => 'cineclubporto.example', 'listings' => 12, 'clicks' => 143],
                ]],
            ];
        }

        return [
            'mode' => 'sender', 'status' => 'approved', 'last_synced_at' => Carbon::instance($now->subMinutes(22)),
            'has_error' => false, 'listings_url' => null, 'totals' => ['total' => 52, 'sent' => 38], 'undecided' => 2,
        ];
    }

    private static function schedules(CarbonImmutable $now, bool $hosted): array
    {
        $image = fn (?string $name) => $name ? asset('images/demo/demo_profile_'.$name.'.jpg') : null;

        return array_map(fn ($row) => [
            'name' => $row[0], 'type' => $row[1], 'place' => $row[2], 'owner' => $row[3], 'events' => $row[4],
            'plan' => $hosted ? $row[5] : null, 'url' => '#', 'image' => $image($row[7]),
            'created_at' => Carbon::instance($now->subMinutes($row[6])),
        ], [
            ['Kulturhaus Linz', 'venue', 'Linz, AT', 'Jonas Reiter', 14, 'pro', 120, 'theater'],
            ['The Vinyl Room', 'venue', 'Leeds, GB', 'Priya Nair', 6, null, 300, 'vinyl'],
            ['Marta Soler Trio', 'talent', 'Valencia, ES', 'Marta Soler', 3, null, 540, 'jazz'],
            ['Riverside Bowl', 'venue', 'Austin, US', 'Dana Whitlock', 0, null, 840, 'bowling'],
            ['Open Stage Utrecht', 'curator', 'Utrecht, NL', 'Sem de Vries', 22, 'trial', 1500, null],
            ['Science After Dark', 'curator', 'Toronto, CA', 'Li Wei', 4, null, 1700, 'science'],
            ['Circo Piccolo', 'talent', 'Bologna, IT', 'Giulia Ferri', 2, null, 2900, 'circus'],
            ['Brighton Folk Club', 'venue', 'Brighton, GB', 'Tom Ashby', 9, 'pro', 3100, null],
            ['Cine Club Porto', 'curator', 'Porto, PT', 'Ana Ferreira', 5, null, 4400, 'film'],
            ['Donut Shop Sessions', 'venue', 'Portland, US', 'Kai Morgan', 1, null, 4600, 'donuts'],
            ['The Night Owls', 'talent', 'Krakow, PL', 'Ola Nowak', 0, null, 5800, 'band'],
            ['Country Roads Hall', 'venue', 'Nashville, US', 'Wade Carter', 7, null, 7300, 'country'],
        ]);
    }

    private static function events(CarbonImmutable $now): array
    {
        $image = fn (?string $name) => $name ? asset('images/demo/demo_'.$name.'.jpg') : null;

        return array_map(fn ($row) => [
            'name' => $row[0], 'schedule' => $row[1], 'series' => $row[2] === null, 'when' => $row[2], 'mode' => $row[3],
            'flag' => $row[5], 'more' => $row[6], 'url' => '#', 'image' => $image($row[7]),
            'created_at' => Carbon::instance($now->subMinutes($row[4])),
        ], [
            ['Thursday Jazz Jam', 'Marta Soler Trio', 'Thu, Oct 9 · 8:30 PM', 'in_person', 40, null, 0, 'flyer_jazz'],
            ['Intro to Night Sky Photography', 'Science After Dark', 'Sat, Oct 18 · 7:00 PM', 'online', 60, null, 0, 'profile_science'],
            ['Open Mic Night', 'Open Stage Utrecht', null, 'in_person', 120, null, 0, 'flyer_openmic'],
            ['Halloween Warehouse Party', 'The Vinyl Room', 'Fri, Oct 31 · 10:00 PM', 'in_person', 180, null, 0, 'flyer_party'],
            ['Stand-up Showcase', 'Back Room Comedy', 'Sat, Oct 11 · 9:00 PM', 'in_person', 300, null, 0, 'flyer_comedy'],
            ['Songwriting Workshop', 'Brighton Folk Club', 'Sun, Oct 12 · 2:00 PM', 'hybrid', 360, 'draft', 0, null],
            ['DJ Maru: All Night Long', 'The Vinyl Room', 'Sat, Oct 25 · 11:00 PM', 'in_person', 540, null, 0, 'flyer_dj'],
            ['Balkan Brass Night', 'Riga Jazz Nights', 'Fri, Oct 24 · 9:00 PM', 'in_person', 660, 'imported', 11, null],
            ['Rock Legends Tribute', 'Hillside Amphitheater', 'Sat, Nov 1 · 7:00 PM', 'in_person', 840, null, 0, 'flyer_rock'],
            ['Morning Flow', 'Yoga by the Canal', null, 'in_person', 1080, null, 0, null],
            ['Live Podcast Recording', 'Open Stage Utrecht', 'Thu, Oct 23 · 7:00 PM', 'hybrid', 2900, 'submitted', 0, null],
            ['Community Board Meeting', 'Kulturhaus Linz', 'Mon, Oct 20 · 6:00 PM', 'no_location', 4400, null, 0, 'profile_theater'],
        ]);
    }
}
