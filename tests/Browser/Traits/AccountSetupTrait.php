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
        $this->startFromACleanSession($browser);

        // Sign up. assertPathIs first: a session that outlived the previous test would leave us
        // authenticated, and the guest middleware bounces an authenticated visitor to home - which
        // used to surface 30 seconds later as a location timeout instead of here.
        $browser->visit('/sign_up')
            ->assertPathIs('/sign_up')
            ->waitFor('#name', 15)
            ->pause(1000)
            ->type('name', $name)
            ->type('email', $email)
            ->type('password', $password);

        // Repair the fields if headless Chrome mangled them. Value-verify rather than only filling
        // blanks: a dropped keystroke leaves a field non-empty but INVALID, and requestSubmit()
        // then aborts on constraint validation without submitting or reporting anything.
        $browser->script('
            function setValue(id, expected) {
                var field = document.getElementById(id);
                if (field && field.value !== expected) {
                    field.value = expected;
                    field.dispatchEvent(new Event("input", { bubbles: true }));
                    field.dispatchEvent(new Event("change", { bubbles: true }));
                }
            }
            setValue("name", '.json_encode($name).');
            setValue("email", '.json_encode($email).');
            setValue("password", '.json_encode($password).');
            var terms = document.getElementById("terms");
            if (terms && !terms.checked) {
                terms.checked = true;
                terms.dispatchEvent(new Event("change", { bubbles: true }));
            }
        ');

        // requestSubmit() runs HTML5 constraint validation and does NOTHING when it fails: no
        // navigation, no exception, no message. Name the offending control here instead.
        $invalid = $browser->script('
            var form = document.querySelector("form[action*=sign_up]") || document.querySelector("form");
            if (!form) { return "no form on " + window.location.pathname; }
            if (form.checkValidity()) { return ""; }
            return Array.from(form.querySelectorAll(":invalid")).map(function (el) {
                return (el.name || el.id || el.tagName) + "=" + JSON.stringify(el.value);
            }).join(", ");
        ')[0];

        if ($invalid !== '') {
            $this->fail('The sign-up form refused to submit, these controls are invalid: '.$invalid);
        }

        // Use JavaScript to submit form (avoids click-targeting issues in headless Chrome)
        $browser->script('(document.querySelector("form[action*=sign_up]") || document.querySelector("form")).requestSubmit();');

        // Fresh organizer signups land on the focused schedule-type chooser
        // (the getting-started heading greets the user by first name only)
        $this->landOn($browser, '/getting-started', 30);
        $browser->assertSee(explode(' ', $name)[0]);
    }

    /**
     * Put the shared browser back to a signed-out, cookie-free state.
     *
     * Dusk keeps ONE browser for every test method in a class (ProvidesBrowser::createBrowsersFor),
     * so cookies cross the test boundary. deleteAllCookies() alone is not enough: it clears what is
     * there at that instant, and Laravel puts a session cookie on every response, so a navigation
     * the previous test left in flight re-installs the cookie moments later. Landing on a real
     * same-origin page first is what closes that window - visit() blocks on document load, so the
     * stale request is finished before the cookies go. /login also forgets signup_role_type.
     */
    private function startFromACleanSession(Browser $browser): void
    {
        $browser->visit('/login');
        $browser->waitUntil('document.readyState === "complete"', 15);

        $browser->driver->manage()->deleteAllCookies();
        $browser->script('try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}');
    }

    /**
     * Wait for the browser to land on $path, and say what actually happened when it does not.
     *
     * waitForLocation() is a bare `window.location.pathname ==` poll, so "the form never submitted",
     * "the server rejected the post" and "the redirect went somewhere else" all surface as the same
     * opaque "Waited N seconds for location" timeout. Report the page we did land on instead.
     */
    protected function landOn(Browser $browser, string $path, int $seconds = 30): void
    {
        try {
            $browser->waitForLocation($path, $seconds);
        } catch (\Throwable $e) {
            $this->fail(sprintf(
                "Expected to land on %s within %ds, but the browser is on %s.\n%s",
                $path,
                $seconds,
                $this->currentUrl($browser),
                $this->describePage($browser),
            ));
        }

        $browser->assertPathIs($path);
    }

    /**
     * The page detail that separates a rejected post from a form that never submitted.
     */
    private function describePage(Browser $browser): string
    {
        try {
            return (string) $browser->script('
                var errors = Array.from(document.querySelectorAll("[role=alert], .text-red-600, .text-red-500, .text-red-400"))
                    .map(function (el) { return el.textContent.trim(); })
                    .filter(function (text) { return text.length > 0; })
                    .slice(0, 5);
                var form = document.querySelector("form");
                var invalid = form
                    ? Array.from(form.querySelectorAll(":invalid")).map(function (el) { return el.name || el.id; })
                    : [];
                return "title=" + document.title
                    + " | form=" + (form ? form.getAttribute("action") : "none")
                    + " | invalid=" + JSON.stringify(invalid)
                    + " | errors=" + JSON.stringify(errors);
            ')[0];
        } catch (\Throwable $e) {
            return 'the page could not be inspected: '.$e->getMessage();
        }
    }

    /**
     * The driver throws if the session died, and a missing URL must not mask the real failure.
     */
    private function currentUrl(Browser $browser): string
    {
        try {
            return $browser->driver->getCurrentURL();
        } catch (\Throwable $e) {
            return 'an unreadable url ('.$e->getMessage().')';
        }
    }

    /**
     * Wait out the save, wherever RoleController::store() decided to send us, and end up on the
     * schedule page either way.
     *
     * A FIRST schedule now goes on to the event form rather than the schedule admin page, because
     * 91% of the people who ever create one do it within an hour of signing up and a schedule with
     * nothing in it is not what they came for. Every caller of these helpers still expects to be
     * left on /{slug}/schedule, so the difference is absorbed here rather than in seventeen test
     * classes.
     */
    private function landAfterScheduleSave(Browser $browser, string $slug): void
    {
        try {
            $browser->waitUntil(
                'window.location.pathname === "/'.$slug.'/schedule" || window.location.pathname === "/'.$slug.'/add-event"',
                45
            );
        } catch (\Throwable $e) {
            $this->fail(sprintf(
                "Expected to land on /%s/schedule or /%s/add-event, but the browser is on %s.\n%s",
                $slug,
                $slug,
                $this->currentUrl($browser),
                $this->describePage($browser),
            ));
        }

        $browser->visit('/'.$slug.'/schedule');
        $browser->assertPathIs('/'.$slug.'/schedule');
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

        // No section nav to click through any more: the first-run form asks a venue for its
        // address on the same screen as its name.
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

        $this->landAfterScheduleSave($browser, strtolower(str_replace(' ', '-', $name)));
    }

    /**
     * Create a test talent
     */
    protected function createTestTalent(Browser $browser, string $name = 'Talent'): void
    {
        $browser->visit('/new/talent')
            ->waitFor('#edit-form', 15)
            ->pause(500);

        // No tabs on the first-run form, so there is no wrong tab to land on.
        $browser->clear('name')
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

        $this->landAfterScheduleSave($browser, strtolower(str_replace(' ', '-', $name)));
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

        // accept_requests used to be switched on by hand here, through two section clicks and a
        // tab. It no longer has to be: roles.accept_requests defaults to TRUE and the first-run
        // form carries that default, where the full form's toggle rendered OFF and posted a 0 over
        // it. tests/Feature/FirstScheduleFormTest.php pins that.
        $browser->scrollIntoView('button[type="submit"]')
            ->click('button[type="submit"]');

        $this->landAfterScheduleSave($browser, strtolower(str_replace(' ', '-', $name)));
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
        $browser->script('
            var v = window.vueApp;
            v.tickets[0].price = 10;
            v.tickets[0].quantity = 50;
        ');

        // Ensure tickets_enabled is set (radio click may not reliably trigger Vue watcher)
        $browser->script("
            var v = window.vueApp;
            v.ticketMode = 'tickets';
            v.event.tickets_enabled = true;
        ");

        // Submit the event form
        $browser->script("
            window._skipUnsavedWarning = true;
            document.getElementById('edit-form').requestSubmit();
        ");

        $this->landOn($browser, '/'.strtolower(str_replace(' ', '-', $talentName)).'/schedule', 45);
        $browser->assertSee($venueName);
    }

    /**
     * Enable API for the current user
     */
    protected function enableApi(Browser $browser): string
    {
        $browser->visit('/settings#section-api')
            ->waitFor('#enable_api', 5);

        // Set checkbox to checked via JS (clicking sr-only label is unreliable in headless Chrome)
        $browser->script("
            var cb = document.getElementById('enable_api');
            if (!cb.checked) {
                cb.checked = true;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            }
        ");

        // Submit the form
        $browser->script("
            window._skipUnsavedWarning = true;
            document.getElementById('enable_api').closest('form').requestSubmit();
        ");

        $browser->waitForText('API settings updated successfully', 10);

        // Ensure the API section is visible after redirect (toast may appear before section JS runs)
        $browser->waitUntil("document.getElementById('section-api') && document.getElementById('section-api').style.display === 'block'", 5);

        // Get the API key from the page
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
    protected function loginUser(Browser $browser, string $email, string $password, string $expectedPath = '/dashboard'): void
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

        // Zero-schedule users get forwarded to /getting-started; callers pass
        // the expected landing when the user has no schedules yet
        $this->landOn($browser, $expectedPath, 15);
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

        $this->landOn($browser, '/', 15);
    }
}
