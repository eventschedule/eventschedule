<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/schedules and its edit page render in every state the new filters and the deletion card
 * can put them in.
 *
 * Both pages grew a set of mutually exclusive branches - live vs deleted vs deleted-but-never-
 * released, original name free vs taken, owned vs ownerless - and a Blade branch that is never
 * exercised is a branch that breaks silently. These are cheap smoke assertions over all of them.
 */
class AdminSchedulePagesRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function actingAsAdmin(): self
    {
        if (! Route::has('admin.schedules')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    /** Every combination of the owner scope and the deleted state. */
    public function test_the_list_renders_in_every_filter_state(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Live']);
        $this->createRole($owner, 'venue', ['name' => 'Gone', 'is_deleted' => true, 'subdomain_before_delete' => 'gone']);

        $orphan = new Role;
        $orphan->subdomain = 'orphanvenue';
        $orphan->name = 'Orphan';
        $orphan->type = 'venue';
        $orphan->timezone = 'America/New_York';
        $orphan->save();

        $admin = $this->actingAsAdmin();
        foreach ([[], ['status' => 'deleted'], ['owner' => 'unclaimed'], ['owner' => 'any'], ['owner' => 'any', 'status' => 'deleted']] as $params) {
            $admin->get(route('admin.schedules', $params))->assertOk();
        }
    }

    /** Each branch of the deletion card, plus the ownerless copy. */
    public function test_the_edit_page_renders_every_deletion_card_branch(): void
    {
        $owner = $this->createOwner();
        $admin = $this->actingAsAdmin();

        $live = $this->createRole($owner, 'venue', ['subdomain' => 'live-one']);
        $admin->get(route('admin.schedules.edit', ['role' => $live->encodeId()]))->assertOk()
            ->assertSee('Mark as Deleted');

        $deleted = $this->createRole($owner, 'venue', ['subdomain' => 'gone-deleted-9']);
        Role::whereKey($deleted->id)->update(['is_deleted' => true, 'subdomain_before_delete' => 'gone']);
        $admin->get(route('admin.schedules.edit', ['role' => $deleted->encodeId()]))->assertOk()
            ->assertSee('Restore Schedule');

        // The expected outcome of a release that did its job: somebody else has the old name.
        $this->createRole($owner, 'venue', ['subdomain' => 'gone']);
        $admin->get(route('admin.schedules.edit', ['role' => $deleted->encodeId()]))->assertOk()
            ->assertSee('now used by another schedule');

        // The backlog the API, unfollow and merge paths left: deleted, but still holding a name.
        $backlog = $this->createRole($owner, 'venue', ['subdomain' => 'backlog-one']);
        Role::whereKey($backlog->id)->update(['is_deleted' => true]);
        $admin->get(route('admin.schedules.edit', ['role' => $backlog->encodeId()]))->assertOk()
            ->assertSee('Release Subdomain');

        // An ownerless row never had a public page, so the card must not claim one is coming down.
        $orphan = new Role;
        $orphan->subdomain = 'orphan2';
        $orphan->name = 'Orphan';
        $orphan->type = 'venue';
        $orphan->timezone = 'America/New_York';
        $orphan->save();
        $admin->get(route('admin.schedules.edit', ['role' => $orphan->encodeId()]))->assertOk()
            ->assertSee('has no owner and no public page');
    }
}
