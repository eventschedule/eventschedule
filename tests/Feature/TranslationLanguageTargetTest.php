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

    public function test_offers_translation_gates_custom_field_and_category_translation(): void
    {
        // The save-time custom-field / label / category translation gates in EventRepo and
        // RoleController now use offersTranslation(). An English-authored schedule with a foreign
        // target must translate (the gate previously hardcoded language_code !== 'en' and skipped it).
        $user = $this->createOwner();

        $englishToFrench = $this->createRole($user, 'venue', ['language_code' => 'en', 'translation_language_code' => 'fr']);
        $this->assertTrue($englishToFrench->offersTranslation(), 'en->fr must translate custom fields/labels/categories');

        $englishToEnglish = $this->createRole($user, 'venue', ['language_code' => 'en', 'translation_language_code' => 'en']);
        $this->assertFalse($englishToEnglish->offersTranslation(), 'en->en has nothing to translate');
    }

    public function test_event_categories_show_target_translation_by_locale(): void
    {
        $user = $this->createOwner();

        // Italian schedule translating to English: name_en holds the English text.
        $italianToEnglish = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);
        $italianToEnglish->event_categories = [
            ['id' => 100, 'name' => 'Concierto', 'name_en' => 'Concert'],
        ];
        $italianToEnglish->saveQuietly();

        $this->assertSame('Concert', $italianToEnglish->getEventCategories('en')[0]['name'], 'target locale shows name_en');
        $this->assertSame('Concierto', $italianToEnglish->getEventCategories('it')[0]['name'], 'authored locale shows name');
        $this->assertSame('Concierto', $italianToEnglish->getEventCategories(null)[0]['name'], 'no locale shows authored name');

        // English schedule translating to French: name_en holds the French text, surfaced when the
        // requested locale is the target 'fr' (the previously-hardcoded 'en' check would have missed it).
        $englishToFrench = $this->createRole($user, 'venue', ['language_code' => 'en', 'translation_language_code' => 'fr']);
        $englishToFrench->event_categories = [
            ['id' => 100, 'name' => 'Party', 'name_en' => 'Fete'],
        ];
        $englishToFrench->saveQuietly();

        $this->assertSame('Fete', $englishToFrench->getEventCategories('fr')[0]['name'], 'target locale (fr) shows name_en');
        $this->assertSame('Party', $englishToFrench->getEventCategories('en')[0]['name'], 'non-target locale shows authored name');
    }

    public function test_get_translated_categories_follows_translation_session(): void
    {
        // The helper passes no explicit locale; it must surface the target-language category name
        // while the guest is viewing the translation, and the authored name otherwise.
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue', ['language_code' => 'it', 'translation_language_code' => 'en']);
        $role->event_categories = [
            ['id' => 100, 'name' => 'Concierto', 'name_en' => 'Concert'],
        ];
        $role->saveQuietly();

        $this->assertSame('Concierto', get_translated_categories($role)[100], 'not translating: authored name');

        session()->put('translate', true);
        $this->assertSame('Concert', get_translated_categories($role)[100], 'translating: target-language name');
        session()->forget('translate');
    }

    public function test_detect_content_language_maps_dominant_non_latin_script(): void
    {
        $this->assertSame('he', detect_content_language('אירוע מוזיקה'));
        $this->assertSame('ar', detect_content_language('مهرجان'));
        $this->assertSame('ru', detect_content_language('Москва'));
        $this->assertNull(detect_content_language('Summer Concert'));
        $this->assertNull(detect_content_language('Concert אירוע'), 'Latin-majority keeps the account language');
        $this->assertSame('he', detect_content_language('אירוע Live'), 'non-Latin majority overrides');
        $this->assertNull(detect_content_language(''));
        $this->assertNull(detect_content_language(null));
        // Decides on the first part that has letters (the event name), not a long Latin description.
        $this->assertSame('he', detect_content_language('אירוע', 'A much longer english description here'));
    }

    public function test_guest_submit_tags_new_talent_and_venue_with_content_language(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true]);

        // An English-account submitter posting Hebrew content: the auto-created talent/venue must be
        // tagged 'he' (from the content), not 'en' (from the account), or the name never translates.
        $submitter = $this->createOwner();
        $submitter->language_code = 'en';
        $submitter->save();

        $this->actingAs($submitter)
            ->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => 'קונצרט חורף',
                'venue_name' => 'היכל התרבות',
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration' => 2,
            ])
            ->assertOk();

        $talent = $submitter->talents()->first();
        $this->assertNotNull($talent);
        $this->assertSame('he', $talent->language_code);

        $event = Event::where('name', 'קונצרט חורף')->first();
        $this->assertNotNull($event);
        $event->load('venue');
        $this->assertSame('he', $event->venue->language_code);
        $this->assertSame('he', $event->getLanguageCode());
        $this->assertSame('en', $event->getTranslationLanguageCode(), 'source he != target en, so the cron translates');
    }

    public function test_guest_submit_english_content_keeps_account_language(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true]);

        $submitter = $this->createOwner();
        $submitter->language_code = 'en';
        $submitter->save();

        $this->actingAs($submitter)
            ->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => 'Summer Concert',
                'venue_name' => 'City Hall',
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration' => 2,
            ])
            ->assertOk();

        $talent = $submitter->talents()->first();
        $this->assertSame('en', $talent->language_code, 'Latin content must not clobber the account language');

        $event = Event::where('name', 'Summer Concert')->first();
        $event->load('venue');
        $this->assertSame('en', $event->venue->language_code);
        $this->assertSame('en', $event->getLanguageCode());
    }

    public function test_guest_submit_forwards_ai_english_name_and_survives_saving_hook(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true]);

        $submitter = $this->createOwner();

        // The AI-parse box already produced the English name; forwarding it gives an immediate
        // translation instead of waiting for the hourly cron. The saving hook only nulls name_en on
        // UPDATE ($model->exists), so a forwarded value must survive the initial insert.
        $this->actingAs($submitter)
            ->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => 'קונצרט',
                'name_en' => 'Concert',
                'short_description' => 'תיאור קצר',
                'short_description_en' => 'A concert',
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration' => 2,
            ])
            ->assertOk();

        $event = Event::where('name', 'קונצרט')->first();
        $this->assertNotNull($event);
        $this->assertSame('Concert', $event->name_en);
        $this->assertSame('A concert', $event->short_description_en);
    }

    public function test_guest_submit_leaves_reused_talent_but_tags_new_venue(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['accept_requests' => true, 'require_account' => true]);

        // An existing English talent is reused (we never mutate a shared schedule's language), but the
        // new venue is still tagged from the content, and the venue wins in getLanguageCode().
        $submitter = $this->createOwner();
        $existingTalent = $this->createRole($submitter, 'talent', ['language_code' => 'en', 'translation_language_code' => 'en']);

        $this->actingAs($submitter)
            ->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
                'name' => 'קונצרט חורף',
                'venue_name' => 'היכל התרבות',
                'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration' => 2,
            ])
            ->assertOk();

        $existingTalent->refresh();
        $this->assertSame('en', $existingTalent->language_code, 'reused talent is left untouched');

        $event = Event::where('name', 'קונצרט חורף')->first();
        $this->assertNotNull($event);
        $event->load('venue');
        $this->assertSame('he', $event->venue->language_code);
        $this->assertSame('he', $event->getLanguageCode(), 'venue-first resolution rescues translation');
    }
}
