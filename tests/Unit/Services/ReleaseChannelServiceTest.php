<?php

namespace Tests\Unit\Services;

use App\Services\ReleaseChannelService;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class ReleaseChannelServiceTest extends TestCase
{
    public function testNormalizeReleaseDataStripsLeadingVFromTag(): void
    {
        $service = new ReleaseChannelService();

        $normalized = $this->normalizeReleaseData($service, [
            'tag_name' => 'v5.0.16',
            'name' => 'Release v5.0.16',
            'prerelease' => false,
        ]);

        $this->assertSame('5.0.16', $normalized['version']);
        $this->assertSame('v5.0.16', $normalized['tag']);
    }

    public function testNormalizeReleaseDataPreservesVersionWithoutLeadingV(): void
    {
        $service = new ReleaseChannelService();

        $normalized = $this->normalizeReleaseData($service, [
            'tag_name' => '5.0.16-beta1',
            'name' => '5.0.16-beta1',
            'prerelease' => true,
        ]);

        $this->assertSame('5.0.16-beta1', $normalized['version']);
        $this->assertSame('5.0.16-beta1', $normalized['tag']);
    }

    public function testNormalizeReleaseDataRequiresTagName(): void
    {
        $this->expectException(RuntimeException::class);

        $service = new ReleaseChannelService();

        $this->normalizeReleaseData($service, []);
    }

    private function normalizeReleaseData(ReleaseChannelService $service, array $release): array
    {
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('normalizeReleaseData');
        $method->setAccessible(true);

        return $method->invoke($service, $release);
    }
}
