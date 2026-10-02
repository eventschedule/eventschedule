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

        $this->assertStringContainsString(__('messages.schedule_type_question'), $html);
        $this->assertStringContainsString('Jordan', $html);

        foreach (['talent', 'venue', 'curator'] as $type) {
            $this->assertStringContainsString('href="'.route('new', ['type' => $type]).'"', $html, $type);
            $this->assertStringContainsString('aria-labelledby="schedule-type-'.$type.'"', $html, $type);
            // The line that tells the types apart, with its <b><i> emphasis kept as markup.
            $this->assertStringContainsString(__('messages.'.$type.'_footer'), $html, $type);
        }
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
