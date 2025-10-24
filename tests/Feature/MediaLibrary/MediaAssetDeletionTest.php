<?php

namespace Tests\Feature\MediaLibrary;

use App\Models\MediaAsset;
use App\Models\MediaAssetUsage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAssetDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_unused_asset(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $assetPath = 'media/test-image.jpg';
        $variantPath = 'media/variants/test-image.jpg';

        Storage::disk('public')->put($assetPath, 'asset');
        Storage::disk('public')->put($variantPath, 'variant');

        $asset = MediaAsset::create([
            'disk' => 'public',
            'path' => $assetPath,
            'original_filename' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'width' => 120,
            'height' => 80,
            'uploaded_by' => $user->id,
        ]);

        $variant = $asset->variants()->create([
            'disk' => 'public',
            'path' => $variantPath,
            'label' => 'crop',
            'width' => 60,
            'height' => 40,
            'size' => 512,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('media.assets.destroy', ['asset' => $asset->id]));

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        $this->assertDatabaseMissing('media_asset_variants', ['id' => $variant->id]);
        Storage::disk('public')->assertMissing($assetPath);
        Storage::disk('public')->assertMissing($variantPath);
    }

    public function test_user_cannot_delete_asset_that_is_in_use(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $assetPath = 'media/used-image.jpg';
        Storage::disk('public')->put($assetPath, 'asset');

        $asset = MediaAsset::create([
            'disk' => 'public',
            'path' => $assetPath,
            'original_filename' => 'used-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'width' => 200,
            'height' => 100,
            'uploaded_by' => $user->id,
        ]);

        MediaAssetUsage::create([
            'media_asset_id' => $asset->id,
            'media_asset_variant_id' => null,
            'usable_type' => User::class,
            'usable_id' => $user->id,
            'context' => 'general',
        ]);

        $response = $this->actingAs($user)->deleteJson(route('media.assets.destroy', ['asset' => $asset->id]));

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'This asset is currently in use and cannot be deleted.',
        ]);

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertExists($assetPath);
    }
}
