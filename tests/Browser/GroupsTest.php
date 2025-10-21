<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\Group;
use App\Models\EventRole;

class GroupsTest extends DuskTestCase
{
    use DatabaseTruncation;
    use AccountSetupTrait;

    /**
     * Test sub-schedules functionality:
     * 1. User can create sub-schedules in their role
     * 2. They can create events and assign them to a sub-schedule
     * 3. The filters in the guest view correctly show/hide the events
     * 4. The API can be used to create an event and assign it to the subschedule
     */
    public function testGroupsFunctionality(): void
    {
        $this->browse(function (Browser $browser) {
            // Step 1: Create a user and talent role
            $userName = 'Test User';
            $userEmail = 'test@gmail.com';
            $userPassword = 'password';
            
            $this->setupTestAccount($browser, $userName, $userEmail, $userPassword);
            $this->createTestTalent($browser);
            $this->createTestVenue($browser);

            $talentSlug = $this->getRoleSlug('talent', 'Talent');

            // Step 2: Create sub-schedules
            $this->createGroups($browser, $talentSlug);

            // Step 3: Create events and assign them to sub-schedules
            $this->createEventsWithGroups($browser, $talentSlug);

            // Step 4: Test filtering in guest view
            $this->testGuestViewFiltering($browser, $talentSlug);

            // Step 5: Test API functionality
            $this->testApiGroupFunctionality($browser, $talentSlug);
        });
    }

    /**
     * Create sub-schedules for the talent role
     */
    protected function createGroups(Browser $browser, string $talentSlug): void
    {
        $browser->visit('/' . $talentSlug . '/edit')
                ->waitForText('Subschedules', 5);

        $this->scrollIntoViewWhenPresent($browser, '#group-items')
                ->waitForText('Subschedules', 5);

        // Add first sub-schedule
        $browser->script("addGroupField();");
        
        $browser->waitFor('input[name*="groups"][name*="name"]', 5)
                ->type('input[name*="groups"][name*="name"]', 'Main Shows');

        $this->scrollIntoViewWhenPresent($browser, 'button[type="submit"]')
                ->press('Save');

        $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 5);

        // Add second sub-schedule
        $browser->visit('/' . $talentSlug . '/edit')
                ->waitForText('Subschedules', 5);

        $this->scrollIntoViewWhenPresent($browser, '#group-items')
                ->waitForText('Subschedules', 5);

        $browser->script("addGroupField();");

        $browser->waitFor('#group-items > div:last-child input[name*="groups"][name*="name"]', 5)
                ->type('#group-items > div:last-child input[name*="groups"][name*="name"]', 'Workshops');

        $this->scrollIntoViewWhenPresent($browser, 'button[type="submit"]')
                ->press('Save');

        $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 5);

        // Verify both sub-schedules were saved in database
        $role = Role::where('subdomain', $talentSlug)->first();
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
    protected function createEventsWithGroups(Browser $browser, string $talentSlug): void
    {
        // Get the actual group IDs from the database
        $role = Role::where('subdomain', $talentSlug)->first();
        $mainShows = $role->groups()->where('name', 'Main Shows')->first();
        $workshops = $role->groups()->where('name', 'Workshops')->first();

        // Create first event for "Main Shows" sub-schedule
        $this->visitRoleAddEventPage($browser, $talentSlug, date('Y-m-d'), 'talent', 'Talent');
        $this->selectExistingVenue($browser);

        $browser->type('name', 'Main Show Event')
                ->type('duration', '2');

        $this->scrollIntoViewWhenPresent($browser, 'select[name="current_role_group_id"]')
                ->select('current_role_group_id', \App\Utils\UrlUtils::encodeId($mainShows->id));

        $this->scrollIntoViewWhenPresent($browser, 'button[type="submit"]')
                ->press('Save');

        $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 5);

        $browser->assertSee('Main Show Event');

        // Create second event for "Workshops" sub-schedule
        $this->visitRoleAddEventPage($browser, $talentSlug, date('Y-m-d'), 'talent', 'Talent');
        $this->selectExistingVenue($browser);

        $browser->type('name', 'Workshop Event')
                ->type('duration', '3');

        $this->scrollIntoViewWhenPresent($browser, 'select[name="current_role_group_id"]')
                ->select('current_role_group_id', \App\Utils\UrlUtils::encodeId($workshops->id));

        $this->scrollIntoViewWhenPresent($browser, 'button[type="submit"]')
                ->press('Save');

        $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 5);

        $browser->assertSee('Workshop Event');

        // Create third event without sub-schedule
        $this->visitRoleAddEventPage($browser, $talentSlug, date('Y-m-d'), 'talent', 'Talent');
        $this->selectExistingVenue($browser);

        $browser->type('name', 'General Event')
                ->type('duration', '1');

        $this->scrollIntoViewWhenPresent($browser, 'button[type="submit"]')
                ->press('Save');

        $this->waitForPath($browser, '/' . $talentSlug . '/schedule', 5);

        $browser->assertSee('General Event');
    }

    /**
     * Test filtering functionality in guest view
     */
    protected function testGuestViewFiltering(Browser $browser, string $talentSlug): void
    {
        // Visit guest view - should show all events initially
        $browser->visit('/' . $talentSlug)
                ->waitForText('Talent', 5);

        // Check that all events are visible initially
        $browser->assertSee('Main Show Event')
                ->assertSee('Workshop Event')
                ->assertSee('General Event');

        // Test filtering by "Main Shows" sub-schedule by visiting the filtered URL
        $browser->visit('/' . $talentSlug . '/main-shows')
                ->waitForText('Talent', 5)
                ->assertSee('Main Show Event')
                ->assertDontSee('Workshop Event')
                ->assertDontSee('General Event');

        // Test filtering by "Workshops" sub-schedule by visiting the filtered URL
        $browser->visit('/' . $talentSlug . '/workshops')
                ->waitForText('Talent', 5)
                ->assertSee('Workshop Event')
                ->assertDontSee('Main Show Event')
                ->assertDontSee('General Event');

        // Test "All Schedules" filter by visiting the base URL again
        $browser->visit('/' . $talentSlug)
                ->waitForText('Talent', 5)
                ->assertSee('Main Show Event')
                ->assertSee('Workshop Event')
                ->assertSee('General Event');
    }

    /**
     * Test API functionality for creating events with sub-schedules
     */
    protected function testApiGroupFunctionality(Browser $browser, string $talentSlug): void
    {
        // Enable API for the user
        $apiKey = $this->enableApi($browser);

        // Test API call to create event with sub-schedule
        $response = $this->createEventViaApi($apiKey, 'API Test Event', 'main-shows', $talentSlug);

        $this->assertEquals(201, $response['httpCode'], 'POST /api/events should return 201');
        $this->assertArrayHasKey('data', $response['data'], 'Response should have data key');
        $this->assertArrayHasKey('id', $response['data']['data'], 'Response should have id in data');

        // Verify the event was created with the correct sub-schedule
        $event = Event::where('name', 'API Test Event')->first();
        $this->assertNotNull($event);

        $role = Role::where('subdomain', $talentSlug)->first();
        $group = $role->groups()->where('slug', 'main-shows')->first();
        $this->assertNotNull($group);

        $eventRole = EventRole::where('event_id', $event->id)
                             ->where('role_id', $role->id)
                             ->first();
        $this->assertNotNull($eventRole);
        $this->assertEquals($group->id, $eventRole->group_id);

        // Test API call with invalid sub-schedule
        $response = $this->createEventViaApi($apiKey, 'Invalid Schedule Event', 'invalid-schedule', $talentSlug);
        $this->assertEquals(422, $response['httpCode'], 'Invalid schedule should return 422');

        $this->assertStringContainsString('Schedule not found', $response['data']['error']);
    }

    /**
     * Helper method to create event via API using cURL
     */
    protected function createEventViaApi(string $apiKey, string $eventName, string $scheduleSlug, string $talentSlug): array
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
            CURLOPT_URL => $baseUrl . '/api/events/' . $talentSlug,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($eventData),
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);
        
        return [
            'httpCode' => $httpCode,
            'data' => $data
        ];
    }
} 