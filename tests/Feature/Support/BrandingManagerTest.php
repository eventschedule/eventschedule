<?php

namespace Tests\Feature\Support;

use App\Support\BrandingManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('filesystems.default', 'public');
        Config::set('branding', []);
        Config::set('app.supported_languages', ['en', 'fr']);
        Config::set('app.fallback_locale', 'en');
    }

    public function testApplyNormalizesBrandingConfiguration(): void
    {
        App::setLocale('fr');

        BrandingManager::apply([
            'primary_color' => ' #abc ',
            'secondary_color' => 'oops',
            'tertiary_color' => '#123456',
            'logo_path' => 'uploads/logo.png',
            'logo_alt' => '  Example  ',
            'logo_media_asset_id' => '12',
            'logo_media_variant_id' => 7,
            'default_language' => 'xx',
        ]);

        $branding = Config::get('branding');

        $this->assertSame('#AABBCC', $branding['colors']['primary']);
        $this->assertSame('#111827', $branding['colors']['secondary']);
        $this->assertSame('#123456', $branding['colors']['tertiary']);
        $this->assertSame('170, 187, 204', $branding['colors']['primary_rgb']);
        $this->assertSame('#D0DAE3', $branding['colors']['primary_light']);
        $this->assertSame('#111827', $branding['colors']['on_primary']);
        $this->assertSame('#FFFFFF', $branding['colors']['on_secondary']);
        $this->assertSame('#FFFFFF', $branding['colors']['on_tertiary']);

        $this->assertSame('Example', $branding['logo_alt']);
        $this->assertSame('/storage/uploads/logo.png', $branding['logo_url']);
        $this->assertSame(12, $branding['logo_media_asset_id']);
        $this->assertSame(7, $branding['logo_media_variant_id']);

        $this->assertSame('en', $branding['default_language']);
        $this->assertSame('en', Config::get('app.locale'));
        $this->assertSame('en', App::getLocale());
    }

    public function testApplyUsesCustomDiskUrlWhenAvailable(): void
    {
        Config::set('filesystems.default', 's3');
        Config::set('filesystems.disks.s3', [
            'driver' => 'local',
            'root' => storage_path('app/s3'),
            'url' => 'https://cdn.example.test',
        ]);

        Storage::fake('s3');

        BrandingManager::apply([
            'logo_path' => 'branding/logo.svg',
            'logo_disk' => 's3',
            'primary_color' => '#000000',
            'secondary_color' => '#ffffff',
            'tertiary_color' => '#777777',
        ]);

        $branding = Config::get('branding');

        $this->assertSame('https://cdn.example.test/branding/logo.svg', $branding['logo_url']);
        $this->assertSame('#000000', $branding['colors']['primary']);
        $this->assertSame('#FFFFFF', $branding['colors']['secondary']);
        $this->assertSame('#777777', $branding['colors']['tertiary']);
        $this->assertSame('Event Schedule', $branding['logo_alt']);
        $this->assertNull($branding['logo_media_asset_id']);
        $this->assertNull($branding['logo_media_variant_id']);
        $this->assertSame('en', $branding['default_language']);
    }
}
