<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The admin portal shows a schedule's events under the names they were written with. The
 * session's translate flag (set by reading ANY schedule's guest page in its second language) is
 * the visitor's, not the schedule's: a member who had read another schedule in English saw their
 * own Hebrew schedule's events under the English copy the AI import had stored (2026-11-18).
 * Guest pages still follow the flag.
 */
class AdminCalendarLanguageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Mutation: let the admin calendar follow displayLanguageCode() again. */
    public function test_the_admin_calendar_shows_the_names_as_written(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he', 'translation_language_code' => 'en']);
        $event = $this->createEvent($role, ['name' => 'סי היימן', 'creator_role_id' => $role->id, 'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')]);
        // The English copy an AI import stores whether or not the schedule offers a translation.
        DB::table('events')->where('id', $event->id)->update(['name_en' => 'Si Heiman Acoustic Show']);

        $when = now()->addDays(3);
        $query = ['year' => $when->year, 'month' => $when->month];

        // The admin portal's calendar and the dashboard's, as their pages fetch them.
        foreach ([
            route('role.admin_calendar_events', ['subdomain' => $role->subdomain] + $query),
            route('home.calendar_events', $query),
        ] as $url) {
            $names = collect($this->withSession(['translate' => true])->actingAs($owner)->getJson($url)->assertOk()->json('events'))->pluck('name');

            $this->assertContains('סי היימן', $names->all(), $url);
            $this->assertNotContains('Si Heiman Acoustic Show', $names->all(), $url);
        }
    }

    /**
     * A schedule whose second language is set to its own: the guest page, for anybody, shows the
     * names as written, never the English copy in the _en column as the "Hebrew translation".
     * Mutation: drop the getLanguageCode() check in Event::textInLanguage().
     */
    public function test_a_translation_into_the_events_own_language_is_never_used(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['language_code' => 'he', 'translation_language_code' => 'he']);
        $event = $this->createEvent($role, ['name' => 'סי היימן', 'creator_role_id' => $role->id, 'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')]);
        DB::table('events')->where('id', $event->id)->update(['name_en' => 'Si Heiman Acoustic Show']);

        $this->assertSame('סי היימן', $event->fresh()->nameInLanguage('he', $role));

        $page = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $this->assertStringNotContainsString('Si Heiman Acoustic Show', $page);
    }
}
