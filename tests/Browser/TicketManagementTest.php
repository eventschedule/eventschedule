<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Sale;
use Carbon\Carbon;

class TicketManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $user;
    protected $venue;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function testCreateTicketTypes(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event first
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Ticket Test Venue')
                    ->type('email', 'tickets@example.com')
                    ->type('address1', '123 Ticket St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/ticket-test-venue/add_event?date={$today}")
                    ->type('name', 'Concert with Tickets')
                    ->check('tickets_enabled')
                    ->select('ticket_currency_code', 'USD')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Add multiple ticket types
            $browser->clickLink('Edit Event')
                    ->pause(1000)
                    ->click('#add-ticket')
                    ->pause(500)
                    ->type('tickets[0][name]', 'Early Bird')
                    ->type('tickets[0][price]', '15.00')
                    ->type('tickets[0][quantity]', '50')
                    ->type('tickets[0][description]', 'Early bird special pricing')
                    ->click('#add-ticket')
                    ->pause(500)
                    ->type('tickets[1][name]', 'General Admission')
                    ->type('tickets[1][price]', '25.00')
                    ->type('tickets[1][quantity]', '200')
                    ->click('#add-ticket')
                    ->pause(500)
                    ->type('tickets[2][name]', 'VIP')
                    ->type('tickets[2][price]', '50.00')
                    ->type('tickets[2][quantity]', '20')
                    ->type('tickets[2][description]', 'VIP access with perks')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Early Bird')
                    ->assertSee('$15.00')
                    ->assertSee('General Admission')
                    ->assertSee('$25.00')
                    ->assertSee('VIP')
                    ->assertSee('$50.00');
        });
    }

    public function testTicketPurchase(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event with tickets
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Purchase Test Venue')
                    ->type('email', 'purchase@example.com')
                    ->type('address1', '456 Purchase Ave')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/purchase-test-venue/add_event?date={$today}")
                    ->type('name', 'Ticket Purchase Event')
                    ->check('tickets_enabled')
                    ->select('ticket_currency_code', 'USD')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Add ticket
            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Standard Ticket')
                    ->type('tickets[0][price]', '20.00')
                    ->type('tickets[0][quantity]', '100')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Purchase tickets
            $browser->clickLink('Buy Tickets')
                    ->pause(1000)
                    ->assertSee('Standard Ticket')
                    ->assertSee('$20.00')
                    ->type('tickets[0]', '2') // Buy 2 tickets
                    ->type('customer_name', 'John Doe')
                    ->type('customer_email', 'john@example.com')
                    ->type('customer_phone', '555-1234')
                    ->press('Purchase Tickets')
                    ->pause(2000)
                    ->assertSee('Purchase Successful')
                    ->assertSee('Total: $40.00');

            // Verify sale was created
            $this->assertDatabaseHas('sales', [
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'total' => 4000 // Stored in cents
            ]);
        });
    }

    public function testTicketValidation(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Validation Test Venue')
                    ->type('email', 'validation@example.com')
                    ->type('address1', '789 Validation St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/validation-test-venue/add_event?date={$today}")
                    ->type('name', 'Validation Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Test Ticket')
                    ->type('tickets[0][price]', '10.00')
                    ->type('tickets[0][quantity]', '5')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test validation errors
            $browser->clickLink('Buy Tickets')
                    ->press('Purchase Tickets')
                    ->assertSee('The customer name field is required')
                    ->assertSee('The customer email field is required');

            // Test invalid email
            $browser->type('customer_name', 'Test User')
                    ->type('customer_email', 'invalid-email')
                    ->press('Purchase Tickets')
                    ->assertSee('The customer email field must be a valid email address');

            // Test purchasing more tickets than available
            $browser->clear('customer_email')
                    ->type('customer_email', 'test@example.com')
                    ->type('tickets[0]', '10') // More than the 5 available
                    ->press('Purchase Tickets')
                    ->assertSee('Not enough tickets available');
        });
    }

    public function testQRCodeGeneration(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'QR Test Venue')
                    ->type('email', 'qr@example.com')
                    ->type('address1', '101 QR Street')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/qr-test-venue/add_event?date={$today}")
                    ->type('name', 'QR Code Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'QR Ticket')
                    ->type('tickets[0][price]', '15.00')
                    ->type('tickets[0][quantity]', '10')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Purchase ticket
            $browser->clickLink('Buy Tickets')
                    ->type('tickets[0]', '1')
                    ->type('customer_name', 'QR Test User')
                    ->type('customer_email', 'qr@example.com')
                    ->press('Purchase Tickets')
                    ->pause(2000);

            // Check for QR code in success page
            $browser->assertPresent('.qr-code')
                    ->assertSee('Show this QR code at the event');
        });
    }

    public function testTicketScanning(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event with tickets
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Scan Test Venue')
                    ->type('email', 'scan@example.com')
                    ->type('address1', '202 Scan Ave')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/scan-test-venue/add_event?date={$today}")
                    ->type('name', 'Scan Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Scan Ticket')
                    ->type('tickets[0][price]', '12.00')
                    ->type('tickets[0][quantity]', '5')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Purchase ticket first
            $browser->clickLink('Buy Tickets')
                    ->type('tickets[0]', '1')
                    ->type('customer_name', 'Scanner User')
                    ->type('customer_email', 'scanner@example.com')
                    ->press('Purchase Tickets')
                    ->pause(2000);

            // Test ticket scanning interface
            $browser->visit('/scan-test-venue/scan')
                    ->assertSee('Scan Tickets')
                    ->assertPresent('#ticket-scanner')
                    ->assertSee('Point camera at QR code');
        });
    }

    public function testTicketRefund(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Refund Test Venue')
                    ->type('email', 'refund@example.com')
                    ->type('address1', '303 Refund St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/refund-test-venue/add_event?date={$today}")
                    ->type('name', 'Refund Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Refundable Ticket')
                    ->type('tickets[0][price]', '30.00')
                    ->type('tickets[0][quantity]', '10')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Purchase ticket
            $browser->clickLink('Buy Tickets')
                    ->type('tickets[0]', '1')
                    ->type('customer_name', 'Refund User')
                    ->type('customer_email', 'refund@example.com')
                    ->press('Purchase Tickets')
                    ->pause(2000);

            // Go to admin area to process refund
            $browser->visit('/refund-test-venue/schedule')
                    ->clickLink('Sales')
                    ->pause(1000)
                    ->assertSee('Refund User')
                    ->assertSee('$30.00')
                    ->click('.refund-button')
                    ->pause(1000)
                    ->press('Process Refund')
                    ->pause(2000)
                    ->assertSee('Refund processed');
        });
    }

    public function testTicketInventoryManagement(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Inventory Test Venue')
                    ->type('email', 'inventory@example.com')
                    ->type('address1', '404 Inventory Blvd')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/inventory-test-venue/add_event?date={$today}")
                    ->type('name', 'Inventory Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Limited Ticket')
                    ->type('tickets[0][price]', '25.00')
                    ->type('tickets[0][quantity]', '3') // Only 3 tickets
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Purchase 2 tickets
            $browser->clickLink('Buy Tickets')
                    ->type('tickets[0]', '2')
                    ->type('customer_name', 'Inventory User 1')
                    ->type('customer_email', 'inv1@example.com')
                    ->press('Purchase Tickets')
                    ->pause(2000);

            // Try to purchase 3 more (should fail)
            $browser->clickLink('Buy More Tickets')
                    ->type('tickets[0]', '3')
                    ->type('customer_name', 'Inventory User 2')
                    ->type('customer_email', 'inv2@example.com')
                    ->press('Purchase Tickets')
                    ->assertSee('Not enough tickets available');

            // Purchase the remaining 1 ticket
            $browser->clear('tickets[0]')
                    ->type('tickets[0]', '1')
                    ->press('Purchase Tickets')
                    ->pause(2000)
                    ->assertSee('Purchase Successful');

            // Verify sold out status
            $browser->clickLink('Buy More Tickets')
                    ->assertSee('Sold Out')
                    ->assertMissing('input[name="tickets[0]"]');
        });
    }

    public function testTicketReports(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event with ticket sales
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Reports Test Venue')
                    ->type('email', 'reports@example.com')
                    ->type('address1', '505 Reports St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/reports-test-venue/add_event?date={$today}")
                    ->type('name', 'Reports Event')
                    ->check('tickets_enabled')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $browser->clickLink('Edit Event')
                    ->click('#add-ticket')
                    ->type('tickets[0][name]', 'Report Ticket')
                    ->type('tickets[0][price]', '18.00')
                    ->type('tickets[0][quantity]', '20')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Make some purchases for reporting
            $browser->clickLink('Buy Tickets')
                    ->type('tickets[0]', '3')
                    ->type('customer_name', 'Report User 1')
                    ->type('customer_email', 'report1@example.com')
                    ->press('Purchase Tickets')
                    ->pause(2000);

            // View sales report
            $browser->visit('/reports-test-venue/schedule')
                    ->clickLink('Sales Report')
                    ->pause(1000)
                    ->assertSee('Sales Report')
                    ->assertSee('Report User 1')
                    ->assertSee('$54.00') // 3 × $18.00
                    ->assertSee('Total Revenue');
        });
    }
} 