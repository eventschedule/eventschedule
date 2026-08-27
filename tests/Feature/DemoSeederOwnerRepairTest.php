<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The hourly demo seeder must survive a demo curator whose owner has gone away.
 *
 * Reported in issue #117 against a live selfhost install: app:setup-demo fired every hour and
 * every hour threw
 *
 *     DemoService::createDemoTalents(): Argument #1 ($user) must be of type User, null given
 *
 * because populateDemoData() reads `$user = $role->user` and hands it straight on. A restored
 * backup or a deleted user leaves roles.user_id dangling, so the relation resolves to null and
 * the whole scheduled run dies - not just the demo data, the command.
 *
 * resetDemoData() already guarded the same value and then called into the unguarded path, which
 * is why the guard it had did nothing. Both halves are pinned here: the seeder repairs the owner
 * (so the install heals on the next run) and populateDemoData() refuses to fatal if it is still
 * missing.
 */
class DemoSeederOwnerRepairTest extends TestCase
{
    use RefreshDatabase;

    private function demoRole(): Role
    {
        return Role::where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->firstOrFail();
    }

    public function test_the_seeder_repairs_a_demo_schedule_whose_owner_is_gone(): void
    {
        $svc = app(DemoService::class);
        $user = $svc->getOrCreateDemoUser();
        $svc->getOrCreateDemoRole($user);

        // Exactly the state the reporter's install was in: the row survives, the owner does not.
        $role = $this->demoRole();
        $role->forceFill(['user_id' => null])->saveQuietly();
        $role->users()->detach();

        $repaired = $svc->getOrCreateDemoRole($user);

        $this->assertSame($user->id, $this->demoRole()->user_id, 'roles.user_id was not repaired');
        $this->assertSame('owner', $repaired->users()->where('users.id', $user->id)->first()?->pivot->level);

        // The returned instance must carry the repaired owner, not the null that was cached on it
        // by the check that spotted the damage - populateDemoData() reads it off this same object.
        $this->assertNotNull($repaired->user, 'the user relation is still the stale null');
        $this->assertSame($user->id, $repaired->user->id);
    }

    public function test_the_repair_does_not_duplicate_an_intact_owner(): void
    {
        $svc = app(DemoService::class);
        $user = $svc->getOrCreateDemoUser();

        $svc->getOrCreateDemoRole($user);
        $role = $svc->getOrCreateDemoRole($user);

        $this->assertSame(1, $role->users()->where('users.id', $user->id)->count());
    }

    public function test_populate_bails_instead_of_fataling_when_the_owner_is_missing(): void
    {
        $svc = app(DemoService::class);
        $role = $svc->getOrCreateDemoRole($svc->getOrCreateDemoUser());

        $role->forceFill(['user_id' => null])->saveQuietly();
        $orphan = $this->demoRole();

        // Before the guard this raised a TypeError out of createDemoTalents() and took the
        // scheduled command down with it.
        $this->assertSame([], $svc->populateDemoData($orphan, false));
    }
}
