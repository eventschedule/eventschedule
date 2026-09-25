<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\GiftCard;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An appointment booking is no public event.
 *
 * A booking is an unlisted event named after its guest ("Intro Chat - Jane Private"), with their
 * notes as its description. The guest manages it through the booking's own secret link, and no
 * mail, page or script sends anybody to its public event page - but that page answered anybody
 * holding the link, and event ids are sqids, not secrets: the page, its dated URL, the photo
 * gallery and the ticket embed all showed the guest's name, and the password form and the poll
 * endpoint answered it differently from an event that does not exist.
 *
 * Every guest surface now answers a booking exactly as it answers an unknown event, for everybody
 * but the schedule's own members and admins. The lists never showed one.
 */
class AppointmentPrivacyTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const GUEST = 'Jane Private';

    private User $owner;

    private Role $role;

    private Event $booking;

    private Sale $sale;

    private string $ticketHash;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['name' => 'Harbour Coaching', 'carpool_enabled' => true]);

        // Booked the way a guest books: through the booking page, on a type open every day.
        $windows = array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '00:00', 'end' => '23:59']]);
        $type = $this->createAppointmentType($this->role, ['name' => 'Intro Chat', 'weekly_windows' => $windows]);
        $days = $this->getJson(route('appointments.slots', ['subdomain' => $this->role->subdomain, 'typeSlug' => $type->slug]))
            ->assertOk()->json('days');
        $slot = collect($days)->flatten(1)->first();

        $this->postJson(route('appointments.book.store', ['subdomain' => $this->role->subdomain, 'typeSlug' => $type->slug]), [
            'name' => self::GUEST,
            'email' => 'jane@gmail.com',
            'slot' => is_array($slot) ? ($slot['start'] ?? reset($slot)) : $slot,
            'guest_timezone' => 'America/New_York',
            'notes' => 'Private notes about my situation',
        ])->assertOk();

        $this->booking = Event::where('appointment_type_id', $type->id)->sole();
        $this->sale = Sale::where('event_id', $this->booking->id)->sole();

        $this->assertStringContainsString(self::GUEST, $this->booking->name, 'fixture: a booking is named after its guest');
        $this->assertTrue((bool) $this->booking->is_private, 'fixture: a booking is saved unlisted');

        // A real code for each validator, so a booking answering them would show. Each one asks
        // for a price and a currency, as a priced appointment type's booking carries.
        $ticket = Ticket::where('event_id', $this->booking->id)->sole();
        $ticket->update(['price' => 40]);
        Event::whereKey($this->booking->id)->update(['ticket_currency_code' => 'USD']);
        $this->booking->refresh();
        $this->ticketHash = UrlUtils::encodeId($ticket->id);

        PromoCode::create(['event_id' => $this->booking->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);
        $card = new GiftCard;
        $card->role_id = $this->role->id;
        // Stored the way it is matched: GiftCard::normalizeCode() keeps letters and digits only.
        $card->code = 'GIFTPROBE1';
        $card->secret = strtolower(Str::random(32));
        $card->amount = 50;
        $card->remaining_amount = 50;
        $card->currency_code = 'USD';
        $card->status = 'active';
        $card->payment_method = 'cash';
        $card->purchaser_name = 'A Friend';
        $card->purchaser_email = 'friend@gmail.com';
        $card->recipient_name = 'A Fan';
        $card->recipient_email = 'fan@gmail.com';
        $card->activated_at = now();
        $card->save();
    }

    /**
     * Every guest surface that takes an event id, as [method, path, payload], for the event with
     * this $hash and $slug.
     *
     * @return list<array{0: string, 1: string, 2: array<string, mixed>}>
     */
    private function surfaces(string $slug, string $hash): array
    {
        $date = substr($this->booking->starts_at, 0, 10);
        $sub = '/'.$this->role->subdomain;

        return [
            ['GET', "$sub/$slug/$hash", []],
            ['GET', "$sub/$slug/$hash/$date", []],
            ['GET', "$sub/$slug/$hash/photos", []],
            ['GET', "$sub/$slug/$hash?embed=1&tickets=true", []],
            ['GET', "$sub/$slug/$hash/ical", []],
            ['GET', "$sub/carpool/$hash", []],
            ['GET', "$sub/curate-event/$hash", []],
            ['GET', "$sub/seating/state?event_id=$hash", []],
            ['POST', "$sub/submit-comment/$hash", ['comment' => 'Hello']],
            ['POST', "$sub/submit-video/$hash", ['youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']],
            ['POST', "$sub/submit-photo/$hash", ['photo' => UploadedFile::fake()->image('p.jpg')]],
            ['POST', "$sub/vote-poll/$hash/$hash", ['option_index' => 0]],
            ['POST', "$sub/suggest-poll-option/$hash/$hash", ['option' => 'Encore']],
            ['POST', "$sub/event-password", ['event_id' => $hash, 'password' => 'guess']],
            ['POST', "$sub/waitlist/join", ['name' => 'A Fan', 'email' => 'fan@gmail.com', 'event_id' => $hash, 'event_date' => $date]],
            ['POST', "$sub/checkout", ['name' => 'A Fan', 'email' => 'fan@gmail.com', 'event_id' => $hash, 'tickets' => [$hash => 1]]],
            ['POST', "$sub/rsvp", ['name' => 'A Fan', 'email' => 'fan@gmail.com', 'event_id' => $hash, 'event_date' => $date]],
            ['POST', "$sub/interest/join", ['email' => 'fan@gmail.com', 'event_id' => $hash]],
            ['POST', "$sub/promo-code/validate", ['event_id' => $hash, 'code' => 'SAVE10', 'tickets' => [$this->ticketHash => 1]]],
            ['POST', "$sub/gift-card/validate", ['event_id' => $hash, 'code' => 'GIFTPROBE1']],
        ];
    }

    /** The response without what changes per request, and with the event id in it taken out. */
    private function comparable(TestResponse $response, string $hash): array
    {
        $scrub = fn (string $text) => str_replace($hash, '{event}', preg_replace(
            ['~nonce="[^"]*"~', '~(<meta name="csrf-token" content=")[^"]*~', '~(name="_token" value=")[^"]*~'],
            ['nonce=""', '$1', '$1'],
            $text
        ));

        return [
            'status' => $response->getStatusCode(),
            'location' => $scrub((string) $response->headers->get('Location')),
            'body' => $scrub((string) $response->getContent()),
        ];
    }

    private function assertAnswersAsAnUnknownEvent(?User $as, string $who): void
    {
        $real = UrlUtils::encodeId($this->booking->id);
        $unknown = UrlUtils::encodeId(987654);

        $pairs = array_map(null, $this->surfaces($this->booking->slug, $real), $this->surfaces('no-such-event', $unknown));

        foreach ($pairs as [[$method, $path, $payload], [, $unknownPath, $unknownPayload]]) {
            $hit = function (string $path, array $payload) use ($method, $as) {
                auth()->forgetGuards();
                if ($as) {
                    $this->actingAs($as);
                }

                return $method === 'GET' ? $this->get($path) : $this->post($path, $payload);
            };

            $answer = $hit($path, $payload);
            $expected = $hit($unknownPath, $unknownPayload);

            $this->assertStringNotContainsString(self::GUEST, (string) $answer->getContent(), "{$who}: {$method} {$path} names the guest");
            $this->assertSame(
                $this->comparable($expected, $unknown),
                $this->comparable($answer, $real),
                "{$who}: {$method} {$path} answers a booking unlike an event that does not exist"
            );
        }
    }

    public function test_a_booking_answers_every_guest_surface_as_an_unknown_event(): void
    {
        $this->assertAnswersAsAnUnknownEvent(null, 'a stranger');
        $this->assertAnswersAsAnUnknownEvent($this->createOwner(), 'a signed-in stranger');
    }

    /**
     * Unlisted is not what hides it. The API can change any event's flags, a booking's too: listed,
     * and even opened to RSVPs and full, so its waitlist would take a name, it stays members-only
     * because it is a booking, on every surface that used to lean on the flags.
     */
    public function test_a_booking_stays_members_only_even_once_listed(): void
    {
        Event::whereKey($this->booking->id)->update([
            'is_private' => false,
            'rsvp_enabled' => true,
            'rsvp_limit' => 1,
            'rsvp_sold' => json_encode([substr($this->booking->starts_at, 0, 10) => 1]),
        ]);
        $this->booking->refresh();

        $this->assertAnswersAsAnUnknownEvent(null, 'a stranger, listed');
    }

    public function test_its_own_people_still_see_it_and_the_guest_keeps_the_manage_link(): void
    {
        $page = $this->guestEventUrl($this->role, $this->booking);

        foreach (['the owner' => $this->owner, 'an admin' => $this->createOwner(admin: true)] as $who => $user) {
            $this->actingAs($user)->get($page)->assertOk()->assertSee(self::GUEST);
            $this->actingAs($user)->get($page.'/ical')->assertOk()->assertSee(self::GUEST);
            auth()->forgetGuards();
        }

        // Signed out, the guest's own way in: the secret link every booking mail carries.
        $this->get(route('appointments.manage', ['event_id' => UrlUtils::encodeId($this->booking->id), 'secret' => $this->sale->secret]))
            ->assertOk()
            ->assertSee('Intro Chat');
        $this->get(route('appointments.ical', ['event_id' => UrlUtils::encodeId($this->booking->id), 'secret' => $this->sale->secret]))
            ->assertOk();
    }

    /** The lists already left a booking out as an unlisted event. A pin: it held before this change. */
    public function test_the_lists_leave_a_booking_out(): void
    {
        $date = \Carbon\Carbon::parse($this->booking->starts_at);
        $sub = '/'.$this->role->subdomain;

        foreach ([
            "$sub/api/calendar-events?month={$date->month}&year={$date->year}",
            "$sub/feed/ical",
            "$sub/feed/rss",
            "$sub/sitemap.xml",
            "$sub?graphic=1",
            $sub,
            '/search?q='.urlencode('Intro Chat'),
            '/browse',
            '/',
        ] as $url) {
            $response = $this->get($url);
            $content = $response->baseResponse instanceof \Symfony\Component\HttpFoundation\StreamedResponse
                ? $response->streamedContent()
                : (string) $response->getContent();

            $this->assertStringNotContainsString(self::GUEST, $content, "{$url} lists the booking");
            $this->assertStringNotContainsString($this->booking->slug, $content, "{$url} links the booking");
        }
    }
}
