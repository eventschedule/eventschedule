<?php

namespace Tests\Feature\Characterization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the custom-fields sections of EventRepo::saveEvent()
 * (REFACTOR_PLAN.md P11, P12 seams 3-4). Both sections communicate via
 * $request->merge() - later code reads the merged values, which the
 * decomposition must preserve (the merge is load-bearing).
 */
class EventSaveCustomFieldsCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_event_custom_fields_json_is_decoded_and_options_normalized(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // The form submits custom_fields as a JSON string; options get their
        // per-item whitespace trimmed but stay one comma-joined string.
        $this->postCreateEvent($owner, $role, [
            'custom_fields' => json_encode([
                'field_1' => [
                    'name' => 'Meal choice',
                    'type' => 'dropdown',
                    'options' => 'Veggie ,  Meat,Fish',
                    'index' => 1,
                ],
            ]),
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertSame([
            'field_1' => [
                'name' => 'Meal choice',
                'type' => 'dropdown',
                'options' => 'Veggie,Meat,Fish',
                'index' => 1,
            ],
        ], $event->custom_fields);
    }

    public function test_duplicate_custom_field_indices_are_reassigned(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'custom_fields' => json_encode([
                'field_1' => ['name' => 'A', 'index' => 1],
                'field_2' => ['name' => 'B', 'index' => 1], // duplicate
            ]),
        ])->assertRedirect();

        $fields = $this->latestEvent()->custom_fields;
        $this->assertSame(1, $fields['field_1']['index']);
        // The duplicate gets the first free slot.
        $this->assertSame(2, $fields['field_2']['index']);
    }

    public function test_invalid_custom_field_values_fail_form_validation_over_http(): void
    {
        // First net: EventCreateRequest builds Rule::in validation from the
        // schedule's field config, so the interactive form path rejects
        // invalid options before saveEvent's own whitelist ever runs.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'event_custom_fields' => [
                'size' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S, M, L'],
                'tags' => ['name' => 'Tags', 'type' => 'multiselect', 'options' => 'Rock, Jazz, Pop'],
            ],
        ]);

        $response = $this->postCreateEvent($owner, $role, [
            'custom_field_values' => [
                'size' => 'XL',
                'tags' => ['Rock', 'Metal'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['custom_field_values.size', 'custom_field_values.tags.1']);
        $this->assertSame(0, \App\Models\Event::count());
    }

    public function test_repo_whitelist_drops_invalid_values_for_synthesized_requests(): void
    {
        // Second net: programmatic callers (WhatsApp/Eventbrite/console import)
        // bypass the FormRequest, so saveEvent's own whitelist does the work.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'event_custom_fields' => [
                'size' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S, M, L'],
                'tags' => ['name' => 'Tags', 'type' => 'multiselect', 'options' => 'Rock, Jazz, Pop'],
            ],
        ]);

        $request = new \Illuminate\Http\Request($this->eventPayload([
            'custom_field_values' => [
                'size' => 'XL',                 // not in S/M/L -> dropped
                'tags' => ['Rock', 'Metal'],    // Metal filtered out
                'note' => '',                   // empty -> dropped
            ],
        ]));
        $request->setUserResolver(fn () => $owner);

        app(\App\Repos\EventRepo::class)->saveEvent($role, $request);

        // Whitelisted multiselect values collapse to a comma-joined string;
        // the invalid dropdown value and the empty are gone.
        $this->assertSame(
            ['tags' => 'Rock'],
            $this->latestEvent()->custom_field_values
        );
    }

    public function test_valid_custom_field_values_are_persisted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'event_custom_fields' => [
                'size' => ['name' => 'Size', 'type' => 'dropdown', 'options' => 'S, M, L'],
            ],
        ]);

        $this->postCreateEvent($owner, $role, [
            'custom_field_values' => ['size' => 'M'],
        ])->assertRedirect();

        $this->assertSame(['size' => 'M'], $this->latestEvent()->custom_field_values);
    }
}
