<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * Every guest route answers a schedule the visitor may not know exists exactly as it answers a
 * subdomain that matches nothing.
 *
 * The schedule page itself has 404'd a deleted schedule for everyone, and an unpublished one for
 * anybody but its own people, for some time. The routes around it did not ask: /guest-add rendered
 * a deleted schedule's form and saved events to it, /booking-request, /book, /carpool and
 * /gift-cards showed an unpublished one's name and branding, its .ics downloads were served, and
 * /request, /follow and /promo redirected where a subdomain that matches nothing 404s - a yes or
 * no to "is there a schedule here?" for anybody who asked.
 *
 * Compared response to response, status, Location and body, so nothing a route prints can tell the
 * two apart, on the path-based routes and on the hosted install's subdomain group alike. A
 * placeholder's page is public by design, and a member of an unpublished schedule still reaches
 * its pages; both are pinned below.
 */
class HiddenScheduleGuestRoutesTest extends TestCase
{
    use CreatesScheduleData;
    use ForcesEnvironment;
    use RefreshDatabase;

    private const NAME = 'Coming Soon Trio';

    private const UNKNOWN = 'nosuchscheduleatall';

    /**
     * A talent schedule with every one of these routes switched on, one appointment type and one
     * event.
     *
     * @return array{0: Role, 1: Event}
     */
    private function schedule(User $owner, array $attrs = []): array
    {
        $role = $this->createRole($owner, 'talent', $attrs + ['name' => self::NAME]);

        $role->forceFill([
            'accept_requests' => true,
            'require_account' => false,
            'carpool_enabled' => true,
            'gift_cards_enabled' => true,
            'gift_card_amounts' => [25],
            'gift_card_currency_code' => 'USD',
            'gift_card_valid_days' => 365,
            'gift_card_payment_method' => 'cash',
            // Hosted gift cards need an email channel to deliver the card.
            'email_settings' => [
                'host' => 'smtp.test.dev',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'mailer',
                'password' => 'secret',
                'from_address' => 'events@schedule.dev',
                'from_name' => self::NAME,
            ],
        ])->save();

        $this->createAppointmentType($role, ['slug' => 'chat']);
        $event = $this->createEvent($role, ['name' => 'Coming Soon Show', 'creator_role_id' => $role->id]);

        return [$role->fresh(), $event];
    }

    /**
     * Every route this covers, as [method, path under the schedule, payload]. Each POST carries a
     * payload that passes whatever the action checks before it looks the schedule up, so an
     * unknown subdomain gets as far as the lookup.
     *
     * @return list<array{0: string, 1: string, 2: array<string, mixed>}>
     */
    private function routes(Event $event): array
    {
        $hash = UrlUtils::encodeId($event->id);

        return [
            ['GET', '/request', []],
            ['GET', '/follow', []],
            ['GET', '/promo/'.$hash, []],
            ['GET', '/guest-add', []],
            ['POST', '/guest-add', ['name' => 'Walk-in Night']],
            // A tripped honeypot is answered before the lookup, for an unknown subdomain too, which
            // is why each route refuses a hidden schedule at the lookup and not in a middleware.
            ['POST', '/guest-add', ['name' => 'Walk-in Night', 'website' => 'https://spam.test']],
            ['GET', '/guest-submit', []],
            ['GET', '/guest-submit/google', []],
            ['GET', '/booking-request', []],
            ['POST', '/booking-request', ['event_name' => 'Walk-in Night', 'contact_name' => 'A Fan', 'contact_email' => 'fan@fans.test']],
            ['GET', '/book', []],
            ['GET', '/book/chat', []],
            ['GET', '/book/chat/slots', []],
            ['POST', '/book/chat', ['name' => 'A Fan', 'email' => 'fan@fans.test', 'slot' => '2030-01-07T15:00:00Z']],
            ['GET', '/carpool/'.$hash, []],
            ['POST', '/carpool/'.$hash.'/agree', ['agree' => '1']],
            ['GET', '/gift-cards', []],
            ['POST', '/gift-cards', ['amount' => 25, 'purchaser_name' => 'A Fan', 'purchaser_email' => 'fan@fans.test', 'send_to_self' => '1']],
            ['GET', '/'.$event->slug.'/'.$hash.'/ical', []],
            ['GET', '/'.$event->slug.'/'.$hash.'/2030-01-07/ical', []],
        ];
    }

    private function hit(string $method, string $url, array $payload, ?User $as): TestResponse
    {
        auth()->forgetGuards();

        if ($as) {
            $this->actingAs($as);
        }

        return $method === 'GET' ? $this->get($url) : $this->post($url, $payload);
    }

    /**
     * The response with the per-request values (CSP nonces, CSRF tokens) and the subdomain it was
     * asked for taken out: a page that echoes the address back tells the visitor only what they
     * typed.
     *
     * @return array{status: int, location: string, body: string}
     */
    private function normalized(TestResponse $response, string $subdomain): array
    {
        $body = preg_replace(
            ['~nonce="[^"]*"~', '~(<meta name="csrf-token" content=")[^"]*~', '~(name="_token" value=")[^"]*~'],
            ['nonce=""', '$1', '$1'],
            (string) $response->getContent()
        );

        return [
            'status' => $response->getStatusCode(),
            'location' => str_replace($subdomain, '{subdomain}', (string) $response->headers->get('Location')),
            'body' => str_replace($subdomain, '{subdomain}', $body),
        ];
    }

    private function assertEveryRouteAnswersAsUnknown(Role $role, Event $event, ?User $as, string $case): void
    {
        foreach ($this->routes($event) as [$method, $path, $payload]) {
            $unknown = $this->normalized($this->hit($method, '/'.self::UNKNOWN.$path, $payload, $as), self::UNKNOWN);
            $hidden = $this->normalized($this->hit($method, '/'.$role->subdomain.$path, $payload, $as), $role->subdomain);

            $this->assertSame($unknown, $hidden, "{$case}: {$method} {$path} answers differently from a subdomain that matches nothing");
            $this->assertStringNotContainsString(self::NAME, $hidden['body'], "{$case}: {$method} {$path} names the schedule");
        }
    }

    public function test_an_unpublished_schedule_answers_every_guest_route_as_an_unknown_one(): void
    {
        [$role, $event] = $this->schedule($this->createOwner(), ['email_verified_at' => null]);

        $this->assertFalse($role->isClaimed(), 'fixture: nobody verified a contact');
        $this->assertFalse($role->isClaimable(), 'fixture: somebody really runs it, so it is no placeholder');

        $this->assertEveryRouteAnswersAsUnknown($role, $event, null, 'an unpublished schedule, signed out');
        $this->assertEveryRouteAnswersAsUnknown($role, $event, $this->createOwner(), 'an unpublished schedule, a signed-in stranger');
    }

    public function test_a_deleted_schedule_answers_every_guest_route_as_an_unknown_one_even_for_its_owner(): void
    {
        $owner = $this->createOwner();
        [$role, $event] = $this->schedule($owner);
        Role::whereKey($role->id)->update(['is_deleted' => true]);
        $role->refresh();

        $this->assertEveryRouteAnswersAsUnknown($role, $event, null, 'a deleted schedule, signed out');
        $this->assertEveryRouteAnswersAsUnknown($role, $event, $owner, 'a deleted schedule, its owner');
        $this->assertSame(1, Event::count(), 'a guest route saved an event to a deleted schedule');
    }

    /** Its own people can do something about an unpublished schedule, so its pages still answer them. */
    public function test_an_unpublished_schedule_still_answers_its_own_people(): void
    {
        $owner = $this->createOwner();
        [$role, $event] = $this->schedule($owner, ['email_verified_at' => null]);
        $admin = $this->createOwner(admin: true);

        foreach (['its owner' => $owner, 'an admin' => $admin] as $who => $user) {
            $this->actingAs($user);

            foreach (['/guest-add', '/booking-request', '/book/chat', '/gift-cards'] as $path) {
                $this->get('/'.$role->subdomain.$path)->assertOk()->assertSee(self::NAME);
            }

            $this->get('/'.$role->subdomain.'/'.$event->slug.'/'.UrlUtils::encodeId($event->id).'/ical')
                ->assertOk()
                ->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        }
    }

    /**
     * The hosted install serves these routes from a subdomain group of its own, which the test env
     * never registers, so this boots the app as that install, the way
     * RouteLoadTest::test_hosted_gp_routes_load() does, and asks the tenant hosts directly. GETs
     * only: throttling is live on this install, and the POSTs share an IP bucket.
     */
    public function test_the_hosted_subdomain_routes_answer_the_same_way(): void
    {
        // Roll back RefreshDatabase's transaction to release its locks before the app is rebuilt.
        $this->app['db']->connection()->rollBack();

        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('APP_TESTING', 'false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            $this->assertTrue(config('app.hosted'));
            $this->assertFalse(config('app.is_testing'), 'fixture: the hosted subdomain group is registered only when not testing');

            $owner = $this->createOwner();
            [$unpublished, $unpublishedEvent] = $this->schedule($owner, ['email_verified_at' => null]);
            [$deleted, $deletedEvent] = $this->schedule($owner);
            Role::whereKey($deleted->id)->update(['is_deleted' => true]);

            $url = fn (string $subdomain, string $path) => 'https://'.$subdomain.'.'._base_domain().$path;

            // Before anything is compared: these must reach the tenant group, not the path-based
            // routes, or the assertions below pass for the wrong reason.
            $route = app('router')->getRoutes()->match(Request::create($url(self::UNKNOWN, '/guest-add')));
            $this->assertSame('event.guest_import', $route->getName());
            $this->assertNotNull($route->getDomain(), 'fixture: the hosted subdomain group answers');

            foreach (['unpublished' => [$unpublished, $unpublishedEvent], 'deleted' => [$deleted, $deletedEvent]] as $case => [$role, $event]) {
                foreach ($this->routes($event) as [$method, $path]) {
                    if ($method !== 'GET') {
                        continue;
                    }

                    $unknown = $this->normalized($this->get($url(self::UNKNOWN, $path)), self::UNKNOWN);
                    $hidden = $this->normalized($this->get($url($role->subdomain, $path)), $role->subdomain);

                    $this->assertSame($unknown, $hidden, "hosted, {$case}: GET {$path} answers differently from a subdomain that matches nothing");
                    $this->assertStringNotContainsString(self::NAME, $hidden['body'], "hosted, {$case}: GET {$path} names the schedule");
                }
            }
        } finally {
            // refreshApplication() swapped the database manager, so RefreshDatabase's teardown cannot
            // see the transaction opened above. Left open it holds metadata locks and the next test
            // class's migrate:fresh hangs - see RouteLoadTest::test_hosted_gp_routes_load().
            $connection = $this->app['db']->connection();

            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }

            $connection->disconnect();
        }
    }

    /** A placeholder's existence is public - its root is an "is this you?" page - so its routes answer as before. */
    public function test_a_placeholder_still_answers(): void
    {
        $placeholder = new Role;
        $placeholder->subdomain = 'act'.strtolower(Str::random(10));
        $placeholder->type = 'talent';
        $placeholder->name = 'The Wandering Few';
        $placeholder->timezone = 'America/New_York';
        $placeholder->save();

        $this->assertTrue($placeholder->fresh()->isClaimable(), 'fixture');

        $sub = $placeholder->subdomain;

        $this->get('/'.$sub.'/booking-request')->assertOk()->assertSee('The Wandering Few');
        $this->get('/'.$sub.'/request')->assertRedirect(route('event.booking_request', ['subdomain' => $sub]));
        $this->get('/'.$sub.'/guest-add')->assertRedirect(route('role.request', ['subdomain' => $sub]));
    }
}
