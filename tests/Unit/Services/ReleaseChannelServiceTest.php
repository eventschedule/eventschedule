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
            'tag_name' => 'v20251024-01p',
            'name' => 'Release v20251024-01p',
            'prerelease' => false,
        ]);

        $this->assertSame('20251024-01p', $normalized['version']);
        $this->assertSame('v20251024-01p', $normalized['tag']);
    }

    public function testNormalizeReleaseDataPreservesVersionWithoutLeadingV(): void
    {
        $service = new ReleaseChannelService();

        $normalized = $this->normalizeReleaseData($service, [
            'tag_name' => '20251024-01b',
            'name' => '20251024-01b',
            'prerelease' => true,
        ]);

        $this->assertSame('20251024-01b', $normalized['version']);
        $this->assertSame('20251024-01b', $normalized['tag']);
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
