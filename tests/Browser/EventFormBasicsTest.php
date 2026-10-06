<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Controls on the event form that stopped answering after an ordinary change of mind.
 *
 * Each was bound once, by element id, to a node Vue later threw away: the form is one Vue mount,
 * and anything under a v-if is a new element every time its condition flips back. The listener
 * stays on the old node, and the control on screen looks alive and does nothing. None of this is
 * reachable from PHPUnit, which never runs the page.
 */
class EventFormBasicsTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->makeRole('talent', 'basicstalent');
        // A saved venue, so the form opens on "Use existing" with the address fields not rendered.
        $this->makeRole('venue', 'basicsvenue', ['address1' => '1 Old Road', 'city' => 'Oldtown']);
    }

    private function makeRole(string $type, string $subdomain, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $this->owner->id;
        $role->type = $type;
        $role->name = ucfirst($subdomain);
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }
        $role->save();
        $role->users()->attach($this->owner->id, ['level' => 'owner']);

        return $role->fresh();
    }

    private function openForm(Browser $browser): void
    {
        $browser->loginAs($this->owner)
            ->visit('/basicstalent/add-event')
            ->waitFor('#event_name', 15)
            ->waitUntil('window.vueApp !== undefined', 15);
    }

    /** Switch the venue to "a new one" and wait for its address fields to exist. */
    private function chooseNewVenue(Browser $browser): void
    {
        $browser->script('
            window.vueApp.isInPerson = true;
            window.vueApp.venueType = "create_new";
        ');
        $browser->waitUntil('document.getElementById("venue_address1") !== null && document.getElementById("view_map_button") !== null', 10);
    }

    /**
     * Validate and Accept sit beside it and are wired the same way, but render only with a Google
     * key, which this run does not have (EventFormBasicsMarkupTest holds their wiring).
     */
    public function test_the_map_button_works_on_a_venue_typed_after_the_page_loaded(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openForm($browser);

            $this->assertSame('use_existing', $browser->script('return window.vueApp.venueType;')[0], 'sanity check: the address fields are not rendered at load');

            $this->chooseNewVenue($browser);

            $opened = $browser->script('
                var opened = [];
                window.open = function (url) { opened.push(url); };

                var set = function (id, value) {
                    var el = document.getElementById(id);
                    el.value = value;
                    el.dispatchEvent(new Event("input", { bubbles: true }));
                };
                set("venue_address1", "131 W 3rd St");
                set("venue_city", "New York");

                document.getElementById("view_map_button").click();

                return opened;
            ')[0];

            $this->assertCount(1, $opened, 'View map opened the map');
            $this->assertStringContainsString('131%20W%203rd%20St', $opened[0]);

            // And again after the fields have been taken away and put back.
            $browser->script('window.vueApp.venueType = "use_existing";');
            $browser->pause(200);
            $this->chooseNewVenue($browser);

            $again = $browser->script('
                var opened = [];
                window.open = function (url) { opened.push(url); };
                var el = document.getElementById("venue_address1");
                el.value = "9 New Street";
                el.dispatchEvent(new Event("input", { bubbles: true }));
                document.getElementById("view_map_button").click();
                return opened;
            ')[0];

            $this->assertCount(1, $again, 'and still does on the re-rendered fields');
        });
    }

    public function test_an_accepted_address_is_the_one_that_is_kept(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openForm($browser);
            $this->chooseNewVenue($browser);

            $browser->script('
                var set = function (id, value) {
                    var el = document.getElementById(id);
                    el.value = value;
                    el.dispatchEvent(new Event("input", { bubbles: true }));
                };
                set("venue_address1", "131 west third");
                set("venue_city", "new york");

                // What a successful Validate leaves behind, without asking Google.
                window.jQuery("#address_response").data("validated_address", {
                    address1: "131 W 3rd St", city: "New York", state: "NY", postal_code: "10012",
                });
                window.acceptAddress({ preventDefault: function () {} });
            ');

            // Any later change re-renders the form from Vue\'s own values.
            $browser->script('window.vueApp.eventName = "Anything";');
            $browser->pause(300);

            $state = $browser->script('return {
                shown: document.getElementById("venue_address1").value,
                city: document.getElementById("venue_city").value,
                kept: window.vueApp.venueAddress1,
                posted: document.querySelector("input[type=hidden][name=venue_address1]").value,
                state: window.vueApp.venueState,
                postal: window.vueApp.venuePostalCode,
            };')[0];

            $this->assertSame('131 W 3rd St', $state['shown'], 'the field still shows the accepted address');
            $this->assertSame('New York', $state['city']);
            $this->assertSame('131 W 3rd St', $state['kept'], 'and it is the value the form holds');
            $this->assertSame('131 W 3rd St', $state['posted'], 'and the value that is posted');
            $this->assertSame('NY', $state['state']);
            $this->assertSame('10012', $state['postal']);
        });
    }

    public function test_multi_day_still_works_after_trying_recurring(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openForm($browser);

            $click = 'var el = document.getElementById(%s); el.checked = true; el.dispatchEvent(new Event("change", { bubbles: true })); el.click();';

            // Recurring removes the multi-day toggle; back to one-time puts a new one on the page.
            $browser->script(sprintf($click, '"recurring"'));
            $browser->waitUntil('window.vueApp.isRecurring === true', 10);
            $browser->script(sprintf($click, '"one_time"'));
            $browser->waitUntil('window.vueApp.isRecurring === false && document.getElementById("is_multi_day") !== null', 10);

            $browser->script('
                var toggle = document.getElementById("is_multi_day");
                toggle.checked = true;
                toggle.dispatchEvent(new Event("change", { bubbles: true }));
            ');
            $browser->pause(300);

            $state = $browser->script('return {
                multiDay: window.vueApp.isMultiDay,
                rowShown: document.getElementById("multi_day_end_date_row").style.display !== "none",
                picker: !! document.getElementById("event_end_date")._flatpickr,
            };')[0];

            $this->assertTrue($state['multiDay'], 'the form knows the event spans days');
            $this->assertTrue($state['rowShown'], 'and shows the end date');
            $this->assertTrue($state['picker'], 'with its date picker');
        });
    }

    public function test_what_was_typed_survives_a_save_the_server_refuses(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openForm($browser);

            // Valid as far as the browser can tell; the server refuses a link this long.
            $browser->script('
                var set = function (el, value) { el.value = value; el.dispatchEvent(new Event("input", { bubbles: true })); };
                set(document.getElementById("event_name"), "Typed before the refusal");
                document.getElementById("event_date")._flatpickr.setDate("'.now()->addDays(5)->format('Y-m-d').'", true);
                set(document.getElementById("start_time"), "8:00 PM");
                document.getElementById("start_time").dispatchEvent(new Event("change", { bubbles: true }));
                document.getElementById("start_time").dispatchEvent(new Event("blur", { bubbles: true }));
                window.vueApp.isOnline = true;
                window.vueApp.event.event_url = "https://example.org/" + "a".repeat(600);
            ');
            $browser->pause(300);
            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');

            $browser->waitUntil('document.body.innerText.indexOf("500 characters") !== -1 || document.querySelector(".text-red-600, .text-red-400") !== null', 20)
                ->waitUntil('window.vueApp !== undefined', 15);

            $this->assertSame('Typed before the refusal', $browser->script('return document.getElementById("event_name").value;')[0]);

            // And the page that came back finished setting itself up: the tab the save was refused
            // on is marked, and is the one showing. The script that marks it used to throw on its
            // first line ("highlightSectionError is not a function") on exactly this page.
            $state = $browser->script('return {
                marked: document.querySelectorAll(".section-nav-link.validation-error").length,
                showing: document.getElementById("section-details").style.display,
            };')[0];
            $this->assertGreaterThan(0, $state['marked']);
            $this->assertSame('block', $state['showing']);
        });
    }

    public function test_the_helper_forms_of_an_existing_event_are_outside_the_event_form(): void
    {
        $role = Role::where('subdomain', 'basicstalent')->firstOrFail();
        $event = new Event;
        $event->user_id = $this->owner->id;
        $event->creator_role_id = $role->id;
        $event->name = 'Existing Event';
        $event->slug = 'existing-event';
        $event->starts_at = Carbon::now()->addDays(10)->setTime(18, 0)->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $this->browse(function (Browser $browser) use ($event) {
            $browser->loginAs($this->owner)
                ->visit('/basicstalent/edit-event/'.UrlUtils::encodeId($event->id))
                ->waitFor('#event_name', 15)
                ->waitUntil('window.vueApp !== undefined', 15);

            // A wrapper opened at the top of the form used to stay open past its end, which put
            // the cancel, restore and notify-preview forms INSIDE the event form.
            $state = $browser->script('return {
                helperForms: ["notify-preview-form", "event-cancel-form", "event-restore-form"].filter(function (id) { return document.getElementById(id) !== null; }).length,
                insideTheEventForm: document.querySelectorAll("#edit-form form").length,
                saveButtonInTheForm: document.querySelector("#edit-form button[type=submit]") !== null,
            };')[0];

            $this->assertSame(3, $state['helperForms'], 'sanity check: the helper forms are on the page');
            $this->assertSame(0, $state['insideTheEventForm']);
            $this->assertTrue($state['saveButtonInTheForm']);
        });
    }
}
