<?php

namespace Tests\Feature;

use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The "Unverified" stat card on /admin/schedules.
 *
 * This number used to be a row in the dashboard's "Needs attention" panel, which was
 * wrong: an unverified schedule is waiting on its owner, not on an admin, and nothing
 * ever transitions an abandoned signup, so the row sat permanently non-zero. It now
 * lives beside the plan cards, whose counts are verified-only - free + pro + enterprise
 * + unverified is every non-demo schedule with an owner.
 *
 * The property worth pinning is the one AdminAlertService was careful about: the count
 * and the list it links to must never disagree.
 */
class AdminSchedulesUnverifiedCountTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
    }

    /** EnsureUserIsAdmin gates every /admin route on a confirmed password this session. */
    private function actingAsAdmin(): self
    {
        // The /admin routes are registered at boot from IS_HOSTED, which differs by
        // environment. Overriding the config in setUp() cannot register a route, so skip
        // rather than fail with a confusing RouteNotFoundException.
        if (! Route::has('admin.schedules')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    public function test_the_stat_matches_the_list_it_links_to(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $this->createRole($owner, 'talent', ['email_verified_at' => null]);
        $this->createRole($owner, 'venue');
        $this->createRole($owner, 'curator');

        $admin = $this->actingAsAdmin();

        $admin->get(route('admin.schedules'))
            ->assertOk()
            ->assertViewHas('unverifiedCount', 3)
            // The card is the click-through the removed alert row used to provide.
            ->assertSee(route('admin.schedules', ['verification' => 'unverified']));

        $filtered = $admin->get(route('admin.schedules', ['verification' => 'unverified']))
            ->assertOk();

        $this->assertSame(3, $filtered->viewData('roles')->total());
    }

    /**
     * isClaimed() is email OR phone, and so is the list's unverified filter. A schedule
     * that verified by SMS is live and must not be counted.
     */
    public function test_a_phone_verified_schedule_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', [
            'email_verified_at' => null,
            'phone' => '+15551234567',
            'phone_verified_at' => now(),
        ]);

        $this->actingAsAdmin()->get(route('admin.schedules'))
            ->assertOk()
            ->assertViewHas('unverifiedCount', 0);
    }

    public function test_demo_schedules_are_excluded(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', [
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
            'email_verified_at' => null,
        ]);
        $this->createRole($owner, 'venue', [
            'subdomain' => 'demo-acme',
            'email_verified_at' => null,
        ]);
        $this->createRole($owner, 'venue', ['email_verified_at' => null]);

        $this->actingAsAdmin()->get(route('admin.schedules'))
            ->assertOk()
            ->assertViewHas('unverifiedCount', 1);
    }

    /** An orphan row with no owner is not a signup that failed to verify. */
    public function test_a_schedule_with_no_owner_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['email_verified_at' => null]);
        $role->user_id = null;
        $role->saveQuietly();

        $this->actingAsAdmin()->get(route('admin.schedules'))
            ->assertOk()
            ->assertViewHas('unverifiedCount', 0);
    }
}
