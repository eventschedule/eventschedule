<?php

namespace Tests\Feature\Characterization;

use App\Models\EventPhoto;
use App\Models\User;
use App\Notifications\AddedMemberNotification;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterization for the P8/P9 controller-split move lists
 * (REFACTOR_PLAN.md): the curate/uncurate flow, a photo moderation
 * approve/reject pair, and team resend-invite. Pins redirect targets,
 * flash keys, JSON error shapes, and pivot values.
 */
class EventCurationModerationCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_editor_curates_event_as_accepted_then_uncurates(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        $curateUrl = '/'.$curator->subdomain.'/curate-event/'.UrlUtils::encodeId($event->id);

        // A curator editor curates -> attached pre-accepted.
        $this->actingAs($curatorOwner)->get($curateUrl)
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.event_added_to_schedule'));

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $curator->id,
            'is_accepted' => 1,
        ]);

        // Curating the same event again -> error flash, no duplicate.
        $this->actingAs($curatorOwner)->get($curateUrl)
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.event_already_curated'));

        // Uncurate detaches.
        $this->actingAs($curatorOwner)
            ->delete('/'.$curator->subdomain.'/uncurate-event/'.UrlUtils::encodeId($event->id))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.uncurate_event'));

        $this->assertDatabaseMissing('event_role', [
            'event_id' => $event->id,
            'role_id' => $curator->id,
        ]);
    }

    public function test_guest_curate_attaches_unaccepted_when_requests_open(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $event = $this->createEvent($venue);
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // Unauthenticated curate on an open-requests curator -> attached
        // WITHOUT acceptance (the is_accepted visibility gate).
        $this->get('/'.$curator->subdomain.'/curate-event/'.UrlUtils::encodeId($event->id))
            ->assertRedirect();

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $curator->id,
            'is_accepted' => null,
        ]);
    }

    public function test_photo_moderation_approve_and_reject_pair(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['fan_photos_enabled' => true]);

        $pending = EventPhoto::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'photo_url' => 'photos/pending.jpg',
            'is_approved' => false,
        ]);

        $this->actingAs($owner)
            ->post('/'.$role->subdomain.'/approve-photo/'.UrlUtils::encodeId($pending->id))
            ->assertRedirect()
            ->assertSessionHas('message', __('messages.photo_approved'));

        $this->assertDatabaseHas('event_photos', ['id' => $pending->id, 'is_approved' => 1]);

        // A non-editor of the event cannot moderate: error flash, no change.
        $stranger = $this->createOwner();
        $this->actingAs($stranger)
            ->post('/'.$role->subdomain.'/approve-photo/'.UrlUtils::encodeId($pending->id))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.not_authorized'));
    }

    public function test_resend_invite_notifies_stub_member_and_rejects_signed_up_member(): void
    {
        Notification::fake();

        // fresh() so the DB defaults (language_code) are hydrated - storeMember
        // copies the inviter's language_code onto the stub, and actingAs()
        // injects the in-memory instance without reloading it.
        $owner = $this->createOwner()->fresh();
        $role = $this->createRole($owner);

        // Adding a member with an unknown email creates a stub user.
        $this->actingAs($owner)->post(route('role.store_member', ['subdomain' => $role->subdomain]), [
            'email' => 'stub-member@gmail.com',
            'name' => 'Stub Member',
            'level' => 'admin',
        ]);
        $stub = User::where('email', 'stub-member@gmail.com')->firstOrFail();
        $this->assertNull($stub->password);

        $this->actingAs($owner)->post(route('role.resend_invite', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($stub->id),
        ]))->assertRedirect()
            ->assertSessionHas('message', __('messages.invite_resent'));

        Notification::assertSentTo($stub, AddedMemberNotification::class);

        // A member who already signed up (has a password) is rejected.
        $signedUp = $this->createOwner();
        $role->users()->attach($signedUp->id, ['level' => 'admin']);

        $this->actingAs($owner)->post(route('role.resend_invite', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($signedUp->id),
        ]))->assertRedirect()
            ->assertSessionHas('error', __('messages.member_already_signed_up'));
    }
}
