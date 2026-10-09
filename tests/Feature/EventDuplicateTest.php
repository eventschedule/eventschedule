<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A duplicate carries its source's venue in the venue field and its performers on the Participants
 * tab, and those decide them.
 *
 * It used to carry them as ticked boxes under "Also list on" as well: the clone payload's
 * "curators" is every schedule the source was on, and the duplicate form ticked each one it
 * offers. So a copy given another venue was saved at BOTH, and showed whichever of the two the
 * database returned first (the old one, for the person who reported it), and a performer removed
 * from a copy stayed on it. It needs the venue or the performer to be one of the person's own
 * schedules, which is what a venue made with your own email is.
 *
 * Each test opens the duplicate form by its real routes and posts the boxes that form has ticked.
 */
class EventDuplicateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private User $owner;

    private Role $band;

    private Role $venueA;

    private Role $venueB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = $this->createOwner();
        $this->band = $this->createRole($this->owner, 'talent', ['name' => 'The Band']);
        $this->venueA = $this->createVenueWithAddress($this->owner, ['name' => 'Venue A']);
        $this->venueB = $this->createVenueWithAddress($this->owner, ['name' => 'Venue B', 'address1' => '9 Other St']);
    }

    /** The boxes the rendered form has ticked under "Also list on", as a browser would post them. */
    private function tickedBoxes(string $html): array
    {
        preg_match_all('/name="curators\[\]"\s+value="([^"]+)"\s+(checked)?/', $html, $boxes, PREG_SET_ORDER);

        return array_values(array_map(fn ($box) => $box[1], array_filter($boxes, fn ($box) => ! empty($box[2]))));
    }

    /** The id the page's venue picker starts on, or null when it starts on none. */
    private function pickedVenue(string $html): ?string
    {
        $this->assertSame(1, preg_match('/^\s*selectedVenue: (.*),\s*$/m', $html, $seed), 'the venue seed was not found on the page');
        $venue = json_decode($seed[1], true);

        return is_array($venue) ? ($venue['id'] ?? null) : null;
    }

    /** The duplicate form of an event, as pressing Duplicate opens it. */
    private function duplicateForm(Event $event): string
    {
        $this->actingAs($this->owner)
            ->get(route('event.clone', ['subdomain' => $this->band->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertRedirect(route('event.create', ['subdomain' => $this->band->subdomain]));

        return $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $this->band->subdomain]))->assertOk()->getContent();
    }

    private function member(Role $talent): array
    {
        return [UrlUtils::encodeId($talent->id) => ['name' => $talent->name, 'email' => '']];
    }

    /** An event of the band's at Venue A, saved the way the form saves one. */
    private function sourceEvent(array $overrides = []): Event
    {
        $this->postCreateEvent($this->owner, $this->band, $overrides + [
            'venue_id' => UrlUtils::encodeId($this->venueA->id), 'venue_submitted' => 1,
            'members' => $this->member($this->band), 'members_submitted' => 1,
            'curators_submitted' => 1,
        ])->assertRedirect();

        return $this->latestEvent();
    }

    private function attachedTo(Event $event): array
    {
        return $event->roles()->pluck('roles.id')->sort()->values()->all();
    }

    private function venuesOf(Event $event): array
    {
        return $event->roles()->where('roles.type', 'venue')->pluck('roles.id')->sort()->values()->all();
    }

    public function test_a_duplicate_given_another_venue_is_at_that_venue_alone(): void
    {
        $source = $this->sourceEvent();
        $this->assertSame([$this->venueA->id], $this->venuesOf($source), 'sanity check: the source is at Venue A');

        $html = $this->duplicateForm($source);
        $this->assertSame(UrlUtils::encodeId($this->venueA->id), $this->pickedVenue($html), 'the duplicate starts on the source venue');
        $this->assertSame([], $this->tickedBoxes($html), 'and does not tick it under "Also list on" as well');

        // Change, Venue B, Save.
        $this->postCreateEvent($this->owner, $this->band, [
            'name' => 'The copy',
            'venue_id' => UrlUtils::encodeId($this->venueB->id), 'venue_submitted' => 1,
            'members' => $this->member($this->band), 'members_submitted' => 1,
            'curators_submitted' => 1, 'curators' => $this->tickedBoxes($html),
        ])->assertRedirect();
        $copy = $this->latestEvent();
        $this->assertNotSame($source->id, $copy->id);

        $this->assertSame([$this->venueB->id], $this->venuesOf($copy), 'the copy is at the venue that was chosen, and no other');

        $copyForm = $this->actingAs($this->owner)
            ->get(route('event.edit', ['subdomain' => $this->band->subdomain, 'hash' => UrlUtils::encodeId($copy->id)]))
            ->assertOk()->getContent();
        $this->assertSame(UrlUtils::encodeId($this->venueB->id), $this->pickedVenue($copyForm), 'and its form shows it');
    }

    public function test_a_duplicate_with_a_performer_removed_does_not_keep_them(): void
    {
        $act = $this->createRole($this->owner, 'talent', ['name' => 'Second Act']);
        $source = $this->sourceEvent(['members' => $this->member($this->band) + $this->member($act)]);
        $this->assertTrue($source->roles()->where('roles.id', $act->id)->exists(), 'sanity check: the second act is on the source');

        $html = $this->duplicateForm($source);

        // The second act is removed on the Participants tab; nothing else is touched.
        $this->postCreateEvent($this->owner, $this->band, [
            'name' => 'The copy',
            'venue_id' => $this->pickedVenue($html), 'venue_submitted' => 1,
            'members' => $this->member($this->band), 'members_submitted' => 1,
            'curators_submitted' => 1, 'curators' => $this->tickedBoxes($html),
        ])->assertRedirect();

        $this->assertFalse($this->latestEvent()->roles()->where('roles.id', $act->id)->exists());
    }

    /** The guard on the fix: what a duplicate is meant to carry, it still carries. */
    public function test_a_duplicate_left_as_it_is_keeps_its_venue_its_performer_and_its_listing(): void
    {
        $act = $this->createRole($this->owner, 'talent', ['name' => 'Second Act']);
        $listing = $this->createCurator($this->owner, ['name' => 'The Listing', 'require_approval' => false]);
        $other = $this->createCurator($this->owner, ['name' => 'Another Listing']);
        $group = $this->createGroup($listing, ['name' => 'Jazz']);

        $source = $this->sourceEvent([
            'members' => $this->member($this->band) + $this->member($act),
            'curators' => [UrlUtils::encodeId($listing->id)],
            'curator_groups' => [UrlUtils::encodeId($listing->id) => UrlUtils::encodeId($group->id)],
        ]);

        $html = $this->duplicateForm($source);
        $ticked = $this->tickedBoxes($html);

        $this->assertSame([UrlUtils::encodeId($listing->id)], $ticked, 'the listing is the one box that is ticked');
        $this->assertSame(UrlUtils::encodeId($this->venueA->id), $this->pickedVenue($html), 'the venue is in the venue field');
        $this->assertMatchesRegularExpression('/selectedMembers: .*Second Act/', $html, 'the performer is on the Participants tab');
        $this->assertMatchesRegularExpression(
            '/<option[^>]*value="'.preg_quote(UrlUtils::encodeId($group->id), '/').'"[^>]*selected/',
            $html,
            'the sub-schedule it was filed under comes too'
        );

        $this->postCreateEvent($this->owner, $this->band, [
            'name' => 'The copy',
            'venue_id' => $this->pickedVenue($html), 'venue_submitted' => 1,
            'members' => $this->member($this->band) + $this->member($act), 'members_submitted' => 1,
            'curators_submitted' => 1, 'curators' => $ticked,
        ])->assertRedirect();
        $copy = $this->latestEvent();

        $this->assertSame(
            collect([$this->band->id, $act->id, $this->venueA->id, $listing->id])->sort()->values()->all(),
            $this->attachedTo($copy)
        );
        $this->assertFalse($copy->roles()->where('roles.id', $other->id)->exists());
    }

    /** A template saved before the fix holds the venue and the performers in its "curators". */
    public function test_a_stored_template_does_not_tick_its_venue_or_its_performers(): void
    {
        $act = $this->createRole($this->owner, 'talent', ['name' => 'Second Act']);
        $listing = $this->createCurator($this->owner, ['name' => 'The Listing']);

        $template = EventTemplate::create([
            'role_id' => $this->band->id,
            'user_id' => $this->owner->id,
            'name' => 'Old template',
            'template_data' => [
                'event' => ['name' => 'Weekly'],
                'tickets' => [[]],
                'addons' => [],
                'venue_id' => UrlUtils::encodeId($this->venueA->id),
                'selected_members' => [$this->band->toData(), $act->toData()],
                'curators' => array_map(fn (Role $schedule) => UrlUtils::encodeId($schedule->id), [$this->band, $act, $this->venueA, $listing]),
                'curator_groups' => [],
                'parts' => [],
                'flyer_image_filename' => null,
            ],
        ]);

        $this->actingAs($this->owner)
            ->get(route('event_template.apply', ['subdomain' => $this->band->subdomain, 'hash' => $template->encodeId()]))
            ->assertRedirect();
        $html = $this->actingAs($this->owner)->get(route('event.create', ['subdomain' => $this->band->subdomain]))->assertOk()->getContent();

        $this->assertSame([UrlUtils::encodeId($listing->id)], $this->tickedBoxes($html));
        $this->assertSame(UrlUtils::encodeId($this->venueA->id), $this->pickedVenue($html));
    }
}
