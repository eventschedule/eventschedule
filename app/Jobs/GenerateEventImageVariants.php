<?php

namespace App\Jobs;

use App\Models\Event;
use App\Utils\ImageUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Build the resized WebP derivatives of an event flyer.
 *
 * Dispatched from Event's created/updated hooks rather than from each controller, because the
 * flyer is written from nine different places (web upload, API, guest submit, guest import, AI
 * flyer, Eventbrite, WhatsApp, curator import, clone) and every one of them ends in an Eloquent
 * save(). The one path that does not is BackupService's restore, which uses saveQuietly() on
 * purpose; `php artisan images:backfill-variants` covers those rows.
 *
 * Queue latency is up to a minute on the hosted deploy (the queue is drained by the scheduler's
 * process-queue entry, not a resident worker). That is fine: every consumer falls back to the
 * original until the derivative is recorded.
 *
 * Failure handling is split the same way ImageUtils splits its reasons, and the split matters
 * most on the `sync` queue - the selfhost default - where this runs INLINE inside the save() that
 * dispatched it, so anything escaping handle() turns a successful flyer upload into a 500:
 *
 *  - Deterministic (a demo flyer, an unreadable or oversized original, a GD without WebP): the
 *    reason is recorded on the row and the job returns normally. A retry would decode the same
 *    bytes with the same extensions and reach the same answer.
 *  - Transient (the disk would not hand the file over, or would not take the derivative): the job
 *    THROWS and records nothing, so $tries brings it back and the column stays null for the
 *    backfill to find.
 */
class GenerateEventImageVariants implements ShouldQueue
{
    use Queueable;

    /**
     * Two attempts, not the default of one-plus-retries-forever: the work is bounded by the
     * pixel guard in the helper, so a failure here is a disk or network problem, not something
     * that gets better on the tenth try.
     */
    public int $tries = 2;

    public int $timeout = 120;

    /**
     * @param  int  $eventId  The event whose flyer to resize.
     * @param  string  $flyer  The raw stored filename at dispatch time. Carried so a flyer that
     *                         was replaced while the job waited in the queue is not resized, and
     *                         so a derivative of the OLD file can never be recorded against the
     *                         NEW one.
     */
    public function __construct(public int $eventId, public string $flyer) {}

    public function handle(): void
    {
        $event = Event::find($this->eventId);

        if (! $event) {
            return;
        }

        $raw = $event->getAttributes()['flyer_image_url'] ?? null;

        if ($raw !== $this->flyer) {
            return;
        }

        // Every width, not just the default: a row carrying only w480 predates the second width
        // and still needs one.
        $missing = array_filter(
            ImageUtils::VARIANT_WIDTHS,
            fn (int $width) => ! $event->imageVariantFilename($width)
        );

        if (! $missing) {
            return;
        }

        try {
            $results = ImageUtils::generateStoredVariants($raw);
        } catch (\Throwable $e) {
            // GD or the disk layer blew up rather than returning a reason. Nothing here can tell
            // whether another attempt would go better, and the cost of guessing wrong in the
            // optimistic direction is a 500 on a save that otherwise succeeded, so this is
            // treated as deterministic: reported, recorded, swallowed.
            report($e);
            Log::warning('GenerateEventImageVariants failed for event '.$this->eventId.': '.$e->getMessage());
            $event->recordImageVariants($this->payload([], 'failed'));

            return;
        }

        $variants = [];
        $transient = null;
        $deterministic = null;

        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $result = $results[$width] ?? ['ok' => false, 'filename' => null, 'reason' => 'failed'];
            $variants['w'.$width] = $result['ok'] ? $result['filename'] : null;

            if ($result['ok']) {
                continue;
            }

            if (ImageUtils::isTransientVariantReason($result['reason'])) {
                $transient ??= $result['reason'];
            } else {
                $deterministic ??= $result['reason'];
            }
        }

        if ($transient !== null) {
            // Deliberately before any recording: the column stays null, so the retry (and the
            // backfill after it) still sees a row that needs doing.
            throw new \RuntimeException(
                'GenerateEventImageVariants could not reach storage for event '.$this->eventId.': '.$transient
            );
        }

        if ($deterministic !== null) {
            Log::info('GenerateEventImageVariants skipped event '.$this->eventId.': '.$deterministic);
        }

        $event->recordImageVariants($this->payload($variants, $deterministic));
    }

    /**
     * The `image_variants` value to store: one key per width, plus the reason when at least one
     * width was skipped for good.
     */
    private function payload(array $variants, ?string $skipped): array
    {
        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $variants['w'.$width] = $variants['w'.$width] ?? null;
        }

        if ($skipped !== null) {
            $variants['skipped'] = $skipped;
        }

        return $variants;
    }
}
