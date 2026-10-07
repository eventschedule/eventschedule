<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ScheduleActivity;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Activity rail, the door card and the sale marks on the Realtime tab of /analytics
 * (App\Services\ScheduleActivity): what people did on a schedule owner's own schedules.
 *
 * Unlike the traffic beside it, this is the owner's own record, and every row of it has a name
 * one click away. So the file is mostly about three things the rail must never do: print a name,
 * show a record that is somebody else's, or count something that did not happen (an import, a
 * booking read as a request). Each test names the one-line change that turns it red.
 */
class ScheduleActivityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Every person a fixture involves. None of these may reach the page or either poll. */
    private const NOBODY = [
        'Dana Whitlock', 'dana.whitlock@gmail.com', 'Rita Okonkwo', 'rita.okonkwo@gmail.com',
        'Bruno Castellan', 'bruno.castellan@gmail.com', 'Fiona Ashdown', 'fiona.ashdown@gmail.com',
        'Sam Villiers', 'sam.villiers@gmail.com', 'Greg Thackeray', 'greg.thackeray@gmail.com',
        'Wendy Marlow', 'wendy.marlow@gmail.com', 'ivan.petrovic@gmail.com',
        'Carl Jessop', 'carl.jessop@gmail.com', 'A comment only its event should show',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Noon UTC, a Tuesday: 08:00 in New York, the timezone the fixtures' schedules are in.
        Carbon::setTestNow(Carbon::parse('2026-10-06 12:00:00', 'UTC'));
        Cache::flush();
        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        date_default_timezone_set('UTC');

        parent::tearDown();
    }

    /** The service as the tab builds it for $user, narrowed as the page's picker narrows it. */
    private function activity(User $user, ?Role $only = null): ScheduleActivity
    {
        $user = $user->fresh();
        $this->actingAs($user);
        app()->forgetInstance('userRoles');

        $all = $user->manageableRoles();

        return new ScheduleActivity($user, $only ? $all->where('id', $only->id)->values() : $all, 'salt', ! $only && $all->count() > 1);
    }

    private function rail(User $user, ?Role $only = null): array
    {
        return $this->activity($user, $only)->rail();
    }

    private function live(User $user, ?Role $only = null): array
    {
        return $this->activity($user, $only)->live(Carbon::now()->getTimestamp());
    }

    /** An event of the schedule's own, selling tickets. */
    private function show(Role $role, string $name = 'Jazz Night', array $attributes = []): Event
    {
        return $this->createEvent($role, $attributes + [
            'name' => $name,
            'creator_role_id' => $role->id,
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'starts_at' => '2026-10-20 23:00:00',
        ]);
    }

    private function kinds(array $rail, string $list = 'all'): array
    {
        return array_column($rail['lists'][$list] ?? [], 'kind');
    }

    private function tile(array $rail, string $type): ?int
    {
        return collect($rail['stats'])->firstWhere('type', $type)['count'] ?? null;
    }

    /** An appointment booking: an event named after its guest, and the sale that goes with it. */
    private function booking(Role $role, string $guest, array $event = [], array $sale = [], ?bool $accepted = true): Event
    {
        $type = $this->createAppointmentType($role, ['name' => 'Consultation']);
        $booking = $this->createEvent($role, $event + [
            'name' => 'Consultation - '.$guest,
            'appointment_type_id' => $type->id,
            'creator_role_id' => $role->id,
            'starts_at' => '2026-10-08 19:00:00',
        ]);
        $booking->roles()->updateExistingPivot($role->id, ['is_accepted' => $accepted]);
        $this->createSale($booking, $role, $sale + ['name' => $guest, 'email' => Str::slug($guest, '.').'@gmail.com', 'payment_method' => 'cash']);

        return $booking;
    }

    /**
     * One of every kind the rail lists, each made by a named person.
     *
     * @return array{Role, Event}
     */
    private function oneOfEverything(User $owner, ?int $saleId = null): array
    {
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->show($role);
        $ticket = $this->createTicket($event, ['price' => 15]);

        $this->createSale($event, $role, array_filter(['id' => $saleId]) + ['name' => 'Dana Whitlock', 'email' => 'dana.whitlock@gmail.com', 'payment_amount' => 30], $ticket, 2);

        $signups = $this->show($role, 'Open Rehearsal', ['tickets_enabled' => false, 'rsvp_enabled' => true]);
        $this->createSale($signups, $role, ['name' => 'Rita Okonkwo', 'email' => 'rita.okonkwo@gmail.com', 'payment_method' => 'rsvp']);

        $this->booking($role, 'Bruno Castellan');

        // The same people when a second owner's fixture is built: an email is one account.
        $follower = User::firstWhere('email', 'fiona.ashdown@gmail.com')
            ?? User::factory()->create(['name' => 'Fiona Ashdown', 'email' => 'fiona.ashdown@gmail.com']);
        $this->followRole($follower, $role);

        DB::table('role_subscribers')->insert([
            'role_id' => $role->id, 'email' => 'sam.villiers@gmail.com', 'name' => 'Sam Villiers', 'source' => 'form',
            'token' => Str::random(64), 'confirmed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $submitter = User::firstWhere('email', 'greg.thackeray@gmail.com')
            ?? User::factory()->create(['name' => 'Greg Thackeray', 'email' => 'greg.thackeray@gmail.com']);
        $request = $this->createEvent($role, ['name' => 'Open Mic Night', 'user_id' => $submitter->id]);
        $request->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        DB::table('ticket_waitlists')->insert([
            'event_id' => $event->id, 'event_date' => '2026-10-20', 'name' => 'Wendy Marlow', 'email' => 'wendy.marlow@gmail.com',
            'subdomain' => $role->subdomain, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('event_interests')->insert([
            'event_id' => $event->id, 'event_date' => '2026-10-20', 'email' => 'ivan.petrovic@gmail.com', 'source' => 'form',
            'token' => Str::random(64), 'confirmed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('event_comments')->insert([
            'event_id' => $event->id, 'guest_name' => 'Carl Jessop', 'guest_email' => 'carl.jessop@gmail.com',
            'comment' => 'A comment only its event should show', 'is_approved' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('event_photos')->insert([
            'event_id' => $event->id, 'guest_name' => 'Carl Jessop', 'photo_url' => 'photos/carl.jpg', 'is_approved' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('event_videos')->insert([
            'event_id' => $event->id, 'guest_name' => 'Carl Jessop', 'youtube_url' => 'https://youtu.be/abcdefghijk', 'is_approved' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$role, $event];
    }

    /**
     * Every kind is listed once, counted where it has a button, and labelled by what it is.
     * Mutation: drop a kind from rail()'s $kinds, or count followers without subscribers.
     */
    public function test_the_rail_lists_one_row_for_each_thing_that_happened(): void
    {
        $owner = $this->createOwner();
        $this->oneOfEverything($owner);

        $rail = $this->rail($owner);

        $kinds = $this->kinds($rail);
        sort($kinds);
        $this->assertSame(
            ['booking', 'comment', 'follower', 'interest', 'photo', 'registration', 'request', 'sale', 'subscriber', 'video', 'waitlist'],
            $kinds
        );

        $this->assertSame(1, $this->tile($rail, 'sale'));
        $this->assertSame(1, $this->tile($rail, 'registration'));
        $this->assertSame(1, $this->tile($rail, 'booking'));
        $this->assertSame(2, $this->tile($rail, 'follower'), 'A follower and a confirmed newsletter subscriber.');
        $this->assertSame(1, $this->tile($rail, 'request'));
        $this->assertSame(11, $rail['total']);

        $rows = collect($rail['lists']['all'])->keyBy('kind');
        $this->assertSame('Jazz Night', $rows['sale']['title']);
        $this->assertSame('$30', $rows['sale']['amount']);
        $this->assertSame(['tickets', 2], [$rows['sale']['unit'], $rows['sale']['quantity']]);
        $this->assertSame(['guests', 1], [$rows['registration']['unit'], $rows['registration']['quantity']]);
        $this->assertNull($rows['registration']['amount']);
        $this->assertSame('Consultation', $rows['booking']['title']);
        $this->assertSame('Open Mic Night', $rows['request']['title']);
        $this->assertSame('waiting', $rows['request']['note']);
        $this->assertSame('approval', $rows['comment']['note'], 'A comment nobody has approved yet says so.');
        $this->assertNull($rows['photo']['note'], 'An approved photo is not waiting for anything.');
        $this->assertSame('The Vinyl Room', $rows['follower']['schedule'], 'What was followed is always said.');
        $this->assertNull($rows['sale']['schedule'], 'With one schedule, a row does not repeat its name.');

        // The filtered lists are cut from their own kind, and a follower's list holds both.
        $this->assertSame(['sale'], $this->kinds($rail, 'sale'));
        $followers = $this->kinds($rail, 'follower');
        sort($followers);
        $this->assertSame(['follower', 'subscriber'], $followers);
    }

    /**
     * The whole point of the tab: nobody is named, in the page or in either poll, and nothing
     * a row carries could be used to look a person up.
     * Mutation: select `sales.name` in purchaseKind() and print it; key a row by its sale id;
     * label a booking with its event's name.
     */
    public function test_neither_the_page_nor_the_polls_name_anybody(): void
    {
        $owner = $this->createOwner();
        [$role] = $this->oneOfEverything($owner, 987654);

        // A visitor on a booking's own page: its label is the event's name, which is the guest's.
        $booking = Event::whereNotNull('appointment_type_id')->firstOrFail();
        DB::table('realtime_hits')->insert([
            'hit_key' => str_repeat('b', 32), 'visitor_key' => str_repeat('c', 16), 'consented' => true, 'owner_visible' => true,
            'surface' => 'gp', 'path' => '/x', 'role_id' => $role->id, 'event_id' => $booking->id, 'device' => 'desktop', 'hb' => 60,
            'started_at' => now()->utc()->format('Y-m-d H:i:s'), 'last_seen_at' => now()->utc()->format('Y-m-d H:i:s'),
            'engaged_at' => now()->utc()->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($owner);
        $page = $this->get('/analytics?tab=realtime')->assertOk()->getContent();
        $activity = $this->getJson('/analytics/realtime/activity')->assertOk();
        $traffic = $this->getJson('/analytics/realtime/data')->assertOk();

        foreach (['page' => $page, 'activity' => $activity->getContent(), 'traffic' => $traffic->getContent()] as $where => $raw) {
            foreach (self::NOBODY as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $raw, "{$forbidden} reached the {$where}.");
            }
            // The sale's own id, raw: a row is keyed by a hash and links to a list, not to itself.
            $this->assertStringNotContainsString('987654', $raw, "A sale id reached the {$where}.");
        }

        $this->assertStringContainsString('Consultation', $traffic->getContent(), 'The booking page is labelled by what was booked.');

        foreach ($activity->json('lists.all') as $row) {
            $this->assertSame(
                ['key', 'kind', 'title', 'amount', 'unit', 'quantity', 'note', 'when', 'schedule', 'ago', 'url'],
                array_keys($row)
            );
            $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $row['key']);
            if ($row['url'] !== null) {
                $this->assertDoesNotMatchRegularExpression('/[@]|filter=|search=|email=/', $row['url']);
            }
        }

        // The rail is in the first render, not fetched after it: no flash of an empty card.
        $this->assertStringContainsString('Open Mic Night', $page);
        $this->assertStringContainsString('rt-rail', $page);
    }

    /**
     * A booking that needs approval is attached with is_accepted = null, exactly as a submitted
     * event is. It is a booking, once, under its type; the guest is never an "event request".
     * Mutation: drop whereNull('events.appointment_type_id') from requests().
     */
    public function test_a_booking_waiting_for_approval_is_a_booking_and_never_a_request(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Studio North']);
        $this->booking($role, 'Bruno Castellan', [], ['status' => 'unpaid'], null);

        $rail = $this->rail($owner);

        $this->assertSame(['booking'], $this->kinds($rail));
        $this->assertSame(0, $this->tile($rail, 'request'));
        $this->assertSame(1, $this->tile($rail, 'booking'));
        $this->assertSame('Consultation', $rail['lists']['all'][0]['title']);
        $this->assertNull($rail['lists']['all'][0]['amount'], 'Nothing was paid yet.');
        $this->assertStringNotContainsString('Bruno', json_encode($rail));

        // The time that was booked, on the schedule's clock: 19:00 UTC is 3 PM in New York.
        $this->assertSame('Thu, Oct 8, 3:00 PM', $rail['lists']['all'][0]['when']);
    }

    /**
     * A cancelled booking, and one whose only sale was cancelled, did not happen.
     * Mutation: drop the whereExists on the booking's sale, or the is_cancelled check.
     */
    public function test_a_cancelled_booking_is_not_listed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->booking($role, 'Bruno Castellan', ['is_cancelled' => true]);
        $this->booking($role, 'Dana Whitlock', [], ['status' => 'cancelled']);
        $paid = $this->booking($role, 'Rita Okonkwo', ['ticket_currency_code' => 'USD'], ['payment_amount' => 45]);

        $rail = $this->rail($owner);

        $this->assertSame(1, $this->tile($rail, 'booking'));
        $this->assertSame('$45', $rail['lists']['booking'][0]['amount']);
        $this->assertNotNull($paid);
    }

    /**
     * Only the viewer's schedules, and the page's picker narrows every kind.
     * Mutation: read followers without whereIn('role_id'), or ignore $onlyRoleId in forRequest().
     */
    public function test_another_owners_records_never_appear_and_the_picker_narrows_every_kind(): void
    {
        $owner = $this->createOwner();
        [$mine] = $this->oneOfEverything($owner);
        $second = $this->createRole($owner, 'talent', ['name' => 'The Second Act']);
        $secondShow = $this->show($second, 'Second Act Live');
        $this->createSale($secondShow, $second, ['payment_amount' => 12], $this->createTicket($secondShow, ['price' => 12]), 1);

        $stranger = $this->createOwner();
        [$theirs] = $this->oneOfEverything($stranger);
        Role::whereKey($theirs->id)->update(['name' => 'Somebody Elses Room']);

        $all = $this->rail($owner);
        $this->assertSame(2, $this->tile($all, 'sale'));
        $this->assertSame(2, $this->tile($all, 'follower'), 'The stranger has two of their own, and none of them is mine.');
        $this->assertStringNotContainsString('Somebody Elses Room', json_encode($all));
        $this->assertSame('The Second Act', collect($all['lists']['sale'])->firstWhere('title', 'Second Act Live')['schedule'],
            'With several schedules a row says which one.');

        $narrowed = $this->rail($owner, $second);
        $this->assertSame(['sale'], $this->kinds($narrowed));
        $this->assertSame(0, $this->tile($narrowed, 'follower'));
        $this->assertSame(1, $narrowed['total']);

        // Over HTTP: the picker's value is checked, and somebody else's schedule is refused.
        $this->actingAs($owner);
        $this->getJson('/analytics/realtime/activity?schedule='.UrlUtils::encodeId($second->id))
            ->assertOk()
            ->assertJsonPath('schedule', UrlUtils::encodeId($second->id))
            ->assertJsonPath('total', 1);
        $this->getJson('/analytics/realtime/activity?schedule='.UrlUtils::encodeId($theirs->id))->assertForbidden();
        $this->assertNotNull($mine);
    }

    /**
     * Sales follow who owns the event. A curator that lists a venue's event sees neither its
     * sales nor its door; a talent the venue's event was accepted onto does.
     * Mutation: put curators in $acceptingIds; drop `is_accepted = true` from the accepted arm.
     */
    public function test_a_curator_does_not_see_the_sales_of_an_event_it_only_lists(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->show($venue, 'Listed Show', ['starts_at' => '2026-10-06 23:00:00']);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $venue, ['payment_amount' => 20], $ticket, 1);

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, ['name' => 'City Guide']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $talentOwner = $this->createOwner();
        $talent = $this->createRole($talentOwner, 'talent', ['name' => 'The Headliner']);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        // Asked, and has not answered; asked, and said no. Neither is part of the event.
        $pendingOwner = $this->createOwner();
        $pending = $this->createRole($pendingOwner, 'talent', ['name' => 'Not Yet Answered']);
        $event->roles()->attach($pending->id, ['is_accepted' => null]);
        $declinedOwner = $this->createOwner();
        $declined = $this->createRole($declinedOwner, 'venue', ['name' => 'Said No']);
        $event->roles()->attach($declined->id, ['is_accepted' => false]);

        foreach ([$pendingOwner, $declinedOwner] as $outsider) {
            $nothing = $this->rail($outsider);
            $this->assertSame(0, $this->tile($nothing, 'sale'));
            $this->assertSame([], $nothing['door']['events']);
            $this->assertSame(0, array_sum($this->live($outsider)['marks']['sales']));
        }

        $this->assertSame(1, $this->tile($this->rail($venueOwner), 'sale'));
        $this->assertSame(1, array_sum($this->live($venueOwner)['marks']['sales']), 'The mark exists: the zeros around this are not an empty chart for everyone.');
        $this->assertSame(1, $this->tile($this->rail($talentOwner), 'sale'), 'An accepted talent is part of the event.');
        $this->assertCount(1, $this->rail($venueOwner)['door']['events']);

        $seen = $this->rail($curatorOwner);
        $this->assertSame(0, $this->tile($seen, 'sale'));
        $this->assertSame([], $seen['lists']['all']);
        $this->assertSame([], $seen['door']['events']);
        $this->assertSame(0, array_sum($this->live($curatorOwner)['marks']['sales']));
    }

    /**
     * On hosted a team member reaches somebody else's schedule only while its plan includes a
     * team. Event::scopeManagedBy() would still hand them the sales of events they created there
     * ("I made it"); the rail goes by the schedules the tab covers and nothing else.
     * Mutation: scope purchases() with Event::managedBy($this->viewer) instead of ownedEventIds().
     */
    public function test_a_schedule_the_plan_closed_is_left_out_even_for_whoever_created_its_events(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Ed Presents']);
        $member = $this->createOwner();
        $this->followRole($member, $role, 'admin');
        $own = $this->createRole($member, 'talent', ['name' => 'Members Own']);

        $event = $this->show($role, 'Made By The Member', ['user_id' => $member->id]);
        $this->createSale($event, $role, ['payment_amount' => 25], $this->createTicket($event, ['price' => 25]), 1);

        // Positive control: while the plan includes a team, the member sees the sale.
        $this->assertSame(1, $this->tile($this->rail($member), 'sale'));

        Role::whereKey($role->id)->update(['plan_type' => 'free', 'plan_expires' => now()->subYear()->format('Y-m-d')]);

        $closed = $this->rail($member);
        $this->assertSame(0, $this->tile($closed, 'sale'));
        $this->assertStringNotContainsString('Made By The Member', json_encode($closed));
        $this->assertNotNull($own);

        // And the tab says why the schedule is gone, as Sales and Check-in do.
        $this->actingAs($member->fresh());
        app()->forgetInstance('userRoles');
        $this->get('/analytics?tab=realtime')->assertOk()->assertSee('Ed Presents');
    }

    /**
     * What is a sale, a registration, or nothing; and which of them may be a mark on the chart.
     * Mutation: drop the import filter from purchases(); classify by the first row's amount
     * instead of the group's; mark a box office sale; answer `at_last` with 1.
     */
    public function test_what_counts_as_a_sale_a_registration_or_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->show($role);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $parking = $this->createTicket($event, ['price' => 5, 'is_addon' => true, 'type' => 'Parking']);

        // Not sales at all.
        $this->createSale($event, $role, ['payment_method' => 'import', 'payment_amount' => 10], $ticket, 1);
        $this->createSale($event, $role, ['payment_method' => 'import', 'payment_amount' => 0], $ticket, 1);
        $this->createSale($event, $role, ['payment_method' => 'cash', 'payment_amount' => 10, 'status' => 'unpaid'], $ticket, 1);
        $this->createSale($event, $role, ['payment_amount' => 10, 'is_deleted' => true], $ticket, 1);
        $this->createSale($event, $role, ['payment_amount' => 10, 'paid_at' => now()->subHours(30)], $ticket, 1);

        // Sales nobody on a page made: listed and counted, never a mark.
        $this->createSale($event, $role, ['payment_method' => 'box_office', 'payment_amount' => 10], $ticket, 1);
        $this->createSale($event, $role, ['payment_method' => 'cash', 'payment_amount' => 10], $ticket, 1);

        // An ordinary sale, with parking: two tickets, and the add-on is not a third.
        $ordinary = $this->createSale($event, $role, ['payment_amount' => 25], $ticket, 2);
        DB::table('sale_tickets')->insert(['sale_id' => $ordinary->id, 'ticket_id' => $parking->id, 'quantity' => 1, 'seats' => '{}']);

        // A party of three, each a row of its own: one purchase, $30, three tickets.
        $first = $this->createSale($event, $role, ['payment_amount' => 10], $ticket, 1);
        Sale::whereKey($first->id)->update(['group_id' => $first->id]);
        $this->createSale($event, $role, ['payment_amount' => 10, 'group_id' => $first->id], $ticket, 1);
        $this->createSale($event, $role, ['payment_amount' => 10, 'group_id' => $first->id], $ticket, 1);

        // A gift card paid for the first seat and not the guest's: the first row is 0, the
        // purchase is not free.
        $gifted = $this->createSale($event, $role, ['payment_amount' => 0], $ticket, 1);
        Sale::whereKey($gifted->id)->update(['group_id' => $gifted->id]);
        $this->createSale($event, $role, ['payment_amount' => 15, 'group_id' => $gifted->id], $ticket, 1);

        // Tickets that cost nothing are a registration.
        $this->createSale($event, $role, ['payment_amount' => 0], $ticket, 1);

        $rail = $this->rail($owner);
        $this->assertSame(5, $this->tile($rail, 'sale'), 'Box office, cash, the ordinary one, the party and the gift card purchase.');
        $this->assertSame(1, $this->tile($rail, 'registration'));
        $this->assertSame(6, $rail['total']);

        $sales = collect($rail['lists']['sale']);
        $this->assertSame(['$10', '$10', '$15', '$25', '$30'], $sales->pluck('amount')->sort()->values()->all());
        $this->assertSame(2, $sales->firstWhere('amount', '$25')['quantity'], 'Parking is not a ticket.');
        $this->assertSame(3, $sales->firstWhere('amount', '$30')['quantity']);
        $this->assertSame(2, $sales->firstWhere('amount', '$15')['quantity']);

        $marks = $this->live($owner)['marks'];
        $this->assertSame(3, array_sum($marks['sales']), 'The ordinary one, the party and the gift card purchase.');
        $this->assertSame(1, array_sum($marks['registrations']));
        // How the page knows the rail is stale: the newest mark's second and how many marks it
        // holds. All four were paid in this one second, and a fifth would make it five.
        $this->assertSame(Carbon::now()->getTimestamp(), $marks['last']);
        $this->assertSame(4, $marks['at_last']);
        $this->createSale($event, $role, ['payment_amount' => 10, 'paid_at' => now()->subMinutes(3)], $ticket, 1);
        $this->assertSame(4, $this->live($owner)['marks']['at_last'], 'An older sale is not news about the newest second.');
        $this->createSale($event, $role, ['payment_amount' => 10], $ticket, 1);
        $this->assertSame(5, $this->live($owner)['marks']['at_last']);
    }

    /**
     * A basket across two events is a row per event, because a row is titled by its event.
     * Mutation: collapse purchases by order_id.
     */
    public function test_a_basket_across_two_events_is_a_row_for_each(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $jazz = $this->show($role, 'Jazz Night');
        $blues = $this->show($role, 'Blues Night');

        $anchor = $this->createSale($jazz, $role, ['payment_amount' => 20], $this->createTicket($jazz, ['price' => 20]), 1);
        Sale::whereKey($anchor->id)->update(['order_id' => $anchor->id]);
        $this->createSale($blues, $role, ['payment_amount' => 30, 'order_id' => $anchor->id], $this->createTicket($blues, ['price' => 30]), 1);

        $rail = $this->rail($owner);

        $this->assertSame(2, $this->tile($rail, 'sale'));
        $this->assertSame(['Blues Night', 'Jazz Night'], collect($rail['lists']['sale'])->pluck('title')->sort()->values()->all());
    }

    /**
     * A count is of the whole day and a list is its newest twenty, and the two are cut from the
     * same rows. Nor does a busier day cost more reads: the quantities come from two grouped
     * queries however many purchases are listed.
     * Mutation: count the list instead of the day; call Sale::groupTotalQuantity() per row.
     */
    public function test_a_count_and_its_list_agree_past_the_cap_and_a_busy_day_costs_no_more_queries(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->show($role);
        $ticket = $this->createTicket($event, ['price' => 10]);

        $queries = function () use ($owner): int {
            $activity = $this->activity($owner);
            DB::flushQueryLog();
            DB::enableQueryLog();
            $activity->rail();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        foreach (range(1, 3) as $minute) {
            $this->createSale($event, $role, ['payment_amount' => 10, 'paid_at' => now()->subMinutes($minute)], $ticket, 1);
        }
        $quiet = $queries();

        foreach (range(4, 25) as $minute) {
            $this->createSale($event, $role, ['payment_amount' => 10, 'paid_at' => now()->subMinutes($minute)], $ticket, 2);
        }
        foreach (range(1, 3) as $hour) {
            $this->createSale($event, $role, ['payment_amount' => 0, 'paid_at' => now()->subHours($hour)], $ticket, 1);
        }
        $busy = $queries();

        $rail = $this->rail($owner);
        $this->assertSame(25, $this->tile($rail, 'sale'));
        $this->assertSame(3, $this->tile($rail, 'registration'));
        $this->assertSame(28, $rail['total']);
        $this->assertCount(ScheduleActivity::LIST, $rail['lists']['sale']);
        $this->assertCount(ScheduleActivity::LIST, $rail['lists']['all']);
        $this->assertCount(3, $rail['lists']['registration'], 'Three registrations are all listed, though none is among the newest twenty of the day.');

        // Newest first, and the ages are the ages.
        $this->assertSame(60, $rail['lists']['all'][0]['ago']);
        $this->assertSame(range(60, 1200, 60), array_column($rail['lists']['all'], 'ago'));

        // One more read when a full list has to be counted, and never one per row.
        $this->assertLessThanOrEqual($quiet + 2, $busy, "{$busy} queries for 28 purchases against {$quiet} for 3.");
        $this->assertLessThanOrEqual(24, $busy, 'A rail refresh is a fixed handful of reads.');
    }

    /**
     * Checkouts begun in the last half hour that cost something, and how many are paid by now.
     * Mutation: count every sale.checkout row (sign-ups and bookings write the same action);
     * drop the 30-minute window; drop the ownership scope; count a cash order.
     */
    public function test_checkouts_started_and_paid(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->show($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $checkout = function (Sale $sale, string $prefix = '', ?Carbon $at = null) {
            DB::table('audit_logs')->insert([
                'action' => AuditService::SALE_CHECKOUT, 'model_type' => 'Sale', 'model_id' => $sale->id, 'ip_address' => '203.0.113.9',
                'metadata' => $prefix.'event_id:'.$sale->event_id, 'created_at' => $at ?? now(),
            ]);
        };

        $checkout($this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid'], $ticket, 1));
        $checkout($this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid'], $ticket, 1));
        $checkout($this->createSale($event, $role, ['payment_amount' => 20], $ticket, 1));

        // None of these is a checkout somebody may be in the middle of.
        $checkout($this->createSale($event, $role, ['payment_amount' => 0], $ticket, 1));
        $checkout($this->createSale($event, $role, ['payment_method' => 'rsvp']), 'rsvp:');
        $checkout($this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid']), 'appointment:');
        // A cash order is finished when it is placed; nobody is in the middle of it.
        $checkout($this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid', 'payment_method' => 'cash'], $ticket, 1));
        $checkout($this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid'], $ticket, 1), '', now()->subMinutes(31));

        $stranger = $this->createOwner();
        $theirRole = $this->createRole($stranger);
        $theirEvent = $this->show($theirRole, 'Elsewhere');
        $checkout($this->createSale($theirEvent, $theirRole, ['payment_amount' => 20, 'status' => 'unpaid'], $this->createTicket($theirEvent, ['price' => 20]), 1));

        $this->assertSame(['started' => 3, 'paid' => 1], $this->live($owner)['checkouts']);
    }

    /**
     * The traffic poll carries the marks and the checkouts, and never the rail: the rail is the
     * heavier read and has a route of its own.
     * Mutation: merge rail() into trafficPayload().
     */
    public function test_the_traffic_poll_carries_marks_and_no_rail(): void
    {
        $owner = $this->createOwner();
        $this->oneOfEverything($owner);

        $this->actingAs($owner);
        $traffic = $this->getJson('/analytics/realtime/data')->assertOk();

        $this->assertCount(30, $traffic->json('marks.sales'));
        $this->assertSame(1, array_sum($traffic->json('marks.sales')));
        $this->assertSame(1, array_sum($traffic->json('marks.registrations')));
        $this->assertSame(['started' => 0, 'paid' => 0], $traffic->json('checkouts'));
        foreach (['lists', 'stats', 'door', 'total'] as $key) {
            $this->assertArrayNotHasKey($key, $traffic->json());
        }
    }

    /**
     * Where the install does not offer the view, the rail's route is a 404 like the others.
     * Mutation: drop the abort_unless() from RealtimeController::activity().
     */
    public function test_the_rail_is_a_404_where_the_view_is_off(): void
    {
        $owner = $this->createOwner();
        $this->oneOfEverything($owner);
        $this->actingAs($owner);

        $this->getJson('/analytics/realtime/activity')->assertOk();

        Setting::set('realtime_owner_view', '0');
        Cache::flush();

        $this->getJson('/analytics/realtime/activity')->assertNotFound();
        $this->getJson('/analytics/realtime/activity?schedule=anything')->assertNotFound();
    }

    /**
     * These tables are written on the app's clock, the traffic table on UTC. A sale made this
     * minute is in the chart's last minute whatever the app's timezone is.
     * Mutation: read paid_at with RealtimeRows::timestamp(), which assumes UTC.
     */
    public function test_a_sale_lands_in_its_own_minute_whatever_the_apps_timezone(): void
    {
        config(['app.timezone' => 'Asia/Tokyo']);
        date_default_timezone_set('Asia/Tokyo');
        Carbon::setTestNow(Carbon::parse('2026-10-06 21:00:30', 'Asia/Tokyo'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->show($role);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $this->createSale($event, $role, ['payment_amount' => 10], $ticket, 1);
        $this->createSale($event, $role, ['payment_amount' => 10, 'paid_at' => now()->subMinutes(10)], $ticket, 1);

        $this->assertSame('2026-10-06 21:00:30', DB::table('sales')->orderBy('id')->value('paid_at'), 'Stored on the app clock.');

        $marks = $this->live($owner)['marks'];
        $this->assertSame(1, $marks['sales'][29]);
        $this->assertSame(1, $marks['sales'][19]);
        $this->assertSame(2, array_sum($marks['sales']));

        $rail = $this->rail($owner);
        $this->assertSame([0, 600], array_column($rail['lists']['sale'], 'ago'));
    }

    /**
     * The door card's arrivals are the number /checkin shows, counted without loading a name.
     * Mutation: count every seat instead of the scanned ones; forget pass redemptions; count a
     * deleted ticket type's scans.
     */
    public function test_the_door_card_counts_what_checkin_counts(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->show($role, 'Jazz Night', ['starts_at' => '2026-10-06 23:00:00']);
        $ticket = $this->createTicket($event, ['price' => 15, 'quantity' => 150]);
        $pass = $this->createTicket($event, ['price' => 60, 'is_pass' => true, 'type' => 'Season pass', 'quantity' => 0]);
        $gone = $this->createTicket($event, ['price' => 15, 'type' => 'Withdrawn', 'is_deleted' => true]);

        $nowTs = Carbon::now()->getTimestamp();
        $scan = function (Sale $sale, array $seats) {
            DB::table('sale_tickets')->where('sale_id', $sale->id)->update(['seats' => json_encode($seats)]);
        };

        $scan($this->createSale($event, $role, ['payment_amount' => 45, 'name' => 'Dana Whitlock'], $ticket, 3), ['1' => $nowTs - 60, '2' => $nowTs - 2400, '3' => null]);
        $scan($this->createSale($event, $role, ['payment_amount' => 30], $ticket, 2), ['1' => null, '2' => null]);
        $scan($this->createSale($event, $role, ['payment_amount' => 15], $gone, 1), ['1' => $nowTs - 30]);
        $scan($this->createSale($event, $role, ['payment_amount' => 15, 'is_deleted' => true], $ticket, 1), ['1' => $nowTs - 30]);
        $scan($this->createSale($event, $role, ['payment_amount' => 15, 'event_date' => '2026-10-05'], $ticket, 1), ['1' => $nowTs - 30]);

        $holder = $this->createSale($event, $role, ['payment_amount' => 60], $pass, 1);
        DB::table('sale_tickets')->where('sale_id', $holder->id)->update(['pass_usages' => json_encode([
            ['event_id' => $event->id, 'date' => '2026-10-06', 'kind' => 'redemption', 'at' => $nowTs - 120, 'admits' => 2],
            ['event_id' => $event->id, 'date' => '2026-10-13', 'kind' => 'booking', 'at' => $nowTs - 120],
            ['event_id' => $event->id, 'date' => '2026-10-05', 'kind' => 'redemption', 'at' => $nowTs - 90000],
        ])]);

        // What the event's own counter says was sold for tonight. Written last: a ticket row
        // bumps it as it is created, and the fixture means six.
        Ticket::whereKey($ticket->id)->update(['sold' => json_encode(['2026-10-06' => 6])]);

        $this->actingAs($owner);
        $checkin = $this->getJson(route('checkin.stats', ['event_id' => UrlUtils::encodeId($event->id)]))->assertOk();
        $this->assertSame(3, $checkin->json('total_checked_in'), 'Two scanned seats and one pass.');

        $door = $this->rail($owner)['door'];
        $this->assertCount(1, $door['events']);
        $card = $door['events'][0];

        $this->assertSame($checkin->json('total_checked_in'), $card['checked_in']);
        $this->assertSame(2, $card['recent'], 'One seat a minute ago and the pass two minutes ago; the other seat was forty minutes ago.');
        $this->assertSame('Jazz Night', $card['name']);
        $this->assertSame('7:00 PM', $card['time']);
        $this->assertNull($card['day'], 'It is today: the time is enough.');
        $this->assertSame(8, $card['sold'], 'Six tickets for tonight, and the two seats the pass took tonight.');
        $this->assertSame(150, $card['capacity']);
        $this->assertFalse($card['signups']);
        $this->assertStringContainsString('/checkin?event='.UrlUtils::encodeId($event->id), $card['url']);
        $this->assertStringNotContainsString('Dana', json_encode($door));

        // And Check-in opens on the event the card names, not on whichever it would have chosen.
        $other = $this->show($role, 'Another Night', ['starts_at' => '2026-10-06 22:00:00']);
        $this->createSale($other, $role, ['payment_amount' => 10], $this->createTicket($other, ['price' => 10]), 1);
        $this->get($card['url'])->assertOk()->assertViewHas('selectedEventId', UrlUtils::encodeId($event->id));
        $this->get('/checkin?event='.UrlUtils::encodeId($other->id))->assertOk()->assertViewHas('selectedEventId', UrlUtils::encodeId($other->id));
        $this->get('/checkin?event=not-an-event')->assertOk();
    }

    /**
     * "Today" is the wrong key at a door. A show that began last night is still on after
     * midnight, a festival is still on on its second day and a fair on its ninth, and each is
     * counted under the date its tickets were sold for. A show that is over is not at the door,
     * however recently it ended; one with no length stays six hours; one that starts after
     * midnight tonight is tomorrow's.
     * Mutation: list by Event::scheduleToday(); keep every show for six hours after it starts;
     * look back only eight days; drop `$date <= $today`; count arrivals under today's date; take
     * the first four entries instead of the first four cards.
     */
    public function test_the_door_keeps_what_is_on_and_lets_go_of_what_is_over(): void
    {
        // 02:30 in New York on Tuesday the 6th.
        Carbon::setTestNow(Carbon::parse('2026-10-06 06:30:00', 'UTC'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $sold = fn (Event $event, array $by, array $ticket = []) => $this->createTicket($event, $ticket + ['price' => 10, 'sold' => json_encode($by)]);

        // On since September, and first in line: an event whose only ticket type is a pass has no
        // seats to count for a date, so it has no card. It must not take one of the four places.
        $passes = $this->show($role, 'Season Pass Desk', ['starts_at' => '2026-09-20 16:00:00', 'duration' => 720]);
        $this->createTicket($passes, ['price' => 60, 'is_pass' => true, 'type' => 'Season pass', 'quantity' => 0]);

        // A fair that opened nine days ago and runs ten.
        $sold($this->show($role, 'Autumn Fair', ['starts_at' => '2026-09-27 16:00:00', 'duration' => 240]), ['2026-09-27' => 77], ['quantity' => 900]);

        // Sunday 2 PM New York, three days long.
        $sold($this->show($role, 'Harvest Festival', ['starts_at' => '2026-10-04 18:00:00', 'duration' => 72]), ['2026-10-04' => 320], ['quantity' => 500]);

        // Monday 9:30 PM with no length given: at the door for six hours, so until 3:30.
        $sold($this->show($role, 'Open Decks', ['starts_at' => '2026-10-06 01:30:00', 'duration' => 0]), ['2026-10-05' => 15], ['quantity' => 0]);

        // Monday 10 PM, six hours long: on until 4 AM, under Monday's date. Two of three seats
        // sold for Monday have been scanned; a seat sold for another night does not count here.
        $late = $this->show($role, 'Late Show', ['starts_at' => '2026-10-06 02:00:00', 'duration' => 6]);
        $lateTicket = $this->createTicket($late, ['price' => 10, 'quantity' => 80]);
        $monday = $this->createSale($late, $role, ['payment_amount' => 30, 'event_date' => '2026-10-05'], $lateTicket, 3);
        DB::table('sale_tickets')->where('sale_id', $monday->id)->update(['seats' => json_encode(['1' => Carbon::now()->getTimestamp() - 300, '2' => Carbon::now()->getTimestamp() - 200, '3' => null])]);
        $tuesday = $this->createSale($late, $role, ['payment_amount' => 10, 'event_date' => '2026-10-06'], $lateTicket, 1);
        DB::table('sale_tickets')->where('sale_id', $tuesday->id)->update(['seats' => json_encode(['1' => Carbon::now()->getTimestamp() - 300])]);
        Ticket::whereKey($lateTicket->id)->update(['sold' => json_encode(['2026-10-05' => 40, '2026-10-06' => 1])]);

        // Tuesday 1 AM, three hours: today's, and under way.
        $sold($this->show($role, 'After Hours', ['starts_at' => '2026-10-06 05:00:00', 'duration' => 3]), ['2026-10-06' => 8], ['quantity' => 50]);

        // None of these is at the door. Monday noon, long over. Monday 9 PM for two hours: it
        // ended at 11, and a show that has a length is not kept for six hours after it starts.
        // A no-length show from Monday 7 PM: its six hours ran out at 1. Wednesday 1 AM: within
        // a day of now, and tomorrow's. Tomorrow night's.
        $sold($this->show($role, 'Lunch Concert', ['starts_at' => '2026-10-05 16:00:00']), ['2026-10-05' => 12]);
        $sold($this->show($role, 'Early Evening', ['starts_at' => '2026-10-06 01:00:00', 'duration' => 2]), ['2026-10-05' => 30]);
        $sold($this->show($role, 'Sound Check', ['starts_at' => '2026-10-05 23:00:00', 'duration' => 0]), ['2026-10-05' => 4]);
        $sold($this->show($role, 'Wednesday Small Hours', ['starts_at' => '2026-10-07 05:00:00', 'duration' => 3]), ['2026-10-07' => 6]);
        $sold($this->show($role, 'Tomorrow Night', ['starts_at' => '2026-10-07 23:00:00']), ['2026-10-07' => 3]);

        $door = $this->rail($owner)['door'];
        $cards = collect($door['events'])->map(fn ($card) => [$card['name'], $card['day'], $card['time'], $card['sold'], $card['capacity']])->all();

        // The earliest four that have something to show, and one more that is on.
        $this->assertSame([
            ['Autumn Fair', 'Sun, Sep 27', '12:00 PM', 77, 900],
            ['Harvest Festival', 'Sun, Oct 4', '2:00 PM', 320, 500],
            ['Open Decks', 'Mon, Oct 5', '9:30 PM', 15, null],
            ['Late Show', 'Mon, Oct 5', '10:00 PM', 40, 80],
        ], $cards);
        $this->assertSame(1, $door['more'], 'After Hours is on too, and is the fifth.');

        $lateCard = collect($door['events'])->firstWhere('name', 'Late Show');
        $this->assertSame(2, $lateCard['checked_in'], 'Arrivals are counted under the date the show was sold for (two), not under today (one).');
    }

    /**
     * A series is looked up for today and for yesterday, whose late show may still be running,
     * and further back when its occurrences run for days: a weekend market that opened on Sunday
     * is still on in the small hours of Tuesday. A series whose run is over is not at the door
     * (it has no occurrence to find; that the scan also leaves it out is an economy, not a rule
     * this test pins).
     * Mutation: look a series up for today only, or for today and yesterday only; keep a set
     * for six hours after every start.
     */
    public function test_a_nightly_series_is_at_the_door_for_last_nights_show_and_tonights(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-06 06:30:00', 'UTC'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Every night at 10 PM for five hours: last night's runs until 3, and tonight's is today's.
        $nightly = $this->createRecurringEvent($role, [
            'name' => 'House Band', 'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => '2026-09-01 02:00:00', 'duration' => 5,
        ]);
        $this->createTicket($nightly, ['price' => 5, 'quantity' => 60, 'sold' => json_encode(['2026-10-05' => 22, '2026-10-06' => 9])]);

        // Every night at 9 PM for two hours: last night's ended at 11. Six hours after its start
        // would be 3 AM, which is the rule this must not go by.
        $short = $this->createRecurringEvent($role, [
            'name' => 'Early Set', 'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => '2026-09-01 01:00:00', 'duration' => 2,
        ]);
        $this->createTicket($short, ['price' => 5, 'quantity' => 40, 'sold' => json_encode(['2026-10-05' => 11, '2026-10-06' => 2])]);

        // Every Sunday at noon for sixty hours: this week's opened two days ago and runs until
        // midnight tonight.
        $market = $this->createRecurringEvent($role, [
            'name' => 'Weekend Market', 'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => '2026-09-06 16:00:00', 'duration' => 60,
            'days_of_week' => '1000000',
        ]);
        $this->createTicket($market, ['price' => 3, 'quantity' => 300, 'sold' => json_encode(['2026-10-04' => 140])]);

        // A series whose run ended in September.
        $ended = $this->createRecurringEvent($role, [
            'name' => 'Summer Sessions', 'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => '2026-06-01 02:00:00', 'duration' => 5,
            'recurring_end_type' => 'on_date', 'recurring_end_value' => '2026-09-15',
        ]);
        $this->createTicket($ended, ['price' => 5, 'sold' => json_encode(['2026-09-15' => 9])]);

        $door = $this->rail($owner)['door'];
        $cards = collect($door['events'])->map(fn ($card) => [$card['name'], $card['day'], $card['time'], $card['sold'], $card['capacity']])->all();

        $this->assertSame([
            ['Weekend Market', 'Sun, Oct 4', '12:00 PM', 140, 300],
            ['House Band', 'Mon, Oct 5', '10:00 PM', 22, 60],
            ['Early Set', null, '9:00 PM', 2, 40],
            ['House Band', null, '10:00 PM', 9, 60],
        ], $cards);
        $this->assertSame(0, $door['more']);
    }

    /**
     * A sign-up records no scan, and check-in is a paid plan's page. Either way there is no
     * arrival count, and the card says how many are coming and opens Sales.
     * Mutation: count arrivals for a sign-up event; link a free schedule's card to /checkin.
     */
    public function test_a_signup_event_and_a_plan_without_checkin_say_how_many_are_coming(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $signups = $this->show($role, 'Open Rehearsal', [
            'starts_at' => '2026-10-06 23:00:00', 'tickets_enabled' => false, 'rsvp_enabled' => true,
            'rsvp_limit' => 40, 'rsvp_sold' => json_encode(['2026-10-06' => 30]),
        ]);

        $card = $this->rail($owner)['door']['events'][0];
        $this->assertSame([30, 40, true, null, null], [$card['sold'], $card['capacity'], $card['signups'], $card['checked_in'], $card['recent']]);
        $this->assertSame(route('sales'), $card['url']);
        $this->assertNotNull($signups);

        // A free schedule on hosted: it can sell, and check-in is not part of its plan.
        $freeOwner = $this->createOwner();
        $free = $this->createFreeRole($freeOwner);
        $this->assertFalse($free->fresh()->isPro());
        $event = $this->show($free, 'Free Plan Show', ['starts_at' => '2026-10-06 23:00:00']);
        $ticket = $this->createTicket($event, ['price' => 10, 'quantity' => 20]);
        $sale = $this->createSale($event, $free, ['payment_amount' => 10], $ticket, 1);
        DB::table('sale_tickets')->where('sale_id', $sale->id)->update(['seats' => json_encode(['1' => Carbon::now()->getTimestamp()])]);
        Ticket::whereKey($ticket->id)->update(['sold' => json_encode(['2026-10-06' => 4])]);

        $card = $this->rail($freeOwner)['door']['events'][0];
        $this->assertSame([4, 20, false, null], [$card['sold'], $card['capacity'], $card['signups'], $card['checked_in']]);
        $this->assertSame(route('sales'), $card['url']);
    }

    /**
     * An event that takes neither tickets nor sign-ups has no door, and a cancelled one is not on.
     * Mutation: drop the tickets_enabled / rsvp_enabled filter or the is_cancelled one.
     */
    public function test_an_event_without_tickets_and_a_cancelled_one_have_no_door(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->show($role, 'Just A Listing', ['starts_at' => '2026-10-06 23:00:00', 'tickets_enabled' => false]);
        $cancelled = $this->show($role, 'Called Off', ['starts_at' => '2026-10-06 23:00:00', 'is_cancelled' => true]);
        $this->createTicket($cancelled, ['price' => 10, 'sold' => json_encode(['2026-10-06' => 5])]);
        $this->booking($role, 'Bruno Castellan', ['starts_at' => '2026-10-06 23:00:00', 'tickets_enabled' => true]);

        $this->assertSame(['events' => [], 'more' => 0], $this->rail($owner)['door']);
    }

    /**
     * The rail is a day's news. Nothing older than 24 hours is on it, of any kind; a sign-up that
     * was never confirmed did not happen; someone who follows AND subscribed is one person; and
     * what the person looking wrote themselves is not news to them.
     * Mutation: drop the window from followers(), requests(), bookings() or a part of others();
     * read `created_at` for a subscriber or an interest; drop the NOT EXISTS; list the viewer's
     * own comment.
     */
    public function test_only_the_last_day_and_only_what_really_happened(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'The Vinyl Room']);
        $event = $this->show($role);
        $old = now()->subHours(30);

        // Older than a day, one of each kind that is not a purchase.
        $earlier = User::factory()->create(['email' => 'earlier.follower@gmail.com']);
        $earlier->roles()->attach($role->id, ['level' => 'follower', 'created_at' => $old]);
        DB::table('role_subscribers')->insert(['role_id' => $role->id, 'email' => 'old.subscriber@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => $old, 'created_at' => $old, 'updated_at' => $old]);
        $request = $this->createEvent($role, ['name' => 'Asked Last Week', 'user_id' => User::factory()->create()->id]);
        $request->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);
        Event::whereKey($request->id)->update(['created_at' => $old]);
        $booking = $this->booking($role, 'Bruno Castellan');
        Event::whereKey($booking->id)->update(['created_at' => $old]);
        DB::table('ticket_waitlists')->insert(['event_id' => $event->id, 'event_date' => '2026-10-20', 'name' => 'Old', 'email' => 'old.waitlist@gmail.com', 'subdomain' => $role->subdomain, 'created_at' => $old, 'updated_at' => $old]);
        DB::table('event_interests')->insert(['event_id' => $event->id, 'event_date' => '2026-10-20', 'email' => 'old.interest@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => $old, 'created_at' => $old, 'updated_at' => $old]);
        foreach (['event_comments' => ['comment' => 'old'], 'event_photos' => ['photo_url' => 'old.jpg'], 'event_videos' => ['youtube_url' => 'https://youtu.be/oldoldoldol']] as $table => $columns) {
            DB::table($table)->insert($columns + ['event_id' => $event->id, 'is_approved' => true, 'created_at' => $old, 'updated_at' => $old]);
        }

        // Made today and never confirmed: a newsletter sign-up and a ticket-interest sign-up.
        DB::table('role_subscribers')->insert(['role_id' => $role->id, 'email' => 'unconfirmed@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => null, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('event_interests')->insert(['event_id' => $event->id, 'event_date' => '2026-10-20', 'email' => 'unconfirmed@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => null, 'created_at' => now(), 'updated_at' => now()]);

        // The owner's own comment.
        DB::table('event_comments')->insert(['event_id' => $event->id, 'user_id' => $owner->id, 'comment' => 'mine', 'is_approved' => true, 'created_at' => now(), 'updated_at' => now()]);

        $nothing = $this->rail($owner);
        $this->assertSame([], $nothing['lists']['all']);
        $this->assertSame(0, $nothing['total']);
        foreach (['sale', 'registration', 'follower', 'request'] as $tile) {
            $this->assertSame(0, $this->tile($nothing, $tile));
        }
        $this->assertNull($this->tile($nothing, 'booking'), 'No booking in the day: no button for bookings.');

        // Today: someone follows, and then confirms the newsletter with the same address. One
        // person. A second person confirms without an account. And somebody else comments.
        $both = User::factory()->create(['email' => 'follows.and.subscribes@gmail.com']);
        $this->followRole($both, $role);
        DB::table('role_subscribers')->insert(['role_id' => $role->id, 'email' => 'follows.and.subscribes@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('role_subscribers')->insert(['role_id' => $role->id, 'email' => 'only.subscribes@gmail.com', 'source' => 'form', 'token' => Str::random(64), 'confirmed_at' => now(), 'created_at' => $old, 'updated_at' => now()]);
        DB::table('event_comments')->insert(['event_id' => $event->id, 'user_id' => $both->id, 'comment' => 'theirs', 'is_approved' => true, 'created_at' => now(), 'updated_at' => now()]);

        $rail = $this->rail($owner);
        $kinds = $this->kinds($rail);
        sort($kinds);
        $this->assertSame(['comment', 'follower', 'subscriber'], $kinds, 'A subscriber is placed by when they confirmed, not by when they first asked.');
        $this->assertSame(2, $this->tile($rail, 'follower'));
        $this->assertSame(3, $rail['total']);
    }

    /**
     * People who signed up together are one row that says how many they are, and a guest who
     * has cancelled since is no longer one of them.
     * Mutation: answer 1 for every sign-up; count cancelled rows of the party, or their tickets.
     */
    public function test_a_party_that_signed_up_together_is_one_row_with_its_size(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->show($role, 'Open Rehearsal', ['tickets_enabled' => false, 'rsvp_enabled' => true]);

        $first = $this->createSale($event, $role, ['payment_method' => 'rsvp']);
        Sale::whereKey($first->id)->update(['group_id' => $first->id]);
        $this->createSale($event, $role, ['payment_method' => 'rsvp', 'group_id' => $first->id, 'name' => 'Guest Two']);
        $this->createSale($event, $role, ['payment_method' => 'rsvp', 'group_id' => $first->id, 'name' => 'Guest Three']);
        $this->createSale($event, $role, ['payment_method' => 'rsvp', 'group_id' => $first->id, 'name' => 'Guest Four', 'status' => 'cancelled']);

        $rail = $this->rail($owner);

        $this->assertSame(1, $this->tile($rail, 'registration'));
        $this->assertCount(1, $rail['lists']['registration']);
        $this->assertSame(['guests', 3], [$rail['lists']['registration'][0]['unit'], $rail['lists']['registration'][0]['quantity']]);

        // The same for free tickets, each guest a row with a ticket of their own: three were
        // taken, one guest has cancelled, two tickets are held.
        $free = $this->show($role, 'Free Tickets Night');
        $ticket = $this->createTicket($free, ['price' => 0]);
        $lead = $this->createSale($free, $role, ['payment_amount' => 0, 'paid_at' => now()->subMinute()], $ticket, 1);
        Sale::whereKey($lead->id)->update(['group_id' => $lead->id]);
        $this->createSale($free, $role, ['payment_amount' => 0, 'group_id' => $lead->id, 'name' => 'Guest Two'], $ticket, 1);
        $this->createSale($free, $role, ['payment_amount' => 0, 'group_id' => $lead->id, 'name' => 'Guest Three', 'status' => 'cancelled'], $ticket, 1);

        $row = collect($this->rail($owner)['lists']['registration'])->firstWhere('title', 'Free Tickets Night');
        $this->assertSame(['tickets', 2], [$row['unit'], $row['quantity']]);
    }

    /**
     * Every kind is counted for the whole day when its list is full, including the kinds read in
     * one UNION, whose count is a query of its own that nothing else runs.
     * Mutation: answer the list's length for followers, requests, bookings or the union.
     */
    public function test_every_kind_is_counted_past_its_list(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Studio North']);
        $event = $this->show($role);
        $type = $this->createAppointmentType($role, ['name' => 'Consultation']);

        foreach (range(1, 22) as $n) {
            User::factory()->create()->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()->subMinutes($n)]);
            DB::table('event_comments')->insert(['event_id' => $event->id, 'comment' => 'c'.$n, 'is_approved' => true, 'created_at' => now()->subMinutes($n), 'updated_at' => now()]);
        }
        foreach (range(1, 21) as $n) {
            $request = $this->createEvent($role, ['name' => 'Request '.$n, 'user_id' => $owner->id]);
            $request->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

            $booking = $this->createEvent($role, ['name' => 'Consultation - Guest '.$n, 'appointment_type_id' => $type->id, 'creator_role_id' => $role->id]);
            $this->createSale($booking, $role, ['payment_method' => 'cash']);
        }

        $rail = $this->rail($owner);

        $this->assertSame(22, $this->tile($rail, 'follower'));
        $this->assertSame(21, $this->tile($rail, 'request'));
        $this->assertSame(21, $this->tile($rail, 'booking'));
        $this->assertSame(22 + 21 + 21 + 22, $rail['total'], 'The comments are counted too, though they have no button.');
        foreach (['follower', 'request', 'booking', 'all'] as $list) {
            $this->assertCount(ScheduleActivity::LIST, $rail['lists'][$list]);
        }
        $this->assertStringNotContainsString('Guest', json_encode($rail));
    }

    /**
     * With several schedules a row says which one it belongs to, and that is the schedule the
     * event is COVERED through: mine that accepted it, never my curator that merely lists it.
     * The row's link opens under that schedule, so the wrong one is a wrong address too.
     * Mutation: file a row under the first of my schedules that has a pivot row.
     */
    public function test_a_row_is_filed_under_the_schedule_the_event_is_covered_through(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'Somebody Elses Venue']);
        $event = $this->show($venue, 'Shared Bill', ['starts_at' => '2026-10-06 23:00:00']);
        $this->createSale($event, $venue, ['payment_amount' => 20], $this->createTicket($event, ['price' => 20, 'quantity' => 100]), 1);

        // Mine: a curator that lists the event (attached first), and a talent it was accepted onto.
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'City Guide']);
        $talent = $this->createRole($owner, 'talent', ['name' => 'The Headliner']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $rail = $this->rail($owner);

        $this->assertSame('The Headliner', $rail['lists']['sale'][0]['schedule']);
        $this->assertSame('The Headliner', $rail['door']['events'][0]['schedule']);
    }

    /**
     * A failure in the rail is reported and leaves the tab standing; a failure in what rides on
     * the traffic poll leaves the traffic standing.
     * Mutation: let railPayload() or trafficPayload() rethrow.
     */
    public function test_a_failure_here_does_not_take_the_tab_with_it(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);
        $this->actingAs($owner);

        // Two reads fail, as on an install whose table is damaged: one the rail makes, one the
        // traffic poll's add-on makes. No DDL: it would commit the test's transaction.
        DB::listen(function ($query) {
            if (str_contains($query->sql, 'from `ticket_waitlists`') || str_contains($query->sql, 'from `audit_logs`')) {
                throw new \RuntimeException('A table this page reads is unavailable.');
            }
        });

        $this->get('/analytics?tab=realtime')->assertOk();
        $this->getJson('/analytics/realtime/activity')->assertOk()->assertJsonPath('failed', true)->assertJsonPath('total', 0);
        $this->getJson('/analytics/realtime/data')->assertOk()
            ->assertJsonPath('state', 'ok')
            ->assertJsonPath('checkouts', ['started' => 0, 'paid' => 0]);
    }
}
