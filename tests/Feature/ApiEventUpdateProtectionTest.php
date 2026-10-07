<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An update through the API changes what the request names, and nothing else.
 *
 * Api\ApiEventController::update() hands EventRepo::saveEvent() a request built from an allow-list,
 * and saveEvent() was written for the event form, which posts every section on every save: a
 * section that is absent means "the person emptied it". So everything the API did not carry over
 * was removed by a request that never mentioned it. Until 2026-10 a PUT holding nothing but a new
 * name deleted the event's promo codes, cleared Max Per Order and every pass setting on its ticket
 * types, cleared a recurring event's added and skipped dates, and took the event off its venue,
 * its other performers and the schedules it was listed on.
 *
 * The rows it returned could not be sent back either. Ticket types, add-ons and agenda parts come
 * back with ENCODED ids, saveEvent() looks a row up by its raw id, and a row whose id matched
 * nothing was neither updated nor created: reading an event and saving its tickets retired every
 * ticket type it had.
 *
 * And DELETE went straight to $event->delete(), which cascades to the event's sales. The admin
 * portal refuses that delete (EventController::delete()).
 */
class ApiEventUpdateProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: User, 1: Role, 2: Event, 3: array} */
    private function ticketedEvent(array $attrs = []): array
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent, $attrs + [
            'creator_role_id' => $talent->id,
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
        ]);

        return [$owner, $talent, $event, $this->keyFor($owner)];
    }

    private function rename(Event $event, array $headers, array $body = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), $body + ['name' => 'Renamed over the API'], $headers);
    }

    public function test_a_rename_keeps_the_events_promo_codes(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['price' => 10]);
        $promo = PromoCode::create([
            'event_id' => $event->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10,
            'max_uses' => 25, 'times_used' => 3, 'expires_at' => '2027-03-01 00:00:00', 'is_active' => true,
            'ticket_ids' => [$general->id],
        ]);
        $off = PromoCode::create(['event_id' => $event->id, 'code' => 'OLD', 'type' => 'fixed', 'value' => 5, 'is_active' => false]);

        $this->rename($event, $headers)->assertOk();

        $this->assertSame('Renamed over the API', $event->fresh()->name);
        $kept = PromoCode::find($promo->id);
        $this->assertNotNull($kept, 'a request that named no promo code deleted one');
        $this->assertSame('SAVE10', $kept->code);
        $this->assertSame(25, (int) $kept->max_uses);
        $this->assertSame(3, (int) $kept->times_used);
        $this->assertSame('2027-03-01 00:00:00', $kept->expires_at->format('Y-m-d H:i:s'));
        $this->assertTrue($kept->is_active);
        $this->assertEquals([$general->id], $kept->ticket_ids);
        $this->assertFalse(PromoCode::find($off->id)->is_active, 'a switched-off code stays off');
    }

    public function test_a_rename_keeps_what_the_api_cannot_say_about_a_ticket_type(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent();
        $group = $this->createGroup($talent);
        $fields = ['field1' => ['name' => 'T-shirt size', 'type' => 'dropdown', 'options' => 'S,M,L', 'required' => true, 'index' => 1]];
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'max_per_order' => 4, 'custom_fields' => $fields]);
        $pass = $this->createTicket($event, [
            'type' => 'Season pass', 'price' => 80, 'is_pass' => true, 'max_per_order' => 1,
            'pass_usage_type' => 'total', 'pass_max_uses' => 10, 'pass_valid_days' => 90,
            'pass_scope' => 'sub_schedule', 'pass_scope_group_id' => $group->id,
            'pass_allow_booking' => true, 'pass_seats_per_occurrence' => 5,
            'pass_cancel_cutoff_hours' => 0, 'pass_late_cancel_policy' => 'block', 'pass_admits_per_event' => 2,
        ]);

        // With custom fields on a ticket type this used to be a 500: the row was carried over as
        // an array and saveEvent() json_decode()s what the form sends as a string.
        $this->rename($event, $headers)->assertOk();

        $general = Ticket::find($general->id);
        $this->assertFalse((bool) $general->is_deleted);
        $this->assertSame(4, (int) $general->max_per_order, 'Max Per Order was cleared by a request that did not mention tickets');
        $this->assertEquals($fields, $general->custom_fields);

        $pass = Ticket::find($pass->id);
        $this->assertTrue($pass->is_pass, 'a pass became an ordinary ticket');
        $this->assertSame('total', $pass->pass_usage_type);
        $this->assertSame(10, $pass->pass_max_uses);
        $this->assertSame(90, $pass->pass_valid_days);
        $this->assertSame('sub_schedule', $pass->pass_scope);
        $this->assertSame($group->id, $pass->pass_scope_group_id);
        $this->assertTrue($pass->pass_allow_booking);
        $this->assertSame(5, $pass->pass_seats_per_occurrence);
        $this->assertSame(0, $pass->pass_cancel_cutoff_hours);
        $this->assertSame('block', $pass->pass_late_cancel_policy);
        $this->assertSame(2, $pass->pass_admits_per_event);
    }

    public function test_a_pass_that_covers_named_events_still_covers_them(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent();
        $covered = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $pass = $this->createTicket($event, [
            'type' => 'Three shows', 'price' => 40, 'is_pass' => true, 'max_per_order' => 1,
            'pass_usage_type' => 'total', 'pass_max_uses' => 3,
            'pass_scope' => 'specific_events', 'pass_event_ids' => [$covered->id],
            'pass_admits_per_event' => 1,
        ]);

        $this->rename($event, $headers)->assertOk();

        $pass = Ticket::find($pass->id);
        $this->assertSame('specific_events', $pass->pass_scope);
        $this->assertEquals([$covered->id], $pass->pass_event_ids);
    }

    public function test_a_rename_keeps_an_add_ons_limit(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $this->createTicket($event, ['price' => 10]);
        $addon = $this->createTicket($event, ['type' => 'Parking', 'price' => 5, 'is_addon' => true, 'max_per_order' => 2, 'url' => 'https://parking.example.org']);

        $this->rename($event, $headers)->assertOk();

        $addon = Ticket::find($addon->id);
        $this->assertFalse((bool) $addon->is_deleted);
        $this->assertSame(2, (int) $addon->max_per_order);
        $this->assertSame('https://parking.example.org', $addon->url);
    }

    public function test_a_rename_keeps_a_recurring_events_added_and_skipped_dates(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createRecurringEvent($talent, ['creator_role_id' => $talent->id]);
        $event->recurring_include_dates = ['2027-01-05'];
        $event->recurring_exclude_dates = ['2027-01-12', '2027-01-19'];
        $event->save();

        $this->rename($event, $this->keyFor($owner))->assertOk();

        $fresh = Event::find($event->id);
        $this->assertSame('weekly', $fresh->recurring_frequency);
        $this->assertEquals(['2027-01-05'], $fresh->recurring_include_dates);
        $this->assertEquals(['2027-01-12', '2027-01-19'], $fresh->recurring_exclude_dates);

        // Changing the rule is not a reason to lose them either: the API has no field for them.
        $this->rename($event, $this->keyFor($owner), ['schedule_type' => 'recurring', 'recurring_frequency' => 'daily'])->assertOk();
        $this->assertEquals(['2027-01-05'], Event::find($event->id)->recurring_include_dates);

        // An event that stops repeating has no dates to add or skip.
        $this->rename($event, $this->keyFor($owner), ['schedule_type' => 'single'])->assertOk();
        $this->assertNull(Event::find($event->id)->recurring_include_dates);
    }

    public function test_a_rename_keeps_the_venue_the_other_performers_and_the_schedules_it_is_listed_on(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $guest = $this->createRole($owner, 'talent', ['name' => 'Guest Act']);
        $curator = $this->createCurator($owner, ['name' => 'City Listings']);
        $declined = $this->createCurator($owner, ['name' => 'Said No']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        foreach ([$venue, $guest, $curator] as $role) {
            $event->roles()->attach($role->id, ['is_accepted' => true]);
        }
        $event->roles()->attach($declined->id, ['is_accepted' => false]);

        $this->rename($event, $this->keyFor($owner))->assertOk();

        $attached = DB::table('event_role')->where('event_id', $event->id)->pluck('is_accepted', 'role_id');
        foreach (['its own schedule' => $talent, 'its venue' => $venue, 'another performer' => $guest, 'a schedule it is listed on' => $curator] as $what => $role) {
            $this->assertTrue($attached->has($role->id), "a request that named nobody took the event off {$what}");
            $this->assertSame(1, (int) $attached[$role->id], "{$what} is still accepted");
        }
        // The form starts a declined schedule unticked, and the API is not a way round that.
        $this->assertNotSame(1, (int) ($attached[$declined->id] ?? 0), 'a schedule that took the event off was put back on');
    }

    /**
     * Kept is not the same as accepted. Every schedule carried over goes through saveEvent()'s
     * accept loop, which answers for any schedule the caller manages, any schedule nobody has
     * claimed and any that takes requests without approval: the first version of this fix put an
     * event back on a venue and a performer that had turned it down, by renaming it.
     */
    public function test_a_rename_does_not_answer_for_a_schedule_it_did_not_name(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'Said No Hall']);
        $guest = $this->createRole($owner, 'talent', ['name' => 'Said No Act']);
        $pending = $this->createCurator($owner, ['name' => 'Not Answered Yet']);
        $declined = $this->createCurator($owner, ['name' => 'Took It Off']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => false]);
        $event->roles()->attach($guest->id, ['is_accepted' => false]);
        $event->roles()->attach($pending->id, ['is_accepted' => null]);
        $event->roles()->attach($declined->id, ['is_accepted' => false]);

        $this->rename($event, $this->keyFor($owner))->assertOk();

        $rows = DB::table('event_role')->where('event_id', $event->id)->pluck('is_accepted', 'role_id');
        foreach (['the venue that said no' => [$venue, 0], 'the performer that said no' => [$guest, 0], 'the schedule that had not answered' => [$pending, null], 'the schedule that took it off' => [$declined, 0]] as $what => [$role, $was]) {
            $this->assertTrue($rows->has($role->id), "{$what} is no longer on the event");
            $this->assertSame($was, $rows[$role->id] === null ? null : (int) $rows[$role->id], "{$what} was answered for");
        }
    }

    public function test_a_rename_does_not_accept_for_a_venue_the_caller_only_follows(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $theirs = $this->createRole($this->createOwner(), 'venue', ['name' => 'Their Hall', 'accept_requests' => true, 'require_approval' => false]);
        $this->followRole($owner, $theirs);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($theirs->id, ['is_accepted' => false]);

        $this->rename($event, $this->keyFor($owner))->assertOk();

        $this->assertSame(0, (int) DB::table('event_role')->where('event_id', $event->id)->where('role_id', $theirs->id)->value('is_accepted'));
    }

    public function test_on_a_talent_schedule_the_other_performers_stay_when_the_list_is_sent_back(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $guest = $this->createRole($owner, 'talent', ['name' => 'Guest Act']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($guest->id, ['is_accepted' => true]);
        $headers = $this->keyFor($owner);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        // The schedule itself is the performer there, and the list a request sends is set aside.
        // It must not also count as "the caller replaced the performers".
        $members = $this->getJson($url, $headers)->assertOk()->json('data.members');
        $this->putJson($url, ['members' => $members], $headers)->assertOk();

        $this->assertTrue(DB::table('event_role')->where('event_id', $event->id)->where('role_id', $guest->id)->exists());
    }

    public function test_naming_another_venue_moves_the_event_off_a_venue_nobody_claimed(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $placeholder = fn (string $name, string $address) => tap(new Role, function (Role $venue) use ($name, $address) {
            $venue->forceFill([
                'type' => 'venue', 'name' => $name, 'address1' => $address,
                'subdomain' => 'venue'.strtolower(Str::random(10)), 'timezone' => 'America/New_York',
            ])->save();
        });
        // A venue typed into an event form: nobody owns it, so it is not one the caller "can see".
        $old = $placeholder('The Old Hall', '1 Old Road');
        $new = $placeholder('The Barn', '2 Farm Lane');
        $this->followRole($owner, $new);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($old->id, ['is_accepted' => true]);
        $headers = $this->keyFor($owner);
        $venues = fn () => Event::find($event->id)->roles()->where('roles.type', 'venue')->pluck('roles.id')->all();

        $this->rename($event, $headers, ['venue_name' => 'The Barn', 'venue_address1' => '2 Farm Lane'])->assertOk();
        $this->assertSame([$new->id], $venues(), 'the event is at two venues, or still at the old one');

        $this->rename($event, $headers)->assertOk();
        $this->assertSame([$new->id], $venues(), 'the next update moved it back');
    }

    public function test_a_pass_keeps_its_named_events_whichever_schedule_the_update_runs_through(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        // Made on the venue with the talent as its performer, the talent's row first, as
        // POST /api/events/{venue} with `members` leaves it.
        $event = $this->createEvent($talent, ['creator_role_id' => $venue->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $covered = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $pass = $this->createTicket($event, [
            'type' => 'Three shows', 'price' => 40, 'is_pass' => true, 'max_per_order' => 1,
            'pass_usage_type' => 'total', 'pass_max_uses' => 3,
            'pass_scope' => 'specific_events', 'pass_event_ids' => [$covered->id], 'pass_admits_per_event' => 1,
        ]);
        $headers = $this->keyFor($owner);

        $this->rename($event, $headers)->assertOk();
        $this->assertEquals([$covered->id], Ticket::find($pass->id)->pass_event_ids, 'the pass covers nothing now');
        $this->rename($event, $headers)->assertOk();

        // And when the only schedule the caller runs is NOT the one the covered events are on.
        $helper = $this->createOwner();
        $this->followRole($helper, $talent, 'admin');
        $this->rename($event, $this->keyFor($helper))->assertOk();
        $this->assertEquals([$covered->id], Ticket::find($pass->id)->pass_event_ids, 'an update through another schedule emptied the pass');
    }

    public function test_the_events_a_pass_covers_can_still_be_changed(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent();
        $first = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $second = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $pass = $this->createTicket($event, [
            'type' => 'Three shows', 'price' => 40, 'is_pass' => true, 'max_per_order' => 1,
            'pass_usage_type' => 'total', 'pass_max_uses' => 3,
            'pass_scope' => 'specific_events', 'pass_event_ids' => [$first->id], 'pass_admits_per_event' => 1,
        ]);

        // Putting back what a request did not send must not undo what one did.
        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['tickets' => [[
            'id' => UrlUtils::encodeId($pass->id), 'type' => 'Three shows', 'pass_event_ids' => [UrlUtils::encodeId($second->id)],
        ]]], $headers)->assertOk();

        $this->assertEquals([$second->id], Ticket::find($pass->id)->pass_event_ids);
    }

    public function test_a_null_venue_names_nothing(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        // What a client whose serializer writes every field sends for one it did not set.
        $this->rename($event, $this->keyFor($owner), ['venue_id' => null, 'venue_name' => null, 'venue_address1' => null])->assertOk();

        $this->assertTrue(DB::table('event_role')->where('event_id', $event->id)->where('role_id', $venue->id)->exists(), 'a null venue took the event off the one it had');
    }

    public function test_a_series_that_sells_a_pass_counted_per_date_is_not_made_single(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createRecurringEvent($talent, ['creator_role_id' => $talent->id, 'tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $pass = $this->createTicket($event, [
            'type' => 'Season pass', 'price' => 80, 'is_pass' => true, 'max_per_order' => 1,
            'pass_usage_type' => 'per_occurrence', 'pass_scope' => 'this_event', 'pass_admits_per_event' => 1,
        ]);
        $headers = $this->keyFor($owner);

        // saveEvent() turns that pass into one with a number of visits and no number, which its
        // own validation then refuses on every later save: one call locked the event out of the API.
        $response = $this->rename($event, $headers, ['schedule_type' => 'single']);

        $response->assertStatus(422);
        $this->assertArrayHasKey('tickets.0.pass_usage_type', $response->json('errors'));
        $this->assertSame('weekly', Event::find($event->id)->recurring_frequency);
        $this->assertSame('per_occurrence', Ticket::find($pass->id)->pass_usage_type);
        $this->rename($event, $headers)->assertOk();
    }

    public function test_a_ticket_type_with_no_name_can_be_sent_back(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $only = $this->createTicket($event, ['price' => 10, 'quantity' => 50]);
        DB::table('tickets')->where('id', $only->id)->update(['type' => null]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        // An event with one ticket type usually has no name on it: the form only asks once there are two.
        $rows = $this->getJson($url, $headers)->assertOk()->json('data.tickets');
        $this->assertNull($rows[0]['type']);
        $rows[0]['price'] = 12;

        $this->putJson($url, ['tickets' => $rows], $headers)->assertOk();

        $this->assertEquals(12, (float) Ticket::find($only->id)->price);
    }

    public function test_the_whole_event_can_be_read_and_sent_back(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent(['duration' => 3, 'description' => 'Doors at seven.']);
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50, 'max_per_order' => 4]);
        $group = $this->createGroup($talent);
        DB::table('event_role')->where('event_id', $event->id)->where('role_id', $talent->id)->update(['group_id' => $group->id]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);
        $before = $this->getJson($url, $headers)->assertOk()->json('data');

        // What a client that reads, changes one field and writes the object back does. The event
        // has no venue, and its null venue_name / venue_address1 used to be looked up as a venue.
        $response = $this->putJson($url, ['name' => 'Renamed over the API'] + $before, $headers);

        $response->assertOk();
        $after = $response->json('data');
        unset($before['name'], $after['name'], $before['updated_at'], $after['updated_at'], $before['url'], $after['url']);
        $this->assertEquals($before, $after);
        $this->assertSame(4, (int) Ticket::find($general->id)->max_per_order);
        $this->assertSame($group->id, (int) DB::table('event_role')->where('event_id', $event->id)->where('role_id', $talent->id)->value('group_id'));
    }

    public function test_naming_the_venue_it_already_has_keeps_it(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->rename($event, $this->keyFor($owner), ['venue_id' => UrlUtils::encodeId($venue->id)])->assertOk();

        $this->assertTrue(DB::table('event_role')->where('event_id', $event->id)->where('role_id', $venue->id)->exists());
    }

    public function test_naming_another_venue_moves_the_event(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $old = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $new = $this->createRole($owner, 'venue', ['name' => 'The Barn']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($old->id, ['is_accepted' => true]);

        $this->rename($event, $this->keyFor($owner), ['venue_id' => UrlUtils::encodeId($new->id)])->assertOk();

        $venues = Event::find($event->id)->roles()->where('roles.type', 'venue')->pluck('roles.id')->all();
        $this->assertSame([$new->id], $venues);
    }

    public function test_naming_the_performers_replaces_the_ones_the_caller_can_see(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'The Hall']);
        $first = $this->createRole($owner, 'talent', ['name' => 'First Act']);
        $second = $this->createRole($owner, 'talent', ['name' => 'Second Act']);
        $stranger = $this->createRole($this->createOwner(), 'talent', ['name' => 'Somebody Else']);
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        foreach ([$first, $second, $stranger] as $role) {
            $event->roles()->attach($role->id, ['is_accepted' => true]);
        }

        $this->rename($event, $this->keyFor($owner), ['members' => [['name' => 'First Act']]])->assertOk();

        $talents = Event::find($event->id)->roles()->where('roles.type', 'talent')->pluck('roles.id')->all();
        $this->assertContains($first->id, $talents);
        $this->assertNotContains($second->id, $talents, 'a performer left out of the list stayed on the event');
        $this->assertContains($stranger->id, $talents, 'a performer outside the account is not the caller\'s to remove');
    }

    public function test_a_new_event_takes_rows_copied_from_a_read_as_new_rows(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50]);
        EventPart::create(['event_id' => $event->id, 'name' => 'Doors', 'start_time' => '19:00', 'sort_order' => 0]);
        $read = $this->getJson('/api/events/'.UrlUtils::encodeId($event->id), $headers)->assertOk()->json('data');

        $response = $this->postJson('/api/events/'.$talent->subdomain, [
            'name' => 'Second night',
            'starts_at' => now()->addDays(14)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'tickets' => $read['tickets'],
            'event_parts' => $read['event_parts'],
        ], $headers);

        $response->assertCreated();
        $copy = Event::where('name', 'Second night')->firstOrFail();
        $this->assertSame(['General'], Ticket::where('event_id', $copy->id)->where('is_deleted', false)->pluck('type')->all(), 'a row sent with another event\'s id was left out of the new event');
        $this->assertSame(['Doors'], EventPart::where('event_id', $copy->id)->pluck('name')->all());
        $this->assertFalse((bool) Ticket::find($general->id)->is_deleted, 'the event the rows were read from is not touched');
        $this->assertSame(1, EventPart::where('event_id', $event->id)->count());
    }

    public function test_ticket_types_are_matched_by_the_ids_the_api_returned(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50, 'description' => 'Standing', 'max_per_order' => 4]);
        $vip = $this->createTicket($event, ['type' => 'VIP', 'price' => 30, 'quantity' => 10]);
        DB::table('tickets')->where('id', $general->id)->update(['sold' => json_encode(['2026-12-01' => 7])]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        $rows = $this->getJson($url, $headers)->assertOk()->json('data.tickets');
        $this->assertCount(2, $rows);
        foreach ($rows as $i => $row) {
            if ($row['type'] === 'General') {
                $rows[$i]['price'] = 12;
            }
        }

        $this->putJson($url, ['tickets' => $rows], $headers)->assertOk();

        $this->assertSame(2, Ticket::where('event_id', $event->id)->where('is_deleted', false)->count(), 'the rows that came back were not the rows that were sent');
        $this->assertSame(2, Ticket::where('event_id', $event->id)->count(), 'a ticket type was made anew instead of being updated');
        $general = Ticket::find($general->id);
        $this->assertEquals(12, (float) $general->price);
        $this->assertSame(4, (int) $general->max_per_order);
        $this->assertSame('{"2026-12-01":7}', str_replace(' ', '', DB::table('tickets')->where('id', $general->id)->value('sold')), 'the sold count belongs to the row and stays with it');
        $this->assertEquals(30, (float) Ticket::find($vip->id)->price);
    }

    public function test_a_ticket_row_keeps_what_it_did_not_send(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50, 'description' => 'Standing']);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'tickets' => [['id' => UrlUtils::encodeId($general->id), 'type' => 'General admission']],
        ], $headers)->assertOk();

        $general = Ticket::find($general->id);
        $this->assertSame('General admission', $general->type);
        $this->assertEquals(10, (float) $general->price);
        $this->assertSame(50, (int) $general->quantity, 'a row that did not name a quantity became unlimited');
        $this->assertSame('Standing', $general->description);

        // Sent empty is still cleared.
        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'tickets' => [['id' => UrlUtils::encodeId($general->id), 'type' => 'General admission', 'description' => null]],
        ], $headers)->assertOk();
        $this->assertNull(Ticket::find($general->id)->description);
    }

    public function test_a_row_without_an_id_is_new_and_a_row_left_out_is_retired(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10]);
        $vip = $this->createTicket($event, ['type' => 'VIP', 'price' => 30]);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), [
            'tickets' => [
                ['id' => UrlUtils::encodeId($general->id), 'type' => 'General', 'price' => 10],
                ['type' => 'Balcony', 'price' => 20, 'quantity' => 40],
            ],
        ], $headers)->assertOk();

        $this->assertFalse((bool) Ticket::find($general->id)->is_deleted);
        $this->assertTrue((bool) Ticket::find($vip->id)->is_deleted, 'a ticket type left out of the list is retired');
        $this->assertTrue(Ticket::where('event_id', $event->id)->where('type', 'Balcony')->where('is_deleted', false)->exists());
    }

    public function test_an_id_that_is_not_one_of_this_events_is_refused_and_nothing_changes(): void
    {
        [$owner, $talent, $event, $headers] = $this->ticketedEvent();
        $general = $this->createTicket($event, ['type' => 'General', 'price' => 10]);
        $other = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'tickets_enabled' => true]);
        $foreign = $this->createTicket($other, ['type' => 'Elsewhere', 'price' => 99]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        foreach ([UrlUtils::encodeId($foreign->id), 'not-an-id', (string) $general->id] as $id) {
            $response = $this->putJson($url, ['name' => 'Should not land', 'tickets' => [['id' => $id, 'type' => 'General', 'price' => 1]]], $headers);
            $response->assertStatus(422);
            $this->assertArrayHasKey('tickets.0.id', $response->json('errors'), "the id {$id} was not refused by name");
        }

        $this->assertSame('Test Event', $event->fresh()->name, 'a refused request still renamed the event');
        $this->assertFalse((bool) Ticket::find($general->id)->is_deleted);
        $this->assertEquals(10, (float) Ticket::find($general->id)->price);
        $this->assertEquals(99, (float) Ticket::find($foreign->id)->price);
    }

    public function test_agenda_parts_and_add_ons_are_matched_by_id_too(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $this->createTicket($event, ['price' => 10]);
        $addon = $this->createTicket($event, ['type' => 'Parking', 'price' => 5, 'is_addon' => true, 'max_per_order' => 2]);
        $doors = EventPart::create(['event_id' => $event->id, 'name' => 'Doors', 'start_time' => '19:00', 'end_time' => '20:00', 'description' => 'Bar open', 'sort_order' => 0]);
        $show = EventPart::create(['event_id' => $event->id, 'name' => 'Show', 'start_time' => '20:00', 'sort_order' => 1]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        $data = $this->getJson($url, $headers)->assertOk()->json('data');
        $parts = $data['event_parts'];
        $parts[0]['name'] = 'Doors open';
        $addons = $data['addons'];
        $addons[0]['price'] = 6;

        $this->putJson($url, ['event_parts' => $parts, 'addons' => $addons], $headers)->assertOk();

        $this->assertSame(2, EventPart::where('event_id', $event->id)->count());
        $doors = EventPart::find($doors->id);
        $this->assertNotNull($doors, 'an agenda part sent back with its id was deleted');
        $this->assertSame('Doors open', $doors->name);
        $this->assertSame('Bar open', $doors->description);
        $this->assertNotNull(EventPart::find($show->id));

        $addon = Ticket::find($addon->id);
        $this->assertFalse((bool) $addon->is_deleted, 'an add-on sent back with its id was retired');
        $this->assertEquals(6, (float) $addon->price);
        $this->assertSame(2, (int) $addon->max_per_order);

        $this->putJson($url, ['event_parts' => [['id' => 'not-an-id', 'name' => 'Doors']]], $headers)
            ->assertStatus(422)->assertJsonValidationErrors(['event_parts.0.id']);
        $this->putJson($url, ['addons' => [['id' => 'not-an-id', 'type' => 'Parking']]], $headers)
            ->assertStatus(422)->assertJsonValidationErrors(['addons.0.id']);
    }

    public function test_an_event_with_sales_is_not_deleted(): void
    {
        [, $talent, $event, $headers] = $this->ticketedEvent();
        $ticket = $this->createTicket($event, ['price' => 10]);
        $sale = $this->createSale($event, $talent, [], $ticket);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        $this->deleteJson($url, [], $headers)->assertStatus(422)->assertJsonStructure(['error']);

        $this->assertNotNull(Event::find($event->id));
        $this->assertNotNull(Sale::find($sale->id), 'the sale went with the event');

        // A refunded sale is the refund trail, and is kept for the same reason.
        $sale->status = 'refunded';
        $sale->saveQuietly();
        $this->deleteJson($url, [], $headers)->assertStatus(422);
        $this->assertNotNull(Sale::find($sale->id));
    }

    public function test_an_event_nobody_bought_is_still_deleted(): void
    {
        [, , $event, $headers] = $this->ticketedEvent();
        $this->createTicket($event, ['price' => 10]);

        $this->deleteJson('/api/events/'.UrlUtils::encodeId($event->id), [], $headers)->assertOk();

        $this->assertNull(Event::find($event->id));
    }
}
