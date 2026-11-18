<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\User;
use App\Services\GeocodingService;
use Facebook\WebDriver\WebDriverKeys;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Cache;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * What the portal's shared scripts do, pinned from the outside: the pop-up menus every page of the
 * portal uses (layouts/app), the flash toast, the phone's sidebar (layouts/app-admin), the schedule
 * form's first values and its address tools (role/edit), and the event form's date type and venue
 * address tools (event/edit).
 *
 * Written against the jQuery these used to be, run green there, and run again unchanged once the
 * jQuery was gone: every value asserted here is one jQuery produced, quirks included (an element
 * jQuery showed gets the inline display a fresh element of its tag would have; a click inside an
 * open menu never reaches the document's other handlers; a toggle that matches both selectors runs
 * both). The menus are tested on fixtures put into a real portal page, so every rule of the shared
 * click handling is reached without depending on any one page's markup.
 *
 * The Validate and Accept buttons exist only with a Google key, so the two tests that press them
 * skip without one; the error paths of both forms are pinned without a key, where the server answers
 * before any lookup. To run the two, set BACKEND_GOOGLE_KEY to any value for THIS FILE'S RUN ONLY
 * (every other journey that saves an address would reach Google with it), on a database cache store:
 * the address is put in the geocoding cache first, so no request ever leaves.
 */
class SharedScriptsJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function person(): User
    {
        return User::factory()->create(['name' => 'Marge Simpson', 'email' => 'marge@gmail.com', 'email_verified_at' => now()]);
    }

    private function role(User $user, string $type, string $subdomain, array $attributes = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = 'Shared Scripts '.$type;
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->forceFill($attributes);
        $role->saveQuietly();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    // ------------------------------------------------------------------ pop-up menus

    /** A portal page with fixture toggles and menus, and a count of clicks that reach the window. */
    private function menus(Browser $browser): void
    {
        // An account with no schedule is sent to the first-run steps, which is not a portal page.
        $owner = $this->person();
        $this->role($owner, 'talent', 'sharedmenus');
        $browser->loginAs($owner)->visit('/dashboard')->waitFor('#main-content');

        $browser->script(<<<'JS'
            var toggle = function (id, tag, cls, target, top, left, width) {
                return '<' + tag + ' id="' + id + '" ' + (tag === 'button' ? 'type="button" ' : '')
                    + (cls ? 'class="' + cls + '" ' : '') + 'data-popup-target="' + target + '" aria-expanded="false"'
                    + ' style="position:fixed;top:' + top + 'px;left:' + left + 'px;width:' + width + 'px;height:30px;z-index:2000;background:#ddd">'
                    + '<span id="' + id + '-icon" style="display:inline-block;width:20px;height:20px">i</span>' + id + '</' + tag + '>';
            };
            // Like the real menus: a part that is just the menu, and an item wrapped in a div that
            // names the menu, which is how choosing an item closes it (home/_header and others).
            var menu = function (id, width) {
                return '<div id="' + id + '" class="pop-up-menu hidden" style="width:' + width + 'px;height:60px;background:#fff">'
                    + '<span id="' + id + '-inside" style="display:block;width:100%;height:30px">inside</span>'
                    + '<div id="' + id + '-item" class="py-1" data-popup-target="' + id + '" style="height:30px"><a id="' + id + '-item-link" href="#" style="display:block;height:30px">item</a></div>'
                    + '</div>';
            };
            document.body.insertAdjacentHTML('beforeend',
                toggle('pj-a', 'button', 'popup-toggle', 'pj-menu-a', 100, 400, 80) + menu('pj-menu-a', 200)
                + toggle('pj-b', 'button', 'popup-toggle', 'pj-menu-b', 100, 800, 80) + menu('pj-menu-b', 200)
                + toggle('pj-div', 'div', '', 'pj-menu-div', 300, 400, 80) + menu('pj-menu-div', 150)
                + toggle('pj-both', 'div', 'popup-toggle', 'pj-menu-both', 400, 400, 80) + menu('pj-menu-both', 150)
                + toggle('pj-missing', 'button', 'popup-toggle', 'pj-nowhere', 500, 400, 80)
                + toggle('pj-edge', 'button', 'popup-toggle', 'pj-menu-edge', 600, 0, 40) + menu('pj-menu-edge', 300)
                + '<div id="pj-outside" style="position:fixed;bottom:0;right:0;width:60px;height:60px;z-index:2000;background:#eee"></div>'
            );
            window.__pjWindowClicks = 0;
            window.addEventListener('click', function () { window.__pjWindowClicks++; });
        JS);
    }

    /** Everything about a menu a script can see, plus the toggle's aria-expanded. */
    private function menuState(Browser $browser, string $menu, ?string $toggle = null): array
    {
        return $browser->script('
            var m = document.getElementById('.json_encode($menu).');
            var t = '.json_encode($toggle).' ? document.getElementById('.json_encode($toggle).') : null;
            return {
                hiddenClass: m.classList.contains("hidden"),
                shown: !!(m.offsetWidth || m.offsetHeight),
                display: m.style.display,
                position: m.style.position,
                top: m.style.top,
                left: m.style.left,
                right: m.style.right,
                zIndex: m.style.zIndex,
                expanded: t ? t.getAttribute("aria-expanded") : null,
                innerWidth: window.innerWidth,
                windowClicks: window.__pjWindowClicks,
            };
        ')[0];
    }

    public function test_a_menu_opens_under_its_toggle_and_says_so(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['hiddenClass']);
            $this->assertTrue($s['shown']);
            $this->assertSame('', $s['display'], 'no inline display is left behind');
            $this->assertSame('fixed', $s['position']);
            $this->assertSame('134px', $s['top'], 'four pixels under the toggle');
            $this->assertSame(($s['innerWidth'] - 480).'px', $s['right'], 'hung from the toggle\'s end edge');
            $this->assertSame('auto', $s['left']);
            $this->assertSame('1000', $s['zIndex']);
            $this->assertSame('true', $s['expanded']);
            $this->assertSame(0, $s['windowClicks'], 'the toggle\'s click stops at the document');
        });
    }

    public function test_a_click_inside_keeps_it_open_and_a_click_outside_closes_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100)->click('#pj-menu-a-inside')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertTrue($s['shown'], 'still open');
            $this->assertSame(0, $s['windowClicks'], 'a click inside a menu never reaches the window');

            $browser->click('#pj-outside')->pause(100);
            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertTrue($s['hiddenClass']);
            $this->assertFalse($s['shown']);
            $this->assertSame('', $s['display']);
            $this->assertSame('false', $s['expanded']);
            $this->assertSame(1, $s['windowClicks'], 'a click elsewhere does');
        });
    }

    public function test_escape_closes_it_and_a_second_click_on_the_toggle_closes_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100);
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
            $browser->pause(100);
            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['shown'], 'Escape closes it');
            $this->assertSame('false', $s['expanded']);

            $browser->click('#pj-a')->pause(100)->click('#pj-a')->pause(100);
            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['shown'], 'the toggle closes what it opened');
            $this->assertSame('false', $s['expanded']);
        });
    }

    /** Opening a second menu leaves the first one open: the toggle's click never reaches the document. */
    public function test_a_second_toggle_opens_its_menu_beside_the_first(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100)->click('#pj-b')->pause(100);

            $this->assertTrue($this->menuState($browser, 'pj-menu-a', 'pj-a')['shown']);
            $this->assertTrue($this->menuState($browser, 'pj-menu-b', 'pj-b')['shown']);
        });
    }

    public function test_a_div_toggle_works_and_one_that_matches_both_rules_runs_both(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-div')->pause(100);
            $s = $this->menuState($browser, 'pj-menu-div', 'pj-div');
            $this->assertTrue($s['shown'], 'a div with data-popup-target is a toggle');
            $this->assertSame('true', $s['expanded']);

            $browser->click('#pj-outside')->pause(100)->click('#pj-both')->pause(100);
            $s = $this->menuState($browser, 'pj-menu-both', 'pj-both');
            $this->assertFalse($s['shown'], 'opened by one rule and closed again by the other');
            $this->assertSame('false', $s['expanded']);
        });
    }

    /** A toggle whose menu is not on the page closes whatever is open. */
    public function test_a_toggle_without_a_menu_closes_the_open_one(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100)->click('#pj-missing')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['shown']);
            $this->assertSame('false', $s['expanded']);
        });
    }

    public function test_a_menu_that_would_leave_the_screen_is_kept_on_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-edge')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-edge', 'pj-edge');
            $this->assertTrue($s['shown']);
            $this->assertSame('8px', $s['left'], 'eight pixels in from the left edge');
            $this->assertSame('auto', $s['right']);
        });
    }

    /** A real toggle holds an icon: a click on it is a click on the toggle, placed under the toggle. */
    public function test_a_click_on_the_icon_inside_a_toggle_opens_its_menu(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a-icon')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertTrue($s['shown']);
            $this->assertSame('134px', $s['top']);
            $this->assertSame(($s['innerWidth'] - 480).'px', $s['right']);
            $this->assertSame('true', $s['expanded'], 'the toggle says so, not the icon');
            $this->assertNull($browser->script('return document.getElementById("pj-a-icon").getAttribute("aria-expanded");')[0]);
        });
    }

    /** An item wrapped in a div that names its menu closes the menu when it is chosen. */
    public function test_choosing_an_item_closes_the_menu(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->click('#pj-a')->pause(100)->click('#pj-menu-a-item-link')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['shown']);
            $this->assertSame('false', $s['expanded']);
            $this->assertSame(0, $s['windowClicks'], 'and the click goes no further');
        });
    }

    public function test_right_to_left_hangs_it_from_the_toggles_start(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->script('document.documentElement.dir = "rtl";');
            $browser->click('#pj-a')->pause(100);

            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertSame('400px', $s['left']);
            $this->assertSame('auto', $s['right']);
            $this->assertSame('134px', $s['top']);
        });
    }

    /** ticket/sales calls the two functions itself, with an event of its own making. */
    public function test_the_two_functions_can_be_called_from_a_page_of_its_own(): void
    {
        $this->browse(function (Browser $browser) {
            $this->menus($browser);
            $browser->script('
                window.__pjStopped = false;
                onPopUpClick("pj-menu-a", {
                    currentTarget: document.getElementById("pj-a"),
                    stopPropagation: function () { window.__pjStopped = true; }
                });
            ');
            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertTrue($s['shown']);
            $this->assertSame('134px', $s['top'], 'placed under the button it was given');
            $this->assertSame('true', $s['expanded']);
            $this->assertTrue($browser->script('return window.__pjStopped;')[0], 'the given event is stopped');

            $browser->script('hidePopUp();');
            $s = $this->menuState($browser, 'pj-menu-a', 'pj-a');
            $this->assertFalse($s['shown']);
            $this->assertSame('false', $s['expanded']);
        });
    }

    // ------------------------------------------------------------------ toast and sidebar

    public function test_a_flashed_message_is_toasted(): void
    {
        $owner = $this->person();
        $role = $this->role($owner, 'talent', 'sharedtoast', ['email_verified_at' => null]);

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/resend');

            // The toast lives three seconds from when the page's ready callbacks run, which can be
            // before a slow page finishes loading: what it leaves in sessionStorage says it ran,
            // and the toast itself is read while it is still there.
            $browser->waitUntil('Object.keys(sessionStorage).some(function (k) { return k.indexOf("toast_") === 0; })', 10);
            $toast = $browser->script('var t = document.querySelector(".toastify"); return t ? [t.textContent, t.style.background] : null;')[0];
            if ($toast !== null) {
                $this->assertStringContainsString(__('messages.sent_confirmation_email'), $toast[0]);
                $this->assertStringContainsString('rgb(75, 181, 67)', $toast[1]);
            }
        });
    }

    public function test_the_phone_sidebar_opens_and_closes_and_gives_focus_back(): void
    {
        $owner = $this->person();
        $this->role($owner, 'talent', 'sharedsidebar');

        $this->browse(function (Browser $browser) use ($owner) {
            $browser->loginAs($owner);
            $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
                'cmd' => 'Emulation.setDeviceMetricsOverride',
                'params' => ['width' => 390, 'height' => 844, 'deviceScaleFactor' => 1, 'mobile' => true],
            ]);

            try {
                $browser->visit('/dashboard')->waitFor('#open-sidebar')->pause(300);

                $state = fn () => $browser->script('var s = document.getElementById("sidebar"); return [s.style.display, s.getAttribute("data-state"), document.activeElement ? document.activeElement.id : null];')[0];

                $browser->click('#open-sidebar')->pause(300);
                $this->assertSame(['block', 'open', 'close-sidebar'], $state());

                $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ESCAPE);
                $browser->pause(200);
                $this->assertSame(['none', 'closed', 'open-sidebar'], $state());

                $browser->click('#open-sidebar')->pause(300);
                $this->assertSame(['block', 'open', 'close-sidebar'], $state());

                $browser->click('#close-sidebar')->pause(200);
                $this->assertSame('none', $state()[0]);
                $this->assertSame('closed', $state()[1]);
            } finally {
                // One browser serves every test in this class: back to its own size.
                $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
                    'cmd' => 'Emulation.clearDeviceMetricsOverride',
                    'params' => (object) [],
                ]);
            }
        });
    }

    // ------------------------------------------------------------------ schedule form

    /**
     * What the form puts back by script after a refused save: the server marks the font, the
     * language and the colours from the saved schedule, and the page's own script sets them to
     * what was typed (old()). So the save is refused on purpose (no name) after changing all three.
     */
    public function test_a_refused_save_comes_back_on_what_was_typed(): void
    {
        $owner = $this->person();
        $role = $this->role($owner, 'venue', 'sharedform', [
            'background' => 'gradient',
            'background_colors' => '#112233, #445566',
            'font_family' => 'Abril_Fatface',
            'language_code' => 'en',
        ]);

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/edit')->waitFor('#edit-form', 15)->pause(500);

            $preset = $browser->script('
                var colors = document.getElementById("background_colors");
                var preset = null;
                for (var i = 0; i < colors.options.length; i++) {
                    if (colors.options[i].value) { preset = colors.options[i].value; break; }
                }
                document.getElementById("font_family").value = "Abel";
                document.getElementById("language_code").value = "es";
                colors.value = preset;
                document.getElementById("name").value = "";
                // Straight to the server, past the checks in the page: the refusal is the point.
                HTMLFormElement.prototype.submit.call(document.getElementById("edit-form"));
                return preset;
            ')[0];
            $this->assertNotEmpty($preset, 'fixture: the colours offer a preset');

            $browser->waitFor('#edit-form', 15)->pause(500);
            $values = $browser->script('
                var v = function (id) { var el = document.getElementById(id); return el ? el.value : null; };
                return { name: v("name"), font: v("font_family"), language: v("language_code"), colors: v("background_colors") };
            ')[0];

            $this->assertSame('', $values['name'], 'fixture: the save was refused');
            $this->assertSame('Abel', $values['font'], 'the typed font, not the saved one');
            $this->assertSame('es', $values['language']);
            $this->assertSame($preset, $values['colors']);
        });
    }

    public function test_view_map_on_the_schedule_form(): void
    {
        $owner = $this->person();
        $role = $this->role($owner, 'venue', 'sharedmap', ['address1' => '', 'city' => '']);

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/edit')->waitFor('#edit-form', 15)->pause(500);
            $browser->script('
                window.__opened = null;
                window.open = function (url) { window.__opened = url; };
                document.getElementById("address1").value = "";
                document.getElementById("city").value = "";
                document.getElementById("state").value = "";
                document.getElementById("postal_code").value = "";
                viewMap();
            ');
            // The country picker always has a country, so even with every field empty there is
            // something to look up.
            $this->assertSame('https://www.google.com/maps/search/?api=1&query=United%20States', $browser->script('return window.__opened;')[0]);

            $browser->script('
                document.getElementById("address1").value = "131 West 3rd";
                document.getElementById("city").value = "New York";
                viewMap();
            ');
            $opened = (string) $browser->script('return window.__opened;')[0];
            $this->assertStringStartsWith('https://www.google.com/maps/search/?api=1&query=131%20West%203rd%2C%20New%20York', $opened);
        });
    }

    // ------------------------------------------------------------------ event form

    public function test_the_days_of_the_week_follow_the_date_type(): void
    {
        $owner = $this->person();
        $role = $this->role($owner, 'talent', 'sharedevent');

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/add-event')
                ->waitFor('#event_name', 15)->waitUntil('window.vueApp !== undefined', 15)->pause(300);

            $days = fn () => $browser->script('var d = document.getElementById("days_of_week_div"); return [d.style.display, !!(d.offsetWidth || d.offsetHeight)];')[0];
            $this->assertSame(['none', false], $days(), 'a one-time event shows no days (put away on load)');

            $browser->click('#recurring + span')->pause(300);
            $this->assertSame(['block', true], $days(), 'weekly shows them');
            $this->assertTrue($browser->script('return window.vueApp.isRecurring;')[0]);

            $browser->click('#one_time + span')->pause(300);
            $this->assertSame(['none', false], $days());
            $this->assertFalse($browser->script('return window.vueApp.isRecurring;')[0]);

            $browser->click('#recurring + span')->pause(300);
            $this->assertSame(['block', true], $days(), 'and again');

            $browser->script('window.vueApp.event.recurring_frequency = "monthly";');
            $browser->pause(300);
            $this->assertSame(['none', false], $days(), 'a monthly event has no days to pick');
        });
    }

    // ------------------------------------------------------------------ Validate without a key

    /**
     * Without a key the buttons are not drawn, but the functions are on the page and the server
     * answers before any lookup: 400 for nothing typed, 500 for an address (no geocoding set up).
     * That pins the request, its token and the error line on CI, where there is no key.
     */
    private function noKey(): void
    {
        if (config('services.google.backend')) {
            $this->markTestSkipped('With a key the server would look the address up; the tests that need a key cover this.');
        }
    }

    private function validated(Browser $browser): string
    {
        $browser->waitUntil('document.getElementById("address_response").textContent.indexOf("...") === -1', 10);

        return $browser->script('var r = document.getElementById("address_response"); return [r.textContent, r.style.display].join("|");')[0];
    }

    public function test_validate_answers_an_error_on_the_schedule_form(): void
    {
        $this->noKey();
        $owner = $this->person();
        $role = $this->role($owner, 'venue', 'sharednokey', ['country_code' => 'US']);

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/edit')->waitFor('#edit-form', 15)->pause(500);

            $browser->script('["address1","city","state","postal_code"].forEach(function (id) { document.getElementById(id).value = ""; }); onValidateClick();');
            $this->assertSame(__('messages.an_error_occurred').'|block', $this->validated($browser), 'nothing typed');

            $browser->script('document.getElementById("address1").value = "131 west third"; document.getElementById("city").value = "new york"; onValidateClick();');
            $this->assertSame(__('messages.an_error_occurred').'|block', $this->validated($browser), 'no geocoding set up');
        });
    }

    public function test_validate_answers_an_error_on_the_event_form(): void
    {
        $this->noKey();
        $owner = $this->person();
        $role = $this->role($owner, 'talent', 'sharednokeyevent');

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/add-event')
                ->waitFor('#event_name', 15)->waitUntil('window.vueApp !== undefined', 15);
            $browser->script('window.vueApp.isInPerson = true; window.vueApp.venueType = "create_new";');
            $browser->waitUntil('document.getElementById("venue_address1") !== null && document.getElementById("address_response") !== null', 10)->pause(300);

            $browser->script('onValidateClick();');
            $this->assertSame(__('messages.an_error_occurred').': Bad Request|block', $this->validated($browser), 'nothing typed');

            $browser->script('
                var set = function (id, value) {
                    var el = document.getElementById(id);
                    el.value = value;
                    el.dispatchEvent(new Event("input", { bubbles: true }));
                };
                set("venue_address1", "131 west third");
                set("venue_city", "new york");
            ');
            $browser->pause(200)->script('onValidateClick();');
            $this->assertSame(__('messages.an_error_occurred').': Internal Server Error|block', $this->validated($browser), 'no geocoding set up');
        });
    }

    // ------------------------------------------------------------------ Validate and Accept (need a key)

    /** The address put in the geocoding cache, for every country the form might send with it. */
    private function seedAddress(array $typed): void
    {
        foreach (['', 'us', 'de', 'il', 'gb'] as $country) {
            $probe = new Role;
            $probe->fill($typed + ['country_code' => $country]);
            $address = $probe->fullAddress();

            Cache::put(GeocodingService::cacheKey($address), [
                'status' => GeocodingService::OK,
                'lat' => 40.7300,
                'lng' => -73.9990,
                'formatted_address' => '131 W 3rd St, New York, NY 10012, USA',
                'place_id' => 'dusk-fixture',
                'address_components' => [
                    ['long_name' => '131', 'short_name' => '131', 'types' => ['street_number']],
                    ['long_name' => 'West 3rd Street', 'short_name' => 'W 3rd St', 'types' => ['route']],
                    ['long_name' => 'New York', 'short_name' => 'New York', 'types' => ['locality']],
                    ['long_name' => 'NY', 'short_name' => 'NY', 'types' => ['administrative_area_level_1']],
                    ['long_name' => '10012', 'short_name' => '10012', 'types' => ['postal_code']],
                    ['long_name' => 'United States', 'short_name' => 'US', 'types' => ['country']],
                ],
            ], now()->addHour());
        }
    }

    private function needsKey(): void
    {
        if (! config('services.google.backend')) {
            $this->markTestSkipped('Validate and Accept are drawn only with a Google key (BACKEND_GOOGLE_KEY).');
        }

        // The seeded answer has to be read by the served app, which only a shared store allows:
        // with the array store the app would ask Google, with whatever key is set.
        if (config('cache.default') === 'array') {
            $this->markTestSkipped('The geocoding cache must be a shared store (CACHE_STORE=database) for the seeded address to be read.');
        }
    }

    /**
     * [text, inline display, computed display] of the answer line, and [inline display, computed
     * display] of Accept. The element's own computed display, not whether it is on screen: these
     * sit in a section that may be folded away, which is the section's business. Accept is given
     * inline-block (what a fresh button would be) and computes to block in its flex row.
     */
    private function addressUi(Browser $browser): array
    {
        return $browser->script('
            var r = document.getElementById("address_response");
            var a = document.getElementById("accept_button");
            return [r.textContent, r.style.display, getComputedStyle(r).display, a.style.display, getComputedStyle(a).display];
        ')[0];
    }

    public function test_validate_and_accept_on_the_schedule_form(): void
    {
        $this->needsKey();
        $this->seedAddress(['address1' => '131 west third', 'city' => 'new york']);

        $owner = $this->person();
        $role = $this->role($owner, 'venue', 'sharedvalidate', ['country_code' => 'US']);

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/edit')->waitFor('#edit-form', 15)->pause(500);

            // Nothing typed: the server answers 400, said in the answer line, and Accept stays away.
            $browser->script('["address1","city","state","postal_code"].forEach(function (id) { document.getElementById(id).value = ""; }); onValidateClick();');
            $browser->waitUntil('document.getElementById("address_response").textContent.indexOf("...") === -1', 10);
            $this->assertSame([__('messages.an_error_occurred'), 'block', 'block', 'none', 'none'], $this->addressUi($browser));

            $browser->script('
                document.getElementById("address1").value = "131 west third";
                document.getElementById("city").value = "new york";
                onValidateClick();
            ');
            $browser->waitUntil('document.getElementById("address_response").textContent.indexOf("131 West 3rd Street") === 0', 10);
            $this->assertSame(['131 West 3rd Street New York NY 10012', 'block', 'block', 'inline-block', 'block'], $this->addressUi($browser));

            // Pressed through the page, as a person's click would: the section may be folded away.
            $browser->script('document.getElementById("accept_button").click();');
            $browser->pause(200);
            $fields = $browser->script('return ["address1","city","state","postal_code"].map(function (id) { return document.getElementById(id).value; });')[0];
            $this->assertSame(['131 West 3rd Street', 'New York', 'NY', '10012'], $fields);
            $ui = $this->addressUi($browser);
            $this->assertSame(['none', 'none', 'none', 'none'], [$ui[1], $ui[2], $ui[3], $ui[4]], 'both put away once accepted');
        });
    }

    public function test_validate_and_accept_on_the_event_form(): void
    {
        $this->needsKey();
        $this->seedAddress(['address1' => '131 west third', 'city' => 'new york']);

        $owner = $this->person();
        $role = $this->role($owner, 'talent', 'sharedvenue');

        $this->browse(function (Browser $browser) use ($owner, $role) {
            $browser->loginAs($owner)->visit('/'.$role->subdomain.'/add-event')
                ->waitFor('#event_name', 15)->waitUntil('window.vueApp !== undefined', 15);
            $browser->script('window.vueApp.isInPerson = true; window.vueApp.venueType = "create_new";');
            $browser->waitUntil('document.getElementById("validate_button") !== null', 10)->pause(300);

            // Nothing typed: the server's status text follows the generic line. Pressed through the
            // page, as a person's click would: the venue's section may be folded away.
            $browser->script('document.getElementById("validate_button").click();');
            $browser->waitUntil('document.getElementById("address_response").textContent.indexOf("...") === -1', 10);
            $ui = $this->addressUi($browser);
            $this->assertSame(__('messages.an_error_occurred').': Bad Request', $ui[0]);
            $this->assertSame(['block', 'block', 'none', 'none'], [$ui[1], $ui[2], $ui[3], $ui[4]]);

            $browser->script('
                var set = function (id, value) {
                    var el = document.getElementById(id);
                    el.value = value;
                    el.dispatchEvent(new Event("input", { bubbles: true }));
                };
                set("venue_address1", "131 west third");
                set("venue_city", "new york");
            ');
            $browser->pause(200);
            $browser->script('document.getElementById("validate_button").click();');
            $browser->waitUntil('document.getElementById("address_response").textContent.indexOf("131 West 3rd Street") === 0', 10);
            $this->assertSame(['131 West 3rd Street New York NY 10012', 'block', 'block', 'inline-block', 'block'], $this->addressUi($browser));

            $browser->script('document.getElementById("accept_button").click();');
            $browser->pause(300);
            $kept = $browser->script('return [window.vueApp.venueAddress1, window.vueApp.venueCity, window.vueApp.venueState, window.vueApp.venuePostalCode];')[0];
            $this->assertSame(['131 West 3rd Street', 'New York', 'NY', '10012'], $kept);
            $ui = $this->addressUi($browser);
            $this->assertSame(['none', 'none', 'none', 'none'], [$ui[1], $ui[2], $ui[3], $ui[4]]);
        });
    }
}
