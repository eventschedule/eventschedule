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
 * Resumable by construction: every row is left with either a derivative filename or a `skipped`
 * reason, and both are filtered out of the next run's query (use --retry-skipped to reconsider
 * the skips, e.g. after re-uploading an original that had gone missing).
 */
class BackfillImageVariants extends Command
{
    protected $signature = 'images:backfill-variants
        {--upcoming-only : Stop after upcoming and recurring events, skipping past ones}
        {--retry-skipped : Also reprocess rows previously recorded as skipped}
        {--limit=0 : Stop after this many events (0 = no limit)}
        {--chunk=100 : Rows per database chunk}
        {--dry-run : List what would be generated without touching storage}';

    protected $description = 'Generate the resized WebP derivative of every event flyer that does not have one yet.';

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

        $this->info('Target width: '.ImageUtils::VARIANT_WIDTH.'px WebP'.($dryRun ? ' (dry run)' : ''));

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
     * Events with a resizable flyer and no derivative recorded for the target width.
     *
     * The JSON filter has to go through JSON_TYPE: `JSON_EXTRACT(col, '$.w480') IS NULL` is FALSE
     * for a recorded skip, because a JSON null is a value, not SQL NULL. COALESCE supplies the
     * third state (the key is absent entirely).
     */
    private function baseQuery(): Builder
    {
        $key = '$.w'.ImageUtils::VARIANT_WIDTH;
        $retrySkipped = (bool) $this->option('retry-skipped');

        return Event::query()
            ->whereNotNull('flyer_image_url')
            ->where('flyer_image_url', '!=', '')
            ->where('flyer_image_url', 'not like', 'demo\_%')
            ->where('flyer_image_url', 'not like', 'http%')
            ->where(function ($q) use ($key, $retrySkipped) {
                $q->whereNull('image_variants');

                if ($retrySkipped) {
                    $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT(image_variants, ?)), 'MISSING') <> 'STRING'", [$key]);
                } else {
                    $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT(image_variants, ?)), 'MISSING') = 'MISSING'", [$key]);
                }
            });
    }

    private function processEvent(Event $event, bool $dryRun): void
    {
        $this->processed++;

        $raw = $event->getAttributes()['flyer_image_url'] ?? null;

        if ($dryRun) {
            $this->line("  [{$event->id}] would generate ".ImageUtils::variantFilename($raw, ImageUtils::VARIANT_WIDTH));
            $this->generated++;

            return;
        }

        // One bad image must never abort a run of thousands.
        try {
            $result = ImageUtils::generateStoredVariant($raw, ImageUtils::VARIANT_WIDTH);
        } catch (\Throwable $e) {
            report($e);
            $this->warn("  [{$event->id}] error: ".$e->getMessage());
            $this->skipped++;

            return;
        }

        $key = 'w'.ImageUtils::VARIANT_WIDTH;

        if ($result['ok']) {
            $event->recordImageVariants([$key => $result['filename']]);
            $this->generated++;
            $this->line("  [{$event->id}] {$result['filename']}");

            return;
        }

        $event->recordImageVariants([$key => null, 'skipped' => $result['reason']]);
        $this->skipped++;
        $this->line("  [{$event->id}] skipped: {$result['reason']}");
        Log::info("images:backfill-variants skipped event {$event->id}: {$result['reason']}");
    }

    private function limitReached(): bool
    {
        return $this->limit > 0 && $this->processed >= $this->limit;
    }
}
