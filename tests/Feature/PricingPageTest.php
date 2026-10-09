<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Utils\PlanRateCard;
use App\Utils\PlatformPricing;
use App\Utils\TicketFees;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /pricing: "Show the cut" (2026-10).
 *
 * The page is whole without its script. Every list, row, price and figure is drawn by the server,
 * and the script only lets a visitor tick what they need and move two numbers. So what can go
 * wrong here goes wrong quietly: a need that marks a plan the rate card does not give it, a line
 * of a curated list that is reworded and stops lighting, a ticket whose parts no longer add up to
 * its price, a figure the fee formula would not print, a trial length typed where the app has a
 * setting. Each test below holds one of those.
 *
 * What the page may say about prices is MarketingPriceTest's; its totals and rates are
 * TicketFeesTest's; the compare table's rows are PlanRateCardTest's.
 */
class PricingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        LegalDocument::create([
            'type' => LegalDocument::COOKIES,
            'content' => 'We use cookies to keep you signed in.',
        ]);
        PlatformPricing::flush();
    }

    protected function tearDown(): void
    {
        PlatformPricing::flush();

        parent::tearDown();
    }

    private function page(): string
    {
        return $this->get('/pricing')->assertOk()->getContent();
    }

    /** The page without its scripts and styles: what a visitor is shown. */
    private function markup(string $html): string
    {
        return preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);
    }

    private function dollars(string $printed): float
    {
        return (float) str_replace(['$', ',', '−'], ['', '', '-'], $printed);
    }

    public function test_every_need_marks_the_plan_the_rate_card_gives_it(): void
    {
        $html = $this->markup($this->page());
        $rows = [];
        foreach (PlanRateCard::rows() as $row) {
            $rows[$row[0]] = $row;
        }

        preg_match_all('/<button[^>]*class="pr-chip"[^>]*data-pr-need="([a-z]+)" data-pr-plan="([a-z]+)"/', $html, $chips, PREG_SET_ORDER);
        $this->assertCount(7, $chips, 'A need whose row left the rate card is dropped without a word: seven are offered');
        $this->assertSame(['free' => 1, 'pro' => 3, 'enterprise' => 3], array_count_values(array_column($chips, 2)));

        foreach ($chips as [, $need, $plan]) {
            // The row the need is decided by, and the plan that row gives it: the first one
            // whose cell is not Free's.
            $this->assertSame(1, preg_match('/<th[^>]*data-pr-row="'.$need.'"[^>]*>([^<]+)<\/th>/', $html, $label), "the compare table has no row for the need \"{$need}\"");
            $row = $rows[html_entity_decode(trim($label[1]), ENT_QUOTES)] ?? null;
            $this->assertNotNull($row, "the row for \"{$need}\" is not the rate card's");
            // Free where Free's own cell says yes; otherwise the first plan whose cell is not Free's.
            $expected = PlanRateCard::includes($row[1]) ? 'free' : ($row[2] !== $row[1] ? 'pro' : 'enterprise');
            $this->assertSame($expected, $plan, "\"{$need}\" marks a plan the rate card does not give it");
            if ($expected !== 'free') {
                $this->assertNotSame($row[3], $row[1], "\"{$need}\" is on a row no plan changes");
            }

            // The line of that plan's curated list that says the same thing is lit with it.
            $this->assertSame(1, preg_match('/<article id="plan-'.$plan.'".*?<\/article>/s', $html, $card));
            $this->assertSame(1, substr_count($card[0], 'data-pr-line="'.$need.'"'), "no line of the {$plan} list answers the need \"{$need}\": was a curated line reworded?");
            $this->assertSame(1, substr_count($html, 'data-pr-line="'.$need.'"'));
        }

        // A Pro need picked beside an Enterprise one is answered by Enterprise's first line.
        $this->assertSame(1, preg_match('/<li[^>]*data-pr-carries="pro"[^>]*>([^<]+)<\/li>/', $html, $carries));
        $this->assertSame('Everything in Pro', trim($carries[1]));

        // The rows that step back once a plan is marked are the ones every plan answers alike.
        $alike = count(array_filter(PlanRateCard::rows(), fn ($row) => $row[1] === $row[2] && $row[2] === $row[3]));
        $this->assertGreaterThan(0, $alike);
        $this->assertSame($alike, substr_count($html, ' data-pr-same'));
    }

    public function test_the_page_is_whole_without_its_script(): void
    {
        $html = $this->page();
        $markup = $this->markup($html);

        // One word says the script is here, and the controls that need it are hidden by it alone.
        $this->assertStringContainsString("page.classList.add('pr-js');", $html);
        $this->assertStringContainsString('#hp:not(.pr-js) .pr-only-js { display: none !important; }', $html);
        foreach (['class="pr-bar pr-only-js"', 'class="pr-dials pr-only-js"', 'class="pr-field pr-against pr-only-js"'] as $gated) {
            $this->assertStringContainsString($gated, $markup);
        }
        // What stands in their place says the example the figures are for.
        $this->assertMatchesRegularExpression('/<p class="pr-still">[\d,]+ tickets at \$[\d.]+, set against [A-Za-z. ]+<\/p>/', $markup);

        // The three lists and the table ship open; the script folds them on a phone.
        $this->assertSame(2, substr_count($markup, '<details class="plan-disc pr-holds" open>'));
        $this->assertStringContainsString('<details id="compare" class="plan-disc pr-compare" open>', $markup);
        foreach (['free' => 10, 'pro' => 10, 'enterprise' => 10] as $plan => $atLeast) {
            $this->assertSame(1, preg_match('/<article id="plan-'.$plan.'".*?<\/article>/s', $markup, $card));
            $this->assertGreaterThanOrEqual($atLeast, substr_count($card[0], '<li'), "the {$plan} list is cut short");
        }

        // Nothing is marked as anybody's plan, and one answer of each kind is showing.
        $this->assertStringNotContainsString('data-pr-yours', $markup);
        $this->assertSame(1, preg_match_all('/data-pr-say="[a-z]+"(?! hidden)/', $markup));
        foreach (['pr-verdict', 'pr-ahead'] as $block) {
            $this->assertSame(1, preg_match('/<p class="'.$block.'">(.*?)<\/p>/s', $markup, $lines));
            $this->assertSame(1, preg_match_all('/<span data-pr-[a-z-]+="[a-z]+"\s*>/', $lines[1]), "{$block} must show exactly one of its sentences");
        }

        // Every hidden resting state is behind the motion gate, and nothing loops.
        preg_match_all('/^\s*([^{}\n]*:not\(\.is-revealed\)[^{}\n]*)\{/m', $html, $rules);
        $this->assertNotEmpty($rules[1]);
        foreach ($rules[1] as $selector) {
            $this->assertStringStartsWith('html.es-anim ', trim($selector), 'A pre-reveal state outside the motion gate: '.trim($selector));
        }
        preg_match('/<section id="top".*<section id="terms".*?<\/section>/s', $html, $own);
        $this->assertDoesNotMatchRegularExpression('/\binfinite\b|setInterval/', $own[0] ?? '');
    }

    public function test_one_ticket_adds_up_and_the_night_settles(): void
    {
        $markup = $this->markup($this->page());

        $this->assertSame(1, preg_match('/data-fee-tickets="(\d+)" data-fee-price="([\d.]+)"/', $markup, $event));
        [$tickets, $price] = [(int) $event[1], (float) $event[2]];
        $this->assertSame(1, preg_match('/data-pr-rival="([a-z-]+)"/', $markup, $rival));

        $figure = function (string $attribute, string $key) use ($markup) {
            $this->assertGreaterThanOrEqual(1, preg_match_all('/'.$attribute.'="'.$key.'"[^>]*>(−?\$[\d,]+\.\d{2})</u', $markup, $found), "no figure printed for {$key}");
            $this->assertCount(1, array_unique($found[1]), "{$key} is printed as two different figures");

            return $this->dollars($found[1][0]);
        };

        foreach (['theirs' => $rival[1], 'ours' => 'eventschedule'] as $side => $platform) {
            $total = TicketFees::cost($platform, $tickets, $price);

            // One ticket: what you keep, what the platform takes and card processing are its price.
            $this->assertEqualsWithDelta(
                $price,
                $figure('data-pr-each', "{$side}-keep") + $figure('data-pr-each', "{$side}-take") + $figure('data-pr-each', "{$side}-processing"),
                0.0001,
                "the three figures under the {$side} ticket do not add up to its price"
            );
            $this->assertEqualsWithDelta(($tickets * $price - $total) / $tickets, $figure('data-pr-each', "{$side}-keep"), 0.005);

            // The night: its four lines come to what you keep, and what you keep is TicketFees'.
            $keep = $figure('data-pr-cell', "{$side}-keep");
            $this->assertEqualsWithDelta($tickets * $price - $total, $keep, 0.005, "{$side}: \"You keep\" is not the formula's");
            $this->assertEqualsWithDelta(
                $keep,
                $figure('data-pr-cell', 'gross') + $figure('data-pr-cell', "{$side}-fees") + $figure('data-pr-cell', "{$side}-plan") + $figure('data-pr-cell', "{$side}-processing"),
                0.0001,
                "the {$side} column of the settlement does not add up"
            );

            // The drawing: the two shares cut from the ticket are the total, as a part of the sale.
            $this->assertSame(1, preg_match('/data-pr-tk="'.$side.'".*?style="--proc: ([\d.]+); --plat: ([\d.]+);/s', $markup, $shares));
            $this->assertEqualsWithDelta($total / ($tickets * $price) * 100, (float) $shares[1] + (float) $shares[2], 0.001, "the {$side} ticket is not drawn to scale");
        }

        // We never take a fee on a ticket: that line is a nought in our column whatever the event.
        $this->assertSame(0.0, $figure('data-pr-cell', 'ours-fees'));

        // "You are ahead from ticket no. N": the first count at which the other platform costs more.
        $this->assertSame(1, preg_match('/data-pr-ahead>([\d,]+)</', $markup, $ahead));
        $first = null;
        for ($count = 1; $count <= $tickets; $count++) {
            if (TicketFees::cost($rival[1], $count, $price) > TicketFees::cost('eventschedule', $count, $price)) {
                $first = $count;
                break;
            }
        }
        $this->assertSame($first, (int) str_replace(',', '', $ahead[1]));

        // The hero's sentence is the calculator's own two totals, and so is the roll of tickets.
        $short = fn (float $amount) => round($amount, 2) == round($amount) ? number_format($amount) : number_format($amount, 2);
        $theirTotal = TicketFees::cost($rival[1], $tickets, $price);
        $ourTotal = TicketFees::cost('eventschedule', $tickets, $price);
        $this->assertSame(1, preg_match('/costs you <b>\$([\d,.]+)<\/b>\. Here the same night costs <b>\$([\d,.]+)<\/b>\./', $markup, $thesis), 'the hero no longer states the two totals');
        $this->assertSame([$short($theirTotal), $short($ourTotal)], [$thesis[1], $thesis[2]]);

        foreach (['theirs' => $theirTotal, 'ours' => $ourTotal] as $side => $total) {
            $this->assertSame(1, preg_match('/data-pr-worth="'.$side.'">([\d,]+)</', $markup, $worth));
            $this->assertSame((int) min($tickets, ceil(round($total / $price, 6))), (int) str_replace(',', '', $worth[1]), "the roll counts the wrong number of tickets for {$side}");
            $this->assertSame(1, preg_match('/data-pr-roll="'.$side.'" style="--w: ([\d.]+);"/', $markup, $rolled));
            $this->assertEqualsWithDelta($total / ($tickets * $price) * 100, (float) $rolled[1], 0.001, "the {$side} roll is not torn to scale");
        }
    }

    public function test_only_platforms_with_a_checked_rate_are_set_against_us(): void
    {
        $markup = $this->markup($this->page());
        $rates = TicketFees::rates();

        preg_match_all('/data-pr-pick="([a-z-]+)">([^<]+)</', $markup, $picks, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(4, count($picks));
        $this->assertSame(1, preg_match('/data-pr-rivals="([^"]*)"/', $markup, $attribute));
        $info = json_decode(html_entity_decode($attribute[1], ENT_QUOTES | ENT_HTML5), true);
        $this->assertSame(1, preg_match('/data-rates="([^"]*)"/', $markup, $attribute));
        $handed = json_decode(html_entity_decode($attribute[1], ENT_QUOTES | ENT_HTML5), true);

        foreach ($picks as [, $key, $name]) {
            // What the script says about a platform is TicketFees' own words for it.
            $this->assertSame($rates[$key]['name'], html_entity_decode($name, ENT_QUOTES));
            $this->assertSame(['name' => $rates[$key]['name'], 'label' => $rates[$key]['label'], 'basis' => $rates[$key]['basis']], $info[$key] ?? null);
            $this->assertArrayHasKey($key, $handed, "{$key} can be picked and the script has no rate for it");
            $this->assertStringNotContainsString('not re-checked', $rates[$key]['label'], "{$key} is offered although its rate could not be re-checked");
        }

        $this->assertSame(array_merge(['stripe', 'eventschedule'], array_column($picks, 1)), array_keys($handed));
    }

    public function test_the_trial_is_as_long_as_the_app_says(): void
    {
        config(['app.trial_days' => 9]);
        $markup = $this->markup($this->page());

        $this->assertSame(2, substr_count($markup, '<p class="pr-trial">9-day free trial</p>'));
        $this->assertStringContainsString('9 days free, then', $markup);
        $this->assertStringContainsString('you get a 9-day free trial, once per schedule', $markup);
        $this->assertStringContainsString('9 days to try a plan.', $markup);
        preg_match('/<section id="top".*<section id="claim".*?<\/section>/s', $markup, $own);
        $this->assertDoesNotMatchRegularExpression('/\b7[- ]days?\b/i', $own[0] ?? $markup, 'a trial length is typed into the page');
    }

    public function test_a_price_is_said_both_ways_wherever_the_toggle_reaches(): void
    {
        config([
            'services.stripe_platform.price_monthly_amount' => '7',
            'services.stripe_platform.price_yearly_amount' => '70',
            'services.stripe_platform.enterprise_price_monthly_amount' => '21',
            'services.stripe_platform.enterprise_price_yearly_amount' => '210',
            // The trial's length is the installation's (TRIAL_DAYS), and the sentence below names
            // it: .env.example ships 365, which is what CI runs with, and a machine with no value
            // has 7. Said here so the sentence is the same on both.
            'app.trial_days' => 7,
        ]);
        PlatformPricing::flush();
        $markup = $this->markup($this->page());

        // The sentence under Pro's button used to name the monthly price whichever was chosen.
        $this->assertSame(1, preg_match('/<article id="plan-pro".*?<\/article>/s', $markup, $pro));
        $this->assertStringContainsString('<p class="bt-note-month">7 days free, then '.plan_price(7).' a month.', $pro[0]);
        $this->assertStringContainsString('<p class="bt-note-year">7 days free, then '.plan_price(70).' a year.', $pro[0]);

        // And the answer over the cards follows the toggle too.
        foreach ([[7, 70], [21, 210]] as [$monthly, $yearly]) {
            $this->assertStringContainsString('<span class="bt-period-month">'.plan_price($monthly).' a month</span><span class="bt-period-year">'.plan_price($yearly).' a year</span>', $markup);
        }
        $this->assertStringContainsString('per schedule</a>', $pro[0]);
    }

    public function test_the_small_print_agrees_with_the_rate_card(): void
    {
        $markup = $this->markup($this->page());

        foreach (PlanRateCard::rows() as $row) {
            if (str_starts_with($row[0], 'Newsletter emails')) {
                $this->assertStringContainsString("{$row[1]}, {$row[2]} or {$row[3]} newsletter emails a month means recipients, not sends", $markup);
            }
        }
        // The clause a price's "per schedule" leads to.
        $this->assertSame(1, preg_match('/<li[^>]*id="per-schedule"[^>]*>\s*<h3>A plan belongs to one schedule\.<\/h3>/', $markup));
    }

    public function test_every_link_lands_and_the_old_anchors_stand(): void
    {
        $html = $this->page();

        preg_match_all('/href="#([A-Za-z0-9_-]+)"/', $html, $links);
        preg_match_all('/\sid="([A-Za-z0-9_-]+)"/', $html, $ids);
        $this->assertSame([], array_values(array_diff(array_unique($links[1]), $ids[1])), 'links on /pricing that go to nothing');
        $this->assertSame([], array_keys(array_filter(array_count_values($ids[1]), fn ($count) => $count > 1)), 'ids used twice');

        // Bookmarks, /features and the review of the page name these.
        foreach (['top', 'pricing-plans', 'fees', 'selfhost', 'compare', 'faq', 'claim'] as $id) {
            $this->assertSame(1, substr_count($html, ' id="'.$id.'"'), "#{$id} must exist once");
        }
        // An anchor that is not a section needs its own room under the fixed bar.
        $this->assertMatchesRegularExpression('/#hp \.pr-card,\s*#hp \.pr-own,\s*#hp \.pr-compare,\s*#hp \.pr-print li \{ scroll-margin-top:/', $html);
    }

    public function test_the_page_is_the_same_for_everyone_and_keeps_the_house_rules(): void
    {
        // Cached at the edge: nothing in the page's own part may differ between two renders.
        $own = fn (string $html) => preg_match('/<section id="top".*<section id="claim"/s', $html, $part) ? preg_replace('/nonce="[^"]*"/', '', $part[0]) : null;
        $first = $own($this->page());
        $this->assertNotNull($first);
        $this->assertSame($first, $own($this->page()));

        // Typed values reach the page as text.
        $this->assertStringNotContainsString('innerHTML', $first);
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+="/', $first, 'an inline handler, which the content security policy refuses');

        $source = file_get_contents(resource_path('views/marketing/pricing.blade.php'));
        $this->assertStringNotContainsString("\u{2014}", $source, 'an em-dash');
        $this->assertDoesNotMatchRegularExpression('/self-host(?!ing)/i', $source);
        $this->assertStringNotContainsString('<!--', $source, 'an HTML comment is sent to every visitor: use a Blade comment');
    }
}
