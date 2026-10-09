<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The house style: the homepage's design language (2026-10) on the other pages named in the
 * header, switched on per page with :hp="true" on the marketing layout.
 *
 * The switch does three things in the layout (the typeface, a wrapper with the id "hp", and one
 * copy of marketing/partials/hp-kit), and the kit re-dresses the shared components from inside
 * that wrapper. Everything it does is scoped to the wrapper, so the failure this guards against
 * is quiet in both directions: a page that lost the switch renders in the old grey with no
 * error, and a kit that leaked would change the hundred pages that never asked for it.
 */
class MarketingHouseStyleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The pages rebuilt around the homepage's own pieces (hero, line-up, bill, night band).
     * The homepage carries its own copy of the styles inline and is not one of them.
     */
    private const PAGES = ['/features', '/pricing', '/use-cases', '/selfhost', '/docs', '/examples'];

    /**
     * The marketing views that deliberately do NOT take the house style: the homepage (its own
     * inline copy), /browse, and the audience pages, each of which is a site of its own with its
     * own typefaces.
     */
    private const OWN_STYLE = ['index.blade.php', 'browse.blade.php'];

    public function test_each_page_in_the_house_style_gets_the_wrapper_the_kit_and_the_typeface_once(): void
    {
        foreach (self::PAGES as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<div id="hp">'), "{$path} is not wrapped once in the house style");
            $this->assertSame(1, substr_count($html, '--hp-bg: #f4f6fb;'), "{$path} does not carry the kit exactly once");
            $this->assertSame(1, preg_match_all('#<link rel="preload" as="font" type="font/woff2" href="[^"]*Red_Hat_Display[^"]*" crossorigin>#', $html),
                "{$path} does not preload the one font file its first screen is set in");
            $this->assertSame(1, preg_match_all('#<h1\b#', $html), "{$path} must have one h1");
            // The kit names the class in its own stylesheet (to hide a nav a page forgot), so this
            // looks for the element, not the word.
            $this->assertStringNotContainsString('<nav class="es-dotnav', $html, "{$path} still draws the dots the house style dropped");
        }
    }

    public function test_a_page_that_did_not_ask_is_left_alone(): void
    {
        $html = $this->get('/for-musicians')->assertOk()->getContent();

        $this->assertStringNotContainsString('id="hp"', $html);
        $this->assertStringNotContainsString('--hp-bg', $html);
    }

    /**
     * Every other marketing page asks for it, so a new page that forgets the attribute renders in
     * the old grey beside a hundred that do not. The guide pages ask through the docs-page
     * component, and the blog and the 404 page ask too.
     */
    public function test_every_marketing_page_but_the_ones_with_a_style_of_their_own_asks_for_it(): void
    {
        $missing = [];

        foreach (File::files(resource_path('views/marketing')) as $file) {
            $name = $file->getFilename();
            if (in_array($name, self::OWN_STYLE, true) || str_starts_with($name, 'for-')) {
                continue;
            }
            if (! str_contains(File::get($file->getPathname()), ':hp="true"')) {
                $missing[] = $name;
            }
        }

        $this->assertSame([], $missing, 'these marketing pages do not take the house style (add :hp="true" to their layout tag)');

        foreach (['components/docs-page.blade.php', 'blog/index.blade.php', 'blog/show.blade.php', 'errors/404.blade.php', 'marketing/docs/index.blade.php'] as $view) {
            $this->assertStringContainsString(':hp="true"', File::get(resource_path('views/'.$view)), "{$view} lost the house style");
        }
    }

    /** A bespoke page keeps its own hero and takes the wrapper, the kit and the typeface around it. */
    public function test_a_bespoke_page_is_wrapped_without_being_rebuilt(): void
    {
        foreach (['/about', '/features/polls', '/docs/tickets'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, '<div id="hp">'), "{$path} is not wrapped once");
            $this->assertSame(1, substr_count($html, '--hp-bg: #f4f6fb;'), "{$path} does not carry the kit exactly once");
            $this->assertStringNotContainsString('class="es-hero hp-hero', $html, "{$path} is a page with a hero of its own");
        }
    }

    /**
     * MarketingRelatedPagesTest finds the pages that use a component by looking for its tag in
     * the source of every view a page renders. The kit is rendered by every page in the house
     * style, so a component tag written in one of its notes made the docs home "use" the related
     * strip. Its notes name components without their angle brackets for that reason.
     */
    public function test_the_kit_names_no_component_by_its_tag(): void
    {
        foreach (['marketing/partials/hp-kit.blade.php', 'marketing/partials/hp-head.blade.php'] as $view) {
            $this->assertDoesNotMatchRegularExpression('/<x-[a-z]/', File::get(resource_path('views/'.$view)),
                "{$view} writes a component tag, which the source-scanning tests read as a use of it");
        }
    }

    /** initClaim() takes the first link beside the box as the box's own button. */
    public function test_the_shared_finale_keeps_the_claim_box_and_its_button_together(): void
    {
        foreach (['/features', '/pricing', '/use-cases'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match('#<div class="hp-claimrow">(.*?)</div>\s*(?:<p class="hp-finale-foot"|</div>|<p)#s', $html, $row), "{$path} has no claim row");
            $this->assertStringContainsString('id="es-claim-input"', $row[1]);
            $this->assertSame(1, preg_match('#<a href="([^"]+)"#', $row[1], $link), "{$path}: the claim box has no button beside it");
            $this->assertStringContainsString('/sign_up', $link[1]);
        }
    }

    public function test_the_features_page_keeps_its_night_chapter_and_its_board(): void
    {
        $html = $this->get('/features')->assertOk()->getContent();

        $start = strpos($html, '<div class="hp-dark is-run">');
        $this->assertNotFalse($start, 'the night chapter lost its run');
        $this->assertNotFalse(strpos($html, 'id="promote"', $start), 'chapter 03 is no longer inside the night run');
        $this->assertLessThan(strpos($html, 'id="engage"'), strpos($html, 'id="promote"', $start), 'the night run does not end before chapter 04');

        // The board under the hero is the page's index: forty keys, and each bank's heading
        // links the chapter it stands for (FeaturesPageTest holds the board itself).
        $this->assertSame(40, preg_match_all('/<a href="[^"]+"\s+class="fb-key[^"]*"\s+data-fb-key="/', $html), 'the board no longer holds forty keys');
        foreach (['sell', 'schedule', 'promote', 'engage', 'own-it'] as $chapter) {
            $this->assertStringContainsString('href="#'.$chapter.'"', $html, "no bank links #{$chapter}");
            $this->assertStringContainsString('id="'.$chapter.'"', $html);
        }
    }
}
