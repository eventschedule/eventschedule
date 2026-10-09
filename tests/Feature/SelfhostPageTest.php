<?php

namespace Tests\Feature;

use App\Utils\PlanRateCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /selfhost: "Watch it come up" (2026-10).
 *
 * The page is whole without its script: every scene of the install, every command, every screen
 * of the control room and every feature of the wall is in the HTML, drawn in the state the
 * sequences END on. The script only lays the scenes on a desk of two windows, walks the plan dial up
 * to the selfhosted column once, and lets a visitor press what is there. So everything that can go
 * wrong here goes wrong quietly: a scene the script cannot find, a wall that starts dark for a
 * visitor with no script, a step beside the stage that the HowTo block does not carry, a figure
 * that no longer follows the plan table. Each test below holds one of those.
 */
class SelfhostPageTest extends TestCase
{
    use RefreshDatabase;

    private function html(): string
    {
        return $this->get('/selfhost')->assertOk()->getContent();
    }

    public function test_the_wall_is_drawn_fully_lit_and_its_counts_add_up(): void
    {
        $html = $this->html();

        // Without the script nothing climbs: the page must already stand on the last answer.
        $this->assertSame(1, preg_match('/<div class="sh-unlock"[^>]*data-plan="self"/', $html), 'The wall must be drawn on the selfhosted column');
        $this->assertSame(1, preg_match('/<button[^>]*data-sh-plan="self"[^>]*aria-pressed="true"/', $html));

        preg_match_all('/class="sh-chip[^"]*" data-tier="([fpes])"/', $html, $chips);
        $tiers = array_count_values($chips[1]);
        $total = count($chips[1]);

        $this->assertGreaterThanOrEqual(60, $total);
        $this->assertSame(2, $tiers['s'], 'Two features exist only on a selfhosted install (docs/FEATURES.md)');

        foreach ([
            'free' => $tiers['f'],
            'pro' => $tiers['f'] + $tiers['p'],
            'ent' => $tiers['f'] + $tiers['p'] + $tiers['e'],
            'self' => $total,
        ] as $plan => $lit) {
            $this->assertStringContainsString('data-for="'.$plan.'">'.$lit.' <small>of '.$total.'</small>', $html, "The read-out for {$plan} does not match the wall");
        }
    }

    public function test_the_read_outs_follow_the_plan_table_and_the_installation_s_prices(): void
    {
        $html = $this->html();
        $rows = collect(PlanRateCard::rows());
        $mail = $rows->first(fn ($row) => str_starts_with($row[0], 'Newsletter emails'));
        $team = $rows->first(fn ($row) => str_starts_with($row[0], 'Team members'));

        $this->assertNotNull($mail, 'The rate card no longer has a newsletter row: the page would fall back to its own numbers');
        $this->assertNotNull($team, 'The rate card no longer has a team row: the page would fall back to its own numbers');

        foreach ([[$mail, 1, 'free'], [$mail, 2, 'pro'], [$mail, 3, 'ent'], [$team, 1, 'free'], [$team, 3, 'ent']] as [$row, $column, $plan]) {
            $this->assertStringContainsString('data-for="'.$plan.'">'.$row[$column].'</span>', $html);
        }

        $pricing = \App\Utils\PlatformPricing::all();
        $this->assertStringContainsString('data-for="pro">'.e(plan_price($pricing['proMonthly'])).' <small>a month</small>', $html);
        $this->assertStringContainsString('data-for="ent">'.e(plan_price($pricing['entMonthly'])).' <small>a month</small>', $html);
        $this->assertStringContainsString('data-for="self">'.e(plan_price(0)).' <small>forever</small>', $html);
    }

    public function test_every_scene_of_the_install_is_in_the_page_and_every_tab_finds_its_panel(): void
    {
        $html = $this->html();

        foreach (['softaculous', 'docker', 'manual'] as $method) {
            $this->assertStringContainsString('id="tab-'.$method.'" aria-controls="panel-'.$method.'"', $html);
            $this->assertStringContainsString('id="panel-'.$method.'" aria-labelledby="tab-'.$method.'" data-sh-scene="1" data-sh-panel="'.$method.'"', $html);
        }

        foreach ([1, 2, 3, 4] as $beat) {
            $this->assertStringContainsString('data-sh-scene="'.$beat.'"', $html, "Scene {$beat} is missing");
            $this->assertStringContainsString('data-sh-beat="'.$beat.'"', $html, "Step {$beat} is missing");
        }

        // The commands a crawler and a visitor without a script must still be given.
        foreach (['docker compose up --build -d', 'chown -R www-data:www-data storage bootstrap public .env', '* * * * * php /path/to/eventschedule/artisan schedule:run'] as $command) {
            $this->assertStringContainsString($command, $html);
        }

        // The wizard's real labels (lang/en/messages.php), not ones made up for the page.
        foreach (['mysql_host', 'port', 'database', 'username', 'test', 'connection_successful', 'full_name', 'sign_up'] as $key) {
            $this->assertStringContainsString(__('messages.'.$key, [], 'en'), $html, "The wizard scene lost the label messages.{$key}");
        }
    }

    public function test_the_steps_beside_the_stage_are_the_howto_block_s_own(): void
    {
        $html = $this->html();

        preg_match_all('#<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $blocks);
        $howTo = collect($blocks[1])->map(fn ($json) => json_decode($json, true))->first(fn ($node) => ($node['@type'] ?? null) === 'HowTo');
        $this->assertNotNull($howTo, 'The HowTo block is missing');
        $steps = $howTo['step'] ?? [];
        $this->assertCount(3, $steps);

        foreach ($steps as $step) {
            $this->assertStringContainsString('<span class="sh-beat-long">'.e($step['name']).'</span>', $html, 'A step of the HowTo block is not beside the stage: '.$step['name']);
            $this->assertStringContainsString('<span class="sh-beat-text">'.e($step['text']).'</span>', $html);
        }
    }

    public function test_the_update_screen_shows_the_installed_version_and_the_app_s_own_words(): void
    {
        $html = $this->html();
        $installed = config('self-update.version_installed');

        $this->assertStringContainsString('<b class="is-before">'.$installed.'</b>', $html);

        foreach (['installed_version', 'latest_version', 'last_checked', 'app_update', 'app_update_available', 'app_update_backup_warning', 'check_for_updates', 'update'] as $key) {
            $this->assertStringContainsString(e(__('messages.'.$key, [], 'en')), $html, "The update screen lost messages.{$key}");
        }
    }

    public function test_the_switches_start_where_a_new_install_starts(): void
    {
        $html = $this->html();

        // Federation is off until an admin switches it on.
        $this->assertSame(1, preg_match('/<div class="sh-fed"[^>]*data-on="0"/', $html));
        $this->assertSame(1, preg_match('/role="switch" aria-checked="false"[^>]*data-sh-fed-switch/', $html));

        // Six things are yours to run, none ticked for you.
        $this->assertSame(6, preg_match_all('/<button[^>]*data-sh-pre-item/', $html));
        $this->assertSame(0, preg_match('/aria-pressed="true"[^>]*data-sh-pre-item/', $html));
    }

    public function test_every_screen_of_the_control_room_is_a_real_file_in_both_lights(): void
    {
        $html = $this->html();

        preg_match_all('#/images/docs/(selfhost-admin--[a-z-]+?)(-dark)?\.webp#', $html, $shots);
        // The stage ends on the dashboard; the control room shows three screens it has not.
        $this->assertGreaterThanOrEqual(8, count($shots[0]), 'The page lost its real screens');
        $this->assertSame(2, substr_count($html, 'selfhost-admin--dashboard'), 'The dashboard is the stage\'s last scene and must not be a control room tab as well');

        foreach (array_unique($shots[0]) as $path) {
            $this->assertFileExists(public_path(ltrim($path, '/')));
        }
    }

    public function test_a_typed_domain_has_somewhere_to_go_on_every_screen(): void
    {
        $html = $this->html();

        $this->assertSame(1, preg_match_all('/<input[^>]*data-sh-domain-input/', $html));
        // Terminal titles, browser bars, the wizard's email, the federation map, the tenants, the last heading.
        $this->assertGreaterThanOrEqual(15, substr_count($html, 'data-sh-domain>') + substr_count($html, 'data-sh-domain '));
        $this->assertStringContainsString('data-sh-domain data-sh-default="Your server">Your server</span> is', $html);
    }

    public function test_what_the_visitor_did_has_somewhere_to_be_said_back(): void
    {
        $html = $this->html();

        // The six items feed the comparison: untouched, the mark stays where the page put it.
        $this->assertSame(1, preg_match('/<div class="sh-vs" data-sh-vs data-pick=""/', $html));
        $this->assertSame(1, preg_match_all('/<div[^>]*data-sh-vs-card="hosted"/', $html));
        $this->assertSame(1, preg_match_all('/<div[^>]*data-sh-vs-card="self"/', $html));
        $this->assertStringContainsString('data-sh-verdict-n', $html);

        // The last panel: one link for a visitor who pressed nothing, and one per way in, hidden.
        $this->assertSame(1, preg_match('/data-sh-finale-go=""(?![^>]*\bhidden\b)/', $html));
        foreach (['softaculous', 'docker', 'manual'] as $method) {
            $this->assertSame(1, preg_match('/data-sh-finale-go="'.$method.'"[^>]*\bhidden\b/', $html), "The last panel has no hidden link for {$method}");
        }
        $this->assertSame(1, preg_match('/<ul class="sh-recap" data-sh-recap hidden>/', $html));
    }

    public function test_the_two_chips_that_change_their_words_say_both(): void
    {
        $html = $this->html();

        // A selfhosted install keeps the licence credit and has no per-schedule custom domain
        // (docs/BRANDING_MATRIX.md, docs/FEATURES.md): the wall may light neither as it stands hosted.
        $this->assertStringContainsString('<span class="is-hosted">White label</span><span class="is-self">White label, bar one small credit</span>', $html);
        $this->assertStringContainsString('<span class="is-hosted">Custom domain per schedule</span><span class="is-self">Your own domain, install-wide</span>', $html);
    }

    public function test_nothing_on_the_page_loops(): void
    {
        // A row of blinking cursors was taken off this page by request. Every sequence here plays
        // once and ends; a rule that repeats for ever is the thing that was asked not to come back.
        $view = file_get_contents(resource_path('views/marketing/selfhost.blade.php'));

        $this->assertStringNotContainsString('infinite', $view);
        $this->assertStringNotContainsString('setInterval', $view);
    }
}
