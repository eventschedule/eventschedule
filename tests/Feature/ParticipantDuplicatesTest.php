<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    }

    public function test_the_same_name_twice_in_one_save_is_one_participant(): void
    {
        [$owner, $curator] = $this->curator();
        $this->stubTalent('Bobcats');
        $this->followRole($owner, Role::where('name', 'Bobcats')->first());

        $this->postCreateEvent($owner, $curator, [
            'members' => ['new_a' => ['name' => 'Bobcats'], 'new_b' => ['name' => 'Bobcats']],
        ])->assertRedirect();

        $this->assertSame(1, Role::where('type', 'talent')->where('name', 'Bobcats')->count());
        $this->assertSame(1, $this->latestEvent()->roles->where('type', 'talent')->count());
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
