<?php

namespace Tests\Feature;

use App\Models\PageView;
use App\Models\Webhook;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The marketing site does not hedge: it says "110 crawler signatures", "fourteen
 * event types", "twelve languages", "24 authenticated endpoints". That specificity
 * is the reason the pages are worth reading, and it is also the reason they rot -
 * every one of those numbers is a count of something that grows.
 *
 * /features/custom-labels is the worked example: it advertised 34 renameable labels
 * for months while the app offered 35, because the audience feature added one and
 * nothing connected the page to the list. MarketingCustomLabelsTest covers that one.
 * This covers the rest, and each case names the source of truth so a future edit
 * knows where to look rather than only that a number moved.
 */
class MarketingCountedClaimsTest extends TestCase
{
    private function page(string $relative): string
    {
        $path = resource_path("views/marketing/{$relative}.blade.php");
        $this->assertFileExists($path, "the page {$relative} has moved; this test needs its new path");

        return file_get_contents($path);
    }

    public function test_the_crawler_signature_count_matches_the_filter(): void
    {
        // PageView::isBot() holds the list inline, so read it off the method body.
        $method = new ReflectionMethod(PageView::class, 'isBot');
        $lines = file($method->getFileName());
        $body = implode('', array_slice(
            $lines,
            $method->getStartLine(),
            $method->getEndLine() - $method->getStartLine()
        ));
        preg_match_all("/^\s*'([^']+)',/m", $body, $matches);
        $count = count($matches[1]);

        $this->assertGreaterThan(50, $count, 'the signature list looks unreadable, not shorter');
        $this->assertStringContainsString(
            'data-count-to="'.$count.'"',
            $this->page('analytics'),
            "/features/analytics states a crawler-signature count; PageView::isBot() now has {$count}"
        );
    }

    public function test_the_webhook_event_type_count_matches_the_model(): void
    {
        $count = count(Webhook::EVENT_TYPES);
        $words = [12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen'];
        $this->assertArrayHasKey($count, $words, 'add the new number word to this test');

        $this->assertStringContainsString(
            $words[$count].' event types',
            $this->page('integrations'),
            "/features/integrations states the webhook event-type count; Webhook::EVENT_TYPES now has {$count}"
        );
    }

    public function test_the_language_count_matches_the_config(): void
    {
        $count = count(config('app.supported_languages'));

        // Read every marketing view AND the marketing lang file. The first draft of
        // this test read only the Blade sources and passed while the wrong number
        // sat in marketing.php's ai_description, which is where the site's
        // "translate to N languages" line actually lives.
        // array_merge, NOT +. The union operator keys on index, so the lang file at
        // index 0 of the third array was silently dropped and this test passed with
        // the wrong number sitting in it - which is exactly what it happened to be
        // guarding against.
        $sources = array_merge(
            glob(resource_path('views/marketing/*.blade.php')) ?: [],
            glob(resource_path('views/marketing/*/*.blade.php')) ?: [],
            [lang_path('en/marketing.php')]
        );

        $seen = 0;
        foreach (array_unique($sources) as $file) {
            preg_match_all('/(\d{1,2})\s+languages\b/i', file_get_contents($file), $matches);
            foreach ($matches[1] as $stated) {
                $seen++;
                $this->assertSame((string) $count, $stated,
                    basename($file)." says {$stated} languages; config('app.supported_languages') has {$count}");
            }
        }

        $this->assertGreaterThan(0, $seen, 'nothing states the language count any more, so this test pins nothing');
    }

    public function test_the_api_endpoint_counts_match_the_routes(): void
    {
        $routes = file_get_contents(base_path('routes/api.php'));
        $marker = 'Route::middleware([ApiAuthentication::class])->group';
        $this->assertStringContainsString($marker, $routes, 'the API-key route group has been renamed');

        $behindTheKey = substr($routes, strpos($routes, $marker));
        $authenticated = preg_match_all('/Route::(get|post|put|patch|delete)/', $behindTheKey);

        $words = [22 => 'Twenty-two', 23 => 'Twenty-three', 24 => 'Twenty-four', 25 => 'Twenty-five', 26 => 'Twenty-six'];
        $this->assertArrayHasKey($authenticated, $words, 'add the new number word to this test');

        $this->assertStringContainsString(
            $words[$authenticated].' authenticated endpoints',
            $this->page('open-source'),
            "/open-source states the authenticated endpoint count; routes/api.php now has {$authenticated}"
        );
    }

    public function test_the_openapi_counts_match_the_published_spec(): void
    {
        $spec = json_decode(file_get_contents(public_path('api/openapi.json')), true);
        $this->assertIsArray($spec['paths'] ?? null, 'the published OpenAPI spec has no paths');

        $paths = count($spec['paths']);
        $operations = 0;
        foreach ($spec['paths'] as $methods) {
            foreach (array_keys($methods) as $verb) {
                if (in_array($verb, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                    $operations++;
                }
            }
        }

        $source = $this->page('open-source');
        $this->assertMatchesRegularExpression(
            '/Sixteen paths, twenty-six operations/i',
            $source,
            "/open-source states the OpenAPI shape; public/api/openapi.json now has {$paths} paths and {$operations} operations"
        );
        $this->assertSame(16, $paths);
        $this->assertSame(26, $operations);
    }
}
