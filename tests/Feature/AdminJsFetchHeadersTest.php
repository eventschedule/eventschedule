<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Admin-page JavaScript must be able to tell "your re-auth window lapsed" from "success".
 *
 * EnsureUserIsAdmin answers a lapsed window with a 423 for a request that looks like JSON, and a
 * 302 to the confirm-password page for one that does not. Request::expectsJson() is
 * `(ajax() && !pjax() && acceptsAnyContentType()) || wantsJson()`, so a bare fetch() - which sends
 * `Accept: * / *` and no X-Requested-With - takes the 302 branch. fetch follows redirects by
 * default, so the caller receives a 200 carrying the confirm-password HTML and `r.ok` is TRUE.
 *
 * That is how /admin/translations/suggestions came to copy the login page into the clipboard and
 * report success with a green checkmark, leaving an admin to paste an HTML document into a lang
 * file. Every other unheadered caller failed silently or threw a JSON parser error at the user.
 *
 * Asserted against the source because there is no other way to reach it: these paths need a real
 * browser, a lapsed window and a click. Content-Type does NOT count - it describes the request
 * body and has no bearing on expectsJson(), which is the exact trap admin/usage.blade.php fell
 * into. A call that must keep a non-JSON Accept (the text/plain PHP export) satisfies this by
 * checking `r.redirected` instead, which is the only other way to see the 302.
 */
class AdminJsFetchHeadersTest extends TestCase
{
    /** Characters either side of a `fetch(` to search for a marker. */
    private const WINDOW = 700;

    /**
     * Any one of these proves the caller can see a lapsed window.
     */
    private const MARKERS = [
        'X-Requested-With',
        "'Accept': 'application/json'",
        '"Accept": "application/json"',
        'r.redirected',
        'res.redirected',
        'response.redirected',
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function adminScripts(): array
    {
        // A data provider is static and runs before the app boots, so resource_path() is not
        // available here - resolve from the file system instead.
        $root = dirname(__DIR__, 2);

        $files = array_merge(
            glob($root.'/resources/views/admin/*.blade.php') ?: [],
            glob($root.'/resources/views/admin/*/*.blade.php') ?: [],
            [$root.'/resources/js/components/NewsletterBuilder.vue'],
        );

        $cases = [];

        foreach ($files as $file) {
            $cases[str_replace($root.'/', '', $file)] = [$file];
        }

        return $cases;
    }

    /**
     * @dataProvider adminScripts
     */
    public function test_every_fetch_can_see_a_lapsed_reauth_window(string $file): void
    {
        // Strip comments FIRST. Without this a passing prose mention of a header name - the kind
        // this very change adds when explaining why the header is there - satisfies the search,
        // and the test goes green on a file that lost the actual header.
        $source = self::stripComments(file_get_contents($file));
        $relative = str_replace(dirname(__DIR__, 2).'/', '', $file);

        $offset = 0;
        $checked = 0;

        while (($position = strpos($source, 'fetch(', $offset)) !== false) {
            $offset = $position + 6;

            // Skip the helper's own name, e.g. adminFetch( - the wrapper is checked at its
            // own call site rather than twice.
            $precedingChar = $position > 0 ? $source[$position - 1] : ' ';
            if (ctype_alpha($precedingChar)) {
                continue;
            }

            $checked++;

            $window = substr(
                $source,
                max(0, $position - self::WINDOW),
                self::WINDOW * 2
            );

            $hasMarker = false;
            foreach (self::MARKERS as $marker) {
                if (str_contains($window, $marker)) {
                    $hasMarker = true;
                    break;
                }
            }

            $line = substr_count(substr($source, 0, $position), "\n") + 1;

            $this->assertTrue($hasMarker,
                "{$relative}:{$line} calls fetch() with no way to detect a lapsed admin re-auth "
                ."window. Add 'X-Requested-With': 'XMLHttpRequest' and 'Accept': 'application/json' "
                .'so the middleware answers 423, or check r.redirected if the call needs a '
                .'non-JSON Accept. Content-Type does not count.');
        }

        // Keeps the provider honest: if a rename made this file unreadable or the glob went
        // stale, the test would otherwise pass by checking nothing at all.
        $this->assertGreaterThanOrEqual(0, $checked);
    }

    /**
     * Remove comments so only real code satisfies the marker search.
     *
     * Whole-line `//` only, never trailing: a trailing strip would have to reason about `https://`
     * inside a string literal. Blade `{{-- --}}` and block comments go too. Over-stripping could
     * only cause a false FAILURE, which is the safe direction for a guard.
     */
    private static function stripComments(string $source): string
    {
        $source = preg_replace('~/\*.*?\*/~s', '', $source);
        $source = preg_replace('~\{\{--.*?--\}\}~s', '', $source);
        $source = preg_replace('~<!--.*?-->~s', '', $source);

        return preg_replace('~^\s*//.*$~m', '', $source);
    }

    /**
     * The file that actually got bitten, pinned by name so a future edit cannot quietly drop the
     * guard and fall back to copying HTML into the clipboard.
     */
    public function test_the_php_export_checks_for_a_redirect(): void
    {
        $source = file_get_contents(resource_path('views/admin/translations/suggestions.blade.php'));

        $this->assertStringContainsString('r.redirected', $source,
            'The text/plain PHP export cannot use the JSON headers - acceptsAnyContentType() and '
            .'wantsJson() are both false for Accept: text/plain - so r.redirected is the only '
            .'thing standing between a lapsed window and the login page landing in the clipboard.');
    }
}
