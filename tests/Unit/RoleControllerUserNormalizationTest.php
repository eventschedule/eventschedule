<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use App\Repos\EventRepo;
use Illuminate\Contracts\Support\Arrayable;
use Mockery;
use Tests\TestCase;

class RoleControllerUserNormalizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testNormalizeAuthenticatedUserHandlesStdClassWithoutProperties(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));
        $user = (object) [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ];

        $normalized = $this->invokeNormalizeUser($controller, $user);

        $this->assertSame('Ada', $normalized['first_name']);
        $this->assertSame('Lovelace', $normalized['last_name']);
        $this->assertArrayNotHasKey('name', $normalized);
    }

    public function testNormalizeAuthenticatedUserHandlesArrayable(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));
        $user = new class implements Arrayable {
            public function toArray()
            {
                return [
                    'name' => 'Grace Hopper',
                    'timezone' => 'UTC',
                ];
            }
        };

        $normalized = $this->invokeNormalizeUser($controller, $user);

        $this->assertSame('Grace Hopper', $normalized['name']);
        $this->assertSame('UTC', $normalized['timezone']);
    }

    public function testNormalizeAuthenticatedUserHandlesNull(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));

        $normalized = $this->invokeNormalizeUser($controller, null);

        $this->assertSame([], $normalized);
    }

    public function testEnsureUserIdentityAttributesPopulatesStdClass(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));
        $role = new \App\Models\Role();
        $role->name = 'Sample Talent';

        $user = (object) [];
        $userData = [
            'first_name' => 'Sample',
            'last_name' => 'Talent',
            'email' => 'sample@example.test',
        ];

        $this->invokeEnsureUserIdentity($controller, $user, $userData, $role);

        $this->assertSame('Sample Talent', $user->name);
        $this->assertSame('Sample', $user->first_name);
        $this->assertSame('Talent', $user->last_name);
        $this->assertSame('sample@example.test', $user->email);
    }

    public function testEnsureUserIdentityAttributesDoesNotOverrideExistingValues(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));
        $role = new \App\Models\Role();
        $role->name = 'Sample Talent';

        $user = (object) [
            'name' => 'Existing Name',
            'first_name' => 'Existing First',
            'last_name' => 'Existing Last',
            'email' => 'existing@example.test',
        ];

        $userData = [
            'first_name' => 'New First',
            'last_name' => 'New Last',
            'email' => 'new@example.test',
        ];

        $this->invokeEnsureUserIdentity($controller, $user, $userData, $role);

        $this->assertSame('Existing Name', $user->name);
        $this->assertSame('Existing First', $user->first_name);
        $this->assertSame('Existing Last', $user->last_name);
        $this->assertSame('existing@example.test', $user->email);
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeNormalizeUser(RoleController $controller, $user): array
    {
        $method = new \ReflectionMethod($controller, 'normalizeAuthenticatedUser');
        $method->setAccessible(true);

        return $method->invoke($controller, $user);
    }

    private function invokeEnsureUserIdentity(RoleController $controller, $user, array $userData, \App\Models\Role $role): void
    {
        $method = new \ReflectionMethod($controller, 'ensureUserIdentityAttributes');
        $method->setAccessible(true);

        $method->invoke($controller, $user, $userData, $role);
    }
}
