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
 * Nothing here may fail a read: every send is inside a catch. And none is sent while a run holds
 * the lock on every feed (hold(), release()): a mail server that takes ten seconds to answer
 * would be ten seconds in which no feed on the install is read.
 *
 * An email nobody could be sent is still owed: the day's allowance is given back and the feed
 * keeps its mark, so the next read tries again. Before that, a mail server that was down for an
 * hour was the one email about forty drafts, gone.
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

    /** How many decisions a feed remembers it has not written about yet. */
    private const OWED_DECISIONS = 50;

    private bool $holding = false;

    /** @var list<array{0: Role, 1: int, 2: string, 3: array}> */
    private array $held = [];

    /** From here on, what is to be sent waits. */
    public function hold(): void
    {
        $this->holding = true;
    }

    /** Send what waited. */
    public function release(): void
    {
        $this->holding = false;
        $held = $this->held;
        $this->held = [];

        foreach ($held as [$role, $feedId, $kind, $facts]) {
            $this->deliver($role, $feedId, $kind, $facts);
        }
    }

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
                $this->send($role, $feed, FeedNotification::PAUSED, ['feed' => $feed->name, 'url' => $url]);

                return;
            }

            $since = $feed->last_success_at ?? $feed->created_at;

            // Once for each time it stops: the mark is taken off by the next good read.
            if (! $since || $since->gt(now()->subDays(self::FAILING_AFTER_DAYS)) || ! empty($feed->stats['failing_told'])) {
                return;
            }

            if ($this->firstToday($role, FeedNotification::FAILING)) {
                // Marked first: a send that reaches nobody takes the mark off again (owe()).
                $feed->forceFill(['stats' => ['failing_told' => true] + ($feed->stats ?? [])])->save();
                $this->send($role, $feed, FeedNotification::FAILING, ['feed' => $feed->name, 'url' => $url, 'since' => $since->toIso8601String()]);
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
        $mail = null;

        if ($owed > 0 && $settled) {
            if ((int) $feed->waiting_count === 0) {
                // Looked over already.
                $owed = 0;
            } elseif ($this->firstToday($role, FeedNotification::REVIEW)) {
                $mail = [
                    'feed' => $feed->name,
                    'url' => $this->page($role, $feed).'#waiting',
                    'count' => (int) $feed->waiting_count,
                ];
                $owed = 0;
            }
        }

        if ($owed !== $before) {
            $stats = $feed->stats ?? [];
            unset($stats['to_tell']);
            $feed->forceFill(['stats' => ($owed > 0 ? ['to_tell' => $owed] : []) + $stats])->save();
        }

        // After the mark is settled, because a send that reaches nobody puts it back.
        if ($mail) {
            $this->send($role, $feed, FeedNotification::REVIEW, $mail);
        }
    }

    /**
     * A decision is written about in the read that raised it, unless the day's email has gone.
     * Then it is owed, as drafts are: the feed remembers which, and the first read of a new day
     * writes about them. Only what this read raised was ever looked at before, so the second
     * decision of a day was mailed by nobody, on that day or any other.
     *
     * @param  list<int>  $raised
     */
    private function decisions(EventFeed $feed, Role $role, array $raised): void
    {
        $owed = array_map('intval', (array) ($feed->stats['decide_owed'] ?? []));

        if (! $raised && ! $owed) {
            return;
        }

        $items = $feed->items()
            ->whereIn('id', array_merge($raised, $owed))
            ->where('state', EventFeedItem::STATE_DECIDE)
            ->with('event')
            ->get()
            ->filter(fn (EventFeedItem $item) => $item->event && isset($item->pending['decide']))
            // The one that happens soonest is the one the email is about.
            ->sortBy(fn (EventFeedItem $item) => $item->event->starts_at)
            ->values();

        if ($items->isEmpty()) {
            // Answered in the meantime.
            $this->oweDecisions($feed, []);

            return;
        }

        // Time running out is asked of what this read raised. An older one was either this near
        // when it was raised, and sent then, or has waited for the day's email since.
        $soon = now()->addDays(self::URGENT_DAYS)->utc()->format('Y-m-d H:i:s');
        $urgent = $items->contains(fn (EventFeedItem $item) => in_array($item->id, $raised, true)
            && $item->event->starts_at && $item->event->starts_at <= $soon);

        // Asked for even when urgent, so that an urgent one counts as today's.
        if (! $this->firstToday($role, FeedNotification::DECIDE) && ! $urgent) {
            $this->oweDecisions($feed, $items->pluck('id')->all());

            return;
        }

        $first = $items->first();
        $this->oweDecisions($feed, []);
        $this->send($role, $feed, FeedNotification::DECIDE, [
            'feed' => $feed->name,
            'url' => $this->page($role, $feed).'#decide',
            'event' => (string) $first->event->name,
            'says' => in_array($first->pending['decide']['kind'] ?? '', ['moved', 'cancelled'], true) ? $first->pending['decide']['kind'] : 'gone',
            'people' => $this->people($first->event),
            'more' => $items->count() - 1,
            // Which ones, so that a send that reaches nobody can owe them again.
            'items' => $items->pluck('id')->all(),
        ]);
    }

    /** @param  list<int>  $ids */
    private function oweDecisions(EventFeed $feed, array $ids): void
    {
        $ids = array_slice(array_values(array_unique(array_map('intval', $ids))), 0, self::OWED_DECISIONS);

        if ($ids === array_map('intval', (array) ($feed->stats['decide_owed'] ?? []))) {
            return;
        }

        $stats = $feed->stats ?? [];
        unset($stats['decide_owed']);
        $feed->forceFill(['stats' => ($ids ? ['decide_owed' => $ids] : []) + $stats])->save();
    }

    private function people(Event $event): int
    {
        return $event->sales()->count() + \App\Models\EventInterest::where('event_id', $event->id)->count();
    }

    /** True the first time it is asked in a day for this kind and schedule. */
    private function firstToday(Role $role, string $kind): bool
    {
        return Cache::add($this->dayKey($role, $kind), 1, now()->addHours(self::QUIET_HOURS));
    }

    private function dayKey(Role $role, string $kind): string
    {
        return 'feeds.mailed.'.$kind.'.'.$role->id;
    }

    /** The feed's own page, as a path: the mail makes it absolute. */
    private function page(Role $role, EventFeed $feed): string
    {
        return route('role.feeds.show', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)], false);
    }

    private function send(Role $role, EventFeed $feed, string $kind, array $facts): void
    {
        if ($this->holding) {
            $this->held[] = [$role, $feed->id, $kind, $facts];

            return;
        }

        $this->deliver($role, $feed->id, $kind, $facts);
    }

    private function deliver(Role $role, int $feedId, string $kind, array $facts): void
    {
        $owedItems = $facts['items'] ?? [];
        unset($facts['items']);

        try {
            if ($this->mail($role, $kind, $facts)) {
                return;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $this->owe($role, $feedId, $kind, $owedItems);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * @return bool False only when there was somebody to write to and nobody could be: a mail
     *              server that is down. Nobody wanting the email is not a failure.
     */
    private function mail(Role $role, string $kind, array $facts): bool
    {
        if (is_demo_role($role) || ! NotificationEmailService::canSend()) {
            return true;
        }

        $editors = $role->getEditorsWantingNotification(self::TYPE);
        $tried = $reached = 0;

        foreach ($editors as $editor) {
            $tried++;

            try {
                $editor->notify((new FeedNotification($role, $kind, $facts))
                    ->locale(is_valid_language_code($editor->language_code) ? $editor->language_code : config('app.locale')));
                $reached++;
            } catch (\Throwable $e) {
                // One address that bounces does not keep the rest from being told.
                report($e);
            }
        }

        if ($role->getNotificationEmailWanting(self::TYPE, $editors)) {
            $tried++;
            $reached += app(NotificationEmailService::class)->sendNotification($role, self::TYPE, new FeedNotification($role, $kind, $facts), $editors) ? 1 : 0;
        }

        return $tried === 0 || $reached > 0;
    }

    /**
     * An email that reached nobody is owed again: today's allowance for its kind is given back,
     * and the feed gets back the mark the next read looks for.
     *
     * @param  list<int>  $items
     */
    private function owe(Role $role, int $feedId, string $kind, array $items): void
    {
        // A pause is said once and has no next read to say it on. The page and the admins' list say it.
        if ($kind === FeedNotification::PAUSED) {
            return;
        }

        Cache::forget($this->dayKey($role, $kind));

        $feed = EventFeed::find($feedId);

        if (! $feed) {
            return;
        }

        $stats = $feed->stats ?? [];

        if ($kind === FeedNotification::REVIEW) {
            $stats['to_tell'] = max(1, (int) ($stats['to_tell'] ?? 0));
        } elseif ($kind === FeedNotification::DECIDE) {
            $stats['decide_owed'] = array_slice(array_values(array_unique(array_map('intval', array_merge((array) ($stats['decide_owed'] ?? []), $items)))), 0, self::OWED_DECISIONS);
        } elseif ($kind === FeedNotification::FAILING) {
            unset($stats['failing_told']);
        }

        $feed->forceFill(['stats' => $stats])->save();
    }
}
