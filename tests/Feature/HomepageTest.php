<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The marketing homepage, as rebuilt in 2026-10 ("one show, start to finish").
 *
 * The page is one Blade view whose moving parts are wired together by names: a claim box and the
 * links that carry its name, a switch and the mock-up strings it re-casts, two sliders and the
 * calculator fields they write into, a stylesheet that reorders a shared component by its shape.
 * None of that breaks loudly. A renamed attribute leaves a poster that never takes a name, a cast
 * that prints one string from the wrong venue, or a slider that moves nothing, and every other
 * test stays green. These hold the wiring.
 *
 * What the page SAYS is held elsewhere: MarketingHeroClaimTest (the fold), MarketingPriceTest and
 * MarketingTicketingTierTest (prices and plans), TicketFeesTest (the calculator's figures),
 * MarketingStructuredDataTest, ImageVariantsTest (the eager image budget), HeroExperimentTest.
 */
class HomepageTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function html(): string
    {
        return $this->get('/')->assertOk()->getContent();
    }

    private function xpath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();

        return new \DOMXPath($dom);
    }

    /** An XPath test for one class among several. */
    private function has(string $class): string
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")';
    }

    private function text(\DOMNode $node): string
    {
        return trim(preg_replace('/\s+/u', ' ', $node->textContent));
    }

    private function source(): string
    {
        return File::get(resource_path('views/marketing/index.blade.php'));
    }

    /** The casts the switch chooses between, as the page hands them to its script. */
    private function casts(string $html): array
    {
        $this->assertSame(1, preg_match('/var casts = (\{.*?\});\s/s', $html, $match), 'the page no longer hands its casts to the script');
        $casts = json_decode($match[1], true);
        $this->assertIsArray($casts, 'the casts are not valid JSON');

        return $casts;
    }

    // ------------------------------------------------------------- the name

    /**
     * initClaim() finds a box's own button as the first link in the box's parent, and a label that
     * has lost its field leaves the box without a name for a screen reader.
     */
    public function test_each_claim_box_has_a_labelled_field_and_its_own_sign_up_link(): void
    {
        $x = $this->xpath($this->html());
        $boxes = $x->query('//div['.$this->has('es-claim').']');

        $this->assertSame(2, $boxes->length, 'the page has a claim box beside the headline and one in the finale');

        foreach ($boxes as $box) {
            $input = $x->query('.//input[@type="text"]', $box)->item(0);
            $this->assertNotNull($input, 'a claim box has no text field');
            $id = $input->getAttribute('id');
            $this->assertNotSame('', $id);
            $this->assertSame(1, $x->query('//label[@for="'.$id.'"]')->length, "the field #{$id} has no label");

            $first = $x->query('.//a[@href]', $box->parentNode)->item(0);
            $this->assertNotNull($first, "the box around #{$id} has no button beside it");
            $this->assertStringContainsString('/sign_up', $first->getAttribute('href'),
                "the first link beside #{$id} is not the sign-up button, so a typed name would ride the wrong link");
        }
    }

    public function test_sign_up_links_away_from_the_boxes_carry_the_typed_name(): void
    {
        $html = $this->html();
        $x = $this->xpath($html);
        $followers = $x->query('//a[@data-claim-link]');

        // Wednesday's and Friday's buttons and the line under the events band. (The rail's last
        // card is a fourth, on an install that has events to show.)
        $this->assertGreaterThanOrEqual(3, $followers->length);
        $this->assertSame(2, $x->query('//article['.$this->has('hp-day').']//a[@data-claim-link]')->length,
            'the week has a sign-up button on Wednesday and one on Friday, and both carry the name');
        foreach ($followers as $link) {
            $this->assertStringContainsString('/sign_up', $link->getAttribute('href'));
        }

        // The calculator is a shared component, so its button is marked by the page's script.
        $this->assertStringContainsString("calcStart.setAttribute('data-claim-link', '')", $html);
        $this->assertSame(1, $x->query('//*['.$this->has('hp-calc').']//a[contains(@href, "/sign_up")]')->length,
            'the calculator no longer has exactly one sign-up button for the page to mark');
    }

    public function test_the_three_posters_wait_for_a_name_and_are_hidden_from_a_screen_reader(): void
    {
        $x = $this->xpath($this->html());
        $posters = $x->query('//*[@data-claim-poster]');

        $this->assertSame(3, $posters->length, 'a poster on the wall, one for phones and one in the finale');

        foreach ($posters as $poster) {
            $this->assertGreaterThan(0, $x->query('ancestor-or-self::*[@aria-hidden="true"]', $poster)->length,
                'a "your name" poster is read out, so its placeholder words are announced as content');
            $this->assertSame(1, $x->query('.//*[@data-claim-name="title"]', $poster)->length);
            $this->assertSame(1, $x->query('.//*[@data-claim-name="url"]', $poster)->length);
        }
    }

    /**
     * The box promises an address on THIS install. _base_domain() drops a leading www, and an
     * install served from www.example.com must not offer "your-name.www.example.com".
     */
    public function test_the_claim_box_names_the_installs_own_domain(): void
    {
        $this->pinAppUrl('https://www.showtimes.test');

        $html = $this->html();

        $this->assertStringContainsString('<span class="hp-claim-suffix">.showtimes.test</span>', $html);
        $this->assertStringNotContainsString('.www.showtimes.test', $html);
        $this->assertStringNotContainsString('blue-note</span>.eventschedule.com', $html,
            'a mock address bar is back on a hardcoded domain');
    }

    // ------------------------------------------------------------- the week

    /**
     * The week is always the week after next, worked out from today. Today's page before the
     * redesign printed "Tue Jul 15" and "Sat Jul 18", which are two different years.
     */
    public function test_the_week_is_one_real_week_whatever_today_is(): void
    {
        foreach (['2026-10-08', '2026-12-21', '2026-12-28', '2027-01-18', '2027-04-19'] as $today) {
            $this->travelTo(Carbon::parse($today.' 10:00:00'));

            $x = $this->xpath($this->html());
            $tiles = $x->query('//div['.$this->has('hp-date').']');
            $this->assertSame(6, $tiles->length, "on {$today}: six days, Monday to Sunday without Thursday");

            $dates = [];
            foreach ($tiles as $tile) {
                $shown = $this->text($x->query('./b', $tile)->item(0)).' '.$this->text($x->query('./i', $tile)->item(0)).' '.$this->text($x->query('./span', $tile)->item(0));
                $found = null;
                for ($day = 0; $day <= 28; $day++) {
                    $candidate = Carbon::now()->startOfDay()->addDays($day);
                    if ($candidate->format('D j M') === $shown) {
                        $found = $candidate;
                        break;
                    }
                }
                $this->assertNotNull($found, "on {$today}: \"{$shown}\" is not a real day in the next four weeks");
                $dates[] = $found;
            }

            $this->assertTrue($dates[0]->isMonday(), "on {$today}: the week does not start on a Monday");
            $this->assertTrue($dates[0]->gt(Carbon::now()), "on {$today}: the week has already begun");
            foreach ([0, 1, 2, 4, 5, 6] as $index => $offset) {
                $this->assertTrue($dates[$index]->isSameDay($dates[0]->copy()->addDays($offset)),
                    "on {$today}: tile {$index} is not day {$offset} of the same week");
            }

            // The booking page mock-up: whole weeks, and the one day picked is the week's Tuesday.
            $cells = $x->query('//div['.$this->has('hp-month').']/span');
            $this->assertGreaterThan(0, $cells->length);
            $this->assertSame(0, $cells->length % 7, "on {$today}: the booking month is not whole weeks");
            $picked = $x->query('//div['.$this->has('hp-month').']/span['.$this->has('is-pick').']');
            $this->assertSame(1, $picked->length, "on {$today}: exactly one day is picked");
            $this->assertSame((string) $dates[1]->day, $this->text($picked->item(0)));
        }
    }

    public function test_the_anchors_exist_and_friday_leads_to_the_calculator(): void
    {
        $x = $this->xpath($this->html());

        foreach (['top', 'showcase', 'features', 'how-it-works', 'appointments', 'open-source', 'fees', 'more-features', 'discover', 'integrations', 'claim'] as $id) {
            $this->assertSame(1, $x->query('//*[@id="'.$id.'"]')->length, "#{$id} is gone or doubled");
        }

        $this->assertSame(1, $x->query('//article['.$this->has('hp-day').']//a[@href="#fees"]')->length,
            'the week no longer has its link down to the fee calculator');
        $this->assertSame(1, $x->query('//section[@id="top"]//a[@href="#showcase"][@data-video-open]')->length,
            'the hero no longer has its link to the film');
    }

    // ------------------------------------------------------------- the casts

    /**
     * The switch writes cast[kind] into every [data-cast="kind"]. A kind no cast has leaves the
     * first cast's words on screen under another venue's name, with nothing in any log.
     */
    public function test_every_cast_can_fill_every_mock_up(): void
    {
        $html = $this->html();
        $casts = $this->casts($html);
        $x = $this->xpath($html);

        $this->assertSame(['jazz', 'comedy', 'yoga', 'festival'], array_keys($casts));

        $keys = array_keys($casts['jazz']);
        foreach ($casts as $name => $cast) {
            $this->assertSame($keys, array_keys($cast), "the {$name} cast does not have the same parts as the jazz cast");
        }

        $slots = $x->query('//*[@data-cast]');
        $this->assertGreaterThan(20, $slots->length);

        foreach ($slots as $slot) {
            $kind = $slot->getAttribute('data-cast');
            $this->assertContains($kind, $keys, "a mock-up asks for \"{$kind}\", which no cast has");
            // What the server prints is the jazz cast. A slot that says anything else flips the
            // moment somebody comes back to the first button.
            $this->assertSame($casts['jazz'][$kind], $this->text($slot), "the \"{$kind}\" slot is not printed from the jazz cast");
        }

        $buttons = $x->query('//button[@data-cast-pick]');
        $picks = [];
        foreach ($buttons as $button) {
            $picks[$button->getAttribute('data-cast-pick')] = $button->getAttribute('aria-pressed');
        }
        $this->assertSame(['jazz' => 'true', 'comedy' => 'false', 'yoga' => 'false', 'festival' => 'false'], $picks);
    }

    /**
     * CLAUDE.md: @json splits its argument on commas, and the default escaping is lost with the
     * second one. Every call in this view takes one value.
     */
    public function test_no_json_directive_is_handed_a_comma(): void
    {
        $source = $this->source();

        $this->assertStringContainsString('@json($hpCasts)', $source);

        $offset = 0;
        $calls = 0;
        while (($at = strpos($source, '@json(', $offset)) !== false) {
            $depth = 0;
            $argument = '';
            for ($i = $at + 5; $i < strlen($source); $i++) {
                $char = $source[$i];
                if ($char === '(') {
                    $depth++;
                    if ($depth === 1) {
                        continue;
                    }
                } elseif ($char === ')') {
                    $depth--;
                    if ($depth === 0) {
                        break;
                    }
                }
                $argument .= $char;
            }
            $this->assertStringNotContainsString(',', $argument, "@json({$argument}) is handed more than one argument");
            $offset = $at + 6;
            $calls++;
        }

        $this->assertGreaterThan(0, $calls);
    }

    // ------------------------------------------------------------- the walls and the room

    public function test_the_poster_wall_is_for_a_pointer_only(): void
    {
        // Only a real event's poster is a link; the stock photographs that fill an empty wall
        // are not, and a wall of those would pass this without being asked anything.
        foreach (range(1, 3) as $i) {
            $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Wall Room '.$i]);
            $this->createEvent($role, ['name' => 'Wall Session '.$i, 'flyer_image_url' => 'flyer_wall'.$i.'.png']);
        }

        $x = $this->xpath($this->html());
        $wall = $x->query('//div['.$this->has('es-wall').']')->item(0);

        $this->assertNotNull($wall);
        $this->assertSame('true', $wall->getAttribute('aria-hidden'));

        $links = $x->query('.//a['.$this->has('es-wall-card').']', $wall);
        $this->assertGreaterThan(0, $links->length, 'no event reached the wall, so there is no link to check');

        foreach ($links as $link) {
            $this->assertSame('-1', $link->getAttribute('tabindex'),
                'a poster inside the aria-hidden wall can be tabbed to: focus would land on something a screen reader cannot see');
        }
    }

    /**
     * Each column is its posters twice over, looping. A column topped up from its first poster
     * ended on the poster the next lap begins with, and the seam showed it twice running.
     */
    public function test_no_poster_stands_next_to_itself_on_the_wall(): void
    {
        $made = 0;

        foreach ([0, 3, 12, 25] as $wanted) {
            while ($made < $wanted) {
                $made++;
                $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Wall Room '.$made]);
                $this->createEvent($role, ['name' => 'Wall Session '.$made, 'flyer_image_url' => 'flyer_wall'.$made.'.png']);
            }

            $x = $this->xpath($this->html());
            $tracks = $x->query('//div['.$this->has('es-wall-track').']');
            $this->assertSame(4, $tracks->length, 'two columns to each wing');

            foreach ($tracks as $column => $track) {
                $posters = [];
                foreach ($x->query('.//img', $track) as $img) {
                    $posters[] = $img->getAttribute('src');
                }
                $this->assertGreaterThanOrEqual(14, count($posters), "with {$wanted} events column {$column} is too short to loop");

                foreach ($posters as $index => $poster) {
                    $next = $posters[($index + 1) % count($posters)];
                    $this->assertNotSame($poster, $next, "with {$wanted} events, column {$column} shows one poster twice running at {$index}");
                }
            }
        }
    }

    /**
     * One light for each of the show's 150 tickets, 142 of them on, lit in an order that is a
     * fixed sum of the seat number: the page is edge-cached, so nothing here may be random.
     */
    public function test_the_room_has_a_light_for_every_ticket(): void
    {
        $x = $this->xpath($this->html());
        $room = $x->query('//div['.$this->has('hp-room').']')->item(0);

        $this->assertNotNull($room);
        $this->assertSame('true', $room->getAttribute('aria-hidden'));

        $seats = $x->query('.//div['.$this->has('hp-crowd').']//i', $room);
        $this->assertSame(150, $seats->length);
        $this->assertSame(142, $x->query('.//div['.$this->has('hp-crowd').']//i['.$this->has('is-in').']', $room)->length);

        $order = [];
        foreach ($seats as $seat) {
            $this->assertSame(1, preg_match('/--i:\s*(\d+)/', $seat->getAttribute('style'), $match));
            $order[] = (int) $match[1];
        }
        sort($order);
        $this->assertSame(range(0, 149), $order, 'the order the lights come on in is not each of 0 to 149 once');

        $this->assertStringContainsString('142', $this->text($x->query('.//p['.$this->has('hp-room-cap').']', $room)->item(0)));
    }

    public function test_the_posters_behind_the_finale_cost_nothing_up_front(): void
    {
        $x = $this->xpath($this->html());
        $posters = $x->query('//div['.$this->has('hp-finale-wall').']//img');

        $this->assertSame(8, $posters->length);
        foreach ($posters as $poster) {
            $this->assertTrue($poster->hasAttribute('alt'));
            $this->assertSame('', $poster->getAttribute('alt'));
            $this->assertSame('lazy', $poster->getAttribute('loading'));
            $this->assertNotSame('high', $poster->getAttribute('fetchpriority'));
        }
    }

    // ------------------------------------------------------------- what it costs

    /**
     * The sliders are this page's; the sums are the shared calculator's. They meet at two number
     * fields the calculator owns, and the page's stylesheet hides, reorders and restyles the
     * calculator by the order of its children. So the shape it leans on is pinned here.
     */
    public function test_the_sliders_drive_the_shared_calculator(): void
    {
        $x = $this->xpath($this->html());
        $calc = $x->query('//*[@data-fee-calculator]');

        $this->assertSame(1, $calc->length);
        $calc = $calc->item(0);
        $this->assertStringContainsString('hp-calc', $calc->getAttribute('class'));

        foreach (['tickets' => 'data-fee-tickets', 'price' => 'data-fee-price'] as $kind => $attribute) {
            $range = $x->query('//input[@type="range"][@data-fee-range="'.$kind.'"]')->item(0);
            $this->assertNotNull($range, "the {$kind} slider is gone");
            $this->assertSame($calc->getAttribute($attribute), $range->getAttribute('value'),
                "the {$kind} slider does not start where the calculator was worked out");
            $this->assertSame(1, $x->query('//label[@for="'.$range->getAttribute('id').'"]')->length,
                "the {$kind} slider has no label of its own");
            $this->assertSame(0, $x->query('//label[.//output]')->length,
                'an <output> inside a label takes the label for itself and leaves the slider nameless');
            $this->assertSame(1, $x->query('.//input[@data-fee-input="'.$kind.'"]', $calc)->length,
                "the calculator has no {$kind} field for the slider to write into");
        }

        $children = [];
        foreach ($calc->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $children[] = $child;
            }
        }
        $this->assertCount(3, $children, 'the calculator is no longer its fields, its cards and its closing block');
        $this->assertSame(2, $x->query('.//input[@data-fee-input]', $children[0])->length, 'the first child is hidden here as the row of fields');
        $this->assertGreaterThanOrEqual(4, $x->query('.//*[@data-fee-total]', $children[1])->length, 'the second child is laid out here as the cards');
        $this->assertSame(1, $x->query('./p[1]//*[@data-fee-saving]', $children[2])->length, 'the closing block no longer opens with the sentence this page moves above the cards');
        $this->assertSame(1, $x->query('./a[contains(@href, "/sign_up")]', $children[2])->length);
    }

    /** TicketFees says which rates were read off a pricing page and which could not be. */
    public function test_the_homepage_compares_only_with_rates_that_were_checked(): void
    {
        $x = $this->xpath($this->html());
        $calc = $x->query('//*[@data-fee-calculator]')->item(0);

        $this->assertStringNotContainsStringIgnoringCase('re-checked', $this->text($calc));
        $this->assertSame('eventschedule', $x->query('.//*[@data-fee-total]', $calc)->item(0)->getAttribute('data-fee-total'));
    }

    /**
     * "Free forever" stands a few lines above a calculator whose first card is a monthly price.
     * The sentence between them is the FAQ's own, so the two cannot come to say different things.
     */
    public function test_the_note_about_priced_tickets_is_the_faqs_own_sentence(): void
    {
        $x = $this->xpath($this->html());
        $note = $this->text($x->query('//p['.$this->has('hp-figs-note').']')->item(0));

        $this->assertNotSame('', $note);

        $answers = [];
        foreach ($x->query('//*['.$this->has('faq-answer').']') as $answer) {
            $answers[] = $this->text($answer);
        }
        $this->assertNotEmpty(array_filter($answers, fn ($answer) => str_contains($answer, $note)),
            "the sentence under the three numerals is no longer in any FAQ answer:\n".$note);
    }

    // ------------------------------------------------------------- the rest

    public function test_the_bill_and_the_integrations_keep_every_name(): void
    {
        $x = $this->xpath($this->html());
        $names = $x->query('//ul['.$this->has('hp-bill-list').']/li/a');

        $this->assertSame(12, $names->length);
        foreach ($names as $name) {
            $this->assertNotSame('', $this->text($x->query('.//*['.$this->has('hp-bill-name').']', $name)->item(0)));
            $this->assertNotSame('', $this->text($x->query('.//*['.$this->has('hp-bill-desc').']', $name)->item(0)));
            $this->assertFalse($name->hasAttribute('aria-label'), 'a name on the bill is announced as something other than the name on the page');
        }
        $this->assertSame(2, $x->query('//ul['.$this->has('hp-bill-list').']/li['.$this->has('hp-bill-break').']')->length,
            'three sizes of name need two breaks');

        $this->assertSame(6, $x->query('//a['.$this->has('hp-plug').']')->length);
    }

    /** Structured data may only describe what a visitor can read on the page. */
    public function test_the_how_to_steps_are_days_of_the_week(): void
    {
        $html = $this->html();
        $steps = [];

        preg_match_all('#<script[^>]*application/ld\+json[^>]*>(.*?)</script>#s', $html, $blocks);
        foreach ($blocks[1] as $block) {
            $data = json_decode($block, true);
            foreach (($data['@graph'] ?? [$data]) as $node) {
                if (($node['@type'] ?? null) === 'HowTo') {
                    $steps = $node['step'];
                }
            }
        }
        $this->assertCount(3, $steps);

        $body = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#s', '', $html);
        $body = html_entity_decode(trim(preg_replace('/\s+/u', ' ', strip_tags($body))), ENT_QUOTES | ENT_HTML5);

        foreach ($steps as $step) {
            $this->assertStringContainsString($step['name'], $body);
            $this->assertStringContainsString($step['text'], $body);
        }
    }

    public function test_the_typeface_that_is_preloaded_is_one_that_exists(): void
    {
        $html = $this->html();

        $this->assertSame(1, preg_match('#<link rel="preload" as="font" type="font/woff2" href="([^"]+)" crossorigin>#', $html, $match),
            'the first screen is set in one font file, and it is no longer asked for early');

        $path = public_path(ltrim(parse_url($match[1], PHP_URL_PATH), '/'));
        $this->assertFileExists($path);
        $this->assertStringContainsString(basename($path), File::get(dirname($path).'/font.css'));
    }

    /** Everything this page animates on its own account has to stop when a visitor asks. */
    public function test_reduced_motion_stills_what_the_page_moves(): void
    {
        $source = $this->source();
        $at = strrpos($source, '@media (prefers-reduced-motion: reduce)');

        $this->assertNotFalse($at);
        $block = substr($source, $at, strpos($source, '</style>', $at) - $at);

        foreach (['.es-wall-track', '.hp-wing-l', '.hp-wing-r', '.hp-mine-plane', '.hp-beams', '.hp-crowd i'] as $selector) {
            $this->assertStringContainsString($selector, $block, "{$selector} moves and is not stilled under reduced motion");
        }
    }
}
