<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AccountSetupTrait;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\EventRole;

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
            $user1Name = 'Curator1';
            $user1Email = 'curator1@test.com';
            $user1Password = 'password123';
            
            $this->setupTestAccount($browser, $user1Name, $user1Email, $user1Password);
            $this->createTestCurator($browser, 'Curator1');            
            $this->logoutUser($browser, $user1Name);

            // Step 2: Create second user with curator role
            $user2Name = 'Curator2';
            $user2Email = 'curator2@test.com';
            $user2Password = 'password123';
            
            $this->setupTestAccount($browser, $user2Name, $user2Email, $user2Password);
            $this->createTestCurator($browser, 'Curator2');
            $this->logoutUser($browser, $user2Name);

            // Step 3: Create third user who follows both curator roles
            $user3Name = 'Event Creator';
            $user3Email = 'test@gmail.com';
            $user3Password = 'password';
            
            $this->setupTestAccount($browser, $user3Name, $user3Email, $user3Password);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);            

            // Follow first curator
            $browser->visit('/curator1')
                    ->waitForText('Submit Event', 5)
                    ->clickLink('Submit Event')
                    ->waitForLocation('/talent/add-event', 5);
            
            // Follow second curator
            $browser->visit('/curator2')
                    ->waitForText('Submit Event', 5)
                    ->clickLink('Submit Event')
                    ->waitForLocation('/talent/add-event', 5);

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
            $browser->visit('/curator1/requests')
                    ->waitForText('Accept', 5)
                    ->click('.test-accept-event')
                    ->waitForLocation('/curator1/schedule', 5)
                    ->assertSee('Talent');
            
            // Get the event from the database
            $event = \App\Models\Event::where('name', 'Talent')->latest()->first();
            $eventUrl = $event->getGuestUrl('curator1');
            
            // Assert that the number of records remains the same
            $this->assertEquals(EventRole::count(), 4, 
                'There should be 4 event_role records before editing the event');
            
            $browser->visit($eventUrl)
                    ->waitForText('Edit Event', 5)
                    ->clickLink('Edit Event')
                    ->waitForText('Edit Event', 5)
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->waitForLocation('/curator1/schedule', 5)
                    ->assertSee('Talent');
            
            // Assert that the number of records remains the same
            $this->assertEquals(EventRole::count(), 4, 
                'The number of event_role records should remain the same after editing the event');
        });
    }

    /**
     * Create an event that will be added to both curator roles
     */
    protected function createEventForBothCurators(Browser $browser): void
    {
        // Create an event and add it to both curators
        $browser->visit('/talent/add-event?date=' . date('Y-m-d'))
                ->type('duration', '2')                
                ->click('a[data-section="section-venue"]')
                ->pause(1000)
                ->select('#selected_venue')
                ->click('a[data-section="section-schedules"]')
                ->pause(1000)
                // Use curator names to find and check the checkboxes
                ->script("
                    var labels = document.querySelectorAll('label[for^=\"curator_\"]');
                    labels.forEach(function(label) {
                        if (label.textContent.includes('Curator1') || label.textContent.includes('Curator2')) {
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
                ->waitForLocation('/talent/schedule', 5)
                ->assertSee('Talent');
    }
} 