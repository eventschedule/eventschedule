<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminDashboard;
use App\Services\AuditService;
use App\Services\DemoService;
use App\Services\RealtimeActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The sign-up figures of /admin/dashboard: who is counted, over which window, where they came
 * from and how far each has got.
 *
 * The headline counts organizers - accounts created to run a schedule - because a follower or a
 * ticket buyer creates an account without ever meaning to make one, and adding them in made every
 * busy on-sale look like growth. The others are counted beside it, never in it.
 */
class AdminDashboardSignupsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The windows are calendar days cut at the clock time of the look. Noon makes "an hour
        // earlier" and "an hour later" the same day whenever the suite happens to run.
        $this->travelTo(now()->setTime(12, 0));
    }

    private function account(array $attrs = []): User
    {
        return User::factory()->create($attrs + ['email_verified_at' => now()]);
    }

    /** Mutation: drop the signup_intent test in signups(), or the verified or demo line. */
    public function test_the_headline_counts_organizers_and_lists_the_rest_beside_them(): void
    {
        // Before intents were recorded an account has none, and is read as an organizer.
        $this->account(['created_at' => now()->subHours(2)]);
        $this->account(['created_at' => now()->subDays(3), 'signup_intent' => 'organizer']);
        $this->account(['created_at' => now()->subHour(), 'signup_intent' => 'ticket']);
        $this->account(['created_at' => now()->subDays(5), 'signup_intent' => 'follow']);
        $this->account(['created_at' => now()->subDays(6), 'signup_intent' => 'follow']);
        // Not people who signed up: an address never confirmed, and the demo account.
        $this->account(['created_at' => now()->subHour(), 'email_verified_at' => null]);
        $this->account(['created_at' => now()->subHour(), 'email' => DemoService::DEMO_EMAIL]);

        $signups = (new AdminDashboard)->signups();

        $this->assertSame(1, $signups['organizers']['last_24h']);
        $this->assertSame(2, $signups['organizers']['last_30d']);
        $this->assertSame(3, $signups['others']['total']);
        $this->assertSame(
            [['intent' => 'follow', 'count' => 2], ['intent' => 'ticket', 'count' => 1]],
            $signups['others']['by_intent']
        );

        // One bar a day, today last, and the bars are the same people as the headline.
        $this->assertCount(30, $signups['days']);
        $this->assertSame(now()->subDays(29)->toDateString(), $signups['days'][0]['date']);
        $this->assertSame(now()->toDateString(), $signups['days'][29]['date']);
        $this->assertSame(2, array_sum(array_column($signups['days'], 'organizers')));
        $this->assertSame(3, array_sum(array_column($signups['days'], 'others')));
    }

    /**
     * Thirty calendar days including today, against the same span thirty days earlier cut at the
     * same clock time: a look at noon is compared with thirty days ago at noon, not with a whole
     * day that today has not had yet. Mutation: end the previous window at the end of its day.
     */
    public function test_the_previous_window_is_cut_at_the_same_clock_time(): void
    {
        $start = now()->startOfDay()->subDays(29);

        $this->account(['created_at' => $start->copy()]);                       // the first second of the window
        $this->account(['created_at' => now()->subDays(30)->subHour()]);        // previous: before noon
        $this->account(['created_at' => $start->copy()->subDays(30)]);          // previous: its first second
        // After noon thirty days ago: today has not reached that hour, so it is in neither.
        $this->account(['created_at' => now()->subDays(30)->addHour()]);
        $this->account(['created_at' => $start->copy()->subDays(30)->subSecond()]);

        $organizers = (new AdminDashboard)->signups()['organizers'];

        $this->assertSame(1, $organizers['last_30d']);
        $this->assertSame(2, $organizers['previous_30d']);
        $this->assertSame(-50.0, $organizers['change']);
    }

    /**
     * How the organizers signed up, and the four numbers add up to the headline: an account with
     * neither a password nor Google (Facebook, or one made for someone) is "other", not nobody.
     * Mutation: drop `other`, and three numbers account for two of the three people.
     */
    public function test_the_ways_of_signing_up_add_up_to_the_headline(): void
    {
        $this->account(['created_at' => now()->subHour()]);
        $this->account(['created_at' => now()->subHours(2), 'password' => null, 'google_oauth_id' => 'g-123']);
        $this->account(['created_at' => now()->subHours(3), 'password' => null, 'facebook_id' => 'f-456']);

        $signups = (new AdminDashboard)->signups();

        $this->assertSame(['email' => 1, 'google' => 1, 'both' => 0, 'other' => 1], $signups['methods']);
        $this->assertSame($signups['organizers']['last_30d'], array_sum($signups['methods']));
    }

    /** Mutation: report +100% when there is nothing to compare with. */
    public function test_with_no_earlier_window_there_is_no_change_figure(): void
    {
        $this->account(['created_at' => now()->subDay()]);

        $organizers = (new AdminDashboard)->signups()['organizers'];

        $this->assertSame(0, $organizers['previous_30d']);
        $this->assertNull($organizers['change']);
    }

    /**
     * Every row is a share of all the organizers, "Not recorded" among them, so the list adds up
     * to the headline. Mutation: leave the unrecorded row out, or sort it by size like the rest.
     */
    public function test_the_sources_add_up_to_the_headline(): void
    {
        $referrer = $this->account(['created_at' => now()->subDays(100)]);

        foreach ([1, 2] as $minutes) {
            $this->account(['created_at' => now()->subMinutes($minutes), 'referrer_url' => 'https://www.google.com/', 'landing_page' => 'luma-alternative']);
        }
        $this->account(['created_at' => now()->subMinutes(3), 'utm_source' => 'chatgpt.com', 'landing_page' => 'features/embed-calendar']);
        $this->account(['created_at' => now()->subMinutes(4), 'referred_by_user_id' => $referrer->id]);
        $this->account(['created_at' => now()->subMinutes(5), 'landing_page' => 'pricing']);
        // Nothing but the sign-up page: a visitor who declined the cookie banner looks like this.
        foreach ([6, 7, 8] as $minutes) {
            $this->account(['created_at' => now()->subMinutes($minutes), 'landing_page' => 'sign_up']);
        }

        $signups = (new AdminDashboard)->signups();
        $sources = $signups['sources'];
        $byChannel = array_column($sources['channels'], 'count', 'channel');

        $this->assertSame(8, $signups['organizers']['last_30d']);
        $this->assertSame(8, $sources['total']);
        $this->assertSame(8, array_sum($byChannel));
        $this->assertSame(['search' => 2, 'ai' => 1, 'referral' => 1, 'direct' => 1, 'unrecorded' => 3], $byChannel);
        $this->assertSame(3, $sources['unrecorded']);

        // The largest group is last all the same: it is the absence of a source, not one.
        $this->assertSame('unrecorded', array_key_last($byChannel));
        $this->assertSame([['name' => 'google.com', 'count' => 2]], $sources['channels'][0]['names']);

        // The first page they saw, never the sign-up page every account passes through.
        $this->assertSame(['path' => '/luma-alternative', 'count' => 2], $sources['landing'][0]);
        $this->assertNotContains('/sign_up', array_column($sources['landing'], 'path'));
    }

    /**
     * One person, one source, on both admin pages: they share a classifier, and it can only agree
     * with itself when each page hands it the same columns. A sign-up tagged with a campaign and
     * nothing else read "Campaign" here and "Direct" on /admin/realtime, with no error anywhere.
     * Mutation: drop utm_campaign from the users select in RealtimeActivity::signups().
     */
    public function test_the_realtime_page_names_the_same_source_for_the_same_person(): void
    {
        $person = $this->account(['created_at' => now()->subMinutes(5), 'utm_campaign' => 'spring', 'landing_page' => 'pricing']);
        DB::table('audit_logs')->insert([
            'user_id' => $person->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $person->id,
            'ip_address' => '203.0.113.9', 'user_agent' => 'Mozilla/5.0', 'created_at' => now()->subMinutes(5),
        ]);

        $onDashboard = (new AdminDashboard)->signups()['latest'][0]['source'];
        $onRealtime = (new RealtimeActivity)->build(true)['signups']['rows'][0]['source'];

        $this->assertSame('campaign', $onDashboard['channel']);
        $this->assertSame($onDashboard, $onRealtime);
    }

    /**
     * How far each of the newest organizers has got, by the rules /admin/realtime uses for the
     * same people. Mutation: count a guest submission as their own event, or an add-on as a
     * ticket, or forget a schedule once it is deleted.
     */
    public function test_the_latest_organizers_and_the_step_each_reached(): void
    {
        $people = [];
        foreach (range(0, 6) as $index) {
            $people[] = $this->account(['created_at' => now()->subMinutes($index + 1)] + ($index === 0 ? ['name' => ''] : []));
        }
        // Newer than all of them, and not an organizer: never in this list.
        $this->account(['created_at' => now(), 'signup_intent' => 'ticket']);

        // 0: an account and nothing else.
        // 1: a schedule.
        $room = $this->createRole($people[1], 'venue', ['name' => 'First Room']);
        // 2: an event.
        $this->createEvent($this->createRole($people[2]));
        // 3: a ticket type.
        $this->createTicket($this->createEvent($this->createRole($people[3])));
        // 4: an add-on is not a ticket.
        $this->createTicket($this->createEvent($this->createRole($people[4])), ['is_addon' => true]);
        // 5: an event submitted to somebody else's schedule is not their own.
        $this->createEvent($room, ['user_id' => $people[5]->id, 'is_guest_submission' => true]);
        // 6: a schedule since deleted was still made, and has no page to link to.
        $this->createRole($people[6], 'venue', ['is_deleted' => true]);

        $latest = (new AdminDashboard)->signups()['latest'];

        $this->assertSame([0, 1, 2, 3, 2, 0, 1], array_column($latest, 'stage'));
        $this->assertSame('First Room', $latest[1]['schedule']['name']);
        $this->assertNull($latest[0]['schedule']);
        $this->assertNull($latest[6]['schedule']);
        // A person with no name is listed by their address.
        $this->assertSame($people[0]->email, $latest[0]['name']);
    }
}
