<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;

class EventApiDateFormattingTest extends TestCase
{
    public function test_local_wall_time_strings_round_trip_across_timezones()
    {
        $eventInstantUtc = Carbon::create(2024, 11, 5, 0, 0, 0, 'UTC');
        $newYorkTz = 'America/New_York';
        $losAngelesTz = 'America/Los_Angeles';

        $apiStringNewYork = $eventInstantUtc->copy()->setTimezone($newYorkTz)->format('Y-m-d H:i:s');
        $apiStringLosAngeles = $eventInstantUtc->copy()->setTimezone($losAngelesTz)->format('Y-m-d H:i:s');

        $storedFromNewYork = Carbon::createFromFormat('Y-m-d H:i:s', $apiStringNewYork, $newYorkTz)
            ->setTimezone('UTC');
        $storedFromLosAngeles = Carbon::createFromFormat('Y-m-d H:i:s', $apiStringLosAngeles, $losAngelesTz)
            ->setTimezone('UTC');

        $this->assertSame($eventInstantUtc->toDateTimeString(), $storedFromNewYork->toDateTimeString());
        $this->assertTrue($storedFromNewYork->equalTo($storedFromLosAngeles));
    }

    public function test_forcing_utc_in_formatter_shifts_wall_time()
    {
        $eventInstantUtc = Carbon::create(2024, 11, 5, 0, 0, 0, 'UTC');
        $losAngelesTz = 'America/Los_Angeles';

        $apiStringUtc = $eventInstantUtc->copy()->setTimezone('UTC')->format('Y-m-d H:i:s');
        $storedFromUtc = Carbon::createFromFormat('Y-m-d H:i:s', $apiStringUtc, $losAngelesTz)
            ->setTimezone('UTC');

        $this->assertNotSame($eventInstantUtc->toDateTimeString(), $storedFromUtc->toDateTimeString());
        $this->assertNotEquals(
            $eventInstantUtc->toDateTimeString(),
            $storedFromUtc->toDateTimeString(),
            'UTC formatting should reveal the drift when parsed as local time.'
        );
    }
}
