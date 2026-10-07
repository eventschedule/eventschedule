<?php

namespace Tests\Feature;

use App\Utils\GuestTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest kit: a schedule's colours as tokens on its guest pages, the shared stylesheet, and the
 * five components built on them. GuestThemeTest holds how the colours are worked out.
 */
class GuestKitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_guest_page_carries_the_schedules_colours_before_the_owners_css(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'accent_color' => '#FFD90F',
            'custom_css' => '.owner-rule { color: rebeccapurple; }',
        ]);
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $theme = GuestTheme::fromAccent('#FFD90F');
        $tokens = strpos($html, 'body { --es-accent: #ffd90f; --es-accent-text: #000000;');
        $this->assertNotFalse($tokens, 'the fill and the label on it');
        $this->assertStringContainsString('--es-accent-readable: '.$theme->readable.';', $html, 'a yellow that can be read as text');
        $this->assertStringContainsString('.dark body { --es-accent: #ffd90f;', $html);

        $kit = strpos($html, '.gk-btn-primary {');
        $owner = strpos($html, '.owner-rule { color: rebeccapurple; }');
        $this->assertNotFalse($kit);
        $this->assertNotFalse($owner);
        $this->assertLessThan($owner, $tokens, 'an owner\'s rule for a token comes later, so it wins');
        $this->assertLessThan($owner, $kit, 'and so does an owner\'s rule against a kit class');

        $this->assertSame(1, substr_count($html, '.gk-btn-primary {'), 'the kit is printed once');
    }

    public function test_the_kit_never_outranks_an_owners_css_and_mirrors_for_right_to_left(): void
    {
        $css = file_get_contents(resource_path('views/partials/guest-kit-styles.blade.php'));
        $css = substr($css, strpos($css, '<style'));

        $this->assertStringNotContainsString('!important', $css);
        // No promised id is styled here: an owner's rule for #gp-... must have nothing to beat.
        $this->assertDoesNotMatchRegularExpression('/#gp-/', $css);
        // One class per rule (a state such as :hover or [disabled] aside), so a tie is a tie.
        preg_match_all('/^\s*([^@{}\n][^{}\n]*)\{/m', $css, $rules);
        foreach ($rules[1] as $selectorList) {
            foreach (explode(',', $selectorList) as $selector) {
                $selector = trim($selector);
                // The style tag itself, the two token blocks, and the print and motion queries' own lines.
                if ($selector === 'body' || $selector === '.dark body' || ! str_starts_with($selector, '.')) {
                    continue;
                }
                $this->assertSame(1, preg_match_all('/\.[a-z]/', $selector), 'one class in: '.$selector);
            }
        }
        // Logical properties only.
        $this->assertDoesNotMatchRegularExpression('/\b(margin|padding|border)-(left|right)\b|\b(left|right)\s*:|text-align:\s*(left|right)/', $css);
        // Comments are Blade's, so the notes do not travel to every visitor.
        $this->assertStringNotContainsString('/*', $css);
    }

    public function test_the_components_draw_the_kits_classes(): void
    {
        $button = Blade::render('<x-guest.button id="go" size="lg" block>Get tickets</x-guest.button>');
        $this->assertStringContainsString('<button type="button"', $button);
        $this->assertStringContainsString('class="gk-btn gk-btn-primary gk-btn-lg gk-btn-block"', $button);
        $this->assertStringContainsString('id="go"', $button);

        $link = Blade::render('<x-guest.button variant="secondary" href="/somewhere">Back</x-guest.button>');
        $this->assertStringContainsString('<a href="/somewhere"', $link);
        $this->assertStringContainsString('gk-btn gk-btn-secondary', $link);

        // A variant that does not exist is the primary, not a class nobody wrote.
        $this->assertStringContainsString('gk-btn gk-btn-primary', Blade::render('<x-guest.button variant="loud">x</x-guest.button>'));

        $panel = Blade::render('<x-guest.panel as="section" id="gp-something">x</x-guest.panel>');
        $this->assertStringContainsString('<section', $panel);
        $this->assertStringContainsString('gk-panel', $panel);
        $this->assertStringContainsString('bg-white/95', $panel, 'the classes an owner\'s CSS may already name');
        $this->assertStringContainsString('gk-pad', $panel);
        $this->assertStringNotContainsString('gk-pad', Blade::render('<x-guest.panel :pad="false">x</x-guest.panel>'));

        $this->assertStringContainsString('<h2 class="gk-h"', Blade::render('<x-guest.heading>About</x-guest.heading>'));
        $this->assertStringContainsString('<h3 class="gk-h gk-h-lg"', Blade::render('<x-guest.heading :level="3" large>About</x-guest.heading>'));

        $this->assertStringContainsString('class="gk-chip gk-chip-out"', Blade::render('<x-guest.chip tone="out">Sold out</x-guest.chip>'));
        $this->assertStringContainsString('class="gk-chip"', Blade::render('<x-guest.chip>Online</x-guest.chip>'));

        $bad = Blade::render('<x-guest.notice tone="bad">It failed</x-guest.notice>');
        $this->assertStringContainsString('role="alert"', $bad);
        $this->assertStringContainsString('gk-note gk-note-bad', $bad);
        $plain = Blade::render('<x-guest.notice>Nothing was charged<x-slot:actions><button>Close</button></x-slot:actions></x-guest.notice>');
        $this->assertStringContainsString('role="status"', $plain);
        $this->assertStringContainsString('<button>Close</button>', $plain);
    }

    public function test_an_event_page_takes_one_schedules_look_not_parts_of_two(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['accent_color' => '#111111']);

        $withLook = $this->createRole($owner, 'venue', ['accent_color' => '#dc2626', 'background' => 'solid', 'background_color' => '#fee2e2']);
        $this->assertTrue($withLook->isClaimed() && $withLook->hasConfiguredBackground());
        $this->assertSame($withLook->id, GuestTheme::lookRole($curator, $withLook)->id);

        // A claimed schedule that never chose a background: the page is the curator's throughout.
        // The buttons used to take this schedule's colour while the background stayed the curator's.
        $noLook = $this->createRole($owner, 'venue', ['accent_color' => '#16a34a']);
        $noLook->forceFill(['background' => 'gradient', 'background_colors' => ''])->save();
        $this->assertFalse($noLook->fresh()->hasConfiguredBackground());
        $this->assertSame($curator->id, GuestTheme::lookRole($curator, $noLook->fresh())->id);

        $this->assertSame($curator->id, GuestTheme::lookRole($curator)->id);
    }
}
