<?php

namespace Tests\Feature;

use App\Models\TranslationOverride;
use App\Services\TranslationOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ResetsTranslationOverrides;
use Tests\TestCase;

class TranslationOverrideTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use ResetsTranslationOverrides;

    private function service(): TranslationOverrideService
    {
        return app(TranslationOverrideService::class);
    }

    private function overrideFilePath(string $locale, string $group = 'messages'): string
    {
        return config('app.lang_overrides_path')."/{$locale}/{$group}.php";
    }

    public function test_saving_an_override_publishes_a_sparse_file_and_wins_over_shipped(): void
    {
        $result = $this->service()->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);

        $this->assertSame(1, $result['saved']);
        $this->assertSame([], $result['warnings']);
        $this->assertCount(1, $result['savedHashes']);
        $this->assertDatabaseHas('translation_overrides', [
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'home',
            'value' => 'Maison',
        ]);

        $file = $this->overrideFilePath('fr');
        $this->assertFileExists($file);
        $published = require $file;
        $this->assertSame(['home' => 'Maison'], $published);

        app()->setLocale('fr');
        $this->assertSame('Maison', __('messages.home'));
        app()->setLocale('en');
    }

    public function test_english_overrides_win_over_shipped_english(): void
    {
        $this->service()->saveOverrides('en', 'messages', ['home' => 'Dashboard Home'], null);

        $this->assertSame('Dashboard Home', __('messages.home'));
    }

    public function test_reverting_restores_the_shipped_translation_and_removes_the_file(): void
    {
        $service = $this->service();
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);

        $removed = $service->revert('fr', 'messages', ['home']);

        $this->assertSame(1, $removed);
        $this->assertDatabaseCount('translation_overrides', 0);
        $this->assertFileDoesNotExist($this->overrideFilePath('fr'));

        app()->setLocale('fr');
        $this->assertSame('Accueil', __('messages.home'));
        app()->setLocale('en');
    }

    public function test_saving_the_shipped_value_or_an_empty_value_deletes_the_override(): void
    {
        $service = $this->service();
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);

        // Value equal to the shipped translation removes the override.
        $result = $service->saveOverrides('fr', 'messages', ['home' => 'Accueil'], null);
        $this->assertSame(['saved' => 0, 'removed' => 1], ['saved' => $result['saved'], 'removed' => $result['removed']]);
        $this->assertDatabaseCount('translation_overrides', 0);

        // A null (or empty) value also removes it.
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);
        $result = $service->saveOverrides('fr', 'messages', ['home' => null], null);
        $this->assertSame(1, $result['removed']);
        $this->assertDatabaseCount('translation_overrides', 0);
    }

    public function test_keys_missing_from_the_english_catalog_are_ignored(): void
    {
        $result = $this->service()->saveOverrides('fr', 'messages', [
            'this_key_does_not_exist_xyz' => 'Valeur',
        ], null);

        $this->assertSame(0, $result['saved']);
        $this->assertDatabaseCount('translation_overrides', 0);
    }

    public function test_placeholder_and_plural_warnings_are_reported_but_not_blocking(): void
    {
        $result = $this->service()->saveOverrides('fr', 'messages', [
            // Shipped value contains :name; the edit drops it.
            'followed_role' => 'Abonnement reussi',
            // Shipped value uses plural pipes; the edit collapses them.
            'seats_left' => ':count places restantes',
        ], null);

        $this->assertSame(2, $result['saved']);
        $this->assertContains(':name', $result['warnings']['followed_role']['placeholders']);
        $this->assertTrue($result['warnings']['seats_left']['plural']);
        $this->assertDatabaseCount('translation_overrides', 2);
    }

    public function test_invalid_locale_or_group_is_rejected_before_touching_the_filesystem(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service()->saveOverrides('../../etc', 'messages', ['home' => 'x'], null);
    }

    public function test_editing_an_override_resets_its_shared_state(): void
    {
        $service = $this->service();
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);
        TranslationOverride::query()->update(['shared_at' => now()]);

        // Saving the same value again is a no-op and keeps shared_at.
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);
        $this->assertNotNull(TranslationOverride::first()->shared_at);

        // Saving a different value resets shared_at so it counts as unshared.
        $service->saveOverrides('fr', 'messages', ['home' => 'Chez moi'], null);
        $this->assertNull(TranslationOverride::first()->shared_at);
    }

    public function test_publish_all_rebuilds_files_and_prunes_junk_ones(): void
    {
        $service = $this->service();
        $service->saveOverrides('fr', 'messages', ['home' => 'Maison'], null);

        // A file for an unsupported locale can never be adopted and is pruned.
        File::ensureDirectoryExists(dirname($this->overrideFilePath('xx')));
        File::put($this->overrideFilePath('xx'), "<?php\n\nreturn ['home' => 'Junk'];\n");

        // Simulate a lost file for a locale that has overrides.
        File::delete($this->overrideFilePath('fr'));

        $this->artisan('translations:publish')->assertSuccessful();

        $this->assertFileExists($this->overrideFilePath('fr'));
        $this->assertFileDoesNotExist($this->overrideFilePath('xx'));
        $this->assertSame(['home' => 'Maison'], require $this->overrideFilePath('fr'));
    }

    public function test_publish_all_leaves_unmanaged_override_files_untouched(): void
    {
        $this->service()->saveOverrides('es', 'messages', ['home' => 'Pagina principal'], null);

        // A hand-made override for a group we do NOT manage (validation.php etc.)
        // is honored by the translator loader and must survive translations:publish.
        File::ensureDirectoryExists(dirname($this->overrideFilePath('es', 'validation')));
        File::put($this->overrideFilePath('es', 'validation'), "<?php\n\nreturn ['required' => 'Requerido'];\n");

        $this->artisan('translations:publish')->assertSuccessful();

        $this->assertFileExists($this->overrideFilePath('es', 'validation'));
        $this->assertSame(['required' => 'Requerido'], require $this->overrideFilePath('es', 'validation'));
        $this->assertFileExists($this->overrideFilePath('es'));
    }

    public function test_saving_sanitizes_dangerous_html_but_keeps_safe_markup(): void
    {
        // messages.talent_footer ships with <b><i> and renders raw on public
        // pages, so an injected <script> must be stripped while the safe
        // formatting survives.
        $this->service()->saveOverrides('fr', 'messages', [
            'talent_footer' => '<b>Vos evenements</b><script>alert(1)</script>',
        ], null);

        $stored = \App\Models\TranslationOverride::where('key', 'talent_footer')->value('value');
        $this->assertStringContainsString('<b>Vos evenements</b>', $stored);
        $this->assertStringNotContainsString('<script', $stored);

        // The published file and the translator serve the sanitized value.
        $published = require $this->overrideFilePath('fr');
        $this->assertStringNotContainsString('<script', $published['talent_footer']);

        app()->setLocale('fr');
        $this->assertStringNotContainsString('<script', __('messages.talent_footer'));
        app()->setLocale('en');
    }

    public function test_sanitizing_preserves_legitimate_literal_markup_and_placeholders(): void
    {
        // Help-text translations legitimately contain literal angle brackets and
        // placeholder links; the sanitizer must not strip or re-encode those.
        $service = $this->service();

        $this->assertSame('Nom <email> par ligne', $service->sanitizeValue('Nom <email> par ligne'));
        $this->assertSame('Injecte dans le <head> de la page', $service->sanitizeValue('Injecte dans le <head> de la page'));
        $this->assertSame('<a href=":smtp_link">config</a>', $service->sanitizeValue('<a href=":smtp_link">config</a>'));

        // Every shipped messages key with literal markup round-trips unchanged.
        $withMarkup = array_filter(
            $service->readShipped('en', 'messages'),
            fn ($v) => is_string($v) && (str_contains($v, '<') || str_contains($v, '>'))
        );
        foreach ($withMarkup as $key => $value) {
            $this->assertSame($value, $service->sanitizeValue($value), "Sanitizer mangled shipped key: {$key}");
        }
    }

    public function test_editor_page_renders_off_nexus(): void
    {
        // Default suite config is IS_NEXUS=true; the sharing UI (share modal +
        // auto-share toggle) only renders off-nexus, so this compiles those
        // @unless(is_nexus) Blade branches.
        config(['app.is_nexus' => false]);
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->get(route('admin.translations'))->assertOk();
    }

    public function test_export_php_produces_paste_ready_lang_file_lines(): void
    {
        $php = $this->service()->exportPhp([
            'zebra' => 'Last',
            'its_a_match' => "It's a match",
            'path_note' => 'Stored in C:\\temp',
        ]);

        $this->assertSame(
            "    'its_a_match' => 'It\\'s a match',\n".
            "    'path_note' => 'Stored in C:\\\\temp',\n".
            "    'zebra' => 'Last',",
            $php
        );
    }

    private function adminActing(\App\Models\User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    public function test_data_endpoint_returns_the_catalog_and_hides_orphaned_overrides(): void
    {
        $admin = $this->createOwner(true);
        $this->service()->saveOverrides('fr', 'messages', ['home' => 'Maison'], $admin->id);

        // An override whose key no longer exists in the shipped catalog
        // (e.g. left behind by an app update) must not appear in the editor.
        TranslationOverride::create([
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'removed_key_from_old_version',
            'value' => 'Orphan',
        ]);

        $response = $this->adminActing($admin)->getJson(route('admin.translations.data', [
            'locale' => 'fr',
            'group' => 'messages',
        ]));

        $response->assertOk();
        $rows = collect($response->json('rows'));

        $home = $rows->firstWhere('key', 'home');
        $this->assertSame('Home', $home['en']);
        $this->assertSame('Accueil', $home['shipped']);
        $this->assertSame('Maison', $home['override']);
        $this->assertNull($rows->firstWhere('key', 'removed_key_from_old_version'));
        $this->assertGreaterThan(3000, $rows->count());
    }

    public function test_save_endpoint_persists_edits_and_returns_warnings(): void
    {
        $admin = $this->createOwner(true);

        $response = $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'fr',
            'group' => 'messages',
            'values' => [
                'home' => 'Maison',
                'followed_role' => 'Abonnement reussi',
            ],
        ]);

        $response->assertOk()
            ->assertJson(['saved' => 2, 'removed' => 0])
            ->assertJsonPath('warnings.followed_role.placeholders.0', ':name')
            ->assertJsonCount(2, 'savedHashes');

        $this->assertDatabaseHas('translation_overrides', ['key' => 'home', 'value' => 'Maison']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translations_update']);
    }

    public function test_revert_endpoint_removes_overrides(): void
    {
        $admin = $this->createOwner(true);
        $this->service()->saveOverrides('fr', 'messages', ['home' => 'Maison'], $admin->id);

        $this->adminActing($admin)->postJson(route('admin.translations.revert'), [
            'locale' => 'fr',
            'group' => 'messages',
            'keys' => ['home'],
        ])->assertOk()->assertJson(['removed' => 1]);

        $this->assertDatabaseCount('translation_overrides', 0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translations_revert']);
    }

    public function test_editor_endpoints_reject_non_admins_and_invalid_scopes(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->getJson(route('admin.translations.data', [
            'locale' => 'fr',
            'group' => 'messages',
        ]))->assertForbidden();

        $admin = $this->createOwner(true);

        $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'xx',
            'group' => 'messages',
            'values' => ['home' => 'x'],
        ])->assertUnprocessable();

        $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'fr',
            'group' => 'config',
            'values' => ['home' => 'x'],
        ])->assertUnprocessable();
    }

    public function test_hand_made_override_files_are_adopted_and_never_lost(): void
    {
        // Selfhost admins were documented to drop override files into the
        // override directory by hand. Using the editor must adopt those keys,
        // not clobber them.
        File::ensureDirectoryExists(dirname($this->overrideFilePath('en')));
        File::put($this->overrideFilePath('en'), "<?php\n\nreturn ['talent' => 'Artist'];\n");

        $admin = $this->createOwner(true);

        // The hand-made value shows up in the editor as a customization.
        $response = $this->adminActing($admin)->getJson(route('admin.translations.data', [
            'locale' => 'en',
            'group' => 'messages',
        ]));
        $row = collect($response->json('rows'))->firstWhere('key', 'talent');
        $this->assertSame('Artist', $row['override']);

        // Saving an unrelated key republishes the file with both keys intact.
        $this->service()->saveOverrides('en', 'messages', ['home' => 'Start'], $admin->id);
        $published = require $this->overrideFilePath('en');
        $this->assertSame('Artist', $published['talent']);
        $this->assertSame('Start', $published['home']);

        // translations:publish adopts instead of pruning hand-made files.
        File::ensureDirectoryExists(dirname($this->overrideFilePath('fr')));
        File::put($this->overrideFilePath('fr'), "<?php\n\nreturn ['home' => 'Chez moi'];\n");
        $this->artisan('translations:publish')->assertSuccessful();
        $this->assertSame(['home' => 'Chez moi'], require $this->overrideFilePath('fr'));
    }

    public function test_editor_page_renders(): void
    {
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->get(route('admin.translations'))->assertOk();
    }

    public function test_demo_mode_blocks_translation_writes(): void
    {
        $admin = $this->createOwner(true);
        // saveQuietly: the updating hook would null email_verified_at on an
        // email change (hosted mode), bouncing the request at the verified
        // middleware before the demo-mode check.
        $admin->forceFill(['email' => \App\Services\DemoService::DEMO_EMAIL])->saveQuietly();

        $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'fr',
            'group' => 'messages',
            'values' => ['home' => 'Maison'],
        ])->assertForbidden();

        $this->assertDatabaseCount('translation_overrides', 0);
    }
}
