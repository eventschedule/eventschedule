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
 *    backfill to find - but ONLY on a connection where $tries means something. On `sync` there is
 *    no retry loop (SyncQueue::executeJob catches once and rethrows), so the throw would be the
 *    500 this list exists to prevent; there the reason is recorded like a deterministic one and
 *    `images:backfill-variants` re-selects it, which its baseQuery() does for a transient
 *    `skipped` without needing --retry-skipped. That command is OPERATOR-RUN - it is on neither
 *    cron rail - so on `sync` the recovery is a thumbnail that stays unbuilt until someone runs
 *    it. The card renders the original meanwhile, so the page is correct and only heavier.
 *    See willBeRetried().
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

        // Merged onto what is already recorded, never rebuilt from scratch. A width that
        // failed this time must not erase a filename recorded by an earlier run: the file is
        // still on the disk (names are deterministic from the flyer, and recordImageVariants()
        // guards its UPDATE on that flyer being unchanged), so dropping the record just makes
        // every card fall back to the full-size original for no reason. `skipped` is rewritten
        // or removed below rather than merged, so a stale reason cannot survive a good run.
        $variants = [];
        $transient = null;
        $deterministic = null;
        $existing = $event->image_variants;
        $existing = is_array($existing) ? $existing : [];

        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $result = $results[$width] ?? ['ok' => false, 'filename' => null, 'reason' => 'failed'];
            $kept = $existing['w'.$width] ?? null;
            $variants['w'.$width] = $result['ok']
                ? $result['filename']
                : (is_string($kept) && $kept !== '' ? $kept : null);

            if ($result['ok']) {
                continue;
            }

            if (ImageUtils::isTransientVariantReason($result['reason'])) {
                $transient ??= $result['reason'];
            } else {
                $deterministic ??= $result['reason'];
            }
        }

        // A transient reason means the disk answered badly rather than the image being
        // unusable, so trying again can genuinely produce a different answer - but only where
        // trying again actually happens. See willBeRetried(): on the `sync` connection nothing
        // retries, and a throw here escapes the Event::save() that dispatched this job.
        if ($transient !== null && $this->willBeRetried()) {
            // Deliberately before any recording: the column stays null, so the retry (and the
            // backfill after it) still sees a row that needs doing.
            throw new \RuntimeException(
                'GenerateEventImageVariants could not reach storage for event '.$this->eventId.': '.$transient
            );
        }

        if ($transient !== null) {
            // Recorded rather than thrown: BackfillImageVariants::baseQuery() re-selects any
            // row whose `skipped` is one of ImageUtils::VARIANT_TRANSIENT_REASONS without
            // needing --retry-skipped, so the row is not lost. It is not picked up on its own
            // either - that command is operator-run (see the class docblock) - which is why
            // this logs at WARNING: the save succeeded, but somebody has work to do.
            Log::warning('GenerateEventImageVariants could not reach storage for event '
                .$this->eventId.': '.$transient.' (left for images:backfill-variants)');
        }

        if ($deterministic !== null) {
            Log::info('GenerateEventImageVariants skipped event '.$this->eventId.': '.$deterministic);
        }

        // Transient wins the `skipped` slot when both happened, because it is the one the
        // backfill's un-flagged query keys on; a deterministic reason recorded over it would
        // strand the row until someone ran --retry-skipped by hand.
        $event->recordImageVariants($this->payload($variants, $transient ?? $deterministic));
    }

    /**
     * Will a failure here actually be retried?
     *
     * Only on a real queue. `sync` - the selfhost default (.env.example) - runs the job INLINE
     * inside the save() that dispatched it, and SyncQueue::executeJob() has no retry loop at all:
     * it catches once and handleException() rethrows without ever consulting $tries. So a throw
     * on that connection is not a retry, it is an HTTP 500 on a flyer upload whose event row has
     * already been committed - the exact failure this class's docblock is written against.
     *
     * $this->job is null when handle() is invoked directly (a test, tinker), which is inline for
     * the same reason, so fall back to the configured connection rather than assuming a worker.
     */
    private function willBeRetried(): bool
    {
        return ($this->job?->getConnectionName() ?? config('queue.default')) !== 'sync';
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
