<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event's custom field answers over the API (issue #141, ApiCustomFieldValues): written by the
 * field's name, an update changing only the fields it names, and read back by name without the
 * private ones.
 */
class ApiCustomFieldValuesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
    }

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: Role, 1: array<string, string>} */
    private function venue(): array
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['event_custom_fields' => [
            'new_0' => ['name' => 'Room', 'type' => 'dropdown', 'options' => 'Garden,Hall', 'show_on_event' => true, 'required' => true, 'index' => 1],
            'new_1' => ['name' => 'Fee paid', 'type' => 'string', 'private' => true, 'index' => 2],
            'new_2' => ['name' => 'Step-free', 'type' => 'switch', 'index' => 3],
            'new_3' => ['name' => 'Genres', 'type' => 'multiselect', 'options' => 'Jazz,Folk,Rock', 'index' => 4],
            'new_4' => ['name' => 'Sign up by', 'type' => 'date', 'index' => 5],
        ]]);

        return [$venue, $this->keyFor($owner)];
    }

    private function create(Role $venue, array $headers, array $values)
    {
        return $this->postJson('/api/events/'.$venue->subdomain, [
            'name' => 'Opening Night',
            'starts_at' => now()->addWeek()->setTime(20, 0)->format('Y-m-d H:i:s'),
            'custom_field_values' => $values,
        ], $headers);
    }

    public function test_a_new_event_is_given_its_answers_by_field_name(): void
    {
        [$venue, $headers] = $this->venue();

        $data = $this->create($venue, $headers, [
            'room' => 'Garden',
            'Fee paid' => '40',
            'Step-free' => true,
            'Genres' => ['Jazz', 'Folk'],
            'new_4' => '2026-12-20',
        ])->assertCreated()->json('data');

        $event = Event::find(UrlUtils::decodeId($data['id']));
        $this->assertSame(['new_0' => 'Garden', 'new_1' => '40', 'new_2' => '1', 'new_3' => 'Jazz, Folk', 'new_4' => '2026-12-20'], $event->custom_field_values);
        $this->assertSame($venue->id, (int) $event->custom_field_values_role_id);

        $this->assertSame(
            ['Room' => 'Garden', 'Step-free' => true, 'Genres' => ['Jazz', 'Folk'], 'Sign up by' => '2026-12-20'],
            $data['custom_field_values'],
            'read back by name, without the private field',
        );
    }

    public function test_an_update_changes_only_the_fields_it_names(): void
    {
        [$venue, $headers] = $this->venue();
        $id = $this->create($venue, $headers, ['Room' => 'Garden', 'Fee paid' => '40', 'Genres' => 'Jazz'])->assertCreated()->json('data.id');

        $this->putJson('/api/events/'.$id, ['custom_field_values' => ['Room' => 'Hall', 'Genres' => null]], $headers)->assertOk();
        $this->assertSame(['new_0' => 'Hall', 'new_1' => '40'], Event::find(UrlUtils::decodeId($id))->custom_field_values, 'null clears; what was not named is kept');

        $this->putJson('/api/events/'.$id, ['name' => 'Renamed'], $headers)->assertOk();
        $this->assertSame(['new_0' => 'Hall', 'new_1' => '40'], Event::find(UrlUtils::decodeId($id))->custom_field_values, 'an update that does not mention them leaves them');
    }

    public function test_a_value_the_form_would_refuse_is_refused(): void
    {
        [$venue, $headers] = $this->venue();

        $this->create($venue, $headers, ['Colour' => 'Red'])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values.Colour']);
        $this->create($venue, $headers, ['Room' => 'Basement'])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values.Room']);
        $this->create($venue, $headers, ['Genres' => ['Jazz', 'Polka']])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values.Genres']);
        $this->create($venue, $headers, ['Step-free' => 'maybe'])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values.Step-free']);
        $this->create($venue, $headers, ['Sign up by' => 'soon'])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values.Sign up by']);
        $this->create($venue, $headers, ['Garden'])->assertStatus(422)->assertJsonValidationErrors(['custom_field_values']);
        $this->assertSame(0, Event::count(), 'nothing is made');

        // A required field is not demanded: an integration that never knew of it keeps working.
        $this->create($venue, $headers, ['Step-free' => false])->assertCreated();
    }
}
