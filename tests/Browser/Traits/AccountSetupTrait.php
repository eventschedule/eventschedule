<?php

namespace Tests\Browser\Traits;

use Laravel\Dusk\Browser;

trait AccountSetupTrait
{
    /**
     * Set up a test account with basic data
     */
    protected function setupTestAccount(Browser $browser, string $name = 'Talent', string $email = 'test@gmail.com', string $password = 'password'): void
    {
        // Sign up
        $browser->visit('/sign_up')
                ->type('name', $name)
                ->type('email', $email)
                ->type('password', $password)
                ->check('terms')
                ->scrollIntoView('button[type="submit"]')
                ->press('SIGN UP')
                ->waitForLocation('/events', 5)
                ->assertPathIs('/events')
                ->assertSee($name);
    }

    /**
     * Create a test venue
     */
    protected function createTestVenue(Browser $browser, string $name = 'Venue', string $address = '123 Test St'): void
    {
        $browser->visit('/new/venue')
                ->waitForText('New Schedule', 5)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->click('a[data-section="section-address"]')
                ->waitFor('#address1', 5)
                ->type('address1', $address)
                ->click('button[type="submit"]')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 5)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test talent
     */
    protected function createTestTalent(Browser $browser, string $name = 'Talent'): void
    {
        $browser->visit('/new/talent')
                ->waitForText('New Schedule', 5)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->scrollIntoView('button[type="submit"]')
                ->click('button[type="submit"]')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 5)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test curator
     */
    protected function createTestCurator(Browser $browser, string $name = 'Curator'): void
    {
        $browser->visit('/new/curator')
                ->waitForText('New Schedule', 5)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->click('a[data-section="section-settings"]')
                ->waitFor('#accept_requests', 5)
                ->check('accept_requests')
                ->click('button[type="submit"]')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 5)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test event with tickets
     */
    protected function createTestEventWithTickets(Browser $browser, string $talentName = 'Talent', string $venueName = 'Venue', string $eventName = 'Test Event'): void
    {
        $browser->visit('/' . strtolower(str_replace(' ', '-', $talentName)) . '/add-event?date=' . date('Y-m-d', strtotime('+3 days')))
                ->type('name', $eventName)
                ->click('a[data-section="section-venue"]')
                ->pause(1000)
                ->select('#selected_venue')
                ->click('a[data-section="section-tickets"]')
                ->waitFor('#tickets_enabled', 5)
                ->check('tickets_enabled')
                ->type('tickets[0][price]', '10')
                ->type('tickets[0][quantity]', '50')
                ->type('tickets[0][description]', 'General admission ticket')                    
                ->click('button[type="submit"]')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $talentName)) . '/schedule', 5)
                ->assertSee($venueName);
    }

    /**
     * Enable API for the current user
     */
    protected function enableApi(Browser $browser): string
    {
        $browser->visit('/settings#section-api')
                ->waitFor('#enable_api', 5)
                ->scrollIntoView('#enable_api')
                ->check('enable_api')
                ->click('button[type="submit"]')
                ->waitForText('API settings updated successfully', 5)
                ->pause(1000);
        
        // Get the API key from the page - it should be visible after enabling
        $browser->waitFor('#api_key', 5);
        $apiKey = $browser->value('#api_key');
        return $apiKey;
    }

    /**
     * Logout user
     */
    protected function logoutUser(Browser $browser, string $name = 'John Doe'): void
    {
        /*
        $browser->visit('/events')
            ->waitForText($name, 5)
            ->press($name)
            ->waitForText('Log Out', 5)
            ->clickLink('Log Out')
            ->waitForLocation('/login', 5)
            ->assertPathIs('/login');
        */
        
        $browser->script("
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/logout';
            var csrf = document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrf;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        ");

        $browser->waitForLocation('/login', 5)
            ->assertPathIs('/login');
    }
} 