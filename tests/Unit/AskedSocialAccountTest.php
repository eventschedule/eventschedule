<?php

namespace Tests\Unit;

use App\Utils\UrlUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Which links answer "Add your social accounts", and what tidying keeps. An account is the
 * network's address and a name; a post, a reel or a page of the network's own is not, and keeps
 * its query, because there the query is often the thing linked to.
 */
class AskedSocialAccountTest extends TestCase
{
    public static function links(): array
    {
        return [
            ['https://www.instagram.com/band/?igsh=abc', 'instagram', 'https://www.instagram.com/band'],
            ['https://instagr.am/band', 'instagram', 'https://instagr.am/band'],
            ['https://x.com/band/status/123?s=20', null, 'https://x.com/band/status/123?s=20'],
            ['https://www.facebook.com/profile.php?id=123&ref=x', 'facebook', 'https://www.facebook.com/profile.php?id=123'],
            ['https://www.facebook.com/pages/Band/12345', 'facebook', 'https://www.facebook.com/pages/Band/12345'],
            ['https://www.facebook.com/band/posts/pfbid', null, 'https://www.facebook.com/band/posts/pfbid'],
            ['https://www.facebook.com/plugins/video.php?href=x', null, 'https://www.facebook.com/plugins/video.php?href=x'],
            ['https://www.facebook.com/search/top?q=band', null, 'https://www.facebook.com/search/top?q=band'],
            ['https://fb.me/band', 'facebook', 'https://fb.me/band'],
            ['https://www.linkedin.com/in/jane-doe?utm=1', 'linkedin', 'https://www.linkedin.com/in/jane-doe'],
            ['https://www.linkedin.com/company/acme/about/', 'linkedin', 'https://www.linkedin.com/company/acme/about'],
            // A personal profile typed after the button's company address is no account.
            ['https://www.linkedin.com/company/in/jane', null, 'https://www.linkedin.com/company/in/jane'],
            ['https://www.linkedin.com/profile/view?id=AA1', null, 'https://www.linkedin.com/profile/view?id=AA1'],
        ];
    }

    #[DataProvider('links')]
    public function test_a_link_is_an_account_only_when_it_names_one(string $url, ?string $platform, string $tidied): void
    {
        $this->assertSame($platform, UrlUtils::askedAccountPlatform($url));
        $this->assertSame($tidied, UrlUtils::tidyAskedLink($url));
    }
}
