<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Covers the four-state event visibility model (issue #98):
 * Public / Draft / Internal / Unlisted, stored across is_draft + is_private
 * + is_internal, plus the per-schedule default_event_visibility.
 */
class EventVisibilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_visibility_state_maps_to_columns_both_ways(): void
    {
        $event = new Event;

        foreach (['public', 'draft', 'internal', 'unlisted'] as $state) {
            $event->setVisibilityState($state);
            $this->assertSame($state, $event->visibilityState(), "round-trip failed for {$state}");
        }

        // Internal implies draft (reuses the hide-from-public gate) and excludes unlisted.
        $event->setVisibilityState('internal');
        $this->assertTrue($event->is_draft);
        $this->assertTrue($event->is_internal);
        $this->assertFalse($event->is_private);

        // Unlisted implies private but not draft.
        $event->setVisibilityState('unlisted');
        $this->assertTrue($event->is_private);
        $this->assertFalse($event->is_draft);
        $this->assertFalse($event->is_internal);

        // Public clears everything.
        $event->setVisibilityState('public');
        $this->assertFalse($event->is_draft);
        $this->assertFalse($event->is_private);
        $this->assertFalse($event->is_internal);
    }

    public function test_default_event_visibility_clamps_for_non_enterprise(): void
    {
        // Plan gates only apply when hosted; force it on so isEnterprise() is real.
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $free = $this->createRole($owner, 'talent', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
            'default_event_visibility' => 'internal',
        ]);
        $this->assertFalse($free->isEnterprise());
        // Internal clamps to draft (stays hidden) for non-Enterprise.
        $this->assertSame('draft', $free->defaultEventVisibility());

        $free->default_event_visibility = 'unlisted';
        $this->assertSame('public', $free->defaultEventVisibility());

        $free->default_event_visibility = 'draft';
        $this->assertSame('draft', $free->defaultEventVisibility());
    }

    public function test_default_event_visibility_passthrough_for_enterprise(): void
    {
        $owner = $this->createOwner();
        // createRole defaults to the enterprise plan; the suite is non-hosted so isEnterprise() is true.
        $role = $this->createRole($owner, 'talent', ['default_event_visibility' => 'internal']);

        $this->assertTrue($role->isEnterprise());
        $this->assertSame('internal', $role->defaultEventVisibility());
    }

    public function test_saving_internal_event_forces_draft_and_clears_private(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // Craft an incoherent payload: internal + private + password. The invariant must win.
        $this->postCreateEvent($owner, $role, [
            'is_internal' => '1',
            'is_private' => '1',
            'event_password' => 'secret',
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_internal' => 1,
            'is_draft' => 1,
            'is_private' => 0,
        ]);
        $this->assertNull($event->fresh()->event_password);
    }

    public function test_saving_unlisted_event_keeps_private_without_draft(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, ['is_private' => '1'])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_private' => 1,
            'is_draft' => 0,
            'is_internal' => 0,
        ]);
    }

    public function test_event_with_no_visibility_fields_is_public(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role)->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'is_draft' => 0,
            'is_private' => 0,
            'is_internal' => 0,
        ]);
    }

    public function test_internal_event_hidden_from_guest_but_visible_to_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, [
            'is_draft' => true,
            'is_internal' => true,
            'name' => 'Secret Internal Gala XYZ',
        ]);

        // A guest (unauthenticated) must never see the internal event.
        $this->get($this->guestEventUrl($role, $event))
            ->assertDontSee('Secret Internal Gala XYZ');

        // The owning member can see it, with the internal banner.
        $this->actingAs($owner)->get($this->guestEventUrl($role, $event))
            ->assertOk()
            ->assertSee('Secret Internal Gala XYZ')
            ->assertSee(__('messages.event_is_internal'));
    }

    public function test_event_form_renders_visibility_selector(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // Renders the full event form with real data - catches runtime Blade errors
        // in the new visibility panel that view:cache compilation cannot.
        $this->actingAs($owner)->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->assertSee(__('messages.visibility'))
            ->assertSee(__('messages.visibility_internal_desc'))
            ->assertSee(__('messages.visibility_unlisted_desc'));
    }

    public function test_non_enterprise_internal_request_degrades_to_draft_not_public(): void
    {
        // Plan gates only apply when hosted. A Pro (non-Enterprise) account can use the API but
        // cannot use Internal; requesting it must degrade to a hidden Draft, never flip to Public.
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);
        $this->assertTrue($role->isPro());
        $this->assertFalse($role->isEnterprise());

        $this->withHeaders($this->apiHeaders($owner))
            ->postJson("/api/events/{$role->subdomain}", [
                'name' => 'API Internal Attempt',
                'starts_at' => '2026-08-15 20:00:00',
                'duration' => 2,
                'is_internal' => true,
            ])->assertSuccessful();

        $event = Event::where('name', 'API Internal Attempt')->firstOrFail();
        $this->assertTrue($event->is_draft, 'internal request must stay hidden as a draft');
        $this->assertFalse($event->is_internal);
        $this->assertFalse($event->is_private);
    }

    public function test_non_enterprise_unlisted_request_degrades_to_draft_not_public(): void
    {
        // Unlisted is Enterprise-only. When a schedule loses Enterprise, editing a formerly-Unlisted
        // event must NOT flip it fully Public - it must stay hidden as a Draft (same gate as create).
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);
        $this->assertFalse($role->isEnterprise());

        $this->withHeaders($this->apiHeaders($owner))
            ->postJson("/api/events/{$role->subdomain}", [
                'name' => 'API Unlisted Attempt',
                'starts_at' => '2026-08-17 20:00:00',
                'duration' => 2,
                'is_private' => true,
            ])->assertSuccessful();

        $event = Event::where('name', 'API Unlisted Attempt')->firstOrFail();
        $this->assertTrue($event->is_draft, 'unlisted request must stay hidden as a draft, not become public');
        $this->assertFalse($event->is_private);
        $this->assertFalse($event->is_internal);
    }

    public function test_api_seeds_draft_default_even_when_a_false_visibility_flag_is_sent(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['default_event_visibility' => 'draft']);

        // is_private=false is present-but-false; it must NOT defeat the schedule's draft default.
        $this->withHeaders($this->apiHeaders($owner))
            ->postJson("/api/events/{$role->subdomain}", [
                'name' => 'API Draft Default',
                'starts_at' => '2026-08-16 20:00:00',
                'duration' => 2,
                'is_private' => false,
            ])->assertSuccessful();

        $event = Event::where('name', 'API Draft Default')->firstOrFail();
        $this->assertTrue($event->is_draft, 'draft default must be seeded when is_draft is omitted');
    }

    public function test_making_an_unlisted_event_public_clears_its_password(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // Unlisted event with a password keeps the password.
        $this->postCreateEvent($owner, $role, ['is_private' => '1', 'event_password' => 'secret'])->assertRedirect();
        $event = $this->latestEvent();
        $this->assertSame('secret', $event->fresh()->event_password);

        // Switching it to Public must null the now-meaningless password (else it stays password-gated).
        $this->putUpdateEvent($owner, $role, $event, ['is_draft' => '0', 'is_private' => '0', 'is_internal' => '0'])
            ->assertRedirect();
        $fresh = $event->fresh();
        $this->assertFalse($fresh->is_private);
        $this->assertNull($fresh->event_password);
    }

    public function test_publish_route_rejects_internal_events(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['is_draft' => true, 'is_internal' => true, 'name' => 'Internal Never Public']);

        $this->actingAs($owner)->post(route('event.publish', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertRedirect();

        // An internal event must not be publishable via the quick-publish route - it stays hidden.
        $fresh = $event->fresh();
        $this->assertTrue($fresh->is_draft);
        $this->assertTrue($fresh->is_internal);
    }

    public function test_publish_route_makes_a_draft_fully_public_and_clears_password(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        // A legacy incoherent draft+private+password event still shows the Publish button.
        $event = $this->createEvent($role, [
            'is_draft' => true,
            'is_private' => true,
            'event_password' => 'secret',
            'name' => 'Draft Private Legacy',
        ]);

        $this->actingAs($owner)->post(route('event.publish', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertRedirect();

        $fresh = $event->fresh();
        $this->assertFalse($fresh->is_draft);
        $this->assertFalse($fresh->is_private);
        $this->assertFalse($fresh->is_internal);
        $this->assertNull($fresh->event_password);
    }

    public function test_api_explicit_unlisted_is_not_overridden_by_draft_default(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['default_event_visibility' => 'draft']);

        // Caller explicitly requests Unlisted (is_private=true) but omits is_draft; the draft default
        // must NOT be forced on top (no incoherent draft+private).
        $this->withHeaders($this->apiHeaders($owner))
            ->postJson("/api/events/{$role->subdomain}", [
                'name' => 'API Explicit Unlisted',
                'starts_at' => '2026-08-17 20:00:00',
                'duration' => 2,
                'is_private' => true,
            ])->assertSuccessful();

        $event = Event::where('name', 'API Explicit Unlisted')->firstOrFail();
        $this->assertTrue($event->is_private);
        $this->assertFalse($event->is_draft);
    }

    public function test_carpool_page_hides_internal_events_from_guests(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['carpool_enabled' => true]);
        $event = $this->createEvent($role, ['is_draft' => true, 'is_internal' => true, 'name' => 'Carpool Internal']);

        $url = route('carpool.index', [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
        ]);

        // A guest must not reach the carpool page for an internal (members-only) event.
        $this->get($url)->assertNotFound();

        // The owning member can.
        $this->actingAs($owner)->get($url)->assertOk();
    }

    public function test_unlisted_past_events_do_not_leak_into_public_graphic_view(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // Past events only, so the current calendar month has NO events - this is the condition that
        // used to skip the private-event filter and leak Unlisted past events into the graphic view.
        $past = now()->subMonths(2)->setTime(12, 0)->format('Y-m-d H:i:s');
        $this->createEvent($role, ['name' => 'Secret Investor Dinner', 'is_private' => 1, 'starts_at' => $past]);
        $this->createEvent($role, ['name' => 'Public Past Show', 'starts_at' => $past]);

        // An unauthenticated guest requesting graphic mode must not receive the Unlisted event's data.
        $response = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]).'?graphic=1');
        $response->assertOk();
        $response->assertDontSee('Secret Investor Dinner');
        // A public past event still appears (the filter must not over-remove).
        $response->assertSee('Public Past Show');
    }

    /** Set an API key on the user and return the X-API-Key auth header. */
    private function apiHeaders(User $user): array
    {
        $rawKey = 'k_'.Str::random(40);
        $user->api_key = substr(hash('sha256', $rawKey), 0, 8);
        $user->api_key_hash = Hash::make($rawKey);
        $user->save();

        return ['X-API-Key' => $rawKey, 'Accept' => 'application/json'];
    }
}
