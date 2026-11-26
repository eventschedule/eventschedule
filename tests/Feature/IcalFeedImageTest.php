<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's iCal feed carries each event's picture (issue #140): the flyer, else a
 * performer's photo, never the feed's own schedule's picture.
 */
class IcalFeedImageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The feed with its folded lines put back together (RFC 5545 3.1). */
    private function feed($role): string
    {
        $ics = $this->get(route('feed.ical', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        foreach (explode("\r\n", $ics) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line), 'every line is folded: '.$line);
        }

        return str_replace("\r\n ", '', $ics);
    }

    /** The VEVENT of the event called $name. */
    private function vevent(string $ics, string $name): string
    {
        foreach (explode('BEGIN:VEVENT', $ics) as $part) {
            if (str_contains($part, 'SUMMARY:'.$name)) {
                return $part;
            }
        }
        $this->fail("no event called {$name}");
    }

    public function test_an_event_with_a_flyer_carries_it(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['profile_image_url' => 'venue_logo.png']);
        $this->createEvent($venue, ['name' => 'Flyer Night', 'creator_role_id' => $venue->id, 'flyer_image_url' => 'flyer_a_very_long_file_name_that_runs_past_the_line_limit.jpg']);
        $this->createEvent($venue, ['name' => 'Plain Night', 'creator_role_id' => $venue->id]);

        $ics = $this->feed($venue);
        $flyer = $this->vevent($ics, 'Flyer Night');
        $url = Event::where('name', 'Flyer Night')->first()->flyer_image_url;
        $this->assertStringStartsWith('http', $url);
        $this->assertStringContainsString('IMAGE;VALUE=URI;DISPLAY=BADGE;FMTTYPE=image/jpeg:'.$url."\r\n", $flyer);
        $this->assertStringContainsString('ATTACH;FMTTYPE=image/jpeg:'.$url."\r\n", $flyer);

        $this->assertStringNotContainsString('IMAGE', $this->vevent($ics, 'Plain Night'), 'never the venue\'s own logo on every event');
    }

    public function test_without_a_flyer_a_performers_photo_stands_in(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($owner, 'talent', ['profile_image_url' => 'act_photo.png']);
        $event = $this->createEvent($venue, ['name' => 'Act Night', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $this->assertStringContainsString('IMAGE;VALUE=URI;DISPLAY=BADGE;FMTTYPE=image/png:'.$act->fresh()->profile_image_url."\r\n", $this->vevent($this->feed($venue), 'Act Night'));
    }
}
