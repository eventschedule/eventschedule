<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PromoterLink;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleRefund;
use App\Services\PromoterLinks;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Promoter links (the schedule's Promoters tab, PromoterLinks): `?via=CODE` is remembered for the
 * schedule it belongs to, counted once a visit, and stamped on the sales it leads to, for the
 * schedule's OWN events only, and only while the schedule is Pro.
 */
class PromoterLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The test client's own "Symfony" agent is a crawler to PageView::isBot().
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15']);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/v1/oauth2/token')) {
                return Http::response(['access_token' => 'fake-token', 'expires_in' => 32400]);
            }
            if (str_contains($request->url(), '/v2/checkout/orders')) {
                return Http::response(['id' => 'ORDER123', 'status' => 'CREATED', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']]]);
            }

            return Http::response([], 404);
        });
    }

    /** @return array{0: Role, 1: Event, 2: PromoterLink} */
    private function setUpSchedule(array $roleAttrs = []): array
    {
        $owner = $this->createOwner();
        $owner->forceFill(['paypal_client_id' => 'client', 'paypal_client_secret' => 'secret', 'paypal_sandbox' => true])->save();
        $role = $this->createRole($owner, 'venue', $roleAttrs);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'paypal', 'ticket_currency_code' => 'USD', 'creator_role_id' => $role->id]);
        $link = PromoterLink::create(['role_id' => $role->id, 'name' => 'Sam Rivera', 'code' => 'sam', 'commission_percent' => 10]);

        return [$role, $event->fresh(), $link];
    }

    private function checkout(Role $role, Event $event, $ticket, array $extra = [])
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Dana Buyer',
            'email' => 'dana@guests.org',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ], $extra));
    }

    public function test_a_visit_through_the_link_is_counted_once_and_credited_with_the_sale(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk();
        $html = $this->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk()->getContent();
        $this->assertSame(1, $link->fresh()->clicks, 'one visit, one click');
        $this->assertNotNull($link->fresh()->first_click_at);
        $this->assertStringContainsString('<input type="hidden" name="via" value="sam">', $html);

        // The session alone carries it to the checkout.
        $this->checkout($role, $event, $ticket);

        $this->assertSame($link->id, Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));
    }

    public function test_the_form_field_carries_it_where_there_is_no_session(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->checkout($role, $event, $ticket, ['via' => 'sam']);

        $this->assertSame($link->id, Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));
    }

    public function test_a_code_that_is_not_this_schedules_or_is_archived_is_ignored(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        [$otherRole] = $this->setUpSchedule();
        $other = PromoterLink::create(['role_id' => $otherRole->id, 'name' => 'Lee', 'code' => 'lee']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->get($this->guestEventUrl($role, $event).'?via=lee')->assertOk();
        $this->assertSame(0, $other->fresh()->clicks);

        $link->forceFill(['archived_at' => now()])->save();
        $this->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk();
        $this->assertSame(0, $link->fresh()->clicks);

        $this->checkout($role, $event, $ticket, ['via' => 'sam']);
        $this->assertNull(Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));
    }

    public function test_a_crawler_is_not_counted(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk();

        $this->assertSame(0, $link->fresh()->clicks);
    }

    /** A curator's link earns nothing on an event it only lists: that event's money is not the curator's to see. */
    public function test_a_curator_link_is_not_credited_with_an_event_it_only_lists(): void
    {
        [$venue, $event] = $this->setUpSchedule();
        $curator = $this->createRole($this->createOwner(), 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        PromoterLink::create(['role_id' => $curator->id, 'name' => 'Kim', 'code' => 'kim']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]).'?via=kim')->assertOk();
        $this->checkout($venue, $event, $ticket, ['via' => 'kim']);

        $this->assertNull(Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));
    }

    public function test_a_free_plan_credits_nothing(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d')])->save();
        $this->assertFalse($role->fresh()->isPro());

        $this->assertNull(PromoterLinks::linkFor(request()->merge(['via' => 'sam']), $event->fresh()));
        $this->assertSame(0, $link->fresh()->clicks);
    }

    public function test_the_cookie_is_kept_only_with_marketing_consent(): void
    {
        [$role, $event] = $this->setUpSchedule();

        $this->get($this->guestEventUrl($role, $event).'?via=sam')->assertCookieMissing(PromoterLinks::COOKIE);

        $this->withUnencryptedCookie('cookie_consent', 'analytics.marketing.'.time())
            ->get($this->guestEventUrl($role, $event).'?via=sam')
            ->assertCookie(PromoterLinks::COOKIE);
    }

    public function test_the_stats_count_paid_tickets_net_of_refunds_and_donations(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 25, 'quantity' => 100]);
        $eur = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'paypal', 'ticket_currency_code' => 'EUR', 'creator_role_id' => $role->id]);
        $eurTicket = $this->createTicket($eur, ['price' => 30, 'quantity' => 100]);

        $this->createSale($event, $role, ['email' => 'a@guests.org', 'payment_amount' => 55, 'donation_amount' => 5, 'promoter_link_id' => $link->id], $ticket, 2);
        $refunded = $this->createSale($event, $role, ['email' => 'b@guests.org', 'payment_amount' => 25, 'promoter_link_id' => $link->id], $ticket, 1);
        SaleRefund::create(['sale_id' => $refunded->id, 'amount' => 10, 'currency_code' => 'USD', 'gateway' => 'paypal', 'status' => 'succeeded', 'idempotency_key' => 'k1']);
        $this->createSale($event, $role, ['email' => 'c@guests.org', 'payment_amount' => 25, 'status' => 'unpaid', 'promoter_link_id' => $link->id], $ticket, 1);
        $this->createSale($eur, $role, ['email' => 'd@guests.org', 'payment_amount' => 30, 'promoter_link_id' => $link->id], $eurTicket, 1);

        $stats = PromoterLinks::stats($role, collect([$link]))[$link->id];

        $this->assertSame(4, $stats['tickets'], 'paid only');
        $this->assertSame(['EUR' => 30.0, 'USD' => 65.0], collect($stats['revenue'])->sortKeys()->all(), '50 + (25 - 10) in dollars, the donation left out');
        $this->assertSame(['EUR' => 3.0, 'USD' => 6.5], collect($stats['commission'])->sortKeys()->all());
    }

    public function test_the_owner_adds_edits_and_archives_a_link_and_a_viewer_cannot(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $owner = $role->users()->first();

        $this->actingAs($owner)->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'Ana María', 'commission_percent' => '12.5'])
            ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'promoters']));
        $ana = PromoterLink::where('name', 'Ana María')->firstOrFail();
        $this->assertSame('ana-maria', $ana->code, 'made from the name');
        $this->assertSame(12.5, $ana->commission_percent);

        $this->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'Ana Maria'])->assertSessionHasNoErrors();
        $this->assertTrue(PromoterLink::where('code', 'ana-maria-2')->exists(), 'never the same code twice');

        $this->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'X', 'code' => 'sam'])->assertSessionHasErrors('code');
        $this->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'X', 'code' => 'no spaces'])->assertSessionHasErrors('code');

        // The code may change until somebody has followed the link.
        $hash = UrlUtils::encodeId($ana->id);
        $this->put(route('role.promoters.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['name' => 'Ana', 'code' => 'ana'])->assertSessionHasNoErrors();
        $this->assertSame('ana', $ana->fresh()->code);
        $ana->forceFill(['clicks' => 1, 'first_click_at' => now()])->save();
        $this->put(route('role.promoters.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['name' => 'Ana', 'code' => 'ana-r'])->assertSessionHasErrors('code');
        $this->assertSame('ana', $ana->fresh()->code);

        $this->post(route('role.promoters.archive', ['subdomain' => $role->subdomain, 'hash' => $hash]));
        $this->assertTrue($ana->fresh()->isArchived());

        // A viewer reads the calendar and nothing here.
        $viewer = $this->createOwner();
        $this->followRole($viewer, $role, 'viewer');
        $this->actingAs($viewer)->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'Nope'])->assertRedirect(route('home'));
        $this->assertFalse(PromoterLink::where('name', 'Nope')->exists());
        $this->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'promoters']))->assertRedirect();

        // Another schedule's link cannot be reached through this one.
        [$otherRole, , $otherLink] = $this->setUpSchedule();
        $this->actingAs($owner)->post(route('role.promoters.archive', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($otherLink->id)]))->assertNotFound();
        $this->assertFalse($otherLink->fresh()->isArchived());
    }

    public function test_a_free_schedule_cannot_add_a_link(): void
    {
        $role = $this->createFreeRole();
        $owner = $role->users()->first();
        $this->assertFalse($role->fresh()->isPro());

        $this->actingAs($owner)->post(route('role.promoters.store', ['subdomain' => $role->subdomain]), ['name' => 'Sam'])->assertSessionHas('error');
        $this->assertSame(0, PromoterLink::count());
    }

    public function test_the_tab_lists_each_link_with_what_it_brought_in(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 25, 'quantity' => 100]);
        $this->createSale($event, $role, ['email' => 'a@guests.org', 'payment_amount' => 50, 'promoter_link_id' => $link->id], $ticket, 2);
        PromoterLink::create(['role_id' => $role->id, 'name' => '{{ 7*7 }}', 'code' => 'curly']);

        $html = $this->actingAs($role->users()->first())
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'promoters']))
            ->assertOk()->getContent();

        $this->assertStringContainsString('Sam Rivera', $html);
        $this->assertStringContainsString('?via=sam', $html);
        $this->assertStringContainsString('<bdi dir="ltr">$50</bdi>', $html);
        $this->assertStringContainsString('<bdi dir="ltr">$5</bdi>', $html, 'ten percent commission');
        $this->assertStringContainsString('<span dir="auto" v-pre>{{ 7*7 }}</span>', $html, 'a name is never compiled by Vue');
        $this->assertStringContainsString(e($event->getGuestUrl($role->subdomain, null, true)), $html, 'the event is offered as a target');
    }

    public function test_the_sales_export_names_the_promoter(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 25, 'quantity' => 100]);
        $this->createSale($event, $role, ['email' => 'a@guests.org', 'payment_amount' => 25, 'promoter_link_id' => $link->id], $ticket, 1);

        $csv = $this->actingAs($role->users()->first())->get(route('sales.export'))->assertOk()->streamedContent();

        $this->assertStringContainsString('Promoter', strtok($csv, "\n"));
        $this->assertStringContainsString('Sam Rivera', $csv);
    }

    /**
     * A code is unique on its own schedule only. `?via=sam` on a curator's page is the curator's
     * sam: the owning venue's own sam, a link nobody followed, must not be credited.
     */
    public function test_a_code_on_another_schedules_page_never_credits_the_owners_link_of_that_name(): void
    {
        [$venue, $event, $venueSam] = $this->setUpSchedule();
        $curator = $this->createRole($this->createOwner(), 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $curatorSam = PromoterLink::create(['role_id' => $curator->id, 'name' => 'Curator Sam', 'code' => 'sam']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $html = $this->get($this->guestEventUrl($curator, $event).'?via=sam')->assertOk()->getContent();
        $this->assertSame(1, $curatorSam->fresh()->clicks, "the curator's own link is counted");
        $this->assertSame(0, $venueSam->fresh()->clicks);
        $this->assertStringNotContainsString('name="via"', $html);

        $this->checkout($curator, $event, $ticket);
        $this->assertNull(Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));
    }

    /** One form can post several events (a cart): its code belongs to the event it was printed for. */
    public function test_a_posted_code_counts_only_for_the_event_it_was_printed_for(): void
    {
        [$role, $event] = $this->setUpSchedule();
        $other = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'paypal', 'creator_role_id' => $role->id]);

        $request = Request::create('/checkout', 'POST', ['via' => 'sam', 'event_id' => UrlUtils::encodeId($event->id)]);
        $request->setLaravelSession(app('session.store'));

        $this->assertNotNull(PromoterLinks::linkFor($request, $event));
        $this->assertNull(PromoterLinks::linkFor($request, $other->fresh()));

        // A hand-made post with an array where the id goes is simply not a match.
        $crafted = Request::create('/checkout', 'POST', ['via' => 'sam', 'event_id' => ['x']]);
        $crafted->setLaravelSession(app('session.store'));
        $this->assertNull(PromoterLinks::linkFor($crafted, $event));
    }

    /** The cookie stands for a choice the visitor can take back: without consent it is not read. */
    public function test_the_cookie_is_not_read_without_consent(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $map = json_encode([$role->id => $link->id]);

        $this->withCookie(PromoterLinks::COOKIE, $map)->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id), 'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Dana Buyer', 'email' => 'dana@guests.org', 'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ]);
        $this->assertNull(Sale::where('email', 'dana@guests.org')->value('promoter_link_id'));

        $this->withCookie(PromoterLinks::COOKIE, $map)->withUnencryptedCookie('cookie_consent', 'analytics.marketing.'.time())
            ->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
                'event_id' => UrlUtils::encodeId($event->id), 'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
                'name' => 'Lee Buyer', 'email' => 'lee@guests.org', 'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
            ]);
        $this->assertSame($link->id, Sale::where('email', 'lee@guests.org')->value('promoter_link_id'));
    }

    /** The schedule's own team checking a link is not a click, and does not lock its code. */
    public function test_the_team_and_a_flood_from_one_address_are_not_counted(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();

        $this->actingAs($role->users()->first())->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk();
        $this->assertSame(0, $link->fresh()->clicks);
        $this->assertNull($link->fresh()->first_click_at);

        auth()->logout();
        for ($i = 0; $i < 12; $i++) {
            $this->flushSession();
            $this->get($this->guestEventUrl($role, $event).'?via=sam')->assertOk();
        }
        $this->assertSame(10, $link->fresh()->clicks, 'at most ten an hour from one address');
    }

    public function test_the_stats_take_a_refund_off_once_leave_add_ons_out_and_count_registrations(): void
    {
        [$role, $event, $link] = $this->setUpSchedule();
        $ticket = $this->createTicket($event, ['price' => 30, 'quantity' => 100]);
        $parking = $this->createTicket($event, ['price' => 5, 'quantity' => 100, 'is_addon' => true, 'type' => 'Parking']);

        // Three seats bought together, $60 of the $90 refunded on the first row.
        $primary = $this->createSale($event, $role, ['email' => 'a@guests.org', 'payment_amount' => 30, 'promoter_link_id' => $link->id], $ticket, 1);
        $primary->forceFill(['group_id' => $primary->id])->save();
        foreach (['b', 'c'] as $guest) {
            $this->createSale($event, $role, ['email' => "{$guest}@guests.org", 'payment_amount' => 30, 'group_id' => $primary->id, 'promoter_link_id' => $link->id], $ticket, 1);
        }
        SaleRefund::create(['sale_id' => $primary->id, 'amount' => 60, 'currency_code' => 'USD', 'gateway' => 'paypal', 'status' => 'succeeded', 'idempotency_key' => 'k2']);

        // One ticket and two parking add-ons.
        $withParking = $this->createSale($event, $role, ['email' => 'd@guests.org', 'payment_amount' => 40, 'promoter_link_id' => $link->id], $ticket, 1);
        $withParking->saleTickets()->create(['ticket_id' => $parking->id, 'quantity' => 2, 'seats' => json_encode([1 => null, 2 => null])]);

        // Two registrations for a free event.
        $free = $this->createEvent($role, ['rsvp_enabled' => true, 'creator_role_id' => $role->id, 'ticket_currency_code' => 'USD']);
        $this->createSale($free, $role, ['email' => 'e@guests.org', 'payment_method' => 'rsvp', 'promoter_link_id' => $link->id]);
        $this->createSale($free, $role, ['email' => 'f@guests.org', 'payment_method' => 'rsvp', 'promoter_link_id' => $link->id]);

        $stats = PromoterLinks::stats($role, collect([$link]))[$link->id];

        $this->assertSame(['USD' => 70.0], $stats['revenue'], '90 - 60 for the group, plus 40');

        // A gift card that paid a $20 ticket and its $5 donation leaves payment 0 and donation 5:
        // that row adds nothing, and takes nothing off the others.
        $this->createSale($event, $role, ['email' => 'g@guests.org', 'payment_amount' => 0, 'donation_amount' => 5, 'promoter_link_id' => $link->id], $ticket, 1);
        $this->assertSame(['USD' => 70.0], PromoterLinks::stats($role, collect([$link]))[$link->id]['revenue']);
        $this->assertSame(6, $stats['tickets'], 'three seats, one ticket (not its parking) and two guests');
    }

    public function test_a_name_is_kept_as_typed_and_a_lapsed_plan_changes_nothing(): void
    {
        [$role, , $link] = $this->setUpSchedule();
        $owner = $role->users()->first();
        $hash = UrlUtils::encodeId($link->id);

        $this->actingAs($owner)->put(route('role.promoters.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['name' => 'Sam <3 Events'])->assertSessionHasNoErrors();
        $this->assertSame('Sam <3 Events', $link->fresh()->name);

        config(['app.hosted' => true]);
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d')])->save();
        $this->put(route('role.promoters.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['name' => 'Renamed'])->assertSessionHas('error');
        $this->assertSame('Sam <3 Events', $link->fresh()->name);
        $this->post(route('role.promoters.archive', ['subdomain' => $role->subdomain, 'hash' => $hash]));
        $this->assertTrue($link->fresh()->isArchived(), 'tidying up stays open');
    }
}
