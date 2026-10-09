<?php

namespace Tests\Feature;

use App\Http\Controllers\GraphicController;
use App\Models\Role;
use App\Models\User;
use App\Utils\EventTextGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Events graphic page (/{subdomain}/events-graphic), rebuilt on 2026-10-08 as one Vue mount
 * with one copy of every field.
 *
 * What this holds is the part a browser cannot be asked about from here: what the page boots with,
 * what a save may and may not be refused for, who is shown what, and that nobody's text is
 * compiled by the page's own mount. The picture and the caption themselves are held by
 * EventGraphicStructuralTest, GraphicPerScheduleCapTest and EventTextGeneratorTest.
 *
 * Three of these are faults the old page had or the rebuild nearly shipped:
 * - a stored hour of 0 (midnight) or day of 0 (Sunday) read with "or" came back as 9 and Monday;
 * - a row from before send_days existed kept one weekly day in send_day, and a MONTHLY row's day
 *   posted back as a week day made every later save answer 422;
 * - a schedule whose Enterprise plan lapsed kept its email switched on in storage, and the
 *   server's own check on that stored state refused a save that only changed the layout.
 */
class GraphicPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The page renders; it never calls out. (The AI rewrite and the header image fetch are
        // not exercised here.)
        Http::preventStrayRequests();
    }

    private function pageUrl(Role $role): string
    {
        return route('event.generate_graphic', ['subdomain' => $role->subdomain]);
    }

    private function saveUrl(Role $role): string
    {
        return route('event.save_graphic_settings', ['subdomain' => $role->subdomain]);
    }

    /**
     * A JSON object the page assigns in a script ("var boot = {...};"), read back out of the
     * HTML. By position, not by a pattern: the page is some 400 KB, and a lazy match across it
     * is the kind of regex that passes here and fails on CI (see TestCase::pregMatchOrFail()).
     */
    private function jsonAfter(string $html, string $marker): array
    {
        $at = strpos($html, $marker);
        $this->assertNotFalse($at, 'the page has no "'.$marker.'"');
        $start = $at + strlen($marker);
        $end = strpos($html, ";\n", $start);
        $this->assertNotFalse($end);
        $value = json_decode(substr($html, $start, $end - $start), true);
        $this->assertIsArray($value, 'what follows "'.$marker.'" is not JSON');

        return $value;
    }

    /** The settings object the page hands its script. */
    private function bootSettings(string $html): array
    {
        return $this->jsonAfter($html, 'var boot = ')['settings'];
    }

    public function test_midnight_and_sunday_survive_a_reload(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'weekly', 'send_days' => [0], 'send_day' => 0, 'send_hour' => 0, 'recipient_emails' => 'owner@gmail.com'];
        $role->save();

        $settings = GraphicController::pageSettings($role->fresh());
        $this->assertSame(0, $settings['send_hour'], 'midnight was read as another hour');
        $this->assertSame([0], $settings['send_days'], 'Sunday was read as another day');

        // And as the page itself carries them.
        $boot = $this->bootSettings($this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent());
        $this->assertSame(0, $boot['send_hour']);
        $this->assertSame([0], $boot['send_days']);
    }

    public function test_a_stored_sunday_does_not_become_a_day_of_the_month(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        // What the page has always sent for a weekly Sunday: send_day 0. The monthly select
        // offers 1 to 28, and the cron reads a monthly day of 0 as already passed.
        $role->graphic_settings = ['frequency' => 'weekly', 'send_days' => [0], 'send_day' => 0];
        $role->save();

        $settings = GraphicController::pageSettings($role->fresh());
        $this->assertSame(1, $settings['send_day']);

        $role->graphic_settings = ['frequency' => 'monthly', 'send_day' => 31];
        $role->save();
        $this->assertSame(28, GraphicController::pageSettings($role->fresh())['send_day']);
    }

    public function test_a_row_from_before_week_days_existed_can_still_be_saved(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        // Monthly on the 15th, saved before send_days was a key.
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'monthly', 'send_day' => 15, 'send_hour' => 9, 'recipient_emails' => 'owner@gmail.com'];
        $role->save();

        $settings = GraphicController::pageSettings($role->fresh());
        // 15 is not a day of the week: seeded into send_days it was posted back on every save
        // and refused by "send_days.* max:6".
        $this->assertSame([], $settings['send_days']);
        $this->assertSame(15, $settings['send_day']);

        // The page posts back what it was handed, as its script does.
        $this->actingAs($owner)->postJson($this->saveUrl($role), [
            'layout' => 'row',
            'enabled' => $settings['enabled'],
            'frequency' => $settings['frequency'],
            'send_days' => $settings['send_days'],
            'send_day' => $settings['send_day'],
            'send_hour' => $settings['send_hour'],
            'recipient_emails' => $settings['recipient_emails'],
        ])->assertOk()->assertJsonPath('success', true);

        // A weekly row of the same age keeps its one day.
        $role->graphic_settings = ['frequency' => 'weekly', 'send_day' => 4];
        $role->save();
        $this->assertSame([4], GraphicController::pageSettings($role->fresh())['send_days']);
    }

    public function test_a_save_that_does_not_mention_the_email_keeps_it_and_is_not_refused_for_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'weekly', 'send_days' => [1, 4], 'send_hour' => 7, 'recipient_emails' => 'owner@gmail.com', 'ai_prompt' => 'Add emoji.'];
        $role->save();

        $this->actingAs($owner)->postJson($this->saveUrl($role), ['layout' => 'list', 'image_size' => 'story'])
            ->assertOk()->assertJsonPath('success', true);

        $stored = $role->fresh()->graphic_settings;
        $this->assertSame('list', $stored['layout']);
        $this->assertTrue($stored['enabled']);
        $this->assertSame([1, 4], $stored['send_days']);
        $this->assertSame('owner@gmail.com', $stored['recipient_emails']);
        $this->assertSame('Add emoji.', $stored['ai_prompt']);
    }

    public function test_a_lapsed_plan_with_the_email_still_stored_can_save_its_layout(): void
    {
        // A Free schedule on a hosted install, with what an Enterprise plan left behind:
        // switched on, weekly, and (from an older row) no send_days at all.
        $role = $this->createFreeRole(null, 'venue');
        $this->assertFalse($role->fresh()->isEnterprise());
        $owner = User::find($role->user_id);
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'weekly', 'send_day' => 2, 'recipient_emails' => ''];
        $role->save();

        // The page leaves the email keys out for a plan that cannot see the row.
        $this->actingAs($owner)->postJson($this->saveUrl($role), ['layout' => 'row', 'max_per_row' => 3])
            ->assertOk()->assertJsonPath('success', true);

        $stored = $role->fresh()->graphic_settings;
        $this->assertSame('row', $stored['layout']);
        $this->assertTrue($stored['enabled'], 'a locked part keeps what it holds');

        // The page itself tells that owner the email is paused, with the lock, and offers no
        // email field to post.
        $html = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.graphic_sum_email_paused'), $html);
        $this->assertStringNotContainsString('id="recipient_emails"', $html);
        $this->assertStringNotContainsString('id="ai_prompt"', $html);
    }

    public function test_a_refused_save_names_the_field_in_json(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $this->actingAs($owner)->postJson($this->saveUrl($role), ['enabled' => true, 'frequency' => 'daily', 'recipient_emails' => ''])
            ->assertStatus(422)->assertJsonPath('errors.recipient_emails.0', __('messages.email_required'));

        $this->actingAs($owner)->postJson($this->saveUrl($role), ['enabled' => true, 'frequency' => 'weekly', 'send_days' => [], 'recipient_emails' => 'owner@gmail.com'])
            ->assertStatus(422)->assertJsonPath('errors.send_days.0', __('messages.send_days_required'));

        // A rule-level refusal is JSON too, for a request that asks for JSON as the page's do.
        // (Without the header it is a redirect, which the old page then failed to parse.)
        $this->actingAs($owner)->withHeaders(['Accept' => 'application/json'])
            ->post($this->saveUrl($role), ['text_template' => str_repeat('a', 2001)])
            ->assertStatus(422)->assertJsonValidationErrors('text_template');

        // The page asks for JSON on every request it makes.
        $html = $this->actingAs($owner)->get($this->pageUrl($role))->getContent();
        $this->assertStringContainsString("'Accept': 'application/json'", $html);
    }

    public function test_a_viewer_is_not_handed_what_only_an_editor_can_use(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'daily', 'recipient_emails' => 'owner@gmail.com'];
        $role->save();
        $viewer = User::factory()->create(['email_verified_at' => now()]);
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $mine = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();
        $theirs = $this->actingAs($viewer)->get($this->pageUrl($role))->assertOk()->getContent();

        foreach (['class="event-save-bar gfx-bar"', 'type="file"', '@click="sendTest"'] as $editorOnly) {
            $this->assertStringContainsString($editorOnly, $mine, 'the owner lost '.$editorOnly);
            $this->assertStringNotContainsString($editorOnly, $theirs, 'a viewer is shown '.$editorOnly);
        }
        $this->assertStringContainsString(__('messages.graphic_viewer'), $theirs);
        $this->assertStringNotContainsString(__('messages.graphic_viewer'), $mine);

        // And the server agrees, in words the page prints.
        $this->actingAs($viewer)->postJson($this->saveUrl($role), ['layout' => 'row'])
            ->assertStatus(403)->assertJsonPath('error', __('messages.not_authorized'));
    }

    public function test_nobodys_text_is_compiled_by_the_pages_own_mount(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Blue {{ 7*7 }} Note </script><script>window.x=1</script>']);
        $role->graphic_settings = [
            'header_text' => '{{ 6*6 }} at {schedule_name}',
            'footer_text' => '{{ 5*5 }}</script><script>window.y=1</script>',
            'text_template' => '{{ constructor.constructor("window.z=1")() }} {event_name}',
            'overlay_text' => '{{ 4*4 }}',
            'ai_prompt' => '{{ 3*3 }}',
            'recipient_emails' => '{{ 2*2 }}@gmail.com',
        ];
        $role->save();
        // A timezone cannot be saved like this (the forms validate it); the page must not rely on that.
        DB::table('roles')->where('id', $role->id)->update(['timezone' => '{{ 1+1 }}']);

        $html = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();

        $from = strpos($html, '<div id="graphic-app"');
        $to = strpos($html, 'var boot = ');
        $this->assertNotFalse($from);
        $this->assertGreaterThan($from, $to);
        $mount = substr($html, $from, strrpos(substr($html, 0, $to), '<script') - $from);

        // What stands under v-pre is not compiled. Everything else in the mount must be free of
        // a mustache: Vue compiles a text node there, and Blade's escaping does not stop it.
        $this->assertStringContainsString('v-pre>({{ 1+1 }})', $mount, 'the timezone is no longer printed under v-pre');
        $withoutPre = str_replace('v-pre>({{ 1+1 }})', '', $mount);
        $this->assertStringNotContainsString('{{', $withoutPre, 'somebody\'s text is a text node inside the mount');

        // The settings travel as data, and a closing script tag inside them cannot end the script.
        $this->assertSame('{{ 6*6 }} at {schedule_name}', $this->bootSettings($html)['header_text']);
        $this->assertStringNotContainsString('</script><script>window.y=1', $html);
        $this->assertStringNotContainsString('</script><script>window.x=1', $html);
    }

    public function test_every_row_is_one_the_help_link_can_follow(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $html = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();

        preg_match_all('/<button[^>]*data-row-group="graphic"[^>]*aria-controls="([^"]+)"/', $html, $rows);
        $this->assertSame(
            ['graphic-pane-events', 'graphic-pane-flyers', 'graphic-pane-header', 'graphic-pane-caption', 'graphic-pane-email'],
            $rows[1],
        );

        // The map the layout prints for this address is the one the Help link reads.
        $anchors = $this->jsonAfter($html, 'var anchorMap = ');

        $guide = file_get_contents(resource_path('views/marketing/docs/event-graphics.blade.php'));
        foreach ($rows[1] as $pane) {
            $this->assertArrayHasKey($pane, $anchors, $pane.' has no anchor in HelpUtils');
            $this->assertStringContainsString('id="'.$pane.'"', $html, 'the row opens a pane that is not on the page');
            $fragment = parse_url($anchors[$pane], PHP_URL_FRAGMENT);
            $this->assertNotEmpty($fragment, $pane.' leads to the top of the guide');
            $this->assertStringContainsString('id="'.$fragment.'"', $guide, 'the guide has no #'.$fragment);
        }
    }

    public function test_the_page_and_the_server_share_one_default_wording(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // Nothing saved: the page shows, previews and saves the server's own default. It used
        // to carry a copy with a line the server's lacked.
        $html = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();
        $this->assertSame(EventTextGenerator::getDefaultTemplate(), $this->bootSettings($html)['text_template']);
        $this->assertStringContainsString('{short_description}', EventTextGenerator::getDefaultTemplate());
    }

    public function test_sending_it_now_with_nothing_to_send_says_so(): void
    {
        Mail::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $role->graphic_settings = ['enabled' => true, 'frequency' => 'daily', 'recipient_emails' => 'owner@gmail.com'];
        $role->save();

        // No upcoming event has a flyer: it used to answer "No events found".
        $this->actingAs($owner)->postJson(route('event.graphic_test_email', ['subdomain' => $role->subdomain]))
            ->assertStatus(400)->assertJsonPath('message', __('messages.graphic_nothing_to_send'));
    }

    /**
     * The default wording gained a {short_description} line with the rebuild. A schedule whose
     * email is on and whose wording was never stored would have had its email grow that line with
     * nobody asking: the migration writes down the wording that email uses today.
     */
    public function test_a_scheduled_email_with_no_wording_stored_keeps_the_wording_it_had(): void
    {
        // Globbed, not hardcoded: a rename to another day must not turn this into a fatal `require`.
        $file = collect(File::glob(database_path('migrations/*_keep_graphic_email_wording.php')))->first();
        $this->assertNotNull($file, 'the migration that keeps the email wording is missing');

        $owner = $this->createOwner();
        $rows = [
            // Switched on before the wording box existed, and not saved since.
            'never' => ['enabled' => true, 'frequency' => 'weekly', 'send_day' => 2, 'recipient_emails' => 'owner@gmail.com'],
            // The box emptied, then saved: the page posts an empty string, which is stored as null.
            'emptied' => ['enabled' => true, 'frequency' => 'daily', 'recipient_emails' => 'owner@gmail.com', 'text_template' => null, 'layout' => 'row'],
            'own' => ['enabled' => true, 'frequency' => 'daily', 'recipient_emails' => 'owner@gmail.com', 'text_template' => '{event_name} at {venue}'],
            'off' => ['enabled' => false, 'frequency' => 'daily', 'recipient_emails' => 'owner@gmail.com'],
        ];
        $ids = [];
        $before = [];
        foreach ($rows as $name => $settings) {
            $role = $this->createRole($owner, 'venue');
            DB::table('roles')->where('id', $role->id)->update(['graphic_settings' => json_encode($settings)]);
            $ids[$name] = $role->id;
        }
        // And one that is not a settings object at all (a backup restore once stored such things).
        $odd = $this->createRole($owner, 'venue');
        DB::table('roles')->where('id', $odd->id)->update(['graphic_settings' => '"enabled"']);
        $ids['odd'] = $odd->id;
        foreach ($ids as $name => $id) {
            $before[$name] = DB::table('roles')->where('id', $id)->value('graphic_settings');
        }
        $stamp = DB::table('roles')->where('id', $ids['never'])->value('updated_at');

        $migration = require $file;
        $migration->up();

        $after = fn (string $name) => DB::table('roles')->where('id', $ids[$name])->value('graphic_settings');
        $old = "*{day_name}* {date_dmy} | {time}\n*{event_name}*:\n{venue} | {city}\n{url}";

        foreach (['never', 'emptied'] as $name) {
            $stored = json_decode($after($name), true);
            $this->assertSame($old, $stored['text_template'], $name.': the wording in use was not written down');
            // Everything else in the row is as it was.
            unset($stored['text_template']);
            $was = $rows[$name];
            unset($was['text_template']);
            $this->assertSame($was, $stored, $name.': something besides the wording changed');
        }
        foreach (['own', 'off', 'odd'] as $name) {
            $this->assertSame($before[$name], $after($name), $name.' was rewritten');
        }
        $this->assertSame($stamp, DB::table('roles')->where('id', $ids['never'])->value('updated_at'), 'the schedule was touched as though it had been edited');

        // What it is for: the caption that schedule's email carries has no new line in it, while a
        // schedule with nothing stored and the email off gets today's default, as its page shows.
        $role = Role::find($ids['never']);
        $event = $this->createEvent($role, ['name' => 'Late Jam', 'short_description' => 'Bring your horn.']);
        $kept = EventTextGenerator::generate($role, [$event], false, $role->graphic_settings['text_template'] ?? '');
        $this->assertStringContainsString('Late Jam', $kept);
        $this->assertStringNotContainsString('Bring your horn.', $kept);
        $this->assertStringContainsString('Bring your horn.', EventTextGenerator::generate($role, [$event], false, ''));

        // And the page shows that schedule the wording its email really uses.
        $html = $this->actingAs($owner)->get($this->pageUrl($role))->assertOk()->getContent();
        $this->assertSame($old, $this->bootSettings($html)['text_template']);

        // Twice is once.
        $again = $after('never');
        (require $file)->up();
        $this->assertSame($again, $after('never'));
    }
}
