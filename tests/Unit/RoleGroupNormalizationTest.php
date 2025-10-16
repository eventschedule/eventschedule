<?php

namespace Tests\Unit;

use App\Http\Controllers\RoleController;
use App\Repos\EventRepo;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class RoleGroupNormalizationTest extends TestCase
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

    public function testNormalizeGroupInputConvertsStdClassPayloads(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'normalizeGroupInput');
        $method->setAccessible(true);

        $payload = [
            'new_1' => (object) ['name' => 'Main Stage'],
            5 => (object) [
                'id' => 5,
                'name' => 'Club Nights',
                'slug' => 'club-nights',
                'name_en' => 'Club Nights EN',
            ],
        ];

        $normalized = $method->invoke($controller, $payload);

        $this->assertArrayHasKey('new_1', $normalized);
        $this->assertSame('Main Stage', $normalized['new_1']['name']);
        $this->assertSame('', $normalized['new_1']['slug']);
        $this->assertSame('', $normalized['new_1']['name_en']);
        $this->assertNull($normalized['new_1']['id']);

        $this->assertArrayHasKey(5, $normalized);
        $this->assertSame('Club Nights', $normalized[5]['name']);
        $this->assertSame('club-nights', $normalized[5]['slug']);
        $this->assertSame('Club Nights EN', $normalized[5]['name_en']);
        $this->assertSame('5', $normalized[5]['id']);
    }

    public function testNormalizeGroupInputCastsCollectionsAndScalars(): void
    {
        $controller = new RoleController($this->eventRepo);

        $method = new \ReflectionMethod(RoleController::class, 'normalizeGroupInput');
        $method->setAccessible(true);

        $payload = collect([
            'main' => Collection::make(['name' => 'Main', 'name_en' => null]),
            'secondary' => 'Side Stage',
        ]);

        $normalized = $method->invoke($controller, $payload);

        $this->assertSame('Main', $normalized['main']['name']);
        $this->assertSame('', $normalized['main']['name_en']);
        $this->assertSame('', $normalized['main']['slug']);
        $this->assertNull($normalized['main']['id']);

        $this->assertSame('Side Stage', $normalized['secondary']['name']);
        $this->assertSame('', $normalized['secondary']['name_en']);
        $this->assertSame('', $normalized['secondary']['slug']);
        $this->assertNull($normalized['secondary']['id']);
    }
}
