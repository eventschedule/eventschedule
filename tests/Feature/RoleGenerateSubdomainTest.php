<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Role::generateSubdomain() tries the name's left-to-right prefixes first ("live", "live-music",
 * "live-music-shop") and keeps the first free one. cleanSubdomain() only ever vetted the WHOLE name
 * against RESERVED_SUBDOMAINS, so the prefixes were never checked: "App Night" was handed "app",
 * "Events at the Park" was handed "events", "WWW Fans" was handed "www" - names an app route, a
 * robots.txt rule or the hosted app/www hosts already own.
 */
class RoleGenerateSubdomainTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // cleanSubdomain() asks Gemini to translate a name Str::slug mangles. None of the names
        // here are mangled, but a pinned null key keeps a regression from reaching the network.
        config([
            'services.google.gemini_key' => null,
            'services.openai.api_key' => null,
        ]);
    }

    public function test_a_reserved_prefix_is_skipped_for_the_next_free_variation(): void
    {
        $this->assertSame('app-night', Role::generateSubdomain('App Night'));
        $this->assertSame('events-at', Role::generateSubdomain('Events at the Park'));
        $this->assertSame('www-fans', Role::generateSubdomain('WWW Fans'));
    }

    public function test_an_ordinary_prefix_is_still_preferred(): void
    {
        // The shortening itself is deliberate and unchanged: an empty install hands out "blue".
        $this->assertSame('blue', Role::generateSubdomain('Blue Note'));
    }

    public function test_no_reserved_name_is_ever_handed_out(): void
    {
        foreach (Role::RESERVED_SUBDOMAINS as $reserved) {
            $subdomain = Role::generateSubdomain($reserved.' Community Club');

            $this->assertNotContains($subdomain, Role::RESERVED_SUBDOMAINS,
                "'{$reserved} Community Club' was handed the reserved name '{$subdomain}'");
            // 'demo' is on the list, and "demo Community Club" used to skip it for demo-community.
            $this->assertStringStartsNotWith('demo-', $subdomain,
                "'{$reserved} Community Club' was handed '{$subdomain}', in the demo's namespace");
        }
    }

    /**
     * demo- is the demo's: its hourly reset used to delete every demo- schedule, and the admin
     * lists and plan jobs still read one as demo. So the prefix is rewritten, not handed out, and
     * not refused either - refusing in cleanSubdomain() means a random eight characters.
     */
    public function test_the_demo_prefix_is_rewritten_rather_than_handed_out(): void
    {
        $this->assertSame('demoday', Role::generateSubdomain('Demo Day'));
        $this->assertSame('demonight', Role::generateSubdomain('Demo Night Live'));

        $this->createRole($this->createOwner(), 'venue', ['subdomain' => 'demoday']);
        $this->assertSame('demoday1', Role::generateSubdomain('Demo Day'));

        // Only the prefix: a name that merely contains the word is untouched.
        $this->assertSame('live-demo-night', Role::cleanSubdomain('Live Demo Night'));
    }

    public function test_when_every_free_variation_is_taken_the_suffix_fallback_still_applies(): void
    {
        $owner = $this->createOwner();
        foreach (['app-night', 'app-night-live'] as $taken) {
            $this->createRole($owner, 'venue', ['subdomain' => $taken]);
        }

        // "app" is reserved and the other two variations are taken, so the whole name gets a
        // numeric suffix - never the bare reserved prefix.
        $this->assertSame('app-night-live1', Role::generateSubdomain('App Night Live'));
    }
}
