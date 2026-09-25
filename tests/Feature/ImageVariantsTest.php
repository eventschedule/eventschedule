<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Jobs\GenerateEventImageVariants;
use App\Jobs\GenerateRoleImageVariants;
use App\Models\BackupJob;
use App\Models\Event;
use App\Models\Role;
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

    /**
     * A 1x1 GIF: a header, a two-colour global table, a graphic control extension and one image.
     * Built in memory, like every fixture here.
     */
    private const ONE_PIXEL_GIF = '474946383961010001008000000000'.'00ffffff21f90401000000002c00000000010001000002024401003b';

    /** Temporary files tempImage() wrote, removed in tearDown(). */
    private array $tempImages = [];

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

        foreach ($this->tempImages as $path) {
            @unlink($path);
        }
        $this->tempImages = [];

        parent::tearDown();
    }

    /** A temporary file holding $bytes, for the helpers that read a path. Removed in tearDown(). */
    private function tempImage(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'variant_test_');
        file_put_contents($path, $bytes);
        $this->tempImages[] = $path;

        return $path;
    }

    /**
     * $gif with the blocks after its global colour table repeated to make $frames frames: a GIF
     * that moves. GD decodes it as its first frame, as it does every animated GIF.
     */
    private function animatedGif(string $gif, int $frames = 2): string
    {
        $packed = ord($gif[10]);
        $blocks = substr($gif, 13 + (($packed & 0x80) ? 3 * (2 << ($packed & 7)) : 0), -1);

        return substr($gif, 0, -1).str_repeat($blocks, $frames - 1).';';
    }

    /** A one-frame GIF of $width x $height, drawn by GD: no extension blocks, a larger table. */
    private function gifBytes(int $width, int $height): string
    {
        $image = imagecreate($width, $height);
        imagecolorallocate($image, 30, 90, 200);
        imagefilledrectangle($image, 0, 0, (int) ($width / 2), $height - 1, imagecolorallocate($image, 240, 200, 30));

        ob_start();
        imagegif($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    /** A still WebP, as GD encodes one: the simple format, a single VP8 chunk. */
    private function webpBytes(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 90, 200));

        ob_start();
        imagewebp($image, null, 80);
        imagedestroy($image);

        return ob_get_clean();
    }

    /**
     * An animated WebP: the extended format's VP8X chunk with the animation flag, an ANIM chunk,
     * and two ANMF frames that are each the still GD encodes.
     */
    private function animatedWebp(int $width, int $height): string
    {
        $le24 = fn (int $value) => substr(pack('V', $value), 0, 3);
        $chunk = fn (string $type, string $payload) => $type.pack('V', strlen($payload)).$payload.(strlen($payload) % 2 ? "\0" : '');

        // The still's own VP8 chunk, header and all, after an ANMF frame header.
        $frame = $chunk('ANMF', $le24(0).$le24(0).$le24($width - 1).$le24($height - 1).$le24(100)."\0".substr($this->webpBytes($width, $height), 12));
        $body = 'WEBP'
            .$chunk('VP8X', "\x02\0\0\0".$le24($width - 1).$le24($height - 1))
            .$chunk('ANIM', "\0\0\0\0\0\0")
            .$frame.$frame;

        return 'RIFF'.pack('V', strlen($body)).$body;
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

    /**
     * The head of a JPEG and nothing else: SOI, an EXIF block declaring $orientation, $padding
     * bytes of APP2 segments, a frame header declaring $width x $height, and a scan header. Enough
     * for getimagesize() and exif_read_data() - both stop at the scan - and far too little to
     * decode, which is the point: it can claim any size without allocating it.
     */
    private function jpegHeaderBytes(int $orientation, int $width, int $height, int $padding = 0): string
    {
        $tiff = "MM\x00\x2a\x00\x00\x00\x08"
            ."\x00\x01"
            ."\x01\x12\x00\x03\x00\x00\x00\x01".pack('n', $orientation)."\x00\x00"
            ."\x00\x00\x00\x00";
        $bytes = "\xff\xd8\xff\xe1".pack('n', strlen($tiff) + 8)."Exif\x00\x00".$tiff;

        while ($padding > 0) {
            $chunk = min($padding, 65000);
            $bytes .= "\xff\xe2".pack('n', $chunk + 2).str_repeat("\x00", $chunk);
            $padding -= $chunk;
        }

        $frame = "\x08".pack('n', $height).pack('n', $width)."\x03\x01\x22\x00\x02\x11\x01\x03\x11\x01";
        $bytes .= "\xff\xc0".pack('n', strlen($frame) + 2).$frame;

        return $bytes."\xff\xda\x00\x0c\x03\x01\x00\x02\x11\x03\x11\x00\x3f\x00\xff\xd9";
    }

    /** A file whose PNG header declares huge dimensions, without allocating them. */
    private function storeOversizedHeader(string $filename, int $width, int $height): void
    {
        $ihdr = pack('NNCCCCC', $width, $height, 8, 6, 0, 0, 0);
        $chunk = pack('N', strlen($ihdr)).'IHDR'.$ihdr.pack('N', crc32('IHDR'.$ihdr));

        Storage::put(ImageUtils::storagePathFor($filename), "\x89PNG\r\n\x1a\n".$chunk);
    }

    /**
     * A decode budget generous enough for $pixels against the CURRENT baseline.
     *
     * Every memory assertion in this class has to be written against memory_get_usage(true) as it
     * is right now, never against a hardcoded figure: canDecodePixels() measures the live baseline
     * internally, and a full-suite process carries hundreds of MB more than a lone run of this
     * file. Three tests here were originally pinned to a flat '512M', passed under --filter, and
     * failed in `php artisan test`.
     */
    private function budgetFor(int $pixels, int $slackMb = 64): int
    {
        return memory_get_usage(true) + ($pixels * 4 * 2) + (16 * 1024 * 1024) + ($slackMb * 1024 * 1024);
    }

    private function pinMemoryLimit(int $bytes): string
    {
        $limit = ((int) ceil($bytes / (1024 * 1024))).'M';
        ini_set('memory_limit', $limit);

        return $limit;
    }

    /** An on-disk (not Storage) PNG header declaring $width x $height, for resizeImageToMax(). */
    private function oversizedHeaderFile(int $width, int $height): string
    {
        $ihdr = pack('NNCCCCC', $width, $height, 8, 6, 0, 0, 0);
        $chunk = pack('N', strlen($ihdr)).'IHDR'.$ihdr.pack('N', crc32('IHDR'.$ihdr));

        $path = tempnam(sys_get_temp_dir(), 'variant_test_');
        file_put_contents($path, "\x89PNG\r\n\x1a\n".$chunk);

        return $path;
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

    /**
     * A variants column compared key by key, in any order. MySQL's JSON type stores an object's
     * keys sorted (by length, then bytes), so a column read back from the database does not keep
     * the order it was written in - `src`, the shortest key, always comes back first.
     */
    private function assertVariants(array $expected, ?array $actual, string $message = ''): void
    {
        $normalise = function (?array $value) use (&$normalise): ?array {
            if ($value === null) {
                return null;
            }

            ksort($value);

            return array_map(fn ($each) => is_array($each) ? $normalise($each) : $each, $value);
        };

        $this->assertSame($normalise($expected), $normalise($actual), $message);
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

    public function test_helper_refuses_an_image_past_the_absolute_ceiling(): void
    {
        // 12000x12000 = 144MP, past IMAGE_MAX_PIXELS_CEILING, so it is refused before the memory
        // budget is even consulted. That is what makes this deterministic: below the ceiling the
        // answer legitimately depends on the runner's memory_limit, which CI and a laptop
        // disagree about (often -1 versus 128M).
        $this->storeOversizedHeader('flyer_huge.png', 12000, 12000);

        $result = ImageUtils::generateStoredVariant('flyer_huge.png');

        $this->assertFalse($result['ok']);
        $this->assertSame('too_large', $result['reason']);
        $this->assertSame('12000x12000, 144MP', $result['detail']);
        Storage::assertMissing('public/flyer_huge_w480.webp');
    }

    public function test_a_size_within_the_memory_budget_is_no_longer_refused(): void
    {
        // 20MP was a flat refusal before the guard started measuring. Given a budget that covers
        // it, the pixel guard now lets it through and the decode fails on its own merits - these
        // header-only fixtures have no pixel data behind them.
        $this->storeOversizedHeader('flyer_big.png', 5000, 4000);

        $budget = $this->budgetFor(20_000_000);
        $previous = ini_get('memory_limit');
        $this->pinMemoryLimit($budget);

        try {
            $result = ImageUtils::generateStoredVariants('flyer_big.png', [480], $budget)[480];
        } finally {
            ini_set('memory_limit', $previous);
        }

        $this->assertFalse($result['ok']);
        $this->assertSame('unreadable', $result['reason']);
    }

    public function test_a_header_claiming_more_pixels_than_php_can_count_is_refused_not_fatal(): void
    {
        // getimagesize() reads PNG IHDR dimensions as raw unsigned 32-bit ints without checking
        // them, so this 33-byte file reports 4294967295x4294967295 and width*height overflows
        // PHP_INT_MAX into a FLOAT. canDecodePixels() therefore takes int|float: a typed `int`
        // throws a TypeError, which on the resizeImageToMax() path is uncaught and turns a flyer
        // upload into a 500, and on the backfill path records nothing so the row is re-selected
        // and re-reported forever.
        $this->assertFalse(ImageUtils::canDecodePixels(4294967295 * 4294967295));

        $this->storeOversizedHeader('flyer_absurd.png', 4294967295, 4294967295);
        $result = ImageUtils::generateStoredVariant('flyer_absurd.png');

        $this->assertFalse($result['ok']);
        $this->assertSame('too_large', $result['reason']);

        // The same header through the upload resizer, which has no try/catch of its own.
        $path = $this->oversizedHeaderFile(4294967295, 4294967295);

        try {
            $this->assertFalse(ImageUtils::resizeImageToMax($path, 2000));
        } finally {
            @unlink($path);
        }
    }

    public function test_the_budget_check_measures_rather_than_assumes(): void
    {
        $previous = ini_get('memory_limit');

        try {
            // Under the floor: waved through without consulting anything.
            $this->assertTrue(ImageUtils::canDecodePixels(1_000_000));

            // A degenerate header cannot talk its way in.
            $this->assertFalse(ImageUtils::canDecodePixels(0));

            // Past the absolute ceiling: refused however much memory is on offer.
            $this->pinMemoryLimit($this->budgetFor(ImageUtils::IMAGE_MAX_PIXELS_CEILING));
            $this->assertFalse(ImageUtils::canDecodePixels(
                ImageUtils::IMAGE_MAX_PIXELS_CEILING + 1,
                $this->budgetFor(ImageUtils::IMAGE_MAX_PIXELS_CEILING + 1)
            ));

            // 20MP, over the old flat refusal, is allowed when the budget covers it.
            $this->assertTrue(ImageUtils::canDecodePixels(20_000_000, $this->budgetFor(20_000_000)));

            // The ceiling caps the DECODE, not just the raise. memory_limit is pinned generously
            // above, so the "already fits" path would approve this if the ceiling were consulted
            // only when a raise was needed - which is exactly what it used to do, and what let a
            // host with memory_limit=-1 hand GD a quarter-gigabyte with nothing left to catch it.
            $this->assertFalse(ImageUtils::canDecodePixels(
                20_000_000,
                $this->budgetFor(20_000_000) - (128 * 1024 * 1024)
            ));

            // floorPixels is what stops the shared helper tightening its callers: this is over
            // the default floor and over the ceiling offered, yet waved through unmeasured.
            $this->assertTrue(ImageUtils::canDecodePixels(20_000_000, 1024, floorPixels: 20_000_000));
        } finally {
            ini_set('memory_limit', $previous);
        }
    }

    public function test_the_memory_limit_is_left_as_it_was_found(): void
    {
        // Just over the unmeasured floor, so the budget check has to raise the limit. A smaller
        // fixture would take the floor fast path and this test would pass without the restore
        // existing at all - which is exactly what it is here to catch.
        $pixels = ImageUtils::VARIANT_MAX_PIXELS + 4000;
        $this->storeOversizedHeader('flyer_wide.png', 4000, (int) ($pixels / 4000));

        $budget = $this->budgetFor($pixels);
        $previous = ini_get('memory_limit');

        // Comfortably below what the decode needs, so a raise is forced. The margin is wide on
        // purpose: canDecodePixels() re-reads memory_get_usage(true), and a wider gap makes the
        // raise more certain, never less.
        $base = $this->pinMemoryLimit($budget - (96 * 1024 * 1024));

        try {
            // Proves the raise genuinely happens for this size, so the assertion below is not
            // quietly vacuous.
            $this->assertTrue(ImageUtils::canDecodePixels($pixels, $budget));
            $this->assertNotSame($base, ini_get('memory_limit'));

            ini_set('memory_limit', $base);
            ImageUtils::generateStoredVariants('flyer_wide.png', ImageUtils::VARIANT_WIDTHS, $budget);

            // A raise left standing outlives the one image it was for: the backfill walks
            // thousands of rows in a single process, and the queue worker is long-lived.
            $this->assertSame($base, ini_get('memory_limit'));
        } finally {
            ini_set('memory_limit', $previous);
        }
    }

    public function test_the_upload_resizer_also_puts_the_memory_limit_back(): void
    {
        // resizeImageToMax() had the same restore written the wrong way round - it captured the
        // limit AFTER canDecodePixels() had already raised it, so the finally restored the raise
        // to itself and a long-lived process ratcheted upward. Nothing covered it, because the
        // only other test of this function uses a 3.84MP image, below the 16MP floor, and never
        // reaches the memory path at all.
        $pixels = 16_000_000 + 4000;
        $path = $this->oversizedHeaderFile(4000, (int) ($pixels / 4000));

        $budget = $this->budgetFor($pixels);
        $previous = ini_get('memory_limit');
        $base = $this->pinMemoryLimit($budget - (96 * 1024 * 1024));

        try {
            $this->assertTrue(ImageUtils::canDecodePixels($pixels, $budget, floorPixels: 16_000_000));
            $this->assertNotSame($base, ini_get('memory_limit'));

            ini_set('memory_limit', $base);

            // Returns false - the header has no pixel data behind it - but the finally still runs,
            // which is the half under test.
            ImageUtils::resizeImageToMax($path, 2000, 85, $budget);

            $this->assertSame($base, ini_get('memory_limit'));
        } finally {
            ini_set('memory_limit', $previous);
            @unlink($path);
        }
    }

    public function test_php_itself_refuses_to_restore_the_limit_below_current_usage(): void
    {
        // A characterization test, not a guard on our own branch: it pins the PHP behaviour the
        // restore RELIES on. Lowering memory_limit under current usage is refused by PHP's own
        // handler - ini_set() returns false and the value is untouched - so putting the limit
        // back can never strand the process beneath its own footprint. If a future PHP made that
        // lowering succeed instead, every restore site here would need a usage check, and this is
        // the test that would say so.
        $previous = ini_get('memory_limit');
        $high = $this->pinMemoryLimit(memory_get_usage(true) + (256 * 1024 * 1024));

        try {
            $this->assertGreaterThan(16 * 1024 * 1024, memory_get_usage(true));
            $this->assertFalse(@ini_set('memory_limit', '16M'));
            $this->assertSame($high, ini_get('memory_limit'));

            // An affordable restore still happens, which is the path the pipeline actually takes.
            $affordable = ((int) ceil((memory_get_usage(true) + (128 * 1024 * 1024)) / (1024 * 1024))).'M';
            ImageUtils::restoreMemoryLimit($affordable);
            $this->assertSame($affordable, ini_get('memory_limit'));

            // Unreadable values are ours to ignore rather than pass to ini_set().
            ImageUtils::restoreMemoryLimit('');
            ImageUtils::restoreMemoryLimit(false);
            $this->assertSame($affordable, ini_get('memory_limit'));
        } finally {
            ini_set('memory_limit', $previous);
        }
    }

    public function test_parse_memory_limit_reads_every_shape_php_uses(): void
    {
        $this->assertSame(PHP_INT_MAX, ImageUtils::parseMemoryLimit('-1'));

        // NOT unlimited: ini_get() returns false when it cannot read the setting, and (string)
        // false is ''. Reading that as PHP_INT_MAX would let a failure to read the limit approve
        // the largest decode on offer.
        $this->assertSame(0, ImageUtils::parseMemoryLimit(''));
        $this->assertSame(128 * 1024 * 1024, ImageUtils::parseMemoryLimit('128M'));
        $this->assertSame(1024 * 1024 * 1024, ImageUtils::parseMemoryLimit('1G'));
        $this->assertSame(64 * 1024, ImageUtils::parseMemoryLimit('64K'));
        $this->assertSame(65536, ImageUtils::parseMemoryLimit('65536'));
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

        // A demo schedule photo never gets derivatives, so offering a srcset would 404. (A stored
        // photo with a full set does get one - see the schedule photo tests below.)
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

        // No flyer: the talent schedule's photo at either width. A demo photo has no derivatives,
        // so the width changes nothing - it never switches to some other image.
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
        // And the original's size, which is what og:image:width and the flyer's <img> declare.
        $this->assertVariants(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]],
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
        $this->storeOversizedHeader('flyer_huge.png', 12000, 12000);

        // Deterministic, so the job returns normally rather than sending the queue round again.
        // Too large to decode is not unknown: the header's size is still recorded.
        (new GenerateEventImageVariants($event->id, 'flyer_huge.png'))->handle();

        $this->assertVariants(
            ['w480' => null, 'w960' => null, 'skipped' => 'too_large', 'src' => ['w' => 12000, 'h' => 12000]],
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
        // The original decoded fine - only the write failed - so its size is known.
        $this->assertVariants(
            ['w480' => null, 'w960' => null, 'skipped' => 'write_failed', 'src' => ['w' => 400, 'h' => 500]],
            $fresh->image_variants
        );
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
        $this->assertVariants(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]],
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
        $this->assertVariants(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp', 'src' => ['w' => 600, 'h' => 800]],
            $event->fresh()->image_variants
        );
    }

    public function test_the_backfill_command_says_how_large_and_tallies_by_reason(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 12000, 12000);
        $this->createEvent($role, ['name' => 'Winter Session', 'flyer_image_url' => 'flyer_gone.png']);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $output = Artisan::output();

        // Without the dimensions there is no way to tell a 13MP flyer worth re-running for from
        // a 200MP one that is never going to work.
        $this->assertStringContainsString('skipped: too_large (12000x12000, 144MP)', $output);

        // And without the tally, a production run's only account of itself is a bare count.
        $this->assertStringContainsString('Skipped by reason - ', $output);
        $this->assertStringContainsString('too_large: 1', $output);
        $this->assertStringContainsString('missing: 1', $output);
    }

    public function test_a_decorated_skip_is_not_what_gets_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 12000, 12000);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);

        // The console line carries the dimensions; the COLUMN must not. baseQuery() matches the
        // recorded value against VARIANT_TRANSIENT_REASONS by equality, and --retry-skipped
        // against 'STRING', so a decorated token would quietly strand the row forever.
        $this->assertSame('too_large', $event->fresh()->image_variants['skipped']);
        $this->assertContains($event->fresh()->image_variants['skipped'], ImageUtils::VARIANT_DETERMINISTIC_REASONS);
    }

    public function test_the_backfill_command_records_and_then_respects_a_skip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 12000, 12000);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertVariants(
            ['w480' => null, 'w960' => null, 'skipped' => 'too_large', 'src' => ['w' => 12000, 'h' => 12000]],
            $event->fresh()->image_variants
        );

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());

        // A recorded skip is reconsidered only on request. Give it a usable original this time.
        Storage::delete('public/flyer_huge.png');
        $this->storeFlyer('flyer_huge.png', 600, 800);
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true, '--retry-skipped' => true]);

        $this->assertVariants(
            ['w480' => 'flyer_huge_w480.webp', 'w960' => 'flyer_huge_w960.webp', 'src' => ['w' => 600, 'h' => 800]],
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

        $this->assertVariants(
            ['w480' => 'flyer_abc123_w480.webp', 'w960' => 'flyer_abc123_w960.webp', 'src' => ['w' => 600, 'h' => 800]],
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
        $this->assertVariants(
            ['w480' => 'flyer_past_w480.webp', 'w960' => 'flyer_past_w960.webp', 'src' => ['w' => 600, 'h' => 800]],
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

        // The toggle is an admin moderation action, so it needs a current password confirmation
        // even though it is served from the base domain rather than the admin route group.
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('flyer_abc123.png', $html);

        // The Hide button is rendered ON this page, so a stale wall would contradict its own
        // flash message and flip the event back on the next click.
        $this->post(route('marketing.discovery.toggle', $event->hashedId()))->assertRedirect();
        $this->assertTrue((bool) $event->fresh()->is_hidden_from_discovery);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('flyer_abc123.png', $html, 'The toggle must bust the wall cache');
    }

    // ------------------------------------------------- schedule profile photos

    /**
     * A schedule's profile photo is the card image of every event without a flyer, and the
     * homepage wall served those originals - a 2.1MB PNG, eight times over - into 96px slots.
     */
    public function test_the_schedule_photo_fallback_serves_its_derivative_when_one_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session']);

        // Nothing recorded yet: the original at every width, and no srcset.
        $this->assertStringEndsWith('/storage/profile_abc.png', $event->getImageUrl(480));
        $this->assertNull($event->imageSrcset());

        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);
        $event = $event->fresh();

        $this->assertSame(url('/storage/profile_abc_w480.webp'), $event->getImageUrl(480));
        $this->assertSame(url('/storage/profile_abc_w960.webp'), $event->getImageUrl(960));
        // No width asked for: full-size consumers keep the original.
        $this->assertSame(url('/storage/profile_abc.png'), $event->getImageUrl());
        $this->assertSame(
            url('/storage/profile_abc_w480.webp').' 480w, '.url('/storage/profile_abc_w960.webp').' 960w',
            $event->imageSrcset()
        );
    }

    public function test_the_venue_photo_fallback_serves_its_derivative_too(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'profile_image_url' => 'profile_venue.png']);
        $event = $this->createEvent($venue, ['name' => 'Autumn Session']);

        $this->assertStringEndsWith('/storage/profile_venue.png', $event->getImageUrl(480));

        $venue->recordImageVariants(['w480' => 'profile_venue_w480.webp', 'w960' => 'profile_venue_w960.webp']);

        $this->assertSame(url('/storage/profile_venue_w480.webp'), $event->fresh()->getImageUrl(480));
    }

    public function test_a_flyer_still_beats_the_schedule_photo_derivative(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        // The event's own flyer, with no derivative yet: its original, never the schedule's WebP.
        $this->assertStringEndsWith('flyer_abc123.png', $event->fresh()->getImageUrl(480));
        $this->assertNull($event->fresh()->imageSrcset());
    }

    public function test_get_profile_image_url_returns_the_variant_only_when_one_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);

        $this->assertSame(url('/storage/profile_abc.png'), $role->getProfileImageUrl(480));

        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => null, 'skipped' => 'write_failed']);

        $this->assertSame(url('/storage/profile_abc_w480.webp'), $role->getProfileImageUrl(480));
        $this->assertSame(url('/storage/profile_abc.png'), $role->getProfileImageUrl(960), 'A skipped width falls back');
        $this->assertSame(url('/storage/profile_abc.png'), $role->getProfileImageUrl());
        $this->assertSame($role->profile_image_url, $role->getProfileImageUrl());

        $bare = $this->createRole($owner, 'talent', ['name' => 'No Photo']);
        $this->assertSame('', $bare->getProfileImageUrl(480));
    }

    public function test_setting_a_schedule_photo_queues_a_generation_job(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        Queue::assertNotPushed(GenerateRoleImageVariants::class);

        $role->profile_image_url = 'profile_abc.png';
        $role->save();

        Queue::assertPushed(GenerateRoleImageVariants::class, 1);
        Queue::assertPushed(function (GenerateRoleImageVariants $job) use ($role) {
            return $job->roleId === $role->id && $job->profileImage === 'profile_abc.png';
        });

        // Created with one: queued from the created hook.
        Queue::fake();
        $created = $this->createRole($owner, 'venue', ['name' => 'Green Room', 'profile_image_url' => 'profile_new.png']);
        Queue::assertPushed(function (GenerateRoleImageVariants $job) use ($created) {
            return $job->roleId === $created->id && $job->profileImage === 'profile_new.png';
        });
    }

    public function test_an_unrelated_schedule_save_and_demo_photos_queue_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);

        Queue::fake();

        $role->name = 'Blue Room II';
        $role->save();

        $demo = $this->createRole($owner, 'talent', ['name' => 'Demo Room', 'profile_image_url' => 'demo_profile_donuts.jpg']);
        $role->profile_image_url = null;
        $role->save();

        Queue::assertNotPushed(GenerateRoleImageVariants::class);
        $this->assertNotNull($demo->id);
    }

    public function test_the_role_job_generates_and_records_every_width(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $this->storeFlyer('profile_abc.png', 1200, 1200);

        (new GenerateRoleImageVariants($role->id, 'profile_abc.png'))->handle();

        Storage::assertExists('public/profile_abc_w480.webp');
        Storage::assertExists('public/profile_abc_w960.webp');
        $this->assertVariants(
            ['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp', 'src' => ['w' => 1200, 'h' => 1200]],
            $role->fresh()->image_variants
        );
    }

    public function test_the_role_job_bails_when_the_photo_was_replaced_after_dispatch(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_new.png']);
        $this->storeFlyer('profile_old.png', 600, 600);

        (new GenerateRoleImageVariants($role->id, 'profile_old.png'))->handle();

        Storage::assertMissing('public/profile_old_w480.webp');
        $this->assertNull($role->fresh()->image_variants);
    }

    public function test_role_recording_is_refused_when_the_photo_changed_underneath(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);

        $stale = Role::find($role->id);
        Role::whereKey($role->id)->update(['profile_image_url' => 'profile_replaced.png']);

        $this->assertFalse($stale->recordImageVariants(['w480' => 'profile_abc_w480.webp']));
        $this->assertNull($role->fresh()->image_variants);
    }

    public function test_a_throwing_helper_never_breaks_the_schedule_save_on_the_sync_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $this->storeFlyer('profile_boom.png', 400, 400);

        $this->swapDisk($this->diskThatThrows());
        $this->useSyncQueue();

        $role->profile_image_url = 'profile_boom.png';
        $role->save();

        $fresh = $role->fresh();
        $this->assertSame('profile_boom.png', $fresh->getAttributes()['profile_image_url'], 'The photo save must stand');
        $this->assertSame(['w480' => null, 'w960' => null, 'skipped' => 'failed'], $fresh->image_variants);
    }

    public function test_a_transient_disk_failure_never_breaks_the_schedule_save_on_the_sync_queue(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $this->storeFlyer('profile_boom.png', 400, 400);

        $this->swapDisk($this->diskThatCannotWrite());
        $this->useSyncQueue();

        $role->profile_image_url = 'profile_boom.png';
        $role->save();

        $fresh = $role->fresh();
        $this->assertSame('profile_boom.png', $fresh->getAttributes()['profile_image_url'], 'The photo save must stand');
        $this->assertVariants(
            ['w480' => null, 'w960' => null, 'skipped' => 'write_failed', 'src' => ['w' => 400, 'h' => 400]],
            $fresh->image_variants
        );
    }

    public function test_replacing_the_schedule_photo_clears_and_deletes_the_old_derivatives(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_old.png']);

        $this->storeFlyer('profile_old.png', 400, 400);
        $this->storeFlyer('profile_new.png', 400, 400);
        ImageUtils::generateStoredVariants('profile_old.png');
        $role->recordImageVariants(['w480' => 'profile_old_w480.webp', 'w960' => 'profile_old_w960.webp']);

        Storage::assertExists('public/profile_old_w480.webp');

        $role->profile_image_url = 'profile_new.png';
        $role->save();

        $this->assertNull($role->fresh()->image_variants);
        $this->assertSame(url('/storage/profile_new.png'), $role->fresh()->getProfileImageUrl(480));
        Storage::assertMissing('public/profile_old_w480.webp');
        Storage::assertMissing('public/profile_old_w960.webp');
        Storage::assertExists('public/profile_new.png');
    }

    public function test_deleting_the_schedule_deletes_the_photo_derivatives(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_old.png']);

        $this->storeFlyer('profile_old.png', 400, 400);
        ImageUtils::generateStoredVariants('profile_old.png');
        $role->recordImageVariants(['w480' => 'profile_old_w480.webp', 'w960' => 'profile_old_w960.webp']);

        $role->delete();

        Storage::assertMissing('public/profile_old_w480.webp');
        Storage::assertMissing('public/profile_old_w960.webp');
    }

    public function test_the_backfill_command_fills_schedule_photos_only_with_the_roles_option(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $demo = $this->createRole($owner, 'talent', ['name' => 'Demo Room', 'profile_image_url' => 'demo_profile_donuts.jpg']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('profile_abc.png', 800, 800);
        $this->storeFlyer('flyer_abc123.png', 600, 800);

        // A plain run is about flyers, and must leave schedules alone.
        Artisan::call('images:backfill-variants');
        $this->assertNull($role->fresh()->image_variants);

        // Reset the flyer's row, so the next assertion can tell whether --roles walked events.
        DB::table('events')->where('id', $event->id)->update(['image_variants' => null]);

        Artisan::call('images:backfill-variants', ['--roles' => true]);

        $this->assertVariants(
            ['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp', 'src' => ['w' => 800, 'h' => 800]],
            $role->fresh()->image_variants
        );
        $this->assertNull($demo->fresh()->image_variants, 'demo_ photos ship in the repo');
        $this->assertNull($event->fresh()->image_variants, '--roles must not walk the events table');

        Artisan::call('images:backfill-variants', ['--roles' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_roles_backfill_records_and_retries_a_skip_on_request(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_huge.png']);
        $this->storeOversizedHeader('profile_huge.png', 12000, 12000);

        Artisan::call('images:backfill-variants', ['--roles' => true]);
        $this->assertStringContainsString('[schedule '.$role->id.'] skipped: too_large', Artisan::output());
        $this->assertVariants(
            ['w480' => null, 'w960' => null, 'skipped' => 'too_large', 'src' => ['w' => 12000, 'h' => 12000]],
            $role->fresh()->image_variants
        );

        Artisan::call('images:backfill-variants', ['--roles' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());

        Storage::delete('public/profile_huge.png');
        $this->storeFlyer('profile_huge.png', 600, 600);
        Artisan::call('images:backfill-variants', ['--roles' => true, '--retry-skipped' => true]);

        $this->assertVariants(
            ['w480' => 'profile_huge_w480.webp', 'w960' => 'profile_huge_w960.webp', 'src' => ['w' => 600, 'h' => 600]],
            $role->fresh()->image_variants
        );
    }

    public function test_the_role_column_is_neither_exported_nor_restored(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);

        $service = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $service->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayNotHasKey('image_variants', $data['schedules'][0]);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $service->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $this->assertNull($restored->image_variants);
    }

    public function test_the_homepage_wall_uses_the_schedule_photo_derivative(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);
        $this->createEvent($role, ['name' => 'Autumn Session']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('profile_abc_w480.webp', $html);
        $this->assertStringNotContainsString('profile_abc.png', $html, 'The original must not be requested when a derivative exists');
    }

    public function test_a_new_schedule_photo_busts_the_wall_cache(): void
    {
        config(['marketing.wall_cache_seconds' => 60]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_old.png']);
        $this->createEvent($role, ['name' => 'Autumn Session']);

        $this->assertStringContainsString('profile_old.png', $this->get('/')->assertOk()->getContent());

        $role->profile_image_url = 'profile_new.png';
        $role->save();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('profile_new.png', $html, 'The cached wall pointed at a photo that was just deleted');
        $this->assertStringNotContainsString('profile_old.png', $html);
    }

    /**
     * One poster, and only one, is marked fetchpriority="high": card 0, which is the same URL on
     * the mobile strip and the desktop wall, so both copies in the markup are one request.
     */
    public function test_the_homepage_asks_for_exactly_one_high_priority_poster(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);

        foreach (range(1, 8) as $i) {
            $this->createEvent($role, ['name' => 'Autumn Session '.$i, 'flyer_image_url' => 'flyer_prio'.$i.'.png']);
        }

        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<img src="([^"]+)"[^>]*fetchpriority="high"/', $html, $matches);

        $this->assertNotEmpty($matches[1], 'The first eager poster must be fetched at high priority');
        $this->assertCount(1, array_unique($matches[1]), 'Only one distinct image may be high priority');
        // The strip and the wall each carry it once, in marquee copy 0.
        $this->assertCount(2, $matches[1]);
        $this->assertStringContainsString('loading="eager"', $this->imgTagFor($html, $matches[1][0]));
    }

    // --------------------------------------------- schedule header and background

    /**
     * The two wide images a schedule can upload get page widths, not card widths, and every build
     * records the original's size - which nothing can measure at render time for a CDN file.
     */
    public function test_the_banner_slots_build_page_widths_and_record_the_original_size(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_abc.png',
        ]);
        $this->storeFlyer('header_abc.png', 2400, 1200);
        $this->storeFlyer('background_abc.png', 1200, 1600);

        (new GenerateRoleImageVariants($role->id, 'header_abc.png', 'header'))->handle();
        (new GenerateRoleImageVariants($role->id, 'background_abc.png', 'background'))->handle();

        $this->assertSame([960, 1920], ImageUtils::BANNER_VARIANT_WIDTHS);

        $fresh = $role->fresh();
        $this->assertVariants(
            ['w960' => 'header_abc_w960.webp', 'w1920' => 'header_abc_w1920.webp', 'src' => ['w' => 2400, 'h' => 1200]],
            $fresh->header_image_variants
        );
        $this->assertVariants(
            ['w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp', 'src' => ['w' => 1200, 'h' => 1600]],
            $fresh->background_image_variants
        );
        $this->assertNull($fresh->image_variants, 'The profile slot is not touched');

        $this->assertSame([960, 480, IMAGETYPE_WEBP], $this->variantSize('header_abc_w960.webp'));
        $this->assertSame([1920, 960, IMAGETYPE_WEBP], $this->variantSize('header_abc_w1920.webp'));
        // Never upscaled: a 1200px background's "1920" is a WebP of its own width.
        $this->assertSame([1200, 1600, IMAGETYPE_WEBP], $this->variantSize('background_abc_w1920.webp'));
        Storage::assertMissing('public/header_abc_w480.webp');

        // The readers every page uses.
        $this->assertSame([2400, 1200], $fresh->imageSourceDimensions('header'));
        $this->assertSame([960, 480], $fresh->imageVariantDimensions(960, 'header'));
        $this->assertSame([1200, 1600], $fresh->imageVariantDimensions(1920, 'background'));
        $this->assertSame(url('/storage/header_abc_w960.webp'), $fresh->imageVariantUrl(960, 'header'));
        $this->assertSame(
            url('/storage/header_abc_w960.webp').' 960w, '.url('/storage/header_abc_w1920.webp').' 1920w',
            $fresh->imageVariantSrcset('header')
        );
        // The original joins only where it is wider than every derivative.
        $this->assertSame(
            url('/storage/header_abc_w960.webp').' 960w, '.url('/storage/header_abc_w1920.webp').' 1920w, '.url('/storage/header_abc.png').' 2400w',
            $fresh->imageVariantSrcset('header', true)
        );
        $this->assertSame(
            url('/storage/background_abc_w960.webp').' 960w, '.url('/storage/background_abc_w1920.webp').' 1920w',
            $fresh->imageVariantSrcset('background', true)
        );
        $this->assertSame(url('/storage/header_abc_w1920.webp'), $fresh->headerImageUrl(1920));
        $this->assertSame(url('/storage/header_abc.png'), $fresh->headerImageUrl());
        $this->assertSame(url('/storage/background_abc_w960.webp'), $fresh->backgroundImageUrl(960));
    }

    /**
     * Why each image has a column of its own: recordImageVariants() rewrites a whole column,
     * guarded on one source, so two jobs from one save sharing a column would have overwritten
     * each other - whichever finished last erasing the other's filenames.
     */
    public function test_one_save_replacing_the_header_and_background_keeps_both_derivative_sets(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'header_image' => '',
            'background' => 'image',
            'background_image' => null,
        ]);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);
        $this->storeFlyer('header_new.png', 1200, 600);
        $this->storeFlyer('background_new.png', 1000, 1400);

        Queue::fake();

        $role->header_image_url = 'header_new.png';
        $role->background_image_url = 'background_new.png';
        $role->save();

        // One job per image, each naming its slot.
        Queue::assertPushed(GenerateRoleImageVariants::class, 2);
        Queue::assertPushed(fn (GenerateRoleImageVariants $job) => $job->slot === 'header' && $job->profileImage === 'header_new.png');
        Queue::assertPushed(fn (GenerateRoleImageVariants $job) => $job->slot === 'background' && $job->profileImage === 'background_new.png');

        // Run them the way a worker might: both loaded before either finishes, finishing in the
        // opposite order to the one they were queued in.
        foreach (Queue::pushed(GenerateRoleImageVariants::class)->reverse() as $job) {
            $job->handle();
        }

        $fresh = $role->fresh();
        $this->assertSame('header_new_w1920.webp', $fresh->imageVariantFilename(1920, 'header'));
        $this->assertSame('background_new_w1920.webp', $fresh->imageVariantFilename(1920, 'background'));
        $this->assertSame('profile_abc_w480.webp', $fresh->imageVariantFilename(480), 'The profile photo did not change');

        // Two writers holding stale copies of the row still write only their own column.
        $staleForHeader = Role::find($role->id);
        $staleForBackground = Role::find($role->id);
        $staleForBackground->recordImageVariants(['w960' => 'background_new_w960.webp', 'w1920' => null, 'skipped' => 'write_failed'], 'background');
        $staleForHeader->recordImageVariants(['w960' => 'header_new_w960.webp', 'w1920' => 'header_new_w1920.webp'], 'header');

        $fresh = $role->fresh();
        $this->assertSame('write_failed', $fresh->background_image_variants['skipped']);
        $this->assertSame('header_new_w1920.webp', $fresh->imageVariantFilename(1920, 'header'));
    }

    public function test_replacing_the_background_deletes_its_derivatives_and_leaves_the_others(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_old.png',
        ]);

        foreach (['profile_abc_w480', 'header_abc_w960', 'header_abc_w1920', 'background_old_w960', 'background_old_w1920'] as $name) {
            Storage::put(ImageUtils::storagePathFor($name.'.webp'), 'webp');
        }
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => null, 'skipped' => 'write_failed']);
        $role->recordImageVariants(['w960' => 'header_abc_w960.webp', 'w1920' => 'header_abc_w1920.webp'], 'header');
        $role->recordImageVariants(['w960' => 'background_old_w960.webp', 'w1920' => 'background_old_w1920.webp'], 'background');

        $role = $role->fresh();
        $role->background_image_url = 'background_new.png';
        $role->save();

        $fresh = $role->fresh();
        $this->assertNull($fresh->background_image_variants);
        $this->assertSame(url('/storage/background_new.png'), $fresh->backgroundImageUrl(960), 'The new original until its derivatives exist');
        Storage::assertMissing('public/background_old_w960.webp');
        Storage::assertMissing('public/background_old_w1920.webp');
        // The other two images are untouched, record and files alike.
        $this->assertSame('header_abc_w1920.webp', $fresh->imageVariantFilename(1920, 'header'));
        $this->assertSame('profile_abc_w480.webp', $fresh->imageVariantFilename(480));
        Storage::assertExists('public/header_abc_w1920.webp');
        Storage::assertExists('public/profile_abc_w480.webp');

        // And deleting the schedule takes every image's derivatives with it.
        $fresh->delete();
        Storage::assertMissing('public/header_abc_w960.webp');
        Storage::assertMissing('public/header_abc_w1920.webp');
        Storage::assertMissing('public/profile_abc_w480.webp');
    }

    /**
     * Deletion by default walks every width any slot builds: the saving hook has already nulled
     * the record, so a width it missed would strand that file on the CDN for good.
     */
    public function test_deleting_derivatives_covers_every_width_of_every_slot(): void
    {
        $this->assertSame([480, 960, 1920], ImageUtils::allVariantWidths());

        foreach ([480, 960, 1920] as $width) {
            Storage::put(ImageUtils::storagePathFor("header_abc_w{$width}.webp"), 'webp');
            Storage::put(ImageUtils::storagePathFor("flyer_abc_w{$width}.webp"), 'webp');
        }

        ImageUtils::deleteStoredVariants('header_abc.png');

        foreach ([480, 960, 1920] as $width) {
            Storage::assertMissing("public/header_abc_w{$width}.webp");
        }

        // An explicit list is honoured as given.
        ImageUtils::deleteStoredVariants('flyer_abc.png', [480]);
        Storage::assertMissing('public/flyer_abc_w480.webp');
        Storage::assertExists('public/flyer_abc_w960.webp');
    }

    /** An EXIF-rotated photo records the size a browser shows, not the sensor frame's. */
    public function test_the_recorded_size_is_the_displayed_one(): void
    {
        $this->storeJpeg('flyer_upright.jpg', 6, 600, 400);

        $result = ImageUtils::generateStoredVariant('flyer_upright.jpg');

        $this->assertTrue($result['ok']);
        $this->assertSame(['w' => 400, 'h' => 600], $result['src']);
    }

    /**
     * Refusing to DECODE an original is not the same as not knowing its size: a too_large skip
     * still records the header's, turned by the EXIF tag like everything else.
     */
    public function test_a_too_large_original_still_records_its_displayed_size(): void
    {
        // 70MP is past IMAGE_MAX_PIXELS_CEILING, refused before any decode whatever the budget.
        Storage::put(ImageUtils::storagePathFor('flyer_vast.jpg'), $this->jpegHeaderBytes(6, 10000, 7000));

        $result = ImageUtils::generateStoredVariant('flyer_vast.jpg');

        $this->assertSame('too_large', $result['reason']);
        $this->assertSame(['w' => 7000, 'h' => 10000], $result['src']);
    }

    public function test_the_size_read_honours_exif_and_stops_at_the_head_of_the_file(): void
    {
        // A megabyte of scan data after the header, which the read never touches.
        Storage::put(ImageUtils::storagePathFor('flyer_tall.jpg'), $this->jpegHeaderBytes(6, 4000, 3000).str_repeat("\x00", 1024 * 1024));
        $this->assertSame(['w' => 3000, 'h' => 4000], ImageUtils::storedImageDimensions('flyer_tall.jpg'));

        // A frame header pushed past the first 256KB is out of reach, although the whole file
        // says where it is: proof that only the head is read.
        Storage::put(ImageUtils::storagePathFor('flyer_deep.jpg'), $this->jpegHeaderBytes(1, 4000, 3000, 300 * 1024));
        $this->assertSame(4000, getimagesizefromstring(Storage::get('public/flyer_deep.jpg'))[0], 'fixture: the whole file is readable');
        $this->assertNull(ImageUtils::storedImageDimensions('flyer_deep.jpg'));

        $this->storeFlyer('flyer_small.png', 300, 200);
        $this->assertSame(['w' => 300, 'h' => 200], ImageUtils::storedImageDimensions('flyer_small.png'));

        $this->assertNull(ImageUtils::storedImageDimensions('flyer_gone.png'));
        $this->assertNull(ImageUtils::storedImageDimensions('demo_flyer_jazz.webp'));
        $this->assertNull(ImageUtils::storedImageDimensions('https://example.com/a.png'));
    }

    public function test_the_backfill_walks_the_slot_it_is_asked_for(): void
    {
        $owner = $this->createOwner();
        // Queue::fake() in setUp() keeps the created hook's three jobs from running, so the
        // schedule starts with nothing built - the state every existing upload is in.
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_abc.png',
        ]);
        $this->storeFlyer('profile_abc.png', 600, 600);
        $this->storeFlyer('header_abc.png', 1200, 600);
        $this->storeFlyer('background_abc.png', 900, 1200);

        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'background']);
        $output = Artisan::output();

        $fresh = $role->fresh();
        $this->assertStringContainsString('Pass: schedule backgrounds', $output);
        $this->assertStringContainsString('Target widths: 960px, 1920px WebP', $output);
        $this->assertStringContainsString('[schedule '.$role->id.' background] background_abc_w960.webp, background_abc_w1920.webp', $output);
        $this->assertVariants(
            ['w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp', 'src' => ['w' => 900, 'h' => 1200]],
            $fresh->background_image_variants
        );
        $this->assertNull($fresh->header_image_variants, '--slot=background builds the background only');
        $this->assertNull($fresh->image_variants);

        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'all']);

        $fresh = $role->fresh();
        $this->assertSame('header_abc_w1920.webp', $fresh->imageVariantFilename(1920, 'header'));
        $this->assertSame('profile_abc_w960.webp', $fresh->imageVariantFilename(960));
        $this->assertSame([600, 600], $fresh->imageSourceDimensions());

        // A plain --roles is still the profile photo, and everything is done now.
        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'all']);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_backfill_refuses_a_slot_it_cannot_honour(): void
    {
        // Silently running the flyer pass instead would tell the operator the backgrounds are done.
        $this->assertSame(1, Artisan::call('images:backfill-variants', ['--slot' => 'background']));
        $this->assertStringContainsString('needs --roles', Artisan::output());

        $this->assertSame(1, Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'banner']));
        $this->assertStringContainsString('Unknown --slot "banner"', Artisan::output());
    }

    /**
     * Rows built before sizes were recorded get one from a header read, and nothing else: no
     * derivative is generated or even needed, and the recorded filenames are left exactly alone.
     */
    public function test_dimensions_records_a_missing_size_and_generates_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'header_image' => '', 'header_image_url' => 'header_old.png']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_old.png']);
        $this->storeFlyer('flyer_old.png', 600, 800);
        $this->storeFlyer('header_old.png', 2400, 1200);

        // What the pipeline recorded before it recorded sizes. The derivative FILES are
        // deliberately absent: --dimensions must neither need them nor write them.
        $event->recordImageVariants(['w480' => 'flyer_old_w480.webp', 'w960' => 'flyer_old_w960.webp']);
        $role->recordImageVariants(['w960' => 'header_old_w960.webp', 'w1920' => 'header_old_w1920.webp'], 'header');

        Artisan::call('images:backfill-variants', ['--dimensions' => true]);
        $this->assertStringContainsString('Recording original sizes only', Artisan::output());

        $this->assertVariants(
            ['w480' => 'flyer_old_w480.webp', 'w960' => 'flyer_old_w960.webp', 'src' => ['w' => 600, 'h' => 800]],
            $event->fresh()->image_variants
        );
        Storage::assertMissing('public/flyer_old_w480.webp');
        $this->assertNull($role->fresh()->imageSourceDimensions('header'), 'A flyer run leaves schedules alone');

        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'all', '--dimensions' => true]);

        $this->assertVariants(
            ['w960' => 'header_old_w960.webp', 'w1920' => 'header_old_w1920.webp', 'src' => ['w' => 2400, 'h' => 1200]],
            $role->fresh()->header_image_variants
        );
        Storage::assertMissing('public/header_old_w960.webp');

        // Done means done, on both rails.
        Artisan::call('images:backfill-variants', ['--dimensions' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'all', '--dimensions' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_banner_columns_are_neither_exported_nor_restored(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_abc.png',
        ]);
        $role->recordImageVariants(['w960' => 'header_abc_w960.webp', 'w1920' => 'header_abc_w1920.webp'], 'header');
        $role->recordImageVariants(['w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp'], 'background');

        $service = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $service->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $this->assertArrayNotHasKey('header_image_variants', $data['schedules'][0]);
        $this->assertArrayNotHasKey('background_image_variants', $data['schedules'][0]);

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $service->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Role::where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $this->assertNull($restored->header_image_variants);
        $this->assertNull($restored->background_image_variants);
    }

    /**
     * A job queued by the previous release is a serialized GenerateRoleImageVariants with no slot
     * property at all, and unserialize() runs no constructor. The class-level default is what
     * gives it one; a promoted constructor property would come back uninitialized and throw.
     *
     * (SerializesModels::__serialize() also leaves out any property still at its declared
     * default, so today's profile-photo jobs are serialized without a slot too - which only works
     * for the same reason.)
     */
    public function test_a_job_queued_before_slots_existed_still_builds_the_profile_photo(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $this->storeFlyer('profile_abc.png', 800, 800);

        // What the previous release's queue holds: the class and the two properties it had.
        $legacy = sprintf(
            'O:%d:"%s":2:{s:6:"roleId";i:%d;s:12:"profileImage";%s}',
            strlen(GenerateRoleImageVariants::class),
            GenerateRoleImageVariants::class,
            $role->id,
            serialize('profile_abc.png')
        );

        $job = unserialize($legacy);

        $this->assertInstanceOf(GenerateRoleImageVariants::class, $job);
        $this->assertSame('default', $job->slot);

        $job->handle();

        $this->assertSame('profile_abc_w480.webp', $role->fresh()->imageVariantFilename(480));

        // And a slot that is not the default does survive the round trip.
        $header = unserialize(serialize(new GenerateRoleImageVariants($role->id, 'header_abc.png', 'header')));
        $this->assertSame('header', $header->slot);
        $this->assertSame('header_abc.png', $header->profileImage);
    }

    /**
     * The recorded size is what lets og:image:width, og:image:height and the JSON-LD ImageObjects
     * describe an upload on the CDN, which SeoUtils::imageDimensions() cannot open.
     */
    public function test_a_recorded_size_reaches_og_image_and_the_json_ld(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
        ]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $this->assertStringNotContainsString('og:image:width', $html, 'Nothing recorded, nothing declared');

        $role->recordImageVariants(['w960' => 'header_abc_w960.webp', 'w1920' => 'header_abc_w1920.webp', 'src' => ['w' => 3000, 'h' => 1500]], 'header');
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp', 'src' => ['w' => 800, 'h' => 800]]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $this->assertStringContainsString('<meta property="og:image" content="'.url('/storage/header_abc.png').'">', $html);
        $this->assertStringContainsString('<meta property="og:image:width" content="3000">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="1500">', $html);

        $node = $this->jsonLdNode($html, 'EventVenue');
        $this->assertSame(['@type' => 'ImageObject', 'url' => url('/storage/header_abc.png'), 'width' => 3000, 'height' => 1500], $node['image']);
        $this->assertSame(['@type' => 'ImageObject', 'url' => url('/storage/profile_abc.png'), 'width' => 800, 'height' => 800], $node['logo']);

        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id, 'flyer_image_url' => 'flyer_abc.png']);
        $event->recordImageVariants(['w480' => 'flyer_abc_w480.webp', 'w960' => 'flyer_abc_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $this->assertStringContainsString('<meta property="og:image:width" content="1600">', $html);
        $this->assertStringContainsString('<meta property="og:image:height" content="2133">', $html);
        $this->assertSame(
            ['@type' => 'ImageObject', 'url' => url('/storage/flyer_abc.png'), 'width' => 1600, 'height' => 2133],
            $this->jsonLdNode($html, 'Event')['image']
        );
    }

    // --------------------------------------------------------- animated originals

    /**
     * GD decodes only the first frame of an animated image, so the pipeline has to tell from the
     * file's structure whether there is more. A GIF is walked block by block, seeking over the
     * image data, and the walk stops at the second frame.
     */
    public function test_the_gif_walker_counts_frames_without_decoding_them(): void
    {
        $still = hex2bin(self::ONE_PIXEL_GIF);

        $this->assertSame(1, ImageUtils::gifFrameCount($this->tempImage($still)));
        $this->assertSame(2, ImageUtils::gifFrameCount($this->tempImage($this->animatedGif($still))));
        $this->assertSame(2, ImageUtils::gifFrameCount($this->tempImage($this->animatedGif($still, 3))), 'it stops at the second');
        $this->assertSame(3, ImageUtils::gifFrameCount($this->tempImage($this->animatedGif($still, 3)), 10));
        // GD's own GIFs carry no extension block and a larger colour table.
        $this->assertSame(1, ImageUtils::gifFrameCount($this->tempImage($this->gifBytes(40, 30))));
        $this->assertSame(2, ImageUtils::gifFrameCount($this->tempImage($this->animatedGif($this->gifBytes(40, 30)))));
        $this->assertSame(0, ImageUtils::gifFrameCount($this->tempImage('not an image')));
        $this->assertSame(0, ImageUtils::gifFrameCount(sys_get_temp_dir().'/no-such-file.gif'));

        // What GD makes of the animated one: a picture of its first frame, and nothing more.
        $this->assertNotFalse(@imagecreatefromstring($this->animatedGif($still)), 'fixture: GD decodes it');

        $this->assertTrue(ImageUtils::isAnimated($this->tempImage($this->animatedGif($still)), 'image/gif'));
        $this->assertFalse(ImageUtils::isAnimated($this->tempImage($still), 'image/gif'));
    }

    /** GD cannot decode an animated WebP at all, but getimagesize() reads it and so does this. */
    public function test_an_animated_webp_is_detected(): void
    {
        $animated = $this->animatedWebp(40, 30);

        $this->assertSame([40, 30], array_slice(getimagesizefromstring($animated), 0, 2), 'fixture: its canvas is readable');
        $this->assertTrue(ImageUtils::isAnimated($this->tempImage($animated), 'image/webp'));

        // A still one, simple or extended (the alpha flag alone), is not.
        $this->assertFalse(ImageUtils::isAnimated($this->tempImage($this->webpBytes(40, 30)), 'image/webp'));
        $this->assertFalse(ImageUtils::isAnimated($this->tempImage(substr_replace($animated, "\x10", 20, 1)), 'image/webp'));

        // Through the pipeline: whatever this build of GD makes of it, the row says it moves.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_moving.webp']);
        Storage::put(ImageUtils::storagePathFor('flyer_moving.webp'), $animated);

        (new GenerateEventImageVariants($event->id, 'flyer_moving.webp'))->handle();

        $this->assertTrue($event->fresh()->imageIsAnimated());
    }

    /** An animated PNG names its frame count in an acTL chunk, ahead of the image data. */
    public function test_an_animated_png_is_detected(): void
    {
        $image = imagecreatetruecolor(4, 3);
        ob_start();
        imagepng($image);
        imagedestroy($image);
        $png = ob_get_clean();

        // After the signature and IHDR, which is its length, type, 13 bytes of data and a CRC.
        $withFrames = function (int $frames) use ($png): string {
            $data = pack('NN', $frames, 0);

            return substr($png, 0, 33).pack('N', 8).'acTL'.$data.pack('N', crc32('acTL'.$data)).substr($png, 33);
        };

        $this->assertFalse(ImageUtils::isAnimated($this->tempImage($png), 'image/png'));
        $this->assertTrue(ImageUtils::isAnimated($this->tempImage($withFrames(2)), 'image/png'));
        $this->assertFalse(ImageUtils::isAnimated($this->tempImage($withFrames(1)), 'image/png'), 'one frame is a still');
        // GD decodes the default image, so an animated PNG gets its still thumbnails too.
        $this->assertNotFalse(@imagecreatefromstring($withFrames(2)));
    }

    /**
     * An animated flyer gets every still thumbnail a still one does - the cards, the homepage wall
     * and avatars show those - and the row records that it moves, which is what the page-width
     * surfaces read to show the original instead.
     */
    public function test_an_animated_gif_is_flagged_and_still_gets_its_still_thumbnails(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_moving.gif']);
        Storage::put(ImageUtils::storagePathFor('flyer_moving.gif'), $this->animatedGif(hex2bin(self::ONE_PIXEL_GIF)));

        foreach (ImageUtils::generateStoredVariants('flyer_moving.gif') as $width => $result) {
            $this->assertTrue($result['ok'], "{$width}: ".($result['reason'] ?? ''));
            $this->assertTrue($result['animated'], (string) $width);
        }

        (new GenerateEventImageVariants($event->id, 'flyer_moving.gif'))->handle();

        Storage::assertExists('public/flyer_moving_w480.webp');
        Storage::assertExists('public/flyer_moving_w960.webp');
        $this->assertVariants(
            ['w480' => 'flyer_moving_w480.webp', 'w960' => 'flyer_moving_w960.webp', 'src' => ['w' => 1, 'h' => 1], 'animated' => true],
            $event->fresh()->image_variants
        );
    }

    public function test_a_one_frame_gif_is_handled_exactly_as_before(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_still.gif']);
        Storage::put(ImageUtils::storagePathFor('flyer_still.gif'), hex2bin(self::ONE_PIXEL_GIF));

        (new GenerateEventImageVariants($event->id, 'flyer_still.gif'))->handle();

        $fresh = $event->fresh();
        $this->assertVariants(
            ['w480' => 'flyer_still_w480.webp', 'w960' => 'flyer_still_w960.webp', 'src' => ['w' => 1, 'h' => 1]],
            $fresh->image_variants
        );
        $this->assertFalse($fresh->imageIsAnimated());
        // So even the page-width surfaces get its derivatives.
        $this->assertSame(url('/storage/flyer_still_w960.webp'), $fresh->getImageUrl(960, pageWidth: true));
        $this->assertSame(
            url('/storage/flyer_still_w480.webp').' 480w, '.url('/storage/flyer_still_w960.webp').' 960w',
            $fresh->imageVariantSrcset('default', true, pageWidth: true)
        );
    }

    /**
     * Over the 2000px upload cap a GIF is re-encoded, and GD re-encodes the one frame it decoded:
     * a large animated flyer used to lose its animation the moment it was uploaded.
     */
    public function test_the_upload_resizer_leaves_an_animated_gif_whole(): void
    {
        $still = $this->gifBytes(2100, 10);
        $animated = $this->animatedGif($still);
        $animatedPath = $this->tempImage($animated);
        $stillPath = $this->tempImage($still);

        $this->assertTrue(ImageUtils::resizeImageToMax($animatedPath, 2000));
        $this->assertSame(sha1($animated), sha1_file($animatedPath), 'every frame kept, byte for byte');
        $this->assertSame(2, ImageUtils::gifFrameCount($animatedPath));

        // A one-frame GIF over the cap is resized exactly as before.
        $this->assertTrue(ImageUtils::resizeImageToMax($stillPath, 2000));
        $this->assertSame([2000, 10], array_slice(getimagesize($stillPath), 0, 2));
    }

    /**
     * Production holds still derivatives of animated flyers and logos built before the flag
     * existed, every width present, so no plain run selects those rows again. --animated re-checks
     * every GIF and WebP whatever its row records.
     */
    public function test_animated_rechecks_every_gif_and_webp_whatever_it_records(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $still = hex2bin(self::ONE_PIXEL_GIF);

        $moving = $this->createEvent($role, ['name' => 'Moving Night', 'flyer_image_url' => 'flyer_moving.gif']);
        Storage::put(ImageUtils::storagePathFor('flyer_moving.gif'), $this->animatedGif($still));
        $moving->recordImageVariants(['w480' => 'flyer_moving_w480.webp', 'w960' => 'flyer_moving_w960.webp', 'src' => ['w' => 1, 'h' => 1]]);

        // A GIF that does not move, recorded as one that does.
        $unmoving = $this->createEvent($role, ['name' => 'Still Night', 'flyer_image_url' => 'flyer_still.gif']);
        Storage::put(ImageUtils::storagePathFor('flyer_still.gif'), $still);
        $unmoving->recordImageVariants(['w480' => 'flyer_still_w480.webp', 'w960' => 'flyer_still_w960.webp', 'src' => ['w' => 1, 'h' => 1], 'animated' => true]);

        // An animated WebP, which GD cannot decode: recorded as unreadable, a skip nothing re-reads.
        $webp = $this->createEvent($role, ['name' => 'Loop Night', 'flyer_image_url' => 'flyer_loop.webp']);
        Storage::put(ImageUtils::storagePathFor('flyer_loop.webp'), $this->animatedWebp(40, 30));
        $webp->recordImageVariants(['w480' => null, 'w960' => null, 'skipped' => 'unreadable']);

        // Not a GIF or a WebP, so not re-checked.
        $poster = $this->createEvent($role, ['name' => 'Poster Night', 'flyer_image_url' => 'flyer_poster.png']);
        $poster->recordImageVariants(['w480' => 'flyer_poster_w480.webp', 'w960' => 'flyer_poster_w960.webp']);

        Artisan::call('images:backfill-variants');
        $this->assertStringContainsString('Processed: 0', Artisan::output(), 'every row is done, so a plain run selects none of them');

        Artisan::call('images:backfill-variants', ['--animated' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('re-checking every GIF and WebP for animation', $output);
        $this->assertStringContainsString('Processed: 3', $output);
        $this->assertStringContainsString('Animated: 2', $output);
        $this->assertTrue($webp->fresh()->imageIsAnimated());
        $this->assertVariants(
            ['w480' => 'flyer_moving_w480.webp', 'w960' => 'flyer_moving_w960.webp', 'src' => ['w' => 1, 'h' => 1], 'animated' => true],
            $moving->fresh()->image_variants
        );
        $this->assertVariants(
            ['w480' => 'flyer_still_w480.webp', 'w960' => 'flyer_still_w960.webp', 'src' => ['w' => 1, 'h' => 1]],
            $unmoving->fresh()->image_variants,
            'a GIF that does not move loses the flag'
        );
        $this->assertVariants(['w480' => 'flyer_poster_w480.webp', 'w960' => 'flyer_poster_w960.webp'], $poster->fresh()->image_variants);

        // Schedule images too, with --roles: an animated header here.
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Hall', 'header_image' => '', 'header_image_url' => 'header_moving.gif']);
        Storage::put(ImageUtils::storagePathFor('header_moving.gif'), $this->animatedGif($still));
        $venue->recordImageVariants(['w960' => 'header_moving_w960.webp', 'w1920' => 'header_moving_w1920.webp'], 'header');

        Artisan::call('images:backfill-variants', ['--roles' => true, '--slot' => 'all', '--animated' => true]);

        $this->assertStringContainsString('Processed: 1', Artisan::output());
        $this->assertTrue($venue->fresh()->imageIsAnimated('header'));
        $this->assertSame('header_moving_w1920.webp', $venue->fresh()->imageVariantFilename(1920, 'header'));

        // One or the other: --dimensions builds nothing, and --animated rebuilds.
        $this->assertSame(1, Artisan::call('images:backfill-variants', ['--animated' => true, '--dimensions' => true]));
        $this->assertStringContainsString('run them separately', Artisan::output());
    }

    /**
     * Whether an original moves is a fact about it, like its size: a run that never got to read
     * the file keeps what an earlier one recorded, in the job and in the backfill alike.
     */
    public function test_a_run_that_cannot_read_the_original_keeps_its_flag(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Moving Night', 'flyer_image_url' => 'flyer_moving.gif']);
        Storage::put(ImageUtils::storagePathFor('flyer_moving.gif'), $this->animatedGif(hex2bin(self::ONE_PIXEL_GIF)));

        // What a run during an object-storage outage leaves on a row known to move.
        $outage = ['w480' => 'flyer_moving_w480.webp', 'w960' => null, 'skipped' => 'write_failed', 'src' => ['w' => 1, 'h' => 1], 'animated' => true];
        $event->recordImageVariants($outage);
        $this->swapDisk($this->diskThatCannotRead());

        // The job, on the sync queue where it records rather than throws.
        (new GenerateEventImageVariants($event->id, 'flyer_moving.gif'))->handle();
        $this->assertVariants(['skipped' => 'read_failed'] + $outage, $event->fresh()->image_variants);

        // The backfill, which picks the transient skip up again unasked.
        $event->recordImageVariants($outage);
        Artisan::call('images:backfill-variants');
        $this->assertVariants(['skipped' => 'read_failed'] + $outage, $event->fresh()->image_variants);
    }

    private function jsonLdNode(string $html, string $type): array
    {
        preg_match_all('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $m);

        foreach ($m[1] as $block) {
            $node = json_decode($block, true);

            if (($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        $this->fail('No '.$type.' JSON-LD node on the page');
    }

    private function imgTagFor(string $html, string $src): string
    {
        preg_match('/<img src="'.preg_quote($src, '/').'"[^>]*>/', $html, $m);

        return $m[0] ?? '';
    }
}
