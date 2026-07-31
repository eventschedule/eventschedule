<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * ?layout= lets an embedding site pick the layout per frame, so the same schedule can be
 * embedded twice on one page as a calendar and as a list. It overrides roles.event_layout
 * and is honored on the public schedule page as well as on ?embed=true.
 *
 * Most assertions target `currentView: '<layout>'`, the Vue data key at
 * role/partials/calendar.blade.php that is the single authoritative init for the layout. The
 * create-form default is covered here too, since it feeds the same column.
 */
class EmbedLayoutParamTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function assertRendersLayout(string $url, string $layout): void
    {
        $this->get($url)
            ->assertOk()
            ->assertSee("currentView: '".$layout."'", false);
    }

    public function test_new_schedule_form_defaults_to_the_list_layout(): void
    {
        // roles.event_layout defaults to 'list', but the create form checks its radios against
        // Role::eventLayout(), whose fallback for an unsaved role is 'calendar'. Once the radios
        // were switched to that accessor the form arrived with Calendar pre-checked and store()'s
        // fill() persisted it, so new schedules silently stopped honouring the column default.
        // Nothing asserted the default, which is why it went unnoticed.
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->get(route('new', ['type' => 'venue']));
        $response->assertOk();

        $this->assertSame('list', $response->viewData('role')->eventLayout());
    }

    public function test_embed_without_the_param_uses_the_stored_layout(): void
    {
        $owner = $this->createOwner();
        $calendarRole = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $listRole = $this->createRole($owner, 'venue', ['event_layout' => 'list']);
        $this->createEvent($calendarRole);
        $this->createEvent($listRole);

        $this->assertRendersLayout('/'.$calendarRole->subdomain.'?embed=true', 'calendar');
        $this->assertRendersLayout('/'.$listRole->subdomain.'?embed=true', 'list');
    }

    public function test_embed_layout_param_overrides_the_stored_layout_both_ways(): void
    {
        $owner = $this->createOwner();
        $calendarRole = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $listRole = $this->createRole($owner, 'venue', ['event_layout' => 'list']);
        $this->createEvent($calendarRole);
        $this->createEvent($listRole);

        $this->assertRendersLayout('/'.$calendarRole->subdomain.'?embed=true&layout=list', 'list');
        $this->assertRendersLayout('/'.$listRole->subdomain.'?embed=true&layout=calendar', 'calendar');
    }

    public function test_grid_and_month_are_aliases_for_the_month_calendar(): void
    {
        // The customer-facing name for the calendar is "grid", and roles.event_layout still
        // carries a dead 'grid' enum value, so both spellings resolve rather than falling back.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'list']);
        $this->createEvent($role);

        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true&layout=grid', 'calendar');
        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true&layout=MONTH', 'calendar');
    }

    public function test_the_param_is_honored_on_the_public_page_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $this->createEvent($role);

        $this->assertRendersLayout('/'.$role->subdomain.'?layout=list', 'list');
    }

    public function test_the_param_is_honored_on_a_sub_schedule_url(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $group = $this->createGroup($role, ['slug' => 'jazz-nights']);
        // The sub-schedule lives on the event_role pivot, not on events.
        $event = $this->createEvent($role);
        $event->roles()->updateExistingPivot($role->id, ['group_id' => $group->id]);

        $this->assertRendersLayout('/'.$role->subdomain.'/jazz-nights?layout=list', 'list');
    }

    public function test_an_unusable_value_falls_back_to_the_stored_layout(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $this->createEvent($role);

        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true&layout=bogus', 'calendar');
        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true&layout=', 'calendar');
        // An array-valued param must not blow up strtolower().
        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true&layout[]=list', 'calendar');
    }

    public function test_a_legacy_grid_row_renders_as_the_month_calendar(): void
    {
        // 'grid' survives in the column's enum but was never offered in the UI and no view
        // branches on it, so Role::eventLayout() has to normalise it or nothing renders.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->forceFill(['event_layout' => 'grid'])->saveQuietly();
        $this->createEvent($role);

        $this->assertRendersLayout('/'.$role->subdomain.'?embed=true', 'calendar');
    }

    public function test_the_embed_tells_vue_to_ignore_the_visitors_saved_view(): void
    {
        // The localStorage restore is client-side, so pin the two data keys that gate it:
        // an embed never honors a saved preference, and neither does a ?layout= URL.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $this->createEvent($role);

        $this->get('/'.$role->subdomain.'?embed=true')
            ->assertOk()
            ->assertSee('embed: true', false)
            ->assertSee('layoutFromUrl: null', false);

        $this->get('/'.$role->subdomain.'?layout=list')
            ->assertOk()
            ->assertSee('embed: false', false)
            ->assertSee('layoutFromUrl: "list"', false);
    }

    public function test_the_public_page_keeps_its_saved_view_when_no_param_is_given(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['event_layout' => 'calendar']);
        $this->createEvent($role);

        // The pre-paint script that reads localStorage is emitted only without ?layout=.
        $this->get('/'.$role->subdomain)
            ->assertOk()
            ->assertSee("localStorage.getItem('es_view_".$role->subdomain."')", false);

        $this->get('/'.$role->subdomain.'?layout=list')
            ->assertOk()
            ->assertDontSee("localStorage.getItem('es_view_".$role->subdomain."')", false);
    }
}
