<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\RoleSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages of one schedule in the admin portal: role/show-admin and its ten tabs.
 *
 * Each test here is a defect a person could see, found by opening the pages (October 2026):
 * a tab the strip had run past the edge of the page, a month whose dates stood under the wrong
 * weekday on a phone, a request that never said which event it was for, an empty card on the Plan
 * page. What the pages look like belongs to the screenshots; these hold what they must say.
 *
 * Assertions count matches with preg_match()/substr_count() and never hand the whole page to a
 * pattern assertion: a failure would print 600 KB of HTML.
 */
class ScheduleAdminPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Plan tab and the plan gates exist only on a hosted install.
        config(['app.hosted' => true]);
    }

    private function page(User $user, Role $role, string $tab): string
    {
        return $this->actingAs($user)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => $tab]))
            ->assertOk()
            ->getContent();
    }

    /** The links of the strip, in order: [tab => whether it is the current one]. */
    private function stripTabs(string $html, Role $role): array
    {
        $this->assertSame(1, preg_match('/<nav class="ap-tabs"[^>]*>(.*?)<\/nav>/s', $html, $nav), 'the page has one strip of tabs');
        preg_match_all('/<a href="[^"]*\/'.preg_quote($role->subdomain, '/').'\/([a-z]+)[^"]*"\s+class="ap-tab"([^>]*)>/', $nav[1], $links, PREG_SET_ORDER);

        $tabs = [];
        foreach ($links as $link) {
            $tabs[$link[1]] = str_contains($link[2], 'aria-current="page"');
        }

        return $tabs;
    }

    /** The options of the phone's dropdown: [tab => whether it is selected]. */
    private function dropdownTabs(string $html, Role $role): array
    {
        $this->assertSame(1, preg_match('/<select id="admin-tab-select"[^>]*>(.*?)<\/select>/s', $html, $select), 'a phone has the dropdown');
        preg_match_all('/<option value="[^"]*\/'.preg_quote($role->subdomain, '/').'\/([a-z]+)[^"]*"([^>]*)>/', $select[1], $options, PREG_SET_ORDER);

        $tabs = [];
        foreach ($options as $option) {
            $tabs[$option[1]] = str_contains($option[2], 'selected');
        }

        return $tabs;
    }

    public function test_every_tab_a_schedule_has_is_in_the_strip_and_in_the_dropdown(): void
    {
        $owner = $this->createOwner();

        // Each kind of schedule has tabs the others do not: an act has Availability, a venue has
        // Seating plans, a curator has Videos. The strip and the dropdown are drawn from one list,
        // so a tab cannot be in one and missing from the other.
        $expected = [
            'talent' => ['schedule', 'templates', 'availability', 'appointments', 'followers', 'team', 'plan'],
            'venue' => ['schedule', 'templates', 'appointments', 'seating', 'followers', 'team', 'plan'],
            'curator' => ['schedule', 'templates', 'videos', 'appointments', 'followers', 'team', 'plan'],
        ];

        foreach ($expected as $type => $tabs) {
            $role = $this->createRole($owner, $type);
            $html = $this->page($owner, $role, 'team');

            $strip = $this->stripTabs($html, $role);
            $this->assertSame($tabs, array_keys($strip), "the strip of a {$type} schedule");
            $this->assertSame(['team'], array_keys(array_filter($strip)), 'exactly the tab you are on is marked current');

            $dropdown = $this->dropdownTabs($html, $role);
            $this->assertSame($tabs, array_keys($dropdown), "the dropdown of a {$type} schedule");
            $this->assertSame(['team'], array_keys(array_filter($dropdown)), 'and the dropdown opens on it');
        }
    }

    public function test_the_last_tab_is_marked_current_on_its_own_page(): void
    {
        // The tab this was written for: at 1,280 pixels "Plan" was past the edge of the page, with
        // the scrollbar hidden, while you stood on it.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $html = $this->page($owner, $role, 'plan');

        $this->assertSame(['plan'], array_keys(array_filter($this->stripTabs($html, $role))));
        $this->assertSame(0, substr_count($html, 'ap-tab-container'), 'the strip that was cut at the edge is gone');
        $this->assertSame(0, substr_count($html, 'onTabChange'), 'and the dropdown no longer runs on jQuery');
    }

    public function test_a_tab_counts_what_waits_and_says_which_counts_want_an_answer(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Homer and the Sharps']);
        foreach (['Barbershop Night', 'Sharps Reunion'] as $name) {
            $event = $this->createEvent($act, ['name' => $name, 'creator_role_id' => $act->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => null]);
        }
        $this->followRole($this->createOwner(), $venue);

        $html = $this->page($owner, $venue, 'team');

        // Requests want an answer, so their count is the loud one; a count of followers is not.
        $this->assertSame(1, preg_match('/'.preg_quote(__('messages.requests'), '/').'\s*<span class="ap-tab-count\s+is-waiting\s*">2<\/span>/', $html), 'two requests, waiting');
        $this->assertSame(1, preg_match('/'.preg_quote(__('messages.followers'), '/').'\s*<span class="ap-tab-count\s*">1<\/span>/', $html), 'one follower, quiet');
    }

    public function test_the_address_guests_use_is_under_the_name(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $html = $this->page($owner, $role, 'schedule');

        // It was nowhere on these pages: only a button that opened it.
        $this->assertSame(1, substr_count($html, 'id="copy-schedule-link-btn"'));
        $this->assertSame(1, preg_match('/data-copy-text="([^"]+)"/', $html, $copy));
        $this->assertSame($role->getGuestUrl(), html_entity_decode($copy[1]), 'Copy copies the address the schedule answers on');
        $this->assertSame(1, substr_count($html, 'data-setup-share="link"'), 'and copying it is the setup guide\'s share step');
    }

    public function test_a_custom_domain_is_offered_only_once_it_answers(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'custom_domain' => 'https://lisa.example.org',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'pending',
        ]);

        // A direct-mode domain that is still pending does not route to the schedule yet. The
        // address under the name is the one people copy and hand out.
        $html = $this->page($owner, $role, 'schedule');
        $this->assertSame(1, preg_match('/data-copy-text="([^"]+)"/', $html, $copy));
        $this->assertSame(route('role.view_guest', ['subdomain' => $role->subdomain]), html_entity_decode($copy[1]), 'a pending domain is not handed out');

        $role->custom_domain_status = 'active';
        $role->save();

        $html = $this->page($owner, $role->fresh(), 'schedule');
        $this->assertSame(1, preg_match('/data-copy-text="([^"]+)"/', $html, $copy));
        $this->assertSame('https://lisa.example.org', html_entity_decode($copy[1]), 'an active one is');

        // An address with no path is all host, and the kit hides the host on a phone: the whole
        // address has to be in the part a phone keeps, or it shows Copy beside nothing.
        $this->assertSame(1, preg_match('/<span class="event-url-text" dir="ltr">(.*?)<\/span><\/span>/s', $html, $strip));
        $this->assertSame(0, substr_count($strip[1], 'event-url-host'));
        $this->assertSame(1, preg_match('/<span class="event-url-path">lisa\.example\.org$/', $strip[1]));
    }

    public function test_a_request_says_what_is_asked_for_and_who_is_asking(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Homer and the Sharps']);
        $event = $this->createEvent($act, ['name' => 'Barbershop Night', 'creator_role_id' => $act->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);

        $html = $this->page($owner, $venue, 'requests');

        // A venue's card named the act twice over and never the event.
        $this->assertSame(1, preg_match('/<li class="ap-card rounded-xl request-card">(.*?)<\/li>/s', $html, $card), 'one card');
        $this->assertSame(1, preg_match('/<h3 class="request-title"[^>]*><bdi>Barbershop Night<\/bdi><\/h3>/', $card[1]), 'the card is headed by the event');
        $this->assertSame(1, substr_count($card[1], 'Homer and the Sharps'), 'and says once who is asking');

        // One request: nothing to accept "all" of.
        $this->assertSame(0, substr_count($html, route('event.accept_all', ['subdomain' => $venue->subdomain])), 'no Accept all beside a single request');
        $this->assertSame(1, substr_count($card[1], 'test-accept-event'));
    }

    public function test_a_request_describes_who_is_asking_in_plain_words(): void
    {
        // A schedule's description is stored as Markdown. Shown from the stored text, the card
        // read "**Hard bop** and standards" with its asterisks.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Homer and the Sharps', 'description' => '**Barbershop** in four parts']);
        $event = $this->createEvent($act, ['name' => 'Barbershop Night', 'creator_role_id' => $act->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => null]);

        $html = $this->page($owner, $venue, 'requests');

        $this->assertSame(1, substr_count($html, '<bdi>Barbershop in four parts</bdi>'), 'the description, as it reads');
        $this->assertSame(0, substr_count($html, '**Barbershop**'), 'not as it was typed');
    }

    public function test_a_request_filed_under_a_sub_schedule_names_it(): void
    {
        // The card looked the sub-schedule up through a bare `Group`, which is no class in a view:
        // the whole tab was a fatal error for any schedule with such a request waiting.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $group = $this->createGroup($venue, ['name' => 'Late shows']);
        $act = $this->createRole($this->createOwner(), 'talent', ['name' => 'Homer and the Sharps']);
        $event = $this->createEvent($act, ['name' => 'Barbershop Night', 'creator_role_id' => $act->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => null, 'group_id' => $group->id]);

        $html = $this->page($owner, $venue, 'requests');

        $this->assertSame(1, preg_match('/<span class="event-chip"[^>]*>Late shows<\/span>/', $html), 'the sub-schedule it was filed under');
    }

    public function test_accept_all_is_offered_above_one_request(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $act = $this->createRole($this->createOwner(), 'talent');
        foreach (['First', 'Second'] as $name) {
            $event = $this->createEvent($act, ['name' => $name, 'creator_role_id' => $act->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => null]);
        }

        $html = $this->page($owner, $venue, 'requests');

        $this->assertSame(1, substr_count($html, route('event.accept_all', ['subdomain' => $venue->subdomain])));
    }

    public function test_one_unconfirmed_subscriber_is_one_subscriber(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        RoleSubscriber::create([
            'role_id' => $role->id,
            'email' => 'pending@fans.test',
            'name' => 'A Fan',
            'token' => RoleSubscriber::newToken(),
            'confirmed_at' => null,
        ]);

        $html = $this->page($owner, $role, 'followers');

        // It read "1 subscribers have not confirmed".
        $this->assertSame(0, substr_count($html, '1 subscribers'));
        $one = trans_choice('messages.subscriber_pending_notice', 1, ['count' => 1]);
        $this->assertNotSame($one, trans_choice('messages.subscriber_pending_notice', 2, ['count' => 1]), 'one and many are worded apart');
        $this->assertSame(1, substr_count($html, e($one)));
    }

    public function test_the_empty_followers_page_shows_the_schedules_real_address(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $html = $this->page($owner, $role, 'followers');

        // The address was put together by hand from the subdomain and ".eventschedule.com".
        $this->assertSame(0, substr_count($html, $role->subdomain.'.eventschedule.com'), 'no address built by hand');
        $this->assertSame(1, preg_match('/<input type="text" id="subscribe-share-url"[^>]*\svalue="([^"]*)"/', $html, $share), 'the one link to share, with Copy');
        $this->assertSame($role->getGuestUrl().'?subscribe=1', html_entity_decode($share[1]), 'and it is the schedule\'s own address');
    }

    public function test_the_plan_page_draws_no_card_around_nothing(): void
    {
        // An Enterprise plan with no subscription behind it (paid by the year, or given by hand):
        // none of the owner's actions applies, and the page ended in an empty white card.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $html = $this->page($owner, $role, 'plan');

        $this->assertSame(0, substr_count($html, 'data-plan-actions'), 'no card for the owner\'s actions when there are none');
        $this->assertSame(0, preg_match('/<div class="ap-card[^"]*">\s*<div class="space-y-4">\s*(?:<div class="space-y-4">\s*)?(?:<\/div>\s*){2}/', $html), 'and no card with nothing in it');
        $this->assertSame(1, substr_count($html, __('messages.curent_plan')), 'the plan itself is still said');
    }

    public function test_the_plan_page_still_offers_the_free_plan_its_way_up(): void
    {
        // The other side of the test above: the card is there when it holds something.
        config(['cashier.key' => 'pk_test_x']);
        $role = $this->createFreeRole(null, 'talent');
        $owner = User::findOrFail($role->user_id);
        $this->assertFalse($role->fresh()->isPro());

        $html = $this->page($owner, $role, 'plan');

        $this->assertSame(1, substr_count($html, 'data-plan-actions'), 'the card is there when it holds something');
        $this->assertSame(1, substr_count($html, __('messages.upgrade_to_pro_plan').'</span>'), 'the upgrade button is in its card');
    }

    public function test_availability_shows_every_day_of_the_grid_on_a_phone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $html = $this->page($owner, $role, 'availability');

        // The days of the months either side were hidden on a phone, and the grid closed up over
        // them: 1 October 2026, a Thursday, stood under Sunday.
        preg_match_all('/<div class="([^"]*)"[^>]*\sdata-date="\d{4}-\d{2}-\d{2}"|<div class="([^"]*day-element[^"]*)"/', $html, $cells);
        $dayCells = array_filter(array_merge($cells[1], $cells[2]), fn ($class) => str_contains($class, 'day-element'));
        $this->assertGreaterThanOrEqual(28, count($dayCells), 'the month is on the page');
        $this->assertSame([], array_values(array_filter($dayCells, fn ($class) => str_contains($class, 'hidden md:block'))), 'no day is hidden on a phone');

        // And the page says what it is for.
        $this->assertSame(1, substr_count($html, e(__('messages.availability_lead'))));
    }

    public function test_the_availability_notice_sends_the_owner_to_this_schedules_checkout(): void
    {
        // On a plan without the feature the page says so at the top. Its button went to the public
        // pricing page, because the notice was not told which schedule it was about.
        $role = $this->createFreeRole(null, 'talent');
        $owner = User::findOrFail($role->user_id);

        $html = $this->page($owner, $role, 'availability');

        // The notice itself, not the page: the upgrade dialog at the foot of every tab carries the
        // same checkout link, and would answer for a notice that had lost its own.
        $this->assertSame(1, substr_count($html, 'data-availability-gate'), 'the notice is on the page');
        $notice = substr($html, strpos($html, 'data-availability-gate'), 4000);
        $checkout = e(route('role.subscribe', ['subdomain' => $role->subdomain, 'tier' => 'enterprise']));
        $this->assertSame(1, substr_count($notice, 'href="'.$checkout.'"'), 'the notice leads to this schedule\'s checkout');
    }

    public function test_an_unavailable_day_is_labelled_in_the_readers_language(): void
    {
        $owner = $this->createOwner();
        $owner->language_code = 'fr';
        $owner->save();
        $role = $this->createRole($owner, 'talent');
        \App\Models\RoleUser::where('role_id', $role->id)->where('user_id', $owner->id)
            ->update(['dates_unavailable' => json_encode([now()->format('Y-m-d')])]);

        $html = $this->page($owner, $role, 'availability');

        // The word was in the stylesheet, in English, whatever the language.
        $this->assertSame(0, substr_count($html, "content: 'Unavailable'"));
        $this->assertSame(1, preg_match('/<div class="day-x" data-label="([^"]+)"><\/div>/', $html, $label), 'the marked day carries its label');
        $this->assertSame(__('messages.unavailable', [], 'fr'), html_entity_decode($label[1]));
    }

    public function test_team_says_what_can_be_done_about_each_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        // The owner: may change the admin's level and remove them, and may hand the schedule over
        // from their own row.
        $html = $this->page($owner, $role, 'team');
        $this->assertSame(1, substr_count($html, 'name="level"'), 'one member whose level can be changed');
        $this->assertSame(1, substr_count($html, route('role.transfer.create', ['subdomain' => $role->subdomain])), 'transfer, once');
        $this->assertSame(1, preg_match('/class="event-link is-danger">\s*'.preg_quote(__('messages.remove'), '/').'\s*</', $html), 'Remove, in red');
        $this->assertSame(0, substr_count($html, e(__('messages.leave_schedule')).'</button>'), 'an owner cannot leave');

        // The admin: may not change levels, hand the schedule over or remove the owner, and may
        // leave.
        $html = $this->page($admin->fresh(), $role, 'team');
        $this->assertSame(0, substr_count($html, 'name="level"'));
        $this->assertSame(0, substr_count($html, route('role.transfer.create', ['subdomain' => $role->subdomain])));
        $this->assertSame(1, preg_match('/class="event-link is-danger">\s*'.preg_quote(__('messages.leave_schedule'), '/').'\s*</', $html), 'Leave, on their own row');
    }

    public function test_the_owner_can_hand_the_schedule_over_even_when_the_pivot_has_drifted(): void
    {
        // roles.user_id says who owns the schedule; the pivot's level can drift from it
        // (ScheduleTransferService). The link is found by who is signed in, so it survives that,
        // and a member's own number is not written into the page.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');
        \DB::table('role_user')->where('role_id', $role->id)->where('user_id', $owner->id)->update(['level' => 'admin']);

        $html = $this->page($owner, $role, 'team');

        $this->assertSame(1, substr_count($html, route('role.transfer.create', ['subdomain' => $role->subdomain])), 'transfer is still offered, once');
        $this->assertSame(1, substr_count($html, 'id="member-level-'.\App\Utils\UrlUtils::encodeId($admin->id).'"'), 'a row is named by its encoded id');
        $this->assertSame(0, substr_count($html, 'id="member-level-'.$admin->id.'"'), 'never by the raw one');
    }

    public function test_a_custom_label_is_never_left_for_vue_to_compile(): void
    {
        // The calendar is one Vue mount and prints the owner's custom labels as text in some fifty
        // places. A mustache in a label ran as script for every visitor of the public page.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $role->custom_labels = ['filters' => ['value' => 'Find {{ 7*7 }}']];
        $role->save();

        $html = $this->page($owner, $role->fresh(), 'schedule');

        $this->assertSame(0, substr_count($html, 'Find {{ 7*7 }}'), 'no mustache of the owner\'s reaches the page whole');
        $this->assertGreaterThanOrEqual(1, substr_count($html, 'Find { { 7*7 }}'), 'the label is still shown, as text');
    }

    /**
     * The Schedule tab's month is the one every page draws now (role/partials/month). It says
     * which events are not public in words, as a line under the name, where the list under a
     * phone's dates has said so all along.
     */
    public function test_the_month_marks_a_draft_and_an_internal_event_in_words(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $html = $this->page($owner, $role, 'schedule');

        $this->assertSame(1, substr_count($html, ' data-month '), 'the one month');
        // A mark is a note of the event's own line, told apart from the phone list's spans by
        // data-month-mark, and worked out by the script: Internal first, then Draft.
        $this->assertStringContainsString(':data-month-mark="note.mark"', $html);
        $this->assertStringContainsString("if (e.is_internal) return ['warn', L.internal, 'internal'];", $html);
        $this->assertStringContainsString("if (e.is_draft) return ['', L.draft, 'draft'];", $html);
        $this->assertStringContainsString('"draft":'.json_encode(__('messages.draft')), $html);
        $this->assertStringContainsString('"internal":'.json_encode(__('messages.internal')), $html);
        // What a day does not show says how many of it are drafts.
        $this->assertStringContainsString('<span class="gk-cal-more-say" data-month-mark="draft"', $html);

        // The dashboard's month is the same one, so an owner sees the same marks there.
        $home = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($home, ' data-month '));
        $this->assertStringContainsString(':data-month-mark="note.mark"', $home);
    }
}
