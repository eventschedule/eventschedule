<?php

namespace App\Utils;

use Illuminate\Support\Str;

/**
 * The demo schedules /examples shows, and how its two walls are hung.
 *
 * The list is written here, not read from the database: the twelve hand-made examples exist only
 * on the hosted install, and this page is served wherever the marketing site is. The pictures the
 * page shows of them (each schedule's own header and logo at the size the wall uses, and the
 * photographs of their pages) are config/example_shots.php, which app:generate-example-shots
 * writes. Without that file the page still stands: the wall uses the header images listed here at
 * whatever size they are, and a picture that is pressed opens the live page.
 */
class ExampleSchedules
{
    /** The made-up town (DemoService's seeds). It stands on a wall of its own, apart from the hand-made examples. */
    public const TOWN = 'Springfield';

    /**
     * The order on the walls: the hand-made examples first, their kinds dealt out so no row is
     * one kind, then the town. A schedule this does not name follows the ones it does.
     */
    private const ORDER = [
        'villageidiot', 'weekendyogaretreat', 'battleofthebands', 'hikingclub', 'karateclub', 'pagesbooknookshop',
        'meditationclasses', 'nateswoodworkingshop', 'countyfairgrounds', 'sufficientgroundscoffeemusic', 'painting', 'communityyouthgroup',
        'simpsons', 'demo-moestavern', 'demo-lardlad', 'demo-bowlarama', 'demo-amphitheater', 'demo-aztectheater',
    ];

    /** A shape is kept between these: a taller picture or a longer banner gives up its ends. */
    private const MIN_RATIO = 1.2;

    private const MAX_RATIO = 3.2;

    /** Where the prepared pictures are, under public/. */
    public const ART_DIR = 'images/examples/wall/';

    public const PAGES_DIR = 'images/examples/pages/';

    /**
     * Demo schedules by kind. Every address is on the hosted install.
     *
     * @return array<string, list<array{subdomain: string, name: string, description: string, url: string, profile_image_url: string, header_image_url: string}>>
     */
    public static function byCategory(): array
    {
        return [
            'Fitness & Wellness' => [
                [
                    'subdomain' => 'meditationclasses',
                    'name' => 'Meditation Classes',
                    'description' => 'Daily guided sessions for mindfulness and calm',
                    'url' => 'https://meditationclasses.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_meditationclasses.png',
                    'header_image_url' => 'images/examples/header_meditationclasses.png',
                ],
                [
                    'subdomain' => 'weekendyogaretreat',
                    'name' => 'Weekend Yoga Retreat',
                    'description' => 'Multi-day weekend retreat with yoga classes',
                    'url' => 'https://weekendyogaretreat.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_weekendyogaretreat.jpg',
                    'header_image_url' => 'images/examples/header_weekendyogaretreat.jpeg',
                ],
                [
                    'subdomain' => 'hikingclub',
                    'name' => 'Hiking Club',
                    'description' => 'Weekly group hikes and outdoor adventures',
                    'url' => 'https://hikingclub.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_hikingclub.png',
                    'header_image_url' => 'images/examples/header_hikingclub.png',
                ],
            ],
            'Music & Entertainment' => [
                [
                    'subdomain' => 'battleofthebands',
                    'name' => 'Battle of the Bands',
                    'description' => 'Live competition showcasing local bands',
                    'url' => 'https://battleofthebands.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_battleofthebands.jpg',
                    'header_image_url' => 'images/examples/header_battleofthebands.jpg',
                ],
                [
                    'subdomain' => 'sufficientgroundscoffeemusic',
                    'name' => 'Sufficient Grounds',
                    'description' => 'Acoustic sets and open mic nights at a cafe',
                    'url' => 'https://sufficientgroundscoffeemusic.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_sufficientgroundscoffeemusic.jpg',
                    'header_image_url' => 'images/examples/header_sufficientgroundscoffeemusic.png',
                ],
                [
                    'subdomain' => 'villageidiot',
                    'name' => 'Village Idiot',
                    'description' => 'Weekly live music lineup at a neighborhood pub',
                    'url' => 'https://villageidiot.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_villageidiot.png',
                    'header_image_url' => 'images/examples/header_villageidiot.png',
                ],
            ],
            'Community & Recreation' => [
                [
                    'subdomain' => 'communityyouthgroup',
                    'name' => 'Community Youth Group',
                    'description' => 'Activities and meetups for young people',
                    'url' => 'https://communityyouthgroup.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_communityyouthgroup.png',
                    'header_image_url' => 'images/examples/header_communityyouthgroup.png',
                ],
                [
                    'subdomain' => 'karateclub',
                    'name' => 'Karate Club',
                    'description' => 'Martial arts classes for all skill levels',
                    'url' => 'https://karateclub.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_karateclub.jpg',
                    'header_image_url' => 'images/examples/header_karateclub.jpg',
                ],
                [
                    'subdomain' => 'countyfairgrounds',
                    'name' => 'County Fairgrounds',
                    'description' => 'Seasonal events, fairs, and community gatherings',
                    'url' => 'https://countyfairgrounds.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_countyfairgrounds.png',
                    'header_image_url' => 'images/examples/header_countyfairgrounds.jpg',
                ],
            ],
            'Creative & Workshops' => [
                [
                    'subdomain' => 'nateswoodworkingshop',
                    'name' => "Nate's Woodworking Shop",
                    'description' => 'Hands-on woodworking classes and projects',
                    'url' => 'https://nateswoodworkingshop.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_nateswoodworkingshop.png',
                    'header_image_url' => 'images/examples/header_nateswoodworkingshop.png',
                ],
                [
                    'subdomain' => 'painting',
                    'name' => 'Painting',
                    'description' => 'Painting sessions for beginners and artists',
                    'url' => 'https://painting.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_painting.jpg',
                    'header_image_url' => 'images/examples/header_painting.jpg',
                ],
                [
                    'subdomain' => 'pagesbooknookshop',
                    'name' => 'Pages Book Nook Shop',
                    'description' => 'Author readings, book clubs, and signings',
                    'url' => 'https://pagesbooknookshop.eventschedule.com/',
                    'profile_image_url' => 'images/examples/profile_pagesbooknookshop.png',
                    'header_image_url' => 'images/examples/header_pagesbooknookshop.png',
                ],
            ],
            'Springfield' => [
                [
                    'subdomain' => 'simpsons',
                    'name' => 'Springfield Events',
                    'description' => 'Community events across Springfield venues',
                    'url' => 'https://simpsons.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_donuts.jpg',
                    'header_image_url' => 'images/demo/demo_header_town.jpg',
                ],
                [
                    'subdomain' => 'demo-moestavern',
                    'name' => "Moe's Tavern",
                    'description' => 'Live music, trivia, and open mic nights',
                    'url' => 'https://demo-moestavern.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_beer.jpg',
                    'header_image_url' => 'images/demo/demo_header_bar.jpg',
                ],
                [
                    'subdomain' => 'demo-amphitheater',
                    'name' => 'Springfield Amphitheater',
                    'description' => 'Outdoor concerts and performances',
                    'url' => 'https://demo-amphitheater.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_amphitheater.jpg',
                    'header_image_url' => 'images/demo/demo_header_concert.jpg',
                ],
                [
                    'subdomain' => 'demo-bowlarama',
                    'name' => "Barney's Bowl-A-Rama",
                    'description' => 'Bowling leagues, tournaments, and cosmic bowling nights',
                    'url' => 'https://demo-bowlarama.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_bowling.jpg',
                    'header_image_url' => 'images/demo/demo_header_bowling.jpg',
                ],
                [
                    'subdomain' => 'demo-aztectheater',
                    'name' => 'The Aztec Theater',
                    'description' => "Classic films and premieres at Springfield's art deco cinema",
                    'url' => 'https://demo-aztectheater.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_popcorn.jpg',
                    'header_image_url' => 'images/demo/demo_header_theater.jpg',
                ],
                [
                    'subdomain' => 'demo-lardlad',
                    'name' => 'Lard Lad Donuts',
                    'description' => 'Donut tastings, coffee events, and sweet celebrations',
                    'url' => 'https://demo-lardlad.eventschedule.com/',
                    'profile_image_url' => 'images/demo/demo_profile_donut_box.jpg',
                    'header_image_url' => 'images/demo/demo_header_donuts.jpg',
                ],
            ],
        ];
    }

    /** @return list<array<string, string>> */
    public static function all(): array
    {
        return collect(self::byCategory())->flatten(1)->all();
    }

    public static function townKey(): string
    {
        return Str::slug(self::TOWN);
    }

    /**
     * What app:generate-example-shots wrote, with every part present.
     *
     * @return array{art: array, shots: array, xray: array, taken: ?string}
     */
    public static function shots(): array
    {
        $stored = config('example_shots');
        $stored = is_array($stored) ? $stored : [];

        return [
            'art' => is_array($stored['art'] ?? null) ? $stored['art'] : [],
            'shots' => is_array($stored['shots'] ?? null) ? $stored['shots'] : [],
            'xray' => is_array($stored['xray'] ?? null) ? $stored['xray'] : [],
            'taken' => is_string($stored['taken'] ?? null) ? $stored['taken'] : null,
        ];
    }

    /**
     * The two walls: the hand-made examples, ending on a space that is free, and under them the
     * town. Each is its schedules in order, each with its kind's key, its picture and its shape,
     * and where every row ends at every breakpoint (PosterWall decides the rows, as it does for
     * /browse, so nothing moves when a picture arrives).
     *
     * @param  array<string, list<array<string, string>>>  $categories
     * @param  array<string, array<string, mixed>>  $art
     * @return array<string, array{units: list<array<string, mixed>>, tiles: list<array<string, mixed>>, rows: array<string, array<string, mixed>>, blank: bool, opening: int}>
     */
    public static function walls(array $categories, array $art): array
    {
        $units = [];

        foreach ($categories as $kind => $schedules) {
            foreach ($schedules as $schedule) {
                $units[] = $schedule + ['room' => Str::slug($kind)];
            }
        }

        usort($units, function ($a, $b) {
            $ai = array_search($a['subdomain'], self::ORDER, true);
            $bi = array_search($b['subdomain'], self::ORDER, true);

            return ($ai === false ? 999 : $ai) <=> ($bi === false ? 999 : $bi);
        });

        $town = self::townKey();

        return [
            'examples' => self::hang(array_filter($units, fn ($unit) => $unit['room'] !== $town), $art, true),
            'town' => self::hang(array_filter($units, fn ($unit) => $unit['room'] === $town), $art, false),
        ];
    }

    /**
     * One wall. With $blank it ends on a tile that has no picture, whose shape is whatever
     * brings the last row flush.
     */
    private static function hang(array $units, array $art, bool $blank): array
    {
        $units = array_values($units);
        $tiles = [];

        foreach ($units as $at => $unit) {
            $units[$at]['picture'] = self::picture($unit, $art[$unit['subdomain']] ?? null);
            $tiles[] = ['ratio' => self::shape($units[$at]['picture'])];
        }

        if ($blank) {
            $tiles[] = ['ratio' => 2.0, 'stretch' => [1.3, 4.6]];
        }

        $rows = PosterWall::layout($tiles);

        return [
            'units' => $units,
            'tiles' => $tiles,
            'rows' => $rows,
            'blank' => $blank,
            // The first row, whichever breakpoint is widest: on screen as the page opens.
            'opening' => max(array_map(fn ($profile) => $rows[$profile]['ends'][0] ?? -1, array_keys(PosterWall::PROFILES))),
        ];
    }

    /**
     * A schedule's picture for the wall: the prepared one, or the header it is listed with at
     * whatever size that file is. Its logo likewise.
     *
     * @return array{src: string, w: int, h: int, logo: ?string}
     */
    private static function picture(array $unit, mixed $own): array
    {
        if (is_array($own) && ! empty($own['header']) && (int) ($own['w'] ?? 0) > 0 && (int) ($own['h'] ?? 0) > 0) {
            $picture = [
                'src' => asset(self::ART_DIR.$own['header']),
                'w' => (int) $own['w'],
                'h' => (int) $own['h'],
                'logo' => ! empty($own['logo']) ? asset(self::ART_DIR.$own['logo']) : null,
            ];
        } else {
            $size = @getimagesize(public_path($unit['header_image_url'])) ?: [1200, 600];
            $picture = [
                'src' => asset(webp_path($unit['header_image_url'])),
                'w' => (int) $size[0],
                'h' => (int) $size[1],
                'logo' => null,
            ];
        }

        $picture['logo'] ??= ! empty($unit['profile_image_url']) ? asset(webp_path($unit['profile_image_url'])) : null;

        return $picture;
    }

    private static function shape(array $picture): float
    {
        return round(max(self::MIN_RATIO, min(self::MAX_RATIO, $picture['w'] / max(1, $picture['h']))), 3);
    }
}
