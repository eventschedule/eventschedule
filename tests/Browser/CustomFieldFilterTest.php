<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The guest calendar's custom field filters and search, which run entirely in the browser
 * (resources/views/role/partials/calendar.blade.php) and so cannot be reached from PHPUnit.
 *
 * The schedule is written straight to the database: the journey under test starts at the guest
 * page, and building the fixture through the schedule and event forms would only add flake. The
 * list layout is forced (?layout=list) so every event is on screen whatever month the run lands
 * in - the calendar layout shows one month at a time.
 */
class CustomFieldFilterTest extends DuskTestCase
{
    use DatabaseTruncation;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $role = new Role;
        $role->subdomain = 'roomtest';
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->name = 'Room Test';
        $role->email = 'rooms@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->event_custom_fields = [
            'room' => ['name' => 'Room', 'type' => 'string', 'filter' => true, 'index' => 1],
            // No filter flag: an option list is a filter unless the owner turns it off.
            'tags' => ['name' => 'Tags', 'type' => 'multiselect', 'options' => 'Jazz,Blues,Rock', 'index' => 2],
        ];
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);
        $this->role = $role->fresh();

        // "Room A" and "room a " are the same room typed twice, and must be one option.
        $this->makeEvent('Keynote', ['room' => 'Room A', 'tags' => 'Jazz'], 3);
        $this->makeEvent('Workshop Alpha', ['room' => 'room a ', 'tags' => 'Rock'], 4);
        $this->makeEvent('Panel Talk', ['room' => 'Room B', 'tags' => 'Jazz, Blues'], 5);
        $this->makeEvent('Café Social', ['room' => 'Room B', 'tags' => 'Rock'], 6);
    }

    private function makeEvent(string $name, array $values, int $daysAhead): void
    {
        $event = new Event;
        $event->user_id = $this->role->user_id;
        $event->creator_role_id = $this->role->id;
        $event->name = $name;
        $event->slug = Str::slug($name).'-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::now()->addDays($daysAhead)->setTime(18, 0)->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->custom_field_values = $values;
        $event->save();

        $event->roles()->attach($this->role->id, ['is_accepted' => true]);
    }

    private function openFilters(Browser $browser): void
    {
        $browser->script('window.calendarVueApp.showDesktopFiltersModal = true;');
        $browser->waitFor('#filter-search-d', 5);
    }

    private function closeFilters(Browser $browser): void
    {
        $browser->script('window.calendarVueApp.showDesktopFiltersModal = false;');
        $browser->waitUntilMissing('#filter-search-d', 5);
    }

    public function test_text_and_multiselect_fields_filter_the_guest_calendar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000)
                ->visit('/roomtest?layout=list')
                ->waitForText('Keynote', 15)
                ->assertSee('Panel Talk');

            $this->openFilters($browser);

            $rooms = $browser->script('return window.calendarVueApp.availableCustomFieldOptions.room.map(o => o.label);')[0];
            $this->assertSame(['Room A', 'Room B'], $rooms, 'Two spellings of one room must fold into a single option');

            $tags = $browser->script('return window.calendarVueApp.availableCustomFieldOptions.tags.map(o => o.label);')[0];
            $this->assertSame(['Jazz', 'Blues', 'Rock'], $tags, 'A multiselect offers each option, in the order the owner defined');

            // Picking a room filters the list, and the address bar carries it.
            $browser->select('#filter-cf-d-room', 'room a');
            $browser->waitUntil("window.location.search.indexOf('custom_1=Room+A') !== -1", 5);
            $this->closeFilters($browser);

            $browser->waitUntilMissingText('Panel Talk', 5)
                ->assertSee('Keynote')
                ->assertSee('Workshop Alpha')
                ->assertDontSee('Café Social')
                ->assertSeeIn('#active-filter-chips', 'Room: Room A');

            // The chip's remove button drops that filter and its URL param.
            $browser->click('#active-filter-chips button[aria-label^="Remove"]')
                ->waitForText('Panel Talk', 5)
                ->assertSee('Café Social');
            $browser->waitUntil("window.location.search.indexOf('custom_1') === -1", 5);

            // "Jazz, Blues" is two options, so Blues finds the event that has both.
            $this->openFilters($browser);
            $browser->select('#filter-cf-d-tags', 'blues');
            $this->closeFilters($browser);
            $browser->waitUntilMissingText('Keynote', 5)
                ->assertSee('Panel Talk')
                ->assertDontSee('Café Social');
            $browser->script('window.calendarVueApp.clearFilters();');
            $browser->waitForText('Keynote', 5);
        });
    }

    public function test_search_matches_without_accents_and_shows_an_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000)
                ->visit('/roomtest?layout=list')
                ->waitForText('Keynote', 15);

            $this->openFilters($browser);
            $browser->type('#filter-search-d', 'cafe');
            $this->closeFilters($browser);

            $browser->waitUntilMissingText('Keynote', 5)
                ->assertSee('Café Social')
                ->assertSeeIn('#active-filter-chips', '"cafe"');

            // A custom field value is searchable too.
            $this->openFilters($browser);
            $browser->clear('#filter-search-d')->type('#filter-search-d', 'room b');
            $this->closeFilters($browser);
            $browser->waitForText('Panel Talk', 5)
                ->assertSee('Café Social')
                ->assertDontSee('Keynote');

            // Nothing matches: the page says so and offers the way out, rather than going blank.
            $this->openFilters($browser);
            $browser->clear('#filter-search-d')->type('#filter-search-d', 'zzzz-nothing');
            $this->closeFilters($browser);
            $browser->waitForText('No events found', 5)
                ->press('Clear Filters')
                ->waitForText('Keynote', 5)
                ->assertSee('Panel Talk');
        });
    }

    /**
     * The "Show as filter" checkbox in the schedule editor. Its state is set by plain JS
     * (updateEventFieldFilterState() in role/edit.blade.php), so PHPUnit sees only the markup.
     * Driven through script() and change events rather than clicks: the fields sit in a hidden
     * Customize tab, and what is pinned here is the logic, not the tab navigation.
     */
    public function test_the_editor_checkbox_follows_the_type_until_the_owner_chooses(): void
    {
        $fields = $this->role->event_custom_fields;
        $fields['saved'] = ['name' => 'Saved', 'type' => 'dropdown', 'options' => 'A,B', 'filter' => false, 'index' => 3];
        $fields['legacy'] = ['name' => 'Legacy', 'type' => 'dropdown', 'options' => 'A,B', 'index' => 4];
        $this->role->event_custom_fields = $fields;
        $this->role->save();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::findOrFail($this->role->user_id))
                ->visit('/roomtest/edit')
                // Present but not visible (a hidden Customize tab), so not waitFor().
                ->waitUntil("document.getElementById('event-custom-fields-container') !== null && typeof updateEventFieldFilterState === 'function'", 15);

            $browser->script(<<<'JS'
                window.__cf = function (key) {
                    var item = document.querySelector('.event-custom-field-item[data-field-key="' + key + '"]');
                    var container = item.querySelector('.event-field-filter-container');
                    var help = item.querySelector('.event-field-filter-help');
                    return {
                        shown: container.style.display !== 'none',
                        checked: item.querySelector('.event-field-filter-input').checked,
                        dimmed: container.classList.contains('opacity-50'),
                        help: help.style.display === 'none' ? '' : help.textContent,
                    };
                };
                window.__change = function (key, selector, mutate) {
                    var el = document.querySelector('.event-custom-field-item[data-field-key="' + key + '"] ' + selector);
                    mutate(el);
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                };
                window.__type = function (key, type) {
                    __change(key, 'select[data-action="toggle-field-options"]', function (el) { el.value = type; });
                };
                window.__toggle = function (key, selector) {
                    __change(key, selector, function (el) { el.checked = !el.checked; });
                };
            JS);
            $state = fn (string $key) => $browser->script("return __cf('{$key}');")[0];

            // A field saved before the flag existed follows its type until someone clicks it.
            $this->assertTrue($state('legacy')['checked']);
            $browser->script("__type('legacy', 'string');");
            $this->assertFalse($state('legacy')['checked'], 'untouched: text is not a filter by default');

            // A saved choice is never overridden by a type change.
            $browser->script("__type('saved', 'multiselect');");
            $this->assertFalse($state('saved')['checked'], 'saved as off, stays off');

            // A new field starts as text: offered, off, no advice yet.
            $browser->script('addEventCustomField();');
            $key = $browser->script("var items = document.querySelectorAll('.event-custom-field-item'); return items[items.length - 1].dataset.fieldKey;")[0];
            $this->assertEquals(['shown' => true, 'checked' => false, 'dimmed' => false, 'help' => ''], $state($key));

            $browser->script("__type('{$key}', 'dropdown');");
            $this->assertTrue($state($key)['checked'], 'a new dropdown is a filter, as it always was');

            $browser->script("__type('{$key}', 'date');");
            $this->assertFalse($state($key)['shown'], 'a date cannot filter');

            // The owner opts a text field in: the advice about consistent spelling appears.
            $browser->script("__type('{$key}', 'string');");
            $browser->script("__toggle('{$key}', '.event-field-filter-input');");
            $this->assertTrue($state($key)['checked']);
            $this->assertStringContainsString('Room A', $state($key)['help']);

            // Private: dimmed with the note, but still posts its value (not disabled).
            $browser->script("__toggle('{$key}', '[data-action=custom-field-private-toggle]');");
            $private = $state($key);
            $this->assertTrue($private['dimmed']);
            $this->assertSame('Private fields are never shown as filters.', $private['help']);
            $this->assertFalse($browser->script("return document.querySelector('.event-custom-field-item[data-field-key={$key}] .event-field-filter-input').disabled;")[0]);
        });
    }

    /**
     * Two sub-schedules and two categories over the fixture events: Kids holds only Café Social,
     * and category 1 is on Keynote and Panel Talk - none of them in Kids.
     */
    private function addSubSchedulesAndCategories(): void
    {
        $kids = $this->role->groups()->create(['name' => 'Kids', 'slug' => 'kids']);

        foreach (Event::all() as $event) {
            $event->category_id = in_array($event->name, ['Keynote', 'Panel Talk'], true) ? 1 : 2;
            $event->save();
            if ($event->name === 'Café Social') {
                $event->roles()->updateExistingPivot($this->role->id, ['group_id' => $kids->id]);
            }
        }
    }

    public function test_a_sub_schedule_page_is_not_shown_as_filtered(): void
    {
        $this->addSubSchedulesAndCategories();

        $this->browse(function (Browser $browser) {
            // The sub-schedule is the page itself: no chips row, and nothing claims a filter.
            $browser->resize(1440, 1000)
                ->visit('/roomtest/kids?layout=list')
                ->waitForText('Café Social', 15)
                ->assertDontSee('Keynote')
                ->assertMissing('#active-filter-chips');
        });
    }

    public function test_a_backslash_in_the_category_link_does_not_break_the_calendar(): void
    {
        $this->browse(function (Browser $browser) {
            // Echoed inside quotes, "\" closed the JS string early and nothing rendered at all.
            // Not waitForText(): the server-rendered markup already holds every empty-state's text,
            // so it would pass with the script dead. The app has to exist and have read the value.
            $browser->resize(1440, 1000)
                ->visit('/roomtest?layout=list&category=%5C')
                ->waitUntil('window.calendarVueApp && window.calendarVueApp.isLoadingEvents === false', 15);
            $this->assertSame('\\', $browser->script('return window.calendarVueApp.selectedCategory;')[0]);
        });
    }

    public function test_back_restores_the_sub_schedule_with_the_category(): void
    {
        $this->addSubSchedulesAndCategories();

        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000)
                ->visit('/roomtest?layout=list')
                ->waitForText('Keynote', 15);

            $browser->script("window.calendarVueApp.selectedCategory = '1';");
            $browser->waitUntil("window.location.search.indexOf('category=1') !== -1", 5)
                ->waitUntilMissingText('Café Social', 5);

            // Kids has none of category 1, so picking it drops the category and pushes /kids.
            $browser->script("window.calendarVueApp.selectedGroup = 'kids';");
            $browser->waitForText('Café Social', 5)->assertDontSee('Keynote');

            // Back to /roomtest?category=1: restoring only the category would pair it with Kids
            // and show nothing. The sub-schedule comes back with it.
            $browser->back()
                ->waitForText('Keynote', 5)
                ->assertSee('Panel Talk')
                ->assertDontSee('Café Social')
                ->assertDontSee('No events found');
            $this->assertSame('', $browser->script('return window.calendarVueApp.selectedGroup;')[0]);
        });
    }

    public function test_a_shared_link_opens_already_filtered(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 1000)
                ->visit('/roomtest?layout=list&custom_1=room+b')
                ->waitForText('Panel Talk', 15)
                ->assertSee('Café Social')
                ->assertDontSee('Keynote')
                ->assertSeeIn('#active-filter-chips', 'Room: Room B');

            // An option list only accepts its own options, compared without case.
            $browser->visit('/roomtest?layout=list&custom_2=BLUES')
                ->waitForText('Panel Talk', 15)
                ->assertDontSee('Keynote')
                ->assertDontSee('Café Social');

            $browser->visit('/roomtest?layout=list&custom_2=Polka')
                ->waitForText('Keynote', 15)
                ->assertSee('Panel Talk');
        });
    }
}
