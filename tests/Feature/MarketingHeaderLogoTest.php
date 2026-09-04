<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The marketing logo must never be the flex item that gives.
 *
 * The logo <img> is `w-auto` inside a <picture> that is a flex child of the header bar, and
 * Tailwind preflight adds `img{max-width:100%}`. A flex item defaults to `flex-shrink:1` with
 * `min-width:auto`, so when the bar runs out of room the image - the only thing in it without an
 * intrinsic floor - is what collapses. Measured on production before this was fixed: the header
 * logo rendered 0px wide at a 768px viewport and 124.7px at 900px, against a natural 162.7px.
 * Nothing errors, nothing shifts, no console warning; the brand mark simply is not there. It took
 * a Lighthouse accessibility audit to notice it sideways, as a 10x40 tap target.
 *
 * `shrink-0` alone is not the whole fix, which is why the other tests exist:
 *
 * - The header content needs ~890px and a 768px viewport offers 720px, so pinning the logo turns
 *   the squeeze into an overflow unless the desktop nav stays hidden until there is room. That
 *   makes the three breakpoints load-bearing as a SET: show the nav at one and hide the hamburger
 *   at another and you get a window with two navs, or with none.
 * - At 320px even the mobile bar cannot hold a 162.7px logo (309px of content in a 288px box), so
 *   the logo steps down a size below `sm`. Without that, `shrink-0` eats the nav's side padding.
 * - The bar shows Sign In / Get Started from `sm`, and the panel repeats them. Whatever breakpoint
 *   the panel appears at, it must not duplicate what the bar is already showing.
 *
 * Locators here key off page CONTENT (the Features link, the menu ids, the @auth block) rather
 * than exact utility-class strings, so reordering classes does not turn a real assertion into a
 * confusing "could not find the element".
 */
class MarketingHeaderLogoTest extends TestCase
{
    private function header(): string
    {
        return File::get(resource_path('views/marketing/partials/header.blade.php'));
    }

    public function test_the_logo_cannot_be_squeezed_in_the_header_or_the_footer(): void
    {
        foreach (['header', 'footer'] as $partial) {
            $path = "views/marketing/partials/{$partial}.blade.php";
            $source = File::get(resource_path($path));

            // The logo anchor is the one whose <picture> points at a *_logo asset.
            preg_match('/<a\s[^>]*class="([^"]*)"[^>]*>\s*<picture[^>]*>\s*<source[^>]*logo/s', $source, $m);

            $this->assertNotEmpty($m, "Could not find the logo anchor in {$path}.");
            $this->assertStringContainsString('shrink-0', $m[1],
                "The {$partial} logo anchor must carry shrink-0, or the browser resolves a tight "
                ."bar by collapsing the logo image to nothing rather than overflowing. Found: \"{$m[1]}\".");
        }
    }

    public function test_the_header_logo_steps_down_below_the_sm_breakpoint(): void
    {
        preg_match_all('/<img class="([^"]*)"[^>]*logo/', $this->header(), $m);

        $this->assertNotEmpty($m[1], 'Could not find the header logo <img> tags.');

        foreach ($m[1] as $classes) {
            $this->assertMatchesRegularExpression('/\bh-7\b.*\bsm:h-8\b/', $classes,
                'The header logo must render smaller below sm. Pinned at h-8 with shrink-0 it needs '
                .'309px inside a 288px content box at a 320px viewport, and the overflow silently '
                ."eats the nav's px-4. Found: \"{$classes}\".");
        }
    }

    public function test_the_header_switches_to_the_mobile_menu_at_a_single_breakpoint(): void
    {
        $source = $this->header();

        // Locate by content, then read the breakpoint out of whatever classes are on it.
        preg_match('/<div class="([^"]*)">\s*<a href="\{\{ marketing_url\(\'\/features\'\)/', $source, $navDiv);
        preg_match('/id="mobile-menu-button".*?class="([^"]*)"/s', $source, $buttonDiv);
        preg_match('/id="mobile-menu"\s+class="([^"]*)"/', $source, $panelDiv);

        $this->assertNotEmpty($navDiv, 'Could not find the desktop nav container (the div holding the Features link).');
        $this->assertNotEmpty($buttonDiv, 'Could not find the mobile menu button.');
        $this->assertNotEmpty($panelDiv, 'Could not find the mobile menu panel.');

        preg_match('/(\w+):flex/', $navDiv[1], $nav);
        preg_match('/(\w+):hidden/', $buttonDiv[1], $button);
        preg_match('/(\w+):hidden/', $panelDiv[1], $panel);

        $this->assertNotEmpty($nav, "The desktop nav has no responsive :flex breakpoint: \"{$navDiv[1]}\".");
        $this->assertNotEmpty($button, "The menu button has no responsive :hidden breakpoint: \"{$buttonDiv[1]}\".");
        $this->assertNotEmpty($panel, "The menu panel has no responsive :hidden breakpoint: \"{$panelDiv[1]}\".");

        $this->assertSame([$nav[1], $nav[1]], [$button[1], $panel[1]],
            "The desktop nav appears at {$nav[1]}:, the hamburger hides at {$button[1]}:, and the "
            ."mobile panel hides at {$panel[1]}:. All three have to name the same breakpoint or "
            .'there is a viewport range showing both navs, or neither.');

        $this->assertNotContains($nav[1], ['sm', 'md'],
            'The desktop bar needs about 890px of row width; at the md breakpoint (768px) only '
            .'720px exist, which is what crushed the logo to 0px. Keep the switch at lg or higher '
            .'(marketing-home.js and docs.js already call min-width:1024px "desktop"), or use an '
            .'arbitrary variant like min-[940px]: if you want the band back.');
    }

    public function test_the_mobile_panel_does_not_duplicate_the_auth_buttons(): void
    {
        $source = $this->header();

        // "mobile-menu-button" does not contain the closing quote, so this finds the panel only.
        $panel = substr($source, (int) strpos($source, 'id="mobile-menu"'));

        preg_match('/<div class="([^"]*)">\s*@auth/', $panel, $authWrapper);

        $this->assertNotEmpty($authWrapper, 'Could not find the auth block inside the mobile menu panel.');
        $this->assertStringContainsString('sm:hidden', $authWrapper[1],
            'The bar shows Sign In / Get Started from sm: (640px) via its own `hidden sm:flex` '
            .'cluster, and the panel repeats them. Since the panel now stays up to lg, the panel '
            .'copy must be hidden from sm: up or both render at once across the whole 640-1023px '
            ."band. Found: \"{$authWrapper[1]}\".");
    }
}
