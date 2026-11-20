<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Hosted with no Azure app shows no Outlook panel, in Settings or on the schedule form.
 *
 * eventschedule.com shipped without MICROSOFT_* set, and both pages drew an Outlook panel that
 * said "Outlook Calendar is not configured on this server", which no customer can act on. Hosted
 * now hides both (outlook_sync_offered()); selfhost keeps the notice, because there it tells the
 * operator what to set up. The other half holds the panels when the app IS configured, so the
 * gate cannot quietly become "never".
 */
class OutlookPanelsHiddenTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function owner(): array
    {
        $user = $this->createOwner();
        $role = $this->createRole($user, 'venue');

        return [$user, $role];
    }

    private function pages(User $user, Role $role): array
    {
        return [
            $this->actingAs($user)->get(route('profile.edit'))->assertOk()->getContent(),
            $this->actingAs($user)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent(),
        ];
    }

    public function test_hosted_without_an_azure_app_shows_no_outlook_panel(): void
    {
        config(['app.hosted' => true, 'services.microsoft.client_id' => null, 'services.microsoft.client_secret' => null]);
        [$user, $role] = $this->owner();
        [$settings, $schedule] = $this->pages($user, $role);

        $this->assertStringNotContainsString('id="section-microsoft-calendar"', $settings);
        $this->assertStringNotContainsString(__('messages.microsoft_calendar_not_configured'), $settings);
        $this->assertStringNotContainsString('id="integration-tab-microsoft"', $schedule);
    }

    public function test_hosted_with_an_azure_app_shows_both_panels(): void
    {
        config(['app.hosted' => true, 'services.microsoft.client_id' => 'test-client', 'services.microsoft.client_secret' => 'test-secret']);
        [$user, $role] = $this->owner();
        [$settings, $schedule] = $this->pages($user, $role);

        $this->assertStringContainsString('id="section-microsoft-calendar"', $settings);
        $this->assertStringContainsString('id="integration-tab-microsoft"', $schedule);
    }

    public function test_the_outlook_page_and_its_sitemap_entry_follow_the_azure_app(): void
    {
        config(['services.microsoft.client_id' => null, 'services.microsoft.client_secret' => null]);
        $this->get(route('marketing.outlook_calendar'))->assertRedirect(url('/features/calendar-sync'));
        $this->assertStringNotContainsString('/outlook-calendar<', $this->get('/sitemap-pages.xml')->streamedContent());

        config(['services.microsoft.client_id' => 'test-client', 'services.microsoft.client_secret' => 'test-secret']);
        $this->get(route('marketing.outlook_calendar'))->assertOk();
        $this->assertStringContainsString('/outlook-calendar<', $this->get('/sitemap-pages.xml')->streamedContent());
    }

    public function test_selfhost_without_an_azure_app_keeps_the_setup_notice(): void
    {
        config(['app.hosted' => false, 'services.microsoft.client_id' => null, 'services.microsoft.client_secret' => null]);
        [$user] = $this->owner();

        $settings = $this->actingAs($user)->get(route('profile.edit'))->assertOk()->getContent();

        $this->assertStringContainsString('id="section-microsoft-calendar"', $settings);
        $this->assertStringContainsString(e(__('messages.microsoft_calendar_not_configured')), $settings);
    }
}
