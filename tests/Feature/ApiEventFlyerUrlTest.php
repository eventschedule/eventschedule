<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * `flyer_image_url` on create and update: the flyer in the same call as the event.
 *
 * The address is somebody else's, so the picture is fetched through the guard every outbound
 * fetch uses, judged by its bytes, and stored as our own file: the event never points at the
 * source. It is fetched before anything is saved, so a picture that cannot be had refuses the
 * request with nothing made. And the object reports the flyer under the same name, so the
 * address of the flyer an event already has must come back as no change.
 *
 * Addresses here are IP literals, so nothing resolves a name, and every one is faked.
 */
class ApiEventFlyerUrlTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SOURCE = 'https://93.184.216.34';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Storage::fake(config('filesystems.default'));
        Http::preventStrayRequests();
        Http::fake([
            '93.184.216.34/poster.jpg' => Http::response($this->picture(40, 30, 'jpeg'), 200, ['Content-Type' => 'image/jpeg']),
            '93.184.216.34/second.png' => Http::response($this->picture(20, 20, 'png'), 200, ['Content-Type' => 'image/png']),
            '93.184.216.34/says-png.png' => Http::response($this->picture(40, 30, 'jpeg'), 200, ['Content-Type' => 'image/png']),
            '93.184.216.34/wide.png' => Http::response($this->picture(3000, 60, 'png'), 200, ['Content-Type' => 'image/png']),
            '93.184.216.34/page.jpg' => Http::response('<html><body>Not a picture</body></html>', 200, ['Content-Type' => 'image/jpeg']),
            '93.184.216.34/gone.jpg' => Http::response('', 404),
            '93.184.216.34/huge.jpg' => Http::response(str_repeat('x', 8 * 1024 * 1024 + 1), 200),
            // Faked so that it is the guard, not the absence of a fake, that keeps these unasked.
            '127.0.0.1/*' => Http::response($this->picture(40, 30, 'jpeg'), 200),
            '169.254.169.254/*' => Http::response($this->picture(40, 30, 'jpeg'), 200),
            '10.0.0.5/*' => Http::response($this->picture(40, 30, 'jpeg'), 200),
        ]);
    }

    private function picture(int $width, int $height, string $type): string
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 30));
        ob_start();
        $type === 'png' ? imagepng($image) : imagejpeg($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return $bytes;
    }

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: Role, 1: array<string, string>} */
    private function schedule(): array
    {
        $owner = $this->createOwner();

        return [$this->createRole($owner, 'talent'), $this->keyFor($owner)];
    }

    private function body(array $overrides = []): array
    {
        return $overrides + ['name' => 'With a poster', 'starts_at' => now()->addWeek()->setTime(18, 0)->format('Y-m-d H:i:s')];
    }

    /** The stored filename of an event's flyer, not the address the accessor makes of it. */
    private function stored(Event|string $event): ?string
    {
        $event = is_string($event) ? Event::find(UrlUtils::decodeId($event)) : $event->fresh();

        return $event->getAttributes()['flyer_image_url'] ?? null;
    }

    /** An event that already has a flyer of ours on disk. */
    private function eventWithFlyer(Role $role): Event
    {
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        Storage::put('public/flyer_old.jpg', $this->picture(40, 30, 'jpeg'));
        $event->forceFill(['flyer_image_url' => 'flyer_old.jpg'])->save();

        return $event->fresh();
    }

    private function url(Event $event): string
    {
        return '/api/events/'.UrlUtils::encodeId($event->id);
    }

    private function asked(string $path): bool
    {
        return Http::recorded(fn ($request) => str_contains($request->url(), $path))->isNotEmpty();
    }

    public function test_a_new_event_gets_the_picture_at_the_address_as_a_flyer_of_our_own(): void
    {
        [$role, $key] = $this->schedule();

        $response = $this->postJson('/api/events/'.$role->subdomain, $this->body(['flyer_image_url' => self::SOURCE.'/poster.jpg']), $key)
            ->assertCreated();

        $name = $this->stored($response->json('data.id'));
        $this->assertMatchesRegularExpression('/^flyer_[a-z0-9]{32}\.jpg$/', (string) $name);
        Storage::assertExists('public/'.$name);
        $this->assertStringEndsWith('/storage/'.$name, $response->json('data.flyer_image_url'));
        $this->assertStringNotContainsString('93.184.216.34', $response->json('data.flyer_image_url'));
        $this->assertTrue($this->asked('/poster.jpg'));
    }

    /** What the address ends in and what its server calls the body are whatever the other side says. */
    public function test_the_kind_of_picture_is_read_from_its_bytes(): void
    {
        [$role, $key] = $this->schedule();

        $id = $this->postJson('/api/events/'.$role->subdomain, $this->body(['flyer_image_url' => self::SOURCE.'/says-png.png']), $key)
            ->assertCreated()
            ->json('data.id');

        $this->assertStringEndsWith('.jpg', $this->stored($id));
    }

    public function test_a_picture_that_cannot_be_had_refuses_the_request_and_nothing_is_made(): void
    {
        [$role, $key] = $this->schedule();

        foreach ([
            '/page.jpg' => 'The address did not return a JPEG, PNG, GIF or WebP image.',
            '/gone.jpg' => 'The image could not be fetched. The address has to be public, answer within 10 seconds and send at most 8 MB.',
            '/huge.jpg' => 'The image is larger than 8 MB.',
        ] as $path => $message) {
            $this->postJson('/api/events/'.$role->subdomain, $this->body(['flyer_image_url' => self::SOURCE.$path, 'venue_name' => 'A venue the event would have made']), $key)
                ->assertStatus(422)
                ->assertJsonPath('errors.flyer_image_url.0', $message);
        }

        $this->assertSame(0, Event::count());
        $this->assertSame(1, Role::count(), 'a venue was made for an event that was refused');
    }

    public function test_an_address_this_server_must_not_ask_is_never_asked(): void
    {
        [$role, $key] = $this->schedule();

        foreach (['http://127.0.0.1/poster.jpg', 'http://169.254.169.254/latest/meta-data/', 'http://10.0.0.5/poster.jpg'] as $address) {
            $this->postJson('/api/events/'.$role->subdomain, $this->body(['flyer_image_url' => $address]), $key)
                ->assertStatus(422)
                ->assertJsonValidationErrors('flyer_image_url');
        }

        foreach (['ftp://93.184.216.34/poster.jpg', 'file:///etc/passwd', 'javascript:alert(1)', 'poster.jpg', ['a'], self::SOURCE.'/'.str_repeat('a', 2048)] as $notAnAddress) {
            $this->postJson('/api/events/'.$role->subdomain, $this->body(['flyer_image_url' => $notAnAddress]), $key)
                ->assertStatus(422)
                ->assertJsonPath('errors.flyer_image_url.0', 'The flyer_image_url must be an http or https address of at most 2048 characters.');
        }

        Http::assertNothingSent();
        $this->assertSame(0, Event::count());
    }

    public function test_an_update_replaces_the_flyer_and_deletes_the_one_it_replaced(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->eventWithFlyer($role);

        $this->putJson($this->url($event), ['flyer_image_url' => self::SOURCE.'/second.png'], $key)->assertOk();

        $name = $this->stored($event);
        $this->assertStringEndsWith('.png', $name);
        Storage::assertExists('public/'.$name);
        Storage::assertMissing('public/flyer_old.jpg');
    }

    /**
     * The object reports the flyer under this name. A client that reads an event and writes it
     * back sends our own address, and that is no change: not a fetch of our own file, and not a
     * new copy of it.
     */
    public function test_the_address_of_the_flyer_it_already_has_changes_nothing(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->eventWithFlyer($role);
        $read = $this->getJson($this->url($event), $key)->assertOk()->json('data');
        $this->assertStringEndsWith('/storage/flyer_old.jpg', $read['flyer_image_url']);

        $this->putJson($this->url($event), ['name' => 'Renamed'] + $read, $key)->assertOk()->assertJsonPath('data.name', 'Renamed');

        $this->assertSame('flyer_old.jpg', $this->stored($event));
        Storage::assertExists('public/flyer_old.jpg');
        Http::assertNothingSent();

        // And an update that does not mention it leaves it alone too.
        $this->putJson($this->url($event), ['name' => 'Renamed again'], $key)->assertOk();
        $this->assertSame('flyer_old.jpg', $this->stored($event));
    }

    public function test_null_takes_the_flyer_off(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->eventWithFlyer($role);

        $this->putJson($this->url($event), ['flyer_image_url' => null], $key)
            ->assertOk()
            ->assertJsonPath('data.flyer_image_url', null);

        $this->assertNull($this->stored($event));
        Storage::assertMissing('public/flyer_old.jpg');

        // Which is also what an event without a flyer reads as, and sends back.
        $this->putJson($this->url($event), ['name' => 'Still none', 'flyer_image_url' => null], $key)->assertOk();
        $this->postJson('/api/events/'.$role->subdomain, $this->body(['name' => 'Born without', 'flyer_image_url' => null]), $key)->assertCreated();
    }

    public function test_an_update_whose_picture_cannot_be_had_changes_nothing(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->eventWithFlyer($role);

        $this->putJson($this->url($event), ['name' => 'Would be renamed', 'flyer_image_url' => self::SOURCE.'/page.jpg'], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('flyer_image_url');

        $this->assertNotSame('Would be renamed', $event->fresh()->name);
        $this->assertSame('flyer_old.jpg', $this->stored($event));
        Storage::assertExists('public/flyer_old.jpg');
    }

    /**
     * Graphic generation decodes a flyer whole, and a multi-megapixel one runs a small worker out
     * of memory. The form has always resized on the way in; the API's upload endpoint kept its
     * own copy of the storing code and did not.
     */
    public function test_a_poster_wider_than_2000px_is_resized_however_it_arrives(): void
    {
        [$role, $key] = $this->schedule();
        $width = fn (Event $event) => getimagesize(Storage::path('public/'.$this->stored($event)))[0];

        $fetched = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->putJson($this->url($fetched), ['flyer_image_url' => self::SOURCE.'/wide.png'], $key)->assertOk();
        $this->assertSame(2000, $width($fetched));

        $uploaded = $this->eventWithFlyer($role);
        $this->post('/api/events/flyer/'.UrlUtils::encodeId($uploaded->id), [
            'flyer_image' => UploadedFile::fake()->image('wide.png', 3000, 60),
        ], $key + ['Accept' => 'application/json'])->assertOk()->assertJsonPath('meta.message', 'Flyer uploaded successfully');
        $this->assertSame(2000, $width($uploaded));
        Storage::assertMissing('public/flyer_old.jpg');
    }

    /**
     * A picture under a name the app does not store (JPEG bytes called poster.txt) passes the
     * endpoint's own rule, which reads the bytes, and is refused on its name. The endpoint used
     * to delete the stored flyer between the two, so the refusal cost the event its flyer.
     */
    public function test_an_upload_that_is_refused_leaves_the_flyer_it_would_have_replaced(): void
    {
        [$role, $key] = $this->schedule();
        $event = $this->eventWithFlyer($role);

        // A real file, not UploadedFile::fake(): the fake reports its type from its NAME, and
        // the endpoint's rule would turn it away before the code this is about.
        $path = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($path, $this->picture(40, 30, 'jpeg'));

        try {
            $this->post('/api/events/flyer/'.UrlUtils::encodeId($event->id), [
                'flyer_image' => new UploadedFile($path, 'poster.txt', null, null, true),
            ], $key + ['Accept' => 'application/json'])
                ->assertStatus(422)
                ->assertJsonPath('errors.flyer_image.0', 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp');
        } finally {
            @unlink($path);
        }

        $this->assertSame('flyer_old.jpg', $this->stored($event));
        Storage::assertExists('public/flyer_old.jpg');
    }
}
