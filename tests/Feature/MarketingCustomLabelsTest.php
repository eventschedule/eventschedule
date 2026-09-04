<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\TestCase;

/**
 * /features/custom-labels publishes the whole renameable-label list as a specimen
 * table, and states its length four times: in the meta description, in the JSON-LD
 * feature list, in the section heading and in the setup steps.
 *
 * That list is Role::getCustomizableLabels(), and it grows. It grew by one when the
 * audience feature added "Email me new events" to the subscribe panel, and the page
 * went on advertising 34 for months while the app offered 35 - a page whose entire
 * claim is "here is the whole list" quietly listing all but one of it.
 *
 * So the count is asserted against the model rather than against a fixture, and the
 * missing-key check names which label drifted rather than only that the totals differ.
 */
class MarketingCustomLabelsTest extends TestCase
{
    private function pageSource(): string
    {
        return file_get_contents(resource_path('views/marketing/custom-labels.blade.php'));
    }

    /** The English string the app ships for a label key. */
    private function shippedWording(string $key): ?string
    {
        $messages = require lang_path('en/messages.php');

        return $messages[$key] ?? null;
    }

    private static function normalize(string $value): string
    {
        return preg_replace('/[^a-z]/', '', strtolower($value));
    }

    public function test_every_customizable_label_appears_on_the_page(): void
    {
        $source = $this->pageSource();

        // The left column of the specimen table: ['Ships as', "One studio's word"].
        preg_match_all("/\['([^']+)',\s*'[^']*'\]/", $source, $matches);
        $printed = array_map([self::class, 'normalize'], $matches[1]);

        $missing = [];
        foreach (array_keys(Role::getCustomizableLabels()) as $key) {
            $wording = $this->shippedWording($key);
            $this->assertNotNull($wording, "messages.{$key} has no English string, so the label cannot be shown");
            if (! in_array(self::normalize($wording), $printed, true)) {
                $missing[] = "{$key} ({$wording})";
            }
        }

        $this->assertSame([], $missing,
            'these customizable labels are missing from the /features/custom-labels specimen table');
    }

    public function test_the_page_advertises_the_real_number_of_labels(): void
    {
        $expected = count(Role::getCustomizableLabels());
        $source = $this->pageSource();

        // Any other two-digit count next to the word "labels" is a stale hardcoded
        // total. $sheetCount is computed from the table itself, so it is exempt.
        preg_match_all('/(\d{2})[\s-](?:renameable\s+)?labels?\b/i', $source, $matches);

        foreach ($matches[1] as $found) {
            $this->assertSame((string) $expected, $found,
                "the page says {$found} labels; Role::getCustomizableLabels() has {$expected}");
        }

        $this->assertNotEmpty($matches[1], 'the page no longer states how many labels there are');
    }
}
