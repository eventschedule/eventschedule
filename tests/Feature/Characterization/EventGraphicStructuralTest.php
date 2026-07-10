<?php

namespace Tests\Feature\Characterization;

use App\Services\designs\GridDesign;
use App\Services\designs\ListDesign;
use App\Services\designs\RowDesign;
use App\Services\EventGraphicGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Structural characterization for the event-graphic pipeline ahead of the P3
 * designs dedup (REFACTOR_PLAN.md): each layout generates a valid PNG with
 * consistent dimensions for fixed inputs, and all three design classes
 * instantiate together (an incompatible child override of a concrete base
 * method is a PHP FATAL that nothing else in the suite compiles).
 *
 * Deliberately NO image-hash assertions here - byte hashes are machine-
 * dependent (GD/freetype builds) and belong to the session-local golden-image
 * workflow (Section 3.4), never to committed tests.
 */
class EventGraphicStructuralTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_all_three_design_classes_instantiate_together(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $events = collect([$this->createEvent($role)]);

        // Loading all three classes in one process is the signature-collision
        // smoke: a P3 hoist that conflicts with RowDesign's variant signatures
        // fatals right here.
        $this->assertInstanceOf(GridDesign::class, new GridDesign($role, $events));
        $this->assertInstanceOf(ListDesign::class, new ListDesign($role, $events));
        $this->assertInstanceOf(RowDesign::class, new RowDesign($role, $events));
    }

    /**
     * @dataProvider layoutProvider
     */
    public function test_layout_generates_valid_png_for_one_and_several_events(string $layout): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        foreach ([1, 3] as $count) {
            $events = collect();
            for ($i = 0; $i < $count; $i++) {
                $events->push($this->createEvent($role, [
                    'name' => "Graphic Event {$i}",
                    'starts_at' => '2026-09-0'.($i + 1).' 18:00:00',
                ]));
            }

            $generator = new EventGraphicGenerator($role, $events, $layout);
            $png = $generator->generate();

            $info = getimagesizefromstring($png);
            $this->assertNotFalse($info, "{$layout}/{$count}: output is not a decodable image");
            $this->assertSame('image/png', $info['mime']);
            $this->assertSame($generator->getWidth(), $info[0], "{$layout}/{$count}: width mismatch");
            $this->assertSame($generator->getHeight(), $info[1], "{$layout}/{$count}: height mismatch");
            $this->assertGreaterThan(0, $info[0]);
            $this->assertGreaterThan(0, $info[1]);
        }
    }

    public static function layoutProvider(): array
    {
        return [
            'grid' => ['grid'],
            'list' => ['list'],
            'row' => ['row'],
        ];
    }
}
