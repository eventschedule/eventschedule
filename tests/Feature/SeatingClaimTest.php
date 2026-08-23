<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Stops the marketing site telling people the product has no seat map.
 *
 * Twelve pages said exactly that, in twelve different voices, because for years it was true - the
 * /features/ticketing headline was literally "Name your prices. Not your seats." and the
 * /compare-single scorecard carried a "No seat maps" band telling a reserved-seating house to go
 * elsewhere. Allocated seating shipped and every one of those became a claim that talks a
 * qualified buyer out of the sale. The next audience page will be written from a sibling, so this
 * is the thing that fails when the sentence gets pasted forward.
 */
class SeatingClaimTest extends TestCase
{
    /**
     * Phrases that assert the absence outright. Each must now carry the Enterprise qualifier
     * instead - the tier IS still a real limit, and saying so is honest; saying nothing exists
     * is not.
     */
    private const STALE = [
        'there is no seat map',
        'there is no seating chart',
        'no seat maps',
        'not a seating chart',
        'there are also no seat maps',
        'no assigned seating',
    ];

    public function test_no_marketing_page_claims_the_product_has_no_seat_map(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views/marketing')) as $file) {
            $body = strtolower(File::get($file->getPathname()));
            $name = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());

            foreach (self::STALE as $phrase) {
                if (str_contains($body, $phrase)) {
                    $offenders[] = $name.': "'.$phrase.'"';
                }
            }
        }

        $this->assertSame([], $offenders,
            "Allocated seating exists (Enterprise). Say which plan it needs, do not say it does not exist:\n"
            .implode("\n", $offenders));
    }

    public function test_the_pricing_and_features_surfaces_list_it(): void
    {
        // It shipped as an Enterprise feature and for a while appeared on no pricing surface at
        // all, so a buyer comparing plans had no way to learn it existed.
        $pricing = $this->get(route('marketing.pricing'))->assertOk()->getContent();
        $this->assertStringContainsString('Allocated (reserved) seating', $pricing);

        $features = $this->get(route('marketing.features'))->assertOk()->getContent();
        $this->assertStringContainsString('Reserved seating', $features);
        $this->assertStringContainsString('Can buyers choose their own seat?', $features);
    }

    public function test_the_pages_a_seated_venue_reads_say_the_feature_exists(): void
    {
        // The surfaces a reserved-seating buyer actually lands on: the ticketing feature page, the
        // audience page for the venues that need it most, and one competitor comparison, whose
        // scorecard used to carry a "No seat maps" band sending exactly this buyer elsewhere.
        // Deleting the stale sentence without replacing it passes the test above and still loses
        // the sale, which is why this half exists.
        // Each must name the TIER, not merely mention seating: "seating plan" alone already
        // appears on pages that only talk about season passes, so matching on it would pass on a
        // page whose seating copy had been deleted rather than corrected.
        foreach (['marketing.ticketing', 'marketing.for_theaters', 'marketing.compare_eventbrite'] as $name) {
            $body = strtolower($this->get(route($name))->assertOk()->getContent());

            $mentionsSeating = str_contains($body, 'seating plan') || str_contains($body, 'seat map')
                || str_contains($body, 'seating chart') || str_contains($body, 'seat off')
                || str_contains($body, 'sell the seats') || str_contains($body, 'own seat');
            $this->assertTrue($mentionsSeating, "{$name} says nothing about seating at all");

            $offset = 0;
            $qualified = false;
            foreach (['seating plan', 'seat map', 'seating chart', 'own seat', 'sell the seats'] as $needle) {
                $at = strpos($body, $needle);
                if ($at === false) {
                    continue;
                }
                // The tier has to be within the same passage, not somewhere else on the page.
                $window = substr($body, max(0, $at - 700), 1400);
                if (str_contains($window, 'enterprise')) {
                    $qualified = true;
                    break;
                }
                $offset = $at;
            }

            $this->assertTrue($qualified,
                "{$name} mentions seating without saying it needs Enterprise (near offset {$offset})");
        }
    }
}
