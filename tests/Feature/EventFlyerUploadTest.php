<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A flyer the app will not take is refused before anything is saved.
 *
 * The rule in the two form requests was keyed 'flyer_image_url', and the form's file input is
 * 'flyer_image', so it never ran. The only check was inside EventRepo::saveEvent(), after the event
 * had been written: a new event was created, then the save was reported as refused, and pressing
 * Save again on the form that came back created a second one.
 */
class EventFlyerUploadTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('filesystems.default'));
        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', ['subdomain' => 'flyertalent']);
    }

    private function create(array $data)
    {
        return $this->actingAs($this->owner)->post(route('event.store', ['subdomain' => 'flyertalent']), array_merge([
            'name' => 'With a flyer', 'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'duration' => 2,
        ], $data));
    }

    public function test_a_flyer_of_a_type_that_is_not_taken_creates_no_event(): void
    {
        $this->create(['flyer_image' => UploadedFile::fake()->create('flyer.svg', 20, 'image/svg+xml')])
            ->assertSessionHasErrors('flyer_image');

        $this->assertSame(0, Event::count(), 'refused means nothing was saved');
    }

    /**
     * A real picture under a name that is not a picture's: only the name gives it away. (A fake
     * upload takes its type from its name, so it would be refused for the type and prove nothing
     * about the name.)
     */
    public function test_a_picture_under_a_name_that_is_not_a_pictures_is_refused_too(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'flyer');
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
        $upload = new UploadedFile($path, 'flyer.tiff', 'image/png', null, true);
        $this->assertSame('image/png', $upload->getMimeType(), 'sanity check: it is a picture');

        $this->create(['flyer_image' => $upload])->assertSessionHasErrors('flyer_image');

        $this->assertSame(0, Event::count());
        @unlink($path);
    }

    /** And the other way round: a name that looks like a picture's on something that is not one. */
    public function test_something_that_is_not_a_picture_under_a_pictures_name_is_refused(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'flyer');
        file_put_contents($path, '<?php echo "not a picture";');
        $upload = new UploadedFile($path, 'flyer.jpg', 'image/jpeg', null, true);

        $this->create(['flyer_image' => $upload])->assertSessionHasErrors('flyer_image');

        $this->assertSame(0, Event::count());
        @unlink($path);
    }

    public function test_a_flyer_that_is_taken_still_saves(): void
    {
        foreach (['flyer.jpg', 'flyer.png', 'FLYER.JPEG', 'flyer.gif', 'flyer.webp'] as $name) {
            $this->create(['name' => 'Flyer '.$name, 'flyer_image' => UploadedFile::fake()->image($name, 600, 400)])
                ->assertSessionHasNoErrors();
            $this->assertNotNull(Event::where('name', 'Flyer '.$name)->first()?->getAttributes()['flyer_image_url'], $name.' was stored');
        }
    }

    /** A large photo was never refused for its size (it is resized on the way in), and still is not. */
    public function test_a_large_flyer_is_not_refused_for_its_size(): void
    {
        $this->create(['flyer_image' => UploadedFile::fake()->image('big.jpg', 600, 400)->size(6000)])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Event::count());
    }

    public function test_an_existing_event_is_not_touched_by_a_refused_flyer(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Before']);

        $this->actingAs($this->owner)->put(route('event.update', ['subdomain' => 'flyertalent', 'hash' => UrlUtils::encodeId($event->id)]), [
            'name' => 'After', 'starts_at' => $event->starts_at, 'duration' => 2,
            'flyer_image' => UploadedFile::fake()->create('flyer.svg', 20, 'image/svg+xml'),
        ])->assertSessionHasErrors('flyer_image');

        $this->assertSame('Before', $event->fresh()->name);
    }
}
