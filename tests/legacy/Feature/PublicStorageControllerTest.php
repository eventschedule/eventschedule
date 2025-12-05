<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageControllerTest extends TestCase
{
    public function test_it_streams_files_from_the_public_disk(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile.png', 32, 32);
        $path = $file->storeAs('', 'profile.png', 'public');

        $response = $this->get('/storage/' . $path);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Cache-Control', 'public, max-age=604800');
        $response->assertHeader('Content-Disposition', 'inline; filename="profile.png"');
        $this->assertSame(Storage::disk('public')->get($path), $response->getContent());
    }

    public function test_it_accepts_requests_with_storage_prefix(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('profile.png', 'avatar');

        $response = $this->get('/storage/storage/profile.png');

        $response->assertOk();
        $this->assertSame('avatar', $response->getContent());
    }

    public function test_it_returns_not_found_for_missing_files(): void
    {
        Storage::fake('public');

        $response = $this->get('/storage/missing.png');

        $response->assertNotFound();
    }

    public function test_it_blocks_directory_traversal_attempts(): void
    {
        Storage::fake('public');

        $response = $this->get('/storage/../.env');

        $response->assertNotFound();
    }
}

