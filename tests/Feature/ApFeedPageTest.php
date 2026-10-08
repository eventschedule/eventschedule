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

    /**
     * Read it again a while later. An entry has to have been out of sight for an hour and a
     * half before its absence means anything (FeedImporter::GONE_AFTER_MINUTES): two reads a
     * minute apart are one look, not two.
     */
    private function readLater(EventFeed $feed): void
    {
        \Illuminate\Support\Facades\DB::table('event_feed_items')->update(['last_seen_at' => \Illuminate\Support\Facades\DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);

        $this->read($feed);
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
            ->assertSee($this->url($feed, 'edit'), false)
            // The decision, in the words of what the feed says.
            ->assertSee(__('messages.feeds_decide_card_title'))
            ->assertSee(__('messages.feeds_decide_card_lead'))
            ->assertSee('Open mic')
            ->assertSee(trans_choice('messages.feeds_signed_up', 1, ['count' => 1]))
            ->assertSee(__('messages.feeds_confirm_notify'))
            // The drafts, soonest first, with what each is missing.
            ->assertSeeInOrder([__('messages.feeds_waiting_title'), 'Farmers market', 'Town Square', __('messages.feeds_no_place')])
            ->assertSee(__('messages.feeds_publish_all', ['count' => 2]))
            // It asks first, in words about publishing: this is not the Add feed button.
            ->assertSee('data-confirm="'.e(__('messages.feeds_publish_all_confirm', ['count' => 2])).'"', false)
            ->assertDontSee('Add feed and publish')
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

    /**
     * Skip deletes for good and asked nothing, and "Skip selected" was the review form's first
     * button, which is the one Enter presses, from a tick box too.
     */
    public function test_skip_asks_first_and_enter_on_a_tick_box_presses_nothing(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'One'), $this->entry('b', 'Two', 11)];
        $this->read($feed);
        $html = $this->actingAs($this->owner)->get($this->url($feed))->assertOk()->getContent();

        preg_match_all('/<button[^>]*name="skip_one"[^>]*>/', $html, $rows);
        $this->assertCount(2, $rows[0]);
        foreach ($rows[0] as $button) {
            $this->assertStringContainsString('data-confirm="'.e(__('messages.feeds_skip_confirm')).'"', $button);
        }
        $this->assertSame(1, preg_match('/<button[^>]*value="skip"[^>]*>/', $html, $selected));
        $this->assertStringContainsString('data-confirm="'.e(__('messages.feeds_skip_selected_confirm')).'"', $selected[0]);

        // The form's first submit button, in the order of the page, is the one Enter presses:
        // the first that names the form, or that stands inside it. It is disabled, and a
        // disabled default button means Enter submits nothing.
        preg_match_all('/<button[^>]*type="submit"[^>]*>/', $html, $buttons, PREG_OFFSET_CAPTURE);
        $form = strpos($html, 'id="feed-review"');
        $first = collect($buttons[0])->first(fn (array $button) => str_contains($button[0], 'form="feed-review"') || $button[1] > $form);
        $this->assertStringContainsString(' disabled', $first[0]);
        $this->assertStringNotContainsString('name=', $first[0]);
        $this->assertStringNotContainsString('data-feed-bulk', $first[0], 'and the script that shows the others never shows it');
    }

    /** A draft the owner has worked on is kept, and the page says so instead of "0 skipped". */
    public function test_skipping_says_what_was_kept_because_it_was_changed(): void
    {
        $feed = $this->feed();
        $this->entries = [$this->entry('a', 'As it came'), $this->entry('b', 'Rewritten', 11)];
        $this->read($feed);
        $this->named('Rewritten')->forceFill(['description' => 'Our own words about it.'])->save();
        $post = fn (array $body) => $this->actingAs($this->owner)->from($this->url($feed))->post($this->url($feed, 'review'), $body);

        $post(['skip_one' => $this->hashOf($feed, 'b')])->assertSessionHas('error', trans_choice('messages.feeds_skipped_kept', 1, ['count' => 1]));
        $this->assertNotNull($this->named('Rewritten'));

        $post(['action' => 'skip', 'items' => [$this->hashOf($feed, 'a'), $this->hashOf($feed, 'b')]])->assertSessionHas('message',
            trans_choice('messages.feeds_skipped_count', 1, ['count' => 1]).' '.trans_choice('messages.feeds_skipped_kept', 1, ['count' => 1]));
        $this->assertNull($this->named('As it came'));
        $this->assertNotNull($this->named('Rewritten'));
    }

    /**
     * On a feed that no run reads, Publish all does it in the request, a page at a time, and
     * the button says so. It used to say "109 are being published" for as long as you looked.
     */
    public function test_publish_all_on_a_paused_feed_publishes_now_and_says_how_many(): void
    {
        $feed = $this->feed();
        $this->entries = array_map(fn ($n) => $this->entry('e'.$n, 'Event '.$n, 10 + $n), range(1, 27));
        $this->read($feed);
        $as = $this->actingAs($this->owner);
        $as->get($this->url($feed))->assertOk()->assertSee(__('messages.feeds_publish_all', ['count' => 27]))->assertDontSee(__('messages.feeds_publish_next', ['count' => 25]));

        $as->post($this->url($feed, 'pause'));
        $as->get($this->url($feed))->assertOk()
            ->assertSee(__('messages.feeds_publish_next', ['count' => 25]))
            ->assertSee(__('messages.feeds_publish_next_confirm', ['count' => 25]))
            ->assertDontSee(__('messages.feeds_publish_all', ['count' => 27]));

        $as->post($this->url($feed, 'publish_all'))->assertRedirect($this->url($feed).'#waiting')
            ->assertSessionHas('message', trans_choice('messages.feeds_published_count', 25, ['count' => 25]));
        $this->assertSame(2, Event::where('is_draft', true)->count());

        // What is left fits in one press, and nothing is said to be on its way.
        $as->get($this->url($feed))->assertOk()
            ->assertSee(__('messages.feeds_publish_all', ['count' => 2]))
            ->assertDontSee(trans_choice('messages.feeds_publishing_requested', 2, ['count' => 2]));
        $as->post($this->url($feed, 'publish_all'))->assertSessionHas('message', trans_choice('messages.feeds_published_count', 2, ['count' => 2]));
        $this->assertSame(0, Event::where('is_draft', true)->count());
    }

    /**
     * What the page says of a feed is true of it: not "Up to date" over one that is paused or
     * cannot be read, not the last good read beside "Not read since", and never a try that is
     * due "2 minutes ago".
     */
    public function test_a_feed_that_is_paused_or_cannot_be_read_is_not_called_up_to_date(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'One')];
        $this->read($feed);
        $as = $this->actingAs($this->owner);
        $tab = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'feeds']);
        $as->get($this->url($feed))->assertOk()->assertSee('<h3 v-pre>'.__('messages.feeds_status_ok').'</h3>', false)->assertSee(', next about ');

        // Two days without a good read, and the next try already due.
        $feed->forceFill(['failure_count' => 4, 'last_status' => 'http_error', 'last_success_at' => now()->subDays(2), 'next_check_at' => now()->subMinutes(2)])->save();
        $failing = __('messages.feeds_status_failing', ['date' => now()->subDays(2)->setTimezone('Europe/Vienna')->translatedFormat('D j M')]);
        foreach ([$this->url($feed), $tab] as $page) {
            $as->get($page)->assertOk()
                ->assertSee($failing)
                ->assertSee(__('messages.feeds_trying_again', ['when' => now()->addSeconds(90)->diffForHumans()]))
                ->assertDontSee(' ago')
                ->assertDontSee(', next about ')
                ->assertDontSee(__('messages.feeds_status_ok'));
        }
        $as->get($this->url($feed))->assertSee('<h3 v-pre>'.e($failing).'</h3>', false);

        $as->post($this->url($feed, 'pause'));
        $as->get($this->url($feed))->assertOk()
            ->assertSee('<h3 v-pre>'.__('messages.feeds_status_paused').'</h3>', false)
            ->assertDontSee(__('messages.feeds_status_ok'))
            ->assertDontSee(__('messages.feeds_read_now'));
    }

    /**
     * The decision's box offered "Email the people who signed up" whenever somebody had. Somebody
     * who began to buy and never paid has signed up as far as the feed is concerned, and is
     * nobody the email goes to: the offer was a switch that did nothing.
     */
    public function test_a_decision_offers_to_email_people_only_where_somebody_would_get_it(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Never paid', 11), $this->entry('c', 'Asked about', 12)];
        $this->read($feed);
        $this->createSale($this->named('Never paid'), $this->role, ['status' => 'unpaid']);
        $asked = $this->named('Asked about');
        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test', 'event_id' => UrlUtils::encodeId($asked->id),
            'event_date' => $asked->getStartDateTime(null, true, $asked->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk();
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(2, $feed->fresh()->decide_count);

        $html = $this->actingAs($this->owner)->get($this->url($feed))->assertOk()->getContent();
        $box = fn (string $uid) => \Illuminate\Support\Str::betweenFirst($html, 'id="confirm-'.$this->hashOf($feed, $uid).'"', '</form>');

        $this->assertStringContainsString('name="notify"', $box('c'));
        $this->assertStringContainsString(trans_choice('messages.feeds_signed_up', 1, ['count' => 1]), $html);
        $this->assertStringNotContainsString('name="notify"', $box('b'));
        $this->assertStringNotContainsString('name="note"', $box('b'));
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
        $this->readLater($feed);
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

    /**
     * A feed told to remove what leaves it does not remove an event the owner has worked on:
     * that is a decision too, and nobody signed up for it, so the page does not say they did.
     */
    public function test_a_decision_about_the_owners_own_work_does_not_say_people_signed_up(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_DELETE]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Rewritten by hand', 11)];
        $this->read($feed);
        $this->named('Rewritten by hand')->forceFill(['description' => 'Our own words about it.'])->save();
        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $this->assertSame(1, $feed->fresh()->decide_count);
        $this->assertNotNull($this->named('Rewritten by hand'));

        $this->actingAs($this->owner)->get($this->url($feed))->assertOk()
            ->assertSee(__('messages.feeds_decide_card_title'))
            ->assertSee(__('messages.feeds_says_gone'))
            ->assertDontSee(__('messages.feeds_decide_card_lead'))
            ->assertDontSee(__('messages.feeds_confirm_notify'));
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

    public function test_a_feeds_settings_are_changed_on_its_edit_page(): void
    {
        $markets = $this->role->groups()->create(['name' => 'Markets', 'slug' => 'markets']);
        $elsewhere = $this->createRole($this->createOwner(), 'talent');
        $theirs = $elsewhere->groups()->create(['name' => 'Not ours', 'slug' => 'not-ours']);
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_KEEP]);
        $feed->forceFill(['etag' => '"abc"', 'last_modified' => 'Thu, 08 Oct 2026 08:00:00 GMT', 'next_check_at' => now()->addHour()])->save();
        $as = $this->actingAs($this->owner);
        $category = collect($this->role->getEventCategories())->first()['id'];

        $page = $as->get($this->url($feed, 'edit'))->assertOk()
            ->assertSee(__('messages.feeds_edit'))
            ->assertSee(__('messages.feeds_reads_from'))
            ->assertSee('93.184.216.34')
            ->assertDontSee(self::SECRET, false)
            ->assertSee('value="Town calendar"', false)
            ->assertSee(__('messages.feeds_remove'));
        $this->assertMatchesRegularExpression('/name="publish_mode" value="publish" checked/', $page->getContent());
        $this->assertMatchesRegularExpression('/name="left_action" value="keep" checked/', $page->getContent());
        $this->assertMatchesRegularExpression('/value="Europe\/Vienna" selected/', $page->getContent());

        // Everything but the clock: saved, and the feed is not hurried.
        $as->put($this->url($feed, 'update'), [
            'name' => '  Town hall  ', 'publish_mode' => 'draft', 'left_action' => 'delete', 'source_timezone' => 'Europe/Vienna',
            'group_id' => UrlUtils::encodeId($markets->id), 'category_id' => $category,
        ])->assertRedirect($this->url($feed))->assertSessionHas('message', __('messages.feeds_saved'));

        $feed->refresh();
        $this->assertSame(['Town hall', 'draft', 'delete', $markets->id, $category], [$feed->name, $feed->publish_mode, $feed->left_action, $feed->group_id, $feed->category_id]);
        $this->assertSame('"abc"', $feed->etag);
        $this->assertTrue($feed->next_check_at->isFuture());

        // A sub-schedule or a category that is not this schedule's is not taken.
        $as->put($this->url($feed, 'update'), [
            'name' => 'Town hall', 'publish_mode' => 'draft', 'left_action' => 'delete', 'source_timezone' => 'Europe/Vienna',
            'group_id' => UrlUtils::encodeId($theirs->id), 'category_id' => 987654,
        ])->assertSessionHas('message');
        $this->assertSame([null, null], [$feed->fresh()->group_id, $feed->fresh()->category_id]);

        foreach ([['name' => ''], ['publish_mode' => 'now'], ['left_action' => 'burn'], ['source_timezone' => 'Mars/Olympus']] as $bad) {
            $as->put($this->url($feed, 'update'), $bad + ['name' => 'Town hall', 'publish_mode' => 'draft', 'left_action' => 'delete', 'source_timezone' => 'Europe/Vienna'])
                ->assertSessionHasErrors(array_key_first($bad));
        }
        $this->assertSame('Europe/Vienna', $feed->fresh()->source_timezone);
        $this->assertSame('Town hall', $feed->fresh()->name);
        // A list where text belongs is refused, not thrown on further in.
        $as->put($this->url($feed, 'update'), ['name' => 'Town hall', 'publish_mode' => 'draft', 'source_timezone' => 'Europe/Vienna', 'group_id' => ['x']])->assertSessionHasErrors('group_id');
        $as->put($this->url($feed, 'update'), ['name' => 'Town hall', 'publish_mode' => 'draft', 'source_timezone' => ['Europe/Vienna']])->assertSessionHasErrors('source_timezone');
        // The two saves that were taken, and none of the six that were refused.
        $this->assertSame(2, \App\Models\AuditLog::where('action', 'schedule.feed_update')->count());
    }

    /**
     * A zone stored under a name PHP's list no longer offers (a browser that reported
     * Asia/Calcutta) matched no option, so the select fell on its first entry and saving a new
     * name moved every start to Africa/Abidjan. It is shown under the name it goes by now, and
     * saving that is no change of clock.
     */
    public function test_a_stored_zone_the_list_does_not_name_is_kept_and_is_not_a_change_of_clock(): void
    {
        $feed = $this->feed();
        $feed->forceFill(['source_timezone' => 'Asia/Calcutta', 'etag' => '"abc"', 'next_check_at' => now()->addHour()])->save();
        $as = $this->actingAs($this->owner);

        $html = $as->get($this->url($feed, 'edit'))->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<option value="[^"]+" selected/', \Illuminate\Support\Str::betweenFirst($html, 'name="source_timezone"', '</select>'), $selected));
        $this->assertStringContainsString('value="Asia/Kolkata" selected', $selected[0][0]);

        $as->put($this->url($feed, 'update'), ['name' => 'A new name', 'publish_mode' => 'draft', 'left_action' => 'cancel', 'source_timezone' => 'Asia/Kolkata'])
            ->assertSessionHas('message', __('messages.feeds_saved'));
        $feed->refresh();
        $this->assertSame(['A new name', 'Asia/Kolkata', '"abc"'], [$feed->name, $feed->source_timezone, $feed->etag]);
        $this->assertTrue($feed->next_check_at->isFuture(), 'the same clock under another name: nothing is read again');

        // A real change of clock on a feed that is paused is saved, and not said to be read again.
        $as->post($this->url($feed, 'pause'));
        $as->put($this->url($feed, 'update'), ['name' => 'A new name', 'publish_mode' => 'draft', 'left_action' => 'cancel', 'source_timezone' => 'Europe/London'])
            ->assertSessionHas('message', __('messages.feeds_saved'));
        $this->assertSame('Europe/London', $feed->fresh()->source_timezone);
    }

    /**
     * "Leave it" has let events go by. Changing it reaches back to every one of them at the next
     * read, and the Edit page says how many before the choice is made.
     */
    public function test_the_edit_page_says_how_many_events_a_change_of_mind_would_reach(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'left_action' => EventFeed::LEFT_KEEP]);
        $this->entries = [$this->entry('a', 'Stays'), $this->entry('b', 'Left', 11), $this->entry('c', 'Left too', 12)];
        $this->read($feed);
        $as = $this->actingAs($this->owner);
        $notice = trans_choice('messages.feeds_gone_reaches', 2, ['count' => 2]);
        $as->get($this->url($feed, 'edit'))->assertOk()->assertDontSee($notice);

        $this->entries = [$this->entry('a', 'Stays')];
        $this->read($feed);
        $this->readLater($feed);
        $as->get($this->url($feed, 'edit'))->assertOk()->assertSee($notice);

        // Once the choice is made it is what the feed does, and there is nothing to warn of.
        $feed->forceFill(['left_action' => EventFeed::LEFT_CANCEL])->save();
        $as->get($this->url($feed, 'edit'))->assertOk()->assertDontSee($notice);
    }

    /**
     * 19:30 with no zone was read as Vienna's. Said to be London's, it is an hour later on the
     * clock, and the start follows unless the owner had set it.
     */
    public function test_a_new_clock_moves_the_starts_the_owner_has_not_changed(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH]);
        $floating = fn (string $uid, string $name, int $days) => "UID:{$uid}\nSUMMARY:{$name}\nDTSTART:".now('Europe/Vienna')->addDays($days)->format('Ymd').'T193000';
        $this->entries = [$floating('a', 'Follows the feed', 10), $floating('b', 'Set by hand', 11), $this->entry('c', 'Says its own zone', 12)];
        $this->read($feed);
        $before = fn (string $name) => $this->named($name)->starts_at;
        $follows = $before('Follows the feed');
        $zoned = $before('Says its own zone');
        $byHand = \Carbon\Carbon::parse($before('Set by hand'))->addHours(3)->format('Y-m-d H:i:s');
        $this->named('Set by hand')->forceFill(['starts_at' => $byHand])->save();
        $feed->forceFill(['etag' => '"abc"', 'next_check_at' => now()->addHour()])->save();

        $this->actingAs($this->owner)->put($this->url($feed, 'update'), [
            'name' => 'Town calendar', 'publish_mode' => 'publish', 'left_action' => 'cancel', 'source_timezone' => 'Europe/London',
        ])->assertSessionHas('message', __('messages.feeds_saved_clock'));

        // Asked again at once, and not with "has it changed since".
        $feed->refresh();
        $this->assertNull($feed->etag);
        $this->assertFalse($feed->next_check_at->isFuture());

        $this->read($feed);

        $this->assertSame(\Carbon\Carbon::parse($follows)->addHour()->format('Y-m-d H:i:s'), $before('Follows the feed'));
        $this->assertSame($byHand, $before('Set by hand'));
        $this->assertSame($zoned, $before('Says its own zone'));
        // The ledger's own copy of when each is, which is what orders the review list.
        foreach (['a' => 'Follows the feed', 'c' => 'Says its own zone'] as $uid => $name) {
            $this->assertSame($before($name), $feed->items()->where('external_key', EventFeedItem::keyFor($uid))->first()->starts_at->format('Y-m-d H:i:s'));
        }
    }

    /**
     * The feed's clock is London's and the schedule's is Vienna's. An event that says "19:30,
     * Vienna" and is already on the schedule at 19:30 is that event: linked, not added again.
     */
    public function test_an_event_already_on_the_schedule_is_matched_on_its_own_clock(): void
    {
        $start = now('Europe/Vienna')->addDays(10)->setTime(19, 30);
        $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Open stage', 'starts_at' => $start->copy()->utc()->format('Y-m-d H:i:s')]);
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'source_timezone' => 'Europe/London']);
        $this->entries = [$this->entry('a', 'Open stage', 10)];

        $found = app(\App\Services\Feeds\FeedSetup::class)->check($this->role, $this->owner, 'https://93.184.216.34/'.self::SECRET.'/check.ics', 'Europe/London');
        $this->assertTrue($found['ok']);
        $this->assertSame(1, $found['matched']);

        $this->read($feed);

        $this->assertSame(1, Event::where('name', 'Open stage')->count());
        $this->assertSame(EventFeedItem::STATE_MATCHED, $feed->items()->first()->state);
    }

    /** Where an event is edited, it says a feed keeps it up to date, and no other event says so. */
    public function test_the_event_form_says_which_feed_an_event_comes_from(): void
    {
        $feed = $this->feed(['publish_mode' => EventFeed::PUBLISH, 'name' => 'Town {{ 7 * 7 }} <b>calendar</b>']);
        $this->entries = [$this->entry('a', 'From the feed')];
        $this->read($feed);
        $byHand = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'name' => 'Made by hand']);
        $form = fn (Event $event) => $this->actingAs($this->owner)
            ->get(route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))->assertOk()->getContent();
        $line = '<span v-pre>'.e(__('messages.feeds_event_line', ['feed' => 'Town {{ 7 * 7 }} <b>calendar</b>'])).'</span>';

        $html = $form($this->named('From the feed'));
        $this->assertStringContainsString($line, $html);
        $this->assertStringContainsString('href="'.$this->url($feed).'"', $html);
        $this->assertStringNotContainsString(self::SECRET, $html);

        $this->assertStringNotContainsString('id="event-from-feed"', $form($byHand));

        // Opened through a schedule that only lists it: the line, and no link to a page that
        // is not that schedule's.
        $curator = $this->createCurator($this->owner);
        $this->named('From the feed')->roles()->attach($curator->id, ['is_accepted' => true]);
        $listed = $this->actingAs($this->owner)
            ->get(route('event.edit', ['subdomain' => $curator->subdomain, 'hash' => UrlUtils::encodeId($this->named('From the feed')->id)]))->assertOk()->getContent();
        $this->assertStringContainsString($line, $listed);
        $this->assertStringNotContainsString('/feeds/'.UrlUtils::encodeId($feed->id), $listed);

        // Once the feed is removed its events are events like any other, whatever other feeds
        // the schedule goes on reading.
        $this->feed(['file' => 'another', 'name' => 'Another feed']);
        app(\App\Services\Feeds\FeedActions::class)->remove($feed->fresh(), $this->owner, false);
        $this->assertStringNotContainsString('id="event-from-feed"', $form($this->named('From the feed')));
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
        $this->actingAs($viewer)->get($this->url($feed, 'edit'))->assertRedirect(route('home'));
        $this->actingAs($stranger)->get($onTheirs('edit'))->assertNotFound();

        foreach ([
            ['post', 'review', [], ['publish_one' => $item]],
            ['post', 'publish_all', [], []],
            ['post', 'pause', [], []],
            ['put', 'update', [], ['name' => 'Taken over', 'publish_mode' => 'publish', 'source_timezone' => 'UTC']],
            ['post', 'undo', [], []],
            ['post', 'decide', ['item' => $item], ['answer' => 'apply']],
            ['delete', 'destroy', [], ['its_events' => 'delete']],
        ] as [$method, $route, $more, $body]) {
            $this->actingAs($viewer)->{$method}($this->url($feed, $route, $more), $body)->assertRedirect(route('home'));
            $this->actingAs($stranger)->{$method}($onTheirs($route, $more), $body)->assertNotFound();
        }

        $this->assertTrue((bool) $this->named('One')->is_draft);
        $this->assertNull($feed->fresh()->paused_at);
        $this->assertSame('Town calendar', $feed->fresh()->name);
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
