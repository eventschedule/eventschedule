<?php

namespace Tests\Feature;

use App\Models\GuestPageSetting;
use App\Utils\ColorUtils;
use App\Utils\GuestTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Style > Background > Page style: the owner's choice of light or dark for the guest pages, or
 * Automatic (by the background's brightness). Kept on guest_page_settings, saved only by a save
 * that carries it, and read by the guest layout's theme script.
 */
class GuestPageStyleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_owners_choice_overrides_automatic_and_reaches_the_page(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['background' => 'image', 'background_image' => 'Calm']);

        $this->assertSame('dark', GuestTheme::pageMode($role->fresh()), 'a photo is dark by default');

        // A light palette makes the page light, with the palette's own ground.
        GuestPageSetting::store($role, ['page_mode' => 'sand']);
        $this->assertSame('light', GuestTheme::pageMode($role->fresh()));
        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $this->assertStringContainsString('var FORCE_MODE = "light";', $html);
        $this->assertStringContainsString('--gk-stage: #f6f4ef;', $html);

        GuestPageSetting::store($role, ['page_mode' => 'carbon']);
        $this->assertSame('dark', GuestTheme::pageMode($role->fresh()));
        $this->assertStringContainsString('--gk-stage: #000000;', $this->get('/'.$role->subdomain)->assertOk()->getContent());

        GuestPageSetting::store($role, ['page_mode' => 'auto']);
        $this->assertSame('dark', GuestTheme::pageMode($role->fresh()));
    }

    public function test_an_embed_keeps_its_own_mode(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        GuestPageSetting::store($role, ['page_mode' => 'paper']);

        $this->assertStringContainsString('var FORCE_MODE = null;', $this->get('/'.$role->subdomain.'?embed=true')->assertOk()->getContent());
    }

    public function test_the_schedule_form_saves_it_only_when_it_is_sent(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $form = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('name="page_mode" value="auto" checked', $form);

        GuestPageSetting::store($role, ['page_mode' => 'midnight']);
        // Another save that does not carry the field leaves it alone (no writes from elsewhere).
        GuestPageSetting::store($role, ['show_next_event' => false]);
        $this->assertSame('midnight', $role->fresh()->guestPageSetting->page_mode);
    }

    /** Every palette's inks, and the accent as text, read on its three grounds whatever the accent. */
    public function test_every_palette_reads_whatever_the_accent(): void
    {
        foreach (['#4e81fa', '#eab308', '#ffffff', '#000000', '#dc2626', '#a3e635'] as $accent) {
            $theme = GuestTheme::fromAccent($accent);
            foreach (array_keys(GuestTheme::PALETTES) as $palette) {
                preg_match_all('/(--[a-z0-9-]+): (#[0-9a-f]{6});/', $theme->paletteCss($palette), $m, PREG_SET_ORDER);
                $t = [];
                foreach ($m as [, $k, $v]) {
                    $t[$k] = $v;
                }
                foreach (['--gk-stage', '--gk-stage-raised', '--gk-stage-well'] as $ground) {
                    foreach (['--gk-stage-ink', '--gk-stage-ink-2', '--gk-stage-ink-3', '--es-accent-readable'] as $ink) {
                        $this->assertGreaterThanOrEqual(4.5, ColorUtils::getContrastRatio($t[$ink], $t[$ground]), "$accent $palette $ink on $ground");
                    }
                }
            }
        }
    }

    /**
     * The first guest page a visitor opens decides light or dark for the visit (es_gp_mode), so a
     * schedule's own palette applies only in its own brightness, and the cookie is set on the
     * platform's domain when hosted so it carries across schedules' subdomains.
     */
    public function test_the_first_pages_brightness_carries_across_schedules(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        GuestPageSetting::store($role, ['page_mode' => 'carbon']);
        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('es_gp_mode=', $html);
        $this->assertStringContainsString('forced = carried;', $html);
        $this->assertStringContainsString(':where(html.dark) body { --gk-stage: #000000;', $html, 'the palette only in its own brightness');
        $this->assertStringNotContainsString(':where(.dark) body { --gk-stage: #000000;', $html);

        $embed = $this->get('/'.$role->subdomain.'?embed=true')->assertOk()->getContent();
        $this->assertStringContainsString('var FORCE_MODE = null;', $embed, 'an embed neither sets nor reads it');
    }

    /**
     * A cookie cannot follow a visitor to a schedule's own domain, so a link there carries the
     * mode in its fragment: the page names the hosts it may decorate (a custom domain it links
     * to, never any other site), and reads and removes the fragment on arrival.
     */
    public function test_a_link_to_a_schedules_own_domain_carries_the_mode(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $this->assertStringContainsString('#es_mode=', $html);
        $this->assertStringContainsString('history.replaceState', $html);

        // A domain the schedule is served on reads the fragment.
        $role->forceFill(['custom_domain' => 'https://tickets.example.org', 'custom_domain_mode' => 'direct', 'custom_domain_status' => 'active'])->saveQuietly();
        $this->assertSame(['tickets.example.org'], GuestTheme::carryHosts($role->fresh()));

        // A redirect-mode domain sends the visitor on to the subdomain, where the cookie already
        // holds the mode, and the page must not name it (its canonical is the subdomain).
        $role->forceFill(['custom_domain_mode' => 'redirect', 'custom_domain_status' => null])->saveQuietly();
        $this->assertSame([], GuestTheme::carryHosts($role->fresh()), 'a redirect-mode domain draws no page');

        $role->forceFill(['custom_domain' => null])->saveQuietly();
        $this->assertSame([], GuestTheme::carryHosts($role->fresh()), 'nothing to decorate without a domain of its own');
    }
}
