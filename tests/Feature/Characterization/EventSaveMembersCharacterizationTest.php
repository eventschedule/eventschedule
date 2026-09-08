<?php

namespace Tests\Feature\Characterization;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the members[] section of EventRepo::saveEvent()
 * (REFACTOR_PLAN.md P11): new talent schedules, owner auto-match by email,
 * unclaimed-member updates, and the claimed-member no-touch rule.
 */
class EventSaveMembersCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_new_member_creates_talent_schedule_and_follows_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->postCreateEvent($owner, $role, [
            'members' => [
                'new_1' => ['name' => 'The Fresh Band', 'email' => '', 'phone' => ''],
            ],
        ])->assertRedirect();

        $talent = Role::where('type', 'talent')->where('name', 'The Fresh Band')->firstOrFail();

        // Creation defaults pinned: generated subdomain, schedule's timezone,
        // random gradient, white font, no owner, empty email stored as null.
        $this->assertNotEmpty($talent->subdomain);
        $this->assertSame('America/New_York', $talent->timezone);
        $this->assertNotEmpty($talent->background_colors);
        $this->assertSame('#ffffff', $talent->font_color);
        $this->assertNull($talent->user_id);
        $this->assertNull($talent->email);

        // Creator follows the new talent schedule.
        $this->assertDatabaseHas('role_user', [
            'role_id' => $talent->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);

        // Talent schedule is attached to the event, and auto-accepted: the placeholder has no
        // user_id, so nobody could ever accept on its behalf (Role::autoAcceptsEventFrom()).
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $talent->id,
            'is_accepted' => 1,
        ]);
    }

    public function test_new_member_email_matching_user_becomes_owner_not_follower(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $matched = $this->createOwner();
        $matched->forceFill(['default_role_id' => null])->save();

        $this->postCreateEvent($owner, $role, [
            'members' => [
                'new_1' => ['name' => 'Matched Act', 'email' => $matched->email],
            ],
        ])->assertRedirect();

        $talent = Role::where('type', 'talent')->where('name', 'Matched Act')->firstOrFail();

        // Auto-match by email: ownership + verified-at copy + default role.
        $this->assertSame($matched->id, $talent->user_id);
        $this->assertNotNull($talent->email_verified_at);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $talent->id,
            'user_id' => $matched->id,
            'level' => 'owner',
        ]);
        $this->assertSame($talent->id, $matched->fresh()->default_role_id);

        // Creator (a different user) still follows.
        $this->assertDatabaseHas('role_user', [
            'role_id' => $talent->id,
            'user_id' => $owner->id,
            'level' => 'follower',
        ]);
    }

    public function test_existing_unclaimed_member_is_updated_in_place(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $unclaimed = new Role;
        $unclaimed->name = 'Old Name';
        $unclaimed->subdomain = 'member'.strtolower(Str::random(6));
        $unclaimed->type = 'talent';
        $unclaimed->save();

        $this->postCreateEvent($owner, $role, [
            'members' => [
                UrlUtils::encodeId($unclaimed->id) => [
                    'name' => 'New Name',
                    'email' => 'member@gmail.com',
                ],
            ],
        ])->assertRedirect();

        $unclaimed->refresh();
        $this->assertSame('New Name', $unclaimed->name);
        $this->assertSame('member@gmail.com', $unclaimed->email);
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $unclaimed->id,
        ]);
    }

    public function test_a_stranger_can_rewrite_an_unclaimed_members_contact(): void
    {
        // CHARACTERIZES A KNOWN WEAKNESS, bug-for-bug, so it is not rediscovered as news.
        //
        // members[] is keyed by an encoded role id straight off the request and nothing ties the
        // sender to the row, so anyone who can edit any schedule can rewrite an unclaimed act's
        // contact - and /{subdomain}/claim then hands the page to whoever holds that address. An
        // isEditableBy() guard was tried and reverted: the legitimate action and the attack are the
        // same request, and the guard silently no-opped for teammates and killed the invitation.
        //
        // If this ever tightens, this test is the one to invert, and the venue side
        // (claimVenueOwnership, which grants ownership with no proof at all) must move with it.
        $stranger = $this->createOwner();
        $role = $this->createRole($stranger, 'venue');

        $unclaimed = new Role;
        $unclaimed->name = 'Taylor Swift';
        $unclaimed->email = 'booking@gmail.com';
        $unclaimed->subdomain = 'member'.strtolower(Str::random(6));
        $unclaimed->type = 'talent';
        $unclaimed->save();

        $this->postCreateEvent($stranger, $role, [
            'members' => [
                UrlUtils::encodeId($unclaimed->id) => [
                    'name' => 'Hijacked',
                    'email' => 'attacker@gmail.com',
                ],
            ],
        ])->assertRedirect();

        $unclaimed->refresh();
        $this->assertSame('Hijacked', $unclaimed->name);
        $this->assertSame('attacker@gmail.com', $unclaimed->email);
        $this->assertFalse($unclaimed->isEditableBy($stranger), 'and they have no relationship to the row');
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $unclaimed->id,
        ]);
    }

    public function test_claimed_member_is_never_updated(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $talentOwner = $this->createOwner();
        // createRole -> claimed: user_id + verified email + owner pivot.
        $claimed = $this->createRole($talentOwner, 'talent', [
            'name' => 'Claimed Act',
            'email' => 'claimed@gmail.com',
        ]);

        $this->postCreateEvent($owner, $role, [
            'members' => [
                UrlUtils::encodeId($claimed->id) => [
                    'name' => 'Hijacked Name',
                    'email' => 'evil@gmail.com',
                ],
            ],
        ])->assertRedirect();

        // Claimed schedules are read-only to other users' event forms.
        $claimed->refresh();
        $this->assertSame('Claimed Act', $claimed->name);
        $this->assertSame('claimed@gmail.com', $claimed->email);

        // But the schedule is still attached to the event.
        $this->assertDatabaseHas('event_role', [
            'event_id' => $this->latestEvent()->id,
            'role_id' => $claimed->id,
        ]);
    }
}
