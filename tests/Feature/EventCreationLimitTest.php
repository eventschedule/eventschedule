<?php

namespace Tests\Feature;

use App\Exceptions\EventCreationLimitException;
use App\Models\UsageDaily;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\UsageTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Anti-abuse: daily event-creation caps. Covers the per-schedule cap, the
 * per-user aggregate backstop, the selfhost "unlimited" escape hatch, the
 * exception response shaping, and the EventRepo::saveEvent() chokepoint wiring
 * (enforcement + usage tracking).
 *
 * The caps are hosted-only, so tests that exercise them force app.hosted=true
 * and pin small, tier-agnostic limits.
 */
class EventCreationLimitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Force the per-plan caps to small, tier-agnostic values while hosted. */
    private function hostedWithLimits(int $schedule, int $user): void
    {
        config([
            'app.hosted' => true,
            'usage.event_create_daily_limit_trial' => $schedule,
            'usage.event_create_daily_limit_pro' => $schedule,
            'usage.event_create_daily_limit_enterprise' => $schedule,
            'usage.event_create_user_daily_limit_trial' => $user,
            'usage.event_create_user_daily_limit_pro' => $user,
            'usage.event_create_user_daily_limit_enterprise' => $user,
        ]);
    }

    private function seedEventUsage(int $roleId, int $times): void
    {
        for ($i = 0; $i < $times; $i++) {
            UsageDaily::record(UsageTrackingService::EVENT_CREATE, $roleId);
        }
    }

    private function makeEventRequest(User $user): Request
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Cap Test Event',
            'starts_at' => '2026-09-15 20:00:00',
            'duration' => 2,
            'schedule_type' => 'one_time',
        ]);
        $request->setUserResolver(fn () => $user);
        // saveEvent reads a couple of fields via the request() helper, so bind it.
        $this->app->instance('request', $request);

        return $request;
    }

    public function test_per_schedule_cap_blocks_at_limit(): void
    {
        $this->hostedWithLimits(schedule: 3, user: 1000);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->seedEventUsage($role->id, 2);
        $this->assertTrue($role->fresh()->canCreateEvent($owner), 'below the cap should be allowed');

        $this->seedEventUsage($role->id, 1); // now at 3 = the cap
        $this->assertFalse($role->fresh()->canCreateEvent($owner), 'at the cap should be blocked');
    }

    public function test_per_user_backstop_blocks_across_owned_schedules(): void
    {
        // Per-schedule cap is generous; the per-user aggregate is what bites.
        $this->hostedWithLimits(schedule: 1000, user: 3);
        $owner = $this->createOwner();
        $r1 = $this->createRole($owner, 'talent');
        $r2 = $this->createRole($owner, 'venue');

        $this->seedEventUsage($r1->id, 2);
        $this->seedEventUsage($r2->id, 1); // aggregate = 3 across the user's schedules

        $this->assertFalse(
            $r1->fresh()->canCreateEvent($owner),
            'the per-user aggregate should block even when the per-schedule cap is fine'
        );

        // A guest (no acting user) is not subject to the per-user aggregate.
        $this->assertTrue(
            $r1->fresh()->canCreateEvent(null),
            'guest submissions skip the per-user aggregate'
        );
    }

    public function test_guest_without_acting_user_still_hits_schedule_cap(): void
    {
        $this->hostedWithLimits(schedule: 2, user: 1);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->seedEventUsage($role->id, 1);
        $this->assertTrue($role->fresh()->canCreateEvent(null), 'one below the schedule cap (user cap skipped for guests)');

        $this->seedEventUsage($role->id, 1); // at the schedule cap of 2
        $this->assertFalse($role->fresh()->canCreateEvent(null), 'the schedule cap is still enforced for guests');
    }

    public function test_selfhost_is_never_capped(): void
    {
        config(['app.hosted' => false]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // Selfhost short-circuits on the null limit before ever counting usage; a few rows suffice.
        $this->seedEventUsage($role->id, 5);

        $this->assertNull($role->fresh()->eventCreateDailyLimit(), 'selfhost has no per-schedule limit');
        $this->assertTrue($role->fresh()->canCreateEvent($owner), 'selfhost is never capped');
    }

    public function test_exception_renders_422_for_json_and_redirect_for_web(): void
    {
        $exception = new EventCreationLimitException;

        $json = Request::create('/x', 'POST');
        $json->headers->set('Accept', 'application/json');
        $this->assertSame(422, $exception->render($json)->getStatusCode(), 'JSON clients (AI import, API) get a 422');

        $web = Request::create('/x', 'POST');
        $this->assertInstanceOf(RedirectResponse::class, $exception->render($web), 'native form submissions get a redirect back');
    }

    public function test_save_event_records_usage_then_enforces_cap(): void
    {
        Queue::fake();
        Mail::fake();
        $this->hostedWithLimits(schedule: 1, user: 1000);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // First create succeeds and records exactly one EVENT_CREATE for the schedule.
        app(EventRepo::class)->saveEvent($role, $this->makeEventRequest($owner), null, false);

        $this->assertDatabaseHas('usage_daily', [
            'role_id' => $role->id,
            'operation' => UsageTrackingService::EVENT_CREATE,
            'count' => 1,
        ]);
        $this->assertSame(1, \App\Models\Event::count(), 'exactly one event was created');

        // Second create is blocked at the chokepoint: the schedule cap (1) is now hit.
        $this->expectException(EventCreationLimitException::class);
        app(EventRepo::class)->saveEvent($role, $this->makeEventRequest($owner), null, false);
    }
}
