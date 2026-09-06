<?php

namespace Tests\Feature;

use App\Mail\TicketPurchase;
use App\Services\Wallet\GoogleWalletService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Add to Google Wallet" on a purchased ticket.
 *
 * The load-bearing assertion in here is test_the_barcode_matches_the_on_page_qr_exactly: the door
 * scanner matches /\/ticket\/view\/([^/]+)\/([^/]+)\/?$/ against whatever it decodes, so if the
 * pass's barcode value ever drifts from what TicketController::qrCode() encodes, every wallet pass
 * stops scanning while the on-page QR keeps working - a failure nobody would see until a queue
 * formed at a door.
 *
 * Credentials are injected with config() rather than $_SERVER, which is safe here only because
 * GoogleWalletService reads config() on every call and memoizes nothing.
 */
class GoogleWalletPassTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private string $privateKey;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // Via a local: a typed property cannot be passed by reference while uninitialized.
        $privateKey = '';
        openssl_pkey_export($resource, $privateKey);

        $this->privateKey = $privateKey;
        $this->publicKey = openssl_pkey_get_details($resource)['key'];

        $this->pinAppUrl('https://eventschedule.test');

        // Role::saving() geocodes any address against the live Maps API whenever
        // BACKEND_GOOGLE_KEY is set, and phpunit.xml does not pin it - so on a developer
        // machine that has a key the venue fixture below came back with Google's real
        // coordinates instead of the ones this test set. Off, so the test says the same
        // thing everywhere.
        config(['services.google.backend' => null]);
    }

    /*
     * ------------------------------------------------------------------ helpers
     */

    private function configureWallet(): void
    {
        config([
            'services.google.wallet_issuer_id' => '3388000000012345678',
            'services.google.wallet_id_prefix' => 'es',
            'services.google.wallet_service_account' => base64_encode(json_encode([
                'client_email' => 'wallet@example-project.iam.gserviceaccount.com',
                'private_key' => $this->privateKey,
            ])),
        ]);
    }

    private function fakeGoogle(int $classLookupStatus = 404): void
    {
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            // Order matters: Laravel matches stubs in order and the bare pattern below cannot match
            // a URL that carries a trailing /{id}.
            'walletobjects.googleapis.com/walletobjects/v1/eventTicketClass/*' => Http::response(
                $classLookupStatus === 200 ? ['id' => 'existing'] : ['error' => ['message' => 'not found']],
                $classLookupStatus
            ),
            'walletobjects.googleapis.com/walletobjects/v1/eventTicketClass' => Http::response(['id' => 'created']),
            // Catch-all last. Http::fake() with an array lets an UNMATCHED url execute for
            // real, which is how the venue fixture reached maps.googleapis.com before this.
            '*' => Http::response([], 200),
        ]);
    }

    /** @return array{0: \App\Models\Role, 1: \App\Models\Event, 2: \App\Models\Sale} */
    private function paidTicket(array $eventAttrs = [], array $saleAttrs = [])
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, array_merge(['tickets_enabled' => true], $eventAttrs));
        $ticket = $this->createTicket($event, ['type' => 'General Admission', 'price' => 10]);
        $sale = $this->createSale($event, $role, array_merge(['status' => 'paid'], $saleAttrs), $ticket, 2);

        return [$role, $event, $sale];
    }

    private function walletUrl($event, $sale): string
    {
        return route('ticket.wallet.google', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]);
    }

    private function decodeJwtFromRedirect(string $location): array
    {
        $this->assertStringStartsWith(GoogleWalletService::SAVE_URL, $location);

        $jwt = substr($location, strlen(GoogleWalletService::SAVE_URL));
        [$header, $claims, $signature] = explode('.', $jwt);

        $decode = fn ($part) => json_decode(base64_decode(strtr($part, '-_', '+/')), true);

        $verified = openssl_verify(
            $header.'.'.$claims,
            base64_decode(strtr($signature, '-_', '+/')),
            $this->publicKey,
            OPENSSL_ALGO_SHA256
        );

        $this->assertSame(1, $verified, 'The save JWT is not signed by the configured service account key.');

        return ['jwt' => $jwt, 'header' => $decode($header), 'claims' => $decode($claims)];
    }

    /*
     * ------------------------------------------------------------------ off by default
     */

    public function test_the_feature_is_off_until_it_is_configured(): void
    {
        [$role, $event, $sale] = $this->paidTicket();

        $this->assertFalse(GoogleWalletService::isConfigured());
        $this->assertFalse(GoogleWalletService::canOffer($sale, $event));

        $this->get(route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]))
            ->assertOk()
            ->assertDontSee('images/wallet/google');
    }

    public function test_an_unconfigured_install_sends_the_buyer_back_to_their_ticket(): void
    {
        [$role, $event, $sale] = $this->paidTicket();

        $this->get($this->walletUrl($event, $sale))
            ->assertRedirect(route('ticket.view', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]));
    }

    public function test_a_partial_credential_does_not_count_as_configured(): void
    {
        config([
            'services.google.wallet_issuer_id' => '3388000000012345678',
            'services.google.wallet_service_account' => null,
        ]);
        $this->assertFalse(GoogleWalletService::isConfigured());

        // A key file that is present but not valid JSON must not read as configured either.
        config(['services.google.wallet_service_account' => base64_encode('not json at all')]);
        $this->assertFalse(GoogleWalletService::isConfigured());

        // Valid JSON missing the private key is still unusable.
        config(['services.google.wallet_service_account' => base64_encode(json_encode(['client_email' => 'a@b.com']))]);
        $this->assertFalse(GoogleWalletService::isConfigured());
    }

    /*
     * ------------------------------------------------------------------ the badge assets
     */

    public function test_every_declared_badge_locale_ships_its_files(): void
    {
        // BADGE_LOCALES exists so the blade never stats disk on render, which means a locale listed
        // here without its artwork is a broken image on a buyer's ticket and nothing would catch it.
        foreach (GoogleWalletService::BADGE_LOCALES as $locale) {
            foreach ([$locale.'.svg', $locale.'-condensed.svg', $locale.'.png'] as $file) {
                $this->assertFileExists(
                    public_path('images/wallet/google/'.$file),
                    "BADGE_LOCALES lists '{$locale}' but {$file} is missing."
                );
            }
        }
    }

    public function test_every_supported_language_has_a_badge(): void
    {
        // A supported language with no badge silently falls back to English mid-page, next to
        // fully translated copy.
        foreach (config('app.supported_languages') as $language => $label) {
            $this->assertContains(
                $language,
                GoogleWalletService::BADGE_LOCALES,
                "The app supports '{$language}' but ships no Google Wallet badge for it."
            );
        }
    }

    public function test_an_unknown_locale_falls_back_to_english(): void
    {
        $this->assertSame('he', GoogleWalletService::badgeLocale('he'));
        $this->assertSame('en', GoogleWalletService::badgeLocale('ja'));
        $this->assertSame('pt', GoogleWalletService::badgeLocale('pt_BR'));
    }

    /*
     * ------------------------------------------------------------------ the happy path
     */

    public function test_a_paid_ticket_renders_the_badge_and_redirects_to_google(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $this->get(route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]))
            ->assertOk()
            ->assertSee('images/wallet/google/en.svg')
            ->assertSee($this->walletUrl($event, $sale), false);

        $response = $this->get($this->walletUrl($event, $sale));
        $response->assertRedirect();

        $decoded = $this->decodeJwtFromRedirect($response->headers->get('Location'));

        $this->assertSame('RS256', $decoded['header']['alg']);
        $this->assertSame('google', $decoded['claims']['aud']);
        $this->assertSame('savetowallet', $decoded['claims']['typ']);
        $this->assertSame('wallet@example-project.iam.gserviceaccount.com', $decoded['claims']['iss']);
        $this->assertContains('https://eventschedule.test', $decoded['claims']['origins']);
    }

    public function test_the_barcode_matches_the_on_page_qr_exactly(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $response = $this->get($this->walletUrl($event, $sale));
        $object = $this->decodeJwtFromRedirect($response->headers->get('Location'))['claims']['payload']['eventTicketObjects'][0];

        // The exact string TicketController::qrCode() hands to QrCodeUtils::png().
        $expected = canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ], false));

        $this->assertSame('QR_CODE', $object['barcode']['type']);
        $this->assertSame($expected, $object['barcode']['value']);

        // And the scanner's own regex has to accept it.
        $this->assertMatchesRegularExpression(
            '#/ticket/view/([^/]+)/([^/]+)/?$#',
            parse_url($object['barcode']['value'], PHP_URL_PATH)
        );
    }

    public function test_the_object_carries_the_ticket_details(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket([], ['name' => 'Dana Holder']);

        $response = $this->get($this->walletUrl($event, $sale));
        $object = $this->decodeJwtFromRedirect($response->headers->get('Location'))['claims']['payload']['eventTicketObjects'][0];

        $this->assertSame('3388000000012345678.es-s'.$sale->id, $object['id']);
        $this->assertStringStartsWith('3388000000012345678.es-e'.$event->id.'-', $object['classId']);
        $this->assertSame('ACTIVE', $object['state']);
        $this->assertSame('Dana Holder', $object['ticketHolderName']);
        $this->assertSame('General Admission', $object['ticketType']['defaultValue']['value']);
        $this->assertSame(UrlUtils::encodeId($sale->id), $object['ticketNumber']);
        $this->assertArrayHasKey('validTimeInterval', $object);

        // Two admissions on one sale, so the pass says so.
        $admits = collect($object['textModulesData'] ?? [])->firstWhere('id', 'admits');
        $this->assertSame('2', $admits['body'] ?? null);
    }

    public function test_venue_coordinates_become_a_pass_location(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        $owner = $this->createOwner();
        // 'talent', not the default 'venue': Event::getVenueAttribute() returns the first
        // venue-type role attached, so a venue-type host schedule would shadow the real venue.
        $role = $this->createRole($owner, 'talent');
        $venue = $this->createVenueWithAddress($owner, ['geo_lat' => 40.7306, 'geo_lon' => -73.9866]);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $sale = $this->createSale($event->fresh(), $role, ['status' => 'paid'], $ticket, 1);

        $response = $this->get($this->walletUrl($event, $sale));
        $object = $this->decodeJwtFromRedirect($response->headers->get('Location'))['claims']['payload']['eventTicketObjects'][0];

        $this->assertEqualsWithDelta(40.7306, $object['locations'][0]['latitude'], 0.0001);
        $this->assertEqualsWithDelta(-73.9866, $object['locations'][0]['longitude'], 0.0001);
    }

    public function test_a_venue_without_coordinates_gets_no_location(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $response = $this->get($this->walletUrl($event, $sale));
        $object = $this->decodeJwtFromRedirect($response->headers->get('Location'))['claims']['payload']['eventTicketObjects'][0];

        $this->assertArrayNotHasKey('locations', $object);
    }

    /*
     * ------------------------------------------------------------------ the size guard
     */

    public function test_the_jwt_sheds_optional_fields_to_stay_inside_googles_safe_length(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        // A plain ticket comfortably fits, and carries the extra rows.
        [$role, $event, $sale] = $this->paidTicket();
        $plain = $this->decodeJwtFromRedirect(
            $this->get($this->walletUrl($event, $sale))->headers->get('Location')
        );
        $this->assertArrayHasKey(
            'textModulesData',
            $plain['claims']['payload']['eventTicketObjects'][0],
            'A ticket that fits should keep its optional rows.'
        );

        // Everything long at once, on an event that also has a located venue. Google truncates the
        // save link past MAX_JWT_LENGTH and a truncated link fails with nothing the buyer can act
        // on, so the optional rows have to go rather than the pass.
        $owner = $this->createOwner();
        $bigRole = $this->createRole($owner, 'talent');
        $venue = $this->createVenueWithAddress($owner, ['geo_lat' => 40.7306, 'geo_lon' => -73.9866]);
        $bigEvent = $this->createEvent($bigRole, [
            'tickets_enabled' => true,
            'name' => str_repeat('A Very Long Festival Name ', 8),
        ]);
        $bigEvent->roles()->attach($venue->id, ['is_accepted' => true]);
        $bigEvent->ticket_notes = str_repeat('Doors open at seven, bring photo identification. ', 12);
        $bigEvent->save();

        $bigTicket = $this->createTicket($bigEvent->fresh(), ['type' => 'General Admission Standing', 'price' => 10]);
        $bigSale = $this->createSale($bigEvent->fresh(), $bigRole, [
            'status' => 'paid',
            'name' => str_repeat('Wilhelmina Featherstonehaugh ', 4),
        ], $bigTicket, 2);

        $decoded = $this->decodeJwtFromRedirect(
            $this->get($this->walletUrl($bigEvent, $bigSale))->headers->get('Location')
        );

        $this->assertLessThanOrEqual(
            GoogleWalletService::MAX_JWT_LENGTH,
            strlen($decoded['jwt']),
            'The save JWT is long enough that a browser would truncate it.'
        );

        // The shed actually happened: without it this payload is over the limit, and buildJwt()
        // would have returned null rather than a link at all.
        $this->assertArrayNotHasKey(
            'textModulesData',
            $decoded['claims']['payload']['eventTicketObjects'][0],
            'The optional rows survived a payload that does not fit, so nothing was shed.'
        );

        // The fields the attendee is shown at the door are the last to go, and survived.
        $object = $decoded['claims']['payload']['eventTicketObjects'][0];
        $this->assertArrayHasKey('barcode', $object);
        $this->assertArrayHasKey('ticketHolderName', $object);
    }

    /*
     * ------------------------------------------------------------------ every gate arm
     */

    public static function refusedSaleProvider(): array
    {
        return [
            'unpaid' => ['unpaid'],
            'cancelled' => ['cancelled'],
            'refunded' => ['refunded'],
            'expired' => ['expired'],
        ];
    }

    /** @dataProvider refusedSaleProvider */
    public function test_a_sale_that_is_not_paid_is_refused(string $status): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();
        $sale->status = $status;
        $sale->save();

        $this->assertFalse(GoogleWalletService::canOffer($sale->fresh(), $event));

        $this->get($this->walletUrl($event, $sale))
            ->assertRedirect(route('ticket.view', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]));

        $this->assertNothingSentToGoogle();
    }

    public function test_a_cancelled_event_is_refused_even_though_the_sale_is_still_paid(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $event->is_cancelled = true;
        $event->save();

        // The trap ticket/order.blade.php documents: cancelling an event leaves its sales `paid`.
        $this->assertSame('paid', $sale->fresh()->status);
        $this->assertFalse(GoogleWalletService::canOffer($sale, $event->fresh()));

        $this->get(route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]))
            ->assertOk()
            ->assertDontSee('images/wallet/google');

        $this->get($this->walletUrl($event, $sale))->assertRedirect();
        $this->assertNothingSentToGoogle();
    }

    public function test_a_deleted_sale_is_refused(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();
        $sale->is_deleted = true;
        $sale->save();

        $this->get($this->walletUrl($event, $sale))->assertNotFound();
        $this->assertNothingSentToGoogle();
    }

    /*
     * ------------------------------------------------------------------ the confirmation email
     */

    public function test_the_confirmation_email_carries_the_badge_and_the_link(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $rendered = (new TicketPurchase($sale, $event, $role))->render();

        // The link is ours, not a signed pay.google.com one: the JWT behind that is short-lived
        // and building it calls Google, neither of which belongs in a queued mailable.
        $this->assertStringContainsString(
            canonical_url(route('ticket.wallet.google', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ], false)),
            $rendered
        );

        // The badge is embedded, never hotlinked - this app does not ask a recipient's mail client
        // to fetch an asset from someone else's server.
        $this->assertStringContainsString('cid:', $rendered);
        $this->assertStringNotContainsString('https://developers.google.com', $rendered);

        // And no call to Google was needed to render it.
        $this->assertNothingSentToGoogle();
    }

    public function test_the_confirmation_email_has_no_badge_when_the_feature_is_off(): void
    {
        [$role, $event, $sale] = $this->paidTicket();

        $rendered = (new TicketPurchase($sale, $event, $role))->render();

        $this->assertStringNotContainsString('ticket/wallet/google', $rendered);
        $this->assertStringContainsString('View Your Tickets', $rendered);
    }

    /*
     * ------------------------------------------------------------------ season passes
     */

    public function test_a_season_pass_gets_a_date_less_class(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, [
            'type' => 'Season Pass',
            'price' => 100,
            'is_pass' => true,
            'pass_valid_days' => 90,
        ]);
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);

        $this->assertTrue($sale->fresh()->isPass());

        $response = $this->get($this->walletUrl($event, $sale));
        $decoded = $this->decodeJwtFromRedirect($response->headers->get('Location'));
        $object = $decoded['claims']['payload']['eventTicketObjects'][0];

        // Date-less: pinning a season pass to the night it happened to be bought on would
        // date-stamp a pass that is valid across the whole series.
        $this->assertSame('3388000000012345678.es-p'.$event->id, $object['classId']);

        $classPayload = $this->sentClassPayload();
        $this->assertArrayNotHasKey('dateTime', $classPayload);

        // Its validity comes from the pass expiry instead.
        $this->assertArrayHasKey('end', $object['validTimeInterval']);
    }

    /*
     * ------------------------------------------------------------------ the class over REST
     */

    public function test_the_class_is_created_once_and_then_cached(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();
        $otherSale = $this->createSale($event, $role, ['status' => 'paid', 'email' => 'second@example.com'], null, 1);

        $this->get($this->walletUrl($event, $sale))->assertRedirect();
        $this->get($this->walletUrl($event, $otherSale))->assertRedirect();

        $inserts = collect(Http::recorded())
            ->filter(fn ($pair) => $pair[0]->method() === 'POST'
                && str_ends_with($pair[0]->url(), '/eventTicketClass'))
            ->count();

        $this->assertSame(1, $inserts, 'The pass class was inserted more than once for one occurrence.');
    }

    public function test_an_existing_class_is_not_re_inserted(): void
    {
        $this->configureWallet();
        $this->fakeGoogle(200);

        [$role, $event, $sale] = $this->paidTicket();

        $this->get($this->walletUrl($event, $sale))->assertRedirect();

        $inserts = collect(Http::recorded())
            ->filter(fn ($pair) => $pair[0]->method() === 'POST'
                && str_ends_with($pair[0]->url(), '/eventTicketClass'))
            ->count();

        $this->assertSame(0, $inserts);
    }

    public function test_the_class_carries_the_event_and_the_schedule(): void
    {
        $this->configureWallet();
        $this->fakeGoogle();

        [$role, $event, $sale] = $this->paidTicket();

        $this->get($this->walletUrl($event, $sale))->assertRedirect();

        $payload = $this->sentClassPayload();

        $this->assertSame('Test Event', $payload['eventName']['defaultValue']['value']);
        $this->assertSame('Test Schedule', $payload['issuerName']);
        // DRAFT would restrict the pass to accounts registered as test accounts on the issuer.
        $this->assertSame('UNDER_REVIEW', $payload['reviewStatus']);
        $this->assertArrayHasKey('dateTime', $payload);

        // APP_URL is eventschedule.test in the suite, which Google could never fetch, so the
        // artwork is deliberately left off rather than shipped as broken images.
        $this->assertArrayNotHasKey('logo', $payload);
        $this->assertArrayNotHasKey('heroImage', $payload);
    }

    public function test_a_google_outage_sends_the_buyer_back_to_their_ticket(): void
    {
        $this->configureWallet();

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'walletobjects.googleapis.com/*' => Http::response(['error' => 'boom'], 500),
        ]);

        [$role, $event, $sale] = $this->paidTicket();

        $this->get($this->walletUrl($event, $sale))
            ->assertRedirect(route('ticket.view', [
                'event_id' => UrlUtils::encodeId($event->id),
                'secret' => $sale->secret,
            ]))
            ->assertSessionHas('error');
    }

    public function test_a_failed_class_is_not_retried_on_every_tap(): void
    {
        $this->configureWallet();

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'walletobjects.googleapis.com/*' => Http::response(['error' => 'boom'], 500),
        ]);

        [$role, $event, $sale] = $this->paidTicket();

        $this->get($this->walletUrl($event, $sale));
        $before = count(Http::recorded());

        $this->get($this->walletUrl($event, $sale));

        $this->assertSame($before, count(Http::recorded()), 'A failing class lookup was retried instead of being cached.');

        // ...but it recovers once the operator fixes things.
        Cache::flush();
        $this->fakeGoogle();
        $this->get($this->walletUrl($event, $sale))->assertRedirect(); // to pay.google.com
    }

    /**
     * No call reached Google.
     *
     * Not Http::assertNothingSent(): Laravel's own 404 page runs the self-updater's version check
     * against api.github.com, so a refused request is not a silent one.
     */
    private function assertNothingSentToGoogle(): void
    {
        $googleCalls = collect(Http::recorded())
            ->filter(fn ($pair) => str_contains($pair[0]->url(), 'googleapis.com')
                || str_contains($pair[0]->url(), 'pay.google.com'))
            ->map(fn ($pair) => $pair[0]->method().' '.$pair[0]->url())
            ->all();

        $this->assertSame([], $googleCalls, 'A refused ticket still reached Google.');
    }

    /**
     * The body of the one eventTicketClass insert the run made.
     */
    private function sentClassPayload(): array
    {
        $insert = collect(Http::recorded())
            ->first(fn ($pair) => $pair[0]->method() === 'POST'
                && str_ends_with($pair[0]->url(), '/eventTicketClass'));

        $this->assertNotNull($insert, 'No pass class was inserted.');

        /** @var Request $request */
        $request = $insert[0];

        return $request->data();
    }
}
