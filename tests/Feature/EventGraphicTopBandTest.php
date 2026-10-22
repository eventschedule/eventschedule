<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\designs\GridDesign;
use App\Services\designs\ListDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The top of the events graphic: the header image runs edge to edge with no padding, and
 * the schedule's profile image (the 'profile_image' option) sits centered below it with
 * padding above and below, ahead of the events. Pictures are handed to the design by
 * overriding fetchImageData(), so nothing is read from disk or the network.
 */
class EventGraphicTopBandTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const HEADER_URL = 'https://images.example.test/header.png';

    public function test_the_header_image_runs_edge_to_edge_from_the_top(): void
    {
        $role = $this->schedule();
        $im = $this->render($role, ['header_image_url' => self::HEADER_URL], header: $this->solid(1200, 150, [220, 20, 20]));

        foreach ([[0, 0], [imagesx($im) - 1, 0], [0, 50]] as [$x, $y]) {
            $this->assertNear([220, 20, 20], $this->rgb($im, $x, $y), "header pixel at {$x},{$y}");
        }
    }

    public function test_a_tall_header_is_cropped_around_its_middle_not_squashed(): void
    {
        $role = $this->schedule();
        // Green above and below, red across the middle third: squashed into the 200px cap the
        // top row would be green; cropped around the middle it is red.
        $header = $this->solid(300, 900, [20, 160, 20]);
        imagefilledrectangle($header, 0, 300, 299, 599, imagecolorallocate($header, 220, 20, 20));

        $im = $this->render($role, ['header_image_url' => self::HEADER_URL], header: $header);

        $this->assertNear([220, 20, 20], $this->rgb($im, 5, 0));
        $this->assertNear([220, 20, 20], $this->rgb($im, 5, 190));
    }

    public function test_the_profile_image_is_centered_below_the_header_with_padding(): void
    {
        $role = $this->schedule(['profile_image_url' => 'profile_graphic_test.png']);
        $header = $this->solid(1200, 150, [220, 20, 20]);
        $profile = $this->solid(200, 200, [20, 20, 220]);

        $without = $this->design($role, ['header_image_url' => self::HEADER_URL], $header, $profile);
        $with = $this->design($role, ['header_image_url' => self::HEADER_URL, 'profile_image' => true], $header, $profile);

        // 120px image plus 24px above and below, in place of the 18px top margin.
        $this->assertSame($without->getHeight() + 120 + 48 - 18, $with->getHeight());

        $im = imagecreatefromstring($with->generate());
        $headerHeight = (int) min(200, (int) (150 * $with->getWidth() / 1200));
        $centerX = (int) ($with->getWidth() / 2);

        $this->assertNear([20, 20, 220], $this->rgb($im, $centerX, $headerHeight + 24 + 60), 'profile image centre');
        $this->assertNotNear([20, 20, 220], $this->rgb($im, $centerX, $headerHeight + 12), 'padding above the profile image');
        $this->assertNotNear([20, 20, 220], $this->rgb($im, $centerX, $headerHeight + 24 + 120 + 12), 'padding below the profile image');
        // A square picture keeps its shape: 120 wide, centered.
        $this->assertNear([20, 20, 220], $this->rgb($im, $centerX - 55, $headerHeight + 24 + 60));
        $this->assertNotNear([20, 20, 220], $this->rgb($im, $centerX - 66, $headerHeight + 24 + 60));
    }

    public function test_the_profile_image_works_without_a_header(): void
    {
        $role = $this->schedule(['profile_image_url' => 'profile_graphic_test.png']);
        $profile = $this->solid(200, 200, [20, 20, 220]);

        $design = $this->design($role, ['profile_image' => true], null, $profile);
        $im = imagecreatefromstring($design->generate());

        $this->assertNear([20, 20, 220], $this->rgb($im, (int) ($design->getWidth() / 2), 24 + 60));
        $this->assertNotNear([20, 20, 220], $this->rgb($im, (int) ($design->getWidth() / 2), 12));
    }

    public function test_the_option_does_nothing_for_a_schedule_without_a_profile_image(): void
    {
        $role = $this->schedule();

        $off = $this->design($role, [], null, null);
        $on = $this->design($role, ['profile_image' => true], null, null);

        $this->assertSame($off->getHeight(), $on->getHeight());
    }

    public function test_the_letterbox_beside_the_header_is_not_smeared_from_it(): void
    {
        $role = $this->schedule(['background' => 'solid', 'background_color' => '#eeeeee']);
        $events = collect();
        for ($i = 0; $i < 8; $i++) {
            $events->push($this->createEvent($role, ['name' => "Night {$i}", 'starts_at' => sprintf('2026-11-%02d 20:00:00', $i + 1)]));
        }

        // A tall list in a square is letterboxed left and right, beside the header band too.
        TopBandListDesign::$pictures = [self::HEADER_URL => $this->png($this->solid(1200, 150, [220, 20, 20]))];
        $design = new TopBandListDesign($role, $events, false, ['header_image_url' => self::HEADER_URL, 'image_size' => 'square']);

        $im = imagecreatefromstring($design->generate());
        $this->assertSame(1080, imagesx($im));

        // The header itself is drawn, and the pad beside it is not a smear of its edge.
        $this->assertNear([220, 20, 20], $this->rgb($im, 540, 2), 'the header');
        $this->assertNotNear([220, 20, 20], $this->rgb($im, 2, 2), 'letterbox beside the header');
    }

    public function test_the_letterbox_above_the_header_is_not_smeared_from_it(): void
    {
        $role = $this->schedule();
        $events = collect();
        for ($i = 0; $i < 3; $i++) {
            $events->push($this->createEvent($role, ['name' => "Night {$i}", 'starts_at' => sprintf('2026-11-%02d 20:00:00', $i + 1)]));
        }
        $header = $this->solid(1200, 150, [220, 20, 20]);

        // A wide grid in a Story is letterboxed above and below; the pad above sits on the header.
        $native = $this->design($role, ['header_image_url' => self::HEADER_URL], $header, null, $events);
        $scale = min(1080 / $native->getWidth(), 1920 / $native->getHeight());
        $dstY = (int) round((1920 - (int) round($native->getHeight() * $scale)) / 2);
        $this->assertGreaterThan(10, $dstY, 'Story should letterbox this grid above and below');

        $im = imagecreatefromstring($this->design($role, ['header_image_url' => self::HEADER_URL, 'image_size' => 'story'], $header, null, $events)->generate());
        $this->assertNear([220, 20, 20], $this->rgb($im, 540, $dstY + 3), 'the header');
        $this->assertNotNear([220, 20, 20], $this->rgb($im, 540, $dstY - 5), 'letterbox above the header');
    }

    public function test_header_image_then_profile_image_then_header_text_then_events(): void
    {
        $role = $this->schedule(['profile_image_url' => 'profile_graphic_test.png']);
        $header = $this->solid(1200, 150, [220, 20, 20]);
        $profile = $this->solid(200, 200, [20, 20, 220]);

        $plain = $this->design($role, ['header_image_url' => self::HEADER_URL, 'profile_image' => true], $header, $profile);
        $titled = $this->design($role, ['header_image_url' => self::HEADER_URL, 'profile_image' => true, 'header_text' => 'This month'], $header, $profile);

        // The headline band is added below the profile image, which stays where it was.
        $this->assertGreaterThan($plain->getHeight(), $titled->getHeight());
        $im = imagecreatefromstring($titled->generate());
        $headerHeight = (int) min(200, (int) (150 * $titled->getWidth() / 1200));
        $centerX = (int) ($titled->getWidth() / 2);

        $this->assertNear([220, 20, 20], $this->rgb($im, 5, 5), 'header image at the top');
        $this->assertNear([20, 20, 220], $this->rgb($im, $centerX, $headerHeight + 24 + 60), 'profile image below it');

        // The headline's ink (anything off the plain #eeeeee page) lies in its band below the profile image.
        $bandTop = $headerHeight + 24 + 120 + 24;
        $inked = false;
        for ($y = $bandTop; $y < $bandTop + 130 && ! $inked; $y++) {
            for ($x = 18; $x < $titled->getWidth() - 18; $x += 2) {
                if (array_sum(array_map(fn ($c) => abs($c - 238), $this->rgb($im, $x, $y))) > 40) {
                    $inked = true;
                    break;
                }
            }
        }
        $this->assertTrue($inked, 'header text drawn in its band below the profile image');
    }

    private function schedule(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['background' => 'solid', 'background_color' => '#eeeeee']);
    }

    private function design(Role $role, array $options, ?\GdImage $header, ?\GdImage $profile, ?Collection $events = null): GridDesign
    {
        $events ??= collect([$this->createEvent($role, ['name' => 'Opening night', 'starts_at' => '2026-11-01 20:00:00'])]);

        $pictures = [];
        if ($header) {
            $pictures[self::HEADER_URL] = $this->png($header);
        }
        if ($profile) {
            $pictures[$role->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH)] = $this->png($profile);
        }

        TopBandGridDesign::$pictures = $pictures;

        return new TopBandGridDesign($role, $events, false, $options);
    }

    private function render(Role $role, array $options, ?\GdImage $header = null, ?\GdImage $profile = null): \GdImage
    {
        return imagecreatefromstring($this->design($role, $options, $header, $profile)->generate());
    }

    private function solid(int $width, int $height, array $rgb): \GdImage
    {
        $im = imagecreatetruecolor($width, $height);
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, imagecolorallocate($im, ...$rgb));

        return $im;
    }

    private function png(\GdImage $im): string
    {
        ob_start();
        imagepng($im);

        return ob_get_clean();
    }

    private function rgb(\GdImage $im, int $x, int $y): array
    {
        $c = imagecolorsforindex($im, imagecolorat($im, $x, $y));

        return [$c['red'], $c['green'], $c['blue']];
    }

    private function assertNear(array $expected, array $actual, string $message = ''): void
    {
        $delta = array_sum(array_map(fn ($a, $b) => abs($a - $b), $expected, $actual));
        $this->assertLessThan(30, $delta, $message.': got rgb('.implode(',', $actual).')');
    }

    private function assertNotNear(array $expected, array $actual, string $message = ''): void
    {
        $delta = array_sum(array_map(fn ($a, $b) => abs($a - $b), $expected, $actual));
        $this->assertGreaterThan(60, $delta, $message.': got rgb('.implode(',', $actual).')');
    }
}

class TopBandGridDesign extends GridDesign
{
    public static array $pictures = [];

    protected function fetchImageData(string $url, int $timeout = 30): string|false
    {
        return self::$pictures[$url] ?? parent::fetchImageData($url, $timeout);
    }
}

class TopBandListDesign extends ListDesign
{
    public static array $pictures = [];

    protected function fetchImageData(string $url, int $timeout = 30): string|false
    {
        return self::$pictures[$url] ?? parent::fetchImageData($url, $timeout);
    }
}
