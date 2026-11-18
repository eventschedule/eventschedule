<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * jQuery is gone from the app (2026-11), and nothing would say so if it came back: a page that
 * calls $(...) only throws in a visitor's browser. layouts/app loaded it for every page of the
 * portal and the guest portal; what the four views used it for is now a handful of plain helpers
 * defined where it was loaded (esReady, esShow, esHide, esVal, esSetVal, esPost), there for those
 * old scripts only. New code is Vue (CLAUDE.md).
 */
class NoJqueryTest extends TestCase
{
    public function test_jquery_is_not_shipped(): void
    {
        $this->assertFileDoesNotExist(public_path('js/jquery.min.js'));
    }

    public function test_no_view_or_script_loads_or_calls_jquery(): void
    {
        $files = array_merge(
            File::allFiles(resource_path('views')),
            File::allFiles(resource_path('js')),
        );

        $calls = [
            '/<script[^>]+jquery/i',
            '/\$\(\s*[\'"`]/',
            '/\$\(\s*(?:document|window|this)\b/',
            '/\bjQuery\s*[.(]/',
            '/\$\.(?:ajax|ajaxSetup|post|get|getJSON|each|extend|fn)\b/',
        ];

        $found = [];

        foreach ($files as $file) {
            $path = $file->getPathname();

            // Vendored libraries keep their own internal $ (Leaflet's build defines one).
            if (str_ends_with($path, '.min.js') || str_ends_with($path, 'leaflet.js')) {
                continue;
            }

            $code = $this->withoutComments(File::get($path));

            foreach ($calls as $pattern) {
                if (preg_match($pattern, $code, $match)) {
                    $found[] = str_replace(base_path().'/', '', $path).': '.trim($match[0]);
                }
            }
        }

        $this->assertSame([], $found, "jQuery is no longer loaded, so these would throw in the browser. Use Vue, or plain DOM:\n".implode("\n", $found));
    }

    /** Blade comments, block comments and line comments (not the // of an address). */
    private function withoutComments(string $code): string
    {
        $code = preg_replace('/\{\{--.*?--\}\}/s', '', $code) ?? $code;
        $code = preg_replace('#/\*.*?\*/#s', '', $code) ?? $code;

        return preg_replace('#(^|[\s;{}(),])//[^\n]*#m', '$1', $code) ?? $code;
    }
}
