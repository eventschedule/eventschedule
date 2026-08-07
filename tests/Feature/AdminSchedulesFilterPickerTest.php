<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The search box on /admin/schedules and the table below it must be drawn from the same
 * set of schedules.
 *
 * They were not: the autocomplete called role.search-subdomains unscoped while the table
 * starts from whereNotNull('user_id') plus the demo exclusions. Auto-created talent and
 * venue schedules have no user_id (EventRepo::saveEvent builds them without one), so they
 * showed up in the dropdown and then filtered to an empty table. It read as a
 * non-English-names bug because those auto-created schedules are exactly the ones that
 * carry the source's own language.
 */
class AdminSchedulesFilterPickerTest extends TestCase
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

    /** An auto-created schedule, the way EventRepo::saveEvent makes one: no owner. */
    private function createUnclaimedRole(string $name, string $type = 'venue'): Role
    {
        $role = new Role;
        $role->subdomain = 'auto'.strtolower(Str::random(10));
        $role->name = $name;
        $role->type = $type;
        $role->timezone = 'America/New_York';
        $role->save();

        return $role->fresh();
    }

    public function test_the_picker_never_offers_a_schedule_the_table_cannot_show(): void
    {
        $admin = $this->actingAsAdmin();

        $unclaimed = $this->createUnclaimedRole('הופעות בגן');

        $results = $admin->getJson(route('role.search-subdomains', [
            'q' => $unclaimed->subdomain,
            'admin_listable' => 1,
        ]))->assertOk()->json();

        $this->assertSame([], $results, 'an ownerless schedule is unreachable in the table, so it must not be offered');

        // And the reproduction it caused: picking it filtered to nothing.
        $filtered = $admin->get(route('admin.schedules', ['search' => $unclaimed->subdomain]))->assertOk();
        $this->assertSame(0, $filtered->viewData('roles')->total());
    }

    public function test_a_claimed_schedule_with_a_non_english_name_is_offered_and_filters(): void
    {
        $admin = $this->actingAsAdmin();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'מועדון הבלוז']);

        $results = $admin->getJson(route('role.search-subdomains', [
            'q' => 'הבלוז',
            'admin_listable' => 1,
        ]))->assertOk()->json();

        $this->assertSame([$role->subdomain], array_column($results, 'subdomain'));

        // The autocomplete writes the subdomain into the search box, so that is what filters.
        $filtered = $admin->get(route('admin.schedules', ['search' => $role->subdomain]))->assertOk();
        $this->assertSame(1, $filtered->viewData('roles')->total());
    }

    public function test_demo_schedules_are_not_offered_either(): void
    {
        $admin = $this->actingAsAdmin();

        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', [
            'subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN,
            'name' => 'Demo Schedule',
        ]);

        $results = $admin->getJson(route('role.search-subdomains', [
            'q' => DemoService::DEMO_ROLE_SUBDOMAIN,
            'admin_listable' => 1,
        ]))->assertOk()->json();

        $this->assertSame([], $results);
    }

    public function test_deleted_schedules_are_never_offered(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Closed Venue', 'is_deleted' => true]);

        $results = $this->actingAs($owner)
            ->getJson(route('role.search-subdomains', ['q' => 'Closed Venue']))
            ->assertOk()
            ->json();

        $this->assertSame([], $results);
    }

    /**
     * % and _ are LIKE wildcards. Unescaped, "A%e" matches "Alpha Venue" and "A_pha"
     * matches "Alpha", so a name containing either character searches as a pattern
     * instead of as text.
     */
    public function test_wildcards_in_the_query_are_matched_literally(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Alpha Venue']);

        foreach (['A%e', 'A_pha'] as $query) {
            $results = $this->actingAs($owner)
                ->getJson(route('role.search-subdomains', ['q' => $query]))
                ->assertOk()
                ->json();

            $this->assertSame([], $results, "\"{$query}\" should be searched as text, not as a LIKE pattern");
        }

        // The same name still matches when it is actually typed.
        $this->assertCount(1, $this->actingAs($owner)
            ->getJson(route('role.search-subdomains', ['q' => 'Alpha']))
            ->json());
    }
}
