<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's settings save may not move it INTO the demo- namespace, and must never move an
 * existing demo- schedule out of it.
 *
 * Role::cleanSubdomain() rewrites a new demo- name (demo-day becomes demoday). new_subdomain is
 * posted on every save of the settings form, so the rewrite must only ever meet a CHANGED value:
 * an unchanged demo-night reaching it would silently rename a live schedule and break every link
 * to it. RoleUpdateRequest refuses a change into demo- with a message instead.
 */
class DemoSubdomainReservationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function save(Role $role, string $newSubdomain, array $overrides = [], ?string $path = null)
    {
        return $this->actingAs($role->user)->put(route('role.update', ['subdomain' => $path ?? $role->subdomain]), $overrides + [
            'name' => $role->name,
            'email' => $role->email,
            'new_subdomain' => $newSubdomain,
            'timezone' => $role->timezone,
        ]);
    }

    public function test_an_unchanged_demo_prefixed_subdomain_saves_and_stays(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night', 'name' => 'Demo Night']);

        $this->save($role, 'demo-night', ['name' => 'Demo Night Live'])->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Demo Night Live', $role->name, 'the save did not go through');
        $this->assertSame('demo-night', $role->subdomain);
    }

    public function test_changing_a_subdomain_into_the_demo_prefix_is_refused(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'harbor-hall']);

        $this->save($role, 'demo-x')->assertSessionHasErrors(['new_subdomain' => __('messages.subdomain_reserved')]);
        $this->assertSame('harbor-hall', $role->fresh()->subdomain);

        // Str::slug() is what decides, so a spaced or capitalised spelling is refused too.
        $this->save($role, 'Demo X')->assertSessionHasErrors(['new_subdomain' => __('messages.subdomain_reserved')]);
        $this->assertSame('harbor-hall', $role->fresh()->subdomain);
    }

    public function test_a_demo_prefixed_schedule_may_not_move_to_another_demo_name(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night']);

        $this->save($role, 'demo-nights')->assertSessionHasErrors(['new_subdomain' => __('messages.subdomain_reserved')]);
        $this->assertSame('demo-night', $role->fresh()->subdomain);
    }

    /**
     * The schedule lookup matches the URL case-blind, so a save can arrive on /Demo-Night/update.
     * Comparing new_subdomain with the URL instead of the stored name read that as a change and
     * sent the untouched demo-night through the rewrite.
     */
    public function test_a_save_on_a_differently_cased_url_leaves_the_subdomain_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night', 'name' => 'Demo Night']);

        $this->save($role, 'demo-night', ['name' => 'Demo Night Live'], 'Demo-Night')->assertSessionHasNoErrors();

        $role->refresh();
        $this->assertSame('Demo Night Live', $role->name, 'the save did not go through');
        $this->assertSame('demo-night', $role->subdomain);
    }

    public function test_an_ordinary_rename_still_works(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demo-night']);

        $this->save($role, 'harbor-nights')->assertSessionHasNoErrors();
        $this->assertSame('harbor-nights', $role->fresh()->subdomain);
    }
}
