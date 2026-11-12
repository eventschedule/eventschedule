<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AuditService;
use App\Services\RealtimeSoFar;
use App\Utils\DayPace;
use App\Utils\RealtimeTracker;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The "So far today, against before" card of /admin/dashboard (RealtimeSoFar, DayPace): today
 * hour by hour against yesterday and a usual day of the same weekday, each cut at the same clock
 * time, in the viewer's zone, with the day projected from what is in so far.
 */
class RealtimeSoFarTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        // Thursday 2026-11-12, 14:30 in Jerusalem (UTC+2).
        $this->travelTo(CarbonImmutable::parse('2026-11-12 12:30:00', 'UTC'));
        Setting::set('realtime_enabled', '1');
    }

    /** Mutation: cut yesterday at another time, or read a usual day from another weekday. */
    public function test_today_is_set_against_yesterday_and_a_usual_weekday_by_the_same_time(): void
    {
        // Ten visits an hour, every hour, for five weeks; today until now. The four Thursdays
        // before today had twenty, so a usual day read from any other weekday reads ten.
        $this->visits(35, 10);
        foreach ([1, 2, 3, 4] as $weeks) {
            $day = CarbonImmutable::now('Asia/Jerusalem')->startOfDay()->subWeeks($weeks);
            DB::table('realtime_hourly')->where('hour', '>=', RealtimeTracker::ts($day))->where('hour', '<', RealtimeTracker::ts($day->addDay()))->update(['visits' => 20]);
        }

        $chart = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build();
        $visits = $chart['metrics']['visits'];

        $this->assertSame('2026-11-12', $chart['date']);
        $this->assertSame('Thursday', $chart['weekday']);
        $this->assertSame(14, $visits['cut']['slot']);
        $this->assertSame(0.5, $visits['cut']['fraction']);
        $this->assertStringContainsString('2:30 PM', $visits['cut']['label']);
        $this->assertStringContainsString('12 AM', $visits['hours'][0]);

        // 00:00 to 14:00 local is 14 whole hours, and the hour under way has its row (12:00 UTC).
        $this->assertSame(150, $visits['soFar']);
        $this->assertNull($visits['now'][15], 'nothing is drawn after now');
        $this->assertSame(240, $visits['previousTotal']);
        $this->assertEquals(145.0, $visits['previousByNow'], '14 hours and half of the 15th');
        $this->assertSame(4, $visits['usual']['available']);
        $this->assertEquals(290.0, $visits['usual']['byNow'], 'the Thursdays, not the days before');
        $this->assertEquals(480.0, $visits['usual']['high'][23]);
        $this->assertEquals(290.0, $visits['usual']['lowByNow'], 'the band at the cut, for "Usually 290 to 290 by now"');
        $this->assertEquals(290.0, $visits['usual']['highByNow']);

        // The projection: the day so far over a usual day's share by now, every line starting
        // from what is already in.
        $projection = $visits['projection'];
        $this->assertTrue($projection['shown']);
        $this->assertGreaterThan(150, $projection['value']);
        $this->assertGreaterThanOrEqual(150, $projection['low']);
        $this->assertCount(10, $projection['path']['mean'], 'from the hour under way to midnight');
        $this->assertGreaterThanOrEqual(150, $projection['path']['low'][0], 'the fan starts at today\'s count, not under it');
        $this->assertEquals($projection['value'], end($projection['path']['mean']));
        $this->assertEquals($projection['low'], end($projection['path']['low']));
    }

    /** Mutation: cut visits at now instead of where the rollup has counted to. */
    public function test_visits_are_cut_where_the_counting_has_reached(): void
    {
        $this->visits(35, 10);
        // The rollup has counted to 12:10 UTC, 14:10 in Jerusalem.
        DB::table('realtime_rollup')->insert(['id' => 1, 'through_at' => '2026-11-12 12:10:00']);

        $chart = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build();

        $this->assertSame(14, $chart['metrics']['visits']['cut']['slot']);
        $this->assertEqualsWithDelta(140 + 10 / 6, $chart['metrics']['visits']['previousByNow'], 0.1);
        $this->assertStringContainsString('2:10 PM', $chart['metrics']['visits']['cut']['label']);
        // Sign-ups are written as they happen, so they are cut at now.
        $this->assertSame(0.5, $chart['metrics']['signups']['cut']['fraction']);

        // A rollup that stopped yesterday: today is cut at its midnight, and says so.
        DB::table('realtime_rollup')->where('id', 1)->update(['through_at' => '2026-11-11 15:00:00']);
        $stopped = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build()['metrics']['visits'];
        $this->assertSame(0, $stopped['cut']['slot']);
        $this->assertStringContainsString('12:00 AM', $stopped['cut']['label']);
        $this->assertEquals(0.0, $stopped['previousByNow']);
    }

    /** Mutation: drop the minutes-past offset, so a half-hour zone compares half an hour more of yesterday. */
    public function test_a_half_hour_zone_counts_the_cut_from_where_its_slots_begin(): void
    {
        $this->visits(35, 10, null, 'Asia/Kolkata');

        // 12:30 UTC is 18:00 in Kolkata; its slots are the UTC hours, so they run from :30.
        $visits = (new RealtimeSoFar(null, 'Asia/Kolkata'))->build()['metrics']['visits'];

        $this->assertStringContainsString('12:30 AM', $visits['hours'][0]);
        $this->assertSame(17, $visits['cut']['slot']);
        $this->assertEquals(175.0, $visits['previousByNow'], '00:30 to 18:00 yesterday: 17.5 slots');
    }

    /** Mutation: send showsFrom as the hour's index, or drop it. */
    public function test_an_early_projection_says_when_it_will_show(): void
    {
        // Every visit of the last five weeks came in the evening, from 18:00 Jerusalem.
        $this->visits(35, 50);
        DB::table('realtime_hourly')->get(['id', 'hour'])->each(function ($row) {
            if (CarbonImmutable::parse($row->hour, 'UTC')->setTimezone('Asia/Jerusalem')->hour < 18) {
                DB::table('realtime_hourly')->where('id', $row->id)->update(['visits' => 0]);
            }
        });

        $projection = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build()['metrics']['visits']['projection'];

        $this->assertFalse($projection['shown']);
        $this->assertSame('early', $projection['hidden']);
        $this->assertStringContainsString('8 PM', $projection['showsFrom']);
    }

    /** Mutation: drop the whole-day test in series(), so a day before counting reads as a quiet one. */
    public function test_a_day_counting_had_not_reached_is_left_out(): void
    {
        // Counting began yesterday at noon (Jerusalem): yesterday and the usual days are unknown.
        $this->visits(1, 10, CarbonImmutable::parse('2026-11-11 12:00:00', 'Asia/Jerusalem'));

        $visits = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build()['metrics']['visits'];

        $this->assertNull($visits['previous']);
        $this->assertNull($visits['previousByNow']);
        $this->assertNull($visits['usual']);
        $this->assertFalse($visits['projection']['shown']);
        $this->assertSame('noHistory', $visits['projection']['hidden']);
    }

    public function test_visits_are_null_until_anything_is_counted_and_signups_are_read_from_the_audit_log(): void
    {
        $owner = $this->createOwner();
        // Two today (one at 01:30 local, which is yesterday 23:30 UTC), one yesterday.
        foreach (['2026-11-11 23:30:00', '2026-11-12 09:10:00', '2026-11-11 09:00:00'] as $at) {
            DB::table('audit_logs')->insert(['user_id' => $owner->id, 'action' => AuditService::AUTH_REGISTER, 'model_type' => 'User', 'model_id' => $owner->id, 'ip_address' => '127.0.0.1', 'created_at' => $at]);
        }

        $chart = (new RealtimeSoFar(null, 'Asia/Jerusalem'))->build();

        $this->assertNull($chart['metrics']['visits'], 'not a zero: nothing was counted');
        $signups = $chart['metrics']['signups'];
        $this->assertSame(2, $signups['soFar']);
        $this->assertSame(1, $signups['now'][1], 'in the local hour it happened');
        $this->assertSame(1, $signups['previousTotal']);
        $this->assertSame('tooFew', $signups['projection']['hidden']);
    }

    public function test_the_address_answers_admins_only(): void
    {
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->createOwner(true))
            ->getJson('/admin/realtime/so-far')->assertOk()->assertJsonStructure(['date', 'cut', 'weekday', 'metrics' => ['visits', 'signups' => ['cut', 'hours', 'ticks', 'now', 'soFar']]]);

        $this->actingAs($this->createOwner())->getJson('/admin/realtime/so-far')->assertStatus(403);
    }

    public function test_a_usual_days_shape_and_the_projection(): void
    {
        $flat = array_fill(0, 24, 10);
        $this->assertEqualsWithDelta(0.5, DayPace::shape($flat, 4, 720), 0.001);
        $this->assertSame(1.0, DayPace::shape($flat, 4, 1440));

        // All of a day's visits in the evening: by noon almost none is in.
        $evening = array_merge(array_fill(0, 18, 0), array_fill(0, 6, 500));
        $this->assertLessThan(0.1, DayPace::shape($evening, 4, 720));
        // By 19:00 one of six evening hours is in; by 20:00, two: past a quarter.
        $this->assertSame(20, DayPace::reaches($evening, 4, 0.25));

        $this->assertSame('early', DayPace::project(100, 0.2, true)['hidden']);
        $this->assertSame('tooFew', DayPace::project(10, 0.5, true)['hidden']);
        // A share reached only at midnight never shows today.
        $this->assertNull(DayPace::reaches(array_merge(array_fill(0, 23, 0), [100000]), 0, 0.9));
        $this->assertSame('noHistory', DayPace::project(100, 0.5, false)['hidden']);
        $this->assertSame(200, DayPace::project(100, 0.5, true)['value']);
    }

    /** $count visits in every hour from $days days ago (or $from) until now. */
    private function visits(int $days, int $count, ?CarbonImmutable $from = null, string $zone = 'Asia/Jerusalem'): void
    {
        $at = ($from ?? CarbonImmutable::now($zone)->startOfDay()->subDays($days))->utc()->startOfHour();
        $rows = [];
        while ($at->lte(now())) {
            $rows[] = ['hour' => RealtimeTracker::ts($at), 'surface' => 'gp', 'page_views' => $count * 2, 'visits' => $count];
            $at = $at->addHour();
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('realtime_hourly')->insert($chunk);
        }
    }
}
