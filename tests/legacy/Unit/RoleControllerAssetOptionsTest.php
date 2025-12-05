<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use App\Repos\EventRepo;
use Mockery;
use Tests\TestCase;

class RoleControllerAssetOptionsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testLoadRoleAssetOptionsHandlesUndefinedPropertyGracefully(): void
    {
        $controller = new RoleController(Mockery::mock(EventRepo::class));
        $method = new \ReflectionMethod($controller, 'loadRoleAssetOptions');
        $method->setAccessible(true);

        $relativePath = 'storage/test-assets.json';
        $absolutePath = base_path($relativePath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0777, true);
        }

        file_put_contents($absolutePath, json_encode([['name' => 'sample']]));

        $result = $method->invoke($controller, $relativePath, function () {
            $object = new \stdClass();

            return $object->name; // Triggers Undefined property notice converted to ErrorException.
        }, 'unit.test');

        $this->assertSame([], $result);

        unlink($absolutePath);
    }
}
