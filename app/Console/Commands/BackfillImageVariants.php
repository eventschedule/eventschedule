<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Utils\ImageUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
 *
 * `--roles` walks schedule images instead of flyers, one pass per image slot
 * (Role::imageVariantSlots()). `--slot` picks which: the profile photo by default, which is what
 * `--roles` always meant (it is the card image of every event without a flyer, Event::getImageUrl(),
 * so the homepage wall used to serve a 2MB original into a 96px slot once per event wearing it);
 * `header` and `background`, the wide images an owner can upload, built at
 * ImageUtils::BANNER_VARIANT_WIDTHS; or `all` three. The background comes first when an operator
 * runs them one at a time: it is the schedule page's LCP image on a phone.
 *
 * `--dimensions` builds nothing. It records the original's size (`src`) on rows that lack one -
 * those built before the pipeline recorded it - reading only the head of each original
 * (ImageUtils::storedImageDimensions()), never decoding or re-encoding it. That size is what lets
 * og:image:width and an <img>'s width and height describe a file on object storage.
 */
class BackfillImageVariants extends Command
{
    protected $signature = 'images:backfill-variants
        {--roles : Process schedule images instead of event flyers (the profile photo unless --slot says otherwise)}
        {--slot=profile : With --roles, which image: profile, header, background or all}
        {--dimensions : Only record the size of originals whose size is not recorded yet, generating nothing}
        {--upcoming-only : Stop after upcoming and recurring events, skipping past ones (ignored with --roles)}
        {--retry-skipped : Also reprocess rows whose recorded skip was deterministic (transient ones are always retried)}
        {--limit=0 : Stop after this many rows (0 = no limit)}
        {--chunk=100 : Rows per database chunk}
        {--dry-run : List what would be generated without touching storage}';

    protected $description = 'Generate the resized WebP derivatives of every event flyer (or, with --roles, schedule image) that is missing one, or with --dimensions record the size of each original.';

    /** --slot's values, as Role::imageVariantSlots() names them. */
    private const ROLE_SLOTS = [
        'profile' => ['default'],
        'header' => ['header'],
        'background' => ['background'],
        'all' => ['default', 'header', 'background'],
    ];

    /** A pass label per slot. "schedules" is what the profile pass has always printed. */
    private const ROLE_PASS_LABELS = [
        'default' => 'schedules',
        'header' => 'schedule headers',
        'background' => 'schedule backgrounds',
    ];

    private int $processed = 0;

    private int $generated = 0;

    private int $skipped = 0;

    /** @var array<string, int> reason => count, for the run summary */
    private array $skippedReasons = [];

    private int $limit = 0;

    private bool $dimensionsOnly = false;

    public function handle(): int
    {
        // Reset explicitly: Artisan registers each command as a single instance, so a second
        // call in the same process (a test, or the scheduler rail) would otherwise report the
        // first run's totals on top of its own.
        $this->processed = 0;
        $this->generated = 0;
        $this->skipped = 0;
        $this->skippedReasons = [];

        $this->limit = max(0, (int) $this->option('limit'));
        $chunk = max(10, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $this->dimensionsOnly = (bool) $this->option('dimensions');

        $slotOption = strtolower(trim((string) ($this->option('slot') ?? 'profile')));

        if (! isset(self::ROLE_SLOTS[$slotOption])) {
            $this->error('Unknown --slot "'.$slotOption.'". Use one of: '.implode(', ', array_keys(self::ROLE_SLOTS)).'.');

            return self::FAILURE;
        }

        // Refused rather than ignored: an operator who typed --slot=background and got a flyer run
        // would believe the backgrounds were done.
        if ($slotOption !== 'profile' && ! $this->option('roles')) {
            $this->error('--slot applies to schedule images, so it needs --roles.');

            return self::FAILURE;
        }

        if ($this->option('roles')) {
            foreach (self::ROLE_SLOTS[$slotOption] as $slot) {
                $this->announce((new Role)->imageVariantWidths($slot), $dryRun);
                $this->runPass(self::ROLE_PASS_LABELS[$slot], Role::query(), $chunk, $dryRun, fn (Builder $query) => null, $slot);
            }
        } else {
            $this->announce(ImageUtils::VARIANT_WIDTHS, $dryRun);
            $this->runEventPasses($chunk, $dryRun);
        }

        $done = $this->dimensionsOnly ? 'recorded' : 'generated';
        $this->info("Done. Processed: {$this->processed}, {$done}: {$this->generated}, skipped: {$this->skipped}");

        // Which reasons, not just how many. Nothing else reads the recorded `skipped` values back
        // out, so without this the only way to find out why a production run skipped rows is to
        // open a SQL console against the table.
        if ($this->skippedReasons) {
            arsort($this->skippedReasons);
            $parts = [];
            foreach ($this->skippedReasons as $reason => $count) {
                $parts[] = "{$reason}: {$count}";
            }
            $this->line('  Skipped by reason - '.implode(', ', $parts));
        }

        return self::SUCCESS;
    }

    private function runEventPasses(int $chunk, bool $dryRun): void
    {
        $this->runPass('upcoming', Event::query(), $chunk, $dryRun, function (Builder $query) {
            $query->where(function ($q) {
                $q->where('starts_at', '>=', Carbon::today())
                    ->orWhereNotNull('days_of_week');
            });
        });

        if (! $this->option('upcoming-only') && ! $this->limitReached()) {
            // The exact complement of the pass above, so a dateless one-off event (starts_at
            // null, no days_of_week) is picked up by the second pass instead of by neither.
            $this->runPass('past', Event::query(), $chunk, $dryRun, function (Builder $query) {
                $query->whereNull('days_of_week')
                    ->where(function ($q) {
                        $q->where('starts_at', '<', Carbon::today())
                            ->orWhereNull('starts_at');
                    });
            });
        }
    }

    /** @param  int[]  $widths */
    private function announce(array $widths, bool $dryRun): void
    {
        $this->info(
            ($this->dimensionsOnly ? 'Recording original sizes only, no derivatives' : 'Target widths: '.implode('px, ', $widths).'px WebP')
            .($dryRun ? ' (dry run)' : '')
        );
    }

    /**
     * @param  Builder  $query  A fresh query on the model to walk (Event or Role).
     * @param  string  $slot  Which of the model's image slots (HasImageVariants::imageVariantSlots()).
     */
    private function runPass(string $label, Builder $query, int $chunk, bool $dryRun, callable $scope, string $slot = 'default'): void
    {
        if ($this->limitReached()) {
            return;
        }

        $this->line("Pass: {$label}");

        $this->dimensionsOnly ? $this->dimensionsQuery($query, $slot) : $this->baseQuery($query, $slot);
        $scope($query);

        // chunkById, not chunk: the pass writes to the rows it is walking, and an offset-based
        // page would then skip rows as the result set shifts under it.
        $query->chunkById($chunk, function ($rows) use ($dryRun, $slot) {
            foreach ($rows as $row) {
                $this->dimensionsOnly
                    ? $this->recordDimensions($row, $dryRun, $slot)
                    : $this->processRow($row, $dryRun, $slot);

                if ($this->limitReached()) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Rows with a resizable stored image (the model's imageVariantSourceColumn()) that is missing
     * at least one of the target widths.
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
    private function baseQuery(Builder $query, string $slot = 'default'): Builder
    {
        $retrySkipped = (bool) $this->option('retry-skipped');
        [$column, $variants, $widths] = $query->getModel()->imageVariantSlots()[$slot];

        return $this->resizableSource($query, $column)
            ->where(function ($q) use ($retrySkipped, $variants, $widths) {
                $q->whereNull($variants);

                foreach ($widths as $width) {
                    $key = '$.w'.$width;

                    if ($retrySkipped) {
                        $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT({$variants}, ?)), 'MISSING') <> 'STRING'", [$key]);
                    } else {
                        $q->orWhereRaw("COALESCE(JSON_TYPE(JSON_EXTRACT({$variants}, ?)), 'MISSING') = 'MISSING'", [$key]);
                    }
                }

                // --retry-skipped's <> 'STRING' clause already covers these.
                if (! $retrySkipped) {
                    $placeholders = implode(',', array_fill(0, count(ImageUtils::VARIANT_TRANSIENT_REASONS), '?'));

                    $q->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT({$variants}, ?)) IN (".$placeholders.')',
                        array_merge(['$.skipped'], ImageUtils::VARIANT_TRANSIENT_REASONS)
                    );
                }
            });
    }

    /** Rows holding a stored image of ours in $column: not blank, not a demo_ file, not a URL. */
    private function resizableSource(Builder $query, string $column): Builder
    {
        return $query
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, 'not like', 'demo\_%')
            ->where($column, 'not like', 'http%');
    }

    /**
     * --dimensions: rows with a stored image whose size is not recorded yet, whether or not any
     * derivative is. A row whose original was recorded as missing or unreadable is left for
     * --retry-skipped, since its head is no more readable than the rest of it was.
     */
    private function dimensionsQuery(Builder $query, string $slot = 'default'): Builder
    {
        [$column, $variants] = $query->getModel()->imageVariantSlots()[$slot];

        return $this->resizableSource($query, $column)
            ->where(function ($q) use ($variants) {
                $q->whereNull($variants)
                    ->orWhereRaw("JSON_EXTRACT({$variants}, '$.src') IS NULL");
            })
            ->when(! $this->option('retry-skipped'), function ($q) use ($variants) {
                $q->where(function ($q) use ($variants) {
                    $q->whereNull($variants)
                        ->orWhereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT({$variants}, '$.skipped')), '') NOT IN ('missing', 'unreadable')");
                });
            });
    }

    /**
     * Events keep their historical bare "[12]"; schedules say so, since the ids overlap, and name
     * the image when it is not the profile photo.
     *
     * @param  Event|Role  $row
     */
    private function tagFor(Model $row, string $slot): string
    {
        if (! $row instanceof Role) {
            return (string) $row->id;
        }

        return 'schedule '.$row->id.($slot === 'default' ? '' : ' '.$slot);
    }

    /**
     * @param  Event|Role  $row
     */
    private function processRow(Model $row, bool $dryRun, string $slot = 'default'): void
    {
        $this->processed++;

        $raw = $row->imageVariantSource($slot);
        $widths = $row->imageVariantWidths($slot);
        $tag = $this->tagFor($row, $slot);

        if ($dryRun) {
            $names = array_map(fn (int $width) => ImageUtils::variantFilename($raw, $width), $widths);
            $this->line("  [{$tag}] would generate ".implode(', ', $names));
            $this->generated++;

            return;
        }

        // One bad image must never abort a run of thousands. Unlike the queue job, which throws
        // on a transient reason so it is retried, this records what happened and moves on - the
        // next plain run comes back to it.
        try {
            $results = ImageUtils::generateStoredVariants($raw, $widths);
        } catch (\Throwable $e) {
            report($e);
            $this->warn("  [{$tag}] error: ".$e->getMessage());
            $this->countSkip('error');

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
        $detail = null;
        $existing = $row->imageVariants($slot);
        // The original's displayed size: from this run's read when it got that far, else from an
        // earlier one - a fact about the original either way. See GenerateImageVariants.
        $src = is_array($existing['src'] ?? null) ? $existing['src'] : null;

        foreach ($widths as $width) {
            $result = $results[$width] ?? ['ok' => false, 'filename' => null, 'reason' => 'failed'];

            if (is_array($result['src'] ?? null)) {
                $src = $result['src'];
            }

            $kept = $existing['w'.$width] ?? null;
            $variants['w'.$width] = $result['ok']
                ? $result['filename']
                : (is_string($kept) && $kept !== '' ? $kept : null);

            if ($result['ok']) {
                $written[] = $result['filename'];
            } elseif ($reason === null) {
                $reason = $result['reason'];
                // Display only. Deliberately NOT merged into $variants: baseQuery() and
                // --retry-skipped match the recorded value against the reason vocabulary
                // exactly, so a decorated token would strand the row.
                $detail = $result['detail'] ?? null;
            }
        }

        if ($reason !== null) {
            $variants['skipped'] = $reason;
        }

        if ($src !== null) {
            $variants['src'] = $src;
        }

        $row->recordImageVariants($variants, $slot);

        // A partial run counts as skipped: the row still needs another pass.
        if ($reason !== null) {
            $this->countSkip($reason);
            $described = $reason.($detail !== null ? " ({$detail})" : '');
            $this->line("  [{$tag}] skipped: {$described}");
            Log::info('images:backfill-variants skipped '.($row instanceof Role ? 'schedule' : 'event')." {$row->id}".($slot === 'default' ? '' : " ({$slot})").": {$described}");

            return;
        }

        $this->generated++;
        $this->line("  [{$tag}] ".implode(', ', $written));
    }

    /**
     * --dimensions: record the original's displayed size and nothing else. The head of the file
     * only (ImageUtils::storedImageDimensions()), and a JSON_SET that leaves every recorded
     * derivative where it is (HasImageVariants::recordImageSourceDimensions()).
     *
     * @param  Event|Role  $row
     */
    private function recordDimensions(Model $row, bool $dryRun, string $slot): void
    {
        $this->processed++;

        $raw = $row->imageVariantSource($slot);
        $tag = $this->tagFor($row, $slot);

        if ($dryRun) {
            $this->line("  [{$tag}] would read the size of {$raw}");
            $this->generated++;

            return;
        }

        $src = ImageUtils::storedImageDimensions($raw);

        if ($src === null) {
            // Nothing is recorded, so the next --dimensions run tries again; storedImageDimensions()
            // cannot tell a missing file from an unreadable one, and says so.
            $this->countSkip('unreadable');
            $this->line("  [{$tag}] skipped: size unreadable");

            return;
        }

        $row->recordImageSourceDimensions($src, $slot);

        $this->generated++;
        $this->line("  [{$tag}] {$src['w']}x{$src['h']}");
    }

    private function countSkip(string $reason): void
    {
        $this->skipped++;
        $this->skippedReasons[$reason] = ($this->skippedReasons[$reason] ?? 0) + 1;
    }

    private function limitReached(): bool
    {
        return $this->limit > 0 && $this->processed >= $this->limit;
    }
}
