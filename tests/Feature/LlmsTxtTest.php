<?php

namespace Tests\Feature;

use App\Utils\PlatformPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PlanSections;
use Tests\TestCase;

/**
 * public/llms.txt and public/llms-full.txt are what an AI crawler reads instead of the site, and
 * they are static files: nothing renders them, so nothing about them breaks loudly. They drifted
 * the quiet way - a Free plan listing every payment method and refunds (both Pro), a Blog link
 * to eventschedule.com/blog, which only redirects, and no product overview at all in llms.txt.
 *
 * So every link has to land, every price has to be the one the site quotes, and the Free plan
 * may not claim what Pro sells.
 */
class LlmsTxtTest extends TestCase
{
    // Pages rendered for the link check read the database, and PlatformPricing reads settings.
    use RefreshDatabase;

    private const FILES = ['llms.txt', 'llms-full.txt'];

    protected function setUp(): void
    {
        parent::setUp();
        PlatformPricing::flush();
    }

    protected function tearDown(): void
    {
        PlatformPricing::flush();
        parent::tearDown();
    }

    private function contents(string $file): string
    {
        return file_get_contents(public_path($file));
    }

    /**
     * Every markdown link to our own site either names a file served straight out of public/ or
     * a page that answers 200 here. The blog is the one other host, and only its front page is
     * linked.
     */
    public function test_every_link_to_our_site_lands(): void
    {
        $checked = 0;

        foreach (self::FILES as $file) {
            preg_match_all('~\]\((https?://[^)\s]+)\)~', $this->contents($file), $links);

            foreach (array_unique($links[1]) as $url) {
                $host = parse_url($url, PHP_URL_HOST);
                $path = parse_url($url, PHP_URL_PATH) ?: '/';

                if ($host === 'blog.eventschedule.com') {
                    $this->assertSame('/', $path, "public/{$file} links {$url}; link the blog's front page");
                    $checked++;

                    continue;
                }

                if ($host !== 'eventschedule.com') {
                    continue;
                }

                $local = public_path(ltrim($path, '/'));

                if ($path !== '/' && is_file($local)) {
                    $checked++;

                    continue;
                }

                $this->assertSame(200, $this->get($path)->getStatusCode(), "public/{$file} links {$url}, which does not answer 200");
                $checked++;
            }
        }

        $this->assertGreaterThan(40, $checked, 'fixture: the links in both files were found');
    }

    /** /blog only redirects to the blog host, so a crawler following it pays a hop for nothing. */
    public function test_neither_file_links_the_blog_redirect(): void
    {
        foreach (self::FILES as $file) {
            $this->assertStringNotContainsString('eventschedule.com/blog', $this->contents($file), "public/{$file}");
            $this->assertStringContainsString('(https://blog.eventschedule.com)', $this->contents($file), "public/{$file} links no blog");
        }
    }

    /**
     * llms-full.txt quotes the default plan prices. It is static, so they are pinned here to the
     * amounts PlatformPricing - and so /pricing - quotes, or a price change leaves AI answers
     * quoting the old one.
     */
    public function test_the_full_file_quotes_the_prices_the_site_quotes(): void
    {
        $body = $this->contents('llms-full.txt');

        foreach ([
            'Pro' => [PlatformPricing::proMonthly(), PlatformPricing::proYearly()],
            'Enterprise' => [PlatformPricing::enterpriseMonthly(), PlatformPricing::enterpriseYearly()],
        ] as $plan => [$monthly, $yearly]) {
            $this->assertMatchesRegularExpression(
                '~^### '.$plan.' Plan\R\R\$([\d.]+)/month or \$([\d.]+)/year\.$~m',
                $body,
                "llms-full.txt has no price line for {$plan}"
            );

            preg_match('~^### '.$plan.' Plan\R\R\$([\d.]+)/month or \$([\d.]+)/year\.$~m', $body, $m);

            $this->assertSame([$monthly, $yearly], [(float) $m[1], (float) $m[2]], "llms-full.txt quotes another {$plan} price");
        }
    }

    /** llms.txt quotes no price at all: nothing would keep one current. */
    public function test_the_summary_file_quotes_no_price(): void
    {
        $this->assertDoesNotMatchRegularExpression('~\$\s?\d~', $this->contents('llms.txt'));
    }

    public function test_no_free_plan_section_lists_a_payment_method_or_refunds(): void
    {
        foreach (self::FILES as $file) {
            $sections = PlanSections::free($this->contents($file));

            $this->assertNotEmpty($sections, "public/{$file} has no Free plan section");

            foreach ($sections as $section) {
                $this->assertDoesNotMatchRegularExpression(PlanSections::PAYMENT, $section,
                    "public/{$file} lists a payment method or refunds under the Free plan; both are Pro");
            }
        }
    }

    /** The one number llms.txt states about the product that the code can check. */
    public function test_the_summary_file_counts_the_real_languages(): void
    {
        $this->assertMatchesRegularExpression(
            '~\b'.count(config('app.supported_languages')).' supported languages\b~',
            $this->contents('llms.txt')
        );
    }
}
