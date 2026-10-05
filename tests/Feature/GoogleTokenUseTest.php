<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GoogleCalendarService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

/**
 * One GoogleCalendarService, several owners: each is read with their own token.
 *
 * `google:sync` and `google:refresh-webhooks` are handed a single service and walk every syncing
 * owner with it. Until October 2026 the token given to Google's client carried no `created`
 * time, and the library reads a token without one as already expired. So it renewed the token
 * itself on every request, through a cache it keys by client id and scopes and not by person:
 * the first owner's renewed token was then served to every owner after them for the rest of
 * the run. A schedule with no calendar chosen syncs `primary`, which under another owner's
 * token is that owner's own calendar.
 *
 * Two halves hold this shut, and each test here fails with one of them removed:
 *  - `created`, so a token that is still good is used as it is;
 *  - a cache of its own per token, so a renewal the library does make (a token that runs out
 *    part way through a long sync) can never be handed to the next owner.
 *
 * Google is a Guzzle mock throughout. Nothing here leaves the machine or touches the database.
 */
class GoogleTokenUseTest extends TestCase
{
    /** @var list<string> what reached "Google", in order */
    private array $seen = [];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'secret',
            'services.google.redirect' => 'https://app.test/google-calendar/callback',
        ]);
    }

    private function service(): GoogleCalendarService
    {
        $service = app(GoogleCalendarService::class);

        $handler = function (RequestInterface $request) {
            if (str_contains((string) $request->getUri(), 'token')) {
                parse_str((string) $request->getBody(), $body);
                $this->seen[] = 'renewed with '.$body['refresh_token'];

                return Create::promiseFor(new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => 'renewed-for-'.$body['refresh_token'],
                    'expires_in' => 3600,
                    'token_type' => 'Bearer',
                ])));
            }

            $this->seen[] = 'read with '.substr($request->getHeaderLine('Authorization'), strlen('Bearer '));

            return Create::promiseFor(new Response(200, ['Content-Type' => 'application/json'], json_encode(['items' => []])));
        };

        // The client is the service's own; only where its requests go is replaced.
        (fn () => $this->client->setHttpClient(new HttpClient(['handler' => HandlerStack::create($handler)])))->call($service);

        return $service;
    }

    private function owner(string $name): User
    {
        // Never saved: a token that is still good is used without a write.
        return (new User)->forceFill([
            'id' => ord($name),
            'google_token' => 'token-'.$name,
            'google_refresh_token' => 'refresh-'.$name,
            'google_token_expires_at' => now()->addMinutes(50),
        ]);
    }

    public function test_each_owner_in_one_run_is_read_with_their_own_token(): void
    {
        $service = $this->service();

        // As SyncGoogleCalendars and RefreshGoogleCalendarWebhooks do it.
        foreach (['A', 'B', 'C'] as $name) {
            $this->assertTrue($service->ensureValidToken($this->owner($name)));
            $service->listImportCalendars();
        }

        $this->assertSame(['read with token-A', 'read with token-B', 'read with token-C'], $this->seen);
    }

    public function test_a_token_that_is_still_good_is_not_renewed_on_every_request(): void
    {
        $service = $this->service();

        $this->assertTrue($service->ensureValidToken($this->owner('A')));
        $service->listImportCalendars();
        $service->listImportCalendars();

        $this->assertSame(['read with token-A', 'read with token-A'], $this->seen);
    }

    public function test_a_token_that_runs_out_mid_run_is_renewed_as_its_own_owner(): void
    {
        $service = $this->service();

        // Twenty seconds left is inside the margin at which Google's client renews by itself.
        // It is what a token set as good becomes during a sync that takes long enough.
        foreach (['A', 'B'] as $name) {
            $service->setAccessToken([
                'access_token' => 'token-'.$name,
                'refresh_token' => 'refresh-'.$name,
                'expires_in' => 20,
            ]);
            $service->listImportCalendars();
        }

        $this->assertSame([
            'renewed with refresh-A',
            'read with renewed-for-refresh-A',
            'renewed with refresh-B',
            'read with renewed-for-refresh-B',
        ], $this->seen);
    }
}
