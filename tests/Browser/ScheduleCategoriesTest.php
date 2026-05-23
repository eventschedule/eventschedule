<?php

namespace Tests\Browser;

use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\AccountSetupTrait;
use Tests\DuskTestCase;

class ScheduleCategoriesTest extends DuskTestCase
{
    use AccountSetupTrait;
    use DatabaseTruncation;

    /**
     * Regression: saving the schedule edit form on a fresh schedule with a
     * custom event category must succeed (was 500 — Sentry EVENTSCHEDULE-PHP-3H).
     */
    public function test_schedule_edit_saves_custom_event_category(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupTestAccount($browser, 'John Doe', 'test@gmail.com', 'password');
            $this->createTestTalent($browser);

            $browser->visit('/talent/edit')
                ->waitFor('#edit-form', 15)
                ->pause(1000);

            $browser->script("document.querySelector('a[data-section=\"section-subschedules\"]').click()");
            $browser->pause(500);
            $browser->script("document.querySelector('button.customize-tab[data-tab=\"categories\"]').click()");
            $browser->waitFor('#event-categories-container', 5);

            // Add-category button inserts a blank row at the bottom; type the name directly into it.
            $browser->script("
                document.getElementById('add-event-category-btn').click();
                var row = document.querySelector('#event-categories-container').lastElementChild;
                row.querySelector('.event-category-name').value = 'Workshop';
            ");
            $browser->pause(300);

            $browser->script("window._skipUnsavedWarning = true; document.getElementById('edit-form').requestSubmit()");
            $browser->waitForLocation('/talent/schedule', 15);

            $role = Role::where('subdomain', 'talent')->firstOrFail();
            $categories = $role->event_categories ?? [];
            $custom = collect($categories)->first(fn ($c) => ($c['name'] ?? null) === 'Workshop');

            $this->assertNotNull($custom, 'Expected a custom event_category named "Workshop" in the saved JSON');
            $this->assertGreaterThanOrEqual(100, (int) $custom['id']);
            $this->assertArrayNotHasKey('color', $custom,
                'Custom category saved without a color should omit the color key entirely');
        });
    }
}
