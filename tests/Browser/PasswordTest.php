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
                ->waitFor('#update_password_current_password', 10)
                ->pause(500);

            $browser->script("
                var cur = document.getElementById('update_password_current_password');
                cur.value = " . json_encode($password) . ";
                cur.dispatchEvent(new Event('input', { bubbles: true }));
                var pw = document.getElementById('update_password_password');
                pw.value = " . json_encode($newPassword) . ";
                pw.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-password form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // -----------------------------------------------
            // 2. Verify new password works
            // -----------------------------------------------
            $this->logoutUser($browser, $name);

            $this->loginUser($browser, $email, $newPassword);
            $browser->assertSee($name);
        });
    }
}
