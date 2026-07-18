<?php

namespace Tests\Feature;

use App\Jobs\RegenerateRoleTranslations;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Covers the configurable translation TARGET language: the new roles.translation_language_code
 * field, the Event/Role helpers that resolve it, and the regenerate-on-change job. The actual
 * Gemini translation is not exercised (it calls an external API); these tests verify the routing
 * and invalidation logic around it, and that the default (target = English) is preserved.
 */
class TranslationLanguageTargetTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_translation_language_code_defaults_to_english(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');

        $this->assertSame('en', $role->translation_language_code);
        $this->assertFalse($role->offersTranslation());
        $this->assertSame(__('messages.english'), $role->translationLanguageName());
    }

    public function test_role_offers_translation_only_when_target_differs(): void
    {
        $user = $this->createOwner();

        $english = $this->createRole($user, 'venue', ['language_code' => 'en', 'translation_language_code' => 'en']);
        $this->assertFalse($english->offersTranslation());

        $italianToEnglish = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);
        $this->assertTrue($italianToEnglish->offersTranslation());

        $italianToFrench = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'fr']);
        $this->assertTrue($italianToFrench->offersTranslation());
        $this->assertSame(__('messages.french'), $italianToFrench->translationLanguageName());
    }

    public function test_event_translation_language_resolves_from_venue_then_talent(): void
    {
        $user = $this->createOwner();

        // Venue target wins when a venue is attached.
        $venue = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'fr']);
        $event = $this->createEvent($venue, ['name' => 'Concerto']);
        $event->setRelation('venue', $venue);
        $this->assertSame('fr', $event->getTranslationLanguageCode());

        // Falls back to 'en' with no venue and no talent target.
        $plain = $this->createRole($user, 'talent', ['language_code' => 'en', 'translation_language_code' => 'en']);
        $plainEvent = $this->createEvent($plain, ['name' => 'Show']);
        $this->assertSame('en', $plainEvent->getTranslationLanguageCode());
    }

    public function test_changing_target_dispatches_regeneration(): void
    {
        Queue::fake();

        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);

        $role->translation_language_code = 'fr';
        $role->save();

        Queue::assertPushed(RegenerateRoleTranslations::class);
    }

    public function test_unrelated_save_does_not_dispatch_regeneration(): void
    {
        Queue::fake();

        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);

        $role->name = 'Renamed Schedule';
        $role->save();

        Queue::assertNotPushed(RegenerateRoleTranslations::class);
    }

    public function test_regeneration_job_clears_stale_translations_and_resets_attempts(): void
    {
        $user = $this->createOwner();
        // language == target so the Group path clears inline without calling Gemini.
        $role = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'it']);

        $event = $this->createEvent($role, ['name' => 'Concerto']);
        // Simulate a previously-generated translation that is now stale.
        $event->name_en = 'Concert';
        $event->description_en = 'A concert';
        $event->translation_attempts = 3;
        $event->saveQuietly();

        (new RegenerateRoleTranslations($role))->handle();

        $event->refresh();
        $this->assertNull($event->name_en);
        $this->assertNull($event->description_en);
        $this->assertSame(0, (int) $event->translation_attempts);
    }

    public function test_showing_translation_helper_tracks_session_and_target(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'fr']);

        $this->assertFalse(showing_translation($role));

        session()->put('translate', true);
        $this->assertTrue(showing_translation($role));
        session()->forget('translate');

        request()->merge(['lang' => 'fr']);
        $this->assertTrue(showing_translation($role));

        request()->merge(['lang' => 'it']);
        $this->assertFalse(showing_translation($role));
    }

    public function test_new_role_model_defaults_target_to_english(): void
    {
        // The model attribute default must match the DB column default so the create form never
        // renders a null target (which made the browser submit the first <option>, 'ar').
        $this->assertSame('en', (new Role)->translation_language_code);
    }

    public function test_store_english_schedule_ignores_bogus_target_submission(): void
    {
        // Simulates what the browser posts for a NEW English schedule: the toggle is off and the
        // disabled/authored <option> makes the select fall through to the first enabled one ('ar').
        $user = $this->createOwner();

        $this->actingAs($user)->post(route('role.store'), [
            'name' => 'Fresh English Schedule',
            'email' => 'fresh@gmail.com',
            'timezone' => 'America/New_York',
            'language_code' => 'en',
            'translation_enabled' => '0',
            'translation_language_code' => 'ar',
        ]);

        $role = Role::where('user_id', $user->id)->where('name', 'Fresh English Schedule')->first();
        $this->assertNotNull($role);
        $this->assertSame('en', $role->translation_language_code, 'toggle off must force target = authored (no Arabic)');
        $this->assertFalse($role->offersTranslation());
    }

    public function test_store_non_english_schedule_keeps_english_target(): void
    {
        // A new Italian schedule renders with the toggle on and target 'en' (offer English),
        // matching existing rows and the pre-feature behavior.
        $user = $this->createOwner();

        $this->actingAs($user)->post(route('role.store'), [
            'name' => 'Fresh Italian Schedule',
            'email' => 'fresh-it@gmail.com',
            'timezone' => 'America/New_York',
            'language_code' => 'it',
            'translation_enabled' => '1',
            'translation_language_code' => 'en',
        ]);

        $role = Role::where('user_id', $user->id)->where('name', 'Fresh Italian Schedule')->first();
        $this->assertNotNull($role);
        $this->assertSame('en', $role->translation_language_code);
        $this->assertTrue($role->offersTranslation());
    }

    public function test_regeneration_skips_events_for_curator_roles(): void
    {
        // A curator never drives an event's target, so changing its own target must not wipe
        // (and force re-translation of) the events it aggregates.
        $user = $this->createOwner();
        $curator = $this->createCurator($user, ['language_code' => 'it', 'translation_language_code' => 'it']);

        $event = $this->createEvent($curator, ['name' => 'Aggregated Event']);
        $event->name_en = 'Aggregated (translated)';
        $event->translation_attempts = 2;
        $event->saveQuietly();

        (new RegenerateRoleTranslations($curator))->handle();

        $event->refresh();
        $this->assertSame('Aggregated (translated)', $event->name_en, 'curator regen must not null aggregated events');
        $this->assertSame(2, (int) $event->translation_attempts);
    }

    public function test_backup_export_import_preserves_translation_target(): void
    {
        // A schedule authored in English but translating to French: its _en columns hold French,
        // so the backup must carry the target or the restored copy shows French under an "en" label.
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['language_code' => 'en', 'translation_language_code' => 'fr']);

        $service = app(\App\Services\BackupService::class);
        $ref = new \ReflectionClass($service);

        $exportRole = $ref->getMethod('exportRole');
        $exportRole->setAccessible(true);
        $imageFiles = [];
        // exportRole's 3rd arg is by-reference; pass a reference and read the nested 'role' payload.
        $args = [$role, false, &$imageFiles];
        $exported = $exportRole->invokeArgs($service, $args);

        $this->assertSame('fr', $exported['role']['translation_language_code'] ?? null, 'target must be exported');

        $importRole = $ref->getMethod('importRole');
        $importRole->setAccessible(true);
        $imported = $importRole->invokeArgs($service, [$exported['role'], $user->id]);

        $this->assertSame('fr', $imported->translation_language_code, 'target must survive restore');
    }

    public function test_store_omitting_language_code_does_not_null_target(): void
    {
        // A crafted POST that omits language_code must not write null into the NOT NULL target.
        $user = $this->createOwner();

        $this->actingAs($user)->post(route('role.store'), [
            'name' => 'No Language Schedule',
            'email' => 'no-lang@gmail.com',
            'timezone' => 'America/New_York',
            'translation_enabled' => '0',
        ]);

        $role = Role::where('user_id', $user->id)->where('name', 'No Language Schedule')->first();
        $this->assertNotNull($role, 'store must not 500');
        $this->assertNotNull($role->translation_language_code);
        $this->assertSame($role->language_code ?: 'en', $role->translation_language_code);
    }
}
