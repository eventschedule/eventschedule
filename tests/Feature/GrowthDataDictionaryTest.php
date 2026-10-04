<?php

namespace Tests\Feature;

use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * docs/GROWTH_DATA.md is what an analysis of a pull - by Claude or anyone - reads to know what a
 * field means, which population it counts and over what window. A field that ships undocumented
 * gets guessed at, and the guesses are how comped plans were once read as customers. So the shape
 * of a real build is held to the document: every top-level section, every row column, every
 * feature flag, and the current schema version must appear in it.
 */
class GrowthDataDictionaryTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function doc(): string
    {
        return file_get_contents(base_path('docs/GROWTH_DATA.md'));
    }

    public function test_every_section_and_row_column_is_documented(): void
    {
        // The nexus, so hero_test is built and its fields can be held to the document too.
        config(['app.hosted' => true, 'app.is_nexus' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'google_calendar_id' => 'x', 'custom_css' => 'x', 'banner_enabled' => true,
        ]);
        $this->createEvent($role);

        $data = app(GrowthExportService::class)->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31));
        $doc = $this->doc();

        $missing = [];
        foreach (array_keys($data) as $section) {
            if (! str_contains($doc, "`{$section}`")) {
                $missing[] = "section {$section}";
            }
        }
        foreach (array_keys($data['meta']) as $key) {
            if (! str_contains($doc, "`{$key}`")) {
                $missing[] = "meta.{$key}";
            }
        }
        foreach (['signups', 'schedules'] as $table) {
            foreach ($data[$table]['columns'] as $column) {
                if (! str_contains($doc, "`{$column}`")) {
                    $missing[] = "{$table}.{$column}";
                }
            }
        }
        foreach (array_keys($data['acquisition']) as $rollup) {
            if (! str_contains($doc, "`{$rollup}`")) {
                $missing[] = "acquisition.{$rollup}";
            }
        }

        // The headline test's fields are looked for in its OWN entry: `signups`, `visitors` and
        // `clicks` are all documented elsewhere as something else, which would pass them for free.
        $this->assertNotNull($data['hero_test'], 'the headline test builds on the nexus');
        $start = strpos($doc, '- `hero_test`:');
        $this->assertNotFalse($start, 'the hero_test entry moved; point this test at it');
        $entry = substr($doc, $start, strpos($doc, "\n## ", $start) - $start);
        foreach ([...array_keys($data['hero_test']), ...array_keys($data['hero_test']['rows'][0])] as $field) {
            if (! str_contains($entry, "`{$field}`")) {
                $missing[] = "hero_test.{$field}";
            }
        }

        $this->assertSame([], $missing, "Document these in docs/GROWTH_DATA.md:\n".implode("\n", $missing));
    }

    /**
     * Feature flags come off the schedule (featuresOf()) and from other tables (adoptionByRole());
     * a new one is a new column in all but name.
     */
    public function test_every_feature_flag_is_documented(): void
    {
        $source = file_get_contents(app_path('Services/GrowthExportService.php'));
        $flags = [];
        foreach ([
            'private function featuresOf(' => ['foreach ([', '] as $key'],
            'private function adoptionByRole(' => ['$sets = [', "\n        ];"],
        ] as $method => [$opener, $closer]) {
            $start = strpos($source, $method);
            $this->assertNotFalse($start, "{$method} moved; point this test at it");
            $body = substr($source, $start);
            $open = strpos($body, $opener);
            $list = substr($body, $open, strpos($body, $closer, $open) - $open);
            preg_match_all("/^\s*'([a-z_0-9]+)' =>/m", $list, $found);
            $this->assertNotEmpty($found[1], "found no flags in {$method}");
            $flags = [...$flags, ...$found[1]];
        }

        $section = substr($this->doc(), strpos($this->doc(), '| `features` |'));
        $line = strtok($section, "\n");
        foreach (array_unique($flags) as $flag) {
            $this->assertStringContainsString("`{$flag}`", $line, "features flag {$flag} is not in the docs/GROWTH_DATA.md features row");
        }
    }

    public function test_the_current_schema_version_has_a_changelog_entry(): void
    {
        $this->assertMatchesRegularExpression(
            '/^- \*\*'.GrowthExportService::SCHEMA_VERSION.'\*\*/m',
            $this->doc(),
            'Bumped GrowthExportService::SCHEMA_VERSION? Say what changed in the docs/GROWTH_DATA.md changelog.'
        );
    }
}
