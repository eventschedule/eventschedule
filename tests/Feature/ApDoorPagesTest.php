<?php

namespace Tests\Feature;

use App\Models\CarpoolOffer;
use App\Models\CarpoolRequest;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages a person uses for themselves and at the door, rebuilt on the page kit in October 2026:
 * /my-carpools, /following and its merge page, /referrals, /checkin, /scan and the plan checkout.
 *
 * Each test is something a person could see: a page with no menu, a search box that never said
 * what to type, "1 venues", three contact columns standing empty, a "Copied" nobody saw. What the
 * pages look like belongs to the screenshots; these hold what they must say and carry.
 *
 * Assertions count matches with substr_count()/preg_match() and never hand the whole page to a
 * pattern assertion: a failure would print 600 KB of HTML.
 */
class ApDoorPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();
    }

    private function page(User $user, string $url): string
    {
        return $this->actingAs($user)->get($url)->assertOk()->getContent();
    }

    /** A venue nobody runs: no address of its own, no owner. */
    private function stubVenue(string $name, array $attrs = []): Role
    {
        $venue = new Role;
        $venue->subdomain = 'stub'.strtolower(Str::random(10));
        $venue->type = 'venue';
        $venue->name = $name;
        $venue->address1 = $name;
        $venue->email = null;

        foreach ($attrs as $key => $value) {
            $venue->{$key} = $value;
        }

        $venue->save();

        return $venue->fresh();
    }

    private function assertOpensLikeThePortal(string $html, string $title): void
    {
        $this->assertSame(1, substr_count($html, 'id="sidebar"'), 'the page has the portal\'s menu');
        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'), 'the page has one title');
        $this->assertSame(1, preg_match('/<h1 class="page-title"[^>]*><bdi>'.preg_quote(e($title), '/').'<\/bdi><\/h1>/', $html), 'the title is '.$title);
    }

    /**
     * /my-carpools is in the signed-in group and is linked from the portal's own menu, and it was
     * drawn on the bare shell: no sidebar, no header, nowhere to go.
     */
    public function test_my_carpools_is_a_page_of_the_portal(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner);

        $html = $this->page($owner, route('my_carpools'));

        $this->assertOpensLikeThePortal($html, __('messages.my_carpools'));
        $this->assertSame(1, substr_count($html, 'id="open-sidebar"'), 'a phone can open the menu');
        $this->assertSame(1, substr_count($html, e(__('messages.carpool_no_offers_yet'))));
        $this->assertSame(1, substr_count($html, e(__('messages.carpool_no_requests_yet'))));
        $this->assertSame(2, substr_count($html, 'class="page-empty is-compact"'), 'each card says it is empty');
        $this->assertSame(0, substr_count($html, 'class="page-table"'));
    }

    public function test_my_carpools_lists_offers_and_requests(): void
    {
        $owner = $this->createOwner();
        $driver = $this->createOwner();
        $role = $this->createRole($owner);
        $mine = $this->createEvent($role, ['name' => 'Harvest Dance']);
        $theirs = $this->createEvent($role, ['name' => 'Winter Market']);

        $offer = CarpoolOffer::create([
            'event_id' => $mine->id, 'user_id' => $owner->id, 'role_id' => $role->id, 'direction' => 'to_event',
            'city' => 'Springfield', 'total_spots' => 3, 'status' => 'active',
        ]);
        CarpoolRequest::create(['carpool_offer_id' => $offer->id, 'user_id' => $driver->id, 'status' => 'pending']);

        $other = CarpoolOffer::create([
            'event_id' => $theirs->id, 'user_id' => $driver->id, 'role_id' => $role->id, 'direction' => 'round_trip',
            'city' => 'Shelbyville', 'total_spots' => 2, 'status' => 'active',
        ]);
        CarpoolRequest::create(['carpool_offer_id' => $other->id, 'user_id' => $owner->id, 'status' => 'declined']);

        $html = $this->page($owner, route('my_carpools'));

        $this->assertSame(2, substr_count($html, 'class="page-table"'), 'one list of offers, one of requests');
        $this->assertSame(0, substr_count($html, 'class="page-empty'));
        $this->assertSame(1, substr_count($html, '<bdi>Harvest Dance</bdi>'));
        $this->assertSame(1, substr_count($html, '<bdi>Winter Market</bdi>'));
        $this->assertSame(1, substr_count($html, e(__('messages.carpool_pending_count', ['count' => 1]))));
        $this->assertSame(1, substr_count($html, '<span class="event-status is-bad">'.e(__('messages.carpool_status_declined')).'</span>'));
        // Each row leads to the event's carpool page.
        $this->assertSame(2, substr_count($html, '/carpool/'.UrlUtils::encodeId($mine->id)) + substr_count($html, '/carpool/'.UrlUtils::encodeId($theirs->id)));
    }

    /**
     * The list drew Email, Phone and Website for everyone, and for most people all three stood
     * empty. A column is there when some row has something to put in it.
     */
    public function test_following_shows_a_contact_column_only_when_a_row_fills_it(): void
    {
        $follower = $this->createOwner();
        $quiet = $this->createRole($this->createOwner(), 'venue', ['name' => 'Quiet Hall']);
        $this->followRole($follower, $quiet);

        $html = $this->page($follower, route('following'));

        $this->assertOpensLikeThePortal($html, __('messages.following'));
        $this->assertSame(1, substr_count($html, 'data-sort="name"'));
        $this->assertSame(0, substr_count($html, 'data-sort="email"'));
        $this->assertSame(0, substr_count($html, 'data-sort="phone"'));
        $this->assertSame(0, substr_count($html, 'data-sort="website"'));
        $this->assertSame(0, substr_count($html, 'class="c-contact"'));

        $open = $this->createRole($this->createOwner(), 'talent', ['name' => 'Open Band', 'email' => 'band@gmail.com', 'show_email' => true, 'website' => 'https://band.example.org']);
        $this->followRole($follower, $open);

        $html = $this->page($follower, route('following'));

        $this->assertSame(1, substr_count($html, 'data-sort="email"'));
        $this->assertSame(1, substr_count($html, 'data-sort="website"'));
        $this->assertSame(0, substr_count($html, 'data-sort="phone"'));
        $this->assertSame(1, substr_count($html, 'href="mailto:band@gmail.com"'));
        // A cell with nothing in it has NOTHING in it: the list drops it from a phone's row by
        // :empty, which a space would defeat.
        $this->assertSame(2, substr_count($html, '<td class="c-contact"></td>'), 'the quiet schedule\'s two cells are empty');
        // The kind of schedule is the name's second line.
        $this->assertSame(1, substr_count($html, '<span class="c-sub">'.e(__('messages.talent')).'</span>'));
    }

    /**
     * A row's menu is one of the layout's shared menus (.popup-toggle[data-popup-target]); it was
     * an Alpine island per row, with inline handlers for everything in it.
     */
    public function test_a_followed_schedules_menu_is_the_shared_popup(): void
    {
        $follower = $this->createOwner();
        $first = $this->createRole($this->createOwner(), 'venue', ['name' => 'First Hall']);
        $second = $this->createRole($this->createOwner(), 'venue', ['name' => 'Second Hall']);
        $this->followRole($follower, $first);
        $this->followRole($follower, $second);

        // The answer to the filter box: the list alone, with no script of its own.
        $list = $this->actingAs($follower)
            ->get(route('following'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->getContent();

        $this->assertSame(0, substr_count($list, '<script'));
        $this->assertSame(0, substr_count($list, 'x-data'));
        $this->assertSame(0, substr_count($list, '@click'));
        $this->assertSame(2, substr_count($list, 'class="popup-toggle page-tool"'));

        foreach ([0, 1] as $row) {
            $this->assertSame(1, substr_count($list, 'class="popup-toggle page-tool" data-popup-target="following-menu-'.$row.'"'));
            $this->assertSame(1, substr_count($list, 'id="following-menu-'.$row.'" class="ap-dropdown pop-up-menu hidden'), 'the menu starts closed');
        }

        // Unfollow asks first, through the layout's own data-confirm.
        $this->assertSame(1, substr_count($list, 'href="'.e(route('role.unfollow', ['subdomain' => $first->subdomain])).'" data-confirm="'.e(__('messages.are_you_sure')).'"'));
        $this->assertSame(4, substr_count($list, 'data-copy-feed="'), 'an iCal and an RSS feed for each');
        $this->assertSame(2, substr_count($list, 'class="row-checkbox'));
        $this->assertSame(1, substr_count($list, 'id="select-all"'));
    }

    public function test_following_says_when_the_filter_matches_nothing(): void
    {
        $follower = $this->createOwner();
        $this->followRole($follower, $this->createRole($this->createOwner(), 'venue', ['name' => 'Quiet Hall']));

        $html = $this->page($follower, route('following', ['filter' => 'zzzz']));

        $this->assertSame(1, substr_count($html, e(__('messages.following_filter_empty'))));
        // Not "No schedules. Start following...": this person follows one.
        $this->assertSame(0, substr_count($html, e(__('messages.start_following_schedules'))));

        $nobody = $this->createOwner();
        $html = $this->page($nobody, route('following'));
        $this->assertSame(1, substr_count($html, e(__('messages.start_following_schedules'))));
        $this->assertSame(0, substr_count($html, 'class="page-table'));
    }

    /**
     * One view, two addresses. The way back names the page it leads to, and a pair of duplicates,
     * which is most groups, no longer reads "1 venues".
     */
    public function test_the_merge_page_names_its_way_back_and_counts_without_a_plural(): void
    {
        $owner = $this->createOwner();
        $first = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $second = $this->stubVenue('Ozen Bar', ['city' => 'Tel Aviv', 'country_code' => 'il']);
        $this->followRole($owner, $first);
        $this->followRole($owner, $second);

        $following = $this->page($owner, route('following'));
        $this->assertSame(1, substr_count($following, 'href="'.e(route('following.merge_venues')).'"'), 'the list offers the merge page');

        $html = $this->page($owner, route('following.merge_venues'));

        $this->assertOpensLikeThePortal($html, __('messages.merge_venues_title'));
        $this->assertSame(1, preg_match('/<a href="'.preg_quote(e(route('following')), '/').'" class="page-back">.*?<bdi>'.preg_quote(e(__('messages.following')), '/').'<\/bdi>/s', $html));
        $this->assertSame(1, substr_count($html, e(__('messages.merge_venues_summary_counts', ['venues' => 1, 'events' => 0]))));
        $this->assertSame(0, substr_count($html, '1 venues'));
        $this->assertSame(2, substr_count($html, e(__('messages.merge_venues_events_label', ['count' => 0]))));
        // The form, and the two answers: the one that goes on is the last.
        $this->assertSame(1, substr_count($html, 'class="merge-group-form"'));
        $this->assertSame(2, substr_count($html, 'name="target_choice_'));
        $this->assertLessThan(strpos($html, 'merge-group-btn'), strpos($html, 'dismiss-group-btn'));

        // The curator's own page: back to the schedule, by name.
        $curator = $this->createRole($owner, 'curator', ['name' => 'City Listings']);
        $html = $this->page($owner, route('role.merge_venues', ['subdomain' => $curator->subdomain]));

        $this->assertSame(1, preg_match('/class="page-back">.*?<bdi>City Listings<\/bdi>/s', $html));
        $this->assertSame(1, substr_count($html, 'class="page-empty"'));
        $this->assertSame(1, substr_count($html, e(__('messages.merge_venues_empty_state'))));
    }

    public function test_referrals_show_four_figures_in_one_strip(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Credited Hall']);
        $friend = $this->createOwner();
        $other = $this->createOwner();

        DB::table('referrals')->insert([
            ['referrer_user_id' => $owner->id, 'referred_user_id' => $friend->id, 'plan_type' => 'enterprise', 'status' => 'qualified', 'credited_role_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['referrer_user_id' => $owner->id, 'referred_user_id' => $other->id, 'plan_type' => 'pro', 'status' => 'credited', 'credited_role_id' => $role->id, 'created_at' => now()->subDay(), 'updated_at' => now()],
        ]);

        $html = $this->page($owner, route('referrals'));

        $this->assertOpensLikeThePortal($html, __('messages.referral_program'));
        $this->assertSame(1, substr_count($html, 'id="referral-dashboard" class="ap-card rounded-xl page-stats is-auto"'));
        $this->assertSame(4, substr_count($html, '<div class="page-stat">'));
        // The ids the user guide's screenshots are taken by.
        foreach (['referral-link', 'referral-dashboard', 'referral-credits', 'referral-how-it-works', 'referral-history', 'referral-url-input', 'copy-referral-link'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'), $id);
        }
        // One list for every width, sorted by the kit's buttons.
        $this->assertSame(1, substr_count($html, 'data-sort="created_at"'));
        $this->assertSame(1, substr_count($html, 'data-sort="status"'));
        $this->assertSame(1, substr_count($html, '<span class="event-status is-on">'.e(__('messages.credited')).'</span>'));
        $this->assertSame(1, substr_count($html, '<bdi>Credited Hall</bdi>'));
        // The credit waiting to be used, in the installation's currency, with the plan's name
        // in the reader's language (it was ucfirst('enterprise')).
        $this->assertSame(1, substr_count($html, 'name="referral_id" value="'));
        $this->assertGreaterThanOrEqual(2, substr_count($html, '>'.e(__('messages.enterprise')).'</span>'));
        $this->assertSame(3, substr_count($html, 'class="referral-step"'));
    }

    /**
     * The door's two pages. The search box was bound to a JSON string whose own quotes closed the
     * attribute, so it never said what can be typed into it.
     */
    public function test_checkin_and_scan_open_with_the_way_back_to_sales(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Door Night']);
        $this->createTicket($event);

        $checkin = $this->page($owner, route('checkin.index'));

        $this->assertOpensLikeThePortal($checkin, __('messages.checkin_dashboard'));
        $this->assertSame(1, preg_match('/<a href="'.preg_quote(e(route('sales')), '/').'" class="page-back">/', $checkin));
        $this->assertSame(1, substr_count($checkin, 'id="checkin-search"'));
        $this->assertSame(1, substr_count($checkin, 'placeholder="'.e(__('messages.checkin_search_placeholder')).'"'));
        $this->assertSame(0, substr_count($checkin, ':placeholder='));
        $this->assertGreaterThanOrEqual(1, substr_count($checkin, 'href="'.e(route('ticket.scan')).'"'), 'the scanner is one press away');
        $this->assertSame(1, substr_count($checkin, 'class="page-stats"'), 'the figures are one strip');
        $this->assertSame(1, substr_count($checkin, 'id="event-selector-dropdown"'));

        $scan = $this->page($owner, route('ticket.scan'));

        $this->assertOpensLikeThePortal($scan, __('messages.scan_ticket'));
        $this->assertSame(1, preg_match('/<a href="'.preg_quote(e(route('sales')), '/').'" class="page-back">/', $scan));
        // The scanner's own element, once, and its buttons dressed by rules a browser can read.
        $this->assertSame(1, substr_count($scan, '<div id="reader"'));
        $this->assertSame(0, substr_count($scan, '@apply'));
        $this->assertSame(1, substr_count($scan, 'v-on:click="startNewScan"'));
        $this->assertSame(1, substr_count($scan, 'v-on:click="admitNextGuest"'));
    }

    /**
     * The plan checkout cannot be opened without a Stripe key (the controller asks Stripe for a
     * SetupIntent first), so the view is rendered here with what the controller passes it.
     */
    public function test_the_plan_checkout_sends_the_choice_it_opened_with(): void
    {
        config(['app.hosted' => true, 'cashier.key' => 'pk_test_fixture']);

        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'venue', ['name' => 'Checkout Hall']);
        $this->actingAs($owner);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        $render = fn (string $tier, bool $enterprise) => view('subscription.show', [
            'role' => $role,
            'intent' => (object) ['client_secret' => 'seti_fixture_secret'],
            'monthlyPrice' => null,
            'yearlyPrice' => null,
            'selectedTier' => $tier,
            'enterpriseConfigured' => $enterprise,
            'checkoutSource' => 'plan',
        ])->render();

        $html = $render('enterprise', true);

        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'));
        $this->assertSame(1, preg_match('/class="page-back">.*?<bdi>Checkout Hall<\/bdi>/s', $html));
        // The two fields the form sends are filled in on the page, so the choice it opened with
        // is what is sent even if the script never ran (they were empty until a script set them).
        $this->assertSame(1, substr_count($html, '<input type="hidden" name="plan" id="selected-plan" value="monthly">'));
        $this->assertSame(1, substr_count($html, '<input type="hidden" name="tier" id="selected-tier" value="enterprise">'));
        $this->assertSame(1, substr_count($html, '<input type="hidden" name="source" value="plan">'));
        $this->assertSame(1, preg_match('/name="tier_radio" value="enterprise" class="sr-only"\s+checked/', $html));
        $this->assertSame(0, preg_match('/name="tier_radio" value="pro" class="sr-only"\s+checked/', $html));
        $this->assertSame(2, substr_count($html, '<input type="radio" name="plan_radio"'));
        $this->assertSame(2, substr_count($html, '<input type="radio" name="tier_radio"'));

        foreach (['payment-form', 'payment-method', 'card-holder-name', 'card-element', 'card-errors', 'submit-button', 'button-text', 'button-spinner'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'), $id);
        }

        // Its own prices, through plan_price(), for both plans and both terms.
        $this->assertGreaterThanOrEqual(1, substr_count($html, e(plan_price(\App\Utils\PlatformPricing::amount('enterprise', 'yearly')))));
        // Cancel first, the button that goes on last.
        $this->assertLessThan(strpos($html, 'id="submit-button"'), strpos($html, 'js-cancel-btn'));

        // Without an Enterprise price there is one plan to choose, and it is chosen.
        $html = $render('pro', false);
        $this->assertSame(1, substr_count($html, '<input type="radio" name="tier_radio"'));
        $this->assertSame(1, substr_count($html, '<input type="hidden" name="tier" id="selected-tier" value="pro">'));

        // Plain script on real radios: this page was the last here driven by Alpine.
        $source = file_get_contents(resource_path('views/subscription/show.blade.php'));
        foreach (['x-data=', 'x-show=', 'x-text=', 'x-cloak', '@click', 'indigo-'] as $gone) {
            $this->assertSame(0, substr_count($source, $gone), $gone);
        }
    }
}
