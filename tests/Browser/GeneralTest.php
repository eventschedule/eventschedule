<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;

class GeneralTest extends DuskTestCase
{
    use DatabaseTruncation;
    use AccountSetupTrait;

    /**
     * A basic browser test example.
     */
    public function testGeneral(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);

            // Log out
            $browser->press($name)
                    ->waitForText('Log Out', 2)
                    ->clickLink('Log Out')
                    ->waitForLocation('/login', 5)
                    ->assertPathIs('/login');

            // Log back in
            $browser->visit('/login')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->press('LOG IN')
                    ->waitForLocation('/events', 5)
                    ->assertPathIs('/events')
                    ->assertSee($name);

            // Create/edit venue using the trait
            $this->createTestVenue($browser);
            $browser->clickLink('Edit Venue')
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
            $browser->clickLink('Edit Talent')
                    ->assertPathIs('/talent/edit')
                    ->click('a[data-section="section-contact-info"]')
                    ->waitFor('#website', 5)
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/talent/schedule', 5)
                    ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/talent/add-event?date=' . date('Y-m-d'))
                    ->click('a[data-section="section-venue"]')
                    ->waitFor('#selected_venue', 5)
                    ->select('#selected_venue')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/talent/schedule', 5)
                    ->assertSee('Venue');
            
            // Create/edit event
            $browser->visit('/venue/add-event?date=' . date('Y-m-d'))
                    ->type('name', 'Venue Event')
                    ->click('a[data-section="section-participants"]')
                    ->waitFor('#selected_member', 5)
                    ->select('#selected_member')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/venue/schedule', 5)
                    ->assertSee('Venue Event');
        });
    }
}