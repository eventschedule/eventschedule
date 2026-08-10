<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Duplicate venue schedules - the ones imports and calendar sync invent - are collapsed out of
 * the event form's picker and cleaned up from the account-wide merge page.
 */
class VenueDuplicatesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** An unclaimed, contact-less venue: what an import or a calendar sync leaves behind. */
    private function stubVenue(string $name, array $attrs = []): Role
    {
        $venue = new Role;
        $venue->subdomain = 'stub'.strtolower(\Illuminate\Support\Str::random(10));
        $venue->type = 'venue';
        $venue->name = $name;
        $venue->email = null;
        $venue->phone = null;

        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }

        $venue->save();

        return $venue->fresh();
    }

    private function venueNamesOnCreateForm(Role $schedule): array
    {
        $response = $this->get(route('event.create', ['subdomain' => $schedule->subdomain]))->assertOk();

        return collect($response->viewData('venues'))->pluck('name')->sort()->values()->all();
    }

    public function test_create_form_collapses_a_stub_onto_the_real_venue(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $real = $this->createRole($owner, 'venue', ['name' => 'Zappa Club', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Zappa Club', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);

        // Two byte-identical options before; the claimed one survives.
        $this->assertSame(['Zappa Club'], $this->venueNamesOnCreateForm($talent));

        $response = $this->get(route('event.create', ['subdomain' => $talent->subdomain]));
        $this->assertSame(1, $response->viewData('duplicateVenueGroupCount'));
        $this->assertSame(
            UrlUtils::encodeId($real->id),
            collect($response->viewData('venues'))->firstWhere('name', 'Zappa Club')['id']
        );
    }

    /**
     * The shape calendar sync ACTUALLY produces, which every other test in this file skips:
     * ConvertsLocationToVenue names the venue after the whole inbound location string and never
     * sets a city. Keyed on the full name plus the city, that stub could never share a group with
     * the real venue - so the merge tool could not clean up the duplicates the sync it ships
     * beside was creating.
     */
    public function test_a_sync_stub_named_after_the_whole_location_groups_with_the_real_venue(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $real = $this->createRole($owner, 'venue', ['name' => 'The Anchor', 'city' => 'Haifa', 'country_code' => 'il']);

        // No city, and the city is baked into the name - exactly what the sync path writes.
        $stub = $this->stubVenue('The Anchor, Haifa', ['country_code' => 'il']);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);

        $this->assertSame(['The Anchor'], $this->venueNamesOnCreateForm($talent));

        $response = $this->get(route('event.create', ['subdomain' => $talent->subdomain]));
        $this->assertSame(1, $response->viewData('duplicateVenueGroupCount'));
    }

    /**
     * The city left the grouping key, so it has to hold the line here instead: two venues that
     * both name a city, and name different ones, are a second room or a franchise branch.
     */
    public function test_two_venues_in_different_cities_are_never_collapsed(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $this->createRole($owner, 'venue', ['name' => 'Barby', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $other = $this->stubVenue('Barby', ['city' => 'Haifa', 'country_code' => 'il']);
        $this->followRole($owner, $other);

        $this->actingAs($owner);

        $this->assertSame(['Barby', 'Barby'], $this->venueNamesOnCreateForm($talent),
            'a different city means a different place, so both stay pickable');
    }

    /**
     * isSafeToCollapse only checked the survivor's level when the survivor was CLAIMED, while the
     * merge page's own predicate is unconditional (owner/admin, or follower on an unclaimed
     * role). An unclaimed survivor the user is merely a `viewer` on therefore collapsed in the
     * picker while the merge page skipped the group, stranding the row with no way back.
     */
    public function test_the_picker_keeps_a_group_the_merge_page_cannot_offer(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');

        $survivor = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $loser = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);

        // Read-only on the survivor: neither owner/admin nor follower, so no merge into it is
        // possible and the merge page will not list the group.
        $this->followRole($owner, $survivor, 'viewer');
        $this->followRole($owner, $loser);

        $this->actingAs($owner);

        $this->assertSame(['Ozen Bar', 'Ozen Bar'], $this->venueNamesOnCreateForm($talent));
    }

    public function test_create_form_keeps_two_real_venues_that_share_a_name(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        // Both claimed - two genuine venues (a franchise branch, a second room), not a stub.
        $this->createRole($owner, 'venue', ['name' => 'The Anchor', 'city' => 'Haifa', 'country_code' => 'il']);
        $this->createRole($owner, 'venue', ['name' => 'The Anchor', 'city' => 'Haifa', 'country_code' => 'il', 'email' => 'anchor2@gmail.com']);

        $this->actingAs($owner);

        $this->assertSame(['The Anchor', 'The Anchor'], $this->venueNamesOnCreateForm($talent));
    }

    public function test_create_form_keeps_a_stub_that_carries_its_own_contact_details(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $this->createRole($owner, 'venue', ['name' => 'Barby', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $other = $this->stubVenue('Barby', ['city' => 'Tel Aviv', 'country_code' => 'il', 'phone' => '+972500000000']);
        $this->followRole($owner, $other);

        $this->actingAs($owner);

        // A phone number means somebody entered real detail - not an empty shell to hide.
        $this->assertSame(['Barby', 'Barby'], $this->venueNamesOnCreateForm($talent));
    }

    public function test_edit_form_still_lists_the_events_own_venue_after_a_collapse(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $real = $this->createRole($owner, 'venue', ['name' => 'Levontin 7', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Levontin 7', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $stub);

        // The event sits on the stub, which loses its group to the claimed venue. The picker is
        // hidden while a venue is selected, but Remove reveals it against this same list - so the
        // venue just removed has to still be there.
        $event = $this->createEvent($talent);
        $event->roles()->attach($stub->id, ['is_accepted' => true]);

        $response = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $talent->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk();

        $ids = collect($response->viewData('venues'))->pluck('id')->all();
        $this->assertContains(UrlUtils::encodeId($stub->id), $ids);
        $this->assertContains(UrlUtils::encodeId($real->id), $ids);
    }

    public function test_merge_page_groups_an_orphan_with_no_events(): void
    {
        $owner = $this->createOwner();
        $real = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $orphan = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $orphan);

        $response = $this->actingAs($owner)->get(route('following.merge_venues'))->assertOk();

        $groups = $response->viewData('groups');
        $this->assertCount(1, $groups);
        $this->assertEqualsCanonicalizing(
            [$real->id, $orphan->id],
            array_map(fn ($v) => $v->id, $groups[0])
        );
    }

    public function test_merging_from_the_account_page_soft_deletes_the_orphan(): void
    {
        $owner = $this->createOwner();
        $real = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $orphan = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $orphan);

        $talent = $this->createRole($owner, 'talent');
        $event = $this->createEvent($talent);
        $event->roles()->attach($orphan->id, ['is_accepted' => true]);

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => $real->id,
            'source_ids' => [$orphan->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('roles', ['id' => $orphan->id, 'is_deleted' => true]);
        $this->assertDatabaseHas('roles', ['id' => $real->id, 'is_deleted' => false]);
        $this->assertSame($real->id, $event->fresh()->venue->id);
        // The user keeps owner on the survivor, not the follower level from the orphan.
        $this->assertDatabaseHas('role_user', [
            'role_id' => $real->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);
    }

    public function test_dismissing_a_group_removes_it_and_the_following_banner(): void
    {
        $owner = $this->createOwner();
        $real = $this->createRole($owner, 'venue', ['name' => 'Beit Haamudim', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Beit Haamudim', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);
        $this->assertSame(2, $this->get(route('following'))->viewData('duplicateVenueCount'));

        $this->post(route('following.merge_venues_dismiss'), [
            'venue_ids' => [$real->id, $stub->id],
        ])->assertRedirect(route('following.merge_venues'));

        $this->assertDatabaseHas('dismissed_venue_merge_suggestions', [
            'user_id' => $owner->id,
            'role_id' => null,
        ]);
        $this->assertCount(0, $this->get(route('following.merge_venues'))->viewData('groups'));
        $this->assertSame(0, $this->get(route('following'))->viewData('duplicateVenueCount'));
    }

    public function test_dismissing_a_group_stops_the_picker_hiding_the_venue(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $real = $this->createRole($owner, 'venue', ['name' => 'Beit Haamudim', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Beit Haamudim', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);
        $this->assertSame(['Beit Haamudim'], $this->venueNamesOnCreateForm($talent));

        $this->post(route('following.merge_venues_dismiss'), [
            'venue_ids' => [$real->id, $stub->id],
        ])->assertRedirect();

        // "Not duplicates" removed the group from the merge page, so the picker must stop
        // collapsing it - otherwise the venue the user kept is hidden with no way back
        // (/search-roles matches on exact email or phone, and a stub has neither).
        $response = $this->get(route('event.create', ['subdomain' => $talent->subdomain]));
        $ids = collect($response->viewData('venues'))->pluck('id')->all();

        $this->assertContains(UrlUtils::encodeId($real->id), $ids);
        $this->assertContains(UrlUtils::encodeId($stub->id), $ids);
        // And the hint must not point at a merge page that will say "none found".
        $this->assertSame(0, $response->viewData('duplicateVenueGroupCount'));
    }

    public function test_the_picker_drops_a_selected_venue_that_was_deleted(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $gone = $this->stubVenue('Merged Away', ['city' => 'Tel Aviv', 'country_code' => 'il', 'is_deleted' => true]);

        // Event::venue() has no is_deleted filter, so a merged-away venue still loads as the
        // event's venue. It must not be re-offered as something you can pick again.
        $event = $this->createEvent($talent);
        $event->roles()->attach($gone->id, ['is_accepted' => true]);

        $response = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $talent->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk();

        $this->assertNotContains(
            UrlUtils::encodeId($gone->id),
            collect($response->viewData('venues'))->pluck('id')->all()
        );
    }

    public function test_the_picker_keeps_a_stub_it_could_never_merge_away(): void
    {
        $owner = $this->createOwner();
        $stranger = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');

        // Somebody else's claimed venue plus the user's own stub. The merge page will not offer
        // this group (a follower cannot edit a claimed venue), so collapsing here would strand
        // the stub: hidden from the picker and unmergeable on the merge page.
        $theirs = $this->createRole($stranger, 'venue', ['name' => 'Hoodna', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Hoodna', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $theirs);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);
        $ids = collect($this->get(route('event.create', ['subdomain' => $talent->subdomain]))->viewData('venues'))
            ->pluck('id')->all();

        $this->assertContains(UrlUtils::encodeId($stub->id), $ids);
        $this->assertContains(UrlUtils::encodeId($theirs->id), $ids);
    }

    public function test_the_picker_keeps_a_stub_carrying_its_own_address(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $this->createRole($owner, 'venue', ['name' => 'Rothschild 12', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        // The option label falls back to address1 when a venue has no name, so hiding the only
        // row that carries an address loses information the user can see.
        $withAddress = $this->stubVenue('Rothschild 12', ['city' => 'Tel Aviv', 'country_code' => 'il', 'address1' => '12 Rothschild Blvd']);
        $this->followRole($owner, $withAddress);

        $this->actingAs($owner);

        $this->assertSame(['Rothschild 12', 'Rothschild 12'], $this->venueNamesOnCreateForm($talent));
    }

    public function test_saving_does_not_resurrect_a_deleted_venue(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $deleted = $this->stubVenue('Gone For Good', ['city' => 'Tel Aviv', 'country_code' => 'il', 'is_deleted' => true]);

        // A form opened before the delete still posts the id AND the venue_* echo of its details.
        // Nothing live matches, so the create branch would have minted the venue straight back.
        $this->postCreateEvent($owner, $talent, [
            'venue_id' => UrlUtils::encodeId($deleted->id),
            'venue_name' => 'Gone For Good',
            'venue_city' => 'Tel Aviv',
            'venue_country_code' => 'il',
        ])->assertRedirect();

        $this->assertSame(1, Role::where('type', 'venue')->count());
        $this->assertNull(\App\Models\Event::latest('id')->firstOrFail()->venue);
    }

    public function test_following_page_skips_the_duplicate_scan_it_cannot_show(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Pasaz', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Pasaz', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $stub);

        $this->actingAs($owner);
        $this->assertSame(2, $this->get(route('following'))->viewData('duplicateVenueCount'));

        // The banner is hidden while a filter is set, and the ajax branch renders the table
        // partial which has no banner - so the scan must not run for either.
        $this->assertSame(0, $this->get(route('following', ['filter' => 'pas']))->viewData('duplicateVenueCount'));
        $this->assertSame(
            0,
            $this->get(route('following'), ['X-Requested-With' => 'XMLHttpRequest'])->viewData('duplicateVenueCount')
        );
    }

    public function test_a_group_with_no_legal_target_is_not_offered(): void
    {
        $owner = $this->createOwner();
        $stranger = $this->createOwner();

        // Someone else's claimed venue plus a stub the user follows. isEditableBy() gives a mere
        // follower no rights over a claimed venue, so the merge could only ever be refused.
        $theirs = $this->createRole($stranger, 'venue', ['name' => 'Hoodna', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = $this->stubVenue('Hoodna', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $theirs);
        $this->followRole($owner, $stub);

        $this->assertCount(0, $this->actingAs($owner)->get(route('following.merge_venues'))->viewData('groups'));
    }

    public function test_merge_page_includes_a_soft_deleted_duplicate(): void
    {
        $owner = $this->createOwner();
        $real = $this->createRole($owner, 'venue', ['name' => 'Teder', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $deleted = $this->stubVenue('Teder', ['city' => 'Tel Aviv', 'country_code' => 'il', 'is_deleted' => true]);
        $this->followRole($owner, $deleted);

        $groups = $this->actingAs($owner)->get(route('following.merge_venues'))->viewData('groups');

        $this->assertCount(1, $groups);
        $this->assertEqualsCanonicalizing(
            [$real->id, $deleted->id],
            array_map(fn ($v) => $v->id, $groups[0])
        );
    }

    public function test_saving_an_event_survives_a_venue_that_was_merged_away(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $survivor = $this->createRole($owner, 'venue', ['name' => 'Kuli Alma', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $merged = $this->stubVenue('Kuli Alma', ['city' => 'Tel Aviv', 'country_code' => 'il', 'is_deleted' => true]);

        // A form opened before the merge still posts the id that is now gone. 404-ing there would
        // cost the whole save; the posted name resolves onto the venue that survived instead.
        $this->postCreateEvent($owner, $talent, [
            'venue_id' => UrlUtils::encodeId($merged->id),
            'venue_name' => 'Kuli Alma',
            'venue_city' => 'Tel Aviv',
            'venue_country_code' => 'il',
        ])->assertRedirect();

        $event = \App\Models\Event::latest('id')->firstOrFail();
        $this->assertSame($survivor->id, $event->venue->id);
        $this->assertSame(0, DB::table('event_role')->where('role_id', $merged->id)->count());
    }
}
