<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.slug has no unique constraint and SlugPatternUtils::generateSlug() applies none of its
 * own, so a pattern like {event_name}-{day_pad}-{month} collides twice over: two shows at one venue
 * on one night, and the same calendar date next year. EventRepo::getEvent() answers a bare /slug
 * with a single row, so on a collision the other event is unreachable at that address, records no
 * page views, and is simply absent from /analytics - which is what "this event is missing from
 * statistics" turned out to mean for a venue running near-nightly shows.
 */
class EventSlugUniquenessTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private const PATTERN = '{event_name}-{day_pad}-{month}';

    public function test_two_events_on_one_night_get_distinct_slugs(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['slug_pattern' => self::PATTERN]);

        $this->postCreateEvent($owner, $venue, ['name' => 'Ba Be', 'starts_at' => '2026-09-09 20:00:00'])
            ->assertRedirect();
        $first = $this->latestEvent();

        $this->postCreateEvent($owner, $venue, ['name' => 'Ba Be', 'starts_at' => '2026-09-09 23:00:00'])
            ->assertRedirect();
        $second = $this->latestEvent();

        $this->assertSame('ba-be-09-9', $first->slug);
        $this->assertSame('ba-be-09-9-2', $second->slug);
        $this->assertNotSame($first->id, $second->id);
    }

    public function test_the_same_date_next_year_does_not_reuse_the_address(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['slug_pattern' => self::PATTERN]);

        $this->postCreateEvent($owner, $venue, ['name' => 'Ba Be', 'starts_at' => '2026-09-09 20:00:00'])
            ->assertRedirect();
        $thisYear = $this->latestEvent();

        $this->postCreateEvent($owner, $venue, ['name' => 'Ba Be', 'starts_at' => '2027-09-09 20:00:00'])
            ->assertRedirect();
        $nextYear = $this->latestEvent();

        $this->assertSame('ba-be-09-9', $thisYear->slug);
        $this->assertSame('ba-be-09-9-2', $nextYear->slug);
    }

    public function test_a_repeat_save_does_not_renumber_an_event(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['slug_pattern' => self::PATTERN]);

        $this->postCreateEvent($owner, $venue, ['name' => 'Ba Be', 'starts_at' => '2026-09-09 20:00:00'])
            ->assertRedirect();
        $event = $this->latestEvent();
        $this->assertSame('ba-be-09-9', $event->slug);

        // Idempotency: regenerating the same slug must not turn "x" into "x-2", nor the next save
        // into "x-2-3". A date edit is what triggers regeneration at all.
        foreach ([1, 2] as $ignored) {
            $this->putUpdateEvent($owner, $venue, $event, [
                'name' => 'Ba Be',
                'starts_at' => '2026-09-10 20:00:00',
            ])->assertRedirect();

            $this->assertSame('ba-be-10-9', $event->fresh()->slug);
        }
    }

    public function test_an_owner_typed_slug_that_collides_is_rejected_not_suffixed(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $taken = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $taken->slug = 'the-good-one';
        $taken->saveQuietly();

        $other = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        // A suffixed address is not one an owner would print - SocialShortLinkTest records the same
        // position for short links - so this must fail loudly rather than quietly become "-2".
        $this->putUpdateEvent($owner, $venue, $other, ['slug' => 'the-good-one'])
            ->assertSessionHasErrors('slug');

        $this->assertNotSame('the-good-one', $other->fresh()->slug);
    }

    public function test_an_event_already_carrying_a_duplicate_can_still_be_saved(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $first = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $second = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        // A pre-existing collision, of the kind the detector finds. Only a CHANGED slug is checked,
        // so an unrelated save of either event must not start failing because of it.
        foreach ([$first, $second] as $event) {
            $event->slug = 'legacy-duplicate';
            $event->saveQuietly();
        }

        $this->putUpdateEvent($owner, $venue, $second, [
            'name' => 'Edited Elsewhere',
            'slug' => 'legacy-duplicate',
        ])->assertSessionHasNoErrors();

        $this->assertSame('legacy-duplicate', $second->fresh()->slug);
    }

    public function test_check_data_finds_and_fixes_a_duplicate_pair(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $keep = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2026-09-09 20:00:00']);
        $rename = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => '2027-09-09 20:00:00']);

        foreach ([$keep, $rename] as $event) {
            $event->slug = 'ba-be-09-9';
            $event->saveQuietly();
        }

        // app:check-data reports through its output and always exits 0 - every existing arm behaves
        // that way, so this one matches rather than introducing a second convention.
        $this->artisan('app:check-data', ['check' => 'duplicate-event-slugs'])
            ->expectsOutputToContain('Duplicate event slug "ba-be-09-9"')
            ->assertExitCode(0);

        // Reporting alone must not move anything.
        $this->assertSame('ba-be-09-9', $rename->fresh()->slug);

        $this->artisan('app:check-data', ['check' => 'duplicate-event-slugs', '--fix' => true])
            ->assertExitCode(0);

        // The earliest occurrence keeps the address: it is the one most likely already printed.
        $this->assertSame('ba-be-09-9', $keep->fresh()->slug);
        $this->assertSame('ba-be-09-9-2', $rename->fresh()->slug);
    }

    public function test_a_bare_slug_resolves_to_the_next_occurrence_not_the_furthest_future(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $nextWeek = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => now()->addWeek()]);
        $nextYear = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'starts_at' => now()->addYear()]);

        // The state the detector exists for: a pre-existing collision, both accepted and live.
        foreach ([$nextWeek, $nextYear] as $event) {
            $event->slug = 'ba-be-09-9';
            $event->saveQuietly();
        }

        // Ascending, matching the dated sibling findEventBySlug(). DESC meant a bare /slug answered
        // with NEXT year's event while /slug/{date} answered with this year's - two addresses for
        // one show, each banking its views on a different row.
        $this->get('/'.$venue->subdomain.'/ba-be-09-9')
            ->assertOk()
            ->assertSee(UrlUtils::encodeId($nextWeek->id), false);

        // Both stay reachable by their permanent id URLs regardless.
        foreach ([$nextWeek, $nextYear] as $event) {
            $this->get('/'.$venue->subdomain.'/ba-be-09-9/'.UrlUtils::encodeId($event->id))->assertOk();
        }
    }
}
