<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\Role;
use App\Models\SaleTicket;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An appointment booking is a paid sale with a sale ticket, on an Event of its own that carries
 * appointment_type_id. The analytics queries that read "paid sales" therefore picked bookings up
 * along with tickets: nobody scans a booking at a door, so each read as a no-show on the Check-Ins
 * tab, and each counted as a conversion of event page views it never came from. Both now leave
 * bookings out, as the check-in dashboard (CheckInController) always has. Revenue still counts
 * them, and so does total_sales, which gates the Revenue cards.
 *
 * Writing the conversion case also turned up an older bug: getConversionStats() summed the views
 * into a total_views alias on an AnalyticsEventsDaily model, whose getTotalViewsAttribute()
 * accessor shadowed it and returned 0, so the conversion rate read 0% for every schedule.
 */
class AnalyticsAppointmentBookingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Carbon $start;

    private Carbon $end;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-06 12:00:00', 'UTC'));

        // last_30_days, the default preset.
        $this->start = now()->subDays(30)->startOfDay();
        $this->end = now()->endOfDay();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * A show with one paid ticket, scanned at the door and seen ten times, and one paid booking,
     * built the way AppointmentService builds one: its own Event, a one-seat ticket and a sale
     * ticket that nobody will ever scan.
     *
     * @return array{0: User, 1: Role, 2: Event, 3: Event}
     */
    private function scheduleWithAShowAndABooking(): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $show = $this->createEvent($venue, [
            'name' => 'The Show',
            'creator_role_id' => $venue->id,
            'starts_at' => '2026-09-03 18:00:00',
            'tickets_enabled' => true,
        ]);
        $ticket = $this->createTicket($show, ['price' => 20]);
        $sale = $this->createSale($show, $venue, ['event_date' => '2026-09-03', 'payment_amount' => 20], $ticket);

        $saleTicket = SaleTicket::where('sale_id', $sale->id)->sole();
        $saleTicket->seats = json_encode([1 => Carbon::parse('2026-09-03 17:55:00')->timestamp]);
        $saleTicket->save();

        $type = $this->createAppointmentType($venue, ['price' => 50]);
        $booking = $this->createEvent($venue, [
            'name' => 'The Booking',
            'creator_role_id' => $venue->id,
            'appointment_type_id' => $type->id,
            'tickets_enabled' => false,
            'starts_at' => '2026-09-04 10:00:00',
        ]);
        $slot = $this->createTicket($booking, ['price' => 50, 'quantity' => 1]);
        $this->createSale($booking, $venue, ['event_date' => '2026-09-04', 'payment_amount' => 50], $slot);

        // Ten page views on the show, so the conversion rate has a denominator.
        AnalyticsEventsDaily::create([
            'event_id' => $show->id,
            'date' => '2026-09-03',
            'desktop_views' => 10,
        ]);

        return [$owner, $venue, $show, $booking];
    }

    public function test_a_booking_is_left_out_of_the_checkins_tab(): void
    {
        [$owner, $venue] = $this->scheduleWithAShowAndABooking();

        $stats = app(AnalyticsService::class)->getCheckinStats($owner, $this->start, $this->end, $venue->id);

        // Was 2 sold at a 50% attendance rate: the unscanned booking counted as a missed door.
        $this->assertSame(1, $stats['total_sold']);
        $this->assertSame(1, $stats['total_checked_in']);
        $this->assertEquals(100, $stats['attendance_rate']);
        $this->assertEquals(0, $stats['no_show_rate']);
        $this->assertSame(['The Show'], array_column($stats['events_breakdown'], 'event_name'));
    }

    public function test_a_booking_picked_as_the_single_event_has_no_checkin_data(): void
    {
        [$owner, , , $booking] = $this->scheduleWithAShowAndABooking();

        $stats = app(AnalyticsService::class)->getCheckinStats($owner, $this->start, $this->end, null, $booking->id);

        $this->assertFalse($stats['has_data']);
    }

    public function test_a_booking_is_not_a_conversion_but_its_revenue_counts(): void
    {
        [$owner, $venue] = $this->scheduleWithAShowAndABooking();

        $stats = app(AnalyticsService::class)->getConversionStats($owner, $this->start, $this->end, $venue->id);

        $this->assertSame(10, $stats['total_views'], 'the model accessor shadowed the summed views and read 0');
        $this->assertSame(1, $stats['conversion_sales']);
        $this->assertEquals(10.0, $stats['conversion_rate'], 'one ticket sale over ten views; counting the booking made it 20%');

        // Still a sale and still money: total_sales gates the Revenue cards, and the dashboard
        // shows it beside the revenue figure, so both keep counting the booking.
        $this->assertSame(2, $stats['total_sales']);
        $this->assertEquals(70.0, $stats['total_revenue']);
    }

    public function test_the_single_event_path_leaves_a_booking_out_of_conversions_too(): void
    {
        [$owner, , , $booking] = $this->scheduleWithAShowAndABooking();

        $stats = app(AnalyticsService::class)->getConversionStats($owner, $this->start, $this->end, null, $booking->id);

        $this->assertSame(0, $stats['conversion_sales']);
        $this->assertSame(1, $stats['total_sales']);
        $this->assertEquals(50.0, $stats['total_revenue']);
    }
}
