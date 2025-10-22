<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../scripts/bump-version.php';

class BumpVersionScriptTest extends TestCase
{
    public function testBetaReleaseFromProductionVersionAddsPatchAndSuffix(): void
    {
        $this->assertSame('2.1.1b', bumpVersion('2.1', 'beta'));
    }

    public function testSubsequentBetaReleaseIncrementsPatch(): void
    {
        $this->assertSame('2.1.2b', bumpVersion('2.1.1b', 'beta'));
    }

    public function testBetaReleaseFromPatchedProductionVersionIncrementsPatch(): void
    {
        $this->assertSame('2.1.4b', bumpVersion('2.1.3', 'beta'));
    }

    public function testProductionReleaseIncrementsMinor(): void
    {
        $this->assertSame('2.2', bumpVersion('2.1', 'production'));
    }

    public function testMajorProductionReleaseResetsMinorVersion(): void
    {
        $this->assertSame('3.0', bumpVersion('2.1', 'production', true));
    }

    public function testMajorBetaReleaseResetsAndAddsBetaSuffix(): void
    {
        $this->assertSame('3.0b', bumpVersion('2.1', 'beta', true));
    }

    public function testProductionReleaseFromBetaFinalizesVersion(): void
    {
        $this->assertSame('2.1.4', bumpVersion('2.1.4b', 'production'));
    }

    public function testProductionReleaseFromMajorBetaFinalizesVersion(): void
    {
        $this->assertSame('3.0', bumpVersion('3.0b', 'production'));
    }
}
