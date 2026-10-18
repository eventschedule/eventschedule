<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\CalendarPrivacyMail;
use App\Models\AuditLog;
use App\Models\CalendarExposureReview;
use App\Models\CalendarImportHold;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminAlertService;
use App\Services\AuditService;
use App\Services\CalendarExposureWatch;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /admin/private-calendars: schedules a calendar sync made into somebody's private calendar. The
 * list finds them, the schedule's page shows what was found and then four steps: hide what is
 * public now, pause the sync, tell the owner, mark reviewed. Each is the operator's own press.
 */
class AdminPrivateCalendarsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ZOOM = 'https://us02web.zoom.us/j/81234567890?pwd=Xq7rTzLmPa2wKdE9vB.1';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['app.hosted' => false, 'app.is_nexus' => false]);
        Cache::forget('calendar_exposure.cursor.v1');
        Cache::forget('calendar_exposure.listed_count');
        AdminAlertService::flush();

        $this->admin = $this->createOwner(true);
    }

    private function adminActing()
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    /** A placeholder venue exactly as ConvertsLocationToVenue made one out of a meeting link. */
    private function stub(User $user, string $location): Role
    {
        $venue = new Role;
        $venue->type = 'venue';
        $venue->user_id = $user->id;
        $venue->subdomain = 'stub-'.Str::lower(Str::random(10));
        $venue->name = $location;
        $venue->address1 = $location;
        $venue->save();
        $venue->members()->attach($user->id, ['level' => 'follower', 'created_at' => now()]);

        return $venue->fresh();
    }

    /** @return array{owner: User, role: Role, codes: Event, thread: Event, zoom: Event, plain: Event, stub: Role} */
    private function leakedCalendar(array $roleAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['name' => 'Sam Owner', 'language_code' => 'es'])->save();
        $role = $this->createRole($owner, 'talent', $roleAttrs + ['name' => 'Sample Schedule', 'sync_direction' => 'both']);
        $synced = ['import_source' => Event::IMPORT_GOOGLE, 'creator_role_id' => $role->id];

        $codes = $this->createEvent($role, ['name' => 'Ops daily call', 'description' => 'Conference call: (555)-010-0199 / Code: 583920174#'] + $synced);
        $thread = $this->createEvent($role, ['name' => 'Re: [EXTERNAL] Appointment', 'description' => 'Received, thank you.'] + $synced);
        $zoom = $this->createEvent($role, ['name' => 'Committee call'] + $synced);
        $stub = $this->stub($owner, self::ZOOM);
        $zoom->roles()->attach($stub->id, ['is_accepted' => true]);
        $plain = $this->createEvent($role, ['name' => 'Speaking on the first Sunday'] + $synced);

        CalendarExposureWatch::sweep();

        return compact('owner', 'role', 'codes', 'thread', 'zoom', 'plain', 'stub');
    }

    private function hash(Role $role): string
    {
        return UrlUtils::encodeId($role->id);
    }

    public function test_only_an_operator_can_open_or_act(): void
    {
        $c = $this->leakedCalendar();
        $someone = $this->createOwner();

        $this->actingAs($someone)->get(route('admin.private_calendars'))->assertRedirect();
        $this->actingAs($someone)->post(route('admin.private_calendars.hide', ['hash' => $this->hash($c['role'])]))->assertRedirect();

        $this->assertFalse($c['codes']->fresh()->is_draft);
    }

    public function test_the_list_shows_what_was_found_and_leaves_out_blocked_demo_and_reviewed_schedules(): void
    {
        $c = $this->leakedCalendar();

        $blocked = $this->leakedCalendar(['name' => 'Blocked One']);
        $blocked['owner']->forceFill(['blocked_at' => now()])->save();

        $reviewed = $this->leakedCalendar(['name' => 'Already Reviewed']);
        CalendarExposureReview::create(['role_id' => $reviewed['role']->id, 'reviewed_at' => now()->addMinute()]);

        $demoOwner = $this->createOwner();
        $demoOwner->forceFill(['email' => DemoService::DEMO_EMAIL])->save();
        $demo = $this->createRole($demoOwner, 'talent', ['name' => 'The Demo']);
        $this->createEvent($demo, ['name' => 'Re: demo', 'import_source' => Event::IMPORT_GOOGLE, 'creator_role_id' => $demo->id]);
        CalendarExposureWatch::sweep();

        $page = $this->adminActing()->get(route('admin.private_calendars'))->assertOk();

        $page->assertSee('Sample Schedule');
        $page->assertSee('<mark class="spam-word" dir="ltr">', false);
        $page->assertSee(__('messages.calendar_privacy_kind_access_codes'));
        $page->assertDontSee('Blocked One');
        $page->assertDontSee('Already Reviewed');
        $page->assertDontSee('The Demo');
        // The codes are shown to the operator, inside a mark, and never anything else of theirs.
        $page->assertSee(__('messages.private_calendars_main_calendar'));
    }

    public function test_the_alert_counts_the_listed_schedules_and_leads_to_the_list(): void
    {
        $this->leakedCalendar();
        $this->leakedCalendar(['name' => 'Second']);

        $row = AdminAlertService::items()->firstWhere('type', 'calendar_exposures');

        $this->assertSame(2, $row['count']);
        $this->assertStringEndsWith('/admin/private-calendars', $row['url']);
        $this->assertSame('amber', $row['color']);
    }

    public function test_hide_takes_the_flagged_events_off_the_page_keeps_ticketed_ones_and_removes_the_meeting_venue(): void
    {
        $c = $this->leakedCalendar();
        $this->createSale($c['thread'], $c['role']);

        // Before anything is done: the evidence, and the four steps with their buttons.
        $this->adminActing()->get(route('admin.private_calendars.show', ['hash' => $this->hash($c['role'])]))
            ->assertOk()
            ->assertSee('Ops daily call')
            ->assertSee('<mark class="spam-word" dir="ltr">', false)
            ->assertSee(__('messages.private_calendars_hide'))
            ->assertSee(__('messages.pause'))
            ->assertSee(route('admin.private_calendars.email_preview', ['hash' => $this->hash($c['role'])]), false)
            ->assertDontSee(__('messages.private_calendars_put_back'));

        $this->adminActing()->post(route('admin.private_calendars.hide', ['hash' => $this->hash($c['role'])]))
            ->assertRedirect(route('admin.private_calendars.show', ['hash' => $this->hash($c['role'])]));

        $this->assertTrue($c['codes']->fresh()->is_draft);
        $this->assertTrue($c['zoom']->fresh()->is_draft);
        $this->assertFalse($c['thread']->fresh()->is_draft, 'people hold tickets to it');
        $this->assertFalse($c['plain']->fresh()->is_draft, 'nothing was found in it');

        $hold = CalendarImportHold::where('event_id', $c['codes']->id)->firstOrFail();
        $this->assertSame($this->admin->id, (int) $hold->held_by);
        $this->assertSame('public', $hold->was_visibility);

        $this->assertTrue((bool) $c['stub']->fresh()->is_deleted);
        $this->assertStringNotContainsString('zoom', (string) $c['stub']->fresh()->subdomain);

        $review = CalendarExposureReview::where('role_id', $c['role']->id)->firstOrFail();
        $this->assertSame([2, 1, 1], [$review->hidden_count, $review->kept_count, $review->venue_count]);
        $this->assertTrue(AuditLog::where('action', AuditService::ADMIN_CALENDAR_PRIVACY_HIDE)->exists());

        // The page says it was done, and offers to put it back.
        $this->adminActing()->get(route('admin.private_calendars.show', ['hash' => $this->hash($c['role'])]))
            ->assertOk()
            ->assertSee(__('messages.private_calendars_put_back'));
    }

    public function test_put_back_restores_what_the_operator_hid(): void
    {
        $c = $this->leakedCalendar();
        $this->adminActing()->post(route('admin.private_calendars.hide', ['hash' => $this->hash($c['role'])]));

        $this->adminActing()->post(route('admin.private_calendars.put_back', ['hash' => $this->hash($c['role'])]))->assertRedirect();

        $this->assertFalse($c['codes']->fresh()->is_draft);
        $this->assertFalse(CalendarImportHold::where('event_id', $c['codes']->id)->whereNull('decided_at')->exists(), 'nothing left waiting');
        $this->assertTrue(AuditLog::where('action', AuditService::ADMIN_CALENDAR_PRIVACY_PUT_BACK)->exists());
    }

    public function test_pause_stops_reading_and_keeps_pushing_and_resume_puts_it_back(): void
    {
        $c = $this->leakedCalendar(['microsoft_sync_direction' => 'from']);

        $this->adminActing()->post(route('admin.private_calendars.pause', ['hash' => $this->hash($c['role'])]))->assertRedirect();

        $role = $c['role']->fresh();
        $this->assertSame('to', $role->sync_direction);
        $this->assertNull($role->microsoft_sync_direction);

        $this->adminActing()->post(route('admin.private_calendars.resume', ['hash' => $this->hash($c['role'])]))->assertRedirect();

        $role = $c['role']->fresh();
        $this->assertSame('both', $role->sync_direction);
        $this->assertSame('from', $role->microsoft_sync_direction);
        $this->assertTrue(AuditLog::where('action', AuditService::ADMIN_CALENDAR_PRIVACY_RESUME)->exists());
    }

    public function test_the_owner_is_told_once_a_week_at_most_and_never_through_the_schedules_own_mail_server(): void
    {
        Queue::fake();
        $c = $this->leakedCalendar();

        $this->adminActing()->post(route('admin.private_calendars.email', ['hash' => $this->hash($c['role'])]))->assertRedirect();
        // An hour later, not the same second: an UPDATE that writes the same values counts no rows.
        $this->travel(1)->hours();
        $this->adminActing()->post(route('admin.private_calendars.email', ['hash' => $this->hash($c['role'])]))
            ->assertSessionHas('error', __('messages.private_calendars_email_too_soon'));

        Queue::assertPushed(SendQueuedEmail::class, 1);
        Queue::assertPushed(SendQueuedEmail::class, function (SendQueuedEmail $job) use ($c) {
            $read = fn (string $property) => (new \ReflectionProperty($job, $property))->getValue($job);

            return $read('recipient') === $c['owner']->email
                && $read('roleId') === null
                && $read('locale') === 'es'
                && $read('mailable') instanceof CalendarPrivacyMail;
        });

        $this->travel(8)->days();
        $this->adminActing()->post(route('admin.private_calendars.email', ['hash' => $this->hash($c['role'])]));
        Queue::assertPushed(SendQueuedEmail::class, 2);
    }

    public function test_the_preview_is_the_mail_in_the_owners_language_or_in_english(): void
    {
        $c = $this->leakedCalendar();

        $this->adminActing()->get(route('admin.private_calendars.email_preview', ['hash' => $this->hash($c['role'])]))
            ->assertOk()
            ->assertSee(e(trans('messages.calendar_privacy_mail_heading', [], 'es')), false);

        $this->adminActing()->get(route('admin.private_calendars.email_preview', ['hash' => $this->hash($c['role']), 'lang' => 'en']))
            ->assertOk()
            ->assertSee(e(trans('messages.calendar_privacy_mail_heading', [], 'en')), false);
    }

    public function test_mark_reviewed_takes_it_off_the_list_until_something_new_turns_up(): void
    {
        $c = $this->leakedCalendar();

        $this->adminActing()->post(route('admin.private_calendars.reviewed', ['hash' => $this->hash($c['role'])]))
            ->assertRedirect(route('admin.private_calendars'));
        // Its row, not its name: the flash the redirect carries names it.
        $row = route('admin.private_calendars.show', ['hash' => $this->hash($c['role'])]);
        $this->adminActing()->get(route('admin.private_calendars'))->assertOk()->assertDontSee($row, false);
        $this->assertSame(0, CalendarExposureWatch::count());

        // A new passcode in an event already flagged is a new match.
        $this->travel(5)->minutes();
        $c['codes']->forceFill(['description' => "Meeting ID: 812 3456 7890\nPasscode: 123456"])->save();
        CalendarExposureWatch::record($c['codes']->fresh());

        $this->adminActing()->get(route('admin.private_calendars'))->assertOk()->assertSee($row, false);
    }
}
