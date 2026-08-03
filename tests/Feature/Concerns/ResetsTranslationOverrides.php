<?php

namespace Tests\Feature\Concerns;

use Illuminate\Support\Facades\File;

/**
 * Keeps published translation-override files (redirected under
 * storage/framework/testing/ by phpunit.xml, per session by tests/bootstrap.php)
 * from bleeding between tests, and flushes the translator's per-process cache of
 * loaded groups.
 */
trait ResetsTranslationOverrides
{
    protected function setUpResetsTranslationOverrides(): void
    {
        $this->resetTranslationOverrideFiles();
    }

    protected function tearDownResetsTranslationOverrides(): void
    {
        $this->resetTranslationOverrideFiles();
    }

    private function resetTranslationOverrideFiles(): void
    {
        File::deleteDirectory(config('app.lang_overrides_path'));
        app('translator')->setLoaded([]);
    }
}
