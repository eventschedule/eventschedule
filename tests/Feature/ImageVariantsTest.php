<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Jobs\GenerateEventImageVariants;
use App\Models\BackupJob;
use App\Models\Event;
use App\Services\BackupService;
use App\Utils\ImageUtils;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The flyer derivative pipeline: helper, model accessor, generation hook, backfill command and
 * the homepage's use of all four.
 *
 * The bug this exists to prevent: the homepage poster wall shipped 18MB of ORIGINAL flyers into
 * 96px and 208px slots, all eager, and mobile LCP was 28.7 s.
 */
class ImageVariantsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The real queue manager, so one test can put the `sync` connection back. */
    private $realQueue;

    protected function setUp(): void
    {
        parent::setUp();

        // Every test here writes to the storage disk. Faking the DEFAULT disk keeps
        // ImageUtils::storagePathFor()'s local/public branch exercised as written.
        Storage::fake();

        // The generation hook dispatches ->afterCommit(), and Laravel's TESTING transactions
        // manager runs after-commit callbacks immediately while only RefreshDatabase's wrapping
        // transaction is open - so on the `sync` queue the job would fire inside every
        // createEvent() below, before the fixture's file exists, and stamp
        // {"w480": null, "skipped": "missing"} on rows these tests need to start empty. The job
        // itself is exercised by calling handle() directly.
        $this->realQueue = $this->app->make('queue');
        Queue::fake();

        // The wall cache is off by default under test (phpunit.xml pins the TTL to 0); the two
        // tests that exercise it turn it on. Flushed at both ends so neither can leak.
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    /** A real PNG of the given size, stored under the disk rule every flyer write path uses. */
    private function storeFlyer(string $filename, int $width = 1600, int $height = 2133): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 200));
        // A second block so the encoder has something to compress and the result is not a
        // degenerate one-colour file.
        imagefilledrectangle($image, 0, 0, (int) ($width / 2), (int) ($height / 3), imagecolorallocate($image, 240, 200, 30));

        $temp = tempnam(sys_get_temp_dir(), 'fixture_').'.png';
        imagepng($image, $temp);
        imagedestroy($image);

        Storage::put(ImageUtils::storagePathFor($filename), file_get_contents($temp));
        @unlink($temp);

        return $filename;
    }

    /**
     * JPEG bytes carrying an EXIF APP1 block that declares $orientation (0 for no EXIF at all).
     *
     * Hand-assembled rather than shipped as a binary fixture: it is 26 bytes of TIFF and it keeps
     * the tag under test visible in the diff. Big-endian ("MM") header, IFD0 at offset 8, one
     * entry - tag 0x0112 (Orientation), type 3 (SHORT), count 1 - and no IFD1.
     */
    private function jpegBytes(int $orientation, int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 200));
        imagefilledrectangle($image, 0, 0, (int) ($width / 2), (int) ($height / 3), imagecolorallocate($image, 240, 200, 30));

        $temp = tempnam(sys_get_temp_dir(), 'fixture_').'.jpg';
        imagejpeg($image, $temp, 90);
        imagedestroy($image);

        $bytes = file_get_contents($temp);
        @unlink($temp);

        if ($orientation < 1) {
            return $bytes;
        }

        $tiff = "MM\x00\x2a\x00\x00\x00\x08"
            ."\x00\x01"
            ."\x01\x12\x00\x03\x00\x00\x00\x01".pack('n', $orientation)."\x00\x00"
            ."\x00\x00\x00\x00";

        // The length field counts itself (2) plus "Exif\0\0" (6) plus the TIFF block.
        $app1 = "\xff\xe1".pack('n', strlen($tiff) + 8)."Exif\x00\x00".$tiff;

        // Immediately after SOI, where a camera writes it.
        return substr($bytes, 0, 2).$app1.substr($bytes, 2);
    }

    private function storeJpeg(string $filename, int $orientation, int $width, int $height): void
    {
        Storage::put(ImageUtils::storagePathFor($filename), $this->jpegBytes($orientation, $width, $height));
    }

    /** A file whose PNG header declares huge dimensions, without allocating them. */
    private function storeOversizedHeader(string $filename, int $width, int $height): void
    {
        $ihdr = pack('NNCCCCC', $width, $height, 8, 6, 0, 0, 0);
        $chunk = pack('N', strlen($ihdr)).'IHDR'.$ihdr.pack('N', crc32('IHDR'.$ihdr));

        Storage::put(ImageUtils::storagePathFor($filename), "\x89PNG\r\n\x1a\n".$chunk);
    }

    private function variantSize(string $filename): array
    {
        $bytes = Storage::get(ImageUtils::storagePathFor($filename));
        $this->assertNotNull($bytes, "Derivative {$filename} was not written");

        $info = getimagesizefromstring($bytes);
        $this->assertNotFalse($info, "Derivative {$filename} is not a readable image");

        return [$info[0], $info[1], $info[2]];
    }

    /**
     * Put a misbehaving disk in front of the faked one.
     *
     * S3 through Flysystem swallows its own exceptions unless the disk sets `throw` (do_spaces
     * does not), so a network failure reaches the helper as a null stream or a false write and is
     * otherwise indistinguishable from a real answer. There is no way to provoke that from the
     * local fake, so the two methods that can lie are overridden directly.
     */
    private function swapDisk(FilesystemAdapter $disk): void
    {
        Storage::set(config('filesystems.default'), $disk);
    }

    private function diskThatCannotRead(): FilesystemAdapter
    {
        $fake = Storage::disk();

        return new class($fake->getDriver(), $fake->getAdapter(), $fake->getConfig()) extends FilesystemAdapter
        {
            public function readStream($path)
            {
                return null;
            }
        };
    }

    private function diskThatCannotWrite(): FilesystemAdapter
    {
        $fake = Storage::disk();

        return new class($fake->getDriver(), $fake->getAdapter(), $fake->getConfig()) extends FilesystemAdapter
        {
            public function put($path, $contents, $options = [])
            {
                return false;
            }
        };
    }

    private function diskThatThrows(): FilesystemAdapter
    {
        $fake = Storage::disk();

        return new class($fake->getDriver(), $fake->getAdapter(), $fake->getConfig()) extends FilesystemAdapter
        {
            public function readStream($path)
            {
                throw new \RuntimeException('the object store hung up');
            }
        };
    }

    /** Undo setUp()'s Queue::fake() so ->afterCommit() dispatches run inline, as on selfhost. */
    private function useSyncQueue(): void
    {
        Queue::swap($this->realQueue);
    }

    // ---------------------------------------------------------------- helper

    public function test_helper_writes_a_480_wide_webp_beside_the_original(): void
    {
        $this->storeFlyer('flyer_abc123.png');

        $result = ImageUtils::generateStoredVariant('flyer_abc123.png');

        $this->assertTrue($result['ok'], 'Variant generation failed: '.($result['reason'] ?? ''));
        $this->assertSame('flyer_abc123_w480.webp', $result['filename']);
        Storage::assertExists('public/flyer_abc123_w480.webp');

        [$width, $height, $type] = $this->variantSize('flyer_abc123_w480.webp');
        $this->assertSame(480, $width);
        // 1600x2133 scaled to 480 wide keeps the 3:4 aspect.
        $this->assertSame(640, $height);
        $this->assertSame(IMAGETYPE_WEBP, $type);
    }

    public function test_one_pass_writes_every_configured_width(): void
    {
        $this->storeFlyer('flyer_abc123.png');

        $results = ImageUtils::generateStoredVariants('flyer_abc123.png');

        $this->assertSame([480, 960], ImageUtils::VARIANT_WIDTHS, 'The card srcsets are written against this list');
        $this->assertSame([480, 960], array_keys($results));

        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $this->assertTrue($results[$width]['ok'], "Width {$width} failed: ".($results[$width]['reason'] ?? ''));
            $this->assertSame("flyer_abc123_w{$width}.webp", $results[$width]['filename']);

            [$actualWidth] = $this->variantSize("flyer_abc123_w{$width}.webp");
            $this->assertSame($width, $actualWidth);
        }
    }

    public function test_the_derivative_is_much_smaller_than_the_original(): void
    {
        $this->storeFlyer('flyer_big.png');
        $originalBytes = strlen(Storage::get('public/flyer_big.png'));

        $result = ImageUtils::generateStoredVariant('flyer_big.png');

        $this->assertTrue($result['ok']);
        $this->assertLessThan(
            $originalBytes,
            strlen(Storage::get('public/flyer_big_w480.webp')),
            'The whole point of the derivative is that it is smaller than the original'
        );
    }

    public function test_the_derivative_name_is_deterministic(): void
    {
        $this->assertSame('flyer_abc123_w480.webp', ImageUtils::variantFilename('flyer_abc123.png'));
        $this->assertSame('flyer_abc123_w480.webp', ImageUtils::variantFilename('flyer_abc123.jpeg'));
        $this->assertSame('flyer_abc123_w200.webp', ImageUtils::variantFilename('flyer_abc123.png', 200));

        // Derived from the original's name, which is what makes the clone path safe: a clone
        // copies the bytes to a NEW filename, so deleting the source's derivatives cannot reach
        // the copy's.
        $this->assertNotSame(
            ImageUtils::variantFilename('flyer_abc123.png'),
            ImageUtils::variantFilename('flyer_clone9.png')
        );

        // Regenerating overwrites rather than orphaning.
        $this->storeFlyer('flyer_twice.png', 800, 800);
        $first = ImageUtils::generateStoredVariant('flyer_twice.png');
        $second = ImageUtils::generateStoredVariant('flyer_twice.png');

        $this->assertSame($first['filename'], $second['filename']);
    }

    public function test_helper_refuses_an_image_over_twelve_megapixels(): void
    {
        // 5000x4000 = 20MP. GD would need ~80MB for the source canvas alone.
        $this->storeOversizedHeader('flyer_huge.png', 5000, 4000);

        $result = ImageUtils::generateStoredVariant('flyer_huge.png');

        $this->assertFalse($result['ok']);
        $this->assertSame('too_large', $result['reason']);
        Storage::assertMissing('public/flyer_huge_w480.webp');
    }

    public function test_helper_skips_demo_and_external_images(): void
    {
        $this->assertSame('demo', ImageUtils::generateStoredVariant('demo_flyer_jazz.webp')['reason']);
        $this->assertSame('external', ImageUtils::generateStoredVariant('https://example.com/a.png')['reason']);
    }

    public function test_helper_reports_a_missing_or_unreadable_original(): void
    {
        $this->assertSame('missing', ImageUtils::generateStoredVariant('flyer_gone.png')['reason']);

        Storage::put(ImageUtils::storagePathFor('flyer_notanimage.png'), 'this is not an image');
        $this->assertSame('unreadable', ImageUtils::generateStoredVariant('flyer_notanimage.png')['reason']);
    }

    public function test_helper_never_upscales_a_small_source(): void
    {
        $this->storeFlyer('flyer_small.png', 200, 300);

        $result = ImageUtils::generateStoredVariant('flyer_small.png');

        $this->assertTrue($result['ok']);
        // The name still carries the REQUESTED width so the recorded key stays predictable.
        $this->assertSame('flyer_small_w480.webp', $result['filename']);
        [$width, $height] = $this->variantSize('flyer_small_w480.webp');
        $this->assertSame(200, $width);
        $this->assertSame(300, $height);
    }

    public function test_the_variant_url_resolves_exactly_like_the_original(): void
    {
        $this->assertSame(
            ImageUtils::storedUrl('flyer_abc123_w480.webp'),
            ImageUtils::variantUrl('flyer_abc123_w480.webp')
        );
        $this->assertSame(url('/storage/flyer_abc123_w480.webp'), ImageUtils::variantUrl('flyer_abc123_w480.webp'));
    }

    // ------------------------------------------------------ EXIF orientation

    public function test_the_derivative_honours_the_exif_orientation_tag(): void
    {
        // What a phone held upright writes: a landscape sensor frame plus "turn me a quarter
        // clockwise". GD ignores the tag and WebP cannot carry it, so without the fix the
        // thumbnail is permanently on its side while the event page shows it upright.
        $this->storeJpeg('flyer_upright.jpg', 6, 600, 400);

        $result = ImageUtils::generateStoredVariant('flyer_upright.jpg');

        $this->assertTrue($result['ok'], 'Variant generation failed: '.($result['reason'] ?? ''));

        [$width, $height] = $this->variantSize('flyer_upright_w480.webp');
        // The rotated source is 400x600, and 400 is under the 480 target, so it is not upscaled.
        $this->assertSame(400, $width);
        $this->assertSame(600, $height);
        $this->assertGreaterThan($width, $height, 'An Orientation 6 photo must come out portrait');
    }

    public function test_a_jpeg_without_an_orientation_tag_is_left_alone(): void
    {
        $this->storeJpeg('flyer_plain.jpg', 0, 600, 400);

        $this->assertTrue(ImageUtils::generateStoredVariant('flyer_plain.jpg')['ok']);

        [$width, $height] = $this->variantSize('flyer_plain_w480.webp');
        $this->assertSame(480, $width);
        $this->assertSame(320, $height);
    }

    public function test_the_upload_resizer_honours_the_exif_orientation_tag(): void
    {
        // Over the 2000px cap, so resizeImageToMax() re-encodes - and re-encoding is exactly what
        // drops the tag, which is why the rotation has to be baked in on the way through.
        $path = tempnam(sys_get_temp_dir(), 'resize_').'.jpg';
        file_put_contents($path, $this->jpegBytes(6, 2400, 1600));

        try {
            $this->assertTrue(ImageUtils::resizeImageToMax($path, 2000));

            [$width, $height] = getimagesize($path);
            // 2400x1600 turned upright is 1600x2400, scaled to a 2000px longest side.
            $this->assertSame(1333, $width);
            $this->assertSame(2000, $height);
        } finally {
            @unlink($path);
        }
    }

    // --------------------------------------------- transient vs deterministic

    public function test_the_reason_vocabulary_is_split_and_disjoint(): void
    {
        $this->assertEmpty(
            array_intersect(ImageUtils::VARIANT_TRANSIENT_REASONS, ImageUtils::VARIANT_DETERMINISTIC_REASONS),
            'A reason cannot be both retryable and final'
        );

        foreach (ImageUtils::VARIANT_DETERMINISTIC_REASONS as $reason) {
            $this->assertFalse(ImageUtils::isTransientVariantReason($reason), "{$reason} must not send the queue round again");
        }

        foreach (ImageUtils::VARIANT_TRANSIENT_REASONS as $reason) {
            $this->assertTrue(ImageUtils::isTransientVariantReason($reason));
        }

        $this->assertFalse(ImageUtils::isTransientVariantReason(null));
    }

    public function test_a_disk_that_will_not_hand_the_file_over_is_transient_not_missing(): void
    {
        $this->storeFlyer('flyer_abc123.png', 400, 500);
        $this->swapDisk($this->diskThatCannotRead());

        $result = ImageUtils::generateStoredVariant('flyer_abc123.png');

        $this->assertFalse($result['ok']);
        // The file EXISTS. Calling this 'missing' is what filed an S3 blip as permanent.
        $this->assertSame('read_failed', $result['reason']);
        $this->assertTrue(ImageUtils::isTransientVariantReason($result['reason']));
    }

    public function test_a_disk_that_will_not_take_the_derivative_is_transient(): void
    {
        $this->storeFlyer('flyer_abc123.png', 400, 500);
        $this->swapDisk($this->diskThatCannotWrite());

        $result = ImageUtils::generateStoredVariant('flyer_abc123.png');

        $this->assertFalse($result['ok']);
        $this->assertSame('write_failed', $result['reason']);
        $this->assertTrue(ImageUtils::isTransientVariantReason($result['reason']));
    }

    // ----------------------------------------------------------- model reads

    public function test_get_image_url_returns_the_variant_only_when_one_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        // No variant recorded yet: the original, at every width.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(480));
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(960));
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl());

        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp']);

        $this->assertStringEndsWith('flyer_abc123_w480.webp', $event->getImageUrl(480));
        $this->assertStringEndsWith('flyer_abc123_w960.webp', $event->getImageUrl(960));
        // No width asked for means no derivative: full-size consumers keep the original.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl());
        // A width with no recorded derivative falls back rather than 404ing.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(1200));
    }

    public function test_the_srcset_needs_every_width_before_it_offers_any(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        $this->assertNull($event->imageSrcset(), 'No derivatives: the card must fall back to a plain src');

        // One width alone is not a srcset - it would tell a 2x screen that 480 is the best there
        // is and stop it reaching for the (sharper) original.
        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);
        $this->assertNull($event->imageSrcset());

        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp']);
        $this->assertSame(
            url('/storage/flyer_abc123_w480.webp').' 480w, '.url('/storage/flyer_abc123_w960.webp').' 960w',
            $event->imageSrcset()
        );
    }

    public function test_an_event_with_no_flyer_has_no_srcset(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'demo_wall_venue.jpg']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);

        // The schedule photo fallback has no derivatives, so offering a srcset would 404.
        $this->assertNull($event->imageSrcset());
    }

    public function test_a_recorded_skip_is_not_mistaken_for_a_derivative(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        $event->recordImageVariants(['w480' => null, 'w960' => null, 'skipped' => 'too_large']);

        $this->assertNull($event->imageVariantFilename(480));
        $this->assertNull($event->imageSrcset());
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(480));
    }

    public function test_a_width_never_diverts_the_schedule_image_fallback(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'demo_wall_venue.jpg']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);

        // No flyer: the talent schedule's photo, which has no derivatives, at either width.
        $this->assertSame($event->getImageUrl(), $event->fresh()->getImageUrl(480));
        $this->assertStringContainsString('demo_wall_venue.jpg', (string) $event->getImageUrl(480));
    }

    public function test_recording_is_refused_when_the_flyer_changed_underneath(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        // What a slow job holds: a model whose flyer has since been replaced in the database.
        $stale = Event::find($event->id);
        Event::whereKey($event->id)->update(['flyer_image_url' => 'flyer_replaced.png']);

        $this->assertFalse($stale->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']));
        $this->assertNull($event->fresh()->image_variants);
    }

    // ------------------------------------------------------- derivative files

    public function test_replacing_the_flyer_clears_and_deletes_the_old_derivatives(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_old.png']);

        $this->storeFlyer('flyer_old.png', 400, 500);
        $this->storeFlyer('flyer_new.png', 400, 500);
        ImageUtils::generateStoredVariants('flyer_old.png');
        $event->recordImageVariants(['w480' => 'flyer_old_w480.webp', 'w960' => 'flyer_old_w960.webp']);

        Storage::assertExists('public/flyer_old_w480.webp');
        Storage::assertExists('public/flyer_old_w960.webp');

        $event->flyer_image_url = 'flyer_new.png';
        $event->save();

        $this->assertNull($event->fresh()->image_variants);
        $this->assertStringEndsWith('flyer_new.png', $event->fresh()->getImageUrl(480));

        // Every width of the outgoing flyer, and nothing else. The original itself belongs to the
        // caller (EventRepo deletes it), and the incoming flyer must not be touched.
        Storage::assertMissing('public/flyer_old_w480.webp');
        Storage::assertMissing('public/flyer_old_w960.webp');
        Storage::assertExists('public/flyer_new.png');
    }

    public function test_clearing_the_flyer_deletes_the_derivatives(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_old.png']);

        $this->storeFlyer('flyer_old.png', 400, 500);
        ImageUtils::generateStoredVariants('flyer_old.png');
        $event->recordImageVariants(['w480' => 'flyer_old_w480.webp', 'w960' => 'flyer_old_w960.webp']);

        $event->flyer_image_url = null;
        $event->save();

        Storage::assertMissing('public/flyer_old_w480.webp');
        Storage::assertMissing('public/flyer_old_w960.webp');
    }

    public function test_deleting_the_event_deletes_the_derivatives(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_old.png']);

        $this->storeFlyer('flyer_old.png', 400, 500);
        ImageUtils::generateStoredVariants('flyer_old.png');
        $event->recordImageVariants(['w480' => 'flyer_old_w480.webp', 'w960' => 'flyer_old_w960.webp']);

        $event->delete();

        Storage::assertMissing('public/flyer_old_w480.webp');
        Storage::assertMissing('public/flyer_old_w960.webp');
    }

    public function test_deleting_derivatives_ignores_demo_and_external_flyers(): void
    {
        // Neither has derivatives, and storagePathFor() would name a file that is not theirs.
        Storage::put(ImageUtils::storagePathFor('demo_flyer_jazz_w480.webp'), 'kept');

        ImageUtils::deleteStoredVariants('demo_flyer_jazz.webp');
        ImageUtils::deleteStoredVariants('https://example.com/a.png');
        ImageUtils::deleteStoredVariants('');

        Storage::assertExists('public/demo_flyer_jazz_w480.webp');
    }

    // ------------------------------------------------------- generation seam

    public function test_the_flyer_write_paths_queue_a_generation_job(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);

        Queue::assertNotPushed(GenerateEventImageVariants::class);

        // What every write path does: assign the stored filename, then save().
        $event->flyer_image_url = 'flyer_abc123.png';
        $event->save();

        Queue::assertPushed(GenerateEventImageVariants::class, 1);
        Queue::assertPushed(function (GenerateEventImageVariants $job) use ($event) {
            return $job->eventId === $event->id && $job->flyer === 'flyer_abc123.png';
        });
    }

    public function test_an_unrelated_save_queues_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        // Re-faked, which clears what the create above recorded.
        Queue::fake();

        $event->name = 'Autumn Session II';
        $event->save();

        Queue::assertNotPushed(GenerateEventImageVariants::class);
    }

    public function test_demo_flyers_and_cleared_flyers_queue_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'demo_flyer_jazz.webp']);

        $event->flyer_image_url = null;
        $event->save();

        Queue::assertNotPushed(GenerateEventImageVariants::class);
    }

    public function test_the_job_generates_and_records_every_width(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png');

        (new GenerateEventImageVariants($event->id, 'flyer_abc123.png'))->handle();

        Storage::assertExists('public/flyer_abc123_w480.webp');
        Storage::assertExists('public/flyer_abc123_w960.webp');
        $this->assertSame(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp'],
            $event->fresh()->image_variants
        );
    }

    public function test_the_job_bails_when_the_flyer_was_replaced_after_dispatch(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_new.png']);
        $this->storeFlyer('flyer_old.png');

        (new GenerateEventImageVariants($event->id, 'flyer_old.png'))->handle();

        Storage::assertMissing('public/flyer_old_w480.webp');
        $this->assertNull($event->fresh()->image_variants);
    }

    public function test_the_job_records_a_skip_reason_for_an_unusable_original(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 5000, 4000);

        // Deterministic, so the job returns normally rather than sending the queue round again.
        (new GenerateEventImageVariants($event->id, 'flyer_huge.png'))->handle();

        $this->assertSame(
            ['w480' => null, 'w960' => null, 'skipped' => 'too_large'],
            $event->fresh()->image_variants
        );
    }

    public function test_the_job_throws_on_a_transient_failure_where_a_retry_will_happen(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png', 400, 500);

        $this->swapDisk($this->diskThatCannotRead());

        // A real queue, so $tries means something. phpunit.xml pins QUEUE_CONNECTION=sync, and
        // the job reads the connection rather than assuming a worker - see willBeRetried().
        config(['queue.default' => 'database']);

        $threw = false;

        try {
            (new GenerateEventImageVariants($event->id, 'flyer_abc123.png'))->handle();
        } catch (\RuntimeException $e) {
            $threw = true;
            $this->assertStringContainsString('read_failed', $e->getMessage());
        }

        $this->assertTrue($threw, 'A transient storage failure must throw so $tries brings the job back');
        $this->assertNull(
            $event->fresh()->image_variants,
            'Recording a transient failure is what made $tries inert and hid the row from the backfill'
        );
    }

    /**
     * The same failure on the connection selfhost actually runs.
     *
     * SyncQueue::executeJob() has no retry loop - it catches once and handleException() rethrows
     * without consulting $tries - so throwing there is not a retry, it is a 500 propagating out
     * of the Event::save() that dispatched the job, on a row that has already been committed.
     */
    public function test_the_job_records_a_transient_failure_instead_of_throwing_on_the_sync_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png', 400, 500);

        $this->swapDisk($this->diskThatCannotRead());

        $this->assertSame('sync', config('queue.default'), 'phpunit.xml pins the selfhost default');

        (new GenerateEventImageVariants($event->id, 'flyer_abc123.png'))->handle();

        // The transient reason wins the `skipped` slot, because that is the value
        // BackfillImageVariants::baseQuery() re-selects on WITHOUT --retry-skipped.
        $this->assertSame(
            ['w480' => null, 'w960' => null, 'skipped' => 'read_failed'],
            $event->fresh()->image_variants,
        );
    }

    /**
     * The whole point of the branch above: the user's flyer upload must still succeed.
     *
     * Distinct from test_a_throwing_helper_never_breaks_the_flyer_save_on_the_sync_queue, which
     * covers the helper THROWING (caught by the job's own try/catch). This covers the helper
     * RETURNING a transient reason, which is the path that used to throw on purpose.
     */
    public function test_a_transient_disk_failure_never_breaks_the_flyer_save_on_the_sync_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);
        $this->storeFlyer('flyer_boom.png', 400, 500);

        $this->swapDisk($this->diskThatCannotWrite());
        $this->useSyncQueue();

        $event->flyer_image_url = 'flyer_boom.png';
        $event->save();

        $fresh = $event->fresh();
        $this->assertSame('flyer_boom.png', $fresh->getAttributes()['flyer_image_url'], 'The flyer save must stand');
        $this->assertSame(['w480' => null, 'w960' => null, 'skipped' => 'write_failed'], $fresh->image_variants);
    }

    public function test_a_throwing_helper_never_breaks_the_flyer_save_on_the_sync_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);
        $this->storeFlyer('flyer_boom.png', 400, 500);

        // QUEUE_CONNECTION=sync is the selfhost default, and SyncQueue rethrows, so the job runs
        // INSIDE the save() below: an escaping Throwable (the live one being `Call to undefined
        // function imagewebp()` on a GD without WebP) turned a successful flyer upload into a 500.
        $this->swapDisk($this->diskThatThrows());
        $this->useSyncQueue();

        $event->flyer_image_url = 'flyer_boom.png';
        $event->save();

        $fresh = $event->fresh();
        $this->assertSame('flyer_boom.png', $fresh->getAttributes()['flyer_image_url'], 'The flyer save must stand');
        $this->assertSame(['w480' => null, 'w960' => null, 'skipped' => 'failed'], $fresh->image_variants);
    }

    // ---------------------------------------------------------- backfill

    public function test_the_backfill_command_fills_a_flyer_and_is_idempotent(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png');

        $this->assertNull($event->fresh()->image_variants, 'The fixture must start with no derivative');

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);

        Storage::assertExists('public/flyer_abc123_w480.webp');
        Storage::assertExists('public/flyer_abc123_w960.webp');
        $this->assertSame(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp'],
            $event->fresh()->image_variants
        );

        // Second run: the row is filtered out by the query, so nothing is even read.
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_backfill_command_fills_in_a_width_added_later(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png', 600, 800);

        // What the pipeline recorded while 480 was the only width. "Already done" has to mean
        // EVERY width, or these rows are invisible to the run that is supposed to fix them.
        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);

        Storage::assertExists('public/flyer_abc123_w960.webp');
        $this->assertSame(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp'],
            $event->fresh()->image_variants
        );
    }

    public function test_the_backfill_command_records_and_then_respects_a_skip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 5000, 4000);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertSame(
            ['w480' => null, 'w960' => null, 'skipped' => 'too_large'],
            $event->fresh()->image_variants
        );

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());

        // A recorded skip is reconsidered only on request. Give it a usable original this time.
        Storage::delete('public/flyer_huge.png');
        $this->storeFlyer('flyer_huge.png', 600, 800);
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true, '--retry-skipped' => true]);

        $this->assertSame(
            ['w480' => 'flyer_huge_w480.webp', 'w960' => 'flyer_huge_w960.webp'],
            $event->fresh()->image_variants
        );
    }

    public function test_the_backfill_command_retries_a_transient_skip_unasked(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png', 600, 800);

        // What a run during an object-storage outage leaves behind.
        $event->recordImageVariants(['w480' => null, 'w960' => null, 'skipped' => 'write_failed']);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);

        $this->assertSame(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp'],
            $event->fresh()->image_variants,
            'A transient skip must be re-attempted without --retry-skipped'
        );

        // A deterministic one in the same position still waits to be asked.
        $event->recordImageVariants(['w480' => null, 'w960' => null, 'skipped' => 'too_large']);
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_backfill_command_leaves_demo_flyers_and_past_events_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        $demo = $this->createEvent($role, ['name' => 'Demo Night Out', 'flyer_image_url' => 'demo_flyer_jazz.webp']);
        $past = $this->createEvent($role, [
            'name' => 'Last Winter Session',
            'starts_at' => now()->subMonths(2)->format('Y-m-d H:i:s'),
            'flyer_image_url' => 'flyer_past.png',
        ]);
        $this->storeFlyer('flyer_past.png', 600, 800);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);

        $this->assertNull($demo->fresh()->image_variants, 'demo_ flyers are already small WebPs');
        $this->assertNull($past->fresh()->image_variants, '--upcoming-only must stop before past events');

        Artisan::call('images:backfill-variants');
        $this->assertSame(
            ['w480' => 'flyer_past_w480.webp', 'w960' => 'flyer_past_w960.webp'],
            $past->fresh()->image_variants
        );
    }

    public function test_the_backfill_dry_run_writes_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png');

        Artisan::call('images:backfill-variants', ['--dry-run' => true]);

        $output = Artisan::output();
        $this->assertStringContainsString('flyer_abc123_w480.webp', $output);
        $this->assertStringContainsString('flyer_abc123_w960.webp', $output);
        Storage::assertMissing('public/flyer_abc123_w480.webp');
        Storage::assertMissing('public/flyer_abc123_w960.webp');
        $this->assertNull($event->fresh()->image_variants);
    }

    public function test_the_backfill_command_honours_a_limit(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        foreach (range(1, 3) as $i) {
            $this->createEvent($role, ['name' => 'Autumn Session '.$i, 'flyer_image_url' => 'flyer_lim'.$i.'.png']);
            $this->storeFlyer('flyer_lim'.$i.'.png', 400, 500);
        }

        Artisan::call('images:backfill-variants', ['--limit' => 2]);

        $this->assertStringContainsString('Processed: 2', Artisan::output());
        $this->assertSame(2, Event::whereNotNull('image_variants')->count());
    }

    // ---------------------------------------------------------------- backup

    /**
     * A restored install holds none of the derivative FILES, so it must hold none of the
     * filenames either. Both directions of BackupService walk getFillable(), and this column is
     * deliberately not fillable - adding it there would make every restored card request a
     * `_w480.webp` that was never in the archive.
     */
    public function test_the_column_is_neither_exported_nor_restored(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);

        $service = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $service->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayNotHasKey('image_variants', $data['schedules'][0]['events'][0]);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $service->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Event::where('id', '!=', $event->id)->latest('id')->firstOrFail();
        $this->assertNull($restored->image_variants);
    }

    // ---------------------------------------------------------- the homepage

    /**
     * The eager budget for the whole document: 6 on the mobile strip plus 5 on the desktop wall
     * (row 0 of each of the five columns), both from marquee copy 0 only. It was 50 before this
     * phase - copy 0 of all 25 strip cards plus copy 0 of all 25 wall cards.
     *
     * Both breakpoints ship in the DOM at once (the strip is lg:hidden, the wall is
     * hidden lg:block), so the two eager sets are drawn from the SAME first cards: cards 0-5 on
     * the strip, cards 0-4 on the wall. The wall's set is a subset of the strip's, so whichever
     * breakpoint is displayed the browser fetches at most SIX distinct posters before first
     * paint - never eleven, and never a poster that is display:none.
     */
    private const MAX_EAGER_IMAGES = 11;

    public function test_the_homepage_stays_within_its_eager_image_budget(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        foreach (range(1, 12) as $i) {
            $this->createEvent($role, [
                'name' => 'Autumn Session '.$i,
                'flyer_image_url' => 'flyer_wall'.$i.'.png',
            ]);
        }

        $html = $this->get('/')->assertOk()->getContent();

        $eager = substr_count($html, 'loading="eager"');
        $this->assertGreaterThan(0, $eager, 'Something above the fold must still load eagerly');
        $this->assertLessThanOrEqual(
            self::MAX_EAGER_IMAGES,
            $eager,
            "The homepage eager-loads {$eager} images. The poster wall is the whole reason mobile LCP was 28.7 s."
        );

        // Both marquee copies of everything else are lazy AND deprioritised.
        $this->assertGreaterThan($eager, substr_count($html, 'loading="lazy"'));
    }

    public function test_the_homepage_wall_uses_the_derivative_when_one_exists(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        $withVariant = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $withVariant->recordImageVariants(['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp']);

        $withoutVariant = $this->createEvent($role, ['name' => 'Winter Session', 'flyer_image_url' => 'flyer_def456.png']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('flyer_abc123_w480.webp', $html);
        $this->assertStringNotContainsString('flyer_abc123.png', $html, 'The original must not be requested when a derivative exists');
        // No derivative recorded: the original is still served, so no card ever breaks.
        $this->assertStringContainsString('flyer_def456.png', $html);
        $this->assertNotNull($withoutVariant->id);
    }

    public function test_the_poster_card_offers_a_srcset_only_when_both_widths_exist(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        $sharp = $this->createEvent($role, ['name' => 'Sharp Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $sharp->recordImageVariants(['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp']);

        $this->createEvent($role, ['name' => 'Plain Session', 'flyer_image_url' => 'flyer_def456.png']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString(
            'srcset="'.url('/storage/flyer_abc123_w480.webp').' 480w, '.url('/storage/flyer_abc123_w960.webp')
                .' 960w" sizes="(min-width: 640px) 320px, 72vw"',
            $html,
            'The rail card is 320 CSS px, so it needs the 960 on a 2x screen'
        );

        // No derivatives at all: a plain src on the original, exactly as before this phase.
        $this->assertStringContainsString('src="'.url('/storage/flyer_def456.png').'"', $html);
        $this->assertStringNotContainsString('flyer_def456_w', $html);
    }

    public function test_the_homepage_preconnects_to_the_image_cdn_only_when_it_uses_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        $cdn = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com';

        // Local/public disk (every selfhost install): the flyers come off this very host, so a
        // preconnect would be pure waste - and pointing one at OUR CDN would be worse.
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString($cdn, $html);
        $this->assertStringNotContainsString('<link rel="preconnect" href="'.url('/').'"', $html);

        // Object storage behind a CDN (the hosted deploy): warm the socket.
        config(['app.hosted' => true, 'filesystems.default' => 'do_spaces']);
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="preconnect" href="'.$cdn.'">', $html);
    }

    // -------------------------------------------------------- the wall cache

    public function test_the_wall_is_cached_and_only_a_deliberate_bust_moves_it(): void
    {
        config(['marketing.wall_cache_seconds' => 60]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        $shown = $this->createEvent($role, ['name' => 'Cached Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $shown->recordImageVariants(['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp']);

        // Starts hidden, so the render that warms the cache does not contain it.
        $later = $this->createEvent($role, [
            'name' => 'Later Session',
            'flyer_image_url' => 'flyer_def456.png',
            'is_hidden_from_discovery' => true,
        ]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('flyer_abc123_w480.webp', $html);
        $this->assertStringNotContainsString('flyer_def456.png', $html);

        // Un-hide it AROUND Eloquent, so no model event fires and only the cache can be
        // responsible for what the next render shows.
        DB::table('events')->where('id', $later->id)->update(['is_hidden_from_discovery' => false]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('flyer_def456.png', $html, 'The second render must be served from the cache');
        $this->assertStringContainsString(
            'flyer_abc123_w480.webp',
            $html,
            'The cached models must still resolve their derivative, not just their id'
        );

        MarketingController::forgetWallCache();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('flyer_def456.png', $html);
    }

    public function test_the_admin_discovery_toggle_busts_the_wall_cache(): void
    {
        config(['marketing.wall_cache_seconds' => 60]);

        $admin = $this->createOwner(true);
        $role = $this->createRole($admin, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Toggle Session', 'flyer_image_url' => 'flyer_abc123.png']);

        $this->actingAs($admin);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('flyer_abc123.png', $html);

        // The Hide button is rendered ON this page, so a stale wall would contradict its own
        // flash message and flip the event back on the next click.
        $this->post(route('marketing.discovery.toggle', $event->hashedId()))->assertRedirect();
        $this->assertTrue((bool) $event->fresh()->is_hidden_from_discovery);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('flyer_abc123.png', $html, 'The toggle must bust the wall cache');
    }
}
