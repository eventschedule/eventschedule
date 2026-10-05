<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The import page as an editor gets it, and the guest submission form that shares its view.
 *
 * One box takes a link, text or a flyer. Everything about links is the editor's: the guest form
 * is the same Blade file and must come out of it exactly as it went in, because a signed-out
 * visitor must never be offered a way to make the server fetch an address.
 */
class ImportPageRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.gemini_key' => 'test-key', 'services.openai.api_key' => null]);

        $this->owner = $this->createOwner();
        // require_account defaults to true, and such a curator's guest page redirects to the
        // structured request form instead of rendering this one.
        $this->role = $this->createRole($this->owner, 'curator', ['accept_requests' => true, 'require_account' => false]);
    }

    private function importPage(?User $user = null, array $query = []): string
    {
        return $this->actingAs($user ?? $this->owner)
            ->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain] + $query))
            ->assertOk()
            ->getContent();
    }

    private function guestPage(): string
    {
        auth()->logout();

        return $this->get(route('event.guest_import', ['subdomain' => $this->role->subdomain]))
            ->assertOk()
            ->getContent();
    }

    /**
     * The v-if conditions of the blocks each marker sits inside, by walking the page's div tags
     * in order. Deliberately not DOMDocument: an HTML parser repairs a missing closing tag, and
     * a missing closing tag is exactly what this is here to see.
     *
     * @return array<string, list<string>> marker => enclosing v-if conditions, outermost first
     */
    private function enclosingConditions(string $html, array $markers): array
    {
        $start = strpos($html, 'id="event-import-app"');
        $form = substr($html, strrpos(substr($html, 0, $start), '<form'), strpos($html, '</form>', $start) - $start);

        preg_match_all('#<div\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>|</div>#', $form, $tags, PREG_OFFSET_CAPTURE);

        $open = [];
        $found = [];
        $cursor = 0;
        foreach ($tags[0] as [$tag, $offset]) {
            // Anything between the previous tag and this one sits inside what is open now.
            $between = substr($form, $cursor, $offset - $cursor).($tag === '</div>' ? '' : $tag);
            foreach ($markers as $marker) {
                if (! isset($found[$marker]) && str_contains($between, $marker)) {
                    $found[$marker] = array_values(array_filter($open));
                }
            }
            $cursor = $offset + strlen($tag);

            if ($tag === '</div>') {
                array_pop($open);
            } else {
                $open[] = preg_match('/\bv-if="([^"]*)"/', $tag, $condition) ? $condition[1] : '';
            }
        }

        return $found;
    }

    public function test_an_editor_gets_one_box_for_a_link_text_or_a_flyer(): void
    {
        $html = $this->importPage();

        $this->assertStringContainsString('placeholder="Paste a link, paste text, or add a flyer"', $html);
        $this->assertStringContainsString('For example: your website&#039;s events page', $html);
        // The buttons say what they do.
        $this->assertStringContainsString('v-text="submitLabel"', $html);
        $this->assertStringContainsString('Read link', $html);
        $this->assertStringContainsString('Read flyer', $html);
        $this->assertStringContainsString('Read text', $html);
        // A link is sent as a link, by name.
        $this->assertStringContainsString("formData.append('source_url', this.linkUrl)", $html);
        $this->assertStringContainsString('isGuestPage: false', $html);
        $this->assertStringContainsString('linksOnly: false', $html);
        // And a row is saved as what it is: with its repeat, and naming the import it came from.
        $this->assertStringContainsString('parsed.recurrence.fields', $html);
        $this->assertStringContainsString('import_token: (this.preview.meta', $html);
        // The notice about the AI service says what happens to a link too.
        $this->assertStringContainsString('A link is read directly when it is a calendar', $html);
        // The other ways in are on the page, not behind a button.
        $this->assertStringContainsString('Other ways to bring events in', $html);
        $this->assertStringContainsString('Import from Eventbrite', $html);
        $this->assertStringNotContainsString('More Options', $html);
    }

    public function test_two_or_more_events_are_a_list_to_choose_from(): void
    {
        $html = $this->importPage();

        // One row per event with the full card on request, a count on the button, and a bar
        // that says how far a bulk add has got.
        $this->assertStringContainsString('v-if="listMode && isRowVisible(idx)"', $html);
        $this->assertStringContainsString('v-if="!listMode || expandedRow === idx"', $html);
        $this->assertStringContainsString('@click="addSelected"', $html);
        $this->assertStringContainsString('role="progressbar"', $html);
        $this->assertStringContainsString('id="import-list-heading"', $html);
        // The zone the times are in is on the page, with a way to change it.
        $this->assertStringContainsString('Times shown in __Z__', $html);
        $this->assertStringContainsString(route('role.edit', ['subdomain' => $this->role->subdomain]), $html);
        // What was read is summed up above one event from a link as well as above a list.
        $this->assertStringContainsString('v-if="showsReadSummary"', $html);
        // A card's Save saves its row, which for a listed series is each of its dates; nothing
        // on a row opens while the list is being added; and a failed row keeps its checkbox.
        $this->assertStringContainsString('@click="saveRow(idx)"', $html);
        $this->assertStringContainsString('@click="expandRow(idx)" :disabled="isAddingAll"', $html);
        $this->assertStringNotContainsString("rowState(idx) === 'error'\" class=\"h-5 w-5", $html);

        // A full import is up to 100 saves, one request each.
        $this->assertContains('throttle:120,1', app('router')->getRoutes()->getByName('event.import')->gatherMiddleware());

        // The guest form has one event and none of this.
        $guest = $this->guestPage();
        $this->assertStringNotContainsString('isRowVisible(idx)"', $guest);
        $this->assertStringNotContainsString('@click="addSelected"', $guest);
        $this->assertStringNotContainsString('id="import-list-heading"', $guest);
    }

    public function test_the_old_source_list_address_lands_on_the_import_page(): void
    {
        $this->actingAs($this->owner)
            ->get(route('event.show_import', ['subdomain' => $this->role->subdomain]))
            ->assertRedirect(route('event.show_import_ai', ['subdomain' => $this->role->subdomain]));
    }

    public function test_with_no_ai_key_the_box_still_takes_links(): void
    {
        config(['services.google.gemini_key' => null]);

        $html = $this->importPage();

        $this->assertStringContainsString('id="event-import-app"', $html);
        $this->assertStringContainsString('placeholder="Paste a link to your events page or calendar"', $html);
        $this->assertStringContainsString('linksOnly: true', $html);
        // No flyer to add when nothing could read it.
        $this->assertStringNotContainsString('@click="openDetailsFileSelector"', $html);
        $this->assertStringContainsString('Calendar links and most event pages work without one.', $html);
        // Nothing here reaches an AI service, so there is no notice saying it does.
        $this->assertStringNotContainsString('are read by an AI service', $html);
        // Setting a key up is for whoever runs the install, and it is not "required" here.
        $this->assertStringNotContainsString('Setup Required', $html);
        $this->assertStringNotContainsString('Get API Key', $html);

        $admin = $this->createOwner(admin: true);
        $this->followRole($admin, $this->role, 'admin');
        $asAdmin = $this->importPage($admin);
        $this->assertStringContainsString('Get API Key', $asAdmin);
        $this->assertStringContainsString('Optional', $asAdmin);
        $this->assertStringNotContainsString('Setup Required', $asAdmin);
    }

    public function test_the_guest_form_is_untouched_by_any_of_it(): void
    {
        $html = $this->guestPage();

        $this->assertStringContainsString('placeholder="Type event details or drag &amp; drop an image here..."', $html);
        $this->assertStringContainsString('isGuestPage: true', $html);
        $this->assertStringContainsString('are read by an AI service to fill in the event details.</p>', $html);
        $this->assertStringNotContainsString('A link is read directly', $html);
        // Nothing an editor gets: no labels, no link line, no examples, no other sources.
        $this->assertStringNotContainsString('v-text="submitLabel"', $html);
        $this->assertStringNotContainsString('v-if="isLink"', $html);
        $this->assertStringNotContainsString('We will read this page', $html);
        $this->assertStringNotContainsString('For example: your website', $html);
        $this->assertStringNotContainsString('Other ways to bring events in', $html);
        $this->assertStringNotContainsString('Import from Eventbrite', $html);
        $this->assertStringNotContainsString('parsed.recurrence', $html);
        $this->assertStringNotContainsString('import_token', $html);
        $this->assertStringNotContainsString('source_url', $html);
        // It still posts to the endpoint that never fetches.
        $this->assertStringContainsString('guest-parse', $html);
    }

    public function test_a_guest_with_no_ai_key_sees_the_setup_notice_and_no_form(): void
    {
        config(['services.google.gemini_key' => null]);

        $html = $this->guestPage();

        $this->assertStringContainsString('Setup Required', $html);
        $this->assertStringNotContainsString('id="event-import-app"', $html);
    }

    /**
     * The block that holds the box is hidden once a preview appears. It used to be one closing tag
     * short, so the "Show all fields" and "Save All" card sat inside it and was never on screen.
     */
    public function test_what_shows_with_a_preview_is_not_inside_what_hides_with_one(): void
    {
        $hidesWithAPreview = '!preview || !preview.parsed || preview.parsed.length === 0';

        // ?automate is what puts Save All on the page for somebody who is not the admin.
        $inside = $this->enclosingConditions($this->importPage(null, ['automate' => 1]), [
            '@click="handleSaveAll"', 'id="event_details"', 'Import from Eventbrite',
        ]);

        // The box and the other sources belong together, and go when the preview comes.
        $this->assertSame([$hidesWithAPreview], $inside['id="event_details"']);
        $this->assertSame([$hidesWithAPreview], $inside['Import from Eventbrite']);
        // What is for a preview is not inside them.
        $this->assertNotContains($hidesWithAPreview, $inside['@click="handleSaveAll"']);
    }

    public function test_the_card_above_the_results_is_there_only_when_it_holds_something(): void
    {
        // It holds a checkbox for the installation's admin and Save All for an automated run.
        // Since it stopped being hidden by accident it was an empty card for everybody else.
        $card = 'preview.parsed.length > 0 && (!listMode || ';

        $this->assertStringNotContainsString($card, $this->importPage());
        $this->assertStringNotContainsString($card, $this->guestPage());

        // Save All is for one event. With a list the card would hold nothing for an automated
        // run, so it is not drawn then.
        $automated = $this->importPage(null, ['automate' => 1]);
        $this->assertStringContainsString($card.'false)"', $automated);
        $this->assertStringContainsString('@click="handleSaveAll" v-if="!listMode && (', $automated);

        // The admin's checkbox is theirs with a list too: it was out of reach there.
        $admin = $this->createOwner();
        $admin->forceFill(['is_admin' => true])->save();
        $this->followRole($admin, $this->role, 'admin');
        $adminPage = $this->importPage($admin);
        $this->assertStringContainsString($card.'true)"', $adminPage);
        $this->assertStringContainsString('id="show_all_fields"', $adminPage);
    }

    public function test_a_list_is_saved_through_one_queue_and_a_series_keeps_what_is_its_own(): void
    {
        // What these lines do is checked in a browser; this holds them where they are. Each was
        // a defect when it read otherwise.
        $html = $this->importPage();

        // A card's Save in a list goes through the queue "Add" uses, under its lock.
        $this->assertStringContainsString('await this.runQueue(this.rowIndexes(idx).filter(i => ! this.savedEvents[i]), false);', $html);
        $this->assertStringContainsString('await this.runQueue(this.preview.parsed.map((event, i) => i).filter(i => this.isQueued(i)), true);', $html);
        $this->assertSame(2, substr_count($html, 'await this.handleSave(idx, true);'), 'one loop sends rows: a send, and its one retry');
        // A series with a date still to add keeps its Save.
        $this->assertStringContainsString('<template v-if="listMode ? rowState(idx) === \'saved\' : savedEvents[idx]">', $html);

        // A date of a series is saved at its own venue when the source put it somewhere else,
        // and a row is ticked, counted and sent whole.
        $this->assertStringContainsString('const venueIdx = this.venueIndex(idx);', $html);
        $this->assertStringContainsString('return !! this.selectedRows[idx] && ! this.savedEvents[idx] && this.rowComplete(idx);', $html);
        $this->assertStringContainsString('this.selectedRows = this.preview.parsed.map((event, i) => this.rowComplete(i) && ! event.event_url);', $html);

        // A list with nothing left to add finishes, however it got there.
        $this->assertSame(2, substr_count($html, 'this.finishList();'));
        $this->assertStringContainsString(json_encode(route('event.import_done', ['subdomain' => $this->role->subdomain])), $html);

        // One event from a link: its header is announced, and "Start over" is not beside a
        // "Clear" that does the same.
        $this->assertStringContainsString('if (this.showsReadSummary) {', $html);
        $this->assertStringContainsString('<button v-if="listMode" type="button" @click="handleClear"', $html);

        // Back, after something was added and the page cleared, still goes to see it.
        $this->assertStringContainsString('this.addedAny = true;', $html);
        $this->assertStringContainsString('if (app && app.addedAny) {', $html);
        $this->assertStringNotContainsString('app.savedEvents.some(Boolean)', $html);
    }
}
