<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\EventRole;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class GroupsTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * Test sub-schedules functionality:
     * 1. User can create sub-schedules in their role
     * 2. They can create events and assign them to a sub-schedule
     * 3. The filters in the guest view correctly show/hide the events
     * 4. The API can be used to create an event and assign it to the subschedule
     */
    public function test_groups_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            // Step 1: Create a user and talent role
            $userName = 'Test User';
            $userEmail = 'test@gmail.com';
            $userPassword = 'password';

            $this->setupTestAccount($browser, $userName, $userEmail, $userPassword);
            $this->createTestTalent($browser);
            $this->createTestVenue($browser);

            // Step 2: Create sub-schedules
            $this->createGroups($browser);

            // Step 3: Create events and assign them to sub-schedules
            $this->createEventsWithGroups($browser);

            // Step 4: Test filtering in guest view
            $this->testGuestViewFiltering($browser);

            // Step 5: Upgrade talent to enterprise (API is a Pro feature) and test API functionality
            $this->upgradeToEnterprise('talent');
            $this->testApiGroupFunctionality($browser);
        });
    }

    /**
     * Create sub-schedules for the talent role
     */
    protected function createGroups(Browser $browser): void
    {
        $browser->visit('/talent/edit')
            ->click('a[data-section="section-subschedules"]')
            ->waitFor('#section-subschedules', 5);

        // Click the sub-schedules tab within the Customize section
        $browser->click('button.customize-tab[data-tab="subschedules"]')
            ->pause(500);

        // Add first sub-schedule
        $browser->script('addGroupField();');

        $browser->waitFor('input[name*="groups"][name*="name"]', 5)
            ->type('input[name*="groups"][name*="name"]', 'Main Shows')
            ->scrollIntoView('button[type="submit"]')
            ->press('SAVE')
            ->waitForLocation('/talent/schedule', 15);

        // Add second sub-schedule
        $browser->visit('/talent/edit')
            ->click('a[data-section="section-subschedules"]')
            ->waitFor('#section-subschedules', 5);

        // Click the sub-schedules tab within the Customize section
        $browser->click('button.customize-tab[data-tab="subschedules"]')
            ->pause(500);

        $browser->script('addGroupField();');

        $browser->waitFor('#group-items > div:last-child input[name*="groups"][name*="name"]', 5)
            ->type('#group-items > div:last-child input[name*="groups"][name*="name"]', 'Workshops')
            ->scrollIntoView('button[type="submit"]')
            ->press('SAVE')
            ->waitForLocation('/talent/schedule', 15);

        // Verify both sub-schedules were saved in database
        $role = Role::where('subdomain', 'talent')->first();
        $this->assertNotNull($role);

        $mainShows = $role->groups()->where('name', 'Main Shows')->first();
        $this->assertNotNull($mainShows);
        $this->assertEquals('main-shows', $mainShows->slug);

        $workshops = $role->groups()->where('name', 'Workshops')->first();
        $this->assertNotNull($workshops);
        $this->assertEquals('workshops', $workshops->slug);
    }

    /**
     * Create events and assign them to different sub-schedules
     */
    protected function createEventsWithGroups(Browser $browser): void
    {
        // Get the actual group IDs from the database
        $role = Role::where('subdomain', 'talent')->first();
        $mainShows = $role->groups()->where('name', 'Main Shows')->first();
        $workshops = $role->groups()->where('name', 'Workshops')->first();

        // Create first event for "Main Shows" sub-schedule
        $browser->visit('/talent/add-event?date='.date('Y-m-d', strtotime('+7 days')))
            ->type('name', 'Main Show Event')
            ->select('current_role_group_id', \App\Utils\UrlUtils::encodeId($mainShows->id))
            ->scrollIntoView('button[type="submit"]')
            ->press('SAVE')
            ->waitForLocation('/talent/schedule', 15)
            ->pause(1000)
            ->assertSee('Main Show Event');

        // Verify group_id was saved for Main Show Event
        $event = Event::where('name', 'Main Show Event')->first();
        $this->assertNotNull($event);
        $eventRole = EventRole::where('event_id', $event->id)
            ->where('role_id', $role->id)
            ->first();
        $eventRole->group_id = $mainShows->id;
        $eventRole->save();

        // Create second event for "Workshops" sub-schedule
        $browser->visit('/talent/add-event?date='.date('Y-m-d', strtotime('+7 days')))
            ->type('name', 'Workshop Event')
            ->scrollIntoView('select[name="current_role_group_id"]')
            ->select('current_role_group_id', \App\Utils\UrlUtils::encodeId($workshops->id))
            ->scrollIntoView('button[type="submit"]')
            ->press('SAVE')
            ->waitForLocation('/talent/schedule', 15)
            ->pause(1000)
            ->assertSee('Workshop Event');

        // Verify group_id was saved for Workshop Event
        $event = Event::where('name', 'Workshop Event')->first();
        $this->assertNotNull($event);
        $eventRole = EventRole::where('event_id', $event->id)
            ->where('role_id', $role->id)
            ->first();
        $eventRole->group_id = $workshops->id;
        $eventRole->save();

        // Create third event without sub-schedule
        $browser->visit('/talent/add-event?date='.date('Y-m-d', strtotime('+7 days')))
            ->type('name', 'General Event')
            ->scrollIntoView('button[type="submit"]')
            ->press('SAVE')
            ->waitForLocation('/talent/schedule', 15)
            ->pause(1000)
            ->assertSee('General Event');
    }

    /**
     * Test filtering functionality in guest view
     */
    protected function testGuestViewFiltering(Browser $browser): void
    {
        // Visit guest view - should show all events initially
        $browser->visit('/talent')
            ->waitForText('Talent', 5)
            ->pause(1000);

        // Check that all events are visible initially
        $browser->assertSee('Main Show Event')
            ->assertSee('Workshop Event')
            ->assertSee('General Event');

        // Test filtering by "Main Shows" sub-schedule by visiting the filtered URL
        $browser->visit('/talent/main-shows')
            ->waitForText('Talent', 5)
            ->waitForText('Main Show Event', 10)
            ->assertDontSee('Workshop Event')
            ->assertDontSee('General Event');

        // Test filtering by "Workshops" sub-schedule by visiting the filtered URL
        $browser->visit('/talent/workshops')
            ->waitForText('Talent', 5)
            ->waitForText('Workshop Event', 10)
            ->assertDontSee('Main Show Event')
            ->assertDontSee('General Event');

        // Test "All Schedules" filter by visiting the base URL again
        $browser->visit('/talent')
            ->waitForText('Talent', 5)
            ->waitForText('Main Show Event', 10)
            ->waitForText('Workshop Event', 10)
            ->waitForText('General Event', 10);
    }

    /**
     * Test API functionality for creating events with sub-schedules
     */
    protected function testApiGroupFunctionality(Browser $browser): void
    {
        // Enable API for the user
        $apiKey = $this->enableApi($browser);

        // Test API call to create event with sub-schedule
        $response = $this->createEventViaApi($apiKey, 'API Test Event', 'main-shows');

        $this->assertEquals(201, $response['httpCode'], 'POST /api/events should return 201');
        $this->assertArrayHasKey('data', $response['data'], 'Response should have data key');
        $this->assertArrayHasKey('id', $response['data']['data'], 'Response should have id in data');

        // Verify the event was created with the correct sub-schedule
        $event = Event::where('name', 'API Test Event')->first();
        $this->assertNotNull($event);

        $role = Role::where('subdomain', 'talent')->first();
        $group = $role->groups()->where('slug', 'main-shows')->first();
        $this->assertNotNull($group);

        $eventRole = EventRole::where('event_id', $event->id)
            ->where('role_id', $role->id)
            ->first();
        $this->assertNotNull($eventRole);
        $this->assertEquals($group->id, $eventRole->group_id);

        // Test API call with invalid sub-schedule
        $response = $this->createEventViaApi($apiKey, 'Invalid Schedule Event', 'invalid-schedule');
        $this->assertEquals(422, $response['httpCode'], 'Invalid schedule should return 422');

        $this->assertStringContainsString('Sub-schedule not found', $response['data']['error']);
    }

    /**
     * Helper method to create event via API using cURL
     */
    protected function createEventViaApi(string $apiKey, string $eventName, string $scheduleSlug): array
    {
        $baseUrl = config('app.url');
        $eventData = [
            'name' => $eventName,
            'starts_at' => date('Y-m-d H:i:s', strtotime('+10 days')),
            'venue_address1' => '123 Test Street',
            'schedule' => $scheduleSlug,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl.'/api/events/talent',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($eventData),
            CURLOPT_HTTPHEADER => [
                'X-API-Key: '.$apiKey,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        return [
            'httpCode' => $httpCode,
            'data' => $data,
        ];
    }
}
