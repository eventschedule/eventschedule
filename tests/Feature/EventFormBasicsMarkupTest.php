<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The markup behind five things the event form did wrong in the browser.
 *
 * tests/Browser/EventFormBasicsTest.php is the half that proves each one: it runs the page. This
 * is the half that runs on every push without a browser, and it pins the cause rather than the
 * symptom - a control wired by the template that renders it, a row hidden instead of removed, a
 * wrapper that is closed, a typed name that reaches the page's data.
 */
class EventFormBasicsMarkupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent');
    }

    private function createUrl(): string
    {
        return route('event.create', ['subdomain' => $this->role->subdomain]);
    }

    private function editUrl(Event $event): string
    {
        return route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    private function page(string $url): string
    {
        return $this->actingAs($this->owner)->get($url)->assertOk()->getContent();
    }

    /**
     * A wrapper opened at the top of the form was never closed, so everything after the form -
     * the cancel, restore and notify-preview forms, the modals, the page script - was parsed
     * inside it, and so inside the event form.
     */
    public function test_the_event_form_closes_every_wrapper_it_opens(): void
    {
        $event = $this->createEvent($this->role);

        foreach (['create' => $this->createUrl(), 'edit' => $this->editUrl($event)] as $name => $url) {
            $html = $this->page($url);
            $start = strpos($html, 'id="edit-form"');
            $form = substr($html, $start, strpos($html, '</form>', $start) - $start);

            $this->assertGreaterThan(200, substr_count($form, '<div'), "sanity check: the {$name} form was sliced whole");
            $this->assertSame(
                preg_match_all('/<div\b/', $form),
                substr_count($form, '</div>'),
                "the {$name} form opens and closes the same number of wrappers"
            );
        }
    }

    /**
     * The address fields sit under v-if, so their buttons are new elements every time the venue
     * choice changes. Listeners bound by id after load stayed on the old ones, and a form opened
     * with a saved venue never rendered the buttons to bind in the first place.
     */
    public function test_the_venue_address_buttons_are_wired_by_the_template(): void
    {
        // Validate and Accept render only with a key. Nothing here saves a schedule, so nothing
        // geocodes, but no test may be able to reach Google: fake it anyway.
        config(['services.google.backend' => 'test-key']);
        Http::fake();

        $html = $this->page($this->createUrl());

        foreach (['view_map_button' => 'viewVenueMap', 'validate_button' => 'validateVenueAddress', 'accept_button' => 'acceptVenueAddress'] as $id => $method) {
            $this->assertSame(1, preg_match('/<button[^>]*id="'.$id.'"[^>]*>/', $html, $tag), "{$id} is rendered");
            $this->assertStringContainsString('@click="'.$method.'"', $tag[0]);
            $this->assertStringNotContainsString("getElementById('{$id}')", $html, "{$id} is not also bound by id");
        }

        Http::assertNothingSent();
    }

    /**
     * The toggle and its end-date row are wired once, on load. Removing them for a recurring
     * event put unwired copies back when the event became one-time again.
     */
    public function test_multi_day_is_hidden_while_recurring_and_never_removed(): void
    {
        $html = $this->page($this->createUrl());

        $this->assertStringNotContainsString('v-if="!isRecurring"', $html);
        $this->assertMatchesRegularExpression('/<div v-show="!isRecurring"[^>]*>\s*<div[^>]*>\s*<label[^>]*>\s*<input type="hidden" name="is_multi_day"/', $html);
        $this->assertMatchesRegularExpression('/<div v-show="!isRecurring">\s*<div id="multi_day_end_date_row"/', $html);
    }

    public function test_an_ordinary_load_has_no_refused_name(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Saved Name']);

        $this->assertStringContainsString('refusedEventName: null,', $this->page($this->createUrl()));

        $html = $this->page($this->editUrl($event));
        $this->assertStringContainsString('refusedEventName: null,', $html);
        $this->assertStringContainsString('eventName: "Saved Name",', $html);
    }

    /**
     * The name input is a v-model field, so what it shows is Vue's value, not the value attribute
     * old() fills. Vue's value came from the model, and mounted() then replaced it with the saved
     * name - or, on a new event, with the schedule's own name.
     */
    public function test_a_name_typed_before_a_refused_save_reaches_the_page_data(): void
    {
        $tooLong = 'https://example.org/'.str_repeat('a', 600);

        // A new event.
        $this->actingAs($this->owner)->from($this->createUrl())
            ->post(route('event.store', ['subdomain' => $this->role->subdomain]), [
                'name' => 'Typed On Create', 'starts_at' => now()->addDays(5)->format('Y-m-d').' 20:00:00', 'duration' => 2, 'event_url' => $tooLong,
            ])
            ->assertRedirect($this->createUrl())
            ->assertSessionHasErrors('event_url');

        $html = $this->page($this->createUrl());
        $this->assertStringContainsString('eventName: "Typed On Create",', $html);
        $this->assertStringContainsString('refusedEventName: "Typed On Create",', $html);
        $this->assertSame(0, Event::where('name', 'Typed On Create')->count(), 'sanity check: the save really was refused');

        // An existing one.
        $event = $this->createEvent($this->role, ['name' => 'Saved Name']);
        $this->actingAs($this->owner)->from($this->editUrl($event))
            ->put(route('event.update', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), [
                'name' => 'Typed On Edit', 'starts_at' => $event->starts_at, 'duration' => 2, 'event_url' => $tooLong,
            ])
            ->assertRedirect($this->editUrl($event))
            ->assertSessionHasErrors('event_url');

        $html = $this->page($this->editUrl($event));
        $this->assertStringContainsString('refusedEventName: "Typed On Edit",', $html);
        $this->assertSame('Saved Name', $event->fresh()->name);
    }
}
