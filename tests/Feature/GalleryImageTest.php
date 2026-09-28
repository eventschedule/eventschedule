<?php

namespace Tests\Feature;

use App\Models\EventPhoto;
use App\Models\GalleryImage;
use App\Models\Role;
use App\Models\User;
use App\Utils\GalleryUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The organizer photo gallery (GalleryImage): uploads land as drafts, the edit form's Save
 * commits the ones it lists (GalleryUtils::sync()), and every way a row can disappear takes its
 * file with it.
 */
class GalleryImageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    private const TOKEN = 'abcdefabcdefabcdefabcdefabcdef12';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));
        Queue::fake();
    }

    private function upload(User $user, Role $role, array $params = [], ?UploadedFile $file = null)
    {
        return $this->actingAs($user)->post(route('gallery.upload', ['subdomain' => $role->subdomain]), $params + [
            'target' => 'schedule',
            'draft_token' => self::TOKEN,
            'photo' => $file ?? UploadedFile::fake()->image('stage.jpg', 1200, 800),
        ]);
    }

    private function draft(Role $role, User $user, array $attrs = []): GalleryImage
    {
        $filename = 'gallery_'.strtolower(\Illuminate\Support\Str::random(32)).'.jpg';
        Storage::put(\App\Utils\ImageUtils::storagePathFor($filename), 'bytes');

        return GalleryImage::create($attrs + [
            'role_id' => $role->id,
            'user_id' => $user->id,
            'filename' => $filename,
            'width' => 1200,
            'height' => 800,
            'draft_token' => self::TOKEN,
        ]);
    }

    private function payload(array $images): string
    {
        return json_encode(array_map(fn ($image) => is_array($image) ? $image : ['id' => UrlUtils::encodeId($image->id)], $images));
    }

    private function assertStored(GalleryImage $image): void
    {
        Storage::assertExists(\App\Utils\ImageUtils::storagePathFor($image->getAttributes()['filename']));
    }

    private function assertGone(GalleryImage $image): void
    {
        Storage::assertMissing(\App\Utils\ImageUtils::storagePathFor($image->getAttributes()['filename']));
        $this->assertNull(GalleryImage::find($image->id));
    }

    // ----- Uploads ---------------------------------------------------------------------------

    public function test_an_upload_is_stored_as_a_draft_of_the_uploader(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $response = $this->upload($owner, $role)->assertOk()->assertJsonPath('success', true);

        $image = GalleryImage::firstOrFail();
        $this->assertSame(self::TOKEN, $image->draft_token);
        $this->assertSame($owner->id, $image->user_id);
        $this->assertSame($role->id, $image->role_id);
        $this->assertNull($image->event_id);
        $this->assertSame(1200, $image->width);
        $this->assertSame(800, $image->height);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', (string) $image->color);
        $this->assertStringStartsWith('gallery_', $image->filename);
        $this->assertStored($image);
        $this->assertSame(UrlUtils::encodeId($image->id), $response->json('image.id'));
        $this->assertArrayNotHasKey('draft_token', $response->json('image'));

        // Nothing is shown until the form is saved.
        $this->assertTrue($role->galleryImages()->doesntExist());
    }

    public function test_an_uploaded_jpeg_is_re_encoded_so_its_exif_is_gone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // A small JPEG with an APP1/EXIF segment carrying a marker string, below the size limit
        // where resizeImageToMax() used to return early and keep the bytes untouched.
        $gd = imagecreatetruecolor(300, 200);
        ob_start();
        imagejpeg($gd, null, 90);
        $jpeg = ob_get_clean();
        $exif = "Exif\0\0".'GPS-SECRET-MARKER';
        $app1 = "\xFF\xE1".pack('n', strlen($exif) + 2).$exif;
        $withExif = substr($jpeg, 0, 2).$app1.substr($jpeg, 2);
        $path = tempnam(sys_get_temp_dir(), 'exif').'.jpg';
        file_put_contents($path, $withExif);

        $this->upload($owner, $role, [], new UploadedFile($path, 'phone.jpg', 'image/jpeg', null, true))->assertOk();

        $stored = Storage::get(\App\Utils\ImageUtils::storagePathFor(GalleryImage::firstOrFail()->filename));
        $this->assertStringNotContainsString('GPS-SECRET-MARKER', $stored);
    }

    public function test_a_free_hosted_schedule_cannot_upload(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->assertFalse($role->fresh()->isPro());

        $this->upload($owner, $role)->assertForbidden();
        $this->assertSame(0, GalleryImage::count());
    }

    public function test_a_viewer_member_cannot_upload(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->upload($viewer, $role)->assertForbidden();
        $this->assertSame(0, GalleryImage::count());
    }

    public function test_an_upload_to_an_event_needs_permission_on_that_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $other = $this->createOwner();
        $otherRole = $this->createRole($other);
        $otherEvent = $this->createEvent($otherRole, ['creator_role_id' => $otherRole->id]);

        $this->upload($owner, $role, ['target' => 'event', 'event' => UrlUtils::encodeId($otherEvent->id)])->assertForbidden();
        $this->assertSame(0, GalleryImage::count());
    }

    public function test_a_malformed_token_is_refused(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->upload($owner, $role, ['draft_token' => 'not-a-token'])->assertStatus(422);
        $this->assertSame(0, GalleryImage::count());
    }

    public function test_a_file_that_is_not_an_image_is_refused(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->upload($owner, $role, [], UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))->assertStatus(422);
        $this->assertSame(0, GalleryImage::count());
    }

    // ----- Committing on Save ----------------------------------------------------------------

    public function test_sync_commits_the_posted_order_with_captions_and_credits(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $a = $this->draft($role, $owner);
        $b = $this->draft($role, $owner);

        $result = GalleryUtils::sync($role, null, $this->payload([
            ['id' => UrlUtils::encodeId($b->id), 'caption' => '  The main   room ', 'credit' => 'Ana Photo'],
            ['id' => UrlUtils::encodeId($a->id), 'caption' => '', 'credit' => ''],
        ]), self::TOKEN, $owner);

        $this->assertSame(['before' => 0, 'after' => 2, 'missing' => 0, 'refused_limit' => 0, 'refused_plan' => 0, 'changed' => true], $result);
        $this->assertSame([$b->id, $a->id], $role->galleryImages()->pluck('id')->all());
        $this->assertSame('The main room', $b->fresh()->caption);
        $this->assertSame('Ana Photo', $b->fresh()->credit);
        $this->assertNull($a->fresh()->caption);
        $this->assertNull($a->fresh()->draft_token);
    }

    public function test_sync_deletes_unlisted_rows_with_their_files(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $kept = $this->draft($role, $owner, ['draft_token' => null]);
        $removed = $this->draft($role, $owner, ['draft_token' => null]);
        $abandonedDraft = $this->draft($role, $owner);

        GalleryUtils::sync($role, null, $this->payload([$kept]), self::TOKEN, $owner);

        $this->assertStored($kept);
        $this->assertGone($removed);
        $this->assertGone($abandonedDraft);
    }

    public function test_sync_ignores_ids_that_are_not_this_gallery_or_this_users_drafts(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stranger = $this->createOwner();
        $strangerRole = $this->createRole($stranger);

        $foreignCommitted = $this->draft($strangerRole, $stranger, ['draft_token' => null]);
        $foreignDraftSameToken = $this->draft($strangerRole, $stranger);

        $result = GalleryUtils::sync($role, null, $this->payload([$foreignCommitted, $foreignDraftSameToken]), self::TOKEN, $owner);

        $this->assertSame(0, $result['after']);
        $this->assertSame(2, $result['missing']);
        $this->assertSame($strangerRole->id, $foreignCommitted->fresh()->role_id, 'another schedule\'s photo is not taken');
        $this->assertSame(self::TOKEN, $foreignDraftSameToken->fresh()->draft_token, 'another user\'s draft is not committed');
    }

    public function test_sync_truncates_to_the_limit(): void
    {
        config(['app.max_gallery_images' => 2]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $drafts = [$this->draft($role, $owner), $this->draft($role, $owner), $this->draft($role, $owner)];

        $result = GalleryUtils::sync($role, null, $this->payload($drafts), self::TOKEN, $owner);

        $this->assertSame([$drafts[0]->id, $drafts[1]->id], $role->galleryImages()->pluck('id')->all());
        $this->assertGone($drafts[2]);
        $this->assertSame(1, $result['refused_limit']);
        $this->assertNotNull(GalleryUtils::errorMessage($result), 'the save says a photo was not added');
    }

    /**
     * A merge, or a lowered MAX_GALLERY_IMAGES, can leave a gallery over the limit. A save must
     * never delete committed photos for that: only NEW drafts are held to the limit.
     */
    public function test_a_gallery_already_over_the_limit_keeps_every_photo_it_lists(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $rows = [];
        foreach (range(0, 3) as $i) {
            $rows[] = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => $i]);
        }
        $newDraft = $this->draft($role, $owner);

        config(['app.max_gallery_images' => 2]);
        $result = GalleryUtils::sync($role, null, $this->payload([...$rows, $newDraft]), self::TOKEN, $owner);

        foreach ($rows as $row) {
            $this->assertStored($row);
        }
        $this->assertSame(4, $role->galleryImages()->count());
        $this->assertGone($newDraft);
        $this->assertSame(1, $result['refused_limit']);
    }

    /**
     * Admin B adds a photo while admin A has the form open. A's save lists only what A's form was
     * rendered with, and must leave B's photo alone rather than delete it as "unlisted".
     */
    public function test_a_stale_form_keeps_photos_added_after_it_was_opened(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $known = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 0]);
        $removedByA = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 1]);
        $addedByB = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 2]);

        $knownIds = json_encode([UrlUtils::encodeId($known->id), UrlUtils::encodeId($removedByA->id)]);
        $result = GalleryUtils::sync($role, null, $this->payload([$known]), self::TOKEN, $owner, $knownIds);

        $this->assertStored($known);
        $this->assertStored($addedByB);
        $this->assertGone($removedByA);
        $this->assertSame([$known->id, $addedByB->id], $role->galleryImages()->pluck('id')->all());
        $this->assertSame(0, $result['missing']);
    }

    public function test_a_listed_photo_somebody_else_removed_is_not_reported_as_lost(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $gone = $this->draft($role, $owner, ['draft_token' => null]);
        $goneId = UrlUtils::encodeId($gone->id);
        $gone->delete();

        $result = GalleryUtils::sync($role, null, json_encode([['id' => $goneId]]), self::TOKEN, $owner, json_encode([$goneId]));

        $this->assertSame(0, $result['missing']);
        $this->assertNull(GalleryUtils::errorMessage($result));
    }

    public function test_malformed_ids_are_ignored_rather_than_failing_the_save(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $image = $this->draft($role, $owner, ['draft_token' => null]);

        $result = GalleryUtils::sync($role, null, json_encode([['id' => [1]], ['id' => UrlUtils::encodeId($image->id)]]), self::TOKEN, $owner, json_encode([[2]]));

        $this->assertSame(1, $result['after']);
        $this->assertStored($image);

        $this->actingAs($owner)->post(route('gallery.from_fan_photos', ['subdomain' => $role->subdomain]), [
            'target' => 'event', 'event' => ['x'], 'draft_token' => self::TOKEN, 'photo_ids' => [['nested']],
        ])->assertStatus(403);
    }

    public function test_a_file_over_php_upload_limit_gets_the_too_large_message(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $file = new UploadedFile(UploadedFile::fake()->image('big.jpg')->getRealPath(), 'big.jpg', 'image/jpeg', UPLOAD_ERR_INI_SIZE, true);

        $this->upload($owner, $role, [], $file)
            ->assertStatus(422)
            ->assertJsonPath('error', __('messages.gallery_too_large', ['size' => round(GalleryUtils::maxUploadBytes() / 1048576, 1).' MB']));
    }

    /**
     * A JPEG that cannot be re-encoded would be published with its EXIF block - GPS included - so
     * it is refused. Its header claims far more pixels than GD may decode.
     */
    public function test_a_photo_that_cannot_be_re_encoded_is_refused_rather_than_published_with_its_exif(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $gd = imagecreatetruecolor(40, 30);
        ob_start();
        imagejpeg($gd);
        $jpeg = ob_get_clean();
        // Rewrite the SOF0 frame size to 20000 x 20000 (400MP, past IMAGE_MAX_PIXELS_CEILING).
        $sof = strpos($jpeg, "\xFF\xC0");
        $jpeg = substr_replace($jpeg, pack('n', 20000).pack('n', 20000), $sof + 5, 4);
        $path = tempnam(sys_get_temp_dir(), 'huge').'.jpg';
        file_put_contents($path, $jpeg);
        $this->assertSame(20000, getimagesize($path)[0], 'fixture: the header claims the huge size');

        $this->upload($owner, $role, [], new UploadedFile($path, 'huge.jpg', 'image/jpeg', null, true))->assertStatus(422);
        $this->assertSame(0, GalleryImage::count());
        $this->assertSame([], Storage::allFiles());
    }

    public function test_sync_touches_the_schedule_so_its_lastmod_moves(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $draft = $this->draft($role, $owner);

        Carbon::setTestNow('2026-09-05 10:00:00');
        GalleryUtils::sync($role, null, $this->payload([$draft]), self::TOKEN, $owner);

        $this->assertSame('2026-09-05', $role->fresh()->updated_at->toDateString());
        Carbon::setTestNow();
    }

    public function test_a_form_without_the_gallery_field_leaves_it_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $image = $this->draft($role, $owner, ['draft_token' => null]);

        $this->assertNull(GalleryUtils::sync($role, null, null, self::TOKEN, $owner));
        $this->assertStored($image);
    }

    public function test_a_downgraded_schedule_can_remove_photos_but_not_add_or_rearrange(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $first = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 0]);
        $second = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 1]);
        $removed = $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 2]);
        $newDraft = $this->draft($role, $owner);

        GalleryUtils::sync($role, null, $this->payload([
            ['id' => UrlUtils::encodeId($newDraft->id)],
            ['id' => UrlUtils::encodeId($second->id), 'caption' => 'Changed'],
            ['id' => UrlUtils::encodeId($first->id)],
        ]), self::TOKEN, $owner);

        $this->assertGone($removed);
        $this->assertGone($newDraft);
        $this->assertSame([$first->id, $second->id], GalleryImage::where('role_id', $role->id)->orderBy('sort_order')->pluck('id')->all());
        $this->assertNull($second->fresh()->caption);
        $this->assertFalse($role->fresh()->showsGallery(), 'hidden from guests while not Pro');
    }

    public function test_saving_an_event_commits_its_gallery_and_flashes_the_first_publish(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $draft = $this->draft($role, $owner, ['event_id' => $event->id]);

        $this->putUpdateEvent($owner, $role, $event, [
            'gallery_images' => $this->payload([$draft]),
            'gallery_draft_token' => self::TOKEN,
        ])->assertRedirect()->assertSessionHas('gallery_published', fn ($flash) => $flash['count'] === 1);

        $this->assertSame([$draft->id], $event->fresh()->galleryImages->pluck('id')->all());

        // A later save that adds nothing new to an already-published gallery gets the toast only.
        $this->putUpdateEvent($owner, $role, $event, [
            'gallery_images' => $this->payload([$draft]),
            'gallery_draft_token' => self::TOKEN,
        ])->assertSessionMissing('gallery_published');
    }

    public function test_creating_an_event_commits_the_drafts_its_form_uploaded(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $draft = $this->draft($role, $owner);

        $this->postCreateEvent($owner, $role, [
            'gallery_images' => $this->payload([$draft]),
            'gallery_draft_token' => self::TOKEN,
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertSame([$draft->id], $event->galleryImages->pluck('id')->all());
        $this->assertTrue($event->showsGallery());
    }

    public function test_a_save_naming_a_pruned_draft_says_so(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->putUpdateEvent($owner, $role, $event, [
            'gallery_images' => json_encode([['id' => UrlUtils::encodeId(999999)]]),
            'gallery_draft_token' => self::TOKEN,
        ])->assertSessionHas('error');
    }

    // ----- Who decides what guests see --------------------------------------------------------

    public function test_the_event_gallery_follows_the_owning_schedules_plan_not_the_viewing_one(): void
    {
        $owner = $this->createOwner();
        $freeOwner = $this->createFreeRole($owner, 'talent');
        $curator = $this->createRole($owner, 'curator');
        $event = $this->createEvent($freeOwner, ['creator_role_id' => $freeOwner->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->draft($freeOwner, $owner, ['event_id' => $event->id, 'draft_token' => null]);

        $this->assertFalse($event->fresh()->showsGallery(), 'a Pro curator listing it does not unlock it');

        $freeOwner->forceFill(['plan_type' => 'pro', 'plan_expires' => now()->addYear()->format('Y-m-d')])->save();
        $this->assertTrue($event->fresh()->showsGallery());
    }

    public function test_the_event_json_ld_lists_the_gallery_only_when_there_is_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $node = $event->fresh()->schemaNode(null, $role, 'en');
        $this->assertTrue(! isset($node['image']) || ! array_is_list($node['image']), 'a single ImageObject (or none) without a gallery');

        $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null, 'caption' => 'Front row', 'credit' => 'Ana']);
        $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null, 'sort_order' => 1]);

        $node = $event->fresh()->schemaNode(null, $role, 'en');
        $this->assertTrue(array_is_list($node['image']));
        $this->assertCount(2, $node['image'], 'with no flyer, the first photo is the share image and is not listed twice');
        $this->assertSame('Ana', $node['image'][0]['creditText']);
        $this->assertSame('Front row', $node['image'][0]['caption']);

        $compact = $event->fresh()->schemaNode(null, $role, 'en', compact: true);
        $this->assertTrue(! isset($compact['image']) || ! array_is_list($compact['image']), 'compact nodes never list the gallery (nor query it)');
    }

    public function test_without_a_flyer_the_first_photo_is_the_share_image(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $first = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null]);

        $share = $event->fresh()->shareImage($role);

        $this->assertSame($first->url(), $share['url']);
        $this->assertSame(1200, $share['width']);
    }

    // ----- Every delete takes the files -------------------------------------------------------

    public function test_deleting_an_event_deletes_its_gallery_files(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $image = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null]);
        $draft = $this->draft($role, $owner, ['event_id' => $event->id]);

        $event->delete();

        $this->assertGone($image);
        $this->assertGone($draft);
    }

    public function test_deleting_a_schedule_deletes_its_gallery_and_its_events_galleries(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $own = $this->draft($role, $owner, ['draft_token' => null]);
        $ofEvent = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null]);

        $role->delete();

        $this->assertGone($own);
        $this->assertGone($ofEvent);
    }

    public function test_deleting_an_account_deletes_its_gallery_files(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $own = $this->draft($role, $owner, ['draft_token' => null]);
        $ofEvent = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null]);

        $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password']);

        $this->assertNull(User::find($owner->id), 'sanity check: the account was deleted');
        $this->assertGone($own);
        $this->assertGone($ofEvent);
    }

    public function test_the_prune_command_removes_old_drafts_only(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $old = $this->draft($role, $owner);
        $old->forceFill(['created_at' => now()->subDays(8)])->saveQuietly();
        $fresh = $this->draft($role, $owner);
        $committed = $this->draft($role, $owner, ['draft_token' => null]);
        $committed->forceFill(['created_at' => now()->subDays(30)])->saveQuietly();

        $this->artisan('app:prune-gallery-drafts')->assertSuccessful();

        $this->assertGone($old);
        $this->assertStored($fresh);
        $this->assertStored($committed);
    }

    // ----- Fan photos ------------------------------------------------------------------------

    public function test_approved_fan_photos_are_copied_into_drafts_credited_to_their_author(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $source = UploadedFile::fake()->image('fan.jpg', 800, 600);
        Storage::put(\App\Utils\ImageUtils::storagePathFor('photo_fan.jpg'), file_get_contents($source->getRealPath()));
        $approved = EventPhoto::create(['event_id' => $event->id, 'guest_name' => 'Dana Guest', 'photo_url' => 'photo_fan.jpg', 'is_approved' => true]);
        $pending = EventPhoto::create(['event_id' => $event->id, 'guest_name' => 'Pat', 'photo_url' => 'photo_fan.jpg', 'is_approved' => false]);

        $response = $this->actingAs($owner)->post(route('gallery.from_fan_photos', ['subdomain' => $role->subdomain]), [
            'target' => 'event',
            'event' => UrlUtils::encodeId($event->id),
            'draft_token' => self::TOKEN,
            'photo_ids' => [UrlUtils::encodeId($approved->id), UrlUtils::encodeId($pending->id)],
        ])->assertOk();

        $this->assertCount(1, $response->json('images'), 'only approved fan photos can be copied');
        $copy = GalleryImage::firstOrFail();
        $this->assertSame('Dana Guest', $copy->credit);
        $this->assertNotSame('photo_fan.jpg', $copy->filename, 'a copy under its own name');
        $this->assertSame(self::TOKEN, $copy->draft_token);
    }

    // ----- The edit forms render the editor ---------------------------------------------------

    /**
     * A double-quoted value written straight into a Vue attribute blanks the whole form at mount
     * without any server error; its fingerprint is an attribute that closes at once and runs into
     * more text.
     */
    private function assertWellFormedVueTemplate(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/\s[:@][\w-]+=""[^\s>\/]/', $html);
    }

    public function test_the_event_form_renders_the_gallery_editor_with_its_photos(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $image = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null, 'caption' => '{{ 7*7 }} front row']);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="section-gallery"', $html);
        $this->assertStringContainsString('<gallery-editor', $html);
        $this->assertStringContainsString(UrlUtils::encodeId($image->id), $html, 'the committed photo is in the editor\'s initial state');
        $this->assertStringNotContainsString('>{{ 7*7 }} front row<', $html, 'a caption is data, never template text');
        $this->assertWellFormedVueTemplate($html);
    }

    public function test_the_new_event_form_starts_with_an_empty_gallery_even_when_the_schedule_has_one(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $scheduleImage = $this->draft($role, $owner, ['draft_token' => null]);

        $html = $this->actingAs($owner)->get(route('event.create', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('id="section-gallery"', $html);
        $this->assertStringNotContainsString(UrlUtils::encodeId($scheduleImage->id), $html);
        $this->assertWellFormedVueTemplate($html);
    }

    public function test_the_schedule_form_renders_the_gallery_island(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $image = $this->draft($role, $owner, ['draft_token' => null]);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('id="gallery-editor-app"', $html);
        $this->assertStringContainsString(UrlUtils::encodeId($image->id), $html);
        $this->assertWellFormedVueTemplate($html);
    }

    public function test_a_free_schedule_sees_the_plan_gate_instead_of_the_editor(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('id="section-gallery"', $html);
        $this->assertStringNotContainsString('id="gallery-editor-app"', $html);
        $this->assertStringContainsString(__('messages.gallery_upsell_title'), $html);
    }

    public function test_the_first_publish_panel_renders(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)
            ->withSession(['gallery_published' => [
                'name' => 'Jazz Night', 'count' => 3, 'is_hidden' => false, 'thumbs' => [],
                'url' => 'https://example.test/jazz#gp-gallery', 'edit_url' => 'https://example.test/edit#section-gallery',
            ]])
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee(__('messages.gallery_live_title'))
            ->assertSee('https://example.test/jazz#gp-gallery', false);
    }

    // ----- Guest pages -----------------------------------------------------------------------

    public function test_the_event_page_shows_the_gallery_and_the_shared_viewer(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $first = $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null, 'caption' => 'Front row']);
        $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null, 'sort_order' => 1]);
        $this->draft($role, $owner, ['event_id' => $event->id]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="gp-gallery"'));
        $this->assertStringContainsString('data-lightbox-set="gallery"', $html);
        $this->assertStringContainsString('<div id="es-lightbox-app"></div>', $html);
        $this->assertStringContainsString('alt="Front row"', $html);
        $this->assertSame(2, substr_count($html, 'class="es-mosaic-item"'), 'a draft is never shown');
        $this->assertStringContainsString(__('messages.gallery_see_all', ['count' => 2]), $html);
    }

    public function test_the_event_page_hides_the_gallery_of_a_downgraded_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'talent');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $this->draft($role, $owner, ['event_id' => $event->id, 'draft_token' => null]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="gp-gallery"', $html);
    }

    public function test_the_schedule_page_shows_its_gallery_and_the_header_link(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['header_style' => 'banner']);
        $this->draft($role, $owner, ['draft_token' => null]);
        $this->draft($role, $owner, ['draft_token' => null, 'sort_order' => 1]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="gp-gallery"'));
        // The header link itself, not the attribute name the viewer's script also mentions.
        $this->assertMatchesRegularExpression('/href="#gp-gallery" data-lightbox-set="gallery"\s+data-lightbox-grid/', $html);
        $this->assertStringContainsString(trans_choice('messages.gallery_photo_count', 2, ['count' => 2]), $html);
        $this->assertStringContainsString('<div id="es-lightbox-app"></div>', $html);
    }

    public function test_the_gallery_heading_is_a_custom_label(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['custom_labels' => ['gallery' => ['value' => 'Inside the studio']]]);
        $this->draft($role, $owner, ['draft_token' => null]);

        $this->get('/'.$role->subdomain)->assertOk()->assertSee('Inside the studio');
    }

    /** The strip under the flyer reads its count from PHP's plural forms, not a split in JS. */
    public function test_the_flyer_strip_count_is_rendered_from_the_locales_plural_forms(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['language_code' => 'de'])->save();
        $role = $this->createRole($owner, 'talent', ['language_code' => 'de']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"one":"1 Foto"', $html);
        $this->assertStringContainsString('"many":":count Fotos"', $html);
        $this->assertStringNotContainsString('[2,*]', $html);
    }
}
