<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\User;
use App\Services\InstallmentService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the door scanner answers for an ordinary ticket (TicketScanService), and the scanner's event
 * picker (TicketController::scan()).
 *
 * The clock is frozen at 9:30 PM in New York on Wednesday 2026-07-15 (01:30 UTC the next day), so
 * a schedule-local date and the UTC date disagree, which is where a scanner reading the wrong zone
 * goes wrong.
 */
class DoorScanVerdictTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-07-16 01:30:00', 'UTC'));

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue', ['timezone' => 'America/New_York']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** An event on the schedule, starting at a New York wall-clock time. */
    private function show(string $localStart, array $attrs = [], ?Role $role = null): Event
    {
        $role ??= $this->role;

        return $this->createEvent($role, array_merge([
            'starts_at' => Carbon::parse($localStart, 'America/New_York')->setTimezone('UTC')->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ], $attrs));
    }

    private function nightly(string $localStart, array $attrs = []): Event
    {
        return $this->show($localStart, array_merge(['days_of_week' => '1111111', 'recurring_frequency' => 'daily'], $attrs));
    }

    private function ticketFor(Event $event, string $date, int $qty = 2, array $attrs = [], ?Role $role = null): Sale
    {
        $ticket = $this->createTicket($event, ['type' => 'VIP']);

        return $this->createSale($event, $role ?? $this->role, array_merge(['event_date' => $date], $attrs), $ticket, $qty);
    }

    private function scan(Event $event, Sale $sale, array $body = [], ?User $as = null): TestResponse
    {
        return $this->actingAs($as ?? $this->owner)->postJson(route('ticket.scanned', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]), $body)->assertOk();
    }

    private function at(Event $event): array
    {
        return ['scan_event_id' => UrlUtils::encodeId($event->id)];
    }

    /** @return array<int, mixed> every slot of every line, in order */
    private function slots(Sale $sale): array
    {
        return SaleTicket::where('sale_id', $sale->id)->orderBy('id')->get()
            ->flatMap(fn ($line) => array_values(json_decode($line->seats, true) ?? []))
            ->all();
    }

    public function test_a_clean_scan_admits_everyone_on_the_order(): void
    {
        $show = $this->show('2026-07-15 21:00');
        $sale = $this->ticketFor($show, '2026-07-15');

        $json = $this->scan($show, $sale, $this->at($show))->json();

        $this->assertSame('checked_in', $json['verdict']);
        $this->assertSame(2, $json['headcount']);
        $this->assertSame(2, $json['admitted_now']);
        $this->assertSame(trans_choice('messages.scan_admit_people', 2), $json['headcount_label']);
        $this->assertArrayNotHasKey('error', $json);
        // `seats` is the map as it stood before this scan, as it always was.
        $this->assertSame([null, null], array_values($json['tickets'][0]['seats']));
        $this->assertSame('VIP', $json['tickets'][0]['type']);
        $this->assertSame([now()->timestamp, now()->timestamp], $this->slots($sale));
    }

    public function test_a_second_scan_says_when_the_first_one_was(): void
    {
        $show = $this->show('2026-07-15 21:00');
        $sale = $this->ticketFor($show, '2026-07-15');

        Carbon::setTestNow(Carbon::parse('2026-07-16 01:05:00', 'UTC'));
        $this->scan($show, $sale);
        $first = $this->slots($sale);

        Carbon::setTestNow(Carbon::parse('2026-07-16 01:30:00', 'UTC'));
        $json = $this->scan($show, $sale)->json();

        $this->assertSame('already_checked_in', $json['verdict']);
        // The schedule's clock, not UTC (05:05) and not the app's.
        $this->assertSame('9:05 PM', $json['already_checked_in_at']);
        $this->assertSame(trans_choice('messages.scan_people_already_in', 2), $json['already_label']);
        $this->assertSame($first, $this->slots($sale), 'a second scan does not move the first check-in');
    }

    public function test_a_check_in_from_another_day_carries_its_date(): void
    {
        $show = $this->nightly('2026-07-01 20:00', ['duration' => 3]);
        $sale = $this->ticketFor($show, '2026-07-15', 1);
        $yesterday = Carbon::parse('2026-07-14 21:00', 'America/New_York')->timestamp;
        SaleTicket::where('sale_id', $sale->id)->update(['seats' => json_encode([1 => $yesterday])]);

        $label = $this->scan($show, $sale)->json('already_checked_in_at');

        $this->assertStringContainsString('9:00 PM', $label);
        $this->assertNotSame('9:00 PM', $label, 'a check-in on another day names that day');
    }

    public function test_the_clock_follows_the_twelve_or_twenty_four_hour_setting(): void
    {
        $show = $this->show('2026-07-15 21:00');
        $stamp = Carbon::parse('2026-07-15 21:05', 'America/New_York')->timestamp;
        $label = function (?bool $user, bool $schedule) use ($show, $stamp) {
            $this->owner->forceFill(['use_24_hour_time' => $user, 'timezone' => 'Asia/Tokyo'])->save();
            $this->role->forceFill(['use_24_hour_time' => $schedule])->save();
            $sale = $this->ticketFor($show, '2026-07-15', 1);
            SaleTicket::where('sale_id', $sale->id)->update(['seats' => json_encode([1 => $stamp])]);

            return $this->scan($show, $sale)->json('already_checked_in_at');
        };

        $this->assertSame('21:05', $label(true, false));
        $this->assertSame('21:05', $label(null, true));
        $this->assertSame('9:05 PM', $label(false, true));
    }

    public function test_a_part_used_order_admits_only_who_is_left(): void
    {
        $show = $this->show('2026-07-15 21:00');
        $sale = $this->ticketFor($show, '2026-07-15', 2);
        $earlier = Carbon::parse('2026-07-15 21:10', 'America/New_York')->timestamp;
        SaleTicket::where('sale_id', $sale->id)->update(['seats' => json_encode([1 => $earlier, 2 => null])]);

        $json = $this->scan($show, $sale)->json();

        $this->assertSame('partly_checked_in', $json['verdict']);
        $this->assertSame(1, $json['admitted_now']);
        $this->assertSame(1, $json['previously_admitted']);
        $this->assertSame(trans_choice('messages.scan_admit_people', 1), $json['headcount_label']);
        $this->assertSame('9:10 PM', $json['already_checked_in_at']);
    }

    public function test_an_add_on_is_not_a_person(): void
    {
        $show = $this->show('2026-07-15 21:00');
        $sale = $this->ticketFor($show, '2026-07-15', 2);
        $parking = $this->createTicket($show, ['type' => 'Parking', 'is_addon' => true]);
        SaleTicket::create(['sale_id' => $sale->id, 'ticket_id' => $parking->id, 'quantity' => 1, 'seats' => json_encode([1 => null])]);

        $json = $this->scan($show, $sale)->json();

        $this->assertSame(2, $json['headcount']);
        $this->assertTrue($json['tickets'][1]['is_addon']);
    }

    public function test_a_ticket_for_another_event_waits_for_admit_anyway(): void
    {
        $tonight = $this->show('2026-07-15 21:00', ['name' => 'Tonight']);
        $tomorrow = $this->show('2026-07-16 20:00', ['name' => 'Tomorrow']);
        $sale = $this->ticketFor($tomorrow, '2026-07-16');

        $json = $this->scan($tomorrow, $sale, $this->at($tonight))->json();

        $this->assertSame('other_event', $json['verdict']);
        $this->assertSame('other_event', $json['override']);
        $this->assertSame('Tomorrow', $json['event']);
        // A scanner opened before `verdict` existed reads `error` and must not paint this green.
        $this->assertSame(__('messages.scan_other_event'), $json['error']);
        $this->assertSame(trans_choice('messages.scan_ticket_admits', 2), $json['headcount_label']);
        $this->assertSame([null, null], $this->slots($sale), 'not checked in until someone says so');

        $json = $this->scan($tomorrow, $sale, $this->at($tonight) + ['overrides' => ['other_event']])->json();

        $this->assertSame('checked_in', $json['verdict']);
        $this->assertSame(['other_event'], $json['overrides_applied']);
        $this->assertSame([now()->timestamp, now()->timestamp], $this->slots($sale));
        $this->assertTrue(AuditLog::where('model_type', 'Sale')->where('model_id', $sale->id)
            ->where('metadata', 'like', 'admit_anyway:%')->exists());
    }

    public function test_without_a_picker_a_ticket_is_checked_against_its_own_event(): void
    {
        $tomorrow = $this->show('2026-07-16 20:00');
        $sale = $this->ticketFor($tomorrow, '2026-07-16');

        $this->assertSame('checked_in', $this->scan($tomorrow, $sale)->json('verdict'));
    }

    public function test_a_picker_that_is_not_the_operators_is_ignored(): void
    {
        $stranger = $this->createOwner();
        $theirs = $this->show('2026-07-15 21:00', [], $this->createRole($stranger));
        $show = $this->show('2026-07-15 21:00');

        foreach ([$this->at($theirs), ['scan_event_id' => 'not-an-id'], ['scan_event_id' => ['x']]] as $body) {
            $sale = $this->ticketFor($show, '2026-07-15');
            $this->assertSame('checked_in', $this->scan($show, $sale, $body)->json('verdict'));
        }
    }

    public function test_tomorrows_ticket_of_a_nightly_show_waits_for_admit_anyway(): void
    {
        $nightly = $this->nightly('2026-07-01 20:00', ['duration' => 3]);
        $tomorrows = $this->ticketFor($nightly, '2026-07-16');
        $tonights = $this->ticketFor($nightly, '2026-07-15');

        $json = $this->scan($nightly, $tomorrows, $this->at($nightly))->json();

        $this->assertSame('other_date', $json['verdict']);
        $this->assertSame('other_date', $json['override']);
        $this->assertStringContainsString('Jul 16', $json['date']);
        $this->assertStringContainsString('Jul 15', $json['door_date']);
        $this->assertSame([null, null], $this->slots($tomorrows));

        $this->assertSame('checked_in', $this->scan($nightly, $tomorrows, $this->at($nightly) + ['overrides' => ['other_date']])->json('verdict'));
        $this->assertSame('checked_in', $this->scan($nightly, $tonights, $this->at($nightly))->json('verdict'));
    }

    public function test_a_show_after_midnight_is_on_its_own_date_the_evening_before(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-16 03:30:00', 'UTC')); // 11:30 PM in New York
        $late = $this->nightly('2026-07-01 01:00');
        $sale = $this->ticketFor($late, '2026-07-16');

        $this->assertSame('checked_in', $this->scan($late, $sale, $this->at($late))->json('verdict'));
    }

    public function test_day_two_of_a_festival_is_not_another_date(): void
    {
        $festival = $this->show('2026-07-14 12:00', ['duration' => 72]);
        $sale = $this->ticketFor($festival, '2026-07-15');

        $this->assertSame('checked_in', $this->scan($festival, $sale, $this->at($festival))->json('verdict'));
    }

    public function test_no_override_reaches_a_red_answer(): void
    {
        $all = ['overrides' => ['other_event', 'other_date', 'overdue']];
        $show = $this->show('2026-07-15 21:00');

        $cancelled = $this->ticketFor($show, '2026-07-15', 1, ['status' => 'cancelled']);
        $json = $this->scan($show, $cancelled, $this->at($show) + $all)->json();
        $this->assertSame('error', $json['verdict']);
        $this->assertSame(__('messages.this_ticket_is_cancelled'), $json['error']);
        $this->assertSame([null], $this->slots($cancelled));

        $nextWeek = $this->show('2026-07-22 21:00');
        $early = $this->ticketFor($nextWeek, '2026-07-22', 1);
        $json = $this->scan($nextWeek, $early, $this->at($show) + $all)->json();
        $this->assertSame('too_early', $json['reason']);
        $this->assertStringContainsString('Jul 22', $json['date'], 'the red card says which night the ticket is for');
        $this->assertSame([null], $this->slots($early));

        $stranger = $this->createOwner();
        $strangersRole = $this->createRole($stranger);
        $theirs = $this->show('2026-07-15 21:00', [], $strangersRole);
        $notMine = $this->ticketFor($theirs, '2026-07-15', 1, [], $strangersRole);
        $json = $this->scan($theirs, $notMine, $all)->json();
        $this->assertSame(__('messages.you_are_not_authorized_to_scan_this_ticket'), $json['error']);
        $this->assertSame([null], $this->slots($notMine));
    }

    public function test_an_overdue_plan_waits_for_admit_anyway_and_then_checks_in(): void
    {
        [$event, $sale] = $this->overdueOrder($this->show('2026-07-15 21:00', $this->installmentFields()));

        $json = $this->scan($event, $sale, $this->at($event))->json();

        $this->assertSame('overdue', $json['verdict']);
        $this->assertSame('overdue', $json['payment_status']);
        $this->assertSame('overdue', $json['override']);
        $this->assertArrayNotHasKey('error', $json);
        $this->assertSame([null], $this->slots($sale));

        $json = $this->scan($event, $sale, $this->at($event) + ['overrides' => ['overdue']])->json();

        $this->assertSame('checked_in', $json['verdict']);
        $this->assertArrayNotHasKey('payment_status', $json);
        $this->assertSame(['overdue'], $json['overrides_applied']);
        $this->assertSame([now()->timestamp], $this->slots($sale));
    }

    public function test_the_stops_come_one_at_a_time_in_order(): void
    {
        [$nightly, $sale] = $this->overdueOrder(
            $this->nightly('2026-07-01 20:00', ['duration' => 3] + $this->installmentFields()),
            '2026-07-16'
        );
        $body = $this->at($nightly);

        $this->assertSame('other_date', $this->scan($nightly, $sale, $body)->json('verdict'));
        $this->assertSame('overdue', $this->scan($nightly, $sale, $body + ['overrides' => ['other_date']])->json('verdict'));

        $json = $this->scan($nightly, $sale, $body + ['overrides' => ['other_date', 'overdue']])->json();
        $this->assertSame('checked_in', $json['verdict']);
        $this->assertSame(['other_date', 'overdue'], $json['overrides_applied']);
    }

    public function test_malformed_overrides_are_ignored(): void
    {
        $tonight = $this->show('2026-07-15 21:00');
        $tomorrow = $this->show('2026-07-16 20:00');
        $sale = $this->ticketFor($tomorrow, '2026-07-16');

        foreach ([['overrides' => [['other_event']]], ['overrides' => ['nonsense' => 1]], ['overrides' => 'admit']] as $junk) {
            $this->assertSame('other_event', $this->scan($tomorrow, $sale, $this->at($tonight) + $junk)->json('verdict'));
        }
    }

    public function test_the_picker_puts_today_first_and_labels_a_series_with_tonight(): void
    {
        $nightly = $this->nightly('2025-01-01 20:00', ['name' => 'Nightly']);
        $tonight = $this->show('2026-07-15 21:00', ['name' => 'Tonight']);
        $this->show('2026-07-22 21:00', ['name' => 'Next week']);
        $this->show('2026-07-17 21:00', ['name' => 'Friday']);
        $this->show('2026-06-01 21:00', ['name' => 'June']);

        $response = $this->actingAs($this->owner)->get(route('ticket.scan'))->assertOk();
        $events = collect($response->viewData('events'));

        $this->assertSame(['Nightly', 'Tonight', 'Friday', 'Next week', 'June'], $events->pluck('name')->all());
        $this->assertSame(['today', 'today', 'upcoming', 'upcoming', 'past'], $events->pluck('when')->all());
        $this->assertSame('Wed, Jul 15, 2026', $events->first()['starts_at'], 'a series shows tonight, not its first night');
        // Nothing sold today: the event on today whose door is still open (the series runs to 10 PM).
        $this->assertSame(UrlUtils::encodeId($nightly->id), $response->viewData('selectedEventId'));

        // A sale for tonight wins.
        $this->ticketFor($tonight, '2026-07-15');
        $this->assertSame(UrlUtils::encodeId($tonight->id),
            $this->actingAs($this->owner)->get(route('ticket.scan'))->viewData('selectedEventId'));
    }

    public function test_the_page_carries_its_words_and_never_prints_a_raw_error(): void
    {
        $html = $this->actingAs($this->owner)->get(route('ticket.scan'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<div id="reader"'));
        $this->assertStringContainsString(json_encode(__('messages.scan_could_not_check')), $html);
        $this->assertStringNotContainsString('HTTP Error', $html);
        // The old page showed a failed request's own text ("Failed to fetch", "HTTP Error: 419").
        $this->assertStringNotContainsString('errorMessage = error', $html);
        // The library's own UI is gone: the page drives the camera itself.
        $this->assertStringNotContainsString('new Html5QrcodeScanner', $html);
    }

    /** @return array<string, mixed> */
    private function installmentFields(): array
    {
        return ['payment_method' => 'stripe', 'installments_enabled' => true, 'installment_count' => 4];
    }

    /** @return array{0: Event, 1: Sale} */
    private function overdueOrder(Event $event, string $date = '2026-07-15'): array
    {
        $this->owner->forceFill(['stripe_account_id' => 'acct_merchant'])->save();
        $ticket = $this->createTicket($event, ['price' => 1000, 'quantity' => 10]);
        $sale = $this->createSale($event, $this->role, [
            'status' => 'paid', 'payment_amount' => 1000, 'email' => 'buyer@gmail.com', 'event_date' => $date,
        ], $ticket);

        $plan = app(InstallmentService::class)->createPlan($sale, $event, 1000.00, 'USD');
        $plan->update(['amount_paid' => 250, 'status' => 'delinquent', 'delinquent_at' => now()]);

        return [$event, $sale->fresh()];
    }
}
