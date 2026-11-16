<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\GraphicEmailService;
use App\Services\GraphicLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Events graphic at a fixed public address (App\Services\GraphicLink): the saved graphic,
 * drawn ahead of time and handed out by key, for a screen or a website.
 *
 * What has to hold: the key is the server's and never the request's; the address answers only
 * while a live Enterprise schedule has the link on; nothing is drawn inside the public request;
 * switching off removes the picture and the next address is a new one.
 */
class GraphicLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $drawn = 0;

    /** False while no upcoming event has a flyer: there is nothing to draw. */
    private bool $hasFlyers = true;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Storage::fake();

        // The drawing itself is EventGraphicGenerator's, held by its own tests: here it is a
        // counter and a few bytes, so each test can say when a picture was drawn and which.
        $test = $this;
        $this->app->bind(GraphicEmailService::class, fn () => new class($test) extends GraphicEmailService
        {
            public function __construct(private GraphicLinkTest $test) {}

            public function posterImage(Role $role): ?string
            {
                return $this->test->draw();
            }
        });
    }

    public function draw(): ?string
    {
        return $this->hasFlyers ? 'picture '.(++$this->drawn) : null;
    }

    private function saveUrl(Role $role): string
    {
        return route('event.save_graphic_settings', ['subdomain' => $role->subdomain]);
    }

    /** @return array{0: Role, 1: User, 2: string} the schedule, its owner and the link's address */
    private function scheduleWithLink(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->assertTrue($role->fresh()->isEnterprise());

        $url = $this->actingAs($owner)->postJson($this->saveUrl($role), ['link_enabled' => true])
            ->assertOk()->json('link_url');
        $this->assertNotEmpty($url);

        return [$role->fresh(), $owner, $url];
    }

    public function test_switching_the_link_on_draws_the_picture_and_gives_its_address(): void
    {
        [$role, , $url] = $this->scheduleWithLink();

        $key = $role->graphic_settings['link_key'];
        $this->assertTrue(GraphicLink::validKey($key));
        $this->assertStringEndsWith('/events/graphic/'.$key.'.png', $url);
        $this->assertStringNotContainsString($role->subdomain, $url);

        $response = $this->get($url)->assertOk();
        $this->assertSame('picture 1', $response->getContent());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('max-age=300', $response->headers->get('Cache-Control'));
        // A screen keeps no cookie: a session per request would be a row per request.
        $this->assertEmpty($response->headers->getCookies());

        // The page hands the saved address to its script.
        $html = $this->get(route('event.generate_graphic', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString($key, $html);
    }

    public function test_the_public_request_draws_nothing_and_a_later_save_keeps_the_address(): void
    {
        [$role, $owner, $url] = $this->scheduleWithLink();

        $this->get($url)->assertOk();
        $this->get($url)->assertOk();
        $this->assertSame(1, $this->drawn, 'the address handed out a picture it drew itself');

        // Saving anything else draws the picture again, at the same address.
        $again = $this->actingAs($owner)->postJson($this->saveUrl($role), ['layout' => 'row'])->assertOk()->json('link_url');
        $this->assertSame($url, $again);
        $this->assertSame('picture 2', $this->get($url)->getContent());
    }

    public function test_the_key_is_never_taken_from_the_request(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $chosen = str_repeat('a', 40);

        $this->actingAs($owner)->postJson($this->saveUrl($role), ['link_enabled' => true, 'link_key' => $chosen])->assertOk();

        $this->assertNotSame($chosen, $role->fresh()->graphic_settings['link_key']);
        $this->get(route('graphic.link', ['key' => $chosen]))->assertNotFound();
    }

    public function test_switching_off_removes_the_picture_and_on_again_is_a_new_address(): void
    {
        [$role, $owner, $url] = $this->scheduleWithLink();
        $key = $role->graphic_settings['link_key'];
        $this->get($url)->assertOk();

        $this->actingAs($owner)->postJson($this->saveUrl($role), ['link_enabled' => false])
            ->assertOk()->assertJsonPath('link_url', null);

        $this->assertArrayNotHasKey('link_key', $role->fresh()->graphic_settings);
        Storage::assertMissing(GraphicLink::path($key));
        $this->get($url)->assertNotFound();

        $new = $this->actingAs($owner)->postJson($this->saveUrl($role), ['link_enabled' => true])->assertOk()->json('link_url');
        $this->assertNotSame($url, $new);
        $this->get($new)->assertOk();
        $this->get($url)->assertNotFound();
    }

    public function test_a_plan_without_it_cannot_switch_it_on_and_a_lapsed_one_stops_answering(): void
    {
        $free = $this->createFreeRole(null, 'venue');
        $owner = User::find($free->user_id);

        $this->actingAs($owner)->postJson($this->saveUrl($free), ['link_enabled' => true])
            ->assertOk()->assertJsonPath('link_url', null);
        $this->assertArrayNotHasKey('link_key', $free->fresh()->graphic_settings);
        $this->assertSame(0, $this->drawn);

        // What an Enterprise plan left behind: the switch, the key and the picture.
        $key = str_repeat('b', 40);
        $free->graphic_settings = ['link_enabled' => true, 'link_key' => $key];
        $free->save();
        Storage::put(GraphicLink::path($key), 'left behind');

        $this->get(route('graphic.link', ['key' => $key]))->assertNotFound();

        // A save from that plan keeps the address for the plan's return, and draws nothing.
        $this->actingAs($owner)->postJson($this->saveUrl($free), ['layout' => 'row', 'link_enabled' => false])->assertOk();
        $this->assertSame($key, $free->fresh()->graphic_settings['link_key']);
        $this->assertTrue($free->fresh()->graphic_settings['link_enabled']);
        $this->assertSame(0, $this->drawn);
    }

    /**
     * The key was settled, and the old picture deleted, before the checks that can still refuse
     * the save: switching the link off in a save the server refused left the link on, under its
     * old address, with nothing behind it.
     */
    public function test_a_refused_save_leaves_the_picture_where_it_is(): void
    {
        [$role, $owner, $url] = $this->scheduleWithLink();
        $key = $role->graphic_settings['link_key'];

        $this->actingAs($owner)->postJson($this->saveUrl($role), [
            'link_enabled' => false, 'enabled' => true, 'frequency' => 'daily', 'recipient_emails' => 'not an address',
        ])->assertStatus(422);

        $this->assertSame($key, $role->fresh()->graphic_settings['link_key']);
        Storage::assertExists(GraphicLink::path($key));
        $this->get($url)->assertOk();
    }

    public function test_an_address_with_no_picture_yet_is_said_to_be_empty(): void
    {
        $this->hasFlyers = false;
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        $saved = $this->actingAs($owner)->postJson($this->saveUrl($role), ['link_enabled' => true])->assertOk();
        $this->assertNotEmpty($saved->json('link_url'));
        $saved->assertJsonPath('link_ready', false);

        // Not found, and not to be remembered by a cache in between: the picture may come within the hour.
        $missing = $this->get($saved->json('link_url'))->assertNotFound();
        $this->assertStringContainsString('no-store', $missing->headers->get('Cache-Control'));

        $html = $this->get(route('event.generate_graphic', ['subdomain' => $role->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('"linkReady":false', $html);
        $this->assertStringContainsString(e(__('messages.graphic_link_empty')), $html);

        // An event gets a flyer: the next save (or the next hour) draws it, at the same address.
        $this->hasFlyers = true;
        $this->actingAs($owner)->postJson($this->saveUrl($role), ['layout' => 'row'])
            ->assertOk()->assertJsonPath('link_ready', true)->assertJsonPath('link_url', $saved->json('link_url'));

        // And once there has been one, a spell with nothing to draw leaves it in place.
        $this->hasFlyers = false;
        $this->actingAs($owner)->postJson($this->saveUrl($role), ['layout' => 'grid'])->assertOk()->assertJsonPath('link_ready', true);
        $this->get($saved->json('link_url'))->assertOk();
    }

    public function test_removing_the_header_image_draws_the_picture_again(): void
    {
        [$role, $owner, $url] = $this->scheduleWithLink();
        $settings = $role->graphic_settings;
        $settings['header_image_url'] = 'graphic_header_test.png';
        $role->graphic_settings = $settings;
        $role->save();

        $this->actingAs($owner)->deleteJson(route('event.graphic_delete_header_image', ['subdomain' => $role->subdomain]))->assertOk();

        $this->assertSame('picture 2', $this->get($url)->getContent());
    }

    /**
     * The command loads every schedule when it starts and stamps last_sent_at when a send is
     * done. Stamped onto the copy from the start of the run, it put back whatever the owner had
     * changed meanwhile: a link switched off was on again, under its retired key.
     */
    public function test_the_email_stamp_does_not_put_back_a_link_switched_off_during_the_run(): void
    {
        [$role, $owner] = $this->scheduleWithLink();
        $settings = $role->graphic_settings;
        $role->graphic_settings = array_merge($settings, ['enabled' => true, 'frequency' => 'daily', 'send_hour' => 0, 'recipient_emails' => 'owner@gmail.com']);
        $role->save();

        // The send is where the run spends its time; the owner's save lands in the middle of it.
        $test = $this;
        $this->app->bind(GraphicEmailService::class, fn () => new class($test) extends GraphicEmailService
        {
            public function __construct(private GraphicLinkTest $test) {}

            public function posterImage(Role $role): ?string
            {
                return 'picture';
            }

            public function sendGraphicEmail(Role $role, string $recipientEmails): bool
            {
                $this->test->switchOffMeanwhile($role);

                return true;
            }
        });

        Artisan::call('app:send-graphic-emails');

        $stored = $role->fresh()->graphic_settings;
        $this->assertNotEmpty($stored['last_sent_at'] ?? null, 'the send was not stamped, so this test saw nothing');
        $this->assertFalse((bool) ($stored['link_enabled'] ?? false), 'the run switched the link back on');
        $this->assertArrayNotHasKey('link_key', $stored);
    }

    public function switchOffMeanwhile(Role $role): void
    {
        $fresh = Role::find($role->id);
        $settings = $fresh->graphic_settings;
        $settings['link_enabled'] = false;
        unset($settings['link_key']);
        $fresh->graphic_settings = $settings;
        $fresh->save();
    }

    public function test_a_deleted_schedule_stops_answering(): void
    {
        [$role, , $url] = $this->scheduleWithLink();
        $this->get($url)->assertOk();

        $role->is_deleted = true;
        $role->save();
        // The answer is remembered for five minutes; after that the address is closed.
        Cache::flush();

        $this->get($url)->assertNotFound();
    }

    public function test_the_hourly_command_draws_it_again_with_no_email_set_up(): void
    {
        [$role, , $url] = $this->scheduleWithLink();
        $this->assertEmpty($role->graphic_settings['enabled'] ?? false);

        // A schedule without the link is left alone.
        $this->createRole($this->createOwner(), 'venue');

        Artisan::call('app:send-graphic-emails');

        $this->assertSame(2, $this->drawn);
        $this->assertSame('picture 2', $this->get($url)->getContent());
    }

    /**
     * graphic_settings is a TEXT column. Looked up by JSON path, one row that is not JSON made
     * MySQL fail the whole query: every public link on the install, for one bad row anywhere.
     */
    public function test_a_row_that_is_not_json_does_not_break_every_link(): void
    {
        [, , $url] = $this->scheduleWithLink();

        $other = $this->createRole($this->createOwner(), 'venue');
        DB::table('roles')->where('id', $other->id)->update(['graphic_settings' => '']);
        $another = $this->createRole($this->createOwner(), 'venue');
        DB::table('roles')->where('id', $another->id)->update(['graphic_settings' => 'not json']);

        $this->get($url)->assertOk();
    }

    public function test_the_address_says_how_long_to_keep_the_picture_itself(): void
    {
        [, , $url] = $this->scheduleWithLink();

        // Apache's mod_expires gives any image/png a month unless the response already has an Expires.
        $expires = strtotime($this->get($url)->assertOk()->headers->get('Expires'));
        $this->assertGreaterThan(time() + 200, $expires);
        $this->assertLessThan(time() + 400, $expires);
    }

    /**
     * On the hosted install the default disk is a public bucket behind a CDN, where every object
     * is served by its key and kept for a year. The picture is written private and uncached, so
     * the app's address is the only one that hands it out.
     */
    public function test_the_picture_is_stored_private(): void
    {
        [$role] = $this->scheduleWithLink();

        $this->assertSame('private', Storage::getVisibility(GraphicLink::path($role->graphic_settings['link_key'])));
    }

    public function test_an_address_that_is_not_a_key_is_not_found(): void
    {
        $this->get('/events/graphic/'.str_repeat('c', 40).'.png')->assertNotFound();
        $this->get('/events/graphic/short.png')->assertNotFound();
    }
}
