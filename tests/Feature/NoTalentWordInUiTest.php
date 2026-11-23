<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\ScheduleTypeLabel;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The word "talent" is a type in the code and nowhere a person reads it.
 *
 * The standard schedule is an "Event schedule" (`talent` in the code, the database, the API and
 * the URLs), and the people an event names are its "Lineup". Both used to print the key
 * `messages.talent`, which is why the type could not be renamed without renaming the heading.
 * App\Utils\ScheduleTypeLabel is the one place that turns the type into words.
 */
class NoTalentWordInUiTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * English keys whose value may still say it. `talent` and `talents` are kept, unread by any
     * view, because an install may have overridden them and ScheduleTypeLabel honours that.
     */
    private const ENGLISH_EXCEPTIONS = ['talent', 'talents'];

    public function test_no_english_ui_string_says_talent(): void
    {
        $lines = require base_path('resources/lang/en/messages.php');

        $offenders = [];
        foreach ($lines as $key => $value) {
            if (in_array($key, self::ENGLISH_EXCEPTIONS, true) || ! is_string($value)) {
                continue;
            }
            if (preg_match('/talent/i', $value)) {
                $offenders[] = $key;
            }
        }

        $this->assertSame([], $offenders, 'These English strings still say "talent": '.implode(', ', $offenders));
    }

    public function test_the_type_picker_offers_an_event_schedule(): void
    {
        $user = $this->createOwner();

        $text = $this->visibleText($this->actingAs($user)->get('/getting-started')->assertOk()->getContent());

        $this->assertStringContainsString(__('messages.schedule_type_talent'), $text);
        $this->assertStringContainsString(__('messages.venue'), $text);
        $this->assertStringContainsString(__('messages.curator'), $text);
        $this->assertDoesNotMatchRegularExpression('/talent/i', $text);
    }

    public function test_the_create_form_and_the_sidebar_never_say_talent(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'talent', ['name' => 'Northside Quartet']);
        $this->createRole($owner, 'venue', ['name' => 'Harbor Room']);

        $dashboard = $this->visibleText($this->actingAs($owner)->get('/dashboard')->assertOk()->getContent());
        $this->assertStringContainsString(__('messages.talent_schedules'), $dashboard);
        $this->assertDoesNotMatchRegularExpression('/talent/i', $dashboard);

        $create = $this->visibleText($this->actingAs($owner)->get(route('new', ['type' => 'talent']))->assertOk()->getContent());
        $this->assertStringContainsString(__('messages.schedule_type_talent'), $create);
        $this->assertDoesNotMatchRegularExpression('/talent/i', $create);
    }

    public function test_the_event_page_calls_its_people_the_lineup(): void
    {
        [$role, $event] = $this->eventWithActs();

        $html = $this->get($this->eventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('id="es-lineup-title" class="gk-label">'.__('messages.lineup').'</h2>', $html);
        // The published section ids stay: owners hide them in their own CSS.
        $this->assertStringContainsString('id="gp-talent"', $html);
        $this->assertDoesNotMatchRegularExpression('/talent/i', $this->visibleText($html));
    }

    public function test_an_owner_can_rename_the_lineup_on_their_own_page(): void
    {
        [$role, $event] = $this->eventWithActs();
        $role->forceFill(['custom_labels' => ['lineup' => ['value' => 'Vendors']]])->save();

        $html = $this->get($this->eventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('id="es-lineup-title" class="gk-label">Vendors</h2>', $html);
        $this->assertArrayHasKey('lineup', Role::getCustomizableLabels());
    }

    public function test_an_install_that_renamed_talent_keeps_its_own_word(): void
    {
        // The selfhost guide's example: an operator file that renames "Talent" to "Artist".
        $dir = storage_path('framework/testing/lang-'.Str::random(8));
        File::ensureDirectoryExists($dir.'/en');
        File::put($dir.'/en/messages.php', "<?php\nreturn ['talent' => 'Artist', 'talents' => 'Artists'];\n");
        config(['app.lang_overrides_path' => $dir]);

        try {
            $this->assertSame('Artist', ScheduleTypeLabel::for('talent'));
            $this->assertSame('Artist', ScheduleTypeLabel::lineup());
            $this->assertSame('Artists', ScheduleTypeLabel::lineupPlural());
            // A described line names it too, since the operator chose a word for it.
            $this->assertSame('Artist', ScheduleTypeLabel::describe('talent'));
        } finally {
            File::deleteDirectory($dir);
        }
    }

    public function test_the_label_helper(): void
    {
        $this->assertSame(__('messages.schedule_type_talent'), ScheduleTypeLabel::for('talent'));
        $this->assertSame(__('messages.venue'), ScheduleTypeLabel::for('venue'));
        $this->assertSame(__('messages.curator'), ScheduleTypeLabel::for('curator'));
        $this->assertSame('', ScheduleTypeLabel::describe('talent'));
        $this->assertSame(__('messages.venue'), ScheduleTypeLabel::describe('venue'));
        $this->assertSame(__('messages.lineup'), ScheduleTypeLabel::lineup());
    }

    /** The words a person sees: scripts, styles and every attribute dropped. */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style|template)\b[^>]*>.*?</\1>#si', ' ', $html);
        $html = preg_replace('#<!--.*?-->#s', ' ', $html);

        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
    }

    /** @return array{0: Role, 1: Event} */
    private function eventWithActs(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['name' => 'Riverside Listings']);
        $event = $this->createEvent($role, ['name' => 'Late Set', 'creator_role_id' => $role->id]);

        $act = $this->createRole($owner, 'talent', ['name' => 'Headliner', 'description' => 'A band with a bio.']);
        $event->roles()->attach($act->id, ['is_accepted' => true]);

        $bare = new Role;
        $bare->subdomain = 'act'.strtolower(Str::random(10));
        $bare->type = 'talent';
        $bare->name = 'Support Act';
        $bare->timezone = 'America/New_York';
        $bare->plan_type = 'free';
        $bare->save();
        $event->roles()->attach($bare->id, ['is_accepted' => true]);

        return [$role->fresh(), $event->fresh()];
    }

    private function eventUrl(Role $role, Event $event): string
    {
        return route('role.view_guest', ['subdomain' => $role->subdomain])
            .'/'.$event->slug.'?id='.UrlUtils::encodeId($event->id);
    }
}
