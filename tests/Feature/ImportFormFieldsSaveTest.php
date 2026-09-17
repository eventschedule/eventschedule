<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The AI import form's optional fields (Settings > Advanced > Import Form Fields). The section renders
 * on the new-schedule page as well, but store() used to ignore it, so every new schedule started
 * with all of its toggles off whatever the owner had set.
 */
class ImportFormFieldsSaveTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FIELDS = ['short_description', 'description', 'ticket_price', 'coupon_code', 'registration_url', 'category_id', 'group_id'];

    /** What the section posts: a hidden 0 per switch and per Required box, overridden when ticked. */
    private function section(array $shown, array $required): array
    {
        $data = ['import_fields' => [], 'required_fields' => []];
        foreach (self::FIELDS as $field) {
            $data['import_fields'][$field] = in_array($field, $shown, true) ? '1' : '0';
            $data['required_fields'][$field] = in_array($field, $required, true) ? '1' : '0';
        }

        return $data;
    }

    public function test_creating_a_schedule_saves_the_import_form_fields(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post(route('role.store'), array_merge([
            'type' => 'curator',
            'name' => 'Fresh Curator',
            'email' => 'fresh.curator@gmail.com',
            'timezone' => 'America/New_York',
            'language_code' => 'en',
            // Saved by the block just before, into the same column.
            'import_cities' => ['Kassel'],
        ], $this->section(['description', 'coupon_code'], ['description'])))->assertSessionHasNoErrors();

        $config = Role::where('name', 'Fresh Curator')->firstOrFail()->import_config;

        $this->assertTrue($config['fields']['description']);
        $this->assertTrue($config['fields']['coupon_code']);
        $this->assertFalse($config['fields']['ticket_price']);
        $this->assertTrue($config['required_fields']['description']);
        $this->assertFalse($config['required_fields']['coupon_code']);
        $this->assertSame(['kassel'], $config['cities']);
    }

    public function test_updating_a_schedule_still_saves_them(): void
    {
        $role = $this->createCurator($this->createOwner(), [
            'import_config' => ['fields' => ['description' => true], 'required_fields' => ['description' => true]],
        ]);

        $this->actingAs($role->user)->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            'new_subdomain' => $role->subdomain,
        ], $this->section(['category_id'], ['category_id'])))->assertSessionHasNoErrors();

        $config = $role->fresh()->import_config;

        $this->assertTrue($config['fields']['category_id']);
        $this->assertTrue($config['required_fields']['category_id']);
        $this->assertFalse($config['fields']['description']);
        $this->assertFalse($config['required_fields']['description']);
    }

    public function test_a_save_without_the_section_leaves_them_alone(): void
    {
        $role = $this->createCurator($this->createOwner(), [
            'import_config' => ['fields' => ['coupon_code' => true], 'required_fields' => ['coupon_code' => true]],
        ]);

        $this->actingAs($role->user)->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => $role->name,
            'email' => $role->email,
            'timezone' => $role->timezone,
            'language_code' => 'en',
            'new_subdomain' => $role->subdomain,
        ])->assertSessionHasNoErrors();

        $config = $role->fresh()->import_config;
        $this->assertTrue($config['fields']['coupon_code']);
        $this->assertTrue($config['required_fields']['coupon_code']);
    }
}
