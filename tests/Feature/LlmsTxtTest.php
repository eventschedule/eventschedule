<?php

namespace Tests\Feature;

use App\Utils\PlatformPricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\PlanSections;
use Tests\TestCase;

/**
 * public/llms.txt and public/llms-full.txt are what an AI crawler reads instead of the site, and
 * they are static files: nothing renders them, so nothing about them breaks loudly. They drifted
 * the quiet way - a Free plan listing every payment method and refunds (both Pro), a Blog link
 * to eventschedule.com/blog, which only redirects, and no product overview at all in llms.txt.
 *
 * So every link has to land, every price has to be the one the site quotes, and the Free plan
 * may not claim what Pro sells. The newsletter allowance has to be stated in the unit it is
 * counted in, here and on every marketing page, since the same sentence was copied into both.
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

    /**
     * The newsletter allowance counts EMAILS, one for each recipient a newsletter goes to, so a
     * newsletter to 100 followers uses 100 of it (Role::newslettersSentThisMonth()). "10 recipients
     * a month" reads as ten people who can be written to as often as you like, which is not the
     * limit, and the phrase sat in both files and in about thirty marketing pages. The unit is
     * "newsletter emails a month, each recipient counting as one", in whatever grammar the sentence
     * needs. "per month" is in the pattern because llms-full.txt wrote it that way.
     */
    public function test_nothing_counts_the_newsletter_allowance_in_recipients_a_month(): void
    {
        $offences = [];

        foreach ($this->newsletterAllowanceSources() as $path => $body) {
            if (preg_match_all('~\brecipients?\s+(?:a|per)\s+month\b~i', $body, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($m[0] as [$hit, $offset]) {
                    $offences[] = $path.':'.(substr_count($body, "\n", 0, $offset) + 1).': "'.$hit.'"';
                }
            }
        }

        $this->assertGreaterThan(100, count($this->newsletterAllowanceSources()), 'fixture: the marketing views were found');
        $this->assertSame([], $offences, implode("\n", array_merge(
            ['These count the newsletter allowance in recipients a month. It counts emails, each '
                .'recipient counting as one (Role::newsletterLimit(), docs/FEATURES.md).'],
            $offences
        )));
    }

    /** The files an AI answer is built from say how the allowance is counted, not only how much it is. */
    public function test_both_files_say_each_recipient_counts_as_one(): void
    {
        foreach (self::FILES as $file) {
            $this->assertMatchesRegularExpression(
                '~\bnewsletter emails (?:a|per) month\b[^\n]{0,80}\beach recipient count(?:s|ing) as one\b~',
                $this->contents($file),
                "public/{$file} states the newsletter allowance without saying each recipient counts as one"
            );
        }
    }

    /** @return array<string, string> path => contents: both files, every marketing view and the controller's page data. */
    private function newsletterAllowanceSources(): array
    {
        $paths = array_merge(
            array_map(fn ($file) => public_path($file), self::FILES),
            array_map(
                fn (\SplFileInfo $file) => $file->getPathname(),
                array_filter(
                    File::allFiles(resource_path('views/marketing')),
                    fn (\SplFileInfo $file) => str_ends_with($file->getFilename(), '.blade.php')
                )
            ),
            [app_path('Http/Controllers/MarketingController.php'), config_path('marketing_related.php')]
        );

        $out = [];
        foreach ($paths as $path) {
            $out[str_replace(base_path().'/', '', $path)] = file_get_contents($path);
        }

        return $out;
    }
}
