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


            // Create a new venue
            $venueName = 'Test Venue';
            $browser->clickLink('Create a Venue')
                    ->assertPathIs('/create-venue')
                    ->type('name', $venueName)
                    ->type('subdomain', 'testvenue')
                    ->type('description', 'A test venue for Dusk')
                    ->type('email', 'venue@example.com')
                    ->type('phone', '1234567890')
                    ->type('website', 'https://testvenue.com')
                    ->type('city', 'Test City')
                    ->type('postal_code', '12345')
                    ->select('timezone', 'America/New_York')
                    ->press('CREATE VENUE')
                    ->assertPathIs('/testvenue/admin')
                    ->assertSee($venueName)
                    ->assertSee('Venue created successfully');

            // Verify venue details
            $browser->assertSee($venueName)
                    ->assertSee('A test venue for Dusk')
                    ->assertSee('venue@example.com')
                    ->assertSee('1234567890')
                    ->assertSee('https://testvenue.com')
                    ->assertSee('Test City')
                    ->assertSee('12345');
        });
    }
}
