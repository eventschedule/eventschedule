<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The places events can be brought in from, offered where a schedule has none yet: above the
 * first-event form, and on the Schedule tab of a schedule with no events (<x-import-options>,
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

    /** The tiles a page offers, in the order it offers them. */
    private function sources(string $html): array
    {
        preg_match_all('/data-import-source="([a-z]+)"/', $html, $matches);

        return $matches[1];
    }

    public function test_a_first_event_offers_the_places_events_live_ahead_of_the_form(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $html = $this->createHtml($owner, $venue);

        $this->assertSame(['google', 'link', 'flyer'], $this->sources($html), 'a venue usually keeps a calendar');
        $this->assertStringContainsString(__('messages.import_band_title'), $html);
        $this->assertStringContainsString(__('messages.import_band_by_hand'), $html);

        // Ahead of the form, and out of Vue's hands: the band sits inside the form's mount.
        $band = strpos($html, 'aria-labelledby="import-options-title" v-pre');
        $this->assertNotFalse($band);
        $this->assertLessThan(strpos($html, 'id="edit-form"'), $band);

        // Each tile opens the import page, Google's on its calendar chooser.
        $import = route('event.show_import_ai', ['subdomain' => $venue->subdomain]);
        $this->assertStringContainsString('href="'.e($import.'?source=google').'" data-import-source="google"', $html);
        $this->assertStringContainsString('href="'.e($import).'" data-import-source="link"', $html);
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

        // No Google set up, and no AI to read a flyer: a link still works.
        config(['services.google.client_id' => null, 'services.google.gemini_key' => null, 'services.openai.api_key' => null]);
        $this->assertSame(['link'], $this->sources($this->createHtml($owner, $venue)));
    }

    public function test_no_band_once_events_exist_or_after_a_refused_save(): void
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

    public function test_a_schedule_with_no_events_offers_the_same_on_its_schedule_tab(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');

        $html = $this->scheduleHtml($owner, $venue);
        $this->assertSame(['google', 'link', 'flyer'], $this->sources($html));
        $this->assertStringContainsString(__('messages.no_events_yet'), $html);
        $this->assertStringContainsString('href="'.e(route('event.create', ['subdomain' => $venue->subdomain])).'"', $html);
        $this->assertStringContainsString(__('messages.add_an_event'), $html);

        // A viewer cannot add events.
        $viewer = $this->createOwner();
        $venue->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->assertSame([], $this->sources($this->scheduleHtml($viewer, $venue)));

        // It goes with the first event.
        $this->createEvent($venue, ['user_id' => $owner->id]);
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
