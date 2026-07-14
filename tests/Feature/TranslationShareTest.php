<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\TranslationOverride;
use App\Models\User;
use App\Services\TranslationOverrideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ResetsTranslationOverrides;
use Tests\TestCase;

class TranslationShareTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use ResetsTranslationOverrides;

    private const NEXUS_ENDPOINT = 'https://eventschedule.com/api/translations/suggestions';

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    private function actingAsInstanceAdmin(): User
    {
        // The suite runs with IS_NEXUS=true; sharing is an instance-side feature.
        config(['app.is_nexus' => false]);

        return $this->createOwner(true);
    }

    private function createOverride(string $key = 'home', string $value = 'Maison'): TranslationOverride
    {
        return TranslationOverride::create([
            'locale' => 'fr',
            'group' => 'messages',
            'key' => $key,
            'value' => $value,
        ]);
    }

    public function test_explicit_share_sends_payload_and_stamps_shared_at(): void
    {
        Http::fake([self::NEXUS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0])]);
        $admin = $this->actingAsInstanceAdmin();
        $this->createOverride();

        $response = $this->adminActing($admin)->postJson(route('admin.translations.share'));

        $response->assertOk()->assertJson(['shared' => 1, 'remaining' => 0, 'failed' => false]);
        $this->assertNotNull(TranslationOverride::first()->shared_at);

        $instanceId = Setting::get('translation_instance_id');
        $this->assertNotEmpty($instanceId);

        Http::assertSent(function ($request) use ($instanceId) {
            return $request->url() === self::NEXUS_ENDPOINT
                && $request['instance_id'] === $instanceId
                && $request['app_version'] === config('self-update.version_installed')
                && $request['items'][0]['locale'] === 'fr'
                && $request['items'][0]['group'] === 'messages'
                && $request['items'][0]['key'] === 'home'
                && $request['items'][0]['value'] === 'Maison'
                && $request['items'][0]['shipped_value'] === 'Accueil';
        });

        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translations_share']);
    }

    public function test_share_can_be_limited_to_selected_overrides(): void
    {
        Http::fake([self::NEXUS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0])]);
        $admin = $this->actingAsInstanceAdmin();
        $shared = $this->createOverride('home', 'Maison');
        $excluded = $this->createOverride('back', 'En arriere');

        $this->adminActing($admin)
            ->postJson(route('admin.translations.share'), ['hashes' => [\App\Utils\UrlUtils::encodeId($shared->id)]])
            ->assertOk()
            ->assertJson(['shared' => 1, 'remaining' => 1]);

        $this->assertNotNull($shared->fresh()->shared_at);
        $this->assertNull($excluded->fresh()->shared_at);
    }

    public function test_failed_share_leaves_overrides_unshared_and_reports_failure(): void
    {
        Http::fake([self::NEXUS_ENDPOINT => Http::response('error', 500)]);
        $admin = $this->actingAsInstanceAdmin();
        $this->createOverride();

        $this->adminActing($admin)->postJson(route('admin.translations.share'))
            ->assertOk()
            ->assertJson(['shared' => 0, 'remaining' => 1, 'failed' => true]);

        $this->assertNull(TranslationOverride::first()->shared_at);
    }

    public function test_partial_failure_across_chunks_keeps_sent_rows_shared(): void
    {
        // 201 overrides = two chunks (200 + 1). First chunk succeeds, second fails.
        Http::fake([self::NEXUS_ENDPOINT => Http::sequence()
            ->push(['accepted' => 200, 'skipped' => 0], 200)
            ->push('server error', 500),
        ]);
        config(['app.is_nexus' => false]);

        $rows = [];
        for ($i = 1; $i <= 201; $i++) {
            $rows[] = [
                'locale' => 'fr', 'group' => 'messages', 'key' => 'chunk_key_'.$i,
                'value' => 'Valeur '.$i, 'created_at' => now(), 'updated_at' => now(),
            ];
        }
        \DB::table('translation_overrides')->insert($rows);

        $result = app(TranslationOverrideService::class)->shareToNexus();

        $this->assertSame(200, $result['shared']);
        $this->assertTrue($result['failed']);
        $this->assertSame(1, $result['remaining']);
        // The first chunk's rows are stamped; the one leftover stays unshared.
        $this->assertSame(200, TranslationOverride::whereNotNull('shared_at')->count());
        $this->assertSame(1, TranslationOverride::unshared()->count());
    }

    public function test_sharing_works_on_self_hosted_saas_but_review_is_hidden(): void
    {
        // Self-hosted SaaS = hosted && !nexus: sharing must work, review must 404.
        Http::fake([self::NEXUS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0])]);
        config(['app.hosted' => true, 'app.is_nexus' => false]);
        $admin = $this->createOwner(true);
        $this->createOverride();

        $this->adminActing($admin)->postJson(route('admin.translations.share'))
            ->assertOk()->assertJson(['shared' => 1]);

        $this->adminActing($admin)->getJson(route('admin.translations.suggestions.data'))->assertNotFound();
    }

    public function test_demo_mode_blocks_sharing_endpoints(): void
    {
        config(['app.is_nexus' => false]);
        $admin = $this->createOwner(true);
        $admin->forceFill(['email' => \App\Services\DemoService::DEMO_EMAIL])->saveQuietly();
        $this->createOverride();

        $this->adminActing($admin)->postJson(route('admin.translations.share'))->assertForbidden();
        $this->adminActing($admin)->postJson(route('admin.translations.auto_share'), ['enabled' => true])->assertForbidden();
        $this->adminActing($admin)->postJson(route('admin.translations.revert'), [
            'locale' => 'fr', 'group' => 'messages', 'keys' => ['home'],
        ])->assertForbidden();
    }

    public function test_share_cap_reports_the_remaining_count(): void
    {
        Http::fake([self::NEXUS_ENDPOINT => Http::response(['accepted' => 2, 'skipped' => 0])]);
        config(['app.is_nexus' => false]);
        $this->createOverride('home', 'Maison');
        $this->createOverride('back', 'En arriere');
        $this->createOverride('cancel', 'Abandonner');

        $result = app(TranslationOverrideService::class)->shareToNexus(null, 2);

        $this->assertSame(['shared' => 2, 'remaining' => 1, 'failed' => false], $result);
    }

    public function test_unshared_endpoint_lists_pending_customizations(): void
    {
        $admin = $this->actingAsInstanceAdmin();
        $this->createOverride();
        TranslationOverride::create([
            'locale' => 'de',
            'group' => 'messages',
            'key' => 'back',
            'value' => 'Rueckwaerts',
            'shared_at' => now(),
        ]);

        $response = $this->adminActing($admin)->getJson(route('admin.translations.unshared'));

        $response->assertOk()->assertJsonCount(1, 'rows');
        $row = $response->json('rows.0');
        $this->assertSame('fr', $row['locale']);
        $this->assertSame('home', $row['key']);
        $this->assertSame('Accueil', $row['before']);
        $this->assertSame('Maison', $row['after']);
        $this->assertNotEmpty($row['hash']);
    }

    public function test_auto_share_toggle_persists_and_save_shares_after_response(): void
    {
        Http::fake([self::NEXUS_ENDPOINT => Http::response(['accepted' => 1, 'skipped' => 0])]);
        $admin = $this->actingAsInstanceAdmin();

        $this->adminActing($admin)
            ->postJson(route('admin.translations.auto_share'), ['enabled' => true])
            ->assertOk()
            ->assertJson(['enabled' => true]);

        $this->assertSame('1', Setting::get('translations_auto_share'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin.translations_auto_share']);

        $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'fr',
            'group' => 'messages',
            'values' => ['home' => 'Maison'],
        ])->assertOk();

        // The auto-share job runs after the response; the override ends up shared.
        Http::assertSent(fn ($request) => $request->url() === self::NEXUS_ENDPOINT);
        $this->assertNotNull(TranslationOverride::first()->shared_at);
    }

    public function test_saving_without_auto_share_sends_nothing(): void
    {
        Http::fake();
        $admin = $this->actingAsInstanceAdmin();

        $this->adminActing($admin)->postJson(route('admin.translations.save'), [
            'locale' => 'fr',
            'group' => 'messages',
            'values' => ['home' => 'Maison'],
        ])->assertOk()->assertJson(['saved' => 1, 'unsharedCount' => 1]);

        Http::assertNothingSent();
    }

    public function test_sharing_endpoints_are_hidden_on_nexus(): void
    {
        // Default suite config: IS_NEXUS=true.
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->postJson(route('admin.translations.share'))->assertNotFound();
        $this->adminActing($admin)->postJson(route('admin.translations.auto_share'), ['enabled' => true])->assertNotFound();
        $this->adminActing($admin)->getJson(route('admin.translations.unshared'))->assertNotFound();
    }

    public function test_sharing_requires_an_admin(): void
    {
        config(['app.is_nexus' => false]);
        $user = $this->createOwner();

        $this->actingAs($user)->postJson(route('admin.translations.share'))->assertForbidden();
    }
}
