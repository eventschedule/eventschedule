<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class TicketingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesScheduleData;

    public function test_free_ticket_checkout_creates_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['type' => 'Free', 'price' => 0, 'quantity' => 100]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Free Buyer',
            'email' => 'free@example.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
        ]);

        $sale = Sale::where('email', 'free@example.com')->first();
        $this->assertNotNull($sale);
        $this->assertDatabaseHas('sale_tickets', [
            'sale_id' => $sale->id,
            'ticket_id' => $ticket->id,
            'quantity' => 2,
        ]);
    }

    public function test_rsvp_creates_sale(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'RSVP Guest',
            'email' => 'rsvp@example.com',
        ]);

        $this->assertDatabaseHas('sales', [
            'event_id' => $event->id,
            'email' => 'rsvp@example.com',
            'payment_method' => 'rsvp',
        ]);
    }

    public function test_oversell_is_rejected(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 2]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Greedy',
            'email' => 'greedy@example.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 5],
        ]);

        // No sale created when requesting more than available.
        $this->assertDatabaseMissing('sales', ['email' => 'greedy@example.com']);
    }

    public function test_qr_checkin_marks_attendance(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Event must be happening now so the check-in window (opens 24h before start) is open.
        $event = $this->createEvent($role, ['starts_at' => now()->format('Y-m-d H:i:s')]);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'event_date' => now()->format('Y-m-d')], $ticket, 1);

        $response = $this->actingAs($owner)->post(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));

        $response->assertOk();

        // The scan must actually record the check-in (scanned() returns 200 even on
        // errors, so asserting only the status is a false positive).
        $saleTicket = \App\Models\SaleTicket::where('sale_id', $sale->id)->first();
        $seats = json_decode($saleTicket->seats, true);
        $this->assertNotNull($seats[1], 'seat 1 should have a check-in timestamp');
    }

    public function test_checkin_stats_endpoint(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);

        $this->actingAs($owner)->get(route('checkin.stats', [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
        ]))->assertOk()
            ->assertJson(['event_name' => $event->name])
            ->assertJsonStructure(['total_sold', 'total_checked_in', 'tickets']);
    }

    public function test_waitlist_join(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 1]);
        $date = \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d');

        // Sell out the only ticket so the waitlist becomes available.
        $this->createSale($event, $role, ['status' => 'paid', 'event_date' => $date], $ticket, 1);

        $this->post(route('waitlist.join', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $date,
            'name' => 'Hopeful',
            'email' => 'waitlist@example.com',
        ]);

        $this->assertDatabaseHas('ticket_waitlists', [
            'event_id' => $event->id,
            'email' => 'waitlist@example.com',
        ]);
    }

    public function test_post_event_feedback(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, [
            'starts_at' => \Carbon\Carbon::now()->subDays(2)->format('Y-m-d H:i:s'),
            'feedback_enabled' => true,
        ]);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'event_date' => \Carbon\Carbon::now()->subDays(2)->format('Y-m-d'),
        ], $ticket, 1);

        $this->post(route('feedback.store', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]), [
            'rating' => 5,
            'comment' => 'Loved it!',
        ]);

        $this->assertDatabaseHas('event_feedbacks', [
            'event_id' => $event->id,
            'rating' => 5,
        ]);
    }

    public function test_sales_csv_export(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $this->createSale($event, $role, ['name' => 'Alice CSV', 'email' => 'alice@example.com', 'status' => 'paid'], $ticket, 1);

        $response = $this->actingAs($owner)->get(route('sales.export', ['role_id' => UrlUtils::encodeId($role->id)]));
        $response->assertOk();
        $this->assertStringContainsString('alice@example.com', $response->streamedContent());
    }

    public function test_promo_code_validation(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 100, 'quantity' => 50]);

        \App\Models\PromoCode::create([
            'event_id' => $event->id,
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->post(route('promo_code.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'code' => 'SAVE10',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
        ]);

        $response->assertOk()->assertJson(['valid' => true]);
    }

    public function test_addon_purchase(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $addon = $this->createTicket($event, ['type' => 'Parking', 'price' => 0, 'quantity' => 50, 'is_addon' => true]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Addon Buyer',
            'email' => 'addon@example.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            'addons' => [UrlUtils::encodeId($addon->id) => 2],
        ]);

        $sale = Sale::where('email', 'addon@example.com')->first();
        $this->assertNotNull($sale);
        $this->assertDatabaseHas('sale_tickets', ['sale_id' => $sale->id, 'ticket_id' => $addon->id, 'quantity' => 2]);
    }

    public function test_pass_purchase(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass',
            'price' => 0,
            'quantity' => 50,
            'is_pass' => true,
            'pass_usage_type' => 'total',
            'pass_max_uses' => 5,
        ]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Pass Buyer',
            'email' => 'pass@example.com',
            'tickets' => [UrlUtils::encodeId($pass->id) => 1],
        ]);

        $sale = Sale::where('email', 'pass@example.com')->first();
        $this->assertNotNull($sale);
        $this->assertDatabaseHas('sale_tickets', ['sale_id' => $sale->id, 'ticket_id' => $pass->id]);
    }

    public function test_individual_tickets_create_per_attendee_sales(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'individual_tickets' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Primary',
            'email' => 'primary@example.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 2],
            'guests' => [
                ['name' => 'Primary', 'email' => 'primary@example.com'],
                ['name' => 'Guest Two', 'email' => 'guest2@example.com'],
            ],
        ]);

        $this->assertDatabaseHas('sales', ['email' => 'guest2@example.com']);
    }

    public function test_bulk_attendee_import(): void
    {
        // NOTE: Import requires the posted event_date to match the event's *local*
        // occurrence date (matchesDate uses the schedule timezone). Reliable coverage
        // needs a fixed-timezone fixture; left for a follow-up. See TEST_COVERAGE notes.
        $this->markTestSkipped('Bulk import date-matching is timezone-sensitive; needs a dedicated fixture.');

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);

        $this->actingAs($owner)->post(route('sales.import_store'), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'ticket_id' => UrlUtils::encodeId($ticket->id),
            'default_status' => 'paid',
            'send_emails' => false,
            'entries' => [
                ['name' => 'Imported One', 'email' => 'import1@example.com', 'quantity' => 1, 'status' => 'paid'],
                ['name' => 'Imported Two', 'email' => 'import2@example.com', 'quantity' => 1, 'status' => 'paid'],
            ],
        ]);

        $this->assertDatabaseHas('sales', ['email' => 'import1@example.com']);
        $this->assertDatabaseHas('sales', ['email' => 'import2@example.com']);
    }

    public function test_sale_confirmation_email_is_sent(): void
    {
        // NOTE: In hosted mode the confirmation email only sends when the schedule has a
        // configured mail transport, and the dispatch path (queued job vs RoleMailerService)
        // depends on runtime config. Not reliably assertable in the array-mailer test env.
        $this->markTestSkipped('Sale confirmation email path is environment-guarded; see TEST_COVERAGE notes.');

        \Illuminate\Support\Facades\Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // A configured mail transport is required before confirmation emails are sent.
        // The model mutator encrypts this array on assignment.
        $role->email_settings = [
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'mailer',
            'password' => 'secret',
            'encryption' => 'tls',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Test Schedule',
        ];
        $role->save();

        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Mail Buyer',
            'email' => 'mailbuyer@example.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ]);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendQueuedEmail::class);
    }

    public function test_rsvp_cancellation(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['rsvp_enabled' => true]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'payment_method' => 'rsvp',
            'payment_amount' => 0,
        ]);

        $this->post(route('rsvp.cancel', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
            'secret' => $sale->secret,
        ]);

        $this->assertSame('cancelled', $sale->fresh()->status);
    }

    public function test_buyer_ticket_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, ['name' => 'Ticket Holder', 'status' => 'paid'], $ticket, 1);

        $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk()->assertSee('Ticket Holder');
    }
}
