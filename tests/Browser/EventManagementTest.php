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
                ->type('name', 'Original Event')
                ->click('a[data-section="section-venue"]')
                ->waitFor('#selected_venue', 5)
                ->select('#selected_venue')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15)
                ->waitForText('Original Event', 5);

            $event = Event::first();
            $hash = UrlUtils::encodeId($event->id);

            // -----------------------------------------------
            // 2. Edit the event - change name
            // -----------------------------------------------
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#event_name', 5)
                ->clear('name')
                ->type('name', 'Updated Event')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15)
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
            $browser->assertInputValue('name', 'Updated Event')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Verify a new event was created
            $this->assertEquals($eventCountBefore + 1, Event::count());

            // -----------------------------------------------
            // 4. Delete one event
            // -----------------------------------------------
            $newestEvent = Event::latest('id')->first();
            $newestHash = UrlUtils::encodeId($newestEvent->id);

            $browser->visit('/talent/edit-event/'.$newestHash)
                ->waitFor('#event-actions-menu-button', 5)
                ->click('#event-actions-menu-button')
                ->waitFor('#event-delete-form', 5);
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#event-delete-form button[type="submit"]')
                ->waitForLocation('/talent/schedule', 15);

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

            $browser->waitForLocation('/events', 15);

            // Verify role was deleted
            $this->assertNull(Role::where('subdomain', 'talent')->first());
        });
    }
}
