<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The structured guest-submit page is a Vue mount whose data is seeded from Blade. A malformed
 * Blade json directive there does not error - it silently kills the whole app, leaving an unusable
 * page - so this renders the page and checks the seeded data and the questions card really arrive.
 */
class RequestCustomFieldsRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_guest_submit_page_seeds_the_questions_into_vue(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
            'event_custom_fields' => [
                'gear' => [
                    'name' => 'What gear do you need?',
                    'type' => 'multiselect',
                    'options' => 'projector,dj controller,tablesoccer',
                    'show_on_request' => true,
                ],
                'ref' => [
                    'name' => 'Booking reference',
                    'type' => 'string',
                    'regex' => '[A-Z]{3}-[0-9]{4}',
                    'regex_hint' => 'Three letters, a dash, four digits',
                    'show_on_request' => true,
                ],
                'internal' => ['name' => 'Internal note', 'type' => 'string', 'show_on_request' => false],
            ],
        ]);

        $response = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]));
        $response->assertOk();

        $html = $response->getContent();

        // The Vue data payload, not server-rendered markup - owner text must reach the page as data.
        $this->assertStringContainsString('requestCustomFields:', $html);
        $this->assertStringContainsString('customFieldValues:', $html);
        $this->assertStringContainsString('What gear do you need?', $html);
        $this->assertStringContainsString('tablesoccer', $html);
        // The pattern must survive JSON encoding intact.
        $this->assertStringContainsString('[A-Z]{3}-[0-9]{4}', $html);
        // Opted-out fields are not sent to the browser at all.
        $this->assertStringNotContainsString('Internal note', $html);

        // The card and the payload key that carries the answers back.
        $this->assertStringContainsString('v-for="field in requestCustomFields"', $html);
        $this->assertStringContainsString('custom_field_values: this.customFieldValues', $html);
    }

    /**
     * The admin event form's custom-fields panel was replaced by <x-custom-field-input>. The rest of
     * the suite only renders that page for schedules with no custom fields, so without this the
     * swapped-in component is never exercised in a real page render.
     */
    public function test_the_admin_event_form_renders_the_shared_component(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'event_custom_fields' => [
                'gear' => [
                    'name' => 'What gear do you need?',
                    'type' => 'multiselect',
                    'options' => 'projector,dj controller,tablesoccer',
                    'show_on_request' => true,
                ],
                'ref' => [
                    'name' => 'Booking reference',
                    'type' => 'string',
                    'regex' => '[A-Z]{3}-[0-9]{4}',
                    'regex_hint' => 'Three letters, a dash, four digits',
                    // Kept off the request form - the admin form still shows every field.
                    'show_on_request' => false,
                ],
            ],
        ]);

        $event = $this->createEvent($role, [
            'name' => 'Editable Event',
            'custom_field_values' => ['gear' => 'projector, tablesoccer', 'ref' => 'ABC-1234'],
        ]);

        $response = $this->actingAs($owner)->get(route('event.edit', [
            'subdomain' => $role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($event->id),
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('What gear do you need?', $html);
        $this->assertStringContainsString('Booking reference', $html);
        $this->assertStringContainsString('name="custom_field_values[gear][]"', $html);
        $this->assertStringContainsString('pattern="[A-Z]{3}-[0-9]{4}"', $html);
        $this->assertStringContainsString('Three letters, a dash, four digits', $html);
        // Saved values repopulate: the string field's value, and exactly the two stored options
        // ticked out of the three offered.
        $this->assertStringContainsString('value="ABC-1234"', $html);
        preg_match_all(
            '/name="custom_field_values\[gear\]\[\]"\s+value="([^"]+)"\s*(checked)?/',
            $html,
            $matches,
            PREG_SET_ORDER
        );
        $this->assertCount(3, $matches);
        $ticked = array_values(array_map(
            fn ($m) => $m[1],
            array_filter($matches, fn ($m) => ! empty($m[2]))
        ));
        $this->assertSame(['projector', 'tablesoccer'], $ticked);
        // The panel sits inside the page's Vue mount, so it must opt out of template compilation.
        $this->assertStringContainsString('v-pre', $html);
    }

    /**
     * The schedule editor renders a field row twice - a Blade loop for saved fields and a JS
     * template literal for newly-added ones. They drifted once already, dropping the whole
     * "show on request form" control from new rows.
     */
    public function test_the_editor_js_row_template_offers_every_control_the_blade_row_does(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'event_custom_fields' => [
                'gear' => ['name' => 'Gear', 'type' => 'string', 'index' => 1],
            ],
        ]);

        $html = $this->actingAs($owner)
            ->get(route('role.edit', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        // Split the page at the JS builder so each half can be checked on its own.
        $jsStart = strpos($html, 'const fieldHtml = `');
        $this->assertNotFalse($jsStart, 'addEventCustomField() template not found');
        $bladeHalf = substr($html, 0, $jsStart);
        $jsHalf = substr($html, $jsStart);

        foreach (['name', 'name_en', 'type', 'options', 'regex', 'regex_hint', 'ai_prompt', 'required', 'private', 'show_on_request', 'index'] as $property) {
            $this->assertStringContainsString(
                "[{$property}]",
                $jsHalf,
                "The JS row template is missing the [{$property}] input"
            );
        }

        // show_on_request defaults to true, so a new row needs the paired hidden 0 (to distinguish
        // "unchecked" from "absent") and must start checked, mirroring ($field[...] ?? true).
        $this->assertStringContainsString('name="event_custom_fields[${fieldKey}][show_on_request]" value="0"', $jsHalf);
        $this->assertMatchesRegularExpression(
            '/\[show_on_request\]"\s+id="event_field_show_on_request_\$\{fieldKey\}"\s+value="1"\s+checked/',
            $jsHalf
        );
        $this->assertStringContainsString('show_on_request', $bladeHalf);
    }

    public function test_a_schedule_with_no_request_fields_seeds_an_empty_list(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
        ]);

        $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('requestCustomFields: []', false);
    }
}
