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
            $user1Name = 'Curator One';
            $user1Email = 'curator1@test.com';
            $user1Password = 'password123';
            
            $this->setupTestAccount($browser, $user1Name, $user1Email, $user1Password);
            $this->createTestCurator($browser, 'First Curator');            
            $this->logoutUser($browser, $user1Name);

            // Step 2: Create second user with curator role
            $user2Name = 'Curator Two';
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
                    ->waitForText('Add Event', 5)
                    ->clickLink('Add Event')
                    ->waitForLocation('/following', 5)
                    ->assertSee('First Curator');
            
            // Follow second curator
            $browser->visit('/second-curator')
                    ->waitForText('Add Event', 5)
                    ->clickLink('Add Event')
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
            
            // Approve the event
            $browser->visit('/first-curator/requests')
                    ->waitForText('Accept', 5)
                    ->click('.test-accept-event')
                    ->waitForLocation('/first-curator/schedule', 5)
                    ->assertSee('Test Talent');
            

            // Get the event from the database
            $event = \App\Models\Event::where('name', 'Test Talent')->latest()->first();
            $browser->visit($event->getGuestUrl())
                    ->waitForText('Edit Event', 5)
                    ->clickLink('Edit Event')
                    ->waitForText('Edit Event', 5)
                    ->scrollIntoView('input[name="curators[]"]')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/first-curator/schedule', 5)
                    ->assertSee('Test Talent')
                    ->screenshot('edit-event');
        });
    }

    /**
     * Create an event that will be added to both curator roles
     */
    protected function createEventForBothCurators(Browser $browser): void
    {
        $this->createTestTalent($browser);
        $this->createTestVenue($browser);
        
        // Create an event and add it to both curators
        $browser->visit('/test-talent/add_event?date=' . date('Y-m-d', strtotime('+3 days')))
                ->select('#selected_venue')
                ->type('duration', '2')
                
                ->scrollIntoView('input[name="curators[]"]')
                // Use curator names to find and check the checkboxes
                ->script("
                    var labels = document.querySelectorAll('label[for^=\"curator_\"]');
                    labels.forEach(function(label) {
                        if (label.textContent.includes('First Curator') || label.textContent.includes('Second Curator')) {
                            var checkboxId = label.getAttribute('for');
                            var checkbox = document.getElementById(checkboxId);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        }
                    });
                ");
                
        $browser->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/test-talent/schedule', 5)
                ->assertSee('Test Talent');
    }
} 