<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SystemRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleListingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.hosted' => false,
            'app.is_testing' => true,
            'services.google.backend' => null,
        ]);
    }

    /**
     * @dataProvider roleListingProvider
     */
    public function test_role_listing_pages_show_owned_roles_only(string $routeName, string $type, ?string $titleKey, string $createKey): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownedRole = $this->createRoleForUser($user, $type, 'Owned ' . Str::title($type));
        $this->createRoleForUser($otherUser, $type, 'Hidden ' . Str::title($type));

        $this->actingAs($user);

        $response = $this->get(route($routeName));

        $response->assertOk();

        $expectedTitle = $titleKey
            ? __('messages.' . $titleKey)
            : Str::plural(__('messages.talent'));

        $response->assertSeeText($expectedTitle);
        $response->assertSeeText(__('messages.' . $createKey));
        $response->assertSeeText($ownedRole->name);
        $response->assertSee(route('role.view_admin', ['subdomain' => $ownedRole->subdomain, 'tab' => 'schedule']), false);
        $response->assertDontSeeText('Hidden ' . Str::title($type));
    }

    /**
     * @dataProvider roleListingProvider
     */
    public function test_role_listing_pages_support_list_view(string $routeName, string $type, ?string $titleKey, string $createKey): void
    {
        $user = User::factory()->create();
        $role = $this->createRoleForUser($user, $type, 'List ' . Str::title($type));

        $this->actingAs($user);

        $response = $this->get(route($routeName, ['view' => 'list']));

        $response->assertOk();
        $response->assertSee('<table', false);
        $response->assertSeeText($role->name);
    }

    public static function roleListingProvider(): array
    {
        return [
            'venues' => ['role.venues', 'venue', 'venues', 'new_venue'],
            'curators' => ['role.curators', 'curator', 'curators', 'new_curator'],
            'talent' => ['role.talent', 'talent', null, 'new_talent'],
        ];
    }

    public function test_viewer_cannot_create_roles_or_access_create_page(): void
    {
        $this->seed(\Database\Seeders\AuthorizationSeeder::class);

        $viewer = User::factory()->create();

        $viewerRole = SystemRole::query()->where('slug', 'viewer')->firstOrFail();
        $viewer->systemRoles()->sync([$viewerRole->getKey()]);

        app(AuthorizationService::class)->forgetUserPermissions($viewer);

        $this->actingAs($viewer);

        $response = $this->get(route('role.venues'));

        $response->assertOk();
        $response->assertDontSeeText(__('messages.new_venue'));

        $createResponse = $this->get(route('new', ['type' => 'venue']));
        $createResponse->assertForbidden();
    }

    private function createRoleForUser(?User $user, string $type, string $name): Role
    {
        $role = new Role([
            'type' => $type,
            'name' => $name,
            'email' => Str::slug($name) . '@example.com',
        ]);

        $role->subdomain = Role::generateSubdomain($name);

        if ($user) {
            $role->user_id = $user->id;
        }

        $role->save();

        if ($user) {
            $role->users()->attach($user->id, ['level' => 'owner']);
        }

        return $role;
    }
}
