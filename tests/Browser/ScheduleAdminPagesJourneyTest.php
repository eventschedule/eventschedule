<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The pages of one schedule (role/show-admin), driven the way an owner drives them. Each journey
 * ends at the database: what the page looks like is the screenshots' business, what it DOES is
 * this file's. Three things these pages do that a rendered-HTML test cannot see: the tabs
 * navigate (a strip from a tablet up, a dropdown on a phone), a member's role saves the moment it
 * is picked, and the Availability grid, which lives inside the calendar's Vue mount, still takes
 * a click after its jQuery became plain script.
 */
class ScheduleAdminPagesJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function role(User $user, string $type, string $subdomain, string $name): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = $name;
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    private function person(string $name, string $email): User
    {
        return User::factory()->create(['name' => $name, 'email' => $email, 'email_verified_at' => now()]);
    }

    private function event(Role $role, string $name, int $days): Event
    {
        $event = new Event;
        $event->user_id = $role->user_id;
        $event->creator_role_id = $role->id;
        $event->name = $name;
        $event->slug = Str::slug($name).'-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::now('America/New_York')->startOfDay()->addDays($days)->setTime(20, 0)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /** A real phone's width, which a resized desktop window does not give (Chrome stops near 500px). */
    private function metrics(Browser $browser, int $width, int $height): void
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Emulation.setDeviceMetricsOverride',
            'params' => ['width' => $width, 'height' => $height, 'deviceScaleFactor' => 1, 'mobile' => $width < 600],
        ]);
    }

    public function test_the_tabs_take_you_there_on_a_laptop_and_on_a_phone(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');

        $this->browse(function (Browser $browser) use ($owner) {
            $browser->loginAs($owner);

            // A laptop: every tab is inside the page, the last one included. The last tab used to
            // be past the right edge, with the scrollbar hidden. Which tab is last depends on the
            // install (Plan exists only on a hosted one), so the journey asks the page.
            $this->metrics($browser, 1280, 900);
            $browser->visit('/lisasimpson/schedule')->waitFor('#admin-tabs');
            $edges = $browser->script('var strip = document.getElementById("admin-tabs"); var last = strip.querySelector("a.ap-tab:last-child"); var r = last.getBoundingClientRect(); return [Math.round(r.right), strip.scrollWidth <= strip.clientWidth + 1, last.href];')[0];
            $this->assertLessThanOrEqual(1280, $edges[0], 'the last tab ends inside the window');
            $this->assertTrue($edges[1], 'and the strip has nothing to scroll to at this width');

            $lastPath = parse_url($edges[2], PHP_URL_PATH);
            $this->assertNotSame('/lisasimpson/schedule', $lastPath, 'the last tab is another page');
            $browser->click('#admin-tabs a.ap-tab:last-child')
                ->waitForLocation($lastPath)
                ->assertAttribute('#admin-tabs a[aria-current="page"]', 'href', $edges[2]);

            // When the strip cannot fit (made narrow here, as a long language would make it), it
            // says there is more: a fade at the edge it runs on from, and the tab you are on in view.
            $more = $browser->script('var wrap = document.getElementById("admin-tabs-wrap"); var strip = document.getElementById("admin-tabs"); wrap.style.maxWidth = "220px"; window.dispatchEvent(new Event("load")); var cur = strip.querySelector("[aria-current]").getBoundingClientRect(); var box = strip.getBoundingClientRect(); var start = [wrap.classList.contains("more-before"), wrap.classList.contains("more-after"), cur.left >= box.left - 1 && cur.right <= box.right + 1]; strip.scrollLeft = 0; strip.dispatchEvent(new Event("scroll")); return start.concat([wrap.classList.contains("more-before"), wrap.classList.contains("more-after")]);')[0];
            $this->assertTrue($more[0], 'on the last tab there is more before');
            $this->assertFalse($more[1], 'and nothing after');
            $this->assertTrue($more[2], 'the tab you are on is in view');
            $this->assertFalse($more[3], 'scrolled back to the start there is nothing before');
            $this->assertTrue($more[4], 'and more after');

            // A phone: the strip gives way to a dropdown that reaches any tab in one choice.
            $this->metrics($browser, 390, 844);
            $browser->visit('/lisasimpson/schedule')->waitFor('#admin-tab-select');
            $this->assertFalse($browser->script('return document.getElementById("admin-tabs-wrap").offsetParent !== null;')[0], 'no strip on a phone');
            // Against the phone's own width: with a mobile viewport Chrome widens the layout to fit
            // what overflows, so innerWidth would agree with a page that is too wide.
            $this->assertLessThanOrEqual(390, $browser->script('return document.documentElement.scrollWidth;')[0], 'the page does not scroll sideways');

            $browser->select('#admin-tab-select', url('/lisasimpson/team'))
                ->waitForLocation('/lisasimpson/team')
                ->assertSelected('#admin-tab-select', url('/lisasimpson/team'));

            $this->metrics($browser, 1280, 900);
        });
    }

    public function test_a_members_role_saves_when_it_is_picked_and_shows_whole(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $talent = $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        $bart = $this->person('Bart Simpson', 'bart@gmail.com');
        $talent->users()->attach($bart->id, ['level' => 'viewer']);
        $select = '#member-level-'.UrlUtils::encodeId($bart->id);
        $level = fn () => RoleUser::where('role_id', $talent->id)->where('user_id', $bart->id)->value('level');

        $this->browse(function (Browser $browser) use ($owner, $select, $level) {
            $browser->loginAs($owner);
            $this->metrics($browser, 1280, 900);
            $browser->visit('/lisasimpson/team')->waitFor($select);

            // The dropdown read "Viewe" under its own arrow: the layout gives every select 1rem
            // of padding on all sides, which is the arrow's room.
            $padding = $browser->script('return parseFloat(getComputedStyle(document.querySelector("'.$select.'")).paddingRight);')[0];
            $this->assertGreaterThanOrEqual(32, $padding, 'room for the arrow');

            // It saves the moment it is picked. The database is asked until it says so: the page
            // that comes back looks the same as the one that left.
            $browser->select($select, 'admin');
            $browser->waitUsing(10, 100, fn () => $level() === 'admin', 'the new role was not saved');
            $browser->waitFor($select)->assertSelected($select, 'admin');

            // On a phone the row stacks: the role and Remove were off the right edge.
            $this->metrics($browser, 390, 844);
            $browser->visit('/lisasimpson/team')->waitFor($select);
            $inside = $browser->script('var controls = document.querySelectorAll(".page-table select, .page-table .c-actions button, .page-table .c-actions a"); return [controls.length, Array.prototype.every.call(controls, function (el) { var r = el.getBoundingClientRect(); return r.width > 0 && r.left >= 0 && r.right <= 390; })];')[0];
            $this->assertGreaterThanOrEqual(3, $inside[0], 'a dropdown, a Remove and the owner\'s link are on the page');
            $this->assertTrue($inside[1], 'every control of every row is on the screen');

            $this->metrics($browser, 1280, 900);
        });
    }

    public function test_a_request_is_accepted_from_its_card(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $venue = $this->role($owner, 'venue', 'bluenote', 'Blue Note');
        $homer = $this->person('Homer Simpson', 'homer@gmail.com');
        $sharps = $this->role($homer, 'talent', 'homersharps', 'Homer and the Sharps');
        $event = $this->event($sharps, 'Barbershop Night', 6);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);
        $accepted = fn () => DB::table('event_role')->where('event_id', $event->id)->where('role_id', $venue->id)->value('is_accepted');

        $this->browse(function (Browser $browser) use ($owner, $accepted) {
            $browser->loginAs($owner);
            $this->metrics($browser, 1280, 900);
            $browser->visit('/bluenote/requests')
                ->waitFor('.request-card')
                ->assertSeeIn('.request-card .request-title', 'Barbershop Night')
                ->assertSeeIn('.request-card', 'Homer and the Sharps');
            $this->assertNull($accepted(), 'not answered yet');

            $browser->click('.request-card .test-accept-event');
            $browser->waitUsing(10, 100, fn () => (int) $accepted() === 1, 'the request was not accepted');
            // The last request answered: its card is gone.
            $browser->waitUntilMissing('.request-card');
        });
    }

    public function test_availability_is_marked_and_saved_on_a_phone(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $talent = $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        // The page opens on the month it is in the app's own timezone, so the day is found there
        // too: in New York the last evening of a month is already the next month to the page.
        $first = now()->startOfMonth();
        $day = $first->copy()->addDays(14)->format('Y-m-d');
        $stored = fn () => json_decode((string) RoleUser::where('role_id', $talent->id)->where('user_id', $owner->id)->value('dates_unavailable'), true) ?: [];

        $this->browse(function (Browser $browser) use ($owner, $first, $day, $stored) {
            $browser->loginAs($owner);
            $this->metrics($browser, 390, 844);
            $browser->visit('/lisasimpson/availability')->waitFor('.day-element[data-date="'.$day.'"]');

            // Every date under its own weekday: seven cells to a row, and the first of the month in
            // the column its weekday owns. The grid used to hide the neighbouring months' days on a
            // phone and close up over them.
            $column = $browser->script('var cells = Array.prototype.slice.call(document.querySelectorAll(".day-element")); var first = document.querySelector(".day-element[data-date=\"'.$first->format('Y-m-d').'\"]"); var top = cells[0].getBoundingClientRect().top; var row = cells.filter(function (c) { return Math.abs(c.getBoundingClientRect().top - top) < 2; }); var lefts = row.map(function (c) { return Math.round(c.getBoundingClientRect().left); }); var x = Math.round(first.getBoundingClientRect().left); return [row.length, lefts.indexOf(x), cells.every(function (c) { return c.offsetParent !== null; })];')[0];
            $this->assertSame(7, $column[0], 'seven days to a row');
            $this->assertTrue($column[2], 'no day of the grid is hidden');
            $this->assertSame($first->dayOfWeek, $column[1], 'the first of the month stands under its own weekday (the week starts on Sunday here)');

            $this->assertTrue($browser->script('return document.getElementById("saveButton").disabled;')[0], 'nothing to save yet');

            $browser->click('.day-element[data-date="'.$day.'"]')
                ->waitFor('.day-element[data-date="'.$day.'"] .day-x');
            $this->assertFalse($browser->script('return document.getElementById("saveButton").disabled;')[0], 'Save wakes when a day is marked');

            $browser->click('#saveButton');
            $browser->waitUsing(10, 100, fn () => in_array($day, $stored(), true), 'the marked day was not saved');

            // And cleared again.
            $browser->visit('/lisasimpson/availability')->waitFor('.day-element[data-date="'.$day.'"] .day-x')
                ->click('.day-element[data-date="'.$day.'"]')
                ->waitUntilMissing('.day-element[data-date="'.$day.'"] .day-x')
                ->click('#saveButton');
            $browser->waitUsing(10, 100, fn () => ! in_array($day, $stored(), true), 'the cleared day was not saved');

            $this->metrics($browser, 1280, 900);
        });
    }
}
