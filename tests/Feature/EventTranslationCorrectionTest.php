<?php

namespace Tests\Feature;

use App\Jobs\RegenerateRoleTranslations;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\TranslationQueue;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
}
