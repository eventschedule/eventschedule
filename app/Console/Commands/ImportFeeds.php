<?php

namespace App\Console\Commands;

use App\Models\EventFeed;
use App\Services\Feeds\FeedImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Reads the feeds that are due: the addresses schedules keep reading for events.
 *
 * Every minute on BOTH cron rails, ungated. A feed is due about once an hour (its own
 * next_check_at, set by each read: later after a failure, the next minute when a read ran out
 * of time), so most runs find nothing to do and are one query.
 *
 * One run at a time across both rails, by a lock: withoutOverlapping() only serialises the
 * scheduler rail against itself, and an install on the HTTP rail may be called more than once
 * a minute. A run that finds the lock taken does nothing, which is right: whoever holds it is
 * reading.
 *
 * Bounded in time, not in feeds. A read makes events, and making an event pushes it to the
 * schedule's connected calendars there and then, so how long a read takes is not this
 * command's to know. It stops starting new work at its deadline and the next run carries on:
 * everything a read still has to do is in the ledger (event_feed_items), not in memory.
 *
 * Whether a schedule's plan includes feeds is asked per feed, inside the read. A feed that is
 * not read (off plan, a schedule nobody owns) is looked at again in an hour with nothing about
 * it counted, so that its failures and its mails stand still while it waits.
 */
class ImportFeeds extends Command
{
    protected $signature = 'app:import-feeds
        {--seconds=20 : How long one run may go on starting new work}
        {--feed= : Read this feed now, whether or not it is due}';

    protected $description = 'Read the feeds that are due and bring their schedules up to date';

    private const LOCK = 'feeds.import';

    private const PRUNE_KEY = 'feeds.pruned';

    public function handle(FeedImporter $importer): int
    {
        // An install that has the code and has not migrated yet.
        if (! EventFeed::tablesReady()) {
            return self::SUCCESS;
        }

        // Released when the run ends, and by itself after two minutes if the process is killed.
        $lock = Cache::lock(self::LOCK, 120);

        if (! $lock->get()) {
            return self::SUCCESS;
        }

        // What the reads have to say by email waits until the lock is let go of.
        $importer->holdMail();

        try {
            $deadline = microtime(true) + max(1, (int) $this->option('seconds'));
            $only = $this->option('feed');
            $read = [];

            while (microtime(true) < $deadline) {
                $feed = EventFeed::query()
                    ->whereNull('paused_at')
                    ->whereNotIn('id', $read)
                    ->when(
                        $only,
                        fn ($query) => $query->whereKey($only),
                        fn ($query) => $query->where(fn ($due) => $due->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
                    )
                    // One that has never been read first, then the one that has waited longest.
                    ->orderByRaw('next_check_at IS NULL DESC')
                    ->orderBy('next_check_at')
                    ->first();

                if (! $feed) {
                    break;
                }

                $read[] = $feed->id;

                // Claimed before it is read. A read that takes the whole process down with it
                // (memory, a call that is killed) never gets to say when it should be tried
                // again, and would otherwise be the most overdue feed on the next run and on
                // every run after, in front of all the others.
                EventFeed::whereKey($feed->id)->update(['next_check_at' => now()->addMinutes(10)]);

                try {
                    $result = $importer->read($feed, $deadline);
                } catch (\Throwable $e) {
                    // One feed's trouble is not the others'. Counted as a failed read, so that it
                    // backs off, shows as failing, is mailed about and pauses in the end.
                    report($e);

                    try {
                        $importer->crashed(EventFeed::find($feed->id) ?? $feed);
                    } catch (\Throwable $again) {
                        EventFeed::whereKey($feed->id)->update([
                            'last_checked_at' => now(),
                            'last_status' => 'failed',
                            'failure_count' => min($feed->failure_count + 1, 65000),
                            'next_check_at' => now()->addHour(),
                        ]);
                    }

                    continue;
                }

                if ($result['status'] === 'idle') {
                    EventFeed::whereKey($feed->id)->update(['next_check_at' => now()->addHour()]);
                }
            }
            // Once an hour, the ledger forgets what its sources can no longer show it.
            if (Cache::add(self::PRUNE_KEY, true, now()->addHour())) {
                $importer->prune();
            }
        } finally {
            $lock->release();
            $importer->sendHeldMail();
        }

        return self::SUCCESS;
    }
}
