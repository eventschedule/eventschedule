<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What saving a schedule may touch, and what a write that is not a save must leave alone.
 *
 * Role's saving hook derives columns from other columns, and it used to do so from whatever the
 * instance happened to hold: a schedule loaded through a narrowed select had its rendered
 * description and banner replaced with null, because their markdown sources read as null. And
 * because the hook is the only way a save() can go, code that only wanted to store a cursor - the
 * calendar syncs, every fifteen minutes - ran all of it and moved updated_at, which the sitemap
 * publishes as the guest page's <lastmod>.
 */
class RoleSaveHookTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const RENDERED = ['description_html', 'description_html_en', 'banner_message_html', 'banner_message_html_en'];

    private const LONG_AGO = '2020-01-01 00:00:00';

    private function describedVenue(): Role
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'description' => 'A **bold** claim',
            'description_en' => 'A **daring** claim',
            'banner_message' => 'Doors at **eight**',
            'banner_message_en' => 'Doors at **nine**',
        ]);
    }

    public function test_saving_a_narrowed_select_keeps_the_rendered_description_and_banner(): void
    {
        $venue = $this->describedVenue();
        $rendered = $venue->only(self::RENDERED);

        foreach ($rendered as $column => $html) {
            $this->assertStringContainsString('<strong>', (string) $html, "fixture: {$column} must be rendered");
        }

        $narrow = Role::query()->select(['id', 'name'])->findOrFail($venue->id);
        $narrow->name = 'Renamed';
        $narrow->save();

        $venue->refresh();
        $this->assertSame('Renamed', $venue->name);
        $this->assertSame($rendered, $venue->only(self::RENDERED), 'a column this instance never loaded was re-derived from null');
    }

    public function test_a_narrowed_select_that_carries_one_source_renders_that_one_only(): void
    {
        $venue = $this->describedVenue();
        $banner = $venue->banner_message_html;

        $narrow = Role::query()->select(['id', 'description'])->findOrFail($venue->id);
        $narrow->description = 'Now a **quiet** claim';
        $narrow->save();

        $venue->refresh();
        $this->assertStringContainsString('<strong>quiet</strong>', $venue->description_html);
        $this->assertSame($banner, $venue->banner_message_html);
    }

    public function test_a_fully_loaded_schedule_is_still_re_rendered_on_every_save(): void
    {
        $venue = $this->describedVenue();
        DB::table('roles')->where('id', $venue->id)->update(['description_html' => '<!--stale-->']);

        // No source changed: the render is unconditional for a loaded source, which is what heals
        // a row after the renderer itself changes.
        $venue->fresh()->save();
        $this->assertStringContainsString('<strong>bold</strong>', $venue->fresh()->description_html);

        $venue = $venue->fresh();
        $venue->description = null;
        $venue->save();
        $this->assertNull($venue->fresh()->description_html, 'clearing the source must clear what was rendered from it');
    }

    public function test_an_operational_write_runs_no_hook_and_does_not_move_updated_at(): void
    {
        $venue = $this->describedVenue();
        DB::table('roles')->where('id', $venue->id)->update([
            'description_html' => '<!--stale-->',
            'updated_at' => self::LONG_AGO,
        ]);

        $venue = $venue->fresh();
        $venue->name = 'An unsaved rename';

        $venue->writeOperationalColumns([
            'google_sync_token' => 'cursor-1',
            'microsoft_last_sync_at' => now(),
        ]);

        $stored = DB::table('roles')->where('id', $venue->id)->first();
        $this->assertSame('cursor-1', $stored->google_sync_token);
        $this->assertNotNull($stored->microsoft_last_sync_at);
        $this->assertSame('<!--stale-->', $stored->description_html, 'the saving hook ran');
        $this->assertSame(self::LONG_AGO, $stored->updated_at, 'a cursor is not a change to what the schedule publishes');
        $this->assertNotSame('An unsaved rename', $stored->name, 'an unrelated unsaved change was written along with the cursor');

        // The instance agrees with the row, and is not left thinking the cursor still needs saving.
        $this->assertSame('cursor-1', $venue->google_sync_token);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $venue->microsoft_last_sync_at);
        $this->assertSame(['name'], array_keys($venue->getDirty()));
    }

    /**
     * The three custom-SMTP failure markers are the same kind of write and go through the same
     * method. They used to carry their own copy of it on the Eloquent builder, which stamps
     * updated_at - so a schedule whose mail server was down got a new <lastmod> with each failure.
     */
    public function test_recording_a_mail_failure_does_not_move_updated_at(): void
    {
        $venue = $this->describedVenue();
        DB::table('roles')->where('id', $venue->id)->update(['updated_at' => self::LONG_AGO]);

        $venue = $venue->fresh();
        $venue->name = 'An unsaved rename';

        $venue->markEmailSettingsFailed('Connection refused');
        $this->assertTrue($venue->isEmailSettingsFailureActive());

        $venue->markEmailSettingsFailureNotified();

        $stored = DB::table('roles')->where('id', $venue->id)->first();
        $this->assertSame('Connection refused', $stored->email_settings_failed_message);
        $this->assertNotNull($stored->email_settings_failed_at);
        $this->assertNotNull($stored->email_settings_failure_notified_at);
        $this->assertSame(self::LONG_AGO, $stored->updated_at);
        $this->assertNotSame('An unsaved rename', $stored->name);

        $venue->clearEmailSettingsFailure();

        $stored = DB::table('roles')->where('id', $venue->id)->first();
        $this->assertNull($stored->email_settings_failed_at);
        $this->assertNull($stored->email_settings_failed_message);
        $this->assertNull($stored->email_settings_failure_notified_at);
        $this->assertSame(self::LONG_AGO, $stored->updated_at);
        $this->assertFalse($venue->isEmailSettingsFailureActive());
    }

    public static function calendarSyncServices(): array
    {
        return [['GoogleCalendarService.php'], ['MicrosoftCalendarService.php'], ['CalDAVService.php']];
    }

    /**
     * Each of these stored its cursor with $role->save(), once per synced schedule per run. A
     * cursor goes through Role::writeOperationalColumns().
     */
    #[DataProvider('calendarSyncServices')]
    public function test_a_calendar_sync_service_never_saves_the_schedule(string $file): void
    {
        $this->assertStringNotContainsString(
            '$role->save()',
            file_get_contents(app_path('Services/'.$file)),
            "{$file} saves the whole schedule. To store a sync cursor use writeOperationalColumns()."
        );
    }

    /**
     * rewriteApprovedSubdomainReferences() promises to leave updated_at alone, for the same
     * sitemap reason, and wrote through the Eloquent builder - which always stamps it.
     */
    public function test_rewriting_an_approve_list_does_not_move_updated_at(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator');
        DB::table('roles')->where('id', $curator->id)->update([
            'approved_subdomains' => json_encode(['old-name', 'another']),
            'updated_at' => self::LONG_AGO,
        ]);

        $this->assertSame(1, Role::rewriteApprovedSubdomainReferences('old-name', 'new-name'));

        $stored = DB::table('roles')->where('id', $curator->id)->first();
        $this->assertSame(['new-name', 'another'], json_decode($stored->approved_subdomains, true));
        $this->assertSame(self::LONG_AGO, $stored->updated_at);
    }
}
