<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A guest page's background is drawn on a layer of its own, fixed to the window
 * (partials/guest-background), and no longer on `body`.
 *
 * On `body`, `cover` was sized to the whole PAGE where the background scrolled with it (the
 * calendar), which enlarged a 928px built-in picture 2.4x on a 3000px calendar and 4.9x on a
 * retina screen, and iPhones ignore `background-attachment: fixed`, so they did the same
 * everywhere. The schedule keeps the background it chose; only where it is drawn changed.
 */
class GuestBackgroundLayerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const LAYER = '<div class="gp-bg" aria-hidden="true">';

    protected function setUp(): void
    {
        parent::setUp();

        // The created hooks queue a variants job per upload, which would run on fixture files
        // that do not exist.
        Queue::fake();
    }

    private function page(string $path): string
    {
        return $this->get($path)->assertOk()->getContent();
    }

    private function schedule(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['name' => 'Blue Room']);
    }

    public function test_a_gradient_and_a_picture_are_drawn_on_the_layer_not_on_body(): void
    {
        $gradient = $this->schedule(['background' => 'gradient', 'background_colors' => '#112233, #445566', 'background_rotation' => 45]);
        $html = $this->page('/'.$gradient->subdomain);

        $this->assertStringContainsString(self::LAYER, $html);
        $this->assertStringContainsString('.gp-bg { background-image: linear-gradient(45deg, #112233, #445566); }', $html);
        // The page under the layer, where iOS shows it past the end of a scroll.
        $this->assertStringContainsString('body { background-color: #112233 !important; }', $html);
        // Fixed to the window, at its tallest, behind everything; never printed.
        $this->assertStringContainsString('.gp-bg { position: fixed; top: 0; left: 0; right: 0; height: 100vh; height: 100lvh; z-index: -1;', $html);
        $this->assertStringContainsString('@media print { .gp-bg { display: none; } }', $html);
        $this->assertStringContainsString('@media (forced-colors: active) { .gp-bg { display: none; } }', $html);
        // Nothing on body is sized to the page any more.
        $this->assertDoesNotMatchRegularExpression('/background-attachment: (fixed|scroll);/', $html);

        $picture = $this->schedule(['background' => 'image', 'background_image' => 'Calm']);
        $html = $this->page('/'.$picture->subdomain);

        $this->assertStringContainsString(self::LAYER, $html);
        $this->assertMatchesRegularExpression('#\.gp-bg \{\s*background-image: url\("[^"]+/images/backgrounds/Calm\.webp"\);#', $html);
        // On a phone the schedule page shows its picture behind the header only.
        $this->assertStringContainsString('@media (max-width: 767.98px) { .gp-bg { display: none; } }', $html);
    }

    public function test_a_solid_colour_is_a_colour_on_body_with_no_layer(): void
    {
        $solid = $this->schedule(['background' => 'solid', 'background_color' => '#223344']);
        $html = $this->page('/'.$solid->subdomain);

        $this->assertStringNotContainsString(self::LAYER, $html);
        $this->assertStringContainsString('background-color: #223344 !important;', $html);
    }

    public function test_the_subscribe_embed_draws_no_layer_under_its_transparent_page(): void
    {
        $role = $this->schedule(['background' => 'gradient', 'background_colors' => '#112233, #445566']);
        $html = $this->page(route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true', 'form' => 'subscribe'], false));

        $this->assertStringNotContainsString(self::LAYER, $html);
        $this->assertStringContainsString('html, html body { background: transparent !important;', $html);
    }

    public function test_an_embed_draws_the_layer_inside_its_frame(): void
    {
        $role = $this->schedule(['background' => 'gradient', 'background_colors' => '#112233, #445566']);

        $this->assertStringContainsString(self::LAYER, $this->page('/'.$role->subdomain.'?embed=true'));
    }

    /** A graphic is captured whole, so the layer runs the page's length instead of repeating per screen. */
    public function test_a_graphic_draws_the_layer_the_length_of_the_page(): void
    {
        $role = $this->schedule(['background' => 'image', 'background_image' => 'Calm']);
        $html = $this->page('/'.$role->subdomain.'?graphic=1');

        $this->assertStringContainsString('body { position: relative; }', $html);
        $this->assertStringContainsString('.gp-bg { position: absolute; bottom: 0; height: auto; }', $html);
        $this->assertStringContainsString('linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)), url(', $html);
    }

    /**
     * marketing/custom-css told Pro owners to override the background on body. Their override was
     * written against those declarations, so their page keeps them, and no layer covers it.
     */
    public function test_custom_css_that_styles_the_page_background_keeps_the_rule_on_body(): void
    {
        foreach ([
            'body { background-size: contain; }',
            "html body {\n  background: #000;\n}",
            '.x{color:red} :root { background-position: top; }',
            '@media (min-width: 1px) { body.gp { background-image: none; } }',
            'body { -webkit-background-size: contain; }',
            'body { background-position-x: 20%; }',
            // Minified, after an at-rule or a string, nested.
            '@media(max-width:600px){.x{color:red}}body{background:red}',
            '@charset "utf-8";body{background:red}',
            '.x::after{content:"}"}html{background:red}',
            'body{background:#000; .card{color:red}}',
            'body{ &.dark{background:#000} }',
            '.x::before{content:"/*"} body{background:red}',
            '.a{background:url(img/*.png)} body{background:red}',
        ] as $css) {
            $role = $this->schedule(['background' => 'image', 'background_image' => 'Calm', 'custom_css' => $css]);
            $this->assertTrue($role->customCssStylesPageBackground(), $css);

            $html = $this->page('/'.$role->subdomain);
            $this->assertStringNotContainsString(self::LAYER, $html, $css);
            $this->assertMatchesRegularExpression('/background-attachment: (fixed|scroll);/', $html, $css);
        }

        foreach ([
            '.gk-body { background: red; }', '/* body { background: red; } */', 'body { color: red; }', 'tbody { background: red; }',
            // A selector that only starts at body styles something inside the page.
            'body .gk-panel { background: red; }', '.body { background: red; }', 'body:not(.x) .card { background: red; }',
            'body:not(.a, .b) .card { background: red; }', 'body { .card { background: #000; } }',
            // A comment left open runs to the end, and is read quickly.
            str_repeat('/*a', 3000).'body { background: red; }',
        ] as $css) {
            $role = $this->schedule(['background' => 'image', 'background_image' => 'Calm', 'custom_css' => $css]);
            $this->assertFalse($role->customCssStylesPageBackground(), $css);
        }

        // A plan whose custom CSS is not printed draws the layer.
        $free = $this->createFreeRole(null, 'venue', ['background' => 'image', 'background_image' => 'Calm', 'custom_css' => 'body { background: red; }']);
        $this->assertFalse($free->fresh()->customCssStylesPageBackground());
    }

    /** An event page wears the venue's or the performer's background, and draws it the same way. */
    public function test_an_event_page_draws_the_other_schedules_background(): void
    {
        $owner = $this->createOwner();
        $act = $this->createRole($owner, 'talent', ['name' => 'The Act', 'background' => 'gradient', 'background_colors' => '#aa0000, #00aa00']);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'background' => 'solid', 'background_color' => null]);
        $event = $this->createEvent($venue, ['name' => 'Autumn Session', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $html = $this->page($event->fresh()->getGuestUrl($venue->subdomain));

        $this->assertStringContainsString(self::LAYER, $html);
        $this->assertStringContainsString('linear-gradient(150deg, #aa0000, #00aa00)', $html);
    }

    /**
     * A page whose own custom CSS styles its background keeps it on its event pages: another
     * schedule's Aurora would be drawn over it.
     */
    public function test_another_schedules_aurora_does_not_cover_an_owners_css_background(): void
    {
        $owner = $this->createOwner();
        $act = $this->createRole($owner, 'talent', ['name' => 'The Act', 'background' => 'aurora']);
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room', 'background' => 'gradient', 'background_colors' => '#112233, #445566',
            'custom_css' => 'body { background-image: none; background-color: #101010; }',
        ]);
        $event = $this->createEvent($venue, ['name' => 'Autumn Session', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $html = $this->page($event->fresh()->getGuestUrl($venue->subdomain));

        $this->assertStringNotContainsString('class="gp-aurora"', $html);
        $this->assertStringNotContainsString(self::LAYER, $html);
        $this->assertStringContainsString('linear-gradient(150deg, #112233, #445566)', $html, 'the page wears its own background, on body');
    }

    /**
     * A retina desktop gets the 3840 derivative where the original is wider than 1920, and never
     * the original. A narrower original records the 1920 file under the 3840 key, which is no
     * wider picture, so no 2x is printed.
     */
    public function test_a_wide_upload_offers_a_retina_desktop_its_widest_derivative(): void
    {
        $role = $this->schedule(['background' => 'image', 'background_image' => null, 'background_image_url' => 'background_abc.png']);
        $role->recordImageVariants([
            'w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp', 'w3840' => 'background_abc_w3840.webp',
            'src' => ['w' => 3000, 'h' => 2000],
        ], 'background');

        $html = $this->page('/'.$role->subdomain);
        $desktop = url('/storage/background_abc_w1920.webp');
        $wide = url('/storage/background_abc_w3840.webp');

        $this->assertMatchesRegularExpression('#@media \(min-width: 1280px\) \{\s*\.gp-bg \{\s*background-image: -webkit-image-set#', $html, 'the wider picture from a laptop up');
        $this->assertStringContainsString('image-set(url("'.$desktop.'") 1x, url("'.$wide.'") 2x)', $html);
        $this->assertStringContainsString('@media (min-width: 1921px) {', $html);
        // As a background: the page's share image may still name the original.
        $this->assertStringNotContainsString('url("'.url('/storage/background_abc.png').'")', $html);
        $this->assertStringNotContainsString("url('".url('/storage/background_abc.png')."')", $html);

        $narrow = $this->schedule(['background' => 'image', 'background_image' => null, 'background_image_url' => 'background_def.png']);
        $narrow->recordImageVariants([
            'w960' => 'background_def_w960.webp', 'w1920' => 'background_def_w1920.webp', 'w3840' => 'background_def_w1920.webp',
            'src' => ['w' => 1600, 'h' => 1000],
        ], 'background');

        $html = $this->page('/'.$narrow->subdomain);
        $this->assertStringContainsString('url("'.url('/storage/background_def_w1920.webp').'")', $html);
        $this->assertStringNotContainsString('image-set(url("'.url('/storage/background_def_w1920.webp'), $html);
        $this->assertStringNotContainsString('min-width: 1921px', $html);
    }

    /** Built before the 3840 width existed: the 1920 rule as before, and nothing missing. */
    public function test_an_upload_without_the_widest_derivative_keeps_its_desktop_one(): void
    {
        $role = $this->schedule(['background' => 'image', 'background_image' => null, 'background_image_url' => 'background_abc.png']);
        $role->recordImageVariants(['w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp', 'src' => ['w' => 3000, 'h' => 2000]], 'background');

        $html = $this->page('/'.$role->subdomain);

        $this->assertStringContainsString('url("'.url('/storage/background_abc_w1920.webp').'")', $html);
        $this->assertStringNotContainsString('image-set(url(', $html);
    }
}
