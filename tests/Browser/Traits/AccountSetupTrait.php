<?php

namespace Tests\Browser\Traits;

use Laravel\Dusk\Browser;

trait AccountSetupTrait
{
    /**
     * Set up a test account with basic data
     */
    protected function setupTestAccount(Browser $browser, string $name = 'John Doe', string $email = 'test@gmail.com', string $password = 'password'): void
    {
        // Sign up
        $browser->visit('/sign_up')
                ->type('name', $name)
                ->type('email', $email)
                ->type('password', $password)
                ->check('terms')
                ->press('SIGN UP')
                ->assertPathIs('/events')
                ->assertSee($name);
    }

    /**
     * Create a test venue
     */
    protected function createTestVenue(Browser $browser, string $name = 'Test Venue', string $address = '123 Test St'): void
    {
        $browser->visit('/new/venue')
                ->type('name', $name)
                ->type('address1', $address)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 5)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test talent
     */
    protected function createTestTalent(Browser $browser, string $name = 'Test Talent'): void
    {
        $browser->visit('/new/talent')
                ->type('name', $name)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 5)
                ->clickLink('Edit Talent')
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/edit');
    }

    /**
     * Create a test event with tickets
     */
    protected function createTestEventWithTickets(Browser $browser, string $talentName = 'Test Talent', string $venueName = 'Test Venue', string $eventName = 'Test Event'): void
    {
        $browser->visit('/' . strtolower(str_replace(' ', '-', $talentName)) . '/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                ->select('#selected_venue')
                ->type('name', $eventName)
                ->scrollIntoView('input[name="tickets_enabled"]')
                ->check('tickets_enabled')
                ->type('tickets[0][price]', '10')
                ->type('tickets[0][quantity]', '50')
                ->type('tickets[0][description]', 'General admission ticket')                    
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $talentName)) . '/schedule', 5)
                ->assertSee($venueName);
    }

    /**
     * Enable API for the current user
     */
    protected function enableApi(Browser $browser): string
    {
        $browser->visit('/account')
                ->scrollIntoView('#enable_api');
        $browser->script("document.getElementById('enable_api').checked = true;");
        // Hide PHP Debug Bar if present
        $browser->script("var bar = document.getElementById('phpdebugbar'); if (bar) { bar.style.display = 'none'; } var badge = document.querySelector('.phpdebugbar-badge'); if (badge) { badge.style.display = 'none'; }");
        $browser->press('Save')
                ->waitForText('API settings updated successfully', 5);
        
        // Get the API key from the page - it should be visible after enabling
        $apiKey = $browser->value('#api_key');
        return $apiKey;
    }
} 