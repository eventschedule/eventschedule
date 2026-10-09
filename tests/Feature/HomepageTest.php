<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The marketing homepage, as rebuilt in 2026-10 ("one show, start to finish"). Its features were
 * the days of that show's week at first; they are the three verbs of the headline and of the film
 * (plan, promote, sell) since later the same month, and since the round after that the acts are
 * lit as one day: the show is introduced beside the switch, Sell's first scene stands under an
 * evening sky, and the calculator is drawn as the show's takings and one bar to a platform.
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

        // The buttons under "Share your link" and "Ticketing" and the line under the events band.
        // (The rail's last card is a fourth, on an install that has events to show.)
        $this->assertGreaterThanOrEqual(3, $followers->length);
        $this->assertSame(2, $x->query('//article['.$this->has('hp-beat').']//a[@data-claim-link]')->length,
            'Promote has a sign-up button and Sell has one, and both carry the name');
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

    // ------------------------------------------------------------- the three acts

    /**
     * The features are the three verbs of the headline and of the film, in the film's order, two
     * features to an act. The card an act opens on, its tile in the margin and the links on the
     * card are three copies of the same facts, written by hand, so each is read back here.
     */
    public function test_the_features_are_three_acts_in_the_order_of_the_film(): void
    {
        $x = $this->xpath($this->html());
        $acts = $x->query('//section[@id="features"]/div['.$this->has('hp-verb').']');

        $this->assertSame(3, $acts->length, 'plan, promote and sell');

        $expected = [
            'plan' => ['Plan', ['how-it-works', 'appointments']],
            'promote' => ['Promote', ['share', 'grow']],
            'sell' => ['Sell', ['tickets', 'doors']],
        ];
        $n = 0;
        foreach ($expected as $id => [$word, $features]) {
            $act = $acts->item($n++);
            $this->assertSame($id, $act->getAttribute('id'), "act {$n} is not {$id}");

            $cards = $x->query('.//header['.$this->has('hp-vcard').']', $act);
            $this->assertSame(1, $cards->length, "{$id} does not open on one card");
            $card = $cards->item(0);
            $this->assertSame("Part {$n} of 3: {$word}.", $this->text($x->query('.//h3', $card)->item(0)));
            // The verb and the list start hidden where motion is allowed, and it is this
            // attribute that has the shared script show them.
            $this->assertTrue($card->hasAttribute('data-reveal'), "nothing would ever reveal the {$id} card's verb");
            $this->assertSame(1, $x->query('.//h3/span['.$this->has('sr-only').']', $card)->length);
            $index = $x->query('.//p['.$this->has('hp-vcard-ix').']', $card)->item(0);
            $this->assertSame("0{$n} / 03", $this->text($index));
            $this->assertSame('true', $index->getAttribute('aria-hidden'), 'the count is read out twice: the heading already says "Part n of 3"');
            $this->assertSame('true', $x->query('./span['.$this->has('hp-vbars').']', $card)->item(0)->getAttribute('aria-hidden'));
            $this->assertBarsReach($x, $card, $n, "the {$id} card");

            $ids = [];
            foreach ($x->query('.//article['.$this->has('hp-beat').']', $act) as $feature) {
                $ids[] = $feature->getAttribute('id');
                $this->assertSame(1, $x->query('.//h4['.$this->has('hp-h3').']', $feature)->length,
                    'a feature is titled one level under its act');
            }
            $this->assertSame($features, $ids, "{$id} does not hold its two features in order");

            // The card's running order: each link leads to one of the act's features, and is
            // called what that feature calls itself.
            $links = [];
            foreach ($x->query('.//a[@href]', $card) as $link) {
                $to = ltrim($link->getAttribute('href'), '#');
                $links[] = $to;
                $kicker = $this->text($x->query('//article[@id="'.$to.'"]//h4/span['.$this->has('hp-kicker').']')->item(0));
                $this->assertStringContainsString($this->text($link), $kicker, "the {$id} card calls #{$to} something its own heading does not");
            }
            $this->assertSame($features, $links, "the {$id} card's links do not lead to its own features");

            // One tile to each piece of the act (Sell has two pieces: the second is the night),
            // and it is the card again: the same bars, the same count, the same verb.
            $runs = $x->query('.//div['.$this->has('hp-run').']', $act);
            $this->assertSame($id === 'sell' ? 2 : 1, $runs->length);
            foreach ($runs as $run) {
                $tiles = $x->query('./div['.$this->has('hp-step').']', $run);
                $this->assertSame(1, $tiles->length, "a piece of {$id} has no tile of its own, or two");
                $tile = $tiles->item(0);
                $this->assertSame('true', $tile->getAttribute('aria-hidden'));
                $this->assertSame("0{$n} / 03", $this->text($x->query('./b', $tile)->item(0)));
                $this->assertSame($word, $this->text($x->query('./i', $tile)->item(0)));
                $this->assertBarsReach($x, $tile, $n, "a {$id} tile");
            }
            $this->assertSame($runs->length, $x->query('.//div['.$this->has('hp-step').']', $act)->length);

            // A phone has no margin for the tile: there an act's second feature names its act in
            // front of its own name, and its first, right under the card, does not.
            $this->assertSame(0, $x->query('.//article[@id="'.$features[0].'"]//*['.$this->has('hp-kicker-act').']')->length);
            $named = $x->query('.//article[@id="'.$features[1].'"]//h4//span['.$this->has('hp-kicker').']/span['.$this->has('hp-kicker-act').']');
            $this->assertSame(1, $named->length, "on a phone #{$features[1]} no longer says which act it is in");
            $this->assertSame($word, $this->text($named->item(0)));
            $this->assertSame('true', $named->item(0)->getAttribute('aria-hidden'));
        }

        // Sell ends on the night of the show, and the section ends with it: the band dawns into
        // the ground of the section underneath, which only holds while nothing stands between.
        $night = $x->query('//section[@id="features"]//div['.$this->has('hp-night').']');
        $this->assertSame(1, $night->length);
        $this->assertSame(1, $x->query('.//article[@id="doors"]', $night->item(0))->length, 'the door is no longer in the night');
        $this->assertSame(0, $x->query('following-sibling::*', $night->item(0))->length, 'something follows the night inside Sell');
        $this->assertSame(0, $x->query('//section[@id="features"]/div[@id="sell"]/following-sibling::*')->length,
            'something follows Sell inside the section');
        // The band's dawn is painted in the ground of an .hp-alt section (--hp-dawn), so that is
        // what has to come next, with nothing of any kind in between.
        $next = $x->query('//section[@id="features"]/following-sibling::*[1]')->item(0);
        $this->assertSame('section', $next->nodeName);
        $this->assertSame('open-source', $next->getAttribute('id'));
        $this->assertStringContainsString('hp-alt', $next->getAttribute('class'));
    }

    /** Three bars: the acts before this one are done, this one's is lit, the rest wait. */
    private function assertBarsReach(\DOMXPath $x, \DOMNode $in, int $act, string $where): void
    {
        $bars = $x->query('.//span['.$this->has('hp-vbars').']/i', $in);
        $this->assertSame(3, $bars->length, "{$where} does not have three bars");
        foreach ($bars as $at => $bar) {
            $this->assertSame($at < $act - 1 ? 'is-done' : ($at === $act - 1 ? 'is-now' : ''), $bar->getAttribute('class'),
                "bar {$at} of {$where}");
        }
    }

    /**
     * The card's bars, its verb and the tile that follows it move on their own account, and the
     * reduced-motion block at the foot of the stylesheet does not name them: what stills them is
     * that every one of those rules sits behind the motion gate (html.es-anim is only set where
     * motion is allowed, and never without JavaScript). A rule written without the gate would
     * also leave the verb hidden for a visitor with scripts off. The one thing left outside the
     * gate is the tile's on-and-off switch, which is a single step and moves nothing.
     */
    public function test_the_cards_own_motion_is_behind_the_motion_gate(): void
    {
        $css = substr($this->source(), 0, strpos($this->source(), '</style>'));
        $moving = 0;

        foreach (preg_split('/\R/', $css) as $line) {
            $rule = trim($line);
            $aboutTheCard = str_contains($rule, '.hp-vcard') || str_contains($rule, '.hp-vband') || str_contains($rule, '.hp-step');
            $moves = preg_match('/\btransition\b|:not\(\.is-revealed\)|scaleX\(0\)|animation-name:|animation: (?!hp-step-on steps\(1, end\))/', $rule) === 1;
            if (! $aboutTheCard || ! $moves || str_starts_with($rule, '.hp-vcard-has')) {
                continue;
            }
            $moving++;
            $this->assertStringStartsWith('html.es-anim', $rule, 'a rule that moves or hides part of an act card is not behind the motion gate');
        }
        $this->assertGreaterThanOrEqual(7, $moving, 'the rules this test reads have been renamed away from it');

        $this->assertStringContainsString('html.es-anim .hp-night .hp-run > .hp-step { animation-name: hp-step-in;', $css);
        $this->assertSame(1, preg_match('/@keyframes hp-step-on \{(.*?)\}\s*@keyframes/s', $css, $step));
        $this->assertStringNotContainsString('transform', $step[1], 'the switch a reduced-motion visitor gets has started to move');
    }

    /** The week is gone, and a day's name in a title would bring half of it back. */
    public function test_no_title_in_the_three_acts_names_a_day(): void
    {
        $html = $this->html();
        $x = $this->xpath($html);
        $titles = $x->query('//section[@id="features"]//*[self::h2 or self::h3 or self::h4]');

        $this->assertSame(10, $titles->length, 'the section, its three acts and their six features');
        foreach ($titles as $title) {
            $this->assertDoesNotMatchRegularExpression('/\b(Mon|Tues|Wednes|Thurs|Fri|Satur|Sun)day\b/', $this->text($title));
        }

        // The switch's label is written twice: by the server, and by the script that puts it back
        // when a typed name is cleared.
        $label = $this->text($x->query('//*[@data-cast-label]')->item(0));
        $this->assertSame(1, preg_match("/castLabel\\.textContent = typed \\? .+? : '([^']+)';/", $html, $match));
        $this->assertSame($label, $match[1]);
        $this->assertStringNotContainsStringIgnoringCase('week', $label);
    }

    /**
     * The show is always on the Saturday of the week after next and the appointment on the
     * Tuesday before it, worked out from today. The page before the 2026-10 redesign printed
     * "Tue Jul 15" and "Sat Jul 18", which are two different years.
     */
    public function test_the_show_and_the_booking_share_one_real_week_whatever_today_is(): void
    {
        // The fourth is a week whose Tuesday and Saturday fall in different months.
        foreach (['2026-10-08', '2026-12-21', '2026-12-28', '2027-03-15', '2027-04-19'] as $today) {
            $this->travelTo(Carbon::parse($today.' 10:00:00'));

            $x = $this->xpath($this->html());

            // The ticket is for the show: "Sat, Oct 24 · 8:00 PM".
            $ticket = $this->text($x->query('//div['.$this->has('hp-ticket-head').']/span')->item(0));
            $show = null;
            for ($day = 0; $day <= 28; $day++) {
                $candidate = Carbon::now()->startOfDay()->addDays($day);
                if (str_starts_with($ticket, $candidate->format('D, M j').' ')) {
                    $show = $candidate;
                    break;
                }
            }
            $this->assertNotNull($show, "on {$today}: \"{$ticket}\" is not a real day in the next four weeks");
            $this->assertTrue($show->isSaturday(), "on {$today}: the show is not on a Saturday");
            $this->assertTrue($show->copy()->startOfWeek(Carbon::MONDAY)->gt(Carbon::now()), "on {$today}: the show's week has already begun");

            // The poster and the event read off it say the same day.
            $this->assertSame($show->format('D j M'), $this->text($x->query('//div['.$this->has('hp-poster-top').']/span[2]')->item(0)));
            $this->assertSame($show->format('D, M j'), $this->text($x->query('//div['.$this->has('hp-fields').']/div[1]/span')->item(0)));
            // The show's own poster and ticket, beside the switch, say that day too.
            $this->assertSame($show->format('D, M j'), $this->text($x->query('//*[@data-cast-stage]//span['.$this->has('hp-stub-rows').']/span[2]/b')->item(0)));

            // The booking page mock-up: whole weeks, and the one day picked is that week's Tuesday.
            $booked = $show->copy()->subDays(4);
            $this->assertSame($booked->format('D, M j'), $this->text($x->query('//div['.$this->has('hp-slots').']/small')->item(0)));
            $this->assertSame($booked->format('F'), $this->text($x->query('//div['.$this->has('hp-month-head').']/strong')->item(0)));
            $cells = $x->query('//div['.$this->has('hp-month').']/span');
            $this->assertGreaterThan(0, $cells->length);
            $this->assertSame(0, $cells->length % 7, "on {$today}: the booking month is not whole weeks");
            $picked = $x->query('//div['.$this->has('hp-month').']/span['.$this->has('is-pick').']');
            $this->assertSame(1, $picked->length, "on {$today}: exactly one day is picked");
            $this->assertSame((string) $booked->day, $this->text($picked->item(0)));
        }
    }

    public function test_the_anchors_exist_and_ticketing_leads_to_the_calculator(): void
    {
        $x = $this->xpath($this->html());

        foreach (['top', 'showcase', 'features', 'plan', 'how-it-works', 'appointments', 'promote', 'share', 'grow', 'sell', 'tickets', 'doors', 'open-source', 'fees', 'more-features', 'discover', 'integrations', 'claim'] as $id) {
            $this->assertSame(1, $x->query('//*[@id="'.$id.'"]')->length, "#{$id} is gone or doubled");
        }

        $this->assertSame(1, $x->query('//article[@id="tickets"]//a[@href="#fees"]')->length,
            'ticketing no longer has its link down to the fee calculator');
        $this->assertSame(1, $x->query('//article['.$this->has('hp-beat').']//a[@href="#fees"]')->length);
        $this->assertSame(1, $x->query('//section[@id="top"]//a[@href="#showcase"][@data-video-open]')->length,
            'the hero no longer has its link to the film');
    }

    // ------------------------------------------------------------- one day

    /**
     * The show is introduced under the switch that re-casts it, so a press is answered where it is
     * made. The script swaps a poster's picture and marks the picture's PARENT plain for a cast
     * that has none, and it does so for every poster that carries one.
     */
    public function test_the_show_stands_under_the_switch_and_every_poster_can_be_recast(): void
    {
        $html = $this->html();
        $x = $this->xpath($html);

        $stage = $x->query('//section[@id="features"]//*[@data-cast-stage]');
        $this->assertSame(1, $stage->length, 'the show is no longer introduced, or is introduced twice');
        $stage = $stage->item(0);
        $this->assertSame('true', $stage->getAttribute('aria-hidden'));
        $this->assertTrue($stage->hasAttribute('data-reveal'));
        $this->assertSame(1, $x->query('preceding-sibling::div['.$this->has('hp-casts').']', $stage)->length,
            'the switch no longer stands with the show it re-casts');
        $this->assertGreaterThanOrEqual(8, $x->query('.//*[@data-cast]', $stage)->length);
        $this->assertSame(0, $x->query('.//*[self::h2 or self::h3 or self::h4 or self::a or self::button]', $stage)->length,
            'the show is a picture: it holds no heading and nothing that takes focus');
        // The ticket is the one scanned at the door: same number.
        $this->assertSame(
            $this->text($x->query('//div['.$this->has('hp-ticket-foot').']/span[2]')->item(0)),
            $this->text($x->query('.//span['.$this->has('hp-stub-foot').']/span[2]', $stage)->item(0)),
            'the ticket beside the switch and the ticket at the door carry different numbers');

        $pictures = $x->query('//img[@data-cast-img]');
        $this->assertSame(2, $pictures->length, 'the show\'s poster and the pasted poster');
        foreach ($pictures as $picture) {
            $this->assertStringContainsString('hp-poster', $picture->parentNode->getAttribute('class'),
                'the script marks a picture\'s parent plain, and that parent is no longer the poster');
            $this->assertSame('lazy', $picture->getAttribute('loading'));
        }
        $this->assertStringContainsString("document.querySelectorAll('[data-cast-img]')", $html);
        $this->assertStringContainsString("document.querySelector('[data-cast-stage]')", $html);

        // The line that says what the block is must be one a reader is given.
        $note = $x->query('//section[@id="features"]//p['.$this->has('hp-meet-note').']');
        $this->assertSame(1, $note->length);
        $this->assertSame(0, $x->query('ancestor-or-self::*[@aria-hidden="true"]', $note->item(0))->length);
    }

    /** Three lines of the pasted poster are marked, and each mark's number stands on the field it became. */
    public function test_the_poster_is_read_in_three_numbered_lines(): void
    {
        $x = $this->xpath($this->html());
        $scene = $x->query('//article[@id="how-it-works"]')->item(0);

        $marks = [];
        foreach ($x->query('.//div['.$this->has('hp-poster').']//*['.$this->has('hp-read').']', $scene) as $mark) {
            $marks[] = $mark->getAttribute('data-n');
        }
        $numbers = [];
        foreach ($x->query('.//div['.$this->has('hp-fields').']/div/b['.$this->has('hp-read-n').']', $scene) as $number) {
            $numbers[] = $this->text($number);
        }

        $this->assertSame(['1', '2', '3'], $marks);
        $this->assertSame($marks, $numbers, 'a line of the poster and the field it became carry different numbers');
        $this->assertSame(3, $x->query('.//div['.$this->has('hp-fields').']/div', $scene)->length);
    }

    /**
     * Sell's first scene stands in the evening, between the act's card and the night, and the
     * night follows it directly: the evening's sky ends in the night's own colour.
     */
    public function test_tickets_go_on_sale_in_the_evening_and_the_night_follows_it(): void
    {
        $x = $this->xpath($this->html());
        $evening = $x->query('//section[@id="features"]/div[@id="sell"]/div['.$this->has('hp-eve').']');

        $this->assertSame(1, $evening->length);
        $evening = $evening->item(0);
        $this->assertSame(1, $x->query('.//article[@id="tickets"]', $evening)->length);
        $this->assertStringContainsString('hp-vband', $x->query('preceding-sibling::*[1]', $evening)->item(0)->getAttribute('class'));
        $this->assertStringContainsString('hp-night', $x->query('following-sibling::*[1]', $evening)->item(0)->getAttribute('class'));
        $this->assertSame(0, $x->query('//div['.$this->has('hp-eve').']//div['.$this->has('hp-night').']')->length);
    }

    /**
     * The slip over the calculator is the two sliders multiplied and nothing else. The script
     * replaces the show's name with "Your show" on the first move, and finds the name as the
     * holder's one element child.
     */
    public function test_the_takings_are_the_two_sliders_multiplied(): void
    {
        $html = $this->html();
        $x = $this->xpath($html);
        $calc = $x->query('//*[@data-fee-calculator]')->item(0);
        $tickets = (int) $calc->getAttribute('data-fee-tickets');
        $price = (int) $calc->getAttribute('data-fee-price');

        $this->assertSame((string) $tickets, $this->text($x->query('//*[@data-fee-n]')->item(0)));
        $this->assertSame('$'.$price, $this->text($x->query('//*[@data-fee-p]')->item(0)));
        $this->assertSame('$'.number_format($tickets * $price), $this->text($x->query('//*[@data-fee-gross]')->item(0)));

        $who = $x->query('//*[@data-fee-who]');
        $this->assertSame(1, $who->length);
        $this->assertSame(1, $x->query('./*', $who->item(0))->length);
        $this->assertSame('event', $x->query('./*', $who->item(0))->item(0)->getAttribute('data-cast'));

        $this->assertStringContainsString("feeGross.textContent = '$' + grouped(count * price);", $html);
        // The calculator's own fields start on the sliders' numbers, whatever a browser restored.
        $this->assertStringContainsString('field.value = range.value;', $html);
    }

    /**
     * The page draws each card of the shared calculator as a row: its name, its rate, a bar and
     * its figure, found by their order and by what they hold. A card that grew a child, or lost
     * the holder of its figure, would be drawn wrong with no error.
     */
    public function test_the_calculator_has_the_shape_the_rows_are_drawn_from(): void
    {
        $x = $this->xpath($this->html());
        $cards = $x->query('//*[@data-fee-calculator]/div[2]/div');

        $this->assertSame(4, $cards->length);
        foreach ($cards as $at => $card) {
            $this->assertSame(3, $x->query('./div', $card)->length, 'a card is its name, its rate, and the holder of its figure and bar');
            $this->assertSame($at === 0 ? 1 : 0, $x->query('./span', $card)->length, 'only our own card carries a mark, and it is the card\'s one span');
            $holder = $x->query('./div[3]', $card)->item(0);
            $this->assertSame(1, $x->query('./*[@data-fee-total]', $holder)->length);
            $this->assertSame(1, $x->query('./div[last()]/*[@data-fee-bar]', $holder)->length);
            $this->assertSame(2, $x->query('./*', $holder)->length);
        }
        $this->assertSame('eventschedule', $x->query('.//*[@data-fee-bar]', $cards->item(0))->item(0)->getAttribute('data-fee-bar'));
    }

    /** The six names stand in two groups around the schedule, each group named by a caption a reader is given. */
    public function test_the_six_integrations_are_wired_to_the_schedule_in_two_groups(): void
    {
        $x = $this->xpath($this->html());
        $lists = $x->query('//section[@id="integrations"]//ul['.$this->has('hp-plugs').']');

        $this->assertSame(2, $lists->length);
        $counts = [];
        foreach ($lists as $list) {
            $caption = $x->query('//*[@id="'.$list->getAttribute('aria-labelledby').'"]');
            $this->assertSame(1, $caption->length, 'a group of integrations is named by a caption that is not on the page');
            $this->assertNotSame('', $this->text($caption->item(0)));
            $counts[] = $x->query('./li/a['.$this->has('hp-plug').']', $list)->length;
        }
        $this->assertSame([4, 2], $counts, 'four calendars and two ways to be paid');

        $hub = $x->query('//section[@id="integrations"]//div['.$this->has('hp-hub').']');
        $this->assertSame(1, $hub->length);
        $this->assertSame('true', $hub->item(0)->getAttribute('aria-hidden'));
        $this->assertSame(0, $x->query('.//a | .//button', $hub->item(0))->length);
    }

    /**
     * The three things in "One link. Everywhere." each carry their own name, and the stage they
     * stand on is a picture: nothing in it takes focus.
     */
    public function test_each_way_out_is_named_on_the_thing_itself(): void
    {
        $x = $this->xpath($this->html());
        $outs = $x->query('//article[@id="share"]//div['.$this->has('hp-out').']');

        $this->assertSame(3, $outs->length);
        $names = [];
        foreach ($outs as $out) {
            $chip = $x->query('./span['.$this->has('hp-chip').']', $out);
            $this->assertSame(1, $chip->length);
            $names[] = $this->text($chip->item(0));
        }
        sort($names);
        $this->assertSame(['Embed on your site', 'Link in bio', 'QR poster'], $names);
        $this->assertSame(0, $x->query('//article[@id="share"]//div['.$this->has('hp-obj').']//*[self::a or self::button or self::input]')->length);
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
        $this->assertSame(1, $x->query('./p[1]//*[@data-fee-saving]', $children[2])->length, 'the closing block no longer opens with the sentence this page sets beside its button');
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
    public function test_the_how_to_steps_are_features_a_visitor_can_read(): void
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
