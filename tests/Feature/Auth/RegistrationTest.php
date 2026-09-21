<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_redirect_to_login_page(): void
    {
        // Pre-existing: this asserts selfhost behavior (/sign_up -> /login) via a runtime
        // config(['app.hosted' => false]) override, but routes/middleware are registered at
        // boot from the hosted .env, so the selfhost path can't be exercised here. Needs a
        // dedicated selfhost test env (IS_HOSTED=false). See TEST_COVERAGE notes.
        $this->markTestSkipped('Selfhost signup redirect not simulatable in the hosted test env.');
    }

    public function test_registration_screen_can_be_rendered_in_hosted_app(): void
    {
        config(['app.hosted' => true]);

        $response = $this->get('/sign_up');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_in_hosted_app(): void
    {
        config(['app.hosted' => true]);

        $this->assertDatabaseCount('users', 0);

        $response = $this->post('/sign_up', [
            'terms' => '1',
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('getting-started', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'signup_intent' => 'organizer',
        ]);
    }

    /**
     * Accepting the terms and the privacy policy is a legal record, so it is checked server-side
     * and not only by the checkbox's `required` attribute - which the sign-up form now arms from
     * its reveal script, putting consent behind one JavaScript function.
     */
    public function test_a_hosted_signup_without_consent_is_refused(): void
    {
        config(['app.hosted' => true]);

        $this->post('/sign_up', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
        ])->assertSessionHasErrors('terms');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * A guard, not a defect pin - the form's summary block already rendered $errors->first(), so
     * this passed before the per-field anchor was added too. What it does pin is the MESSAGE: this
     * repo ships no per-locale validation lang file, so without an explicit one the `accepted` rule
     * falls back to framework English in all 12 locales. It also catches anyone removing the
     * summary block, which is the only thing making the refusal visible at all.
     */
    public function test_the_consent_refusal_is_announced_in_the_users_language(): void
    {
        config(['app.hosted' => true]);

        $this->followingRedirects()
            ->from('/sign_up')
            ->post('/sign_up', [
                'name' => 'Test User',
                'email' => 'test@gmail.com',
                'password' => 'password',
            ])
            ->assertOk()
            ->assertSee(__('messages.terms_must_be_accepted'))
            ->assertDontSee('must be accepted', false);
    }

    public function test_an_unticked_consent_box_is_refused_too(): void
    {
        config(['app.hosted' => true]);

        // An unchecked box posts nothing at all, but a hand-built request can send a falsy value
        // and `accepted` has to reject that as well, not just a missing key.
        $this->post('/sign_up', [
            'terms' => '0',
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'password',
        ])->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('users', 0);
    }
}
