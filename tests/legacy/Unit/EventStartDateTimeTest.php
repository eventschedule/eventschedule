<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Role;
use Tests\TestCase;

class EventStartDateTimeTest extends TestCase
{
    public function test_localized_start_uses_converted_event_date()
    {
        $event = new Event([
            'starts_at' => '2024-06-02 00:00:00',
        ]);

        $event->setRelation('creatorRole', new Role([
            'timezone' => 'America/New_York',
        ]));

        $start = $event->getStartDateTime('2024-06-02', true);

        $this->assertSame('America/New_York', $start->getTimezone()->getName());
        $this->assertSame('2024-06-01 20:00:00', $start->format('Y-m-d H:i:s'));
    }

    public function test_custom_event_date_respects_local_timezone_shift()
    {
        $event = new Event([
            'starts_at' => '2024-06-02 00:00:00',
        ]);

        $event->setRelation('creatorRole', new Role([
            'timezone' => 'America/New_York',
        ]));

        $start = $event->getStartDateTime('2024-06-04', true);

        $this->assertSame('2024-06-04', $start->format('Y-m-d'));
        $this->assertSame('America/New_York', $start->getTimezone()->getName());
        $this->assertSame('2024-06-05 00:00:00', $start->copy()->setTimezone('UTC')->format('Y-m-d H:i:s'));
    }
}
