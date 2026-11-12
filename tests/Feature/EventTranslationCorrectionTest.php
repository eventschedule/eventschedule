<?php

namespace Tests\Feature;

use App\Jobs\RegenerateRoleTranslations;
use App\Jobs\TranslateEventSoon;
use App\Models\Event;
use App\Models\EventRole;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\TranslationQueue;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A person can correct an event's automatic translation, and the correction is theirs: the
 * translation command never writes over it, a change to the original keeps it (and says the
 * original changed), and only the person, by Reset to automatic, gives it back to the command.
 *
 * Asked for by an organization that writes in English and publishes in Portuguese: before this the
 * saving hook emptied the translation whenever the original changed, and the command redid it.
 */
class EventTranslationCorrectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $venue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->venue = $this->createRole($this->owner, 'venue', ['language_code' => 'en', 'translation_language_code' => 'pt']);
    }

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);

        parent::tearDown();
    }

    private function translatedEvent(array $attrs = []): Event
    {
        $event = $this->createEvent($this->venue, $attrs + [
            'creator_role_id' => $this->venue->id,
            'name' => 'Jazz Night',
            'short_description' => 'Live music by the river',
            'description' => 'Doors at **seven**.',
        ]);
        // As app:translate leaves it.
        $event->name_en = 'Noite de Jazz automática';
        $event->short_description_en = 'Música ao vivo junto ao rio';
        $event->description_en = 'Portas às **sete**.';
        $event->save();

        return $event->fresh();
    }

    private function update(Event $event, array $fields): TestResponse
    {
        return $this->actingAs($this->owner)->put(
            route('event.update', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            array_merge([
                'name' => $event->name,
                'short_description' => $event->short_description,
                'description' => $event->description,
                'starts_at' => $event->starts_at,
                'duration' => 2,
                'schedule_type' => 'one_time',
            ], $fields)
        );
    }

    private function editPage(Event $event): string
    {
        return $this->actingAs($this->owner)
            ->get(route('event.edit', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
    }

    public function test_correcting_a_translation_saves_it_and_marks_it_as_a_persons(): void
    {
        $event = $this->translatedEvent();

        $this->update($event, ['translations' => [
            'name' => 'Noite de Jazz',
            'description' => "Portas às **19h**.\r\n\r\nEntrada livre.",
        ]])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertTrue($event->translationIsManual('name'));
        $this->assertFalse($event->translationIsStale('name'));
        $this->assertTrue($event->translationIsManual('description'));
        // The HTML a guest reads is made from it by the same sanitizing path as the original's.
        $this->assertStringContainsString('<strong>19h</strong>', $event->description_html_en);
        // What nobody touched stays automatic.
        $this->assertFalse($event->translationIsManual('short_description'));
        $this->assertSame('Música ao vivo junto ao rio', $event->short_description_en);
    }

    public function test_opening_a_field_and_saving_nothing_changes_nothing(): void
    {
        $event = $this->translatedEvent();

        // A save that does not mention translations, as every save without the row does.
        $this->update($event, ['name' => 'Jazz Night'])->assertRedirect();

        $this->assertNull($event->fresh()->translation_overrides);
    }

    public function test_the_translation_command_never_writes_over_a_correction(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        // Something else is left for the command to do, so it does run on this event.
        Event::where('id', $event->id)->update(['short_description_en' => null]);
        GeminiUtils::fakeResponses(fn () => ['TRADUÇÃO AUTOMÁTICA']);

        Artisan::call('app:translate', ['--event_id' => $event->id]);

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertSame('TRADUÇÃO AUTOMÁTICA', $event->short_description_en);
    }

    public function test_a_changed_original_keeps_the_correction_and_says_so(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        $this->update($event->fresh(), [
            'name' => 'Jazz Night by the River',
            'short_description' => 'Live music, free entry',
        ])->assertRedirect();

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en, 'the correction is still what guests read');
        $this->assertTrue($event->translationIsManual('name'));
        $this->assertTrue($event->translationIsStale('name'));
        // A field nobody corrected is emptied and translated again, as it always was.
        $this->assertNull($event->short_description_en);
        $this->assertSame(0, (int) $event->translation_attempts);
        $this->assertContains($event->id, TranslationQueue::events($event->id)->pluck('id')->all());

        // The form says it, beside the field.
        $html = $this->editPage($event);
        $this->assertStringContainsString('"stale":true', $html);
        $this->assertStringContainsString(__('messages.translation_keep_mine'), $html);
    }

    public function test_keep_mine_dates_the_correction_against_the_new_original(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);
        $this->update($event->fresh(), ['name' => 'Jazz Night by the River']);
        $this->assertTrue($event->fresh()->translationIsStale('name'));

        $this->update($event->fresh(), ['translations_keep' => ['name']])->assertRedirect();

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertTrue($event->translationIsManual('name'));
        $this->assertFalse($event->translationIsStale('name'));
    }

    public function test_emptying_the_original_takes_the_correction_with_it(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['short_description' => 'Música ao vivo']]);

        $this->update($event->fresh(), ['short_description' => ''])->assertRedirect();

        $event->refresh();
        $this->assertNull($event->short_description_en);
        $this->assertFalse($event->translationIsManual('short_description'));
    }

    public function test_reset_to_automatic_forgets_the_correction_and_queues_the_field(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);
        Event::where('id', $event->id)->update(['translation_attempts' => 3]);

        $this->update($event->fresh(), ['translations' => ['name' => '']])->assertRedirect();

        $event->refresh();
        $this->assertNull($event->name_en);
        $this->assertFalse($event->translationIsManual('name'));
        $this->assertSame(0, (int) $event->translation_attempts);
        $this->assertContains($event->id, TranslationQueue::events($event->id)->pluck('id')->all());
    }

    public function test_nothing_but_the_correction_writes_a_corrected_field(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        // An import or a feed that fills the column on an event it updates.
        $event = $event->fresh();
        $event->name_en = 'Escrito por uma importação';
        $event->save();

        $this->assertSame('Noite de Jazz', $event->fresh()->name_en);
    }

    public function test_a_post_cannot_mark_a_translation_by_filling_the_column(): void
    {
        $event = $this->translatedEvent();

        $this->update($event, ['translation_overrides' => ['name' => ['source' => str_repeat('a', 40)]]]);

        $this->assertNull($event->fresh()->translation_overrides);
        $this->assertNotContains('translation_overrides', (new Event)->getFillable());
    }

    public function test_an_api_update_keeps_the_corrections_and_can_make_one(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        $raw = 'testapikey_'.Str::random(24);
        $this->owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $this->owner->api_key_hash = Hash::make($raw);
        $this->owner->save();
        $headers = ['X-API-Key' => $raw];
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        // A rename that never mentions translations.
        $this->putJson($url, ['name' => 'Jazz Night by the River'], $headers)->assertOk();
        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertTrue($event->translationIsStale('name'));

        // And a correction sent over the API.
        $this->putJson($url, ['translations' => ['short_description' => 'Música ao vivo']], $headers)->assertOk();
        $event->refresh();
        $this->assertSame('Música ao vivo', $event->short_description_en);
        $this->assertTrue($event->translationIsManual('short_description'));
        $this->assertTrue($event->translationIsManual('name'), 'the other correction is carried over');
    }

    public function test_a_copy_carries_the_corrections(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);
        $event->refresh();

        $payload = EventRepo::buildClonePayload($event);
        $this->assertTrue(isset($payload['event']['translation_overrides']['name']));
        $basis = $payload['event']['translation_overrides']['name']['source'];

        // The create form opened from the copy shows the row, with the correction to post back.
        $this->actingAs($this->owner)
            ->get(route('event.clone', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect();
        $html = $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $this->venue->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('id="event-translation"', $html);
        $this->assertStringContainsString($basis, $html);

        // What that form posts.
        $this->actingAs($this->owner)->post(route('event.store', ['subdomain' => $this->venue->subdomain]), [
            'name' => 'Jazz Night',
            'starts_at' => now()->addDays(20)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
            'translations' => ['name' => 'Noite de Jazz'],
            'translation_basis' => ['name' => $basis],
        ])->assertRedirect();

        $copy = Event::where('id', '!=', $event->id)->latest('id')->firstOrFail();
        $this->assertSame('Noite de Jazz', $copy->name_en);
        $this->assertTrue($copy->translationIsManual('name'));
        $this->assertFalse($copy->translationIsStale('name'));
    }

    public function test_a_new_target_language_discards_the_corrections(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        RegenerateRoleTranslations::dispatchSync($this->venue->fresh());

        $event->refresh();
        $this->assertNull($event->name_en);
        $this->assertFalse($event->translationIsManual('name'));
    }

    public function test_markup_in_a_translation_is_inert(): void
    {
        $event = $this->translatedEvent();

        $this->update($event, ['translations' => [
            'name' => 'Noite {{ constructor.constructor("alert(1)")() }}',
            'description' => "<script>alert(1)</script>Portas às sete.\n\n<img src=x onerror=alert(2)>",
        ]])->assertRedirect();

        $event->refresh();
        $this->assertStringNotContainsString('<script', $event->description_html_en);
        $this->assertStringNotContainsString('onerror', $event->description_html_en);

        // On the form the text reaches the page only inside the row's JSON data, never as markup
        // Vue would compile.
        $html = $this->editPage($event);
        $this->assertStringNotContainsString('<script>alert(1)', $html);
        // Inside a script it is JSON data (the row's, the event's own); anywhere else on the page it
        // would be markup the Vue mount compiles.
        $outsideScripts = preg_replace('~<script\b[^>]*>.*?</script>~s', '', $html);
        $this->assertStringContainsString('{{ constructor', $html, 'the probe reached the page at all');
        $this->assertStringNotContainsString('{{ constructor', $outsideScripts);
    }

    public function test_the_row_is_only_there_when_the_event_is_translated(): void
    {
        $event = $this->translatedEvent();
        $this->assertStringContainsString('id="event-translation"', $this->editPage($event));

        $this->venue->forceFill(['translation_language_code' => 'en'])->saveQuietly();
        $this->assertStringNotContainsString('id="event-translation"', $this->editPage($event->fresh()));
    }

    public function test_renaming_an_event_read_with_a_narrowed_select_keeps_the_correction(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        // The cast value handed back to the cast was a TypeError, and a 500 on the save.
        $narrow = Event::select('id', 'name', 'creator_role_id')->find($event->id);
        $narrow->name = 'Jazz Night Two';
        $narrow->save();

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertTrue($event->translationIsStale('name'));
    }

    public function test_a_curator_reading_the_target_language_is_shown_the_correction(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        $curator = $this->createRole($this->owner, 'curator', ['language_code' => 'pt', 'translation_language_code' => 'en']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // The curator pass buys no copy of a field the owner corrected into its language.
        $asked = [];
        GeminiUtils::fakeResponses(function ($prompt) use (&$asked) {
            $asked[] = $prompt;

            return ['CÓPIA DA IA'];
        });
        Artisan::call('app:translate', ['--event_id' => $event->id]);
        $pivot = EventRole::where('event_id', $event->id)->where('role_id', $curator->id)->first();
        $this->assertNull($pivot->name_translated);
        $this->assertSame('CÓPIA DA IA', $pivot->short_description_translated, 'what was not corrected is still translated');

        // And a copy made before the correction does not hide it.
        EventRole::where('id', $pivot->id)->update(['name_translated' => 'Noite de Jazz (IA)']);
        $event = Event::with('roles')->find($event->id);
        $this->assertSame('Noite de Jazz', $event->nameInLanguage('pt', $curator->fresh()));
    }

    public function test_a_refused_copy_keeps_its_corrections(): void
    {
        $basis = sha1('Jazz Night');
        $html = $this->actingAs($this->owner)
            ->withSession([
                '_old_input' => ['name' => 'Jazz Night', 'translations' => ['name' => 'Noite de Jazz'], 'translation_basis' => ['name' => $basis]],
                'errors' => (new ViewErrorBag)->put('default', new MessageBag(['starts_at' => ['x']])),
            ])
            ->get(route('event.create', ['subdomain' => $this->venue->subdomain]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('id="event-translation"', $html);
        $this->assertStringContainsString($basis, $html, 'the original it was made for rides again');
    }

    public function test_a_long_description_correction_is_saved(): void
    {
        $event = $this->translatedEvent();
        // Longer than the 16,000 characters the first cap allowed, within the column.
        $long = str_repeat('Portas às sete. ', 1500);

        $this->update($event, ['translations' => ['description' => $long]])->assertSessionHasNoErrors();

        $this->assertSame(trim($long), $event->fresh()->description_en);
    }

    public function test_a_copy_does_not_carry_corrections_in_another_language(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);
        $this->assertSame('pt', $event->fresh()->translationOverrides()['name']['lang']);

        // The schedule translates into French now (saved quietly: the job that clears the stored
        // translations is not what this is about; a template keeps its copy all the same).
        $this->venue->forceFill(['translation_language_code' => 'fr'])->saveQuietly();

        $this->actingAs($this->owner)->get(route('event.clone', ['subdomain' => $this->venue->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));
        $html = $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $this->venue->subdomain]))->assertOk()->getContent();

        // Nothing carried: no row, and nothing for the form to post as a correction.
        $this->assertStringNotContainsString('id="event-translation"', $html);
        $this->assertMatchesRegularExpression('/^\s*translation: null,$/m', $html);
    }

    public function test_a_correction_goes_when_the_event_translates_into_another_language(): void
    {
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);

        $this->venue->forceFill(['translation_language_code' => 'fr'])->saveQuietly();
        $this->update($event->fresh(), [])->assertRedirect();

        $event->refresh();
        $this->assertFalse($event->translationIsManual('name'));
        $this->assertNull($event->name_en);
        $this->assertContains($event->id, TranslationQueue::events($event->id)->pluck('id')->all());
    }

    public function test_a_reset_of_the_schedule_keeps_corrections_as_a_reset_of_the_event_does(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $event = $this->translatedEvent();
        $this->update($event, ['translations' => ['name' => 'Noite de Jazz']]);
        GeminiUtils::fakeResponses(fn () => ['NOVA']);

        Artisan::call('app:translate', ['--reset' => true, '--role_id' => $this->venue->id, '--no-interaction' => true]);

        $event->refresh();
        $this->assertSame('Noite de Jazz', $event->name_en);
        $this->assertTrue($event->translationIsManual('name'));
        $this->assertSame('NOVA', $event->short_description_en);
    }

    public function test_a_new_events_row_follows_the_venue_that_governs_it(): void
    {
        $talent = $this->createRole($this->owner, 'talent', ['language_code' => 'en', 'translation_language_code' => 'pt']);
        $venue = $this->createRole($this->owner, 'venue', ['language_code' => 'en', 'translation_language_code' => 'fr']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'name' => 'Jazz Night']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event = $event->fresh();
        $event->name_en = 'Soirée jazz';
        $event->markTranslationManual('name', null, 'fr');
        $event->save();

        $this->actingAs($this->owner)->get(route('event.clone', ['subdomain' => $talent->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));
        $html = $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $talent->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString(e(__('messages.event_translation_row', ['language' => __('messages.french')])), $html);
    }

    public function test_a_saved_event_is_translated_soon_after_through_the_crons_lock(): void
    {
        config(['services.google.gemini_key' => 'test-key', 'queue.default' => 'database']);
        Queue::fake();

        $event = $this->translatedEvent();
        Queue::assertPushed(TranslateEventSoon::class, fn ($job) => $job->eventId === $event->id);

        // The command's own save writes translations, never an original: nothing more is queued.
        Queue::fake();
        $event->name_en = 'Noite de Jazz';
        $event->save();
        Queue::assertNotPushed(TranslateEventSoon::class);

        // The job is the cron's run for one event, under the cron's lock.
        Event::where('id', $event->id)->update(['short_description_en' => null]);
        GeminiUtils::fakeResponses(fn () => ['MÚSICA']);
        $lock = Cache::lock('app_translate_lock', 600);
        $lock->get();
        (new TranslateEventSoon($event->id))->withFakeQueueInteractions()->handle();
        $this->assertNull($event->fresh()->short_description_en, 'not while the cron holds the lock');
        $lock->release();

        (new TranslateEventSoon($event->id))->handle();
        $this->assertSame('MÚSICA', $event->fresh()->short_description_en);
    }

    public function test_the_row_links_to_the_page_in_the_translation(): void
    {
        $this->assertStringContainsString('lang=pt', $this->editPage($this->translatedEvent()));
    }

    public function test_an_online_event_on_a_curator_follows_the_curators_languages(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $curator = $this->createRole($this->owner, 'curator', ['language_code' => 'en', 'translation_language_code' => 'pt-PT']);
        $online = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'name' => 'Online talk', 'event_url' => 'https://example.org/talk']);
        // A venue still governs an event at a venue, as it always has.
        $atVenue = $this->createEvent($this->venue, ['creator_role_id' => $curator->id, 'name' => 'Talk at the hall']);
        $atVenue->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->assertSame('en', Event::with('roles')->find($online->id)->getLanguageCode());
        $this->assertSame('pt-PT', Event::with('roles')->find($online->id)->getTranslationLanguageCode());
        $this->assertSame('pt', Event::with('roles')->find($atVenue->id)->getTranslationLanguageCode());

        // And the cron's own selection picks it up and translates it.
        GeminiUtils::fakeResponses(fn () => ['Palestra online']);
        Artisan::call('app:translate');
        $this->assertSame('Palestra online', $online->fresh()->name_en);
    }

    public function test_events_parked_as_english_to_english_are_taken_up_again(): void
    {
        $curator = $this->createRole($this->owner, 'curator', ['language_code' => 'en', 'translation_language_code' => 'pt-PT']);
        $parked = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'name' => 'Online talk']);
        $atVenue = $this->createEvent($this->venue, ['creator_role_id' => $this->venue->id, 'name' => 'Talk at the hall']);
        Event::whereIn('id', [$parked->id, $atVenue->id])->update(['name_en' => '', 'translation_attempts' => 3]);

        (require database_path('migrations/2026_11_12_000008_translate_events_their_creator_translates.php'))->up();

        $this->assertNull($parked->fresh()->name_en);
        $this->assertSame(0, (int) $parked->fresh()->translation_attempts);
        $this->assertSame('', $atVenue->fresh()->name_en, 'an event at a venue is left as it is');
    }
}
