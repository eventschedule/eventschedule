<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Role;

class VenueManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a verified user for testing
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function testCreateVenue(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->assertSee('Create New Venue')
                    ->type('name', 'Test Venue')
                    ->type('email', 'venue@example.com')
                    ->type('address1', '123 Test Street')
                    ->type('city', 'Test City')
                    ->type('state', 'CA')
                    ->type('zip', '12345')
                    ->type('phone', '555-1234')
                    ->type('website', 'https://testvenue.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertPathIs('/test-venue/schedule')
                    ->assertSee('Test Venue');

            // Verify venue was created in database
            $this->assertDatabaseHas('roles', [
                'name' => 'Test Venue',
                'type' => 'venue',
                'email' => 'venue@example.com'
            ]);
        });
    }

    public function testEditVenue(): void
    {
        $this->browse(function (Browser $browser) {
            // First create a venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Original Venue')
                    ->type('email', 'original@example.com')
                    ->type('address1', '123 Original St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Then edit it
            $browser->clickLink('Edit Venue')
                    ->assertPathIs('/original-venue/edit')
                    ->assertSee('Edit Venue')
                    ->clear('name')
                    ->type('name', 'Updated Venue')
                    ->clear('website')
                    ->type('website', 'https://updatedvenue.com')
                    ->type('description', 'This is an updated venue description')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Updated Venue')
                    ->assertSee('updatedvenue.com');

            // Verify changes in database
            $this->assertDatabaseHas('roles', [
                'name' => 'Updated Venue',
                'website' => 'https://updatedvenue.com'
            ]);
        });
    }

    public function testVenueValidation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->press('SAVE')
                    ->assertSee('The name field is required')
                    ->assertSee('The email field is required');

            // Test invalid email
            $browser->type('name', 'Test Venue')
                    ->type('email', 'invalid-email')
                    ->press('SAVE')
                    ->assertSee('The email field must be a valid email address');

            // Test invalid website URL
            $browser->clear('email')
                    ->type('email', 'valid@example.com')
                    ->type('website', 'not-a-url')
                    ->press('SAVE')
                    ->assertSee('The website field must be a valid URL');
        });
    }

    public function testVenueImageUpload(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Image Test Venue')
                    ->type('email', 'image@example.com')
                    ->type('address1', '123 Image St')
                    ->attach('profile_image', __DIR__.'/../../storage/test-assets/test-image.jpg')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(3000)
                    ->assertSee('Image Test Venue');
        });
    }

    public function testVenueScheduleView(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Schedule Test Venue')
                    ->type('email', 'schedule@example.com')
                    ->type('address1', '123 Schedule St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Check schedule view
            $browser->assertPathIs('/schedule-test-venue/schedule')
                    ->assertSee('Schedule Test Venue')
                    ->assertSee('Add Event')
                    ->assertPresent('.calendar-container')
                    ->click('.calendar-next')
                    ->pause(1000)
                    ->click('.calendar-prev')
                    ->pause(1000);
        });
    }

    public function testVenueSearch(): void
    {
        // Create multiple venues first
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user);

            // Create first venue
            $browser->visit('/new/venue')
                    ->type('name', 'Jazz Club Venue')
                    ->type('email', 'jazz@example.com')
                    ->type('address1', '123 Jazz St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create second venue
            $browser->visit('/new/venue')
                    ->type('name', 'Rock Arena')
                    ->type('email', 'rock@example.com')
                    ->type('address1', '456 Rock Ave')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test search functionality
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->pause(1000)
                    ->assertSee('Jazz Club Venue')
                    ->assertDontSee('Rock Arena');
        });
    }

    public function testVenueFollowing(): void
    {
        $this->browse(function (Browser $browser) {
            // Create venue
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'Follow Test Venue')
                    ->type('email', 'follow@example.com')
                    ->type('address1', '123 Follow St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test following/unfollowing (if this feature exists)
            if ($browser->element('.follow-button')) {
                $browser->click('.follow-button')
                        ->pause(1000)
                        ->assertSee('Following')
                        ->click('.follow-button')
                        ->pause(1000)
                        ->assertSee('Follow');
            }
        });
    }

    public function testVenueSubdomainGeneration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/venue')
                    ->type('name', 'My Awesome Venue!')
                    ->type('email', 'awesome@example.com')
                    ->type('address1', '123 Awesome St')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertPathIs('/my-awesome-venue/schedule');

            // Verify subdomain was generated correctly
            $this->assertDatabaseHas('roles', [
                'name' => 'My Awesome Venue!',
                'subdomain' => 'my-awesome-venue'
            ]);
        });
    }
} 