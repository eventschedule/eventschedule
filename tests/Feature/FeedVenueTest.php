<?php

namespace Tests\Feature;

use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Models\Role;
use App\Models\User;
use App\Services\Feeds\FeedVenueResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which venue an event from a feed is at.
 *
 * A save looks a venue up across the whole install by name and attaches what it finds. With a
 * person at a form that is a convenience; for a feed it files a request on somebody else's
 * venue for every one of a hundred events. So a feed only ever uses a venue its own team runs,
 * an unclaimed one already on its events, or one it makes.
 */
class FeedVenueTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function feed(Role $role): EventFeed
    {
        $url = 'https://calendar.example.org/'.Str::random(8).'.ics';

        return EventFeed::create([
            'role_id' => $role->id, 'name' => 'Town calendar', 'url' => $url, 'url_hash' => EventFeed::hashOf($url),
            'host' => 'calendar.example.org', 'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna',
        ]);
    }

    private function resolver(): FeedVenueResolver
    {
        return app(FeedVenueResolver::class);
    }

    /** A venue nobody has claimed: no owner, no verified contact. */
    private function unclaimed(string $name, array $attrs = []): Role
    {
        $venue = new Role;
        $venue->subdomain = 'stub'.strtolower(Str::random(10));
        $venue->type = 'venue';
        $venue->name = $name;
        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }
        $venue->save();

        return $venue->fresh();
    }

    private function row(string $venue, string $address = '', string $city = ''): array
    {
        return ['event_name' => 'Concert', 'venue_name' => $venue, 'event_address' => $address, 'event_city' => $city];
    }

    public function test_a_venue_schedules_events_are_at_the_venue_and_a_row_that_names_no_place_has_none(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $talent = $this->createRole($owner, 'talent');
        $known = [];
        $before = Role::count();

        $atVenue = $this->resolver()->resolve($this->feed($venue), $venue, $this->row('Somewhere Else', '1 Other Street'), $known);
        $this->assertSame($venue->id, $atVenue['venue']->id);

        $nowhere = $this->resolver()->resolve($this->feed($talent), $talent, $this->row(''), $known);
        $this->assertNull($nowhere['venue']);
        $this->assertFalse($nowhere['deferred']);

        $this->assertSame($before, Role::count());
        $this->assertSame([], $known);
    }

    public function test_a_venue_the_team_runs_is_used_and_one_somebody_else_runs_never_is(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['country_code' => 'at', 'language_code' => 'de']);
        $ours = $this->createRole($owner, 'venue', ['name' => 'Stadtsaal Voitsberg']);
        // Run by an admin of the schedule who is not its owner.
        $admin = User::factory()->create();
        $talent->users()->attach($admin->id, ['level' => 'admin']);
        $adminsVenue = $this->createRole($admin, 'venue', ['name' => 'Kunsthaus']);
        // The same names, run by people who have nothing to do with this schedule.
        $theirs = $this->createRole($this->createOwner(), 'venue', ['name' => 'Rathaus']);
        $follower = $this->createRole($this->createOwner(), 'venue', ['name' => 'Schlossberg']);
        $this->followRole($owner, $follower);
        // One of them even hosts an event of ours already. It is still theirs to say yes to.
        $this->createEvent($talent, ['creator_role_id' => $talent->id])->roles()->attach($theirs->id, ['is_accepted' => true]);

        $feed = $this->feed($talent);
        $known = [];
        $resolver = $this->resolver();

        $this->assertSame($ours->id, $resolver->resolve($feed, $talent, $this->row('  stadtsaal   VOITSBERG ', 'Conrad-von-Hötzendorf-Straße 25'), $known)['venue']->id);
        $this->assertSame($adminsVenue->id, $resolver->resolve($feed, $talent, $this->row('Kunsthaus'), $known)['venue']->id);

        foreach (['Rathaus' => $theirs, 'Schlossberg' => $follower] as $name => $notOurs) {
            $made = $resolver->resolve($feed, $talent, $this->row($name, 'Hauptplatz 1', 'Voitsberg'), $known)['venue'];

            $this->assertNotSame($notOurs->id, $made->id, $name.' is run by somebody else');
            $this->assertFalse($made->isClaimed());
            $this->assertSame([$name, 'Hauptplatz 1', 'Voitsberg', 'at', 'de', 'Europe/Vienna'], [$made->name, $made->address1, $made->city, $made->country_code, $made->language_code, $made->timezone]);
            // The owner follows it, so the event form offers it; nobody owns it.
            $this->assertSame('follower', DB::table('role_user')->where('role_id', $made->id)->where('user_id', $owner->id)->value('level'));
        }

        // Nothing more was attached to, or asked of, the venues somebody else runs.
        $this->assertSame(1, DB::table('event_role')->whereIn('role_id', [$theirs->id, $follower->id])->count());
    }

    public function test_an_unclaimed_venue_already_on_the_schedules_events_is_used_again(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $onOurEvents = $this->unclaimed('Alte Schmiede', ['city' => 'Köflach']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($onOurEvents->id, ['is_accepted' => true]);
        // Unclaimed, the same name, and only on somebody else's events.
        $elsewhere = $this->unclaimed('Alte Schmiede', ['city' => 'Graz']);
        $othersEvent = $this->createEvent($this->createRole($this->createOwner(), 'talent'));
        $othersEvent->roles()->attach($elsewhere->id, ['is_accepted' => true]);

        $feed = $this->feed($talent);
        $known = [];
        $before = Role::count();

        $this->assertSame($onOurEvents->id, $this->resolver()->resolve($feed, $talent, $this->row('Alte Schmiede'), $known)['venue']->id);
        $this->assertSame($onOurEvents->id, $this->resolver()->resolve($feed, $talent, $this->row('Alte Schmiede', '', 'Köflach'), $known)['venue']->id);
        $this->assertSame($before, Role::count());

        // Two halls of one name in two towns are two halls.
        $inGraz = $this->resolver()->resolve($feed, $talent, $this->row('Alte Schmiede', '', 'Graz'), $known)['venue'];
        $this->assertNotContains($inGraz->id, [$onOurEvents->id, $elsewhere->id]);
    }

    public function test_a_place_with_an_address_and_no_name_is_one_venue_however_often_it_comes(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        $feed = $this->feed($talent);
        $known = [];
        $resolver = $this->resolver();

        $first = $resolver->resolve($feed, $talent, $this->row('', 'Hauptplatz 1, 8570 Voitsberg'), $known)['venue'];
        $again = $resolver->resolve($feed, $talent, $this->row('', 'hauptplatz 1,  8570 voitsberg'), $known)['venue'];
        $other = $resolver->resolve($feed, $talent, $this->row('', 'Hauptplatz 2, 8570 Voitsberg'), $known)['venue'];

        $this->assertNull($first->name);
        $this->assertSame('Hauptplatz 1, 8570 Voitsberg', $first->address1);
        $this->assertSame($first->id, $again->id);
        $this->assertNotSame($first->id, $other->id);

        // A hall first met without its town is the same hall when a later row names the town.
        $hall = $resolver->resolve($feed, $talent, $this->row('Stadthalle'), $known)['venue'];
        $this->assertSame($hall->id, $resolver->resolve($feed, $talent, $this->row('Stadthalle', '', 'Voitsberg'), $known)['venue']->id);

        // And on the next run, with nothing remembered, it is found on the schedule's events.
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($first->id, ['is_accepted' => true]);
        $forgotten = [];
        $this->assertSame($first->id, $this->resolver()->resolve($feed, $talent, $this->row('', 'Hauptplatz 1, 8570 Voitsberg'), $forgotten)['venue']->id);
    }

    /** The place at an address is the venue at that address, whatever the venue is called. */
    public function test_an_address_with_no_name_is_the_venue_the_team_runs_there(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $rathaus = $this->createRole($owner, 'venue', ['name' => 'Rathaus', 'address1' => 'Hauptplatz 1']);
        $known = [];
        $before = Role::count();

        $this->assertSame($rathaus->id, $this->resolver()->resolve($this->feed($talent), $talent, $this->row('', 'hauptplatz 1'), $known)['venue']->id);
        $this->assertSame($before, Role::count());
    }

    /** Each new venue is a subdomain and an address to look up. A first read can name fifty. */
    public function test_one_run_makes_only_so_many_venues_and_the_rest_wait(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        $feed = $this->feed($talent);
        $known = [];
        $resolver = $this->resolver();
        $before = Role::count();

        for ($i = 1; $i <= FeedVenueResolver::PER_RUN; $i++) {
            $this->assertFalse($resolver->resolve($feed, $talent, $this->row('Hall '.$i), $known)['deferred']);
        }

        $waiting = $resolver->resolve($feed, $talent, $this->row('One hall too many'), $known);
        $this->assertTrue($waiting['deferred']);
        $this->assertNull($waiting['venue']);
        $this->assertSame($before + FeedVenueResolver::PER_RUN, Role::count());
        $this->assertArrayNotHasKey($waiting['key'], $known);

        // One it already made costs nothing, and the next run has its own share.
        $this->assertFalse($resolver->resolve($feed, $talent, $this->row('Hall 3'), $known)['deferred']);
        $this->assertFalse($this->resolver()->resolve($feed, $talent, $this->row('One hall too many'), $known)['deferred']);
    }

    /**
     * The owner renames the venue the feed made, or merges it into the one they already had. The
     * place has not moved: the feed's next event there goes where the earlier ones are now,
     * instead of making the duplicate again every hour.
     */
    public function test_the_feed_follows_its_venue_through_a_rename_and_a_merge(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $feed = $this->feed($talent);
        $known = [];

        $made = $this->resolver()->resolve($feed, $talent, $this->row('Stadtsaal'), $known)['venue'];
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($made->id, ['is_accepted' => true]);
        EventFeedItem::create([
            'event_feed_id' => $feed->id, 'external_key' => EventFeedItem::keyFor('1'), 'external_id' => '1',
            'event_id' => $event->id, 'state' => EventFeedItem::STATE_IMPORTED, 'imported' => ['venue_id' => $made->id],
        ]);

        $made->forceFill(['name' => 'Stadtsaal Voitsberg (großer Saal)'])->save();
        $this->assertSame($made->id, $this->resolver()->resolve($feed, $talent, $this->row('Stadtsaal'), $known)['venue']->id);

        // Merged into a venue of another name, the way the merge tool leaves things: the events
        // re-pointed, the merged-away venue marked deleted.
        $survivor = $this->createRole($owner, 'venue', ['name' => 'Kulturzentrum']);
        DB::table('event_role')->where('role_id', $made->id)->update(['role_id' => $survivor->id]);
        $made->forceFill(['is_deleted' => true])->save();
        $before = Role::count();

        $followed = $this->resolver()->resolve($feed, $talent, $this->row('Stadtsaal'), $known);

        $this->assertSame($survivor->id, $followed['venue']->id);
        $this->assertSame($before, Role::count(), 'the duplicate was made again');
        $this->assertSame($survivor->id, $known[$followed['key']]);
    }
}
