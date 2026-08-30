<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\EventTextGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The External ticket mode carries a display-only coupon code. The discount says what that
 * code is worth, so the event page can read "SAVE20 - 15% off" instead of sending guests to
 * the external platform to find out.
 *
 * Nothing redeems the value, which is exactly why it needs pinning: the only thing stopping
 * "150% off" or "15.000%" from rendering as fact is the validation rule and the accessor.
 */
class EventCouponDiscountTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue', ['timezone' => 'UTC']);
    }

    private function externalEvent(array $attrs = []): Event
    {
        return $this->createEvent($this->role, array_merge([
            'creator_role_id' => $this->role->id,
            'registration_url' => 'https://tickets.example.org/show',
            'ticket_price' => 48,
            'ticket_currency_code' => 'USD',
        ], $attrs));
    }

    private function guestHtml(Event $event): string
    {
        return $this->get(route('event.view_guest', [
            'subdomain' => $this->role->subdomain,
            'slug' => $event->slug,
        ]))->assertOk()->getContent();
    }

    public function test_a_percentage_discount_round_trips_through_the_event_form(): void
    {
        $event = $this->externalEvent();

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_code' => 'SAVE20',
            'coupon_discount' => '15',
            'coupon_discount_type' => 'percentage',
        ])->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('percentage', $event->coupon_discount_type);
        $this->assertEquals(15, (float) $event->coupon_discount);
        $this->assertSame('15%', $event->formatted_coupon_discount);
    }

    public function test_a_fixed_discount_round_trips_and_renders_in_the_event_currency(): void
    {
        $event = $this->externalEvent(['ticket_currency_code' => 'EUR']);

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_code' => 'TENOFF',
            'coupon_discount' => '10',
            'coupon_discount_type' => 'fixed',
        ])->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('fixed', $event->coupon_discount_type);
        // Whatever the symbol is, it must be the row's currency and never a hardcoded '$'.
        $this->assertSame(
            \App\Utils\MoneyUtils::format($event->coupon_discount, 'EUR'),
            $event->formatted_coupon_discount
        );
        $this->assertStringNotContainsString('%', $event->formatted_coupon_discount);
    }

    /**
     * The column is decimal(13,3), so the stored value reads back as "15.000". Rendering that
     * verbatim would put "15.000% off" on the event page.
     */
    public function test_a_percentage_drops_the_stored_trailing_zeros(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ]);

        $this->assertSame('15%', $event->fresh()->formatted_coupon_discount);
    }

    public function test_a_fractional_percentage_keeps_its_decimal(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 12.5,
            'coupon_discount_type' => 'percentage',
        ]);

        $this->assertSame('12.5%', $event->fresh()->formatted_coupon_discount);
    }

    /** A zero discount is noise, not information - it must not render as "0% off". */
    public function test_a_zero_discount_renders_as_nothing(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 0,
            'coupon_discount_type' => 'percentage',
        ]);

        $this->assertSame('', $event->fresh()->formatted_coupon_discount);
        $this->assertSame('', $event->fresh()->couponDiscountLabel());
    }

    public function test_a_percentage_over_one_hundred_is_rejected(): void
    {
        $event = $this->externalEvent();

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_discount' => '150',
            'coupon_discount_type' => 'percentage',
        ])->assertSessionHasErrors('coupon_discount');

        $this->assertNull($event->fresh()->coupon_discount);
    }

    /** The same ceiling must not apply to a fixed amount - 150 EUR off is legitimate. */
    public function test_a_fixed_amount_over_one_hundred_is_accepted(): void
    {
        $event = $this->externalEvent();

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_discount' => '150',
            'coupon_discount_type' => 'fixed',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(150, (float) $event->fresh()->coupon_discount);
    }

    public function test_a_non_numeric_discount_is_rejected(): void
    {
        $event = $this->externalEvent();

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_discount' => 'free stuff',
        ])->assertSessionHasErrors('coupon_discount');
    }

    public function test_the_guest_page_shows_the_discount_beside_the_coupon_code(): void
    {
        $event = $this->externalEvent([
            'coupon_code' => 'SAVE20',
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ]);

        $html = $this->guestHtml($event);

        $this->assertStringContainsString('SAVE20', $html);
        $this->assertStringContainsString(__('messages.discount_off', ['amount' => '15%']), $html);
    }

    /** Either half stands alone: a bare "15% off" is as useful to a guest as a bare code. */
    public function test_the_guest_page_shows_a_discount_with_no_coupon_code(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ]);

        $this->assertStringContainsString(
            __('messages.discount_off', ['amount' => '15%']),
            $this->guestHtml($event)
        );
    }

    public function test_the_guest_page_shows_nothing_when_there_is_no_discount(): void
    {
        $event = $this->externalEvent(['coupon_code' => 'SAVE20']);

        $this->assertStringNotContainsString(
            __('messages.discount_off', ['amount' => '15%']),
            $this->guestHtml($event)
        );
    }

    public function test_the_coupon_discount_token_substitutes_in_a_text_template(): void
    {
        $event = $this->externalEvent([
            'coupon_code' => 'SAVE20',
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ])->fresh();

        $this->assertSame(
            'Use SAVE20 for 15% off',
            EventTextGenerator::parseTemplate(
                'Use {coupon_code} for {coupon_discount} off',
                $event,
                $this->role,
                false
            )
        );
    }

    public function test_the_coupon_discount_token_resolves_to_nothing_when_unset(): void
    {
        $event = $this->externalEvent()->fresh();

        $this->assertSame(
            'Discount:',
            trim(EventTextGenerator::parseTemplate('Discount: {coupon_discount}', $event, $this->role, false))
        );
    }

    /**
     * The AP form is Vue. A select bound to null renders blank, and decimal(13,3) serializes
     * as the string "15.000" - neither is a usable starting value, so pin the seeding.
     */
    public function test_the_edit_form_seeds_both_fields_in_a_usable_shape(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 15,
            'coupon_discount_type' => 'fixed',
        ]);

        $html = $this->actingAs($this->owner)->get(route('event.edit', [
            'subdomain' => $this->role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($event->id),
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('name="coupon_discount_type"', $html);
        $this->assertStringContainsString('name="coupon_discount"', $html);
        $this->assertStringContainsString('coupon_discount_type: "fixed"', $html);
        $this->assertStringContainsString('coupon_discount: 15,', $html);
        $this->assertStringNotContainsString('coupon_discount: "15.000"', $html);
    }

    /**
     * A brand new event must not open with a blank type select, and a row that predates the
     * column must open on the amount rather than on the percentage it never chose.
     */
    public function test_the_edit_form_defaults_an_unset_type_to_a_fixed_amount(): void
    {
        $html = $this->actingAs($this->owner)->get(route('event.edit', [
            'subdomain' => $this->role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($this->externalEvent()->id),
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('coupon_discount_type: "fixed"', $html);
    }

    /**
     * The guest-submit form seeds its type entirely client-side, so nothing on the AP side
     * pins it. The page is a plain GET that renders the seed server-side, which is also the
     * only way to catch a broken @json here: the directive does not error, it silently kills
     * the Vue mount and leaves an unusable page.
     */
    public function test_the_guest_submit_form_seeds_the_type_to_a_fixed_amount(): void
    {
        // showGuestSubmit() 404s without accept_requests and redirects without require_account.
        $curator = $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
        ]);

        $html = $this->get(route('event.guest_submit', ['subdomain' => $curator->subdomain]))
            ->assertOk()->getContent();

        // The blank-form default and the post-submit reset are separate seeds in that file.
        $this->assertStringContainsString('coupon_discount_type: "fixed"', $html);
        $this->assertStringContainsString('this.event.coupon_discount_type = "fixed"', $html);
    }

    /** The AI import preview seeds the type onto every parsed row that arrives without one. */
    public function test_the_ai_import_form_seeds_the_type_to_a_fixed_amount(): void
    {
        // Without a key the page collapses to the setup guide and the seed never renders at
        // all - so this assertion would pass locally and fail in CI.
        config(['services.google.gemini_key' => 'test-key']);

        $html = $this->actingAs($this->owner)
            ->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain]))
            ->assertOk()->getContent();

        // e(), not json_encode(), matching the currency default seeded beside it - so the
        // rendered value carries single quotes rather than the double quotes above.
        $this->assertStringContainsString("event.coupon_discount_type = 'fixed'", $html);
    }

    /**
     * The row a type-less client write still produces: an amount with no type. It has to read
     * as money, which is what the accessor's fallback decides - the old `?? 'percentage'`
     * made "150% off" of exactly this shape.
     */
    public function test_a_stored_amount_with_no_type_renders_as_money(): void
    {
        $event = $this->externalEvent(['coupon_discount' => 15]);

        $this->assertNull($event->coupon_discount_type);
        $this->assertSame(
            \App\Utils\MoneyUtils::format($event->coupon_discount, 'USD'),
            $event->formatted_coupon_discount
        );
        $this->assertStringNotContainsString('%', $event->formatted_coupon_discount);
    }

    /**
     * The gap the ceiling used to have. saveEvent()'s fill() leaves an omitted
     * coupon_discount_type alone, so a partial update that sends only the amount is rendered
     * under the type ALREADY on the row - and 150 under a stored 'percentage' is "150% off"
     * as fact. The rule has to read the stored type, not just the submitted one.
     */
    public function test_a_partial_update_cannot_put_an_over_hundred_value_on_a_percentage_event(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 10,
            'coupon_discount_type' => 'percentage',
        ]);

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_discount' => '150',
        ])->assertSessionHasErrors('coupon_discount');

        $this->assertEquals(10, (float) $event->fresh()->coupon_discount);
    }

    /** The tightened rule must not start rejecting a legitimate large fixed amount. */
    public function test_a_partial_update_of_a_fixed_amount_event_still_accepts_a_large_value(): void
    {
        $event = $this->externalEvent([
            'coupon_discount' => 10,
            'coupon_discount_type' => 'fixed',
        ]);

        $this->putUpdateEvent($this->owner, $this->role, $event, [
            'coupon_discount' => '150',
        ])->assertSessionHasNoErrors();

        // Also pins the fill() behaviour the rule above has to compensate for: the omitted
        // type key leaves the stored 'fixed' in place rather than resetting it.
        $this->assertEquals(150, (float) $event->fresh()->coupon_discount);
        $this->assertSame('fixed', $event->fresh()->coupon_discount_type);
    }

    /**
     * The guest schedule page loads its events over Ajax, so CalendarDataTrait - not the
     * blade's server-rendered twin - is what actually feeds the cards and the popup.
     */
    public function test_the_calendar_payload_carries_the_discount_label(): void
    {
        $this->externalEvent([
            'coupon_code' => 'SAVE20',
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ]);

        $payload = json_encode($this->getJson(route('role.calendar_events', [
            'subdomain' => $this->role->subdomain,
            'month' => now()->addDays(7)->month,
            'year' => now()->addDays(7)->year,
        ]))->assertOk()->json());

        $this->assertStringContainsString('coupon_discount_label', $payload);
        $this->assertStringContainsString(__('messages.discount_off', ['amount' => '15%']), $payload);
    }

    /** Password-protected events redact the coupon; the discount must not leak past it. */
    public function test_the_calendar_payload_redacts_the_discount_for_a_locked_event(): void
    {
        $this->externalEvent([
            'coupon_code' => 'SAVE20',
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
            'event_password' => 'letmein',
        ]);

        $payload = json_encode($this->getJson(route('role.calendar_events', [
            'subdomain' => $this->role->subdomain,
            'month' => now()->addDays(7)->month,
            'year' => now()->addDays(7)->year,
        ]))->assertOk()->json());

        $this->assertStringNotContainsString('SAVE20', $payload);
        $this->assertStringNotContainsString(__('messages.discount_off', ['amount' => '15%']), $payload);
    }

    /**
     * The shape this was built for: the discounted price with the old one beside it. Both
     * tokens have to reach parseTemplate(), so this also pins the replacement map wiring.
     */
    public function test_the_price_tokens_render_a_before_and_after_pair(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_code' => 'SAVE30',
            'coupon_discount' => 30,
            'coupon_discount_type' => 'fixed',
        ])->fresh();

        $this->assertSame(
            '119 (149)',
            EventTextGenerator::parseTemplate(
                '{discounted_price} ({original_price})',
                $event,
                $this->role,
                false
            )
        );

        // {price} stays the LIST price on a couponed event. Every template written before
        // this feature uses it, so making it discount-aware would silently change what they
        // all advertise - and nothing else in the suite would notice.
        $this->assertSame(
            'price 149',
            EventTextGenerator::parseTemplate('price {price}', $event, $this->role, false)
        );
    }

    /**
     * A percentage rarely divides evenly. The result must land on the currency's own
     * precision rather than a raw float, which would render 149 less 15% as '126.65000001'.
     */
    public function test_a_percentage_discount_rounds_to_the_currency_decimals(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ])->fresh();

        $this->assertSame('126.65', $event->discounted_price);
        $this->assertSame('149', $event->original_price);
    }

    /**
     * A whole price must not render with a stray single decimal. 149 less 20% is 119.20,
     * and a raw float cast would print '119.2', which no price ever reads as.
     */
    public function test_a_fractional_result_keeps_both_decimals(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 20,
            'coupon_discount_type' => 'percentage',
        ])->fresh();

        $this->assertSame('119.20', $event->discounted_price);
    }

    /**
     * The pair is for the comparison, so it goes blank together. {price} is the
     * unconditional one and must be untouched by any of this.
     */
    public function test_the_price_tokens_are_blank_without_a_discount(): void
    {
        $event = $this->externalEvent(['ticket_price' => 149, 'coupon_code' => 'SAVE30'])->fresh();

        $this->assertSame('', $event->discounted_price);
        $this->assertSame('', $event->original_price);
        $this->assertSame(
            'price 149',
            EventTextGenerator::parseTemplate('price {price}', $event, $this->role, false)
        );
    }

    /** No price means there is nothing for a discount to come off. */
    public function test_the_price_tokens_are_blank_on_a_free_event(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 0,
            'coupon_discount' => 30,
            'coupon_discount_type' => 'fixed',
        ])->fresh();

        $this->assertSame('', $event->discounted_price);
        $this->assertSame('', $event->original_price);
    }

    /**
     * A zero-decimal currency must not gain '.00'. The thousands separator still applies,
     * matching every other price the app prints.
     */
    public function test_a_zero_decimal_currency_drops_the_decimals(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 5000,
            'ticket_currency_code' => 'JPY',
            'coupon_discount' => 15,
            'coupon_discount_type' => 'percentage',
        ])->fresh();

        $this->assertSame('4,250', $event->discounted_price);
        $this->assertSame('5,000', $event->original_price);
    }

    /** A discount worth more than the ticket cannot produce a negative price. */
    public function test_a_discount_larger_than_the_price_clamps_to_zero(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 200,
            'coupon_discount_type' => 'fixed',
        ])->fresh();

        $this->assertSame('0', $event->discounted_price);
    }

    /**
     * The regression that matters most. coupon_discount_type is nullable with no backfill,
     * and DEFAULT_COUPON_DISCOUNT_TYPE resolves a missing one to 'fixed'. If the subtraction
     * resolved it differently from getFormattedCouponDiscountAttribute(), one token would
     * say '$30 off' while the other took 30 percent - both on the same line of the same post.
     */
    public function test_an_untyped_discount_is_subtracted_as_money_not_a_percentage(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 30,
            'coupon_discount_type' => null,
        ])->fresh();

        $this->assertSame(\App\Utils\MoneyUtils::format(30, 'USD'), $event->formatted_coupon_discount);
        $this->assertSame('119', $event->discounted_price);
        // 149 less 30 PERCENT would be 104.30. That is the drift this guards against.
        $this->assertNotSame('104.30', $event->discounted_price);
    }

    /**
     * The flyer overlay keeps its own replacement map, separate from the text template's.
     * A token missing from it is invisible to every other test and shows up only as a
     * literal '{discounted_price}' rasterised onto the artwork.
     */
    public function test_the_flyer_overlay_substitutes_the_price_tokens(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('Event graphics need the GD extension.');
        }

        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 30,
            'coupon_discount_type' => 'fixed',
        ])->fresh();

        $design = new \App\Services\designs\ListDesign($this->role, collect([$event]));

        $parse = new \ReflectionMethod($design, 'parseOverlayText');
        $parse->setAccessible(true);

        $this->assertSame(
            '119 (149)',
            $parse->invoke($design, '{discounted_price} ({original_price})', $event)
        );
    }

    /**
     * The coupon points at an outside ticket platform and nothing here redeems it. The form
     * hides the field behind v-show when tickets are switched on, which still submits, and
     * nothing on the save path clears it - so a stale discount outlives the switch.
     *
     * Quoting a price off that stale value puts a number on a flyer that our own checkout
     * will never charge: the internal path discounts through promo_codes, which knows nothing
     * about these columns. The guest page already gates its coupon block the same way, so
     * without this the graphic and the event page would contradict each other.
     */
    public function test_the_price_tokens_go_quiet_once_the_event_sells_tickets_itself(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_code' => 'SAVE30',
            'coupon_discount' => 30,
            'coupon_discount_type' => 'fixed',
            'tickets_enabled' => true,
        ]);

        // The ticket row is what makes this bite. getPrice() checks $event->tickets BEFORE
        // it looks at tickets_enabled, so without a row it returns '' on its own and this
        // test would pass with the gate deleted.
        $this->createTicket($event, ['price' => 149]);
        $event = $event->fresh();

        $this->assertSame(149.0, (float) EventTextGenerator::getPrice($event), 'fixture must have a price to suppress');
        $this->assertSame('', $event->discounted_price);
        $this->assertSame('', $event->original_price);
    }

    /** Same reasoning for an RSVP event: no money changes hands, so no coupon price. */
    public function test_the_price_tokens_go_quiet_on_an_rsvp_event(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 149,
            'coupon_discount' => 30,
            'coupon_discount_type' => 'fixed',
            'rsvp_enabled' => true,
        ])->fresh();

        $this->assertSame('', $event->discounted_price);
        $this->assertSame('', $event->original_price);
    }

    /**
     * A zero-decimal currency whose discount does NOT divide evenly - the only case that
     * actually exercises round($v, 0). 4999 less 33% is 3349.33.
     */
    public function test_a_zero_decimal_currency_rounds_a_fractional_result(): void
    {
        $event = $this->externalEvent([
            'ticket_price' => 4999,
            'ticket_currency_code' => 'JPY',
            'coupon_discount' => 33,
            'coupon_discount_type' => 'percentage',
        ])->fresh();

        $this->assertSame('3,349', $event->discounted_price);
    }
}
