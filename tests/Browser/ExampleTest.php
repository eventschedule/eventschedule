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

            $browser->visit('/home')                
                    ->assertPathIs('/home')
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
                    ->assertPathIs('/home')
                    ->assertSee($name);
        });
    }
}
