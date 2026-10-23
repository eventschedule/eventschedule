<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Per-order questions (an event's own custom_fields) are Pro.
 *
 * The event form shows their editor only on Pro and does not post them below it, but
 * EventRepo::saveEvent() stored whatever was posted, so a hand-made post set them on a Free
 * schedule (the API itself is refused below Pro on hosted). The plan that counts is the current
 * schedule's OR the event's own (as for its sponsors), and a request that does not carry the
 * field leaves the stored questions alone.
 */
class OrderFieldsPlanGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private const FIELDS = '{"field_1":{"name":"Dietary needs","type":"string","required":false,"index":1}}';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    public function test_a_free_schedule_cannot_post_them_on_a_new_event(): void
    {
        $owner = $this->createOwner();
        $free = $this->createFreeRole($owner, 'talent');
        $this->assertFalse($free->fresh()->isPro(), 'sanity check: the schedule is Free');

        $this->postCreateEvent($owner, $free, ['custom_fields' => self::FIELDS])->assertRedirect();

        $this->assertEmpty($this->latestEvent()->custom_fields, 'a hand-made post set per-order questions on a Free schedule');
    }

    public function test_a_free_save_keeps_the_questions_already_stored(): void
    {
        $owner = $this->createOwner();
        $free = $this->createFreeRole($owner, 'talent');
        $event = $this->createEvent($free, [
            'creator_role_id' => $free->id,
            'custom_fields' => json_decode(self::FIELDS, true),
        ]);

        $this->putUpdateEvent($owner, $free, $event, ['custom_fields' => '{}'])->assertRedirect();

        $this->assertSame('Dietary needs', Event::find($event->id)->custom_fields['field_1']['name'] ?? null,
            'a Free save that posted the field cleared the questions it may not change');
    }

    public function test_a_pro_schedule_saves_them(): void
    {
        $owner = $this->createOwner();
        $pro = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $pro, ['custom_fields' => self::FIELDS])->assertRedirect();

        $this->assertSame('Dietary needs', $this->latestEvent()->custom_fields['field_1']['name'] ?? null);
    }

    public function test_an_event_whose_own_schedule_is_pro_saves_them_from_a_free_schedule(): void
    {
        $owner = $this->createOwner();
        $pro = $this->createRole($owner, 'talent');
        $freeVenue = $this->createFreeRole($owner, 'venue');
        $event = $this->createEvent($pro, ['creator_role_id' => $pro->id]);
        $event->roles()->attach($freeVenue->id, ['is_accepted' => true]);

        $this->putUpdateEvent($owner, $freeVenue, $event, ['custom_fields' => self::FIELDS])->assertRedirect();

        $this->assertSame('Dietary needs', Event::find($event->id)->custom_fields['field_1']['name'] ?? null,
            'the event\'s own schedule is Pro, so the questions are on its plan');
    }
}
