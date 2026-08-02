<?php

namespace Tests\Feature\Characterization;

use App\Models\Event;
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
     * Every design must survive an empty collection. RowDesign used to fatal here:
     * its row maths indexes $rowAssignments[0], and with nothing to lay out that is
     * count(null), a TypeError rather than a warning. Callers all guard isEmpty()
     * today, so this pins the family invariant its two siblings already honoured.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('layoutProvider')]
    public function test_layout_survives_an_empty_event_collection(string $layout): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $generator = new EventGraphicGenerator($role, collect(), $layout);
        $png = $generator->generate();

        $info = getimagesizefromstring($png);
        $this->assertNotFalse($info, "{$layout}: empty collection did not produce an image");
        $this->assertSame('image/png', $info['mime']);
        $this->assertGreaterThan(0, $info[0]);
        $this->assertGreaterThan(0, $info[1]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('layoutProvider')]
    public function test_layout_generates_valid_png_for_one_and_several_events(string $layout): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // 12 is past the old hard-coded 3x3 ceiling, where the canvas used to be sized
        // for 9 flyers while 12 were handed in.
        foreach ([1, 3, 12] as $count) {
            $events = collect();
            for ($i = 0; $i < $count; $i++) {
                $events->push($this->createEvent($role, [
                    'name' => "Graphic Event {$i}",
                    'starts_at' => sprintf('2026-09-%02d 18:00:00', $i + 1),
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

    #[\PHPUnit\Framework\Attributes\DataProvider('socialFormatProvider')]
    public function test_image_size_format_produces_exact_dimensions(string $format, int $expectedWidth, int $expectedHeight): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $events = collect([
            $this->createEvent($role, ['name' => 'A', 'starts_at' => '2026-09-01 18:00:00']),
            $this->createEvent($role, ['name' => 'B', 'starts_at' => '2026-09-02 18:00:00']),
        ]);

        $generator = new EventGraphicGenerator($role, $events, 'grid', false, ['image_size' => $format]);
        $png = $generator->generate();

        $info = getimagesizefromstring($png);
        $this->assertNotFalse($info, "{$format}: output is not a decodable image");
        $this->assertSame('image/png', $info['mime']);
        // The graphic is contain-scaled and padded onto an exact-format canvas.
        $this->assertSame($expectedWidth, $info[0], "{$format}: width mismatch");
        $this->assertSame($expectedHeight, $info[1], "{$format}: height mismatch");
        // getWidth()/getHeight() should report the actual output dimensions.
        $this->assertSame($expectedWidth, $generator->getWidth(), "{$format}: getWidth mismatch");
        $this->assertSame($expectedHeight, $generator->getHeight(), "{$format}: getHeight mismatch");

        // The letterbox padding must be filled with the schedule's background, not
        // left as the raw black of a fresh truecolor canvas. A small 2-event grid is
        // narrower/shorter than every target format, so the top-left corner is padding.
        $im = imagecreatefromstring($png);
        $rgb = imagecolorsforindex($im, imagecolorat($im, 0, 0));
        imagedestroy($im);
        $this->assertGreaterThan(30, $rgb['red'] + $rgb['green'] + $rgb['blue'], "{$format}: padding corner is unfilled (black)");
    }

    /**
     * The grid shape per event count. Everything from 9 up used to be a hard-coded
     * 3x3, so the canvas was sized for 9 cells and generateEventLayout() - which is
     * bounded by gridRows x gridCols - silently never drew the rest. Counts 0-9 are
     * the original hand-picked shapes and must not drift.
     */
    public function test_grid_shape_per_event_count(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // Build the maximum set once and slice it - GridDesign only reads the count.
        $events = collect();
        for ($i = 0; $i < 20; $i++) {
            $events->push($this->createEvent($role, [
                'name' => "Grid Event {$i}",
                'starts_at' => sprintf('2026-09-%02d 18:00:00', $i + 1),
            ]));
        }

        $expected = [
            0 => [1, 1], 1 => [1, 1], 2 => [2, 1], 3 => [3, 1], 4 => [2, 2],
            5 => [3, 2], 6 => [3, 2], 7 => [3, 3], 8 => [3, 3], 9 => [3, 3],
            10 => [4, 3], 11 => [4, 3], 12 => [4, 3],
            13 => [4, 4], 14 => [4, 4], 15 => [4, 4], 16 => [4, 4],
            17 => [5, 4], 18 => [5, 4], 19 => [5, 4], 20 => [5, 4],
        ];

        foreach ($expected as $count => [$cols, $rows]) {
            $layout = (new GridDesign($role, $events->take($count)))->getCurrentGridLayout();

            $this->assertSame($cols, $layout['cols'], "{$count} events: wrong column count");
            $this->assertSame($rows, $layout['rows'], "{$count} events: wrong row count");

            // The invariant the layout loop depends on: a grid smaller than the
            // collection drops the overflow without any warning.
            $this->assertGreaterThanOrEqual(
                $count,
                $layout['cols'] * $layout['rows'],
                "{$count} events: grid holds only {$layout['cols']}x{$layout['rows']} cells"
            );
        }
    }

    /**
     * The bug itself, asserted directly: generateEventLayout() is bounded by
     * gridRows x gridCols, so with the old 3x3 fallback the 10th event onward was
     * never handed to generateSingleFlyer() at all - no warning, no truncation
     * notice, just nine tiles on a poster whose caption listed twenty.
     */
    public function test_every_event_is_drawn_not_just_the_first_nine(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $events = collect();
        for ($i = 0; $i < 20; $i++) {
            $events->push($this->createEvent($role, [
                'name' => "Grid Event {$i}",
                'starts_at' => sprintf('2026-09-%02d 18:00:00', $i + 1),
            ]));
        }

        $design = new class($role, $events) extends GridDesign
        {
            /** @var list<string> */
            public array $drawn = [];

            protected function generateSingleFlyer(Event $event, int $row, int $col, int $eventNumber = 0): void
            {
                $this->drawn[] = $event->name;
            }
        };

        $design->generate();

        $this->assertSame(
            $events->pluck('name')->all(),
            $design->drawn,
            'the grid drew '.count($design->drawn).' of 20 flyers, in order'
        );
    }

    public function test_grid_canvas_grows_past_the_old_nine_event_ceiling(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $events = collect();
        for ($i = 0; $i < 20; $i++) {
            $events->push($this->createEvent($role, [
                'name' => "Grid Event {$i}",
                'starts_at' => sprintf('2026-09-%02d 18:00:00', $i + 1),
            ]));
        }

        $nine = (new GridDesign($role, $events->take(9)))->getCurrentGridLayout();
        $twenty = (new GridDesign($role, $events))->getCurrentGridLayout();

        // Before the fix both were 3x3 and both canvases were identical.
        $this->assertGreaterThan($nine['total_width'], $twenty['total_width']);
        $this->assertGreaterThan($nine['total_height'], $twenty['total_height']);
    }

    public function test_max_per_row_override_still_fits_every_event(): void
    {
        // The Flyers Per Row branch was always correct - it is the auto path that was
        // broken - so pin it against collateral damage from the auto-path rewrite.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $events = collect();
        for ($i = 0; $i < 20; $i++) {
            $events->push($this->createEvent($role, [
                'name' => "Grid Event {$i}",
                'starts_at' => sprintf('2026-09-%02d 18:00:00', $i + 1),
            ]));
        }

        $layout = (new GridDesign($role, $events, false, ['max_per_row' => 4]))->getCurrentGridLayout();

        $this->assertSame(4, $layout['cols']);
        $this->assertSame(5, $layout['rows']);
    }

    public static function socialFormatProvider(): array
    {
        return [
            'square' => ['square', 1080, 1080],
            'portrait' => ['portrait', 1080, 1350],
            'story' => ['story', 1080, 1920],
            'landscape' => ['landscape', 1200, 630],
        ];
    }

    public function test_image_size_auto_matches_native_size(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $events = collect([
            $this->createEvent($role, ['name' => 'A', 'starts_at' => '2026-09-01 18:00:00']),
            $this->createEvent($role, ['name' => 'B', 'starts_at' => '2026-09-02 18:00:00']),
        ]);

        // 'auto' (and any unknown value) must leave the native, content-driven size untouched.
        $nativeInfo = getimagesizefromstring((new EventGraphicGenerator($role, $events, 'grid'))->generate());
        $autoInfo = getimagesizefromstring((new EventGraphicGenerator($role, $events, 'grid', false, ['image_size' => 'auto']))->generate());

        $this->assertSame($nativeInfo[0], $autoInfo[0], 'auto width should equal native');
        $this->assertSame($nativeInfo[1], $autoInfo[1], 'auto height should equal native');
    }

    public function test_letterbox_padding_blends_with_gradient_background(): void
    {
        $owner = $this->createOwner();
        // High-contrast vertical gradient makes any seam a large color delta.
        $role = $this->createRole($owner, 'venue', [
            'background' => 'gradient',
            'background_colors' => '#000000,#ffffff',
        ]);
        $events = collect([
            $this->createEvent($role, ['name' => 'A', 'starts_at' => '2026-09-01 18:00:00']),
            $this->createEvent($role, ['name' => 'B', 'starts_at' => '2026-09-02 18:00:00']),
        ]);

        // Native size, to locate the top edge of the graphic inside the Story canvas.
        $nativeInfo = getimagesizefromstring((new EventGraphicGenerator($role, $events, 'grid'))->generate());
        [$srcW, $srcH] = [$nativeInfo[0], $nativeInfo[1]];

        $targetW = 1080;
        $targetH = 1920; // Story: a wide grid letterboxes top/bottom.
        $scale = min($targetW / $srcW, $targetH / $srcH);
        $drawH = (int) round($srcH * $scale);
        $dstY = (int) round(($targetH - $drawH) / 2);
        $this->assertGreaterThan(6, $dstY, 'Story format should letterbox this grid top/bottom');

        $im = imagecreatefromstring((new EventGraphicGenerator($role, $events, 'grid', false, ['image_size' => 'story']))->generate());
        $cx = (int) ($targetW / 2);
        $padding = imagecolorsforindex($im, imagecolorat($im, $cx, $dstY - 3)); // just above the graphic
        $edge = imagecolorsforindex($im, imagecolorat($im, $cx, $dstY + 3));     // graphic's top background band
        imagedestroy($im);

        // Seamless: the padding continues the graphic's edge (both ~color1). The old
        // re-paint approach put an intermediate gradient color in the padding (~250 delta).
        $delta = abs($padding['red'] - $edge['red']) + abs($padding['green'] - $edge['green']) + abs($padding['blue'] - $edge['blue']);
        $this->assertLessThan(40, $delta, "letterbox seam: padding (rgb {$padding['red']},{$padding['green']},{$padding['blue']}) does not blend with graphic edge (rgb {$edge['red']},{$edge['green']},{$edge['blue']})");
    }
}
