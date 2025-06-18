<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class GeneralTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * A basic browser test example.
     */
    public function testBasicExample(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Sign up
            $browser->visit('/sign_up')
                    ->type('name', $name)
                    ->type('email', $email)
                    ->type('password', $password)
                    ->check('terms')
                    ->press('SIGN UP')
                    ->assertPathIs('/events')
                    ->assertSee($name);            

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

            // Create/edit venue
            $browser->visit('/new/venue')
                    ->type('name', 'Test Venue')
                    ->type('address1', '123 Test St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-venue/schedule', 5)
                    ->assertPathIs('/test-venue/schedule')
                    ->clickLink('Edit Venue')
                    ->assertPathIs('/test-venue/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-venue/schedule', 5)
                    ->assertSee('google.com');

            // Create/edit talent
            $browser->visit('/new/talent')
                    ->type('name', 'Test Talent')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-talent/schedule', 5)
                    ->clickLink('Edit Talent')
                    ->assertPathIs('/test-talent/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-talent/schedule', 5)
                    ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/test-talent/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_venue')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-talent/schedule', 5)
                    ->assertSee('Test Venue');
            
            // Create/edit event
            $browser->visit('/test-venue/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_member')
                    ->type('name', 'Venue Event')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-venue/schedule', 5)
                    ->assertSee('Venue Event');
        });
    }
}