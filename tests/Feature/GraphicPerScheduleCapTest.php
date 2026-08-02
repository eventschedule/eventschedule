<?php

namespace Tests\Feature;

use App\Http\Controllers\GraphicController;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Events Graphic "Events Per Schedule" cap (graphic_settings.max_per_schedule).
 *
 * The graphic pulls a flat, starts_at-ascending list for one schedule. On a curator
 * that list can be dominated by a single prolific act or room, so the cap limits how
 * many events each linked talent/venue contributes and backfills from further down
 * the calendar. Selection happens in PHP because MySQL cannot easily do "top N per
 * group" through the event_role pivot.
 */
class GraphicPerScheduleCapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_resolve_max_per_schedule_treats_empty_and_zero_as_no_cap(): void
    {
        $this->assertNull(GraphicController::resolveMaxPerSchedule(null));
        $this->assertNull(GraphicController::resolveMaxPerSchedule(''));
        $this->assertNull(GraphicController::resolveMaxPerSchedule(0));
        $this->assertNull(GraphicController::resolveMaxPerSchedule('0'));
        $this->assertNull(GraphicController::resolveMaxPerSchedule(-3));

        $this->assertSame(3, GraphicController::resolveMaxPerSchedule('3'));
        $this->assertSame(1, GraphicController::resolveMaxPerSchedule(1));
        // Clamped to the same ceiling as event_count, so the pool sizing stays sane.
        $this->assertSame(20, GraphicController::resolveMaxPerSchedule(99));
    }

    public function test_resolve_max_per_row_clamps_to_the_saved_range(): void
    {
        // The preview endpoint reads this straight off the query string, so it has to
        // land in the same range saveSettings() validates - otherwise the preview can
        // render a shape that cannot be saved.
        $this->assertNull(GraphicController::resolveMaxPerRow(null));
        $this->assertNull(GraphicController::resolveMaxPerRow(''));
        $this->assertNull(GraphicController::resolveMaxPerRow(0));
        $this->assertNull(GraphicController::resolveMaxPerRow(-5));
        $this->assertNull(GraphicController::resolveMaxPerRow('auto'));

        $this->assertSame(4, GraphicController::resolveMaxPerRow('4'));
        $this->assertSame(20, GraphicController::resolveMaxPerRow(999));
    }

    public function test_pool_limit_only_widens_when_a_cap_is_set(): void
    {
        // No cap: the query must keep the exact LIMIT it had before this feature.
        $this->assertSame(6, GraphicController::resolveGraphicPoolLimit(6, null));
        // With a cap the query needs spare candidates to backfill from.
        $this->assertGreaterThan(20, GraphicController::resolveGraphicPoolLimit(6, 1));
    }

    public function test_cap_limits_each_linked_talent_and_venue(): void
    {
        // The worked example: a curator graphic where two acts share two rooms.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);
        $marlow = $this->createRole($owner, 'talent', ['name' => 'Marlow']);
        $roxy = $this->createRole($owner, 'venue', ['name' => 'Roxy']);
        $hive = $this->createRole($owner, 'venue', ['name' => 'Hive']);

        $events = collect([
            $this->createLinkedEvent($curator, 'Blues Night', 1, [$kays, $roxy]),
            $this->createLinkedEvent($curator, 'Kays Acoustic', 3, [$kays, $roxy]),
            $this->createLinkedEvent($curator, 'Kays Late', 5, [$kays, $hive]),
            $this->createLinkedEvent($curator, 'Duo Set', 7, [$marlow, $roxy]),
            $this->createLinkedEvent($curator, 'Marlow Trio', 9, [$marlow, $hive]),
        ]);

        $capped = GraphicController::applyPerScheduleCap($events, 2, $curator);

        // Kays Late is dropped because The Kays is at the cap; Duo Set because Roxy is.
        $this->assertSame(
            ['Blues Night', 'Kays Acoustic', 'Marlow Trio'],
            $capped->pluck('name')->all()
        );
    }

    public function test_cap_is_re_indexed_so_it_returns_a_clean_list(): void
    {
        // filter() preserves keys. Both consumers happen to re-index anyway
        // (EventTextGenerator::generate() and AbstractEventDesign::__construct), but a
        // helper that returns "the chosen events" should hand back a list, not a
        // sparse map.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);
        $marlow = $this->createRole($owner, 'talent', ['name' => 'Marlow']);

        $events = collect([
            $this->createLinkedEvent($curator, 'One', 1, [$kays]),
            $this->createLinkedEvent($curator, 'Two', 3, [$kays]),
            $this->createLinkedEvent($curator, 'Three', 5, [$marlow]),
        ]);

        $capped = GraphicController::applyPerScheduleCap($events, 1, $curator);

        $this->assertSame(['One', 'Three'], $capped->pluck('name')->all());
        $this->assertSame([0, 1], $capped->keys()->all());
    }

    public function test_cap_ignores_the_schedule_the_graphic_is_for(): void
    {
        // Every event on a venue's own graphic is at that venue. Counting it would
        // collapse the graphic to $cap events overall.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $events = collect([
            $this->createLinkedEvent($venue, 'Resident One', 1, []),
            $this->createLinkedEvent($venue, 'Resident Two', 3, []),
            $this->createLinkedEvent($venue, 'Resident Three', 5, []),
            $this->createLinkedEvent($venue, 'Resident Four', 7, []),
            $this->createLinkedEvent($venue, 'Resident Five', 9, []),
        ]);

        $capped = GraphicController::applyPerScheduleCap($events, 1, $venue);

        $this->assertCount(5, $capped);
    }

    public function test_cap_ignores_curator_schedules_on_the_event(): void
    {
        // A talent's graphic should not be thinned because several curators list
        // the same events - the cap is about acts and rooms on the poster.
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $otherCurator = $this->createRole($owner, 'curator', ['name' => 'City Guide']);

        $events = collect([
            $this->createLinkedEvent($talent, 'Show One', 1, [$otherCurator]),
            $this->createLinkedEvent($talent, 'Show Two', 3, [$otherCurator]),
            $this->createLinkedEvent($talent, 'Show Three', 5, [$otherCurator]),
        ]);

        $capped = GraphicController::applyPerScheduleCap($events, 1, $talent);

        $this->assertCount(3, $capped);
    }

    public function test_no_cap_returns_the_collection_untouched(): void
    {
        // The default. Existing graphics must be unchanged until an owner opts in.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);

        $events = collect([
            $this->createLinkedEvent($curator, 'One', 1, [$kays]),
            $this->createLinkedEvent($curator, 'Two', 3, [$kays]),
        ]);

        $this->assertSame($events, GraphicController::applyPerScheduleCap($events, null, $curator));
        $this->assertSame($events, GraphicController::applyPerScheduleCap($events, 0, $curator));
    }

    public function test_caption_never_describes_a_different_lineup_than_the_poster(): void
    {
        // The poster is capped over flyered events only, the caption over all events.
        // Run as two independent passes, a schedule's flyerless early dates fill its
        // quota in the caption and push out the very flyered dates the poster shows -
        // so the caption ends up describing neither event on the picture.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);

        $this->createLinkedEvent($curator, 'Kays Rehearsal', 1, [$kays]);
        $this->createLinkedEvent($curator, 'Kays Warmup', 3, [$kays]);
        $this->createLinkedEvent($curator, 'Kays Headline', 5, [$kays], 'headline.jpg');
        $this->createLinkedEvent($curator, 'Kays Encore', 7, [$kays], 'encore.jpg');

        $url = route('event.generate_graphic_data', ['subdomain' => $curator->subdomain]);

        $image = $this->actingAs($owner)->get($url.'?type=image&max_per_schedule=2');
        $image->assertOk();
        // Assert on `image`, not `image_error`: with type=image the empty-flyer path
        // returns early with an `error` key and never sets `image_error`, so asserting
        // that is null would pass whether or not the poster rendered.
        $this->assertNotNull($image->json('image'), 'expected the poster to render');

        $text = $this->actingAs($owner)->get($url.'?type=text&max_per_schedule=2')->json('text');

        // The two flyered dates are the poster, so they must be in the caption.
        $this->assertStringContainsString('Kays Headline', $text);
        $this->assertStringContainsString('Kays Encore', $text);
        // Kays' quota of 2 is spent by the poster, so the flyerless dates drop out.
        $this->assertStringNotContainsString('Kays Rehearsal', $text);
        $this->assertStringNotContainsString('Kays Warmup', $text);
    }

    public function test_cap_selection_is_stable_when_events_share_a_start_time(): void
    {
        // The cap is a greedy left-to-right walk, so without a tiebreak two events on
        // the same starts_at could be returned in either order and the walk would keep
        // and drop *different* events between the separate text and image requests.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);

        $sameSlot = now()->addDays(5)->setTime(12, 0)->format('Y-m-d H:i:s');
        foreach (['Tie A', 'Tie B', 'Tie C'] as $name) {
            $event = $this->createEvent($curator, ['name' => $name, 'starts_at' => $sameSlot]);
            $event->roles()->attach($kays->id, ['is_accepted' => true]);
        }

        $url = route('event.generate_graphic_data', ['subdomain' => $curator->subdomain]).'?type=text&max_per_schedule=1';

        $first = $this->actingAs($owner)->get($url)->json('text');

        // The guarantee is the lowest id wins, not merely that repeated calls agree -
        // MySQL returns a stable order for an identical query either way, so asserting
        // only repeatability would pass with the tiebreak removed.
        $this->assertStringContainsString('Tie A', $first);
        $this->assertStringNotContainsString('Tie B', $first);
        $this->assertStringNotContainsString('Tie C', $first);

        $second = $this->actingAs($owner)->get($url)->json('text');
        $this->assertSame($first, $second);
    }

    public function test_cap_can_leave_the_graphic_shorter_than_the_event_count(): void
    {
        // Documented behaviour, pinned so it cannot change silently: the rule is
        // all-or-nothing and counts venues as well as talents, so a resident act at a
        // single room cannot fill a graphic however far down the calendar it looks.
        // The docs and the help text say so; this proves they are telling the truth.
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $club = $this->createRole($owner, 'venue', ['name' => 'The Club']);

        for ($i = 1; $i <= 8; $i++) {
            $this->createLinkedEvent($talent, "Residency {$i}", $i * 2, [$club]);
        }

        $url = route('event.generate_graphic_data', ['subdomain' => $talent->subdomain]);
        $text = $this->actingAs($owner)->get($url.'?type=text&event_count=8&max_per_schedule=2')->json('text');

        $this->assertStringContainsString('Residency 1', $text);
        $this->assertStringContainsString('Residency 2', $text);
        $this->assertStringNotContainsString('Residency 3', $text);
        // Pin the upper bound too, so a regression that stops capping is caught.
        $this->assertStringNotContainsString('Residency 8', $text);
    }

    public function test_text_show_all_lifts_the_count_limit_but_keeps_the_cap(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);
        $marlow = $this->createRole($owner, 'talent', ['name' => 'Marlow']);

        $this->createLinkedEvent($curator, 'Kays One', 1, [$kays]);
        $this->createLinkedEvent($curator, 'Kays Two', 3, [$kays]);
        $this->createLinkedEvent($curator, 'Marlow One', 5, [$marlow]);
        $this->createLinkedEvent($curator, 'Marlow Two', 7, [$marlow]);

        $url = route('event.generate_graphic_data', ['subdomain' => $curator->subdomain]);

        // event_count=1 would normally cut the list to one event; show-all lifts that.
        $text = $this->actingAs($owner)
            ->get($url.'?type=text&event_count=1&text_show_all=1&max_per_schedule=1')
            ->json('text');

        $this->assertStringContainsString('Kays One', $text);
        $this->assertStringContainsString('Marlow One', $text);
        // The cap still applies - it governs which events, not how many.
        $this->assertStringNotContainsString('Kays Two', $text);
        $this->assertStringNotContainsString('Marlow Two', $text);
    }

    public function test_preview_endpoint_applies_the_cap_from_the_request(): void
    {
        // The live preview reads max_per_schedule from the request only - falling back
        // to saved settings would keep showing a stale cap after switching to Unlimited.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);
        $marlow = $this->createRole($owner, 'talent', ['name' => 'Marlow']);

        $this->createLinkedEvent($curator, 'Kays Opening', 1, [$kays]);
        $this->createLinkedEvent($curator, 'Kays Encore', 3, [$kays]);
        $this->createLinkedEvent($curator, 'Marlow Trio', 5, [$marlow]);

        // A cap IS saved, so a fallback to settings would be visible. Without this the
        // test proves nothing: with no saved value, request-only and fall-back-to-saved
        // behave identically.
        $curator->graphic_settings = array_merge($curator->graphic_settings, ['max_per_schedule' => 1]);
        $curator->save();

        $url = route('event.generate_graphic_data', ['subdomain' => $curator->subdomain]);

        // No param at all means Unlimited, even though 1 is saved.
        $uncapped = $this->actingAs($owner)->get($url.'?type=text');
        $uncapped->assertOk();
        $this->assertStringContainsString('Kays Opening', $uncapped->json('text'));
        $this->assertStringContainsString('Kays Encore', $uncapped->json('text'));
        $this->assertStringContainsString('Marlow Trio', $uncapped->json('text'));

        $capped = $this->actingAs($owner)->get($url.'?type=text&max_per_schedule=1');
        $capped->assertOk();
        $this->assertStringContainsString('Kays Opening', $capped->json('text'));
        $this->assertStringNotContainsString('Kays Encore', $capped->json('text'));
        $this->assertStringContainsString('Marlow Trio', $capped->json('text'));
    }

    public function test_graphic_page_renders_both_copies_of_the_control(): void
    {
        // The settings panel is duplicated for desktop and mobile and kept in sync by
        // hand, so a control that only lands in one copy silently stops working there.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        $response = $this->actingAs($owner)
            ->get(route('event.generate_graphic', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertSee('id="max_per_schedule"', false);
        $response->assertSee('id="max_per_schedule_mobile"', false);
        $response->assertSee(__('messages.max_per_schedule'), false);
    }

    public function test_saved_settings_accept_and_persist_the_cap(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');

        // Null is the default, so an untouched schedule keeps the old behaviour.
        $this->assertNull($role->graphic_settings['max_per_schedule']);

        $this->actingAs($owner)
            ->postJson(route('event.save_graphic_settings', ['subdomain' => $role->subdomain]), [
                'max_per_schedule' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('settings.max_per_schedule', 3);

        $this->assertSame(3, $role->fresh()->graphic_settings['max_per_schedule']);

        // Out of range is rejected rather than silently stored.
        $this->actingAs($owner)
            ->postJson(route('event.save_graphic_settings', ['subdomain' => $role->subdomain]), [
                'max_per_schedule' => 99,
            ])
            ->assertStatus(422);
    }

    /**
     * Create an event on $role, dated $daysAhead out, also linked to $linked schedules.
     * Pass $flyer to give it artwork, which is what puts it on the image as well as in
     * the text.
     */
    private function createLinkedEvent(Role $role, string $name, int $daysAhead, array $linked, ?string $flyer = null): Event
    {
        $event = $this->createEvent($role, [
            'name' => $name,
            'starts_at' => now()->addDays($daysAhead)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
            'flyer_image_url' => $flyer,
        ]);

        foreach ($linked as $other) {
            $event->roles()->attach($other->id, ['is_accepted' => true]);
        }

        return $event->fresh()->load('roles');
    }

    public function test_truncation_keeps_poster_events_over_earlier_uncapped_ones(): void
    {
        // The cap admits the poster, but the later cut to event_count could still drop
        // it: a flyerless event on a DIFFERENT schedule's untouched quota survives the
        // filter, sorts earlier, and eats the slot. Marlow's rehearsal is that event.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $kays = $this->createRole($owner, 'talent', ['name' => 'The Kays']);
        $marlow = $this->createRole($owner, 'talent', ['name' => 'Marlow']);

        $this->createLinkedEvent($curator, 'Solo Rehearsal', 1, [$marlow]);
        $this->createLinkedEvent($curator, 'Kays Headline', 5, [$kays], 'headline.jpg');
        $this->createLinkedEvent($curator, 'Kays Encore', 7, [$kays], 'encore.jpg');

        $url = route('event.generate_graphic_data', ['subdomain' => $curator->subdomain]);
        $text = $this->actingAs($owner)
            ->get($url.'?type=text&event_count=2&max_per_schedule=2')
            ->json('text');

        // Both flyered dates are on the poster, so both must be in the caption even
        // though Solo Rehearsal is earlier and passes the cap on Marlow's quota.
        $this->assertStringContainsString('Kays Headline', $text);
        $this->assertStringContainsString('Kays Encore', $text);
        $this->assertStringNotContainsString('Solo Rehearsal', $text);
    }

    public function test_poster_events_outside_the_candidate_pool_do_not_burn_quota(): void
    {
        // The flyer pool and the base pool are the same size but the flyer one filters
        // harder, so a poster event can sit past the base pool's window. Charging its
        // quota against events that can never repay it thins the caption - and with one
        // venue and a cap of 1 it empties it, turning the text pane into a hard error.
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        $club = $this->createRole($owner, 'venue', ['name' => 'The Club']);

        // A poster event the base query will not return, standing in for one that falls
        // past GRAPHIC_CANDIDATE_POOL: private events are excluded from every graphic query.
        $offPool = $this->createLinkedEvent($curator, 'Off Pool Gig', 30, [$club], 'offpool.jpg');
        $offPool->is_private = true;
        $offPool->save();

        $this->createLinkedEvent($curator, 'Club Night', 2, [$club]);

        $capped = GraphicController::applyPerScheduleCap(
            collect([$curator->events()->firstWhere('name', 'Club Night')]),
            1,
            $curator,
            collect([$offPool])
        );

        $this->assertCount(1, $capped, 'an out-of-pool poster event must not spend quota');
        $this->assertSame('Club Night', $capped->first()->name);
    }

    /**
     * Guard the helper signature: it takes and returns an Illuminate collection.
     */
    public function test_cap_returns_a_collection(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->assertInstanceOf(
            Collection::class,
            GraphicController::applyPerScheduleCap(collect(), 2, $role)
        );
    }
}
