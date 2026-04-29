<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_profile_settings(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Setup
            $this->setupTestAccount($browser, $name, $email, $password);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // 1. Change timezone
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->pause(500);

            $browser->script("
                var select = document.getElementById('timezone');
                select.value = 'Pacific/Auckland';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('Pacific/Auckland', User::first()->refresh()->timezone);

            // Verify persisted on reload
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->pause(500);
            $timezoneValue = $browser->script("return document.getElementById('timezone').value;");
            $this->assertEquals('Pacific/Auckland', $timezoneValue[0]);

            // -----------------------------------------------
            // 2. Toggle 24-hour time
            // -----------------------------------------------
            $browser->scrollIntoView('label[for="use_24_hour_time"]');
            $browser->script("document.getElementById('use_24_hour_time').checked = true;");
            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertTrue((bool) User::first()->refresh()->use_24_hour_time);

            // -----------------------------------------------
            // 3. Change language to Spanish
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->pause(500);

            $browser->script("
                var select = document.getElementById('language_code');
                select.value = 'es';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Guardado', 15);

            // Verify DB
            $this->assertEquals('es', User::first()->refresh()->language_code);

            // Verify page shows Spanish text (the settings page header)
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->pause(500)
                ->assertSee('Configuraci');

            // Change back to English
            $browser->script("
                var select = document.getElementById('language_code');
                select.value = 'en';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('en', User::first()->refresh()->language_code);

            // -----------------------------------------------
            // 4. Change name
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="general"]', 5)
                ->click('button[data-tab="general"]')
                ->waitFor('#name', 5);

            $browser->script("
                var input = document.getElementById('name');
                input.value = 'New Name';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('New Name', User::first()->refresh()->name);

            // Verify persisted on reload
            $browser->visit('/settings')
                ->waitFor('button[data-tab="general"]', 5)
                ->click('button[data-tab="general"]')
                ->waitFor('#name', 5)
                ->assertInputValue('name', 'New Name');
        });
    }
}
