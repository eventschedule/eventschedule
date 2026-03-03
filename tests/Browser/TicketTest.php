<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class TicketTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * A basic test for ticket functionality.
     */
    public function test_basic_ticket_functionality(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account using the trait
            $this->setupTestAccount($browser, $name, $email, $password);

            // Create test data using the trait
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);
            $this->createTestEventWithTickets($browser);

            // Purchase ticket
            $browser->visit('/talent/venue')
                ->press('Buy Tickets')
                ->waitFor('#ticket-0', 5)
                ->select('#ticket-0', '1')
                ->scrollIntoView('button[type="submit"]')
                ->press('CHECKOUT')
                ->waitForText('ATTENDEE', 5) // Wait for ticket confirmation page
                ->assertSee($name); // Verify attendee name on ticket
        });
    }

    /**
     * Test multiple ticket types with descriptions, custom fields, and promo codes.
     */
    public function test_ticket_types_with_settings(): void
    {
        $name = 'Jane Smith';
        $email = 'jane@example.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Set up account, venue, talent
            $this->setupTestAccount($browser, $name, $email, $password);
            $this->createTestVenue($browser);
            $this->createTestTalent($browser);

            // Create event with 2 ticket types, custom fields, and promo code
            $eventDate = date('Y-m-d', strtotime('+3 days'));
            $browser->visit('/talent/add-event?date=' . $eventDate)
                ->waitFor('#name', 15)
                ->type('name', 'Test Event');

            // Set venue
            $browser->click('a[data-section="section-venue"]')
                ->waitFor('#in_person', 5);
            $browser->script("var cb = document.getElementById('in_person'); if (!cb.checked) cb.click();");
            $browser->waitFor('#selected_venue', 5)
                ->select('#selected_venue');

            // Enable tickets
            $browser->click('a[data-section="section-tickets"]')
                ->waitFor('#ticket_mode_tickets', 5)
                ->click('label[for="ticket_mode_tickets"]')
                ->pause(1000);

            // Configure ticket 0: General Admission ($10, qty 50, description, dropdown custom field)
            $browser->script("
                var v = window.vueApp;
                v.tickets[0].price = 10;
                v.tickets[0].quantity = 50;
                v.tickets[0].type = 'General Admission';
                v.tickets[0].description = 'Standard entry to the event';
                var cfKey0 = 'field' + Date.now();
                v.tickets[0].custom_fields = {
                    [cfKey0]: { name: 'Dietary Needs', name_en: '', type: 'dropdown', options: 'None,Vegetarian,Vegan,Gluten-Free', required: false, index: 1 }
                };
            ");

            // Add second ticket type
            $browser->script("window.vueApp.addTicket();");
            $browser->pause(500);

            // Configure ticket 1: VIP ($25, qty 20, description, string custom field)
            $browser->script("
                var v = window.vueApp;
                v.tickets[1].price = 25;
                v.tickets[1].quantity = 20;
                v.tickets[1].type = 'VIP';
                v.tickets[1].description = 'VIP access with premium seating';
                var cfKey1 = 'field' + Date.now();
                v.tickets[1].custom_fields = {
                    [cfKey1]: { name: 'Seat Preference', name_en: '', type: 'string', required: false, index: 1 }
                };
            ");

            // Switch to Options tab and add event-level custom fields
            $browser->script("window.vueApp.activeTicketTab = 'options';");
            $browser->pause(300);

            // Add "Company" (string) and "T-Shirt Size" (dropdown, required) event custom fields
            $browser->script("
                var v = window.vueApp;
                var key1 = 'field' + Date.now();
                var key2 = 'field' + (Date.now() + 1);
                v.eventCustomFields = {
                    [key1]: { name: 'Company', name_en: '', type: 'string', required: false, index: 1 },
                    [key2]: { name: 'T-Shirt Size', name_en: '', type: 'dropdown', options: 'S,M,L,XL', required: true, index: 2 }
                };
            ");

            // Switch to Promo Codes tab and add a 100% promo code
            $browser->script("window.vueApp.activeTicketTab = 'promo_codes';");
            $browser->pause(300);
            $browser->script("window.vueApp.addPromoCode();");
            $browser->pause(300);

            // Configure promo code: FREEPASS, 100% off, active
            $browser->script("
                var v = window.vueApp;
                var pc = v.promoCodes[v.promoCodes.length - 1];
                pc.code = 'FREEPASS';
                pc.type = 'percentage';
                pc.value = 100;
                pc.is_active = true;
            ");

            // Submit the event form
            $browser->script("
                window._skipUnsavedWarning = true;
                document.getElementById('edit-form').requestSubmit();
            ");

            $browser->waitForLocation('/talent/schedule', 45)
                ->assertSee('Venue');

            // ── GP Checkout ──

            // Visit the event page
            $browser->visit('/talent/venue')
                ->waitForText('Buy Tickets', 10)
                ->press('Buy Tickets')
                ->waitFor('#ticket-0', 5);

            // Assert ticket types and descriptions are visible
            $browser->assertSee('General Admission')
                ->assertSee('VIP')
                ->assertSee('Standard entry to the event')
                ->assertSee('VIP access with premium seating');

            // Select 1 General Admission + 1 VIP
            $browser->select('#ticket-0', '1')
                ->select('#ticket-1', '1')
                ->pause(500);

            // Fill event-level custom fields via Vue
            $browser->script("
                var app = document.querySelector('#ticket-selector').__vue_app__;
                var vm = app._instance.proxy;
                var keys = Object.keys(vm.eventCustomFields);
                // First field is Company (string), second is T-Shirt Size (dropdown)
                for (var i = 0; i < keys.length; i++) {
                    var field = vm.eventCustomFields[keys[i]];
                    if (field.type === 'string') {
                        vm.eventCustomValues[keys[i]] = 'Acme Corp';
                    } else if (field.type === 'dropdown') {
                        vm.eventCustomValues[keys[i]] = 'L';
                    }
                }
            ");

            // Fill ticket-level custom fields via Vue
            $browser->script("
                var app = document.querySelector('#ticket-selector').__vue_app__;
                var vm = app._instance.proxy;
                vm.tickets.forEach(function(ticket) {
                    if (ticket.custom_fields) {
                        Object.keys(ticket.custom_fields).forEach(function(key) {
                            var field = ticket.custom_fields[key];
                            if (field.type === 'dropdown') {
                                ticket.custom_values[key] = 'Vegetarian';
                            } else if (field.type === 'string') {
                                ticket.custom_values[key] = 'Front Row';
                            }
                        });
                    }
                });
            ");

            // Expand promo code section and apply
            $browser->script("
                var app = document.querySelector('#ticket-selector').__vue_app__;
                var vm = app._instance.proxy;
                vm.promoCodeExpanded = true;
                vm.promoCode = 'FREEPASS';
            ");
            $browser->pause(300);

            // Click Apply button
            $browser->script("
                var app = document.querySelector('#ticket-selector').__vue_app__;
                var vm = app._instance.proxy;
                vm.applyPromoCode();
            ");
            $browser->waitForText('Promo code applied', 10);

            // Click CHECKOUT
            $browser->scrollIntoView('button[type="submit"]')
                ->press('CHECKOUT');

            // ── Ticket View Page Verification ──
            $browser->waitForText('ATTENDEE', 15)
                ->assertSee($name);

            // Assert ticket types and quantities
            $browser->assertSee('General Admission')
                ->assertSee('VIP')
                ->assertSee('x1');

            // Assert promo code discount is shown
            $browser->assertSee('FREEPASS');

            // Assert custom field labels and values
            $browser->assertSee('Company')
                ->assertSee('Acme Corp')
                ->assertSee('T-Shirt Size')
                ->assertSee('Dietary Needs')
                ->assertSee('Vegetarian')
                ->assertSee('Seat Preference')
                ->assertSee('Front Row');

            // ── Admin Sales Page Verification ──
            $browser->visit('/sales')
                ->waitForText($name, 10)
                ->assertSee('FREEPASS');

            // Click expand button to show custom field values
            $browser->click('[data-toggle-custom-fields]')
                ->pause(500);

            // Assert custom field values in expanded row
            $browser->assertSee('Acme Corp')
                ->assertSee('Front Row');
        });
    }
}
