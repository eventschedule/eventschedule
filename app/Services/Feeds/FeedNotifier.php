<?php

namespace App\Services\Feeds;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Notifications\FeedNotification;
use App\Services\NotificationEmailService;
use App\Utils\UrlUtils;
use Illuminate\Support\Facades\Cache;

/**
 * Who is told what a feed needs somebody for, and how often.
 *
 * A feed works with nobody watching, which is the point of it, so the three things that do need
 * a person have to reach one: drafts waiting to be looked over, an event people signed up for
 * that the source moved, called off or dropped, and a feed that stopped being read. Each goes to
 * every member who runs the schedule and wants it, in their own language, and to the schedule's
 * shared address.
 *
 * At most one of each kind a day per schedule: a source that changes forty events in an
 * afternoon is one email, not forty. Two exceptions, both about time running out: a decision on
 * an event within three days is sent at once whatever was sent today, and a feed being paused is
 * always said, because it is said once.
 *
 * Nothing here may fail a read: every send is inside a catch.
 */
class FeedNotifier
{
    /** The notification preference these belong to (Role::getEditorsWantingNotification()). */
    public const TYPE = 'feed';

    /** A feed that has gone this long without one good read is worth an email. */
    public const FAILING_AFTER_DAYS = 3;

    /** A decision about an event this near does not wait for tomorrow's email. */
    public const URGENT_DAYS = 3;

    /** How long "today" lasts for the one-a-day rule. Under a day, so a daily rhythm does not drift later. */
    private const QUIET_HOURS = 20;

    /**
     * After a read that went through.
     *
     * @param  array{created?: int}  $counts
     * @param  bool  $unfinished  Whether the read left work for the next run.
     * @param  list<int>  $raised  The items that became a decision in this read.
     */
    public function afterRead(EventFeed $feed, Role $role, array $counts, bool $unfinished, array $raised): void
    {
        try {
            $this->decisions($feed, $role, $raised);
            $this->drafts($feed, $role, (int) ($counts['created'] ?? 0), $unfinished);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** After a read that failed. */
    public function afterFailure(EventFeed $feed, bool $justPaused): void
    {
        try {
            $role = Role::find($feed->role_id);

            if (! $role || $role->is_deleted || ! EventFeed::allowedFor($role)) {
                return;
            }

            $url = $this->page($role, $feed);

            if ($justPaused) {
                $this->send($role, FeedNotification::PAUSED, ['feed' => $feed->name, 'url' => $url]);

                return;
            }

            $since = $feed->last_success_at ?? $feed->created_at;

            // Once for each time it stops: the mark is taken off by the next good read.
            if (! $since || $since->gt(now()->subDays(self::FAILING_AFTER_DAYS)) || ! empty($feed->stats['failing_told'])) {
                return;
            }

            if ($this->firstToday($role, FeedNotification::FAILING)) {
                $this->send($role, FeedNotification::FAILING, ['feed' => $feed->name, 'url' => $url, 'since' => $since->toIso8601String()]);
                $feed->forceFill(['stats' => ['failing_told' => true] + ($feed->stats ?? [])])->save();
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Drafts are said once they have all arrived. A first read of a hundred events takes
     * several runs, and "30 are waiting" followed by silence about the other seventy would be
     * wrong by the time it was opened. What is owed an email is kept on the feed until a read
     * ends with nothing left over; and if the day's email has gone, until tomorrow's.
     */
    private function drafts(EventFeed $feed, Role $role, int $created, bool $unfinished): void
    {
        $before = (int) ($feed->stats['to_tell'] ?? 0);
        $owed = $before + ($feed->publishes() ? 0 : $created);
        // Out of the day's allowance is as settled as today gets.
        $settled = ! $unfinished || ! empty($feed->stats['continues_tomorrow']);

        if ($owed > 0 && $settled) {
            if ((int) $feed->waiting_count === 0) {
                // Looked over already.
                $owed = 0;
            } elseif ($this->firstToday($role, FeedNotification::REVIEW)) {
                $this->send($role, FeedNotification::REVIEW, [
                    'feed' => $feed->name,
                    'url' => $this->page($role, $feed).'#waiting',
                    'count' => (int) $feed->waiting_count,
                ]);
                $owed = 0;
            }
        }

        if ($owed !== $before) {
            $stats = $feed->stats ?? [];
            unset($stats['to_tell']);
            $feed->forceFill(['stats' => ($owed > 0 ? ['to_tell' => $owed] : []) + $stats])->save();
        }
    }

    /** @param  list<int>  $raised */
    private function decisions(EventFeed $feed, Role $role, array $raised): void
    {
        if (! $raised) {
            return;
        }

        $items = $feed->items()
            ->whereIn('id', $raised)
            ->where('state', EventFeedItem::STATE_DECIDE)
            ->with('event')
            ->get()
            ->filter(fn (EventFeedItem $item) => $item->event && isset($item->pending['decide']))
            // The one that happens soonest is the one the email is about.
            ->sortBy(fn (EventFeedItem $item) => $item->event->starts_at)
            ->values();

        if ($items->isEmpty()) {
            return;
        }

        $first = $items->first();
        $soon = now()->addDays(self::URGENT_DAYS)->utc()->format('Y-m-d H:i:s');
        $urgent = $first->event->starts_at && $first->event->starts_at <= $soon;

        // Asked for even when urgent, so that an urgent one counts as today's.
        if (! $this->firstToday($role, FeedNotification::DECIDE) && ! $urgent) {
            return;
        }

        $this->send($role, FeedNotification::DECIDE, [
            'feed' => $feed->name,
            'url' => $this->page($role, $feed).'#decide',
            'event' => (string) $first->event->name,
            'says' => in_array($first->pending['decide']['kind'] ?? '', ['moved', 'cancelled'], true) ? $first->pending['decide']['kind'] : 'gone',
            'people' => $this->people($first->event),
            'more' => $items->count() - 1,
        ]);
    }

    private function people(Event $event): int
    {
        return $event->sales()->count() + \App\Models\EventInterest::where('event_id', $event->id)->count();
    }

    /** True the first time it is asked in a day for this kind and schedule. */
    private function firstToday(Role $role, string $kind): bool
    {
        return Cache::add('feeds.mailed.'.$kind.'.'.$role->id, 1, now()->addHours(self::QUIET_HOURS));
    }

    /** The feed's own page, as a path: the mail makes it absolute. */
    private function page(Role $role, EventFeed $feed): string
    {
        return route('role.feeds.show', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)], false);
    }

    private function send(Role $role, string $kind, array $facts): void
    {
        if (is_demo_role($role) || ! NotificationEmailService::canSend()) {
            return;
        }

        $editors = $role->getEditorsWantingNotification(self::TYPE);

        foreach ($editors as $editor) {
            try {
                $editor->notify((new FeedNotification($role, $kind, $facts))
                    ->locale(is_valid_language_code($editor->language_code) ? $editor->language_code : config('app.locale')));
            } catch (\Throwable $e) {
                // One address that bounces does not keep the rest from being told.
                report($e);
            }
        }

        app(NotificationEmailService::class)->sendNotification($role, self::TYPE, new FeedNotification($role, $kind, $facts), $editors);
    }
}
