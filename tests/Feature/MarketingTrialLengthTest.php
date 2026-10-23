<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use App\Services\Blog\BlogFacts;
use App\Utils\PlatformPricing;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Every length of the free trial the marketing site states is the installation's own
 * (config('app.trial_days'), TRIAL_DAYS).
 *
 * Some thirty comparison pages, three feature pages, the pricing nudge and the blog's facts said
 * "7-day free trial" in type while /pricing and the guide printed the setting, so an install set
 * to 14 days promised two lengths at once. CI ships TRIAL_DAYS=365 and a machine with no value
 * has 7, which is why each render below pins its own number.
 */
class MarketingTrialLengthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        PlatformPricing::flush();
        config(['app.trial_days' => 14]);
    }

    protected function tearDown(): void
    {
        PlatformPricing::flush();
        parent::tearDown();
    }

    /** "7-day trial", "7 day free trial", "seven-day trial", "free for 7 days" and the like. */
    private const TYPED_WEEK = '~\b(?:7|seven)[- ]days?\b[^.\'"]{0,20}\btrial\b|\btrial\b[^.\'"]{0,30}\b(?:7|seven) days\b|\bfree for (?:7|seven) days\b~i';

    public function test_no_marketing_copy_types_the_trial_length(): void
    {
        $files = array_merge(
            [app_path('Http/Controllers/MarketingController.php'), config_path('blog_facts.php')],
            array_map(fn ($f) => $f->getPathname(), File::allFiles(resource_path('views/marketing'))),
            is_dir(resource_path('views/blog')) ? array_map(fn ($f) => $f->getPathname(), File::allFiles(resource_path('views/blog'))) : [],
        );

        $offenders = [];
        foreach ($files as $path) {
            foreach (explode("\n", File::get($path)) as $index => $line) {
                if (preg_match(self::TYPED_WEEK, $line, $m)) {
                    $offenders[] = str_replace(base_path().'/', '', $path).':'.($index + 1).': '.$m[0];
                }
            }
        }

        $this->assertSame([], $offenders, "A trial length is typed: use config('app.trial_days').\n".implode("\n", $offenders));
    }

    public function test_the_comparison_pages_say_the_configured_length(): void
    {
        $controller = app(MarketingController::class);
        $method = new \ReflectionMethod($controller, 'getComparisonData');
        $method->setAccessible(true);

        foreach (['luma', 'eventbrite', 'the-events-calendar', 'universe'] as $key) {
            $json = json_encode($method->invoke($controller, $key), JSON_UNESCAPED_SLASHES);

            $this->assertStringContainsString('14-day free trial', $json, $key);
            $this->assertStringNotContainsString('7-day free trial', $json, $key);
        }
    }

    public function test_the_feature_pages_say_the_configured_length(): void
    {
        foreach (['marketing.custom-fields', 'marketing.for-ai-agents', 'marketing.why-create-account'] as $view) {
            $html = view($view)->render();

            $this->assertStringContainsString('14-day free trial', $html, $view);
            $this->assertDoesNotMatchRegularExpression(self::TYPED_WEEK, $html, $view);
        }

        $html = view('marketing.ticketing', ['ticketFeatures' => []])->render();
        $this->assertStringContainsString('14-day free trial', $html);
    }

    public function test_the_blog_facts_say_the_configured_length(): void
    {
        $prompt = BlogFacts::forPrompt();

        $this->assertStringContainsString('a 14-day free trial', $prompt);
        $this->assertStringNotContainsString(':trial_days', $prompt);
    }
}
