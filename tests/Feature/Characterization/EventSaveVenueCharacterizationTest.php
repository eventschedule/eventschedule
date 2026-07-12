<?php

namespace Tests\Feature\Characterization;

use App\Models\Role;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the venue-resolution section of EventRepo::saveEvent()
 * (REFACTOR_PLAN.md P11) against the UNMODIFIED code. These tests pin
 * CURRENT behavior - bug-for-bug - before the P12 decomposition.
 */
class EventSaveVenueCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_create_with_no_venue_pins_event_row_and_redirect(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $response = $this->postCreateEvent($owner, $role);

        // 20:00 America/New_York (EDT, UTC-4) on 2026-08-15 -> next day 00:00 UTC.
        $response->assertRedirect($this->adminCalendarUrl($role, 8, 2026));
        $response->assertSessionHas('message', __('messages.event_created'));

        $event = $this->latestEvent();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Characterized Event',
            'starts_at' => '2026-08-16 00:00:00',
            'timezone' => 'America/New_York',
            'duration' => 2.5,
            'user_id' => $owner->id,
            'creator_role_id' => $role->id,
            'is_draft' => 0,
            'tickets_enabled' => 0,
            'days_of_week' => null,
            'recurring_frequency' => null,
            'recurring_end_type' => 'never',
            'recurring_end_value' => null,
        ]);

        // Owner is a member of the schedule -> auto-accepted pivot.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $role->id,
            'is_accepted' => 1,
        ]);

        // No venue submitted -> no venue role attached, no roles row created.
        $this->assertSame(1, $event->roles()->count());
    }

    public function test_create_with_existing_venue_id_attaches_without_new_role(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $venue = $this->createVenueWithAddress($owner);
        $rolesBefore = Role::count();

        $this->postCreateEvent($owner, $role, [
            'venue_id' => UrlUtils::encodeId($venue->id),
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertSame($rolesBefore, Role::count());
        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $venue->id,
            'is_accepted' => 1,
        ]);
    }

    public function test_create_with_venue_fields_creates_new_venue_role_and_follows_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Brand New Hall',
            'venue_address1' => '9 New St',
            'venue_city' => 'Newtown',
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Brand New Hall')->firstOrFail();

        // Creation defaults pinned: generated subdomain, random gradient,
        // white font, schedule's capture timezone, unclaimed (no owner).
        $this->assertNotEmpty($venue->subdomain);
        $this->assertNotEmpty($venue->background_colors);
        $this->assertSame('#ffffff', $venue->font_color);
        $this->assertSame('America/New_York', $venue->timezone);
        $this->assertSame('9 New St', $venue->address1);
        $this->assertSame('Newtown', $venue->city);
        $this->assertNull($venue->user_id);
        $this->assertNull($venue->email);

        // $followNewRoles=true on the form path -> creator follows the new venue.
        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);

        // roles.require_approval defaults to TRUE, so a brand-new venue does
        // NOT auto-accept the event: the pivot stays un-accepted (null). This
        // is the known is_accepted visibility behavior - the event shows on
        // the creator's schedule but not on the new venue's own page.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $venue->id,
            'is_accepted' => null,
        ]);
    }

    public function test_create_with_venue_fields_matching_existing_venue_reuses_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        // Unclaimed venue that the normalized safety-net lookup should find.
        $existing = new Role;
        $existing->name = 'The Blue Note';
        $existing->subdomain = 'bluenote'.strtolower(\Illuminate\Support\Str::random(6));
        $existing->type = 'venue';
        $existing->city = 'Springfield';
        $existing->save();

        $rolesBefore = Role::count();

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'The  BLUE Note', // normalization collapses case/whitespace
            'venue_city' => 'Springfield',
        ])->assertRedirect();

        // No new roles row; the existing venue was attached instead.
        $this->assertSame($rolesBefore, Role::count());
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $existing->id,
        ]);

        // Matched-existing venues are auto-followed so they appear in the
        // user's venue dropdown for future imports.
        $this->assertDatabaseHas('role_user', [
            'role_id' => $existing->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);
    }

    public function test_unclaimed_venue_blank_clears_field_only_with_editable_flag(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $unclaimed = new Role;
        $unclaimed->name = 'Unclaimed Venue';
        $unclaimed->subdomain = 'unclaimed'.strtolower(\Illuminate\Support\Str::random(6));
        $unclaimed->type = 'venue';
        $unclaimed->website = 'https://keep-or-clear.example';
        $unclaimed->save();

        // Interactive form sets venue_details_editable -> blank is an
        // intentional clear (has() semantics). The whole unclaimed-edit block
        // only runs when at least one venue field is non-empty, so venue_name
        // is resubmitted unchanged the way the form does.
        $this->postCreateEvent($owner, $role, [
            'venue_id' => UrlUtils::encodeId($unclaimed->id),
            'venue_name' => 'Unclaimed Venue',
            'venue_details_editable' => '1',
            'venue_website' => '',
        ])->assertRedirect();

        $this->assertNull($unclaimed->fresh()->website);
    }

    public function test_unclaimed_venue_blank_does_not_clear_without_editable_flag(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $unclaimed = new Role;
        $unclaimed->name = 'Unclaimed Venue';
        $unclaimed->subdomain = 'unclaimed'.strtolower(\Illuminate\Support\Str::random(6));
        $unclaimed->type = 'venue';
        $unclaimed->website = 'https://keep-or-clear.example';
        $unclaimed->save();

        // Programmatic callers never set the flag -> filled() semantics, a
        // blank never wipes shared venue data.
        $this->postCreateEvent($owner, $role, [
            'venue_id' => UrlUtils::encodeId($unclaimed->id),
            'venue_name' => 'Unclaimed Venue',
            'venue_website' => '',
        ])->assertRedirect();

        $this->assertSame('https://keep-or-clear.example', $unclaimed->fresh()->website);
    }

    public function test_claim_venue_ownership_sets_owner_and_default_role(): void
    {
        // BUG-001 only manifested on hosted (the Role::updating hook is app.hosted-gated);
        // pin hosted so this guards the fix regardless of the ambient IS_HOSTED env.
        config(['app.hosted' => true]);

        \Illuminate\Support\Facades\Notification::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $owner->forceFill(['default_role_id' => null])->save();

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Claimed Hall',
            'claim_venue_ownership' => '1',
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Claimed Hall')->firstOrFail();

        // Auth user's contact overwrites the parsed venue contact.
        $this->assertSame($owner->id, $venue->user_id);
        $this->assertSame($owner->email, $venue->email);

        // BUG-001 fixed: the claim block saves quietly, so the copied verification
        // survives (isClaimed() true) and no redundant verify-email is sent - even on
        // hosted, where the Role::updating hook would otherwise null email_verified_at
        // on the now-dirty email.
        $this->assertNotNull($venue->email_verified_at);
        $this->assertTrue($venue->isClaimed());
        \Illuminate\Support\Facades\Notification::assertNotSentTo(
            $venue,
            \App\Notifications\VerifyEmail::class
        );

        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'owner',
        ]);

        $this->assertSame($venue->id, $owner->fresh()->default_role_id);
    }

    public function test_new_venue_email_matching_user_auto_assigns_ownership(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $venueOwner = $this->createOwner();
        $venueOwner->forceFill(['default_role_id' => null])->save();

        $this->postCreateEvent($owner, $role, [
            'venue_name' => 'Matched Hall',
            'venue_email' => $venueOwner->email,
        ])->assertRedirect();

        $venue = Role::where('type', 'venue')->where('name', 'Matched Hall')->firstOrFail();

        $this->assertSame($venueOwner->id, $venue->user_id);
        $this->assertNotNull($venue->email_verified_at);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $venueOwner->id,
            'level' => 'owner',
        ]);
        $this->assertSame($venue->id, $venueOwner->fresh()->default_role_id);

        // The event creator still follows the venue (owner is someone else).
        $this->assertDatabaseHas('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);
    }

    public function test_follow_new_roles_false_attaches_no_follower(): void
    {
        // The Eventbrite/WhatsApp import path: synthesized Request +
        // $followNewRoles=false, called directly on the repo.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $request = new Request($this->eventPayload([
            'venue_name' => 'Import Hall',
        ]));
        $request->setUserResolver(fn () => $owner);

        app(EventRepo::class)->saveEvent($role, $request, null, false);

        $venue = Role::where('type', 'venue')->where('name', 'Import Hall')->firstOrFail();

        $this->assertDatabaseMissing('role_user', [
            'role_id' => $venue->id,
            'user_id' => $owner->id,
        ]);
    }
}
