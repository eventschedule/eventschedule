<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class LogoWallTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Curator with header_image = 'logos' (banner style is the default). */
    private function createLogoWallCurator(User $owner, array $attrs = []): Role
    {
        return $this->createRole($owner, 'curator', array_merge([
            'name' => 'Wall Curator',
            'header_image' => 'logos',
        ], $attrs));
    }

    /** Claimed venue with a deterministic demo profile image, attached to a new accepted event. */
    private function connectVenue(User $owner, Role $curator, string $image, array $venueAttrs = [], array $eventAttrs = []): Role
    {
        $venue = $this->createRole($owner, 'venue', array_merge([
            'name' => 'Venue '.$image,
            'profile_image_url' => $image,
        ], $venueAttrs));

        $event = $this->createEvent($curator, $eventAttrs);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return $venue;
    }

    public function test_logo_wall_shows_venues_from_accepted_public_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $venue = $this->connectVenue($owner, $curator, 'demo_wall_venue.jpg');

        $response = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertSee('data-logo-wall', false);
        $response->assertSee('/images/demo/demo_wall_venue.jpg', false);
        $response->assertSee(route('role.view_guest', ['subdomain' => $venue->subdomain]), false);
        $response->assertDontSee('images/headers/logos');
    }

    public function test_logo_wall_excludes_pending_unaccepted_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $this->connectVenue($owner, $curator, 'demo_wall_accepted.jpg');

        // createEvent's `?? true` treats an explicit null as absent, so flip the
        // curator's own pivot to pending after the fact.
        $pendingVenue = $this->createRole($owner, 'venue', [
            'name' => 'Pending Venue',
            'profile_image_url' => 'demo_wall_pending.jpg',
        ]);
        $pendingEvent = $this->createEvent($curator);
        $pendingEvent->roles()->updateExistingPivot($curator->id, ['is_accepted' => null]);
        $pendingEvent->roles()->attach($pendingVenue->id, ['is_accepted' => true]);

        $response = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertSee('/images/demo/demo_wall_accepted.jpg', false);
        $response->assertDontSee('/images/demo/demo_wall_pending.jpg');
    }

    public function test_logo_wall_excludes_draft_unlisted_and_cancelled_events(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $this->connectVenue($owner, $curator, 'demo_wall_public.jpg');
        $this->connectVenue($owner, $curator, 'demo_wall_draft.jpg', [], ['is_draft' => true]);
        $this->connectVenue($owner, $curator, 'demo_wall_unlisted.jpg', [], ['is_private' => true]);
        $this->connectVenue($owner, $curator, 'demo_wall_cancelled.jpg', [], ['is_cancelled' => true]);

        $response = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertSee('/images/demo/demo_wall_public.jpg', false);
        $response->assertDontSee('/images/demo/demo_wall_draft.jpg');
        $response->assertDontSee('/images/demo/demo_wall_unlisted.jpg');
        $response->assertDontSee('/images/demo/demo_wall_cancelled.jpg');
    }

    public function test_logo_wall_excludes_deleted_and_imageless_venues(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $kept = $this->connectVenue($owner, $curator, 'demo_wall_kept.jpg');
        $deleted = $this->connectVenue($owner, $curator, 'demo_wall_deleted.jpg', ['is_deleted' => true]);
        $imageless = $this->connectVenue($owner, $curator, '', ['profile_image_url' => null]);

        $ids = $curator->fresh()->logoWallRoles()->pluck('id');

        $this->assertTrue($ids->contains($kept->id));
        $this->assertFalse($ids->contains($deleted->id));
        $this->assertFalse($ids->contains($imageless->id));
    }

    public function test_venue_schedule_logo_wall_shows_talents_not_venues(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Wall Venue',
            'header_image' => 'logos',
        ]);
        $talent = $this->createRole($owner, 'talent', [
            'name' => 'Wall Talent',
            'profile_image_url' => 'demo_wall_talent.jpg',
        ]);
        $otherVenue = $this->createRole($owner, 'venue', [
            'name' => 'Other Venue',
            'profile_image_url' => 'demo_wall_othervenue.jpg',
        ]);

        $event = $this->createEvent($venue);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $event->roles()->attach($otherVenue->id, ['is_accepted' => true]);

        $ids = $venue->fresh()->logoWallRoles()->pluck('id');
        $this->assertTrue($ids->contains($talent->id));
        $this->assertFalse($ids->contains($otherVenue->id));
        $this->assertFalse($ids->contains($venue->id));

        $response = $this->get(route('role.view_guest', ['subdomain' => $venue->subdomain]));
        $response->assertOk();
        $response->assertSee('/images/demo/demo_wall_talent.jpg', false);
        $response->assertDontSee('/images/demo/demo_wall_othervenue.jpg');
    }

    public function test_empty_logo_wall_degrades_like_none(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);

        $response = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertDontSee('data-logo-wall');
        $response->assertDontSee('images/headers/logos');
    }

    public function test_unclaimed_venue_renders_unlinked_tile(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $unclaimed = $this->connectVenue($owner, $curator, 'demo_wall_unclaimed.jpg', [
            'email_verified_at' => null,
        ]);

        $response = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]));

        $response->assertOk();
        $response->assertSee('/images/demo/demo_wall_unclaimed.jpg', false);
        $response->assertDontSee(route('role.view_guest', ['subdomain' => $unclaimed->subdomain]), false);
    }

    public function test_saved_order_comes_first_then_alphabetical(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $alpha = $this->connectVenue($owner, $curator, 'demo_wall_a.jpg', ['name' => 'Alpha Venue']);
        $beta = $this->connectVenue($owner, $curator, 'demo_wall_b.jpg', ['name' => 'Beta Venue']);
        $gamma = $this->connectVenue($owner, $curator, 'demo_wall_c.jpg', ['name' => 'Gamma Venue']);

        $curator = $curator->fresh();
        $this->assertSame(
            [$alpha->id, $beta->id, $gamma->id],
            $curator->logoWallRoles()->pluck('id')->all(),
            'Without a saved order the wall is alphabetical'
        );

        $curator->logo_wall_order = json_encode([$gamma->id, $beta->id]);
        $curator->save();

        $this->assertSame(
            [$gamma->id, $beta->id, $alpha->id],
            $curator->fresh()->logoWallRoles()->pluck('id')->all(),
            'Saved order first, unordered venues follow alphabetically'
        );
    }

    public function test_malformed_logo_wall_order_renders_alphabetically_without_error(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $this->connectVenue($owner, $curator, 'demo_wall_a.jpg', ['name' => 'Alpha Venue']);
        $this->connectVenue($owner, $curator, 'demo_wall_b.jpg', ['name' => 'Beta Venue']);

        // A JSON scalar (not an array) would make array_flip throw a TypeError and 500
        // the public page if unguarded. Set it directly (the app never writes this).
        $curator->logo_wall_order = '"5"';
        $curator->save();

        $curator = $curator->fresh();
        $this->assertSame(
            ['Alpha Venue', 'Beta Venue'],
            $curator->logoWallRoles()->pluck('name')->all(),
            'A malformed order degrades to alphabetical'
        );

        $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]))
            ->assertOk()
            ->assertSee('data-logo-wall', false);
    }

    public function test_update_endpoint_persists_decoded_validated_order(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $alpha = $this->connectVenue($owner, $curator, 'demo_wall_a.jpg', ['name' => 'Alpha Venue']);
        $beta = $this->connectVenue($owner, $curator, 'demo_wall_b.jpg', ['name' => 'Beta Venue']);

        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $curator->subdomain]), [
            'name' => $curator->name,
            'timezone' => $curator->timezone,
            'email' => $curator->email,
            'new_subdomain' => $curator->subdomain,
            'logo_wall_order' => json_encode([
                UrlUtils::encodeId($beta->id),
                UrlUtils::encodeId($alpha->id),
                UrlUtils::encodeId(999999999),
            ]),
        ])->assertRedirect();

        $curator->refresh();
        $this->assertSame([$beta->id, $alpha->id], json_decode($curator->logo_wall_order, true));

        // An untouched (empty) hidden input leaves the stored order alone.
        $this->actingAs($owner)->put(route('role.update', ['subdomain' => $curator->subdomain]), [
            'name' => $curator->name,
            'timezone' => $curator->timezone,
            'email' => $curator->email,
            'new_subdomain' => $curator->subdomain,
            'logo_wall_order' => '',
        ])->assertRedirect();

        $this->assertSame([$beta->id, $alpha->id], json_decode($curator->fresh()->logo_wall_order, true));
    }

    public function test_edit_page_renders_logos_option_and_order_list(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);
        $this->connectVenue($owner, $curator, 'demo_wall_venue.jpg');

        $response = $this->actingAs($owner)->get('/'.$curator->subdomain.'/edit');

        $response->assertOk();
        $response->assertSee('value="logos"', false);
        $response->assertSee(__('messages.header_image_logos_venue'));
        $response->assertSee('id="logo-wall-list"', false);
        $response->assertSee('/images/demo/demo_wall_venue.jpg', false);
        $response->assertDontSee('images/headers/logos.png');
    }

    public function test_edit_page_shows_empty_warning_when_no_connected_venues(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createLogoWallCurator($owner);

        $response = $this->actingAs($owner)->get('/'.$curator->subdomain.'/edit');

        $response->assertOk();
        $response->assertSee(__('messages.logo_wall_empty_warning'));
        $response->assertDontSee('id="logo-wall-list"', false);
    }
}
