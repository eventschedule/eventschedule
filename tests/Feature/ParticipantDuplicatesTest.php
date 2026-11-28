<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A curator who adds a team as "Someone new" for every game used to get one placeholder schedule
 * per game (team-name, team-name2, ...). A save now lands on the one the person already has
 * (EventRepo::existingParticipantNamed()), and the copies already made are offered on the
 * account-wide merge page for participants (/following/merge-participants).
 */
class ParticipantDuplicatesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** An unclaimed placeholder, as saveEvent() makes one for a typed-in participant. */
    private function stubTalent(string $name, array $attrs = []): Role
    {
        $role = new Role;
        $role->subdomain = 'stub'.strtolower(Str::random(10));
        $role->type = 'talent';
        $role->name = $name;
        $role->country_code = 'us';

        foreach ($attrs as $key => $value) {
            $role->{$key} = $value;
        }

        $role->save();

        return $role->fresh();
    }

    private function curator()
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['name' => 'Town Calendar', 'country_code' => 'us']);

        return [$owner, $curator];
    }

    private function addGame($owner, Role $curator, array $member, string $name = 'Game')
    {
        return $this->postCreateEvent($owner, $curator, [
            'name' => $name,
            'members' => ['new_'.Str::random(6) => $member],
        ])->assertRedirect();
    }

    public function test_a_name_typed_as_someone_new_twice_makes_one_schedule(): void
    {
        [$owner, $curator] = $this->curator();

        $this->addGame($owner, $curator, ['name' => 'Childress Lady Cats'], 'Lady Cats vs Clarendon');
        $first = $this->latestEvent();
        $this->addGame($owner, $curator, ['name' => 'childress lady cats '], 'Lady Cats vs Quanah');
        $second = $this->latestEvent();

        $teams = Role::where('type', 'talent')->where('name_normalized', 'childress lady cats')->get();
        $this->assertCount(1, $teams);
        $this->assertTrue($first->roles->contains('id', $teams[0]->id));
        $this->assertTrue($second->roles->contains('id', $teams[0]->id));
        $entries = DB::table('backstage_entries')->where('schedule_id', $curator->id)->where('participant_id', $teams[0]->id);
        $this->assertSame(1, $entries->count());
        $this->get(route('role.view_admin', [$curator->subdomain, 'participants']))->assertOk()
            ->assertSee('entry='.\App\Utils\UrlUtils::encodeId($entries->value('id')), false);
    }

    public function test_the_same_name_twice_in_one_save_is_one_participant(): void
    {
        [$owner, $curator] = $this->curator();

        // Nothing by that name yet: the first row makes the schedule, the second lands on it.
        $this->postCreateEvent($owner, $curator, [
            'members' => ['new_a' => ['name' => 'Bobcats'], 'new_b' => ['name' => 'Bobcats']],
        ])->assertRedirect();

        $bobcats = Role::where('type', 'talent')->where('name', 'Bobcats')->get();
        $this->assertCount(1, $bobcats);
        $this->assertSame(1, DB::table('event_role')
            ->where('event_id', $this->latestEvent()->id)
            ->where('role_id', $bobcats[0]->id)
            ->count());
    }

    public function test_an_anonymous_guest_submission_does_not_land_on_the_owners_schedules(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', [
            'country_code' => 'us',
            'accept_requests' => true,
            'require_account' => false,
        ]);
        $placeholder = $this->stubTalent('Childress Lady Cats');
        $this->followRole($owner, $placeholder);

        $this->postJson(route('event.guest_import.store', ['subdomain' => $curator->subdomain]), [
            'name' => 'Stranger Game',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'members' => ['new_talent_0' => ['name' => 'Childress Lady Cats']],
        ])->assertOk();

        $event = \App\Models\Event::where('name', 'Stranger Game')->firstOrFail();
        $this->assertTrue($event->is_guest_submission);
        $this->assertFalse($event->roles->contains('id', $placeholder->id));
        $this->assertTrue($event->roles->contains(fn ($role) => $role->type === 'talent' && $role->name === 'Childress Lady Cats'));
    }

    public function test_a_name_marked_not_duplicates_is_not_reused(): void
    {
        [$owner, $curator] = $this->curator();
        $a = $this->stubTalent('DJ Sam');
        $b = $this->stubTalent('DJ Sam');
        $this->followRole($owner, $a);
        $this->followRole($owner, $b);

        $this->actingAs($owner)->post(route('following.merge_participants_dismiss'), [
            'venue_ids' => [UrlUtils::encodeId($a->id), UrlUtils::encodeId($b->id)],
        ])->assertRedirect();

        $this->addGame($owner, $curator, ['name' => 'DJ Sam']);

        $this->assertSame(3, Role::where('type', 'talent')->where('name', 'DJ Sam')->count());
        $this->assertFalse($this->latestEvent()->roles->contains('id', $a->id));
        $this->assertFalse($this->latestEvent()->roles->contains('id', $b->id));

        // And on every save after it: the schedule each save makes joins the dismissal, so the
        // group never comes back to the merge page and no save picks one of them.
        $this->addGame($owner, $curator, ['name' => 'DJ Sam']);

        $this->assertSame(4, Role::where('type', 'talent')->where('name', 'DJ Sam')->count());
        $this->assertCount(0, $this->get(route('following.merge_participants'))->viewData('groups'));
    }

    public function test_not_duplicates_is_about_every_schedule_of_the_name_not_only_those_shown(): void
    {
        [$owner, $curator] = $this->curator();
        $real = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Rovers', 'country_code' => 'us']);
        $copyA = $this->stubTalent('The Rovers');
        $copyB = $this->stubTalent('The Rovers');
        foreach ([$real, $copyA, $copyB] as $role) {
            $this->followRole($owner, $role);
        }

        // The page shows the two copies (the real page is someone else's); the post names those.
        $this->actingAs($owner)->post(route('following.merge_participants_dismiss'), [
            'venue_ids' => [UrlUtils::encodeId($copyA->id), UrlUtils::encodeId($copyB->id)],
        ])->assertRedirect(route('following.merge_participants'));

        $this->addGame($owner, $curator, ['name' => 'The Rovers']);

        $this->assertFalse($this->latestEvent()->roles->contains('id', $copyA->id));
        $this->assertFalse($this->latestEvent()->roles->contains('id', $copyB->id));
    }

    public function test_a_source_claimed_after_the_page_was_drawn_is_skipped_not_refused(): void
    {
        [$owner] = $this->curator();
        $stubs = collect(range(1, 3))->map(function () use ($owner) {
            $stub = $this->stubTalent('Lady Cats');
            $this->followRole($owner, $stub);

            return $stub;
        });
        $this->actingAs($owner);
        $this->assertCount(3, $this->get(route('following.merge_participants'))->viewData('groups')[0]);

        // Between render and submit, somebody claims one of the sources.
        $claimant = $this->createOwner();
        $claimed = $stubs[2];
        $claimed->user_id = $claimant->id;
        $claimed->email = 'lady.cats.coach@gmail.com';
        $claimed->email_verified_at = now();
        $claimed->save();
        $claimant->roles()->attach($claimed->id, ['level' => 'owner', 'created_at' => now()]);

        $this->post(route('following.merge_participants_group'), [
            'target_id' => UrlUtils::encodeId($stubs[0]->id),
            'source_ids' => [UrlUtils::encodeId($stubs[1]->id), UrlUtils::encodeId($claimed->id)],
        ])->assertRedirect()->assertSessionHas('message');

        $this->assertDatabaseHas('roles', ['id' => $stubs[1]->id, 'is_deleted' => true]);
        $this->assertDatabaseHas('roles', ['id' => $claimed->id, 'is_deleted' => false]);
    }

    public function test_a_placeholder_in_another_country_is_not_reused(): void
    {
        [$owner, $curator] = $this->curator();
        $elsewhere = $this->stubTalent('Lions', ['country_code' => 'ca']);
        $this->followRole($owner, $elsewhere);

        $this->addGame($owner, $curator, ['name' => 'Lions']);

        $this->assertFalse($this->latestEvent()->roles->contains('id', $elsewhere->id));
    }

    public function test_with_no_country_on_either_side_the_name_is_enough(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['country_code' => null]);
        $placeholder = $this->stubTalent('Lions', ['country_code' => null]);
        $this->followRole($owner, $placeholder);

        $this->addGame($owner, $curator, ['name' => 'Lions']);

        $this->assertTrue($this->latestEvent()->roles->contains('id', $placeholder->id));
    }

    public function test_a_deleted_placeholder_is_not_reused(): void
    {
        [$owner, $curator] = $this->curator();
        $gone = $this->stubTalent('Lions', ['is_deleted' => true]);
        $this->followRole($owner, $gone);

        $this->addGame($owner, $curator, ['name' => 'Lions']);

        $this->assertFalse($this->latestEvent()->roles->contains('id', $gone->id));
        $this->assertSame(1, Role::where('name', 'Lions')->where('is_deleted', false)->count());
    }

    public function test_retyping_an_attached_participant_on_an_edit_keeps_it_on_the_event(): void
    {
        [$owner, $curator] = $this->curator();
        $band = $this->createRole($owner, 'talent', ['name' => 'My Band', 'country_code' => 'us']);

        $this->postCreateEvent($owner, $curator, [
            'members' => [UrlUtils::encodeId($band->id) => ['name' => 'My Band']],
        ])->assertRedirect();
        $event = $this->latestEvent();
        $this->assertTrue($event->roles->contains('id', $band->id));

        // The chip removed and the same name typed in again as "Someone new".
        $this->putUpdateEvent($owner, $curator, $event, [
            'members_submitted' => 1,
            'members' => ['new_again' => ['name' => 'My Band']],
        ])->assertRedirect();

        $this->assertTrue($event->fresh()->roles->contains('id', $band->id));
        $this->assertSame(1, Role::where('type', 'talent')->where('name', 'My Band')->count());
    }

    public function test_a_row_with_an_email_still_makes_its_own_schedule(): void
    {
        [$owner, $curator] = $this->curator();

        $this->addGame($owner, $curator, ['name' => 'DJ Sam']);
        $this->addGame($owner, $curator, ['name' => 'DJ Sam', 'email' => 'another.dj.sam@gmail.com']);

        $this->assertSame(2, Role::where('type', 'talent')->where('name', 'DJ Sam')->count());
    }

    public function test_somebody_elses_page_the_curator_follows_is_not_taken_over(): void
    {
        [$owner, $curator] = $this->curator();
        $other = $this->createOwner();
        $theirs = $this->createRole($other, 'talent', ['name' => 'The Rovers', 'country_code' => 'us']);
        $this->followRole($owner, $theirs);

        $this->addGame($owner, $curator, ['name' => 'The Rovers']);

        $this->assertSame(2, Role::where('type', 'talent')->where('name', 'The Rovers')->count());
        $this->assertFalse($this->latestEvent()->roles->contains('id', $theirs->id));
    }

    public function test_a_placeholder_the_person_is_not_connected_to_is_not_reused(): void
    {
        [$owner, $curator] = $this->curator();
        $strangers = $this->stubTalent('Lone Act');

        $this->addGame($owner, $curator, ['name' => 'Lone Act']);

        $this->assertSame(2, Role::where('type', 'talent')->where('name', 'Lone Act')->count());
        $this->assertFalse($this->latestEvent()->roles->contains('id', $strangers->id));
    }

    public function test_the_merge_page_offers_the_copies_and_merges_them(): void
    {
        [$owner, $curator] = $this->curator();
        $copies = collect(range(1, 3))->map(function () use ($owner, $curator) {
            $stub = $this->stubTalent('Childress Lady Cats');
            $this->followRole($owner, $stub);
            $event = $this->createEvent($curator);
            $event->roles()->attach($stub->id, ['is_accepted' => true]);

            return [$stub, $event];
        });

        $this->actingAs($owner);
        $this->assertSame(3, $this->get(route('following'))->viewData('duplicateParticipantCount'));

        $response = $this->get(route('following.merge_participants'))->assertOk()
            ->assertSee(__('messages.merge_participants_title'));
        $groups = $response->viewData('groups');
        $this->assertCount(1, $groups);
        $this->assertCount(3, $groups[0]);

        [$target, $targetEvent] = $copies[0];
        $sources = $copies->slice(1);

        $this->post(route('following.merge_participants_group'), [
            'target_id' => UrlUtils::encodeId($target->id),
            'source_ids' => $sources->map(fn ($pair) => UrlUtils::encodeId($pair[0]->id))->values()->all(),
        ])->assertRedirect(route('following'));

        foreach ($sources as [$source, $event]) {
            $this->assertDatabaseHas('roles', ['id' => $source->id, 'is_deleted' => true]);
            $this->assertTrue($event->fresh()->roles->contains('id', $target->id));
        }
        $this->assertSame(0, $this->get(route('following'))->viewData('duplicateParticipantCount'));
    }

    public function test_a_followed_real_page_does_not_hide_the_copies(): void
    {
        [$owner] = $this->curator();
        $real = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Rovers', 'country_code' => 'us']);
        $this->followRole($owner, $real);
        $copyA = $this->stubTalent('The Rovers');
        $copyB = $this->stubTalent('The Rovers');
        $this->followRole($owner, $copyA);
        $this->followRole($owner, $copyB);

        $groups = $this->actingAs($owner)->get(route('following.merge_participants'))->viewData('groups');

        $this->assertCount(1, $groups);
        $this->assertEqualsCanonicalizing([$copyA->id, $copyB->id], array_map(fn ($r) => $r->id, $groups[0]));
    }

    public function test_a_page_somebody_runs_is_not_offered_even_when_its_address_is_unverified(): void
    {
        [$owner] = $this->curator();
        // Owned, but the contact is not verified: isClaimed() is false, hasRealOwner() is true.
        $theirs = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'The Rovers', 'country_code' => 'us', 'email_verified_at' => null,
        ]);
        $this->followRole($owner, $theirs);
        $this->followRole($owner, $this->stubTalent('The Rovers'));

        $this->actingAs($owner);
        $this->assertCount(0, $this->get(route('following.merge_participants'))->viewData('groups'));
        $this->assertSame(0, $this->get(route('following'))->viewData('duplicateParticipantCount'));
    }

    public function test_names_are_compared_whole_not_by_their_first_comma_segment(): void
    {
        [$owner] = $this->curator();
        $this->followRole($owner, $this->stubTalent('Earth, Wind & Fire'));
        $this->followRole($owner, $this->stubTalent('Earth'));

        $this->assertCount(0, $this->actingAs($owner)->get(route('following.merge_participants'))->viewData('groups'));
    }

    public function test_the_participant_page_never_offers_venues_and_the_venue_page_never_participants(): void
    {
        [$owner] = $this->curator();
        $this->followRole($owner, $this->stubTalent('Same Name'));
        $this->followRole($owner, $this->stubTalent('Same Name'));

        $this->actingAs($owner);
        $this->assertCount(1, $this->get(route('following.merge_participants'))->viewData('groups'));
        $this->assertCount(0, $this->get(route('following.merge_venues'))->viewData('groups'));

        // A participant id posted to the venue page's merge is refused.
        $ids = Role::where('name', 'Same Name')->pluck('id');
        $this->post(route('following.merge_venues_group'), [
            'target_id' => UrlUtils::encodeId($ids[0]),
            'source_ids' => [UrlUtils::encodeId($ids[1])],
        ]);
        $this->assertSame(0, Role::where('name', 'Same Name')->where('is_deleted', true)->count());
    }

    public function test_not_duplicates_silences_the_group(): void
    {
        [$owner] = $this->curator();
        $a = $this->stubTalent('Twin Names');
        $b = $this->stubTalent('Twin Names');
        $this->followRole($owner, $a);
        $this->followRole($owner, $b);

        $this->actingAs($owner)->post(route('following.merge_participants_dismiss'), [
            'venue_ids' => [UrlUtils::encodeId($a->id), UrlUtils::encodeId($b->id)],
        ])->assertRedirect(route('following.merge_participants'));

        $this->assertCount(0, $this->get(route('following.merge_participants'))->viewData('groups'));
        $this->assertSame(0, $this->get(route('following'))->viewData('duplicateParticipantCount'));
    }
}
