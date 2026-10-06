<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The shape of the event form: the essentials on the first tab, the rest behind tabs that say
 * what they hold.
 *
 * Location and the ticket choice used to live behind tabs most people never opened, so events
 * were published with no place and no way to sign up. They are on the Event tab now, and the old
 * Venue, Recurring and Schedules tabs are gone. What this file holds in place:
 *
 *   - every tab that is rendered has a summary, on the sidebar and on a phone, and the page's
 *     data can answer for it (a tab without one renders an empty line and throws in the browser);
 *   - a refused save says which tab its errors are on, by the field they are keyed on;
 *   - tickets live in the Tickets tab and nowhere else: its three tiles are the choice, and what
 *     were four more tabs inside it are rows that open in place;
 *   - the Event tab is two sections (what and when; where) and an event opens on its fields;
 *   - picking a saved venue, and finding one by email or phone, are still on the page.
 *
 * tests/Browser/EventFormJourneyTest.php runs the same things in a browser.
 */
class EventFormSectionsTest extends TestCase
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

    private function createUrl(?Role $role = null): string
    {
        return route('event.create', ['subdomain' => ($role ?? $this->role)->subdomain]);
    }

    private function editUrl(Event $event, ?Role $role = null): string
    {
        return route('event.edit', ['subdomain' => ($role ?? $this->role)->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
    }

    private function page(string $url, ?User $user = null): string
    {
        return $this->actingAs($user ?? $this->owner)->get($url)->assertOk()->getContent();
    }

    /** The page as it comes back from a save the server refused over these fields. */
    private function pageWithErrors(string $url, array $keys): string
    {
        $bag = new MessageBag(array_fill_keys($keys, ['Refused.']));

        return $this->actingAs($this->owner)
            ->withSession(['errors' => (new ViewErrorBag)->put('default', $bag)])
            ->get($url)->assertOk()->getContent();
    }

    /** One element by id, up to the comment or element that follows it in the view. */
    private function slice(string $html, string $from, string $to): string
    {
        $start = strpos($html, $from);
        $this->assertNotFalse($start, "{$from} is on the page");
        $end = strpos($html, $to, $start);
        $this->assertNotFalse($end, "{$to} follows {$from}");

        return substr($html, $start, $end - $start);
    }

    /** @return string[] the section each sidebar tab opens, in order */
    private function tabs(string $html): array
    {
        preg_match_all('/<a href="#(section-[a-z-]+)" class="section-nav-link" data-section="\1"/', $html, $found);

        return $found[1];
    }

    public function test_the_tabs_are_the_essentials_then_the_rest(): void
    {
        $event = $this->createEvent($this->role);

        $this->assertSame(
            ['section-details', 'section-tickets', 'section-participants', 'section-agenda', 'section-gallery', 'section-listing', 'section-engagement', 'section-event-settings'],
            array_values(array_diff($this->tabs($this->page($this->editUrl($event))), ['section-calendar-sync'])),
        );

        // The tabs that were folded into the Event tab and into Listing are not sections any more.
        $html = $this->page($this->createUrl());
        foreach (['section-venue', 'section-recurring', 'section-schedules', 'section-google-calendar', 'section-microsoft-calendar'] as $retired) {
            $this->assertStringNotContainsString('id="'.$retired.'"', $html);
            $this->assertStringNotContainsString('data-section="'.$retired.'"', $html);
        }
    }

    public function test_every_tab_has_a_summary_on_the_sidebar_and_on_a_phone(): void
    {
        $event = $this->createEvent($this->role);

        foreach (['create' => $this->createUrl(), 'edit' => $this->editUrl($event)] as $name => $url) {
            $html = $this->page($url);
            $tabs = $this->tabs($html);
            $this->assertGreaterThanOrEqual(8, count($tabs), "sanity check: the {$name} page has its tabs");

            foreach ($tabs as $tab) {
                $summary = '<span class="section-nav-summary" v-cloak :class="{ \'is-empty\': tabSummaries[\''.$tab.'\'].empty }"><bdi v-text="tabSummaries[\''.$tab.'\'].text"></bdi></span>';
                $this->assertSame(2, substr_count($html, $summary), "{$tab} has a summary on the sidebar and on its phone header ({$name})");
                $this->assertSame(2, substr_count($html, 'v-show="sectionDirty[\''.$tab.'\']"'), "{$tab} can show unsaved changes in both places ({$name})");
                // What the page's data answers with, and the name the save bar calls the tab by.
                $this->assertStringContainsString("'{$tab}': ", $this->slice($html, 'tabSummaries() {', 'barStatus() {'), "{$tab} has a summary to show ({$name})");
                $this->assertMatchesRegularExpression('/"tabs":\{[^}]*"'.$tab.'":"/', $html, "{$tab} has a name ({$name})");
            }
        }
    }

    public function test_one_save_bar_holds_save_and_cancel(): void
    {
        $event = $this->createEvent($this->role, ['is_draft' => true]);

        foreach ([$this->createUrl(), $this->editUrl($event)] as $url) {
            $html = $this->page($url);
            $form = $this->slice($html, 'id="edit-form"', '</form>');

            $this->assertSame(1, substr_count($form, 'class="event-save-bar"'));
            // The only button that submits the event form; every other submit names another form.
            $this->assertSame(1, preg_match_all('/<button(?![^>]*\sform=)[^>]*type="submit"/', $form));
            $this->assertSame(1, substr_count($form, 'id="event-cancel-real"'));
        }

        // A draft can be published from the bar.
        $bar = $this->slice($this->page($this->editUrl($event)), 'class="event-save-bar"', '</form>');
        $this->assertStringContainsString('@click="publishEvent()"', $bar);
    }

    public function test_a_refused_save_names_the_tab_each_error_is_on(): void
    {
        $event = $this->createEvent($this->role);
        $url = $this->editUrl($event);

        // First, before this test's session holds any errors: an ordinary load names no tab.
        $this->assertStringContainsString('sectionErrors: [],', $this->page($url));

        $cases = [
            'section-tickets' => ['promo_codes.0.code', 'tickets.1.type', 'payment_method', 'registration_url'],
            'section-participants' => ['members.0.email'],
            'section-agenda' => ['event_parts.0.name'],
            'section-listing' => ['slug', 'category_id', 'curators.0'],
            'section-event-settings' => ['new_event_sponsor_name'],
            // Anything that names no other tab is the Event tab's.
            'section-details' => ['name', 'venue_email', 'event_url', 'something_new'],
        ];

        foreach ($cases as $tab => $keys) {
            foreach ($keys as $key) {
                $this->assertStringContainsString('sectionErrors: ["'.$tab.'"],', $this->pageWithErrors($url, [$key]), "{$key} belongs to {$tab}");
            }
        }

        $this->assertStringContainsString('sectionErrors: ["section-tickets","section-listing"],', $this->pageWithErrors($url, ['tickets.0.price', 'slug', 'promo_codes.0.code']));

        // The Tickets tab has tabs of its own: it opens on the one the error is under.
        $this->assertStringContainsString('activeTicketTab: "promo_codes",', $this->pageWithErrors($url, ['promo_codes.0.code']));
        $this->assertStringContainsString('activeTicketTab: "add_ons",', $this->pageWithErrors($url, ['addons.0.price']));
        $this->assertStringContainsString('activeTicketTab: "payment",', $this->pageWithErrors($url, ['payment_method']));
        $this->assertStringContainsString('activeTicketTab: "tickets",', $this->pageWithErrors($url, ['slug']));
        $this->assertStringContainsString('activeTicketTab: "options",', $this->pageWithErrors($url, ['terms_url']));
        $this->assertStringContainsString('activeTicketTab: "payment",', $this->pageWithErrors($url, ['ticket_currency_code']));
        $this->assertStringContainsString('activeTicketTab: "tickets",', $this->pageWithErrors($url, ['seating_plan_id']));
    }

    public function test_an_event_that_exists_opens_on_its_fields(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Saved Name']);
        $html = $this->page($this->editUrl($event));

        // No card to click through, and nothing folded: the fields are the page.
        $this->assertStringNotContainsString('event-card', $html);
        $this->assertStringNotContainsString('basicsOpen', $html);
        $this->assertStringContainsString('<div class="ap-card rounded-xl p-4 sm:p-6" id="event-basics">', $html);
        $this->assertStringContainsString('<div class="ap-card rounded-xl p-4 sm:p-6" id="event-location">', $html);

        // The page is titled with the event, over a small "Edit Event", with what is SAVED beside it.
        $this->assertMatchesRegularExpression('/<p class="event-eyebrow">'.preg_quote(__('messages.edit_event'), '/').'<\/p>/', $html);
        $this->assertMatchesRegularExpression('/<h2 [^>]*v-pre>\s*Saved Name\s*<\/h2>/', $html);
        $this->assertMatchesRegularExpression('/<button type="button" class="event-badge is-public" id="event-saved-state" @click="goToTab\(\'section-listing\'\)">'.preg_quote(__('messages.public'), '/').'<\/button>/', $html);
        $this->assertStringContainsString('savedVisibility: "public",', $html);
        $this->assertStringContainsString('class="event-url-strip"', $html);

        $draft = $this->createEvent($this->role, ['is_draft' => true]);
        $this->assertStringContainsString('class="event-badge is-draft" id="event-saved-state"', $this->page($this->editUrl($draft)));
    }

    public function test_a_new_event_keeps_the_plain_title_and_has_no_badge(): void
    {
        $html = $this->page($this->createUrl());

        $this->assertStringNotContainsString('class="event-eyebrow"', $html);
        $this->assertStringNotContainsString('id="event-saved-state"', $html);
        $this->assertStringNotContainsString('class="event-url-strip"', $html);
        $this->assertStringContainsString('<div class="ap-card rounded-xl p-4 sm:p-6" id="event-basics">', $html);
    }

    /**
     * Two sections and About: what and when, then where. And nothing about tickets: they were
     * asked about here and again on their own tab, which was one place too many.
     */
    public function test_the_event_tab_is_two_sections_and_holds_no_tickets(): void
    {
        $this->createRole($this->owner, 'venue', ['name' => 'Blue Note']);
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'Door', 'price' => 10]);

        foreach (['create' => $this->createUrl(), 'edit' => $this->editUrl($event)] as $name => $url) {
            $html = $this->page($url);
            $tab = $this->slice($html, 'id="section-details"', 'data-section="section-tickets"');

            $basics = strpos($tab, 'id="event-basics"');
            $location = strpos($tab, 'id="event-location"');
            $about = strpos($tab, 'id="event-about"');
            $this->assertTrue($basics !== false && $location !== false && $about !== false, "the {$name} Event tab has its three parts");
            $this->assertTrue($basics < $location && $location < $about, "in order ({$name})");

            $what = substr($tab, $basics, $location - $basics);
            $where = substr($tab, $location, $about - $location);
            foreach (['id="event_name"', 'id="event_date"', 'id="start_time"', 'id="flyer_image"'] as $field) {
                $this->assertStringContainsString($field, $what, "{$field} is in the first section ({$name})");
            }
            $this->assertStringNotContainsString('id="in_person"', $what);
            foreach (['id="in_person"', 'id="online"', 'name="venue_id"', 'name="venue_submitted"'] as $field) {
                $this->assertStringContainsString($field, $where, "{$field} is in the location section ({$name})");
            }

            foreach (['chooseTickets', 'event-tile', 'ticketChoice', 'name="rsvp_limit"', 'name="registration_url"', 'tickets[', 'event-ticket-choice', 'event-tickets-row'] as $ticketThing) {
                $this->assertStringNotContainsString($ticketThing, $tab, "{$ticketThing} is not on the {$name} Event tab");
            }
        }
    }

    /**
     * The three tiles are the Tickets tab's own choice (ticketMode), where three radios were. One
     * set of fields for each thing: nothing is asked in two places.
     */
    public function test_the_tiles_are_the_tickets_tabs_own_choice(): void
    {
        $html = $this->page($this->createUrl());
        $panel = $this->slice($html, '<div id="section-tickets"', 'id="section-participants"');

        foreach (['rsvp', 'tickets', 'external'] as $choice) {
            $this->assertMatchesRegularExpression(
                '/<button type="button" class="event-tile ticket-mode-radio" id="ticket_choice_'.$choice.'" value="'.$choice.'"[^>]*@click="chooseTickets\(\''.$choice.'\'\)"/',
                $panel
            );
            $this->assertStringNotContainsString('id="ticket_mode_'.$choice.'"', $html, 'the radio it replaced is gone');
        }
        $this->assertStringContainsString('id="ticket_choice_none" @click="chooseTickets(null)"', $panel);

        $form = $this->slice($html, 'id="edit-form"', '</form>');
        foreach (['id="rsvp_limit" name="rsvp_limit"', 'id="registration_url" name="registration_url"', 'name="tickets_enabled"', 'name="rsvp_enabled"', 'v-bind:name="`tickets[${index}][price]`"'] as $field) {
            $this->assertSame(1, substr_count($form, $field), "{$field} is asked in one place");
            $this->assertStringContainsString($field, $panel);
        }

        // A choice that is not the one chosen has its fields switched off, so that a value left in
        // one cannot refuse the save from where nobody can see it, and what they hold is sent by a
        // hidden stand-in instead: one of the two is live at a time, never both.
        $this->assertSame(4, substr_count($panel, '<fieldset class="mb-6 event-fieldset" v-show="ticketChoice === \'external\'" :disabled="ticketChoice !== \'external\'">'));
        $this->assertMatchesRegularExpression('/<template v-if="ticketChoice !== \'external\'">\s*<input type="hidden" name="registration_url" :value="event\.registration_url \|\| \'\'">/', $panel);
        $this->assertStringContainsString('v-model="event.rsvp_limit" v-bind:disabled="ticketChoice !== \'rsvp\'"', $panel);
        $this->assertStringContainsString('<input type="hidden" name="rsvp_limit" v-if="ticketChoice !== \'rsvp\'"', $panel);
        // "Not needed" is always there, and says when it is the one in force.
        $this->assertStringContainsString(':class="{ \'is-current\': ! ticketChoice }"', $panel);
        $this->assertStringNotContainsString('quick_', $html);
    }

    /**
     * Each panel says what is in force, not what could be: a row with nothing in it reads "None",
     * an Engagement row gives the setting and then whether it is the schedule's, the three choices
     * of a setting are three different words, and a new public event is published by a button
     * that says so.
     */
    public function test_each_panel_says_what_is_in_force(): void
    {
        $this->role->forceFill(['fan_photos_enabled' => false])->save();
        $event = $this->createEvent($this->role);
        $html = $this->page($this->editUrl($event));

        // What "same as the schedule" comes to, for the summaries and beside each setting's name.
        $this->assertSame(1, preg_match('/^\s*engagementInherited: (\{.*\}),$/m', $html, $seed));
        $this->assertSame(
            ['fan_comments_enabled' => true, 'fan_photos_enabled' => false, 'fan_videos_enabled' => true, 'feedback_enabled' => false],
            json_decode($seed[1], true)
        );
        $this->assertMatchesRegularExpression('/id="fan_comments_enabled_label">Comments<span class="event-tab-aside block font-normal">Schedule: enabled<\/span>/', $html);
        $this->assertMatchesRegularExpression('/id="fan_photos_enabled_label">Photos<span class="event-tab-aside block font-normal">Schedule: disabled<\/span>/', $html);
        $this->assertStringNotContainsString('Same as schedule (', $html, 'the first choice no longer reads as a second "Enabled"');

        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertStringContainsString("options: on.length ? { text: on.join(', '), empty: false } : { text: this.tabLabels.none, empty: true },", $view);
        $this->assertStringNotContainsString("@json(__('messages.add_add_on')), empty: true", $view);
        $this->assertStringContainsString("return ! this.eventIsSaved && this.visibility === 'public' ? this.tabLabels.publish : this.tabLabels.save;", $view);

        // Visibility: one line right under the choices says what the chosen one means (or the one
        // being pointed at). A separate list of all four was tried and taken back: it parted each
        // choice from its own text.
        $this->assertStringNotContainsString('event-visibility-key', $html);
        $this->assertSame(1, preg_match('/<div class="mt-2 min-h-\[1\.25rem\]" id="visibility-desc">(.*?)<\/div>/s', $html, $line));
        foreach (['public', 'draft', 'internal', 'unlisted'] as $choice) {
            $this->assertMatchesRegularExpression(
                '/<p class="[^"]*"\s+'.($choice === 'public' ? '' : 'v-cloak\s+').'v-show="\(hoveredVisibility \|\| visibility\) === \''.$choice.'\'">'.preg_quote(e(__('messages.visibility_'.$choice.'_desc')), '/').'<\/p>/',
                $line[1],
                $choice.' has its line, and only the chosen one shows before the page script runs'
            );
        }

        // The price says what it is in, and tickets with no way to be paid say so under the tiles.
        $this->assertStringContainsString('<span class="event-tab-aside font-normal" v-cloak v-text="event.ticket_currency_code"></span>', $html);
        $this->assertStringContainsString('v-show="ticketChoice === \'tickets\' && anyTicketPriced && paymentWarning"', $html);

        // Participants are the people on stage; said whether or not anyone is listed.
        $this->assertSame(1, substr_count($html, '<p class="event-hint">'.e(__('messages.participants_help')).'</p>'));
        $this->assertStringContainsString('v-if="eventParts.length && ! agendaShowTimes" class="event-hint">'.e(__('messages.agenda_show_times_help')), $html);
    }

    /** The "Same as schedule" choice for sponsors names them, or says the schedule has none. */
    public function test_the_sponsor_choice_names_the_schedules_sponsors(): void
    {
        $event = $this->createEvent($this->role);
        $none = $this->page($this->editUrl($event));
        $this->assertStringContainsString('<span class="event-tile-help" v-pre>'.e(__('messages.sponsors_same_help').': '.mb_strtolower(__('messages.none'))).'</span>', $none);

        $this->role->forceFill(['sponsor_logos' => json_encode([
            ['logo' => 'sponsor_a.png', 'name' => 'Duff {{ 7*7 }}', 'url' => '', 'tier' => ''],
            ['logo' => 'sponsor_b.png', 'name' => 'Kwik-E-Mart', 'url' => '', 'tier' => ''],
        ])])->save();
        $named = $this->page($this->editUrl($event));
        // The names are the owner's text inside the Vue mount: v-pre, on the element itself.
        $this->assertStringContainsString('<span class="event-tile-help" v-pre>'.e('Duff {{ 7*7 }}, Kwik-E-Mart').'</span>', $named);
    }

    /** A sub-schedule is optional, and its empty choice says "None" where it said "Please select". */
    public function test_no_sub_schedule_is_a_choice_that_says_so(): void
    {
        $group = new \App\Models\Group;
        $group->role_id = $this->role->id;
        $group->name = 'Late shows';
        $group->slug = 'late-shows';
        $group->save();

        $html = $this->page($this->createUrl());
        $this->assertMatchesRegularExpression('/<select id="current_role_group_id"[^>]*>\s*<option value="">None<\/option>/', $html);
    }

    /**
     * A calendar that has not been sent the event says why. The tab is only there when the
     * schedule sends its events out, so the reasons are two.
     */
    public function test_an_event_that_is_not_synced_says_why(): void
    {
        $view = file_get_contents(resource_path('views/event/edit.blade.php'));
        $this->assertSame(2, substr_count($view, "{{ \$event->is_draft ? __('messages.calendar_not_synced_draft') : __('messages.calendar_not_synced_next_save') }}"));
        $this->assertStringNotContainsString('calendar_not_synced_manual', $view);
    }

    /**
     * Payment, Options, Promo codes and Add-ons were a second row of tabs inside the tab. Each is a
     * row that opens in place; "tickets" is the name for none of them being open.
     */
    public function test_the_tickets_tab_has_rows_where_it_had_tabs(): void
    {
        $event = $this->createEvent($this->role, ['rsvp_enabled' => true]);

        foreach ([$this->createUrl(), $this->editUrl($event)] as $url) {
            $html = $this->page($url);
            $panel = $this->slice($html, '<div id="section-tickets"', 'id="section-participants"');

            $this->assertStringNotContainsString('class="ticket-tab text-center', $panel, 'the strip is gone');
            foreach (['payment', 'options', 'promo_codes', 'add_ons'] as $row) {
                // ticket-tab and data-tab are what the Help link reads (layouts/navigation).
                $this->assertSame(1, preg_match_all('/<button type="button" class="event-subrow ticket-tab" data-tab="'.$row.'"[^>]*@click="toggleTicketRow\(\''.$row.'\'\)"/', $panel), "{$row} is a row");
                $this->assertStringContainsString('v-text="ticketRows.'.$row.'.text"', $panel);
                $this->assertStringContainsString('data-ticket-pane="'.$row.'"', $panel);
            }
            $this->assertStringContainsString('<div v-show="event.tickets_enabled" data-ticket-pane="tickets">', $panel, 'the ticket types are always showing while tickets are on');
            // Whatever the event's choice, no row is open on an ordinary load.
            $this->assertStringContainsString('activeTicketTab: "tickets",', $html);
        }
    }

    /**
     * A promo code or add-on row left half-filled, in a choice that has since been switched off,
     * refused the save in the browser and again on the server with nothing on screen to say why.
     */
    public function test_promo_codes_and_add_ons_are_disabled_while_tickets_are_off(): void
    {
        $panel = $this->slice($this->page($this->createUrl()), '<div id="section-tickets"', 'id="section-participants"');

        $this->assertSame(2, substr_count($panel, '<fieldset class="event-fieldset" :disabled="! event.tickets_enabled">'));
        foreach (['promo_codes', 'add_ons'] as $pane) {
            $this->assertMatchesRegularExpression('/<fieldset class="event-fieldset" :disabled="! event\.tickets_enabled">\s*<!--[^>]*-->\s*<div v-show="activeTicketTab === \''.$pane.'\'" data-ticket-pane="'.$pane.'"/', $panel);
        }
    }

    public function test_the_payment_row_says_connect_stripe_only_to_someone_with_nothing_connected(): void
    {
        config(['services.stripe_platform.secret' => null]);
        $html = $this->page($this->createUrl());

        // PaymentMethodEventFormTest holds the other half: an owner whose gateway cannot take the
        // currency must never be sent these words.
        $this->assertStringContainsString('paymentWarning: '.json_encode(__('messages.connect_stripe_to_get_paid')).',', $html);
        $this->assertStringContainsString('if (this.anyTicketPriced && this.paymentWarning) {', $html, 'and only once a price is typed');
    }

    /** Asked for by name: the venue picker and the email and phone lookup moved, and must not be lost. */
    public function test_location_keeps_the_saved_venue_list_and_the_lookup_by_email_or_phone(): void
    {
        $this->createRole($this->owner, 'venue', ['name' => 'Blue Note', 'address1' => '131 W 3rd St', 'city' => 'New York']);
        $html = $this->page($this->createUrl());
        $location = $this->slice($html, 'id="event-location"', 'id="event-about"');

        $this->assertStringContainsString('<select id="selected_venue"', $location);
        $this->assertStringContainsString('v-model="selectedVenue"', $location);
        $this->assertStringContainsString('venueType: "use_existing",', $html);

        // The lookup: leaving the email searches, the phone searches as it is typed, the results
        // can be selected, and the invitation is still offered.
        $this->assertMatchesRegularExpression('/id="venue_email"[^>]*@blur="searchVenues"/', $location);
        $this->assertStringContainsString('name="venue_phone"', $location);
        $this->assertStringContainsString('@click="selectVenue(venue)"', $location);
        $this->assertStringContainsString('@click="openVenueContact"', $location);
        foreach (['venue_id', 'venue_submitted', 'venue_details_editable', 'venue_name', 'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code', 'venue_website'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $location, "{$field} is still posted from the Event tab");
        }
    }

    /**
     * A first event is one focused column, which is also what leaves the setup guide's card its
     * place beside the form; the flyer follows the location so the place stays on the first screen.
     */
    public function test_a_first_event_is_one_column_with_the_flyer_after_the_place(): void
    {
        $first = $this->page($this->createUrl());
        $this->assertStringContainsString(__('messages.first_event_form_subtitle'), $first, 'sanity check: this is a first event');
        $this->assertStringContainsString('<div class="event-tab event-tab-first max-w-xl">', $first);
        $this->assertSame(1, substr_count($first, 'id="flyer_image"'), 'the flyer field is rendered exactly once');
        $this->assertGreaterThan(strpos($first, 'id="event-location"'), strpos($first, 'id="flyer_image"'));
        $this->assertStringContainsString('<div class="max-w-xl">', $this->slice($first, '<div id="section-tickets"', 'id="ticket_choice_rsvp"'));

        $this->createEvent($this->role, ['user_id' => $this->owner->id]);
        $later = $this->page($this->createUrl());
        $this->assertStringNotContainsString(__('messages.first_event_form_subtitle'), $later);
        $this->assertStringContainsString('<div class="event-tab">', $later);
        $this->assertSame(1, substr_count($later, 'id="flyer_image"'));
        $this->assertLessThan(strpos($later, 'id="event-location"'), strpos($later, 'id="flyer_image"'));
        $this->assertStringContainsString('<div class="event-tickets-wide">', $this->slice($later, '<div id="section-tickets"', 'id="ticket_choice_rsvp"'));
    }

    public function test_the_recent_venues_are_the_ones_this_schedule_was_last_at(): void
    {
        $venues = [];
        foreach (['Alpha', 'Bravo', 'Charlie', 'Delta', 'Echo'] as $name) {
            $venues[$name] = $this->createRole($this->owner, 'venue', ['name' => $name.' Hall']);
        }

        // Oldest to newest: Delta, Alpha, Echo, Bravo. Charlie has never been played.
        foreach (['Delta', 'Alpha', 'Echo', 'Bravo'] as $name) {
            $event = $this->createEvent($this->role, ['name' => 'At '.$name]);
            $event->roles()->attach($venues[$name]->id, ['is_accepted' => true]);
        }
        // Somebody else's night at Charlie is not this schedule's.
        $other = $this->createRole($this->owner, 'talent');
        $this->createEvent($other)->roles()->attach($venues['Charlie']->id, ['is_accepted' => true]);

        $ids = array_map(fn ($name) => UrlUtils::encodeId($venues[$name]->id), ['Bravo', 'Echo', 'Alpha']);
        $this->assertStringContainsString('recentVenueIds: '.json_encode($ids).',', $this->page($this->createUrl()));
    }

    public function test_a_short_venue_list_is_its_own_shortcut(): void
    {
        foreach (['Alpha', 'Bravo', 'Charlie'] as $name) {
            $venue = $this->createRole($this->owner, 'venue', ['name' => $name.' Hall']);
            $this->createEvent($this->role)->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        $this->assertStringContainsString('recentVenueIds: [],', $this->page($this->createUrl()));
    }

    public function test_someone_refused_the_ticket_setup_gets_the_event_tab_and_no_tickets(): void
    {
        $event = $this->createEvent($this->role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'SECRETTIER', 'price' => 25]);

        $curatorUser = $this->createOwner();
        $curator = $this->createRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $html = $this->page($this->editUrl($event, $curator), $curatorUser);

        $this->assertStringNotContainsString('id="section-tickets"', $html, 'sanity check: this user is refused the panel');
        $this->assertStringNotContainsString('ticket_choice_tickets', $html);
        $this->assertStringNotContainsString('SECRETTIER', $html);
        $this->assertStringContainsString('savedTicketTypes: 0,', $html, 'not even how many types there are');
        // The Event tab is theirs to use.
        $this->assertStringContainsString('id="event-basics"', $html);
        $this->assertStringContainsString('id="event-location"', $html);
    }

    public function test_a_later_event_lands_on_a_strip_with_its_link_and_what_it_lacks(): void
    {
        // The first event keeps its panel.
        $this->createEvent($this->role, ['user_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)->post(route('event.store', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Second Night',
            'starts_at' => now()->addDays(9)->format('Y-m-d').' 20:00:00',
            'duration' => 2,
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('first_event_created');
        $created = session('event_created');
        $this->assertIsArray($created);
        $this->assertSame('Second Night', $created['name']);
        $this->assertFalse($created['has_location']);
        $this->assertFalse($created['has_tickets']);
        $this->assertFalse($created['is_draft']);
        $this->assertNotEmpty($created['url']);

        $html = $this->actingAs($this->owner)->withSession(['event_created' => $created])
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $strip = $this->slice($html, 'id="event-created-strip"', '</script>');

        $this->assertStringContainsString(e(__('messages.event_created_on_schedule', ['name' => 'Second Night'])), $strip);
        $this->assertStringContainsString($created['edit_url'].'#section-venue', $strip);
        $this->assertStringContainsString($created['edit_url'].'#section-tickets', $strip);
        $this->assertStringContainsString('data-url="'.e($created['url']).'"', $strip);
    }

    public function test_the_strip_offers_nothing_an_event_already_has(): void
    {
        $this->createEvent($this->role, ['user_id' => $this->owner->id]);

        $this->actingAs($this->owner)->post(route('event.store', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Online Night',
            'starts_at' => now()->addDays(9)->format('Y-m-d').' 20:00:00',
            'duration' => 2,
            'event_url' => 'https://example.org/stream',
            'rsvp_enabled' => 1,
            'is_draft' => 1,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $created = session('event_created');
        $this->assertTrue($created['has_location'], 'an online link is a location');
        $this->assertTrue($created['has_tickets']);
        $this->assertTrue($created['is_draft']);
        $this->assertNull($created['url'], 'a draft has no public page to link to');

        $html = $this->actingAs($this->owner)->withSession(['event_created' => $created])
            ->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $strip = $this->slice($html, 'id="event-created-strip"', "\n</div>\n");

        $this->assertStringContainsString(e(__('messages.event_created_as_draft', ['name' => 'Online Night'])), $strip);
        $this->assertStringNotContainsString('#section-venue', $strip);
        $this->assertStringNotContainsString('#section-tickets', $strip);
        $this->assertStringNotContainsString('id="event-created-copy"', $strip);
    }

    public function test_a_first_event_saved_without_a_place_is_offered_one_first(): void
    {
        $this->actingAs($this->owner)->post(route('event.store', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Opening Night',
            'starts_at' => now()->addDays(9)->format('Y-m-d').' 20:00:00',
            'duration' => 2,
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('event_created');

        $first = session('first_event_created');
        $this->assertIsArray($first);
        $this->assertFalse($first['has_location']);
    }
}
