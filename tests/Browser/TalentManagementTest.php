<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Role;

class TalentManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function testCreateTalent(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->assertSee('Create New Schedule')
                    ->type('name', 'Rock Band Alpha')
                    ->type('email', 'rockband@example.com')
                    ->type('phone', '555-ROCK')
                    ->type('website', 'https://rockbandalpha.com')
                    ->type('description', 'High-energy rock band from the city')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertPathIs('/rock-band-alpha/schedule')
                    ->assertSee('Rock Band Alpha');

            // Verify talent was created in database
            $this->assertDatabaseHas('roles', [
                'name' => 'Rock Band Alpha',
                'type' => 'talent',
                'email' => 'rockband@example.com'
            ]);
        });
    }

    public function testEditTalent(): void
    {
        $this->browse(function (Browser $browser) {
            // First create a talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Original Band')
                    ->type('email', 'original@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Then edit it
            $browser->clickLink('Edit Schedule')
                    ->assertPathIs('/original-band/edit')
                    ->assertSee('Edit Schedule')
                    ->clear('name')
                    ->type('name', 'Updated Band Name')
                    ->clear('website')
                    ->type('website', 'https://updatedband.com')
                    ->type('bio', 'Updated biography of the band')
                    ->select('genre', 'Rock')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Updated Band Name')
                    ->assertSee('updatedband.com');

            // Verify changes in database
            $this->assertDatabaseHas('roles', [
                'name' => 'Updated Band Name',
                'website' => 'https://updatedband.com'
            ]);
        });
    }

    public function testTalentValidation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->press('SAVE')
                    ->assertSee('The name field is required');

            // Test invalid email
            $browser->type('name', 'Test Band')
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

    public function testTalentImageUpload(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Image Test Band')
                    ->type('email', 'image@example.com')
                    ->attach('profile_image', __DIR__.'/../../storage/test-assets/test-image.jpg')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(3000)
                    ->assertSee('Image Test Band');
        });
    }

    public function testTalentScheduleView(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Schedule Test Band')
                    ->type('email', 'schedule@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Check schedule view
            $browser->assertPathIs('/schedule-test-band/schedule')
                    ->assertSee('Schedule Test Band')
                    ->assertSee('Add Event')
                    ->assertPresent('.calendar-container')
                    ->click('.calendar-next')
                    ->pause(1000)
                    ->click('.calendar-prev')
                    ->pause(1000);
        });
    }

    public function testTalentGenreAndCategories(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Genre Test Band')
                    ->type('email', 'genre@example.com')
                    ->select('genre', 'Jazz')
                    ->select('category', 'Professional')
                    ->type('instruments', 'Guitar, Bass, Drums, Vocals')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Genre Test Band');

            // Verify genre/category was saved
            $this->assertDatabaseHas('roles', [
                'name' => 'Genre Test Band',
                'genre' => 'Jazz'
            ]);
        });
    }

    public function testTalentSearch(): void
    {
        // Create multiple talents first
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user);

            // Create first talent
            $browser->visit('/new/schedule')
                    ->type('name', 'Jazz Quartet')
                    ->type('email', 'jazz@example.com')
                    ->select('genre', 'Jazz')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Create second talent
            $browser->visit('/new/schedule')
                    ->type('name', 'Rock Legends')
                    ->type('email', 'rock@example.com')
                    ->select('genre', 'Rock')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Test search functionality
            $browser->visit('/events')
                    ->type('search', 'Jazz')
                    ->pause(1000)
                    ->assertSee('Jazz Quartet')
                    ->assertDontSee('Rock Legends');
        });
    }

    public function testTalentAvailability(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Availability Band')
                    ->type('email', 'availability@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Set availability
            $browser->clickLink('Availability')
                    ->pause(1000)
                    ->check('monday')
                    ->check('friday')
                    ->check('saturday')
                    ->type('available_from', '18:00')
                    ->type('available_to', '23:00')
                    ->press('Save Availability')
                    ->pause(2000)
                    ->assertSee('Availability updated');
        });
    }

    public function testTalentPortfolio(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Portfolio Band')
                    ->type('email', 'portfolio@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Add portfolio items
            $browser->clickLink('Portfolio')
                    ->pause(1000)
                    ->click('#add-portfolio-item')
                    ->type('portfolio[0][title]', 'Live at Jazz Club')
                    ->type('portfolio[0][description]', 'Amazing live performance')
                    ->type('portfolio[0][media_url]', 'https://youtube.com/watch?v=123')
                    ->click('#add-portfolio-item')
                    ->type('portfolio[1][title]', 'Studio Recording')
                    ->type('portfolio[1][description]', 'Professional studio session')
                    ->press('Save Portfolio')
                    ->pause(2000)
                    ->assertSee('Live at Jazz Club')
                    ->assertSee('Studio Recording');
        });
    }

    public function testTalentRatesAndPricing(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Pricing Band')
                    ->type('email', 'pricing@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Set rates
            $browser->clickLink('Edit Schedule')
                    ->type('hourly_rate', '150')
                    ->type('minimum_booking', '2')
                    ->type('travel_fee', '50')
                    ->select('currency', 'USD')
                    ->press('SAVE')
                    ->pause(2000);

            // Verify rates are displayed
            $browser->assertSee('$150/hour')
                    ->assertSee('2 hour minimum');
        });
    }

    public function testTalentCollaborators(): void
    {
        // Create another user to invite
        $collaborator = User::factory()->create([
            'email' => 'collaborator@example.com',
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($collaborator) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Collab Band')
                    ->type('email', 'collab@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Add collaborator
            $browser->clickLink('Collaborators')
                    ->pause(1000)
                    ->type('collaborator_email', 'collaborator@example.com')
                    ->select('role', 'editor')
                    ->press('Send Invitation')
                    ->pause(2000)
                    ->assertSee('Invitation sent')
                    ->assertSee('collaborator@example.com');
        });
    }

    public function testTalentBookingRequests(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Booking Band')
                    ->type('email', 'booking@example.com')
                    ->check('accepts_bookings')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Check booking form
            $browser->clickLink('Book This Talent')
                    ->pause(1000)
                    ->assertSee('Booking Request')
                    ->type('event_name', 'Wedding Reception')
                    ->type('event_date', '2024-12-25')
                    ->type('duration', '4')
                    ->type('message', 'Looking for live music for our wedding')
                    ->type('contact_name', 'John Smith')
                    ->type('contact_email', 'john@example.com')
                    ->press('Send Booking Request')
                    ->pause(2000)
                    ->assertSee('Booking request sent');
        });
    }

    public function testTalentSocialMedia(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Social Media Band')
                    ->type('email', 'social@example.com')
                    ->type('facebook_url', 'https://facebook.com/socialmediaband')
                    ->type('instagram_url', 'https://instagram.com/socialmediaband')
                    ->type('youtube_url', 'https://youtube.com/socialmediaband')
                    ->type('spotify_url', 'https://open.spotify.com/artist/123')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('Social Media Band');

            // Check social media links are displayed
            $browser->assertPresent('.social-links')
                    ->assertPresent('a[href*="facebook.com"]')
                    ->assertPresent('a[href*="instagram.com"]')
                    ->assertPresent('a[href*="youtube.com"]')
                    ->assertPresent('a[href*="spotify.com"]');
        });
    }

    public function testTalentDeletion(): void
    {
        $this->browse(function (Browser $browser) {
            // Create talent
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'Delete Test Band')
                    ->type('email', 'delete@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000);

            // Delete the talent
            $browser->clickLink('Edit Schedule')
                    ->pause(1000)
                    ->scrollIntoView('.danger-zone')
                    ->press('Delete Schedule')
                    ->pause(1000)
                    ->acceptDialog()
                    ->pause(2000)
                    ->assertPathIs('/events')
                    ->assertDontSee('Delete Test Band');

            // Verify soft deletion
            $this->assertSoftDeleted('roles', [
                'name' => 'Delete Test Band'
            ]);
        });
    }

    public function testTalentSubdomainGeneration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/new/schedule')
                    ->type('name', 'My Awesome Band!')
                    ->type('email', 'awesome@example.com')
                    ->scrollIntoView('button[type="submit"]')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertPathIs('/my-awesome-band/schedule');

            // Verify subdomain was generated correctly
            $this->assertDatabaseHas('roles', [
                'name' => 'My Awesome Band!',
                'subdomain' => 'my-awesome-band'
            ]);
        });
    }
} 