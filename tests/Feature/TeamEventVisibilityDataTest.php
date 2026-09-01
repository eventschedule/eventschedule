<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The event_role half of Event::scopeManagedThrough().
 *
 * The scope's first disjunct is `events.user_id = me`, which the OWNER always matches and a team
 * member never can - events.user_id is the payment account, not the schedule. So the owner is
 * structurally immune to every fault inside the event_role EXISTS, and any damage there shows up
 * as "the owner sees the sales, the admin sees an empty page". Every fixture here is built to be
 * owner-visible and member-invisible for exactly that reason; one that is invisible to both would
 * pass against the bug.
 *
 * Of the faults available, only a MISSING pivot row is fatal on its own: arm 2 (is_accepted = true
 * on a non-curator schedule) rescues a wrong or NULL creator_role_id, and arm 1
 * (role_id = creator_role_id) rescues an unaccepted pivot. The negative control below pins that,
 * so the reasoning is tested rather than merely asserted in a comment.
 */
class TeamEventVisibilityDataTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function scheduleWithASale(array $roleAttrs = [], array $eventAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, $roleAttrs['type'] ?? 'venue', $roleAttrs);
        $event = $this->createEvent($role, array_merge([
            'tickets_enabled' => true,
            'creator_role_id' => $role->id,
        ], $eventAttrs));
        $ticket = $this->createTicket($event, ['price' => 25]);
        $this->createSale($event, $role, [
            'name' => 'Zaphod Beeblebrox',
            'payment_amount' => 25,
            'status' => 'paid',
        ], $ticket);

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        return [$owner, $role, $event, $admin];
    }

    public function test_a_missing_creator_pivot_hides_sales_from_the_team_but_not_the_owner(): void
    {
        config(['app.hosted' => true]);

        [$owner, $role, $event, $admin] = $this->scheduleWithASale();

        // Positive control: with the row present the admin sees the sale, so the assertion below
        // cannot pass on a fixture that was never visible.
        $this->actingAs($admin)->get(route('sales'))->assertOk()->assertSee('Zaphod Beeblebrox');

        // The fault. A save performed under a sibling schedule used to do this on every call.
        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $role->id)->delete();

        // manageableRoles() and planBlockedRoles() memoize on the model instance, so re-resolve.
        $this->actingAs($admin->fresh())->get(route('sales'))
            ->assertOk()
            ->assertDontSee('Zaphod Beeblebrox');

        // ...while the owner still sees it through events.user_id. This asymmetry is the bug:
        // nobody with the power to notice ever does.
        $this->actingAs($owner->fresh())->get(route('sales'))
            ->assertOk()
            ->assertSee('Zaphod Beeblebrox');

        $this->artisan('app:check-data', ['check' => 'creator-roles', '--fix' => true]);

        $this->actingAs($admin->fresh())->get(route('sales'))
            ->assertOk()
            ->assertSee('Zaphod Beeblebrox');
    }

    /**
     * Negative control for the table in the plan: a wrong creator_role_id is NOT fatal by itself.
     * If this ever fails, the diagnosis above is wrong, not just the fixture.
     */
    public function test_an_accepted_pivot_rescues_a_wrong_creator_role_id(): void
    {
        config(['app.hosted' => true]);

        $stranger = $this->createRole($this->createOwner());

        // creator_role_id points somewhere unrelated - the shape the 2025-01-15 backfill left on
        // every pre-2025 event of a multi-schedule owner - but the pivot is accepted.
        [, , , $admin] = $this->scheduleWithASale([], [
            'creator_role_id' => $stranger->id,
            'is_accepted' => true,
        ]);

        $this->actingAs($admin)->get(route('sales'))->assertOk()->assertSee('Zaphod Beeblebrox');
    }

    public function test_the_repair_reattaches_the_pivot_and_never_rewrites_creator_role_id(): void
    {
        $owner = $this->createOwner();
        $creator = $this->createRole($owner);
        $other = $this->createRole($owner);

        $event = $this->createEvent($creator, ['creator_role_id' => $creator->id]);
        $event->roles()->attach($other->id, ['is_accepted' => true]);

        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $creator->id)->delete();

        $this->artisan('app:check-data', ['check' => 'creator-roles', '--fix' => true]);

        // The old repair stamped $other onto creator_role_id, which left the member just as blind
        // and destroyed the record of who created the event.
        $this->assertSame($creator->id, $event->fresh()->creator_role_id, 'provenance is untouched');
        $this->assertTrue(
            DB::table('event_role')->where('event_id', $event->id)->where('role_id', $creator->id)->exists(),
            'the missing membership row is what gets repaired'
        );
    }

    public function test_a_null_creator_role_id_is_reported_and_never_guessed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);

        Event::whereKey($event->id)->update(['creator_role_id' => null]);

        $this->artisan('app:check-data', ['check' => 'creator-roles', '--fix' => true])
            ->expectsOutputToContain('Null creator_role_id');

        $this->assertNull($event->fresh()->creator_role_id, 'a creator is never invented');
    }

    public function test_a_save_that_does_not_name_the_creator_keeps_its_pivot_row(): void
    {
        $owner = $this->createOwner();
        $creator = $this->createRole($owner, 'talent');
        $other = $this->createRole($owner, 'venue');

        $event = $this->createEvent($creator, ['creator_role_id' => $creator->id]);
        $event->roles()->attach($other->id, ['is_accepted' => true]);

        // A save under the OTHER schedule, naming nothing about the creator. This is the shape
        // Api\ApiEventController::update() produces on every call: its $currentRole comes from an
        // unordered $event->roles loop and its request whitelist has no field for the creator.
        $request = Request::create('/', 'POST', [
            'name' => 'Renamed Under A Sibling Schedule',
            'starts_at' => now()->addMonths(2)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);
        $request->setUserResolver(fn () => $owner);

        app(EventRepo::class)->saveEvent($other, $request, $event->fresh());

        $this->assertTrue(
            DB::table('event_role')->where('event_id', $event->id)->where('role_id', $creator->id)->exists(),
            'sync() must not drop the creating schedule'
        );
    }

    public function test_uncurate_declines_rather_than_detaching_the_creating_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->actingAs($owner)->delete(route('event.uncurate', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]));

        $pivot = DB::table('event_role')
            ->where('event_id', $event->id)
            ->where('role_id', $role->id)
            ->first();

        $this->assertNotNull($pivot, 'the creating schedule keeps its row');
        // EventRole does not cast is_accepted, so compare the raw value: a truthiness test would
        // collapse declined (0) into pending (null).
        $this->assertSame(0, (int) $pivot->is_accepted);
    }

    /**
     * The diagnostic has to survive the broken state, not just the healthy one - that is the only
     * state anybody will ever run it in.
     */
    public function test_the_diagnostic_names_a_missing_pivot_row(): void
    {
        config(['app.hosted' => true]);

        [, $role, $event, $admin] = $this->scheduleWithASale();

        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $role->id)->delete();

        $this->artisan('app:diagnose-team-access', [
            'email' => $admin->email,
            'subdomain' => $role->subdomain,
        ])->expectsOutputToContain('MISSING')->assertSuccessful();
    }

    public function test_the_diagnostic_survives_a_healthy_schedule_and_a_bad_argument(): void
    {
        [, $role, , $admin] = $this->scheduleWithASale();

        $this->artisan('app:diagnose-team-access', [
            'email' => $admin->email,
            'subdomain' => $role->subdomain,
        ])->assertSuccessful();

        $this->artisan('app:diagnose-team-access', [
            'email' => 'nobody@gmail.com',
            'subdomain' => $role->subdomain,
        ])->assertFailed();
    }

    public function test_a_booking_request_records_its_creator_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['accept_requests' => true]);

        $this->post(route('event.booking_request.store', ['subdomain' => $role->subdomain]), [
            'event_name' => 'Booked By A Stranger',
            'date' => now()->addDays(4)->format('Y-m-d'),
            'start_time' => '19:00',
            'contact_name' => 'Stranger',
            'contact_email' => 'stranger@gmail.com',
        ]);

        $event = Event::where('name', 'Booked By A Stranger')->firstOrFail();

        $this->assertSame($role->id, $event->creator_role_id);
    }
}
