<?php

namespace Tests\Unit;

use App\Support\GroupPayloadNormalizer;
use PHPUnit\Framework\TestCase;

class GroupPayloadNormalizerTest extends TestCase
{
    public function testForViewCastsStdClassWithoutName(): void
    {
        $groups = [
            (object) ['id' => 5, 'slug' => 'main-stage'],
            (object) ['slug' => 'vip'],
        ];

        $normalized = GroupPayloadNormalizer::forView($groups);

        $this->assertSame([
            0 => [
                'id' => '5',
                'name' => '',
                'name_en' => '',
                'slug' => 'main-stage',
            ],
            1 => [
                'id' => '1',
                'name' => '',
                'name_en' => '',
                'slug' => 'vip',
            ],
        ], $normalized);
    }

    public function testForPersistenceNormalizesScalars(): void
    {
        $groups = [
            'Morning Set',
            ['name' => 'Evening', 'name_en' => 'Evening', 'slug' => 'evening'],
        ];

        $normalized = GroupPayloadNormalizer::forPersistence($groups);

        $this->assertSame([
            0 => [
                'id' => null,
                'name' => 'Morning Set',
                'name_en' => '',
                'slug' => '',
            ],
            1 => [
                'id' => null,
                'name' => 'Evening',
                'name_en' => 'Evening',
                'slug' => 'evening',
            ],
        ], $normalized);
    }
}
