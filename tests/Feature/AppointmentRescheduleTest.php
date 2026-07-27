<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Sale;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Rescheduling MOVES the existing Event and Sale rather than cancel-and-rebook, so most of what these
 * tests pin is what must survive the move (sale id, secret, payment, analytics) and what must be
 * rewritten alongside `starts_at` (duration, event_date, ticket.sold, ical_sequence, reminder_sent_at).
 */
class AppointmentRescheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): AppointmentService
    {
        return app(AppointmentService::class);
    }

    private function allDays(string $start = '09:00', string $end = '17:00'): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => $start, 'end' => $end]]);
    }

    /**
     * Books through the real service, so the Event + inventory Ticket + Sale + SaleTicket all exist.
     * ReleaseReviewFixesTest::bookingAt() hand-builds only an Event and Sale, which would make the
     * ticket.sold assertion below pass vacuously.
     *
     * @return array{0:\App\Models\Role, 1:\App\Models\AppointmentType, 2:Sale, 3:Event}
     */
    private function booking(array $typeAttrs = [], array $guest = []): array
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, array_merge(['weekly_windows' => $this->allDays()], $typeAttrs));

        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = $this->service()->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];

        $sale = $this->service()->book($type, $role, array_merge([
            'name' => 'Jane Guest',
            'email' => 'jane@gmail.com',
            'slot' => $slot,
            'guest_timezone' => 'America/New_York',
        ], $guest));

        return [$role, $type->fresh(), $sale->fresh(), $sale->event->fresh()];
    }

    /** A different open slot on the same day, excluding the booking's own event. */
    private function otherSlot($type, Event $event): string
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')
            ->setTimezone($type->timezone())->format('Y-m-d');

        $slots = $this->service()->availableSlots($type, $date, 1, null, true, $event->id)['days'][$date] ?? [];
        foreach ($slots as $slot) {
            if ($slot['utc'] !== Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z')) {
                return $slot['utc'];
            }
        }

        $this->fail('no alternative slot available in the fixture');
    }

    /**
     * Steps a booking outside the reschedule cooldown. Only needed by tests that deliberately perform a
     * SECOND move - a first move is never blocked, which is the point of keying on rescheduled_at.
     */
    private function clearCooldown(Event $event): void
    {
        Event::whereKey($event->id)->update(['rescheduled_at' => now()->subHour()]);
    }

    /**
     * Assert a move is refused for a SPECIFIC reason.
     *
     * reschedule() has five BusinessException guards and they run in a fixed order. Asserting only the
     * exception class meant a reordering that tripped the wrong guard first - showing the guest an
     * unrelated message - would pass every one of these tests.
     */
    private function assertMoveRefused(callable $move, string $expectedMessage, ?string $expectedClass = null): void
    {
        try {
            $move();
            $this->fail('the move should have been refused');
        } catch (BusinessException $e) {
            $this->assertSame($expectedMessage, $e->getMessage(), 'the wrong guard fired');
            if ($expectedClass) {
                $this->assertInstanceOf($expectedClass, $e,
                    'the exception TYPE decides whether the picker gets a slot-recovery payload');
            }
        }
    }

    public function test_moves_the_event_and_keeps_the_sale_identity(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);
        $oldStartsAt = $event->starts_at;
        $oldSequence = (int) $event->ical_sequence;
        $saleIdBefore = $sale->id;
        $secretBefore = $sale->secret;

        $returned = $this->service()->reschedule($sale, $target);

        $this->assertSame($oldStartsAt, $returned, 'returns the OLD start so the mail can say "moved from"');

        $event->refresh();
        $sale->refresh();

        $this->assertSame(
            Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $target, 'UTC')->format('Y-m-d H:i:s'),
            $event->starts_at
        );
        $this->assertSame($oldSequence + 1, (int) $event->ical_sequence);

        // The sale is the same ROW. assertNotNull would have passed even if reschedule() regenerated the
        // secret, which would silently break every manage/reschedule/ical link already in the guest's
        // inbox - so compare against the values captured before the move.
        $this->assertSame(1, Sale::where('event_id', $event->id)->count());
        $this->assertSame($saleIdBefore, $sale->id, 'a move must not create a new sale');
        $this->assertSame($secretBefore, $sale->secret, 'the secret is the guest\'s only credential');
    }

    public function test_event_date_and_ticket_sold_follow_the_move(): void
    {
        // A window spanning midnight-adjacent hours is not needed; just move across a day boundary.
        [, $type, $sale, $event] = $this->booking();

        $oldDate = $sale->event_date;
        $nextDay = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')
            ->setTimezone($type->timezone())->addDay()->format('Y-m-d');
        $slots = $this->service()->availableSlots($type, $nextDay, 1, null, true, $event->id)['days'][$nextDay];

        $this->service()->reschedule($sale, $slots[0]['utc']);

        // Checked WITHOUT refresh(): the service refreshes the caller's instance, because the cascade
        // rewrites sales.event_date behind our back and anything building a guest URL or mail off this
        // object afterwards would otherwise describe the old date.
        $this->assertNotSame($oldDate, $sale->event_date);
        $this->assertSame($nextDay, $sale->event_date);

        $sale->refresh();
        $this->assertSame($nextDay, $sale->event_date);

        // The Event::saving cascade re-keys the inventory. This is what breaks if the write is ever
        // switched to saveQuietly(), and it is why the sale update must come after the event write.
        $ticket = $event->fresh()->tickets->first();
        $sold = json_decode($ticket->sold, true);
        $this->assertSame([$nextDay => 1], $sold);
    }

    public function test_reminder_is_rearmed_and_guest_timezone_refreshed(): void
    {
        [, $type, $sale, $event] = $this->booking();
        Sale::whereKey($sale->id)->update(['reminder_sent_at' => now()->subHour()]);

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event), 'guest', null, 'Europe/Paris');

        $sale->refresh();
        // The reminder latch is permanent, so without clearing it the guest gets no reminder for the
        // new time at all.
        $this->assertNull($sale->reminder_sent_at);
        $this->assertSame('Europe/Paris', $sale->guest_timezone);
    }

    public function test_a_garbage_guest_timezone_is_ignored_rather_than_stored(): void
    {
        [, $type, $sale, $event] = $this->booking();

        $this->service()->reschedule($sale, $this->otherSlot($type, $event), 'guest', null, 'Not/AZone');

        $this->assertSame('America/New_York', $sale->fresh()->guest_timezone);
    }

    public function test_duration_is_rewritten_to_the_types_current_length(): void
    {
        [, $type, $sale, $event] = $this->booking(['duration_minutes' => 30]);
        $this->assertSame(0.5, (float) $event->duration);

        // The owner lengthens the type after the booking was made.
        $type->forceFill(['duration_minutes' => 60])->save();
        $target = $this->otherSlot($type->fresh(), $event);

        $this->service()->reschedule($sale, $target);

        // Leaving 0.5 here would let busyIntervals() free the second half hour of a 60-minute slot,
        // so a second guest could book the overlap.
        $this->assertSame(1.0, (float) $event->fresh()->duration);
    }

    public function test_a_paid_booking_keeps_its_payment(): void
    {
        [, $type, $sale, $event] = $this->booking(['price' => 50, 'currency_code' => 'USD', 'payment_method' => 'stripe']);
        Sale::whereKey($sale->id)->update(['status' => 'paid', 'transaction_reference' => 'pi_test_123']);

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event));

        $sale->refresh();
        $this->assertSame('paid', $sale->status);
        $this->assertSame('pi_test_123', $sale->transaction_reference);
        $this->assertEquals(50.0, (float) $sale->payment_amount);
    }

    public function test_guest_move_on_an_approval_type_returns_to_pending_and_clears_the_latch(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);

        // Approve it first, so we are moving a CONFIRMED booking.
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $this->service()->confirm($sale->fresh());
        $this->assertNotNull($sale->fresh()->confirmed_at);

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event), 'guest');

        $pivot = $event->fresh()->roles()->where('roles.id', $role->id)->first()->pivot;
        $this->assertNull($pivot->is_accepted);
        // confirm() is a one-shot latch on confirmed_at; leaving it set means re-approval silently
        // sends nothing and never syncs the calendar.
        $this->assertNull($sale->fresh()->confirmed_at);
    }

    public function test_owner_move_on_an_approval_type_stays_confirmed(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $this->service()->confirm($sale->fresh());

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event), 'owner');

        // An owner should not have to approve their own action.
        $pivot = $event->fresh()->roles()->where('roles.id', $role->id)->first()->pivot;
        $this->assertTrue((bool) $pivot->is_accepted);
        $this->assertNotNull($sale->fresh()->confirmed_at);
    }

    /**
     * The back-to-pending branch is `$initiator === 'guest' && $type->requires_approval && $wasAccepted`.
     * The positive case and the initiator guard are covered above; this isolates the TYPE-FLAG guard.
     *
     * Fixture: an APPROVED booking on a type whose owner has since switched approval off. Nothing should
     * un-approve it. The previous version of this test used an unapproved booking, so pivot and
     * confirmed_at were already null before the call and both assertions passed with the entire branch
     * condition deleted.
     */
    public function test_approval_switched_off_since_booking_does_not_unapprove_it(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);

        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $this->service()->confirm($sale->fresh());
        $this->assertNotNull($sale->fresh()->confirmed_at, 'fixture premise: it starts CONFIRMED');

        // The owner switches approval off after the fact.
        $type->forceFill(['requires_approval' => false])->save();

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type->fresh(), $event), 'guest');

        // A type that no longer requires approval must not send a confirmed booking back for one.
        $pivot = $event->fresh()->roles()->where('roles.id', $role->id)->first()->pivot;
        $this->assertTrue((bool) $pivot->is_accepted, 'the booking must stay approved');
        $this->assertNotNull($sale->fresh()->confirmed_at, 'and stay confirmed');
    }

    /**
     * And this isolates the LIVE-PIVOT guard ($wasAccepted).
     *
     * A booking that confirm() never confirmed must not have its latch cleared by a move, or re-approval
     * would mail a "you're booked" for something nobody ever approved. confirmed_at is written directly
     * here because that pairing - a set latch with a null pivot - is precisely the drift the guard exists
     * to survive, and no ordinary flow produces it.
     */
    public function test_a_move_does_not_clear_the_latch_on_a_booking_that_was_never_approved(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);

        $this->assertNull($event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
        Sale::whereKey($sale->id)->update(['confirmed_at' => now()->subHour()]);

        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event), 'guest');

        $this->assertNotNull(
            $sale->fresh()->confirmed_at,
            'the pivot was never accepted, so there is no confirmation to re-arm'
        );
    }

    public function test_picking_the_current_slot_is_a_no_op(): void
    {
        [, , $sale, $event] = $this->booking();
        $current = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');
        $sequence = (int) $event->ical_sequence;

        $this->service()->reschedule($sale, $current);

        // No bump means no calendar churn and no "moved from X to X" mail.
        $this->assertSame($sequence, (int) $event->fresh()->ical_sequence);
    }

    public function test_a_stale_from_slot_is_rejected(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);

        // The page thought the booking was somewhere it is not - a second tab, targeting a different
        // slot (a replay onto the SAME slot is a success; see the replay test).
        $this->assertMoveRefused(
            fn () => $this->service()->reschedule($sale, $target, 'guest', '2020-01-01T10:00:00Z'),
            __('messages.appointments_slot_taken'),
            \App\Exceptions\SlotUnavailableException::class
        );
    }

    public function test_the_cooldown_rejects_a_rapid_second_move(): void
    {
        [, $type, $sale, $event] = $this->booking();

        $this->service()->reschedule($sale, $this->otherSlot($type, $event));

        // rescheduled_at is now, so a second move immediately after is refused. This is what bounds the
        // per-move owner mail and the inline calendar API calls.
        $this->assertMoveRefused(
            fn () => $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event->fresh())),
            __('messages.appointments_reschedule_too_soon')
        );

        // NOT a SlotUnavailableException: the picker must not wipe the day for a non-availability error.
        try {
            $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event->fresh()));
        } catch (BusinessException $e) {
            $this->assertNotInstanceOf(\App\Exceptions\SlotUnavailableException::class, $e);
        }
    }

    public function test_a_cancelled_booking_cannot_be_moved(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);

        $sale->status = 'cancelled';
        $sale->save();

        $this->assertMoveRefused(
            fn () => $this->service()->reschedule($sale->fresh(), $target),
            __('messages.appointments_reschedule_unavailable', ['schedule' => $sale->event->creatorRole->name])
        );
    }

    public function test_a_past_booking_cannot_be_moved(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);
        Event::whereKey($event->id)->update([
            'starts_at' => now('UTC')->subDay()->format('Y-m-d H:i:s'),
            'updated_at' => now()->subHour(),
        ]);

        $this->assertMoveRefused(
            fn () => $this->service()->reschedule($sale->fresh(), $target),
            __('messages.appointments_reschedule_unavailable', ['schedule' => $sale->event->creatorRole->name])
        );
    }

    public function test_a_taken_slot_is_rejected_but_the_bookings_own_slot_is_not(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);

        // Someone else takes the target between render and submit.
        $this->service()->book($type, $role, [
            'name' => 'Other', 'email' => 'other@gmail.com', 'slot' => $target, 'guest_timezone' => 'America/New_York',
        ]);

        $this->assertMoveRefused(
            fn () => $this->service()->reschedule($sale->fresh(), $target),
            __('messages.appointments_slot_taken'),
            \App\Exceptions\SlotUnavailableException::class
        );

        // The second clause the old name promised but never checked: the booking's OWN slot is still
        // selectable, so the guest can always keep the time they have.
        $ownSlot = Carbon::createFromFormat('Y-m-d H:i:s', $event->fresh()->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');
        $this->assertTrue(
            $this->service()->isSlotAvailable($type, $ownSlot, null, $event->id),
            "the booking's own slot must not block its own move"
        );
    }

    // ---------------------------------------------------------------- guest HTTP endpoints

    private function guestUrls(Event $event, Sale $sale): array
    {
        $params = ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret];

        return [
            'page' => route('appointments.reschedule', $params),
            'post' => route('appointments.reschedule.store', $params),
            'slots' => route('appointments.reschedule_slots', $params),
            'manage' => route('appointments.manage', $params),
        ];
    }

    public function test_the_guest_page_renders_the_picker_in_reschedule_mode(): void
    {
        [, , $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);

        $html = $this->get($urls['page'])->assertOk()->getContent();

        $this->assertStringContainsString('booking-app', $html);
        $this->assertStringContainsString('&quot;mode&quot;:&quot;reschedule&quot;', $html);
        // It must post to the secret-link endpoint, never the public booking one.
        $this->assertStringContainsString('appointment\/reschedule', $html);
    }

    /**
     * The URL carries the secret, so this page must never be indexed - unlike public /book.
     *
     * Asserting `noindex` on the reschedule page alone proves nothing: the layout also emits it for any
     * is_private event, and every booking is private, so the :noIndex binding could be deleted and the
     * assertion would still pass. The public /book page is the control - it must NOT be noindexed.
     */
    public function test_only_the_secret_bearing_page_is_noindexed(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        $this->get($this->guestUrls($event, $sale)['page'])
            ->assertOk()
            ->assertSee('noindex', false);

        $this->get(route('appointments.book_type', [
            'subdomain' => $role->subdomain, 'typeSlug' => $type->slug,
        ]))->assertOk()->assertDontSee('noindex', false);
    }

    /**
     * The requester already proved possession of the secret, so a challenge is pure friction - and it
     * would put a cross-origin Cloudflare script on a secret-bearing page.
     *
     * Turnstile keys have to be CONFIGURED for this to mean anything: phpunit.xml sets none, so
     * turnstileEnabled is false everywhere and the `! $isReschedule` term could be deleted unnoticed.
     * The public booking page is the control that shows the keys are live.
     */
    public function test_turnstile_is_forced_off_on_the_reschedule_page_only(): void
    {
        config(['services.turnstile.site_key' => 'test-site-key', 'services.turnstile.secret_key' => 'test-secret']);

        [$role, $type, $sale, $event] = $this->booking();

        // Control: with keys configured the public page really does challenge.
        $public = $this->get(route('appointments.book_type', [
            'subdomain' => $role->subdomain, 'typeSlug' => $type->slug,
        ]))->assertOk()->getContent();
        $this->assertStringContainsString('&quot;turnstileEnabled&quot;:true', $public, 'fixture premise');
        $this->assertStringContainsString('challenges.cloudflare.com', $public);

        // The secret-bearing page must not.
        $resched = $this->get($this->guestUrls($event, $sale)['page'])->assertOk()->getContent();
        $this->assertStringContainsString('&quot;turnstileEnabled&quot;:false', $resched);
        $this->assertStringNotContainsString('challenges.cloudflare.com', $resched);
    }

    public function test_the_guest_slots_endpoint_excludes_the_booking_and_ignores_a_supplied_id(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);
        $date = $sale->event_date;

        $own = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');

        $slots = $this->getJson($urls['slots'].'?from='.$date.'&days=1')->assertOk()->json('days.'.$date);
        $this->assertContains($own, collect($slots)->pluck('utc')->all(), 'own slot must be offered back');

        // A caller-supplied exclusion must be ignored: busyIntervals covers inbound-synced private
        // events, so honouring it would read back the owner's personal calendar.
        $other = $this->createEvent($role, [
            'starts_at' => Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->addHours(2)->format('Y-m-d H:i:s'),
            'duration' => 1, 'is_accepted' => true,
        ]);
        $blocked = Carbon::createFromFormat('Y-m-d H:i:s', $other->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');

        $withAttempt = $this->getJson($urls['slots'].'?from='.$date.'&days=1&excludeEventId='.$other->id)
            ->assertOk()->json('days.'.$date);
        $this->assertNotContains($blocked, collect($withAttempt)->pluck('utc')->all());
    }

    public function test_the_guest_post_moves_the_booking_and_returns_json(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);
        $target = $this->otherSlot($type, $event);
        $from = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');

        $response = $this->postJson($urls['post'], [
            'slot' => $target,
            'from_slot' => $from,
            'guest_timezone' => 'Europe/Paris',
        ])->assertOk();

        // Always JSON: a redirect lands in the picker's parse-failure branch and tells the guest their
        // session expired, which is wrong and unrecoverable-looking.
        $this->assertStringContainsString('moved=1', $response->json('redirect_url'));
        $this->assertSame(
            Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $target, 'UTC')->format('Y-m-d H:i:s'),
            $event->fresh()->starts_at
        );
        $this->assertSame('Europe/Paris', $sale->fresh()->guest_timezone);
    }

    public function test_a_race_returns_the_refreshed_day_for_the_pickers_recovery(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);
        $target = $this->otherSlot($type, $event);

        // Someone else takes it between render and submit.
        $this->service()->book($type, $role, [
            'name' => 'Other', 'email' => 'other@gmail.com', 'slot' => $target, 'guest_timezone' => 'America/New_York',
        ]);

        $response = $this->postJson($urls['post'], ['slot' => $target])->assertStatus(422);
        $this->assertNotEmpty($response->json('error'));
        // The recovery payload must still contain the booking's OWN slot, or the picker erases it from
        // the grid when it redraws the day.
        $own = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');
        $date = $sale->fresh()->event_date;
        $this->assertContains($own, collect($response->json('slots.days.'.$date))->pluck('utc')->all());
    }

    public function test_a_wrong_secret_and_a_deleted_sale_both_404(): void
    {
        [, , $sale, $event] = $this->booking();
        $encoded = \App\Utils\UrlUtils::encodeId($event->id);

        $this->get(route('appointments.reschedule', ['event_id' => $encoded, 'secret' => str_repeat('x', 32)]))
            ->assertNotFound();

        Sale::whereKey($sale->id)->update(['is_deleted' => true]);
        $this->get(route('appointments.reschedule', ['event_id' => $encoded, 'secret' => $sale->secret]))
            ->assertNotFound();
    }

    public function test_an_unpaid_card_hold_is_blocked_with_an_explanation(): void
    {
        [, , $sale, $event] = $this->booking(['price' => 40, 'currency_code' => 'USD', 'payment_method' => 'stripe']);
        $urls = $this->guestUrls($event, $sale);

        // The hold expires on its CREATION clock, so moving it would hand out a slot that dies.
        $this->assertSame('unpaid', $sale->status);
        $this->get($urls['page'])->assertRedirect($urls['manage']);
        $this->postJson($urls['post'], ['slot' => Carbon::now('UTC')->addDays(2)->format('Y-m-d\TH:i:s\Z')])
            ->assertStatus(422)
            ->assertJsonPath('error', __('messages.appointments_reschedule_blocked_payment'));
    }

    public function test_a_deactivated_type_blocks_the_move_but_not_the_manage_page(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);

        $type->forceFill(['is_active' => false])->save();

        $this->get($urls['page'])->assertRedirect($urls['manage']);
        // Cancel stays reachable, so a guest is never trapped by the owner's settings.
        $this->get($urls['manage'])->assertOk();
    }

    // ---------------------------------------------------------------- owner HTTP endpoints

    private function ownerUrls($role, Sale $sale): array
    {
        $params = ['subdomain' => $role->subdomain, 'saleHash' => \App\Utils\UrlUtils::encodeId($sale->id)];

        return [
            'page' => route('appointments.booking_reschedule', $params),
            'post' => route('appointments.booking_reschedule.store', $params),
            'slots' => route('appointments.booking_reschedule_slots', $params),
        ];
    }

    public function test_the_owner_page_renders_in_owner_mode(): void
    {
        [$role, , $sale, $event] = $this->booking();
        $owner = $role->user;

        $html = $this->actingAs($owner)->get($this->ownerUrls($role, $sale)['page'])->assertOk()->getContent();

        $this->assertStringContainsString('&quot;mode&quot;:&quot;reschedule&quot;', $html);
        $this->assertStringContainsString('&quot;ownerMode&quot;:true', $html);
        // The owner sees the guest's name so they know whose booking they are moving.
        $this->assertStringContainsString('Jane Guest', $html);
    }

    public function test_the_owner_picker_relaxes_min_notice(): void
    {
        // Book first with no notice requirement, then impose one - the guest path could not create a
        // booking inside its own notice window in the first place.
        [$role, $type, $sale, $event] = $this->booking();
        $type->forceFill(['min_notice_hours' => 48])->save();
        $type = $type->fresh();
        $owner = $role->user;
        $date = $sale->event_date;

        $guestSlots = $this->service()->availableSlots($type, $date, 1, null, true, $event->id)['days'][$date] ?? [];
        $this->assertEmpty($guestSlots, 'guest path is correctly empty inside the notice window');

        $ownerSlots = $this->actingAs($owner)
            ->getJson($this->ownerUrls($role, $sale)['slots'].'?from='.$date.'&days=1')
            ->assertOk()->json('days.'.$date);
        $this->assertNotEmpty($ownerSlots, 'owner path must still offer the day');
    }

    public function test_the_owner_move_notifies_the_guest_and_can_be_suppressed(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        config(['app.hosted' => false, 'mail.default' => 'smtp']);

        [$role, $type, $sale, $event] = $this->booking();
        $owner = $role->user;
        $urls = $this->ownerUrls($role, $sale);

        $this->actingAs($owner)->postJson($urls['post'], [
            'slot' => $this->otherSlot($type, $event),
            'note' => 'Clinic emergency, sorry.',
            'notify' => true,
        ])->assertOk();

        $guestMails = $this->queuedFor(\App\Mail\AppointmentRescheduled::class);
        $this->assertCount(1, $guestMails);

        // "Don't notify" suppresses the guest mail only - the move itself still happens.
        \Illuminate\Support\Facades\Queue::fake();
        $event->refresh();
        $this->clearCooldown($event);
        $before = $event->fresh()->starts_at;

        $this->actingAs($owner)->postJson($urls['post'], [
            'slot' => $this->otherSlot($type, $event->fresh()),
            'notify' => false,
        ])->assertOk();

        $this->assertNotSame($before, $event->fresh()->starts_at, 'the booking still moved');
        $this->assertCount(0, $this->queuedFor(\App\Mail\AppointmentRescheduled::class));
    }

    /**
     * sales.subdomain is a booking-time snapshot that is never rewritten on rename, so a schedule that
     * claims a freed subdomain must not inherit the previous owner's bookings.
     */
    public function test_a_booking_from_another_schedule_is_not_reschedulable(): void
    {
        [$roleA, , $saleA] = $this->booking();

        $ownerB = $this->createOwner();
        $roleB = $this->createRole($ownerB, 'talent', ['timezone' => 'America/New_York']);

        // Reproduce the drift: A renames, B takes the old subdomain, so sales.subdomain now matches B.
        $freed = $roleA->subdomain;
        $roleA->forceFill(['subdomain' => $freed.'-renamed'])->save();
        $roleB->forceFill(['subdomain' => $freed])->save();
        $this->assertSame($roleB->subdomain, $saleA->subdomain, 'the drift is real');

        $this->actingAs($ownerB)
            ->get(route('appointments.booking_reschedule', [
                'subdomain' => $roleB->subdomain, 'saleHash' => \App\Utils\UrlUtils::encodeId($saleA->id),
            ]))
            ->assertNotFound();
    }

    /**
     * gate() requires isEditor(), which checks the pivot LEVEL is owner/admin. A stranger with no
     * membership at all is rejected by the earlier no-user branch, so testing one never reaches the level
     * check - a regression widening editorship to viewer or writer would have gone unnoticed.
     */
    public function test_a_viewer_level_member_cannot_use_the_owner_endpoints(): void
    {
        [$role, , $sale] = $this->booking();
        $urls = $this->ownerUrls($role, $sale);

        // The full set of non-editor levels the enum allows.
        foreach (['viewer', 'follower'] as $level) {
            $member = $this->createOwner();
            $role->users()->attach($member->id, ['level' => $level]);

            $this->actingAs($member)->get($urls['page'])->assertForbidden();
            $this->actingAs($member)->postJson($urls['post'], ['slot' => '2030-01-01T10:00:00Z'])->assertForbidden();
        }

        // And a stranger with no membership at all, which is the case the old test actually covered.
        $this->actingAs($this->createOwner())->get($urls['page'])->assertForbidden();
    }

    /**
     * Let owner notifications actually dispatch.
     *
     * EmailService::appointmentCanSend() refuses on hosted without per-schedule email settings, and on
     * selfhost when the mailer is log/array - and phpunit.xml sets MAIL_MAILER=array. So in the default
     * test environment the owner half of every appointment notification is silently skipped, which is
     * exactly why it had no coverage. Safe with Queue::fake(): the job is recorded, never executed, so no
     * transport is ever opened.
     */
    private function enableOwnerNotifications(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
    }

    /**
     * The (kind, recipient) pairs of every queued owner notification. $kind is protected, so reflection -
     * asserting on the kind is the only way to tell a "booking moved" notice from a plain new request.
     *
     * @return array<int, array{kind: string, to: string}>
     */
    private function queuedOwnerNotifications(): array
    {
        $found = [];
        foreach (\Illuminate\Support\Facades\Queue::pushedJobs() as $jobs) {
            foreach ($jobs as $job) {
                $payload = $job['job'] ?? null;
                if (! $payload instanceof \App\Jobs\SendQueuedEmail) {
                    continue;
                }
                $mailable = null;
                $to = null;
                foreach ((new \ReflectionClass($payload))->getProperties() as $prop) {
                    $prop->setAccessible(true);
                    $value = $prop->getValue($payload);
                    if ($value instanceof \App\Mail\AppointmentBookedNotification) {
                        $mailable = $value;
                    } elseif (is_string($value) && str_contains($value, '@')) {
                        $to ??= $value;
                    }
                }
                if ($mailable) {
                    $kindProp = new \ReflectionProperty($mailable, 'kind');
                    $kindProp->setAccessible(true);
                    $found[] = ['kind' => $kindProp->getValue($mailable), 'to' => (string) $to];
                }
            }
        }

        return $found;
    }

    /** @return array<int, \Illuminate\Mail\Mailable> */
    private function queuedFor(string $class): array
    {
        $found = [];
        foreach (\Illuminate\Support\Facades\Queue::pushedJobs() as $jobs) {
            foreach ($jobs as $job) {
                $payload = $job['job'] ?? null;
                if ($payload instanceof \App\Jobs\SendQueuedEmail) {
                    $r = new \ReflectionClass($payload);
                    foreach ($r->getProperties() as $prop) {
                        $prop->setAccessible(true);
                        $value = $prop->getValue($payload);
                        if ($value instanceof $class) {
                            $found[] = $value;
                        }
                    }
                }
            }
        }

        return $found;
    }

    public function test_analytics_and_usage_are_untouched_by_a_move(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        // Seeded, because AnalyticsEventsDaily is only ever written by TicketController - book() never
        // touches it - so comparing the natural state would be 0 against 0 and would not notice a move
        // that started double-counting or re-keying rows.
        $analyticsRow = \App\Models\AnalyticsEventsDaily::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'date' => $sale->created_at->format('Y-m-d'),
            'sales_count' => 3,
        ]);

        $usageBefore = (int) \App\Models\UsageDaily::where('role_id', $role->id)->sum('count');
        $this->assertGreaterThan(0, $usageBefore, 'fixture premise: book() recorded an EVENT_CREATE');

        $this->service()->reschedule($sale, $this->otherSlot($type, $event));

        // No new Event exists, so EVENT_CREATE must not fire again...
        $this->assertSame(
            $usageBefore,
            (int) \App\Models\UsageDaily::where('role_id', $role->id)->sum('count'),
            'a move is not a creation'
        );

        // ...and the analytics row keeps both its count and its date, which is keyed off the sale's
        // created_at and therefore must not follow starts_at.
        $analyticsRow->refresh();
        $this->assertSame(3, (int) $analyticsRow->sales_count);
        $this->assertSame($sale->created_at->format('Y-m-d'), $analyticsRow->date->format('Y-m-d'));
        $this->assertSame(1, \App\Models\AnalyticsEventsDaily::where('event_id', $event->id)->count(),
            'a move must not spawn a second analytics row');
    }

    /** The action a faked sync job was dispatched with; $action is protected on all three jobs. */
    private function syncActions(string $jobClass): array
    {
        $actions = [];
        \Illuminate\Support\Facades\Bus::assertDispatchedSync($jobClass, function ($job) use (&$actions) {
            $prop = new \ReflectionProperty($job, 'action');
            $prop->setAccessible(true);
            $actions[] = $prop->getValue($job);

            return true;
        });

        return $actions;
    }

    /**
     * confirm()'s latch on confirmed_at used to make dispatchCalendarSync('create') safe, because it
     * could only fire once. reschedule() clears that latch for a back-to-pending move, so a second
     * approval re-armed it - and createEvent() overwrites the CalendarSync row, orphaning the entry it
     * replaced on the owner's real calendar.
     */
    public function test_reapproving_a_moved_booking_updates_the_calendar_entry_instead_of_creating_a_second(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);
        $role->forceFill(['sync_direction' => 'both'])->save();
        $role->user->forceFill(['google_token' => '{"access_token":"x"}'])->save();

        // First approval: nothing synced yet, so this is the create.
        \Illuminate\Support\Facades\Bus::fake();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $this->service()->confirm($sale->fresh());
        $this->assertSame(['update'], $this->syncActions(\App\Jobs\SyncEventToGoogleCalendar::class),
            "the action is always 'update'; each provider falls back to create when it has no external id");

        // Guest moves it -> back to pending, confirmed_at cleared, so the latch is re-armed.
        $this->service()->reschedule($sale->fresh(), $this->otherSlot($type, $event), 'guest');
        $this->assertNull($sale->fresh()->confirmed_at, 'fixture premise: the latch really is re-armed');

        // Second approval must NOT create a second remote event.
        \Illuminate\Support\Facades\Bus::fake();
        $event->fresh()->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $this->service()->confirm($sale->fresh());
        $this->assertSame(['update'], $this->syncActions(\App\Jobs\SyncEventToGoogleCalendar::class));
    }

    /**
     * Owner mode drops min-notice, and $earliest was the only past-slot floor - so the picker offered
     * this morning's elapsed slots and the write path accepted them. A booking parked in the past can
     * no longer be moved OR cancelled by anyone, so this is unrecoverable without a DB edit.
     *
     * The clock is pinned BEFORE the booking is made. The target has to be elapsed, inside the type's
     * weekly window, and on the interval grid, while the booking itself stays in the future - pinning
     * afterwards makes the booking past too, and then the pre-flight guard returns 422 for an entirely
     * different reason and the test proves nothing.
     */
    /**
     * A duplicate delivery must report success, not "that time was just booked". The guest taps once on
     * a flaky connection, the request commits, the response is lost, they tap again: from_slot is stale
     * but the target already IS the live start. The old order checked from_slot BEFORE the no-op return,
     * so this 422'd - and the picker's recovery never refreshes currentSlotUtc, so every retry re-sent
     * the same stale value and the guest was stuck until they reloaded.
     */
    public function test_a_replayed_post_reports_success_instead_of_a_dead_end(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);
        $originalStart = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');
        $target = $this->otherSlot($type, $event);

        $body = ['slot' => $target, 'from_slot' => $originalStart, 'guest_timezone' => 'America/New_York'];

        $first = $this->postJson($urls['post'], $body)->assertOk();
        $this->assertStringContainsString('moved=1', $first->json('redirect_url'));

        // Byte-identical replay: from_slot is now stale, but the booking is already where it asked for.
        $replay = $this->postJson($urls['post'], $body)->assertOk();
        $this->assertStringNotContainsString('moved=1', $replay->json('redirect_url'),
            'nothing moved the second time, so do not claim it did');

        // Exactly one move: one sequence bump, and the guest was told once.
        $this->assertSame(1, (int) $event->fresh()->ical_sequence);
    }

    /** A genuine conflict - two tabs picking DIFFERENT slots - must still be rejected. */
    public function test_a_stale_from_slot_targeting_a_different_slot_is_still_rejected(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);
        $originalStart = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');

        $this->postJson($urls['post'], [
            'slot' => $this->otherSlot($type, $event), 'from_slot' => $originalStart,
        ])->assertOk();

        $this->clearCooldown($event);
        $moved = $event->fresh();

        // Second tab still believes the original start and wants a third slot.
        $response = $this->postJson($urls['post'], [
            'slot' => $this->otherSlot($type, $moved), 'from_slot' => $originalStart,
        ])->assertStatus(422);

        $this->assertNotNull($response->json('slots'), 'a real conflict gets the slot-recovery payload');
        $this->assertSame($moved->starts_at, $event->fresh()->starts_at, 'the winner keeps the slot');
    }

    /**
     * The blocked-state message contains a :schedule placeholder. reschedule()'s own throw sites called
     * __() with no replacement array, so Laravel rendered "Contact :schedule if you need to change the
     * time" verbatim.
     *
     * Driven through the SERVICE, not the endpoint: the controller's pre-flight gate always passed the
     * replacement correctly, so an HTTP-level test is answered by the gate and never reaches the throw
     * inside the transaction. That path is real - it fires when the booking is cancelled between the
     * page render and the submit.
     */
    public function test_the_in_transaction_block_never_renders_a_raw_placeholder(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);

        // Cancelled after the gate would have passed, so only the in-lock re-check can catch it.
        Event::whereKey($event->id)->update(['is_cancelled' => true]);

        try {
            $this->service()->reschedule($sale, $target);
            $this->fail('a cancelled booking must not be movable');
        } catch (BusinessException $e) {
            $this->assertStringNotContainsString(':schedule', $e->getMessage(), 'the placeholder must be replaced');
            $this->assertStringContainsString($role->name, $e->getMessage());
        }
    }

    /**
     * The same for the past-booking branch, which is a separate throw site with its own __() call.
     */
    public function test_the_past_booking_block_never_renders_a_raw_placeholder(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $target = $this->otherSlot($type, $event);

        Event::whereKey($event->id)->update(['starts_at' => now('UTC')->subDay()->format('Y-m-d H:i:s')]);

        try {
            $this->service()->reschedule($sale->fresh(), $target);
            $this->fail('a past booking must not be movable');
        } catch (BusinessException $e) {
            $this->assertStringNotContainsString(':schedule', $e->getMessage());
            $this->assertStringContainsString($role->name, $e->getMessage());
        }
    }

    /** A non-availability error must NOT carry `slots`, or the picker wipes the guest's selection. */
    public function test_a_cooldown_error_does_not_trigger_the_slot_recovery(): void
    {
        [, $type, $sale, $event] = $this->booking();
        $urls = $this->guestUrls($event, $sale);

        $this->postJson($urls['post'], ['slot' => $this->otherSlot($type, $event)])->assertOk();

        // Immediate second move: refused by the cooldown, which is not a slot problem.
        $response = $this->postJson($urls['post'], [
            'slot' => $this->otherSlot($type, $event->fresh()),
        ])->assertStatus(422);

        $this->assertNull($response->json('slots'),
            'slots would make the picker replace the day and clear the selection for no reason');
        $this->assertNotNull($response->json('error'));
    }

    /**
     * gate() aborts the plan check with a REDIRECT, and abort() carrying a Response throws it verbatim
     * regardless of Accept - so fetch() followed it into HTML, res.json() threw, and the picker reported
     * "session expired" to an owner whose plan had lapsed. These two endpoints answer in JSON.
     */
    public function test_the_owner_json_endpoints_report_a_lapsed_plan_as_json(): void
    {
        [$role, , $sale] = $this->booking();
        $owner = $role->user;
        $urls = $this->ownerUrls($role, $sale);

        // Force the hosted Pro gate closed.
        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d')])->save();

        foreach (['slots' => 'get', 'post' => 'postJson'] as $key => $verb) {
            $response = $verb === 'get'
                ? $this->actingAs($owner)->getJson($urls[$key])
                : $this->actingAs($owner)->postJson($urls[$key], ['slot' => '2030-01-01T10:00:00Z']);

            $response->assertStatus(403);
            $this->assertNotNull($response->json('error'), $key.' must answer in JSON, not a redirect');
            $this->assertSame(
                'application/json',
                explode(';', (string) $response->headers->get('content-type'))[0],
                $key.' must not hand the picker an HTML page'
            );
        }
    }

    /** The page endpoint keeps its redirect - that is correct for a normal navigation. */
    public function test_the_owner_page_still_redirects_a_lapsed_plan(): void
    {
        [$role, , $sale] = $this->booking();
        $owner = $role->user;

        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d')])->save();

        $this->actingAs($owner)
            ->get($this->ownerUrls($role, $sale)['page'])
            ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']));
    }

    /**
     * A blocked booking used to make the month fetch look like a network failure: fetchMonth tested only
     * res.ok, threw the body away, and showed "Could not load times" with a Retry that re-fired the same
     * 422 forever. The real reason has to reach the guest.
     */
    public function test_the_slots_endpoint_explains_why_it_refused(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        // The owner deactivates the type while the guest has the picker open.
        $type->forceFill(['is_active' => false])->save();

        $response = $this->getJson($this->guestUrls($event, $sale)['slots'])->assertStatus(422);

        $error = $response->json('error');
        $this->assertNotNull($error, 'the picker reads this to replace its generic load-failure message');
        $this->assertStringNotContainsString(':schedule', $error);
    }

    /**
     * The Bookings list was scoped on sales.subdomain - a booking-time snapshot that update() never
     * rewrites on rename. Two consequences, both tested here: a renamed schedule lost its own bookings
     * while the tab badge still counted them, and whoever claimed the freed subdomain saw the original
     * schedule's bookings, guest names and emails included.
     */
    public function test_the_bookings_list_survives_a_rename_and_never_leaks_to_the_new_owner(): void
    {
        [$roleA, , $saleA] = $this->booking();
        $freed = $roleA->subdomain;

        $bookingsUrl = fn ($role) => route('role.view_admin', [
            'subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings',
        ]);

        // Baseline: the owner can see their own booking.
        $this->actingAs($roleA->user)->get($bookingsUrl($roleA))->assertOk()->assertSee($saleA->email);

        // Rename, then hand the old subdomain to somebody else.
        $roleA->forceFill(['subdomain' => $freed.'-renamed'])->save();
        $roleB = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $roleB->forceFill(['subdomain' => $freed])->save();
        $this->assertSame($roleB->subdomain, $saleA->fresh()->subdomain, 'fixture premise: the drift is real');

        // A still sees its booking...
        $this->actingAs($roleA->fresh()->user)
            ->get($bookingsUrl($roleA->fresh()))
            ->assertOk()
            ->assertSee($saleA->email);

        // ...and B, which now holds the old subdomain, sees nothing of A's.
        $this->actingAs($roleB->user)
            ->get($bookingsUrl($roleB))
            ->assertOk()
            ->assertDontSee($saleA->email)
            ->assertDontSee($saleA->name);
    }

    /**
     * availableSlots() takes `string $fromDate`, so an array `from` was an uncaught TypeError - a 500
     * whose request path contains the 32-char booking secret, written straight into the log.
     */
    public function test_an_array_from_parameter_is_rejected_rather_than_500ing(): void
    {
        [$role, , $sale, $event] = $this->booking();

        $this->getJson($this->guestUrls($event, $sale)['slots'].'?from[]=x')->assertStatus(422);

        // The owner endpoint has the same signature and the same hole.
        $this->actingAs($role->user)
            ->getJson($this->ownerUrls($role, $sale)['slots'].'?from[]=x')
            ->assertStatus(422);

        // A garbage string is still tolerated - it falls back to today rather than failing the request.
        $this->getJson($this->guestUrls($event, $sale)['slots'].'?from=not-a-date')->assertStatus(422);
    }

    /**
     * Each guest secret-link route needs its OWN throttle bucket. The limiter key for an
     * unauthenticated request is $prefix.sha1(domain|ip) with no route name, so unprefixed routes shared
     * a counter and the tightest limit governed them all.
     *
     * Asserts the wiring, because ThrottleRequests short-circuits under app.is_testing and the behaviour
     * itself cannot be exercised by the suite at all.
     */
    public function test_every_guest_appointment_route_has_its_own_throttle_bucket(): void
    {
        $prefixes = [];

        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            if (! str_starts_with((string) $route->uri(), 'appointment/')) {
                continue;
            }

            $throttles = array_values(array_filter(
                $route->gatherMiddleware(),
                fn ($m) => is_string($m) && (str_starts_with($m, 'throttle:') || str_contains($m, 'ThrottleRequests:'))
            ));
            $this->assertNotEmpty($throttles, $route->uri().' must be throttled');

            $parts = explode(':', $throttles[0]);
            $args = isset($parts[1]) ? explode(',', $parts[1]) : [];
            $this->assertCount(3, $args, $route->uri().' needs a distinct throttle prefix, got: '.$throttles[0]);
            $prefixes[] = $args[2];
        }

        $this->assertNotEmpty($prefixes);
        $this->assertSame(
            count($prefixes),
            count(array_unique($prefixes)),
            'two appointment routes share a throttle bucket: '.implode(', ', $prefixes)
        );
    }

    /**
     * The owner-notification half of the feature had no coverage at all. Three things are pinned here:
     * the kind carries the move (not a bare "pending"), the ACTING owner is excluded, and co-admins are
     * not - a second admin's calendar shifting with no explanation was the failure to avoid.
     */
    public function test_a_guest_move_notifies_every_admin_and_reads_as_a_move(): void
    {
        [$role, $type, $sale, $event] = $this->booking(['requires_approval' => true]);
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        $coAdmin = $this->createOwner();
        $role->users()->attach($coAdmin->id, ['level' => 'admin']);

        $this->enableOwnerNotifications();
        \Illuminate\Support\Facades\Queue::fake();
        $this->postJson($this->guestUrls($event, $sale)['post'], [
            'slot' => $this->otherSlot($type, $event),
        ])->assertOk();

        $notifications = $this->queuedOwnerNotifications();
        $this->assertCount(2, $notifications, 'both admins must hear about it');

        foreach ($notifications as $n) {
            $this->assertSame('rescheduled_pending', $n['kind'],
                'an approval-required move goes back to pending, but must still read as a MOVE');
        }
        $this->assertEqualsCanonicalizing(
            [$role->user->email, $coAdmin->email],
            array_column($notifications, 'to')
        );

        // And the guest is told once.
        $this->assertCount(1, $this->queuedFor(\App\Mail\AppointmentRescheduled::class));
    }

    /** An owner-initiated move excludes the acting user and nobody else. */
    public function test_an_owner_move_excludes_the_acting_user_but_not_their_co_admins(): void
    {
        [$role, $type, $sale, $event] = $this->booking();
        $acting = $role->user;
        $coAdmin = $this->createOwner();
        $role->users()->attach($coAdmin->id, ['level' => 'admin']);

        $this->enableOwnerNotifications();
        \Illuminate\Support\Facades\Queue::fake();
        $this->actingAs($acting)->postJson($this->ownerUrls($role, $sale)['post'], [
            'slot' => $this->otherSlot($type, $event),
        ])->assertOk();

        $recipients = array_column($this->queuedOwnerNotifications(), 'to');
        $this->assertContains($coAdmin->email, $recipients, 'a co-admin\'s calendar moved too');
        $this->assertNotContains($acting->email, $recipients, 'the acting owner does not need telling');
    }

    /**
     * The move notice rode the default-on new_request preference only. That preference is opt-OUT, so an
     * owner who explicitly turned it off - reasonable on an auto-approve schedule - got no notice of any
     * reschedule, which is the silent calendar drift the notification exists to prevent.
     */
    public function test_a_move_still_reaches_an_owner_who_opted_out_of_request_emails(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        // Keeps sale notices, declines request notices.
        $role->users()->updateExistingPivot($role->user_id, [
            'notification_settings' => json_encode(['new_request' => false, 'new_sale' => true]),
        ]);

        $this->enableOwnerNotifications();
        \Illuminate\Support\Facades\Queue::fake();
        $this->postJson($this->guestUrls($event, $sale)['post'], [
            'slot' => $this->otherSlot($type, $event),
        ])->assertOk();

        $this->assertContains(
            $role->user->email,
            array_column($this->queuedOwnerNotifications(), 'to'),
            'a move satisfies either preference'
        );
    }

    /** Somebody who turned BOTH off asked for silence and must still get it. */
    public function test_a_move_respects_an_owner_who_opted_out_of_everything(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        $role->users()->updateExistingPivot($role->user_id, [
            'notification_settings' => json_encode(['new_request' => false, 'new_sale' => false]),
        ]);

        $this->enableOwnerNotifications();
        \Illuminate\Support\Facades\Queue::fake();
        $this->postJson($this->guestUrls($event, $sale)['post'], [
            'slot' => $this->otherSlot($type, $event),
        ])->assertOk();

        $this->assertEmpty($this->queuedOwnerNotifications());
    }

    /**
     * The grid excludes the booking's own event so it does not block itself, which means its current slot
     * is offered back as an ordinary selectable button. Unlabelled, picking it produced a review step
     * reading "Previously: 2:00 PM / Now: 2:00 PM" and then a silent no-op.
     *
     * Asserts the two things the runtime label needs: the slot really is in the grid, and the tag string
     * reached the Vue props. The comparison itself is one line of template.
     */
    public function test_the_bookings_own_slot_is_offered_back_and_carries_a_current_tag(): void
    {
        [, , $sale, $event] = $this->booking();

        $html = $this->get($this->guestUrls($event, $sale)['page'])->assertOk()->getContent();

        $props = json_decode(html_entity_decode(
            \Illuminate\Support\Str::before(
                \Illuminate\Support\Str::after($html, 'data-props="'), '"'
            )
        ), true);
        $this->assertNotNull($props, 'the picker props must parse');

        $currentUtc = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z');
        $this->assertSame($currentUtc, $props['currentSlotUtc']);

        $offered = collect($props['initial']['days'] ?? [])->flatten(1)->pluck('utc');
        $this->assertContains($currentUtc, $offered->all(),
            "the booking's own slot must be selectable, or the guest cannot keep their time");

        $this->assertSame(__('messages.appointments_current_slot_tag'), $props['t']['currentSlotTag']);
        $this->assertNotEmpty($props['t']['currentSlotTag']);
    }

    /**
     * The SEQUENCE inside the .ics is what tells a client this is a newer version of an entry it already
     * holds. Only the DB column was ever asserted, and the two are separate lines of code - so a bug that
     * bumped the column but emitted a stale SEQUENCE (or folded starts_at into the UID) would ship.
     */
    public function test_the_ics_uid_survives_a_move_while_its_sequence_advances(): void
    {
        [$role, $type, $sale, $event] = $this->booking();

        $read = function (string $ics, string $field) {
            foreach (explode("\r\n", str_replace("\r\n ", '', $ics)) as $line) {
                if (str_starts_with($line, $field.':')) {
                    return substr($line, strlen($field) + 1);
                }
            }

            return null;
        };

        $before = \App\Utils\IcsUtils::buildInvite($event, $role, $sale, 'REQUEST');
        $uidBefore = $read($before, 'UID');
        $seqBefore = (int) $read($before, 'SEQUENCE');
        $this->assertNotNull($uidBefore);

        $this->service()->reschedule($sale, $this->otherSlot($type, $event), 'guest');

        $after = \App\Utils\IcsUtils::buildInvite($event->fresh(), $role, $sale->fresh(), 'REQUEST');

        // Same UID, or the client adds a second entry instead of updating the one it has.
        $this->assertSame($uidBefore, $read($after, 'UID'), 'the UID must not change across a move');
        // Higher SEQUENCE, or the client treats the update as stale and ignores it.
        $this->assertGreaterThan($seqBefore, (int) $read($after, 'SEQUENCE'));
        // And DTSTART really did move, so the invite is describing the new time.
        $this->assertNotSame($read($before, 'DTSTART'), $read($after, 'DTSTART'));
    }

    public function test_the_owner_cannot_move_a_booking_into_the_past(): void
    {
        // 13:00 EDT, mid-window on a day the 09:00-17:00 schedule is open.
        Carbon::setTestNow(Carbon::parse('2026-09-08 17:00:00', 'UTC'));

        try {
            [$role, , $sale, $event] = $this->booking();
            $owner = $role->user;

            // 09:00 EDT the same day: in-window, on the grid, four hours gone.
            $elapsed = '2026-09-08T13:00:00Z';
            $this->assertTrue(Carbon::parse($elapsed)->isPast(), 'the target is elapsed');
            $this->assertSame(9, Carbon::parse($elapsed)->setTimezone('America/New_York')->hour, 'and in-window');
            $this->assertTrue($event->getStartDateTime()->isFuture(), 'but the booking itself is not past');

            $this->actingAs($owner)
                ->postJson($this->ownerUrls($role, $sale)['post'], ['slot' => $elapsed, 'notify' => false])
                ->assertStatus(422);

            $this->assertSame($event->starts_at, $event->fresh()->starts_at, 'the booking must not have moved');
        } finally {
            Carbon::setTestNow();
        }
    }

    /**
     * Calls a private/protected inbound-sync method. They are all non-public and none of them is
     * reachable without a live provider connection, so reflection is the only way to prove the guard.
     */
    private function callInbound(object $service, string $method, array $args): bool
    {
        $ref = new \ReflectionMethod($service, $method);
        $ref->setAccessible(true);

        return (bool) $ref->invokeArgs($service, $args);
    }

    /**
     * Our own outbound dispatchCalendarSync('update') can bounce straight back in as an inbound change,
     * and these three methods would then rewrite the booking from the remote copy - dragging a
     * rescheduled appointment back to its old time, replacing the guest's notes with the schedule's
     * rendered calendar_description_template, and (CalDAV) stretching a 30-minute booking to 2 hours.
     */
    public function test_inbound_google_sync_leaves_a_booking_untouched(): void
    {
        [$role, , , $event] = $this->booking();
        $before = $event->only(['name', 'description', 'starts_at', 'duration']);

        $start = new \Google\Service\Calendar\EventDateTime;
        $start->setDateTime('2020-01-01T10:00:00Z');
        $end = new \Google\Service\Calendar\EventDateTime;
        $end->setDateTime('2020-01-01T12:00:00Z');

        $changed = $this->callInbound(app(\App\Services\GoogleCalendarService::class), 'updateEventFromGoogle', [
            $event,
            ['summary' => 'Hijacked', 'description' => 'Template text', 'start' => $start, 'end' => $end, 'location' => null],
            $role,
        ]);

        $this->assertFalse($changed, 'the guard must report "nothing changed" so the caller does not track usage');
        $this->assertSame($before, $event->fresh()->only(['name', 'description', 'starts_at', 'duration']));
    }

    public function test_inbound_microsoft_sync_leaves_a_booking_untouched(): void
    {
        [$role, , , $event] = $this->booking();
        $before = $event->only(['name', 'description', 'starts_at', 'duration']);

        $changed = $this->callInbound(app(\App\Services\MicrosoftCalendarService::class), 'updateEventFromMicrosoft', [
            [
                'subject' => 'Hijacked',
                'body' => ['content' => 'Template text'],
                'start' => ['dateTime' => '2020-01-01T10:00:00', 'timeZone' => 'UTC'],
                'end' => ['dateTime' => '2020-01-01T12:00:00', 'timeZone' => 'UTC'],
                'isAllDay' => false,
            ],
            $event,
            $role,
        ]);

        $this->assertFalse($changed);
        $this->assertSame($before, $event->fresh()->only(['name', 'description', 'starts_at', 'duration']));
    }

    public function test_inbound_caldav_sync_leaves_a_booking_untouched(): void
    {
        [$role, , , $event] = $this->booking();
        $before = $event->only(['name', 'description', 'starts_at', 'duration']);

        // CalDAV resolves the event through the pivot rather than being handed one.
        $event->roles()->updateExistingPivot($role->id, ['caldav_event_uid' => 'uid-abc']);

        $changed = $this->callInbound(app(\App\Services\CalDAVService::class), 'updateEventFromCalDAV', [
            [
                'uid' => 'uid-abc',
                'summary' => 'Hijacked',
                'description' => 'Template text',
                'start' => Carbon::parse('2020-01-01 10:00:00', 'UTC'),
                'end' => Carbon::parse('2020-01-01 12:00:00', 'UTC'),
                'duration' => 0,
                'location' => null,
            ],
            $role,
        ]);

        $this->assertFalse($changed);
        $this->assertSame($before, $event->fresh()->only(['name', 'description', 'starts_at', 'duration']));
    }

    /**
     * The section, the sidebar link and the search-index row all have to agree on the anchor, or the
     * docs search sends people to a page that does not scroll anywhere.
     */
    public function test_the_docs_cover_rescheduling(): void
    {
        $this->get('/docs/appointments')
            ->assertOk()
            ->assertSee('id="rescheduling"', false)
            ->assertSee('href="#rescheduling"', false)
            ->assertSee('Rescheduling');

        $method = new \ReflectionMethod(\App\Http\Controllers\MarketingController::class, 'getDocSearchIndex');
        $method->setAccessible(true);
        $urls = array_column($method->invoke(app(\App\Http\Controllers\MarketingController::class)), 'url');

        $this->assertNotEmpty(array_filter($urls, fn ($u) => str_contains($u, '/docs/appointments#rescheduling')));
    }

    public function test_robots_disallows_the_secret_bearing_appointment_paths(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /appointment/');
    }

    /**
     * CaptureUtmParameters persists $request->path() as utm_landing_page into the session AND a 30-day
     * cookie, and the secret authenticates the booking - so on these routes that is a credential in a
     * cookie. Capture is first-touch, so this has to be the very first request in the session.
     */
    public function test_the_secret_bearing_path_is_never_captured_as_a_landing_page(): void
    {
        [, , $sale, $event] = $this->booking();

        $response = $this->get($this->guestUrls($event, $sale)['manage'])->assertOk();

        $this->assertNull(session('utm_landing_page'));
        $this->assertNull($this->utmLandingCookie($response));

        // Proves the assertion above is not vacuous: the same middleware DOES capture a normal page.
        $this->get('/')->assertOk();
        $this->assertNotNull(session('utm_landing_page'));
    }

    private function utmLandingCookie(\Illuminate\Testing\TestResponse $response): ?string
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'utm_landing_page') {
                return $cookie->getValue();
            }
        }

        return null;
    }
}
