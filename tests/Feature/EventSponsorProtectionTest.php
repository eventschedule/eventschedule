<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event's own sponsors are changed by the Sponsors tab of its own schedule's plan, and by
 * nothing else.
 *
 * EventRepo::saveEvent() used to gate the sponsor block on the schedule the form was OPENED from
 * and to read sponsor_mode with a default. So the same owner saving a Pro schedule's event from
 * their Free venue schedule, a Free curator that listed it, and every update through the API (which
 * never sends sponsor_mode) each wiped the event's sponsors, the last one deleting the logo files
 * as well. Nothing on screen said so in any of the three. (A curator that only lists an event no
 * longer saves it at all, so the second case is played here by another owner's venue.)
 */
class EventSponsorProtectionTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true, 'filesystems.default' => 'local']);
        Storage::fake('local');
    }

    private function storedLogo(): string
    {
        $name = 'sponsor_'.Str::lower(Str::random(32)).'.png';
        Storage::put('public/'.$name, 'logo');

        return $name;
    }

    /** @return array{0: User, 1: Role, 2: Event, 3: string} */
    private function sponsoredEvent(): array
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $logo = $this->storedLogo();
        $event = $this->createEvent($talent, [
            'creator_role_id' => $talent->id,
            'sponsor_mode' => 'custom',
            'sponsor_logos' => json_encode([['name' => 'Duff', 'logo' => $logo, 'url' => 'https://duff.example.org', 'tier' => 'gold']]),
        ]);

        return [$owner, $talent, $event, $logo];
    }

    private function assertSponsorsKept(Event $event, string $logo, string $why): void
    {
        $fresh = Event::find($event->id);
        $this->assertSame('custom', $fresh->sponsor_mode, $why);
        $this->assertStringContainsString($logo, (string) $fresh->sponsor_logos, $why);
        $this->assertTrue(Storage::exists('public/'.$logo), $why.' (the logo file)');
    }

    public function test_the_same_owner_saving_from_their_free_schedule_keeps_them(): void
    {
        [$owner, , $event, $logo] = $this->sponsoredEvent();
        $freeVenue = $this->createFreeRole($owner, 'venue');
        $event->roles()->attach($freeVenue->id, ['is_accepted' => true]);
        $this->assertFalse($freeVenue->isPro(), 'sanity check: the schedule the form is opened from has no sponsors of its own');

        $this->putUpdateEvent($owner, $freeVenue, $event)->assertRedirect();

        $this->assertSponsorsKept($event, $logo, 'saved from the owner\'s Free venue schedule');
    }

    /**
     * Somebody else's Free schedule that the event is on. Until 2026-10 this was a curator that
     * listed it; listing is no longer editing (EventListingRightsTest), and the schedule that still
     * saves an event it did not make is the venue it is at.
     */
    public function test_another_owners_free_schedule_the_event_is_on_keeps_them(): void
    {
        [, , $event, $logo] = $this->sponsoredEvent();
        $venueUser = $this->createOwner();
        $venue = $this->createFreeRole($venueUser, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->putUpdateEvent($venueUser, $venue, $event, ['name' => 'Renamed by the venue'])->assertRedirect();

        $this->assertSame('Renamed by the venue', Event::find($event->id)->name, 'sanity check: the save went through, it was not refused');
        $this->assertSponsorsKept($event, $logo, 'saved by a Free venue the event is at');

        // The curator of before: its save is refused outright, so there is nothing to wipe.
        $curatorUser = $this->createOwner();
        $curator = $this->createFreeRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->putUpdateEvent($curatorUser, $curator, $event, ['name' => 'Renamed by the curator']);

        $this->assertSame('Renamed by the venue', Event::find($event->id)->name);
        $this->assertSponsorsKept($event, $logo, 'a Free curator that lists it');
    }

    /**
     * The event's own schedule has sponsors on its plan, so the Free schedule's form shows the
     * tab and posts it: the mode, and the list as the page held it.
     */
    public function test_the_free_schedules_form_posts_the_tab_and_the_list_it_showed_is_kept(): void
    {
        [$owner, , $event, $logo] = $this->sponsoredEvent();
        $freeVenue = $this->createFreeRole($owner, 'venue');
        $event->roles()->attach($freeVenue->id, ['is_accepted' => true]);
        $stored = json_decode(Event::find($event->id)->sponsor_logos, true);

        $this->putUpdateEvent($owner, $freeVenue, $event, [
            'sponsor_mode' => 'custom',
            'existing_event_sponsors' => json_encode($stored),
        ])->assertRedirect();

        $this->assertSponsorsKept($event, $logo, 'the tab was on the page and its list was sent back unchanged');

        // And taking the sponsor off that list, on that form, takes it off the event.
        $this->putUpdateEvent($owner, $freeVenue, $event, ['sponsor_mode' => 'custom', 'existing_event_sponsors' => '[]'])->assertRedirect();
        $this->assertStringNotContainsString($logo, (string) Event::find($event->id)->sponsor_logos);
    }

    /**
     * A template is a stored copy. One saved before a copy stopped carrying "this event's own"
     * still says so, and has no logos to show for it.
     */
    public function test_a_template_saved_with_the_events_own_sponsors_starts_on_the_schedules(): void
    {
        [$owner, $talent, $event] = $this->sponsoredEvent();
        $payload = EventRepo::buildClonePayload($event);
        $payload['event']['sponsor_mode'] = 'custom';
        $payload['event']['sponsor_logos'] = null;
        $template = \App\Models\EventTemplate::create(['role_id' => $talent->id, 'user_id' => $owner->id, 'name' => 'Old template', 'template_data' => $payload]);

        $this->actingAs($owner)->get(route('event_template.apply', ['subdomain' => $talent->subdomain, 'hash' => $template->encodeId()]))->assertRedirect();

        $this->assertSame('default', session('cloned_event')['event']['sponsor_mode']);
    }

    public function test_an_update_through_the_api_keeps_them(): void
    {
        [$owner, , $event, $logo] = $this->sponsoredEvent();
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['name' => 'Renamed over the API'], ['X-API-Key' => $raw])->assertOk();

        $this->assertSame('Renamed over the API', Event::find($event->id)->name, 'sanity check: the update happened');
        $this->assertSponsorsKept($event, $logo, 'updated through the API');
    }

    public function test_a_save_that_never_showed_the_sponsors_tab_keeps_them(): void
    {
        [$owner, $talent, $event, $logo] = $this->sponsoredEvent();

        // sponsor_logos posted by hand, with no sponsor_mode: neither kept as posted nor a reason to clear.
        $this->putUpdateEvent($owner, $talent, $event, ['sponsor_logos' => '[]'])->assertRedirect();

        $this->assertSponsorsKept($event, $logo, 'the form did not post sponsor_mode');
    }

    public function test_the_sponsors_tab_still_decides(): void
    {
        [$owner, $talent, $event, $logo] = $this->sponsoredEvent();

        $this->putUpdateEvent($owner, $talent, $event, ['sponsor_mode' => 'none'])->assertRedirect();

        $fresh = Event::find($event->id);
        $this->assertSame('none', $fresh->sponsor_mode);
        $this->assertNull($fresh->sponsor_logos);
        $this->assertFalse(Storage::exists('public/'.$logo), 'leaving the event\'s own list deletes its logos, as it always did');
    }

    /** What a schedule on a paid plan could already do from its own form is not taken away. */
    public function test_a_paid_schedule_still_manages_sponsors_from_its_own_form(): void
    {
        $owner = $this->createOwner();
        $freeTalent = $this->createFreeRole($owner, 'talent');
        $event = $this->createEvent($freeTalent, ['creator_role_id' => $freeTalent->id]);
        // The paid schedule is the venue the event is at (a curator that only lists it saves
        // nothing of it since 2026-10).
        $venueUser = $this->createOwner();
        $venue = $this->createRole($venueUser, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertTrue($venue->isPro());

        $this->putUpdateEvent($venueUser, $venue, $event, ['sponsor_mode' => 'none'])->assertRedirect();

        $this->assertSame('none', Event::find($event->id)->sponsor_mode);
    }

    /** And where neither schedule has sponsors on its plan, a save clears them, as it always did. */
    public function test_an_event_of_free_schedules_holds_no_sponsors(): void
    {
        $owner = $this->createOwner();
        $freeTalent = $this->createFreeRole($owner, 'talent');
        $event = $this->createEvent($freeTalent, ['creator_role_id' => $freeTalent->id, 'sponsor_mode' => 'none']);

        $this->putUpdateEvent($owner, $freeTalent, $event, ['sponsor_mode' => 'custom'])->assertRedirect();

        $this->assertNull(Event::find($event->id)->sponsor_mode);
    }

    public function test_the_demo_account_leaves_them_alone(): void
    {
        [$owner, $talent, $event, $logo] = $this->sponsoredEvent();
        $owner->forceFill(['email' => DemoService::DEMO_EMAIL])->save();

        $this->putUpdateEvent($owner->fresh(), $talent, $event, ['sponsor_mode' => 'none'])->assertRedirect();

        $this->assertSponsorsKept($event, $logo, 'saved in demo mode');
    }

    /** A copy does not carry the logos, so it must not carry "this event's own" either: it showed none. */
    public function test_a_copy_of_an_event_with_its_own_sponsors_starts_on_the_schedules(): void
    {
        [, , $event] = $this->sponsoredEvent();

        $payload = EventRepo::buildClonePayload($event);

        $this->assertNull($payload['event']['sponsor_logos'] ?? null);
        $this->assertSame('default', $payload['event']['sponsor_mode']);

        $event->forceFill(['sponsor_mode' => 'none', 'sponsor_logos' => null])->save();
        $this->assertSame('none', EventRepo::buildClonePayload($event->fresh())['event']['sponsor_mode'], '"none" needs no logos, and is kept');
    }
}
