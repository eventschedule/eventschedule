<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Handing an auto-created placeholder schedule to the person it was created for.
 *
 * EventRepo::saveEvent() mints an ownerless Role every time an organizer lists a performer or a
 * venue by name, and the app has always offered to email that act an invitation to claim it. The
 * claim itself lived in VerifyEmailController and was unreachable: RegisteredUserController marks
 * a new account verified inline, so the verification link that block hangs off is never sent.
 */
class ScheduleClaimByEmailTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    /**
     * A placeholder exactly as EventRepo::saveEvent() writes one: a name, a subdomain, a contact
     * the organizer typed, and no owner of any kind.
     */
    private function placeholder(array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $attrs['subdomain'] ?? 'act'.strtolower(\Illuminate\Support\Str::random(10));
        $role->type = $attrs['type'] ?? 'talent';
        $role->name = $attrs['name'] ?? 'The Wandering Few';
        $role->email = $attrs['email'] ?? 'band@gmail.com';
        $role->timezone = 'America/New_York';
        $role->plan_type = 'free';

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role->fresh();
    }

    private function register(string $email): void
    {
        $this->post('/sign_up', [
            'name' => 'Claimant',
            'email' => $email,
            'password' => 'password',
        ])->assertSessionHasNoErrors();
    }

    public function test_registering_with_a_placeholders_address_hands_it_over(): void
    {
        $placeholder = $this->placeholder(['email' => 'band@gmail.com']);

        $this->register('band@gmail.com');

        $user = User::whereEmail('band@gmail.com')->firstOrFail();
        $placeholder->refresh();

        $this->assertSame($user->id, $placeholder->user_id, 'the placeholder should now be owned');
        $this->assertNotNull($placeholder->email_verified_at, 'and claimed, not merely assigned');
        $this->assertTrue($placeholder->isClaimed());
        $this->assertSame('owner', $user->roles()->where('roles.id', $placeholder->id)->first()?->pivot?->level);
        $this->assertSame($placeholder->id, $user->fresh()->default_role_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditService::SCHEDULE_CLAIM,
            'user_id' => $user->id,
            'model_id' => $placeholder->id,
        ]);
    }

    public function test_a_real_schedule_whose_owner_pivot_is_missing_is_never_claimed(): void
    {
        // The drift CheckData::checkRoleOwnership() exists to repair: a live, verified, owned
        // schedule that has lost its role_user row. It matches ownerless(), so without the
        // verified-column guard anyone knowing the address could register and take it.
        $owner = $this->createOwner();
        $victim = $this->createRole($owner, 'venue', ['email' => 'venue@gmail.com']);
        $victim->users()->detach();

        $this->assertTrue($victim->fresh()->isClaimed());

        $this->register('venue@gmail.com');

        $victim->refresh();
        $this->assertSame($owner->id, $victim->user_id, 'the real owner must keep the schedule');
        $this->assertDatabaseMissing('audit_logs', ['action' => AuditService::SCHEDULE_CLAIM]);
    }

    public function test_a_placeholder_carrying_the_creators_user_id_is_still_claimable(): void
    {
        // ConvertsLocationToVenue stamps the CURATOR's user_id on every venue it invents and
        // attaches that user only as a follower, so whereNull('user_id') never saw these.
        $curator = $this->createOwner();
        $placeholder = $this->placeholder(['type' => 'venue', 'email' => 'hall@gmail.com']);
        $placeholder->user_id = $curator->id;
        $placeholder->save();
        $placeholder->users()->attach($curator->id, ['level' => 'follower']);

        $this->register('hall@gmail.com');

        $claimant = User::whereEmail('hall@gmail.com')->firstOrFail();
        $this->assertSame($claimant->id, $placeholder->fresh()->user_id);
    }

    public function test_a_claimant_who_already_follows_the_placeholder_ends_up_with_one_owner_row(): void
    {
        // role_user is unique on (user_id, role_id) and the organizer who created the placeholder
        // is attached to it as a follower, so attach() threw a raw 1062 here.
        $placeholder = $this->placeholder(['email' => 'solo@gmail.com']);
        $follower = User::factory()->create(['email' => 'solo@gmail.com', 'email_verified_at' => null]);
        $placeholder->users()->attach($follower->id, ['level' => 'follower']);

        $follower->email_verified_at = now();
        $follower->save();
        $this->assertTrue($follower->fresh()->claimRolesByEmail());

        $rows = \DB::table('role_user')->where('role_id', $placeholder->id)->where('user_id', $follower->id)->get();
        $this->assertCount(1, $rows);
        $this->assertSame('owner', $rows->first()->level);
    }

    public function test_a_deleted_placeholder_is_not_resurrected_by_a_signup(): void
    {
        // unfollow() soft-deletes an ownerless row when its last follower leaves, so inheriting
        // one would bring back a page somebody removed.
        $placeholder = $this->placeholder(['email' => 'gone@gmail.com', 'is_deleted' => true]);

        $this->register('gone@gmail.com');

        $this->assertNull($placeholder->fresh()->user_id);
    }

    public function test_past_guest_purchases_are_linked_at_registration(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'user_id' => null]);

        $this->register('buyer@gmail.com');

        $buyer = User::whereEmail('buyer@gmail.com')->firstOrFail();
        $this->assertSame($buyer->id, $sale->fresh()->user_id);
    }

    public function test_the_admin_listing_scopes_partition_every_schedule(): void
    {
        $owner = $this->createOwner();
        $owned = $this->createRole($owner);
        $orphan = $this->placeholder(['email' => 'a@gmail.com']);
        $curatorStamped = $this->placeholder(['email' => 'b@gmail.com']);
        $curatorStamped->user_id = $owner->id;
        $curatorStamped->save();
        $curatorStamped->users()->attach($owner->id, ['level' => 'follower']);

        $ownedIds = Role::adminListable()->pluck('id')->all();
        $unclaimedIds = Role::adminListableUnclaimed()->pluck('id')->all();

        $this->assertContains($owned->id, $ownedIds);
        $this->assertContains($orphan->id, $unclaimedIds);
        $this->assertContains($curatorStamped->id, $unclaimedIds, 'a curator-stamped placeholder belongs in the unclaimed list');
        $this->assertEmpty(array_intersect($ownedIds, $unclaimedIds), 'the two lists must not overlap');
        $this->assertEqualsCanonicalizing(
            Role::adminListableAny()->pluck('id')->all(),
            array_merge($ownedIds, $unclaimedIds),
            'and together they must cover everything'
        );
    }
}
