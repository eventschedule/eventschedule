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
}
