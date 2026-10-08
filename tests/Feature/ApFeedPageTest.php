<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedImporter;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A feed's own page, through its routes: what it shows, and that each button does what it says
 * for the people who run the schedule and nothing for anybody else. What each action does in
 * detail is FeedActionsTest's.
 */
class ApFeedPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SECRET = 'private-0123456789abcdef';

    private User $owner;

    private Role $role;

    private array $entries = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake(fn () => Http::response($this->calendar(), 200, ['Content-Type' => 'text/calendar']));

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/Vienna', 'show_event_interest' => true]);
    }

    private function entry(string $uid, string $name, int $days = 10, string $more = ''): string
    {
        $start = now('Europe/Vienna')->addDays($days)->setTime(19, 30);

        return "UID:{$uid}\nSUMMARY:{$name}\nDTSTART;TZID=Europe/Vienna:".$start->format('Ymd\THis')."\nDTEND;TZID=Europe/Vienna:".$start->copy()->addHours(2)->format('Ymd\THis').($more ? "\n".$more : '');
    }

    private function calendar(): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($entry) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($entry))."\r\nEND:VEVENT\r\n", $this->entries))
            ."END:VCALENDAR\r\n";
    }

    private function feed(array $attrs = [], ?Role $role = null): EventFeed
    {
        $url = 'https://93.184.216.34/'.self::SECRET.'/'.($attrs['file'] ?? 'basic').'.ics';
        unset($attrs['file']);

        return EventFeed::create($attrs + [
            'role_id' => ($role ?? $this->role)->id, 'added_by' => $this->owner->id, 'name' => 'Town calendar', 'url' => $url,
            'url_hash' => EventFeed::hashOf($url), 'host' => '93.184.216.34', 'kind' => EventFeed::KIND_CALENDAR,
            'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true, 'publish_mode' => EventFeed::DRAFT,
            'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
    }

    private function read(EventFeed $feed): void
    {
        app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 30);
    }

    private function url(EventFeed $feed, string $route = 'show', array $more = []): string
    {
        return route('role.feeds.'.$route, ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($feed->id)] + $more);
    }

    private function hashOf(EventFeed $feed, string $uid): string
    {
        return UrlUtils::encodeId($feed->items()->where('external_key', EventFeedItem::keyFor($uid))->value('id'));
    }

    private function named(string $name): ?Event
    {
        return Event::where('name', $name)->first();
    }

    public function test_the_page_shows_what_waits_for_somebody_and_what_the_feed_has_done(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'Farmers market', 3, 'LOCATION:Town Square'), $this->entry('b', '{{ 7 * 7 }}<i>Jazz</i>', 4), $this->entry('c', 'Open mic', 5)];
        $this->read($feed);
        // One of them is public, somebody signs up, and then the source moves it.
        $mic = $this->named('Open mic');
        $mic->forceFill(['is_draft' => false])->save();
        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test', 'event_id' => UrlUtils::encodeId($mic->id),
            'event_date' => $mic->fresh()->getStartDateTime(null, true, $mic->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();
        $this->entries[2] = $this->entry('c', 'Open mic', 6);
        $this->read($feed);

        $response = $this->actingAs($this->owner)->get($this->url($feed))->assertOk();

        $response->assertSee('Town calendar')
            ->assertSee(trans_choice('messages.feeds_status_decide', 1, ['count' => 1]))
            ->assertSee(trans_choice('messages.feeds_waiting', 2, ['count' => 2]))
            ->assertSee(trans_choice('messages.feeds_events_count', 3, ['count' => 3]))
            ->assertSee(__('messages.feeds_read_now'))
            ->assertSee(__('messages.feeds_undo'))
            // The decision, in the words of what the feed says.
            ->assertSee(__('messages.feeds_decide_card_title'))
            ->assertSee('Open mic')
            ->assertSee(trans_choice('messages.feeds_signed_up', 1, ['count' => 1]))
            ->assertSee(__('messages.feeds_confirm_notify'))
            // The drafts, soonest first, with what each is missing.
            ->assertSeeInOrder([__('messages.feeds_waiting_title'), 'Farmers market', 'Town Square', __('messages.feeds_no_place')])
            ->assertSee(__('messages.feeds_publish_all', ['count' => 2]))
            ->assertSee(__('messages.feeds_no_picture'))
            ->assertSee(__('messages.feeds_reads_title'))
            ->assertSee(trans_choice('messages.feeds_read_added', 3, ['count' => 3]));

        // The site, never the address; and a source's words are text.
        $response->assertDontSee(self::SECRET, false)->assertSee('93.184.216.34');
        $response->assertDontSee('<i>Jazz</i>', false);
        $this->assertStringContainsString('v-pre><bdi>{{ 7 * 7 }}&lt;i&gt;Jazz', $response->getContent());
    }

    public function test_drafts_are_published_and_skipped_from_the_page_by_row_and_by_tick(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11), $this->entry('c', 'Three', 12), $this->entry('d', 'Four', 13)];
        $this->read($feed);
        $post = fn (array $body) => $this->actingAs($this->owner)->from($this->url($feed))->post($this->url($feed, 'review'), $body);

        $post(['publish_one' => $this->hashOf($feed, 'a')])->assertRedirect($this->url($feed))
            ->assertSessionHas('message', trans_choice('messages.feeds_published_count', 1, ['count' => 1]));
        $this->assertFalse((bool) $this->named('One')->is_draft);

        $post(['skip_one' => $this->hashOf($feed, 'b')])->assertSessionHas('message', trans_choice('messages.feeds_skipped_count', 1, ['count' => 1]));
        $this->assertNull($this->named('Two'));

        $post(['action' => 'publish', 'items' => [$this->hashOf($feed, 'c'), $this->hashOf($feed, 'd')]])
            ->assertSessionHas('message', trans_choice('messages.feeds_published_count', 2, ['count' => 2]));
        $this->assertSame(0, Event::where('is_draft', true)->count());

        // Nothing ticked, or something that is not an action.
        $post(['action' => 'publish'])->assertSessionHas('error', __('messages.feeds_nothing_selected'));
        $post(['action' => 'delete', 'items' => [$this->hashOf($feed, 'c')]])->assertSessionHas('error');
        $this->assertSame(3, Event::count());
    }

    public function test_publish_all_read_now_pause_resume_and_undo_are_one_press_each(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $as = $this->actingAs($this->owner);

        $as->post($this->url($feed, 'publish_all'))->assertRedirect($this->url($feed))
            ->assertSessionHas('message', trans_choice('messages.feeds_publish_all_done', 2, ['count' => 2]));
        $this->assertSame(2, $feed->items()->whereNotNull('publish_requested_at')->count());
        $as->get($this->url($feed))->assertOk()->assertSee(trans_choice('messages.feeds_publishing_requested', 2, ['count' => 2]));

        // Just read: not again within the minute.
        $as->post($this->url($feed, 'read'))->assertSessionHas('error', __('messages.feeds_read_now_wait'));
        $this->travel(2)->minutes();
        $as->post($this->url($feed, 'read'))->assertSessionHas('message', __('messages.feeds_read_now_done'));

        $as->post($this->url($feed, 'pause'))->assertSessionHas('message', __('messages.feeds_paused_done'));
        $this->assertTrue($feed->fresh()->isPaused());
        $as->get($this->url($feed))->assertOk()->assertSee(__('messages.feeds_paused_owner'))->assertSee(__('messages.resume'))->assertDontSee(__('messages.feeds_read_now'));
        $as->post($this->url($feed, 'resume'))->assertSessionHas('message', __('messages.feeds_resumed_done'));
        $this->assertFalse($feed->fresh()->isPaused());

        $as->post($this->url($feed, 'undo'))->assertRedirect($this->url($feed))
            ->assertSessionHas('message', __('messages.feeds_undone', ['removed' => 2, 'kept' => 0]));
        $this->assertSame(0, Event::count());
        $this->assertSame(EventFeed::PAUSED_UNDO, $feed->fresh()->pause_reason);

        $this->travel(2)->days();
        $as->post($this->url($feed, 'undo'))->assertSessionHas('error', __('messages.feeds_undo_too_late'));
    }

    public function test_a_decision_is_answered_from_the_page(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Gone', 11), $this->entry('c', 'Also gone', 12)];
        $this->read($feed);
        foreach (['Gone', 'Also gone'] as $name) {
            $event = $this->named($name);
            $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
                'email' => 'fan@fans.test', 'event_id' => UrlUtils::encodeId($event->id),
                'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            ])->assertOk();
        }
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->read($feed);
        $this->assertSame(2, $feed->fresh()->decide_count);
        $as = $this->actingAs($this->owner);

        $as->post($this->url($feed, 'decide', ['item' => $this->hashOf($feed, 'b')]), ['answer' => 'keep'])
            ->assertRedirect($this->url($feed))->assertSessionHas('message', __('messages.feeds_decided_kept'));
        $this->assertFalse((bool) $this->named('Gone')->is_cancelled);

        $as->post($this->url($feed, 'decide', ['item' => $this->hashOf($feed, 'c')]), ['answer' => 'apply', 'notify' => '1', 'note' => 'The hall is flooded.'])
            ->assertSessionHas('message', __('messages.feeds_decided_cancelled'));
        $this->assertTrue((bool) $this->named('Also gone')->is_cancelled);
        $this->assertSame(0, $feed->fresh()->decide_count);

        $as->post($this->url($feed, 'decide', ['item' => $this->hashOf($feed, 'b')]), ['answer' => 'maybe'])->assertSessionHasErrors('answer');
        $as->post($this->url($feed, 'decide', ['item' => $this->hashOf($feed, 'b')]), ['answer' => 'apply', 'note' => str_repeat('a', 281)])->assertSessionHasErrors('note');
    }

    public function test_removing_a_feed_keeps_its_events_or_takes_the_coming_ones_with_it(): void
    {
        $kept = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Kept')];
        $this->read($kept);
        $this->actingAs($this->owner)->delete($this->url($kept), ['its_events' => 'keep'])
            ->assertRedirect(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']))
            ->assertSessionHas('message', __('messages.feeds_removed'));
        $this->assertNotNull($this->named('Kept'));

        $gone = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'file' => 'other']);
        $this->entries = [$this->entry('b', 'Goes with it', 11)];
        $this->read($gone);
        $this->actingAs($this->owner)->delete($this->url($gone), ['its_events' => 'delete'])
            ->assertSessionHas('message', trans_choice('messages.feeds_removed_with', 1, ['count' => 1]));

        $this->assertSame(['Kept'], Event::pluck('name')->all());
        $this->assertSame(0, EventFeed::count());
        $this->assertSame(2, \App\Models\AuditLog::where('action', 'schedule.feed_remove')->count());
    }

    /** A viewer changes nothing, and a feed's id from another schedule opens nothing. */
    public function test_nobody_but_the_people_who_run_the_schedule_reaches_any_of_it(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One')];
        $this->read($feed);
        $item = $this->hashOf($feed, 'a');
        $viewer = User::factory()->create();
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);
        // Somebody who runs another schedule, trying this feed's id on their own.
        $stranger = $this->createOwner();
        $theirs = $this->createRole($stranger, 'talent');
        $onTheirs = fn (string $route, array $more = []) => route('role.feeds.'.$route, ['subdomain' => $theirs->subdomain, 'hash' => UrlUtils::encodeId($feed->id)] + $more);

        $this->actingAs($viewer)->get($this->url($feed))->assertRedirect(route('home'));
        $this->actingAs($stranger)->get($this->url($feed))->assertRedirect(route('home'));
        $this->actingAs($stranger)->get($onTheirs('show'))->assertNotFound();

        foreach ([
            ['post', 'review', [], ['publish_one' => $item]],
            ['post', 'publish_all', [], []],
            ['post', 'pause', [], []],
            ['post', 'undo', [], []],
            ['post', 'decide', ['item' => $item], ['answer' => 'apply']],
            ['delete', 'destroy', [], ['its_events' => 'delete']],
        ] as [$method, $route, $more, $body]) {
            $this->actingAs($viewer)->{$method}($this->url($feed, $route, $more), $body)->assertRedirect(route('home'));
            $this->actingAs($stranger)->{$method}($onTheirs($route, $more), $body)->assertNotFound();
        }

        $this->assertTrue((bool) $this->named('One')->is_draft);
        $this->assertNull($feed->fresh()->paused_at);
        $this->assertSame(1, EventFeed::count());
    }

    /** Off the plan nothing is read, and what the feed already made is still the owner's to deal with. */
    public function test_a_feed_on_a_plan_without_feeds_can_still_be_dealt_with_and_cannot_be_started(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One')];
        $this->read($feed);
        $feed->forceFill(['paused_at' => now(), 'pause_reason' => EventFeed::PAUSED_BY_OWNER])->save();
        $this->role->forceFill(['plan_type' => 'pro'])->save();
        $as = $this->actingAs($this->owner);

        $as->get($this->url($feed))->assertOk()->assertSee(__('messages.feeds_not_read_off_plan'))->assertDontSee(__('messages.feeds_read_now'));
        $as->post($this->url($feed, 'resume'))->assertSessionHas('error', __('messages.feeds_need_enterprise'));
        $this->assertTrue($feed->fresh()->isPaused());
        $as->post($this->url($feed, 'read'))->assertSessionHas('error', __('messages.feeds_need_enterprise'));

        $as->post($this->url($feed, 'review'), ['publish_one' => $this->hashOf($feed, 'a')])->assertSessionHas('message');
        $this->assertFalse((bool) $this->named('One')->is_draft);
        $as->delete($this->url($feed), ['its_events' => 'keep'])->assertSessionHas('message', __('messages.feeds_removed'));
    }
}
