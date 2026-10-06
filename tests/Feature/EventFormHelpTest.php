<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Help, on the event form, opens the part of the user guide for what is on screen.
 *
 * The form is tabs, and two of the tabs are rows that open in place. Pressing any of them moves
 * the Help link (layouts/navigation), by a key built from the element: a tab's data-section, a
 * row's "ticket-tab-" or "engagement-tab-" plus its data-tab, a ticket tile's "ticket-mode-" plus
 * its value. Three things have to hold for each, and each has been wrong before without anything
 * failing: the key is in the map HelpUtils gives this page, the place it names exists in the
 * guide, and the guide's own search can find that place.
 *
 * A tab or a row added to the form without its line in HelpUtils fails here, as does a section of
 * the guide renamed out from under its link.
 */
class EventFormHelpTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);

        $this->owner = $this->createOwner();
        // Carpool is the one row that depends on the schedule, so the schedule has it.
        $this->role = $this->createRole($this->owner, 'talent', ['carpool_enabled' => true]);
        $this->event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $this->createTicket($this->event, ['type' => 'General', 'price' => 10]);
    }

    /** @return array<string, string> the page under test => its URL */
    private function pages(): array
    {
        return [
            'edit' => route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]),
            'add' => route('event.create', ['subdomain' => $this->role->subdomain]),
        ];
    }

    private function page(string $url): string
    {
        return $this->actingAs($this->owner)->get($url)->assertOk()->getContent();
    }

    /** The map the layout inlines for the Help link, as the browser gets it. */
    private function anchorMap(string $html): array
    {
        $this->assertSame(1, preg_match('/var anchorMap = (\{.*?\});\n/s', $html, $found), 'the page carries a Help map');

        return json_decode($found[1], true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Every key the page can ask Help for, read from the page: its tabs, the rows of the Tickets
     * and Engagement tabs, and the three ticket tiles.
     *
     * @return string[]
     */
    private function keysOnThePage(string $html): array
    {
        preg_match_all('/<a href="#(section-[a-z-]+)" class="section-nav-link" data-section="\1"/', $html, $tabs);
        preg_match_all('/class="event-subrow ticket-tab" data-tab="([a-z_]+)"/', $html, $ticketRows);
        preg_match_all('/class="event-subrow engagement-tab" data-tab="([a-z_]+)"/', $html, $engagementRows);
        preg_match_all('/class="event-tile ticket-mode-radio" id="ticket_choice_[a-z]+" value="([a-z]+)"/', $html, $tiles);

        return array_values(array_unique(array_merge(
            $tabs[1],
            array_map(fn ($row) => 'ticket-tab-'.$row, $ticketRows[1]),
            array_map(fn ($row) => 'engagement-tab-'.$row, $engagementRows[1]),
            array_map(fn ($tile) => 'ticket-mode-'.$tile, $tiles[1]),
        )));
    }

    /** The ids a page of the guide gives its sections, from the view itself. */
    private function idsInTheGuide(string $path): array
    {
        static $read = [];

        if (! isset($read[$path])) {
            $view = resource_path('views/marketing'.$path.'.blade.php');
            $this->assertFileExists($view, "Help names {$path}, which is not a page of the guide");
            preg_match_all('/\sid="([a-z0-9-]+)"/', file_get_contents($view), $ids);
            $read[$path] = $ids[1];
        }

        return $read[$path];
    }

    public function test_every_tab_and_row_of_the_form_has_a_place_in_the_guide(): void
    {
        foreach ($this->pages() as $name => $url) {
            $html = $this->page($url);
            $map = $this->anchorMap($html);
            $keys = $this->keysOnThePage($html);

            // The fixture shows the whole form, so a regex that stopped matching fails here
            // rather than passing over nothing.
            foreach (['section-details', 'section-tickets', 'section-participants', 'section-agenda', 'section-gallery', 'section-listing', 'section-engagement', 'section-event-settings'] as $tab) {
                $this->assertContains($tab, $keys, "{$tab} is a tab of the {$name} page");
            }
            foreach (['ticket-tab-payment', 'ticket-tab-options', 'ticket-tab-promo_codes', 'ticket-tab-add_ons', 'engagement-tab-polls', 'engagement-tab-fan_content', 'engagement-tab-feedback', 'engagement-tab-carpool', 'ticket-mode-rsvp', 'ticket-mode-tickets', 'ticket-mode-external'] as $row) {
                $this->assertContains($row, $keys, "{$row} is on the {$name} page");
            }

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $map, "{$key} on the {$name} page has no Help page: add it to HelpUtils");
            }

            // Every place the map names, whether or not something on this page asks for it.
            foreach ($map as $key => $target) {
                $this->assertSame(1, preg_match('~^(?:https?://[^/]+)?(/docs/[a-z/-]+)#([a-z0-9-]+)$~', $target, $parts), "{$key} names a section of a page: {$target}");
                $this->assertContains($parts[2], $this->idsInTheGuide($parts[1]), "{$key} opens {$parts[1]}#{$parts[2]}, which is not in the guide");
            }
        }
    }

    /** A place Help opens for a tab or a row is one the guide's search can also find. */
    public function test_the_guides_search_knows_each_of_those_places(): void
    {
        $index = (new \ReflectionMethod(MarketingController::class, 'getDocSearchIndex'))->invoke(app(MarketingController::class));
        $indexed = array_map(fn ($entry) => parse_url($entry['url'], PHP_URL_PATH).'#'.parse_url($entry['url'], PHP_URL_FRAGMENT), $index);

        $html = $this->page($this->pages()['edit']);
        $map = $this->anchorMap($html);

        foreach ($this->keysOnThePage($html) as $key) {
            $target = parse_url($map[$key], PHP_URL_PATH).'#'.parse_url($map[$key], PHP_URL_FRAGMENT);
            $this->assertContains($target, $indexed, "{$key} opens {$target}, which the guide's search does not list");
        }
    }

    /**
     * The link follows what is pressed. The branches are the layout's; what this form owes them
     * is the classes and attributes they read, and the one press that has no key of its own:
     * "Not needed" puts Help back on the tab's page.
     */
    public function test_pressing_a_tab_or_a_row_moves_the_help_link(): void
    {
        $html = $this->page($this->pages()['edit']);

        foreach ([
            "e.target.closest('.section-nav-link, .mobile-section-header')",
            "e.target.closest('.ticket-tab')",
            "e.target.closest('.engagement-tab')",
            "e.target.closest('.ticket-mode-radio')",
            "e.target.closest('#ticket_choice_none')",
        ] as $branch) {
            $this->assertStringContainsString($branch, $html);
        }

        // A phone's accordion headers name the same tabs.
        preg_match_all('/class="mobile-section-header[^"]*"[^>]*data-section="(section-[a-z-]+)"/', $html, $headers);
        preg_match_all('/<a href="#(section-[a-z-]+)" class="section-nav-link"/', $html, $tabs);
        $this->assertNotEmpty($headers[1]);
        $this->assertEqualsCanonicalizing($tabs[1], array_values(array_unique($headers[1])));
    }
}
