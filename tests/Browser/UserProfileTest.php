<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserProfileTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'timezone' => 'UTC',
            'language_code' => 'en',
        ]);
    }

    public function testViewProfile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->assertSee('Profile')
                    ->assertSee('Test User')
                    ->assertSee('test@example.com')
                    ->assertInputValue('name', 'Test User')
                    ->assertInputValue('email', 'test@example.com')
                    ->assertSelected('timezone', 'UTC')
                    ->assertSelected('language_code', 'en');
        });
    }

    public function testEditProfile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->clear('name')
                    ->type('name', 'Updated Test User')
                    ->clear('email')
                    ->type('email', 'updated@example.com')
                    ->select('timezone', 'America/New_York')
                    ->select('language_code', 'es')
                    ->press('Save')
                    ->pause(2000)
                    ->assertSee('Profile updated successfully')
                    ->assertInputValue('name', 'Updated Test User')
                    ->assertInputValue('email', 'updated@example.com')
                    ->assertSelected('timezone', 'America/New_York')
                    ->assertSelected('language_code', 'es');

            // Verify changes in database
            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'name' => 'Updated Test User',
                'email' => 'updated@example.com',
                'timezone' => 'America/New_York',
                'language_code' => 'es'
            ]);
        });
    }

    public function testProfileValidation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->clear('name')
                    ->clear('email')
                    ->press('Save')
                    ->assertSee('The name field is required')
                    ->assertSee('The email field is required');

            // Test invalid email
            $browser->type('name', 'Valid Name')
                    ->type('email', 'invalid-email')
                    ->press('Save')
                    ->assertSee('The email field must be a valid email address');
        });
    }

    public function testProfileImageUpload(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->attach('profile_image', __DIR__.'/../../storage/test-assets/test-image.jpg')
                    ->press('Save')
                    ->pause(3000)
                    ->assertSee('Profile updated successfully');

            // Check if image is displayed
            $browser->assertPresent('.profile-image')
                    ->assertAttribute('.profile-image img', 'src', function ($src) {
                        return str_contains($src, 'profile');
                    });
        });
    }

    public function testChangePassword(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#password-tab')
                    ->pause(500)
                    ->type('current_password', 'password') // Default factory password
                    ->type('password', 'newpassword123')
                    ->type('password_confirmation', 'newpassword123')
                    ->press('Update Password')
                    ->pause(2000)
                    ->assertSee('Password updated successfully');

            // Test login with new password
            $browser->press($this->user->name)
                    ->clickLink('Log Out')
                    ->visit('/login')
                    ->type('email', $this->user->email)
                    ->type('password', 'newpassword123')
                    ->press('LOG IN')
                    ->assertPathIs('/events');
        });
    }

    public function testPasswordValidation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#password-tab')
                    ->pause(500)
                    ->type('current_password', 'wrongpassword')
                    ->type('password', 'new')
                    ->type('password_confirmation', 'different')
                    ->press('Update Password')
                    ->assertSee('The current password is incorrect')
                    ->assertSee('The password field must be at least 8 characters')
                    ->assertSee('The password field confirmation does not match');
        });
    }

    public function testAPIKeyGeneration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#api-tab')
                    ->pause(500)
                    ->assertSee('API Settings')
                    ->press('Generate API Key')
                    ->pause(2000)
                    ->assertSee('API key generated successfully')
                    ->assertPresent('.api-key-display');

            // Verify API key was created in database
            $this->user->refresh();
            $this->assertNotNull($this->user->api_key);
        });
    }

    public function testAPIKeyRegeneration(): void
    {
        // First generate an API key
        $this->user->update(['api_key' => 'existing_key']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#api-tab')
                    ->pause(500)
                    ->assertSee('Regenerate API Key')
                    ->press('Regenerate API Key')
                    ->pause(1000)
                    ->acceptDialog() // Confirm regeneration
                    ->pause(2000)
                    ->assertSee('API key regenerated successfully');

            // Verify API key was changed
            $this->user->refresh();
            $this->assertNotEquals('existing_key', $this->user->api_key);
        });
    }

    public function testShowAPIKey(): void
    {
        $this->user->update(['api_key' => 'test_api_key_12345']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#api-tab')
                    ->pause(500)
                    ->press('Show API Key')
                    ->pause(2000)
                    ->assertSee('test_api_key_12345');
        });
    }

    public function testAPIDocumentation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#api-tab')
                    ->pause(500)
                    ->clickLink('API Documentation')
                    ->assertPathIs('/api/documentation')
                    ->assertSee('API Documentation')
                    ->assertSee('Authentication')
                    ->assertSee('Endpoints');
        });
    }

    public function testNotificationSettings(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#notifications-tab')
                    ->pause(500)
                    ->assertSee('Notification Settings')
                    ->check('email_notifications')
                    ->check('event_reminders')
                    ->uncheck('marketing_emails')
                    ->press('Save Preferences')
                    ->pause(2000)
                    ->assertSee('Preferences saved successfully');
        });
    }

    public function testAccountDeletion(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#danger-tab')
                    ->pause(500)
                    ->assertSee('Delete Account')
                    ->assertSee('This action cannot be undone')
                    ->press('Delete Account')
                    ->pause(1000)
                    ->type('confirmation', 'DELETE')
                    ->press('Confirm Deletion')
                    ->pause(2000)
                    ->assertPathIs('/login')
                    ->assertSee('Account deleted successfully');

            // Verify user was soft deleted
            $this->assertSoftDeleted('users', ['id' => $this->user->id]);
        });
    }

    public function testLanguageChange(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->select('language_code', 'es')
                    ->press('Save')
                    ->pause(2000)
                    ->visit('/events')
                    ->assertSee('Eventos'); // Spanish for "Events"
        });
    }

    public function testTimezoneChange(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->select('timezone', 'America/Los_Angeles')
                    ->press('Save')
                    ->pause(2000);

            // Create an event to verify timezone display
            $browser->visit('/new/venue')
                    ->type('name', 'Timezone Test Venue')
                    ->type('email', 'timezone@example.com')
                    ->type('address1', '123 Timezone St')
                    ->press('SAVE')
                    ->pause(2000);

            $today = date('Y-m-d');
            $browser->visit("/timezone-test-venue/add_event?date={$today}")
                    ->type('name', 'Timezone Event')
                    ->type('starts_at_time', '19:00')
                    ->press('SAVE')
                    ->pause(2000)
                    ->assertSee('7:00 PM'); // Should show in PST format
        });
    }

    public function testEmailVerificationAfterChange(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->clear('email')
                    ->type('email', 'newemail@example.com')
                    ->press('Save')
                    ->pause(2000)
                    ->assertSee('Profile updated')
                    ->visit('/events')
                    ->assertPathIs('/verify-email')
                    ->assertSee('Please verify your new email address');
        });
    }

    public function testSecurityLog(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#security-tab')
                    ->pause(500)
                    ->assertSee('Recent Activity')
                    ->assertSee('Login')
                    ->assertPresent('.activity-log');
        });
    }

    public function testTwoFactorAuthentication(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#security-tab')
                    ->pause(500);

            // Check if 2FA is available
            if ($browser->element('#enable-2fa')) {
                $browser->press('Enable Two-Factor Authentication')
                        ->pause(2000)
                        ->assertSee('Scan QR Code')
                        ->assertPresent('.qr-code');
            }
        });
    }

    public function testExportData(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/profile')
                    ->click('#privacy-tab')
                    ->pause(500)
                    ->assertSee('Export Data')
                    ->press('Download My Data')
                    ->pause(3000);

            // Should trigger download - check if no errors occurred
            $browser->assertDontSee('Error');
        });
    }
} 