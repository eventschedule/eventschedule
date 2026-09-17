<?php

namespace Tests\Unit;

use App\Utils\JsonUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonUtilsTest extends TestCase
{
    public static function values(): array
    {
        $object = '{"a":1}';

        return [
            'object' => [$object, ['a' => 1]],
            'list' => ['[1,2]', [1, 2]],
            'empty list' => ['[]', []],
            'encoded twice' => [json_encode($object), ['a' => 1]],
            'encoded three times' => [json_encode(json_encode($object)), ['a' => 1]],
            'a JSON string that is not an array' => ['"hello"', null],
            'a JSON number' => ['5', null],
            'invalid JSON' => ['{not json', null],
            'empty string' => ['', null],
            'null' => [null, null],
            'already an array' => [['a' => 1], ['a' => 1]],
            'more layers than the limit' => [json_encode(json_encode(json_encode(json_encode(json_encode($object))))), null],
        ];
    }

    #[DataProvider('values')]
    public function test_decode_to_array(mixed $input, ?array $expected): void
    {
        $this->assertSame($expected, JsonUtils::decodeToArray($input));
    }
}
