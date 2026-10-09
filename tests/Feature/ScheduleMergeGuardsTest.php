<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Merging schedules: what "nobody runs this" has to mean, and whose team comes along.
 *
 * Two rules the merge tools rested on were wider than they read:
 *
 *   - "unclaimed" was isClaimed(), which also asks for a verified contact. On hosted every change
 *     of email clears that stamp, so an OWNED schedule read as a placeholder, and anybody following
 *     it could merge it away. Role::isEditableBy() and canMergeRoles() ask hasRealOwner() as well;
 *   - the curator's "merge duplicate venues" proved only that every venue is in one duplicate
 *     group (same name, same country, on one of the curator's coming events). performMerge()
 *     carries a source's team to the target at the level each person held, so a caller who merged
 *     their own unverified venue into somebody else's claimed one ended as an owner of it. A source
 *     with a team is merged only onto a venue the caller runs, a source somebody runs only by its
 *     own people, and a venue its owner deleted is not revived by somebody else.
 *
 * Folding a real placeholder into the real venue is unchanged.
 */
class ScheduleMergeGuardsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_follower_cannot_merge_away_a_schedule_somebody_runs(): void
    {
        $owned = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        Role::where('id', $owned->id)->update(['email_verified_at' => null, 'phone_verified_at' => null]);
        $this->assertFalse($owned->fresh()->isClaimed(), 'sanity check: owned, with no verified contact');

        $follower = $this->createOwner();
        $mine = $this->createRole($follower, 'venue', ['name' => 'Harbour Hall']);
        $this->followRole($follower, $owned);

        $this->actingAs($follower)->post(route('role.merge', ['subdomain' => $owned->subdomain]), ['target_subdomain' => $mine->subdomain]);

        $this->assertFalse((bool) $owned->fresh()->is_deleted);
    }

    /** @return array{0: User, 1: Role, 2: Role, 3: Role} the curator's owner, the curator, somebody's claimed venue, the caller's own unverified venue of the same name */
    private function duplicateVenues(): array
    {
        $caller = $this->createOwner();
        $curator = $this->createCurator($caller, ['country_code' => 'us']);

        $claimed = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall', 'country_code' => 'us']);
        $callers = $this->createRole($caller, 'venue', ['name' => 'Harbour Hall', 'country_code' => 'us']);
        Role::where('id', $callers->id)->update(['email_verified_at' => null, 'phone_verified_at' => null]);

        foreach ([$claimed, $callers] as $venue) {
            $event = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'user_id' => $caller->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        return [$caller, $curator, $claimed->fresh(), $callers->fresh()];
    }

    public function test_a_curators_merge_does_not_put_the_caller_on_a_venue_they_do_not_run(): void
    {
        [$caller, $curator, $claimed, $callers] = $this->duplicateVenues();

        $this->actingAs($caller)->post(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]), [
            'target_id' => UrlUtils::encodeId($claimed->id),
            'source_ids' => [UrlUtils::encodeId($callers->id)],
        ]);

        $this->assertFalse($caller->fresh()->isEditor($claimed->subdomain));
        $this->assertSame(0, DB::table('role_user')->where('role_id', $claimed->id)->where('user_id', $caller->id)->where('level', '!=', 'follower')->count());
    }

    public function test_a_curator_still_merges_an_unowned_duplicate_into_the_real_venue(): void
    {
        $caller = $this->createOwner();
        $curator = $this->createCurator($caller, ['country_code' => 'us']);
        $claimed = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall', 'country_code' => 'us']);
        $placeholder = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall', 'country_code' => 'us']);
        DB::table('role_user')->where('role_id', $placeholder->id)->delete();
        Role::where('id', $placeholder->id)->update(['user_id' => null, 'email' => null, 'email_verified_at' => null, 'phone_verified_at' => null]);

        foreach ([$claimed, $placeholder] as $venue) {
            $event = $this->createEvent($curator, ['creator_role_id' => $curator->id, 'user_id' => $caller->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        $this->actingAs($caller)->post(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]), [
            'target_id' => UrlUtils::encodeId($claimed->id),
            'source_ids' => [UrlUtils::encodeId($placeholder->id)],
        ]);

        $this->assertTrue((bool) $placeholder->fresh()->is_deleted, 'the placeholder was folded into the real venue');
        $this->assertSame(2, DB::table('event_role')->where('role_id', $claimed->id)->count());
    }
}
