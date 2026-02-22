<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class PasswordTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_password_change(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';
        $newPassword = 'newpassword123';

        $this->browse(function (Browser $browser) use ($name, $email, $password, $newPassword) {
            // Setup
            $this->setupTestAccount($browser, $name, $email, $password);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // 1. Change password
            // -----------------------------------------------
            $browser->visit('/settings#section-password')
                ->waitFor('#update_password_current_password', 5)
                ->type('current_password', $password)
                ->type('password', $newPassword);

            // Click the save button inside the password form
            $browser->script("
                const field = document.getElementById('update_password_password');
                const form = field.closest('form');
                const submitButton = form.querySelector('button[type=\"submit\"]');
                if (submitButton) {
                    submitButton.scrollIntoView({ block: 'center', behavior: 'instant' });
                }
            ");

            $browser->pause(300);

            $browser->script("
                const field = document.getElementById('update_password_password');
                const form = field.closest('form');
                const submitButton = form.querySelector('button[type=\"submit\"]');
                if (submitButton) {
                    submitButton.click();
                }
            ");

            $browser->waitForText('Saved', 5);

            // -----------------------------------------------
            // 2. Verify new password works
            // -----------------------------------------------
            $this->logoutUser($browser, $name);

            $this->loginUser($browser, $email, $newPassword);
            $browser->assertSee($name);
        });
    }
}
