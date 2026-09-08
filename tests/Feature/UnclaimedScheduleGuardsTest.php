<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What must be true of an auto-created placeholder before its page can be published.
 *
 * These rows describe a named third party who never signed up, so anything the app does on a
 * schedule's behalf - monetizing it, letting a visitor attach themselves to it - has to stop at
 * the boundary between "somebody runs this" and "we invented it while entering an event".
 */
class UnclaimedScheduleGuardsTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function placeholder(array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $attrs['subdomain'] ?? strtolower(Str::random(12));
        $role->type = $attrs['type'] ?? 'talent';
        $role->name = $attrs['name'] ?? 'Unclaimed Act';
        $role->timezone = 'America/New_York';
        $role->plan_type = 'free';

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role->fresh();
    }

    public function test_a_placeholder_never_carries_ads(): void
    {
        config(['ads.enabled' => true, 'app.is_nexus' => false]);

        $owner = $this->createOwner();
        $free = $this->createRole($owner, 'venue', ['plan_type' => 'free', 'plan_expires' => null]);

        // The control: without this the assertion below could pass for any reason at all.
        $this->assertTrue($free->fresh()->showAds(), 'a real free schedule is the ad-eligible case');

        $this->assertFalse($this->placeholder()->showAds());
    }

    public function test_a_placeholder_carrying_the_creators_user_id_never_carries_ads(): void
    {
        config(['ads.enabled' => true, 'app.is_nexus' => false]);

        $curator = $this->createOwner();
        $placeholder = $this->placeholder(['type' => 'venue']);
        $placeholder->user_id = $curator->id;
        $placeholder->save();
        $placeholder->users()->attach($curator->id, ['level' => 'follower']);

        $this->assertFalse($placeholder->fresh()->showAds());
    }

    public function test_a_stranger_cannot_follow_a_placeholder(): void
    {
        // isEditableBy() grants edit rights on an unclaimed schedule to anyone who follows it, and
        // canMergeRoles() needs no more than that, so a public Follow here is a path to absorbing
        // somebody else's page. unfollow() would then delete it outright.
        $stranger = $this->createOwner();
        $placeholder = $this->placeholder();

        $this->actingAs($stranger)
            ->get(route('role.follow', ['subdomain' => $placeholder->subdomain]))
            ->assertRedirect();

        $this->assertDatabaseMissing('role_user', [
            'role_id' => $placeholder->id,
            'user_id' => $stranger->id,
        ]);
        $this->assertFalse($placeholder->fresh()->isEditableBy($stranger));
    }

    public function test_a_real_schedule_can_still_be_followed(): void
    {
        $owner = $this->createOwner();
        $fan = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($fan)->get(route('role.follow', ['subdomain' => $role->subdomain]));

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id,
            'user_id' => $fan->id,
            'level' => 'follower',
        ]);
    }

    public function test_the_claim_url_answers_only_for_a_placeholder(): void
    {
        $owner = $this->createOwner();

        $placeholder = $this->placeholder();
        $this->assertNotSame('', $placeholder->getClaimUrl());
        $this->assertSame('', $placeholder->getGuestUrl(), 'the two must not both answer');

        $this->assertSame('', $this->createRole($owner)->getClaimUrl());
        $this->assertSame('', $this->placeholder(['is_deleted' => true])->getClaimUrl());
    }
}
