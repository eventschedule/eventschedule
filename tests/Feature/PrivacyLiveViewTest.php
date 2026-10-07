<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Utils\RealtimeTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What the public pages say about who sees the live view follows the switch that decides it.
 *
 * For a week the privacy policy and the Analytics feature page said that only administrators
 * see Realtime, and the cookie notice said "we". Then schedule owners were given the part about
 * their own pages (the Realtime tab of /analytics, App\Services\ScheduleRealtime). A promise like that is easy to
 * leave behind in one of three places, and nothing else would notice: every page would still
 * render.
 *
 * The other half is the notice. A visitor may appear on an organizer's page only if the notice
 * they answered said that organizers see visits, so the sentence sits on the notice's first
 * line, beside "Allow all", and the notice is marked as carrying it. cookie-consent.js records
 * that mark on the choice and RealtimeTracker::consentCoversOrganizers() reads it back
 * (RealtimeBeaconTest holds that end). Sentence, mark and reader must stay one thing.
 */
class PrivacyLiveViewTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ORGANIZER_SEES = "The organizer of a schedule can see the part about that schedule's own pages";

    private const ADMINS_ONLY = 'only our administrators can see, never schedule owners';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Setting::set('realtime_enabled', '1');
        Setting::set('realtime_owner_view', '1');
    }

    private function page(string $path): string
    {
        return html_entity_decode($this->get($path)->assertOk()->getContent(), ENT_QUOTES);
    }

    /**
     * Mutation: print either sentence unconditionally in marketing/privacy.blade.php.
     */
    public function test_the_privacy_policy_says_what_an_organizer_sees_exactly_where_they_see_it(): void
    {
        $html = $this->page('/privacy');
        $this->assertStringContainsString(self::ORGANIZER_SEES, $html);
        $this->assertStringContainsString('A schedule owner can also see live traffic to their own schedule', $html, 'clause 06');
        $this->assertStringContainsString("Show a schedule's organizer your visit to their own pages as a row with no name", $html, 'the legal bases');
        $this->assertStringNotContainsString(self::ADMINS_ONLY, $html);
        // It says what they never see, and that an older choice does not list anyone.
        $this->assertStringContainsString('In that view an organizer never sees your name, email address or account', $html);
        $this->assertStringContainsString('before our cookie notice began to mention organizers', $html);
        // The tab now sets a day of the organizer's own sales beside the unnamed visits, so the
        // policy says what that makes possible, in both clauses, and promises deletion only of
        // what the live view itself holds.
        $this->assertSame(2, substr_count($html, 'may be able to tell which unnamed visit was yours'), 'clauses 06 and 12');
        $this->assertStringContainsString('what it holds about your visit is deleted about an hour after your last activity', $html);
        $this->assertStringNotContainsString('and it is deleted about an hour after your last activity', $html);

        Setting::set('realtime_owner_view', '0');
        $html = $this->page('/privacy');
        $this->assertStringContainsString(self::ADMINS_ONLY, $html);
        $this->assertStringNotContainsString(self::ORGANIZER_SEES, $html);
        $this->assertStringNotContainsString('A schedule owner can also see live traffic', $html);
        $this->assertStringNotContainsString('which unnamed visit was yours', $html, 'Nothing to explain where organizers see no visits.');
    }

    /** Mutation: leave "never shown to schedule owners" in the feature page's answer. */
    public function test_the_analytics_page_does_not_promise_what_the_install_does_not_give(): void
    {
        $html = $this->page('/features/analytics');
        $this->assertStringNotContainsString('never shown to schedule owners', $html);
        $this->assertStringContainsString('Realtime shows you the live traffic to your own pages', $html);
        $this->assertStringContainsString('as a row with no name', $html);
        // "It is deleted about an hour later" was about the whole tab, and the tab now also lists
        // a day of the organizer's own sales, which nothing deletes.
        $this->assertStringContainsString('What it holds about a visit is deleted about an hour later', $html);
        $this->assertStringContainsString('without names', $html);

        Setting::set('realtime_owner_view', '0');
        $html = $this->page('/features/analytics');
        $this->assertStringContainsString('never shown to schedule owners', $html);
        $this->assertStringNotContainsString('Realtime shows you the live traffic', $html);
    }

    /**
     * The sentence a visitor's choice has to have been made under: on the notice's first line,
     * where everyone who presses "Allow all" has it in front of them, and never tucked into the
     * Choose panel, which that button does not open. The mark that says "this notice carried it"
     * is there exactly when the sentence is: a mark without the sentence would list people who
     * were never told. Mutation: print either whatever the switch says, or move the sentence
     * back under the panel.
     */
    public function test_the_cookie_notice_names_organizers_on_its_first_line_and_is_marked_for_it(): void
    {
        $sentence = __('messages.cookie_consent_analytics_organizers');
        $marked = '/<div data-cookie-consent\s[^>]*data-names-organizers[\s>]/';

        $html = $this->page('/privacy');
        $notice = substr($html, (int) strpos($html, '<div data-cookie-consent'));
        $panel = strpos($notice, 'data-cookie-consent-choices hidden');

        $this->assertNotFalse($panel, 'the Choose panel is still there, closed');
        $this->assertMatchesRegularExpression($marked, $html);
        $this->assertSame(1, substr_count($notice, $sentence), 'said once');
        $this->assertLessThan($panel, strpos($notice, $sentence), 'before the panel, so it shows without opening anything');
        $this->assertLessThan(strpos($notice, 'data-cookie-consent-action="all"'), strpos($notice, $sentence));

        Setting::set('realtime_owner_view', '0');
        $html = $this->page('/privacy');
        $this->assertStringNotContainsString($sentence, $html);
        $this->assertDoesNotMatchRegularExpression($marked, $html);
        $this->assertStringContainsString(__('messages.cookie_consent_analytics_help_no_ga'), $html, 'the Analytics line itself is still there');
    }

    /**
     * Three files have to agree on two words, and no test runs the browser's half: the mark the
     * notice carries is the one cookie-consent.js reads, and the token it writes into the cookie
     * is the one consent-state.js keeps out of the categories and the server looks for.
     * Mutation: rename either on one side only.
     */
    public function test_the_notice_the_script_and_the_server_agree_on_the_mark(): void
    {
        $banner = (string) file_get_contents(resource_path('views/partials/cookie-banner.blade.php'));
        $writer = (string) file_get_contents(resource_path('js/cookie-consent.js'));
        $reader = (string) file_get_contents(resource_path('js/consent-state.js'));

        $this->assertStringContainsString('@if ($consentNamesOrganizers) data-names-organizers @endif', $banner);
        $this->assertStringContainsString("hasAttribute('data-names-organizers')", $writer);
        $this->assertStringContainsString("? '.org' : ''", $writer);
        $this->assertStringContainsString("parts.indexOf('org') !== -1", $reader);

        Setting::set('realtime_owner_view', '1');
        $with = \Illuminate\Http\Request::create('/api/realtime', 'POST', [], ['cookie_consent' => 'analytics.org.'.now()->timestamp]);
        $without = \Illuminate\Http\Request::create('/api/realtime', 'POST', [], ['cookie_consent' => 'analytics.'.now()->timestamp]);

        $this->assertTrue(RealtimeTracker::consentCoversOrganizers($with));
        $this->assertFalse(RealtimeTracker::consentCoversOrganizers($without));
        // The token leaves the categories as they were, for the server's parser too.
        $this->assertTrue(consent_granted('analytics', $with));
        $this->assertFalse(consent_granted('marketing', $with));
    }

    /**
     * The two switches are two: a client that posts the first alone must not take the view away
     * from every organizer, and the second goes off by itself. Mutation: read the second switch
     * from a save that did not carry it.
     */
    public function test_a_save_changes_the_owner_view_only_when_it_carried_that_switch(): void
    {
        $admin = $this->createOwner(true);
        $save = fn (array $fields) => $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)->post(route('admin.settings.update_realtime'), $fields)->assertRedirect();

        Setting::set('realtime_owner_view', '0');
        $save(['realtime_enabled' => '1', 'realtime_owner_view_submitted' => '1', 'realtime_owner_view' => '1']);
        $this->assertTrue(RealtimeTracker::ownerViewEnabled());

        // A client that sends the first switch alone changes nothing about the second.
        $save(['realtime_enabled' => '1']);
        $this->assertTrue(RealtimeTracker::ownerViewEnabled());

        // Realtime itself off: organizers have no view, whatever their own switch says.
        $save(['realtime_enabled' => '0', 'realtime_owner_view_submitted' => '1', 'realtime_owner_view' => '1']);
        $this->assertFalse(RealtimeTracker::ownerViewEnabled());
        $this->assertTrue(RealtimeTracker::ownerViewSetting(), 'the stored choice is kept for when it comes back');

        // Off by its own switch: Realtime stays, the owner view goes.
        $save(['realtime_enabled' => '1', 'realtime_owner_view_submitted' => '1']);
        $this->assertTrue(RealtimeTracker::enabled());
        $this->assertFalse(RealtimeTracker::ownerViewEnabled());
    }

    /**
     * The page hands its beacon nothing about who may be listed: that is read on the server.
     * Mutation: print a consent date or an "o" bit into partials/realtime-beacon again.
     */
    public function test_the_page_does_not_decide_who_an_organizer_may_see(): void
    {
        $html = $this->get('/privacy')->assertOk()->getContent();

        $this->assertStringContainsString("send({ t: 'pv', m: mode, r: referrerHost", $html);
        $this->assertStringNotContainsString('ownerSince', $html);
        $this->assertStringNotContainsString('ownerOk', $html);
    }
}
