<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The post-signup schedule type chooser (/getting-started), and the same cards on the dashboard's
 * zero-schedule panel. See partials/schedule-type-cards.blade.php.
 */
class ScheduleTypeChooserTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_chooser_offers_all_three_types_and_greets_the_user(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['name' => 'Jordan Rivera'])->save();

        $html = $this->actingAs($user)->get('/getting-started')->assertOk()->getContent();

        $this->assertStringContainsString(e(__('messages.schedule_type_question_for')), $html);
        $this->assertStringContainsString('Jordan', $html);

        // One GET form back to this page, whose ?type= forwards to /new/{type}.
        $this->assertStringContainsString('action="'.route('getting-started').'"', $html);

        foreach (['talent', 'venue', 'curator'] as $type) {
            $this->assertMatchesRegularExpression('/<input type="radio" id="schedule-type-choice-'.$type.'" name="type" value="'.$type.'"/', $html, $type);
            $this->assertStringContainsString('aria-labelledby="schedule-type-'.$type.'"', $html, $type);
        }

        // Event schedule is the one chosen to begin with, and only it.
        $this->assertSame(1, preg_match_all('/name="type" value="[a-z]+" class="sr-only" checked/', $html));
        $this->assertMatchesRegularExpression('/name="type" value="talent" class="sr-only" checked/', $html);
    }

    public function test_each_type_says_whose_events_and_which_venues(): void
    {
        $user = $this->createOwner();

        $html = $this->actingAs($user)->get('/getting-started')->assertOk()->getContent();

        // The primary card's strip: two cells, Events and Venues.
        preg_match_all('/class="st-cell-value[^"]*">([^<]*)</', $html, $m);
        $values = array_map(fn ($v) => html_entity_decode(trim($v), ENT_QUOTES), $m[1]);

        $this->assertSame([__('messages.schedule_type_events_yours'), __('messages.schedule_type_venues_anywhere')], $values);

        // The two alternatives say it in one line each, as the 2026-11 design draws them:
        // "Anyone's events · your venue" and "Anyone's events · any venue".
        preg_match_all('/<p class="st-line[^"]*">(.*?)<\/p>/s', $html, $lines);
        $lines = array_map(fn ($l) => html_entity_decode(trim(preg_replace('/\s+/', ' ', strip_tags($l))), ENT_QUOTES), $lines[1]);
        $events = str_replace(':whose', __('messages.schedule_type_line_anyones'), __('messages.schedule_type_line_events'));

        $this->assertSame([
            $events.' · '.__('messages.schedule_type_line_your_venue'),
            $events.' · '.__('messages.schedule_type_line_any_venue'),
        ], $lines);
    }

    public function test_the_first_run_screens_show_their_step(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['name' => 'Jordan Rivera'])->save();

        // Step 1 of 2 on the chooser, with the welcome on its own; step 2 of 2 on the form.
        $this->actingAs($user)->get('/getting-started')->assertOk()
            ->assertSee(__('messages.setup_step_of', ['current' => 1, 'total' => 2]))
            ->assertSee(__('messages.getting_started_welcome_name', ['name' => 'Jordan']));

        $this->get(route('new', ['type' => 'venue']))->assertOk()
            ->assertSee(__('messages.setup_step_of', ['current' => 2, 'total' => 2]))
            ->assertSee(__('messages.time_zone'))
            ->assertSee(__('messages.venue_tagline'));
    }

    public function test_the_market_switch_lives_under_venue_and_no_separate_card_is_offered(): void
    {
        $user = $this->createOwner();

        $html = $this->actingAs($user)->get('/getting-started')->assertOk()->getContent();

        // The switch is inside the Venue option, not a card of its own linking to the venue form.
        $this->assertMatchesRegularExpression('/data-type="venue".*?name="vendor_map" value="market".*?data-type="curator"/s', $html);
        $this->assertStringNotContainsString('vendor_map=market', $html);
    }

    public function test_continue_forwards_to_the_chosen_form_and_keeps_the_market_only_for_a_venue(): void
    {
        $user = $this->createOwner();
        $this->actingAs($user);

        $this->get('/getting-started?type=venue&vendor_map=market')
            ->assertRedirect(route('new', ['type' => 'venue', 'vendor_map' => 'market']));

        // Left on, then another type chosen: the switch is dropped.
        $this->get('/getting-started?type=curator&vendor_map=market')
            ->assertRedirect(route('new', ['type' => 'curator']));

        // A kind the vendor map does not know is not carried.
        $this->get('/getting-started?type=venue&vendor_map=nonsense')
            ->assertRedirect(route('new', ['type' => 'venue']));

        $this->get('/getting-started?type=talent')
            ->assertRedirect(route('new', ['type' => 'talent']));
    }

    /**
     * A deleted sale is no ticket. The dashboard has always read it so and offers the chooser to
     * somebody whose only sale was deleted; /getting-started counted it and sent their Continue
     * straight back to the dashboard, so they could never make a schedule from there.
     */
    public function test_a_deleted_sale_does_not_turn_the_chooser_into_a_dead_end(): void
    {
        $organizer = $this->createOwner();
        $schedule = $this->createRole($organizer, 'talent');
        $event = $this->createEvent($schedule);

        $user = $this->createOwner();
        $sale = $this->createSale($event, $schedule, ['user_id' => $user->id, 'is_deleted' => true]);
        $this->actingAs($user);

        $this->get('/dashboard?skip_onboarding=1')->assertOk()->assertSee('id="schedule-type-talent"', false);
        $this->get('/getting-started?type=talent')->assertRedirect(route('new', ['type' => 'talent']));
        $this->get('/getting-started')->assertOk();

        // A ticket that stands still makes them an attendee, and the chooser is not for them.
        $sale->is_deleted = false;
        $sale->save();
        $this->get('/getting-started?type=talent')->assertRedirect(route('home'));
    }

    public function test_the_dashboard_panel_nests_its_card_titles_under_its_own_heading(): void
    {
        // /getting-started has the page's h1 above the cards, so they are h2. On the dashboard
        // they sit under the panel's h2, so they must be h3 or the outline skips back up a level.
        $user = $this->createOwner();

        $html = $this->actingAs($user)->get('/dashboard?skip_onboarding=1')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<h3 id="schedule-type-talent"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<h2 id="schedule-type-talent"/', $html);
    }
}
