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

    private function importPage(?User $user = null): string
    {
        return $this->actingAs($user ?? $this->owner)
            ->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain]))
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
        // The notice about the AI service says what happens to a link too.
        $this->assertStringContainsString('A link is read directly when it is a calendar', $html);
        // The other ways in are on the page, not behind a button.
        $this->assertStringContainsString('Other ways to bring events in', $html);
        $this->assertStringContainsString('Import from Eventbrite', $html);
        $this->assertStringNotContainsString('More Options', $html);
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

        $inside = $this->enclosingConditions($this->importPage(), [
            '@click="handleSaveAll"', 'id="event_details"', 'Import from Eventbrite',
        ]);

        // The box and the other sources belong together, and go when the preview comes.
        $this->assertSame([$hidesWithAPreview], $inside['id="event_details"']);
        $this->assertSame([$hidesWithAPreview], $inside['Import from Eventbrite']);
        // What is for a preview is not inside them.
        $this->assertNotContains($hidesWithAPreview, $inside['@click="handleSaveAll"']);
    }
}
