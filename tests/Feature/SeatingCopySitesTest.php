<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Every surface that names a sale's tickets has to name its seats too.
 *
 * A buyer holding "Stalls A3, A4" who sees "Stalls x2" on the door scanner, the check-in feed, the
 * sales export, the webhook or the plain-text confirmation has no way to find their seats, and the
 * staff reading those screens have no way to send them there. Each of these was carrying the
 * quantity alone.
 */
class SeatingCopySitesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated',
        ]);
        for ($n = 1; $n <= 10; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, ?SeatingPlan $plan, ?string $startsAt = null): Event
    {
        $payload = [
            'name' => 'Seated Show',
            'starts_at' => $startsAt ?: now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999]
                + ($plan ? ['seating_band' => 'Stalls'] : [])],
        ];
        if ($plan) {
            $payload['seating_plan_id'] = $plan->id;
        }
        $request = Request::create('/', 'POST', $payload);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /** Buy the first $count seats through the real hold + checkout path. */
    private function buy(Role $role, Event $event, int $count): Sale
    {
        $map = app(SeatingMapService::class)->materialize($event);
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->take($count)->get();

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seats->pluck('id')->all(),
        ])->assertOk();

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Buyer', 'email' => 'buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($event->tickets->first()->id) => $count],
        ])->assertRedirect();

        return Sale::latest('id')->firstOrFail();
    }

    private function labels(Sale $sale): array
    {
        return $sale->saleTickets()->first()->seatLabels();
    }

    public function test_the_door_scanner_is_told_the_seats_not_just_the_slot_numbers(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        // Check-in opens 24 hours out, so a show six months away is never scannable.
        $event = $this->seatedEvent($role, $this->makePlan($role), now()->addHours(2)->format('Y-m-d H:i:s'));
        $sale = $this->buy($role, $event, 2);
        $sale->forceFill(['event_date' => $event->saleEventDateFromStartsAt(), 'status' => 'paid'])->save();

        $response = $this->actingAs($role->user)->postJson(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret,
        ]))->assertOk();

        $ticket = $response->json('tickets.0');
        $this->assertSame($this->labels($sale), $ticket['seat_labels']);
        // The slot map still means "checked in / not", and must not have been repurposed.
        $this->assertCount(2, $ticket['seats']);
    }

    public function test_a_general_admission_scan_carries_no_seat_labels(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, null, now()->addHours(2)->format('Y-m-d H:i:s'));
        $sale = $this->createSale($event, $role, [], $event->tickets->first(), 2);

        $response = $this->actingAs($role->user)->postJson(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret,
        ]))->assertOk();

        $this->assertSame([], $response->json('tickets.0.seat_labels'));
    }

    public function test_the_check_in_feed_names_the_seat_that_walked_in(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $sale = $this->buy($role, $event, 2);
        $date = $event->saleEventDateFromStartsAt();
        $sale->forceFill(['event_date' => $date, 'status' => 'paid'])->save();

        // Check the second seat in, not the first, so a right-answer-by-accident off-by-one shows.
        $line = $sale->saleTickets()->first();
        $line->forceFill(['seats' => json_encode([1 => null, 2 => time()])])->save();

        $stats = $this->actingAs($role->user)->getJson(route('checkin.stats', [
            'event_id' => UrlUtils::encodeId($event->id),
        ]).'?date='.$date)->assertOk();

        $recent = $stats->json('recent_checkins');
        $this->assertCount(1, $recent);
        $this->assertSame($this->labels($sale)[1], $recent[0]['seat_label']);
    }

    public function test_the_sales_export_carries_a_seats_column(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $role->update(['plan_type' => 'pro', 'plan_expires' => now()->addYear()]);
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $sale = $this->buy($role, $event, 2);

        $csv = $this->actingAs($role->user)->get(route('sales.export'))->assertOk()->streamedContent();

        $rows = array_map('str_getcsv', array_filter(explode("\n", trim($csv))));
        $header = $rows[0];
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $seatsCol = array_search('Seats', $header, true);
        $this->assertNotFalse($seatsCol, 'the export must have a Seats column');
        $this->assertSame(implode(', ', $this->labels($sale)), $rows[1][$seatsCol]);
    }

    public function test_the_sale_webhook_payload_lists_the_seats(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $sale = $this->buy($role, $event, 2);
        $sale->forceFill(['event_date' => $event->saleEventDateFromStartsAt()])->save();

        $data = json_decode(json_encode($sale->fresh()->load('saleTickets.ticket', 'event.tickets')->toApiData(true)), true);
        $this->assertSame($this->labels($sale), $data['tickets'][0]['seats']);
    }

    public function test_a_general_admission_sale_has_no_seats_key(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, null);
        $sale = $this->createSale($event, $role, [], $event->tickets->first(), 2);

        $data = json_decode(json_encode($sale->fresh()->load('saleTickets.ticket', 'event.tickets')->toApiData(true)), true);
        $this->assertArrayNotHasKey('seats', $data['tickets'][0]);
    }

    public function test_the_plain_text_confirmation_lists_the_seats(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $sale = $this->buy($role, $event, 2);

        // Render the real mailable so the whole pipeline is proven, not a hand-assembled view.
        $mail = new \App\Mail\TicketPurchase($sale->fresh()->load('saleTickets.ticket'), $event, $role);
        $body = $mail->render();

        $content = $mail->content();
        $this->assertSame('emails.ticket_purchase_text', $content->text);
        $plain = view($content->text, $content->with)->render();

        foreach ($this->labels($sale) as $label) {
            $this->assertStringContainsString($label, $plain);
            $this->assertStringContainsString($label, $body);
        }
    }

    public function test_the_event_api_reports_the_plan_and_the_allocated_bands(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);

        $data = json_decode(json_encode($event->load('tickets')->toApiData()), true);

        $this->assertSame(UrlUtils::encodeId($plan->id), $data['seating_plan_id']);
        $this->assertSame('Stalls', $data['tickets'][0]['seating_band']);
        $this->assertTrue($data['tickets'][0]['is_allocated']);

        // ...and a general-admission event says so rather than omitting the key.
        $plain = json_decode(json_encode($this->seatedEvent($role, null)->load('tickets')->toApiData()), true);
        $this->assertNull($plain['seating_plan_id']);
        $this->assertArrayNotHasKey('seating_band', $plain['tickets'][0]);
    }
}
