<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The places events can be brought in from, offered while a schedule has none: above the form for
 * someone's first event, and above the calendar on its Schedule tab (<x-import-options>,
 * App\Utils\ImportOptions).
 *
 * Organizers who saved a schedule mostly stopped at an empty event form, and a schedule fills from
 * where its events already are far faster than one by hand. So the import page is offered at the
 * two moments a schedule is empty, by the names of the places events live, and only those this
 * person on this install can use.
 */
class FirstRunImportBandTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true, 'services.google.client_id' => 'test-client', 'services.google.gemini_key' => 'test-key']);
    }

    private function createHtml(User $user, Role $role): string
    {
        return $this->actingAs($user)
            ->get(route('event.create', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();
    }

    private function scheduleHtml(User $user, Role $role): string
    {
        return $this->actingAs($user)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->getContent();
    }

    /** The band's own markup, or '' when the page has none. */
    private function block(string $html): string
    {
        $start = strpos($html, '<section class="import-options');

        return $start === false ? '' : substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    /** The tiles a page offers, in the order it offers them. */
    private function sources(string $html): array
    {
        preg_match_all('/data-import-source="([a-z]+)"/', $this->block($html), $matches);

        return $matches[1];
    }

    public function test_a_first_event_offers_the_places_events_live_ahead_of_the_form(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $html = $this->createHtml($owner, $venue);
        $block = $this->block($html);

        $this->assertSame(['google', 'link', 'flyer'], $this->sources($html), 'a venue usually keeps a calendar');
        $this->assertStringContainsString(__('messages.import_band_title'), $block);
        $this->assertStringContainsString(__('messages.import_band_by_hand'), $block);

        // Ahead of the form, and out of Vue's hands: the band sits inside the form's mount.
        $band = strpos($html, 'aria-labelledby="import-band-title" v-pre');
        $this->assertNotFalse($band);
        $this->assertLessThan(strpos($html, 'id="edit-form"'), $band);

        // Each tile opens the import page, Google's on its own steps.
        $import = route('event.show_import_ai', ['subdomain' => $venue->subdomain]);
        $this->assertStringContainsString('href="'.e($import.'?source=google').'" data-import-source="google"', $block);
        $this->assertStringContainsString('href="'.e($import).'" data-import-source="link"', $block);
    }

    public function test_the_order_follows_what_each_type_of_schedule_reaches_for(): void
    {
        $owner = $this->createOwner();

        $this->assertSame(['link', 'flyer', 'google'], $this->sources($this->createHtml($owner, $this->createRole($owner, 'talent'))));
        $this->assertSame(['link', 'google', 'flyer'], $this->sources($this->createHtml($owner, $this->createRole($owner, 'curator'))));
    }

    /** A tile only for what works here: left out, never shown locked. */
    public function test_a_source_this_person_or_install_cannot_use_is_left_out(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        // A schedule's Google connection is its owner's.
        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);
        $this->assertSame(['link', 'flyer'], $this->sources($this->createHtml($admin, $venue)));

        // Either AI service reads a flyer.
        config(['services.google.gemini_key' => null, 'services.openai.api_key' => 'test-openai']);
        $this->assertSame(['google', 'link', 'flyer'], $this->sources($this->createHtml($owner, $venue)));

        // No Google set up, and no AI to read a flyer: a link still works.
        config(['services.google.client_id' => null, 'services.openai.api_key' => null]);
        $this->assertSame(['link'], $this->sources($this->createHtml($owner, $venue)));
    }

    public function test_no_band_once_the_schedule_has_events_or_after_a_refused_save(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent');
        $createUrl = route('event.create', ['subdomain' => $talent->subdomain]);

        // A refused save is somebody already filling the form in.
        $this->actingAs($owner)->get($createUrl)->assertOk();
        $this->from($createUrl)
            ->post(route('event.store', ['subdomain' => $talent->subdomain]), $this->eventPayload(['name' => '']))
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors('name');
        $refused = $this->createHtml($owner, $talent);
        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $refused, 'sanity check: still a first event');
        $this->assertSame([], $this->sources($refused));

        $this->createEvent($talent, ['user_id' => $owner->id]);
        $this->assertSame([], $this->sources($this->createHtml($owner, $talent)));
    }

    /**
     * The person's first event is not the schedule's: a team member who has never made an event,
     * on a schedule that has plenty, is not asked whether its events are listed somewhere.
     */
    public function test_a_team_members_first_event_on_a_busy_schedule_has_no_band(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $this->createEvent($venue, ['user_id' => $owner->id]);

        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);

        $html = $this->createHtml($admin, $venue);
        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $html, 'sanity check: the admin\'s first event');
        $this->assertSame([], $this->sources($html));
    }

    /** A copy (Duplicate, a template) is a form already filled in. */
    public function test_no_band_over_a_copy(): void
    {
        $owner = $this->createOwner();
        $empty = $this->createRole($owner, 'venue');

        // Somebody else's event elsewhere, so the owner still has made none of their own.
        $elsewhere = $this->createRole($this->createOwner(), 'venue');
        $source = $this->createEvent($elsewhere);

        $html = $this->actingAs($owner)
            ->withSession(['cloned_event' => EventRepo::buildClonePayload($source)])
            ->get(route('event.create', ['subdomain' => $empty->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $html, 'sanity check: still a first event');
        $this->assertSame([], $this->sources($html));
    }

    public function test_a_schedule_with_no_events_asks_the_same_on_its_schedule_tab(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $html = $this->scheduleHtml($owner, $venue);
        $block = $this->block($html);
        $this->assertSame(['google', 'link', 'flyer'], $this->sources($html));
        $this->assertStringContainsString(__('messages.import_band_title'), $block);
        $this->assertStringContainsString('aria-labelledby="import-schedule-title" v-pre', $html);
        // The calendar's own Add Event below is the way by hand.
        $this->assertStringNotContainsString(__('messages.import_band_by_hand'), $block);

        // A viewer cannot add events.
        $viewer = $this->createOwner();
        $venue->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->assertSame([], $this->sources($this->scheduleHtml($viewer, $venue)));

        // Nor straight after an import was undone: its notice already offers to import again.
        $undone = $this->actingAs($owner)->withSession(['import_undone' => ['removed' => 3, 'kept' => 0]])
            ->get(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'schedule']))
            ->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.import_again'), $undone, 'sanity check: the notice is there');
        $this->assertSame([], $this->sources($undone));

        // It goes with the first event.
        $this->createEvent($venue, ['user_id' => $owner->id]);
        $this->assertSame([], $this->sources($this->scheduleHtml($owner, $venue)));
    }

    /** Adding an event is refused on a schedule that is not claimed, so nothing offers it there. */
    public function test_no_offer_on_a_schedule_that_is_not_claimed(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        DB::table('roles')->where('id', $venue->id)->update(['email_verified_at' => null, 'phone_verified_at' => null]);

        $this->assertSame([], $this->sources($this->scheduleHtml($owner, $venue)));
    }

    /** The tile and the page it opens answer the same question, so Google is never a dead end. */
    public function test_the_import_page_offers_google_to_exactly_whom_the_band_does(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $admin = $this->createOwner();
        $venue->users()->attach($admin->id, ['level' => 'admin']);

        $page = fn (User $user) => $this->actingAs($user)
            ->get(route('event.show_import_ai', ['subdomain' => $venue->subdomain]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('@click="openGoogle"', $page($owner));
        $this->assertStringNotContainsString('@click="openGoogle"', $page($admin));
    }
}
