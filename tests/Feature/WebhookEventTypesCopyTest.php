<?php

namespace Tests\Feature;

use App\Models\Webhook;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Keeps the marketing site's webhook copy tied to Webhook::EVENT_TYPES.
 *
 * Two failure modes, both of which had actually happened. Prose across nine places was updated
 * from "twelve event types" to "fourteen" while the two arrays those headings sit directly above
 * were left at twelve, so both pages counted a list and then rendered a different one. And the
 * developer reference table listed the types by hand, where a new type can simply be forgotten.
 *
 * Source scanning rather than rendering: these pages are nexus-gated, and the point is to catch
 * the omission in review, not to assert on markup.
 */
class WebhookEventTypesCopyTest extends TestCase
{
    /**
     * Pages that render one entry per event type. Every type must appear literally in the source.
     */
    private const LISTING_PAGES = [
        'marketing/integrations.blade.php',
        'marketing/for-ai-agents.blade.php',
        'marketing/docs/developer/webhooks.blade.php',
    ];

    /** Enough of the ramp to cover any plausible list length. */
    private const NUMBER_WORDS = [
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        20 => 'twenty',
    ];

    private function source(string $relative): string
    {
        $path = resource_path('views/'.$relative);
        $this->assertFileExists($path);

        return File::get($path);
    }

    /**
     * The regression: a page that lists the types must list all of them.
     */
    public function test_every_event_type_appears_on_the_pages_that_list_them(): void
    {
        foreach (self::LISTING_PAGES as $page) {
            $source = $this->source($page);

            foreach (Webhook::EVENT_TYPES as $type) {
                $this->assertStringContainsString(
                    $type,
                    $source,
                    "{$page} lists webhook event types but is missing {$type}"
                );
            }
        }
    }

    /**
     * Any page that states the count in words has to state the right one. This is the half that
     * drifted: the prose was updated and the list underneath it was not, so the two disagreed on
     * the same screen.
     */
    public function test_the_spelled_out_count_matches_the_real_one(): void
    {
        $count = count(Webhook::EVENT_TYPES);
        $expected = self::NUMBER_WORDS[$count] ?? null;

        $this->assertNotNull($expected, "Add {$count} to NUMBER_WORDS");

        $wrong = collect(self::NUMBER_WORDS)->reject(fn ($w) => $w === $expected)->values()->all();
        $found = 0;

        foreach (File::allFiles(resource_path('views/marketing')) as $file) {
            $source = File::get($file->getPathname());
            $name = $file->getFilename();

            // Only the phrase that counts event types, not every number on the page.
            if (preg_match_all('/\b([a-z]+)\s+event\s+types\b/i', $source, $matches)) {
                foreach ($matches[1] as $word) {
                    $word = strtolower($word);

                    if (in_array($word, $wrong, true)) {
                        $this->fail("{$name} says \"{$word} event types\" but there are {$count}");
                    }

                    if ($word === $expected) {
                        $found++;
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $found, 'No page states the event type count at all');
    }

    /**
     * The reference table documents what each type means, so a new type needs a row of prose and
     * not merely a mention. Guards against satisfying the test above with a bare list entry.
     */
    public function test_the_developer_reference_describes_each_type(): void
    {
        $source = $this->source('marketing/docs/developer/webhooks.blade.php');

        foreach (Webhook::EVENT_TYPES as $type) {
            // Bounded to the cell. A greedy `.{40,}` under /s runs straight past </td> and into the
            // rest of the file, so a one-word description passes - which is exactly what happened
            // when this assertion was first written.
            $matched = preg_match(
                '/<td[^>]*>'.preg_quote($type, '/').'<\/td>\s*<td[^>]*>(.*?)<\/td>/s',
                $source,
                $cell
            );

            $this->assertSame(1, $matched, "The webhook reference has no row for {$type}");

            $text = trim(html_entity_decode(strip_tags($cell[1])));

            $this->assertGreaterThanOrEqual(
                40,
                strlen($text),
                "The webhook reference row for {$type} says only \"{$text}\""
            );
        }
    }
}
