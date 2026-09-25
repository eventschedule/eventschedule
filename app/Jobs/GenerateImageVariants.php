<?php

namespace App\Jobs;

use App\Traits\HasImageVariants;
use App\Utils\ImageUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Build the resized WebP derivatives of one stored image and record them on its row.
 *
 * Shared by GenerateEventImageVariants (flyers) and GenerateRoleImageVariants (a schedule's
 * profile photo, header or background); a subclass says which row, which image slot
 * (HasImageVariants::imageVariantSlots()) and which stored filename, and everything else - the
 * widths, the failure split below, the merge onto what is already recorded, the guarded write -
 * follows from the slot.
 *
 * Queue latency is up to a minute on the hosted deploy (the queue is drained by the scheduler's
 * process-queue entry, not a resident worker). That is fine: every consumer falls back to the
 * original until the derivative is recorded.
 *
 * Failure handling is split the same way ImageUtils splits its reasons, and the split matters
 * most on the `sync` queue - the selfhost default - where this runs INLINE inside the save() that
 * dispatched it, so anything escaping handle() turns a successful image upload into a 500:
 *
 *  - Deterministic (a demo image, an unreadable or oversized original, a GD without WebP): the
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
abstract class GenerateImageVariants implements ShouldQueue
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
     * The row to resize for, freshly loaded, or null if it is gone.
     *
     * @return (Model&HasImageVariants)|null
     */
    abstract protected function findModel(): ?Model;

    /**
     * The raw stored filename at dispatch time. Compared against the row so an image that was
     * replaced while the job waited in the queue is not resized, and so a derivative of the OLD
     * file can never be recorded against the NEW one.
     */
    abstract protected function storedName(): string;

    /** "event 12", "schedule 7": for log lines only. */
    abstract protected function subject(): string;

    /**
     * Which of the model's image slots this job builds. Everything but a schedule has only the
     * one, 'default'.
     */
    protected function variantSlot(): string
    {
        return 'default';
    }

    public function handle(): void
    {
        $model = $this->findModel();

        if (! $model) {
            return;
        }

        $slot = $this->variantSlot();
        $raw = $model->imageVariantSource($slot);

        if ($raw === null || $raw !== $this->storedName()) {
            return;
        }

        $widths = $model->imageVariantWidths($slot);
        $existing = $model->imageVariants($slot);
        // A size recorded by an earlier run (or --dimensions) survives a run that never got to
        // read the original, like the filenames below. So does the animated flag.
        $knownSrc = is_array($existing['src'] ?? null) ? $existing['src'] : null;
        $knownAnimated = ($existing['animated'] ?? false) === true;

        // Every width, not just the default: a row carrying only w480 predates the second width
        // and still needs one.
        $missing = array_filter(
            $widths,
            fn (int $width) => ! $model->imageVariantFilename($width, $slot)
        );

        // Nothing to build, and nothing to record either. A row only arrives here complete from
        // an earlier run of this job - the saving hooks null the column whenever the image
        // changes, and only then queue this - and that run recorded `animated` already. Rows
        // completed before the flag existed are what `images:backfill-variants --animated`
        // re-checks.
        if (! $missing) {
            return;
        }

        try {
            $results = ImageUtils::generateStoredVariants($raw, $widths);
        } catch (\Throwable $e) {
            // GD or the disk layer blew up rather than returning a reason. Nothing here can tell
            // whether another attempt would go better, and the cost of guessing wrong in the
            // optimistic direction is a 500 on a save that otherwise succeeded, so this is
            // treated as deterministic: reported, recorded, swallowed.
            report($e);
            Log::warning(class_basename($this).' failed for '.$this->subject().': '.$e->getMessage());
            $model->recordImageVariants($this->payload($widths, [], 'failed', $knownSrc, $knownAnimated), $slot);

            return;
        }

        // Merged onto what is already recorded, never rebuilt from scratch. A width that
        // failed this time must not erase a filename recorded by an earlier run: the file is
        // still on the disk (names are deterministic from the original, and recordImageVariants()
        // guards its UPDATE on that original being unchanged), so dropping the record just makes
        // every card fall back to the full-size original for no reason. `skipped` is rewritten
        // or removed below rather than merged, so a stale reason cannot survive a good run.
        $variants = [];
        $transient = null;
        $deterministic = null;
        $deterministicDetail = null;
        $src = $knownSrc;
        $animated = $knownAnimated;

        foreach ($widths as $width) {
            $result = $results[$width] ?? ['ok' => false, 'filename' => null, 'reason' => 'failed'];

            // The original's displayed size, which every width's result carries alike. Recorded
            // whether or not this width worked: it is a fact about the original, and a too_large
            // skip reports the header's size precisely so a page can still declare it.
            if (is_array($result['src'] ?? null)) {
                $src = $result['src'];
            }

            // Whether the original moves, the same kind of fact: true or false wherever this run
            // read the file, which then decides it either way, and null where it never did.
            if (is_bool($result['animated'] ?? null)) {
                $animated = $result['animated'];
            }

            $kept = $existing['w'.$width] ?? null;
            $variants['w'.$width] = $result['ok']
                ? $result['filename']
                : (is_string($kept) && $kept !== '' ? $kept : null);

            if ($result['ok']) {
                continue;
            }

            if (ImageUtils::isTransientVariantReason($result['reason'])) {
                $transient ??= $result['reason'];
            } elseif ($deterministic === null) {
                $deterministic = $result['reason'];
                // Display only, and only for the log line below: recordImageVariants() must keep
                // receiving the bare token the backfill's query matches on.
                $deterministicDetail = $result['detail'] ?? null;
            }
        }

        // A transient reason means the disk answered badly rather than the image being
        // unusable, so trying again can genuinely produce a different answer - but only where
        // trying again actually happens. See willBeRetried(): on the `sync` connection nothing
        // retries, and a throw here escapes the save() that dispatched this job.
        if ($transient !== null && $this->willBeRetried()) {
            // Deliberately before any recording: the column stays null, so the retry (and the
            // backfill after it) still sees a row that needs doing.
            throw new \RuntimeException(
                class_basename($this).' could not reach storage for '.$this->subject().': '.$transient
            );
        }

        if ($transient !== null) {
            // Recorded rather than thrown: BackfillImageVariants::baseQuery() re-selects any
            // row whose `skipped` is one of ImageUtils::VARIANT_TRANSIENT_REASONS without
            // needing --retry-skipped, so the row is not lost. It is not picked up on its own
            // either - that command is operator-run (see the class docblock) - which is why
            // this logs at WARNING: the save succeeded, but somebody has work to do.
            Log::warning(class_basename($this).' could not reach storage for '
                .$this->subject().': '.$transient.' (left for images:backfill-variants)');
        }

        if ($deterministic !== null) {
            Log::info(class_basename($this).' skipped '.$this->subject().': '.$deterministic
                .($deterministicDetail !== null ? ' ('.$deterministicDetail.')' : ''));
        }

        // Transient wins the `skipped` slot when both happened, because it is the one the
        // backfill's un-flagged query keys on; a deterministic reason recorded over it would
        // strand the row until someone ran --retry-skipped by hand.
        $model->recordImageVariants($this->payload($widths, $variants, $transient ?? $deterministic, $src, $animated), $slot);
    }

    /**
     * Will a failure here actually be retried?
     *
     * Only on a real queue. `sync` - the selfhost default (.env.example) - runs the job INLINE
     * inside the save() that dispatched it, and SyncQueue::executeJob() has no retry loop at all:
     * it catches once and handleException() rethrows without ever consulting $tries. So a throw
     * on that connection is not a retry, it is an HTTP 500 on an image upload whose row has
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
     * The variants value to store: one key per width of the slot, plus the reason when at least
     * one width was skipped for good, plus the original's size when it is known, plus
     * `"animated": true` for an original that moves (HasImageVariants::imageIsAnimated()). A
     * still one records no flag at all, which is what every row built before the flag reads as.
     *
     * @param  int[]  $widths
     * @param  array{w: int, h: int}|null  $src
     */
    private function payload(array $widths, array $variants, ?string $skipped, ?array $src = null, bool $animated = false): array
    {
        foreach ($widths as $width) {
            $variants['w'.$width] = $variants['w'.$width] ?? null;
        }

        if ($skipped !== null) {
            $variants['skipped'] = $skipped;
        }

        if ($src !== null) {
            $variants['src'] = $src;
        }

        if ($animated) {
            $variants['animated'] = true;
        }

        return $variants;
    }
}
