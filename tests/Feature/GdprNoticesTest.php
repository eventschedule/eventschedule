<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What people are told, and who may see what: the privacy policy's provider schedule follows the
 * install's config, guests are told who receives their details, a sign-up code request does not
 * reveal whose address it is, and hosted admins use 2FA.
 */
class GdprNoticesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A provider row appears exactly when this install can send that provider data. */
    public function test_the_privacy_policy_lists_a_provider_only_where_it_is_configured(): void
    {
        config(['services.onesignal.app_id' => null, 'services.onesignal.rest_api_key' => null, 'services.twilio.sid' => null]);
        $this->get('/privacy')->assertOk()->assertDontSee('>OneSignal<', false)->assertDontSee('>Twilio<', false);

        config(['services.onesignal.app_id' => 'app', 'services.onesignal.rest_api_key' => 'key', 'services.twilio.sid' => 'sid']);
        $this->get('/privacy')->assertOk()->assertSee('>OneSignal<', false)->assertSee('>Twilio<', false);
    }

    public function test_the_privacy_policy_carries_a_date_and_the_rights(): void
    {
        $this->get('/privacy')->assertOk()
            ->assertSee('Last updated')
            ->assertSee('id="your-rights"', false)
            ->assertSee('id="retention"', false)
            ->assertSee('Download my data');
    }

    public function test_checkout_tells_a_guest_who_receives_their_details(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $this->createTicket($event, ['price' => 10]);

        $this->get($this->guestEventUrl($role, $event))->assertOk()
            ->assertSee(__('messages.guest_privacy_note', ['schedule' => 'Harbour Hall']));
    }

    /** Asking for a code to an address must not say whose it is. */
    public function test_a_code_request_does_not_return_a_stub_accounts_name(): void
    {
        User::factory()->create(['email' => 'buyer@example.com', 'name' => 'Private Person', 'password' => null]);

        $response = $this->postJson(route('sign_up.send_code'), ['email' => 'buyer@example.com']);

        $this->assertStringNotContainsString('Private Person', (string) $response->getContent());
    }

    /**
     * A selfhosted install that collects data still points its visitors at eventschedule.com's
     * policy until the operator writes one: the admin dashboard says so until they do.
     */
    public function test_a_selfhost_install_without_its_own_privacy_policy_is_told_to_publish_one(): void
    {
        config(['app.is_nexus' => false, 'app.cookie_consent_banner' => true]);
        \App\Services\AdminAlertService::flush();

        $types = fn () => \App\Services\AdminAlertService::items()->pluck('type')->all();
        $this->assertContains('privacy_policy_missing', $types());

        \App\Models\LegalDocument::create(['type' => 'privacy', 'content' => '## Our policy']);
        \App\Services\AdminAlertService::flush();
        $this->assertNotContains('privacy_policy_missing', $types());

        config(['app.is_nexus' => true]);
        \App\Models\LegalDocument::query()->delete();
        \App\Services\AdminAlertService::flush();
        $this->assertNotContains('privacy_policy_missing', $types(), 'the nexus publishes the built-in policy itself');
    }

    public function test_hosted_admins_must_turn_on_two_factor(): void
    {
        config(['auth.admin_requires_two_factor' => true]);
        $admin = $this->createOwner(admin: true);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertRedirect(route('profile.edit').'#section-two-factor');

        $admin->forceFill(['two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_confirmed_at' => now()])->save();

        $response = $this->actingAs($admin->fresh())->get('/admin/dashboard');
        $this->assertNotSame(route('profile.edit').'#section-two-factor', $response->headers->get('Location'));
    }

    /**
     * The support-chat presence routes sit outside the admin middleware and return visitors'
     * names and message previews, so they must not be the way around the 2FA rule.
     */
    public function test_support_presence_refuses_an_admin_without_two_factor_where_it_is_required(): void
    {
        config(['auth.admin_requires_two_factor' => true]);
        $admin = $this->createOwner(admin: true);
        $this->actingAs($admin);

        try {
            app(\App\Http\Controllers\SupportChatController::class)->presencePing(request());
            $this->fail('presence answered an admin without two-factor authentication');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    /** GDPR Art. 7(3): withdrawing must be as easy as giving, on the pages that ask. */
    public function test_the_cookie_banner_can_be_reopened_from_the_sign_in_pages_but_not_by_an_admin(): void
    {
        config(['services.google.analytics' => 'G-TEST123']);

        $this->get('/login')->assertOk()->assertSee('data-cookie-consent-reopen', false);

        // No banner is rendered for an admin, so a reopen control would do nothing.
        $admin = $this->createOwner(admin: true);
        $html = $this->actingAs($admin)->get(route('profile.edit'))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/<div data-cookie-consent\s/', $html);
        $this->assertStringNotContainsString('data-cookie-consent-reopen', $html);
    }

    /** ?graphic=1 is turned into an image by a headless browser that can never answer a banner. */
    public function test_a_rendered_graphic_carries_no_banner_and_no_legal_pill(): void
    {
        config(['services.google.analytics' => 'G-TEST123']);
        $role = $this->createRole($this->createOwner());

        $page = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('data-cookie-consent-reopen', $page, 'the ordinary page has both');

        $graphic = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain, 'graphic' => 1]))->assertOk()->getContent();
        $this->assertStringNotContainsString('data-cookie-consent-reopen', $graphic);
        $this->assertDoesNotMatchRegularExpression('/<div data-cookie-consent\s/', $graphic);
        $this->assertMatchesRegularExpression('/<div data-cookie-consent\s/', $page, 'the ordinary page renders the banner');
    }

    /**
     * The export is delivered only as an emailed link, so an install whose mailer writes to the
     * log offers no button and refuses the request.
     */
    public function test_download_my_data_is_not_offered_where_mail_cannot_be_sent(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'log']);
        $user = $this->createOwner();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertDontSee('action="'.route('profile.data_export').'"', false);

        $this->actingAs($user)->post(route('profile.data_export'))->assertSessionHas('error');
        $this->assertSame(0, \App\Models\BackupJob::where('user_id', $user->id)->where('type', 'personal')->count());
    }
}
