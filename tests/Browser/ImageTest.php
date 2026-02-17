<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class ImageTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_images(): void
    {
        $this->browse(function (Browser $browser) {
            $imagePath = public_path('images/demo/demo_profile_vinyl.jpg');

            // Setup
            $this->setupTestAccount($browser);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // A. User Profile Image
            // -----------------------------------------------

            // Upload
            $browser->visit('/settings')
                ->waitFor('#profile_image_choose', 5)
                ->attach('#profile_image', $imagePath)
                ->waitFor('#profile_image_preview_clear', 5)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/settings', 15);

            // Verify DB
            $this->assertNotNull(User::first()->profile_image_url);

            // Verify page shows existing image
            $browser->visit('/settings')
                ->waitFor('#profile_image_existing', 5)
                ->assertVisible('#profile_image_existing');

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#profile_image_existing button[data-delete-image-url]')
                ->waitUntilMissing('#profile_image_existing', 5)
                ->waitFor('#profile_image_choose', 5);

            // Verify DB
            $this->assertEmpty(User::first()->refresh()->profile_image_url);

            // -----------------------------------------------
            // B. Schedule Profile Image
            // -----------------------------------------------

            // Navigate to edit > style > branding
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="branding"]')
                ->pause(500);

            // Upload
            $browser->attach('#profile_image', $imagePath)
                ->waitFor('#profile_image_preview_clear', 5)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->profile_image_url);

            // Navigate back to edit > style > branding
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="branding"]')
                ->pause(500)
                ->waitFor('#profile_image_existing', 5);

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#profile_image_existing button[data-delete-image-url]')
                ->waitUntilMissing('#profile_image_existing', 5)
                ->waitFor('#profile_image_choose', 5);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->profile_image_url);

            // -----------------------------------------------
            // C. Schedule Header Image
            // -----------------------------------------------

            // Navigate to edit > style > background
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="background"]')
                ->pause(500);

            // Select "Custom" (empty value) from header_image dropdown
            $browser->script("document.getElementById('header_image').value = ''; document.getElementById('header_image').dispatchEvent(new Event('input', { bubbles: true }));");
            $browser->pause(500)
                ->waitFor('#custom_header_input', 5);

            // Upload
            $browser->attach('#header_image_url', $imagePath)
                ->waitFor('#header_image_url_preview_clear', 5)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->refresh()->header_image_url);

            // Navigate back to edit > style > background
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="background"]')
                ->pause(500)
                ->waitFor('#delete_header_image_button', 5);

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#delete_header_image_button button[data-delete-image-url]')
                ->waitUntilMissing('#delete_header_image_button', 5);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->header_image_url);

            // -----------------------------------------------
            // D. Schedule Background Image
            // -----------------------------------------------

            // Navigate to edit > style > background
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="background"]')
                ->pause(500);

            // Select "image" radio
            $browser->radio('background', 'image')
                ->pause(500)
                ->waitFor('#style_background_image', 5);

            // Select "Custom" (empty value) from background_image dropdown
            $browser->script("document.getElementById('background_image').value = ''; document.getElementById('background_image').dispatchEvent(new Event('input', { bubbles: true }));");
            $browser->pause(500)
                ->waitFor('#custom_image_input', 5);

            // Upload
            $browser->attach('#background_image_url', $imagePath)
                ->waitFor('#background_image_preview_clear', 5)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->refresh()->background_image_url);

            // Navigate back, select image radio + custom dropdown
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->click('a[data-section="section-style"]')
                ->waitFor('#section-style', 5)
                ->click('button[data-style-tab="background"]')
                ->pause(500)
                ->radio('background', 'image')
                ->pause(500)
                ->waitFor('#style_background_image', 5);

            // Select Custom to reveal background image section
            $browser->script("document.getElementById('background_image').value = ''; document.getElementById('background_image').dispatchEvent(new Event('input', { bubbles: true }));");
            $browser->pause(500);

            // Show the custom_image_input container (it's hidden by toggleCustomImageInput when existing image present)
            $browser->script("document.getElementById('custom_image_input').style.display = 'block';");
            $browser->pause(300)
                ->waitFor('#background_image_existing', 5);

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#background_image_existing button[data-delete-image-url]')
                ->waitUntilMissing('#background_image_existing', 5);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->background_image_url);

            // -----------------------------------------------
            // E. Event Flyer Image
            // -----------------------------------------------

            // Create event
            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#event_name', 5)
                ->type('name', 'Flyer Test Event')
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Get event hash
            $event = Event::first();
            $hash = UrlUtils::encodeId($event->id);

            // Edit event - upload flyer
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#flyer_image_choose', 5)
                ->attach('#flyer_image', $imagePath)
                ->waitFor('#image_preview', 5)
                ->scrollIntoView('button[type="submit"]')
                ->press('SAVE')
                ->waitForLocation('/talent/schedule', 15);

            // Verify DB
            $this->assertNotNull(Event::first()->refresh()->flyer_image_url);

            // Edit event again - delete flyer (override confirm dialog)
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#flyer_image_existing', 5);
            $browser->script('window.confirm = function() { return true; }');
            $browser->click('#delete-flyer-btn')
                ->waitUntilMissing('#flyer_image_existing', 5)
                ->waitFor('#flyer_image_choose', 5);

            // Verify DB
            $this->assertEmpty(Event::first()->refresh()->flyer_image_url);
        });
    }
}
