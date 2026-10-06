<?php

namespace Tests\Browser;

use App\Models\CarpoolOffer;
use App\Models\Event;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Three things the event form did wrong that only a real browser shows, because each one is made
 * by the page that is actually posted rather than by a request a test writes by hand.
 *
 * PHPUnit covers the same ground from the server's side (EventTicketSetupProtectionTest,
 * EventFormStructureTest). This is the other half: the form a curator is really given, saved; the
 * form an owner is really given, with a carpool offer in it, saved; and the links people really
 * click in their email.
 *
 * Fixtures are written straight to the database. The journeys start at the edit form, and building
 * two accounts, three schedules and a ticketed event through the UI would only add flake.
 */
class EventFormGuardsTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function makeRole(User $user, string $type, string $subdomain, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
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
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    private function makeEvent(Role $venue): Event
    {
        $event = new Event;
        $event->user_id = $venue->user_id;
        $event->creator_role_id = $venue->id;
        $event->name = 'Jazz Night';
        $event->slug = 'jazz-night-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::now()->addDays(10)->setTime(18, 0)->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->tickets_enabled = true;
        $event->ticket_notes = 'Doors at seven';
        $event->save();
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function editPath(Role $role, Event $event): string
    {
        return '/'.$role->subdomain.'/edit-event/'.UrlUtils::encodeId($event->id);
    }

    /** Rename the event in the form as it stands and submit it, the way the Save button does. */
    private function renameAndSave(Browser $browser, string $name): void
    {
        $browser->waitFor('#event_name', 15)
            ->waitUntil('window.vueApp !== undefined', 15);

        $browser->script('
            var field = document.getElementById("event_name");
            field.value = '.json_encode($name).';
            field.dispatchEvent(new Event("input", { bubbles: true }));
            window._skipUnsavedWarning = true;
            document.getElementById("edit-form").requestSubmit();
        ');

        $browser->waitUntil('! window.location.pathname.includes("edit-event")', 20);
    }

    public function test_a_curator_saving_the_form_it_is_given_keeps_the_tickets(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $venue = $this->makeRole($owner, 'venue', 'guardvenue');
        $event = $this->makeEvent($venue);

        $ticket = new Ticket;
        $ticket->event_id = $event->id;
        $ticket->type = 'General';
        $ticket->quantity = 50;
        $ticket->price = 25;
        $ticket->save();
        PromoCode::create(['event_id' => $event->id, 'code' => 'EARLYBIRD', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $curatorUser = User::factory()->create(['email_verified_at' => now()]);
        $curator = $this->makeRole($curatorUser, 'curator', 'guardcurator');
        // The curator lists the event but did not create it.
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->browse(function (Browser $browser) use ($curatorUser, $curator, $event) {
            $browser->loginAs($curatorUser)->visit($this->editPath($curator, $event));
            $browser->waitFor('#event_name', 15)->waitUntil('window.vueApp !== undefined', 15);

            $page = $browser->script('return {
                panel: document.getElementById("section-tickets") !== null,
                tickets: window.vueApp.tickets.length,
                promoCodes: window.vueApp.promoCodes.length,
                source: document.documentElement.innerHTML.indexOf("EARLYBIRD") !== -1,
            };')[0];

            $this->assertFalse($page['panel'], 'the curator is not offered the Tickets panel');
            $this->assertSame(0, $page['tickets'], 'and is not sent the ticket types');
            $this->assertSame(0, $page['promoCodes']);
            $this->assertFalse($page['source'], 'nor the promo code, anywhere in the page');

            $this->renameAndSave($browser, 'Renamed by the curator');
        });

        $saved = Event::findOrFail($event->id);
        $this->assertSame('Renamed by the curator', $saved->name, 'the curator\'s save went through');
        $this->assertSame(['General'], $saved->tickets()->where('is_deleted', false)->pluck('type')->all());
        $this->assertSame(['EARLYBIRD'], $saved->promoCodes()->pluck('code')->all());
        $this->assertTrue((bool) $saved->tickets_enabled);
        $this->assertSame('Doors at seven', $saved->ticket_notes);
    }

    public function test_an_event_with_a_carpool_offer_saves_and_its_offer_can_be_removed(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $venue = $this->makeRole($owner, 'venue', 'carpoolvenue', ['carpool_enabled' => true]);
        $event = $this->makeEvent($venue);
        $driver = User::factory()->create(['email_verified_at' => now()]);

        $offers = [];
        foreach (['Brooklyn', 'Queens'] as $city) {
            $offers[] = CarpoolOffer::create([
                'event_id' => $event->id, 'user_id' => $driver->id, 'role_id' => $venue->id,
                'event_date' => Carbon::now()->addDays(10)->format('Y-m-d'), 'direction' => 'to_event', 'city' => $city,
                'departure_time' => '18:30', 'meeting_point' => 'Atlantic Ave', 'total_spots' => 3, 'status' => 'active',
            ]);
        }

        $this->browse(function (Browser $browser) use ($owner, $venue, $event) {
            $browser->loginAs($owner)->visit($this->editPath($venue, $event));

            // The event form used to carry the first offer's _method=DELETE, and this save was a 405.
            $this->renameAndSave($browser, 'Renamed with a carpool offer');
        });

        $this->assertSame('Renamed with a carpool offer', Event::findOrFail($event->id)->name);

        $this->browse(function (Browser $browser) use ($venue, $event) {
            $browser->visit($this->editPath($venue, $event).'?engagement=carpool#section-engagement')
                ->waitUntil('window.vueApp !== undefined && window.vueApp.activeEngagementTab === "carpool"', 15);

            // While the form holds unsaved changes the button waits: removing an offer reloads the
            // page, and the half-typed name would go with it. The save bar says why.
            $held = $browser->script('
                window.confirm = function () { window._confirmAsked = true; return true; };
                var name = document.getElementById("event_name");
                name.value = "Half-typed";
                name.dispatchEvent(new Event("input", { bubbles: true }));
                document.querySelector("button[form^=\'form-remove-carpool-offer-\']").click();
                return { held: window.vueApp.heldNotice, asked: !! window._confirmAsked };
            ')[0];
            $browser->pause(400);

            $this->assertTrue($held['held']);
            $this->assertFalse($held['asked'], 'nobody is asked to confirm an action that is not going to happen');
            $this->assertSame('Save your changes first', trim($browser->script('return document.querySelector(".event-save-status").innerText;')[0]));
            $this->assertTrue($browser->script('return window.location.pathname.includes("edit-event") && window.location.hash === "#section-engagement";')[0]);
            $this->assertSame(2, CarpoolOffer::where('event_id', $event->id)->where('status', 'active')->count());

            // With nothing unsaved it goes through. The first button is the one that used to
            // submit the event instead of its own form.
            $browser->script('
                window.vueApp.isDirty = false;
                window.vueApp.sectionDirty = {};
                window.vueApp.heldNotice = false;
                window.confirm = function () { return true; };
                window._skipUnsavedWarning = true;
                document.querySelector("button[form^=\'form-remove-carpool-offer-\']").click();
            ');

            // Back on the form, on the carpool tab, by the fragment the redirect carries.
            $browser->waitUntil('window.location.hash === "#section-carpool" && window.vueApp !== undefined && window.vueApp.activeEngagementTab === "carpool"', 20)
                ->waitUntil('document.getElementById("section-engagement").style.display === "block"', 10);

            $shown = $browser->script('return document.getElementById("section-engagement").style.display;')[0];
            $this->assertSame('block', $shown);
        });

        $this->assertSame('Renamed with a carpool offer', Event::findOrFail($event->id)->name, 'removing an offer did not submit the event');
        $this->assertSame(1, CarpoolOffer::where('event_id', $event->id)->where('status', 'active')->count(), 'one of the two offers was removed');
        $this->assertNotSame('active', CarpoolOffer::findOrFail($offers[0]->id)->status, 'and it was the first one');
    }

    public function test_links_to_a_tab_inside_engagement_open_it(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $venue = $this->makeRole($owner, 'venue', 'linkvenue');
        $event = $this->makeEvent($venue);

        $this->browse(function (Browser $browser) use ($owner, $venue, $event) {
            $browser->loginAs($owner);

            $cases = [
                // What the notification email and the approve/reject redirects link to.
                '#section-fan-content' => 'fan_content',
                '?engagement=polls#section-fan-content' => 'fan_content',
                '#section-polls' => 'polls',
                // The dashboard's links, which already worked and must keep working.
                '?engagement=polls#section-engagement' => 'polls',
            ];

            foreach ($cases as $suffix => $tab) {
                // A fragment-only change does not reload the page, so leave it first.
                $browser->visit('/'.$venue->subdomain.'/schedule')
                    ->visit($this->editPath($venue, $event).$suffix)
                    ->waitUntil('window.vueApp !== undefined', 15)
                    ->waitUntil('document.getElementById("section-engagement").style.display === "block"', 10);

                $state = $browser->script('return {
                    tab: window.vueApp.activeEngagementTab,
                    details: document.getElementById("section-details").style.display,
                };')[0];

                $this->assertSame($tab, $state['tab'], $suffix);
                $this->assertSame('none', $state['details'], $suffix.' no longer lands on the first section');
            }
        });
    }
}
