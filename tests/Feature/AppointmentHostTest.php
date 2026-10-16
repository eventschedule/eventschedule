<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentBookedNotification;
use App\Mail\AppointmentHostReleased;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\RoleUser;
use App\Models\Sale;
use App\Models\User;
use App\Services\AccountDeletionService;
use App\Services\AppointmentService;
use App\Services\ScheduleTransferService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An appointment type booked with one team member (AppointmentHostService).
 *
 * A hosted type's times are the host's: their live bookings on every schedule and their days away
 * on this one block them, and the schedule's own events do not. An unhosted type keeps the
 * schedule-wide rule, less the bookings made with a host. A host is an owner or admin, checked on
 * save and again at booking; when they leave, their types are switched off and the owner is told.
 */
class AppointmentHostTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TZ = 'America/New_York';

    private function everyDay(string $start = '09:00', string $end = '17:00'): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => $start, 'end' => $end]]);
    }

    /** A schedule with an admin, Sam, and a type hosted by Sam. */
    private function scheduleWithHost(?User $sam = null): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => self::TZ]);
        $sam ??= $this->createOwner();
        $sam->name = 'Sam Jones';
        $sam->save();
        $role->users()->attach($sam->id, ['level' => 'admin']);

        $type = $this->createAppointmentType($role, [
            'name' => 'Consult with Sam',
            'weekly_windows' => $this->everyDay(),
            'host_user_id' => $sam->id,
        ]);

        return [$owner, $role, $sam, $type];
    }

    private function tomorrowSlots(AppointmentType $type): array
    {
        $date = Carbon::now(self::TZ)->addDay()->format('Y-m-d');

        return collect(app(AppointmentService::class)->availableSlots($type->fresh(), $date, 1)['days'][$date] ?? [])
            ->pluck('utc')->all();
    }

    private function book(AppointmentType $type, string $slot, string $email = 'guest@gmail.com'): Sale
    {
        $type = $type->fresh();

        return app(AppointmentService::class)->book($type, $type->role, [
            'name' => 'Guest', 'email' => $email, 'slot' => $slot, 'guest_timezone' => self::TZ,
        ]);
    }

    // ---------------------------------------------------------------- busy time

    public function test_the_hosts_booking_on_another_schedule_blocks_the_hosted_type(): void
    {
        [, $roleA, $sam, $typeA] = $this->scheduleWithHost();

        $ownerB = $this->createOwner();
        $roleB = $this->createRole($ownerB, 'talent', ['timezone' => self::TZ]);
        $roleB->users()->attach($sam->id, ['level' => 'admin']);
        $typeB = $this->createAppointmentType($roleB, ['weekly_windows' => $this->everyDay(), 'host_user_id' => $sam->id]);
        $unhostedA = $this->createAppointmentType($roleA, ['weekly_windows' => $this->everyDay()]);

        $slot = $this->tomorrowSlots($typeB)[0];
        $this->assertContains($slot, $this->tomorrowSlots($typeA));

        $sale = $this->book($typeB, $slot);
        $this->assertSame($sam->id, (int) $sale->event->appointment_host_user_id, 'the booking keeps who it is with');

        $this->assertNotContains($slot, $this->tomorrowSlots($typeA), 'Sam is booked elsewhere at that time');
        $this->assertContains($slot, $this->tomorrowSlots($unhostedA), "schedule A's own time is not Sam's");
    }

    public function test_a_hosted_booking_does_not_block_the_schedules_unhosted_types(): void
    {
        [, $role, , $hosted] = $this->scheduleWithHost();
        $unhosted = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);

        $slot = $this->tomorrowSlots($hosted)[0];
        $this->book($hosted, $slot);

        $this->assertNotContains($slot, $this->tomorrowSlots($hosted));
        $this->assertContains($slot, $this->tomorrowSlots($unhosted));

        // And the other way round: an unhosted booking is the schedule's, not Sam's.
        $next = $this->tomorrowSlots($unhosted)[1];
        $this->book($unhosted, $next, 'other@gmail.com');
        $this->assertNotContains($next, $this->tomorrowSlots($unhosted));
        $this->assertContains($next, $this->tomorrowSlots($hosted));
    }

    public function test_a_departed_hosts_bookings_block_the_schedules_unhosted_types(): void
    {
        [, $role, $sam, $hosted] = $this->scheduleWithHost();
        $unhosted = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);

        $slot = $this->tomorrowSlots($hosted)[0];
        $this->book($hosted, $slot);
        $this->assertContains($slot, $this->tomorrowSlots($unhosted));

        // Sam leaves; the booking stays, and it is the schedule's time now.
        $role->users()->detach($sam->id);
        $this->assertNotContains($slot, $this->tomorrowSlots($unhosted));
    }

    public function test_the_schedules_own_events_block_only_its_unhosted_types(): void
    {
        [, $role, , $hosted] = $this->scheduleWithHost();
        $unhosted = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);

        $slot = $this->tomorrowSlots($hosted)[2];
        $event = $this->createEvent($role, [
            'starts_at' => Carbon::createFromFormat('Y-m-d\TH:i:s\Z', $slot, 'UTC')->format('Y-m-d H:i:s'),
            'duration' => 1,
        ]);
        $this->assertNotNull($event->id);

        $this->assertNotContains($slot, $this->tomorrowSlots($unhosted));
        $this->assertContains($slot, $this->tomorrowSlots($hosted));
    }

    public function test_the_hosts_days_away_on_this_schedule_block_the_whole_day(): void
    {
        [, $role, $sam, $hosted] = $this->scheduleWithHost();
        $unhosted = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);
        $tomorrow = Carbon::now(self::TZ)->addDay()->format('Y-m-d');

        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)
            ->update(['dates_unavailable' => json_encode([$tomorrow])]);

        $this->assertSame([], $this->tomorrowSlots($hosted));
        $this->assertNotEmpty($this->tomorrowSlots($unhosted));
    }

    public function test_a_moved_booking_keeps_the_host_it_was_made_with(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);

        // The type is given to the owner. New bookings are the owner's; this one stays Sam's.
        $type->forceFill(['host_user_id' => $owner->id])->save();
        $this->assertSame($sam->id, (int) $sale->event->fresh()->appointment_host_user_id);

        // Sam's day away now blocks moving the booking onto that day.
        $dayAfter = Carbon::now(self::TZ)->addDays(2)->format('Y-m-d');
        $before = app(AppointmentService::class)->availableSlots($type->fresh(), $dayAfter, 1, null, false, $sale->event_id);
        $this->assertNotEmpty($before['days']);

        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)
            ->update(['dates_unavailable' => json_encode([$dayAfter])]);

        $after = app(AppointmentService::class)->availableSlots($type->fresh(), $dayAfter, 1, null, false, $sale->event_id);
        $this->assertSame([], $after['days'], "a move is checked against Sam's time, not the type's new host");
    }

    public function test_a_booking_on_a_schedule_the_host_has_left_no_longer_blocks_them(): void
    {
        [, $roleA, $sam, $typeA] = $this->scheduleWithHost();
        $roleB = $this->createRole($this->createOwner(), 'talent', ['timezone' => self::TZ]);
        $roleB->users()->attach($sam->id, ['level' => 'admin']);
        $typeB = $this->createAppointmentType($roleB, ['weekly_windows' => $this->everyDay(), 'host_user_id' => $sam->id]);

        $slot = $this->tomorrowSlots($typeB)[0];
        $this->book($typeB, $slot);
        $this->assertNotContains($slot, $this->tomorrowSlots($typeA));

        // Sam leaves B. The booking stays B's to move, and is not Sam's time any more.
        RoleUser::where('role_id', $roleB->id)->where('user_id', $sam->id)->delete();
        $this->assertContains($slot, $this->tomorrowSlots($typeA));
    }

    public function test_an_owner_who_hosts_is_not_booked_twice_on_their_own_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => self::TZ]);
        $hosted = $this->createAppointmentType($role, ['name' => 'With the owner', 'weekly_windows' => $this->everyDay(), 'host_user_id' => $owner->id]);
        $unhosted = $this->createAppointmentType($role, ['name' => 'With the schedule', 'weekly_windows' => $this->everyDay()]);
        $slots = $this->tomorrowSlots($unhosted);

        // An unhosted booking here is the owner's time too ...
        $this->book($unhosted, $slots[0]);
        $this->assertNotContains($slots[0], $this->tomorrowSlots($hosted));

        // ... and the owner's hosted booking blocks the unhosted types.
        $this->book($hosted, $slots[2], 'other@gmail.com');
        $this->assertNotContains($slots[2], $this->tomorrowSlots($unhosted));
    }

    /** An owner who hosts a type on somebody else's schedule is the same person on their own. */
    public function test_an_owner_who_hosts_elsewhere_is_not_booked_twice_across_schedules(): void
    {
        $alice = $this->createOwner();
        $roleA = $this->createRole($alice, 'talent', ['timezone' => self::TZ]);
        $consult = $this->createAppointmentType($roleA, ['name' => 'Consult', 'weekly_windows' => $this->everyDay()]);

        $roleB = $this->createRole($this->createOwner(), 'talent', ['timezone' => self::TZ]);
        $roleB->users()->attach($alice->id, ['level' => 'admin']);
        $demo = $this->createAppointmentType($roleB, ['name' => 'Demo', 'weekly_windows' => $this->everyDay(), 'host_user_id' => $alice->id]);
        $slots = $this->tomorrowSlots($consult);

        // A booking on her own schedule, with nobody named, is Alice's time on B ...
        $this->book($consult, $slots[0]);
        $this->assertNotContains($slots[0], $this->tomorrowSlots($demo));

        // ... and a booking with her on B is her time on her own schedule.
        $this->book($demo, $slots[2], 'other@gmail.com');
        $this->assertNotContains($slots[2], $this->tomorrowSlots($consult));
    }

    public function test_an_owner_hosted_type_is_blocked_by_what_a_departed_host_left(): void
    {
        [$owner, $role, $sam, $samsType] = $this->scheduleWithHost();
        $ownersType = $this->createAppointmentType($role, ['name' => 'With the owner', 'weekly_windows' => $this->everyDay(), 'host_user_id' => $owner->id]);

        $slot = $this->tomorrowSlots($samsType)[0];
        $this->book($samsType, $slot);
        $this->assertContains($slot, $this->tomorrowSlots($ownersType), "Sam's booking is Sam's time");

        // Sam leaves: the booking stays, it is the schedule's time now, and so the owner's.
        $role->users()->detach($sam->id);
        $this->assertNotContains($slot, $this->tomorrowSlots($ownersType));
    }

    public function test_a_blocked_account_cannot_host(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        $sam->forceFill(['blocked_at' => now()])->save();

        $this->assertFalse(\App\Services\AppointmentHostService::isEligible($role->id, $sam->id));
        $this->assertFalse($type->fresh()->isBookable());
        $this->assertFalse(app(\App\Services\AppointmentHostService::class)->candidates($role)->contains('id', $sam->id));
        $this->assertTrue(\App\Services\AppointmentHostService::isEligible($role->id, $owner->id));
    }

    public function test_the_departed_hosts_count_matches_the_list_it_links_to(): void
    {
        [, $role, $sam, $type] = $this->scheduleWithHost();
        $slots = $this->tomorrowSlots($type);
        $this->book($type, $slots[0]);

        $type->forceFill(['requires_approval' => true])->save();
        $this->book($type, $slots[2], 'waiting@gmail.com');

        $this->assertSame(1, \App\Services\AppointmentHostService::futureBookingCount($role, $sam->id), 'Upcoming leaves pending out');
        $this->assertSame(1, \App\Services\AppointmentHostService::futureBookingCount($role, $sam->id, pending: true));
    }

    public function test_a_copy_off_enterprise_does_not_keep_the_host(): void
    {
        [$owner, $role, , $type] = $this->scheduleWithHost();
        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d')])->save();
        $this->assertTrue($role->fresh()->isPro());
        $this->assertFalse($role->fresh()->isEnterprise());

        $this->actingAs($owner)
            ->post(route('appointments.duplicate', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]))
            ->assertSessionHasNoErrors();

        $copy = AppointmentType::where('role_id', $role->id)->where('id', '!=', $type->id)->firstOrFail();
        $this->assertNull($copy->host_user_id, 'a type may keep its host off Enterprise, a new one gains none');
        $this->assertNotNull($type->fresh()->host_user_id);
    }

    // ---------------------------------------------------------------- locking

    /**
     * The statements a call runs inside its own transaction (deeper than the test's), in order.
     *
     * @return array<int, string>
     */
    private function statementsInside(\Closure $call): array
    {
        $baseline = DB::transactionLevel();
        $sql = [];
        DB::listen(function ($query) use (&$sql, $baseline) {
            if ($query->connection->transactionLevel() > $baseline) {
                $sql[] = $query->sql;
            }
        });

        $call();

        return $sql;
    }

    /**
     * Users first, then the schedule: the order User::claimRolesByPhone() takes them in, so a
     * booking and a claim can never wait on each other in a cycle. And nothing is read inside the
     * transaction before the user row is locked.
     */
    public function test_booking_locks_the_host_first_then_the_schedule(): void
    {
        [, , , $type] = $this->scheduleWithHost();
        $slot = $this->tomorrowSlots($type)[0];

        $sql = $this->statementsInside(fn () => $this->book($type, $slot));

        $this->assertMatchesRegularExpression('/^select .* from `users` .* for update$/i', $sql[0], 'the first statement locks the host');
        $this->assertMatchesRegularExpression('/^select .* from `roles` .* for update$/i', $sql[1], 'then the schedule');
    }

    public function test_a_move_locks_the_host_first_and_reads_nothing_before_it(): void
    {
        [, , , $type] = $this->scheduleWithHost();
        $slots = $this->tomorrowSlots($type);
        $sale = $this->book($type, $slots[0]);
        $sale->load('event.appointmentType', 'event.creatorRole');

        $sql = $this->statementsInside(fn () => app(AppointmentService::class)->reschedule($sale, $slots[3], 'owner', null, null, true));

        // The host and the owner (a move against the schedule's own time reads the owner's
        // bookings elsewhere), then the schedule, and no plain read before them.
        $this->assertMatchesRegularExpression('/^select .* from `users` .* for update$/i', $sql[0], 'no plain read comes before the user lock');
        $this->assertMatchesRegularExpression('/^select .* from `users` .* for update$/i', $sql[1]);
        $this->assertMatchesRegularExpression('/^select .* from `roles` .* for update$/i', $sql[2]);
        $this->assertSame($slots[3], Carbon::parse($sale->event->fresh()->starts_at, 'UTC')->format('Y-m-d\TH:i:s\Z'));
    }

    /**
     * A type with no host is the owner's time, which a type the owner hosts on another schedule
     * also reads (busyIntervals()), so the two queue on the owner as a hosted booking queues on
     * its host.
     */
    public function test_an_unhosted_booking_locks_the_owner_first_then_the_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => self::TZ]);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);
        $slot = $this->tomorrowSlots($type)[0];

        $sql = $this->statementsInside(fn () => $this->book($type, $slot));

        $this->assertMatchesRegularExpression('/^select .* from `users` .* for update$/i', $sql[0], 'the first statement locks the owner');
        $this->assertMatchesRegularExpression('/^select .* from `roles` .* for update$/i', $sql[1], 'then the schedule');
    }

    // ---------------------------------------------------------------- who may host

    private function savePayload(?string $hostHash): array
    {
        return [
            'name' => 'Hosted',
            'duration_minutes' => 30,
            'location_type' => 'in_person',
            'weekly_windows' => json_encode($this->everyDay()),
            'is_active' => '1',
            'host_submitted' => '1',
            'host_user_id' => $hostHash,
        ];
    }

    public function test_only_an_owner_or_admin_of_the_schedule_may_host(): void
    {
        [$owner, $role, $sam] = $this->scheduleWithHost();
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);
        $stranger = $this->createOwner();

        foreach ([$viewer, $stranger] as $refused) {
            $this->actingAs($owner)
                ->post(route('appointments.store', ['subdomain' => $role->subdomain]), $this->savePayload(UrlUtils::encodeId($refused->id)))
                ->assertSessionHasErrors('host_user_id');
        }
        $this->assertFalse(AppointmentType::where('name', 'Hosted')->exists());

        foreach ([$sam, $owner] as $allowed) {
            $this->actingAs($owner)
                ->post(route('appointments.store', ['subdomain' => $role->subdomain]), $this->savePayload(UrlUtils::encodeId($allowed->id)))
                ->assertSessionHasNoErrors();
        }
        $this->assertEqualsCanonicalizing(
            [$sam->id, $owner->id],
            AppointmentType::where('name', 'Hosted')->pluck('host_user_id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_a_save_without_the_select_keeps_the_stored_host(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();

        $payload = $this->savePayload(null);
        unset($payload['host_submitted'], $payload['host_user_id']);

        $this->actingAs($owner)
            ->put(route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]), $payload)
            ->assertSessionHasNoErrors();

        $this->assertSame($sam->id, (int) $type->fresh()->host_user_id);
    }

    public function test_a_booking_is_refused_once_the_host_no_longer_runs_the_schedule(): void
    {
        [, $role, $sam, $type] = $this->scheduleWithHost();
        $slot = $this->tomorrowSlots($type)[0];
        $loaded = $type->fresh();

        // Made a viewer behind the page's back (no release ran): the booking itself says no.
        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)->update(['level' => 'viewer']);

        $this->expectException(BusinessException::class);
        app(AppointmentService::class)->book($loaded, $role, [
            'name' => 'Guest', 'email' => 'guest@gmail.com', 'slot' => $slot,
        ]);
    }

    public function test_a_type_whose_host_left_cannot_be_switched_back_on_or_cloned_with_them(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)->delete();
        app(\App\Services\AppointmentHostService::class)->releaseHost($role, $sam);

        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertNotNull($type->fresh()->host_released_at);

        $this->actingAs($owner)
            ->post(route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]), ['is_active' => '1'])
            ->assertSessionHas('error', __('messages.appointments_host_choose_first'));
        $this->assertFalse((bool) $type->fresh()->is_active);

        $this->actingAs($owner)
            ->post(route('appointments.duplicate', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]));
        $copy = AppointmentType::where('role_id', $role->id)->where('id', '!=', $type->id)->firstOrFail();
        $this->assertNull($copy->host_user_id);
        $this->assertNull($copy->host_released_at);

        // Choosing nobody is an answer: the mark goes and the type can be on again.
        $this->actingAs($owner)
            ->put(route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]), $this->savePayload(null))
            ->assertSessionHasNoErrors();
        $fresh = $type->fresh();
        $this->assertNull($fresh->host_user_id);
        $this->assertNull($fresh->host_released_at);
        $this->assertTrue((bool) $fresh->is_active);
    }

    // ---------------------------------------------------------------- releasing a host

    private function hostReleasedMails(): array
    {
        $mails = [];
        Queue::assertPushed(SendQueuedEmail::class, function (SendQueuedEmail $job) use (&$mails) {
            $mailable = (new \ReflectionProperty($job, 'mailable'))->getValue($job);
            if ($mailable instanceof AppointmentHostReleased) {
                $mails[] = [(new \ReflectionProperty($job, 'recipient'))->getValue($job), $mailable];
            }

            return true;
        });

        return $mails;
    }

    public function test_removing_the_host_switches_their_types_off_and_tells_the_owner(): void
    {
        Queue::fake();
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        $unhosted = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay()]);

        $this->actingAs($owner)
            ->delete(route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($sam->id)]))
            ->assertRedirect();

        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertNotNull($type->fresh()->host_released_at);
        $this->assertTrue((bool) $unhosted->fresh()->is_active, 'only the types Sam hosted');

        $mails = $this->hostReleasedMails();
        $this->assertCount(1, $mails);
        $this->assertSame($owner->email, $mails[0][0]);
        $this->assertSame('Sam Jones', $mails[0][1]->hostName);
    }

    public function test_a_demotion_to_viewer_releases_the_host_but_handing_over_events_alone_does_not(): void
    {
        Queue::fake();
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();

        // handOverEventsOf() runs for reasons of its own; it is not what releases a host.
        app(ScheduleTransferService::class)->handOverEventsOf($sam->id, $role);
        $this->assertTrue((bool) $type->fresh()->is_active);

        $this->actingAs($owner)
            ->patch(route('role.update_member_level', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($sam->id)]), ['level' => 'viewer'])
            ->assertRedirect();

        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertCount(1, $this->hostReleasedMails());
    }

    /**
     * A member added by phone is a stub account until somebody verifies that number, and then its
     * places move to that account (User::claimRolesByPhone()). What it hosted moves with them.
     */
    public function test_claiming_a_hosts_phone_moves_the_types_and_bookings_it_hosted(): void
    {
        $stub = $this->createOwner();
        $stub->forceFill(['password' => null, 'phone' => '+15555550142'])->saveQuietly();
        [, $role, , $type] = $this->scheduleWithHost($stub);
        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);

        $person = $this->createOwner();
        $this->assertTrue($person->claimRolesByPhone('+15555550142'));

        $this->assertSame($person->id, (int) $type->fresh()->host_user_id);
        $this->assertSame($person->id, (int) $sale->event->fresh()->appointment_host_user_id);
        $this->assertTrue(\App\Services\AppointmentHostService::isEligible($role->id, $person->id));
        $this->assertTrue($type->fresh()->isBookable(), 'still on the booking page, with the same person');
    }

    public function test_claiming_a_hosts_phone_as_a_viewer_releases_the_types_it_hosted(): void
    {
        Queue::fake();
        $stub = $this->createOwner();
        $stub->forceFill(['password' => null, 'phone' => '+15555550143'])->saveQuietly();
        [$owner, $role, , $type] = $this->scheduleWithHost($stub);

        // The person who verifies the number is already on the schedule, as a viewer, who cannot host.
        $person = $this->createOwner();
        $role->users()->attach($person->id, ['level' => 'viewer']);
        $person->claimRolesByPhone('+15555550143');

        $this->assertNotSame($person->id, (int) $type->fresh()->host_user_id);
        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertNotNull($type->fresh()->host_released_at);
        $this->assertSame($owner->email, $this->hostReleasedMails()[0][0], 'the owner is told, as when a host leaves');
    }

    public function test_deleting_the_hosts_account_releases_them(): void
    {
        Queue::fake();
        [$owner, , $sam, $type] = $this->scheduleWithHost();

        app(AccountDeletionService::class)->prepare($sam);

        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertNotNull($type->fresh()->host_released_at);
        $this->assertSame($owner->email, $this->hostReleasedMails()[0][0]);
    }

    public function test_a_transfer_that_drops_the_previous_owner_releases_them(): void
    {
        Queue::fake();
        $previous = $this->createOwner();
        $role = $this->createRole($previous, 'talent', ['timezone' => self::TZ]);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->everyDay(), 'host_user_id' => $previous->id]);
        $newOwner = $this->createOwner();

        $transfer = app(ScheduleTransferService::class)->initiate($role, $previous, $newOwner->email, false);
        $this->assertTrue(app(ScheduleTransferService::class)->accept($transfer->fresh(), $newOwner));

        $this->assertFalse((bool) $type->fresh()->is_active);
        $this->assertSame($newOwner->email, $this->hostReleasedMails()[0][0], 'the new owner is the one told');
    }

    public function test_an_owner_reschedules_a_booking_whose_type_was_switched_off(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);

        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)->delete();
        app(\App\Services\AppointmentHostService::class)->releaseHost($role, $sam);

        $page = route('appointments.booking_reschedule', ['subdomain' => $role->subdomain, 'saleHash' => UrlUtils::encodeId($sale->id)]);
        $this->actingAs($owner)->get($page)->assertOk();

        // The list the owner is sent to shows Sam's bookings, with the move offered.
        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings', 'host' => UrlUtils::encodeId($sam->id)]))
            ->assertOk()
            ->assertSee('Sam Jones')
            ->assertSee($page, false);
    }

    // ---------------------------------------------------------------- notices

    private function ownerNoticeRecipients(): array
    {
        $to = [];
        Queue::assertPushed(SendQueuedEmail::class, function (SendQueuedEmail $job) use (&$to) {
            if ((new \ReflectionProperty($job, 'mailable'))->getValue($job) instanceof AppointmentBookedNotification) {
                $to[] = (new \ReflectionProperty($job, 'recipient'))->getValue($job);
            }

            return true;
        });

        return $to;
    }

    public function test_the_host_is_told_about_the_booking_as_well_as_the_editors(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['notification_settings' => json_encode(['new_sale' => true])]);

        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);
        app(AppointmentService::class)->confirm($sale);

        $this->assertEqualsCanonicalizing([$owner->email, $sam->email], $this->ownerNoticeRecipients());
    }

    public function test_a_host_who_has_left_is_not_told(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['notification_settings' => json_encode(['new_sale' => true])]);
        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);

        RoleUser::where('role_id', $role->id)->where('user_id', $sam->id)->delete();
        app(AppointmentService::class)->confirm($sale);

        $this->assertSame([$owner->email], $this->ownerNoticeRecipients());
    }

    // ---------------------------------------------------------------- what people see

    public function test_the_guest_pages_say_who_the_booking_is_with(): void
    {
        [, $role, , $type] = $this->scheduleWithHost();
        $second = $this->createOwner();
        $second->name = 'Ana Li';
        $second->save();
        $role->users()->attach($second->id, ['level' => 'admin']);
        $this->createAppointmentType($role, ['name' => 'Intro with Ana', 'weekly_windows' => $this->everyDay(), 'host_user_id' => $second->id]);

        // Two hosts: the chooser groups the cards under each one's name.
        $this->get(route('appointments.book', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee(__('messages.appointments_with_host', ['name' => 'Sam Jones']))
            ->assertSee(__('messages.appointments_with_host', ['name' => 'Ana Li']));

        $sale = $this->book($type, $this->tomorrowSlots($type)[0]);
        $this->get(route('appointments.manage', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]))
            ->assertOk()
            ->assertSee(__('messages.appointments_with_host', ['name' => 'Sam Jones']));

        $ics = $this->get(route('appointments.ical', ['event_id' => UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]))->getContent();
        $this->assertStringContainsString('with Sam Jones', $ics);
        $this->assertStringNotContainsString(User::find($type->host_user_id)->email, $ics, 'by name only');
    }

    public function test_the_editor_offers_owners_and_admins_and_names_the_hours(): void
    {
        [$owner, $role, $sam, $type] = $this->scheduleWithHost();
        $viewer = $this->createOwner();
        $viewer->name = 'Vera Viewer';
        $viewer->save();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'edit' => $type->hashedId()]))
            ->assertOk()
            ->assertSee('name="host_user_id"', false)
            ->assertSee('Sam Jones')
            ->assertDontSee('Vera Viewer')
            ->assertSee(__('messages.appointments_host_hours', ['name' => 'Sam Jones']));
    }

    public function test_backups_leave_the_host_out(): void
    {
        [$owner, $role, , $type] = $this->scheduleWithHost();
        $this->book($type, $this->tomorrowSlots($type)[0]);

        $job = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $json = json_encode(app(\App\Services\BackupService::class)->exportSchedules([$role->fresh()], false, $job)['json']);

        $this->assertStringNotContainsString('host_user_id', $json);
        $this->assertStringNotContainsString('host_released_at', $json);
        $this->assertTrue(Event::whereNotNull('appointment_host_user_id')->exists());
    }
}
