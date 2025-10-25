<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../scripts/bump-version.php';

class BumpVersionScriptTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('BUMP_VERSION_DATE');
        putenv('GITHUB_OUTPUT');

        parent::tearDown();
    }

    public function testBetaReleaseFromProductionVersionStartsNewSequenceForCurrentDate(): void
    {
        putenv('BUMP_VERSION_DATE=20251024');

        $this->assertSame('20251024-01b', bumpVersion('20251023-05p', 'beta'));
    }

    public function testSubsequentBetaReleaseIncrementsSequence(): void
    {
        putenv('BUMP_VERSION_DATE=20251024');

        $this->assertSame('20251024-02b', bumpVersion('20251024-01b', 'beta'));
    }

    public function testProductionReleaseResetsSequenceForNewChannel(): void
    {
        putenv('BUMP_VERSION_DATE=20251024');

        $this->assertSame('20251024-01p', bumpVersion('20251024-02b', 'production'));
    }

    public function testMajorFlagForcesNewDailySequence(): void
    {
        putenv('BUMP_VERSION_DATE=20251024');

        $this->assertSame('20251024-01p', bumpVersion('20251024-05p', 'production', true));
    }

    public function testLegacyVersionFormatFallsBackToCurrentDate(): void
    {
        putenv('BUMP_VERSION_DATE=20251024');

        $this->assertSame('20251024-01b', bumpVersion('5.0.17b', 'beta'));
    }

    public function testGithubOutputsMarkBetaReleasesAsPrereleases(): void
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'gho_');

        $this->assertIsString($outputFile);

        putenv('GITHUB_OUTPUT=' . $outputFile);

        writeGithubOutputs('20251024-01b', 'beta', false);

        $this->assertStringContainsString('prerelease=true', (string) file_get_contents($outputFile));

        @unlink($outputFile);
    }

    public function testGithubOutputsMarkProductionReleasesAsStable(): void
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'gho_');

        $this->assertIsString($outputFile);

        putenv('GITHUB_OUTPUT=' . $outputFile);

        writeGithubOutputs('20251024-01p', 'production', false);

        $this->assertStringContainsString('prerelease=false', (string) file_get_contents($outputFile));

        @unlink($outputFile);
    }
}
