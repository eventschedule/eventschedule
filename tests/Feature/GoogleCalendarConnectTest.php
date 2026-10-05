<?php

namespace Tests\Feature;

use App\Jobs\SyncEventToGoogleCalendar;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Google\Service\Calendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Google Calendar as a place to bring events in from, on the import page.
 *
 * Somebody who only wants to copy their events over is asked for read access, not for the right
 * to edit their calendar, and is brought back to the page they started on whatever happens at
 * Google. What the connection was granted is recorded, so a read-only one is never asked to
 * write. Google itself is faked throughout: no test here leaves the machine.
 */
class GoogleCalendarConnectTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const READ = Calendar::CALENDAR_READONLY.' openid email profile';

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'client-id.apps.googleusercontent.com',
            'services.google.client_secret' => 'secret',
            'services.google.redirect' => 'https://app.test/google-calendar/callback',
        ]);

        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'venue', ['timezone' => 'America/New_York']);
        $this->actingAs($this->owner);
    }

    private function connect(User $user, ?string $scopes): User
    {
        $user->forceFill([
            'google_token' => 'token-'.$user->id,
            'google_refresh_token' => 'refresh-'.$user->id,
            'google_token_expires_at' => now()->addHour(),
            'google_token_scopes' => $scopes,
        ])->save();

        return $user->fresh();
    }

    private function scopesAskedFor(string $location): array
    {
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return explode(' ', $query['scope'] ?? '');
    }

    private function importPage(?Role $role = null): string
    {
        return route('event.show_import_ai', ['subdomain' => ($role ?? $this->role)->subdomain, 'source' => 'google']);
    }

    private function settings(): string
    {
        return route('profile.edit').'#section-google-calendar';
    }

    private function returning(string $from = 'import', ?int $at = null, ?Role $role = null): array
    {
        return [
            'google_oauth_state' => 'state-1',
            'google_oauth_return' => ['role_id' => ($role ?? $this->role)->id, 'from' => $from, 'at' => $at ?? now()->getTimestamp()],
        ];
    }

    /**
     * Put a fake in Google's place. A route keeps the controller it first built, and with it the
     * service of that moment, so a fake set after a request to the same route would be ignored
     * and the real client would be called: the controllers are dropped first.
     */
    private function fakeGoogle(\Closure $expectations)
    {
        $this->freshControllers();

        return $this->mock(GoogleCalendarService::class, $expectations);
    }

    private function freshControllers(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            $route->flushController();
        }
    }

    private function googleAnswers(array $token): void
    {
        $this->fakeGoogle(function ($mock) use ($token) {
            $mock->shouldReceive('getAccessToken')->andReturn($token);
        });
    }

    private function token(string $scope): array
    {
        return ['access_token' => 'fresh-access', 'refresh_token' => 'fresh-refresh', 'expires_in' => 3600, 'scope' => $scope, 'id_token' => null];
    }

    /** An id token as Google signs one: three base64url parts. Only the middle one is read. */
    private function idToken(array $claims): string
    {
        $part = fn (array $data) => rtrim(strtr(base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE)), '+/', '-_'), '=');

        return $part(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$part($claims).'.signature';
    }

    private function entry(array $overrides = []): array
    {
        return array_merge([
            'id' => 'evt-'.substr(md5(json_encode($overrides)), 0, 8),
            'status' => 'confirmed',
            'summary' => 'Untitled',
            'description' => null,
            'location' => null,
            'visibility' => null,
            'eventType' => 'default',
            'start' => ['date' => null, 'dateTime' => now('America/New_York')->addDays(5)->setTime(19, 0)->toRfc3339String(), 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => now('America/New_York')->addDays(5)->setTime(21, 0)->toRfc3339String(), 'timeZone' => 'America/New_York'],
            'recurrence' => [],
            'recurringEventId' => null,
            'originalStartTime' => null,
        ], $overrides);
    }

    public function test_connecting_from_the_import_page_asks_google_to_read_and_nothing_more(): void
    {
        $response = $this->get(route('google.calendar.redirect', ['from' => 'import', 'subdomain' => $this->role->subdomain]));

        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/', $location);
        $asked = $this->scopesAskedFor($location);
        $this->assertContains(Calendar::CALENDAR_READONLY, $asked);
        $this->assertNotContains(Calendar::CALENDAR_EVENTS, $asked);
        $this->assertNotContains(Calendar::CALENDAR, $asked);
        // Asking for less never takes away what the account already granted.
        $this->assertStringContainsString('include_granted_scopes=true', $location);

        $response->assertSessionHas('google_oauth_return', fn ($return) => $return['role_id'] === $this->role->id && $return['from'] === 'import');

        // Account settings and a schedule's settings ask for what the standing sync needs.
        foreach ([[], ['from' => 'settings', 'subdomain' => $this->role->subdomain]] as $query) {
            $asked = $this->scopesAskedFor($this->get(route('google.calendar.redirect', $query))->headers->get('Location'));
            $this->assertContains(Calendar::CALENDAR_EVENTS, $asked);
            $this->assertContains(Calendar::CALENDAR_READONLY, $asked);
        }
    }

    public function test_another_account_is_offered_only_while_nothing_is_synced(): void
    {
        $choose = ['from' => 'import', 'subdomain' => $this->role->subdomain, 'choose' => 1];

        $this->assertStringContainsString('select_account', $this->get(route('google.calendar.redirect', $choose))->headers->get('Location'));

        // A schedule of theirs runs on this connection: another account would take it over.
        $synced = $this->createRole($this->owner, 'talent');
        $synced->forceFill(['sync_direction' => 'from'])->save();
        $this->freshControllers();

        $this->assertStringNotContainsString('select_account', $this->get(route('google.calendar.redirect', $choose))->headers->get('Location'));
    }

    public function test_one_request_for_another_account_does_not_colour_the_next(): void
    {
        // The Google client keeps what it was last told, and one service can outlive a request.
        $service = app(GoogleCalendarService::class);

        $this->assertStringContainsString('select_account', $service->getAuthUrl(true, true));
        $next = $service->getAuthUrl(true, false);
        $this->assertStringNotContainsString('select_account', $next);
        $this->assertStringContainsString('prompt=consent', $next);

        // Nor does a read-only request leave the next one read-only.
        $this->assertContains(Calendar::CALENDAR_EVENTS, $this->scopesAskedFor($service->getAuthUrl()));
    }

    public function test_the_way_back_is_remembered_only_for_the_persons_own_schedule(): void
    {
        $theirs = $this->createRole($this->createOwner(), 'venue');
        $this->followRole($this->owner, $theirs, 'admin');

        // Somebody else's schedule, even as its admin: the connection is the owner's.
        $response = $this->get(route('google.calendar.redirect', ['from' => 'import', 'subdomain' => $theirs->subdomain]));
        $response->assertSessionMissing('google_oauth_return');
        $this->assertContains(Calendar::CALENDAR_EVENTS, $this->scopesAskedFor($response->headers->get('Location')));

        // A place that is not on the list, and an address, are not places to come back to.
        foreach (['somewhere', 'https://evil.example/'] as $from) {
            $this->get(route('google.calendar.redirect', ['from' => $from, 'subdomain' => $this->role->subdomain]))
                ->assertSessionMissing('google_oauth_return');
        }

        // A remembered schedule that is not theirs by the time Google answers lands in settings.
        $this->googleAnswers($this->token(self::READ));
        $this->withSession($this->returning('import', null, $theirs))
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect($this->settings());
    }

    public function test_google_brings_the_person_back_to_the_import_page_and_what_was_granted_is_kept(): void
    {
        $this->googleAnswers($this->token(self::READ));

        $response = $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']));

        $response->assertRedirect($this->importPage());
        // The page opens on the calendars: there is nothing to announce.
        $response->assertSessionMissing('google_import_error')->assertSessionMissing('message');

        $owner = $this->owner->fresh();
        $this->assertSame('fresh-access', $owner->google_token);
        $this->assertSame(self::READ, $owner->google_token_scopes);
        $this->assertFalse($owner->googleCanWrite());

        // The way back is used once.
        $response->assertSessionMissing('google_oauth_return');
    }

    public function test_the_google_account_is_read_from_its_id_token_whatever_its_name(): void
    {
        // A token's payload is base64url. A name with a letter outside ASCII is enough to put
        // a "-" or "_" in it, and read as plain base64 the payload stopped being JSON: the
        // account was stored with no id.
        $idToken = $this->idToken(['sub' => '110169484474386276334', 'email' => 'owner@example.com', 'name' => 'Олег Петров ???>>>']);
        $this->assertMatchesRegularExpression('/[-_]/', explode('.', $idToken)[1]);

        $this->googleAnswers(['id_token' => $idToken] + $this->token(self::READ));
        $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect($this->importPage());

        $this->assertSame('110169484474386276334', $this->owner->fresh()->google_id);
    }

    public function test_what_a_connection_may_do_follows_what_google_granted(): void
    {
        $this->assertFalse($this->owner->googleCanWrite(), 'not connected');
        $this->assertFalse($this->connect($this->owner, self::READ)->googleCanWrite());
        $this->assertTrue($this->connect($this->owner, self::READ.' '.Calendar::CALENDAR_EVENTS)->googleCanWrite());
        $this->assertTrue($this->connect($this->owner, Calendar::CALENDAR)->googleCanWrite());
        // Connected before the grant was recorded: every connection then asked for everything.
        $this->assertTrue($this->connect($this->owner, null)->googleCanWrite());
        // Not something a form can set.
        $this->assertNotContains('google_token_scopes', (new User)->getFillable());
    }

    public function test_a_connection_that_did_not_happen_says_so_on_the_page_it_was_started_from(): void
    {
        // Cancel on Google's screen: no code comes back.
        $this->googleAnswers($this->token(self::READ));
        $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['error' => 'access_denied', 'state' => 'state-1']))
            ->assertRedirect($this->importPage())
            ->assertSessionHas('google_import_error', __('messages.google_connect_cancelled'))
            ->assertSessionMissing('error');

        // The calendar permission unticked on Google's screen: nothing a connection could do.
        $working = $this->connect($this->owner, null);
        $this->googleAnswers($this->token('openid email profile'));
        $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect($this->importPage())
            ->assertSessionHas('google_import_error', __('messages.google_connect_no_calendar_access'));
        $this->assertSame($working->google_token, $this->owner->fresh()->google_token, 'a working connection is not replaced by a useless one');

        // Google's own words for a failure stay in the log.
        $this->googleAnswers(['error' => 'invalid_grant', 'error_description' => 'Bad Request: code was already redeemed']);
        $response = $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']));
        $response->assertSessionHas('google_import_error', __('messages.google_connect_failed'));
        $this->assertStringNotContainsString('redeemed', (string) session('google_import_error'));

        // A state that does not match is somebody else's request.
        $this->withSession($this->returning())
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'other']))
            ->assertSessionHas('google_import_error', __('messages.google_connect_failed'));
    }

    public function test_a_connection_started_in_settings_or_long_ago_lands_in_settings_as_before(): void
    {
        $this->googleAnswers($this->token(self::READ.' '.Calendar::CALENDAR_EVENTS));

        $this->withSession(['google_oauth_state' => 'state-1'])
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect($this->settings())
            ->assertSessionHas('message', __('messages.google_connect_done'));

        // The way back is good for half an hour.
        $this->withSession($this->returning('import', now()->subMinutes(45)->getTimestamp()))
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect($this->settings());

        // From a schedule's settings, back to its integrations.
        $this->withSession($this->returning('settings'))
            ->get(route('google.calendar.callback', ['code' => 'c', 'state' => 'state-1']))
            ->assertRedirect(route('role.edit', ['subdomain' => $this->role->subdomain]).'#section-integrations')
            ->assertSessionHas('message', __('messages.google_connect_done'));
        $this->assertTrue($this->owner->fresh()->googleCanWrite());
    }

    public function test_the_import_page_offers_google_to_the_owner_where_google_is_set_up(): void
    {
        config(['services.google.gemini_key' => 'test-key']);
        $page = fn (array $query = []) => $this->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain] + $query))->assertOk()->getContent();
        $connect = e(route('google.calendar.redirect', ['from' => 'import', 'subdomain' => $this->role->subdomain]));

        $html = $page();
        $this->assertStringContainsString('@click="openGoogle"', $html);
        $this->assertStringContainsString($connect, $html);
        $this->assertStringContainsString(__('messages.import_google_read_only'), $html);
        // It opens on the box unless asked for Google, and says what went wrong on the way back.
        $this->assertStringContainsString('source: "box"', $html);
        $this->assertStringContainsString('source: "google"', $page(['source' => 'google']));
        $failed = $this->withSession(['google_import_error' => 'It did not connect.'])
            ->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain, 'source' => 'google']))->getContent();
        $this->assertStringContainsString('error: "It did not connect."', $failed);

        // An editor who is not the owner: the connection would not be theirs to use.
        $admin = $this->createOwner();
        $this->followRole($admin, $this->role, 'admin');
        $theirs = $this->actingAs($admin)->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain, 'source' => 'google']))->assertOk()->getContent();
        $this->assertStringNotContainsString('@click="openGoogle"', $theirs);
        $this->assertStringNotContainsString($connect, $theirs);
        $this->assertStringContainsString('source: "box"', $theirs);

        // An install with no Google credentials offers nothing it could not do.
        config(['services.google.client_id' => null]);
        $without = $this->actingAs($this->owner)->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain, 'source' => 'google']))->assertOk()->getContent();
        $this->assertStringNotContainsString('@click="openGoogle"', $without);
        $this->assertStringContainsString('source: "box"', $without);
    }

    public function test_the_calendars_are_listed_for_the_owner_with_googles_own_left_out(): void
    {
        $url = route('google.calendar.import_calendars', ['subdomain' => $this->role->subdomain]);

        // Not connected: the page offers the button.
        $this->getJson($url)->assertOk()->assertExactJson(['connected' => false]);

        $this->connect($this->owner, self::READ);
        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('listImportCalendars')->andReturn([
                ['id' => 'owner@example.com', 'name' => 'owner@example.com', 'color' => '#9fe1e7', 'primary' => true, 'access' => 'owner'],
                ['id' => 'en.usa#holiday@group.v.calendar.google.com', 'name' => 'Holidays', 'color' => null, 'primary' => false, 'access' => 'reader'],
                ['id' => 'classes@group.calendar.google.com', 'name' => 'Studio classes', 'color' => '#f691b2', 'primary' => false, 'access' => 'owner'],
            ]);
        });

        $this->getJson($url)->assertOk()->assertJson([
            'connected' => true,
            'account' => 'owner@example.com',
            'can_switch_account' => true,
            'calendars' => [
                ['id' => 'classes@group.calendar.google.com', 'name' => 'Studio classes', 'primary' => false, 'read_only' => false],
                ['id' => 'owner@example.com', 'primary' => true],
            ],
        ])->assertJsonCount(2, 'calendars');

        // An editor who is not the owner has no business with the owner's Google connection.
        $admin = $this->connect($this->createOwner(), self::READ);
        $this->followRole($admin, $this->role, 'admin');
        $this->actingAs($admin)->getJson($url)->assertStatus(403);
        $this->actingAs($admin)->postJson(route('google.calendar.import_events', ['subdomain' => $this->role->subdomain]), ['calendar_id' => 'x'])->assertStatus(403);
    }

    public function test_google_failing_is_not_the_same_as_having_no_calendars(): void
    {
        $this->connect($this->owner, self::READ);
        $url = route('google.calendar.import_calendars', ['subdomain' => $this->role->subdomain]);

        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('listImportCalendars')->andThrow(new \RuntimeException('backendError: secret detail'));
        });
        $response = $this->getJson($url)->assertStatus(422)->assertJson(['connected' => true, 'error' => __('messages.google_import_load_failed')]);
        $this->assertStringNotContainsString('secret detail', $response->getContent());

        // A connection that no longer works is one to make again.
        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(false);
            $mock->shouldNotReceive('listImportCalendars');
        });
        $this->getJson($url)->assertOk()->assertJson(['connected' => false, 'error' => __('messages.google_import_reconnect')]);
    }

    public function test_a_calendars_events_are_previewed_like_a_feed_and_saved_as_google(): void
    {
        $this->connect($this->owner, self::READ);
        $this->get(route('event.show_import_ai', ['subdomain' => $this->role->subdomain]))->assertOk();

        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('listUpcomingEvents')->once()
                ->withArgs(fn ($calendarId, $from, $to) => $calendarId === 'classes@group.calendar.google.com'
                    && $from->format('H:i') === '00:00' && (int) round($from->diffInDays($to)) === 365)
                ->andReturn([
                    'name' => 'Studio classes',
                    'timezone' => 'America/New_York',
                    'events' => [
                        $this->entry(['id' => 'show', 'summary' => 'Friday show', 'location' => 'The Blue Note, 131 W 3rd St, New York']),
                        $this->entry(['id' => 'dentist', 'summary' => 'Dentist', 'visibility' => 'private']),
                        $this->entry(['id' => 'second', 'summary' => 'Second show']),
                    ],
                ]);
        });

        $response = $this->postJson(route('google.calendar.import_events', ['subdomain' => $this->role->subdomain]), ['calendar_id' => 'classes@group.calendar.google.com'])
            ->assertOk()
            ->assertJsonCount(2, 'parsed')
            ->assertJsonPath('meta.source', Event::IMPORT_GOOGLE)
            ->assertJsonPath('meta.host', 'Studio classes')
            ->assertJsonPath('meta.skipped.private', 1)
            ->assertJsonPath('meta.timezone', 'America/New_York');
        $this->assertStringNotContainsString('Dentist', $response->getContent());

        // Saved through the same path as any import, and labelled with where it came from.
        $row = $response->json('parsed.0');
        $this->postJson(route('event.import', ['subdomain' => $this->role->subdomain]), [
            'name' => $row['event_name'],
            'starts_at' => $row['event_date_time'].':00',
            'duration' => 2,
            'schedule_type' => 'one_time',
            'import_token' => $response->json('meta.import_token'),
        ])->assertOk();

        $event = Event::query()->latest('id')->firstOrFail();
        $this->assertSame(Event::IMPORT_GOOGLE, $event->import_source);
        $this->assertNotNull($event->import_batch);
    }

    public function test_a_calendar_with_nothing_upcoming_or_that_google_will_not_give_says_so(): void
    {
        $this->connect($this->owner, self::READ);
        $url = route('google.calendar.import_events', ['subdomain' => $this->role->subdomain]);

        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('listUpcomingEvents')->andReturn(['name' => 'Empty', 'timezone' => 'UTC', 'events' => []]);
        });
        $this->postJson($url, ['calendar_id' => 'empty'])->assertStatus(422)
            ->assertJson(['error' => __('messages.link_import_calendar_empty'), 'reason' => 'calendar_empty']);

        $this->fakeGoogle(function ($mock) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('listUpcomingEvents')->andThrow(new \RuntimeException('404 Not Found: calendar internals'));
        });
        $response = $this->postJson($url, ['calendar_id' => 'gone'])->assertStatus(422)
            ->assertJson(['error' => __('messages.google_import_load_failed'), 'reason' => 'failed']);
        $this->assertStringNotContainsString('internals', $response->getContent());

        $this->postJson($url, [])->assertStatus(422)->assertJsonValidationErrors('calendar_id');
    }

    public function test_a_read_only_connection_is_never_asked_to_write(): void
    {
        $this->connect($this->owner, self::READ);
        $event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);

        // The job that sends an event to Google stops before it reaches Google.
        $service = $this->fakeGoogle(function ($mock) {
            $mock->shouldNotReceive('ensureValidToken');
            $mock->shouldNotReceive('createEvent');
            $mock->shouldNotReceive('updateEvent');
            $mock->shouldNotReceive('deleteEvent');
        });
        (new SyncEventToGoogleCalendar($event, $this->role->fresh(), 'create'))->handle($service);

        // Asking for a direction that writes is refused, and says what to do.
        $this->postJson(route('google.calendar.sync', ['subdomain' => $this->role->subdomain]), ['sync_direction' => 'both'])
            ->assertStatus(422)->assertJson(['error' => __('messages.google_needs_edit_access')]);
        $this->assertNull($this->role->fresh()->sync_direction);

        // Nor is one event sent by hand, or a team member's own calendar pointed at a schedule.
        $this->postJson(route('google.calendar.sync_event', ['subdomain' => $this->role->subdomain, 'eventId' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertStatus(422)->assertJson(['error' => __('messages.google_needs_edit_access')]);
        $elsewhere = $this->createRole($this->createOwner(), 'venue');
        $this->followRole($this->owner, $elsewhere, 'admin');
        $this->postJson(route('google.calendar.member_sync', ['subdomain' => $elsewhere->subdomain]), ['google_calendar_id' => 'mine@example.com'])
            ->assertStatus(422)->assertJson(['error' => __('messages.google_needs_edit_access')]);
        $this->assertNull(\App\Models\RoleUser::where('role_id', $elsewhere->id)->where('user_id', $this->owner->id)->first()->google_calendar_id);

        // A settings save that asks for it anyway keeps the half the connection can carry.
        $this->fakeGoogle(fn ($mock) => $mock->shouldIgnoreMissing());
        $save = fn (string $direction) => $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), [
            'name' => $this->role->name,
            'email' => $this->role->email,
            'timezone' => $this->role->timezone,
            'new_subdomain' => $this->role->subdomain,
            'google_integration_submitted' => '1',
            'google_calendar_id' => 'classes@group.calendar.google.com',
            'sync_direction' => $direction,
        ])->assertRedirect();
        $save('to');
        $this->assertNull($this->role->fresh()->sync_direction, '"to Google" alone is nothing a read-only connection can do');
        $save('both');
        $this->assertSame('from', $this->role->fresh()->sync_direction, '"both ways" keeps the reading half');

        // The settings page turns those two choices off and offers the way to allow them.
        $page = $this->get(route('role.edit', ['subdomain' => $this->role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.google_allow_edit_access'), $page);
        $this->assertStringContainsString(e(route('google.calendar.redirect', ['from' => 'settings', 'subdomain' => $this->role->subdomain])), $page);
        $this->assertSame(2, preg_match_all('/name="sync_direction"\s+value="(to|both)"\s+disabled/', $page));

        // With the full grant none of that applies. (On the signed-in instance: the guard keeps
        // the model it was given.)
        $this->connect($this->owner, null);
        $page = $this->get(route('role.edit', ['subdomain' => $this->role->subdomain]))->assertOk()->getContent();
        $this->assertStringNotContainsString(__('messages.google_allow_edit_access'), $page);
        $this->assertSame(0, preg_match_all('/name="sync_direction"\s+value="(to|both)"\s+disabled/', $page));
    }
}
