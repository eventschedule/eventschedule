<?php

namespace Tests\Feature;

use App\Support\BladeJson;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * `@json` is compiled by App\Support\BladeJson, not by Laravel.
 *
 * Laravel's compileJson() splits the expression on every comma, so `@json(old('name', $x))` lost
 * the default JSON_HEX_TAG escaping (its flags became 512), and a `<!--<script` in somebody's text
 * kept the script block it was printed in from ending. Some two hundred calls in the views had
 * that shape. Here a comma splits arguments only where PHP would read it as one.
 */
class BladeJsonDirectiveTest extends TestCase
{
    private const SWALLOWER = 'Jazz <!--<script night';

    public function test_a_value_with_a_comma_in_it_keeps_the_default_escaping(): void
    {
        $html = Blade::render(
            '<script>var a = @json(collect([1])->map(fn ($n) => ["name" => $name, "n" => $n])); var b = @json(old("missing", $name));</script>',
            ['name' => self::SWALLOWER],
            deleteCachedView: true,
        );

        $this->assertStringNotContainsString('<!--', $html);
        $this->assertStringContainsString('var a = [{"name":"Jazz \u003C!--\u003Cscript night","n":1}];', $html);
        $this->assertStringContainsString('var b = "Jazz \u003C!--\u003Cscript night";', $html);
    }

    public function test_flags_a_caller_names_are_kept_and_a_tag_is_still_escaped(): void
    {
        $html = Blade::render('@json($name, JSON_UNESCAPED_UNICODE)', ['name' => 'Café <b>'], deleteCachedView: true);

        $this->assertSame('"Café \u003Cb\u003E"', $html);
    }

    public function test_a_comma_inside_a_string_or_a_closure_is_not_an_argument(): void
    {
        $this->assertSame(
            ["['a, b' => 'c,d']", 'JSON_PRETTY_PRINT', '4'],
            BladeJson::topLevelArguments("['a, b' => 'c,d'], JSON_PRETTY_PRINT, 4"),
        );
        $this->assertSame(
            ['array_map(function ($x) use ($y) { return [$x, "{$y[0]}, z"]; }, $list)'],
            BladeJson::topLevelArguments('array_map(function ($x) use ($y) { return [$x, "{$y[0]}, z"]; }, $list)'),
        );
        $this->assertSame(['(bool) ($rtl ?? false)'], BladeJson::topLevelArguments('(bool) ($rtl ?? false)'));
    }

    public function test_every_view_that_uses_it_compiles_to_valid_php(): void
    {
        $views = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if (! str_ends_with($file, '.blade.php') || ! str_contains($source = file_get_contents($file), '@json(')) {
                continue;
            }

            try {
                token_get_all(Blade::compileString($source), TOKEN_PARSE);
            } catch (\ParseError $e) {
                $this->fail(str_replace(resource_path('views').'/', '', $file).': '.$e->getMessage());
            }

            $views++;
        }

        $this->assertGreaterThan(50, $views);
    }
}
