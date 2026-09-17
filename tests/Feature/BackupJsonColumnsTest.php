<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * JSON settings through a backup round trip.
 *
 * The exporters copy raw column values, so a column with an `array` cast travels as its JSON text,
 * and the importers used to assign that text straight back - which Eloquent JSON-encodes a second
 * time. A restored schedule then read its custom fields back as a string, and the public schedule
 * page and the settings page threw on getEventCustomFields(): array.
 */
class BackupJsonColumnsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const CUSTOM_FIELDS = [
        'gear' => ['name' => 'Gear', 'type' => 'string', 'index' => 1],
    ];

    private function export(User $owner, Role $role): array
    {
        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = app(BackupService::class)->exportSchedules([$role->fresh()], false, $job)['json'];

        // Through JSON, exactly as the backup file carries it.
        return json_decode(json_encode($data), true);
    }

    private function import(User $owner, array $data, Role $original): Role
    {
        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        app(BackupService::class)->importSchedules($data, [0], $owner->id, $job);

        return Role::where('user_id', $owner->id)->where('id', '!=', $original->id)->latest('id')->firstOrFail();
    }

    private function schedule(User $owner): Role
    {
        return $this->createRole($owner, 'venue', [
            // The owner's own address, so the restored copy comes back verified and its guest page renders.
            'email' => $owner->email,
            'accept_requests' => true,
            'event_custom_fields' => self::CUSTOM_FIELDS,
            'custom_labels' => ['our_sponsors' => ['value' => 'Our Friends']],
            'gift_card_amounts' => [25, 50],
            'booking_form_config' => ['required_fields' => ['description' => true], 'allow_online' => false],
        ]);
    }

    private function assertStoredAsJsonStructure(string $table, int $id, array $columns): void
    {
        foreach ($columns as $column) {
            $raw = ltrim((string) DB::table($table)->where('id', $id)->value($column));
            $this->assertContains($raw[0] ?? '', ['{', '['], "$table.$column was stored double-encoded: $raw");
        }
    }

    public function test_json_settings_survive_a_backup_round_trip(): void
    {
        $owner = $this->createOwner();
        $role = $this->schedule($owner);
        $this->createEvent($role, ['name' => 'Gear Night', 'custom_field_values' => ['gear' => 'Two amps']]);

        $restored = $this->import($owner, $this->export($owner, $role), $role);

        $this->assertSame(self::CUSTOM_FIELDS, $restored->getEventCustomFields());
        $this->assertSame('Our Friends', $restored->customLabel('our_sponsors'));
        $this->assertEquals([25, 50], $restored->gift_card_amounts);
        $this->assertTrue($restored->bookingFormRequires('description'));
        $this->assertFalse($restored->bookingFormAllowsOnline());
        $this->assertStoredAsJsonStructure('roles', $restored->id, ['event_custom_fields', 'custom_labels', 'gift_card_amounts', 'booking_form_config']);

        $event = Event::where('name', 'Gear Night')->latest('id')->firstOrFail();
        $this->assertTrue($event->roles()->where('roles.id', $restored->id)->exists(), 'the restored event belongs to the restored schedule');
        $this->assertSame(['gear' => 'Two amps'], $event->custom_field_values);
        $this->assertStoredAsJsonStructure('events', $event->id, ['custom_field_values']);

        // The pages that read those settings render.
        $this->get(route('role.view_guest', ['subdomain' => $restored->subdomain]))->assertOk();
        $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $restored->subdomain]))->assertOk();
    }

    public function test_the_defaults_survive_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $restored = $this->import($owner, $this->export($owner, $role), $role);

        $this->assertNull($restored->getRawOriginal('booking_form_config'));
        $this->assertTrue($restored->bookingFormAllowsOnline());
        $this->assertSame([], $restored->getEventCustomFields());
    }

    public function test_a_backup_holding_an_already_double_encoded_value_restores_cleanly(): void
    {
        $owner = $this->createOwner();
        $role = $this->schedule($owner);

        $data = $this->export($owner, $role);
        // What a backup taken of a previously restored schedule looks like.
        $data['schedules'][0]['role']['event_custom_fields'] = json_encode($data['schedules'][0]['role']['event_custom_fields']);

        $restored = $this->import($owner, $data, $role);

        $this->assertSame(self::CUSTOM_FIELDS, $restored->getEventCustomFields());
    }

    public function test_unreadable_json_restores_as_empty_instead_of_failing_the_import(): void
    {
        $owner = $this->createOwner();
        $role = $this->schedule($owner);

        $data = $this->export($owner, $role);
        $data['schedules'][0]['role']['custom_labels'] = '{not json';

        $restored = $this->import($owner, $data, $role);

        $this->assertNull($restored->custom_labels);
        $this->assertSame(__('messages.our_sponsors'), $restored->customLabel('our_sponsors'));
        $this->assertSame(self::CUSTOM_FIELDS, $restored->getEventCustomFields());
    }

    public function test_the_repair_migration_unwraps_what_an_older_restore_stored(): void
    {
        $owner = $this->createOwner();
        $broken = $this->createRole($owner, 'venue');
        $clean = $this->createRole($owner, 'venue', ['event_custom_fields' => self::CUSTOM_FIELDS]);
        $brokenEvent = $this->createEvent($broken, ['name' => 'Broken Event']);
        $oddEvent = $this->createEvent($clean, ['name' => 'Odd Event']);

        DB::table('roles')->where('id', $broken->id)->update([
            'event_custom_fields' => json_encode(json_encode(self::CUSTOM_FIELDS)),
            'custom_labels' => json_encode(json_encode(['our_sponsors' => ['value' => 'Friends']])),
            'gift_card_amounts' => json_encode(json_encode([25, 50])),
        ]);
        DB::table('events')->where('id', $brokenEvent->id)->update([
            'custom_field_values' => json_encode(json_encode(['gear' => 'Amp'])),
            // Restored twice.
            'custom_fields' => json_encode(json_encode(json_encode(['note' => ['name' => 'Note']]))),
        ]);
        // A quoted value that does not unwrap to an array is not ours to touch.
        DB::table('events')->where('id', $oddEvent->id)->update(['custom_field_values' => '"just text"']);

        $cleanBefore = DB::table('roles')->where('id', $clean->id)->value('event_custom_fields');

        $migration = require database_path('migrations/2026_09_17_000001_unwrap_double_encoded_json_settings.php');
        $migration->up();
        $migration->up();

        $broken = $broken->fresh();
        $this->assertSame(self::CUSTOM_FIELDS, $broken->getEventCustomFields());
        $this->assertSame('Friends', $broken->customLabel('our_sponsors'));
        $this->assertEquals([25, 50], $broken->gift_card_amounts);

        $brokenEvent = $brokenEvent->fresh();
        $this->assertSame(['gear' => 'Amp'], $brokenEvent->custom_field_values);
        $this->assertSame(['note' => ['name' => 'Note']], $brokenEvent->custom_fields);

        $this->assertSame('"just text"', DB::table('events')->where('id', $oddEvent->id)->value('custom_field_values'));
        $this->assertSame($cleanBefore, DB::table('roles')->where('id', $clean->id)->value('event_custom_fields'));
    }
}
