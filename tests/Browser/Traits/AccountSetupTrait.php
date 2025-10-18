<?php

namespace Tests\Browser\Traits;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Throwable;

trait AccountSetupTrait
{
    protected ?string $testAccountEmail = null;
    protected ?int $testAccountUserId = null;

    /**
     * @var array<string, array<string, string>>
     */
    protected array $roleSlugs = [];

    /**
     * Set up a test account with basic data
     */
    protected function setupTestAccount(Browser $browser, string $name = 'Talent', string $email = 'test@gmail.com', string $password = 'password'): void
    {
        $this->testAccountEmail = $email;
        $this->testAccountUserId = null;

        // Sign up
        $browser->visit('/')
                ->cookie('browser_testing', '1')
                ->visit('/sign_up')
                ->type('name', $name)
                ->type('email', $email)
                ->type('password', $password)
                ->check('terms')
                ->scrollIntoView('button[type="submit"]')
                ->click('@sign-up-button')
                ->waitUsing(20, 100, function () use ($browser) {
                    $currentUrl = $browser->driver->getCurrentURL();

                    if (! is_string($currentUrl)) {
                        return false;
                    }

                    $path = parse_url($currentUrl, PHP_URL_PATH) ?: '';

                    return in_array($path, ['/events', '/login'], true);
                });

        $currentUrl = $browser->driver->getCurrentURL();
        $currentPath = is_string($currentUrl) ? parse_url($currentUrl, PHP_URL_PATH) : null;

        if ($currentPath !== '/events') {
            $browser->assertPathIs('/login')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('@log-in-button')
                    ->waitForLocation('/events', 20);

            $currentPath = '/events';
        }

        $browser->assertPathIs('/events')
                ->assertSee($name);

        if ($user = $this->resolveTestAccountUser()) {
            $this->testAccountUserId = $user->id;
        }
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
                ->press('Save');

        $slug = $this->getRoleSlug('venue', $name, 15);
        $schedulePath = '/' . $slug . '/schedule';

        $browser->waitForLocation($schedulePath, 20)
                ->assertPathIs($schedulePath);
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
                ->press('Save');

        $slug = $this->getRoleSlug('talent', $name, 15);
        $schedulePath = '/' . $slug . '/schedule';

        $browser->waitForLocation($schedulePath, 20)
                ->assertPathIs($schedulePath);
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
                ->press('Save');

        $slug = $this->getRoleSlug('curator', $name, 15);
        $schedulePath = '/' . $slug . '/schedule';

        $browser->waitForLocation($schedulePath, 20)
                ->assertPathIs($schedulePath);
    }

    /**
     * Create a test event with tickets
     */
    protected function createTestEventWithTickets(Browser $browser, string $talentName = 'Talent', string $venueName = 'Venue', string $eventName = 'Test Event'): void
    {
        $talentSlug = $this->getRoleSlug('talent', $talentName, 15);

        $browser->visit('/' . $talentSlug . '/add-event?date=' . date('Y-m-d', strtotime('+3 days')));

        $this->selectExistingVenue($browser);

        $browser->type('name', $eventName)
                ->scrollIntoView('input[name="tickets_enabled"]')
                ->check('tickets_enabled')
                ->type('tickets[0][price]', '10')
                ->type('tickets[0][quantity]', '50')
                ->type('tickets[0][description]', 'General admission ticket')
                ->scrollIntoView('button[type="submit"]')
                ->press('Save');

        $schedulePath = '/' . $talentSlug . '/schedule';

        $browser->waitForLocation($schedulePath, 20)
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
                ->scrollIntoView('#enable_api')
                ->check('enable_api');

        $browser->press('Save');

        $browser->waitUsing(5, 100, function () use ($browser) {
            return $browser->element('@api-settings-success') !== null
                || $browser->element('#api_key') !== null;
        });

        $apiKey = null;

        try {
            $browser->waitUsing(15, 200, function () use (&$apiKey) {
                $apiKey = $this->resolveApiKeyFromDatabase();

                return ! empty($apiKey) && strlen($apiKey) >= 32;
            });
        } catch (Throwable $exception) {
            $apiKey = $this->resolveApiKeyFromDatabase();

            if (empty($apiKey)) {
                throw $exception;
            }
        }

        if ($browser->element('#api_key')) {
            $browser->waitUsing(5, 100, function () use ($browser) {
                $value = $browser->value('#api_key');

                return ! empty($value) && strlen($value) >= 32;
            });
        }

        if ($browser->element('@api-settings-success')) {
            $browser->assertSeeIn('@api-settings-success', 'API settings updated successfully');
        }

        return $apiKey;
    }

    protected function resolveApiKeyFromDatabase(): ?string
    {
        if (! $this->testAccountEmail) {
            return null;
        }

        $user = $this->resolveTestAccountUser();

        return optional($user)->api_key;
    }

    protected function getRoleSlug(string $type, string $name, int $waitSeconds = 5): string
    {
        $typeKey = strtolower($type);

        if (isset($this->roleSlugs[$typeKey][$name]) && $this->roleSlugs[$typeKey][$name] !== '') {
            return $this->roleSlugs[$typeKey][$name];
        }

        $slug = $this->waitForRoleSubdomain($typeKey, $name, $waitSeconds);

        if (! empty($slug)) {
            return $slug;
        }

        $fallback = Str::slug($name);

        $this->roleSlugs[$typeKey][$name] = $fallback;

        return $fallback;
    }

    protected function waitForRoleSubdomain(string $type, string $name, int $seconds = 5): ?string
    {
        $typeKey = strtolower($type);
        $deadline = microtime(true) + max($seconds, 1);
        $slug = null;

        do {
            $slug = $this->resolveRoleSubdomain($name, $typeKey);

            if (! empty($slug)) {
                $this->rememberRoleSlug($typeKey, $name, $slug);

                return $slug;
            }

            usleep(100000);
        } while (microtime(true) < $deadline);

        return $slug;
    }

    protected function rememberRoleSlug(string $type, string $name, string $slug): void
    {
        if ($slug === '') {
            return;
        }

        $typeKey = strtolower($type);

        $this->roleSlugs[$typeKey][$name] = $slug;
    }

    protected function resolveRoleSubdomain(string $name, ?string $type = null): ?string
    {
        $query = Role::query();

        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }

        if ($name !== '') {
            $query->where('name', $name);
        }

        if ($user = $this->resolveTestAccountUser()) {
            $query->where('user_id', $user->id);
        }

        $role = $query->latest('id')->first();

        if (! $role && $name !== '') {
            $fallback = Role::query()->where('name', $name);

            if ($type !== null && $type !== '') {
                $fallback->where('type', $type);
            }

            $role = $fallback->latest('id')->first();
        }

        if ($role) {
            $this->rememberRoleSlug($type ?? ($role->type ?? ''), $name, $role->subdomain);

            return $role->subdomain;
        }

        return null;
    }

    protected function resolveTestAccountUser(): ?User
    {
        if ($this->testAccountUserId) {
            $user = User::find($this->testAccountUserId);

            if ($user) {
                return $user;
            }

            $this->testAccountUserId = null;
        }

        if (! $this->testAccountEmail) {
            return null;
        }

        $user = User::where('email', $this->testAccountEmail)
            ->latest('id')
            ->first();

        if ($user) {
            $this->testAccountUserId = $user->id;
        }

        return $user;
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
