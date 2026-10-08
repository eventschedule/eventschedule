<?php

namespace App\Services\Feeds;

use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Services\ScheduleEventMatcher;
use App\Utils\ImportAddress;
use App\Utils\ImportedTime;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Adding a feed: looking at an address before anything is added, and then adding it.
 *
 * The CHECK writes nothing. It reads the address once, as the hourly read will, and says what
 * it found: what kind of source it is, how many events are coming, how many of them the
 * schedule already has (those will be matched, not added twice), the first few, and how the
 * source's own time reads beside ours, which is the one thing that is easy to get wrong and
 * hard to see afterwards.
 *
 * What the check learned travels to the second step in a sealed token (the address, its kind
 * and a name for it), tied to the schedule, the person and the hour. So the address is posted
 * once and never rides in a query string, and what is added is what was checked.
 */
class FeedSetup
{
    /** Rows shown from what was found. */
    public const SAMPLE = 5;

    /** For a source whose items have to be opened one by one: how many a check opens. */
    private const OPENED = 8;

    private const SECONDS = 12;

    public function __construct(private FeedFetcher $fetcher) {}

    /**
     * @return array{ok: bool, reason?: string, status?: ?int, platform?: string, url?: string, host?: string, kind?: string, title?: string, count?: ?int, posts?: ?int, more?: bool, skipped?: array, matched?: int, sample?: list<array>, timezone?: string, source_time?: string, token?: string}
     */
    public function check(Role $role, User $by, string $link, ?string $timezone = null): array
    {
        $url = ImportAddress::normalise($link);

        if ($url === null) {
            return ['ok' => false, 'reason' => 'invalid_url'];
        }

        if ($platform = ImportAddress::signInWall($url)) {
            return ['ok' => false, 'reason' => 'sign_in_wall', 'platform' => $platform];
        }

        [$url, $rewrittenFrom] = ImportAddress::knownFeedFor($url);

        if (EventFeed::where('role_id', $role->id)->where('url_hash', EventFeed::hashOf($url))->exists()) {
            return ['ok' => false, 'reason' => 'already_added'];
        }

        $deadline = microtime(true) + self::SECONDS;
        $fetched = $this->fetcher->get($url);

        if (! $fetched->ok()) {
            // The feed a Google share link stands for answers 404 while the calendar is private.
            $reason = $rewrittenFrom === 'google' && $fetched->status === FeedFetcher::HTTP_ERROR ? 'google_private' : $fetched->status;

            return ['ok' => false, 'reason' => $reason, 'status' => $fetched->httpStatus];
        }

        $kind = FeedKind::detect($url, $fetched);

        if (! $kind) {
            return ['ok' => false, 'reason' => 'unsupported'];
        }

        $timezone = $timezone && in_array($timezone, timezone_identifiers_list(), true) ? $timezone : $role->captureTimezone();
        $keepLocalClock = ! $role->isVenue();
        $reader = FeedKind::reader($kind);
        // Unzoned times are read on the feed's clock and everything is placed on the schedule's,
        // exactly as a read will do it, so what is shown here is what will be on the schedule.
        $clock = $role->captureTimezone();
        $reading = ImportedTime::onClock($clock, fn () => $reader->read($fetched, $timezone, $keepLocalClock));

        if (! $reading) {
            // A page was opened and marks up no event: the commonest way a check ends.
            return ['ok' => false, 'reason' => $kind === EventFeed::KIND_PAGE ? 'no_events' : 'unsupported'];
        }

        $rows = [];
        $posts = null;

        if (in_array($kind, [EventFeed::KIND_CALENDAR, EventFeed::KIND_PAGE], true)) {
            $rows = array_column($reading->items, 'row');
        } else {
            // A feed of posts says when each was posted, not when it happens: that is on each
            // post's own page. A check opens the first few, for a look; the first read opens all.
            $posts = count($reading->items);

            foreach (array_slice($reading->items, 0, self::OPENED) as $item) {
                if (count($rows) >= self::SAMPLE || microtime(true) > $deadline || ! $item['detail_url']) {
                    continue;
                }

                $page = $this->fetcher->get($item['detail_url'], null, null, 6);
                $found = $page->ok() ? ImportedTime::onClock($clock, fn () => $reader->detail($item, $page, $timezone, $keepLocalClock)) : null;

                if (is_array($found)) {
                    $rows[] = $found;
                }
            }

            if ($posts > 0 && ! $rows && $kind === EventFeed::KIND_ITEMS) {
                return ['ok' => false, 'reason' => 'no_dates', 'posts' => $posts];
            }
        }

        $matcher = new ScheduleEventMatcher($role, $clock);
        $title = $this->titleOf($kind, $fetched) ?: (string) parse_url($url, PHP_URL_HOST);

        return [
            'ok' => true,
            'url' => $url,
            'host' => (string) parse_url($url, PHP_URL_HOST),
            'kind' => $kind,
            'title' => mb_substr($title, 0, 120),
            'count' => $posts === null ? count($rows) : null,
            'posts' => $posts,
            'more' => $reading->next !== null,
            'skipped' => $reading->skipped,
            'matched' => $posts === null ? count(array_filter($rows, fn (array $row) => $matcher->match($row) !== null)) : 0,
            'sample' => array_map(fn (array $row) => [
                'name' => (string) $row['event_name'],
                'at' => $row['event_date_time'],
                'duration' => is_numeric($row['event_duration'] ?? null) ? (float) $row['event_duration'] : null,
                'all_day' => (bool) ($row['is_all_day'] ?? false),
                'venue' => (string) ($row['venue_name'] ?? '') ?: (string) ($row['event_address'] ?? ''),
            ], array_slice($rows, 0, self::SAMPLE)),
            'timezone' => $timezone,
            // What the source itself wrote for the first one, where the reader kept it.
            'source_time' => (string) ($rows[0]['source_time'] ?? ''),
            'token' => encrypt(['u' => $url, 'k' => $kind, 'n' => mb_substr($title, 0, 120), 'r' => $role->id, 'b' => $by->id, 't' => now()->getTimestamp()]),
        ];
    }

    /**
     * What a token from check() says, when it is this person's, for this schedule, from the last
     * hour. Null otherwise: a token is not a way to add an address nobody looked at.
     *
     * @return ?array{url: string, kind: string, title: string}
     */
    public function opened(?string $token, Role $role, User $by): ?array
    {
        try {
            $claim = is_string($token) && $token !== '' ? decrypt($token) : null;
        } catch (\Throwable $e) {
            return null;
        }

        $valid = is_array($claim)
            && ($claim['r'] ?? null) === $role->id
            && ($claim['b'] ?? null) === $by->id
            && ($claim['t'] ?? 0) > now()->subHour()->getTimestamp()
            && in_array($claim['k'] ?? null, EventFeed::KINDS, true)
            && is_string($claim['u'] ?? null);

        return $valid ? ['url' => $claim['u'], 'kind' => $claim['k'], 'title' => (string) ($claim['n'] ?? '')] : null;
    }

    /**
     * Add the feed that was checked. It is due at once: the next run reads it.
     *
     * @param  array{url: string, kind: string, title: string}  $checked
     * @param  array{name?: ?string, publish_mode?: ?string, left_action?: ?string, group_id?: ?int, category_id?: ?int, add_organizer?: bool, source_timezone?: ?string}  $choices
     */
    public function add(Role $role, User $by, array $checked, array $choices): EventFeed
    {
        $canSeeLeaving = FeedKind::canSeeLeaving($checked['kind']);
        $left = $choices['left_action'] ?? EventFeed::LEFT_KEEP;
        $zone = $choices['source_timezone'] ?? null;

        return EventFeed::create([
            'role_id' => $role->id,
            'added_by' => $by->id,
            'name' => mb_substr(trim((string) ($choices['name'] ?? '')) ?: $checked['title'], 0, 120),
            'url' => $checked['url'],
            'url_hash' => EventFeed::hashOf($checked['url']),
            'host' => mb_substr((string) parse_url($checked['url'], PHP_URL_HOST), 0, 255),
            'kind' => $checked['kind'],
            'publish_mode' => ($choices['publish_mode'] ?? null) === EventFeed::PUBLISH ? EventFeed::PUBLISH : EventFeed::DRAFT,
            // A source that cannot say an event is gone is not asked to act on it.
            'left_action' => $canSeeLeaving && in_array($left, EventFeed::LEFT_ACTIONS, true) ? $left : EventFeed::LEFT_KEEP,
            'group_id' => $choices['group_id'] ?? null,
            'category_id' => $choices['category_id'] ?? null,
            'add_organizer' => (bool) ($choices['add_organizer'] ?? false),
            'source_timezone' => $zone && in_array($zone, timezone_identifiers_list(), true) ? $zone : $role->captureTimezone(),
            'can_see_leaving' => $canSeeLeaving,
            'baseline_batch' => strtolower(Str::random(12)),
            'next_check_at' => now(),
        ]);
    }

    /** How a row's time reads on the check: "Sat 10 Oct, 09:00 to 13:00", on the clock the reader uses. */
    public static function when(array $sample, bool $use24 = true): string
    {
        $start = Carbon::parse($sample['at']);
        $text = $start->translatedFormat('D j M');
        $clock = $use24 ? 'H:i' : 'g:i A';

        if ($sample['all_day']) {
            return $text;
        }

        $text .= ', '.$start->format($clock);
        $minutes = $sample['duration'] ? (int) round($sample['duration'] * 60) : 0;

        // An end on another day is said by how the event is listed, not squeezed in here.
        if ($minutes > 0 && $start->copy()->addMinutes($minutes)->isSameDay($start)) {
            $text .= ' '.__('messages.feeds_time_to').' '.$start->copy()->addMinutes($minutes)->format($clock);
        }

        return $text;
    }

    /** A name for the feed, from what it calls itself. */
    private function titleOf(string $kind, FetchResult $fetched): string
    {
        if ($kind === EventFeed::KIND_CALENDAR) {
            return preg_match('/^X-WR-CALNAME[^:\r\n]*:(.+)$/mi', $fetched->body, $m) ? trim(stripcslashes($m[1])) : '';
        }

        if ($kind === EventFeed::KIND_PAGE) {
            return preg_match('#<title[^>]*>(.*?)</title>#is', substr($fetched->body, 0, 20000), $m)
                ? trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')))
                : '';
        }

        return (string) (FeedDocument::parse($fetched->body, $fetched->url)?->title ?? '');
    }
}
