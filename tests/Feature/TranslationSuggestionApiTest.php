<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ApiTranslationSuggestionController;
use App\Models\TranslationSuggestion;
use App\Services\TranslationOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\ResetsTranslationOverrides;
use Tests\TestCase;

class TranslationSuggestionApiTest extends TestCase
{
    use RefreshDatabase;
    use ResetsTranslationOverrides;

    private const ENDPOINT = '/api/translations/suggestions';

    private function payload(array $items, ?string $instanceId = null): array
    {
        return [
            'instance_id' => $instanceId ?? (string) Str::uuid(),
            'app_version' => 'v1.0.118',
            'items' => $items,
        ];
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'home',
            'value' => 'Maison',
            'shipped_value' => 'Accueil',
        ], $overrides);
    }

    public function test_valid_suggestions_are_accepted_and_stored(): void
    {
        $payload = $this->payload([
            $this->item(),
            $this->item(['key' => 'unknown_key_from_the_future', 'value' => 'Nope']),
        ]);

        $this->postJson(self::ENDPOINT, $payload)
            ->assertOk()
            ->assertJson(['accepted' => 1, 'skipped' => 1]);

        $this->assertDatabaseHas('translation_suggestions', [
            'instance_id' => $payload['instance_id'],
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'home',
            'suggested_value' => 'Maison',
            'shipped_value' => 'Accueil',
            'app_version' => 'v1.0.118',
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('translation_suggestions', 1);
    }

    public function test_invalid_payloads_are_rejected(): void
    {
        // Bad instance id
        $this->postJson(self::ENDPOINT, $this->payload([$this->item()], 'not-a-uuid'))
            ->assertUnprocessable();

        // Unsupported locale
        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['locale' => 'xx'])]))
            ->assertUnprocessable();

        // Unsupported file
        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['group' => 'config'])]))
            ->assertUnprocessable();

        // Oversize batch
        $items = array_fill(0, 201, $this->item());
        $this->postJson(self::ENDPOINT, $this->payload($items))->assertUnprocessable();

        // Oversize value
        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['value' => str_repeat('x', 5001)])]))
            ->assertUnprocessable();

        $this->assertDatabaseCount('translation_suggestions', 0);
    }

    public function test_values_matching_the_live_translation_are_skipped(): void
    {
        // 'Accueil' is exactly what this nexus install already serves for fr home.
        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['value' => 'Accueil'])]))
            ->assertOk()
            ->assertJson(['accepted' => 0, 'skipped' => 1]);

        // With a nexus override in place, the override is the live value.
        app(TranslationOverrideService::class)->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);

        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['value' => 'Maison'])]))
            ->assertOk()
            ->assertJson(['accepted' => 0, 'skipped' => 1]);

        $this->assertDatabaseCount('translation_suggestions', 0);
    }

    public function test_resharing_updates_in_place_and_only_changed_values_reenter_the_queue(): void
    {
        $instanceId = (string) Str::uuid();

        $this->postJson(self::ENDPOINT, $this->payload([$this->item()], $instanceId))->assertOk();
        $suggestion = TranslationSuggestion::firstOrFail();
        $suggestion->update(['status' => 'rejected', 'reviewed_at' => now()]);

        // Same value again: stays rejected, no duplicate row.
        $this->postJson(self::ENDPOINT, $this->payload([$this->item()], $instanceId))
            ->assertOk()
            ->assertJson(['accepted' => 1]);
        $this->assertDatabaseCount('translation_suggestions', 1);
        $this->assertSame('rejected', $suggestion->fresh()->status);

        // A changed value re-enters the review queue.
        $this->postJson(self::ENDPOINT, $this->payload([$this->item(['value' => 'Chez moi'])], $instanceId))
            ->assertOk();
        $this->assertDatabaseCount('translation_suggestions', 1);
        $fresh = $suggestion->fresh();
        $this->assertSame('pending', $fresh->status);
        $this->assertSame('Chez moi', $fresh->suggested_value);
        $this->assertNull($fresh->reviewed_at);
    }

    public function test_control_characters_are_stripped_from_values(): void
    {
        $this->postJson(self::ENDPOINT, $this->payload([
            $this->item(['value' => "Mai\x00son\x07 line\nbreak\ttab"]),
        ]))->assertOk()->assertJson(['accepted' => 1]);

        $this->assertSame("Maison line\nbreak\ttab", TranslationSuggestion::first()->suggested_value);
    }

    public function test_rtl_bidi_format_characters_are_preserved(): void
    {
        // U+200F (RIGHT-TO-LEFT MARK) is a legitimate Cf format char used to lay
        // out a placeholder inside Arabic/Hebrew text - an interior mark must
        // survive intake. (Leading/trailing marks are trimmed by the framework's
        // TrimStrings middleware, which is separate, expected behavior.)
        $value = "مرحبا \u{200F}:name\u{200F} شكرا";

        $this->postJson(self::ENDPOINT, $this->payload([
            $this->item(['locale' => 'ar', 'value' => $value]),
        ]))->assertOk()->assertJson(['accepted' => 1]);

        $this->assertSame($value, TranslationSuggestion::first()->suggested_value);
    }

    public function test_a_value_that_is_only_control_characters_is_skipped_not_stored_empty(): void
    {
        // Stripping leaves nothing; storing an empty suggestion would self-clean
        // (revert) on approval, so it must be skipped instead.
        $this->postJson(self::ENDPOINT, $this->payload([
            $this->item(['value' => "\x00\x07\x1b"]),
        ]))->assertOk()->assertJson(['accepted' => 0, 'skipped' => 1]);

        $this->assertDatabaseCount('translation_suggestions', 0);
    }

    public function test_an_instance_hitting_the_pending_cap_is_rejected(): void
    {
        $instanceId = (string) Str::uuid();
        $now = now();

        foreach (array_chunk(range(1, ApiTranslationSuggestionController::MAX_PENDING_PER_INSTANCE), 1000) as $chunk) {
            DB::table('translation_suggestions')->insert(array_map(fn ($i) => [
                'instance_id' => $instanceId,
                'locale' => 'fr',
                'group' => 'messages',
                'key' => 'filler_key_'.$i,
                'suggested_value' => 'x',
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk));
        }

        $this->postJson(self::ENDPOINT, $this->payload([$this->item()], $instanceId))
            ->assertUnprocessable()
            ->assertJson(['error' => 'Suggestion limit reached']);
    }

    public function test_the_endpoint_is_hidden_off_nexus(): void
    {
        config(['app.is_nexus' => false]);

        $this->postJson(self::ENDPOINT, $this->payload([$this->item()]))->assertNotFound();
    }
}
