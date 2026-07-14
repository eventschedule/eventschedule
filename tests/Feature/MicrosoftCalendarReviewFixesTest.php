<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Models\User;
use App\Services\MicrosoftCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class MicrosoftCalendarReviewFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.microsoft', [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-secret',
            'redirect' => 'https://app.test/microsoft-calendar/callback',
            'tenant' => 'common',
            'webhook_secret' => 'test-webhook-secret',
        ]);
    }

    private function connectedOwner(): User
    {
        $user = $this->createOwner();
        $user->forceFill([
            'microsoft_token' => 'owner-token',
            'microsoft_refresh_token' => 'owner-refresh',
            'microsoft_token_expires_at' => now()->addHour(),
        ])->save();

        return $user->fresh();
    }

    // F1 - deletion tears down the Graph subscription

    public function test_role_delete_tears_down_subscription(): void
    {
        $owner = $this->connectedOwner();
        $role = $this->createRole($owner, 'venue', [
            'microsoft_webhook_id' => 'sub-role',
            'microsoft_webhook_expires_at' => now()->addDay(),
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response('', 204)]);

        $response = $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]));
        $response->assertRedirect();

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/subscriptions/sub-role'));
    }

    public function test_account_delete_tears_down_subscription(): void
    {
        $owner = $this->connectedOwner();
        $this->createRole($owner, 'venue', [
            'microsoft_webhook_id' => 'sub-acct',
            'microsoft_webhook_expires_at' => now()->addDay(),
        ]);

        Http::fake(['graph.microsoft.com/*' => Http::response('', 204)]);

        $response = $this->actingAs($owner)->delete('/settings', ['password' => 'password']);
        $response->assertRedirect('/');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && str_contains($request->url(), '/subscriptions/sub-acct'));
    }

    // F3 - inbound sync is serialized per role

    public function test_inbound_sync_is_skipped_when_role_lock_is_held(): void
    {
        $user = $this->connectedOwner();
        $role = $this->createRole($user, 'venue');

        // Simulate another inbound sync already holding the per-role lock.
        $this->assertTrue(Cache::lock('microsoft-role-sync-'.$role->id, 120)->get());

        Http::fake();
        $result = app(MicrosoftCalendarService::class)->syncFromMicrosoftCalendar($user, $role, null);

        $this->assertSame(['created' => 0, 'updated' => 0, 'errors' => 0], $result);
        // No Graph request (not even a token refresh) - it returned before touching the network.
        Http::assertNothingSent();
    }

    // F4 - doc search index covers the two Outlook user-guide sub-sections

    public function test_doc_search_index_includes_outlook_subsections(): void
    {
        $controller = app(MarketingController::class);
        $method = new \ReflectionMethod($controller, 'getDocSearchIndex');
        $method->setAccessible(true);
        $urls = array_column($method->invoke($controller), 'url');

        $this->assertTrue(
            collect($urls)->contains(fn ($u) => str_contains($u, '#integrations-microsoft')),
            'Creating Schedules Outlook section missing from doc search index'
        );
        $this->assertTrue(
            collect($urls)->contains(fn ($u) => str_contains($u, '/account-settings#microsoft')),
            'Account Settings Outlook section missing from doc search index'
        );
    }
}
