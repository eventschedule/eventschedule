<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class ExampleTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * A basic browser test example.
     */
    public function testBasicExample(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Sign up
            $browser->visit('/sign_up')
                    ->type('name', $name)
                    ->type('email', $email)
                    ->type('password', $password)
                    ->type('password_confirmation', $password)
                    ->check('terms')
                    ->press('REGISTER')
                    ->assertPathIs('/events');

            $user = User::where('email', $email)->first();
            $user->email_verified_at = now();
            $user->save();

            $browser->visit('/events')                
                    ->assertPathIs('/events')
                    ->assertSee($name);            

            // Log out
            $browser->press($name)
                    ->clickLink('Log Out')
                    ->assertPathIs('/login');

            // Log back in
            $browser->visit('/login')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->press('LOG IN')
                    ->assertPathIs('/events')
                    ->assertSee($name);

            // Create/edit venue
            $browser->visit('/new/venue')
                    ->type('name', 'Test Venue')
                    ->type('address1', '123 Test St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-venue/schedule')
                    ->clickLink('Edit Venue')
                    ->assertPathIs('/test-venue/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-venue/schedule')
                    ->assertSee('google.com');

            // Create/edit talent
            $browser->visit('/new/schedule')
                    ->type('name', 'Test Schedule')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-schedule/schedule')
                    ->clickLink('Edit Schedule')
                    ->assertPathIs('/test-schedule/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-schedule/schedule')
                    ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/test-schedule/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_venue')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-schedule/schedule')
                    ->assertSee('Test Venue');
            
            // Create/edit event
            $browser->visit('/test-venue/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_member')
                    ->type('name', 'Venue Event')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/test-venue/schedule')
                    ->assertSee('Venue Event');
        });
    }
}