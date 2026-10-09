<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * /open-source says "every claim here has a file path" and then prints the file: each claim is
 * quoted from this application's own source at render time (App\Utils\SourceExcerpt). A quotation
 * is found by the words on its lines, so it follows the code as it moves, and when the words go it
 * is simply left out. That is right for a visitor and wrong for us: a claim whose proof has left
 * the file must not stay on the page unnoticed. This is what notices.
 *
 * It also holds the page's own wiring, because the page is plain script on server-rendered
 * markup and a renamed hook fails without a word: the switch, the claims, the headline's two
 * faces and the endpoint tools each find their parts by a data attribute.
 */
class OpenSourcePageTest extends TestCase
{
    /** What each quotation must still say, by the file it is quoted from. */
    private const PROOFS = [
        'resources/views/marketing/open-source.blade.php' => 'class="hp-h1"',
        'app/Models/Role.php' => "if (! config('app.hosted')) {",
        'app/Http/Middleware/ApiAuthentication.php' => 'Hash::check($apiKey, $candidate->api_key_hash)',
        'app/Jobs/SendWebhook.php' => "hash_hmac('sha256', \$jsonBody, \$this->webhook->secret)",
        'app/Utils/UrlUtils.php' => 'FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE',
        'app/Services/BackupService.php' => "'events' => \$eventsData",
        'config/app.php' => "'he' => 'hebrew'",
        '.env.example' => 'ONESIGNAL_APP_ID=',
        'app/Services/FederationService.php' => "Setting::get('federation_enabled')",
        'config/self-update.php' => "'version_installed' =>",
        'app/Services/AuditService.php' => 'self::filterSensitive($oldValues)',
        'routes/api.php' => "Route::get('/fan-content'",
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    private function page(): string
    {
        $response = $this->get(route('marketing.open_source'));
        $response->assertOk();

        return $response->getContent();
    }

    /** @return array<string, list<string>> path => the text of each quotation from it */
    private function quotations(string $html): array
    {
        preg_match_all('/<figure class="[^"]*os-code[^"]*">(.*?)<\/figure>/s', $html, $figures);
        $found = [];

        foreach ($figures[1] as $figure) {
            // The path is printed a folder to a span, so that it folds at its slashes.
            $this->assertSame(1, preg_match('/class="os-path">(.*?)<\/a>/s', $figure, $path), 'a quotation has no path');
            preg_match_all('/<code[^>]*>(.*?)<\/code>/s', $figure, $lines);
            $found[strip_tags($path[1])][] = implode("\n", array_map(
                // An empty line is printed as a hard space, to keep its height.
                fn ($line) => str_replace("\u{00A0}", '', html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5)),
                $lines[1]
            ));
        }

        return $found;
    }

    public function test_every_claim_still_has_its_proof(): void
    {
        $quotations = $this->quotations($this->page());

        foreach (self::PROOFS as $path => $words) {
            $this->assertArrayHasKey($path, $quotations,
                "/open-source no longer quotes {$path}: the words it looks for have left the file. ".
                'Either the claim is no longer true (take it off the page) or the code moved (update the words in the view).');

            $this->assertTrue(
                collect($quotations[$path])->contains(fn ($text) => str_contains($text, $words)),
                "the quotation from {$path} no longer holds the line that proves its claim: {$words}"
            );
        }

        // The middleware is quoted twice (the key, the limits), everything else once.
        $this->assertCount(2, $quotations['app/Http/Middleware/ApiAuthentication.php']);
        $this->assertSame(count(self::PROOFS) + 1, array_sum(array_map('count', $quotations)));
    }

    public function test_a_quotation_is_the_file_word_for_word(): void
    {
        $quoted = $this->quotations($this->page())['app/Models/Role.php'][0];
        $method = new \ReflectionMethod(\App\Models\Role::class, 'isPro');
        $lines = array_slice(file($method->getFileName(), FILE_IGNORE_NEW_LINES), $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);

        // The page takes each line's indent off and hands it to the stylesheet as a number.
        $this->assertSame(array_map('trim', $lines), array_map('trim', explode("\n", $quoted)));
    }

    public function test_the_switch_marks_the_lines_each_side_runs(): void
    {
        $html = $this->page();

        // Counted on the lines themselves: the stylesheet names the same attribute.
        $this->assertSame(3, preg_match_all('/class="os-line is-marked"\s+data-mark="self"/', $html), 'the three lines a selfhost stops at');
        $this->assertGreaterThanOrEqual(10, preg_match_all('/class="os-line is-marked"\s+data-mark="hosted"/', $html));

        // Ten things a selfhost changes, each with both sides in the markup: with scripts off
        // nothing is hidden, the line reads as plain text and no switch is offered (the large
        // one, the bar and the one in the method's heading each start hidden).
        $this->assertSame(10, substr_count($html, 'class="os-cell is-hosted"'));
        $this->assertSame(10, substr_count($html, 'class="os-cell is-self"'));
        $this->assertMatchesRegularExpression('/<button[^>]*data-os-toggle[^>]*\shidden>/', $html);
        $this->assertMatchesRegularExpression('/<div class="os-dock"[^>]*\shidden>/', $html);
        $this->assertMatchesRegularExpression('/<span class="os-mini"[^>]*\shidden>/', $html);
        $this->assertStringContainsString('data-os-static', $html);

        $source = file_get_contents(resource_path('views/marketing/open-source.blade.php'));
        $this->assertStringContainsString('.os-code-head .os-mini:not([hidden])', $source,
            'without :not([hidden]) the rule that shows the small switch outranks the attribute that hides it, and a dead switch stands on the page until the script runs');
    }

    public function test_the_parts_the_script_looks_for_are_there(): void
    {
        $html = $this->page();

        // The page's own script is the last inline one that speaks of the switch.
        $this->assertSame(1, preg_match('/<script[^>]*>(?:(?!<\/script>).)*data-os-switch(?:(?!<\/script>).)*<\/script>/s', $html, $script));
        $markup = preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html);

        foreach ([
            'data-os-flip', 'data-os-flip-back', 'data-os-own', 'data-os-turn', 'data-os-turn-label',
            'data-os-switch', 'data-os-toggle', 'data-os-static', 'data-os-hint', 'data-os-head', 'data-os-sum',
            'data-os-dock', 'data-os-mini', 'data-os-env', 'data-os-proof',
            'data-os-api-tools', 'data-os-verbs', 'data-os-verb', 'data-os-api-list', 'data-os-api-source', 'data-os-face', 'data-os-end', 'data-os-res-count',
        ] as $hook) {
            $exact = '/'.preg_quote($hook, '/').'(?![a-z-])/';

            $this->assertMatchesRegularExpression($exact, $markup, "the script looks for {$hook} and the markup no longer carries it");
            $this->assertMatchesRegularExpression($exact, $script[0], "{$hook} is in the markup and the script no longer asks for it");
        }

        // The state the switch writes is read by the stylesheet under these two names only.
        $this->assertSame(1, preg_match('/data-os-first="(hosted|self)"/', $markup, $first));
        $this->assertSame(config('app.hosted') ? 'hosted' : 'self', $first[1], 'the switch opens on how this server itself is set');
    }

    public function test_every_link_inside_the_page_lands_somewhere(): void
    {
        $html = $this->page();

        preg_match_all('/href="#([A-Za-z0-9_-]+)"/', $html, $links);
        preg_match_all('/\sid="([A-Za-z0-9_-]+)"/', $html, $ids);

        $missing = array_values(array_diff(array_unique($links[1]), $ids[1]));

        $this->assertSame([], $missing, 'these links on /open-source go to nothing: #'.implode(', #', $missing));

        // Nine claims, each reachable by name: the files under the hero open them.
        $this->assertSame(9, preg_match_all('/id="proof-tab-[a-z]+"/', $html));
    }

    public function test_the_endpoints_printed_are_the_routes(): void
    {
        $html = $this->page();
        $routes = file_get_contents(base_path('routes/api.php'));
        $behindTheKey = substr($routes, strpos($routes, 'Route::middleware([ApiAuthentication::class])->group'));

        preg_match_all('/data-os-end="(GET|POST|PUT|DELETE)">.*?<code[^>]*>(.*?)<\/code>/s', $html, $rows, PREG_SET_ORDER);
        $this->assertCount(preg_match_all('/Route::(get|post|put|patch|delete)/', $behindTheKey), $rows, 'the page lists a different number of endpoints than routes/api.php declares');

        foreach ($rows as [, $verb, $path]) {
            $path = substr(html_entity_decode(strip_tags($path)), 4);
            $this->assertStringContainsString('Route::'.strtolower($verb)."('".$path."'", $behindTheKey, "{$verb} /api{$path} is printed on /open-source and is not in routes/api.php");
        }

        // The method filter's counts are counted, not typed.
        foreach (['GET', 'POST', 'PUT', 'DELETE'] as $verb) {
            $count = count(array_filter($rows, fn ($row) => $row[1] === $verb));
            $this->assertMatchesRegularExpression('/data-os-verb="'.$verb.'">'.$verb.' <span>'.$count.'<\/span>/', $html);
        }
    }

    public function test_the_licence_is_printed_whole(): void
    {
        $html = $this->page();
        $licence = trim(file_get_contents(base_path('LICENSE')));
        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', $licence))));

        $this->assertSame(1, preg_match('/<article class="os-sheet".*?<\/article>/s', $html, $sheet));

        foreach ($paragraphs as $paragraph) {
            $this->assertStringContainsString(e($paragraph), $sheet[0], 'a paragraph of LICENSE is missing from the page');
        }

        // The two conditions that ask something are the ones marked, and nothing else is.
        preg_match_all('/<mark>(.*?)<\/mark>/s', $sheet[0], $marked);
        $this->assertCount(5, $marked[1]);
        $this->assertStringStartsWith('1. ', $marked[1][0]);
        $this->assertStringStartsWith('2. ', $marked[1][1]);

        $this->assertStringContainsString(count(file(base_path('LICENSE'))).' lines', $sheet[0]);
    }

    public function test_the_page_keeps_to_the_house_rules_its_new_text_could_break(): void
    {
        $source = file_get_contents(resource_path('views/marketing/open-source.blade.php'));

        $this->assertStringNotContainsString("\u{2014}", $source, 'no em-dashes');
        $this->assertDoesNotMatchRegularExpression('/self-host(?!ing)/i', $source, '"selfhost", not "self-host"');
        $this->assertStringNotContainsString('<!--', $source, 'Blade comments, never HTML ones');
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+="/', $source, 'no inline handlers: the content security policy drops them');
    }
}
