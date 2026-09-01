<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * ?lang[]=en must never 500 a page.
 *
 * A query param arrives as an array for ?lang[]=en, and is_valid_language_code() used to be typed
 * ?string - an uncaught TypeError on every page that forwards the visitor's language. The bug was
 * patched with an inline is_string() guard four separate times (app-guest.blade.php,
 * RoleController::viewGuest, showGuestSubmit, showGuestImport) while roughly nine other call sites
 * across controllers and Blade views stayed exposed, because each fix only covered the page that
 * had just been reported. The helper now takes mixed and answers false for a non-string, so the
 * whole class is closed at the root; these are the pages that were still live.
 */
class ArrayLanguageParamTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_helper_rejects_a_non_string_instead_of_throwing(): void
    {
        $this->assertFalse(is_valid_language_code(['en']));
        $this->assertFalse(is_valid_language_code(null));
        $this->assertFalse(is_valid_language_code(''));
        $this->assertFalse(is_valid_language_code(42));
        $this->assertFalse(is_valid_language_code('zz'));
        $this->assertTrue(is_valid_language_code('en'));
        $this->assertTrue(is_valid_language_code('he'));
    }

    /** MarketingController::whyCreateAccount() - the page guest-submit now links to. */
    public function test_the_why_create_account_page_survives_an_array_lang(): void
    {
        $this->get('/why-create-account?lang[]=en')->assertOk();
    }

    /**
     * EventController::showBookingRequest(). This one 302s rather than 200s, and that is the
     * intended contract, not a workaround: it passes $request->lang straight through, so an
     * unusable value is redirected away to the clean URL. showGuestSubmit() narrows $lang to null
     * for an array first and so renders 200 instead - which is why the two tests here assert
     * different status codes. What matters is that it is a redirect and not a 500, and that the
     * array does not survive into the target.
     */
    public function test_the_booking_request_page_survives_an_array_lang(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['accept_requests' => true]);
        $url = route('event.booking_request', ['subdomain' => $talent->subdomain]);

        $this->get($url.'?lang[]=en')->assertRedirect($url);

        // And the clean URL itself renders, so the redirect is not a loop.
        $this->get($url)->assertOk();
    }

    /** event/import.blade.php:93 and :793, reached through guest-import. Needs an AI key: without
     *  one import.blade.php renders <x-gemini-setup-guide /> instead of the form that carries the
     *  offending expression, so this would pass for the wrong reason. */
    public function test_the_guest_import_page_survives_an_array_lang(): void
    {
        config(['services.google.gemini_key' => 'test-key']);

        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => false,
        ]);

        $response = $this->get(route('event.guest_import', ['subdomain' => $curator->subdomain]).'?lang[]=en');

        $response->assertOk();
        // Proves the vulnerable branch actually rendered.
        $this->assertStringContainsString('why-create-account', $response->getContent());
    }

    /** auth/register.blade.php:9 and :643, plus RegisteredUserController::create(). */
    public function test_the_sign_up_page_survives_an_array_lang(): void
    {
        $this->get('/sign_up?lang[]=en')->assertOk();
    }
}
