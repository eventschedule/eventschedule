<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\GraphicEmailService;
use App\Services\GraphicLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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

    public function draw(): string
    {
        return 'picture '.(++$this->drawn);
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
        $this->assertStringEndsWith('/events-graphic/'.$key.'.png', $url);
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

    public function test_an_address_that_is_not_a_key_is_not_found(): void
    {
        $this->get('/events-graphic/'.str_repeat('c', 40).'.png')->assertNotFound();
        $this->get('/events-graphic/short.png')->assertNotFound();
    }
}
