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
                var options = document.querySelectorAll('select[name=\"timezone\"] option');
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === 'Pacific/Auckland') {
                        options[i].selected = true;
                        break;
                    }
                }
                document.getElementById('timezone').dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/settings', 15);

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
            $browser->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/settings', 15);

            // Verify DB
            $this->assertTrue((bool) User::first()->refresh()->use_24_hour_time);

            // -----------------------------------------------
            // 3. Change language to Spanish
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->waitFor('#language_code', 5)
                ->select('language_code', 'es')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/settings', 15);

            // Verify DB
            $this->assertEquals('es', User::first()->refresh()->language_code);

            // Verify page shows Spanish text (the settings page header)
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)
                ->click('button[data-tab="localization"]')
                ->waitFor('#language_code', 5)
                ->assertSee('Configuraci');

            // Change back to English
            $browser->select('language_code', 'en')
                ->scrollIntoView('button[type="submit"]')
                ->press('GUARDAR')
                ->waitForLocation('/settings', 15);

            // Verify DB
            $this->assertEquals('en', User::first()->refresh()->language_code);

            // -----------------------------------------------
            // 4. Change name
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="general"]', 5)
                ->click('button[data-tab="general"]')
                ->waitFor('#name', 5)
                ->clear('name')
                ->type('name', 'New Name')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/settings', 15);

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
