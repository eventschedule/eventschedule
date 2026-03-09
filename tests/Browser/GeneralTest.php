<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class GeneralTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * A basic browser test example.
     */
    public function test_general(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);

            // Log out
            $this->logoutUser($browser, $name);

            // Log back in
            $this->loginUser($browser, $email, $password);
            $browser->assertSee($name);

            // Create/edit venue using the trait
            $this->createTestVenue($browser);
            $browser->clickLink('Edit Schedule')
                ->assertPathIs('/venue/edit')
                ->waitFor('#edit-form', 15)
                ->click('a[data-section="section-details"]')
                ->pause(500);
            $browser->script("document.querySelector('.details-tab[data-tab=\"contact\"]').click()");
            $browser->waitFor('#website', 5)
                ->script("document.getElementById('website').value = 'https://google.com'");
            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit()");
            $browser->waitForLocation('/venue/schedule', 15)
                ->assertSee('google.com');

            // Create/edit talent using the trait
            $this->createTestTalent($browser);
            $browser->clickLink('Edit Schedule')
                ->assertPathIs('/talent/edit')
                ->waitFor('#edit-form', 15)
                ->click('a[data-section="section-details"]')
                ->pause(500);
            $browser->script("document.querySelector('.details-tab[data-tab=\"contact\"]').click()");
            $browser->waitFor('#website', 5)
                ->script("document.getElementById('website').value = 'https://google.com'");
            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit()");
            $browser->waitForLocation('/talent/schedule', 15)
                ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#event_name', 10)
                ->pause(500);
            $browser->script("document.querySelector('a[data-section=\"section-venue\"]').click()");
            $browser->waitFor('#in_person', 5);
            $browser->script("var cb = document.getElementById('in_person'); if (!cb.checked) cb.click();");
            $browser->waitFor('#selected_venue', 5)
                ->select('#selected_venue')
                ->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit()");
            $browser->waitForLocation('/talent/schedule', 15)
                ->assertSee('Venue');

            // Create/edit event
            $browser->visit('/venue/add-event?date='.date('Y-m-d'))
                ->type('name', 'Venue Event')
                ->click('a[data-section="section-participants"]')
                ->waitFor('#selected_member', 5)
                ->select('#selected_member')
                ->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit()");
            $browser->waitForLocation('/venue/schedule', 15)
                ->pause(1000)
                ->assertSee('Venue Event');
        });
    }
}
