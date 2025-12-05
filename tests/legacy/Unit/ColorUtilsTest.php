<?php

namespace Tests\Unit;

use App\Utils\ColorUtils;
use Tests\TestCase;

class ColorUtilsTest extends TestCase
{
    public function testDetermineRoleAssetNameHandlesStdClass(): void
    {
        $method = new \ReflectionMethod(ColorUtils::class, 'determineRoleAssetName');
        $method->setAccessible(true);

        $value = (object) [
            'meta' => (object) ['title' => 'Ignored'],
            'label' => (object) ['text' => 'Display'],
            'name' => (object) ['value' => 'Background_Name'],
        ];

        $result = $method->invoke(null, $value, 0);

        $this->assertSame('Background_Name', $result);
    }

    public function testExtractColorsHandlesNestedStructures(): void
    {
        $method = new \ReflectionMethod(ColorUtils::class, 'extractColors');
        $method->setAccessible(true);

        $value = (object) [
            'info' => 'unused',
            'colors' => [
                '#123456',
                (object) ['primary' => '#abcdef', 'secondary' => 'not-a-color'],
                ['nested' => ['#FEDCBA']],
            ],
        ];

        $result = $method->invoke(null, $value, 0);

        $this->assertSame(['#123456', '#abcdef', '#FEDCBA'], $result);
    }

    public function testDetermineRoleAssetLabelPrefersLabel(): void
    {
        $method = new \ReflectionMethod(ColorUtils::class, 'determineRoleAssetLabel');
        $method->setAccessible(true);

        $value = [
            'value' => 'bg-one',
            'label' => 'Background One',
        ];

        $result = $method->invoke(null, $value, 0);

        $this->assertSame('Background One', $result);
    }

    public function testBackgroundImageOptionsIncludeBundledAssets(): void
    {
        $options = ColorUtils::backgroundImageOptions();

        $this->assertNotEmpty($options);

        $paths = glob(base_path('public/images/backgrounds/*.png')) ?: [];
        $this->assertNotEmpty($paths, 'Expected bundled background images to be present');

        $filenames = array_map(static function (string $path): string {
            return pathinfo($path, PATHINFO_FILENAME);
        }, $paths);

        sort($filenames);

        $keys = array_keys($options);
        sort($keys);

        $this->assertSame($filenames, $keys);

        foreach ($options as $name => $label) {
            $this->assertSame(str_replace('_', ' ', $name), $label);
        }
    }

    public function testRandomBackgroundImageFallsBackGracefully(): void
    {
        $class = new class extends ColorUtils {
            public static bool $shouldThrow = true;

            public static function backgroundImageOptions(): array
            {
                if (self::$shouldThrow) {
                    throw new \RuntimeException('boom');
                }

                return [];
            }
        };

        $fqcn = get_class($class);
        $result = $fqcn::randomBackgroundImage();

        $this->assertNotSame('', $result);

        $expectedOptions = array_keys(ColorUtils::backgroundImageOptions());
        $staticOptions = array_keys((new \ReflectionClass(ColorUtils::class))->getConstant('STATIC_BACKGROUND_IMAGES'));
        $combined = array_unique(array_merge($expectedOptions, $staticOptions));

        $this->assertContains($result, $combined);
    }
}
