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
        config(['app.hosted' => true]);

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

        $this->assertSame([], $missing, "Document these in docs/GROWTH_DATA.md:\n".implode("\n", $missing));
    }

    /** Feature flags are read straight off the schedule; a new one is a new column in all but name. */
    public function test_every_feature_flag_is_documented(): void
    {
        $source = file_get_contents(app_path('Services/GrowthExportService.php'));
        $start = strpos($source, 'private function featuresOf(');
        $this->assertNotFalse($start, 'featuresOf() moved; point this test at it');
        preg_match_all("/'([a-z_]+)' => \\\$r->/", substr($source, $start, 3000), $flags);
        $this->assertNotEmpty($flags[1]);

        $section = substr($this->doc(), strpos($this->doc(), '| `features` |'));
        $line = strtok($section, "\n");
        foreach ($flags[1] as $flag) {
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
