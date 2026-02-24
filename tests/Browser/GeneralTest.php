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
                ->click('a[data-section="section-contact-info"]')
                ->waitFor('#website', 5)
                ->type('website', 'https://google.com')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/venue/schedule', 5)
                ->assertSee('google.com');

            // Create/edit talent using the trait
            $this->createTestTalent($browser);
            $browser->clickLink('Edit Schedule')
                ->assertPathIs('/talent/edit')
                ->click('a[data-section="section-contact-info"]')
                ->waitFor('#website', 5)
                ->type('website', 'https://google.com')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 5)
                ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#name', 10)
                ->pause(500);
            $browser->script("document.querySelector('a[data-section=\"section-venue\"]').click()");
            $browser->waitFor('#selected_venue', 5)
                ->select('#selected_venue')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 5)
                ->assertSee('Venue');

            // Create/edit event
            $browser->visit('/venue/add-event?date='.date('Y-m-d'))
                ->type('name', 'Venue Event')
                ->click('a[data-section="section-participants"]')
                ->waitFor('#selected_member', 5)
                ->select('#selected_member')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/venue/schedule', 5)
                ->pause(1000)
                ->assertSee('Venue Event');
        });
    }
}
