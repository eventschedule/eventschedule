<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\TranslationOverride;
use App\Utils\UrlUtils;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Manages UI translation overrides: admin-edited strings stored in the
 * translation_overrides table and published as sparse PHP files under
 * config('app.lang_overrides_path'), where the translator loader merges
 * them over the shipped resources/lang files (later loader paths win).
 *
 * Also handles sharing improvements with the nexus app (eventschedule.com),
 * which only ever happens on explicit admin action or opt-in.
 */
class TranslationOverrideService
{
    public const GROUPS = ['messages', 'accessibility', 'marketing'];

    public const SHARE_CHUNK_SIZE = 200;

    public const SHARE_MAX_ITEMS = 1000;

    /** Lazily-built HTML sanitizer for override values (see sanitizeValue). */
    protected ?HTMLPurifier $purifier = null;

    /**
     * The shipped translations from resources/lang, bypassing any overrides.
     */
    public function readShipped(string $locale, string $group): array
    {
        $this->assertValidScope($locale, $group);

        $path = lang_path($locale.'/'.$group.'.php');

        if (! is_file($path)) {
            return [];
        }

        $values = require $path;

        return is_array($values) ? $values : [];
    }

    /**
     * The current overrides for a locale/group as a key => value map.
     */
    public function overridesFor(string $locale, string $group): array
    {
        $this->assertValidScope($locale, $group);

        return TranslationOverride::where('locale', $locale)
            ->where('group', $group)
            ->pluck('value', 'key')
            ->all();
    }

    /**
     * Shipped values with overrides applied, matching what the translator serves.
     */
    public function effective(string $locale, string $group): array
    {
        return array_replace($this->readShipped($locale, $group), $this->overridesFor($locale, $group));
    }

    /**
     * Adopt hand-made override files into the database. Selfhost admins were
     * historically documented to drop PHP files into the override directory;
     * the database is now the source of truth and publish() rewrites those
     * files, so any file-only keys must be imported first or they would be
     * silently lost. Idempotent; existing database rows always win.
     */
    public function adoptFileOverrides(string $locale, string $group): int
    {
        $this->assertValidScope($locale, $group);

        $path = config('app.lang_overrides_path').'/'.$locale.'/'.$group.'.php';

        if (! is_file($path)) {
            return 0;
        }

        $values = require $path;

        if (! is_array($values)) {
            return 0;
        }

        $existing = $this->overridesFor($locale, $group);
        $adopted = 0;

        foreach ($values as $key => $value) {
            if (! is_string($key) || ! is_string($value) || array_key_exists($key, $existing)) {
                continue;
            }

            TranslationOverride::firstOrCreate(
                ['locale' => $locale, 'group' => $group, 'key' => $key],
                ['value' => $value]
            );
            $adopted++;
        }

        return $adopted;
    }

    /**
     * Save a batch of edited values. A null/empty value or a value equal to the
     * shipped translation removes the override (self-cleaning). Keys not present
     * in the shipped English catalog are ignored. Returns per-key quality
     * warnings (never blocking) and the encoded ids of the saved overrides.
     */
    public function saveOverrides(string $locale, string $group, array $values, ?int $userId, bool $publish = true): array
    {
        $this->assertValidScope($locale, $group);
        $this->adoptFileOverrides($locale, $group);

        $enCatalog = $this->readShipped('en', $group);
        $shipped = $locale === 'en' ? $enCatalog : $this->readShipped($locale, $group);

        $saved = 0;
        $removed = 0;
        $warnings = [];
        $savedHashes = [];

        foreach ($values as $key => $value) {
            if (! is_string($key) || ! array_key_exists($key, $enCatalog)) {
                continue;
            }

            // Sanitize before the value can become a live override. messages.*
            // is rendered raw ({!! __() !!}) on public pages, and this value may
            // be an approved community suggestion from an untrusted install, so
            // strip scripts/handlers/iframes while keeping safe inline markup.
            if (is_string($value)) {
                $value = $this->sanitizeValue($value);
            }

            $shippedValue = $shipped[$key] ?? null;

            if ($value === null || $value === '' || $value === $shippedValue) {
                $removed += TranslationOverride::where('locale', $locale)
                    ->where('group', $group)
                    ->where('key', $key)
                    ->delete();

                continue;
            }

            $override = TranslationOverride::firstOrNew([
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ]);

            if ($override->exists && $override->value === $value) {
                continue;
            }

            $override->value = $value;
            $override->shared_at = null;

            if (! $override->exists) {
                $override->created_by = $userId;
            }

            $override->save();

            $saved++;
            $savedHashes[] = UrlUtils::encodeId($override->id);

            $reference = $shippedValue ?? $enCatalog[$key];
            if ($warning = $this->qualityWarnings($reference, $value)) {
                $warnings[$key] = $warning;
            }
        }

        // The caller can defer publishing (e.g. to run file I/O outside a DB
        // transaction). $changed lets it know whether a publish is still owed.
        $changed = $saved || $removed;
        if ($publish && $changed) {
            $this->publish($locale, $group);
        }

        return [
            'saved' => $saved,
            'removed' => $removed,
            'warnings' => $warnings,
            'savedHashes' => $savedHashes,
            'changed' => $changed,
        ];
    }

    /**
     * Remove overrides for the given keys, restoring the shipped translations.
     */
    public function revert(string $locale, string $group, array $keys): int
    {
        $this->assertValidScope($locale, $group);
        $this->adoptFileOverrides($locale, $group);

        $removed = TranslationOverride::where('locale', $locale)
            ->where('group', $group)
            ->whereIn('key', $keys)
            ->delete();

        if ($removed) {
            $this->publish($locale, $group);
        }

        return $removed;
    }

    /**
     * Rewrite the published override file for a locale/group from the database.
     */
    public function publish(string $locale, string $group): void
    {
        $this->assertValidScope($locale, $group);

        $overrides = $this->overridesFor($locale, $group);
        $path = config('app.lang_overrides_path').'/'.$locale.'/'.$group.'.php';

        if (empty($overrides)) {
            File::delete($path);
        } else {
            File::ensureDirectoryExists(dirname($path));
            File::replace($path, $this->renderPhpFile($overrides));
        }

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }

        // Flush the translator's per-process cache so this process (tests,
        // sync-queue jobs) sees the new values immediately.
        app('translator')->setLoaded([]);
    }

    /**
     * Rebuild every published override file from the database and delete stale
     * files whose overrides no longer exist. Used after a database restore or
     * when cloning the app to a new server.
     */
    public function publishAll(): array
    {
        $written = 0;
        $deleted = 0;
        $basePath = config('app.lang_overrides_path');

        // Adopt any hand-made override files first so pruning below can never
        // delete overrides that only exist on disk.
        if (File::isDirectory($basePath)) {
            foreach (File::directories($basePath) as $localeDir) {
                $locale = basename($localeDir);
                foreach (File::files($localeDir) as $file) {
                    $group = $file->getFilenameWithoutExtension();
                    if (is_valid_language_code($locale) && in_array($group, self::GROUPS, true)) {
                        $this->adoptFileOverrides($locale, $group);
                    }
                }
            }
        }

        $active = TranslationOverride::query()
            ->select(['locale', 'group'])
            ->distinct()
            ->get()
            ->map(fn ($row) => $row->locale.'/'.$row->group)
            ->all();

        if (File::isDirectory($basePath)) {
            foreach (File::directories($basePath) as $localeDir) {
                foreach (File::files($localeDir) as $file) {
                    $group = $file->getFilenameWithoutExtension();
                    $pair = basename($localeDir).'/'.$group;
                    // Only prune files for the groups we manage. Other override
                    // files (validation.php, auth.php, custom groups) are hand-made
                    // and honored by the translator loader; never delete them.
                    if (in_array($group, self::GROUPS, true) && ! in_array($pair, $active)) {
                        File::delete($file->getPathname());
                        $deleted++;
                    }
                }
            }
        }

        foreach ($active as $pair) {
            [$locale, $group] = explode('/', $pair);
            $this->publish($locale, $group);
            $written++;
        }

        return ['written' => $written, 'deleted' => $deleted];
    }

    /**
     * One line of a lang file: 4-space indent, var_export quoting (matches the
     * style used throughout resources/lang).
     */
    public function formatPhpLine(string $key, string $value): string
    {
        return '    '.var_export($key, true).' => '.var_export($value, true).',';
    }

    /**
     * Paste-ready lang-file lines for a key => value map, sorted by key.
     */
    public function exportPhp(array $pairs): string
    {
        ksort($pairs);

        $lines = [];
        foreach ($pairs as $key => $value) {
            $lines[] = $this->formatPhpLine((string) $key, (string) $value);
        }

        return implode("\n", $lines);
    }

    /**
     * Non-blocking quality checks comparing an edited value to the reference
     * text: missing :placeholder tokens and mismatched plural "|" segments.
     * Returns null when the value looks fine.
     */
    public function qualityWarnings(string $reference, string $value): ?array
    {
        $missing = array_values(array_diff(
            $this->placeholderTokens($reference),
            $this->placeholderTokens($value)
        ));

        $plural = str_contains($reference, '|')
            && substr_count($reference, '|') !== substr_count($value, '|');

        if (! $missing && ! $plural) {
            return null;
        }

        return [
            'placeholders' => $missing,
            'plural' => $plural,
        ];
    }

    /**
     * Sanitize an override value so it can be safely rendered raw. Several
     * messages.* strings are output via {!! __() !!} on public pages; a
     * translation is UI text, never a script surface. Keeps the safe inline
     * markup the shipped strings use (bold/italic/links/line breaks) and strips
     * everything else (script/iframe/svg/img/event handlers/javascript: URIs).
     * Plain text and :placeholder tokens pass through untouched.
     */
    public function sanitizeValue(string $value): string
    {
        // Only purify values that actually carry a dangerous construct. Many
        // legitimate strings contain literal angle brackets or placeholder
        // links (help text referring to "<head>"/"<email>", a link with a
        // :smtp_link href) that the purifier would strip or re-encode - purifying
        // those would corrupt benign content for no security gain.
        if (! $this->containsDangerousHtml($value)) {
            return $value;
        }

        if ($this->purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'b,strong,i,em,u,a[href|title|target|rel],br');
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.Nofollow', true);
            $this->purifier = new HTMLPurifier($config);
        }

        return $this->purifier->purify($value);
    }

    /**
     * Whether a value carries a construct that is unsafe to render raw
     * (scripts, framing/embedding tags, event handlers, javascript:/data: URIs).
     * The single source of truth shared by the sanitizer and the review warning.
     */
    public function containsDangerousHtml(string $value): bool
    {
        return (bool) preg_match(
            '/<\s*(script|iframe|object|embed|svg|img|style|link|meta|base)\b|\bon\w+\s*=|javascript:|data:\s*text\/html/i',
            $value
        );
    }

    /**
     * The anonymous identifier sent with shared suggestions, created on first use.
     */
    public function instanceId(): string
    {
        $id = Setting::get('translation_instance_id');

        if (! $id) {
            $id = (string) Str::uuid();
            Setting::set('translation_instance_id', $id);
        }

        return $id;
    }

    /**
     * Send unshared overrides to the nexus app. Only called on explicit admin
     * action or when the auto-share setting is enabled. Successfully delivered
     * overrides are stamped shared_at; on failure the remainder stays unshared
     * so the admin can retry. Capped per call to keep requests bounded.
     */
    public function shareToNexus(?array $overrideIds = null, int $maxItems = self::SHARE_MAX_ITEMS): array
    {
        $query = TranslationOverride::unshared()->orderBy('id');

        if ($overrideIds !== null) {
            $query->whereIn('id', $overrideIds);
        }

        $overrides = $query->limit($maxItems)->get();

        // A blank-after-trim value (e.g. a hand-made ' ' label adopted from a
        // file) carries nothing to share and would be rejected by the intake's
        // `required` rule - the framework trims it to null - which would abort
        // the whole batch and permanently wedge sharing. Mark such overrides
        // handled (a later real edit resets shared_at) and never send them.
        [$blank, $sendable] = $overrides->partition(fn ($o) => trim($o->value) === '');
        if ($blank->isNotEmpty()) {
            TranslationOverride::whereIn('id', $blank->pluck('id'))->update(['shared_at' => now()]);
        }
        $overrides = $sendable->values();

        $url = rtrim(config('app.nexus_url'), '/').'/api/translations/suggestions';
        $instanceId = $this->instanceId();
        $appVersion = config('self-update.version_installed');
        $shipped = [];

        $shared = 0;
        $failed = false;

        foreach ($overrides->chunk(self::SHARE_CHUNK_SIZE) as $chunk) {
            $items = $chunk->map(function ($override) use (&$shipped) {
                $scope = $override->locale.'/'.$override->group;
                $shipped[$scope] ??= $this->readShipped($override->locale, $override->group);

                return [
                    'locale' => $override->locale,
                    'group' => $override->group,
                    'key' => $override->key,
                    'value' => $override->value,
                    'shipped_value' => $shipped[$scope][$override->key] ?? null,
                ];
            })->values()->all();

            try {
                $response = Http::timeout(10)->post($url, [
                    'instance_id' => $instanceId,
                    'app_version' => $appVersion,
                    'items' => $items,
                ]);
            } catch (\Throwable $e) {
                report($e);
                $failed = true;
                break;
            }

            if (! $response->successful()) {
                $failed = true;
                break;
            }

            TranslationOverride::whereIn('id', $chunk->pluck('id'))->update(['shared_at' => now()]);
            $shared += $chunk->count();
        }

        return [
            'shared' => $shared,
            'remaining' => TranslationOverride::unshared()->count(),
            'failed' => $failed,
        ];
    }

    protected function renderPhpFile(array $pairs): string
    {
        return "<?php\n\n// Generated by the admin translation manager. Do not edit by hand;\n// run `php artisan translations:publish` to rebuild from the database.\n\nreturn [\n".$this->exportPhp($pairs)."\n];\n";
    }

    /**
     * The :placeholder tokens in a translation string.
     */
    protected function placeholderTokens(string $text): array
    {
        preg_match_all('/(?<![a-zA-Z0-9_]):([a-zA-Z][a-zA-Z0-9_]*)/', $text, $matches);

        return array_values(array_unique(array_map(
            fn ($token) => ':'.strtolower($token),
            $matches[1]
        )));
    }

    protected function assertValidScope(string $locale, string $group): void
    {
        if (! is_valid_language_code($locale) || ! in_array($group, self::GROUPS, true)) {
            throw new \InvalidArgumentException('Invalid translation locale or file.');
        }
    }
}
