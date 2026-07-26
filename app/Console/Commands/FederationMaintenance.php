<?php

namespace App\Console\Commands;

use App\Models\FederatedEvent;
use App\Models\FederatedInstance;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;

/**
 * Nexus-side upkeep for federated listings: fetch their images, drop what has
 * expired, and stop holding files nothing references.
 */
class FederationMaintenance extends Command
{
    protected $signature = 'federation:maintain {--limit=100 : Images to fetch in one run}';

    protected $description = 'Fetch federated listing images and prune expired federated content';

    /** An instance that has not checked in for this long is treated as gone. */
    public const STALE_INSTANCE_DAYS = 60;

    /**
     * An unapproved registration that never pushed a single event is dropped this
     * quickly, so junk cannot hold a place in the review queue for two months.
     */
    public const ABANDONED_REGISTRATION_DAYS = 7;

    /** Blocked rows are kept as tombstones, but not forever. */
    public const TOMBSTONE_DAYS = 180;

    /**
     * Rows removed per prune per run. Deleting a listing also deletes its stored image,
     * which on the hosted nexus is an HTTP call to object storage - and this command
     * runs inside the cron request, so an unbounded sweep could starve everything
     * scheduled after it.
     */
    public const PRUNE_LIMIT = 500;

    public function handle(): int
    {
        if (! config('app.is_nexus')) {
            $this->info('Not the nexus; nothing to maintain.');

            return self::SUCCESS;
        }

        $this->info('Fetched '.$this->fetchImages((int) $this->option('limit')).' image(s).');
        $this->info('Pruned '.$this->pruneExpired().' expired listing(s).');
        $this->info('Pruned '.$this->pruneTombstones().' tombstone(s).');
        $this->info('Pruned '.$this->pruneStaleInstances().' stale instance(s).');

        return self::SUCCESS;
    }

    /**
     * Download and store advertised images locally.
     *
     * Deliberately out of band rather than on the intake request: the URL is
     * attacker-supplied, so the fetch goes through UrlUtils::safeHttpGet(), which is
     * GET-only, pins the resolved IP, re-validates every redirect hop and caps the
     * body size. Hotlinking instead would leak visitor IPs to arbitrary hosts and
     * force img-src open.
     */
    public function fetchImages(int $limit = 100): int
    {
        // Rows with no image yet, PLUS rows whose source has since advertised a
        // different URL. Without the second case a replaced flyer would never reach the
        // listing, because the row already has an image_path and would never be
        // revisited.
        $rows = FederatedEvent::whereNotNull('image_url')
            ->whereNull('blocked_at')
            // Approved instances only. A listing is not renderable before approval
            // (scopeListable() requires it), so fetching earlier buys nothing - while
            // letting anyone who can register spend our storage on images we may never
            // show. Nothing reclaims them either: pruneStaleInstances() skips instances
            // that have pushed, and every push refreshes last_seen_at.
            ->whereHas('instance', fn ($q) => $q->approved())
            ->where(function ($q) {
                $q->whereNull('image_path')
                    ->orWhereNull('image_fetched_url')
                    ->orWhereColumn('image_url', '!=', 'image_fetched_url');
            })
            ->limit($limit)
            ->get();

        $fetched = 0;

        foreach ($rows as $row) {
            $data = UrlUtils::safeFetch($row->image_url);

            if (! $data) {
                // Unreachable, blocked by the SSRF guard, or too large. Clear the URL so
                // a dead link is not retried every run; a re-push can advertise a new one.
                $row->image_url = null;
                $row->save();

                continue;
            }

            // Trust the bytes, not the declared content type or the file extension.
            // Note ImageUtils::detectImageFormat() cannot be used as the gate here: it
            // falls back to the URL extension and finally defaults to 'jpeg', so it
            // never rejects anything.
            if (! $this->looksLikeImage($data)) {
                $row->image_url = null;
                $row->save();

                continue;
            }

            // Drop the copy this one replaces, or a source that changes its flyer
            // repeatedly would leave a trail of orphaned files behind.
            $previous = $row->image_path;

            $row->image_path = ImageUtils::saveImageData($data, $row->image_url, 'federated_');
            $row->image_fetched_url = $row->image_url;
            $row->save();

            if ($previous && $previous !== $row->image_path) {
                $this->deleteImage($previous);
            }

            $fetched++;
        }

        return $fetched;
    }

    /**
     * Magic-byte check for the formats the app stores. Anything else is refused
     * rather than saved under this domain.
     */
    protected function looksLikeImage(string $data): bool
    {
        $head = substr($data, 0, 4);

        return str_starts_with($head, "\xFF\xD8")                              // jpeg
            || $head === "\x89PNG"                                              // png
            || $head === 'GIF8'                                                 // gif
            || ($head === 'RIFF' && substr($data, 8, 4) === 'WEBP')             // webp
            || str_starts_with($head, 'BM');                                    // bmp
    }

    /**
     * Drop listings whose last occurrence has passed.
     *
     * Sweeps on next_occurrence_at, never on starts_at: a recurring listing carries
     * resolved occurrences and a null starts_at, so a starts_at sweep would delete
     * exactly the rows that should persist.
     */
    public function pruneExpired(): int
    {
        // Bounded: every deleted row can cost an HTTP DELETE against object storage,
        // and this runs inside the hosted cron request. The backlog drains over
        // subsequent hourly runs.
        $rows = FederatedEvent::whereNull('blocked_at')
            ->whereNotNull('next_occurrence_at')
            ->where('next_occurrence_at', '<', now()->subWeek())
            ->limit(self::PRUNE_LIMIT)
            ->get();

        return $this->deleteWithImages($rows);
    }

    public function pruneTombstones(): int
    {
        $rows = FederatedEvent::whereNotNull('blocked_at')
            ->where('blocked_at', '<', now()->subDays(self::TOMBSTONE_DAYS))
            ->limit(self::PRUNE_LIMIT)
            ->get();

        return $this->deleteWithImages($rows);
    }

    public function pruneStaleInstances(): int
    {
        $instances = FederatedInstance::where('status', '!=', FederatedInstance::STATUS_APPROVED)
            ->where(function ($q) {
                // Registered a while ago and has gone quiet.
                $q->where(function ($stale) {
                    $stale->where(function ($seen) {
                        $seen->whereNull('last_seen_at')
                            ->orWhere('last_seen_at', '<', now()->subDays(self::STALE_INSTANCE_DAYS));
                    })->where('created_at', '<', now()->subDays(self::STALE_INSTANCE_DAYS));
                })
                    // Or registered and never sent a single event. A real operator
                    // enables federation and pushes within the hour, so this clears
                    // junk registrations out of the review queue in days rather than
                    // letting them hold a slot for two months.
                    ->orWhere(function ($never) {
                        $never->where('created_at', '<', now()->subDays(self::ABANDONED_REGISTRATION_DAYS))
                            ->whereDoesntHave('events');
                    });
            })
            ->limit(self::PRUNE_LIMIT)
            ->get();

        foreach ($instances as $instance) {
            // purge() rather than ->get(): an abandoned instance may still hold thousands
            // of rows, and loading them all to delete them one at a time is what the
            // per-run bound above is trying to avoid.
            FederatedEvent::purge(FederatedEvent::where('federated_instance_id', $instance->id));
            $instance->delete();
        }

        return $instances->count();
    }

    /**
     * Delete rows and their stored images, so files do not outlive their listings.
     *
     * The image goes via FederatedEvent's `deleting` hook, which is what also covers
     * anything else that deletes a single listing through a model instance.
     */
    protected function deleteWithImages($rows): int
    {
        $deleted = 0;

        foreach ($rows as $row) {
            $row->delete();
            $deleted++;
        }

        return $deleted;
    }

    protected function deleteImage(?string $path): void
    {
        FederatedEvent::deleteStoredImage($path);
    }
}
