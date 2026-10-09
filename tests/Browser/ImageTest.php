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
                ->waitFor('#profile_image_preview_clear', 5);

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitFor('#profile_image_existing', 15);

            // Verify DB
            $this->assertNotNull(User::first()->profile_image_url);

            // Verify page shows existing image
            $browser->assertVisible('#profile_image_existing');

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->script("document.querySelector('#profile_image_existing button[data-delete-image-url]').click()");
            $browser->waitUntilMissing('#profile_image_existing', 15)
                ->waitFor('#profile_image_choose', 5);

            // Verify DB
            $this->assertEmpty(User::first()->refresh()->profile_image_url);

            // -----------------------------------------------
            // B. Schedule Profile Image
            // -----------------------------------------------

            // Navigate to edit > style > branding
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            // Branding is always on the Style tab: there is no row to open for it.
            $browser->pause(500);

            // Upload
            $browser->attach('#profile_image', $imagePath)
                ->waitFor('#profile_image_preview_clear', 5);

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit();");
            $this->landOn($browser, '/talent/schedule', 30);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->profile_image_url);

            // Navigate back to edit > style > branding
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            // Branding is always on the Style tab: there is no row to open for it.
            $browser->pause(500)
                ->waitFor('#profile_image_existing', 5);

            // Delete via AJAX (override confirm dialog)
            $browser->script('window.confirm = function() { return true; }');
            $browser->script("document.querySelector('#profile_image_existing button[data-delete-image-url]').click()");
            $browser->waitUntilMissing('#profile_image_existing', 15)
                ->waitFor('#profile_image_choose', 5);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->profile_image_url);

            // -----------------------------------------------
            // C. Schedule Header Image
            // -----------------------------------------------

            // Navigate to edit > style > advanced (header image)
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            $browser->script("document.getElementById('style-tab-advanced').click();");
            $browser->pause(500);

            // Header image controls only show for the "banner" header style; select it to reveal them
            $browser->script("document.getElementById('header_style_banner').checked = true; document.getElementById('header_style_banner').dispatchEvent(new Event('change', { bubbles: true }));");
            $browser->pause(300);

            // The header's pictures are a wall now (resources/js/components/StylePictureWall.vue),
            // and its Upload tile presses the page's own file field, which is out of sight. A
            // picture that arrives becomes the choice by itself.
            $browser->waitUntil('!! window.StyleStudio && window.StyleStudio.state.ready === true', 15)
                ->waitFor('#style-content-advanced .st-wall', 5);
            $this->assertNotSame('', $browser->value('#header_image'), 'sanity check: no picture of its own yet');

            // Upload
            $browser->attach('#header_image_url', $imagePath)
                ->waitFor('#style-content-advanced .st-choices .st-tile.is-on.has-picture', 5)
                ->waitFor('#style-content-advanced [data-own="remove"]', 5);
            $this->assertSame('', $browser->value('#header_image'), 'the picture that was uploaded is the header picture');
            $this->assertNotNull($browser->script('return document.querySelector("#style-preview .st-pv-stage");')[0], 'and the preview shows it');

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit();");
            $this->landOn($browser, '/talent/schedule', 30);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->refresh()->header_image_url);

            // Navigate back to edit > style > advanced (header image)
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            $browser->script("document.getElementById('style-tab-advanced').click();");
            $browser->pause(500);
            // Ensure banner style so the header's pictures (and Remove, under the choices) are on the page
            $browser->script("document.getElementById('header_style_banner').checked = true; document.getElementById('header_style_banner').dispatchEvent(new Event('change', { bubbles: true }));");
            $browser->pause(300)
                ->waitUntil('!! window.StyleStudio && window.StyleStudio.state.ready === true', 15)
                ->waitFor('#style-content-advanced .st-choices .st-tile.is-on.has-picture', 5)
                ->waitFor('#style-content-advanced [data-own="remove"]', 5);

            // Remove deletes the stored picture at once, after the page's question (answered here)
            $browser->script('window.confirm = function() { return true; }');
            $browser->script("document.querySelector('#style-content-advanced [data-own=\"remove\"]').scrollIntoView({ block: 'center' });");
            $browser->pause(200)
                ->click('#style-content-advanced [data-own="remove"]')
                ->waitUntil('document.getElementById("delete_header_image_button") === null', 15)
                ->pause(300);

            // With no picture of its own left, the header has none; the preview has let go of it too.
            $this->assertSame('none', $browser->value('#header_image'));
            $this->assertNull($browser->script('return document.querySelector("#style-preview .st-pv-stage");')[0]);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->header_image_url);

            // -----------------------------------------------
            // D. Schedule Background Image
            // -----------------------------------------------

            // Navigate to edit > style > background
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            $browser->script("document.getElementById('style-tab-background').click();");
            $browser->pause(500);

            // Choose "image". The choice is a pill now: the radio inside it has no size of its
            // own, so the pill is what gets clicked, brought clear of the save bar first.
            $browser->script("document.querySelector('label[for=\"background_type_image\"]').scrollIntoView({ block: 'center' });");
            $browser->pause(200)
                ->click('label[for="background_type_image"]')
                ->pause(500)
                ->waitFor('#style_background_image', 5);

            // The same wall as the header's: the file field is the page's own, out of sight.
            $browser->waitUntil('!! window.StyleStudio && window.StyleStudio.state.ready === true', 15)
                ->waitFor('#style_background_image .st-wall', 5);

            // Upload
            $browser->attach('#background_image_url', $imagePath)
                ->waitFor('#style_background_image .st-choices .st-tile.is-on.has-picture', 5)
                ->waitFor('#style_background_image [data-own="remove"]', 5);
            $this->assertSame('', $browser->value('#background_image'));

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit();");
            $this->landOn($browser, '/talent/schedule', 30);

            // Verify DB
            $this->assertNotNull(Role::where('subdomain', 'talent')->first()->refresh()->background_image_url);

            // Navigate back, select image radio + custom dropdown
            $browser->visit('/talent/edit')
                ->waitFor('a[data-section="section-style"]', 5)
                ->pause(1000);
            $browser->script("document.querySelector('a[data-section=\"section-style\"]').click()");
            $browser->waitFor('#section-style', 10);
            $browser->script("document.getElementById('style-tab-background').click();");
            $browser->pause(500);
            $browser->script("document.querySelector('label[for=\"background_type_image\"]').scrollIntoView({ block: 'center' });");
            $browser->pause(200)
                ->click('label[for="background_type_image"]')
                ->pause(500)
                ->waitFor('#style_background_image', 5);

            // The stored picture is the choice, with Remove under it
            $browser->waitUntil('!! window.StyleStudio && window.StyleStudio.state.ready === true', 15)
                ->waitFor('#style_background_image .st-choices .st-tile.is-on.has-picture', 5)
                ->waitFor('#style_background_image [data-own="remove"]', 5);

            // Remove deletes it at once, after the page's question (answered here)
            $browser->script('window.confirm = function() { return true; }');
            $browser->script("document.querySelector('#style_background_image [data-own=\"remove\"]').scrollIntoView({ block: 'center' });");
            $browser->pause(200)
                ->click('#style_background_image [data-own="remove"]')
                ->waitUntil('document.getElementById("background_image_existing") === null', 15);

            // Verify DB
            $this->assertEmpty(Role::where('subdomain', 'talent')->first()->refresh()->background_image_url);

            // -----------------------------------------------
            // E. Event Flyer Image
            // -----------------------------------------------

            // Create event. #event_name is server-rendered, so it exists before the Vue app that
            // owns the form has mounted; wait for the app, then set the name through its v-model
            // (Dusk type() on this input is unreliable in headless CI - see EventManagementTest).
            $browser->visit('/talent/add-event?date='.date('Y-m-d'))
                ->waitFor('#event_name', 15)
                ->waitUntil('!! window.vueApp', 15)
                ->pause(500);
            $browser->script("
                var nameField = document.getElementById('event_name');
                nameField.value = 'Flyer Test Event';
                nameField.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit();");
            $this->landOn($browser, '/talent/schedule', 30);

            // Get event hash
            $event = Event::first();
            $hash = UrlUtils::encodeId($event->id);

            // Edit event - upload flyer
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#flyer_image_choose', 5)
                ->attach('#flyer_image', $imagePath)
                ->waitFor('#image_preview', 5);

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit();");
            $this->landOn($browser, '/talent/schedule', 30);

            // Verify DB
            $this->assertNotNull(Event::first()->refresh()->flyer_image_url);

            // Edit event again - delete flyer (override confirm dialog)
            $browser->visit('/talent/edit-event/'.$hash)
                ->waitFor('#flyer_image_existing', 5);
            $browser->script('window.confirm = function() { return true; }');
            $browser->script("document.querySelector('#delete-flyer-btn').click()");
            $browser->waitUntilMissing('#flyer_image_existing', 15)
                ->waitFor('#flyer_image_choose', 5);

            // Verify DB
            $this->assertEmpty(Event::first()->refresh()->flyer_image_url);
        });
    }
}
