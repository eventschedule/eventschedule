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
                    ->assertPathIs('/verify-email');

            $user = User::where('email', $email)->first();
            $user->email_verified_at = now();
            $user->save();

            $browser->visit('/events')                
                    ->assertPathIs('/events')
                    ->assertSee($name);            

            // Log out
            $browser->press($name)
                    ->clickLink('Log Out')
                    ->assertPathIs('/');

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
                    ->assertPathIs('/testvenue')
                    ->clickLink('Edit Venue')
                    ->assertPathIs('/testvenue/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/testvenue')
                    ->assertSee('google.com');

            // Create/edit talent
            $browser->visit('/new/schedule')
                    ->type('name', 'Test Schedule')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/testschedule')
                    ->clickLink('Edit Schedule')
                    ->assertPathIs('/testschedule/edit')
                    ->type('website', 'https://google.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/testschedule')
                    ->assertSee('google.com');

            // Create/edit event
            $browser->visit('/testschedule/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_venue')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/testschedule/schedule')
                    ->assertSee('Test Venue');
            
            // Create/edit event
            $browser->visit('/testvenue/add_event?date=' . date('Y-m-d'))
                    ->select('#selected_member')
                    ->type('name', 'Venue Event')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->assertPathIs('/testvenue/schedule')
                    ->assertSee('Venue Event');
        });
    }
}