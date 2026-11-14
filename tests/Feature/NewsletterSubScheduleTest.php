<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An Events block narrowed to one sub-schedule: a venue that hosts poker every week and music on
 * other nights mails its music season without the poker (support request, 2026-11).
 */
class NewsletterSubScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_block_lists_only_its_sub_schedules_events(): void
    {
        [$role, $music] = $this->venueWithMusic();

        $this->assertSame(['Jazz trio', 'Folk night'], $this->listed($role, 'all', UrlUtils::encodeId($music->id)));
        $this->assertSame(['Jazz trio', 'Poker', 'Folk night'], $this->listed($role, 'all', ''), 'no sub-schedule is every event, as before');
        $this->assertSame(['Jazz trio'], $this->listed($role, 'next_7_days', UrlUtils::encodeId($music->id)), 'with a date range too');
    }

    public function test_a_sub_schedule_that_is_gone_or_another_schedules_lists_nothing(): void
    {
        [$role, $music] = $this->venueWithMusic();
        $elsewhere = Group::forceCreate(['role_id' => $this->createRole($this->createOwner(), 'venue')->id, 'name' => 'Theirs', 'slug' => 'theirs']);

        $this->assertSame([], $this->listed($role, 'all', UrlUtils::encodeId($elsewhere->id)));

        $gone = UrlUtils::encodeId($music->id);
        $music->delete();
        $this->assertSame([], $this->listed($role, 'all', $gone), 'never everything else in its place');
    }

    public function test_the_builder_is_given_the_schedules_sub_schedules(): void
    {
        [$role, $music] = $this->venueWithMusic();

        $this->actingAs($role->user)->get(route('newsletter.create', ['role_id' => UrlUtils::encodeId($role->id)]))
            ->assertOk()
            ->assertSee(UrlUtils::encodeId($music->id))
            ->assertSee(__('messages.newsletter_events_all_sub_schedules'));
    }

    /** @return array{0: Role, 1: Group} */
    private function venueWithMusic(): array
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $music = Group::forceCreate(['role_id' => $role->id, 'name' => 'Music', 'slug' => 'music']);

        $this->event($role, 'Jazz trio', 2, $music);
        $this->event($role, 'Poker', 3, null);
        $this->event($role, 'Folk night', 12, $music);

        return [$role, $music];
    }

    private function event(Role $role, string $name, int $inDays, ?Group $group): Event
    {
        $event = $this->createEvent($role, ['name' => $name, 'starts_at' => now()->addDays($inDays)->setTime(20, 0)->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);

        if ($group) {
            $event->roles()->updateExistingPivot($role->id, ['group_id' => $group->id]);
        }

        return $event;
    }

    /** @return array<int, string> */
    private function listed(Role $role, string $range, string $groupId): array
    {
        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'user_id' => $role->user_id,
            'subject' => 'Music',
            'status' => 'draft',
            'template' => 'modern',
            'type' => 'schedule',
            'blocks' => [['id' => 'e', 'type' => 'events', 'data' => ['layout' => 'list', 'useAllEvents' => true, 'range' => $range, 'groupId' => $groupId, 'eventIds' => []]]],
        ]);
        $newsletter->setRelation('role', $role);

        $block = collect(app(NewsletterService::class)->processBlocks($newsletter))->firstWhere('type', 'events');

        return collect($block['data']['resolvedEvents'])->pluck('name')->all();
    }
}
