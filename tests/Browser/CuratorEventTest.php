<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\EventRole;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class CuratorEventTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * Test curator event scenario:
     * 1. First user creates a curator role
     * 2. Second user creates a curator role
     * 3. Third user follows both curator roles and creates an event added to both
     * 4. First user accepts the event, and is NOT given its form: a curator that lists an event
     *    does not run it
     * 5. Third user, whose schedule made the event, saves its form: it stays on both curators
     */
    public function test_curator_event_scenario(): void
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

            // Follow both curators so they appear as selectable schedules on the talent's
            // add-event page. The single-page submission flow no longer establishes the
            // follow via the "Submit Event" button, so follow as the Follow button does: a form
            // posted with the page's token (the address opened on its own follows nothing).
            foreach (['curator1', 'curator2'] as $curator) {
                $browser->visit('/'.$curator);
                $browser->script("window.esPostFollow('/".$curator."/follow');");
                $browser->waitForLocation('/following', 15);
            }

            // Create an event that will be added to both curator roles
            $this->createEventForBothCurators($browser);

            // Log out third user
            $this->logoutUser($browser, $user3Name);

            // Step 4: First user logs back in, accepts the event, and is not given its form
            $this->loginUser($browser, $user1Email, $user1Password);
            $browser->assertSee($user1Name);

            // Approve the event
            $browser->visit('/curator1/requests')
                ->waitFor('.test-accept-event', 10)
                ->script("document.querySelector('.test-accept-event').closest('form').requestSubmit()");

            $browser->waitForLocation('/curator1/schedule', 15)
                ->waitForText('Talent', 5);

            // Get the event from the database
            $event = \App\Models\Event::where('name', 'Talent')->latest()->first();
            $eventUrl = $event->getGuestUrl('curator1');

            // The venue, the talent and the two curators
            $this->assertEquals(EventRole::count(), 4,
                'There should be 4 event_role records before editing the event');

            // Listing an event is not running it (EventListingRightsTest): the curator's owner is
            // offered no way into the event's form, and the form's address does not give it either.
            $editPath = '/edit-event/'.UrlUtils::encodeId($event->id);

            $browser->visit($eventUrl)
                ->waitForText('Talent', 5)
                ->assertDontSee('Edit Event');

            $browser->visit('/curator1'.$editPath)
                ->waitUntil('! window.location.pathname.includes("edit-event")', 10)
                ->assertMissing('#edit-form');

            $this->assertEquals(EventRole::count(), 4,
                'Being turned away from the form changes nothing about where the event is listed');

            // Step 5: the event's own person saves the form they are given, and the event stays
            // on both curators. (Until 2026-10 it was the curator who saved here.)
            $browser->visit('/curator1/schedule');
            $this->logoutUser($browser, $user1Name);
            $this->loginUser($browser, $user3Email, $user3Password);

            $browser->visit('/talent'.$editPath)
                ->waitFor('#edit-form', 10);

            // Use JavaScript to submit form (avoids click-targeting issues in headless Chrome)
            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 5)
                ->pause(1000)
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
        $browser->visit('/talent/add-event?date='.date('Y-m-d'))
            ->waitFor('#event_name', 10)
            ->pause(500);

        // The venue is on the Event tab, which is the one the form opens on.
        $browser->waitFor('#in_person', 5);
        $browser->script("var cb = document.getElementById('in_person'); if (!cb.checked) cb.click();");
        $browser->waitFor('#selected_venue', 5)
            ->select('#selected_venue');

        // The other schedules to list on are part of the Listing tab
        $browser->script("document.querySelector('a[data-section=\"section-listing\"]').click()");
        $browser->pause(1000)
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

        // Use JavaScript to submit form (avoids click-targeting issues in headless Chrome)
        $browser->script("
            window._skipUnsavedWarning = true;
            document.getElementById('edit-form').requestSubmit();
        ");

        $browser->waitForLocation('/talent/schedule', 5)
            ->pause(1000)
            ->assertSee('Talent');
    }
}
