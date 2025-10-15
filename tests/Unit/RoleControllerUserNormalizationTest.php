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

    /**
     * @return array<string, mixed>
     */
    private function invokeNormalizeUser(RoleController $controller, $user): array
    {
        $method = new \ReflectionMethod($controller, 'normalizeAuthenticatedUser');
        $method->setAccessible(true);

        return $method->invoke($controller, $user);
    }
}
