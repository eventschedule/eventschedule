<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class TicketTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * A basic test for ticket functionality.
     */
    public function testBasicTicketFunctionality(): void
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

            // Create venue
            $browser->visit('/new/venue')
                    ->type('name', 'Test Venue')
                    ->type('address1', '123 Test St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-venue/schedule', 5)
                    ->assertPathIs('/test-venue/schedule');

            // Create talent
            $browser->visit('/new/talent')
                    ->type('name', 'Test Talent')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-talent/schedule', 5)
                    ->clickLink('Edit Talent')
                    ->assertPathIs('/test-talent/edit');

            // Create event
            $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                    ->select('#selected_venue')
                    ->type('name', 'Test Event')
                    ->scrollIntoView('input[name="tickets_enabled"]')
                    ->check('tickets_enabled')
                    ->type('tickets[0][price]', '10')
                    ->type('tickets[0][quantity]', '50')
                    ->type('tickets[0][description]', 'General admission ticket')                    
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/test-talent/schedule', 5)
                    ->assertSee('Test Venue');

            // Purchase ticket
            $browser->visit('/test-talent/test-venue')
                    ->press('Buy Tickets')
                    ->select('#ticket-0', '1')
                    ->press('CHECKOUT')
                    ->waitForText('NUMBER OF ATTENDEES', 3)
                    ->assertSee(strtoupper($name));
        });
    }
}