<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Utils\ImageUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Build the missing WebP derivatives for flyers that predate the generation hook.
 *
 * Two passes, upcoming events first: those are the ones the homepage wall, the discover rail and
 * /browse actually render, so the first pass is what fixes the page. The second pass works
 * through the rest of the catalogue and can be left to run separately.
 *
 * Generates EVERY width in ImageUtils::VARIANT_WIDTHS, and a row counts as done only when it has
 * all of them - which is what picks up the rows built when 480 was the only width.
 *
 * Resumable by construction: every row is left with either a derivative filename or a `skipped`
 * reason, and a DETERMINISTIC skip is filtered out of the next run's query (use --retry-skipped
 * to reconsider those, e.g. after re-uploading an original that had gone missing). A TRANSIENT
 * skip - the disk would not hand the original over, or would not take the derivative - is
 * re-attempted by the next plain run, because the answer really can be different next time.
 */
class BackfillImageVariants extends Command
{
    protected $signature = 'images:backfill-variants
        {--upcoming-only : Stop after upcoming and recurring events, skipping past ones}
        {--retry-skipped : Also reprocess rows whose recorded skip was deterministic (transient ones are always retried)}
        {--limit=0 : Stop after this many events (0 = no limit)}
        {--chunk=100 : Rows per database chunk}
        {--dry-run : List what would be generated without touching storage}';

    protected $description = 'Generate the resized WebP derivatives of every event flyer that is missing one.';

    private int $processed = 0;

    private int $generated = 0;

    private int $skipped = 0;

    private int $limit = 0;

    public function handle(): int
    {
        // Reset explicitly: Artisan registers each command as a single instance, so a second
        // call in the same process (a test, or the scheduler rail) would otherwise report the
        // first run's totals on top of its own.
        $this->processed = 0;
        $this->generated = 0;
        $this->skipped = 0;

        $this->limit = max(0, (int) $this->option('limit'));
        $chunk = max(10, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Target widths: '.implode('px, ', ImageUtils::VARIANT_WIDTHS).'px WebP'.($dryRun ? ' (dry run)' : ''));

        $this->runPass('upcoming', $chunk, $dryRun, function (Builder $query) {
            $query->where(function ($q) {
                $q->where('starts_at', '>=', Carbon::today())
                    ->orWhereNotNull('days_of_week');
            });
        });

        if (! $this->option('upcoming-only') && ! $this->limitReached()) {
            // The exact complement of the pass above, so a dateless one-off event (starts_at
            // null, no days_of_week) is picked up by the second pass instead of by neither.
            $this->runPass('past', $chunk, $dryRun, function (Builder $query) {
                $query->whereNull('days_of_week')
                    ->where(function ($q) {
                        $q->where('starts_at', '<', Carbon::today())
                            ->orWhereNull('starts_at');
                    });
            });
        }

        $this->info("Done. Processed: {$this->processed}, generated: {$this->generated}, skipped: {$this->skipped}");

        return self::SUCCESS;
    }

    private function runPass(string $label, int $chunk, bool $dryRun, callable $scope): void
    {
        if ($this->limitReached()) {
            return;
        }

        $this->line("Pass: {$label}");

        $query = $this->baseQuery();
        $scope($query);

        // chunkById, not chunk: the pass writes to the rows it is walking, and an offset-based
        // page would then skip rows as the result set shifts under it.
        $query->chunkById($chunk, function ($events) use ($dryRun) {
            foreach ($events as $event) {
                $this->processEvent($event, $dryRun);

                if ($this->limitReached()) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Events with a resizable flyer that is missing at least one of the target widths.
     *
     * The JSON filter has to go through JSON_TYPE: `JSON_EXTRACT(col, '$.w480') IS NULL` is FALSE
     * for a recorded skip, because a JSON null is a value, not SQL NULL. COALESCE supplies the
     * third state (the key is absent entirely).
     *
     * "Done" requires EVERY width, so the clause is ORed across the list. That is what makes
     * adding a width to ImageUtils::VARIANT_WIDTHS enough on its own: rows holding only w480 have
     * the w960 key MISSING and are picked up by the next plain run.
     *
     * A recorded skip is then filtered by its reason, not by its existence: a transient one gets
     * another go unasked, a deterministic one waits for --retry-skipped.
     */
    private function baseQuery(): Builder
    {
        $retrySkipped = (bool) $this->option('retry-skipped');

        return Event::query()
            ->whereNotNull('flyer_image_url')
            ->where('flyer_image_url', '!=', '')
            ->where('flyer_image_url', 'not like', 'demo\_%')
            ->where('flyer_image_url', 'not like', 'http%')
            ->where(function ($q) use ($retrySkipped) {
                $q->whereNull('image_variants');

                foreach (ImageUtils::VARIANT_WIDTHS as $width) {
                    $key = '$.w'.$width;

                    if ($retrySkipped) {
                        $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT(image_variants, ?)), 'MISSING') <> 'STRING'", [$key]);
                    } else {
                        $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT(image_variants, ?)), 'MISSING') = 'MISSING'", [$key]);
                    }
                }

                // --retry-skipped's <> 'STRING' clause already covers these.
                if (! $retrySkipped) {
                    $placeholders = implode(',', array_fill(0, count(ImageUtils::VARIANT_TRANSIENT_REASONS), '?'));

                    $q->orWhereRaw(
                        'JSON_UNQUOTE(JSON_EXTRACT(image_variants, ?)) IN ('.$placeholders.')',
                        array_merge(['$.skipped'], ImageUtils::VARIANT_TRANSIENT_REASONS)
                    );
                }
            });
    }

    private function processEvent(Event $event, bool $dryRun): void
    {
        $this->processed++;

        $raw = $event->getAttributes()['flyer_image_url'] ?? null;

        if ($dryRun) {
            $names = array_map(fn (int $width) => ImageUtils::variantFilename($raw, $width), ImageUtils::VARIANT_WIDTHS);
            $this->line("  [{$event->id}] would generate ".implode(', ', $names));
            $this->generated++;

            return;
        }

        // One bad image must never abort a run of thousands. Unlike the queue job, which throws
        // on a transient reason so it is retried, this records what happened and moves on - the
        // next plain run comes back to it.
        try {
            $results = ImageUtils::generateStoredVariants($raw);
        } catch (\Throwable $e) {
            report($e);
            $this->warn("  [{$event->id}] error: ".$e->getMessage());
            $this->skipped++;

            return;
        }

        // Merged onto what is already recorded, never rebuilt from scratch. A width that
        // failed this time must not erase a filename recorded by an earlier run: the file is
        // still on the disk (names are deterministic from the flyer, and recordImageVariants()
        // guards its UPDATE on that flyer being unchanged), so dropping the record just makes
        // every card fall back to the full-size original for no reason. `skipped` is rewritten
        // or removed below rather than merged, so a stale reason cannot survive a good run.
        $variants = [];
        $written = [];
        $reason = null;
        $existing = $event->image_variants;
        $existing = is_array($existing) ? $existing : [];

        foreach (ImageUtils::VARIANT_WIDTHS as $width) {
            $result = $results[$width] ?? ['ok' => false, 'filename' => null, 'reason' => 'failed'];
            $kept = $existing['w'.$width] ?? null;
            $variants['w'.$width] = $result['ok']
                ? $result['filename']
                : (is_string($kept) && $kept !== '' ? $kept : null);

            if ($result['ok']) {
                $written[] = $result['filename'];
            } else {
                $reason ??= $result['reason'];
            }
        }

        if ($reason !== null) {
            $variants['skipped'] = $reason;
        }

        $event->recordImageVariants($variants);

        // A partial run counts as skipped: the row still needs another pass.
        if ($reason !== null) {
            $this->skipped++;
            $this->line("  [{$event->id}] skipped: {$reason}");
            Log::info("images:backfill-variants skipped event {$event->id}: {$reason}");

            return;
        }

        $this->generated++;
        $this->line("  [{$event->id}] ".implode(', ', $written));
    }

    private function limitReached(): bool
    {
        return $this->limit > 0 && $this->processed >= $this->limit;
    }
}
