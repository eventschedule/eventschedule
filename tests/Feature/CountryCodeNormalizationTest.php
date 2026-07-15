<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Sentry EVENTSCHEDULE-JS-2Y: a venue stored an alpha-3 country code ("isr") that the
 * alpha-2-only country picker rejected. Covers both defenses on the data side:
 *  - the Role saving hook normalizes alpha-3 -> alpha-2 on write, and
 *  - the backfill migration converts existing alpha-3 rows.
 */
class CountryCodeNormalizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_saving_a_role_normalizes_alpha3_country_code(): void
    {
        $owner = $this->createOwner();

        // alpha-3 in -> alpha-2 stored (the Role saving hook)
        $this->assertSame('il', $this->createRole($owner, 'venue', ['country_code' => 'ISR'])->fresh()->country_code);
        $this->assertSame('us', $this->createRole($owner, 'venue', ['country_code' => 'USA'])->fresh()->country_code);

        // already alpha-2 preserved (lowercased)
        $this->assertSame('il', $this->createRole($owner, 'venue', ['country_code' => 'IL'])->fresh()->country_code);
    }

    public function test_backfill_migration_converts_existing_alpha3_rows(): void
    {
        $owner = $this->createOwner();

        $isr = $this->createRole($owner, 'venue', ['country_code' => 'us']);
        $usa = $this->createRole($owner, 'venue', ['country_code' => 'us']);
        $keepAlpha2 = $this->createRole($owner, 'venue', ['country_code' => 'il']);
        $keepUnknown = $this->createRole($owner, 'venue', ['country_code' => 'us']);

        // Force raw alpha-3 values into the DB, bypassing the model saving hook that
        // would otherwise normalize them on write.
        DB::table('roles')->where('id', $isr->id)->update(['country_code' => 'isr']);
        DB::table('roles')->where('id', $usa->id)->update(['country_code' => 'USA']);
        DB::table('roles')->where('id', $keepUnknown->id)->update(['country_code' => 'zzz']);

        (require database_path('migrations/2026_07_15_000000_convert_alpha3_country_codes.php'))->up();

        $this->assertSame('il', DB::table('roles')->where('id', $isr->id)->value('country_code'));
        $this->assertSame('us', DB::table('roles')->where('id', $usa->id)->value('country_code'));
        // 2-letter and unmappable 3-letter values are left untouched (non-destructive).
        $this->assertSame('il', DB::table('roles')->where('id', $keepAlpha2->id)->value('country_code'));
        $this->assertSame('zzz', DB::table('roles')->where('id', $keepUnknown->id)->value('country_code'));
    }

    public function test_backup_restore_normalizes_alpha3_country_code(): void
    {
        $owner = $this->createOwner();

        // BackupService::importRole() restores via saveQuietly(), which skips the Role saving
        // hook, so it must normalize country_code itself. An old backup can carry alpha-3.
        $method = new \ReflectionMethod(\App\Services\BackupService::class, 'importRole');
        $method->setAccessible(true);

        $role = $method->invoke(app(\App\Services\BackupService::class), [
            'name' => 'Restored Venue',
            'type' => 'venue',
            'country_code' => 'ISR',
        ], $owner->id);

        $this->assertSame('il', $role->fresh()->country_code);
    }
}
