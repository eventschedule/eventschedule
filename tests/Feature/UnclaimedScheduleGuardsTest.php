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

    /**
     * An anonymous request is filed under the schedule's owner (events.user_id is NOT NULL), and a
     * placeholder with no owner at all has nobody to stand in, so both request forms answered a
     * signed-out visitor with a server error. They now ask for the account the form offers, in
     * the form's own error slot, and write nothing until they have one.
     */
    public function test_a_signed_out_request_to_an_ownerless_placeholder_asks_for_an_account(): void
    {
        $talent = $this->placeholder();
        $venue = $this->placeholder(['type' => 'venue', 'require_account' => false]);
        $this->assertNull($talent->user_id, 'fixture: nobody to stand in');

        // What each one's GET offers: the booking form for the act, the AI import for the venue.
        $this->get('/'.$talent->subdomain.'/booking-request')->assertOk()->assertSee('name="create_account"', false);
        $this->get('/'.$venue->subdomain.'/guest-add')->assertOk();

        $this->postJson('/'.$talent->subdomain.'/booking-request', [
            'event_name' => 'Walk-in Night',
            'contact_name' => 'A Fan',
            'contact_email' => 'fan@gmail.com',
        ])->assertStatus(422)
            ->assertJsonPath('errors.create_account.0', __('messages.request_needs_account'));

        $this->postJson('/'.$venue->subdomain.'/guest-add', ['name' => 'Walk-in Night'])
            ->assertStatus(422)
            ->assertJsonPath('errors.create_account.0', __('messages.request_needs_account'));

        $this->assertSame(0, \App\Models\Event::count());
        $this->assertSame(0, \App\Models\User::count());

        // Signed in, the visitor is the one the request is filed under, as before.
        $this->actingAs($this->createOwner())
            ->postJson('/'.$venue->subdomain.'/guest-add', ['name' => 'Walk-in Night'])
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame(1, \App\Models\Event::count());
    }

    /** Ticking the form's own account box is the way through, and files the request under that account. */
    public function test_creating_the_account_the_form_offers_sends_the_request(): void
    {
        $talent = $this->placeholder();

        $this->postJson('/'.$talent->subdomain.'/booking-request', [
            'event_name' => 'Walk-in Night',
            'contact_name' => 'A Fan',
            'contact_email' => 'fan@gmail.com',
            'create_account' => '1',
            'password' => 'sup3rsecret',
            'terms' => '1',
        ])->assertOk()->assertJsonPath('success', true);

        $fan = \App\Models\User::where('email', 'fan@gmail.com')->firstOrFail();
        $this->assertSame($fan->id, \App\Models\Event::sole()->user_id);
    }
}
