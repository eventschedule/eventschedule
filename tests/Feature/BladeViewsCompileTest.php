<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Every view compiles to PHP, and a JSON-LD "@context" key is printed as written.
 *
 * Laravel 12 added a `@context` directive (Laravel's Context), so the key in
 * `"@context": "https://schema.org"` began compiling as the opening of an `if` with no end: the
 * marketing layout, 60 pages and the user guide stopped compiling, and a key passed inside
 * `{!! !!}` printed PHP source into a page's structured data instead of failing. AppServiceProvider
 * registers `@context` over Laravel's, printing the word, since the app never uses the directive.
 */
class BladeViewsCompileTest extends TestCase
{
    public function test_every_view_compiles_to_valid_php_and_no_json_ld_key_is_a_directive(): void
    {
        $views = 0;
        $failures = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if (! str_ends_with($file, '.blade.php')) {
                continue;
            }

            $name = str_replace(resource_path('views').'/', '', $file);
            $compiled = Blade::compileString(file_get_contents($file));

            // What Laravel's own @context compiles to; `{{ context()->get(...) }}` written on purpose
            // compiles to something else and passes.
            if (str_contains($compiled, '$__contextArgs')) {
                $failures[] = $name.': "@context" is compiled as Laravel\'s @context directive (see AppServiceProvider)';
            }

            try {
                token_get_all($compiled, TOKEN_PARSE);
            } catch (\ParseError $e) {
                $failures[] = $name.': '.$e->getMessage();
            }

            $views++;
        }

        $this->assertGreaterThan(500, $views);
        $this->assertSame([], $failures);
    }

    public function test_a_context_key_is_printed_as_written_wherever_it_is_written(): void
    {
        $render = fn (string $template) => Blade::render($template, deleteCachedView: true);

        $this->assertSame(
            '<script type="application/ld+json">{"@context": "https://schema.org"}</script>',
            $render('<script type="application/ld+json">{"@context": "https://schema.org"}</script>')
        );
        $this->assertSame('{"@context":"x"}', $render('{!! json_encode([\'@context\' => \'x\']) !!}'));
        $this->assertSame('{"@context":"y"}', $render('@php($a = [\'@context\' => \'y\']){!! json_encode($a) !!}'));
        // A @php block is left raw by Blade: Laravel's directive never reached inside one.
        $this->assertSame('{"@context":"z"}', $render("@php \$b = ['@context' => 'z']; @endphp\n{!! json_encode(\$b) !!}"));
    }
}
