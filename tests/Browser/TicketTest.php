<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class TicketTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * A basic test for ticket functionality.
     */
    public function test_basic_ticket_functionality(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);

            // Create test data using the trait
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            // Purchase ticket
            $browser->visit('/talent/venue')
                ->press('Buy Tickets')
                ->waitFor('#ticket-0', 5)
                ->select('#ticket-0', '1')
                ->scrollIntoView('button[type="submit"]')
                ->press('CHECKOUT')
                ->waitForText('ATTENDEE', 5) // Wait for ticket confirmation page
                ->assertSee($name); // Verify attendee name on ticket
        });
    }
}
