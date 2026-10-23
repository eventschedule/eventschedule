<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A provider's feed of posts that pages back for years, read only as far as the importer goes
 * (twenty pages). Such a read never reaches the end, so it used to say nothing about what had
 * left the source: a post taken down stayed an event for good. The list is newest first by when
 * each post was put up, so what was put up after the oldest post the read reached has all been
 * seen, and a post from then that is gone has left.
 *
 * One post a page, so that twenty pages are twenty posts. Every answer is faked.
 */
class FeedPartialListTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const FEED = 'https://93.184.216.34/rss/feedtoken1';

    private User $owner;

    private Role $role;

    /** The posts the source lists, newest first, as id => when it was put up. */
    private array $posts = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => false]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake(function ($request) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);

            if (preg_match('#/item/(\d+)\.json$#', $path, $m)) {
                return Http::response(json_encode(['posting' => [
                    'post_message_id' => (int) $m[1],
                    'post_message_type' => 'event',
                    'post_message_title' => 'Event '.$m[1],
                    'post_message_start' => now('Europe/Vienna')->addDays(10 + (int) $m[1] % 50)->setTime(19, 0)->format('Y-m-d H:i:s'),
                    'post_message_location' => 'Mill Hall',
                ]]), 200, ['Content-Type' => 'application/json']);
            }

            $page = preg_match('#/rss/feedtoken1/(\d+)$#', $path, $m) ? (int) $m[1] : 1;
            $id = array_keys($this->posts)[$page - 1] ?? null;
            $item = $id === null ? '' : '<item><title>Event '.$id.'</title><guid>'.self::FEED.'/item/'.$id.'.xml</guid>'
                .'<pubDate>'.gmdate('D, d M Y H:i:s', $this->posts[$id]).' GMT</pubDate></item>';

            return Http::response('<?xml version="1.0"?><rss version="2.0"><channel><title>Posts</title>'.$item.'</channel></rss>', 200, ['Content-Type' => 'application/rss+xml']);
        });

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna']);
    }

    private function feed(): EventFeed
    {
        return EventFeed::create([
            'role_id' => $this->role->id, 'added_by' => $this->owner->id, 'name' => 'Posts',
            'url' => self::FEED, 'url_hash' => EventFeed::hashOf(self::FEED), 'host' => '93.184.216.34',
            'kind' => EventFeed::KIND_JOLIOO, 'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true,
            'publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    /** Posts $from..$to, the highest put up last. */
    private function posted(int $from, int $to): array
    {
        $posts = [];
        for ($id = $to; $id >= $from; $id--) {
            $posts[$id] = 1_780_000_000 + $id * 3600;
        }

        return $posts;
    }

    private function read(EventFeed $feed): array
    {
        return app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 60);
    }

    /** Read twice, an hour and a half apart, which is what "gone" takes. */
    private function readTwiceLater(EventFeed $feed): void
    {
        foreach ([1, 2] as $look) {
            DB::table('event_feed_items')->update(['last_seen_at' => DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);
            $this->read($feed);
        }
    }

    private function cancelled(int $id): bool
    {
        return (bool) Event::where('name', 'Event '.$id)->value('is_cancelled');
    }

    public function test_a_post_taken_down_inside_what_the_read_reached_is_gone_and_one_beyond_it_is_not(): void
    {
        $feed = $this->feed();
        // Twenty posts: every one on the twenty pages read, and the list ends there.
        $this->posts = $this->posted(1, 20);
        $this->read($feed);
        $this->assertSame(20, Event::count());

        // Five new posts push the oldest five past the twentieth page, and post 12 is taken down.
        $this->posts = $this->posted(1, 25);
        unset($this->posts[12]);
        $this->readTwiceLater($feed);

        $this->assertTrue($this->cancelled(12), 'taken down after the oldest post the read reached');
        foreach ([1, 2, 3, 4, 5, 6] as $beyond) {
            $this->assertFalse($this->cancelled($beyond), "post {$beyond} is only further back than the read went");
        }
        $this->assertSame(1, Event::where('is_cancelled', true)->count());
    }

    public function test_a_post_moved_to_the_top_is_not_gone(): void
    {
        $feed = $this->feed();
        $this->posts = $this->posted(1, 20);
        $this->read($feed);

        // Edited and put up again: it leads the list now, and the rest moved down one.
        $this->posts = [15 => 1_780_000_000 + 99 * 3600] + array_diff_key($this->posted(1, 20), [15 => true]);
        $this->readTwiceLater($feed);

        $this->assertSame(0, Event::where('is_cancelled', true)->count());
    }

    /** Stored before a hint said when its post was put up: nothing to judge it by. */
    public function test_an_item_that_does_not_say_when_it_was_put_up_is_never_judged(): void
    {
        $feed = $this->feed();
        $this->posts = $this->posted(1, 20);
        $this->read($feed);

        $item = $feed->items()->where('external_key', EventFeedItem::keyFor('12'))->firstOrFail();
        $pending = $item->pending;
        unset($pending['hint']['published']);
        $item->forceFill(['pending' => $pending])->save();

        $this->posts = $this->posted(1, 25);
        unset($this->posts[12]);
        $this->readTwiceLater($feed);

        $this->assertFalse($this->cancelled(12));
    }

    /** An item stored before is given its time on the next read that lists it. */
    public function test_an_item_stored_without_its_time_is_given_it(): void
    {
        $feed = $this->feed();
        $this->posts = $this->posted(1, 3);
        $this->read($feed);

        $item = $feed->items()->where('external_key', EventFeedItem::keyFor('2'))->firstOrFail();
        $pending = $item->pending;
        unset($pending['hint']['published']);
        $item->forceFill(['pending' => $pending])->save();

        $this->read($feed);

        $this->assertSame($this->posts[2], $item->fresh()->pending['hint']['published']);
    }
}
