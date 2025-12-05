<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_user_without_password_changes(): void
    {
        $admin = User::factory()->create();
        $managedUser = User::factory()->create([
            'password' => Hash::make('initial-pass'),
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('settings.users.update', $managedUser), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'password' => '',
                'password_confirmation' => '',
                'timezone' => 'UTC',
                'language_code' => 'en',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('settings.users.index'));

        $managedUser->refresh();

        $this->assertSame('Updated Name', $managedUser->name);
        $this->assertSame('updated@example.com', $managedUser->email);
        $this->assertTrue(Hash::check('initial-pass', $managedUser->password));
    }
}
