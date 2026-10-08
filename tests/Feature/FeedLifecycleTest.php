<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\RoleTransfer;
use App\Models\User;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What happens to a feed when the people around it change, and what its ledger forgets.
 *
 * A feed makes events as the schedule's owner, from an address somebody chose. When that
 * somebody is no longer there, the feed waits. And the ledger, which is what stops an event
 * being made twice, keeps an item for as long as its source could still show it to us.
 */
class FeedLifecycleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
    }

    private function feed(Role $role, ?User $addedBy = null, array $attrs = []): EventFeed
    {
        $url = 'https://93.184.216.34/'.Str::random(8).'.ics';

        return EventFeed::create($attrs + [
            'role_id' => $role->id, 'added_by' => $addedBy?->id, 'name' => 'Town calendar', 'url' => $url,
            'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34', 'kind' => EventFeed::KIND_CALENDAR,
            'source_timezone' => 'Europe/Vienna',
        ]);
    }

    private function item(EventFeed $feed, array $attrs = []): EventFeedItem
    {
        $id = Str::random(12);

        return EventFeedItem::create($attrs + ['event_feed_id' => $feed->id, 'external_key' => EventFeedItem::keyFor($id), 'external_id' => $id]);
    }

    /** The new owner chose none of its feeds. Each waits for a word from them. */
    public function test_a_schedule_that_changes_hands_has_its_feeds_paused_with_the_handover(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $other = $this->createRole($owner);
        $feed = $this->feed($role, $owner);
        $asked = $this->item($feed, ['publish_requested_at' => now()]);
        $alreadyPaused = $this->feed($role, $owner, ['paused_at' => now()->subDay(), 'pause_reason' => EventFeed::PAUSED_BY_OWNER]);
        $elsewhere = $this->feed($other, $owner);

        $newOwner = User::factory()->create(['email_verified_at' => now()]);
        $transfer = new RoleTransfer;
        $transfer->role_id = $role->id;
        $transfer->from_user_id = $owner->id;
        $transfer->to_email = $newOwner->email;
        $transfer->save();

        $this->actingAs($newOwner)->post(route('role.transfer.accept', ['token' => $transfer->fresh()->token]))->assertRedirect();

        $this->assertSame($newOwner->id, $role->fresh()->user_id, 'the handover did not go through');
        $this->assertSame(EventFeed::PAUSED_TRANSFER, $feed->fresh()->pause_reason);
        $this->assertNotNull($feed->fresh()->paused_at);
        // A "Publish all" the previous owner pressed does not fire in the new owner's name.
        $this->assertNull($asked->fresh()->publish_requested_at);
        // One that was paused for its own reason keeps it, and another schedule's is untouched.
        $this->assertSame(EventFeed::PAUSED_BY_OWNER, $alreadyPaused->fresh()->pause_reason);
        $this->assertNull($elsewhere->fresh()->paused_at);
    }

    public function test_a_feed_waits_when_the_member_who_added_it_leaves(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $admin = User::factory()->create();
        $role->users()->attach($admin->id, ['level' => 'admin']);
        $theirs = $this->feed($role, $admin);
        $owners = $this->feed($role, $owner);

        $this->actingAs($owner)->delete(route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($admin->id)]))->assertRedirect();

        $this->assertSame(EventFeed::PAUSED_MEMBER_LEFT, $theirs->fresh()->pause_reason);
        $this->assertNull($owners->fresh()->paused_at);
        // The feed is still theirs to have added, on the record.
        $this->assertSame($admin->id, $theirs->fresh()->added_by);
    }

    /**
     * Forgetting an item too soon brings its event back: the source goes on listing an event
     * until it happens, and the ledger is the only thing that says "the owner deleted that".
     */
    public function test_the_ledger_forgets_only_what_its_source_can_no_longer_show(): void
    {
        $role = $this->createRole($this->createOwner());
        $feed = $this->feed($role);
        $event = fn (int $daysAgo, float $hours = 2) => $this->createEvent($role, [
            'creator_role_id' => $role->id, 'starts_at' => now()->subDays($daysAgo)->format('Y-m-d H:i:s'), 'duration' => $hours,
        ]);
        $seen = ['last_seen_at' => now()->subDays(40)];
        $paused = $this->feed($role);
        $paused->forceFill(['paused_at' => now()->subDays(120), 'pause_reason' => EventFeed::PAUSED_BY_OWNER])->save();

        $keep = [
            'dismissed, its event still to come' => $this->item($feed, ['state' => EventFeedItem::STATE_DISMISSED, 'starts_at' => now()->addDays(20)] + $seen),
            'dismissed, three weeks past' => $this->item($feed, ['state' => EventFeedItem::STATE_DISMISSED, 'starts_at' => now()->subDays(21)] + $seen),
            'imported, three weeks past' => $this->item($feed, ['state' => EventFeedItem::STATE_IMPORTED, 'event_id' => $event(21)->id, 'starts_at' => now()->subDays(21)] + $seen),
            // The owner moved the event the item made to next week.
            'imported, moved by the owner to a date still to come' => $this->item($feed, ['state' => EventFeedItem::STATE_IMPORTED, 'event_id' => $event(-7)->id, 'starts_at' => now()->subDays(40)] + $seen),
            // A festival that began six weeks ago and runs for two months.
            'imported, still running' => $this->item($feed, ['state' => EventFeedItem::STATE_IMPORTED, 'event_id' => $event(42, 24 * 60)->id, 'starts_at' => now()->subDays(42)] + $seen),
            'new, seen last week' => $this->item($feed, ['starts_at' => null, 'last_seen_at' => now()->subDays(7)]),
            // An exhibition that opened five weeks ago is listed for months. The owner deleted
            // or skipped it: forgotten while it is still shown, it came back.
            'dismissed, five weeks past, and shown last week' => $this->item($feed, ['state' => EventFeedItem::STATE_DISMISSED, 'starts_at' => now()->subDays(35), 'last_seen_at' => now()->subDays(7)]),
            'skipped, five weeks past, and shown last week' => $this->item($feed, ['state' => EventFeedItem::STATE_SKIPPED, 'starts_at' => now()->subDays(35), 'last_seen_at' => now()->subDays(7)]),
            // A paused feed is not looking, so "not shown for a season" says nothing about its
            // source: resumed after four months it must not find its ledger empty.
            'on a paused feed, unseen for a season' => $this->item($paused, ['starts_at' => null, 'last_seen_at' => now()->subDays(100)]),
            'skipped on a paused feed, long past and long unseen' => $this->item($paused, ['state' => EventFeedItem::STATE_SKIPPED, 'starts_at' => now()->subDays(60), 'last_seen_at' => now()->subDays(100)]),
        ];
        $forget = [
            'dismissed, five weeks past' => $this->item($feed, ['state' => EventFeedItem::STATE_DISMISSED, 'starts_at' => now()->subDays(35)] + $seen),
            'imported, five weeks past' => $this->item($feed, ['state' => EventFeedItem::STATE_IMPORTED, 'event_id' => $event(35)->id, 'starts_at' => now()->subDays(35)] + $seen),
            'a post that was never an event, unseen for a season' => $this->item($feed, ['starts_at' => null, 'last_seen_at' => now()->subDays(100)]),
            'dismissed and undated, unseen for a season' => $this->item($feed, ['state' => EventFeedItem::STATE_DISMISSED, 'starts_at' => null, 'last_seen_at' => now()->subDays(100)]),
            'skipped, five weeks past and not shown since' => $this->item($feed, ['state' => EventFeedItem::STATE_SKIPPED, 'starts_at' => now()->subDays(35)] + $seen),
        ];

        $this->assertSame(count($forget), app(FeedImporter::class)->prune());

        foreach ($keep as $what => $item) {
            $this->assertNotNull($item->fresh(), $what);
        }
        foreach ($forget as $what => $item) {
            $this->assertNull($item->fresh(), $what);
        }
        // Forgetting an item never touches the event it made.
        $this->assertSame(4, Event::count());
    }
}
