<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The small print at the foot of /features ("And the small print, which is mostly good news") is
 * the one list of everything the app does that has no banner of its own. Until 2026-10 fifteen of
 * its twenty-eight rows were plain text and seven linked into the user guide, so a visitor who
 * wanted to know what "Venue logo wall" meant had nowhere to go, or was dropped into a manual.
 *
 * Every row links to a feature page now: a page of its own where the feature has one, otherwise
 * the section of a feature page that describes it. A section is found by an id, and an id is the
 * quiet kind of link: rename it on the page it lives on and the row still answers 200, at the top
 * of the wrong page. So this renders the list and follows every row.
 *
 * The other way a list like this fails is by being short. Its plan buttons answer "what is on
 * Enterprise?", and until 2026-10 the answer was two rows: reserved seating, feeds, the AI
 * generators and the scheduled graphic email were on neither the list nor a banner above it, and
 * the Enterprise features that do have a banner were not named at all. So every feature in the
 * Free, Pro and Enterprise tables of docs/FEATURES.md is accounted for here by name (ACCOUNTED): it
 * is a row carrying that plan's badge, or the plan's own line under the list links the page that
 * covers it, or it is left out for a reason written beside it. A feature added to that file fails
 * the build until somebody decides which.
 */
class MarketingFeaturesSmallPrintTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Feature pages that do not sit under /features/, with the reason each counts as one. An
     * integration has a page of its own at the root (/stripe, /paypal, /google-calendar).
     */
    private const FEATURE_PAGES_ELSEWHERE = [
        '/paypal' => 'the PayPal integration page',
        '/invoiceninja' => 'the Invoice Ninja integration page',
    ];

    private const ROW = 'row';

    private const ABOVE = 'above';

    private const LEFT_OUT = 'left out';

    /**
     * Where each feature of docs/FEATURES.md stands on /features, keyed by its name in that file.
     *
     *   ROW       the name of its row in the small print, which must carry the plan's badge
     *   ABOVE     the feature page of the banner or card that covers it, which that plan's line
     *             under the list must link
     *   LEFT_OUT  why the page does not list it
     */
    private const ACCOUNTED = [
        // ---- Free
        'Unlimited events and schedules' => [self::LEFT_OUT, 'the product itself: the hero and the first FAQ answer say it'],
        'Event visibility (Public & Draft)' => [self::LEFT_OUT, 'the baseline of the visibility banner, whose Enterprise half the plan line names'],
        'Mobile-optimized, professional design' => [self::LEFT_OUT, 'the product itself, not a feature to pick a plan for'],
        'Custom schedule URLs' => [self::LEFT_OUT, 'the claim box at the foot of the page is this feature'],
        'Team collaboration (single member)' => [self::LEFT_OUT, 'the absence of the Enterprise feature, which the plan line names'],
        'Schedule ownership transfer' => [self::ROW, 'Schedule transfer'],
        'Claim a page created for you' => [self::ROW, 'Pages for the acts you list'],
        'Venue location maps' => [self::LEFT_OUT, 'part of every event page, described on /event-landing-page'],
        'Google Calendar sync' => [self::ABOVE, '/features/calendar-sync'],
        'Outlook Calendar sync' => [self::ABOVE, '/features/calendar-sync'],
        'CalDAV sync' => [self::ABOVE, '/features/calendar-sync'],
        'Fan videos, photos & comments on events' => [self::ABOVE, '/features/fan-videos'],
        'Built-in analytics' => [self::ABOVE, '/features/analytics'],
        'Configurable dashboard' => [self::LEFT_OUT, 'a preference inside the admin portal'],
        'Sub-schedules' => [self::ABOVE, '/features/sub-schedules'],
        'Schedule page search and filters' => [self::LEFT_OUT, 'how the calendar itself behaves; the sub-schedules banner names its visitor filter'],
        'Multi-event cart' => [self::ROW, 'Multi-event cart'],
        'Curator event sources' => [self::ROW, 'Event sources for curators'],
        'Online events' => [self::ABOVE, '/features/online-events'],
        'Event requests' => [self::ABOVE, '/features/booking-requests'],
        'Recurring events' => [self::ABOVE, '/features/recurring-events'],
        'Shared notification address' => [self::LEFT_OUT, 'a notification setting, not a feature a visitor chooses a plan for'],
        'Newsletter management' => [self::ABOVE, '/features/newsletters'],
        'Email subscribers (audience capture)' => [self::ROW, 'Email sign-up'],
        'Event interest capture ("tell me when tickets go on sale")' => [self::ROW, 'Interest list'],
        'Automatic new-event announcements' => [self::ABOVE, '/features/newsletters'],
        'Embed calendar on website' => [self::ABOVE, '/features/embed-calendar'],
        'Free event registration / RSVP' => [self::ROW, 'Free registration and RSVP'],
        'Free ticket rows ($0) and free registration' => [self::ROW, 'Free registration and RSVP'],
        'Ticket sales windows and volume discounts' => [self::ROW, 'Sales windows and group rates'],
        'Ticket-type custom fields' => [self::LEFT_OUT, 'the free corner of custom fields, which its own page states; the banner is the Pro feature'],
        'Appointment booking (1 free appointment type)' => [self::ABOVE, '/features/appointments'],
        'QR code scanning at the door' => [self::ABOVE, '/features/check-in'],
        'Add to Google Wallet' => [self::ROW, 'Add to Google Wallet'],
        "Subscribe to a schedule's calendar" => [self::ROW, 'Live calendar and RSS feeds'],
        'iCal download' => [self::ROW, 'Live calendar and RSS feeds'],
        'Fan photos on events (25 per schedule)' => [self::ABOVE, '/features/fan-videos'],
        'Event cloning' => [self::ROW, 'Event cloning'],
        'Generate event graphics' => [self::ABOVE, '/features/event-graphics'],
        'Venue logo wall header' => [self::ROW, 'Venue logo wall'],
        'Event animations' => [self::LEFT_OUT, 'a display setting of the schedule page, like its header style and its layout'],
        'Backup & restore' => [self::LEFT_OUT, 'one of the twelve cards above; it has no feature page for a plan line to link'],
        '10 newsletter emails per month' => [self::ABOVE, '/features/newsletters'],
        'AI event parsing' => [self::ABOVE, '/features/ai'],
        'Import from a link' => [self::ROW, 'Import from a link'],
        'Import from Google Calendar' => [self::LEFT_OUT, 'one more source on the import page; the calendar sync banner covers the standing connection'],

        // ---- Pro
        'Remove Event Schedule branding' => [self::ABOVE, '/features/white-label'],
        'Sell paid tickets' => [self::ABOVE, '/features/ticketing'],
        'Payment gateways (Stripe, PayPal, Payfast, Invoice Ninja, payment link, cash)' => [self::ABOVE, '/features/ticketing'],
        'Refunds, full or partial' => [self::ROW, 'Refunds, full or partial'],
        'Invoice Ninja integration' => [self::ABOVE, '/invoiceninja'],
        'Passes & subscriptions' => [self::ABOVE, '/features/passes'],
        'Individual tickets' => [self::ROW, 'Individual tickets'],
        'Unlimited appointment types, paid bookings, advanced scheduling' => [self::ABOVE, '/features/appointments'],
        'REST API access' => [self::ABOVE, '/features/integrations'],
        'Webhooks' => [self::ABOVE, '/features/integrations'],
        'Event boosting with ads' => [self::ABOVE, '/features/boost'],
        'Custom CSS styling' => [self::ABOVE, '/features/custom-css'],
        'Custom fields' => [self::ABOVE, '/features/custom-fields'],
        'Event polls' => [self::ABOVE, '/features/polls'],
        'Event templates' => [self::ROW, 'Event templates'],
        'Check-in dashboard' => [self::ABOVE, '/features/check-in'],
        'Ticket waitlist' => [self::ROW, 'Ticket waitlist'],
        'Sale notification emails' => [self::ROW, 'Sale notification emails'],
        'Push notifications' => [self::ROW, 'Push notifications'],
        'Sales CSV export' => [self::ROW, 'Sales CSV export'],
        'Post-event feedback' => [self::ABOVE, '/features/feedback'],
        'Carpool matching' => [self::ABOVE, '/features/carpool'],
        'Embed ticket widget' => [self::ABOVE, '/features/embed-tickets'],
        'Promo/discount codes' => [self::ROW, 'Promo codes'],
        'Gift cards' => [self::ABOVE, '/features/gift-cards'],
        'Installment payments' => [self::ROW, 'Installment payments'],
        'Eventbrite import' => [self::ROW, 'Eventbrite import'],
        'Bulk attendee import' => [self::ROW, 'Bulk attendee import'],
        'Ticket add-ons' => [self::ROW, 'Ticket add-ons'],
        '100 newsletter emails per month' => [self::ABOVE, '/features/newsletters'],
        'Unlimited fan photos + bulk download' => [self::ABOVE, '/features/fan-videos'],
        'Photo gallery (events + schedules)' => [self::ROW, 'Photo gallery'],
        'Sponsor/partner logos' => [self::ROW, 'Sponsor and partner logos'],
        'Guest portal banner' => [self::ROW, 'Announcement banner'],
        'Custom guest favicon' => [self::LEFT_OUT, 'part of white label, which the Pro line names'],

        // ---- Enterprise
        'Agenda scanning' => [self::ROW, 'Agenda scanning'],
        'AI flyer generation' => [self::ROW, 'AI flyers and styles'],
        'AI style generation' => [self::ROW, 'AI flyers and styles'],
        'AI schedule details generation' => [self::ROW, 'AI-written descriptions'],
        'AI event details generation' => [self::ROW, 'AI-written descriptions'],
        'Save parsed event parts' => [self::ROW, 'Agenda scanning'],
        'AI text processing on graphics' => [self::LEFT_OUT, 'a field of the graphic page, described beside the scheduled email it rides in'],
        'Email scheduling (graphic emails)' => [self::ROW, 'Scheduled graphic emails'],
        'Allocated (reserved) seating' => [self::ROW, 'Reserved seating'],
        'Custom domains' => [self::ABOVE, '/features/custom-domain'],
        'Internal & unlisted events' => [self::ABOVE, '/features/private-events'],
        'Multiple team members' => [self::ABOVE, '/features/team-scheduling'],
        'Availability management' => [self::ABOVE, '/features/availability'],
        '1,000 newsletter emails per month' => [self::ABOVE, '/features/newsletters'],
        'WhatsApp event creation' => [self::ROW, 'WhatsApp event creation'],
        'Feeds' => [self::ROW, 'Feeds from other sites'],
        'Priority support' => [self::LEFT_OUT, 'a service commitment, not something the app does; /pricing lists it and no feature page describes it'],
    ];

    private function page(): \DOMXPath
    {
        $html = $this->get('/features')->assertOk()->getContent();

        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

        return new \DOMXPath($dom);
    }

    /** The rows of the list as [name, href, plan], read from the rendered page. */
    private function rows(): array
    {
        $xpath = $this->page();

        $rows = [];

        foreach ($xpath->query('//*[@id="also-list"]/div') as $row) {
            $term = $xpath->query('./dt', $row)->item(0);
            $this->assertNotNull($term, 'a row of the small print has no name');

            $link = $xpath->query('./a', $term)->item(0);
            $name = trim(preg_replace('/\s+/u', ' ', $xpath->query('./*[1]', $term)->item(0)?->textContent ?? ''));

            $rows[] = [$name, $link?->getAttribute('href'), $row->getAttribute('data-also-row')];
        }

        return $rows;
    }

    /** The plan lines under the list, as plan => [the paths that line links]. */
    private function planLines(): array
    {
        $xpath = $this->page();
        $lines = [];

        foreach ($xpath->query('//*[@id="also-above"]/p[@data-also-above]') as $line) {
            $paths = [];

            foreach ($xpath->query('.//a', $line) as $link) {
                $paths[] = parse_url($link->getAttribute('href'), PHP_URL_PATH) ?: '/';
            }

            $lines[$line->getAttribute('data-also-above')] = $paths;
        }

        return $lines;
    }

    /** The features of docs/FEATURES.md as name => plan, from its Free, Pro and Enterprise tables. */
    private function planFeatures(): array
    {
        $features = [];
        $plan = null;

        foreach (file(base_path('docs/FEATURES.md'), FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^## (Free|Pro|Enterprise) Features\b/', $line, $heading)) {
                $plan = $heading[1];

                continue;
            }

            if (str_starts_with($line, '## ')) {
                $plan = null;
            }

            if ($plan === null || ! str_starts_with($line, '| ')) {
                continue;
            }

            $name = trim(explode('|', $line)[1] ?? '');

            // The header row, the rule under it, and a row struck through because it moved plan.
            if ($name === '' || $name === 'Feature' || preg_match('/^[-: ]+$/', $name) || str_starts_with($name, '~~')) {
                continue;
            }

            $features[$name] = $plan;
        }

        return $features;
    }

    public function test_every_row_links_to_a_feature_page_and_never_to_the_guide(): void
    {
        $rows = $this->rows();

        $this->assertNotEmpty($rows, 'the small print of /features was not found');
        $this->assertSame(0, count($rows) % 4, 'the list no longer ends on a full row of four');

        foreach ($rows as [$name, $href]) {
            $this->assertNotEmpty($href, "\"$name\" has no link: every row of the small print names a feature page");

            $path = parse_url($href, PHP_URL_PATH) ?: '/';

            $this->assertFalse(
                $path === '/docs' || str_starts_with($path, '/docs/'),
                "\"$name\" links into the user guide ($path); it must name a feature page"
            );
            $this->assertTrue(
                str_starts_with($path, '/features/') || array_key_exists($path, self::FEATURE_PAGES_ELSEWHERE),
                "\"$name\" links to $path, which is not a feature page"
            );
        }
    }

    public function test_every_row_lands_on_a_page_and_on_the_section_it_names(): void
    {
        $pages = [];

        foreach ($this->rows() as [$name, $href]) {
            $path = parse_url((string) $href, PHP_URL_PATH) ?: '/';
            $fragment = parse_url((string) $href, PHP_URL_FRAGMENT);

            $pages[$path] ??= $this->get($path)->assertOk()->getContent();

            if ($fragment === null || $fragment === '') {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/\sid="'.preg_quote($fragment, '/').'"/',
                $pages[$path],
                "\"$name\" links to $path#$fragment, and that page has no element with that id"
            );
        }

        $this->assertNotEmpty($pages);
    }

    public function test_every_plan_feature_is_a_row_or_named_above_or_left_out_for_a_reason(): void
    {
        $features = $this->planFeatures();

        $this->assertGreaterThan(80, count($features), 'docs/FEATURES.md was not read: its plan tables were not found');

        $unplaced = array_diff(array_keys($features), array_keys(self::ACCOUNTED));
        $this->assertSame([], array_values($unplaced), 'docs/FEATURES.md has a feature /features does not account for. Give it a row in the small print, name it in its plan\'s line, or record why it is left out');

        $stale = array_diff(array_keys(self::ACCOUNTED), array_keys($features));
        $this->assertSame([], array_values($stale), 'ACCOUNTED names a feature docs/FEATURES.md no longer has in a plan table');

        $rowPlans = [];
        foreach ($this->rows() as [$name, , $plan]) {
            $rowPlans[$name] = $plan;
        }

        $lines = $this->planLines();
        $this->assertSame(['Free', 'Pro', 'Enterprise'], array_keys($lines), 'each plan has one line under the small print');

        foreach (self::ACCOUNTED as $feature => [$kind, $where]) {
            $plan = $features[$feature];

            if ($kind === self::ROW) {
                $this->assertArrayHasKey($where, $rowPlans, "\"$feature\" is accounted for by a row called \"$where\", and the small print has no such row");
                $this->assertSame($plan, $rowPlans[$where], "\"$where\" carries the {$rowPlans[$where]} badge, and docs/FEATURES.md lists \"$feature\" under $plan");
            } elseif ($kind === self::ABOVE) {
                $this->assertContains($where, $lines[$plan], "\"$feature\" is $plan and is covered by $where, which the $plan line under the small print does not link");
            } else {
                $this->assertSame(self::LEFT_OUT, $kind);
                $this->assertNotSame('', trim($where), "\"$feature\" is left out with no reason given");
            }
        }
    }

    /** No row is an orphan: each one is some plan feature's row, or is listed here with its source. */
    public function test_every_row_stands_for_a_feature(): void
    {
        // Rows for things the app does that docs/FEATURES.md keeps outside its three plan tables.
        $elsewhere = [
            'PayPal checkout' => 'one of the payment gateways, with a page of its own',
            'Short links' => 'schedule links, counted on the analytics page',
            'The whole lineup' => 'the Participants tab of every event',
            'Audit log' => 'on every plan, described on the team scheduling page',
            'Nearby accommodation map' => 'the Accommodation Affiliate table, operator-enabled and free',
        ];

        $accountedRows = [];
        foreach (self::ACCOUNTED as [$kind, $where]) {
            if ($kind === self::ROW) {
                $accountedRows[$where] = true;
            }
        }

        foreach ($this->rows() as [$name]) {
            $this->assertTrue(
                isset($accountedRows[$name]) || isset($elsewhere[$name]),
                "\"$name\" is a row that stands for no feature of docs/FEATURES.md; map a feature to it, or list it with where it comes from"
            );
        }
    }

    public function test_each_plan_line_links_feature_pages_that_answer(): void
    {
        foreach ($this->planLines() as $plan => $paths) {
            $this->assertNotEmpty($paths, "the $plan line under the small print links nothing");

            foreach (array_unique($paths) as $path) {
                $this->assertTrue(
                    str_starts_with($path, '/features/') || array_key_exists($path, self::FEATURE_PAGES_ELSEWHERE),
                    "the $plan line links $path, which is not a feature page"
                );
                $this->get($path)->assertOk();
            }
        }
    }
}
