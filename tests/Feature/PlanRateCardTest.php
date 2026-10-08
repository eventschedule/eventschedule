<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Utils\PlanRateCard;
use App\Utils\PlatformPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /faq prints a rate card and /pricing prints "Compare plans". They are the same table, one row
 * per question, and a visitor who reads both must not find them disagreeing about a tier. So
 * both read App\Utils\PlanRateCard, and what each page RENDERS is held to that list here: a row
 * typed into either view, or a cell changed in one of them, fails the first test.
 */
class PlanRateCardTest extends TestCase
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

    /** The cells of one table on a page, row by row, as the visitor reads them. */
    private function table(string $path, string $class): array
    {
        $html = $this->get($path)->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<table class="'.preg_quote($class, '/').'".*?<\/table>/s', $html, $table),
            "{$path} has no {$class} table");

        preg_match_all('/<tr role="row">(.*?)<\/tr>/s', $table[0], $rows);

        $cells = [];
        foreach (array_slice($rows[1], 1) as $row) {
            preg_match_all('/<t[hd]\b[^>]*>(.*?)<\/t[hd]>/s', $row, $found);
            $cells[] = array_map(fn ($cell) => html_entity_decode(trim(strip_tags($cell)), ENT_QUOTES), $found[1]);
        }

        return $cells;
    }

    public function test_the_faq_and_the_pricing_page_print_the_same_rows(): void
    {
        $rows = PlanRateCard::rows();

        $this->assertSame($rows, $this->table('/faq', 'es-desk-rate'));
        $this->assertSame($rows, $this->table('/pricing', 'pc-table'));
    }

    public function test_a_limit_never_takes_the_affirmative_ink(): void
    {
        foreach (['No', '10', '1,000', '1', '1 type', 'Up to 5'] as $limit) {
            $this->assertFalse(PlanRateCard::includes($limit), "{$limit} reads as an inclusion");
        }

        foreach (['Yes', 'Unlimited', 'Unlimited types', 'Zero', plan_price(5).' / month or '.plan_price(50).' / year', plan_price(0).', permanently'] as $inclusion) {
            $this->assertTrue(PlanRateCard::includes($inclusion), "{$inclusion} reads as a limit");
        }
    }

    public function test_the_prices_follow_the_installation(): void
    {
        config(['services.stripe_platform.price_monthly_amount' => '7', 'services.stripe_platform.price_yearly_amount' => '70']);
        PlatformPricing::flush();

        $this->assertSame(plan_price(7).' / month or '.plan_price(70).' / year', PlanRateCard::rows()[0][2]);
    }
}
