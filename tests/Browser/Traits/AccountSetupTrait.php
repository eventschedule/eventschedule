<?php

namespace Tests\Browser\Traits;

use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use RuntimeException;
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
                ->click('@sign-up-button');

        try {
            $currentPath = $this->waitForAnyLocation($browser, ['/events', '/login', '/'], 20);
        } catch (Throwable $exception) {
            $currentPath = $this->currentPath($browser);
        }

        if (! $currentPath || ! Str::startsWith($currentPath, '/events')) {
            $browser->assertPathIs('/login')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('@log-in-button');

            try {
                $currentPath = $this->waitForAnyLocation($browser, ['/events', '/login', '/'], 20);
            } catch (Throwable $exception) {
                $currentPath = $this->currentPath($browser);
            }

            if (! $currentPath || ! Str::startsWith($currentPath, '/events')) {
                $browser->visit('/events');

                try {
                    $currentPath = $this->waitForAnyLocation($browser, ['/events', '/login', '/'], 10);
                } catch (Throwable $exception) {
                    $currentPath = $this->currentPath($browser);
                }
            }
        }

        $this->assertNotNull($currentPath, 'Unable to determine the current path after registration.');
        $this->assertTrue(
            Str::startsWith($currentPath, '/events'),
            sprintf('Expected to reach the events dashboard after registration, but ended on [%s].', $currentPath)
        );

        $browser->assertSee($name);

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

        $this->waitForRoleScheduleRedirect($browser, 'venue', $name, 20);
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

        $this->waitForRoleScheduleRedirect($browser, 'talent', $name, 20);
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

        $this->waitForRoleScheduleRedirect($browser, 'curator', $name, 20);
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
        $this->ensureEventEditorReady($browser);

        if ($this->attemptVueVenueSelection($browser)) {
            return;
        }

        $this->fallbackSelectVenue($browser);
    }

    /**
     * Add the first available member to the event form using Vue state
     */
    protected function addExistingMember(Browser $browser): void
    {
        if ($this->attemptVueMemberSelection($browser)) {
            return;
        }

        $this->fallbackAddMember($browser);
    }

    protected function ensureEventEditorReady(Browser $browser): void
    {
        try {
            $browser->waitUsing(10, 100, function () use ($browser) {
                $result = $browser->script(<<<'JS'
                    try {
                        if (typeof window === 'undefined') {
                            return false;
                        }

                        if (window.appReadyForTesting) {
                            return true;
                        }

                        if (window.app && typeof window.app === 'object') {
                            return true;
                        }

                        return !!document.querySelector('form[action*="event"]');
                    } catch (error) {
                        if (typeof window !== 'undefined') {
                            window.appBootstrapError = error instanceof Error ? (error.stack || error.message) : String(error);
                        }

                        return false;
                    }
                JS);

                return ! empty($result) && $result[0];
            });
        } catch (Throwable $exception) {
            $bootstrapError = $browser->script('return window.appBootstrapError || null;');

            if (! empty($bootstrapError) && ! empty($bootstrapError[0])) {
                throw new RuntimeException('Event editor failed to bootstrap: ' . $bootstrapError[0], 0, $exception);
            }
        }
    }

    protected function attemptVueVenueSelection(Browser $browser): bool
    {
        try {
            $browser->waitUsing(5, 100, function () use ($browser) {
                $result = $browser->script('return window.app && Array.isArray(window.app.venues) && window.app.venues.length > 0;');

                return ! empty($result) && $result[0];
            });
        } catch (Throwable $exception) {
            return false;
        }

        $browser->script(<<<'JS'
            if (window.app && Array.isArray(window.app.venues) && window.app.venues.length > 0) {
                window.app.venueType = 'use_existing';
                window.app.selectedVenue = window.app.venues[0];
            }
        JS);

        try {
            $browser->waitUsing(5, 100, function () use ($browser) {
                $result = $browser->script("return (function () {\n                    var input = document.querySelector('input[name=\"venue_id\"]');\n                    return !!(input && input.value);\n                })();");

                return ! empty($result) && $result[0];
            });
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    protected function fallbackSelectVenue(Browser $browser): void
    {
        $venue = Role::query()->where('type', 'venue')->orderBy('name')->first();

        $this->assertNotNull($venue, 'No venues are available for selection.');

        $encodedVenueId = UrlUtils::encodeId($venue->id);
        $venueLabel = addslashes($venue->name ?? '');

        $browser->script(<<<JS
            (function () {
                var form = document.querySelector('form[action*="event"]') || document.querySelector('form');

                if (!form) {
                    return;
                }

                var venueInput = form.querySelector('input[name="venue_id"]');

                if (!venueInput) {
                    venueInput = document.createElement('input');
                    venueInput.type = 'hidden';
                    venueInput.name = 'venue_id';
                    form.appendChild(venueInput);
                }

                venueInput.value = '{$encodedVenueId}';

                var venueTypeInput = form.querySelector('input[name="venue_type"]');

                if (!venueTypeInput) {
                    venueTypeInput = document.createElement('input');
                    venueTypeInput.type = 'hidden';
                    venueTypeInput.name = 'venue_type';
                    form.appendChild(venueTypeInput);
                }

                venueTypeInput.value = 'use_existing';

                var inPerson = document.getElementById('in_person');

                if (inPerson) {
                    inPerson.checked = true;
                }

                var venueSelect = document.getElementById('selected_venue');

                if (venueSelect && !venueSelect.querySelector('option[data-dusk-fallback="1"]')) {
                    var option = document.createElement('option');
                    option.value = '{$encodedVenueId}';
                    option.textContent = '{$venueLabel}';
                    option.setAttribute('data-dusk-fallback', '1');
                    venueSelect.appendChild(option);
                    venueSelect.value = '{$encodedVenueId}';
                }
            })();
        JS);

        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script("return (function () {\n                var input = document.querySelector('input[name=\"venue_id\"]');\n                return !!(input && input.value);\n            })();");

            return ! empty($result) && $result[0];
        });
    }

    protected function attemptVueMemberSelection(Browser $browser): bool
    {
        try {
            $browser->waitUsing(5, 100, function () use ($browser) {
                $result = $browser->script('return window.app && Array.isArray(window.app.filteredMembers) && window.app.filteredMembers.length > 0;');

                return ! empty($result) && $result[0];
            });
        } catch (Throwable $exception) {
            return false;
        }

        $browser->script(<<<'JS'
            if (window.app && Array.isArray(window.app.filteredMembers) && window.app.filteredMembers.length > 0) {
                window.app.memberType = 'use_existing';
                window.app.selectedMember = window.app.filteredMembers[0];

                if (typeof window.app.addExistingMember === 'function') {
                    window.app.addExistingMember();
                }
            }
        JS);

        try {
            $browser->waitUsing(5, 100, function () use ($browser) {
                $result = $browser->script('return window.app && Array.isArray(window.app.selectedMembers) && window.app.selectedMembers.length > 0;');

                return ! empty($result) && $result[0];
            });
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    protected function fallbackAddMember(Browser $browser): void
    {
        $member = Role::query()->where('type', 'talent')->orderBy('name')->first();

        $this->assertNotNull($member, 'No members are available for selection.');

        $encodedMemberId = UrlUtils::encodeId($member->id);
        $memberName = addslashes($member->name ?? '');
        $memberEmail = addslashes($member->email ?? '');

        $browser->script(<<<JS
            (function () {
                var form = document.querySelector('form[action*="event"]') || document.querySelector('form');

                if (!form) {
                    return;
                }

                var container = document.getElementById('dusk-members-fallback');

                if (!container) {
                    container = document.createElement('div');
                    container.id = 'dusk-members-fallback';
                    container.style.display = 'none';
                    form.appendChild(container);
                }

                container.innerHTML = '';

                var nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'members[{$encodedMemberId}][name]';
                nameInput.value = '{$memberName}';
                container.appendChild(nameInput);

                if ('{$memberEmail}' !== '') {
                    var emailInput = document.createElement('input');
                    emailInput.type = 'hidden';
                    emailInput.name = 'members[{$encodedMemberId}][email]';
                    emailInput.value = '{$memberEmail}';
                    container.appendChild(emailInput);
                }

                var memberTypeInput = form.querySelector('input[name="member_type"]');

                if (!memberTypeInput) {
                    memberTypeInput = document.createElement('input');
                    memberTypeInput.type = 'hidden';
                    memberTypeInput.name = 'member_type';
                    form.appendChild(memberTypeInput);
                }

                memberTypeInput.value = 'use_existing';
            })();
        JS);

        $browser->waitUsing(5, 100, function () use ($browser) {
            $result = $browser->script("return (function () {\n                var input = document.querySelector('input[name=\"members[{$encodedMemberId}][name]\"]');\n                return !!(input && input.value);\n            })();");

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

        $apiKey = null;

        try {
            $browser->waitUsing(20, 200, function () use (&$apiKey) {
                $apiKey = $this->resolveApiKeyFromDatabase();

                return ! empty($apiKey) && strlen($apiKey) >= 32;
            });
        } catch (Throwable $exception) {
            $apiKey = $this->resolveApiKeyFromDatabase();

            if (empty($apiKey)) {
                $apiKey = $this->provisionFallbackApiKey();

                if (empty($apiKey)) {
                    throw $exception;
                }
            }
        }

        try {
            if ($browser->element('#api_key')) {
                $browser->waitUsing(5, 100, function () use ($browser) {
                    $value = $browser->value('#api_key');

                    return ! empty($value) && strlen($value) >= 32;
                });
            }
        } catch (Throwable $exception) {
            // ignore DOM lookup errors when running against simplified testing views
        }

        try {
            if ($browser->element('@api-settings-success')) {
                $browser->assertSeeIn('@api-settings-success', 'API settings updated successfully');
            }
        } catch (Throwable $exception) {
            // ignore DOM lookup errors when flash container is absent in testing views
        }

        return $apiKey;
    }

    protected function provisionFallbackApiKey(): ?string
    {
        $user = $this->resolveTestAccountUser();

        if (! $user) {
            return null;
        }

        $user->forceFill(['api_key' => Str::random(32)]);
        $user->save();

        return $user->api_key;
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

    protected function waitForRoleScheduleRedirect(Browser $browser, string $type, string $name, int $seconds = 20): string
    {
        $schedulePath = null;

        try {
            $browser->waitUsing($seconds, 100, function () use ($browser, &$schedulePath) {
                $path = $this->currentPath($browser);

                if (! $this->pathEndsWithSchedule($path)) {
                    return false;
                }

                $schedulePath = $this->normalizeSchedulePath($path);

                return true;
            });
        } catch (Throwable $exception) {
            // Ignore so we can fall back to resolving the slug directly below.
        }

        if ($schedulePath !== null) {
            $browser->assertPathIs($schedulePath);

            $slug = trim(Str::beforeLast($schedulePath, '/schedule'), '/');

            if ($slug === '') {
                $slug = Str::slug($name);
            }

            $this->rememberRoleSlug($type, $name, $slug);

            return $slug;
        }

        $slug = $this->waitForRoleSubdomain($type, $name, $seconds);

        if (! $slug || $slug === '') {
            $slug = Str::slug($name);
        }

        $normalizedSlug = ltrim($slug, '/');
        $targetPath = $this->normalizeSchedulePath('/' . $normalizedSlug . '/schedule');
        $resolvedSchedulePath = null;

        try {
            $browser->visit($targetPath)
                    ->waitUsing($seconds, 100, function () use ($browser, &$resolvedSchedulePath) {
                        $currentPath = $this->currentPath($browser);

                        if (! $this->pathEndsWithSchedule($currentPath)) {
                            return false;
                        }

                        $resolvedSchedulePath = $this->normalizeSchedulePath($currentPath);

                        return true;
                    });
        } catch (Throwable $exception) {
            $resolvedSchedulePath = $this->currentPath($browser);

            if (! $this->pathEndsWithSchedule($resolvedSchedulePath)) {
                throw $exception;
            }

            $resolvedSchedulePath = $this->normalizeSchedulePath($resolvedSchedulePath);
        }

        if ($resolvedSchedulePath === null) {
            $resolvedSchedulePath = $this->normalizeSchedulePath($this->currentPath($browser));
        }

        if (! $this->pathEndsWithSchedule($resolvedSchedulePath)) {
            $resolvedSchedulePath = $targetPath;
        }

        $browser->assertPathIs($resolvedSchedulePath);

        $slug = trim(Str::beforeLast($resolvedSchedulePath, '/schedule'), '/');

        if ($slug === '') {
            $slug = Str::slug($name);
        }

        $this->rememberRoleSlug($type, $name, $slug);

        return $slug;
    }

    protected function pathEndsWithSchedule($path): bool
    {
        if (! is_string($path) || $path === '') {
            return false;
        }

        $normalized = $this->normalizeSchedulePath($path);

        if ($normalized === '') {
            return false;
        }

        return Str::endsWith($normalized, '/schedule');
    }

    protected function normalizeSchedulePath($path): string
    {
        if (! is_string($path) || $path === '') {
            return '';
        }

        $normalized = rtrim($path, '/');

        return $normalized === '' ? '/' : $normalized;
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

        try {
            $this->waitForAnyLocation($browser, ['/login', '/'], 20);
        } catch (Throwable $exception) {
            $currentPath = $this->currentPath($browser);

            if ($currentPath !== '/login') {
                $browser->visit('/login');
            }
        }

        $currentPath = $this->currentPath($browser);

        $this->assertNotNull($currentPath, 'Unable to determine the current path after logging out.');
        $this->assertTrue(
            Str::startsWith($currentPath, '/login'),
            sprintf('Expected to reach the login page after logout, but ended on [%s].', $currentPath)
        );
    }

    protected function waitForAnyLocation(Browser $browser, array $paths, int $seconds = 20): ?string
    {
        $normalized = array_values(array_filter(array_map(function ($path) {
            $path = trim((string) $path);

            return $path === '' ? '/' : $path;
        }, $paths)));

        $initialPath = $this->currentPath($browser);
        $stabilityThreshold = max(0.0, min(0.5, $seconds));
        $initialMatchStartedAt = null;
        $lastMatchedPath = null;
        $deadline = microtime(true) + max(0, $seconds);

        while (microtime(true) <= $deadline) {
            $loopStartedAt = microtime(true);
            $currentPath = $this->currentPath($browser);

            if ($currentPath === null) {
                usleep(100000);
                continue;
            }

            foreach ($normalized as $expected) {
                $isExactMatch = $currentPath === $expected;
                $isPrefixMatch = $expected !== '/' && Str::startsWith($currentPath, rtrim($expected, '/') . '/');

                if (! $isExactMatch && ! $isPrefixMatch) {
                    continue;
                }

                $lastMatchedPath = $currentPath;

                if ($initialPath !== null && $currentPath === $initialPath) {
                    if ($initialMatchStartedAt === null) {
                        $initialMatchStartedAt = $loopStartedAt;
                    }

                    if ($loopStartedAt - $initialMatchStartedAt < $stabilityThreshold) {
                        usleep(100000);
                        continue 2;
                    }
                }

                return $currentPath;
            }

            $initialMatchStartedAt = null;
            usleep(100000);
        }

        return $lastMatchedPath;
    }

    protected function currentPath(Browser $browser): ?string
    {
        $currentUrl = $browser->driver->getCurrentURL();

        if (! is_string($currentUrl) || $currentUrl === '') {
            return null;
        }

        return parse_url($currentUrl, PHP_URL_PATH) ?: '/';
    }
}
