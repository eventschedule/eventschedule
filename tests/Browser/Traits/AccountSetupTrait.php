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
                ->waitForLocation('/events', 20)
                ->assertPathIs('/events')
                ->assertSee($name);
    }

    /**
     * Create a test venue
     */
    protected function createTestVenue(Browser $browser, string $name = 'Venue', string $address = '123 Test St'): void
    {
        $browser->visit('/new/venue')
                ->waitForLocation('/new/venue', 10)
                ->assertPathIs('/new/venue')
                ->waitFor('input[name="name"]', 10)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->type('address1', $address)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 20)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test talent
     */
    protected function createTestTalent(Browser $browser, string $name = 'Talent'): void
    {
        $browser->visit('/new/talent')
                ->waitForLocation('/new/talent', 10)
                ->assertPathIs('/new/talent')
                ->waitFor('input[name="name"]', 10)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 20)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test curator
     */
    protected function createTestCurator(Browser $browser, string $name = 'Curator'): void
    {
        $browser->visit('/new/curator')
                ->waitForLocation('/new/curator', 10)
                ->assertPathIs('/new/curator')
                ->waitFor('input[name="name"]', 10)
                ->clear('name')
                ->type('name', $name)
                ->pause(1000)
                ->scrollIntoView('input[name="accept_requests"]')
                ->check('accept_requests')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule', 20)
                ->assertPathIs('/' . strtolower(str_replace(' ', '-', $name)) . '/schedule');
    }

    /**
     * Create a test event with tickets
     */
    protected function createTestEventWithTickets(Browser $browser, string $talentName = 'Talent', string $venueName = 'Venue', string $eventName = 'Test Event'): void
    {
        $browser->visit('/' . strtolower(str_replace(' ', '-', $talentName)) . '/add-event?date=' . date('Y-m-d', strtotime('+3 days')));

        $this->selectExistingVenue($browser);

        $browser->type('name', $eventName)
                ->scrollIntoView('input[name="tickets_enabled"]')
                ->check('tickets_enabled')
                ->type('tickets[0][price]', '10')
                ->type('tickets[0][quantity]', '50')
                ->type('tickets[0][description]', 'General admission ticket')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/' . strtolower(str_replace(' ', '-', $talentName)) . '/schedule', 20)
                ->assertSee($venueName);
    }

    /**
     * Select the first available venue for the event form using Vue state
     */
    protected function selectExistingVenue(Browser $browser): void
    {
        $browser->waitFor('#selected_venue', 5);

        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script('return window.app && Array.isArray(window.app.venues) && window.app.venues.length > 0;');

            return ! empty($result) && $result[0];
        });

        $browser->script(<<<'JS'
            if (window.app && Array.isArray(window.app.venues) && window.app.venues.length > 0) {
                window.app.venueType = 'use_existing';
                window.app.selectedVenue = window.app.venues[0];
            }
        JS);

        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script("return (function () {\n                var input = document.querySelector('input[name=\"venue_id\"]');\n                return !!(input && input.value);\n            })();");

            return ! empty($result) && $result[0];
        });
    }

    /**
     * Add the first available member to the event form using Vue state
     */
    protected function addExistingMember(Browser $browser): void
    {
        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script('return window.app && Array.isArray(window.app.filteredMembers) && window.app.filteredMembers.length > 0;');

            return ! empty($result) && $result[0];
        });

        $browser->script(<<<'JS'
            if (window.app && Array.isArray(window.app.filteredMembers) && window.app.filteredMembers.length > 0) {
                window.app.memberType = 'use_existing';
                window.app.selectedMember = window.app.filteredMembers[0];

                if (typeof window.app.addExistingMember === 'function') {
                    window.app.addExistingMember();
                }
            }
        JS);

        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script('return window.app && Array.isArray(window.app.selectedMembers) && window.app.selectedMembers.length > 0;');

            return ! empty($result) && $result[0];
        });
    }

    /**
     * Enable API for the current user
     */
    protected function enableApi(Browser $browser): string
    {
        $browser->visit('/settings/integrations')
                ->waitFor('#enable_api', 5)
                ->scrollIntoView('#enable_api');
        $browser->script("document.getElementById('enable_api').checked = true;");
        $browser->press('Save')
                ->waitForText('API settings updated successfully', 5);
        
        // Get the API key from the page - it should be visible after enabling
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

        $browser->waitForLocation('/login', 20)
            ->assertPathIs('/login');
    }
} 
