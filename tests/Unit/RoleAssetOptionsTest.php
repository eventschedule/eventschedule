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
}
