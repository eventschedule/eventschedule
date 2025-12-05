<?php

namespace Tests\Unit;

use Tests\TestCase;

class StorageAssetUrlTest extends TestCase
{
    public function test_it_generates_relative_urls_for_public_disk(): void
    {
        config(['filesystems.default' => 'local']);

        $this->assertSame('/storage/profile.png', storage_asset_url('profile.png'));
    }
}
