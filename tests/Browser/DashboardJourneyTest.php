<?php

namespace Tests\Browser;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Cache;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * /dashboard, and the cookie notice it depends on, driven in a browser.
 *
 * Every journey here is a defect that five hundred green feature tests sat beside, because a
 * rendered-HTML test cannot see any of them: a button that is in the page and hidden, a menu that
 * opens off the edge of a phone, a dialog that has to post and come back, and a choice the
 * browser writes into a cookie. Each ends at something the page DID, not at what it printed.
 *
 * Nothing here assumes a hosted install: the page is asked what it has.
 */
class DashboardJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function person(string $name, string $email): User
    {
        return User::factory()->create(['name' => $name, 'email' => $email, 'email_verified_at' => now()]);
    }

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

    /** A real phone's width, which a resized desktop window does not give (Chrome stops near 500px). */
    private function metrics(Browser $browser, int $width, int $height): void
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Emulation.setDeviceMetricsOverride',
            'params' => ['width' => $width, 'height' => $height, 'deviceScaleFactor' => 1, 'mobile' => $width < 600],
        ]);
    }

    /** [left, right, shown] of an element, in the viewport's own pixels. */
    private function box(Browser $browser, string $selector): array
    {
        return $browser->script(
            'var el = document.querySelector('.json_encode($selector).');'
            .'if (!el) return [0, 0, false];'
            .'var r = el.getBoundingClientRect();'
            .'return [Math.round(r.left), Math.round(r.right), r.width > 0 && r.height > 0 && getComputedStyle(el).visibility !== "hidden"];'
        )[0];
    }

    /**
     * "I don't see a way to add more schedules." The button was in the page and hidden on a
     * laptop, and on a phone its menu opened past the left edge of the screen: the shared menu
     * script hangs a menu from its button's end, and this button sits at the start of the row.
     * With two schedules there is no "View page" before it, which is the worst case.
     */
    public function test_new_schedule_and_customize_can_be_reached_on_a_laptop_and_on_a_phone(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        $this->role($owner, 'venue', 'thevinylroom', 'The Vinyl Room');

        $this->browse(function (Browser $browser) use ($owner) {
            $browser->loginAs($owner);

            // A laptop: both secondary buttons are on the page, and the menu opens inside it.
            $this->metrics($browser, 1280, 900);
            $browser->visit('/dashboard')->waitFor('[data-popup-target="dashboard-new-schedule-menu"]');
            $this->assertTrue($this->box($browser, 'button[data-popup-target="dashboard-new-schedule-menu"]')[2], 'New Schedule is shown');
            $this->assertTrue($this->box($browser, 'button[data-dashboard-customize]')[2], 'Customize is shown');

            $browser->click('button[data-popup-target="dashboard-new-schedule-menu"]')->waitFor('#dashboard-new-schedule-menu');
            [$left, $right] = $this->box($browser, '#dashboard-new-schedule-menu');
            $this->assertGreaterThanOrEqual(0, $left);
            $this->assertLessThanOrEqual(1280, $right);
            $this->assertSame(3, count($browser->elements('#dashboard-new-schedule-menu a[href*="/new/"]')));

            // A phone: the two rare actions are behind one button, and its menu has to open
            // where a thumb can reach it.
            $this->metrics($browser, 390, 844);
            $browser->visit('/dashboard')->waitFor('button[data-popup-target="dashboard-more-menu"]');
            $this->assertTrue($this->box($browser, 'button[data-popup-target="dashboard-more-menu"]')[2]);
            $this->assertFalse($this->box($browser, 'button[data-popup-target="dashboard-new-schedule-menu"]')[2], 'its own button is a laptop\'s');

            $browser->click('button[data-popup-target="dashboard-more-menu"]')->waitFor('#dashboard-more-menu');
            [$left, $right, $shown] = $this->box($browser, '#dashboard-more-menu');
            $this->assertTrue($shown);
            $this->assertGreaterThanOrEqual(0, $left, 'the menu starts inside the screen');
            $this->assertLessThanOrEqual(390, $right, 'and ends inside it');
            $this->assertTrue($this->box($browser, '#dashboard-more-menu button[data-dashboard-customize]')[2]);

            // And it leads somewhere: the venue form.
            $browser->click('#dashboard-more-menu a[href$="/new/venue"]')->waitForLocation('/new/venue');
        });
    }

    /**
     * Customize is a dialog that posts and reloads. What it saved is read from the database, and
     * the page that comes back has to have taken it: the card switched off is gone and the
     * period chosen is the one the page was built for.
     */
    public function test_customize_saves_and_the_page_comes_back_changed(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $role = $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        // Something to count, so the page shows its tiles and cards and not the first-day view.
        \App\Models\AnalyticsDaily::create(['role_id' => $role->id, 'date' => now()->toDateString(), 'desktop_views' => 3, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);

        $this->browse(function (Browser $browser) use ($owner) {
            $this->metrics($browser, 1280, 900);
            $browser->loginAs($owner)->visit('/dashboard')
                ->waitFor('#dashboard-activity')
                ->click('button[data-dashboard-customize]')
                ->waitFor('#dashboard-customize [role="dialog"]');

            // Seven days, and Recent Activity off. The dialog's buttons are found by what they
            // are (the pressed state, the switch's label), not by where they happen to sit.
            $browser->script('document.querySelectorAll("#dashboard-customize [role=group] button")[0].click();');
            $browser->script('document.querySelector("#dashboard-customize [role=switch][aria-labelledby=dashboard-customize-recent_activity]").click();');
            $browser->pause(100);
            $this->assertSame('true', $browser->script('return document.querySelectorAll("#dashboard-customize [role=group] button")[0].getAttribute("aria-pressed");')[0]);
            $this->assertSame('false', $browser->script('return document.querySelector("#dashboard-customize [role=switch][aria-labelledby=dashboard-customize-recent_activity]").getAttribute("aria-checked");')[0]);

            $browser->script('var buttons = document.querySelectorAll("#dashboard-customize [role=dialog] > div:last-child button"); buttons[buttons.length - 1].click();');

            $browser->waitUsing(10, 100, function () use ($owner) {
                $panels = collect($owner->fresh()->dashboard_config['panels'] ?? [])->keyBy('id');

                return (int) ($panels['views']['period'] ?? 0) === 7 && ($panels['recent_activity']['visible'] ?? true) === false;
            }, 'the choice was saved');

            $browser->visit('/dashboard')->waitFor('#dashboard-coming-up');
            $browser->assertMissing('#dashboard-activity');
            $this->assertStringContainsString('"period":7', $browser->driver->getPageSource());
        });
    }

    /**
     * Realtime is a tab of Analytics. The dashboard's Realtime number opens that tab, the tab's
     * own app starts and draws its chart, and the sidebar has no Realtime entry of its own. The filters that mean nothing for the last half hour are
     * not on the tab, and going back to Web brings them back.
     */
    public function test_the_realtime_number_opens_the_realtime_tab_of_analytics(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $role = $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        \App\Models\AnalyticsDaily::create(['role_id' => $role->id, 'date' => now()->toDateString(), 'desktop_views' => 3, 'mobile_views' => 0, 'tablet_views' => 0, 'unknown_views' => 0]);

        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
        Cache::flush();

        $this->browse(function (Browser $browser) use ($owner) {
            $this->metrics($browser, 1280, 900);
            $browser->loginAs($owner)->visit('/dashboard')->waitFor('a[data-live-tile]');

            // Nothing in the sidebar goes to Realtime; Analytics is the way in.
            $this->assertSame(0, count($browser->elements('nav a[href*="realtime"]')));

            $browser->click('a[data-live-tile]')
                ->waitForLocation('/analytics')
                ->assertQueryStringHas('tab', 'realtime')
                ->waitFor('#schedule-realtime section');

            // The tab is the current one, and its app is running: the status line is Vue's.
            $this->assertStringContainsString(__('messages.realtime'), $browser->text('#analytics-tabs [aria-current="page"]'));
            $browser->assertSeeIn('#schedule-realtime', __('messages.realtime_live'));
            // And the chart was really drawn: Vue swallows an error thrown while mounting, so
            // the tab would appear, say "Live", and hold an empty canvas. Chart.js registers a
            // chart before it lays it out, so "it exists" is not enough: it has an area, and
            // both of its datasets (the bars, and the marks on them) hold their thirty minutes.
            $drawn = $browser->script('var c = document.querySelector("#schedule-realtime canvas"); var chart = c && window.Chart ? Chart.getChart(c) : null;'
                .' return chart && chart.chartArea ? [chart.getDatasetMeta(0).data.length, chart.getDatasetMeta(1).data.length] : null;')[0];
            $this->assertSame([30, 30], $drawn, 'The chart was never drawn.');
            $browser->assertMissing('#date-range');

            $browser->click('#analytics-tabs a:first-child')
                ->waitFor('#date-range')
                ->assertMissing('#schedule-realtime');
        });
    }

    /**
     * The Realtime tab is not only traffic: beside it stands what people did on the schedule in
     * the last day, and who has arrived at tonight's event. The rail is in the first render, a
     * count filters it and says so, the door card opens Check-in on its own event, and a sale
     * made while the tab is open arrives by itself: the traffic poll sees a new mark on the
     * chart and asks for the rail at once, without waiting out its minute.
     *
     * None of that can be seen by a feature test: the rail is a Vue list over JSON, and "arrives
     * by itself" is two polls and a comparison in the browser.
     */
    public function test_the_realtime_tab_lists_what_happened_and_hears_of_a_sale_by_itself(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $role = $this->role($owner, 'venue', 'vinylroom', 'The Vinyl Room');

        // On tonight, on the schedule's own clock, whatever time the suite runs at.
        $today = now('America/New_York');
        $event = new \App\Models\Event;
        $event->user_id = $owner->id;
        $event->creator_role_id = $role->id;
        $event->name = 'Jazz Night';
        $event->slug = 'jazz-night';
        $event->starts_at = $today->copy()->setTime(23, 30)->utc()->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->tickets_enabled = true;
        $event->ticket_currency_code = 'USD';
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $ticket = new \App\Models\Ticket;
        $ticket->event_id = $event->id;
        $ticket->type = 'General';
        $ticket->price = 15;
        $ticket->quantity = 100;
        $ticket->save();

        $buy = function (string $name, array $seats, $paidAt = null) use ($event, $role, $ticket, $today) {
            $sale = new \App\Models\Sale;
            $sale->paid_at = $paidAt;
            $sale->event_id = $event->id;
            $sale->subdomain = $role->subdomain;
            $sale->name = $name;
            $sale->email = \Illuminate\Support\Str::slug($name, '.').'@gmail.com';
            $sale->event_date = $today->format('Y-m-d');
            $sale->status = 'paid';
            $sale->payment_method = 'stripe';
            $sale->payment_amount = 15 * count($seats);
            $sale->secret = \Illuminate\Support\Str::random(32);
            $sale->save();

            $line = new \App\Models\SaleTicket;
            $line->sale_id = $sale->id;
            $line->ticket_id = $ticket->id;
            $line->quantity = count($seats);
            $line->seats = json_encode($seats);
            $line->save();
        };
        $buy('Dana Whitlock', ['1' => time() - 120, '2' => null]);

        $follower = $this->person('Fiona Ashdown', 'fiona@gmail.com');
        $follower->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);

        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
        Cache::flush();

        $this->browse(function (Browser $browser) use ($owner, $event, $buy) {
            $this->metrics($browser, 1280, 900);
            $browser->loginAs($owner)->visit('/analytics?tab=realtime')
                ->waitFor('.rt-rail li[data-kind="sale"]')
                ->assertPresent('.rt-rail li[data-kind="follower"]')
                ->assertSeeIn('.rt-rail li[data-kind="sale"]', 'Jazz Night')
                ->assertSeeIn('.rt-rail li[data-kind="sale"]', '$30')
                // Nobody is named, on a tab whose every row has a name one click away.
                ->assertDontSeeIn('#schedule-realtime', 'Dana')
                ->assertDontSeeIn('#schedule-realtime', 'Fiona');

            // A count filters the list and says so; pressing it again lets go.
            $browser->click('.rt-rail button[data-feed="follower"]')
                ->waitUntilMissing('.rt-rail li[data-kind="sale"]')
                ->assertPresent('.rt-rail li[data-kind="follower"]')
                ->assertAttribute('.rt-rail button[data-feed="follower"]', 'aria-pressed', 'true')
                ->click('.rt-rail button[data-feed="follower"]')
                ->waitFor('.rt-rail li[data-kind="sale"]');

            // Tonight's event is at the door: one of the two seats sold has been scanned.
            $browser->assertSeeIn('.rt-door', 'Jazz Night')
                ->assertSeeIn('.rt-door', str_replace([':count', ':total'], ['1', '2'], __('messages.dash_count_of_total')));
            $this->assertStringContainsString('/checkin?event='.\App\Utils\UrlUtils::encodeId($event->id), $browser->attribute('.rt-door a', 'href'));

            // A sale lands while the tab is open. No reload, and no waiting for the rail's minute.
            // In the very second the first one was paid, on purpose: two people paying at once is
            // an ordinary night, and a page that went by the newest sale's time alone missed the
            // later of them until its minute was up (which is how this journey first failed).
            // The page now knows it by the count at that second and by the chart's total; this
            // journey proves that one of the two works, not which.
            $buy('Rita Okonkwo', ['1' => null], \App\Models\Sale::query()->oldest('id')->value('paid_at'));
            $bought = microtime(true);
            $browser->waitUsing(40, 500, fn () => count($browser->elements('.rt-rail li[data-kind="sale"]')) === 2, 'The new sale never reached the rail.');
            // "By itself" means on the next traffic poll, which is at most 15 seconds away. The
            // rail's own clock would bring it too, about a minute after the page loaded, and the
            // page was loaded a few seconds before the purchase: so inside 35 seconds it can only
            // have been the poll. (On a machine so slow that the steps above took half a minute,
            // this bound would no longer tell the two apart; the wait above still would.)
            $this->assertLessThan(35, microtime(true) - $bought, 'The sale reached the rail only when its minute came round.');
            $browser->assertSeeIn('.rt-rail button[data-feed="sale"]', '2')
                ->assertDontSeeIn('#schedule-realtime', 'Rita');

            $browser->click('.rt-door a')
                ->waitForLocation('/checkin')
                ->assertQueryStringHas('event', \App\Utils\UrlUtils::encodeId($event->id))
                ->waitForText('Jazz Night');
        });
    }

    /**
     * "Allow all" has to record what the notice said. Where schedule owners have their own
     * Realtime tab, the notice says on its first line that the organizer of a schedule page
     * sees visits to it, and a choice made on that notice carries "org" in the cookie, which is
     * the only thing that lets an organizer see the visitor as a row
     * (RealtimeTracker::consentCoversOrganizers()). Where they have none, the notice does not
     * say it and the choice does not carry it. No feature test runs this half: it is the
     * browser that writes the cookie.
     */
    public function test_allow_all_records_whether_the_notice_named_organizers(): void
    {
        $owner = $this->person('Lisa Simpson', 'lisa@gmail.com');
        $role = $this->role($owner, 'talent', 'lisasimpson', 'Lisa Simpson Quartet');
        $sentence = __('messages.cookie_consent_analytics_organizers');

        $choice = fn (Browser $browser) => $browser->script(
            'var m = document.cookie.match(/(?:^|; )cookie_consent=([^;]*)/);'
            .'var stored = null; try { stored = JSON.parse(localStorage.getItem("cookie_consent")); } catch (e) {}'
            .'return [m ? decodeURIComponent(m[1]) : "", stored ? stored.o : null];'
        )[0];

        $this->browse(function (Browser $browser) use ($role, $sentence, $choice) {
            $this->metrics($browser, 1280, 900);

            Setting::set('realtime_enabled', '1');
            Setting::set('realtime_owner_view', '1');
            Cache::flush();

            $browser->visit('/'.$role->subdomain)->waitFor('[data-cookie-consent]:not([hidden])');
            $this->assertTrue($browser->script('return document.querySelector("[data-cookie-consent]").hasAttribute("data-names-organizers");')[0]);
            // Read with nothing opened: the Choose panel is still closed.
            $this->assertTrue($browser->script('return document.querySelector("[data-cookie-consent-choices]").hidden;')[0]);
            $browser->assertSeeIn('[data-cookie-consent]', $sentence);

            $browser->click('[data-cookie-consent-action="all"]')->pause(400);
            [$cookie, $stored] = $choice($browser);
            $this->assertMatchesRegularExpression('/^analytics\.marketing\.org\.\d+$/', $cookie);
            $this->assertSame(1, $stored);

            // The organizers' view off: the notice does not say it, so the choice does not carry it.
            Setting::set('realtime_owner_view', '0');
            Cache::flush();
            $browser->driver->manage()->deleteAllCookies();
            $browser->script('localStorage.clear();');

            $browser->visit('/'.$role->subdomain)->waitFor('[data-cookie-consent]:not([hidden])');
            $this->assertFalse($browser->script('return document.querySelector("[data-cookie-consent]").hasAttribute("data-names-organizers");')[0]);
            $browser->assertDontSeeIn('[data-cookie-consent]', $sentence);

            $browser->click('[data-cookie-consent-action="all"]')->pause(400);
            [$cookie, $stored] = $choice($browser);
            $this->assertMatchesRegularExpression('/^analytics\.marketing\.\d+$/', $cookie);
            $this->assertSame(0, $stored);
        });
    }
}
