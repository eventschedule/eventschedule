<?php

namespace Tests\Unit;

use App\Utils\SponsorUtils;
use PHPUnit\Framework\TestCase;

/**
 * SponsorUtils::keepStoredLogos(): a sponsor list posted from the browser keeps a logo only when
 * the schedule or event already holds that file, because whatever the stored list later drops is
 * deleted.
 */
class SponsorUtilsTest extends TestCase
{
    public function test_a_stored_logo_is_kept_in_the_posted_order(): void
    {
        $posted = [
            ['name' => 'Second', 'logo' => 'sponsor_b.png', 'url' => null, 'tier' => ''],
            ['name' => 'First', 'logo' => 'sponsor_a.png', 'url' => 'https://a.test', 'tier' => 'gold'],
        ];

        $this->assertSame($posted, SponsorUtils::keepStoredLogos($posted, ['sponsor_a.png', 'sponsor_b.png']));
    }

    public function test_a_logo_the_row_does_not_hold_is_dropped_and_the_sponsor_kept(): void
    {
        $kept = SponsorUtils::keepStoredLogos([
            ['name' => 'Ours', 'logo' => 'sponsor_ours.png', 'url' => null, 'tier' => ''],
            ['name' => 'Theirs', 'logo' => 'sponsor_theirs.png', 'url' => 'https://b.test', 'tier' => 'silver'],
            ['name' => 'Path', 'logo' => '../../.env', 'url' => null, 'tier' => ''],
            ['name' => 'Not a name', 'logo' => ['sponsor_ours.png'], 'url' => null, 'tier' => ''],
            ['name' => 'Empty', 'logo' => '', 'url' => null, 'tier' => ''],
        ], ['sponsor_ours.png']);

        $this->assertSame([
            ['name' => 'Ours', 'logo' => 'sponsor_ours.png', 'url' => null, 'tier' => ''],
            ['name' => 'Theirs', 'url' => 'https://b.test', 'tier' => 'silver'],
            ['name' => 'Path', 'url' => null, 'tier' => ''],
            ['name' => 'Not a name', 'url' => null, 'tier' => ''],
            ['name' => 'Empty', 'url' => null, 'tier' => ''],
        ], $kept);
    }

    public function test_a_new_row_keeps_no_posted_logo(): void
    {
        $this->assertSame(
            [['name' => 'Theirs', 'tier' => '']],
            SponsorUtils::keepStoredLogos([['name' => 'Theirs', 'logo' => 'sponsor_theirs.png', 'tier' => '']], [])
        );
    }

    public function test_anything_that_is_not_a_list_of_sponsors_is_nothing(): void
    {
        $this->assertSame([], SponsorUtils::keepStoredLogos(null, ['sponsor_a.png']));
        $this->assertSame([], SponsorUtils::keepStoredLogos('sponsor_a.png', ['sponsor_a.png']));
        $this->assertSame([], SponsorUtils::keepStoredLogos(['sponsor_a.png', 7, null], ['sponsor_a.png']));
    }
}
