<?php

namespace Tests\Feature\Concerns;

use Illuminate\Support\Facades\File;

/**
 * Keeps published translation-override files (redirected to
 * storage/framework/testing/lang by phpunit.xml) from bleeding between tests,
 * and flushes the translator's per-process cache of loaded groups.
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
