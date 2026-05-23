<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class EventManagementTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_event_management(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // 1. Create an event
            // -----------------------------------------------
            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#event_name', 5)
                ->pause(1000);

            // Set event name via JS (Dusk type() is unreliable on this Vue-bound input in headless CI)
            $browser->script("
                var nameField = document.getElementById('event_name');
                nameField.value = 'Original Event';
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            // Navigate to venue section via JS (more reliable than clicking the nav link)
            $browser->script("document.querySelector('a[data-section=\"section-venue\"]').click()");
            $browser->waitFor('#in_person', 10);
            $browser->script("var cb = document.getElementById('in_person'); if (!cb.checked) cb.click();");
            $browser->waitFor('#selected_venue', 5)
                ->select('#selected_venue');

            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 15)
                ->waitForText('Original Event', 5);

            $event = Event::first();
            $hash = UrlUtils::encodeId($event->id);

            // -----------------------------------------------
            // 2. Edit the event - change name
            // -----------------------------------------------
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#event_name', 5);

            $browser->script("
                var nameField = document.getElementById('event_name');
                nameField.value = 'Updated Event';
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 15)
                ->waitForText('Updated Event', 5);

            // Verify DB
            $this->assertEquals('Updated Event', Event::first()->refresh()->name);

            // -----------------------------------------------
            // 3. Clone the event
            // -----------------------------------------------
            $eventCountBefore = Event::count();

            $browser->visit('/talent/clone-event/'.$hash)
                ->waitForLocation('/talent/add-event', 15)
                ->waitFor('#event_name', 5);

            // The cloned event form should be pre-filled with the original name
            $browser->assertInputValue('name', 'Updated Event');

            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 15);

            // Verify a new event was created
            $this->assertEquals($eventCountBefore + 1, Event::count());

            // -----------------------------------------------
            // 4. Delete one event
            // -----------------------------------------------
            $newestEvent = Event::latest('id')->first();
            $newestHash = UrlUtils::encodeId($newestEvent->id);

            $browser->visit('/talent/edit-event/'.$newestHash)
                ->waitFor('#event_name', 10)
                ->waitUntil("document.getElementById('event-delete-form') !== null", 10);

            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('event-delete-form').submit();
            ");

            $browser->waitForLocation('/talent/schedule', 45);

            // Verify event was deleted
            $this->assertNull(Event::find($newestEvent->id));

            // -----------------------------------------------
            // 5. Delete the schedule
            // -----------------------------------------------
            $talentRole = Role::where('subdomain', 'talent')->first();
            $this->assertNotNull($talentRole);

            // Submit the delete form via JS (avoids needing to open dropdown menu and confirm dialog)
            $browser->visit('/talent/schedule')
                ->waitForText('Talent', 5);

            $browser->script("
                var form = document.querySelector('form.form-confirm');
                if (form) form.submit();
            ");

            $browser->waitForLocation('/dashboard', 15);

            // Verify role was deleted
            $this->assertNull(Role::where('subdomain', 'talent')->first());
        });
    }

    /**
     * Create an event by typing a brand-new venue name (no #selected_venue dropdown).
     *
     * This exercises the venue-matching branch in EventRepo::saveEvent() which runs a
     * `Role::where('type', 'venue')->withCount('events')` query. A previous regression
     * added an `events.is_deleted` clause to that withCount, which does not exist as a
     * column on the events table and 500'd in production. The standard event-creation
     * test always picks a venue from the dropdown, so this path was uncovered.
     */
    public function test_event_creation_with_new_venue(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);

            $venueCountBefore = Role::where('type', 'venue')->count();
            $eventCountBefore = Event::count();

            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#event_name', 10)
                ->pause(1000);

            $browser->script("
                var nameField = document.getElementById('event_name');
                nameField.value = 'New Venue Event';
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            // Force the create-new venue path with a name that does NOT match any existing
            // venue, so saveEvent() walks the matching query and then inserts a new Role.
            $browser->script("
                window.vueApp.isInPerson = true;
                window.vueApp.selectedVenue = '';
                window.vueApp.venueType = 'create_new';
                window.vueApp.venueName = 'Brand New Venue';
                window.vueApp.venueCity = 'Newtown';
            ");

            $browser->pause(500);

            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 15)
                ->waitForText('New Venue Event', 10);

            $this->assertSame($eventCountBefore + 1, Event::count());
            $this->assertSame($venueCountBefore + 1, Role::where('type', 'venue')->count());
            $this->assertNotNull(
                Role::where('type', 'venue')->where('name', 'Brand New Venue')->first()
            );
        });
    }
}
