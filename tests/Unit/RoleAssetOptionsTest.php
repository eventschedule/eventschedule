<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use App\Repos\EventRepo;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class RoleAssetOptionsTest extends TestCase
{
    /**
     * @var EventRepo&MockObject
     */
    private $eventRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepo = $this->createMock(EventRepo::class);
    }

    public function testPrepareNameOptionsHandlesObjectsWithoutName(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'prepareNameOptions');
        $method->setAccessible(true);

        $items = [
            (object) ['file' => 'header_one'],
            (object) ['name' => 'Header_Two'],
            ['label' => 'Header Three'],
            'header_four',
            null,
            (object) [],
        ];

        $result = $method->invoke($controller, $items);

        $this->assertArrayHasKey('header_one', $result);
        $this->assertSame('header one', $result['header_one']);
        $this->assertArrayHasKey('Header_Two', $result);
        $this->assertSame('Header Two', $result['Header_Two']);
        $this->assertArrayHasKey('Header Three', $result);
        $this->assertSame('Header Three', $result['Header Three']);
        $this->assertArrayHasKey('header_four', $result);
        $this->assertSame('header four', $result['header_four']);
    }

    public function testPrepareNameOptionsHandlesStdClassPayload(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'prepareNameOptions');
        $method->setAccessible(true);

        $payload = json_decode('[{"name":"Alpha"},{"label":"Beta"},"Gamma"]');

        $result = $method->invoke($controller, $payload);

        $this->assertArrayHasKey('Alpha', $result);
        $this->assertSame('Alpha', $result['Alpha']);
        $this->assertArrayHasKey('Beta', $result);
        $this->assertSame('Beta', $result['Beta']);
        $this->assertArrayHasKey('Gamma', $result);
        $this->assertSame('Gamma', $result['Gamma']);
    }

    public function testPrepareGradientOptionsHandlesNestedColors(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'prepareGradientOptions');
        $method->setAccessible(true);

        $items = [
            (object) [
                'name' => 'Sunrise',
                'colors' => ['#ff0000', ' #00ff00 ', '', null],
            ],
            [
                'title' => 'Ocean Breeze',
                'value' => [
                    ['colors' => ['#123456', '#abcdef']],
                ],
            ],
            (object) [
                'label' => 'Nested',
                'value' => [
                    (object) ['value' => ' #111111, #222222 '],
                ],
            ],
            (object) [],
        ];

        $result = $method->invoke($controller, $items);

        $this->assertArrayHasKey('#ff0000, #00ff00', $result);
        $this->assertSame('Sunrise', $result['#ff0000, #00ff00']);
        $this->assertArrayHasKey('#123456, #abcdef', $result);
        $this->assertSame('Ocean Breeze', $result['#123456, #abcdef']);
        $this->assertArrayHasKey('#111111, #222222', $result);
        $this->assertSame('Nested', $result['#111111, #222222']);
    }

    public function testNormalizeRoleAssetDatasetCastsObjectsRecursively(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'normalizeRoleAssetDataset');
        $method->setAccessible(true);

        $payload = json_decode('{
            "headers": {
                "alpha": {"name": "ALPHA"},
                "beta": {"items": [{"value": {"label": "BETA"}}]},
                "gamma": "GAMMA",
                "delta": [{"meta": {"title": "DELTA"}}]
            }
        }');

        $normalized = $method->invoke($controller, $payload);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('headers', $normalized);
        $this->assertIsArray($normalized['headers']);
        $this->assertIsArray($normalized['headers']['alpha']);
        $this->assertIsArray($normalized['headers']['beta']);

        $prepare = new \ReflectionMethod(RoleController::class, 'prepareNameOptions');
        $prepare->setAccessible(true);

        $result = $prepare->invoke($controller, $normalized['headers']);

        $this->assertArrayHasKey('ALPHA', $result);
        $this->assertArrayHasKey('BETA', $result);
        $this->assertArrayHasKey('GAMMA', $result);
        $this->assertArrayHasKey('DELTA', $result);
    }

    public function testNormalizeDecodedJsonStructureConvertsStdClassToArrays(): void
    {
        $controller = new RoleController($this->eventRepo);

        $normalizer = new \ReflectionMethod(RoleController::class, 'normalizeDecodedJsonStructure');
        $normalizer->setAccessible(true);

        $prepare = new \ReflectionMethod(RoleController::class, 'prepareNameOptions');
        $prepare->setAccessible(true);

        $json = <<<JSON
        {
            "headers": [
                {"name": "Alpha_Header"},
                {"value": {"label": "Bravo_Header"}},
                {"meta": {"title": "Charlie Header"}},
                {"items": [{"items": [{"value": {"name": "Delta_Header"}}]}]}
            ]
        }
        JSON;

        $decoded = json_decode($json);
        $normalized = $normalizer->invoke($controller, $decoded);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('headers', $normalized);

        $result = $prepare->invoke($controller, $normalized['headers']);

        $this->assertArrayHasKey('Alpha_Header', $result);
        $this->assertSame('Alpha Header', $result['Alpha_Header']);
        $this->assertArrayHasKey('Bravo_Header', $result);
        $this->assertSame('Bravo Header', $result['Bravo_Header']);
        $this->assertArrayHasKey('Charlie Header', $result);
        $this->assertSame('Charlie Header', $result['Charlie Header']);
        $this->assertArrayHasKey('Delta_Header', $result);
        $this->assertSame('Delta Header', $result['Delta_Header']);
    }
}
