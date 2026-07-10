<?php

namespace Tests\Feature\Characterization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Full settings-save round trip for RoleController::update() ahead of the
 * P14 decomposition (REFACTOR_PLAN.md): pins the resulting roles-row column
 * map, the redirect + flash, the audit-log row, and image upload storage.
 */
class RoleUpdateCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function updatePayload($role, array $overrides = []): array
    {
        // The form always submits the identity block; resubmitting the same
        // email avoids the hosted email-change verification reset.
        return array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'new_subdomain' => $role->subdomain,
        ], $overrides);
    }

    public function test_full_settings_round_trip_pins_roles_row(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $response = $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->updatePayload($role, [
                'name' => 'Renamed Venue',
                'description' => 'A **markdown** description',
                'address1' => '55 Renamed St',
                'city' => 'Newtown',
                'state' => 'NY',
                'postal_code' => '10001',
                'accent_color' => '#123456',
                'font_family' => 'Roboto',
                'timezone' => 'Europe/London',
            ])
        );

        $response->assertRedirect(route('role.view_admin', [
            'subdomain' => $role->subdomain,
            'tab' => 'schedule',
        ]));
        $response->assertSessionHas('message', __('messages.updated_schedule'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Renamed Venue',
            'subdomain' => $role->subdomain,
            'address1' => '55 Renamed St',
            'city' => 'Newtown',
            'state' => 'NY',
            'postal_code' => '10001',
            'accent_color' => '#123456',
            'font_family' => 'Roboto',
            'timezone' => 'Europe/London',
            'email' => $role->email,
        ]);

        // Markdown description derives its *_html sibling via the model hook.
        $fresh = $role->fresh();
        $this->assertStringContainsString('<strong>markdown</strong>', (string) $fresh->description_html);

        // Same-email resubmission does NOT reset verification.
        $this->assertNotNull($fresh->email_verified_at);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'schedule.update',
            'user_id' => $owner->id,
            'model_type' => 'Role',
            'model_id' => $role->id,
        ]);
    }

    public function test_profile_image_upload_is_stored_and_recorded(): void
    {
        Storage::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->updatePayload($role, [
                'profile_image' => UploadedFile::fake()->image('profile.png', 300, 300),
            ])
        )->assertRedirect();

        $filename = $role->fresh()->getAttributes()['profile_image_url'];
        $this->assertNotEmpty($filename);
        Storage::assertExists('public/'.$filename);
    }

    public function test_subdomain_change_round_trip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $newSubdomain = 'renamed'.strtolower(\Illuminate\Support\Str::random(6));

        $response = $this->actingAs($owner)->put(
            route('role.update', ['subdomain' => $role->subdomain]),
            $this->updatePayload($role, ['new_subdomain' => $newSubdomain])
        );

        // The redirect targets the NEW subdomain.
        $response->assertRedirect(route('role.view_admin', [
            'subdomain' => $newSubdomain,
            'tab' => 'schedule',
        ]));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'subdomain' => $newSubdomain,
        ]);
    }
}
