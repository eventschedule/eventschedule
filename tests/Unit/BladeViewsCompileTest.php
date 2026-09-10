<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Blade;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Every Blade view must compile to valid PHP, and none may leak Blade source into its output.
 *
 * Nothing else in the suite proves this. A view is only compiled when something renders it, and
 * the views most likely to carry a compilation fault are the ones no test renders - the
 * plain-text halves of mailables in particular, because Mailable::render() returns only the HTML
 * view. `emails/event_interest_text.blade.php` shipped broken for exactly that reason: it threw a
 * ParseError on every send while EventInterestSendTest, which calls ->render(), stayed green.
 *
 * THE TWO ASSERTIONS CATCH DIFFERENT THINGS, and the second is not redundant.
 *
 * 1. Lint. Blade's directive pattern requires a non-word-boundary before the `@`, so a directive
 *    placed immediately after another one - `@endif@if`, the classic - is left as literal text
 *    while its partner still compiles. The result is PHP with an unmatched `endif`.
 *
 * 2. No leaked markers. `@endphp` followed directly by an echo, and a Blade comment containing a
 *    literal comment terminator, both end the block early and spill the remainder into the
 *    rendered output. That output is still syntactically valid PHP, so the linter passes it -
 *    the only evidence is Blade's own `@__raw_block_N__` placeholder surviving into the compiled
 *    source, or a stray `{{` / `@if` in what should be finished PHP. Both mistakes were made
 *    while fixing the first one, which is why this is pinned rather than trusted.
 */
class BladeViewsCompileTest extends TestCase
{
    public function test_every_blade_view_compiles_to_valid_php(): void
    {
        $failures = [];
        $leaks = [];
        $checked = 0;

        $scratch = tempnam(sys_get_temp_dir(), 'bladecheck').'.php';

        foreach ($this->views() as $view) {
            $checked++;

            $compiled = Blade::compileString(file_get_contents($view));

            file_put_contents($scratch, $compiled);
            $output = [];
            exec('php -l '.escapeshellarg($scratch).' 2>&1', $output, $status);

            if ($status !== 0) {
                $failures[] = $this->relative($view).' - '.trim($output[1] ?? $output[0] ?? 'parse error');
            }

            // A surviving placeholder means a @php/@verbatim block was closed early and its tail
            // is now literal output. Blade never leaves one behind in a well-formed template.
            if (str_contains($compiled, '@__raw_block_')) {
                $leaks[] = $this->relative($view).' - unterminated raw block (@__raw_block_ marker survived)';
            }
        }

        @unlink($scratch);

        $this->assertGreaterThan(100, $checked, 'the view sweep found almost nothing - has resources/views moved?');
        $this->assertSame([], $failures, "Blade views that do not compile:\n".implode("\n", $failures));
        $this->assertSame([], $leaks, "Blade views leaking source into their output:\n".implode("\n", $leaks));
    }

    /** @return iterable<string> */
    private function views(): iterable
    {
        $root = resource_path('views');

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                yield $file->getPathname();
            }
        }
    }

    private function relative(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/');
    }
}
