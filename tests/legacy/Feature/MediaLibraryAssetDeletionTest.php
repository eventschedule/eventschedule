<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class MediaLibraryAssetDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_asset_in_use_requires_force_flag_before_deletion(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $asset = $this->createAsset($user);
        $role = $this->createRole($user);
        $role->profile_image_url = $asset->path;
        $role->save();

        $usage = MediaAssetUsage::create([
            'media_asset_id' => $asset->id,
            'usable_type' => Role::class,
            'usable_id' => $role->id,
            'context' => 'profile',
        ]);

        $response = $this->deleteJson(route('media.assets.destroy', ['asset' => $asset->id]));

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This asset is currently in use and cannot be deleted.',
            ])
            ->assertJsonFragment([
                'id' => $usage->id,
                'context' => 'profile',
            ]);

        $this->assertDatabaseHas('media_asset_usages', ['id' => $usage->id]);
        $this->assertNotNull(Role::find($role->id)->profile_image_url);
        $this->assertNotNull(MediaAsset::find($asset->id));
        Storage::disk('public')->assertExists($asset->path);
    }

    public function test_forced_deletion_clears_usages_and_removes_asset(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $asset = $this->createAsset($user);
        $role = $this->createRole($user);
        $role->profile_image_url = $asset->path;
        $role->save();

        MediaAssetUsage::create([
            'media_asset_id' => $asset->id,
            'usable_type' => Role::class,
            'usable_id' => $role->id,
            'context' => 'profile',
        ]);

        $response = $this->deleteJson(route('media.assets.destroy', ['asset' => $asset->id, 'force' => 1]));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('media_asset_usages', [
            'media_asset_id' => $asset->id,
        ]);
        $this->assertNull(Role::find($role->id)->profile_image_url);
        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($asset->path);
    }

    private function createAsset(User $user): MediaAsset
    {
        $path = 'media/' . Str::lower(Str::random(12)) . '.jpg';
        Storage::disk('public')->put($path, 'fake-image-content');

        return MediaAsset::create([
            'disk' => 'public',
            'path' => $path,
            'original_filename' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 12345,
            'width' => 600,
            'height' => 400,
            'uploaded_by' => $user->id,
        ]);
    }

    private function createRole(User $user): Role
    {
        $role = new Role();
        $role->user_id = $user->id;
        $role->type = 'venue';
        $role->subdomain = 'role-' . Str::lower(Str::random(8));
        $role->name = 'Role ' . Str::random(5);
        $role->email = Str::lower(Str::random(5)) . '@example.com';
        $role->background = 'gradient';
        $role->language_code = 'en';
        $role->accept_requests = true;
        $role->save();

        return $role;
    }
}
