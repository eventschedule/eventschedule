<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::releasedSubdomain() - the name a schedule takes when it gives its own up.
 *
 * roles.subdomain is UNIQUE, so freeing a name for another schedule is necessarily a rename of
 * the row that holds it. Everything here is about that rename being safe to apply blind: unique
 * against the index, stable under a double submit, and inside the 50-character cap the owner's
 * own settings form enforces, so the row stays editable through the normal UI afterwards.
 */
class RoleReleasedSubdomainTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_it_appends_deleted_and_the_row_id(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->releasedSubdomain());
    }

    /**
     * The id, not a counter, is what makes this unique - which also makes it deterministic, so a
     * double-submitted form recomputes the same value instead of releasing twice.
     */
    public function test_it_is_stable_across_repeated_calls(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $this->assertSame($role->releasedSubdomain(), $role->releasedSubdomain());
    }

    /**
     * Re-releasing a row whose Restore could not reclaim its name must not nest the suffix into
     * tel-aviv-deleted-42-deleted-42. The ORIGINAL name is the base whenever we still have it.
     */
    public function test_it_releases_from_the_original_name_not_the_released_one(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'tel-aviv']);

        $role->subdomain_before_delete = 'tel-aviv';
        $role->subdomain = 'tel-aviv-deleted-'.$role->id;
        $role->save();

        $this->assertSame('tel-aviv-deleted-'.$role->id, $role->releasedSubdomain());
    }

    /**
     * Subdomains longer than the 50-character cap were grandfathered in before it existed
     * (SitemapController::isListable), so this genuinely has to truncate rather than assume.
     * Staying inside the cap keeps the row editable through RoleUpdateRequest's max:50 later.
     */
    public function test_it_truncates_to_fifty_characters_without_a_trailing_hyphen(): void
    {
        $long = str_repeat('abcde-', 12);  // 72 chars, hyphen every 6
        $role = $this->createRole($this->createOwner(), 'venue');
        Role::whereKey($role->id)->update(['subdomain' => $long]);
        $role->refresh();

        $released = $role->releasedSubdomain();

        $this->assertLessThanOrEqual(50, strlen($released));
        $this->assertStringEndsWith('-deleted-'.$role->id, $released);
        $this->assertStringNotContainsString('--', $released);
    }

    /** A live schedule squatting the composed name must not collide with the released one. */
    public function test_it_steps_around_a_name_a_live_schedule_already_holds(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['subdomain' => 'tel-aviv']);
        $this->createRole($owner, 'venue', ['subdomain' => 'tel-aviv-deleted-'.$role->id]);

        $this->assertSame('tel-aviv-deleted-'.$role->id.'-2', $role->releasedSubdomain());
    }

    /**
     * cleanSubdomain() calls GeminiUtils::translate() for a name Str::slug mangles. Releasing is
     * an admin click, not a naming decision, so it must never reach for the network - and with no
     * API key configured a fallthrough would surface as a random 8-character name instead.
     */
    public function test_a_non_latin_name_is_released_without_translating_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'hofaot-bagan']);
        $role->name = 'הופעות בגן';
        $role->saveQuietly();

        $this->assertSame('hofaot-bagan-deleted-'.$role->id, $role->releasedSubdomain());
    }
}
