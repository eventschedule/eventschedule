<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * From a saved schedule to a saved first event.
 *
 * In the 2026-08 cohort 36 people saved a schedule and 22 went on to save an event. The event
 * form they landed on was the full one - seven sections, a Boost button that can only say "save
 * first", Enterprise upsells - with nothing saying this was the last step, and the step indicator
 * the schedule form showed as "2 of 3" vanished on step 3. Saving it produced a three-second toast.
 */
class FirstEventFlowTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function createHtml(User $user, Role $role): string
    {
        return $this->actingAs($user)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();
    }

    /** Tests make new schedules Enterprise; the upsells being removed only exist on free. */
    private function freeTalent(User $owner): Role
    {
        return $this->createFreeRole($owner, 'talent');
    }

    private function saveFirstNotice(): string
    {
        return Js::from(__('messages.save_event_first'))->toHtml();
    }

    public function test_a_first_event_gets_the_first_run_form(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $html = $this->createHtml($owner, $role);

        // The three-circle band is the guest-submit flow's alone now. A first event of one's own
        // is walked by the setup guide (SetupGuideTest), which this fixture, a schedule written
        // straight to the database rather than saved through the wizard, does not have.
        $this->assertStringNotContainsString('class="step-indicator', $html);
        $this->assertStringContainsString(__('messages.next_step_add_first_event'), $html);
        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $html);
        $this->assertStringContainsString(__('messages.more_options'), $html);
        $this->assertStringContainsString('v-show="showMoreSections"', $html);
        $this->assertStringContainsString('showMoreSections: false', $html);
        $this->assertStringContainsString(__('messages.create_event'), $html);

        $this->assertStringNotContainsString($this->saveFirstNotice(), $html, 'no Boost button that can only say "save first"');
        $this->assertStringNotContainsString(
            __('messages.internal').' ('.__('messages.enterprise').')', $html,
            'no locked Enterprise visibility pills'
        );
    }

    public function test_the_locked_ai_generator_is_left_off_a_first_event(): void
    {
        config(['services.google.gemini_key' => 'test-key']);

        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $this->assertStringNotContainsString("openUpgrade('upgrade-ai-details')", $this->createHtml($owner, $role));

        $this->createEvent($role);

        $this->assertStringContainsString(
            "openUpgrade('upgrade-ai-details')", $this->createHtml($owner, $role),
            'still offered from the second event on'
        );
    }

    /** Someone who has made an event before gets the form exactly as it was. */
    public function test_a_later_event_gets_the_full_form(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);
        $this->createEvent($role);

        $html = $this->createHtml($owner, $role);

        $this->assertStringNotContainsString('class="step-indicator', $html);
        $this->assertStringNotContainsString(__('messages.first_event_form_subtitle'), $html);
        $this->assertStringNotContainsString('v-show="showMoreSections"', $html);
        $this->assertStringContainsString('showMoreSections: true', $html);
        $this->assertStringContainsString($this->saveFirstNotice(), $html);
        $this->assertStringContainsString(__('messages.internal').' ('.__('messages.enterprise').')', $html);
    }

    /**
     * The first run is read from the data, so it survives a save the server refuses.
     *
     * RoleController::store() flashes onboarding_event_redirect onto the redirect to this form,
     * and the first GET consumes it. A server-side validation failure redirects back here with the
     * flash long gone - if the first-run state rode on it, the step indicator and the simpler form
     * would disappear at exactly the moment someone is struggling with the page.
     */
    public function test_a_failed_first_save_keeps_the_first_run_form(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);
        $createUrl = route('event.create', ['subdomain' => $role->subdomain]);

        // Flashed exactly as the redirect from RoleController::store() leaves it: consumed by
        // the GET below, gone by the time the refused save sends the user back.
        $this->app['session']->flash('onboarding_event_redirect', true);
        $this->actingAs($owner)->get($createUrl)->assertOk();

        $this->actingAs($owner)
            ->from($createUrl)
            ->post(route('event.store', ['subdomain' => $role->subdomain]), $this->eventPayload(['name' => '']))
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors('name');

        $html = $this->createHtml($owner, $role);

        $this->assertStringNotContainsString('class="step-indicator', $html);
        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $html);
    }

    /** The band survives in exactly one place: someone adding an event to another schedule. */
    public function test_the_guest_submit_flow_keeps_the_step_band(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $html = $this->actingAs($owner)
            ->withSession(['pending_request' => $role->subdomain])
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('class="step-indicator', $html);
    }

    /** Both date/time checks show inline instead of in an alert(). */
    public function test_missing_date_and_time_are_reported_inline(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);
        $this->createEvent($role);

        $html = $this->createHtml($owner, $role);

        $this->assertStringContainsString('showDateTimeError(', $html);
        $this->assertStringContainsString('v-if="dateTimeError && dateTimeErrorField !== \'end_date\'"', $html);
        $this->assertStringNotContainsString("alert(@json(__('messages.date_and_time_required')))", $html);
        $this->assertStringNotContainsString('alert('.json_encode(__('messages.date_and_time_required')).')', $html);
        $this->assertStringNotContainsString('alert('.json_encode(__('messages.end_date_required')).')', $html);
    }

    public function test_the_first_event_gets_a_panel_with_its_link(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $response = $this->postCreateEvent($owner, $role, [
            'name' => 'Opening Night',
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
        ]);

        $event = $this->latestEvent();
        $response->assertRedirect()->assertSessionHas('message', __('messages.event_created'));

        $flash = session('first_event_created');
        $this->assertIsArray($flash);
        $this->assertSame('Opening Night', $flash['name']);
        $this->assertFalse($flash['is_draft']);
        $this->assertSame($event->getUndatedGuestUrl($role->subdomain), $flash['url']);
        $this->assertSame(
            route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            $flash['edit_url']
        );

        $html = $this->actingAs($owner)
            ->withSession(['first_event_created' => $flash])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.first_event_live_title'), $html);
        $this->assertStringContainsString('id="first-event-url"', $html);
        $this->assertStringContainsString('value="'.e($flash['url']).'"', $html);
        $this->assertStringContainsString(e($flash['edit_url']).'#section-tickets', $html);
        $this->assertStringContainsString(__('messages.add_another_event'), $html);
    }

    /** The name is the user's own text, rendered inside the AP: it must not compile as Vue. */
    public function test_the_panel_does_not_let_the_event_name_run_as_a_template(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $html = $this->actingAs($owner)
            ->withSession(['first_event_created' => [
                'name' => '{{ 7*7 }}',
                'is_draft' => false,
                'url' => 'https://example.com/e',
                'edit_url' => 'https://example.com/edit',
            ]])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/<span[^>]*v-pre[^>]*>\{\{ 7\*7 \}\}<\/span>/', $html);
    }

    public function test_a_draft_first_event_gets_the_panel_without_a_link(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $this->postCreateEvent($owner, $role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
            'is_draft' => 1,
        ])->assertRedirect();

        $flash = session('first_event_created');
        $this->assertTrue($flash['is_draft']);
        $this->assertNull($flash['url'], 'a draft is a 404 for guests, so there is no link to share');

        $html = $this->actingAs($owner)
            ->withSession(['first_event_created' => $flash])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.first_event_draft_title'), $html);
        $this->assertStringNotContainsString('id="first-event-url"', $html);
    }

    /** A refused flyer does not make the event any less created or any less first. */
    public function test_the_panel_survives_a_refused_flyer(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);

        $this->postCreateEvent($owner, $role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
            'ai_flyer_image' => 'never-issued.png',
        ])
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.ai_image_not_applied'));

        $this->assertIsArray(session('first_event_created'));
    }

    public function test_a_second_event_gets_no_panel(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeTalent($owner);
        $this->createEvent($role);

        $this->postCreateEvent($owner, $role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 20:00:00',
        ])->assertRedirect()->assertSessionMissing('first_event_created');

        $this->assertSame(2, Event::count());
    }
}
