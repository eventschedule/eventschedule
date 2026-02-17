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
            $otherRole->save();

            // -----------------------------------------------
            // 2. Follow the schedule
            // -----------------------------------------------
            $browser->visit('/otherschedule/follow')
                ->waitForLocation('/following', 15)
                ->assertPathIs('/following')
                ->waitForText('Other Schedule', 5);

            // -----------------------------------------------
            // 3. Verify unfollow button is visible
            // -----------------------------------------------
            $browser->assertVisible('.btn-confirm-navigate');

            // -----------------------------------------------
            // 4. Unfollow
            // -----------------------------------------------
            $browser->script('window.confirm = function() { return true; }');

            $browser->click('.btn-confirm-navigate')
                ->pause(1000)
                ->waitForLocation('/following', 15)
                ->assertDontSee('Other Schedule');
        });
    }
}
