<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Repos\EventRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Every schedule has a name. A venue made from an event that gave the place by its address alone
 * used to be stored nameless, with eight random letters for an address, and its guest page was a
 * 500 (EVENTSCHEDULE-PHP-52). It is named after the address now, and so is every row made before.
 */
class ScheduleAlwaysNamedTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_venue_made_from_an_address_alone_is_named_after_it(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');

        $venue = app(EventRepo::class)->makeVenue(['address1' => '12 Harbour Road', 'city' => 'Springfield'], $talent, 'UTC');

        $this->assertSame('12 Harbour Road', $venue->name);
        $this->assertStringStartsWith('12-harbour-road', $venue->subdomain, 'and its address is made from that name');
        $this->assertTrue($venue->isOnlyAnAddress());

        $townOnly = app(EventRepo::class)->makeVenue(['city' => 'Shelbyville'], $talent, 'UTC');
        $this->assertSame('Shelbyville', $townOnly->name);

        $named = app(EventRepo::class)->makeVenue(['name' => 'Moe\'s Tavern', 'address1' => '12 Harbour Road'], $talent, 'UTC');
        $this->assertSame('Moe\'s Tavern', $named->name, 'a name that was given is kept');
        $this->assertFalse($named->isOnlyAnAddress());
    }

    public function test_no_save_stores_a_schedule_without_a_name(): void
    {
        $role = new Role;
        $role->type = 'venue';
        $role->subdomain = 'nameless-'.uniqid();
        $role->address1 = '742 Evergreen Terrace';
        $role->address1_en = '742 Evergreen Terrace (en)';
        $role->save();

        $this->assertSame('742 Evergreen Terrace', $role->fresh()->name);
        $this->assertSame('742 Evergreen Terrace (en)', $role->fresh()->name_en, 'the translation of the line comes with it');
        $this->assertNotNull($role->fresh()->name_normalized, 'and the venue lookup can find it by that name');

        $bare = new Role;
        $bare->type = 'venue';
        $bare->subdomain = 'bare-'.uniqid();
        $bare->save();
        $this->assertNotSame('', trim((string) $bare->fresh()->name), 'with nothing to go on it takes its type');
    }

    public function test_the_migration_names_the_schedules_stored_without_one(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['address1' => '1 Main Street', 'city' => 'Ogdenville']);
        $townOnly = $this->createRole($owner, 'venue', ['address1' => null, 'city' => 'North Haverbrook']);
        $named = $this->createRole($owner, 'venue', ['name' => 'Kept Hall']);
        DB::table('roles')->whereIn('id', [$venue->id, $townOnly->id])->update(['name' => null, 'name_normalized' => null]);
        $stamp = DB::table('roles')->where('id', $venue->id)->value('updated_at');

        (require database_path('migrations/2026_11_27_000000_name_schedules_that_have_no_name.php'))->up();

        $this->assertSame('1 Main Street', $venue->fresh()->name);
        $this->assertNotNull($venue->fresh()->name_normalized);
        $this->assertSame('North Haverbrook', $townOnly->fresh()->name);
        $this->assertSame('Kept Hall', $named->fresh()->name);
        $this->assertSame($stamp, DB::table('roles')->where('id', $venue->id)->value('updated_at'), 'updated_at is the sitemap\'s <lastmod> and does not move');
    }

    public function test_a_name_that_restates_the_address_is_not_the_venues_own(): void
    {
        $owner = $this->createOwner();
        $street = $this->createRole($owner, 'venue', ['name' => '12 Harbour Road', 'address1' => '12  harbour road', 'city' => 'Springfield']);
        $town = $this->createRole($owner, 'venue', ['name' => 'Springfield', 'address1' => null, 'city' => 'Springfield']);
        $hall = $this->createRole($owner, 'venue', ['name' => 'Harbour Hall', 'address1' => '12 Harbour Road', 'city' => 'Springfield']);
        $hallInTown = $this->createRole($owner, 'venue', ['name' => 'Springfield', 'address1' => '1 Main Street', 'city' => 'Springfield']);

        $this->assertFalse($street->hasOwnName());
        $this->assertNull($street->ownName());
        $this->assertFalse($town->hasOwnName());
        $this->assertTrue($hall->hasOwnName());
        $this->assertSame('Harbour Hall', $hall->ownName());
        $this->assertTrue($hallInTown->hasOwnName(), 'a hall on a street may be named after its town by choice');

        $ids = [$street->id, $town->id, $hall->id, $hallInTown->id];
        $act = $this->createRole($owner, 'talent', ['name' => 'Springfield', 'city' => 'Springfield']);
        $this->assertTrue($act->hasOwnName(), 'an act called after its town is not a place');
        $ids[] = $act->id;

        $this->assertEqualsCanonicalizing([$hall->id, $hallInTown->id, $act->id], Role::whereIn('id', $ids)->withOwnName()->pluck('id')->all(), 'the SQL says what hasOwnName() says');

        $this->assertSame('12  harbour road', $street->shortVenue(false), 'said by its address once, not "address | address"');
    }

    public function test_correcting_the_address_of_a_venue_that_is_only_an_address_renames_it(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => '12 Harbour Road', 'address1' => '12 Harbour Road', 'city' => 'Springfield']);
        $named = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall', 'address1' => '12 Harbour Road', 'city' => 'Springfield']);

        $venue->fresh()->fill(['address1' => '14 Harbour Road'])->save();
        $named->fresh()->fill(['address1' => '14 Harbour Road'])->save();

        $this->assertSame('14 Harbour Road', $venue->fresh()->name);
        $this->assertSame('Harbour Hall', $named->fresh()->name, 'a name of its own is never touched');
    }

    public function test_the_event_page_says_the_address_of_a_venue_that_is_only_an_address_once(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = app(EventRepo::class)->makeVenue(['address1' => '742 Evergreen Terrace', 'city' => 'Springfield'], $talent, 'UTC');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'name' => 'Street Party']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        // Enough for the page to draw the venue's card rather than leave it out as bare.
        $venue->forceFill(['website' => 'https://example.com', 'formatted_address' => '742 Evergreen Terrace, Springfield'])->saveQuietly();

        $html = $this->get($event->fresh()->getGuestUrl($talent->subdomain))->assertOk()->getContent();
        $this->assertStringContainsString('gk-venue-id', $html, 'the card is drawn');
        $this->assertStringContainsString('742 Evergreen Terrace', $html);
        $this->assertStringNotContainsString('<span class="gk-venue-addr">', $html, 'no second address line under it');

        $this->get($venue->getGuestUrl(true) ?: '/'.$venue->subdomain)->assertOk();
    }

    public function test_only_a_venue_is_renamed_when_its_town_changes(): void
    {
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Berlin', 'city' => 'Berlin']);

        $act->fresh()->fill(['city' => 'Hamburg'])->save();

        $this->assertSame('Berlin', $act->fresh()->name);
    }

    public function test_a_meeting_link_never_becomes_a_name_or_an_address(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');

        $venue = app(EventRepo::class)->makeVenue(['address1' => 'https://zoom.us/j/123456789?pwd=SeCrEt', 'city' => 'Springfield'], $talent, 'UTC');

        $this->assertSame('Springfield', $venue->name);
        $this->assertStringNotContainsStringIgnoringCase('secret', $venue->subdomain);
        $this->assertStringNotContainsStringIgnoringCase('zoom', $venue->subdomain);
    }

    public function test_a_renamed_venue_does_not_take_the_old_streets_translation(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => '12 Harbour Road', 'address1' => '12 Harbour Road', 'city' => 'Springfield']);
        DB::table('roles')->where('id', $venue->id)->update(['name_en' => null, 'address1_en' => 'Old translation']);

        $venue->fresh()->fill(['address1' => '14 Harbour Road'])->save();

        $this->assertSame('14 Harbour Road', $venue->fresh()->name);
        $this->assertNotSame('Old translation', $venue->fresh()->name_en);
    }

    public function test_a_hall_named_after_its_town_keeps_its_name_when_its_street_changes(): void
    {
        $hall = $this->createRole($this->createOwner(), 'venue', ['name' => 'Springfield', 'address1' => '1 Main Street', 'city' => 'Springfield']);

        $hall->fresh()->fill(['address1' => '2 Main Street'])->save();

        $this->assertSame('Springfield', $hall->fresh()->name);
    }

    public function test_a_venue_with_nothing_to_be_named_after_is_not_called_by_its_type(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');

        $venue = app(EventRepo::class)->makeVenue(['address1' => 'https://zoom.us/j/1?pwd=x'], $talent, 'UTC');

        $this->assertNotSame('', trim((string) $venue->name));
        $this->assertFalse($venue->hasOwnName(), 'the type word is no name of its own');
        $this->assertDoesNotMatchRegularExpression('~^venue\d*$~', $venue->subdomain, 'and the address stays random');
    }

    public function test_a_name_given_in_english_alone_is_the_name(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');

        $venue = app(EventRepo::class)->makeVenue(['name_en' => 'Harbour Hall', 'address1' => '12 Harbour Road'], $talent, 'UTC');

        $this->assertSame('Harbour Hall', $venue->name);
        $this->assertTrue($venue->hasOwnName());
    }

    public function test_the_header_of_a_venue_named_after_its_town_says_the_town_once(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $venue = app(EventRepo::class)->makeVenue(['city' => 'Shelbyville'], $talent, 'UTC');
        $this->assertSame('', $venue->placeBesideName());

        $street = app(EventRepo::class)->makeVenue(['address1' => '12 Harbour Road', 'city' => 'Springfield'], $talent, 'UTC');
        $this->assertSame('Springfield', $street->placeBesideName(), 'beside a street, the town');
    }

    public function test_a_claimed_venue_named_after_its_street_is_still_linked_from_the_event_page(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => '12 Harbour Road', 'address1' => '12 Harbour Road', 'city' => 'Springfield', 'website' => 'https://example.com', 'formatted_address' => '12 Harbour Road, Springfield']);
        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertTrue($venue->fresh()->isClaimed());

        $html = $this->get($event->fresh()->getGuestUrl($talent->subdomain))->assertOk()->getContent();

        $this->assertStringContainsString('href="'.$venue->fresh()->getGuestUrl(true), $html);
        $this->assertStringNotContainsString('<span class="gk-venue-addr">12 Harbour Road', $html, 'the street is said once');
    }
}
