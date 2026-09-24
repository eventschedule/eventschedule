<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule's style values reach CSS on every guest page: the gradient, the solid colour and the
 * background image in the layout's <style> block, the font in style="" attributes, the accent in
 * both and in Vue :style expressions. None of them is validated on every write path - the web form
 * checks a few, a backup restore writes them as they came - so a value could close its declaration
 * and restyle the page, and through $otherRole a talent or venue attached to SOMEONE ELSE's event
 * restyled that schedule's event page too.
 *
 * Role's accessors sanitize them where they are read (RoleStyleAttributesTest has the tables), and
 * css_url() encodes every url() fed by a stored value (CssUrlTest). These render the pages, and
 * check the readers that relied on a stored value always being a string.
 */
class GuestStyleSanitizingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const BREAK_OUT = 'body{display:none}';

    protected function setUp(): void
    {
        parent::setUp();

        // Saving an image column queues its derivatives, which read the disk.
        Storage::fake(config('filesystems.default'));
    }

    private function schedulePage(Role $role)
    {
        return $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))->assertOk();
    }

    /**
     * $host's event page for an event $guest also plays: on $host's page the layout reads $guest
     * as $otherRole, and paints $guest's background when it is claimed and has one.
     */
    private function eventPageWith(Role $host, Role $guest): string
    {
        $event = $this->createEvent($host, ['name' => 'Shared Night']);
        $event->roles()->attach($guest->id, ['is_accepted' => true]);

        return $this->get($this->guestEventUrl($host, Event::find($event->id)))->assertOk()->getContent();
    }

    public function test_a_gradient_that_closes_its_declaration_renders_sanitized_on_its_own_page(): void
    {
        foreach (['red; } '.self::BREAK_OUT.' x{', "#ffffff, #000000\n} ".self::BREAK_OUT.' x{'] as $colors) {
            $role = $this->createRole($this->createOwner(), 'venue', [
                'background' => 'gradient',
                'background_colors' => $colors,
                'background_rotation' => 45,
            ]);

            $this->schedulePage($role)->assertDontSee(self::BREAK_OUT, false);
        }

        // What survives is the real colour, in the declaration where it always went.
        $this->schedulePage($role)->assertSee('background-image: linear-gradient(45deg, #ffffff);', false);
    }

    public function test_another_schedules_gradient_is_sanitized_on_an_event_page(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Host Hall']);
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Visiting Act',
            'background' => 'gradient',
            'background_colors' => '#123456, red; } '.self::BREAK_OUT.' x{',
            'background_rotation' => 30,
        ]);

        $html = $this->eventPageWith($venue, $talent);

        $this->assertStringNotContainsString(self::BREAK_OUT, $html);
        // The act's background still paints the page, which is what $otherRole is for.
        $this->assertStringContainsString('background-image: linear-gradient(30deg, #123456);', $html);
    }

    public function test_another_schedules_colour_that_is_all_junk_leaves_the_host_background(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', [
            'background' => 'gradient',
            'background_colors' => '#abcdef, #fedcba',
            'background_rotation' => 90,
        ]);
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'background' => 'solid',
            'background_color' => 'red !important; } '.self::BREAK_OUT.' x{',
        ]);

        $html = $this->eventPageWith($venue, $talent);

        $this->assertStringNotContainsString(self::BREAK_OUT, $html);
        $this->assertStringContainsString('background-image: linear-gradient(90deg, #abcdef, #fedcba);', $html);
    }

    public function test_another_schedules_accent_reaches_no_style_and_no_vue_expression(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        // The accent also goes into Vue :style bindings, which the runtime compiler runs as
        // JavaScript: RoleUpdateRequest only accepts #rrggbb for that reason, and a restore does not
        // ask it.
        $talent = $this->createRole($this->createOwner(), 'talent', ['accent_color' => "#fff' + alert(document.domain) + '; } ".self::BREAK_OUT]);

        $html = $this->eventPageWith($venue, $talent);

        $this->assertStringNotContainsString('alert(document.domain)', $html);
        $this->assertStringNotContainsString(self::BREAK_OUT, $html);
        // The page falls back to the default accent, as it does for a schedule without one.
        $this->assertStringContainsString('background-color: #4E81FA', $html);
    }

    public function test_an_uploaded_background_url_cannot_leave_its_css_string(): void
    {
        // A backup restore keeps an image column it cannot resolve, so the stored value is not
        // always a name this app wrote.
        $role = $this->createRole($this->createOwner(), 'venue', [
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => "x.png\n} ".self::BREAK_OUT.' x{ y.png\');}',
        ]);
        $raw = $role->background_image_url;

        $html = $this->schedulePage($role)->getContent();

        // The <style> block's url("...") ended its string at the line break, and in the phone
        // banner's style="url('...')" the browser decodes &#039; back into a closing quote.
        $this->assertStringNotContainsString('url("'.$raw, $html);
        $this->assertStringNotContainsString("url('".e($raw), $html);
        $this->assertStringContainsString('url("'.css_url($raw).'")', $html);
        $this->assertStringContainsString("url('".css_url($raw)."')", $html);
    }

    public function test_another_schedules_uploaded_background_url_is_encoded_on_an_event_page(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => "act.png\n} ".self::BREAK_OUT.' x{',
        ]);
        $raw = $talent->background_image_url;

        $html = $this->eventPageWith($venue, $talent);

        $this->assertStringNotContainsString('url("'.$raw, $html);
        $this->assertStringContainsString('url("'.css_url($raw).'")', $html);
    }

    public function test_a_font_with_a_quote_or_a_semicolon_never_reaches_a_style_attribute(): void
    {
        $font = "Roboto'; background:url(//evil.test/x); x:'";
        $role = $this->createRole($this->createOwner(), 'venue', ['font_family' => $font]);

        $this->schedulePage($role)
            ->assertDontSee('evil.test', false)
            // Nor does it leave a stylesheet link asking Google Fonts for no family at all.
            ->assertDontSee('css2?family=:wght', false);

        $venue = $this->createRole($this->createOwner(), 'venue', ['font_family' => 'Roboto; es-marker: 1']);
        $talent = $this->createRole($this->createOwner(), 'talent', ['font_family' => $font]);

        $html = $this->eventPageWith($venue, $talent);

        $this->assertStringNotContainsString('evil.test', $html);
        $this->assertStringNotContainsString('es-marker', $html);
    }

    public function test_every_css_url_a_view_echoes_into_goes_through_css_url(): void
    {
        // Blade's escaping does not keep a value inside a CSS string (see css_url()), so a stored
        // URL echoed into url() without it is the bug this class is about, in a view added later.
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            foreach (preg_split('/\R/', $file->getContents()) as $index => $line) {
                if (preg_match('/url\(\s*["\']?\s*(?:\{\{|\{!!)(?!\s*css_url\()/', $line)) {
                    $offenders[] = $file->getRelativePathname().':'.($index + 1);
                }
            }
        }

        $this->assertSame([], $offenders, 'Print a stored URL inside CSS url() through css_url().');
    }

    public function test_the_settings_page_opens_for_a_gradient_left_with_one_colour(): void
    {
        // The custom-colour inputs split the gradient in two, and a sanitized one can be a single
        // colour: a missing second half must not be an "Undefined array key" 500.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'background' => 'gradient',
            'background_colors' => '#123456, red; } '.self::BREAK_OUT,
        ]);

        $this->actingAs($owner)->get('/'.$role->subdomain.'/edit')
            ->assertOk()
            ->assertDontSee(self::BREAK_OUT, false);
    }

    public function test_style_generation_takes_a_cleared_accent(): void
    {
        // No key, so the image request fails the way it does on an install without AI, before any
        // network call. The prompt builders take a string, and the accessor reads '' as null.
        config(['services.openai.api_key' => null, 'services.google.gemini_key' => null]);
        $role = $this->createRole($this->createOwner(), 'venue', ['accent_color' => '']);

        $this->assertSame(['image_error' => true], GeminiUtils::generateScheduleStyle($role, ['profile_image'], null, []));
    }

    public function test_valid_values_render_exactly_as_before(): void
    {
        $gradient = $this->createRole($this->createOwner(), 'venue', [
            'background' => 'gradient',
            'background_colors' => '#aabbcc, #112233',
            'background_rotation' => 45,
            'font_family' => 'Playfair_Display',
            'accent_color' => '#ff8800',
        ]);
        $this->schedulePage($gradient)
            ->assertSee('background-image: linear-gradient(45deg, #aabbcc, #112233);', false)
            ->assertSee("font-family: 'Playfair Display', sans-serif;", false)
            ->assertSee('background-color: #ff8800', false)
            ->assertSee('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap', false);

        // The one preset whose colours have no #, kept exactly as it is stored.
        $bare = $this->createRole($this->createOwner(), 'venue', [
            'background' => 'gradient',
            'background_colors' => '43ADD0, 998EE0, E17DC2, EF9393',
            'background_rotation' => 150,
        ]);
        $this->schedulePage($bare)->assertSee('background-image: linear-gradient(150deg, 43ADD0, 998EE0, E17DC2, EF9393);', false);

        $solid = $this->createRole($this->createOwner(), 'venue', ['background' => 'solid', 'background_color' => '#123456']);
        $this->schedulePage($solid)->assertSee('background-color: #123456 !important;', false);

        $preset = $this->createRole($this->createOwner(), 'venue', ['background' => 'image', 'background_image' => 'Abstract_Sunrise']);
        $this->schedulePage($preset)
            ->assertSee('url("'.asset('images/backgrounds/Abstract_Sunrise.webp').'")', false)
            ->assertSee('url("'.asset('images/backgrounds/Abstract_Sunrise.png').'") type("image/png")', false);
    }
}
