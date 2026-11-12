<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TranslationGlossaryEntry;
use App\Models\User;
use App\Services\BackupService;
use App\Services\TranslationGlossary;
use App\Utils\GeminiUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's translation glossary (Details > Translation glossary, App\Services\TranslationGlossary):
 * saved from the schedule form only when the form drew it, passed to every translation of the
 * schedule's content, checked in code after the model answers, and, when it changes, handing back
 * to the translator only what uses a changed term.
 */
class TranslationGlossaryTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @var array<int, string> every prompt the fake provider was sent */
    private array $prompts = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.gemini_key' => 'test-key']);
    }

    protected function tearDown(): void
    {
        GeminiUtils::fakeResponses(null);
        TranslationGlossary::forget();

        parent::tearDown();
    }

    /** Answer every prompt with $answer($prompt), recording what was asked. */
    private function fakeProvider(\Closure $answer): void
    {
        GeminiUtils::fakeResponses(function ($prompt) use ($answer) {
            $this->prompts[] = $prompt;

            return [$answer($prompt)];
        });
    }

    private function venue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + [
            'name' => 'Casa Comum',
            'language_code' => 'en',
            'translation_language_code' => 'pt',
            // Nothing of the schedule's own left to translate unless a test says so.
            'name_en' => 'Casa Comum',
        ]);
    }

    private function glossary(Role $role, array $entries): void
    {
        foreach (array_values($entries) as $position => [$term, $translation]) {
            TranslationGlossaryEntry::create(['role_id' => $role->id, 'term' => $term, 'translation' => $translation, 'position' => $position]);
        }
    }

    private function save(Role $role, array $fields): TestResponse
    {
        return $this->actingAs(User::find($role->user_id))
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    private function terms(Role $role): array
    {
        return array_map(fn ($e) => [$e['term'], $e['translation']], TranslationGlossary::entries($role->fresh()));
    }

    // ------------------------------------------------------------------
    // Saving
    // ------------------------------------------------------------------

    public function test_the_form_saves_the_list_in_its_order_and_cleans_it(): void
    {
        $role = $this->venue();

        $this->save($role, [
            'glossary_submitted' => '1',
            'glossary' => [
                ['term' => '  Drinx ', 'translation' => ''],
                ['term' => '', 'translation' => 'nothing to translate'],
                ['term' => 'Drink & Draw', 'translation' => null],
                ['term' => 'drinx', 'translation' => 'a repeat'],
                ['term' => 'open mic', 'translation' => 'microfone aberto'],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame([['Drinx', null], ['Drink & Draw', null], ['open mic', 'microfone aberto']], $this->terms($role));
    }

    /** Absent is not empty: a save from anywhere that did not draw the list leaves it alone. */
    public function test_a_save_without_the_marker_keeps_the_list_and_one_with_it_can_empty_it(): void
    {
        $role = $this->venue();
        $this->glossary($role, [['Drinx', null]]);

        $this->save($role, ['glossary' => []])->assertSessionHasNoErrors();
        $this->assertSame([['Drinx', null]], $this->terms($role), 'no marker, no change');

        $this->save($role, ['glossary_submitted' => '1'])->assertSessionHasNoErrors();
        $this->assertSame([], $this->terms($role), 'the marker and no rows: the owner removed them all');
    }

    public function test_the_list_and_its_terms_are_capped(): void
    {
        $role = $this->venue();
        $this->glossary($role, [['Drinx', null]]);

        $rows = array_map(fn ($i) => ['term' => 'Term '.$i, 'translation' => ''], range(1, TranslationGlossary::MAX_ENTRIES + 1));
        $this->save($role, ['glossary_submitted' => '1', 'glossary' => $rows])->assertSessionHasErrors('glossary');

        $this->save($role, ['glossary_submitted' => '1', 'glossary' => [['term' => str_repeat('x', TranslationGlossary::MAX_LENGTH + 1)]]])
            ->assertSessionHasErrors('glossary.0.term');

        $this->assertSame([['Drinx', null]], $this->terms($role), 'a refused save changes nothing');

        // And save() holds the cap itself, for the backup's restore.
        TranslationGlossary::save($role, $rows);
        $this->assertCount(TranslationGlossary::MAX_ENTRIES, $this->terms($role));
    }

    public function test_the_form_draws_the_row_only_while_translation_is_on(): void
    {
        $role = $this->venue();
        $this->glossary($role, [['Drinx', null], ['open mic', 'microfone aberto']]);

        $html = $this->actingAs(User::find($role->user_id))->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<button type="button" class="event-subrow details-tab"\s+data-row-group="details" data-tab="glossary"/', $html, 'shown, not hidden');
        $this->assertStringContainsString('name="glossary_submitted" value="1"', $html);
        $this->assertStringContainsString('value="microfone aberto"', $html);
        $this->assertStringContainsString('placeholder="'.e(__('messages.glossary_keep_as_written')).'"', $html, 'an empty translation reads as keeping the term');
        $this->assertStringContainsString('aria-label="'.e(__('messages.glossary_translation_into', ['language' => $role->translationLanguageName()])).'"', $html, 'the box names its language');
        $this->assertStringContainsString('data-open-glossary', $html, 'the translation switch points at the glossary');
        $this->assertStringContainsString("['section-details', '#glossary-items']", $html, 'the list is watched, so a removal is announced');

        // An empty list opens on one empty row, ready for the first term.
        $empty = $this->venue();
        $html = $this->actingAs(User::find($empty->user_id))->get(route('role.edit', ['subdomain' => $empty->subdomain]))->getContent();
        $this->assertMatchesRegularExpression('/name="glossary\[0\]\[term\]" value=""/', $html);

        $role->forceFill(['translation_language_code' => 'en'])->saveQuietly();
        $html = $this->actingAs(User::find($role->user_id))->get(route('role.edit', ['subdomain' => $role->subdomain]))->getContent();
        $this->assertMatchesRegularExpression('/<button type="button" class="event-subrow details-tab" hidden="hidden"\s+data-row-group="details" data-tab="glossary"/', $html, 'translation off: the row is hidden, and its list still posted');
        $this->assertStringContainsString('name="glossary_submitted" value="1"', $html);
    }

    // ------------------------------------------------------------------
    // The prompt
    // ------------------------------------------------------------------

    public function test_terms_kept_as_written_have_a_rule_of_their_own_in_the_prompt(): void
    {
        $prompt = GeminiUtils::buildTranslatePrompt('Join Drinx tonight for open mic.', 'en', 'pt', ['open mic' => 'microfone aberto', 'Drinx' => null]);

        $this->assertStringContainsString('- "open mic" => "microfone aberto"', $prompt);
        $this->assertStringContainsString('Keep these names and terms exactly as written', $prompt);
        $this->assertStringContainsString('- "Drinx"', $prompt);
        $this->assertStringNotContainsString('"Drinx" =>', $prompt);
        $this->assertStringNotContainsString("\n ", $prompt, 'no line starts with a stray space');
        $this->assertStringEndsWith("\nJoin Drinx tonight for open mic.", $prompt);
    }

    public function test_an_events_translation_carries_the_terms_it_uses_and_no_others(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drinx', null], ['Drink & Draw', null], ['open mic', 'microfone aberto']]);
        $event = $this->createEvent($venue, ['name' => 'Late night at Drinx', 'description' => 'Bring a poem for the open mic.']);

        $this->fakeProvider(fn ($prompt) => 'Noite em Drinx');
        Artisan::call('app:translate', ['--event_id' => $event->id]);

        $namePrompt = collect($this->prompts)->first(fn ($p) => str_ends_with($p, "\nLate night at Drinx"));
        $this->assertNotNull($namePrompt, 'the name was sent');
        $this->assertStringContainsString('- "Drinx"', $namePrompt);
        $this->assertStringNotContainsString('Drink & Draw', $namePrompt, 'only terms the text uses');
        $this->assertStringNotContainsString('microfone aberto', $namePrompt);

        $descriptionPrompt = collect($this->prompts)->first(fn ($p) => str_ends_with($p, "\nBring a poem for the open mic."));
        $this->assertStringContainsString('- "open mic" => "microfone aberto"', $descriptionPrompt);
    }

    public function test_a_text_that_is_a_term_is_answered_by_the_glossary(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drink & Draw', null]]);
        $event = $this->createEvent($venue, ['name' => 'Drink & Draw']);

        $this->fakeProvider(fn ($prompt) => 'Beber e Desenhar');
        Artisan::call('app:translate', ['--event_id' => $event->id]);

        $this->assertSame('Drink & Draw', $event->fresh()->name_en);
        $this->assertSame([], $this->prompts, 'nobody was asked');
    }

    public function test_the_schedules_own_text_and_its_batches_carry_the_glossary(): void
    {
        $venue = $this->venue(['description' => 'Home of Drinx and friends.', 'event_categories' => [['id' => 1, 'name' => 'Drink & Draw']]]);
        $this->glossary($venue, [['Drinx', null], ['Drink & Draw', null]]);

        $this->fakeProvider(fn ($prompt) => str_contains($prompt, 'Return ONLY a raw JSON object') ? [] : 'Casa de Drinx e amigos.');
        Artisan::call('app:translate', ['--role_id' => $venue->id]);

        $descriptionPrompt = collect($this->prompts)->first(fn ($p) => str_ends_with($p, "\nHome of Drinx and friends."));
        $this->assertNotNull($descriptionPrompt);
        $this->assertStringContainsString('- "Drinx"', $descriptionPrompt);

        $venue->refresh();
        $this->assertSame('Casa de Drinx e amigos.', $venue->description_en);
        $this->assertSame('Drink & Draw', $venue->event_categories[0]['name_en'] ?? null, 'a category named by a term needs no answer');
    }

    public function test_a_sub_schedule_name_is_translated_with_the_glossary(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drinx', null]]);

        $this->fakeProvider(fn ($prompt) => ['Drinx Sessions' => 'Sessões Drinx']);
        $translations = GeminiUtils::translateGroupNames(['Drinx Sessions'], 'en', 'pt', TranslationGlossary::relevant(TranslationGlossary::forRole($venue), ['Drinx Sessions']));

        $this->assertSame('Sessões Drinx', $translations['Drinx Sessions']);
        $this->assertStringStartsWith('Keep these names and terms exactly as written', $this->prompts[0]);
        $this->assertStringContainsString('- "Drinx"', $this->prompts[0]);
    }

    /** A prompt is a request: a kept term the answer lost is asked for once more, naming it. */
    public function test_a_lost_term_is_asked_for_once_more_and_then_kept_or_logged(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drinx', null]]);
        $event = $this->createEvent($venue, ['name' => 'Quiz at Drinx']);

        $this->fakeProvider(fn ($prompt) => str_contains($prompt, 'a previous translation of this text changed') ? 'Quiz no Drinx' : 'Quiz no Drinques');
        Artisan::call('app:translate', ['--event_id' => $event->id]);

        $this->assertCount(2, $this->prompts);
        $this->assertStringContainsString('changed "Drinx"', $this->prompts[1]);
        $this->assertSame('Quiz no Drinx', $event->fresh()->name_en);

        // Lost twice: the translation is kept (an otherwise good one beats none) and the miss logged.
        $other = $this->createEvent($venue, ['name' => 'Bingo at Drinx']);
        $this->prompts = [];
        $this->fakeProvider(fn ($prompt) => 'Bingo no Drinques');
        Artisan::call('app:translate', ['--event_id' => $other->id]);

        $this->assertCount(2, $this->prompts);
        $this->assertSame('Bingo no Drinques', $other->fresh()->name_en);
    }

    // ------------------------------------------------------------------
    // After a change
    // ------------------------------------------------------------------

    public function test_a_change_hands_back_only_what_uses_the_term_and_does_not_already_follow_it(): void
    {
        $venue = $this->venue();

        $uses = $this->createEvent($venue, ['name' => 'Night at Drinx', 'description' => 'A quiet evening.']);
        $alreadyRight = $this->createEvent($venue, ['name' => 'Morning at Drinx']);
        $unrelated = $this->createEvent($venue, ['name' => 'Poetry night']);
        $past = $this->createEvent($venue, ['name' => 'Last year at Drinx', 'starts_at' => Carbon::now()->subMonths(2)->format('Y-m-d H:i:s')]);
        $byHand = $this->createEvent($venue, ['name' => 'Drinx after dark']);

        DB::table('events')->where('id', $uses->id)->update(['name_en' => 'Noite no Bebidas', 'description_en' => 'Uma noite calma.', 'translation_attempts' => 2]);
        DB::table('events')->where('id', $alreadyRight->id)->update(['name_en' => 'Manhã no Drinx']);
        DB::table('events')->where('id', $unrelated->id)->update(['name_en' => 'Noite de poesia']);
        DB::table('events')->where('id', $past->id)->update(['name_en' => 'Ano passado no Bebidas']);
        DB::table('events')->where('id', $byHand->id)->update(['name_en' => 'Bebidas depois do escuro']);
        if (Schema::hasColumn('events', 'translation_overrides')) {
            DB::table('events')->where('id', $byHand->id)->update(['translation_overrides' => json_encode(['name' => ['source' => sha1('Drinx after dark'), 'at' => now()->toIso8601String()]])]);
        }

        $this->fakeProvider(fn ($prompt) => []);
        $this->save($venue, ['glossary_submitted' => '1', 'glossary' => [['term' => 'Drinx', 'translation' => '']]])->assertSessionHasNoErrors();

        $uses->refresh();
        $this->assertNull($uses->name_en, 'uses the term and lost it: translated again');
        $this->assertSame(0, (int) $uses->translation_attempts);
        $this->assertSame('Uma noite calma.', $uses->description_en, 'only the field that uses the term');
        $this->assertSame('Manhã no Drinx', $alreadyRight->fresh()->name_en, 'already reads the way the glossary says');
        $this->assertSame('Noite de poesia', $unrelated->fresh()->name_en, 'does not use the term');
        $this->assertSame('Ano passado no Bebidas', $past->fresh()->name_en, 'past: the cron would never translate it again');
        if (Schema::hasColumn('events', 'translation_overrides')) {
            $this->assertSame('Bebidas depois do escuro', $byHand->fresh()->name_en, 'a correction made by hand stays');
        }

        // Saving the same list again changes nothing and hands nothing back.
        DB::table('events')->where('id', $uses->id)->update(['name_en' => 'Noite no Bebidas']);
        $this->save($venue, ['glossary_submitted' => '1', 'glossary' => [['term' => 'Drinx', 'translation' => '']]]);
        $this->assertSame('Noite no Bebidas', $uses->fresh()->name_en);
    }

    public function test_a_removed_term_hands_back_what_it_kept_and_the_schedules_own_text_follows_too(): void
    {
        $venue = $this->venue(['short_description' => 'Drinx bar and gallery']);
        DB::table('roles')->where('id', $venue->id)->update(['short_description_en' => 'Drinx bar e galeria']);
        $this->glossary($venue, [['Drinx', null]]);
        $event = $this->createEvent($venue, ['name' => 'Night at Drinx']);
        DB::table('events')->where('id', $event->id)->update(['name_en' => 'Noite no Drinx']);

        $counts = TranslationGlossary::requeue($venue, TranslationGlossary::save($venue, []));

        $this->assertNull($event->fresh()->name_en);
        $this->assertNull($venue->fresh()->short_description_en);
        $this->assertSame(1, $counts['events']);
        $this->assertSame(1, $counts['schedule']);
    }

    // ------------------------------------------------------------------
    // Backup
    // ------------------------------------------------------------------

    public function test_the_glossary_survives_a_backup_and_restore(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drinx', null], ['open mic', 'microfone aberto']]);

        $service = app(BackupService::class);
        $ref = new \ReflectionClass($service);
        $export = $ref->getMethod('exportRole');
        $export->setAccessible(true);
        $imageFiles = [];
        $args = [$venue->fresh(), false, &$imageFiles];
        $exported = $export->invokeArgs($service, $args);

        $this->assertSame([['term' => 'Drinx', 'translation' => null], ['term' => 'open mic', 'translation' => 'microfone aberto']], $exported['role']['translation_glossary']);

        $import = $ref->getMethod('importRole');
        $import->setAccessible(true);
        $restored = $import->invokeArgs($service, [$exported['role'], $venue->user_id]);

        $this->assertNotSame($venue->id, $restored->id);
        $this->assertSame([['Drinx', null], ['open mic', 'microfone aberto']], $this->terms($restored));
    }

    public function test_the_glossary_goes_with_its_schedule(): void
    {
        $venue = $this->venue();
        $this->glossary($venue, [['Drinx', null]]);

        DB::table('roles')->where('id', $venue->id)->delete();

        $this->assertSame(0, TranslationGlossaryEntry::count());
    }
}
