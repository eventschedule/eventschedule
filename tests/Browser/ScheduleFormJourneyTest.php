<?php

namespace Tests\Browser;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The schedule form as someone uses it: tabs that say what they hold, rows that open in place
 * where there were tabs inside the tabs, and one bar that says what is unsaved and what Save will
 * remove.
 *
 * tests/Feature/ScheduleFormShellTest.php holds the markup. This runs it: which row is open, what
 * a summary reads and what the bar says are all decided by the page's script, and none of that is
 * reachable from PHPUnit.
 */
class ScheduleFormJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $owner;

    private Role $talent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->talent = $this->makeRole($this->owner, 'talent', 'journeyquartet', ['name' => 'Journey Quartet', 'website' => 'https://quartet.example.org']);
        foreach (['Main stage', 'Late shows'] as $name) {
            $group = new Group;
            $group->role_id = $this->talent->id;
            $group->name = $name;
            $group->slug = \Illuminate\Support\Str::slug($name);
            $group->save();
        }
    }

    private function makeRole(User $user, string $type, string $subdomain, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = ucfirst($subdomain);
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    /**
     * Each visit is a page load (a visit that differs only by its fragment is not), and a form the
     * last test changed is allowed to be left.
     */
    private function open(Browser $browser, string $fragment = '', string $query = ''): void
    {
        $browser->script('window._skipUnsavedWarning = true;');
        $browser->loginAs($this->owner)
            ->visit('/journeyquartet/edit?visit='.uniqid().$query.$fragment)
            ->waitFor('#edit-form', 15)
            // The page arms its unsaved tracking shortly after load, and nothing before that
            // counts: waited for by name, where a fixed pause was a guess about a slow machine.
            ->waitUntil('window.FormKit !== undefined && window.FormSaveBar !== undefined && window.FormKit.isArmed()', 15)
            ->pause(100);
    }

    private function barText(Browser $browser): string
    {
        return trim(preg_replace('/\s+/', ' ', $browser->script('return document.querySelector("#form-save-bar .event-save-status").innerText;')[0]));
    }

    private function summary(Browser $browser, string $key): string
    {
        return trim($browser->script('return document.querySelector(\'[data-summary="'.$key.'"]\').textContent;')[0]);
    }

    private function shownTab(Browser $browser): string
    {
        return $browser->script('return Array.prototype.filter.call(document.querySelectorAll(".section-content"), function (s) { return s.style.display === "block"; }).map(function (s) { return s.id; }).join(",");')[0];
    }

    private function showTab(Browser $browser, string $section): void
    {
        $browser->script('document.querySelector(\'a[data-section="'.$section.'"]\').click();');
        $browser->waitUntil('document.getElementById("'.$section.'").style.display === "block"', 10);
    }

    /** Whether a row's pane is on the page. */
    private function paneOpen(Browser $browser, string $pane): bool
    {
        return (bool) $browser->script('var p = document.getElementById("'.$pane.'"); return ! p.hidden && p.offsetParent !== null;')[0];
    }

    /**
     * A real click, on an element brought to the middle of the window first: the save bar sits
     * over the bottom of the page, and a row down there is under it until the page is scrolled.
     */
    private function press(Browser $browser, string $selector): void
    {
        $browser->script('document.querySelector('.json_encode($selector).').scrollIntoView({ block: "center" });');
        $browser->pause(150)->click($selector)->pause(200);
    }

    private function type(Browser $browser, string $selector, string $value): void
    {
        $browser->script('var el = document.querySelector('.json_encode($selector).'); el.value = '.json_encode($value).'; el.dispatchEvent(new Event("input", { bubbles: true }));');
        $browser->pause(150);
    }

    public function test_the_tabs_say_what_they_hold_and_the_rows_say_their_setting(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $this->assertSame('section-details', $this->shownTab($browser));
            $this->assertSame('Journey Quartet', $this->summary($browser, 'section-details'));
            // A tab with nothing in it says so, quietly, where it used to say nothing.
            $this->assertSame(__('messages.settings_not_connected'), $this->summary($browser, 'section-integrations'));
            $this->assertSame(__('messages.settings_not_connected'), $this->summary($browser, 'integration:google'));
            $this->assertTrue((bool) $browser->script('return document.querySelector(\'[data-summary="section-integrations"]\').classList.contains("is-empty");')[0]);
            $this->assertNotSame('', $this->summary($browser, 'settings:advanced'), 'a row of mixed settings names the first of them');
            $this->assertSame('Main stage, Late shows', $this->summary($browser, 'section-subschedules'));
            $this->assertSame('Main stage, Late shows', $this->summary($browser, 'customize:subschedules'));
            $this->assertStringContainsString('journeyquartet@gmail.com', $this->summary($browser, 'details:contact'));
            $this->assertStringContainsString('quartet.example.org', $this->summary($browser, 'details:contact'));
            $this->assertStringContainsString('America/New_York', $this->summary($browser, 'details:localization'));

            // Nothing is unsaved on arrival, and nothing claims to be.
            $this->assertSame(__('messages.no_unsaved_changes'), $this->barText($browser));
            $this->assertSame(0, $browser->script('return Array.prototype.filter.call(document.querySelectorAll("[data-dirty-dot]"), function (d) { return ! d.hidden; }).length;')[0]);

            // The name typed is the tab's summary as it is typed.
            $this->type($browser, '#name', 'Journey Quintet');
            $this->assertSame('Journey Quintet', $this->summary($browser, 'section-details'));
        });
    }

    public function test_a_row_opens_in_place_and_one_of_a_list_is_open_at_a_time(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $this->assertFalse($this->paneOpen($browser, 'details-tab-contact'), 'rows start closed');
            $this->assertTrue((bool) $browser->script('return document.getElementById("name").offsetParent !== null;')[0], 'and the name is on the page without opening anything');

            $this->press($browser, '.details-tab[data-tab="contact"]');
            $this->assertTrue($this->paneOpen($browser, 'details-tab-contact'));
            $this->assertSame('true', $browser->attribute('.details-tab[data-tab="contact"]', 'aria-expanded'));

            $this->press($browser, '.details-tab[data-tab="localization"]');
            $this->assertTrue($this->paneOpen($browser, 'details-tab-localization'));
            $this->assertFalse($this->paneOpen($browser, 'details-tab-contact'), 'opening one row closes the other');

            // Pressing the open row closes it.
            $this->press($browser, '.details-tab[data-tab="localization"]');
            $this->assertFalse($this->paneOpen($browser, 'details-tab-localization'));

            // Pressing the tab you are on leaves its rows alone: it used to press the first inner tab.
            $this->press($browser, '.details-tab[data-tab="contact"]');
            $browser->script('document.querySelector(\'a[data-section="section-details"]\').click();');
            $browser->pause(200);
            $this->assertTrue($this->paneOpen($browser, 'details-tab-contact'));
        });
    }

    public function test_a_change_marks_its_tab_and_the_bar_names_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $this->press($browser, '.details-tab[data-tab="contact"]');
            $this->type($browser, '#website', 'https://quintet.example.org');

            $this->assertStringContainsString('quintet.example.org', $this->summary($browser, 'details:contact'), 'the row says the new value');
            $this->assertSame(__('messages.unsaved').': '.__('messages.details'), $this->barText($browser));
            $this->assertFalse((bool) $browser->script('return document.querySelector(\'.section-nav-link [data-dirty-dot="section-details"]\').hidden;')[0]);
            $this->assertTrue((bool) $browser->script('return document.querySelector(\'.section-nav-link [data-dirty-dot="section-style"]\').hidden;')[0]);

            // The bar's tab name is a link to the tab.
            $this->showTab($browser, 'section-style');
            $browser->click('#form-save-bar .event-save-status button.event-link')->pause(200);
            $this->assertSame('section-details', $this->shownTab($browser));

            // Cancel asks before it throws the change away, and Keep editing keeps it.
            $browser->click('#form-save-bar .event-bar-text')->pause(200);
            $this->assertSame(__('messages.discard_unsaved_changes'), $this->barText($browser));
            $browser->script('Array.prototype.filter.call(document.querySelectorAll("#form-save-bar .event-bar-text"), function (b) { return b.offsetParent !== null; })[0].click();');
            $browser->pause(200);
            $this->assertSame('https://quintet.example.org', $browser->value('#website'));
            $this->assertStringContainsString(__('messages.details'), $this->barText($browser));
        });
    }

    public function test_removing_a_sub_schedule_is_announced_and_then_saved(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-subschedules');
            $this->assertSame('section-subschedules', $this->shownTab($browser));

            $this->press($browser, 'button.customize-tab[data-tab="subschedules"]');
            $this->assertSame(2, $browser->script('return document.querySelectorAll("#group-items > div").length;')[0]);

            $browser->script('document.querySelector("#group-items > div [data-action=\"remove-list-row\"]").click();');
            $browser->pause(300);

            // Said before it is done: saving deletes the sub-schedule and takes it off its events.
            // By name: "Saving removes: Main stage", a link to the tab it was on.
            $this->assertSame(__('messages.saving_removes').': Main stage', $this->barText($browser));
            $browser->script('window.FormKit.showSection("section-details");');
            $browser->click('#form-save-bar .event-save-status button.event-link')->pause(200);
            $this->assertSame('section-subschedules', $this->shownTab($browser));
            $this->assertSame('Late shows', $this->summary($browser, 'customize:subschedules'));

            $browser->script('document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertSame(['Late shows'], $this->talent->groups()->pluck('name')->all());
    }

    /** Two added, the first of them removed, a third added: three keys, where two used to collide. */
    public function test_rows_added_after_a_removal_are_all_saved(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-subschedules');
            $this->press($browser, 'button.customize-tab[data-tab="subschedules"]');

            $browser->script('addGroupField(); addGroupField();');
            $browser->pause(200);
            $browser->script('document.querySelectorAll("#group-items > div")[2].querySelector("[data-action=\"remove-list-row\"]").click(); addGroupField();');
            $browser->pause(200);
            $browser->script('
                var rows = document.querySelectorAll("#group-items > div");
                var set = function (row, value) { var input = row.querySelector("input[name$=\"[name]\"]"); input.value = value; input.dispatchEvent(new Event("input", { bubbles: true })); };
                set(rows[2], "Workshops"); set(rows[3], "Matinees");
            ');
            $browser->pause(200);
            $this->assertSame('Main stage, Late shows, Workshops, Matinees', $this->summary($browser, 'customize:subschedules'));

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertEqualsCanonicalizing(['Main stage', 'Late shows', 'Workshops', 'Matinees'], $this->talent->groups()->pluck('name')->all());
    }

    /**
     * A refused save draws the new rows back under the keys they were posted with. The next row
     * added used to start counting from zero again, take one of those keys, and replace it.
     */
    public function test_a_row_added_after_a_refused_save_does_not_replace_one(): void
    {
        $this->makeRole($this->owner, 'talent', 'takenname');

        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-subschedules');
            $this->press($browser, 'button.customize-tab[data-tab="subschedules"]');
            $browser->script('addGroupField();');
            $browser->pause(200);
            $this->type($browser, '#group-items > div:last-child input[name$="[name]"]', 'Workshops');

            $browser->script('toggleSubdomainEdit();');
            $this->type($browser, '#new_subdomain', 'takenname');
            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitUntil('document.readyState === "complete" && window.FormKit !== undefined && window.FormKit.isArmed() && document.querySelector("ul.text-red-600, ul.text-red-400") !== null', 30)->pause(100);

            // The row typed before the refusal is back, and the next one gets a key of its own.
            $browser->script('addGroupField();');
            $browser->pause(200);
            $keys = $browser->script('return Array.prototype.map.call(document.querySelectorAll("#group-items input[name^=\"groups[new_\"][name$=\"[name]\"]"), function (i) { return i.name; });')[0];
            $this->assertCount(2, $keys);
            $this->assertCount(2, array_unique($keys), 'two new rows, two keys');
            $this->type($browser, '#group-items > div:last-child input[name$="[name]"]', 'Matinees');
            $this->type($browser, '#new_subdomain', 'journeyquartet');

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertEqualsCanonicalizing(['Main stage', 'Late shows', 'Workshops', 'Matinees'], $this->talent->groups()->pluck('name')->all());
    }

    /** A colour is picked with a click on a swatch, which changed a hidden field and told nobody. */
    public function test_picking_a_colour_is_a_change(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-subschedules');
            $this->press($browser, 'button.customize-tab[data-tab="subschedules"]');
            $this->assertSame(__('messages.no_unsaved_changes'), $this->barText($browser));

            $this->assertSame(__('messages.color'), $browser->attribute('#group-items > div:first-child .color-picker-root > button', 'aria-label'));
            $this->press($browser, '#group-items > div:first-child .color-picker-root > button');
            $browser->waitFor('#group-items > div:first-child .color-picker-root .grid button', 5)
                ->click('#group-items > div:first-child .color-picker-root .grid button')
                ->pause(300);

            $this->assertSame(__('messages.unsaved').': '.__('messages.customize'), $this->barText($browser));
        });
    }

    /** The final pass over each panel, where it takes a browser to see it. */
    public function test_lists_and_switches_say_what_they_do(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-subschedules');

            // A category taken off its list is named by the bar, as a sub-schedule is.
            $this->press($browser, 'button.customize-tab[data-tab="categories"]');
            $first = $browser->script('return document.querySelector("#event-categories-container .event-category-name").value;')[0];
            $browser->script('document.querySelector("#event-categories-container [data-action=\"remove-event-category\"]").click();');
            $browser->pause(300);
            $this->assertSame(__('messages.saving_removes').': '.$first, $this->barText($browser));

            // A custom field's rarely used parts are behind "More options".
            $this->press($browser, 'button.customize-tab[data-tab="custom-fields"]');
            $browser->script('document.querySelector("[data-action=\"add-custom-field\"]").click();');
            $browser->pause(200);
            $this->assertFalse((bool) $browser->script('return document.querySelector(".event-custom-field-item .event-field-regex-container").offsetParent !== null;')[0]);
            $browser->script('document.querySelector(".event-custom-field-item [data-action=\"toggle-field-more\"]").click();');
            $browser->pause(200);
            $this->assertTrue((bool) $browser->script('return document.querySelector(".event-custom-field-item .event-field-regex-container").offsetParent !== null;')[0]);
            $browser->script('document.querySelector(".event-custom-field-item [data-action=\"remove-custom-field\"]").click();');

            // Gift cards: the settings appear with the switch.
            $this->showTab($browser, 'section-gift-cards');
            $this->assertFalse((bool) $browser->script('return document.getElementById("gift-card-details").offsetParent !== null;')[0]);
            $browser->script('var s = document.querySelector("input[type=\"checkbox\"][name=\"gift_cards_enabled\"]"); s.checked = true; s.dispatchEvent(new Event("change", { bubbles: true }));');
            $browser->pause(200);
            $this->assertTrue((bool) $browser->script('return document.getElementById("gift-card-details").offsetParent !== null;')[0]);

            // Sponsors: an empty line, the form behind its link, and a reason when Add cannot add.
            $this->showTab($browser, 'section-engagement');
            $this->press($browser, '.engagement-tab[data-tab="sponsors"]');
            $this->assertTrue((bool) $browser->script('return document.getElementById("sponsors-empty").offsetParent !== null && document.getElementById("sponsor-form-shell").hidden;')[0]);
            $this->press($browser, '#sponsor-form-open');
            $this->assertFalse((bool) $browser->script('return document.getElementById("sponsor-form-shell").hidden;')[0]);
            $browser->script('document.querySelector("[data-action=\"add-sponsor\"]").click();');
            $browser->pause(200);
            $this->assertSame(__('messages.sponsor_needs_logo'), trim($browser->script('return document.getElementById("sponsor-form-note").textContent;')[0]));
            $browser->script('document.getElementById("sponsor-form-close").click();');
            $browser->pause(200);
            $this->assertTrue((bool) $browser->script('return document.getElementById("sponsor-form-shell").hidden && ! document.getElementById("sponsor-form-open").hidden;')[0]);

            // Style: the animation picker is a row that says its choice.
            $this->showTab($browser, 'section-style');
            $this->assertSame(__('messages.list_animation_none'), $this->summary($browser, 'style:animation'));
            $this->press($browser, '#style-tab-animation');
            $this->assertTrue($this->paneOpen($browser, 'style-content-animation'));
        });
    }

    /** A field that will not validate, in a row that is closed: Save used to do nothing and say nothing. */
    public function test_save_opens_the_row_that_holds_the_field_in_its_way(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $this->press($browser, '.details-tab[data-tab="contact"]');
            $this->type($browser, '#email', 'not-an-address');
            $this->press($browser, '.details-tab[data-tab="contact"]');
            $this->assertFalse($this->paneOpen($browser, 'details-tab-contact'));
            $this->showTab($browser, 'section-style');

            $browser->script('document.getElementById("edit-form").requestSubmit();');
            $browser->pause(500);

            $this->assertSame('section-details', $this->shownTab($browser), 'the tab is shown');
            $this->assertTrue($this->paneOpen($browser, 'details-tab-contact'), 'and the row is opened');
            $this->assertSame(__('messages.check_tabs').': '.__('messages.details'), $this->barText($browser));
            $this->assertStringContainsString('/journeyquartet/edit', $browser->driver->getCurrentURL(), 'nothing was sent');
        });

        $this->assertSame('journeyquartet@gmail.com', $this->talent->fresh()->email);
    }

    /** Something typed in the Add Link dialog and left there is not a field of the form. */
    public function test_a_leftover_in_the_add_link_dialog_does_not_stop_a_save(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $browser->script('document.getElementById("link").value = "not a link";');
            $this->type($browser, '#name', 'Journey Quintet');

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertSame('Journey Quintet', $this->talent->fresh()->name);
    }

    /** A refused save comes back on the tab its message is about, with the row open, and the input kept. */
    public function test_a_refused_save_comes_back_where_the_problem_is(): void
    {
        $this->makeRole($this->owner, 'talent', 'takenname');

        $this->browse(function (Browser $browser) {
            $this->open($browser);

            $this->type($browser, '#name', 'Typed before the refusal');
            $this->showTab($browser, 'section-settings');
            $browser->script('toggleSubdomainEdit();');
            $browser->pause(300);
            $this->type($browser, '#new_subdomain', 'takenname');

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitUntil('document.readyState === "complete" && window.FormKit !== undefined && document.querySelector("ul.text-red-600, ul.text-red-400") !== null', 30)->pause(500);

            $this->assertSame('section-settings', $this->shownTab($browser), 'not Details, which is where it used to land whatever the message was about');
            $this->assertTrue((bool) $browser->script('return document.querySelector("#subdomain-edit ul.text-red-600, #subdomain-edit ul.text-red-400").offsetParent !== null;')[0], 'the address editor is opened, so its message can be read');
            $this->assertSame('takenname', $browser->value('#new_subdomain'));
            $this->assertSame('Typed before the refusal', $browser->value('#name'), 'and what was typed on another tab is still there');
            $this->assertStringContainsString(__('messages.schedule_settings'), $this->barText($browser));
        });

        $this->assertSame('Journey Quartet', $this->talent->fresh()->name);
    }

    public function test_a_link_can_name_a_row_and_opens_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#engagement-tab-requests');
            $this->assertSame('section-engagement', $this->shownTab($browser));
            $this->assertTrue($this->paneOpen($browser, 'engagement-tab-requests'));

            $this->open($browser, '#section-settings', '&settings_tab=notifications');
            $this->assertSame('section-settings', $this->shownTab($browser));
            $this->assertTrue($this->paneOpen($browser, 'settings-tab-notifications'));

            // A link that names nothing on the page shows the first tab, not an empty page.
            $this->open($browser, '#no-such-thing');
            $this->assertSame('section-details', $this->shownTab($browser));
        });
    }

    public function test_the_style_rows_and_their_choices(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser, '#section-style');

            $this->assertTrue((bool) $browser->script('return document.getElementById("font_family").offsetParent !== null || document.querySelector("#style-content-branding").offsetParent !== null;')[0], 'branding is on the page');
            $this->press($browser, '#style-tab-background');
            $this->assertTrue($this->paneOpen($browser, 'style-content-background'));

            $browser->script('document.getElementById("background_type_solid").click();');
            $browser->pause(300);
            $this->assertStringStartsWith(__('messages.solid'), $this->summary($browser, 'style:background'));
            $this->assertSame(__('messages.unsaved').': '.__('messages.schedule_style'), $this->barText($browser));

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertSame('solid', $this->talent->fresh()->background);
    }

    private function giftSwitch(Browser $browser, bool $on): void
    {
        $browser->script('var box = document.querySelector(\'input[type="checkbox"][name="gift_cards_enabled"]\'); box.checked = '.($on ? 'true' : 'false').'; box.dispatchEvent(new Event("change", { bubbles: true }));');
        $browser->pause(200);
    }

    /**
     * The gift card settings fold away while gift cards are off, and their fields are still fields
     * of the form. One left with a value the browser will not take refused every save from where
     * nobody could see it: the bar said "Check: Gift Cards" and the tab showed nothing.
     */
    public function test_save_unfolds_the_block_that_holds_the_field_in_its_way(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            $this->showTab($browser, 'section-gift-cards');

            $this->giftSwitch($browser, true);
            $this->type($browser, '#gift_card_valid_days', '99999');
            $this->giftSwitch($browser, false);
            $this->assertTrue((bool) $browser->script('return document.getElementById("gift-card-details").hidden;')[0], 'sanity check: off folds the settings away');
            $this->showTab($browser, 'section-style');

            $browser->script('document.getElementById("edit-form").requestSubmit();');
            $browser->pause(600);

            $this->assertSame('section-gift-cards', $this->shownTab($browser), 'the tab is shown');
            $this->assertTrue((bool) $browser->script('var f = document.getElementById("gift_card_valid_days"); return ! document.getElementById("gift-card-details").hidden && f.offsetParent !== null;')[0], 'and the field is on the page to be fixed');
            $this->assertSame(__('messages.check_tabs').': '.__('messages.gift_cards'), $this->barText($browser));
            $this->assertStringContainsString('/journeyquartet/edit', $browser->driver->getCurrentURL(), 'nothing was sent');
        });
    }

    /** Cancel on the form a sponsor is added with means what was typed is not wanted. */
    public function test_cancel_empties_the_sponsor_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            $this->showTab($browser, 'section-engagement');
            $browser->script('window.FormKit.openFromHash("#sponsor-form-open");');
            $browser->pause(300);

            $this->press($browser, '#sponsor-form-open');
            $this->type($browser, '#new_sponsor_url_input', 'acme.com');
            $this->press($browser, '#sponsor-form-close');
            $this->assertTrue((bool) $browser->script('return document.getElementById("sponsor-form-shell").hidden;')[0]);
            $this->assertSame('', $browser->script('return document.getElementById("new_sponsor_url_input").value;')[0], 'Cancel empties what was typed');
        });
    }

    /**
     * The box a sponsor's link is typed in before the sponsor is added has no name: it is never
     * sent. Text left in it stopped every save all the same, from a form that had been closed.
     */
    public function test_a_leftover_in_the_closed_sponsor_form_does_not_stop_a_save(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);

            // Left there without Cancel: the form also closes whenever the list changes.
            $browser->script('document.getElementById("new_sponsor_url_input").value = "acme.com";');
            $this->type($browser, '#name', 'Journey Quintet');

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/journeyquartet/schedule', 30);
        });

        $this->assertSame('Journey Quintet', $this->talent->fresh()->name);
    }

    /**
     * A message the server left inside a folded block is shown when the page comes back, and not
     * only when it is the first one: here the name is refused too, on a tab that comes first.
     */
    public function test_a_refusal_inside_a_folded_block_is_shown_when_the_page_comes_back(): void
    {
        $this->browse(function (Browser $browser) {
            $this->open($browser);
            // Longer than the server stores, set past the field's own limit: only the server objects.
            $this->type($browser, '#name', str_repeat('x', 300));
            $this->showTab($browser, 'section-gift-cards');

            $this->giftSwitch($browser, true);
            $this->press($browser, '#add-gift-card-amount');
            // More than the server takes, and nothing the browser objects to: the field has no max.
            $browser->script('var rows = document.querySelectorAll(\'#gift-card-amounts-items input[name="gift_card_amounts[]"]\'); var last = rows[rows.length - 1]; last.value = "100000"; last.dispatchEvent(new Event("input", { bubbles: true }));');
            $this->giftSwitch($browser, false);

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitUntil('document.readyState === "complete" && window.FormKit !== undefined && document.querySelector("ul.text-red-600, ul.text-red-400") !== null', 30)->pause(500);

            $this->assertSame('section-details', $this->shownTab($browser), 'the first tab with a message is the one shown');
            $this->assertStringContainsString(__('messages.gift_cards'), $this->barText($browser), 'and the bar names the other');
            $this->showTab($browser, 'section-gift-cards');
            $this->assertTrue((bool) $browser->script('var m = document.querySelector("#gift-card-details ul.text-red-600, #gift-card-details ul.text-red-400"); return m !== null && m.offsetParent !== null;')[0], 'its message can be read');
        });
    }
}
