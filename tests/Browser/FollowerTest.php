<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class FollowerTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_follow_unfollow(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // 1. Seed a second schedule to follow
            // -----------------------------------------------
            $otherUser = User::create([
                'name' => 'Other User',
                'email' => 'other@gmail.com',
                'password' => bcrypt('password'),
            ]);

            $otherRole = new Role;
            $otherRole->name = 'Other Schedule';
            $otherRole->subdomain = 'otherschedule';
            $otherRole->type = 'talent';
            $otherRole->user_id = $otherUser->id;
            $otherRole->timezone = 'America/New_York';
            // A verified contact, plus the owner pivot below, is what makes this a real schedule
            // rather than a placeholder. Without the verified address isEditableBy() ends with
            // "! isClaimed() && isFollowing()", so following it would hand the test account edit
            // rights on a stranger's page - not what a follow test should be simulating.
            $otherRole->email = 'otherschedule@gmail.com';
            $otherRole->email_verified_at = now();
            $otherRole->save();

            // Load-bearing. follow() refuses anything failing Role::hasRealOwner(), which asks for
            // BOTH roles.user_id and a matching owner/admin pivot: ConvertsLocationToVenue stamps a
            // curator's user_id onto every placeholder venue it invents while attaching that user
            // only as a follower, so user_id alone does not mean anybody runs the schedule. Both
            // branches of follow() redirect to /following, so without this the waits below pass and
            // only the text assertion fails, on a page carrying an "Invalid request" toast.
            $otherRole->users()->attach($otherUser->id, ['level' => 'owner']);

            // -----------------------------------------------
            // 2. Follow the schedule
            // -----------------------------------------------
            $browser->visit('/otherschedule/follow')
                ->waitForLocation('/following', 15)
                ->assertPathIs('/following')
                ->waitForText('Other Schedule', 5);

            // -----------------------------------------------
            // 3. Verify schedule appears in following list
            // -----------------------------------------------
            $browser->assertSee('Other Schedule');

            // -----------------------------------------------
            // 4. Unfollow
            // -----------------------------------------------
            $browser->visit('/otherschedule/unfollow')
                ->waitForLocation('/following', 15)
                ->pause(3500)
                ->assertDontSee('Other Schedule');
        });
    }
}
