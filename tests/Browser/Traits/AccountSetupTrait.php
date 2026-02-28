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
            ->waitFor('#name', 15)
            ->type('name', $name)
            ->type('email', $email)
            ->type('password', $password)
            ->check('terms')
            ->scrollIntoView('button[type="submit"]')
            ->press('SIGN UP')
            ->waitForLocation('/events', 15)
            ->assertPathIs('/events')
            ->assertSee($name);
    }

    /**
     * Create a test venue
     */
    protected function createTestVenue(Browser $browser, string $name = 'Venue', string $address = '123 Test St'): void
    {
        $browser->visit('/new/venue')
            ->waitFor('#name', 15)
            ->pause(1000)
            ->type('name', $name);

        // Use JavaScript to switch to address section (more reliable than clicking the nav link)
        $browser->script("document.querySelector('a[data-section=\"section-address\"]').click()");

        $browser->waitFor('#address1', 15)
            ->type('address1', $address);

        // Use JavaScript to submit form (avoids click-targeting issues with multiple submit buttons)
        $browser->script("document.getElementById('edit-form').requestSubmit()");

        $browser->waitForLocation('/'.strtolower(str_replace(' ', '-', $name)).'/schedule', 45)
            ->assertPathIs('/'.strtolower(str_replace(' ', '-', $name)).'/schedule');
    }

    /**
     * Create a test talent
     */
    protected function createTestTalent(Browser $browser, string $name = 'Talent'): void
    {
        $browser->visit('/new/talent')
            ->waitFor('#name', 15)
            ->pause(1000)
            ->clear('name')
            ->type('name', $name);

        // Use JavaScript to submit form (avoids click-targeting issues in headless Chrome)
        $browser->script("document.getElementById('edit-form').requestSubmit()");

        $browser->waitForLocation('/'.strtolower(str_replace(' ', '-', $name)).'/schedule', 45)
            ->assertPathIs('/'.strtolower(str_replace(' ', '-', $name)).'/schedule');
    }

    /**
     * Create a test curator
     */
    protected function createTestCurator(Browser $browser, string $name = 'Curator'): void
    {
        $browser->visit('/new/curator')
            ->waitFor('#name', 15)
            ->pause(1000)
            ->type('name', $name);

        // Use JavaScript to switch to settings section (more reliable than clicking the nav link)
        $browser->script("document.querySelector('a[data-section=\"section-settings\"]').click()");

        $browser->pause(500)
            ->click('button.settings-tab[data-tab="requests"]')
            ->waitFor('#accept_requests', 5)
            ->click('label[for="accept_requests"]')
            ->scrollIntoView('button[type="submit"]')
            ->click('button[type="submit"]')
            ->waitForLocation('/'.strtolower(str_replace(' ', '-', $name)).'/schedule', 15)
            ->assertPathIs('/'.strtolower(str_replace(' ', '-', $name)).'/schedule');
    }

    /**
     * Create a test event with tickets
     */
    protected function createTestEventWithTickets(Browser $browser, string $talentName = 'Talent', string $venueName = 'Venue', string $eventName = 'Test Event'): void
    {
        $browser->visit('/'.strtolower(str_replace(' ', '-', $talentName)).'/add-event?date='.date('Y-m-d', strtotime('+3 days')))
            ->type('name', $eventName)
            ->click('a[data-section="section-venue"]')
            ->pause(1000)
            ->select('#selected_venue')
            ->click('a[data-section="section-tickets"]')
            ->waitFor('#tickets_enabled', 5)
            ->click('label[for="tickets_enabled"]')
            ->pause(1000); // Wait for Vue to render the ticket tabs

        // Use JavaScript to scroll the price input into view and ensure it's visible
        $browser->script("
            const priceInput = document.querySelector('input[name=\"tickets[0][price]\"]');
            if (priceInput) {
                priceInput.scrollIntoView({ block: 'center', behavior: 'instant' });
            }
        ");

        $browser->pause(300)
            ->type('tickets[0][price]', '10')
            ->type('tickets[0][quantity]', '50')
            ->click('button[type="submit"]')
            ->waitForLocation('/'.strtolower(str_replace(' ', '-', $talentName)).'/schedule', 15)
            ->assertSee($venueName);
    }

    /**
     * Enable API for the current user
     */
    protected function enableApi(Browser $browser): string
    {
        $browser->visit('/settings#section-api')
            ->waitFor('#enable_api', 5)
            ->scrollIntoView('#enable_api');

        // Check if already enabled, if not enable it
        $isChecked = $browser->script("return document.getElementById('enable_api').checked;");
        if (! $isChecked[0]) {
            // Click the label to toggle the switch (sr-only inputs can't be clicked directly)
            $browser->click('label[for="enable_api"]');
            // Wait a moment for any UI updates
            $browser->pause(500);
        }

        // Find and click the submit button in the API settings form using JavaScript
        // This is more reliable when the button might not be immediately interactable via Dusk
        $browser->script("
            const checkbox = document.getElementById('enable_api');
            const form = checkbox.closest('form');
            const submitButton = form.querySelector('button[type=\"submit\"]');
            if (submitButton) {
                submitButton.scrollIntoView({ block: 'center', behavior: 'instant' });
            }
        ");

        // Wait a moment for scroll to complete, then click
        $browser->pause(300);

        // Click the button using JavaScript to avoid interactability issues
        $browser->script("
            const checkbox = document.getElementById('enable_api');
            const form = checkbox.closest('form');
            const submitButton = form.querySelector('button[type=\"submit\"]');
            if (submitButton) {
                submitButton.click();
            }
        ");

        $browser->waitForText('API settings updated successfully', 5);

        // Get the API key from the page - it should be visible after enabling
        $browser->waitFor('#api_key', 5);
        $apiKey = $browser->value('#api_key');

        return $apiKey;
    }

    /**
     * Login user
     */
    protected function loginUser(Browser $browser, string $email, string $password): void
    {
        $browser->visit('/login')
            ->waitFor('#email', 5)
            ->pause(500)
            ->type('email', $email)
            ->type('password', $password)
            ->pause(500)
            ->scrollIntoView('button[type="submit"]')
            ->click('button[type="submit"]')
            ->waitForLocation('/events', 15)
            ->assertPathIs('/events');
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
