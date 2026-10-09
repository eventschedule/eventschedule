<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The stored mail password belongs to the server it was typed for.
 *
 * Every member who may edit a schedule can post its form and its Test email button. Naming
 * another server (or port, sign-in name or encryption) while the password stayed as the masked
 * placeholder, or was left out, sent the stored password to wherever the new values pointed.
 *
 *   - such a save and such a test are refused, before anything in the save is written;
 *   - the same server keeps its password, and a new server with its password typed is saved.
 */
class ScheduleMailSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Role, 1: User} a hosted schedule with stored mail settings, and an admin who is not its owner */
    private function scheduleWithMailSettings(): array
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->createOwner(), 'venue');
        $role->setEmailSettings(['host' => '127.0.0.1', 'port' => 1, 'username' => 'box', 'password' => 'the-stored-secret', 'from_address' => 'box@gmail.com']);
        $role->save();

        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);

        return [$role->fresh(), $admin->fresh()];
    }

    public function test_a_test_mail_to_another_server_does_not_take_the_stored_password_along(): void
    {
        [$role, $admin] = $this->scheduleWithMailSettings();

        $this->actingAs($admin)->postJson(route('role.test_email', ['subdomain' => $role->subdomain]), [
            'email' => 'to@gmail.com',
            'email_settings' => ['host' => 'localhost', 'port' => 1, 'password' => str_repeat('•', 10)],
        ]);

        $this->assertNotSame('the-stored-secret', config('mail.mailers.role_'.$role->id.'.password'));
    }

    public function test_the_same_holds_when_the_password_is_simply_left_out(): void
    {
        [$role, $admin] = $this->scheduleWithMailSettings();

        $this->actingAs($admin)->postJson(route('role.test_email', ['subdomain' => $role->subdomain]), [
            'email' => 'to@gmail.com',
            'email_settings' => ['host' => 'localhost', 'port' => 1],
        ])->assertStatus(422);

        $this->assertNotSame('the-stored-secret', config('mail.mailers.role_'.$role->id.'.password'));
    }

    public function test_a_new_server_with_its_password_typed_is_saved(): void
    {
        [$role, $admin] = $this->scheduleWithMailSettings();

        $this->actingAs($admin)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), [
                'name' => $role->name, 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
                'email_settings' => ['host' => 'localhost', 'port' => 1, 'username' => 'box', 'password' => 'a-new-secret', 'from_address' => 'box@gmail.com'],
            ])->assertSessionHasNoErrors();

        $stored = $role->fresh()->getEmailSettings();
        $this->assertSame(['localhost', 'a-new-secret'], [$stored['host'] ?? null, $stored['password'] ?? null]);
    }

    public function test_saving_another_server_does_not_keep_the_stored_password(): void
    {
        [$role, $admin] = $this->scheduleWithMailSettings();

        $this->actingAs($admin)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), [
                'name' => $role->name, 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
                'email_settings' => ['host' => 'localhost', 'port' => 1, 'username' => 'box', 'password' => str_repeat('•', 10), 'from_address' => 'box@gmail.com'],
            ]);

        $stored = $role->fresh()->getEmailSettings();
        $this->assertFalse(($stored['host'] ?? null) === 'localhost' && ($stored['password'] ?? null) === 'the-stored-secret');
    }

    public function test_saving_the_same_server_keeps_the_stored_password(): void
    {
        [$role, $admin] = $this->scheduleWithMailSettings();

        $this->actingAs($admin)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), [
                'name' => $role->name, 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
                'email_settings' => ['host' => '127.0.0.1', 'port' => 1, 'username' => 'box', 'password' => str_repeat('•', 10), 'from_address' => 'box@gmail.com', 'from_name' => 'The Box'],
            ]);

        $stored = $role->fresh()->getEmailSettings();
        $this->assertSame('the-stored-secret', $stored['password'] ?? null);
        $this->assertSame('The Box', $stored['from_name'] ?? null);
    }
}
