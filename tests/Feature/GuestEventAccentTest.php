<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\GuestTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The event page takes its colours from the tokens the layout prints (App\Utils\GuestTheme), and
 * decides whose look it wears by the one rule they follow.
 *
 * It used to paste the schedule's raw accent into some sixty style attributes: as the fill of a
 * button, as text, as an outline. A yellow accent as text on a white panel and a black one as an
 * outline on a dark panel were both close to invisible, and the page asked its own, looser
 * question about WHOSE accent to use, so an event could sit on one schedule's background under
 * another schedule's buttons.
 */
class GuestEventAccentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function page(Event $event, Role $role, string $query = ''): string
    {
        return $this->get($event->fresh()->getGuestUrl($role->subdomain).$query)->assertOk()->getContent();
    }

    private function body(string $html): string
    {
        // After the head, where the theme-color and the tokens themselves are.
        return substr($html, strpos($html, '</head>'));
    }

    public function test_fills_and_text_take_the_tokens_not_the_raw_colour(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['accent_color' => '#ffd90f']);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $this->createTicket($event, ['price' => 10, 'quantity' => 5]);

        $html = $this->page($event, $role, '?tickets=true');
        $body = $this->body($html);

        // The button that sells: the fill and the text made for it.
        $cta = substr($body, strpos($body, 'id="gp-event-cta"'), 3000);
        $this->assertStringContainsString('style="background-color: var(--es-accent); color: var(--es-accent-text);"', $cta);
        // The month in the date tile is text, so it takes the accent made readable on a panel:
        // yellow on white was 1.4 to 1.
        $date = substr($body, strpos($body, 'id="gp-event-date"'), 1200);
        $this->assertStringContainsString('style="color: var(--es-accent-readable);"', $date);
        // And the form's own Checkout.
        $form = substr($body, strpos($body, 'id="ticket-selector"'));
        $this->assertStringContainsString('style="background-color: var(--es-accent); color: var(--es-accent-text);"', $form);

        // Nothing is filled or written in the raw colour any more. What is left of it is a
        // literal for script or an alpha-suffixed tint, never a whole style of its own.
        $this->assertSame(0, preg_match('/style="[^"]*(?:background-color|(?<![-a-z])color|border-color): #ffd90f[;"]/i', $body));

        // The tokens themselves are on the page, in both modes.
        [$light, $dark] = GuestTheme::fromAccent('#ffd90f')->tokens();
        $this->assertStringContainsString('--es-accent-readable: '.$light['--es-accent-readable'].';', $html);
        $this->assertStringContainsString('--es-accent-readable: '.$dark['--es-accent-readable'].';', $html);
    }

    public function test_the_page_wears_one_schedules_look_from_its_background_to_its_buttons(): void
    {
        // A venue's page for an event with a claimed performer who never chose a background.
        // The layout paints the venue's background (its rule asks for a claimed schedule WITH a
        // background); the page's own colours used to be the performer's, because it only asked
        // whether the performer was claimed.
        $venue = $this->createRole($this->createOwner(), 'venue', ['accent_color' => '#dc2626']);
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'The Nightjars', 'accent_color' => '#16a34a']);
        $this->assertTrue($talent->isClaimed(), 'fixture');
        $this->assertFalse($talent->hasConfiguredBackground(), 'fixture');

        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $html = $this->page($event, $venue);

        $this->assertStringContainsString('--es-accent: '.GuestTheme::fromAccent('#dc2626')->tokens()[0]['--es-accent'].';', $html, 'the tokens are the venue\'s');
        $this->assertStringNotContainsString('#16a34a', $this->body($html), 'and nothing on the page is in the performer\'s colour');

        // Once the performer has a look of its own, the whole page is theirs.
        $talent->forceFill(['background' => 'solid', 'background_color' => '#052e16'])->save();
        $html = $this->page($event, $venue);
        $this->assertStringContainsString('--es-accent: '.GuestTheme::fromAccent('#16a34a')->tokens()[0]['--es-accent'].';', $html);
        // (The tab's theme-color stays the venue's: that is the colour of the schedule's own
        // installed app, set in the head, not this page's look.)
        $this->assertStringNotContainsString('#dc2626', $this->body($html));
    }
}
