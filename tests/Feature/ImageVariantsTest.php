<?php

namespace Tests\Feature;

use App\Jobs\GenerateEventImageVariants;
use App\Models\BackupJob;
use App\Models\Event;
use App\Services\BackupService;
use App\Utils\ImageUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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
        Queue::fake();
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

    // ----------------------------------------------------------- model reads

    public function test_get_image_url_returns_the_variant_only_when_one_is_recorded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        // No variant recorded yet: the original, at every width.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(480));
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl());

        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);

        $this->assertStringEndsWith('flyer_abc123_w480.webp', $event->getImageUrl(480));
        // No width asked for means no derivative: full-size consumers keep the original.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl());
        // A width with no recorded derivative falls back rather than 404ing.
        $this->assertStringEndsWith('flyer_abc123.png', $event->getImageUrl(1200));
    }

    public function test_a_recorded_skip_is_not_mistaken_for_a_derivative(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);

        $event->recordImageVariants(['w480' => null, 'skipped' => 'too_large']);

        $this->assertNull($event->imageVariantFilename(480));
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

    public function test_replacing_the_flyer_clears_the_recorded_variants(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $event->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);

        $event->flyer_image_url = 'flyer_def456.png';
        $event->save();

        $this->assertNull($event->fresh()->image_variants);
        $this->assertStringEndsWith('flyer_def456.png', $event->fresh()->getImageUrl(480));
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

    public function test_the_job_generates_and_records_the_derivative(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png');

        (new GenerateEventImageVariants($event->id, 'flyer_abc123.png'))->handle();

        Storage::assertExists('public/flyer_abc123_w480.webp');
        $this->assertSame(['w480' => 'flyer_abc123_w480.webp'], $event->fresh()->image_variants);
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

        (new GenerateEventImageVariants($event->id, 'flyer_huge.png'))->handle();

        $this->assertSame(['w480' => null, 'skipped' => 'too_large'], $event->fresh()->image_variants);
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
        $this->assertSame(['w480' => 'flyer_abc123_w480.webp'], $event->fresh()->image_variants);

        // Second run: the row is filtered out by the query, so nothing is even read.
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());
    }

    public function test_the_backfill_command_records_and_then_respects_a_skip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_huge.png']);
        $this->storeOversizedHeader('flyer_huge.png', 5000, 4000);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertSame(['w480' => null, 'skipped' => 'too_large'], $event->fresh()->image_variants);

        Artisan::call('images:backfill-variants', ['--upcoming-only' => true]);
        $this->assertStringContainsString('Processed: 0', Artisan::output());

        // A recorded skip is reconsidered only on request. Give it a usable original this time.
        Storage::delete('public/flyer_huge.png');
        $this->storeFlyer('flyer_huge.png', 600, 800);
        Artisan::call('images:backfill-variants', ['--upcoming-only' => true, '--retry-skipped' => true]);

        $this->assertSame(['w480' => 'flyer_huge_w480.webp'], $event->fresh()->image_variants);
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
        $this->assertSame(['w480' => 'flyer_past_w480.webp'], $past->fresh()->image_variants);
    }

    public function test_the_backfill_dry_run_writes_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'flyer_image_url' => 'flyer_abc123.png']);
        $this->storeFlyer('flyer_abc123.png');

        Artisan::call('images:backfill-variants', ['--dry-run' => true]);

        $this->assertStringContainsString('flyer_abc123_w480.webp', Artisan::output());
        Storage::assertMissing('public/flyer_abc123_w480.webp');
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
        $withVariant->recordImageVariants(['w480' => 'flyer_abc123_w480.webp']);

        $withoutVariant = $this->createEvent($role, ['name' => 'Winter Session', 'flyer_image_url' => 'flyer_def456.png']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('flyer_abc123_w480.webp', $html);
        $this->assertStringNotContainsString('flyer_abc123.png', $html, 'The original must not be requested when a derivative exists');
        // No derivative recorded: the original is still served, so no card ever breaks.
        $this->assertStringContainsString('flyer_def456.png', $html);
        $this->assertNotNull($withoutVariant->id);
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
}
