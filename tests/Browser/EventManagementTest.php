<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Event;
use Carbon\Carbon;

class EventManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $user;
    protected $venue;
    protected $talent;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function testCreateEventFromVenue(): void
    {
        $this->browse(function (Browser $browser) {
            // First create a venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Event Test Venue')
                    ->type('email', 'venue@example.com')
                    ->type('address1', '123 Event St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create talent/schedule
            $browser->visit('/new/schedule')
                    ->type('name', 'Test Artist')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create event from venue
            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/event-test-venue/add_event?date={$today}")
                    ->assertSee('Add Event')
                    ->type('name', 'Rock Concert')
                    ->type('description', 'Amazing rock concert with great music')
                    ->select('category_id', '3') // Sports category
                    ->type('duration', '180') // 3 hours
                    ->select('#selected_member') // Select the talent
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(3000)
                    ->assertSee('Rock Concert');

            // Verify event was created
            $this->assertDatabaseHas('events', [
                'name' => 'Rock Concert',
                'description' => 'Amazing rock concert with great music',
                'duration' => 180
            ]);
        });
    }

    public function testCreateEventFromTalent(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent first
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Jazz Musician')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create venue
            $browser->visit('/new/venue')
                    ->type('name', 'Jazz Club')
                    ->type('email', 'jazz@example.com')
                    ->type('address1', '456 Jazz Ave')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create event from talent
            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/jazz-musician/add_event?date={$today}")
                    ->type('name', 'Jazz Night')
                    ->type('description', 'Smooth jazz evening')
                    ->select('category_id', '1') // Music category
                    ->type('duration', '120')
                    ->select('#selected_venue') // Select the venue
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(3000)
                    ->assertSee('Jazz Night');
        });
    }

    public function testEditEvent(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event first
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Edit Test Venue')
                    ->type('email', 'edit@example.com')
                    ->type('address1', '789 Edit St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/edit-test-venue/add_event?date={$today}")
                    ->type('name', 'Original Event')
                    ->type('description', 'Original description')
                    ->type('duration', '60')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Edit the event
            $browser->click('.event-item') // Click on the event
                    ->pause(1000)
                    ->clickLink('Edit Event')
                    ->clear('name')
                    ->type('name', 'Updated Event')
                    ->clear('description')
                    ->type('description', 'Updated description with more details')
                    ->clear('duration')
                    ->type('duration', '90')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Updated Event')
                    ->assertSee('Updated description');

            // Verify changes in database
            $this->assertDatabaseHas('events', [
                'name' => 'Updated Event',
                'description' => 'Updated description with more details',
                'duration' => 90
            ]);
        });
    }

    public function testEventValidation(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue first
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Validation Test Venue')
                    ->type('email', 'validation@example.com')
                    ->type('address1', '101 Validation St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test validation
            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/validation-test-venue/add_event?date={$today}")
                    ->press('SAVE')
                    ->assertSee('The name field is required');

            // Test duration validation
            $browser->type('name', 'Test Event')
                    ->type('duration', '-10')
                    ->press('SAVE')
                    ->assertSee('The duration field must be at least 1');

            // Test very long duration
            $browser->clear('duration')
                    ->type('duration', '9999')
                    ->press('SAVE')
                    ->assertSee('The duration field must not be greater than 1440');
        });
    }

    public function testEventWithTickets(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Ticket Test Venue')
                    ->type('email', 'tickets@example.com')
                    ->type('address1', '202 Ticket St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create event with tickets
            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/ticket-test-venue/add_event?date={$today}")
                    ->type('name', 'Ticketed Event')
                    ->type('description', 'Event with tickets for sale')
                    ->check('tickets_enabled')
                    ->select('ticket_currency_code', 'USD')
                    ->type('ticket_notes', 'Bring ID for entry')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Add ticket types
            $browser->clickLink('Edit Event')
                    ->pause(1000)
                    ->click('#add-ticket')
                    ->pause(500)
                    ->type('tickets[0][name]', 'General Admission')
                    ->type('tickets[0][price]', '25.00')
                    ->type('tickets[0][quantity]', '100')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('General Admission')
                    ->assertSee('$25.00');
        });
    }

    public function testEventCategories(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Category Test Venue')
                    ->type('email', 'category@example.com')
                    ->type('address1', '303 Category St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test different categories
            $categories = [
                ['id' => '1', 'name' => 'Music Event', 'category' => 'Music'],
                ['id' => '2', 'name' => 'Art Show', 'category' => 'Art'],
                ['id' => '3', 'name' => 'Sports Game', 'category' => 'Sports'],
                ['id' => '4', 'name' => 'Theater Play', 'category' => 'Theatre'],
                ['id' => '5', 'name' => 'Workshop Event', 'category' => 'Workshop'],
            ];

            $today = Carbon::today()->format('Y-m-d');
            
            foreach ($categories as $category) {
                $browser->visit("/category-test-venue/add_event?date={$today}")
                        ->type('name', $category['name'])
                        ->select('category_id', $category['id'])
                        ->scrollIntoView('button[type="submit"]')
                        ->press('SAVE')
                        ->pause(2000)
                        ->assertSee($category['name']);
            }
        });
    }

    public function testEventTimeSlots(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Time Test Venue')
                    ->type('email', 'time@example.com')
                    ->type('address1', '404 Time St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create event with specific time
            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/time-test-venue/add_event?date={$today}")
                    ->type('name', 'Timed Event')
                    ->type('starts_at_time', '19:30') // 7:30 PM
                    ->type('duration', '120') // 2 hours
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Timed Event')
                    ->assertSee('7:30 PM');
        });
    }

    public function testEventDeletion(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue and event
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Delete Test Venue')
                    ->type('email', 'delete@example.com')
                    ->type('address1', '505 Delete St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            $today = Carbon::today()->format('Y-m-d');
            $browser->visit("/delete-test-venue/add_event?date={$today}")
                    ->type('name', 'Event to Delete')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Delete the event
            $browser->click('.event-item')
                    ->pause(1000)
                    ->clickLink('Edit Event')
                    ->pause(1000)
                    ->press('Delete Event')
                    ->pause(1000)
                    ->acceptDialog()
                    ->pause(2000)
                    ->assertDontSee('Event to Delete');
        });
    }

    public function testEventImport(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Import Test Venue')
                    ->type('email', 'import@example.com')
                    ->type('address1', '606 Import St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test AI import feature
            $browser->visit('/import-test-venue/import')
                    ->assertSee('Import Event')
                    ->type('event_details', 'Rock concert tonight at 8 PM, tickets $20, duration 2 hours')
                    ->press('Parse Event')
                    ->pause(3000);

            // Should parse and show suggested event details
            if ($browser->element('#parsed_name')) {
                $browser->assertSee('Rock concert')
                        ->press('Create Event')
                        ->pause(2000)
                        ->assertSee('Rock concert');
            }
        });
    }
} 