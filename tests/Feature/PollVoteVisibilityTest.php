<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPoll;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Who can vote on a poll, or add a choice to it, by the event's visibility.
 *
 * Voting used to gate an Unlisted event like a draft, so a signed-in guest who opened one from its
 * link saw the poll and got a 404 for every vote. Now Draft and Internal are members-only, a
 * password holds back everyone but members until it has been entered, an Unlisted event is open
 * to whoever has the link, and only after all that is an anonymous visitor asked to sign in.
 *
 * Anonymous requests come first in each test: actingAs() sticks for the rest of it.
 */
class PollVoteVisibilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_signed_in_guest_can_vote_on_an_unlisted_event(): void
    {
        [$role, $event, $poll] = $this->eventWithPoll(['is_private' => true]);
        $url = $this->voteUrl($role, $event, $poll);

        $this->postJson($url, ['option_index' => 1])
            ->assertStatus(401)
            ->assertJson(['error' => __('messages.sign_in_to_vote')]);

        $guest = $this->createOwner();

        $this->actingAs($guest)->postJson($url, ['option_index' => 1])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('event_poll_votes', [
            'event_poll_id' => $poll->id,
            'user_id' => $guest->id,
            'option_index' => 1,
        ]);
    }

    public function test_an_anonymous_visitor_on_a_public_event_is_asked_to_sign_in(): void
    {
        [$role, $event, $poll] = $this->eventWithPoll();

        $this->postJson($this->voteUrl($role, $event, $poll), ['option_index' => 0])
            ->assertStatus(401)
            ->assertJson(['error' => __('messages.sign_in_to_vote')]);

        $this->assertDatabaseCount('event_poll_votes', 0);
    }

    public function test_a_password_protected_event_takes_votes_only_once_the_password_is_entered(): void
    {
        [$role, $event, $poll] = $this->eventWithPoll(['is_private' => true, 'event_password' => 'letmein']);
        $url = $this->voteUrl($role, $event, $poll);
        $guest = $this->createOwner();

        $this->actingAs($guest)->postJson($url, ['option_index' => 1])->assertNotFound();
        $this->assertDatabaseCount('event_poll_votes', 0);

        $this->actingAs($guest)
            ->withSession(['event_password_'.$event->id => true])
            ->postJson($url, ['option_index' => 1])
            ->assertOk();

        $this->assertDatabaseHas('event_poll_votes', ['event_poll_id' => $poll->id, 'user_id' => $guest->id]);
    }

    public function test_a_draft_takes_votes_only_from_members(): void
    {
        [$role, $event, $poll] = $this->eventWithPoll(['is_draft' => true]);
        $url = $this->voteUrl($role, $event, $poll);

        // 404, not 401: an anonymous caller must not learn that a draft exists at this address.
        $this->postJson($url, ['option_index' => 0])->assertNotFound();

        $this->actingAs($this->createOwner())->postJson($url, ['option_index' => 0])->assertNotFound();
        $this->assertDatabaseCount('event_poll_votes', 0);

        $member = $this->createOwner();
        $role->users()->attach($member->id, ['level' => 'viewer']);

        $this->actingAs($member)->postJson($url, ['option_index' => 0])->assertOk();
        $this->assertDatabaseHas('event_poll_votes', ['event_poll_id' => $poll->id, 'user_id' => $member->id]);
    }

    public function test_suggesting_a_choice_is_gated_the_same_way(): void
    {
        [$role, $event, $poll] = $this->eventWithPoll(
            ['is_private' => true, 'event_password' => 'letmein'],
            ['allow_user_options' => true],
        );
        $url = $this->suggestUrl($role, $event, $poll);

        // The password gate runs before the sign-in check, so an anonymous caller gets a 404 too.
        $this->postJson($url, ['label' => 'Purple'])->assertNotFound();

        $guest = $this->createOwner();

        $this->actingAs($guest)->postJson($url, ['label' => 'Purple'])->assertNotFound();
        $this->assertSame(['Red', 'Blue', 'Green'], $poll->fresh()->options);

        $this->actingAs($guest)
            ->withSession(['event_password_'.$event->id => true])
            ->postJson($url, ['label' => 'Purple'])
            ->assertOk();

        $this->assertContains('Purple', $poll->fresh()->options);
    }

    /** @return array{0: Role, 1: Event, 2: EventPoll} */
    private function eventWithPoll(array $eventAttrs = [], array $pollAttrs = []): array
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, $eventAttrs);
        $poll = EventPoll::create(array_merge([
            'event_id' => $event->id,
            'question' => 'Favorite color?',
            'options' => ['Red', 'Blue', 'Green'],
            'is_active' => true,
        ], $pollAttrs));

        return [$role, $event, $poll];
    }

    private function voteUrl(Role $role, Event $event, EventPoll $poll): string
    {
        return route('event.vote_poll', $this->pollRouteParams($role, $event, $poll));
    }

    private function suggestUrl(Role $role, Event $event, EventPoll $poll): string
    {
        return route('event.suggest_poll_option', $this->pollRouteParams($role, $event, $poll));
    }

    private function pollRouteParams(Role $role, Event $event, EventPoll $poll): array
    {
        return [
            'subdomain' => $role->subdomain,
            'event_hash' => UrlUtils::encodeId($event->id),
            'poll_hash' => UrlUtils::encodeId($poll->id),
        ];
    }
}
