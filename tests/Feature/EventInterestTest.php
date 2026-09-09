<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Models\User;
use App\Utils\HoneypotUtils;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Per-event interest capture.
 *
 * The load-bearing assertions here are the ones about what the endpoint REFUSES. It is reachable
 * on every public event page with no account and no sold-out gate, unlike WaitlistController::join(),
 * so the date check, the visibility gate and the rate limits are the feature - the happy path is
 * the easy part.
 */
class EventInterestTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = $this->createRole($this->createOwner());

        // creator_role_id is NOT set by createEvent(), and without it Event::scheduleTimezone()
        // short-circuits on the null BelongsTo key and silently falls back to the app timezone -
        // no query, no error. resolveDate() and the eligibility gate both read it.
        $this->event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
    }

    private function joinUrl(): string
    {
        return route('event.interest.join', ['subdomain' => $this->role->subdomain]);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => $this->event->getStartDateTime(null, true, $this->event->scheduleTimezone())->format('Y-m-d'),
        ];
    }

    public function test_a_signed_out_visitor_is_captured_with_only_an_email(): void
    {
        $response = $this->postJson($this->joinUrl(), $this->payload());

        $response->assertOk()->assertJson(['success' => true]);

        $interest = EventInterest::first();
        $this->assertNotNull($interest);
        $this->assertSame('fan@fans.test', $interest->email);
        $this->assertSame($this->event->id, $interest->event_id);
        // Single opt-in: stamped on create, so the send commands can see it immediately. The
        // double-opt-in half belongs to the schedule-wide subscription, not to this.
        $this->assertTrue($interest->isConfirmed());
        $this->assertNotNull($interest->token);
        // No account, and no schedule-wide subscription: agreeing to hear about one event is not
        // agreeing to a mailing list.
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('role_subscribers', 0);
    }

    public function test_the_same_address_twice_reports_success_rather_than_erroring(): void
    {
        $this->postJson($this->joinUrl(), $this->payload())->assertOk()->assertJson(['success' => true]);
        $this->postJson($this->joinUrl(), $this->payload())->assertOk()->assertJson(['success' => true]);

        // The unique index caught it at 1062 and the controller reported success, which it genuinely
        // is. A distinct "already on the list" would make this endpoint a membership oracle.
        $this->assertSame(1, EventInterest::count());
    }

    public function test_a_date_the_event_does_not_occur_on_is_refused(): void
    {
        $response = $this->postJson($this->joinUrl(), $this->payload([
            'event_date' => now()->addYears(2)->format('Y-m-d'),
        ]));

        $response->assertOk()->assertJson(['success' => false]);

        // Without the matchesDate() check, one address x 365 dates is 365 rows on a public page,
        // because the unique key includes event_date. WaitlistController::join() takes this on
        // trust and is only safe because it is double-gated behind sold-out-and-Pro.
        $this->assertSame(0, EventInterest::count());
    }

    public function test_a_draft_event_is_not_capturable(): void
    {
        $this->event->forceFill(['is_draft' => true])->save();

        $this->postJson($this->joinUrl(), $this->payload())->assertNotFound();
        $this->assertSame(0, EventInterest::count());
    }

    public function test_an_unlisted_event_is_not_capturable(): void
    {
        $this->event->forceFill(['is_private' => true])->save();

        $this->postJson($this->joinUrl(), $this->payload())->assertNotFound();
        $this->assertSame(0, EventInterest::count());
    }

    public function test_a_cancelled_event_takes_no_new_signups(): void
    {
        $this->event->forceFill(['is_cancelled' => true])->save();

        $this->postJson($this->joinUrl(), $this->payload())->assertOk()->assertJson(['success' => false]);
        $this->assertSame(0, EventInterest::count());
    }

    public function test_an_event_on_another_schedule_is_refused(): void
    {
        $other = $this->createRole($this->createOwner());
        $foreign = $this->createEvent($other, ['creator_role_id' => $other->id]);

        $response = $this->postJson($this->joinUrl(), $this->payload([
            'event_id' => UrlUtils::encodeId($foreign->id),
        ]));

        $response->assertNotFound();
        $this->assertSame(0, EventInterest::count());
    }

    public function test_the_honeypot_bails_without_opening_the_ticket_modal(): void
    {
        $response = $this->post($this->joinUrl(), $this->payload([
            HoneypotUtils::FIELD => 'i am a bot',
        ]));

        $this->assertSame(0, EventInterest::count());

        // NOT session('error'), and no validation errors. event/show-guest.blade.php force-opens
        // the RSVP / ticket-purchase modal on `session('error') || $errors->any()`, so either would
        // throw the buy dialog at somebody who mistyped an address.
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $response->assertSessionHas('interest_error');
    }

    public function test_a_rejected_address_does_not_open_the_ticket_modal_either(): void
    {
        $response = $this->post($this->joinUrl(), $this->payload(['email' => 'not-an-address']));

        $this->assertSame(0, EventInterest::count());
        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $response->assertSessionHas('interest_error');
        // Repopulated under its own key: old('email') is shared with the ticket and RSVP forms on
        // the same page and would cross-fill them.
        $response->assertSessionHas('interest_email', 'not-an-address');
    }

    public function test_an_array_email_is_rejected_rather_than_fatal(): void
    {
        // input() hands back `email[]=x` untouched and this runs before the validator, so without
        // the is_string() guard `(string) []` raises "Array to string conversion", which
        // HandleExceptions promotes to a 500 on a public endpoint.
        $response = $this->postJson($this->joinUrl(), $this->payload(['email' => ['x']]));

        $response->assertOk()->assertJson(['success' => false]);
        $this->assertSame(0, EventInterest::count());
    }

    public function test_one_click_unsubscribe_deletes_the_row(): void
    {
        $this->postJson($this->joinUrl(), $this->payload())->assertOk();
        $interest = EventInterest::firstOrFail();

        // POST, not GET: a mutating GET is fetched by corporate mail scanners, which would
        // unsubscribe people who never clicked.
        $this->post('/int/u/'.$interest->token)->assertOk();

        // Deleted, not suppressed. The relationship is bounded by one event, so there is nothing
        // left to suppress - and a deletion is a real erasure rather than a retained address.
        $this->assertSame(0, EventInterest::count());
    }

    public function test_the_unsubscribe_get_shows_a_button_without_deleting(): void
    {
        $this->postJson($this->joinUrl(), $this->payload())->assertOk();
        $interest = EventInterest::firstOrFail();

        $this->get('/int/u/'.$interest->token)->assertOk();

        $this->assertSame(1, EventInterest::count());
    }

    public function test_a_signed_in_buyer_is_shown_the_audience_opt_in(): void
    {
        // The hole captureAudienceOptIn()'s docblock names: the box was wrapped in
        // `! auth()->check()`, so "every signed-in buyer is captured at zero". The POST path always
        // handled them - only the blade hid the control - so this has to be a RENDER assertion.
        $this->event->forceFill(['tickets_enabled' => true])->save();
        $this->createTicket($this->event, ['price' => 10]);

        $html = $this->actingAs($this->createOwner())
            ->get($this->event->getGuestUrl($this->role->subdomain))
            ->assertOk()
            ->getContent();

        // id="audience_opt_in" is the SINGLE-EVENT form's control. Asserting on name= alone
        // passed with this fix reverted, because partials/guest-cart.blade.php renders a checkbox
        // of the same name into the layout on every guest page - so the loose assertion pinned
        // nothing at all.
        // The exact unchecked markup of the SINGLE-EVENT control. Two things this assertion has to
        // be careful about, both of which produced a test that pinned nothing:
        //   - name= alone matches partials/guest-cart.blade.php's checkbox, which the layout
        //     renders on every guest page, so it passed with this fix reverted.
        //   - a bare assertStringNotContainsString('checked') matches the word anywhere in ~490KB
        //     of page, so it failed with the fix in place.
        $this->assertStringContainsString(
            'id="audience_opt_in" name="audience_opt_in" type="checkbox" value="1"',
            $html
        );
        // Still unchecked: who SEES the box is a visibility question, whether it is pre-ticked is a
        // consent one, and GDPR Art. 4(11) wants an affirmative act.
        $this->assertStringNotContainsString(
            'id="audience_opt_in" name="audience_opt_in" type="checkbox" value="1" checked',
            $html
        );
    }

    public function test_the_opt_in_is_still_hidden_in_an_embed(): void
    {
        // An embed is somebody else's page. The embed guard is the half of the old condition that
        // stays.
        $this->event->forceFill(['tickets_enabled' => true])->save();
        $this->createTicket($this->event, ['price' => 10]);

        $html = $this->get($this->event->getGuestUrl($this->role->subdomain).'?embed=true')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('id="audience_opt_in"', $html);
    }

    public function test_the_dashboard_next_step_names_the_people_waiting(): void
    {
        // The organizer-facing half. It enriches the EXISTING next_step_tickets row rather than
        // adding a step type: HomeController's branch 1 already fires on this exact population
        // (something upcoming, no way to buy) and then continues, so a new type could never reach
        // them. A generic "add a ticket type" cannot say anyone is waiting; this can.
        $owner = User::find($this->role->user_id);

        $this->postJson($this->joinUrl(), $this->payload())->assertOk();

        $html = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('1 person is waiting to buy', $html);
    }

    public function test_the_next_step_keeps_its_generic_copy_with_nobody_waiting(): void
    {
        $owner = User::find($this->role->user_id);

        $html = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('is waiting to buy', $html);
        $this->assertStringNotContainsString('are waiting to buy', $html);
    }

    public function test_capture_works_on_a_monthly_recurring_event(): void
    {
        // Every fixture in this file was non-recurring, which is why the monthly case shipped
        // broken. The guest page backfills $date for a recurring event; that backfill used to read
        // days_of_week alone, and EventRepo writes '1111111' for monthly_date, so it handed back
        // TODAY - which resolveDate()'s matchesDate() check then rejected. The visitor got
        // "invalid request", the same string a honeypot trip produces, every single time.
        $anchorDay = now()->day === 15 ? 20 : 15;
        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => now()->subMonths(3)->day($anchorDay)->setTime(19, 0)->format('Y-m-d H:i:s'),
            'recurring_frequency' => 'monthly_date',
        ]);

        // Exactly what the rendered form posts.
        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();
        preg_match('/name="event_date" value="([^"]*)"/', $html, $m);
        $submitted = $m[1] ?? '';
        $this->assertNotEmpty($submitted);

        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $submitted,
        ])->assertOk()->assertJson(['success' => true]);

        $interest = EventInterest::firstOrFail();
        $this->assertSame($submitted, $interest->event_date);
        $this->assertSame($anchorDay, (int) date('j', strtotime($interest->event_date)));
    }

    public function test_a_ticketed_event_offers_the_quiet_link_beside_the_buy_button(): void
    {
        // Moment B. The plan called for it, the commit message claimed it, and it did not exist -
        // so on a ticketed event there was no entry point to the card at all, because the
        // Add-to-Calendar menu that carries the other two links only renders when there is NO
        // primary CTA.
        $this->event->forceFill(['tickets_enabled' => true])->save();
        $this->createTicket($this->event, ['price' => 10]);

        $html = $this->get($this->event->getGuestUrl($this->role->subdomain))->assertOk()->getContent();

        $this->assertStringContainsString('href="#event-interest"', $html);
        $this->assertStringContainsString(__('messages.event_interest_not_ready'), $html);
        // And the thing it points at is actually on the page.
        $this->assertStringContainsString('id="event-interest"', $html);
    }

    public function test_a_cancelled_event_offers_no_dead_interest_links(): void
    {
        // The anchors and the card used to be gated separately: the mobile Add-to-Calendar sheet
        // renders on a cancelled event (canSellTickets/canAcceptRsvp are false), while the card
        // refused - so the menu offered "Tell me when tickets go on sale" and clicking did nothing.
        $this->event->forceFill(['is_cancelled' => true])->save();

        $html = $this->get($this->event->getGuestUrl($this->role->subdomain))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="event-interest"', $html, 'the card must not render');
        $this->assertStringNotContainsString('href="#event-interest"', $html, 'and nothing may link to it');
    }

    public function test_a_past_event_offers_no_dead_interest_links(): void
    {
        $this->event->forceFill([
            'starts_at' => now()->subMonth()->format('Y-m-d H:i:s'),
        ])->save();

        $html = $this->get($this->event->getGuestUrl($this->role->subdomain))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="event-interest"', $html);
        $this->assertStringNotContainsString('href="#event-interest"', $html);
    }

    public function test_a_past_occurrence_cannot_be_captured_by_a_direct_post(): void
    {
        // The view refuses to render the form on a past event; the controller used to accept one
        // anyway. Such a row can never produce a send and would sit in the organizer's demand count
        // for ever.
        $this->event->forceFill(['starts_at' => now()->subMonth()->format('Y-m-d H:i:s')])->save();
        $this->event->refresh();

        $this->postJson($this->joinUrl(), $this->payload([
            'event_date' => $this->event->getStartDateTime(null, true, $this->event->scheduleTimezone())->format('Y-m-d'),
        ]))->assertOk()->assertJson(['success' => false]);

        $this->assertSame(0, EventInterest::count());
    }

    public function test_an_event_hidden_from_discovery_cannot_be_captured(): void
    {
        $this->event->forceFill(['is_hidden_from_discovery' => true])->save();

        $this->postJson($this->joinUrl(), $this->payload())->assertOk()->assertJson(['success' => false]);

        $this->assertSame(0, EventInterest::count());
    }
}
