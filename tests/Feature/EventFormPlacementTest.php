<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Where each thing lives on the event form since its tabs were rearranged (2026-10), nine tabs
 * becoming seven:
 *
 *   - Page content is a tab of two rows, Gallery and Sponsors, each a tab of its own before.
 *     A row a plan does not include is locked and says what would unlock it, and on a first
 *     event (or a request to another schedule) a locked row is left out.
 *   - Calendar sync is the end of Listing, beside the other places the event appears.
 *   - The Tickets tab's Options row, two blocks under one name, is Checkout (what a buyer is
 *     asked and told; "Registration form" on free registration) and Sales rules (how sales
 *     run). A ticket type's sales dates are its own, opened from its line of links.
 *   - The user guide follows the same order.
 *
 * tests/Browser/EventFormJourneyTest.php and EventFormGuardsTest.php run the moving parts.
 */
class EventFormPlacementTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent');
    }

    private function editUrl(Event $event, Role $role): string
    {
        return route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    private function page(string $url, ?User $user = null): string
    {
        return $this->actingAs($user ?? $this->owner)->get($url)->assertOk()->getContent();
    }

    private function slice(string $html, string $from, string $to): string
    {
        $start = strpos($html, $from);
        $this->assertNotFalse($start, "{$from} is on the page");
        $end = strpos($html, $to, $start + strlen($from));
        $this->assertNotFalse($end, "{$to} follows {$from}");

        return substr($html, $start, $end - $start);
    }

    public function test_each_control_sits_in_its_new_place_and_nowhere_else(): void
    {
        $event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'tickets_enabled' => true]);
        $html = $this->page($this->editUrl($event, $this->role));

        $checkout = $this->slice($html, 'data-ticket-pane="checkout"', 'data-tab="sales_rules"');
        $salesRules = $this->slice($html, 'data-ticket-pane="sales_rules"', 'data-tab="promo_codes"');
        foreach (['id="ask_phone_checkbox"', 'name="custom_fields"', 'id="individual_tickets_checkbox"', 'name="terms_url"', 'name="ticket_notes"'] as $field) {
            $this->assertStringContainsString($field, $checkout, "{$field} is asked in Checkout");
            $this->assertStringNotContainsString($field, $salesRules);
        }
        foreach (['id="sell_after_start_checkbox"', 'id="show_unavailable_tickets_checkbox"', 'id="expire_unpaid_tickets_checkbox"'] as $field) {
            $this->assertStringContainsString($field, $salesRules, "{$field} is a sales rule");
            $this->assertStringNotContainsString($field, $checkout);
        }
        foreach (['id="ask_phone_checkbox"', 'id="sell_after_start_checkbox"', 'id="individual_tickets_checkbox"', 'name="terms_url"'] as $field) {
            $this->assertSame(1, substr_count($html, $field), "{$field} is on the page once");
        }
        // The switch that showed every type's dates at once is gone; each type opens its own.
        $this->assertStringNotContainsString('show_sales_dates_checkbox', $html);
        $types = $this->slice($html, 'data-ticket-pane="tickets"', 'data-tab="payment"');
        $this->assertStringContainsString(':data-sales-dates-box="ticket.uid"', $types);
        $this->assertSame(2, substr_count($types, ':data-sales-dates-link="ticket.uid"'), 'on the single-type line of links and the multi-type one');
        $this->assertSame(1, substr_count($html, 'v-bind:name="`tickets[${index}][sales_start_at]`"'), 'the start date is always posted, from one field');
        $this->assertStringNotContainsString('<template v-if="showSalesDates">', $html);

        // Page content holds the gallery and the sponsors, each in a row that is only ever hidden:
        // the gallery editor carries the input syncGallery() reads, and the logo inputs are added
        // into their container by script.
        $page = $this->slice($html, '<div id="section-page"', 'data-section="section-listing"');
        $this->assertStringContainsString('<div id="page-tab-gallery" v-show="activePageTab === \'gallery\'"', $page);
        $this->assertStringContainsString('<div id="page-tab-sponsors" v-show="activePageTab === \'sponsors\'"', $page);
        $this->assertStringContainsString('<gallery-editor', $page);
        $this->assertStringContainsString('name="sponsor_mode"', $page);
        $this->assertStringContainsString('id="new-event-sponsor-inputs-container"', $page);
        $this->assertSame(1, substr_count($html, 'name="sponsor_mode"'));
        $this->assertSame(1, substr_count($html, '<gallery-editor'));
    }

    public function test_page_content_on_a_free_schedule_is_two_locked_rows(): void
    {
        $free = $this->createFreeRole($this->owner, 'talent');
        $this->assertFalse($free->fresh()->isPro());
        $event = $this->createEvent($free, ['creator_role_id' => $free->id]);
        $html = $this->page($this->editUrl($event, $free));

        $page = $this->slice($html, '<div id="section-page"', 'data-section="section-listing"');
        $this->assertStringContainsString('data-tab="gallery"', $page);
        $this->assertStringContainsString('data-tab="sponsors"', $page);
        $this->assertStringContainsString(e(__('messages.event_sponsors_pro_only')), $page);
        // The locked Sponsors box has no heading of its own: the row above names it, and a title
        // brings a second "Sponsors" and a second lock badge with it.
        $sponsorsPane = substr($page, strpos($page, '<div id="page-tab-sponsors"'));
        $this->assertStringNotContainsString('rounded-full bg-gray-100 dark:bg-white/[0.06]', $sponsorsPane, 'no lock badge inside the box');
        $this->assertStringNotContainsString('name="sponsor_mode"', $html, 'a locked row posts nothing');

        // The rows carry the locks; the tab does not, since its name and a badge wrapped onto two
        // lines in French, and its summary names the locked rows instead.
        $lock = 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75'; // the lock badge's own icon
        foreach (['gallery', 'sponsors'] as $row) {
            $this->assertStringContainsString($lock, $this->slice($page, 'data-tab="'.$row.'"', '</button>'), "the {$row} row carries the lock");
        }
        $this->assertStringNotContainsString($lock, $this->slice($html, '<a href="#section-page"', '</a>'));
        $this->assertStringNotContainsString($lock, $this->slice($html, 'class="mobile-section-header" data-section="section-page"', '</button>'), 'nor on the phone header');
        $this->assertStringContainsString('"sponsorsUnlocked":false', $html);

        // A Pro schedule's rows carry none.
        $proEvent = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
        $proHtml = $this->page($this->editUrl($proEvent, $this->role));
        $this->assertStringNotContainsString($lock, $this->slice($proHtml, '<div id="section-page"', 'data-section="section-listing"'));
        $this->assertStringContainsString('"sponsorsUnlocked":true', $proHtml);
    }

    public function test_a_first_event_on_a_free_schedule_has_no_page_content_tab(): void
    {
        $owner = $this->createOwner();
        $free = $this->createFreeRole($owner, 'talent');

        $html = $this->page(route('event.create', ['subdomain' => $free->subdomain]), $owner);

        $this->assertStringContainsString('showMoreSections: false', $html, 'sanity check: this is the first-event form');
        $this->assertStringNotContainsString('id="section-page"', $html);
        $this->assertStringNotContainsString('data-section="section-page"', $html);
        // Nor the custom fields' upgrade note, among the other locked controls a first event leaves out.
        $this->assertStringNotContainsString(e(__('messages.order_fields_pro_only')), $html);
    }

    public function test_a_venues_owner_is_told_what_unlocks_sponsors_for_her(): void
    {
        $talent = $this->createFreeRole($this->owner, 'talent');
        $venueOwner = $this->createOwner();
        $venue = $this->createFreeRole($venueOwner, 'venue');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $html = $this->page($this->editUrl($event, $venue), $venueOwner);

        // Upgrading HER schedule unlocks the row (EventRepo accepts either schedule's plan), so the
        // gate is about the venue, and she owns it, so it offers the upgrade.
        $sponsors = $this->slice($html, 'id="page-tab-sponsors"', 'data-section="section-listing"');
        $this->assertStringContainsString(route('role.subscribe', ['subdomain' => $venue->subdomain, 'tier' => 'pro']), $sponsors);
        $this->assertStringNotContainsString(route('role.subscribe', ['subdomain' => $talent->subdomain, 'tier' => 'pro']), $sponsors);
    }

    public function test_a_locked_sponsors_row_says_that_saving_removes_them(): void
    {
        $free = $this->createFreeRole($this->owner, 'talent');
        $event = $this->createEvent($free, [
            'creator_role_id' => $free->id,
            'sponsor_mode' => 'custom',
            'sponsor_logos' => json_encode([['name' => 'Duff', 'logo' => 'duff.png', 'url' => null, 'tier' => 'gold']]),
        ]);
        $html = $this->page($this->editUrl($event, $free));

        $this->assertStringContainsString('savedSponsorCount: 1,', $html);
        $this->assertStringContainsString('"sponsorsUnlocked":false', $html);

        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        // The row's own line.
        $this->assertStringContainsString('sponsors = this.savedSponsorCount > 0 ? { text: L.saving_removes_sponsors, warn: true } : { text: L.none, empty: true };', $view);

        // And the save bar, as a note that stands BESIDE what the bar says next, never in its place:
        // in its place, the bar of a downgraded event could not say a save publishes it, nor which
        // tabs are unsaved. Leaving "This event's own" by the tiles is a removal, as before.
        $bar = $this->slice($view, '      barStatus() {', '      saveLabel() {');
        $this->assertStringContainsString("if (this.savedSponsorCount > 0 && ! this.pageFlags.sponsorsUnlocked) {\n          standing = L.saving_removes_sponsors;", $bar);
        $this->assertStringContainsString("} else if (this.savedSponsorCount > 0 && this.event.sponsor_mode !== 'custom') {\n          removes.push(L.saving_removes_sponsors);", $bar);
        $this->assertStringContainsString("text: withStanding(this.visibility === 'public' ? L.saving_publishes : L.saving_hides)", $bar);
        $this->assertStringContainsString('tabs: asTabs(dirty), note: standing }', $bar);
        $this->assertStringContainsString('text: withStanding(L.unsaved_changes)', $bar);
        $this->assertStringContainsString('<span v-if="barStatus.note" class="event-save-strong ms-2" v-text="barStatus.note"></span>', $html, 'the unsaved tabs carry it');
    }

    public function test_listing_ends_with_calendar_sync_and_names_a_calendar_the_event_misses(): void
    {
        $role = $this->createRole($this->owner, 'talent', ['sync_direction' => 'to']);
        RoleUser::where('role_id', $role->id)->where('user_id', $this->owner->id)->update(['google_calendar_id' => 'primary']);

        $public = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $html = $this->page($this->editUrl($public, $role));
        $listing = $this->slice($html, '<div id="section-listing"', 'data-section="section-engagement"');
        $this->assertStringContainsString('<h3 id="listing-calendar-sync"', $listing);
        $this->assertStringContainsString('id="sync-event-btn"', $listing);
        $this->assertStringNotContainsString('data-section="section-calendar-sync"', $html, 'not a tab of its own');
        $this->assertStringNotContainsString('id="section-calendar-sync"', $html);
        $this->assertStringContainsString('calendarSummary: "Google: '.__('messages.not_synced').'",', $html);

        // A Draft and a cancelled event are never copied by a save, so the summary does not nag.
        foreach (['draft' => ['is_draft' => true], 'cancelled' => ['is_cancelled' => true]] as $state => $attrs) {
            $other = $this->createEvent($role, ['creator_role_id' => $role->id] + $attrs);
            $this->assertStringContainsString('calendarSummary: "",', $this->page($this->editUrl($other, $role)), "a {$state} event");
        }
    }

    /**
     * No save copies a Draft, Internal or cancelled event to a calendar (EventRepo::saveEvent()),
     * so Sync Now, which would, is not offered on one, and the line under the calendar says why
     * instead of promising the next save. It used to say "It is copied the next time you save."
     * on a cancelled event, which has not been true since a save stopped pushing one.
     */
    public function test_sync_now_is_offered_only_where_a_save_would_copy_the_event(): void
    {
        $role = $this->createRole($this->owner, 'talent', ['sync_direction' => 'to']);
        RoleUser::where('role_id', $role->id)->where('user_id', $this->owner->id)->update(['google_calendar_id' => 'primary']);

        $listing = function (array $attrs) use ($role): string {
            $event = $this->createEvent($role, ['creator_role_id' => $role->id] + $attrs);

            return $this->slice($this->page($this->editUrl($event, $role)), '<h3 id="listing-calendar-sync"', 'data-section="section-engagement"');
        };

        $public = $listing([]);
        $this->assertStringContainsString('id="sync-event-btn"', $public);
        $this->assertStringContainsString(e(__('messages.calendar_not_synced_next_save')), $public);

        foreach ([
            'draft' => [['is_draft' => true], 'calendar_not_synced_draft'],
            'internal' => [['is_draft' => true, 'is_internal' => true], 'calendar_not_synced_draft'],
            'cancelled' => [['is_cancelled' => true], 'calendar_not_synced_cancelled'],
        ] as $state => [$attrs, $line]) {
            $html = $listing($attrs);
            $this->assertStringNotContainsString('id="sync-event-btn"', $html, "Sync Now on a {$state} event");
            $this->assertStringContainsString(e(__('messages.'.$line)), $html, "the line on a {$state} event");
            $this->assertStringNotContainsString(e(__('messages.calendar_not_synced_next_save')), $html, "a {$state} event is not copied by a save");
        }
    }

    /**
     * Saving an event with a pass turns Individual tickets off (EventRepo::saveEvent()), so while a
     * type is a pass the switch is drawn off and locked with the reason under it, and posts 0. A
     * pass is one per order whatever is typed, so it is not offered + Add Limit either.
     */
    public function test_a_pass_locks_individual_tickets_and_has_no_order_limit(): void
    {
        $event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id, 'tickets_enabled' => true]);
        $html = $this->page($this->editUrl($event, $this->role));

        $checkout = $this->slice($html, 'data-ticket-pane="checkout"', 'data-tab="sales_rules"');
        $this->assertStringContainsString(':checked="individualTicketsOn"', $checkout);
        $this->assertStringContainsString(':disabled="individualTicketsPlanLocked || hasPassTicket"', $checkout);
        $this->assertStringContainsString('v-if="hasPassTicket" data-individual-tickets-pass-note>'.e(__('messages.individual_tickets_off_for_passes')).'</p>', $checkout);
        $this->assertStringContainsString('name="individual_tickets" :value="individualTicketsOn ? 1 : 0"', $checkout);

        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertStringContainsString('return !! this.event.tickets_enabled && this.tickets.some(function (ticket) { return !! ticket.is_pass; });', $view);
        $this->assertStringContainsString("return !! this.event.individual_tickets && ! this.hasPassTicket\n          && (! this.individualTicketsPlanLocked || this.savedIndividualTicketsSold);", $view);
        $this->assertStringContainsString('if (this.individualTicketsOn) { asked.push(C.individual_tickets); }', $view, 'the Checkout line does not name it either');

        $this->assertSame(2, substr_count($html, '@click="addTicketMaxPerOrder(index)"'), 'sanity check: both + Add Limit links are on the page');
        $this->assertSame(2, substr_count($html, 'v-if="!ticket.is_pass && (ticket.max_per_order === null || ticket.max_per_order === undefined)"'), '+ Add Limit is not offered on a pass');
        $this->assertStringContainsString('<div v-if="!ticket.is_pass && ticket.max_per_order !== null && ticket.max_per_order !== undefined" class="mt-4">', $html);
    }

    /**
     * Individual tickets is free on free registration and Pro with tickets (docs/FEATURES.md,
     * EventRepo::saveEvent()). Below Pro the switch is locked only while tickets are on, and under
     * that lock it shows (and posts) only what an event SAVED selling with it keeps.
     */
    public function test_individual_tickets_is_locked_below_pro_only_with_tickets(): void
    {
        $free = $this->createFreeRole($this->owner, 'talent');
        $rsvp = $this->createEvent($free, ['creator_role_id' => $free->id, 'rsvp_enabled' => true, 'individual_tickets' => true]);
        $html = $this->page($this->editUrl($rsvp, $free));

        $checkout = $this->slice($html, 'data-ticket-pane="checkout"', 'data-tab="sales_rules"');
        $this->assertStringContainsString(':disabled="individualTicketsPlanLocked || hasPassTicket"', $checkout);
        $this->assertStringContainsString('<template v-if="individualTicketsPlanLocked">', $checkout, 'the Pro badge follows the plan lock, not the plan alone');
        $this->assertStringNotContainsString('!isPro', $this->slice($checkout, 'id="individual_tickets_checkbox"', 'name="individual_tickets"'));
        $this->assertStringContainsString('savedIndividualTicketsSold: false,', $html, 'stored under free registration is not stored selling tickets');

        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertStringContainsString("individualTicketsPlanLocked() {\n        return ! this.isPro && !! this.event.tickets_enabled;", $view);

        $lapsed = $this->createEvent($free, ['creator_role_id' => $free->id, 'tickets_enabled' => true, 'individual_tickets' => true]);
        $this->assertStringContainsString('savedIndividualTicketsSold: true,', $this->page($this->editUrl($lapsed, $free)), 'a lapsed Pro event keeps what it sells with');
    }

    public function test_checkout_offers_its_custom_fields_upgrade_for_tickets_as_well(): void
    {
        $free = $this->createFreeRole($this->owner, 'talent');
        $event = $this->createEvent($free, ['creator_role_id' => $free->id, 'tickets_enabled' => true]);
        $html = $this->page($this->editUrl($event, $free));

        $checkout = $this->slice($html, 'data-ticket-pane="checkout"', 'data-tab="sales_rules"');
        $this->assertStringContainsString(e(__('messages.order_fields_pro_only')), $checkout);
        $this->assertStringNotContainsString(e(__('messages.custom_fields_pro_only')), $checkout);
        $this->assertStringNotContainsString('v-show="event.rsvp_enabled"', $checkout, 'shown for tickets too, not only for free registration');

        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        // Below Pro the editor is not shown, but a saved event's questions are still asked at
        // checkout: the line names them, and the upsell says they are kept. A new event below Pro
        // (a copy, a template) cannot post them, so for it they are not named.
        $this->assertStringContainsString('if ((this.isPro || this.eventExists) && this.eventCustomFields && Object.keys(this.eventCustomFields).length) { asked.push(C.custom_fields); }', $view);
        $this->assertStringContainsString(e(__('messages.order_fields_kept_below_pro')), $checkout);
    }

    public function test_the_sales_rules_line_starts_with_the_dates(): void
    {
        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $source = $this->slice($view, 'var S = this.tabLabels.sales_rules;', 'var line = function');

        $dates = strpos($source, 'rules.push(S.sales_dates)');
        $this->assertNotFalse($dates);
        $this->assertLessThan(strpos($source, 'rules.push(S.sell_after_start)'), $dates, 'first, as the line is cut at its end');
        $this->assertStringContainsString('if (this.hasLimitedPaidTickets && this.showExpireUnpaid) { rules.push(S.expire_unpaid); }', $source);
    }

    /** The guide's sections, like its contents list, follow the form. */
    public function test_the_guide_follows_the_form(): void
    {
        $event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
        preg_match_all('/<a href="#(section-[a-z-]+)" class="section-nav-link" data-section="\1"/', $this->page($this->editUrl($event, $this->role)), $tabs);
        $sectionFor = [
            'section-details' => 'details', 'section-tickets' => 'tickets', 'section-participants' => 'participants',
            'section-agenda' => 'agenda', 'section-page' => 'page-content', 'section-listing' => 'listing',
            'section-engagement' => 'engagement',
        ];
        $this->assertSame(array_keys($sectionFor), $tabs[1], 'sanity check: the form\'s tabs');

        $guide = file_get_contents(resource_path('views/marketing/docs/creating-events.blade.php'));
        $at = fn (string $id) => strpos($guide, '<section id="'.$id.'"');
        $order = array_map($at, array_values($sectionFor));
        $this->assertNotContains(false, $order);
        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order, 'creating-events has a section per tab, in the form\'s order');

        // Inside a tab, its rows' order: Gallery then Sponsors; Calendar sync at the end of Listing.
        $this->assertLessThan($at('sponsors'), $at('gallery'));
        $this->assertLessThan($at('listing'), $at('sponsors'));
        $this->assertLessThan($at('google-calendar'), $at('schedules'));
        $this->assertLessThan($at('engagement'), $at('google-calendar'));

        $tickets = file_get_contents(resource_path('views/marketing/docs/tickets.blade.php'));
        $row = fn (string $id) => strpos($tickets, '<section id="'.$id.'"');
        $this->assertLessThan($row('checkout'), $row('payment'));
        $this->assertLessThan($row('sales-rules'), $row('checkout'));
        $this->assertLessThan($row('promo-codes'), $row('sales-rules'));
        $this->assertStringContainsString('<span id="options" aria-hidden="true"></span>', $tickets, 'links from outside to the old Options row still land');
    }

    /**
     * In French, Hebrew, Italian and Arabic the guest page's "checkout" is the same word as
     * "payment", so the Checkout row has a word of its own, the buyer's form.
     */
    public function test_checkout_and_payment_are_two_words_in_every_language(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $checkout = __('messages.ticket_row_checkout', [], $lang);
            $this->assertNotSame('messages.ticket_row_checkout', $checkout, "{$lang} has the key");
            $this->assertNotSame(__('messages.payment', [], $lang), $checkout, "{$lang}: the Checkout row is not a second Payment row");
            $this->assertNotSame(__('messages.ticket_row_sales_rules', [], $lang), $checkout, $lang);
        }
    }
}
