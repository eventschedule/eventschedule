<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;

class GeneralTest extends DuskTestCase
{
    use DatabaseTruncation;
    use AccountSetupTrait;

    /**
     * A basic browser test example.
     */
    public function testGeneral(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);

            // Log out
            $this->logoutUser($browser, $name);

            // Log back in
            $browser->visit('/login')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('@log-in-button');

            $currentPath = $this->waitForAnyLocation($browser, ['/events', '/login'], 20);

            $this->assertNotNull($currentPath, 'Unable to determine the current path after logging in.');
            $this->assertTrue(
                Str::startsWith($currentPath, '/events'),
                sprintf('Expected to reach the events dashboard after logging in, but ended on [%s].', $currentPath)
            );

            $browser->assertPathIs($currentPath)
                    ->assertSee($name);

            // Create/edit venue using the trait
            $this->createTestVenue($browser);
            $venueSlug = $this->getRoleSlug('venue', 'Venue');

            $browser->clickLink('Edit Venue')
                    ->assertPathIs('/' . $venueSlug . '/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('Save');

            $this->waitForPath($browser, '/' . $venueSlug . '/schedule', 20);

            $browser->assertSee('google.com');

            // Create/edit talent using the trait
            $this->createTestTalent($browser);
            $talentSlug = $this->getRoleSlug('talent', 'Talent');

            $browser->clickLink('Edit Talent')
                    ->assertPathIs('/' . $talentSlug . '/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('Save');

            $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 20);

            $browser->assertSee('google.com');

            // Create/edit event
            $this->visitRoleAddEventPage($browser, $talentSlug, date('Y-m-d'), 'talent', 'Talent');
            $this->selectExistingVenue($browser);

            $browser->scrollIntoView('button[type="submit"]')
                    ->press('Save');

            $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 20);

            $browser->assertSee('Venue');

            // Create/edit event
            $this->visitRoleAddEventPage($browser, $venueSlug, date('Y-m-d'), 'venue', 'Venue');
            $this->addExistingMember($browser);

            $browser->type('name', 'Venue Event')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('Save');

            $this->waitForPath($browser, '/' . $venueSlug . '/schedule', 20);

            $browser->assertSee('Venue Event');
        });
    }
}
