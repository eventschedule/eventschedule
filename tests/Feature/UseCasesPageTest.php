<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * /use-cases: "Find yours" (2026-10).
 *
 * The page is the parent of every audience page (each of them names it in its breadcrumb), and it
 * is whole without its script: the six groups are plain links, the stage beside them is drawn
 * showing the first of them, and the night below is drawn with all six places reached and each
 * side's picture under its words. The script only narrows the list to what is typed, re-casts the
 * stage, moves the six pictures into the event where there is room, and fills the six places as
 * their sides pass. So what goes wrong here goes wrong quietly: an audience page nothing links
 * to, a "Start for free" that opens the wrong kind of schedule, a name the script has no record
 * for, a sample dated last week, a word in a mock-up that the product never prints. Each test
 * below holds one of those.
 */
class UseCasesPageTest extends TestCase
{
    use RefreshDatabase;

    private function html(): string
    {
        return $this->get('/use-cases')->assertOk()->getContent();
    }

    /** The record the script is handed for each name, read back out of the page. */
    private function cards(string $html): array
    {
        $this->assertSame(1, preg_match('/var cards = (\[.*?\]);\n/s', $html, $match), 'The script was not handed its records');
        $cards = json_decode($match[1], true);
        $this->assertIsArray($cards, 'The records are not valid JSON');

        return $cards;
    }

    public function test_every_audience_page_is_linked_from_the_directory(): void
    {
        $html = $this->html();

        $pages = collect(File::files(resource_path('views/marketing')))
            ->map(fn ($file) => $file->getFilenameWithoutExtension())
            ->map(fn ($name) => str_replace('.blade', '', $name))
            ->filter(fn ($name) => str_starts_with($name, 'for-'))
            ->values();

        $this->assertGreaterThanOrEqual(41, $pages->count());

        foreach ($pages as $slug) {
            $this->assertStringContainsString('href="'.marketing_url('/'.$slug).'"', $html, "Nothing on /use-cases links to /{$slug}: the page would be an orphan under its own parent");
        }

        // The eighth of the communities is not a for- page.
        $this->assertStringContainsString('href="'.marketing_url('/community-event-calendar').'"', $html);
    }

    public function test_every_name_on_the_list_has_a_record_and_every_record_a_name(): void
    {
        $html = $this->html();
        $cards = $this->cards($html);

        preg_match_all('/data-uc-act="([^"]+)"/', $html, $acts);
        $listed = $acts[1];

        $this->assertSame(count($listed), count(array_unique($listed)), 'A name is on the list twice');
        $this->assertSame($listed, array_column($cards, 'key'), 'The list and the records the script is handed are not the same names in the same order');
        $this->assertCount(40, $cards);

        foreach ($cards as $card) {
            $this->assertNotSame('', $card['blurb'], $card['key'].' has no sentence for the stage');
            $this->assertNotEmpty($card['tags'], $card['key'].' has no specialities');
            $this->assertNotEmpty($card['find'], $card['key'].' answers to no words of its own: add them in config/marketing_directory.php');

            if ($card['kind'] === 'schedule') {
                $this->assertCount(3, $card['rows'], $card['key'].' needs three dates: the stage has three rows');
                $this->assertNotSame('', $card['who']);
                $this->assertMatchesRegularExpression('/^[a-z0-9](?:[a-z0-9-]{0,28}[a-z0-9])?$/', $card['slug'], $card['key'].' has a sample address no schedule could have');
            } else {
                $this->assertSame('for-ai-agents', $card['key'], 'Only the developer entry stages a request in place of a schedule');
            }
        }
    }

    public function test_every_speciality_is_printed_under_its_name(): void
    {
        $html = $this->html();

        // They are how a visitor recognises themselves, and what the field searches: visible at
        // every width, never folded away.
        $count = 0;
        foreach ($this->cards($html) as $card) {
            foreach ($card['tags'] as $tag) {
                $this->assertStringContainsString('<span>'.e($tag).'</span>', $html, "The speciality \"{$tag}\" is not printed under ".$card['name']);
                $count++;
            }
        }
        $this->assertGreaterThanOrEqual(220, $count);
    }

    public function test_start_for_free_opens_the_schedule_type_the_audience_s_own_page_opens(): void
    {
        $html = $this->html();

        foreach ($this->cards($html) as $card) {
            $view = resource_path('views/marketing/'.$card['key'].'.blade.php');
            $this->assertFileExists($view);

            preg_match_all('/sign_up\?type=([a-z]+)/', File::get($view), $own);
            $types = array_values(array_unique($own[1]));

            if ($types === []) {
                // A page whose own buttons name no type (the developer page) leaves the choice open.
                continue;
            }

            $this->assertCount(1, $types, $card['key'].' sends its own visitors to two kinds of schedule');
            $this->assertStringEndsWith('/sign_up?type='.$types[0], $card['start'], $card['key'].': the directory would open a different kind of schedule than the page itself does');
        }
    }

    public function test_the_stage_is_drawn_showing_the_first_name_with_no_script(): void
    {
        $html = $this->html();
        $first = $this->cards($html)[0];

        $this->assertSame('for-musicians', $first['key']);
        $this->assertStringContainsString('data-uc-act="for-musicians" aria-current="true"', $html);
        $this->assertSame(1, preg_match('/<div class="uc-stage" data-uc-stage data-accent="blue" data-kind="schedule">/', $html));
        $this->assertStringContainsString('<h3 class="uc-stage-name" data-uc="name">'.e($first['name']).'</h3>', $html);
        $this->assertStringContainsString('data-uc="start">', $html);
        $this->assertSame(1, preg_match('/<a href="([^"]+)"[^>]*data-uc="start"/', $html, $start));
        $this->assertSame($first['start'], html_entity_decode($start[1]));

        foreach ($first['rows'] as $row) {
            $this->assertStringContainsString('<strong data-uc-title>'.e($row['title']).'</strong>', $html);
        }
        $this->assertSame(3, preg_match_all('/<div class="uc-row" data-uc-row>/', $html));

        // The link beside it says where it goes, by name.
        $this->assertStringContainsString('<span data-uc="pageLabel">See the '.e($first['name']).' page</span>', $html);

        // The field is the script's: it must not be offered where nothing answers it, and the
        // six ways in must still be there when it is not.
        $this->assertStringContainsString('.uc-field { display: none; }', $html);
        $this->assertStringContainsString('#hp.uc-js .uc-field {', $html);
        $this->assertSame(6, preg_match_all('/<a href="#[a-z]+" data-accent="[a-z]+"><b>0[1-6]<\/b>/', $html), 'The six groups by number are the way round the list with no script');
        $this->assertStringNotContainsString('<form', substr($html, strpos($html, 'id="find"'), 8000));
    }

    public function test_the_samples_are_dated_in_the_week_to_come(): void
    {
        $html = $this->html();
        $monday = now()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addWeek();
        $this->assertTrue($monday->isFuture() || $monday->isToday());

        // Position in next week, Monday first, by what the tile prints.
        $place = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $monday->copy()->addDays($i);
            $place[$day->format('M').' '.$day->format('j')] = $i;
        }

        foreach ($this->cards($html) as $card) {
            $last = -1;
            foreach ($card['rows'] as $row) {
                $key = $row['month'].' '.$row['day'];
                $this->assertArrayHasKey($key, $place, $card['key'].' has a date outside next week');
                $this->assertGreaterThanOrEqual($last, $place[$key], $card['key'].': the sample dates are not in order');
                $last = $place[$key];
            }
        }
    }

    public function test_a_sample_says_what_a_schedule_s_own_page_would_say(): void
    {
        $html = $this->html();

        // role/partials/guest-row prints the price itself, "Free entry", "Few left" and "Sold
        // Out" beside a date. It never prints "Tickets" or "RSVP", so a sample must not.
        $words = ['free' => __('messages.free_entry', [], 'en'), 'few' => __('messages.few_left', [], 'en'), 'sold' => __('messages.sold_out', [], 'en')];
        foreach ($this->cards($html) as $card) {
            foreach ($card['rows'] as $row) {
                match ($row['pill']) {
                    '' => $this->assertSame('', $row['pillLabel']),
                    'price' => $this->assertMatchesRegularExpression('/^\$\d+$/', $row['pillLabel'], $card['key'].': a priced date shows its price'),
                    default => $this->assertSame($words[$row['pill']] ?? null, $row['pillLabel'], $card['key'].': "'.$row['pillLabel'].'" is not a word the product prints beside a date'),
                };
            }
        }

        // A sample ticket is somebody's own price. Printed beside the word "plan" or "month" it
        // would read as one of ours, which MarketingPriceTest forbids as a literal.
        $ours = collect(\App\Utils\PlatformPricing::all())->map(fn ($amount) => (int) $amount)->filter()->unique()->all();
        foreach (config('marketing_directory') as $path => $extra) {
            foreach ($extra['sample']['rows'] ?? [] as $row) {
                if (preg_match('/^\$(\d+)$/', $row[3], $price)) {
                    $this->assertNotContains((int) $price[1], $ours, "{$path}: a sample ticket costs what one of our plans costs");
                }
                $this->assertContains($row[4], ['', 'tickets', 'rsvp', 'free', 'few', 'sold', 'online'], "{$path}: a kind of date the page does not know");
                $this->assertLessThanOrEqual(26, mb_strlen($row[1]), "{$path}: \"{$row[1]}\" is too long for one line of the stage");
            }
        }

        // The mock-ups below use the product's words too.
        foreach (['buy_tickets', 'follow', 'show_all'] as $key) {
            $this->assertStringContainsString('>'.__('messages.'.$key, [], 'en').'<', $html, "The mock-ups no longer say messages.{$key} as the product does");
        }
    }

    public function test_the_night_is_drawn_with_all_six_places_reached_and_every_one_leads_somewhere(): void
    {
        $html = $this->html();

        preg_match_all('/<li class="is-on" data-uc-place="([a-z-]+)">\s*<a href="#([a-z-]+)">/', $html, $places);
        $this->assertSame(['the-act', 'the-room', 'the-guide', 'the-stream', 'the-town', 'the-code'], $places[1], 'With no script the six places must all be reached');
        $this->assertSame($places[1], $places[2]);
        $this->assertSame(6, preg_match_all('/<i class="is-on"><\/i>/', $html), 'The six dots that ride along on a phone are drawn reached too');

        preg_match_all('/<article id="([a-z-]+)" class="uc-side" data-accent="([a-z]+)" data-uc-side>/', $html, $sides);
        $this->assertSame($places[1], $sides[1], 'A place on the event leads to a side that is not there, or the sides are out of order');

        // Each side is its group's: the same colour as that group on the list above, and the
        // side a name's record carries is one of the six.
        preg_match_all('/<div id="[a-z]+" class="uc-group[^"]*" data-accent="([a-z]+)" data-uc-group>/', $html, $groups);
        $this->assertSame($groups[1], $sides[2]);
        foreach ($this->cards($html) as $card) {
            $this->assertContains($card['side'], $sides[1], $card['key'].' belongs to a side that is not on the page');
        }

        // With no script each side's picture stands under its words, inside its own side.
        foreach ($sides[1] as $id) {
            $start = strpos($html, '<article id="'.$id.'"');
            $end = strpos($html, '</article>', $start);
            $this->assertStringContainsString('data-uc-face="'.$id.'"', substr($html, $start, $end - $start), "The picture for #{$id} is not drawn inside its side");
        }
        // And the event's own picture area is empty until a script fills it.
        $this->assertSame(1, preg_match('/<div class="uc-screen" data-uc-screen><\/div>/', $html));

        // The last line and the way out are drawn, since with no script nothing would bring them.
        $this->assertSame(1, preg_match('/<p data-uc-now data-end="([^"]+)">\1<\/p>/', $html));

        // Every in-page link of the page's own lands on something.
        $own = substr($html, strpos($html, '<div id="hp">'));
        preg_match_all('/href="#([a-zA-Z][\w-]*)"/', $own, $anchors);
        $this->assertGreaterThanOrEqual(12, count(array_unique($anchors[1])));
        foreach (array_unique($anchors[1]) as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, "A link points at #{$id}, which is not on the page");
        }
    }

    public function test_the_list_in_the_page_s_schema_is_the_list_on_the_page(): void
    {
        $html = $this->html();

        preg_match_all('#<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $blocks);
        $nodes = collect($blocks[1])->map(fn ($json) => json_decode($json, true));
        $this->assertNotContains(null, $nodes->all(), 'A JSON-LD block on the page does not parse');

        $collection = $nodes->first(fn ($node) => ($node['@type'] ?? null) === 'CollectionPage');
        $this->assertNotNull($collection);
        $items = $collection['mainEntity']['itemListElement'];

        $this->assertSame(count($items), $collection['mainEntity']['numberOfItems']);
        // The forty names on the list and the two guides that head a group.
        $this->assertCount(42, $items);
        foreach ($items as $item) {
            $this->assertStringContainsString('href="'.$item['url'].'"', $html, 'The schema lists '.$item['name'].', which the page does not link');
        }
    }

    public function test_the_page_s_own_script_and_styles_keep_the_house_rules(): void
    {
        $source = File::get(resource_path('views/marketing/use-cases.blade.php'));

        // Every string a visitor reads is written by the server: the script moves text, it never
        // builds markup, and nothing on the page runs without end.
        $this->assertStringNotContainsString('innerHTML', $source);
        $this->assertStringNotContainsString('setInterval', $source);
        $this->assertStringNotContainsString('infinite', $source);
        $this->assertStringNotContainsString('<!--', $source, 'A note in the page is sent to every visitor: write it as a Blade comment');
        $this->assertStringNotContainsString("\u{2014}", $source);
        $this->assertSame(0, preg_match('/\son[a-z]+="/', $source), 'An inline handler does not run under the page\'s content policy');
        $this->assertSame(0, preg_match('/[\x{0300}-\x{036f}\x{2019}]/u', $source), 'A combining mark or a curly apostrophe typed raw into a pattern: write it as an escape');

        // What the script looks for is in the markup, and what the markup offers the script uses.
        preg_match_all('/\[data-uc-([a-z]+)[\]=]/', $source, $hooks);
        foreach (array_unique($hooks[1]) as $hook) {
            $this->assertSame(1, preg_match('/\sdata-uc-'.$hook.'[\s=>"]/', $source), "The script looks for data-uc-{$hook}, which the markup does not carry");
        }

        // Every part of the stage the script rewrites exists in the stage the server draws.
        foreach (['groupLine', 'badge', 'name', 'blurb', 'slug', 'initial', 'who', 'tagline', 'page', 'pageLabel', 'start'] as $part) {
            $this->assertStringContainsString('data-uc="'.$part.'"', $source, "The stage has lost its {$part}");
        }
    }
}
