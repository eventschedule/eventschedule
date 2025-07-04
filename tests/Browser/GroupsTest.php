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
            $this->createTestTalent($browser, 'Test Talent');
            $this->createTestVenue($browser, 'Test Venue');
            
            // Step 2: Create sub-schedules
            $this->createGroups($browser);
            
            // Step 3: Create events and assign them to sub-schedules
            $this->createEventsWithGroups($browser);
            
            // Step 4: Test filtering in guest view
            $this->testGuestViewFiltering($browser);
            
            // Step 5: Test API functionality
            //$this->testApiGroupFunctionality($browser);
        });
    }

    /**
     * Create sub-schedules for the talent role
     */
    protected function createGroups(Browser $browser): void
    {
        $browser->visit('/test-talent/edit')
                ->waitForText('Subschedules', 5)
                ->scrollIntoView('#address')
                ->waitForText('Subschedules', 5);
        
        // Add first sub-schedule
        $browser->script("addGroupField();");
        
        $browser->waitFor('input[name*="groups"][name*="name"]', 5)
                ->type('input[name*="groups"][name*="name"]', 'Main Shows')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5);
        
        // Add second sub-schedule
        $browser->visit('/test-talent/edit')
                ->waitForText('Subschedules', 5)
                ->scrollIntoView('#address')
                ->waitForText('Subschedules', 5)
                ->scrollIntoView('input[name*="groups"][name*="name"]:last-of-type');
        
        $browser->script("addGroupField();");
        
        $browser->waitFor('#group-items > div:last-child input[name*="groups"][name*="name"]', 5)
                ->type('#group-items > div:last-child input[name*="groups"][name*="name"]', 'Workshops')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5);

        // Verify both sub-schedules were saved in database
        $role = Role::where('subdomain', 'test-talent')->first();
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
        // Create first event for "Main Shows" sub-schedule
        $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                ->select('#selected_venue')
                ->type('name', 'Main Show Event')
                ->type('duration', '2')
                ->scrollIntoView('select[name="current_role_group_id"]')
                ->select('current_role_group_id', '1')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertSee('Main Show Event');
        
        // Create second event for "Workshops" sub-schedule
        $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+5 days')))
                ->select('#selected_venue')
                ->type('name', 'Workshop Event')
                ->type('duration', '3')
                ->scrollIntoView('select[name="current_role_group_id"]')
                ->select('current_role_group_id', '2')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertSee('Workshop Event');
        
        // Create third event without sub-schedule
        $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+7 days')))
                ->select('#selected_venue')
                ->type('name', 'General Event')
                ->type('duration', '1')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertSee('General Event');
    }

    /**
     * Test filtering functionality in guest view
     */
    protected function testGuestViewFiltering(Browser $browser): void
    {
        // Visit guest view
        $browser->visit('/test-talent')
                ->waitForText('Test Talent', 5);
        
        // Check that all events are visible initially
        $browser->assertSee('Main Show Event')
                ->assertSee('Workshop Event')
                ->assertSee('General Event');
        
        // Test filtering by "Main Shows" sub-schedule
        $browser->script("
            var select = document.querySelector('select[v-model=\"selectedGroup\"]');
            if (select) {
                select.value = 'main-shows';
                select.dispatchEvent(new Event('change'));
            }
        ");
        
        $browser->waitForText('Main Show Event', 5)
                ->assertSee('Main Show Event')
                ->assertDontSee('Workshop Event')
                ->assertDontSee('General Event');
        
        // Test filtering by "Workshops" sub-schedule
        $browser->script("
            var select = document.querySelector('select[v-model=\"selectedGroup\"]');
            if (select) {
                select.value = 'workshops';
                select.dispatchEvent(new Event('change'));
            }
        ");
        
        $browser->waitForText('Workshop Event', 5)
                ->assertSee('Workshop Event')
                ->assertDontSee('Main Show Event')
                ->assertDontSee('General Event');
        
        // Test "All Schedules" filter
        $browser->script("
            var select = document.querySelector('select[v-model=\"selectedGroup\"]');
            if (select) {
                select.value = '';
                select.dispatchEvent(new Event('change'));
            }
        ");
        
        $browser->waitForText('Main Show Event', 5)
                ->assertSee('Main Show Event')
                ->assertSee('Workshop Event')
                ->assertSee('General Event');
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
        
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        
        // Verify the event was created with the correct sub-schedule
        $event = Event::where('name', 'API Test Event')->first();
        $this->assertNotNull($event);
        
        $role = Role::where('subdomain', 'test-talent')->first();
        $group = $role->groups()->where('slug', 'main-shows')->first();
        $this->assertNotNull($group);
        
        $eventRole = EventRole::where('event_id', $event->id)
                             ->where('role_id', $role->id)
                             ->first();
        $this->assertNotNull($eventRole);
        $this->assertEquals($group->id, $eventRole->group_id);
        
        // Test API call with invalid sub-schedule
        $response = $this->createEventViaApi($apiKey, 'Invalid Schedule Event', 'invalid-schedule');
        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Schedule not found', $responseData['error']);
    }

    /**
     * Helper method to create event via API
     */
    protected function createEventViaApi(string $apiKey, string $eventName, string $scheduleSlug)
    {
        $response = $this->post('/api/events/test-talent', [
            'name' => $eventName,
            'starts_at' => date('Y-m-d H:i:s', strtotime('+10 days')),
            'venue_address1' => '123 Test Street',
            'venue_city' => 'Test City',
            'venue_state' => 'TS',
            'venue_postal_code' => '12345',
            'venue_country_code' => 'US',
            'schedule' => $scheduleSlug,
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);
        
        return $response;
    }

    /**
     * Test that sub-schedules are properly saved and retrieved
     */
    /*
    public function testGroupPersistence(): void
    {
        $this->browse(function (Browser $browser) {
            // Create user and role
            $this->setupTestAccount($browser, 'Persistence Test User', 'persistence@test.com', 'password123');
            $this->createTestTalent($browser, 'Persistence Talent');
            
            // Create sub-schedule
            $browser->visit('/persistence-talent/edit')
                    ->waitForText('Subschedules', 5)
                    ->scrollIntoView('#address')
                    ->waitForText('Subschedules', 5);
            
            $browser->script("addGroupField();");
            
            $browser->waitFor('input[name*="groups"][name*="name"]', 5)
                    ->type('input[name*="groups"][name*="name"]', 'Test Schedule')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForText('Settings updated successfully', 5);
            
            // Verify sub-schedule was saved in database
            $role = Role::where('subdomain', 'persistence-talent')->first();
            $this->assertNotNull($role);
            
            $group = $role->groups()->where('name', 'Test Schedule')->first();
            $this->assertNotNull($group);
            $this->assertEquals('test-schedule', $group->slug);
            
            // Logout and login again to test persistence
            $this->logoutUser($browser, 'Persistence Test User');
            
            $browser->visit('/login')
                    ->type('email', 'persistence@test.com')
                    ->type('password', 'password123')
                    ->press('LOG IN')
                    ->waitForLocation('/events', 5);
            
            // Verify sub-schedule is still there
            $browser->visit('/persistence-talent/edit')
                    ->waitForText('Subschedules', 5)
                    ->scrollIntoView('#address')
                    ->waitForText('Test Schedule', 5)
                    ->assertSee('Test Schedule');
        });
    }
    */

    /**
     * Test that events can be moved between sub-schedules
     */
    /*
    public function testMoveEventBetweenGroups(): void
    {
        $this->browse(function (Browser $browser) {
            // Create user and role with sub-schedules
            $this->setupTestAccount($browser, 'Move Test User', 'move@test.com', 'password123');
            $this->createTestTalent($browser, 'Move Talent');
            
            // Create two sub-schedules
            $browser->visit('/move-talent/edit')
                    ->waitForText('Subschedules', 5)
                    ->scrollIntoView('#address')
                    ->waitForText('Subschedules', 5);
            
            // Add first sub-schedule
            $browser->script("addGroupField();");
            
            $browser->waitFor('input[name*="groups"][name*="name"]', 5)
                    ->type('input[name*="groups"][name*="name"]', 'Schedule A')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForText('Settings updated successfully', 5);
            
            // Add second sub-schedule
            $browser->visit('/move-talent/edit')
                    ->waitForText('Subschedules', 5)
                    ->scrollIntoView('#address')
                    ->waitForText('Subschedules', 5);
            
            $browser->script("addGroupField();");
            
            $browser->waitFor('input[name*="groups"][name*="name"]:last-of-type', 5)
                    ->type('input[name*="groups"][name*="name"]:last-of-type', 'Schedule B')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForText('Settings updated successfully', 5);
            
            // Create event in Schedule A
            $browser->visit('/move-talent/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                    ->type('name', 'Movable Event')
                    ->type('duration', '2')
                    ->scrollIntoView('select[name="current_role_group_id"]')
                    ->select('current_role_group_id', '1')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/move-talent/schedule', 5)
                    ->assertSee('Movable Event');
            
            // Edit event and move it to Schedule B
            $event = Event::where('name', 'Movable Event')->first();
            $eventUrl = $event->getGuestUrl('move-talent');
            
            $browser->visit($eventUrl)
                    ->waitForText('Edit Event', 5)
                    ->clickLink('Edit Event')
                    ->waitForText('Edit Event', 5)
                    ->scrollIntoView('select[name="current_role_group_id"]')
                    ->select('current_role_group_id', '2')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/move-talent/schedule', 5)
                    ->assertSee('Movable Event');
            
            // Verify the event was moved in the database
            $role = Role::where('subdomain', 'move-talent')->first();
            $scheduleB = $role->groups()->where('name', 'Schedule B')->first();
            
            $eventRole = EventRole::where('event_id', $event->id)
                                 ->where('role_id', $role->id)
                                 ->first();
            $this->assertEquals($scheduleB->id, $eventRole->group_id);
        });
    }
        */
} 