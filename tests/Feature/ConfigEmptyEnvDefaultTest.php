<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * A present-but-EMPTY environment variable must not defeat a config default.
 *
 * `env('K', 500)` returns the default only when K is absent. When it is present and blank -
 * which is what `.env.example` ships and what a well-meaning operator creates on a DigitalOcean
 * app spec - env() returns '', the default never fires, and `(int) ''` is 0. Several entries in
 * config/usage.php already say this in a comment above themselves; these tests make it
 * mechanical, because the failure is silent in every case:
 *
 *   - a nudge ceiling of 0 is floored to 1 by max(1, ...), so mail drains one message per run;
 *   - a wall cache TTL of 0 disables the cache and puts five correlated subqueries plus a regex
 *     pass on every origin hit of "/".
 *
 * Asserted against the config SOURCE rather than the resolved value, for the reason
 * BackupStorageTest gives about the same class of bug: the resolved value is correct on any
 * machine where the var is simply absent, which is every machine that runs this suite. The
 * expression is the thing that has to be right.
 */
class ConfigEmptyEnvDefaultTest extends TestCase
{
    /**
     * Entries where 0 carries no meaning, so `?:` is the correct guard.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function guardedEntries(): array
    {
        return [
            'onboarding nudge batch' => ['usage.php', 'ONBOARDING_NUDGE_BATCH'],
            'activation nudge batch' => ['usage.php', 'ACTIVATION_NUDGE_BATCH'],
            'unverified audience mail cap' => ['usage.php', 'AUDIENCE_MAIL_UNVERIFIED_MAX_RECIPIENTS'],
            'announcement cadence floor' => ['usage.php', 'AUDIENCE_ANNOUNCEMENT_MIN_HOURS'],
            'announcement batch' => ['usage.php', 'AUDIENCE_ANNOUNCEMENT_BATCH'],
            'announcement recipient batch' => ['usage.php', 'AUDIENCE_ANNOUNCEMENT_RECIPIENT_BATCH'],
        ];
    }

    /**
     * @dataProvider guardedEntries
     */
    public function test_the_entry_uses_the_elvis_form_not_a_default_argument(string $file, string $key): void
    {
        $source = file_get_contents(config_path($file));

        $this->assertStringContainsString("env('{$key}') ?:", $source,
            "{$key} must use env('{$key}') ?: <default>, or a blank value on the app spec resolves to 0.");

        $this->assertStringNotContainsString("env('{$key}', ", $source,
            "{$key} must not use a default argument: it never fires for a present-but-empty value.");
    }

    /**
     * The wall cache is the exception, and it is worth its own test so nobody "fixes" it into
     * line with the entries above.
     *
     * 0 is MEANINGFUL here - it disables the cache, and phpunit.xml forces exactly that so a wall
     * cached by one test cannot render against the next test's wiped database. `?:` would map
     * that 0 straight back to 600 and break the pin. is_numeric() is the only form that
     * separates "unset or blank" from "deliberately zero".
     */
    public function test_the_wall_cache_ttl_keeps_a_deliberate_zero_but_rejects_a_blank(): void
    {
        $source = file_get_contents(config_path('marketing.php'));

        $this->assertStringContainsString("is_numeric(env('MARKETING_WALL_CACHE_SECONDS'))", $source,
            'MARKETING_WALL_CACHE_SECONDS needs a form that keeps an explicit 0 and rejects a blank.');

        $this->assertStringNotContainsString("env('MARKETING_WALL_CACHE_SECONDS', ", $source,
            'a default argument never fires for a blank value, and (int) \'\' silently disables the cache.');

        $this->assertStringNotContainsString("env('MARKETING_WALL_CACHE_SECONDS') ?:", $source,
            '?: would map the deliberate 0 in phpunit.xml back to 600.');

        // And the pin itself still holds, which is what the suite depends on.
        $this->assertSame(0, config('marketing.wall_cache_seconds'),
            'phpunit.xml forces this to 0; if it is not 0 the wall cache is live across tests.');
    }
}
