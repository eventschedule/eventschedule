<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    /**
     * The settings page holds itself at the top until it has finished loading, then moves to the
     * section its address named. A press before that is scrolled away from under the pointer: the
     * browser brings the row into view, the page puts itself back, and the press lands on whatever
     * is there now. On a busy machine that is every row below the fold.
     */
    private const SETTLED = 'window._settingsLoading === false';

    use AccountSetupTrait;
    use DatabaseTruncation;

    public function test_profile_settings(): void
    {
        $name = 'John Doe';
        $email = 'test@gmail.com';
        $password = 'password';

        $this->browse(function (Browser $browser) use ($name, $email, $password) {
            // Setup
            $this->setupTestAccount($browser, $name, $email, $password);
            $this->createTestTalent($browser);

            // -----------------------------------------------
            // 1. Change timezone
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)->waitUntil(self::SETTLED, 5)
                ->scrollIntoView('button[data-tab="localization"]')
                ->pause(150)
                ->click('button[data-tab="localization"]')
                ->pause(500);

            $browser->script("
                var select = document.getElementById('timezone');
                select.value = 'Pacific/Auckland';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('Pacific/Auckland', User::first()->refresh()->timezone);

            // Verify persisted on reload
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)->waitUntil(self::SETTLED, 5)
                ->scrollIntoView('button[data-tab="localization"]')
                ->pause(150)
                ->click('button[data-tab="localization"]')
                ->pause(500);
            $timezoneValue = $browser->script("return document.getElementById('timezone').value;");
            $this->assertEquals('Pacific/Auckland', $timezoneValue[0]);

            // -----------------------------------------------
            // 2. Toggle 24-hour time
            // -----------------------------------------------
            $browser->scrollIntoView('label[for="use_24_hour_time"]');
            $browser->script("document.getElementById('use_24_hour_time').checked = true;");
            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertTrue((bool) User::first()->refresh()->use_24_hour_time);

            // -----------------------------------------------
            // 3. Change language to Spanish
            // -----------------------------------------------
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)->waitUntil(self::SETTLED, 5)
                ->scrollIntoView('button[data-tab="localization"]')
                ->pause(150)
                ->click('button[data-tab="localization"]')
                ->pause(500);

            $browser->script("
                var select = document.getElementById('language_code');
                select.value = 'es';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Guardado', 15);

            // Verify DB
            $this->assertEquals('es', User::first()->refresh()->language_code);

            // Verify page shows Spanish text (the settings page header)
            $browser->visit('/settings')
                ->waitFor('button[data-tab="localization"]', 5)->waitUntil(self::SETTLED, 5)
                ->scrollIntoView('button[data-tab="localization"]')
                ->pause(150)
                ->click('button[data-tab="localization"]')
                ->pause(500)
                ->assertSee('Configuraci');

            // Change back to English
            $browser->script("
                var select = document.getElementById('language_code');
                select.value = 'en';
                select.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('en', User::first()->refresh()->language_code);

            // -----------------------------------------------
            // 4. Change name
            // -----------------------------------------------
            // The name is always on the page: General is no longer a tab to press.
            $browser->visit('/settings')
                ->waitFor('#name', 5)->waitUntil(self::SETTLED, 5);

            $browser->script("
                var input = document.getElementById('name');
                input.value = 'New Name';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            ");

            $browser->script("document.querySelector('#section-profile form').requestSubmit()");
            $browser->waitForText('Saved', 15);

            // Verify DB
            $this->assertEquals('New Name', User::first()->refresh()->name);

            // Verify persisted on reload
            $browser->visit('/settings')
                ->waitFor('#name', 5)->waitUntil(self::SETTLED, 5)
                ->assertInputValue('name', 'New Name');
        });
    }

    /**
     * The rows of the profile, and which section an address opens.
     *
     * Localization and Appearance open in place under the fields that are always there; a row says
     * what it holds, and keeps saying it as the form is edited. An address may name a section, a
     * row, or a field: the last used to hide every section, and a link to ?highlight=phone used
     * to land on a hidden tab whenever another one had been remembered.
     */
    public function test_rows_open_in_place_and_addresses_find_their_section(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser, 'John Doe', 'test@gmail.com', 'password');
            $this->createTestTalent($browser);

            // The general fields are visible with every row closed.
            $browser->visit('/settings#section-profile')
                ->waitFor('#name', 5)->waitUntil(self::SETTLED, 5)
                ->assertVisible('#name')
                ->assertVisible('#phone-field')
                ->assertMissing('#timezone')
                ->assertAttribute('button.profile-tab[data-tab="localization"]', 'aria-expanded', 'false');

            // A row opens under its own name, says what it holds, and closes when pressed again.
            $browser->click('button.profile-tab[data-tab="localization"]')
                ->waitFor('#language_code', 5)
                ->assertAttribute('button.profile-tab[data-tab="localization"]', 'aria-expanded', 'true');
            // The row says the clock the account is on, whichever that is for the account this
            // browser made, and says the other one as soon as the switch is flipped.
            $on24 = (bool) $browser->script("return document.getElementById('use_24_hour_time').checked;")[0];
            $words = [true => __('messages.settings_24_hour'), false => __('messages.settings_12_hour')];
            $browser->assertSeeIn('button.profile-tab[data-tab="localization"] .event-row-summary', $words[$on24])
                // Changes count as unsaved only once the page has finished setting itself up: the
                // page says when that is, where this used to wait 700ms and hope.
                ->waitUntil('window.FormKit && window.FormKit.isArmed()', 5);
            $browser->script("
                var toggle = document.getElementById('use_24_hour_time');
                toggle.checked = ! toggle.checked;
                toggle.dispatchEvent(new Event('change', { bubbles: true }));
            ");
            $browser->assertSeeIn('button.profile-tab[data-tab="localization"] .event-row-summary', $words[! $on24]);
            // Something typed and not saved puts a dot on the section, and saving takes it off.
            $this->assertTrue($browser->script("return ! document.querySelector('.section-nav-link [data-dirty-dot=\"section-profile\"]').hidden;")[0]);
            $browser->click('button.profile-tab[data-tab="localization"]')
                ->waitUntilMissing('#language_code', 5);

            // Pressing the section you are on leaves its rows alone (it used to press its first tab).
            $browser->click('button.profile-tab[data-tab="appearance"]')
                ->waitFor('#profile-tab-appearance .js-theme-mode-btn', 5)
                ->click('.section-nav-link[data-section="section-profile"]')
                ->pause(300)
                ->assertAttribute('button.profile-tab[data-tab="appearance"]', 'aria-expanded', 'true');

            // An address that names a field opens the section the field is in.
            $browser->visit('/settings#section-api')->waitFor('#enable_api', 5)->waitUntil(self::SETTLED, 5);
            $browser->visit('/settings?highlight=phone#phone-field')
                ->waitFor('#phone-field', 5)->waitUntil(self::SETTLED, 5)
                ->assertVisible('#phone-field')
                ->assertMissing('#enable_api');

            // An address that names a row opens its section and the row.
            $browser->visit('/settings#payment-tab-payment-url')
                ->waitFor('#payment_url', 5)->waitUntil(self::SETTLED, 5)
                ->assertVisible('#payment_url')
                ->assertAttribute('button.payment-tab[data-tab="payment-url"]', 'aria-expanded', 'true')
                // Not connected: the row's line says what the method is, quietly, and steps aside
                // while the row is open, because the pane under it says the same in full.
                ->assertPresent('button.payment-tab[data-tab="payment-url"] .event-row-summary.is-empty')
                ->assertSeeIn('#payment-tab-payment-url', __('messages.payment_url_help'));
            $this->assertSame('hidden', $browser->script("return getComputedStyle(document.querySelector('button.payment-tab[data-tab=\"payment-url\"] .event-row-summary')).visibility;")[0]);
            $this->assertSame(__('messages.connect'), trim($browser->text('#payment-tab-payment-url button[type="submit"]')), 'the button says what pressing it does');

            // The two rows of the backup tool are its own (a Vue app): an address opens one too,
            // which is where the Help link for it and the user guide point.
            $browser->visit('/settings#backup-tab-import')
                ->waitFor('#backup-tab-import', 5)->waitUntil(self::SETTLED, 5)
                ->assertVisible('#backup-tab-import')
                ->assertAttribute('#backup-app button.event-subrow[data-tab="import"]', 'aria-expanded', 'true')
                ->assertMissing('#backup-tab-export');

            // An address that names nothing falls back to a real section, never to an empty page.
            $browser->visit('/settings#no-such-thing')->pause(500);
            $this->assertSame(1, $browser->script("return [].filter.call(document.querySelectorAll('.section-content'), function (s) { return getComputedStyle(s).display !== 'none'; }).length;")[0]);
        });
    }

    /** A refused save comes back on its own section, with the row that holds the message open. */
    public function test_a_refused_save_opens_the_row_that_holds_the_message(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser, 'John Doe', 'test@gmail.com', 'password');
            $this->createTestTalent($browser);

            // Leave another section as the one last visited, then post a timezone that does not exist.
            $browser->visit('/settings#section-api')->waitFor('#enable_api', 5)->waitUntil(self::SETTLED, 5);
            $browser->visit('/settings#section-profile')->waitFor('#name', 5)->waitUntil(self::SETTLED, 5);
            $browser->script("
                localStorage.setItem('lastSettingsSection', 'section-api');
                var select = document.getElementById('timezone');
                var option = document.createElement('option');
                option.value = 'Mars/Phobos';
                option.textContent = 'Mars/Phobos';
                select.appendChild(option);
                select.value = 'Mars/Phobos';
                var language = document.getElementById('language_code');
                language.value = 'fr';
                document.querySelector('#section-profile form').requestSubmit();
            ");

            $browser->waitFor('#section-profile ul.text-red-600, #section-profile ul.text-red-400', 15)
                ->assertVisible('#name')
                ->assertVisible('#language_code')
                ->assertAttribute('button.profile-tab[data-tab="localization"]', 'aria-expanded', 'true')
                ->assertSelected('#language_code', 'fr');
            $this->assertSame('en', User::first()->refresh()->language_code, 'sanity check: the save was refused');
        });
    }

    /**
     * The list is six entries with no headings between them, an address that names a section
     * inside a tab opens the tab and moves to it, and three switches that are about the account
     * as a whole are on the page without opening anything.
     */
    public function test_the_sections_are_grouped_and_the_preferences_are_always_there(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser, 'John Doe', 'test@gmail.com', 'password');
            $this->createTestTalent($browser);

            $browser->visit('/settings')
                ->waitFor('#name', 5)->waitUntil(self::SETTLED, 5)
                ->assertVisible('.section-nav-link[data-section="section-security"]');
            // Six entries, sign-in security straight after the account itself, and no headings.
            $order = $browser->script("return [].map.call(document.querySelectorAll('.section-nav-link'), function (a) { return a.dataset.section; });")[0];
            $this->assertSame(['section-profile', 'section-payment-methods', 'section-security', 'section-integrations', 'section-developers', 'section-account-data'], $order);
            $this->assertSame(0, $browser->script("return document.querySelectorAll('.section-nav-group').length;")[0]);

            // A tab holds its sections one under the other. An address that names the second one
            // shows the tab and moves the page to that section, so what the app links to
            // ("#section-two-factor", from a dozen places) is still where the person lands.
            $browser->visit('/settings?visit='.uniqid().'#section-two-factor')
                ->waitFor('#section-two-factor', 5)
                ->waitUntil("document.getElementById('section-security').style.display === 'block'", 5);
            $browser->waitUntil('window._settingsLoading === false', 5)->pause(300);
            $place = $browser->script("var block = document.getElementById('section-two-factor'); return { top: block.getBoundingClientRect().top, height: window.innerHeight, scrolled: window.scrollY, atEnd: window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2 };")[0];
            $this->assertGreaterThan(0, $place['scrolled'], 'the page moved');
            $this->assertGreaterThanOrEqual(0, $place['top'], 'to the section that was named: its title is in the window');
            // At the top of the window, or as near it as the end of the page allows.
            $this->assertTrue($place['top'] < 120 || $place['atEnd'], json_encode($place));
            $this->assertLessThan($place['height'] - 200, $place['top']);
            $browser->assertVisible('#section-two-factor')->assertVisible('.section-nav-link.nav-active[data-section="section-security"]');
            // Help opens the guide at the section the address named, before anything is pressed
            // (it used to open the top of the guide's page: its links are rendered after the
            // script that sets them).
            $this->assertStringEndsWith('/docs/account-settings#two-factor', $browser->script("return document.querySelector('.js-help-link').href;")[0]);

            // The first section of a tab is where the tab opens anyway: nothing moves.
            $browser->visit('/settings?visit='.uniqid().'#section-password')->waitFor('#section-password', 5);
            $browser->waitUntil('window._settingsLoading === false', 5)->pause(300);
            $this->assertSame(0, (int) $browser->script('return Math.round(window.scrollY);')[0]);

            // Pressing inside a section moves Help to that section of the guide.
            $this->assertStringEndsWith('/docs/account-settings#password', $browser->script("return document.querySelector('.js-help-link').href;")[0]);
            $browser->click('#section-two-factor h2')->pause(200);
            $this->assertStringEndsWith('/docs/account-settings#two-factor', $browser->script("return document.querySelector('.js-help-link').href;")[0]);

            // Back to the profile for the rest (the page remembers the tab last opened).
            $browser->visit('/settings?visit='.uniqid().'#section-profile')->waitFor('#name', 5)->waitUntil(self::SETTLED, 5);

            // "Ask me before I follow" sat inside Localization. It is with the other two switches
            // now, in no row at all, and saving it works from there.
            $this->assertTrue($browser->script("var box = document.getElementById('ask_before_following'); return !! box && ! box.closest('.event-subrow-body') && !! box.closest('#profile-preferences');")[0]);
            $was = (bool) $browser->script("return document.getElementById('ask_before_following').checked;")[0];
            $browser->waitUntil('window.FormKit && window.FormKit.isArmed()', 5);
            $browser->script("
                var box = document.getElementById('ask_before_following');
                box.checked = ! box.checked;
                box.dispatchEvent(new Event('change', { bubbles: true }));
                document.querySelector('#section-profile form').requestSubmit();
            ");
            $browser->waitForText('Saved', 15);
            // Stored the other way round: follow_consent_dismissed is "do not ask me".
            $this->assertSame($was, (bool) User::first()->refresh()->follow_consent_dismissed);
        });
    }

    /**
     * A webhook that gets everything shows one switch, not fourteen ticked boxes. Switched off,
     * the list is there to untick from; switched back on, every box is ticked again.
     */
    public function test_a_webhook_lists_its_event_types_only_when_it_does_not_get_them_all(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser, 'John Doe', 'test@gmail.com', 'password');
            $this->createTestTalent($browser);

            $browser->visit('/settings#section-webhooks')
                ->waitFor('#section-webhooks input[name="url"]', 5)->waitUntil(self::SETTLED, 5)
                ->assertMissing('#section-webhooks .event-check-grid');
            $this->assertTrue($browser->script("return document.getElementById('webhook_all_events_new').checked;")[0]);

            $browser->script("document.getElementById('webhook_all_events_new').click();");
            $browser->waitFor('#section-webhooks .event-check-grid', 5)
                ->assertSee(__('messages.settings_webhook_no_events_help'));

            // Untick two, then ask for everything again: all of them come back, and the list goes.
            $ticked = $browser->script("
                var form = document.getElementById('webhook_all_events_new').closest('form');
                var boxes = form.querySelectorAll('input[name=\"event_types[]\"]');
                boxes[0].checked = false;
                boxes[3].checked = false;
                document.getElementById('webhook_all_events_new').click();
                return [form.querySelectorAll('input[name=\"event_types[]\"]:checked').length, boxes.length];
            ")[0];
            $this->assertSame($ticked[1], $ticked[0], 'every type is ticked again');
            $this->assertGreaterThan(5, $ticked[1], 'sanity check: the list of types was found');
            $browser->waitUntilMissing('#section-webhooks .event-check-grid', 5);
        });
    }
}
