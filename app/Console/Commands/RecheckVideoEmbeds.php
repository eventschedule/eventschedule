<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Utils\UrlUtils;
use App\Utils\VideoUtils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Prune saved videos that YouTube will no longer play in an embedded player.
 *
 * The search-time filters in GeminiUtils::searchYouTube() stop new bad matches being offered, but
 * they do nothing for the ones already stored, and a video can have embedding switched off or be
 * deleted long after it was matched. Those render as YouTube's "Video unavailable" panel on the
 * guest pages, which nothing in the app can see.
 *
 * Scope is `roles.youtube_links` only. The fan-video system (`event_videos`) is deliberately left
 * alone: those are guest submissions with an identified submitter and an approve/reject workflow,
 * there is no soft-delete column so removing one destroys it, and re-pending them would trigger
 * NotifyFanContentChanges to mail every schedule owner on the install. Handling those needs an
 * "unavailable" flag the render sites and the pending count both respect - a separate change.
 */
class RecheckVideoEmbeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recheck-video-embeds
                            {--dry-run : Report what would be removed without writing}
                            {--limit= : Stop after this many schedules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove saved YouTube videos that can no longer be played in an embedded player';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config('services.google.backend');

        if (! $apiKey) {
            // The normal state on a selfhost install with no YouTube key. Nothing to check and
            // nothing worth logging about it.
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $stats = [
            'roles_scanned' => 0,
            'videos_checked' => 0,
            'videos_removed' => 0,
            'roles_emptied' => 0,
            'batches_skipped' => 0,
        ];

        Role::whereNotNull('youtube_links')
            ->where('youtube_links', '!=', '[]')
            ->orderBy('id')
            // Full rows, never a partial select. Role's saving hook rewrites description_html and
            // banner_message_html from attributes it does not check for dirtiness, so a partially
            // hydrated model written back nulls four rendered columns. The write below uses the
            // query builder and so avoids the hook entirely, but chunkById also hands these models
            // to decodeLinks(), and a future edit reaching for save() should not find a trap.
            ->chunkById(200, function ($roles) use ($apiKey, $dryRun, $limit, &$stats) {
                return $this->processChunk($roles, $apiKey, $dryRun, $limit, $stats);
            });

        // Artisan::call() from AppController::translateData() throws stdout away, so anything that
        // needs to survive the hosted rail has to go through the logger. Stay silent on a clean
        // run - the common case is "nothing to do".
        if ($stats['videos_removed'] > 0 || $stats['batches_skipped'] > 0) {
            \Log::info('app:recheck-video-embeds', $stats + ['dry_run' => $dryRun]);
        }

        $this->info(sprintf(
            'Scanned %d schedules, checked %d videos, removed %d%s.',
            $stats['roles_scanned'],
            $stats['videos_checked'],
            $stats['videos_removed'],
            $dryRun ? ' (dry run, nothing written)' : ''
        ));

        if ($stats['batches_skipped'] > 0) {
            $this->warn($stats['batches_skipped'].' batch(es) skipped after a failed YouTube request.');
        }

        return self::SUCCESS;
    }

    /**
     * @return bool false to stop chunking (the --limit has been reached)
     */
    private function processChunk($roles, string $apiKey, bool $dryRun, ?int $limit, array &$stats): bool
    {
        $linksByRole = [];
        $videoIds = [];

        foreach ($roles as $role) {
            $links = $role->decodeLinks('youtube_links');
            $linksByRole[$role->id] = $links;

            foreach ($links as $link) {
                $videoId = UrlUtils::extractYouTubeVideoId($link->url ?? '');

                if ($videoId) {
                    $videoIds[$videoId] = true;
                }
            }
        }

        $videoIds = array_keys($videoIds);
        $stats['videos_checked'] += count($videoIds);

        $broken = [];

        // videos.list takes up to 50 ids and costs one quota unit per call regardless, so this is
        // negligible next to the 100 units a single search costs.
        foreach (array_chunk($videoIds, 50) as $batch) {
            $statuses = $this->fetchStatuses($batch, $apiKey);

            if ($statuses === null) {
                // Fail closed: a transport failure means "no answer", not "not embeddable". Without
                // this, one bad night would delete every video in the install.
                $stats['batches_skipped']++;

                continue;
            }

            foreach ($batch as $videoId) {
                if ($this->isBroken($statuses[$videoId] ?? null)) {
                    $broken[] = $videoId;
                }
            }
        }

        foreach ($roles as $role) {
            if ($limit !== null && $stats['roles_scanned'] >= $limit) {
                return false;
            }

            $stats['roles_scanned']++;

            $links = $linksByRole[$role->id];
            $remaining = VideoUtils::removeByVideoIds($links, $broken);

            if (count($remaining) === count($links)) {
                continue;
            }

            $stats['videos_removed'] += count($links) - count($remaining);

            $value = VideoUtils::encodeLinks($remaining);

            if ($value === null) {
                $stats['roles_emptied']++;
            }

            \Log::debug('app:recheck-video-embeds removing videos', [
                'role_id' => $role->id,
                'subdomain' => $role->subdomain,
                'removed' => count($links) - count($remaining),
                'emptied' => $value === null,
            ]);

            if (! $dryRun) {
                // Query builder rather than $role->save(): youtube_links is not fillable, and the
                // model's saving hook rewrites derived HTML columns and can fire an outbound
                // geocoding request. Neither belongs in a nightly sweep touching one column.
                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['youtube_links' => $value, 'updated_at' => now()]);
            }
        }

        return true;
    }

    /**
     * Whether a videos.list `status` object means the video can no longer be shown.
     *
     * Deliberately narrower than GeminiUtils::isPlayableStatus(), which vets fresh search results:
     * an `unlisted` video embeds perfectly well and a schedule owner may have pasted one into the
     * YouTube Videos tab on purpose, so this must not delete it. `private` needs no rule -
     * videos.list omits what an API key cannot see, which the null case below already covers.
     */
    private function isBroken(?array $status): bool
    {
        // Absent from the response: deleted, or private.
        if ($status === null) {
            return true;
        }

        if (($status['embeddable'] ?? null) === false) {
            return true;
        }

        return in_array($status['uploadStatus'] ?? null, ['rejected', 'deleted', 'failed'], true);
    }

    /**
     * @return array|null status objects keyed by video id, or null if the request failed
     */
    private function fetchStatuses(array $videoIds, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->get('https://www.googleapis.com/youtube/v3/videos', [
                    'key' => $apiKey,
                    'id' => implode(',', $videoIds),
                    'part' => 'status',
                ]);
        } catch (\Exception $e) {
            // This is the live path for a failed status too, not just a connection error: retry()
            // calls throw() between attempts, so an exhausted 4xx/5xx arrives here as an exception.
            \Log::warning('app:recheck-video-embeds YouTube request failed: '.$e->getMessage());

            return null;
        }

        // Kept as a backstop for a future edit that drops retry(), which would make a failed status
        // return normally again.
        if (! $response->successful()) {
            \Log::warning('app:recheck-video-embeds YouTube request failed', ['status' => $response->status()]);

            return null;
        }

        $items = $response->json('items');

        if (! is_array($items)) {
            return null;
        }

        $statuses = [];

        foreach ($items as $item) {
            if (isset($item['id'])) {
                $statuses[$item['id']] = $item['status'] ?? [];
            }
        }

        return $statuses;
    }
}
