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
        $this->browse(function (Browser $browser) {
            $browser->visit('/sign_up')
                    ->type('name', 'John Doe')
                    ->type('email', 'test@gmail.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->check('terms')
                    ->press('REGISTER')
                    ->assertPathIs('/verify-email');

            $user = User::where('email', 'test@gmail.com')->first();
            $user->email_verified_at = now();
            $user->save();

            $browser->visit('/home')                
                    ->assertPathIs('/home')
                    ->assertSee('John Doe');            
        });

    }
}
