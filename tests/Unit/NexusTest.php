<?php

namespace Tests\Unit;

use App\Support\Nexus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * IS_NEXUS counts only on eventschedule.com or a development host, so a selfhosted SaaS that sets
 * it does not serve Event Schedule's marketing pages (GitHub #136). The routes it gates are held
 * by Tests\Feature\MarketingPagesNexusOnlyTest.
 */
class NexusTest extends TestCase
{
    public static function hosts(): array
    {
        return [
            'the nexus' => ['https://eventschedule.com', true],
            'the nexus, a trailing slash' => ['https://eventschedule.com/', true],
            'the nexus, upper case' => ['https://EventSchedule.com', true],
            'a subdomain of the nexus' => ['https://app.eventschedule.com', true],
            'local dev' => ['https://eventschedule.test', true],
            'artisan serve' => ['http://127.0.0.1:8000', true],
            'localhost' => ['http://localhost:8000', true],
            'a .localhost name' => ['http://es.localhost', true],
            'ipv6 loopback' => ['http://[::1]:8000', true],
            'a selfhosted SaaS' => ['https://tickets.example.co.za', false],
            'a lookalike' => ['https://eventschedule.com.example.net', false],
            'a name ending in the domain' => ['https://myeventschedule.com', false],
            'a name ending in test' => ['https://contest', false],
            'a public IP' => ['http://203.0.113.5', false],
            'no APP_URL' => ['', false],
            'APP_URL unset' => [null, false],
        ];
    }

    #[DataProvider('hosts')]
    public function test_the_flag_counts_only_on_the_nexus_or_a_dev_host(?string $appUrl, bool $expected): void
    {
        $this->assertSame($expected, Nexus::claimed(true, $appUrl));
    }

    public function test_the_flag_off_is_off_everywhere(): void
    {
        foreach ([false, null, 'false', '', '0'] as $flag) {
            $this->assertFalse(Nexus::claimed($flag, 'https://eventschedule.com'));
        }
        $this->assertTrue(Nexus::claimed('true', 'https://eventschedule.com'));
    }
}
