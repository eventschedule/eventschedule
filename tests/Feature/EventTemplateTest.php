<?php

namespace Tests\Feature;

use App\Models\EventTemplate;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class EventTemplateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function storeUrl($role, $event): string
    {
        return route('event_template.store', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    private function makeTemplate($role, $owner, array $overrides = []): EventTemplate
    {
        $payload = array_merge([
            'event' => ['name' => 'Weekly Meetup'],
            'tickets' => [[]],
            'addons' => [],
            'venue_id' => null,
            'selected_members' => [],
            'curators' => [],
            'curator_groups' => [],
            'parts' => [],
            'flyer_image_filename' => null,
        ], $overrides);

        return EventTemplate::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'My Template',
            'template_data' => $payload,
        ]);
    }

    public function test_save_as_template_persists_serialized_payload(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'name' => 'Trivia Night',
            'event_password' => 'secret',
            'rsvp_sold' => 5,
        ]);
        $this->createTicket($event, ['type' => 'General', 'price' => 10]);

        $response = $this->actingAs($owner)->post($this->storeUrl($role, $event), ['name' => 'Trivia Template']);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_templates', [
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'Trivia Template',
        ]);

        $data = EventTemplate::where('role_id', $role->id)->first()->template_data;
        $this->assertNull($data['event']['starts_at'], 'starts_at should be cleared');
        $this->assertNull($data['event']['event_password'], 'event_password should be stripped');
        $this->assertSame(0, $data['event']['rsvp_sold'], 'rsvp_sold should be reset');
        $this->assertNull($data['flyer_image_filename'], 'flyer should not be carried');
        $this->assertCount(1, $data['tickets']);
        $this->assertSame('General', $data['tickets'][0]['type']);
    }

    public function test_recurrence_pattern_preserved_and_exceptions_cleared(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, [
            'recurring_frequency' => 'weekly',
            'recurring_include_dates' => ['2026-07-04'],
            'recurring_exclude_dates' => ['2026-07-11'],
        ]);
        // days_of_week is not fillable; set it directly (Tue/Thu).
        $event->days_of_week = '0010100';
        $event->save();

        $this->actingAs($owner)->post($this->storeUrl($role, $event), ['name' => 'Recurring Template'])->assertRedirect();

        $data = EventTemplate::where('role_id', $role->id)->first()->template_data;
        $this->assertSame('0010100', $data['event']['days_of_week'], 'recurrence day pattern must survive (clone bug fix)');
        $this->assertSame('weekly', $data['event']['recurring_frequency']);
        $this->assertNull($data['event']['recurring_include_dates'], 'date exceptions cleared for template');
        $this->assertNull($data['event']['recurring_exclude_dates'], 'date exceptions cleared for template');
        $this->assertNull($data['event']['starts_at']);
    }

    public function test_apply_loads_session_and_redirects_to_create(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($role, $owner);

        $response = $this->actingAs($owner)->get(route('event_template.apply', [
            'subdomain' => $role->subdomain, 'hash' => $template->encodeId(),
        ]));

        $response->assertRedirect(route('event.create', ['subdomain' => $role->subdomain]));
        $response->assertSessionHas('cloned_event');
        $this->assertSame('Weekly Meetup', session('cloned_event')['event']['name']);
    }

    public function test_apply_forwards_date_to_create(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($role, $owner);

        $response = $this->actingAs($owner)->get(route('event_template.apply', [
            'subdomain' => $role->subdomain, 'hash' => $template->encodeId(),
        ]).'?date=2026-07-15');

        $response->assertRedirect(route('event.create', ['subdomain' => $role->subdomain, 'date' => '2026-07-15']));
    }

    public function test_rename_template(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($role, $owner);

        $this->actingAs($owner)->put(route('event_template.update', [
            'subdomain' => $role->subdomain, 'hash' => $template->encodeId(),
        ]), ['name' => 'Renamed Template'])->assertRedirect();

        $this->assertSame('Renamed Template', $template->fresh()->name);
    }

    public function test_delete_template(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($role, $owner);

        $this->actingAs($owner)->delete(route('event_template.destroy', [
            'subdomain' => $role->subdomain, 'hash' => $template->encodeId(),
        ]))->assertRedirect();

        $this->assertDatabaseMissing('event_templates', ['id' => $template->id]);
    }

    public function test_template_is_scoped_to_its_schedule(): void
    {
        $owner = $this->createOwner();
        $roleA = $this->createRole($owner, 'venue');
        $roleB = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($roleA, $owner);

        // Applying role A's template under role B must be forbidden.
        $this->actingAs($owner)->get(route('event_template.apply', [
            'subdomain' => $roleB->subdomain, 'hash' => $template->encodeId(),
        ]))->assertForbidden();
    }

    public function test_non_editor_cannot_save_template(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);

        $stranger = $this->createOwner();

        $this->actingAs($stranger)->post($this->storeUrl($role, $event), ['name' => 'Nope']);

        $this->assertDatabaseCount('event_templates', 0);
    }

    public function test_pro_gating_blocks_free_schedule(): void
    {
        // Tests run non-hosted, where isPro() is always true; force hosted so the gate applies.
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
        $event = $this->createEvent($role);

        $this->assertFalse($role->fresh()->isPro(), 'sanity: role must be non-Pro for this test');

        $this->actingAs($owner)->post($this->storeUrl($role, $event), ['name' => 'Blocked']);

        $this->assertDatabaseCount('event_templates', 0);
    }

    public function test_deleting_schedule_cascades_to_templates(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $template = $this->makeTemplate($role, $owner);

        // DB-level cascade (FK), no model events.
        DB::table('roles')->where('id', $role->id)->delete();

        $this->assertDatabaseMissing('event_templates', ['id' => $template->id]);
    }
}
