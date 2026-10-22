<?php

namespace Tests\Feature;

use App\Jobs\GenerateRoleImageVariants;
use App\Models\AuroraSetting;
use App\Models\BackupJob;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use App\Utils\Aurora;
use App\Utils\ImageUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Aurora, the background drawn from a schedule's colours (App\Utils\Aurora): on the guest pages,
 * on the schedule form, from the header picture, on a ticket and through a backup.
 */
class AuroraBackgroundTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function page(string $path): string
    {
        return $this->get($path)->assertOk()->getContent();
    }

    private function aurora(array $attrs = [], ?User $owner = null): Role
    {
        return $this->createRole($owner ?? $this->createOwner(), 'venue', $attrs + [
            'name' => 'Blue Room',
            'background' => 'aurora',
            'accent_color' => '#14b8a6',
        ]);
    }

    private function save(User $user, Role $role, array $fields = []): TestResponse
    {
        return $this->actingAs($user)
            ->from(route('role.edit', ['subdomain' => $role->subdomain]))
            ->put(route('role.update', ['subdomain' => $role->subdomain]), array_merge([
                'name' => $role->name,
                'email' => $role->email,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
            ], $fields));
    }

    public function test_the_guest_page_draws_the_lights_in_both_modes(): void
    {
        $role = $this->aurora();
        Aurora::saveSettings($role, 'lagoon', null, 3, true);

        $html = $this->page('/'.$role->subdomain);

        $this->assertStringContainsString('<div class="gp-bg" aria-hidden="true">', $html);
        $this->assertStringContainsString('<div class="gp-aurora"  data-aurora-comp="3"  data-aurora-mirror="0"  data-aurora-motion="on" >', $html);
        $this->assertSame(count(Aurora::COMPOSITIONS[3]), substr_count($html, '<i style="--x:'));
        $this->assertStringContainsString('.gp-bg{--aurora-ground:', $html);
        $this->assertStringContainsString(':where(.dark) .gp-bg{--aurora-ground:', $html);
        $this->assertStringContainsString('@keyframes aurora-k0{', $html);
        // The shared stylesheet, and the page under the layer in each mode.
        $this->assertStringContainsString('.gp-aurora-m > i {', $html);
        $aurora = Aurora::for($role->fresh());
        $this->assertStringContainsString('body { background-color: '.$aurora->ground('light').' !important; }', $html);
        $this->assertStringContainsString(':where(html.dark) body { background-color: '.$aurora->ground('dark').' !important; }', $html);
        $this->assertMatchesRegularExpression('/@media screen \{\s*:where\(html\.dark\) body \{ color: #ffffff !important; \}/', $html, 'white ink on screen only');
        // While the lights move the panels over them are not blurred again every frame.
        $this->assertStringContainsString('.gp-bg ~ * :is(.gk-panel,', $html);
        $this->assertStringNotContainsString('.gp-bg ~ * :is(.gk-panel,', $this->page('/'.$role->subdomain.'?embed=true'), 'a still layer keeps the blur');
        // Nothing to fetch: no picture, so no preload.
        $this->assertStringNotContainsString('rel="preload" as="image"', $html);
    }

    /** Still for every reason there is, and the same frame for all of them. */
    public function test_the_lights_are_still_when_anyone_asks(): void
    {
        $role = $this->aurora();
        Aurora::saveSettings($role, 'ember', null, 5, false);

        $this->assertStringContainsString('data-aurora-motion="off"', $this->page('/'.$role->subdomain));

        Aurora::saveSettings($role, 'ember', null, 5, true);
        $this->assertStringContainsString('data-aurora-motion="on"', $this->page('/'.$role->subdomain));
        $this->assertStringContainsString('data-aurora-motion="off"', $this->page('/'.$role->subdomain.'?embed=true'), 'an embed sits in somebody else\'s site');
        $this->assertStringContainsString('data-aurora-motion="off"', $this->page('/'.$role->subdomain.'?graphic=1'), 'a graphic is a picture');

        $html = $this->page('/'.$role->subdomain);
        $this->assertStringContainsString('html.es-a11y-reduce-motion .gp-aurora-m > i,', $html);
        $this->assertStringContainsString('html.es-a11y-high-contrast .gp-aurora-m > i {', $html);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce) {', $html);
    }

    /** A schedule with no row is arranged by its id, the same way on every visit. */
    public function test_with_no_settings_the_arrangement_comes_from_the_schedules_id(): void
    {
        $role = $this->aurora();
        $this->assertFalse(AuroraSetting::where('role_id', $role->id)->exists());

        $expected = (crc32((string) $role->id) & 0xFFFF) % count(Aurora::COMPOSITIONS);

        $this->assertStringContainsString('data-aurora-comp="'.$expected.'"', $this->page('/'.$role->subdomain));
        $this->assertSame('accent', Aurora::for($role)->palette);
    }

    public function test_an_event_page_wears_the_other_schedules_aurora(): void
    {
        $owner = $this->createOwner();
        $act = $this->createRole($owner, 'talent', ['name' => 'The Act', 'background' => 'aurora', 'accent_color' => '#ef4444']);
        Aurora::saveSettings($act, 'coral', null, 6, true);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'background' => 'solid', 'background_color' => null]);
        $event = $this->createEvent($venue, ['name' => 'Autumn Session', 'creator_role_id' => $venue->id]);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $html = $this->page($event->fresh()->getGuestUrl($venue->subdomain));

        $this->assertStringContainsString('data-aurora-comp="6"', $html);
        $this->assertStringContainsString(Aurora::for($act->fresh())->css('.gp-bg'), $html);
    }

    public function test_the_form_saves_aurora_and_a_save_without_it_leaves_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora([], $owner);

        $this->save($owner, $role, [
            'background' => 'aurora',
            'aurora_submitted' => 1,
            'aurora_palette' => 'dusk',
            'aurora_seed' => 4321,
            'aurora_motion' => 0,
            // A colour field always posts a value, and it is not Custom's.
            'aurora_color1' => '#000000', 'aurora_color2' => '#111111', 'aurora_color3' => '#222222',
        ])->assertSessionHasNoErrors();

        $setting = AuroraSetting::where('role_id', $role->id)->firstOrFail();
        $this->assertSame(['dusk', null, 4321, false], [$setting->palette, $setting->colors, $setting->seed, $setting->motion]);

        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_palette' => 'custom', 'aurora_seed' => 4321, 'aurora_motion' => 1,
            'aurora_color1' => '#AA0000', 'aurora_color2' => '#00aa00', 'aurora_color3' => '#0000aa'])->assertSessionHasNoErrors();
        $this->assertSame('#aa0000, #00aa00, #0000aa', $setting->fresh()->colors);

        // A save from a form without the row (another tab's, the API's) leaves it as it was.
        $this->save($owner, $role, ['description' => 'New words'])->assertSessionHasNoErrors();
        $this->assertSame('custom', $setting->fresh()->palette);
        $this->assertTrue($setting->fresh()->motion);
    }

    public function test_the_form_refuses_what_aurora_cannot_hold(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora([], $owner);

        $this->save($owner, $role, ['background' => 'video'])->assertSessionHasErrors('background');
        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_palette' => 'neon'])->assertSessionHasErrors('aurora_palette');
        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_seed' => 70000])->assertSessionHasErrors('aurora_seed');
        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_palette' => 'custom', 'aurora_color1' => 'red'])->assertSessionHasErrors('aurora_color1');

        $this->assertSame('aurora', $role->fresh()->background);
        $this->assertFalse(AuroraSetting::where('role_id', $role->id)->exists());
    }

    public function test_the_style_tab_offers_aurora_and_keeps_a_refused_choice(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora([], $owner);
        Aurora::saveSettings($role, 'meadow', null, 77, true);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        foreach (Role::BACKGROUNDS as $type) {
            $this->assertStringContainsString('id="background_type_'.$type.'"', $html);
        }
        $this->assertStringContainsString('<input type="hidden" name="aurora_submitted" value="1">', $html);
        $this->assertStringContainsString('<option value="meadow" selected>', $html);
        $this->assertStringContainsString('id="aurora_seed" name="aurora_seed" value="77"', $html);
        $this->assertStringContainsString('class="vue-style-aurora"', $html);
        $this->assertStringContainsString('data-aurora="', $html);
        // The switch is the page's own markup, never inside the island.
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="aurora_motion" name="aurora_motion" value="1"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/<div id="aurora_custom_colors" class="mt-4"\s+hidden\s*>/', $html, 'the three colours wait for Custom');

        // A refused save comes back on what was chosen.
        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_palette' => 'custom', 'aurora_seed' => 99, 'aurora_color1' => 'nope']);
        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->getContent();
        $this->assertStringContainsString('<option value="custom" selected>', $html);
        $this->assertStringContainsString('id="aurora_seed" name="aurora_seed" value="99"', $html);
    }

    public function test_custom_css_on_the_page_background_is_mentioned_beside_aurora(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora(['custom_css' => 'body { background: #000; }'], $owner);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->getContent();
        $this->assertStringContainsString(e(__('messages.aurora_custom_css_note')), $html);

        // And the layer is drawn all the same.
        $this->assertStringContainsString('<div class="gp-aurora"', $this->page('/'.$role->subdomain));
    }

    /** Match header is sampled on the server, follows the picture, and stands on the accent without one. */
    public function test_match_header_samples_the_header_picture(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora(['header_style' => 'banner', 'header_image' => 'Arena'], $owner);

        $this->save($owner, $role, ['aurora_submitted' => 1, 'aurora_palette' => 'header', 'aurora_seed' => 1, 'aurora_motion' => 1, 'header_image' => 'Arena'])->assertSessionHasNoErrors();
        $built = AuroraSetting::where('role_id', $role->id)->firstOrFail();
        $this->assertSame('header', $built->palette);
        $this->assertCount(3, Aurora::colorList($built->colors), 'a built-in header is sampled');

        // An uploaded header, re-sampled when its variants job has run.
        Storage::fake();
        $image = imagecreatetruecolor(200, 100);
        imagefill($image, 0, 0, imagecolorallocate($image, 10, 160, 40));
        ob_start();
        imagepng($image);
        Storage::put(ImageUtils::storagePathFor('header_upload.png'), ob_get_clean());
        imagedestroy($image);

        Role::where('id', $role->id)->update(['header_image' => '', 'header_image_url' => 'header_upload.png']);
        (new GenerateRoleImageVariants($role->id, 'header_upload.png', 'header'))->handle();
        $green = Aurora::colorList($built->fresh()->colors)[0];
        [$r, $g, $b] = sscanf($green, '#%02x%02x%02x');
        $this->assertGreaterThan($r + $b, $g * 1.5, 'the upload\'s green, not the built-in\'s colours');

        // No picture: the swatch is not offered, and the page stands on the accent.
        Role::where('id', $role->id)->update(['header_image' => 'none', 'header_image_url' => null]);
        Aurora::refreshHeaderColors($role->fresh());
        $this->assertNull($built->fresh()->colors);
        $this->assertSame('accent', Aurora::for($role->fresh())->palette);
    }

    public function test_a_ticket_carries_a_still_frame_of_the_schedules_aurora(): void
    {
        $role = $this->aurora();
        Aurora::saveSettings($role, 'glacier', null, 2, true);

        $css = (string) $this->blade("@include('partials.guest-ticket-styles')", ['themeRole' => $role->fresh()]);

        $this->assertStringContainsString('.gk-tkpage::before { background: '.Aurora::for($role->fresh())->staticGradient('dark').'; }', $css);
    }

    public function test_aurora_survives_a_backup_with_its_arrangement(): void
    {
        $owner = $this->createOwner();
        $role = $this->aurora(['email' => $owner->email], $owner);
        // No row at all: the arrangement comes from the id, which a restore changes.
        $seed = Aurora::for($role)->seed;

        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = json_decode(json_encode(app(BackupService::class)->exportSchedules([$role->fresh()], false, $job)['json']), true);

        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        app(BackupService::class)->importSchedules($data, [0], $owner->id, $job);
        $restored = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();

        $this->assertSame('aurora', $restored->background);
        $this->assertSame($seed, Aurora::for($restored)->seed);

        // A background this install does not know restores as a gradient rather than failing.
        $data['schedules'][0]['role']['background'] = 'hologram';
        $job = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        app(BackupService::class)->importSchedules($data, [0], $owner->id, $job);
        $this->assertSame('gradient', Role::where('user_id', $owner->id)->latest('id')->firstOrFail()->background);
    }
}
