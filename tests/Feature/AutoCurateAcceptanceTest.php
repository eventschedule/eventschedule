<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::autoCurateEvent() used to decide purely on the curator's require_approval, so an
 * import queued a request against a curator the importer owns - one they could only ever have
 * approved themselves. It now runs the same acceptance rule as every other attach.
 */
class AutoCurateAcceptanceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function importInto($user, $role): void
    {
        $this->actingAs($user)->postJson(
            route('event.import', ['subdomain' => $role->subdomain]),
            [
                'name' => 'Imported Event',
                'starts_at' => '2026-08-15 20:00:00',
                'duration' => 2,
                'schedule_type' => 'one_time',
            ]
        )->assertOk();
    }

    private function curatorPivot($curator): ?object
    {
        return DB::table('event_role')
            ->where('event_id', Event::query()->orderByDesc('id')->firstOrFail()->id)
            ->where('role_id', $curator->id)
            ->first();
    }

    public function test_default_curator_the_importer_owns_is_accepted(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, [
            'accept_requests' => true,
            'require_approval' => true,
        ]);
        $venue = $this->createRole($owner, 'venue', [
            'default_curator_ids' => [$curator->id],
        ]);

        $this->importInto($owner, $venue);

        $this->assertSame(1, (int) $this->curatorPivot($curator)->is_accepted);
    }

    public function test_default_curator_owned_by_someone_else_still_requires_approval(): void
    {
        $owner = $this->createOwner();
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, [
            'accept_requests' => true,
            'require_approval' => true,
        ]);
        // Only a follower, which is how allCurators() offers a third-party curator.
        $this->followRole($owner, $curator);

        $venue = $this->createRole($owner, 'venue', [
            'default_curator_ids' => [$curator->id],
        ]);

        $this->importInto($owner, $venue);

        $this->assertNull($this->curatorPivot($curator)->is_accepted);
    }
}
