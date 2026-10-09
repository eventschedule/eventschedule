<?php

namespace Tests\Feature;

use App\Models\NewsletterSegment;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a signed-in person can reach on a schedule that is not theirs.
 *
 *   - the event form's picture upload took a file from any signed-in account, at any schedule's
 *     address, and kept it on the server. It asks isEditor() and is limited;
 *   - the availability form wrote to "my row on this schedule" without asking whether there is
 *     one, and answered a stranger with a 500;
 *   - a newsletter segment stores the event id it was posted, and its edit page printed the name
 *     of whatever event that id was, a draft on somebody else's schedule included.
 */
class ReachBeyondOwnScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_stranger_cannot_upload_to_a_schedule_or_reach_its_availability(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $stranger = $this->createOwner();

        $this->actingAs($stranger)->post(route('event.upload_image', ['subdomain' => $role->subdomain]), ['image' => UploadedFile::fake()->image('a.jpg', 20, 20)])
            ->assertStatus(403);

        $status = $this->actingAs($stranger)->post(route('role.availability', ['subdomain' => $role->subdomain]), ['available_days' => '[]', 'unavailable_days' => '[]'])->status();
        $this->assertNotSame(500, $status);
    }

    public function test_a_segment_does_not_name_another_schedules_event(): void
    {
        $theirs = $this->createEvent($this->createRole($this->createOwner(), 'venue'), ['name' => 'Somebody Elses Private Night', 'is_draft' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $segment = NewsletterSegment::create(['role_id' => $role->id, 'name' => 'Buyers', 'type' => 'ticket_buyers', 'filter_criteria' => ['event_id' => $theirs->id]]);

        $html = $this->actingAs($owner)->get(route('newsletter.segment.edit', ['hash' => UrlUtils::encodeId($segment->id), 'role_id' => UrlUtils::encodeId($role->id)]))->getContent();

        $this->assertStringNotContainsString('Somebody Elses Private Night', $html);
    }
}
