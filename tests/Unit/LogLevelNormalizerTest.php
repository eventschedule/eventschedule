<?php

namespace Tests\Unit;

use App\Support\Logging\LogLevelNormalizer;
use PHPUnit\Framework\TestCase;

class LogLevelNormalizerTest extends TestCase
{
    /**
     * @dataProvider providesValidCandidates
     */
    public function testNormalizeReturnsFirstValidLevel($value, string $expected): void
    {
        $this->assertSame($expected, LogLevelNormalizer::normalize($value, 'warning'));
    }

    public function providesValidCandidates(): array
    {
        return [
            'string-level' => ['INFO', 'info'],
            'json-array' => ['["error", "debug"]', 'error'],
            'comma-separated' => [' notice , warning ', 'notice'],
            'array-value' => [['', 'CRITICAL'], 'critical'],
            'numeric-value' => [300, 'warning'],
            'monolog-name' => ['EMERGENCY', 'emergency'],
        ];
    }

    public function testNormalizeFallsBackToDefaultWhenNoValidValueFound(): void
    {
        $this->assertSame('warning', LogLevelNormalizer::normalize(['', null, ''], 'warning'));
    }
}
