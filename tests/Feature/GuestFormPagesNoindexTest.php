<?php

namespace Tests\Feature;

use App\Models\GiftCard;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest pages that are forms, or that sit on a secret URL, answer noindex.
 *
 * They all render through the guest layout, which says "index, follow" for any schedule that
 * passes Role::isIndexableHost() - so every schedule's submit form, import form, booking-request
 * form, gift-card shop, ride board and password gate was an indexable page, and a gift card's
 * view page, whose URL carries its secret, was one a crawler could be handed.
 *
 * The appointment booking pages are the deliberate exception: a schedule's /book is its service
 * menu, and AppointmentRescheduleTest pins that it stays indexable.
 */
class GuestFormPagesNoindexTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function robotsOf(string $url): ?string
    {
        $html = $this->get($url)->assertOk()->getContent();

        return preg_match('/<meta name="robots" content="([^"]*)"/', $html, $m) ? $m[1] : null;
    }

    /** "index, follow" and whatever directives follow it. */
    private function assertIndexable(?string $robots, string $message = ''): void
    {
        $this->assertMatchesRegularExpression('/^index, follow\b/', (string) $robots, $message);
    }

    public function test_the_form_and_secret_pages_are_noindex(): void
    {
        // The import form renders only with an AI key; a GET makes no AI call.
        config(['services.google.gemini_key' => 'test-key']);

        $submits = $this->createCurator($this->createOwner(), ['accept_requests' => true, 'require_account' => true]);
        $imports = $this->createRole($this->createOwner(), 'venue', ['accept_requests' => true, 'require_account' => false]);
        $books = $this->createRole($this->createOwner(), 'talent', [
            'accept_requests' => true,
            'require_account' => false,
            'require_approval' => true,
            'event_request_form' => 'booking',
        ]);
        $shop = $this->giftCardSchedule();
        $card = $this->giftCard($shop);

        $rides = $this->createRole($this->createOwner(), 'talent', ['carpool_enabled' => true]);
        $ride = $this->createEvent($rides, ['creator_role_id' => $rides->id]);

        $locked = $this->createRole($this->createOwner(), 'venue');
        $gated = $this->createEvent($locked, ['creator_role_id' => $locked->id, 'event_password' => 'letmein']);

        $pages = [
            'guest submit' => route('event.guest_submit', ['subdomain' => $submits->subdomain]),
            'guest import' => route('event.guest_import', ['subdomain' => $imports->subdomain]),
            'booking request' => route('event.booking_request', ['subdomain' => $books->subdomain]),
            'gift card shop' => route('gift_card.purchase', ['subdomain' => $shop->subdomain]),
            'gift card view' => route('gift_card.view', ['gift_card_id' => UrlUtils::encodeId($card->id), 'secret' => $card->secret]),
            'carpool' => route('carpool.index', ['subdomain' => $rides->subdomain, 'event_hash' => UrlUtils::encodeId($ride->id)]),
            'password gate' => $this->guestEventUrl($locked, $gated),
        ];

        foreach ($pages as $label => $url) {
            $this->assertSame('noindex, nofollow', $this->robotsOf($url), $label);
        }

        // The control: every one of these schedules is indexable, so the layout's schedule rule is
        // not what put noindex on the pages above.
        foreach ([$submits, $imports, $books, $shop, $rides, $locked] as $role) {
            $this->assertTrue($role->fresh()->isIndexableHost());
            $this->assertIndexable($this->robotsOf(route('role.view_guest', ['subdomain' => $role->subdomain])));
        }
    }

    /** A schedule's /book is its service menu, and stays indexable. */
    public function test_the_appointment_booking_pages_stay_indexable(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $first = $this->createAppointmentType($role, ['name' => 'Intro Call']);
        $this->createAppointmentType($role, ['name' => 'Full Session']);

        $this->assertIndexable($this->robotsOf(route('appointments.book', ['subdomain' => $role->subdomain])));
        $this->assertIndexable($this->robotsOf(route('appointments.book_type', [
            'subdomain' => $role->subdomain,
            'typeSlug' => $first->slug,
        ])));
    }

    /** Gift cards on, with the email channel hosted mode requires to deliver one. */
    private function giftCardSchedule(): Role
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'gift_cards_enabled' => true,
            'gift_card_amounts' => [25, 50],
            'gift_card_currency_code' => 'USD',
            'gift_card_valid_days' => 365,
            'gift_card_payment_method' => 'cash',
        ]);
        $role->email_settings = [
            'host' => 'smtp.test.dev',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'mailer',
            'password' => 'secret',
            'from_address' => 'events@schedule.dev',
            'from_name' => 'Test Schedule',
        ];
        $role->save();

        return $role->fresh();
    }

    private function giftCard(Role $role): GiftCard
    {
        $card = new GiftCard;
        $card->role_id = $role->id;
        $card->code = GiftCard::generateCode();
        $card->secret = strtolower(Str::random(32));
        $card->amount = 50;
        $card->remaining_amount = 50;
        $card->currency_code = 'USD';
        $card->status = 'active';
        $card->payment_method = 'cash';
        $card->purchaser_name = 'Alice Buyer';
        $card->purchaser_email = 'alice@test.dev';
        $card->recipient_name = 'Bob Recipient';
        $card->recipient_email = 'bob@test.dev';
        $card->activated_at = now();
        $card->save();

        return $card->fresh();
    }
}
