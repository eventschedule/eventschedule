<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;

class CuratorEventTest extends DuskTestCase
{
    use DatabaseTruncation;
    use AccountSetupTrait;

    /**
     * Test curator event scenario:
     * 1. First user creates a curator role
     * 2. Second user creates a curator role  
     * 3. Third user follows both curator roles and creates an event added to both
     * 4. First user edits the event (should fail - event no longer linked to both curators)
     */
    public function testCuratorEventScenario(): void
    {
        $this->browse(function (Browser $browser) {
            // Step 1: Create first user with curator role
            $user1Name = 'Curator 1';
            $user1Email = 'curator1@test.com';
            $user1Password = 'password123';
            
            $this->setupTestAccount($browser, $user1Name, $user1Email, $user1Password);
            $this->createTestCurator($browser, 'First Curator');            
            $this->logoutUser($browser, $user1Name);

            // Step 2: Create second user with curator role
            $user2Name = 'Curator 2';
            $user2Email = 'curator2@test.com';
            $user2Password = 'password123';
            
            $this->setupTestAccount($browser, $user2Name, $user2Email, $user2Password);
            $this->createTestCurator($browser, 'Second Curator');
            $this->logoutUser($browser, $user2Name);

            // Step 3: Create third user who follows both curator roles
            $user3Name = 'Event Creator';
            $user3Email = 'creator@test.com';
            $user3Password = 'password123';
            
            $this->setupTestAccount($browser, $user3Name, $user3Email, $user3Password);
            
            // Follow first curator
            $browser->visit('/first-curator')
                    ->waitForText('Follow', 5)
                    ->clickLink('Follow')
                    ->waitForLocation('/following', 5)
                    ->assertSee('First Curator');
            
            // Follow second curator
            $browser->visit('/second-curator')
                    ->waitForText('Follow', 5)
                    ->clickLink('Follow')
                    ->waitForLocation('/following', 5)
                    ->assertSee('Second Curator');
            
            // Create an event that will be added to both curator roles
            $this->createEventForBothCurators($browser);
            
            // Log out third user
            $this->logoutUser($browser, $user3Name);

            // Step 4: First user logs back in and tries to edit the event
            $browser->visit('/login')
                    ->type('email', $user1Email)
                    ->type('password', $user1Password)
                    ->press('LOG IN')
                    ->waitForLocation('/events', 5)
                    ->assertPathIs('/events')
                    ->assertSee($user1Name);
            
            // Navigate to first curator's schedule
            $browser->visit('/first-curator/schedule')
                    ->waitForText('Test Event', 5)
                    ->assertSee('Test Event');
            
            // Try to edit the event - this should fail because the event
            // will no longer be linked to both curator roles after editing
            $browser->clickLink('Test Event')
                    ->waitForLocation('/first-curator/edit_event', 5)
                    ->type('name', 'Updated Test Event')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/first-curator/schedule', 5);
            
            // Verify the event was updated
            $browser->assertSee('Updated Test Event');
            
            // Now check if the event is still linked to the second curator
            // This is where we expect the test to fail
            $browser->visit('/second-curator/schedule')
                    ->waitForText('Updated Test Event', 5)
                    ->assertSee('Updated Test Event');
        });
    }

    /**
     * Create an event that will be added to both curator roles
     */
    protected function createEventForBothCurators(Browser $browser): void
    {
        // Create a talent role first (needed to create events)
        $browser->visit('/new/talent')
                ->waitForText('New Talent', 5)
                ->type('name', 'Test Talent')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertPathIs('/test-talent/schedule');
        
        // Create an event and add it to both curators
        $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                ->type('name', 'Test Event')
                ->type('duration', '2')
                
                // Add to first curator
                ->scrollIntoView('input[name="curators[]"]')
                ->check('input[name="curators[]"][value*="first-curator"]')
                
                // Add to second curator  
                ->check('input[name="curators[]"][value*="second-curator"]')
                
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertSee('Test Event');
    }
} 