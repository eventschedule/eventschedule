<?php

namespace Tests\Feature;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The hourly demo reset (app:setup-demo, DemoService::resetDemoData()) deletes the demo's own data
 * and nothing else.
 *
 * It used to select every demo-% schedule whoever owned it, and to delete the tickets and sales of
 * every event ATTACHED to one or to the simpsons curator. Real schedules hold demo- names (a real
 * "Demo Night" was handed demo-night, and an event's unclaimed act is auto-created on the same
 * kind of name), and real events reach the demo two ways: a demo visitor curating one into
 * simpsons, or an event listing a demo act among its members. Each of those lost its schedule, or
 * every ticket and paid sale, within the hour.
 */
class DemoResetTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Every demo schedule has a city, and the Role saving hook geocodes an address whenever a
        // backend key is configured.
        config(['services.google.backend' => null]);
    }

    private function demoService(): DemoService
    {
        return new class extends DemoService
        {
            // Goes to the network for any demo image missing locally.
            protected function downloadDemoImages(): void {}

            // About 20,000 analytics upserts, which nothing here reads.
            protected function seedAnalyticsData(Role $role, array $venues): void {}
        };
    }

    /**
     * The demo as app:setup-demo leaves it before its first hourly reset.
     *
     * @return array{DemoService, User, Role}
     */
    private function seedDemo(): array
    {
        $svc = $this->demoService();
        $demoUser = $svc->getOrCreateDemoUser();
        $curator = $svc->getOrCreateDemoRole($demoUser);
        $svc->populateDemoData($curator, false);

        return [$svc, $demoUser, $curator->fresh()];
    }

    private function reset(DemoService $svc): void
    {
        $svc->resetDemoData(Role::where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->firstOrFail());
    }

    /** An ownerless schedule, the way EventRepo::saveEvent() auto-creates an event's venue or act. */
    private function placeholder(string $subdomain, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->name = ucwords(str_replace('-', ' ', $subdomain));
        $role->type = 'talent';
        $role->timezone = 'America/New_York';

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role->fresh();
    }

    /**
     * An event $creator made, with a ticket, a paid sale and a day of analytics: everything the
     * reset used to delete.
     *
     * @return array{Event, Ticket, Sale}
     */
    private function eventWithSales(Role $creator, string $name, array $saleAttrs = []): array
    {
        $event = $this->createEvent($creator, [
            'name' => $name,
            'creator_role_id' => $creator->id,
            'tickets_enabled' => true,
        ]);
        $ticket = $this->createTicket($event, ['price' => 25]);
        $sale = $this->createSale($event, $creator, $saleAttrs + ['payment_amount' => 25], $ticket);

        AnalyticsEventsDaily::create([
            'event_id' => $event->id,
            'date' => now()->toDateString(),
            'desktop_views' => 7,
        ]);

        return [$event, $ticket, $sale];
    }

    private function assertKeepsItsSales(Event $event, Ticket $ticket, Sale $sale, string $label): void
    {
        $this->assertTrue(Event::whereKey($event->id)->exists(), "{$label}: the event was deleted");
        $this->assertTrue(Ticket::whereKey($ticket->id)->exists(), "{$label}: its ticket was deleted");
        $this->assertTrue(Sale::whereKey($sale->id)->exists(), "{$label}: its paid sale was deleted");
        $this->assertSame(1, SaleTicket::where('sale_id', $sale->id)->count(), "{$label}: the sale lost its tickets");
        $this->assertSame(1, AnalyticsEventsDaily::where('event_id', $event->id)->count(), "{$label}: its analytics were cleared");
    }

    public function test_a_real_demo_prefixed_schedule_survives_with_its_sales_and_analytics(): void
    {
        [$svc] = $this->seedDemo();

        $owner = $this->createOwner();
        $night = $this->createRole($owner, 'venue', ['subdomain' => 'demo-night', 'name' => 'Demo Night']);
        [$event, $ticket, $sale] = $this->eventWithSales($night, 'Demo Night Live');
        AnalyticsDaily::create(['role_id' => $night->id, 'date' => now()->toDateString(), 'desktop_views' => 3]);

        $this->reset($svc);

        $night = Role::find($night->id);
        $this->assertNotNull($night, 'the reset deleted a real schedule for its demo- name');
        $this->assertSame($owner->id, $night->user_id);
        $this->assertSame('demo-night', $night->subdomain);
        $this->assertTrue($night->events()->whereKey($event->id)->exists(), 'the schedule lost its event');
        $this->assertSame(1, AnalyticsDaily::where('role_id', $night->id)->count(), 'its analytics were cleared');
        $this->assertKeepsItsSales($event, $ticket, $sale, 'demo-night');
    }

    /**
     * Ownerless demo- rows are a real event's unclaimed venue or act. The orphan arm must not read
     * a NULL owner as a missing one, even for a placeholder carrying the demo's address.
     */
    public function test_an_ownerless_demo_prefixed_placeholder_is_left_alone(): void
    {
        [$svc] = $this->seedDemo();

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        $crew = $this->placeholder('demo-crew');
        $guest = $this->placeholder('demo-guest', ['email' => DemoService::DEMO_EMAIL]);

        [$event, $ticket, $sale] = $this->eventWithSales($venue, 'Crew Night');
        $crew->events()->attach($event->id, ['is_accepted' => true]);
        $guest->events()->attach($event->id, ['is_accepted' => true]);

        $this->reset($svc);

        $this->assertTrue(Role::whereKey($crew->id)->exists(), 'the reset deleted an ownerless placeholder');
        $this->assertTrue(Role::whereKey($guest->id)->exists(), 'a NULL owner was read as an orphaned demo schedule');
        $this->assertTrue($crew->events()->whereKey($event->id)->exists(), 'the placeholder lost its event');
        $this->assertKeepsItsSales($event, $ticket, $sale, 'a real event with a demo-crew placeholder');
    }

    /** DemoAutoLogin signs every visitor in as the demo user, who can curate any public event into simpsons. */
    public function test_a_real_event_curated_into_the_demo_keeps_its_sales(): void
    {
        [$svc, , $curator] = $this->seedDemo();

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        [$event, $ticket, $sale] = $this->eventWithSales($venue, 'Harbor Jazz');
        $curator->events()->attach($event->id, ['is_accepted' => true]);

        $this->reset($svc);

        $this->assertKeepsItsSales($event, $ticket, $sale, 'a real event curated into simpsons');
        $this->assertFalse($curator->fresh()->events()->whereKey($event->id)->exists(), 'it should only be detached from the demo');
        $this->assertTrue($venue->events()->whereKey($event->id)->exists(), 'its own schedule lost it');
    }

    /** EventRepo::saveEvent() attaches any encoded schedule id in members[], the demo's acts included. */
    public function test_a_real_event_naming_a_demo_act_keeps_its_sales(): void
    {
        [$svc] = $this->seedDemo();

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        [$event, $ticket, $sale] = $this->eventWithSales($venue, 'Clown College Night');
        Role::where('subdomain', 'demo-krusty')->firstOrFail()->events()->attach($event->id, ['is_accepted' => true]);

        $this->reset($svc);

        $this->assertKeepsItsSales($event, $ticket, $sale, 'a real event with demo-krusty among its members');
        $this->assertTrue($venue->events()->whereKey($event->id)->exists(), 'its own schedule lost it');
    }

    /**
     * A demo visitor who buys a ticket to a real event does it as the demo user. That sale is the
     * organizer's record, and deleting it also left the ticket's sold count inflated for good.
     */
    public function test_a_purchase_the_demo_user_made_on_a_real_event_is_kept(): void
    {
        [$svc, $demoUser] = $this->seedDemo();

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        [$event, $ticket, $sale] = $this->eventWithSales($venue, 'Harbor Jazz', ['user_id' => $demoUser->id]);

        $this->reset($svc);

        $this->assertKeepsItsSales($event, $ticket, $sale, 'the demo user\'s purchase on a real event');
    }

    public function test_the_demos_own_data_is_deleted_and_recreated(): void
    {
        [$svc, $demoUser, $curator] = $this->seedDemo();

        $oldRoleIds = Role::where('subdomain', 'like', 'demo-%')->pluck('id', 'subdomain');
        $oldEventIds = Event::whereIn('creator_role_id', $oldRoleIds->values()->push($curator->id))->pluck('id');
        $oldSaleIds = Sale::whereIn('event_id', $oldEventIds)->pluck('id');
        $this->assertCount(16, $oldRoleIds, 'fixture: the demo seeds sixteen demo- schedules');
        $this->assertNotEmpty($oldSaleIds, 'fixture: the demo seeds sales');

        $this->reset($svc);

        $newRoles = Role::where('subdomain', 'like', 'demo-%')->get();
        $this->assertEqualsCanonicalizing($oldRoleIds->keys()->all(), $newRoles->pluck('subdomain')->all());
        $this->assertSame([], $newRoles->pluck('id')->intersect($oldRoleIds->values())->values()->all(), 'a demo schedule was not recreated');
        $this->assertSame([$demoUser->id], $newRoles->pluck('user_id')->unique()->values()->all());

        $this->assertSame(0, Event::whereIn('id', $oldEventIds)->count(), 'an old demo event survived');
        $this->assertSame(0, Sale::whereIn('id', $oldSaleIds)->count(), 'an old demo sale survived');
        $this->assertGreaterThan(0, $curator->fresh()->events()->count(), 'the demo was not repopulated');
        $this->assertGreaterThan(0, Sale::where('user_id', $demoUser->id)->count(), 'the demo user has no purchases');
    }

    /**
     * A restore that bypassed the foreign key leaves a demo schedule whose owner id points at
     * nothing. It is the demo's, so it goes, and the demo gets its name back.
     */
    public function test_an_orphaned_demo_schedule_is_removed(): void
    {
        [$svc, $demoUser] = $this->seedDemo();

        // Take the name off the demo's own copy, then plant the orphan on it.
        Role::where('subdomain', 'demo-shelbyville')->firstOrFail()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            $orphan = $this->placeholder('demo-shelbyville', [
                'type' => 'venue',
                'email' => DemoService::DEMO_EMAIL,
                'user_id' => 987654321,
            ]);
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->reset($svc);

        $this->assertFalse(Role::whereKey($orphan->id)->exists(), 'the orphaned demo schedule survived');
        $this->assertSame($demoUser->id, Role::where('subdomain', 'demo-shelbyville')->value('user_id'),
            'the demo did not get its followed schedule back');
    }

    /**
     * where('user_id', null) compiles to whereNull(), so an owner test written that way against a
     * curator with no owner selects every ownerless demo- placeholder on the install.
     */
    public function test_a_curator_with_no_owner_selects_nothing_ownerless(): void
    {
        $curator = $this->placeholder(DemoService::DEMO_ROLE_SUBDOMAIN, [
            'type' => 'curator',
            'email' => DemoService::DEMO_EMAIL,
        ]);
        $this->assertNull(User::where('email', DemoService::DEMO_EMAIL)->value('id'), 'fixture: no demo user');

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        $crew = $this->placeholder('demo-crew');
        [$event, $ticket, $sale] = $this->eventWithSales($venue, 'Crew Night');
        $crew->events()->attach($event->id, ['is_accepted' => true]);

        $this->demoService()->resetDemoData($curator);

        $this->assertTrue(Role::whereKey($crew->id)->exists(), 'an ownerless placeholder was selected for deletion');
        $this->assertKeepsItsSales($event, $ticket, $sale, 'a real event with a demo-crew placeholder');
    }

    /**
     * A real schedule already holding one of the names the demo seeds. createDemoTalents() always
     * skipped a taken name, but the other lookups matched by subdomain alone: the venue was reused
     * as a demo venue, the act was handed demo events, and a taken followed-schedule name failed
     * the unique index and rolled the whole reset back.
     */
    public function test_the_demo_never_takes_over_a_real_schedule_on_one_of_its_names(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['subdomain' => 'demo-lardlad', 'name' => 'Lard Lad Fan Club']);
        $act = $this->createRole($owner, 'talent', ['subdomain' => 'demo-lisajazz', 'name' => 'Lisa Jazz Tribute']);
        $arena = $this->createRole($owner, 'venue', ['subdomain' => 'demo-capitalcity', 'name' => 'Capital City Fans']);

        $arenaEvents = collect(range(1, 10))->map(fn ($i) => tap($this->createEvent($arena, [
            'name' => 'Fan Night '.$i,
            'creator_role_id' => $arena->id,
        ]), fn ($event) => $this->createTicket($event)));

        [$svc, $demoUser] = $this->seedDemo();
        $this->reset($svc);

        foreach ([$venue, $act, $arena] as $real) {
            $real = $real->fresh();

            $this->assertSame($owner->id, $real->user_id, "{$real->subdomain} changed hands");
            $this->assertSame(
                $real->subdomain === 'demo-capitalcity' ? $arenaEvents->pluck('id')->sort()->values()->all() : [],
                $real->events()->pluck('events.id')->sort()->values()->all(),
                "{$real->subdomain} was handed demo events"
            );
        }

        $this->assertSame(0, Sale::whereIn('event_id', $arenaEvents->pluck('id'))->count(), 'the demo user bought tickets on a real schedule');
        $this->assertSame(0, Event::where('creator_role_id', $venue->id)->count(), 'demo events were created on a real venue');
        $this->assertTrue(Role::where('subdomain', 'demo-shelbyville')->where('user_id', $demoUser->id)->exists(),
            'the reset did not complete');
    }

    /** With no demo venue left to reuse, createEvents() has nothing to put an event on. */
    public function test_the_demo_still_seeds_when_every_venue_name_is_taken(): void
    {
        $owner = $this->createOwner();
        $names = ['demo-moestavern', 'demo-bowlarama', 'demo-aztectheater', 'demo-amphitheater', 'demo-lardlad', 'demo-communitycenter'];

        foreach ($names as $name) {
            $this->createRole($owner, 'venue', ['subdomain' => $name, 'name' => 'Fans of '.$name]);
        }

        [$svc, $demoUser] = $this->seedDemo();
        $this->reset($svc);

        $this->assertSame(0, Event::whereIn('creator_role_id', Role::whereIn('subdomain', $names)->pluck('id'))->count());
        $this->assertTrue(Role::where('subdomain', 'demo-shelbyville')->where('user_id', $demoUser->id)->exists(),
            'the reset did not complete');
    }

    /**
     * app:setup-demo repopulates instead of resetting when simpsons has no events left, and then
     * the demo's followed schedules already exist, with whatever real events have been attached
     * to them since. The demo user's made-up purchases belong on the demo's own events only.
     */
    public function test_a_repopulate_buys_tickets_only_on_the_demos_own_events(): void
    {
        [$svc, , $curator] = $this->seedDemo();

        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbor Hall']);
        $arena = Role::where('subdomain', 'demo-capitalcity')->firstOrFail();

        // Ten, because each event is bought for at random, seven times in ten.
        $realEvents = collect(range(1, 10))->map(function ($i) use ($venue, $arena) {
            $event = $this->createEvent($venue, ['name' => 'Harbor Night '.$i, 'creator_role_id' => $venue->id]);
            $this->createTicket($event);
            $arena->events()->attach($event->id, ['is_accepted' => true]);

            return $event;
        });

        $svc->populateDemoData($curator->fresh(), false);

        $this->assertSame(0, Sale::whereIn('event_id', $realEvents->pluck('id'))->count(),
            'the demo user bought tickets on a real event attached to a demo schedule');
    }
}
