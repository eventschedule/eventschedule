<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Somebody who signed up in a schedule's "Stay up to date" panel can go on to run a schedule of
 * their own, through each of the three doors that give the account its first password, and the
 * Following page they land on offers it.
 */
class SubscriberBecomesOrganizerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function subscribe(Role $role, string $email): RoleSubscriber
    {
        $this->post(route('role.audience.join', ['subdomain' => $role->subdomain]), [
            'email' => $email,
            'name' => 'A Fan',
        ]);

        return RoleSubscriber::where('role_id', $role->id)->where('email', $email)->firstOrFail();
    }

    private function assertCanCreateSchedule(User $user): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('new', ['type' => 'talent']), false);
        $this->get(route('new', ['type' => 'talent']))->assertOk();

        $this->post(route('role.store'), [
            'type' => 'talent',
            'name' => 'Fan Band',
            'email' => $user->email,
            'timezone' => 'America/New_York',
            'language_code' => 'en',
        ])->assertRedirect();

        $own = Role::where('user_id', $user->id)->where('name', 'Fan Band')->first();
        $this->assertNotNull($own, 'the former subscriber owns a schedule of their own');
        $this->assertTrue($user->fresh()->isConnected($own->subdomain));
        $this->assertSame('owner', \DB::table('role_user')->where('role_id', $own->id)->where('user_id', $user->id)->value('level'));
    }

    public function test_password_on_the_confirm_page_then_a_schedule(): void
    {
        $role = $this->createRole($this->createOwner());
        $sub = $this->subscribe($role, 'fan@fans.test');

        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]), ['password' => 'sup3rsecret']);

        $user = User::where('email', 'fan@fans.test')->firstOrFail();
        $this->assertSame('subscriber', $user->signup_intent);
        $this->assertAuthenticatedAs($user);

        $this->assertCanCreateSchedule($user);
    }

    public function test_one_shot_form_on_the_done_page_then_a_schedule(): void
    {
        $role = $this->createRole($this->createOwner());
        $sub = $this->subscribe($role, 'fan@fans.test');

        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]));
        $this->assertGuest();
        $this->post(route('subscriber.claim_account'), ['password' => 'sup3rsecret'])
            ->assertRedirect(app_url(route('following', [], false)));

        $user = User::where('email', 'fan@fans.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        // Where that form lands them: the list, then the offer below it.
        $this->get(route('following'))->assertOk()
            ->assertSeeInOrder([$role->name, __('messages.dash_own_events_title')], false)
            ->assertSee(route('new', ['type' => 'venue']), false);

        $this->assertCanCreateSchedule($user);
    }

    public function test_set_password_link_mailed_later_then_a_schedule(): void
    {
        $role = $this->createRole($this->createOwner());
        $sub = $this->subscribe($role, 'fan@fans.test');
        $this->post(route('subscriber.confirm', ['token' => $sub->confirm_token]));
        $this->flushSession();

        $user = User::where('email', 'fan@fans.test')->firstOrFail();
        $this->assertTrue($user->isStub());

        $token = Password::createToken($user);
        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'fan@fans.test',
            'password' => 'sup3rsecret',
            'password_confirmation' => 'sup3rsecret',
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($user->fresh());

        $this->assertCanCreateSchedule($user);
    }

    /** Mutation: drop the member() check in RoleController::following(). */
    public function test_the_following_page_makes_no_offer_to_somebody_who_runs_a_schedule(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);
        $this->createRole($this->createOwner())->followers()->attach($owner->id, ['level' => 'follower']);

        $this->actingAs($owner)->get(route('following'))->assertOk()
            ->assertDontSee(__('messages.dash_own_events_title'), false);
    }
}
