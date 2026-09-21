<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Models\Event;
use App\Models\Role;
use App\Utils\DiscoveryUtils;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One schedule cannot own a platform discovery list.
 *
 * The bug: the homepage Discover rail opened with four consecutive cards from one festival,
 * showing the same flyer four times, because that schedule had published a per-day event twice,
 * once per language. Every discovery surface ordered by date and took the first N rows, so a
 * cluster of same-day events from one schedule sat at the top of all of them.
 *
 * Two halves. The collection walk is tested directly, because the inputs that matter (an event
 * with no resolvable schedule, a deliberately scrambled sort order) cannot be produced through
 * the discovery queries, which require an accepted pivot on a listed schedule. The wiring is
 * tested over HTTP, one test per surface, because the thing most likely to break there is a
 * caller handing the view the candidate POOL instead of its display limit.
 *
 * The invariant to protect: the cap DEMOTES, it never drops. The homepage wall pads itself with
 * demo flyers below 25 real events, the rail drops its pinned animation below 4, and
 * /for-talent hides its whole section below 4. A cap that shortened these lists would trade a
 * repetitive rail for a fake one.
 */
class DiscoveryPerScheduleCapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    // ------------------------------------------------------------- the walk

    public function test_one_schedule_gets_two_slots_and_the_rest_are_demoted_not_dropped(): void
    {
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'a.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => 1, 'image' => 'b.png', 'at' => '2026-10-02 19:00:00'],
            ['schedule' => 1, 'image' => 'c.png', 'at' => '2026-10-03 19:00:00'],
            ['schedule' => 1, 'image' => 'd.png', 'at' => '2026-10-04 19:00:00'],
            ['schedule' => 2, 'image' => 'e.png', 'at' => '2026-10-05 19:00:00'],
        ]);

        $spread = DiscoveryUtils::spread($events, 5);

        $this->assertSame([1, 1, 2, 1, 1], $this->schedules($spread));
        $this->assertCount(5, $spread, 'The demoted events must still be in the list, further down');
    }

    public function test_a_list_is_never_shortened_by_the_cap(): void
    {
        $events = $this->fakeEvents(array_map(fn ($i) => [
            'schedule' => 1,
            'image' => "flyer{$i}.png",
            'at' => '2026-10-0'.$i.' 19:00:00',
        ], range(1, 6)));

        $this->assertCount(6, DiscoveryUtils::spread($events, 25));
    }

    public function test_the_same_flyer_twice_on_one_schedule_is_demoted(): void
    {
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'same.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => 1, 'image' => 'same.png', 'at' => '2026-10-02 19:00:00'],
            ['schedule' => 1, 'image' => 'other.png', 'at' => '2026-10-03 19:00:00'],
        ]);

        $spread = DiscoveryUtils::spread($events, 3);

        $this->assertSame(
            ['same.png', 'other.png', 'same.png'],
            $this->flyerNames($spread),
            'The repeat of one picture yields its slot to a different one'
        );
    }

    public function test_one_schedule_publishing_the_same_slot_twice_is_demoted_even_with_different_files(): void
    {
        // The case that prompted this. A cloned or re-uploaded flyer lands under a fresh random
        // filename, so the two language copies of one session carry different image URLs and
        // one identical start. Without the start-time signal the rail shows both.
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'day1_th.png', 'at' => '2026-09-23 09:30:00'],
            ['schedule' => 1, 'image' => 'day1_en.png', 'at' => '2026-09-23 09:30:00'],
            ['schedule' => 1, 'image' => 'day2_th.png', 'at' => '2026-09-24 09:30:00'],
            ['schedule' => 1, 'image' => 'day2_en.png', 'at' => '2026-09-24 09:30:00'],
        ]);

        $spread = DiscoveryUtils::spread($events, 4);

        $this->assertSame(
            ['day1_th.png', 'day2_th.png', 'day1_en.png', 'day2_en.png'],
            $this->flyerNames($spread)
        );
    }

    public function test_two_different_schedules_sharing_a_picture_both_keep_it(): void
    {
        // Scoped per schedule on purpose: two schedules are not each other's duplicates, and
        // one of them would otherwise lose its only card to the other's stock photo.
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'stock.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => 2, 'image' => 'stock.png', 'at' => '2026-10-02 19:00:00'],
        ]);

        $this->assertSame([1, 2], $this->schedules(DiscoveryUtils::spread($events, 2)));
    }

    public function test_events_with_no_resolvable_schedule_do_not_share_one_bucket(): void
    {
        // Unreachable through the discovery queries, which all require an accepted pivot on a
        // listed schedule. It matters anyway: a single null key would put every such event in
        // one bucket and demote all but two of them.
        $events = $this->fakeEvents([
            ['schedule' => null, 'image' => 'a.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => null, 'image' => 'b.png', 'at' => '2026-10-02 19:00:00'],
            ['schedule' => null, 'image' => 'c.png', 'at' => '2026-10-03 19:00:00'],
            ['schedule' => null, 'image' => 'd.png', 'at' => '2026-10-04 19:00:00'],
        ]);

        $spread = DiscoveryUtils::spread($events, 4);

        $this->assertSame(
            ['a.png', 'b.png', 'c.png', 'd.png'],
            $this->flyerNames($spread)
        );
    }

    public function test_a_list_with_nothing_to_demote_comes_back_untouched(): void
    {
        // The no-op proof, and the guard against someone "restoring" a date sort at the end.
        // The input order here is the SQL order, which puts a recurring series whose anchor has
        // passed after the upcoming one-offs, and undated rows after that. Any re-sort on
        // starts_at would reorder these three, and would put the null FIRST.
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'soon.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => 2, 'image' => 'series.png', 'at' => '2025-01-04 19:00:00'],
            ['schedule' => 3, 'image' => 'undated.png', 'at' => null],
        ]);

        $spread = DiscoveryUtils::spread($events, 25);

        $this->assertSame(
            ['soon.png', 'series.png', 'undated.png'],
            $this->flyerNames($spread)
        );
    }

    public function test_a_quota_below_one_disables_the_spread(): void
    {
        $events = $this->fakeEvents([
            ['schedule' => 1, 'image' => 'a.png', 'at' => '2026-10-01 19:00:00'],
            ['schedule' => 1, 'image' => 'a.png', 'at' => '2026-10-02 19:00:00'],
        ]);

        $this->assertSame(
            ['a.png', 'a.png'],
            $this->flyerNames(DiscoveryUtils::spread($events, 25, 0))
        );
    }

    public function test_the_pool_is_wider_than_any_surface_asks_to_display(): void
    {
        // /browse renders its whole collection, so reordering 24 rows would leave the same 24
        // events on the page. The benefit comes entirely from looking past them.
        $this->assertGreaterThan(24, DiscoveryUtils::poolLimit(24));
        $this->assertSame(DiscoveryUtils::poolLimit(8), DiscoveryUtils::poolLimit(24));
        $this->assertSame(200, DiscoveryUtils::poolLimit(200), 'A limit past the pool wins');
    }

    // ---------------------------------------------------------- the surfaces

    public function test_the_homepage_rail_does_not_open_with_one_schedule(): void
    {
        $owner = $this->createOwner();
        $loud = $this->createRole($owner, 'talent', ['name' => 'Loud Festival']);

        foreach (range(1, 4) as $i) {
            $this->createEvent($loud, [
                'name' => 'Loud Day '.$i,
                'flyer_image_url' => 'loud'.$i.'.png',
                'starts_at' => now()->addDays($i)->setTime(9, 30)->format('Y-m-d H:i:s'),
            ]);
        }

        $quiet = $this->createRole($this->createOwner(), 'venue', ['name' => 'Quiet Room']);
        $this->createEvent($quiet, [
            'name' => 'Quiet Night',
            'flyer_image_url' => 'quiet.png',
            // Latest of all five, so ONLY the spread can pull it into the front of the rail.
            'starts_at' => now()->addDays(9)->setTime(9, 30)->format('Y-m-d H:i:s'),
        ]);

        $this->assertSame(
            ['loud1.png', 'loud2.png', 'quiet.png', 'loud3.png', 'loud4.png'],
            $this->flyers($this->get('/')->assertOk(), 'discoverEvents'),
            'Two from the festival, then the other schedule, then the demoted pair'
        );
    }

    public function test_the_homepage_caches_the_spread_list_not_the_raw_pool(): void
    {
        // Spreading after Cache::remember would re-run the walk on every request for nothing,
        // and would put the whole candidate pool in the cache instead of the 25 the wall wants.
        //
        // This asserts on the CACHED VALUE, and uses two schedules so the spread actually
        // reorders. Both matter: with a single schedule the spread is order-preserving, so the
        // rendered page looks identical whether the walk ran inside the closure, outside it, or
        // not at all - a test built that way cannot fail.
        config(['marketing.wall_cache_seconds' => 60]);

        $loud = $this->createRole($this->createOwner(), 'talent', ['name' => 'Loud Festival']);

        foreach (range(1, 3) as $i) {
            $this->createEvent($loud, [
                'name' => 'Loud Day '.$i,
                'flyer_image_url' => 'loud'.$i.'.png',
                'starts_at' => now()->addDays($i)->setTime(9, 30)->format('Y-m-d H:i:s'),
            ]);
        }

        $quiet = $this->createRole($this->createOwner(), 'venue', ['name' => 'Quiet Room']);
        $this->createEvent($quiet, [
            'name' => 'Quiet Night',
            'flyer_image_url' => 'quiet.png',
            // Last by date, so only the spread can lift it above the festival's third night.
            'starts_at' => now()->addDays(9)->setTime(9, 30)->format('Y-m-d H:i:s'),
        ]);

        $this->get('/')->assertOk();

        $cached = Cache::get(MarketingController::wallCacheKey());

        $this->assertNotNull($cached, 'The homepage wall must be cached');
        $this->assertSame(
            ['loud1.png', 'loud2.png', 'quiet.png', 'loud3.png'],
            $this->flyerNames($cached),
            'The cached collection must already be spread, not the raw candidate pool'
        );
    }

    public function test_two_events_wearing_the_same_profile_photo_both_stay(): void
    {
        // The card image falls back to the schedule's profile photo, and the wall, /browse and
        // /for-talent all admit events on the strength of that photo. Fingerprinting the RESOLVED
        // image would therefore hand every schedule without per-event flyers a quota of one,
        // silently and only for them. Two cards wearing a venue's photo still carry their own
        // name and date; two cards wearing the same flyer are the same poster twice.
        $room = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Quiet Room',
            'profile_image_url' => 'room.png',
        ]);

        foreach (['Quiet One', 'Quiet Two'] as $i => $name) {
            $this->createEvent($room, [
                'name' => $name,
                'starts_at' => now()->addDays($i + 1)->setTime(20, 0)->format('Y-m-d H:i:s'),
            ]);
        }

        $this->assertSame(
            ['Quiet One', 'Quiet Two'],
            $this->get('/browse')->assertOk()->viewData('events')->map(fn ($e) => $e->name)->all()
        );
    }

    public function test_a_card_credits_the_schedule_attached_first_not_the_oldest(): void
    {
        // Nothing else in the suite pins this, which is how an eager-load `orderBy('roles.id')`
        // slipped through green while changing what production serves. The order decides the
        // name on the card (getViewableRole) AND its href, because getGuestUrlData() reads the
        // first talent. Built so the two candidate orderings DISAGREE: the older schedule is
        // attached second, so a roles.id sort would credit it and an event_role.id sort will not.
        $owner = $this->createOwner();
        $older = $this->createRole($owner, 'talent', ['name' => 'Older Schedule']);
        $attachedFirst = $this->createRole($owner, 'talent', ['name' => 'Attached First']);

        $this->assertLessThan($attachedFirst->id, $older->id, 'The fixture needs the older id first');

        $event = $this->createEvent($attachedFirst, [
            'name' => 'Double Billed',
            'flyer_image_url' => 'double.png',
            'starts_at' => now()->addDays(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);
        $event->roles()->attach($older->id, ['is_accepted' => true]);

        $shown = $this->get('/browse')->assertOk()->viewData('events')->first();

        $this->assertSame('Attached First', $shown->getViewableRole()->name);
        $this->assertStringContainsString($attachedFirst->subdomain, $shown->getGuestUrl());
    }

    public function test_browse_shows_its_limit_not_the_candidate_pool(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Loud Festival']);

        foreach (range(1, 30) as $i) {
            $this->createEvent($role, [
                'name' => 'Loud Night '.$i,
                'flyer_image_url' => 'loud'.$i.'.png',
                'starts_at' => now()->addDays($i)->setTime(20, 0)->format('Y-m-d H:i:s'),
            ]);
        }

        $this->assertCount(24, $this->get('/browse')->assertOk()->viewData('events'));
    }

    public function test_search_still_returns_every_match_for_one_schedule(): void
    {
        // Search is intent-driven: someone typing a festival's name wants its dates. The spread
        // reorders them, it must never hide one.
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Mekong Festival']);

        foreach (range(1, 5) as $i) {
            $this->createEvent($role, [
                'name' => 'Mekong Session '.$i,
                'flyer_image_url' => 'mekong'.$i.'.png',
                'starts_at' => now()->addDays($i)->setTime(9, 30)->format('Y-m-d H:i:s'),
            ]);
        }

        $found = $this->get('/search?q=Mekong')->assertOk()->viewData('events')
            ->map(fn ($e) => $e->name)->sort()->values()->all();

        $this->assertSame([
            'Mekong Session 1', 'Mekong Session 2', 'Mekong Session 3',
            'Mekong Session 4', 'Mekong Session 5',
        ], $found);
    }

    public function test_for_talent_still_fills_its_rail(): void
    {
        // The view renders 8, else 4, else nothing. A cap that dropped rows could push a page
        // with nine real shows down to a hidden section.
        $role = $this->createRole($this->createOwner(), 'talent', ['name' => 'Loud Festival']);

        foreach (range(1, 9) as $i) {
            $this->createEvent($role, [
                'name' => 'Loud Show '.$i,
                'flyer_image_url' => 'loud'.$i.'.png',
                'starts_at' => now()->addDays($i)->setTime(20, 0)->format('Y-m-d H:i:s'),
            ]);
        }

        $this->assertCount(8, $this->get('/for-talent')->assertOk()->viewData('talentEvents'));
    }

    // ------------------------------------------------------------- fixtures

    /**
     * Unsaved events, so the walk can be handed shapes the discovery queries cannot produce.
     *
     * Each one carries a real CLAIMED role on its `roles` relation, so the walk keys on
     * getViewableRole() - the schedule the card credits - exactly as it does for a real row.
     *
     * creator_role_id is deliberately a DECOY, unique per event and never equal to the attached
     * schedule. It is the fallback key, so pointing it somewhere else is what makes these tests
     * able to tell the two branches apart: drop getViewableRole() from scheduleKey() and every
     * event lands in its own bucket, no quota is ever reached, and the assertions below fail.
     * Setting it to the real schedule id (the obvious thing) silently tests nothing.
     *
     * A null schedule attaches nothing and carries no decoy, which is the shape that reaches the
     * last-resort per-event key.
     */
    private function fakeEvents(array $rows): EloquentCollection
    {
        $schedules = [];

        return new EloquentCollection(array_map(function ($row, $i) use (&$schedules) {
            $event = new Event;
            $event->id = $i + 1;
            $event->name = 'Event '.($i + 1);
            $event->creator_role_id = $row['schedule'] === null ? null : 900 + $i;
            $event->flyer_image_url = $row['image'];
            $event->starts_at = $row['at'];

            $attached = new EloquentCollection;

            if ($row['schedule'] !== null) {
                $schedules[$row['schedule']] ??= tap(new Role, function ($role) use ($row) {
                    $role->id = $row['schedule'];
                    $role->name = 'Schedule '.$row['schedule'];
                    $role->type = 'talent';
                    // isClaimed(): a verified contact plus an owner.
                    $role->user_id = 1;
                    $role->email_verified_at = now();
                });

                $attached->push($schedules[$row['schedule']]);
            }

            $event->setRelation('roles', $attached);
            $event->setRelation('creatorRole', null);

            return $event;
        }, $rows, array_keys($rows)));
    }

    /**
     * The flyer filenames a rendered surface put in front of the visitor, in order.
     *
     * Read off the view data rather than the markup: the order is the whole assertion, and it
     * is also where the likeliest wiring mistake shows up, which is handing the view the wide
     * candidate pool instead of the number the surface means to display.
     */
    private function flyers(TestResponse $response, string $key): array
    {
        return $this->flyerNames($response->viewData($key));
    }

    /**
     * The id of the schedule each card credits, in order - the key the quota is spent against.
     * Read through getViewableRole() rather than creator_role_id, which the fixture points
     * elsewhere on purpose.
     */
    private function schedules(iterable $events): array
    {
        return collect($events)->map(fn ($e) => $e->getViewableRole()?->id)->all();
    }

    /**
     * Flyer filenames, in order. Compared by basename because the flyer_image_url accessor
     * resolves a bare filename to a full storage URL, which varies by host and disk.
     */
    private function flyerNames(iterable $events): array
    {
        return collect($events)->map(fn ($e) => basename((string) $e->flyer_image_url))->all();
    }
}
