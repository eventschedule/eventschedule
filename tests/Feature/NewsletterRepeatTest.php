<?php

namespace Tests\Feature;

use App\Jobs\ProcessScheduledNewsletters;
use App\Jobs\SendNewsletterBatch;
use App\Mail\NewsletterRepeatSkippedMail;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\User;
use App\Services\NewsletterService;
use App\Services\ScheduleDeletionService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Repeating newsletters (issue #134): a newsletter that sends a copy of itself every week or every
 * month, on the schedule's clock, and is never sent itself.
 *
 * The things that broke in review are each held here: two cron rails claiming one run, a monthly
 * anchor on the 31st drifting to the 28th, a copy turned back into a plain scheduled send, the
 * phone of somebody who left the team passing the verification gate, and "Send now" ending the
 * series.
 */
class NewsletterRepeatTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
    }

    private function berlinRole(?User $owner = null, array $attrs = []): Role
    {
        return $this->createRole($owner ?? $this->createOwner(), 'venue', $attrs + ['timezone' => 'Europe/Berlin']);
    }

    private function follower(Role $role): User
    {
        $user = User::factory()->create([
            'email' => 'reader'.Str::random(6).'@gmail.com',
            'is_subscribed' => true,
            'email_verified_at' => now(),
        ]);
        $role->users()->attach($user->id, ['level' => 'follower']);

        return $user;
    }

    /** A newsletter repeating every month from $at, Berlin wall clock, due at $at. */
    private function repeating(Role $role, string $at = '2026-11-01 09:00', string $every = 'month', array $attrs = []): Newsletter
    {
        $newsletter = Newsletter::create($attrs + [
            'role_id' => $role->id,
            'user_id' => $role->user_id,
            'subject' => 'What is on in {month}',
            'status' => 'scheduled',
            'template' => 'modern',
            'blocks' => [['id' => 'h', 'type' => 'heading', 'data' => ['text' => 'Hello']]],
            'type' => 'schedule',
            'scheduled_at' => Carbon::createFromFormat('Y-m-d H:i', $at, 'Europe/Berlin')->setTimezone('UTC'),
        ]);
        $newsletter->forceFill(['repeat' => ['every' => $every, 'at' => $at]])->save();

        return $newsletter->fresh();
    }

    private function runCron(): void
    {
        (new ProcessScheduledNewsletters)();
    }

    private function berlin(string $at): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', $at, 'Europe/Berlin');
    }

    private function copiesOf(Newsletter $parent)
    {
        return Newsletter::where('repeat_parent_id', $parent->id)->get();
    }

    public function test_a_due_run_sends_one_copy_and_moves_on_to_the_next_month(): void
    {
        $role = $this->berlinRole();
        $this->follower($role);
        $parent = $this->repeating($role);

        $this->travelTo($this->berlin('2026-11-01 09:00')->addSeconds(30));
        $this->runCron();

        $copies = $this->copiesOf($parent);
        $this->assertCount(1, $copies);
        $copy = $copies->first();
        $this->assertSame('sending', $copy->status);
        $this->assertNull($copy->scheduled_at);
        $this->assertNull($copy->repeat);
        // {month} is written into the copy, so the list and the statistics read "November" later.
        $this->assertSame('What is on in November 2026', $copy->subject);
        // Read as of when it went, which a run sent late must not place at its due time.
        $this->assertTrue($copy->events_as_of->eq($this->berlin('2026-11-01 09:00')->addSeconds(30)));
        Queue::assertPushed(SendNewsletterBatch::class);

        $parent->refresh();
        $this->assertSame('scheduled', $parent->status);
        $this->assertTrue($parent->isRepeating());
        $this->assertTrue($parent->scheduled_at->eq($this->berlin('2026-12-01 09:00')), (string) $parent->scheduled_at);

        // The other cron rail, the same minute: nothing left to claim.
        $this->runCron();
        $this->assertCount(1, $this->copiesOf($parent));
    }

    public function test_a_run_another_rail_already_claimed_is_lost_and_sends_nothing(): void
    {
        $role = $this->berlinRole();
        $this->follower($role);
        $parent = $this->repeating($role);
        $stale = $parent->fresh();

        $this->travelTo($this->berlin('2026-11-01 09:01'));
        $this->assertSame('sent', app(NewsletterService::class)->runRepeat($parent));

        // Meanwhile the owner moved the series to the 15th. A rail still holding the row as it
        // was at 09:00 must find nothing to claim, not move it back and send again. (Moved, so
        // the claim would CHANGE the row: MySQL counts an UPDATE that writes the value already
        // there as no row, which would let a missing due-time condition pass unnoticed.)
        $parent->refresh()->forceFill([
            'scheduled_at' => $this->berlin('2026-12-15 09:00')->setTimezone('UTC'),
            'repeat' => ['every' => 'month', 'at' => '2026-12-15 09:00'],
        ])->save();

        $this->assertSame('lost', app(NewsletterService::class)->runRepeat($stale));

        $this->assertCount(1, $this->copiesOf($parent));
        $this->assertTrue($parent->fresh()->scheduled_at->eq($this->berlin('2026-12-15 09:00')));
    }

    public function test_a_monthly_anchor_on_the_31st_goes_through_february_and_comes_back(): void
    {
        $repeat = ['every' => 'month', 'at' => '2027-01-31 09:00'];

        $feb = Newsletter::nextRepeatRun($repeat, 'Europe/Berlin', $this->berlin('2027-01-31 10:00'));
        $this->assertSame('2027-02-28 09:00', $feb->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));

        $mar = Newsletter::nextRepeatRun($repeat, 'Europe/Berlin', $this->berlin('2027-02-28 10:00'));
        $this->assertSame('2027-03-31 09:00', $mar->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));
    }

    public function test_nine_oclock_stays_nine_oclock_across_the_change_from_summer_time(): void
    {
        $repeat = ['every' => 'month', 'at' => '2026-10-01 09:00'];

        $next = Newsletter::nextRepeatRun($repeat, 'Europe/Berlin', $this->berlin('2026-10-01 09:05'));

        $this->assertSame('2026-11-01 09:00', $next->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));
        $this->assertSame('08:00', $next->copy()->setTimezone('UTC')->format('H:i'));

        $weekly = Newsletter::nextRepeatRun(['every' => 'week', 'at' => '2026-10-19 09:00'], 'Europe/Berlin', $this->berlin('2026-10-19 09:05'));
        $this->assertSame('2026-10-26 09:00', $weekly->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));
    }

    public function test_runs_missed_for_months_fire_once(): void
    {
        $role = $this->berlinRole();
        $this->follower($role);
        $parent = $this->repeating($role, '2026-08-01 09:00');

        $this->travelTo($this->berlin('2026-11-15 12:00'));
        $this->runCron();
        $this->runCron();

        $this->assertCount(1, $this->copiesOf($parent));
        // The month it went out in, not the month it was due in.
        $this->assertSame('What is on in November 2026', $this->copiesOf($parent)->first()->subject);
        $this->assertTrue($parent->fresh()->scheduled_at->eq($this->berlin('2026-12-01 09:00')));
    }

    public function test_a_month_with_no_events_in_its_range_is_skipped_and_the_owner_is_told(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $this->follower($role);
        $parent = $this->repeating($role, attrs: ['blocks' => [
            ['id' => 'e', 'type' => 'events', 'data' => ['layout' => 'cards', 'useAllEvents' => true, 'range' => 'this_month', 'eventIds' => []]],
        ]]);

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $this->runCron();

        $this->assertCount(0, $this->copiesOf($parent));
        Queue::assertNotPushed(SendNewsletterBatch::class);
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->hasTo($owner->email) && $mail->reason === 'no_events');
        $this->assertTrue($parent->fresh()->scheduled_at->eq($this->berlin('2026-12-01 09:00')));
    }

    public function test_a_run_with_nobody_to_send_to_is_skipped_deleted_and_told(): void
    {
        $owner = $this->createOwner();
        // The schedule's own members are always among the recipients; an owner who unsubscribed is not.
        $owner->forceFill(['is_subscribed' => false])->save();
        $role = $this->berlinRole($owner);
        $parent = $this->repeating($role);

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $this->runCron();

        $this->assertCount(0, $this->copiesOf($parent));
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->hasTo($owner->email) && $mail->reason === 'no_recipients');
        $this->assertTrue($parent->fresh()->isRepeating());
    }

    public function test_a_run_over_the_months_allowance_is_skipped_and_told(): void
    {
        $role = $this->createFreeRole(null, 'venue', ['timezone' => 'Europe/Berlin']);
        foreach (range(1, 3) as $i) {
            $this->follower($role);
        }
        // Nine of the ten emails a Free schedule sends in a month are already used.
        Newsletter::create(['role_id' => $role->id, 'user_id' => $role->user_id, 'subject' => 'Earlier', 'status' => 'sent', 'template' => 'modern', 'blocks' => [], 'type' => 'schedule', 'sent_at' => now(), 'sent_count' => 9]);
        $parent = $this->repeating($role, Carbon::now('Europe/Berlin')->addMinute()->format('Y-m-d H:i'));

        $this->travel(2)->minutes();
        $this->runCron();

        $this->assertCount(0, $this->copiesOf($parent));
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->reason === 'limit_exceeded');
    }

    public function test_an_author_who_left_the_team_is_neither_the_sender_nor_mailed(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $this->follower($role);
        $author = $this->createOwner();
        $role->users()->attach($author->id, ['level' => 'admin']);
        $parent = $this->repeating($role, attrs: ['user_id' => $author->id]);

        $role->users()->detach($author->id);

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $this->runCron();

        $this->assertSame($owner->id, $this->copiesOf($parent)->first()->user_id);

        // And when a run is skipped, only the owner hears of it. A segment that matches nobody,
        // with the owner (always a recipient) unsubscribed, leaves nobody to send to.
        $owner->forceFill(['is_subscribed' => false])->save();
        $empty = $this->repeating($role, '2026-11-01 09:00', attrs: ['user_id' => $author->id, 'segment_ids' => [999999]]);
        $this->runCron();
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->hasTo($owner->email));
        Mail::assertNotSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->hasTo($author->email));
        $this->assertCount(0, $this->copiesOf($empty));
    }

    public function test_send_now_sends_a_copy_and_the_series_runs_on(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $this->follower($role);
        $this->travelTo($this->berlin('2026-10-20 10:00'));
        $parent = $this->repeating($role);

        $this->actingAs($owner)->post(route('newsletter.send', ['hash' => UrlUtils::encodeId($parent->id), 'role_id' => UrlUtils::encodeId($role->id)]));

        $copies = $this->copiesOf($parent);
        $this->assertCount(1, $copies);
        $this->assertSame('sending', $copies->first()->status);
        $this->assertSame('What is on in October 2026', $copies->first()->subject);

        $parent->refresh();
        $this->assertTrue($parent->isRepeating());
        $this->assertTrue($parent->scheduled_at->eq($this->berlin('2026-11-01 09:00')));

        // The newsletter itself is never sent, whoever asks.
        $this->assertFalse(app(NewsletterService::class)->send($parent));
        $this->assertSame('scheduled', $parent->fresh()->status);
    }

    public function test_the_schedule_dialog_reads_a_repeat_on_the_schedules_clock_and_keeps_its_anchor(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['timezone' => 'Asia/Tokyo'])->save();
        $role = $this->berlinRole($owner);
        $this->follower($role);
        $newsletter = Newsletter::create(['role_id' => $role->id, 'user_id' => $owner->id, 'subject' => 'Monthly', 'status' => 'draft', 'template' => 'modern', 'blocks' => [], 'type' => 'schedule']);
        $url = route('newsletter.schedule', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]);

        $this->travelTo($this->berlin('2027-01-10 12:00'));
        $this->actingAs($owner)->post($url, ['scheduled_at' => '2027-01-31 09:00', 'repeat' => 'month'])->assertSessionHasNoErrors();

        $newsletter->refresh();
        $this->assertEqualsCanonicalizing(['every' => 'month', 'at' => '2027-01-31 09:00'], $newsletter->repeat);
        $this->assertTrue($newsletter->scheduled_at->eq($this->berlin('2027-01-31 09:00')));

        // February: the dialog shows the next run, Feb 28, and is saved again as it is.
        $newsletter->forceFill(['scheduled_at' => $this->berlin('2027-02-28 09:00')->setTimezone('UTC')])->save();
        $this->travelTo($this->berlin('2027-02-10 12:00'));
        $this->actingAs($owner)->post($url, ['scheduled_at' => '2027-02-28 09:00', 'repeat' => 'month']);

        $this->assertSame('2027-01-31 09:00', $newsletter->fresh()->repeat['at']);
    }

    public function test_a_repeat_that_could_never_go_out_on_the_plan_is_refused_and_selfhost_has_no_limit(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'venue', ['timezone' => 'Europe/Berlin']);
        foreach (range(1, 3) as $i) {
            $this->follower($role);
        }
        $newsletter = Newsletter::create(['role_id' => $role->id, 'user_id' => $owner->id, 'subject' => 'Weekly', 'status' => 'draft', 'template' => 'modern', 'blocks' => [], 'type' => 'schedule']);
        $url = route('newsletter.schedule', ['hash' => UrlUtils::encodeId($newsletter->id), 'role_id' => UrlUtils::encodeId($role->id)]);
        $when = Carbon::now('Europe/Berlin')->addDay()->format('Y-m-d H:i');

        // Three readers a week is fifteen emails in a month of five weeks, over Free's ten.
        $this->actingAs($owner)->post($url, ['scheduled_at' => $when, 'repeat' => 'week'])->assertSessionHas('error');
        $this->assertSame('draft', $newsletter->fresh()->status);

        $this->actingAs($owner)->post($url, ['scheduled_at' => $when, 'repeat' => 'month'])->assertSessionMissing('error');
        $this->assertTrue($newsletter->fresh()->isRepeating());

        config(['app.hosted' => false]);
        $this->actingAs($owner)->post($url, ['scheduled_at' => $when, 'repeat' => 'week'])->assertSessionMissing('error');
        $this->assertSame('week', $newsletter->fresh()->repeatEvery());
    }

    public function test_stop_repeating_clone_ab_test_and_a_deleted_schedule_all_leave_no_repeat_behind(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $params = fn (Newsletter $n) => ['hash' => UrlUtils::encodeId($n->id), 'role_id' => UrlUtils::encodeId($role->id)];

        $stopped = $this->repeating($role, '2027-01-01 09:00');
        $this->actingAs($owner)->post(route('newsletter.cancel', $params($stopped)));
        $this->assertSame('draft', $stopped->fresh()->status);
        $this->assertNull($stopped->fresh()->repeat);

        $source = $this->repeating($role, '2027-01-01 09:00');
        $this->actingAs($owner)->post(route('newsletter.clone', $params($source)));
        $clone = Newsletter::where('role_id', $role->id)->latest('id')->first();
        $this->assertNotSame($source->id, $clone->id);
        $this->assertNull($clone->repeat);
        $this->assertSame('draft', $clone->status);

        $this->actingAs($owner)->post(route('newsletter.ab_test', $params($source)), [
            'test_field' => 'subject', 'sample_percentage' => 20, 'winner_criteria' => 'open_rate', 'winner_wait_hours' => 4,
        ])->assertSessionHas('error');
        $this->assertNull($source->fresh()->ab_test_id);

        app(ScheduleDeletionService::class)->markDeleted($role, $owner->id);
        $this->assertNull($source->fresh()->repeat);
        $this->assertSame('draft', $source->fresh()->status);
    }

    public function test_a_copy_stuck_before_its_recipients_were_made_is_never_sent_the_plain_way(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $parent = $this->repeating($role, '2027-01-01 09:00');
        $copy = $parent->replicate(['repeat']);
        $copy->forceFill([
            'status' => 'sending',
            'scheduled_at' => null,
            'repeat_parent_id' => $parent->id,
            'events_as_of' => now(),
        ])->save();
        Newsletter::where('id', $copy->id)->update(['updated_at' => now()->subHours(2)]);

        $this->runCron();

        $this->assertNull(Newsletter::find($copy->id));
        Mail::assertSent(NewsletterRepeatSkippedMail::class, fn ($mail) => $mail->reason === 'failed');
        Queue::assertNotPushed(SendNewsletterBatch::class);
    }

    public function test_the_list_pins_a_repeating_newsletter_and_its_copies_lead_back_to_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $this->follower($role);
        $parent = $this->repeating($role, '2026-11-01 09:00');

        $this->travelTo($this->berlin('2026-11-01 09:00'));
        $this->runCron();
        $copy = $this->copiesOf($parent)->first();
        $copy->forceFill(['status' => 'sent', 'sent_at' => now(), 'sent_count' => 1])->save();

        $page = $this->actingAs($owner)->get(route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]));

        $page->assertOk()
            ->assertSee(__('messages.newsletter_status_repeating_month'))
            ->assertSee(route('newsletter.edit', ['hash' => UrlUtils::encodeId($parent->id), 'role_id' => UrlUtils::encodeId($role->id)]), false)
            ->assertSee('What is on in November 2026');
        // The repeating newsletter is listed once, pinned, not again among the rows below.
        $this->assertSame(1, substr_count($page->getContent(), 'news-repeat-row'));

        $this->actingAs($owner)->get(route('newsletter.stats', ['hash' => UrlUtils::encodeId($copy->id), 'role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(__('messages.newsletter_edit_next_one'));

        $this->actingAs($owner)->get(route('newsletter.edit', ['hash' => UrlUtils::encodeId($parent->id), 'role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(__('messages.newsletter_stop_repeating'));
    }

    public function test_a_send_recovered_before_its_recipients_forgets_when_it_was_tried(): void
    {
        $owner = $this->createOwner();
        $role = $this->berlinRole($owner);
        $stuck = Newsletter::create(['role_id' => $role->id, 'user_id' => $owner->id, 'subject' => 'One-off', 'status' => 'sending', 'template' => 'modern', 'blocks' => [], 'type' => 'schedule']);
        $stuck->forceFill(['events_as_of' => now()->subDays(3)])->save();
        Newsletter::where('id', $stuck->id)->update(['updated_at' => now()->subHours(2)]);

        $this->runCron();

        $this->assertSame('draft', $stuck->fresh()->status);
        $this->assertNull($stuck->fresh()->events_as_of);
    }
}
