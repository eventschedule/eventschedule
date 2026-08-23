<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * A marketing page that gates on `es-anim` must also load the reveal observer.
 *
 * marketing.css hides every [data-reveal] element behind html.es-anim, and the observer that
 * reveals them ships in marketing-home.js. Set the class, forget the script, and the page renders
 * completely blank below the nav - in every browser, for every visitor, with nothing in the console
 * and a 200 on the response. The layout warns about this in a comment; a comment does not fail a
 * build, and I shipped exactly this bug on /features/allocated-seating before a screenshot caught
 * it. The tests that "rendered" the page passed the whole time, because the markup was all there.
 */
class MarketingRevealGateTest extends TestCase
{
    public function test_every_page_that_hides_content_can_also_reveal_it(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views/marketing')) as $file) {
            $body = File::get($file->getPathname());

            // Strip Blade and HTML comments first: several pages discuss data-reveal in a note
            // explaining why they do NOT use it, and privacy.blade.php is one of them.
            $code = preg_replace(['/\{\{--.*?--\}\}/s', '/<!--.*?-->/s'], '', $body);

            if (! str_contains($code, 'data-reveal') || ! str_contains($code, 'es-anim')) {
                continue;
            }

            if (! str_contains($code, 'marketing-home.js')) {
                $offenders[] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders,
            'These pages add html.es-anim and use [data-reveal] but never load the observer, so '
            ."their content is hidden forever:\n".implode("\n", $offenders));
    }
}
