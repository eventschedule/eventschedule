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
        // Clear any stale cookies/session from previous tests
        $browser->driver->manage()->deleteAllCookies();

        // Sign up
        $browser->visit('/sign_up')
            ->waitFor('#name', 15)
            ->type('name', $name)
            ->type('email', $email)
            ->type('password', $password)
            ->check('terms')
            ->scrollIntoView('button[type="submit"]')
            ->press('SIGN UP')
            ->waitForLocation('/dashboard', 30)
            ->assertPathIs('/dashboard')
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

        // Ensure name was set (JS fallback for headless Chrome flakiness)
        $browser->script("
            var nameField = document.getElementById('name');
            if (!nameField.value) {
                nameField.value = ".json_encode($name).";
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        // Use JavaScript to switch to address section (more reliable than clicking the nav link)
        $browser->script("document.querySelector('a[data-section=\"section-address\"]').click()");

        $browser->waitFor('#address1', 15)
            ->type('address1', $address);

        // Ensure address was set (JS fallback for headless Chrome flakiness)
        $browser->script("
            var addressField = document.getElementById('address1');
            if (!addressField.value) {
                addressField.value = ".json_encode($address).";
                addressField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

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
            ->waitFor('#edit-form', 15)
            ->pause(500);

        // Ensure we're on the General tab (page may land on a different tab)
        $browser->script("
            var generalTab = document.querySelector('button.details-tab[data-tab=\"general\"]');
            if (generalTab) generalTab.click();
        ");
        $browser->pause(500)
            ->clear('name')
            ->type('name', $name);

        // Ensure name was set (JS fallback for headless Chrome flakiness)
        $browser->script("
            var nameField = document.getElementById('name');
            if (!nameField.value || nameField.value !== ".json_encode($name).') {
                nameField.value = '.json_encode($name).";
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

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

        // Ensure name was set (JS fallback for headless Chrome flakiness)
        $browser->script("
            var nameField = document.getElementById('name');
            if (!nameField.value || nameField.value !== ".json_encode($name).') {
                nameField.value = '.json_encode($name).";
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        // Use JavaScript to switch to engagement section (where the requests tab lives)
        $browser->script("document.querySelector('a[data-section=\"section-engagement\"]').click()");

        $browser->pause(500)
            ->click('button.engagement-tab[data-tab="requests"]')
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
        $eventDate = date('Y-m-d', strtotime('+3 days'));
        $browser->visit('/'.strtolower(str_replace(' ', '-', $talentName)).'/add-event?date='.$eventDate)
            ->waitFor('#event_name', 15)
            ->pause(1000);

        // Set event name via JS (more reliable than Dusk type in headless Chrome)
        $browser->script("
            var nameField = document.getElementById('event_name');
            nameField.value = ".json_encode($eventName).";
            nameField.dispatchEvent(new Event('input', { bubbles: true }));
        ");

        // Navigate to venue section via JS
        $browser->script("document.querySelector('a[data-section=\"section-venue\"]').click()");
        $browser->waitFor('#in_person', 10);
        $browser->script("var cb = document.getElementById('in_person'); if (!cb.checked) cb.click();");
        $browser->waitFor('#selected_venue', 5)
            ->select('#selected_venue');

        // Navigate to tickets section via JS
        $browser->script("document.querySelector('a[data-section=\"section-tickets\"]').click()");
        $browser->waitFor('#ticket_mode_tickets', 10)
            ->click('label[for="ticket_mode_tickets"]')
            ->pause(1000);

        // Configure ticket via Vue (more reliable than DOM type in headless Chrome)
        $browser->script("
            var v = window.vueApp;
            v.tickets[0].price = 10;
            v.tickets[0].quantity = 50;
        ");

        // Submit the event form
        $browser->script("
            window._skipUnsavedWarning = true;
            document.getElementById('edit-form').requestSubmit();
        ");

        $browser->waitForLocation('/'.strtolower(str_replace(' ', '-', $talentName)).'/schedule', 45)
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
     * Upgrade a schedule to enterprise plan
     */
    protected function upgradeToEnterprise(string $slug): void
    {
        \App\Models\Role::where('subdomain', $slug)->update([
            'plan_type' => 'enterprise',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);
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
            ->type('password', $password);

        // Ensure fields were set (JS fallback for headless Chrome flakiness)
        $browser->script("
            var emailField = document.getElementById('email');
            if (!emailField.value) {
                emailField.value = ".json_encode($email).";
                emailField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            var passwordField = document.getElementById('password');
            if (!passwordField.value) {
                passwordField.value = ".json_encode($password).";
                passwordField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");

        $browser->pause(500);

        // Use JavaScript to submit form (avoids click-targeting issues in headless Chrome)
        $browser->script("document.querySelector('form').requestSubmit()");

        $browser->waitForLocation('/dashboard', 15)
            ->assertPathIs('/dashboard');
    }

    /**
     * Logout user
     */
    protected function logoutUser(Browser $browser, string $name = 'John Doe'): void
    {
        /*
        $browser->visit('/dashboard')
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

        $browser->waitForLocation('/', 5)
            ->assertPathIs('/');
    }
}
