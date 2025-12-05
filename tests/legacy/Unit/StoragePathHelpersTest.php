<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class StoragePathHelpersTest extends TestCase
{
    public function test_it_strips_leading_storage_prefixes(): void
    {
        $this->assertSame('file.png', storage_normalize_path('storage/file.png'));
        $this->assertSame('file.png', storage_normalize_path('storage/storage/file.png'));
    }

    public function test_it_preserves_public_directory_prefix(): void
    {
        $this->assertSame('public/avatar.jpg', storage_normalize_path('public/avatar.jpg'));
        $this->assertSame('public/avatar.jpg', storage_normalize_path('storage/public/avatar.jpg'));
    }

    public function test_it_converts_legacy_app_public_paths(): void
    {
        $this->assertSame('public/banner.png', storage_normalize_path('app/public/banner.png'));
        $this->assertSame('public/banner.png', storage_normalize_path('storage/app/public/banner.png'));
        $this->assertSame('public/banner.png', storage_normalize_path('storage/storage/app/public/banner.png'));
        $this->assertSame('public', storage_normalize_path('app/public'));
        $this->assertSame('public', storage_normalize_path('storage/app/public'));
    }

    public function test_it_normalizes_backslashes(): void
    {
        $this->assertSame('public/image.gif', storage_normalize_path('storage\\app\\public\\image.gif'));
    }
}
