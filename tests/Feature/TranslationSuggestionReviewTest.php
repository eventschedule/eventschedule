<?php

namespace Tests\Feature;

use App\Models\TranslationSuggestion;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ResetsTranslationOverrides;
use Tests\TestCase;

class TranslationSuggestionReviewTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use ResetsTranslationOverrides;

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    private function createSuggestion(array $overrides = []): TranslationSuggestion
    {
        return TranslationSuggestion::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'home',
            'suggested_value' => 'Maison',
            'shipped_value' => 'Accueil',
            'app_version' => 'v1.0.118',
            'status' => 'pending',
        ], $overrides));
    }

    public function test_queue_data_groups_identical_suggestions_across_instances(): void
    {
        $admin = $this->createOwner(true);
        $this->createSuggestion(['app_version' => 'v1.0.117']);
        $this->createSuggestion(['app_version' => 'v1.0.118']);
        $this->createSuggestion(['suggested_value' => 'Chez moi']);

        $response = $this->adminActing($admin)->getJson(route('admin.translations.suggestions.data'));

        $response->assertOk()->assertJsonCount(2, 'rows');
        $rows = collect($response->json('rows'));

        $grouped = $rows->firstWhere('suggested', 'Maison');
        $this->assertSame(2, $grouped['instance_count']);
        $this->assertEqualsCanonicalizing(['v1.0.117', 'v1.0.118'], $grouped['app_versions']);
        $this->assertSame('Accueil', $grouped['nexus_shipped']);
        $this->assertSame('Home', $grouped['en']);
        $this->assertFalse($grouped['warnings']['html']);
    }

    public function test_queue_data_flags_html_and_placeholder_regressions(): void
    {
        $admin = $this->createOwner(true);
        $this->createSuggestion([
            'key' => 'followed_role',
            'suggested_value' => '<script>alert(1)</script>',
        ]);

        $row = $this->adminActing($admin)
            ->getJson(route('admin.translations.suggestions.data'))
            ->json('rows.0');

        $this->assertTrue($row['warnings']['html']);
        $this->assertContains(':name', $row['warnings']['quality']['placeholders']);
    }

    public function test_approving_applies_a_live_override_and_covers_identical_suggestions(): void
    {
        $admin = $this->createOwner(true);
        $first = $this->createSuggestion();
        $twin = $this->createSuggestion();
        $other = $this->createSuggestion(['suggested_value' => 'Chez moi']);

        $this->adminActing($admin)
            ->postJson(route('admin.translations.suggestions.approve', ['hash' => UrlUtils::encodeId($first->id)]))
            ->assertOk()
            ->assertJson(['approved' => 2]);

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('approved', $twin->fresh()->status);
        $this->assertSame('pending', $other->fresh()->status);
        $this->assertSame($admin->id, $first->fresh()->reviewed_by);

        // The approval is live on nexus: override row, published file, translator.
        $this->assertDatabaseHas('translation_overrides', ['locale' => 'fr', 'key' => 'home', 'value' => 'Maison']);
        app()->setLocale('fr');
        $this->assertSame('Maison', __('messages.home'));
        app()->setLocale('en');

        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translation_suggestion_approve']);
    }

    public function test_approving_with_an_edited_value_stores_the_edit(): void
    {
        $admin = $this->createOwner(true);
        $suggestion = $this->createSuggestion();

        $this->adminActing($admin)
            ->postJson(route('admin.translations.suggestions.approve', ['hash' => UrlUtils::encodeId($suggestion->id)]), [
                'value' => 'Page d\'accueil',
            ])
            ->assertOk();

        $this->assertSame('approved', $suggestion->fresh()->status);
        $this->assertDatabaseHas('translation_overrides', ['key' => 'home', 'value' => 'Page d\'accueil']);
    }

    public function test_rejecting_marks_the_group_without_touching_translations(): void
    {
        $admin = $this->createOwner(true);
        $suggestion = $this->createSuggestion();

        $this->adminActing($admin)
            ->postJson(route('admin.translations.suggestions.reject', ['hash' => UrlUtils::encodeId($suggestion->id)]))
            ->assertOk()
            ->assertJson(['rejected' => 1]);

        $this->assertSame('rejected', $suggestion->fresh()->status);
        $this->assertDatabaseCount('translation_overrides', 0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translation_suggestion_reject']);
    }

    public function test_bulk_review_processes_multiple_suggestions(): void
    {
        $admin = $this->createOwner(true);
        $first = $this->createSuggestion();
        $second = $this->createSuggestion(['key' => 'back', 'suggested_value' => 'En arriere', 'shipped_value' => null]);

        $this->adminActing($admin)->postJson(route('admin.translations.suggestions.bulk'), [
            'action' => 'approve',
            'hashes' => [UrlUtils::encodeId($first->id), UrlUtils::encodeId($second->id)],
        ])->assertOk()->assertJson(['processed' => 2]);

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('approved', $second->fresh()->status);
        $this->assertDatabaseCount('translation_overrides', 2);
    }

    public function test_export_returns_paste_ready_lines_for_approved_keys(): void
    {
        $admin = $this->createOwner(true);
        $approved = $this->createSuggestion(['suggested_value' => "L'accueil"]);
        $this->createSuggestion(['key' => 'back', 'suggested_value' => 'En arriere', 'shipped_value' => null]);

        $this->adminActing($admin)
            ->postJson(route('admin.translations.suggestions.approve', ['hash' => UrlUtils::encodeId($approved->id)]))
            ->assertOk();

        $response = $this->adminActing($admin)->get(route('admin.translations.suggestions.export', [
            'locale' => 'fr',
            'group' => 'messages',
        ]));

        $response->assertOk()->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        // Only the approved key, var_export-escaped, lang-file formatted.
        $this->assertSame("    'home' => 'L\\'accueil',\n", $response->getContent());
    }

    public function test_review_endpoints_are_hidden_off_nexus_and_from_non_admins(): void
    {
        $suggestion = $this->createSuggestion();
        $hash = UrlUtils::encodeId($suggestion->id);

        $user = $this->createOwner();
        $this->actingAs($user)->getJson(route('admin.translations.suggestions.data'))->assertForbidden();

        $admin = $this->createOwner(true);
        config(['app.is_nexus' => false]);

        $this->adminActing($admin)->get(route('admin.translations.suggestions'))->assertNotFound();
        $this->adminActing($admin)->getJson(route('admin.translations.suggestions.data'))->assertNotFound();
        $this->adminActing($admin)->postJson(route('admin.translations.suggestions.approve', ['hash' => $hash]))->assertNotFound();
        $this->adminActing($admin)->postJson(route('admin.translations.suggestions.reject', ['hash' => $hash]))->assertNotFound();
        $this->adminActing($admin)->getJson(route('admin.translations.suggestions.export', ['locale' => 'fr', 'group' => 'messages']))->assertNotFound();
    }

    public function test_suggestions_page_renders_on_nexus(): void
    {
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->get(route('admin.translations.suggestions'))->assertOk();
    }
}
