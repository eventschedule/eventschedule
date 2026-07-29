<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the Invoice Ninja connect flow in ProfileController::updatePayments (issue #110).
 *
 * Hermetic: every case points at http://127.0.0.1:1, which refuses the connection
 * immediately. No DNS lookup, no external network.
 *
 * Each test pins config('app.hosted') explicitly. phpunit.xml sets APP_TESTING and
 * IS_NEXUS but not IS_HOSTED, so it otherwise falls through to the developer's .env and
 * the SSRF guard would fire on 127.0.0.1 in some environments and not others.
 */
class InvoiceNinjaConnectTest extends TestCase
{
    use RefreshDatabase;

    private const UNREACHABLE_URL = 'http://127.0.0.1:1';

    private function connectingUser(): User
    {
        return User::factory()->create(['email_verified_at' => now()]);
    }

    public function test_failed_connection_does_not_persist_credentials(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();

        $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => 'test-token',
            'invoiceninja_api_url' => self::UNREACHABLE_URL,
        ]);

        $user->refresh();

        // The webhook is registered before anything is written, so a failure anywhere in
        // the connect sequence must leave the account completely untouched.
        $this->assertNull($user->invoiceninja_api_key);
        $this->assertNull($user->invoiceninja_api_url);
        $this->assertNull($user->invoiceninja_company_name);
        $this->assertNull($user->invoiceninja_webhook_secret);
    }

    public function test_failed_connection_flashes_the_underlying_error(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();

        $response = $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => 'test-token',
            'invoiceninja_api_url' => self::UNREACHABLE_URL,
        ]);

        $response->assertSessionHas('error', __('messages.error_invoiceninja_connection'));
        $response->assertSessionHas('invoiceninja_reason', 'invoiceninja_error_unreachable');

        $detail = session('invoiceninja_error');
        $this->assertNotEmpty($detail);
        $this->assertStringContainsString('Invoice Ninja API connection failed', $detail);
    }

    public function test_the_failure_panel_renders_on_the_settings_page(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();

        $response = $this->actingAs($user)
            ->patch(route('profile.update_payments'), [
                'invoiceninja_api_key' => 'test-token',
                'invoiceninja_api_url' => self::UNREACHABLE_URL,
            ])
            ->assertRedirect();

        $page = $this->actingAs($user)->get($response->headers->get('Location'));

        $page->assertOk();
        // Plain language cause, then the raw detail the owner can paste into a bug report.
        $page->assertSee(__('messages.invoiceninja_error_unreachable'), false);
        $page->assertSee('Invoice Ninja API connection failed', false);
        // And the tab is force-opened so the panel is not stranded behind "hidden".
        $page->assertSee("switchPaymentTab('invoiceninja')", false);
    }

    public function test_non_http_api_url_is_rejected(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();

        $response = $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => 'test-token',
            'invoiceninja_api_url' => 'file:///etc/passwd',
        ]);

        $response->assertSessionHas('error', __('messages.error_invoiceninja_connection'));

        $user->refresh();
        $this->assertNull($user->invoiceninja_api_key);
    }

    public function test_api_token_is_not_flashed_back_into_the_session(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();

        $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => 'test-token',
            'invoiceninja_api_url' => self::UNREACHABLE_URL,
        ]);

        // The URL comes back so the owner does not retype it, but the token must never be
        // written into the session store.
        $this->assertSame(self::UNREACHABLE_URL, old('invoiceninja_api_url'));
        $this->assertEmpty(old('invoiceninja_api_key'));
    }

    public function test_blank_token_from_a_connected_user_keeps_stored_credentials(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();
        $user->invoiceninja_api_key = 'existing-token';
        $user->invoiceninja_api_url = 'https://invoices.example.com';
        $user->invoiceninja_company_name = 'Existing Co';
        $user->invoiceninja_webhook_secret = 'existingsecret';
        $user->save();

        // "Change credentials" submits a blank token to mean "unchanged". The new URL is
        // unreachable, so the existing working configuration must survive untouched.
        $response = $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => '',
            'invoiceninja_api_url' => self::UNREACHABLE_URL,
        ]);

        // Assert the connect was actually attempted, which only happens once the sentinel
        // has resolved a non-empty key. Without this the test passes either way: if the
        // sentinel broke, $apiKey stays empty, the method falls through to the
        // "payments-updated" return, and the credentials are equally untouched.
        $response->assertSessionHas('invoiceninja_reason', 'invoiceninja_error_unreachable');

        $user->refresh();
        $this->assertSame('existing-token', $user->invoiceninja_api_key);
        $this->assertSame('https://invoices.example.com', $user->invoiceninja_api_url);
        $this->assertSame('Existing Co', $user->invoiceninja_company_name);
    }

    public function test_payment_url_form_is_not_hijacked_by_a_connected_user(): void
    {
        config(['app.hosted' => false]);
        $user = $this->connectingUser();
        $user->invoiceninja_api_key = 'existing-token';
        $user->save();

        // The Payment URL form posts to this same route without an invoiceninja_api_key
        // field. It must reach the payment_url branch rather than triggering a reconnect.
        $response = $this->actingAs($user)->patch(route('profile.update_payments'), [
            'payment_url' => 'https://pay.example.com/checkout',
        ]);

        $response->assertSessionHas('message', __('messages.payment_url_connected'));

        $user->refresh();
        $this->assertSame('https://pay.example.com/checkout', $user->payment_url);
    }

    public function test_hosted_blocks_a_private_address(): void
    {
        config(['app.hosted' => true]);
        $user = $this->connectingUser();

        $response = $this->actingAs($user)->patch(route('profile.update_payments'), [
            'invoiceninja_api_key' => 'test-token',
            'invoiceninja_api_url' => self::UNREACHABLE_URL,
        ]);

        $response->assertSessionHas('invoiceninja_reason', 'invoiceninja_url_not_allowed');

        $user->refresh();
        $this->assertNull($user->invoiceninja_api_key);
    }
}
