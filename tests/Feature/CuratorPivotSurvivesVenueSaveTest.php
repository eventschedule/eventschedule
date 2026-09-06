<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Pins a NON-defect, so it stops being re-diagnosed.
 *
 * "A venue editing its event re-pends the curator's event_role row" is the obvious explanation for
 * an event that drops out of a curator's /analytics picker and comes back later, and it is wrong.
 * EventRepo::save() ends with $event->roles()->sync($roleIds), and BelongsToMany::sync() called
 * with a plain id list only DETACHES ids absent from the list - it never rewrites pivot data on a
 * row that stays, because no pivot attributes were supplied. On top of that, saveEvent() copies
 * every attachment the saving user cannot see into $roleIds first ("Preserve attachments the user
 * has no visibility into"), so a cross-user curator is not even a detach candidate.
 *
 * If this test ever fails, that reasoning is wrong and the curator pivot becomes the prime suspect
 * for the missing-from-analytics reports again.
 */
class CuratorPivotSurvivesVenueSaveTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_a_venue_saving_its_event_keeps_a_cross_user_curators_acceptance(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['name' => 'Ba-Be Bar']);

        // A DIFFERENT user's curator: the shape in the report, and the one the preservation
        // branch exists for. The venue owner cannot see it in the schedules tab.
        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner, ['name' => 'Emek Live']);

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // No curators[] and no curators_submitted, exactly as the venue's own form posts.
        $this->putUpdateEvent($venueOwner, $venue, $event, ['name' => 'Renamed By The Venue'])
            ->assertRedirect();

        $this->assertDatabaseHas('event_role', [
            'event_id' => $event->id,
            'role_id' => $curator->id,
            'is_accepted' => 1,
        ]);

        // The two surfaces that read that pivot must still see it.
        $this->assertTrue(
            app(AnalyticsService::class)->getEventsForSchedule($curator->id, false)
                ->contains('raw_id', $event->id),
            'The event left the curator analytics picker after the venue saved it.'
        );

        $this->get('/'.$curator->subdomain.'/'.$event->fresh()->slug)->assertOk();
    }
}
