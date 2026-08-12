<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Role;
use App\Services\BackupService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * roles.website is a varchar(255) under a strict connection, so an over-long value is a
 * QueryException (MySQL 1406) rather than a truncation.
 *
 * Production report EVENTSCHEDULE-PHP-40: someone pasted a 390-character Facebook link shim into
 * the venue Website field and the event create form 500'd, losing the event they were entering.
 * The AI ingestion paths had been clamped against exactly this; the interactive form never was.
 *
 * Two layers are pinned here - the form-level clean-then-validate, and the Role saving hook that
 * backs up every writer with no FormRequest (import, guest submit, WhatsApp webhook, curator cron).
 */
class WebsiteLengthGuardTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** The exact value from the production report, at its full 390 characters. */
    private const PRODUCTION_SHIM = 'https://l.facebook.com/l.php?u=https%3A%2F%2Fbeithadoar.com%2F%3Ffbclid%3DIwcGRvZgFleHRuA2FlbQIxMABicmlkETFFOFoyMkQyRlYybjRPcHF4c3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHh7JTu2XqUXfGevjbwBL7ugyy6B9nZ-Jfa2YbCvInHsTJW1Hk9p6heqkDwWe_aem_psmIxEJ2vh47GDYFMY-xKA&h=AUCBIJa8scQQgWmJT6Good4Ee5p7oC8fM5gBv4Two7DpNX4_35JZy7JsKv78SbgmpGMe-wnX2pxshF6ZDXEyL6DafE8p1YBxzZb40vbdOxo1EsacoVxZGujKwdNEgmNmKpEk';

    /** Over the column, but no shim and no tracking params - nothing to clean, so it must be refused. */
    private function overlongPlainUrl(): string
    {
        return 'https://example.org/'.str_repeat('a', 300);
    }

    // -----------------------------------------------------------------
    // The event form - the surface that actually 500'd.
    // -----------------------------------------------------------------

    public function test_a_pasted_link_shim_creates_the_venue_with_the_unwrapped_url(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Beit HaDoar Karkur',
            'venue_website' => self::PRODUCTION_SHIM,
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Beit HaDoar Karkur')->firstOrFail();
        $this->assertSame('https://beithadoar.com/', $venue->website);
    }

    public function test_an_over_long_website_is_a_field_error_rather_than_a_500(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $before = Role::count();

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Refused Venue',
            'venue_website' => $this->overlongPlainUrl(),
        ])->assertSessionHasErrors('venue_website');

        $this->assertSame($before, Role::count(), 'a refused save must not have created the venue');
    }

    public function test_an_over_long_venue_name_is_a_field_error_too(): void
    {
        // Same defect one column over: venue_name lands in roles.name, also varchar(255).
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => str_repeat('n', 300),
        ])->assertSessionHasErrors('venue_name');
    }

    public function test_an_over_long_venue_country_code_is_a_field_error(): void
    {
        // Same block, same varchar(255). CountryUtils::normalizeCountryCode() returns an
        // unrecognized value unchanged with no length bound, so the Role saving hook cannot
        // catch this one - the rule is the only guard.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Bad Country Venue',
            'venue_country_code' => str_repeat('c', 300),
        ])->assertSessionHasErrors('venue_country_code');
    }

    public function test_an_unsupported_venue_language_code_is_a_field_error(): void
    {
        // Nothing normalizes language_code at all, so an unbounded value reached roles.language_code
        // (varchar(255)) directly.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Bad Language Venue',
            'venue_language_code' => str_repeat('l', 300),
        ])->assertSessionHasErrors('venue_language_code');
    }

    public function test_a_supported_venue_language_code_is_accepted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Good Language Venue',
            'venue_language_code' => 'he',
            'venue_country_code' => 'il',
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Good Language Venue')->firstOrFail();
        $this->assertSame('he', $venue->language_code);
        $this->assertSame('il', $venue->country_code);
    }

    public function test_a_scheme_less_venue_website_round_trips_unchanged(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Scheme Less Hall',
            'venue_website' => 'example.com',
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Scheme Less Hall')->firstOrFail();
        $this->assertSame('example.com', $venue->website);
    }

    public function test_omitting_venue_website_entirely_does_not_clear_a_stored_one(): void
    {
        // The normalizing merge has to be has()-guarded: making an absent key present would read
        // to EventRepo::saveEvent() as "the form cleared this field" whenever
        // venue_details_editable is set, wiping a website the request never mentioned.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $unclaimed = new Role;
        $unclaimed->name = 'Unclaimed Venue';
        $unclaimed->subdomain = 'unclaimed'.strtolower(Str::random(6));
        $unclaimed->type = 'venue';
        $unclaimed->website = 'https://keep-me.example';
        $unclaimed->save();

        $this->postCreateEvent($owner, $role, [
            'venue_id' => UrlUtils::encodeId($unclaimed->id),
            'venue_name' => 'Unclaimed Venue',
            'venue_details_editable' => '1',
            // venue_website deliberately absent.
        ])->assertRedirect();

        $this->assertSame('https://keep-me.example', $unclaimed->fresh()->website);
    }

    // -----------------------------------------------------------------
    // The schedule settings form - the same hole, second surface.
    // -----------------------------------------------------------------

    public function test_a_link_shim_on_the_schedule_form_is_unwrapped(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'timezone' => $role->timezone,
            'website' => self::PRODUCTION_SHIM,
        ]);

        $this->assertSame('https://beithadoar.com/', $role->fresh()->website);
    }

    public function test_an_over_long_schedule_website_is_a_field_error(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'timezone' => $role->timezone,
            'website' => $this->overlongPlainUrl(),
        ])->assertSessionHasErrors('website');

        $this->assertNull($role->fresh()->website);
    }

    public function test_a_settings_save_that_omits_the_website_leaves_it_alone(): void
    {
        // update() fills from $request->all(), so an unguarded merge would null a stored website
        // on any save that does not carry the key - which most of the suite's PUTs do not.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['website' => 'https://keep-me.example']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => 'Renamed',
            'email' => $role->email,
            'new_subdomain' => $role->subdomain,
            'timezone' => $role->timezone,
        ]);

        $this->assertSame('https://keep-me.example', $role->fresh()->website);
    }

    // -----------------------------------------------------------------
    // The model hook - the backstop for every writer with no FormRequest.
    // -----------------------------------------------------------------

    public function test_saving_a_role_directly_cleans_and_bounds_the_website(): void
    {
        // Covers the callers that never see a FormRequest: EventController::import(),
        // guest submission, the WhatsApp webhook and the curator-import cron.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $role->website = self::PRODUCTION_SHIM;
        $role->save();

        $this->assertSame('https://beithadoar.com/', $role->fresh()->website);
    }

    public function test_a_website_with_no_shim_that_overflows_is_clamped_rather_than_thrown(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $role->website = $this->overlongPlainUrl();
        $role->save();

        $this->assertSame(255, mb_strlen($role->fresh()->website));
    }

    public function test_restoring_a_backup_carrying_an_over_long_website_does_not_throw(): void
    {
        // importRole() persists with saveQuietly(), which fires no model events - so the saving
        // hook above cannot help here and the clamp has to be re-applied by hand, the same way
        // country_code already is. A hand-edited or legacy backup is the way an over-long value
        // reaches this path, so it is injected into the payload rather than stored first.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $data['schedules'][0]['role']['website'] = self::PRODUCTION_SHIM;

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertSame('https://beithadoar.com/', $restored->website);
    }

    public function test_a_save_that_does_not_touch_the_website_leaves_it_untouched(): void
    {
        // Guarded on isDirty: re-cleaning an untouched legacy value would rewrite stored data
        // during an unrelated save. This is also what makes the fix not a lazy backfill.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $role->website = 'https://legacy.example/?fbclid=kept';
        $role->saveQuietly();

        $role->name = 'Renamed';
        $role->save();

        $this->assertSame('https://legacy.example/?fbclid=kept', $role->fresh()->website);
    }
}
