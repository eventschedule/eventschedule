<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Translation\FileLoader;

/**
 * The translation file loader, made survivable when an operator hand-edits a broken override.
 *
 * AppServiceProvider::register() appends config('app.lang_overrides_path') - storage/app/lang, or
 * LANG_OVERRIDES_PATH - to the translator's search path, and the selfhost docs tell operators to
 * drop their own PHP files in there. Laravel's FileLoader then does a bare require on every match,
 * so a single typo in storage/app/lang/en/messages.php throws on the FIRST __() of every request.
 * That is a total outage with no way back in: the admin translations page needs __() too, so the
 * one screen that could fix the file is the one screen that cannot render. Issue #117.
 *
 * Skipping only the offending file - rather than the whole group - keeps the shipped
 * resources/lang catalog loaded, so a bad override degrades to English instead of to raw keys.
 *
 * A parse error inside require IS catchable as a ParseError on PHP 8 (verified against a real
 * unparseable file, with OPcache on, through Filesystem::getRequire itself), which is why a
 * try/catch is enough here and no lint-before-load pass is needed.
 *
 * The guard deliberately covers every path, not just the overrides directory: a corrupted file
 * anywhere should degrade the same way rather than white-screen the install.
 */
class SafeTranslationLoader extends FileLoader
{
    /**
     * Copy an already-constructed loader's state onto a safe one. Used from the container
     * extender, because the framework builds the original with paths we should not restate
     * (they include the framework's own lang directory, which is not ours to hardcode).
     */
    public static function wrap(FileLoader $loader, Filesystem $files): self
    {
        $safe = new self($files, $loader->paths());

        foreach ($loader->jsonPaths() as $path) {
            $safe->addJsonPath($path);
        }

        foreach ($loader->namespaces() as $namespace => $hint) {
            $safe->addNamespace($namespace, $hint);
        }

        return $safe;
    }

    /**
     * {@inheritdoc}
     *
     * The is_array() check is a second, cheaper failure mode: a file that parses but forgets its
     * return statement yields int 1, and array_replace_recursive() would raise a TypeError on it.
     */
    protected function loadPaths(array $paths, $locale, $group)
    {
        return (new Collection($paths))
            ->reduce(function ($output, $path) use ($locale, $group) {
                if (! $this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
                    return $output;
                }

                try {
                    $lines = $this->files->getRequire($full);
                } catch (\Throwable $e) {
                    report($e);

                    return $output;
                }

                return is_array($lines) ? array_replace_recursive($output, $lines) : $output;
            }, []);
    }

    /**
     * {@inheritdoc}
     *
     * The parent throws RuntimeException on malformed JSON, and $this->paths is merged into the
     * search list here, so the overrides directory can take the app down through a stray
     * storage/app/lang/en.json as well as through a PHP file.
     */
    protected function loadJsonPaths($locale)
    {
        return (new Collection(array_merge($this->jsonPaths, $this->paths)))
            ->reduce(function ($output, $path) use ($locale) {
                if (! $this->files->exists($full = "{$path}/{$locale}.json")) {
                    return $output;
                }

                try {
                    $decoded = json_decode($this->files->get($full), true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    report($e);

                    return $output;
                }

                return is_array($decoded) ? array_merge($output, $decoded) : $output;
            }, []);
    }
}
