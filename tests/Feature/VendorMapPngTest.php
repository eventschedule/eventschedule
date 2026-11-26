<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\VendorMap;
use App\Models\VendorMapArea;
use App\Models\VendorMapLandmark;
use App\Models\VendorMapSpot;
use App\Services\VendorMaps\VendorMapPng;
use App\Utils\ImageUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The vendor map's pictures (VendorMapPng): the vendor's mail picture with their stall marked, the
 * share card behind the vendor link's preview, the entrance sign as a print-shop PNG, and the
 * printable sign page.
 */
class VendorMapPngTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function mapFor(Role $role, array $attrs = []): VendorMap
    {
        $map = VendorMap::create(['role_id' => $role->id, 'kind' => 'market', 'width' => 1000, 'height' => 700] + $attrs);

        $hangar = VendorMapArea::create(['vendor_map_id' => $map->id, 'name' => $attrs['area_name'] ?? 'Hangar', 'color' => '#16A34A']);
        $orchard = VendorMapArea::create(['vendor_map_id' => $map->id, 'name' => 'Orchard', 'color' => '#F59E0B']);

        for ($i = 0; $i < 8; $i++) {
            VendorMapSpot::create([
                'vendor_map_id' => $map->id, 'label' => (string) ($i + 1), 'area_id' => $i < 4 ? $hangar->id : $orchard->id,
                'x' => 100 + ($i % 4) * 90, 'y' => $i < 4 ? 120 : 320, 'w' => 70, 'h' => 50, 'rotation' => $i === 7 ? 30 : 0,
            ]);
        }
        // A stall that is not a rectangle, and one far too small for its own number.
        VendorMapSpot::create(['vendor_map_id' => $map->id, 'label' => 'B12', 'x' => 600, 'y' => 120, 'w' => 80, 'h' => 80, 'shape' => [[600, 120], [680, 140], [660, 200], [610, 190]]]);
        VendorMapSpot::create(['vendor_map_id' => $map->id, 'label' => '99', 'x' => 760, 'y' => 400, 'w' => 6, 'h' => 6]);

        foreach (['entrance', 'restroom', 'parking', 'stage', 'text'] as $i => $kind) {
            VendorMapLandmark::create(['vendor_map_id' => $map->id, 'kind' => $kind, 'label' => $kind === 'text' ? 'Main street' : null, 'x' => 100 + $i * 140, 'y' => 520, 'w' => 80, 'h' => 50]);
        }

        return $map->fresh();
    }

    private function assertPng(?string $bytes, int $width, int $height): void
    {
        $this->assertNotNull($bytes);
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $bytes);
        $size = getimagesizefromstring($bytes);
        $this->assertSame([$width, $height], [$size[0], $size[1]]);
    }

    public function test_the_stall_picture_marks_one_spot(): void
    {
        $role = $this->createRole($this->createOwner());
        $map = $this->mapFor($role);
        $spot = $map->spots()->where('label', '3')->first();

        $png = VendorMapPng::stallImage($map, $spot->id);
        $this->assertPng($png, 640, 400);

        // The marked spot is drawn in brand blue, somewhere in the picture.
        $im = imagecreatefromstring($png);
        $found = false;
        for ($x = 0; $x < 640 && ! $found; $x += 2) {
            for ($y = 0; $y < 400 && ! $found; $y += 2) {
                $c = imagecolorsforindex($im, imagecolorat($im, $x, $y));
                $found = abs($c['red'] - 78) < 6 && abs($c['green'] - 129) < 6 && abs($c['blue'] - 250) < 6;
            }
        }
        $this->assertTrue($found, 'the stall should be drawn in brand blue');

        // A spot too small for its number gets a bubble, and a spot of another map gets nothing.
        $this->assertPng(VendorMapPng::stallImage($map, $map->spots()->where('label', '99')->value('id')), 640, 400);
        $this->assertNull(VendorMapPng::stallImage($map, 999999));
    }

    public function test_the_share_card_is_a_new_public_file_each_time(): void
    {
        Storage::fake(config('filesystems.default'));
        $role = $this->createRole($this->createOwner());
        $map = $this->mapFor($role);
        $disk = Storage::disk(config('filesystems.default'));

        VendorMapPng::refreshShareImage($map);
        $first = $map->fresh()->share_image_path;
        $this->assertMatchesRegularExpression('/^vmshare_[a-z0-9]{32}\.png$/', $first);
        $this->assertTrue($disk->exists(ImageUtils::storagePathFor($first)));
        $this->assertPng($disk->get(ImageUtils::storagePathFor($first)), 1200, 630);

        VendorMapPng::refreshShareImage($map->fresh());
        $second = $map->fresh()->share_image_path;
        $this->assertNotSame($first, $second);
        $this->assertTrue($disk->exists(ImageUtils::storagePathFor($second)));
        $this->assertFalse($disk->exists(ImageUtils::storagePathFor($first)), 'the old card is removed');
    }

    public function test_a_map_with_no_spots_keeps_no_card(): void
    {
        Storage::fake(config('filesystems.default'));
        $role = $this->createRole($this->createOwner());
        $map = VendorMap::create(['role_id' => $role->id, 'kind' => 'market']);

        VendorMapPng::refreshShareImage($map);

        $this->assertNull($map->fresh()->share_image_path);
        $this->assertSame([], Storage::disk(config('filesystems.default'))->allFiles());
    }

    public function test_the_sign_is_an_a4_page_at_300_dpi(): void
    {
        $role = $this->createRole($this->createOwner());
        $map = $this->mapFor($role, ['here_x' => 450, 'here_y' => 600]);

        $this->assertPng(VendorMapPng::sign($role, $map), 2480, 3508);
    }

    public function test_hebrew_and_arabic_schedules_render(): void
    {
        Storage::fake(config('filesystems.default'));

        $hebrew = $this->createRole($this->createOwner(), 'venue', ['name' => 'שוק האיכרים בנהר (Riverside)', 'language_code' => 'he']);
        $map = $this->mapFor($hebrew, ['here_x' => 300, 'here_y' => 600, 'area_name' => 'האנגר']);
        $this->assertPng(VendorMapPng::sign($hebrew, $map), 2480, 3508);
        $this->assertPng(VendorMapPng::stallImage($map, $map->spots()->value('id')), 640, 400);
        VendorMapPng::refreshShareImage($map);
        $this->assertNotNull($map->fresh()->share_image_path);

        // GD cannot join Arabic letters, so their words are left out and the numbers are drawn.
        $arabic = $this->createRole($this->createOwner(), 'venue', ['name' => 'سوق المزارعين', 'language_code' => 'ar']);
        $map = $this->mapFor($arabic);
        $this->assertPng(VendorMapPng::sign($arabic, $map), 2480, 3508);
        $this->assertFalse(VendorMapPng::drawable('سوق المزارعين'));
        $this->assertTrue(VendorMapPng::drawable('שוק 14'));

        // The visitor's locale is put back.
        $this->assertSame('en', app()->getLocale());
    }

    public function test_the_sign_page_is_for_the_schedules_editors(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Riverside Farmers Market']);
        $this->mapFor($role, ['here_x' => 450, 'here_y' => 600]);

        $body = $this->actingAs($owner)->get('/'.$role->subdomain.'/vendor-map/sign')->assertOk()->getContent();
        $this->assertStringContainsString('class="vms"', $body, 'the market is drawn as blocks of stalls');
        $this->assertStringContainsString('data:image/png;base64,', $body);
        $this->assertStringContainsString(route('vendor_map.sign_image', ['subdomain' => $role->subdomain]), $body);
        $this->assertStringContainsString('>B12<', $body);
        $this->assertStringContainsString('#dc2626', $body, 'the "You are here" marker is drawn');
        $this->assertStringNotContainsString(' onclick=', $body);

        $this->actingAs($owner)->get('/'.$role->subdomain.'/vendor-map/sign.png')
            ->assertOk()->assertHeader('Content-Type', 'image/png');

        $this->actingAs($this->createOwner())->get('/'.$role->subdomain.'/vendor-map/sign')->assertForbidden();
        $this->actingAs($this->createOwner())->get('/'.$role->subdomain.'/vendor-map/sign.png')->assertForbidden();
    }

    public function test_without_gd_the_sign_page_offers_no_png_and_the_png_address_does_not_fail(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Riverside Farmers Market']);
        $this->mapFor($role);
        $png = route('vendor_map.sign_image', ['subdomain' => $role->subdomain]);

        VendorMapPng::fakeAvailable(false);
        try {
            $body = $this->actingAs($owner)->get('/'.$role->subdomain.'/vendor-map/sign')->assertOk()->getContent();
            $this->assertStringNotContainsString($png, $body, 'no download where there is no PNG');
            $this->assertStringContainsString('vm-sign-print', $body, 'the printable page is still there');

            $this->actingAs($owner)->get('/'.$role->subdomain.'/vendor-map/sign.png')
                ->assertRedirect(route('vendor_map.sign', ['subdomain' => $role->subdomain]));
        } finally {
            VendorMapPng::fakeAvailable(null);
        }
    }

    public function test_the_sign_page_follows_the_schedules_direction(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'שוק האיכרים', 'language_code' => 'he']);
        $this->mapFor($role);

        $body = $this->actingAs($owner)->get('/'.$role->subdomain.'/vendor-map/sign')->assertOk()->getContent();
        $this->assertStringContainsString('<html lang="he" dir="rtl">', $body);
    }
}
