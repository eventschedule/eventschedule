<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TranslationSuggestion;
use App\Services\TranslationOverrideService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Public intake endpoint on the nexus app (eventschedule.com) for translation
 * improvements shared by other installs. Anonymous by design: submissions
 * carry a self-issued instance UUID and translation strings, nothing else.
 * Suggestions are inert data until an admin reviews and approves them.
 */
class ApiTranslationSuggestionController extends Controller
{
    /**
     * The most pending suggestions a single instance may hold; the backstop
     * against queue flooding on top of the route-level throttle.
     */
    public const MAX_PENDING_PER_INSTANCE = 5000;

    public function store(Request $request, TranslationOverrideService $service)
    {
        abort_unless(config('app.is_nexus'), 404);

        try {
            $validated = $request->validate([
                'instance_id' => ['required', 'uuid'],
                'app_version' => ['nullable', 'string', 'max:32'],
                'items' => ['required', 'array', 'min:1', 'max:200'],
                'items.*.locale' => ['required', Rule::in(array_keys(config('app.supported_languages')))],
                'items.*.group' => ['required', Rule::in(TranslationOverrideService::GROUPS)],
                'items.*.key' => ['required', 'string', 'max:191'],
                'items.*.value' => ['required', 'string', 'max:5000'],
                'items.*.shipped_value' => ['nullable', 'string', 'max:5000'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $instanceId = strtolower($validated['instance_id']);

        $pending = TranslationSuggestion::where('instance_id', $instanceId)->pending()->count();
        if ($pending >= self::MAX_PENDING_PER_INSTANCE) {
            return response()->json(['error' => 'Suggestion limit reached'], 422);
        }

        $enCatalogs = [];
        $effective = [];
        $accepted = 0;
        $skipped = 0;

        foreach ($validated['items'] as $item) {
            $group = $item['group'];
            $enCatalogs[$group] ??= $service->readShipped('en', $group);

            // Only keys this nexus version knows about; silently skips
            // version skew and junk keys.
            if (! array_key_exists($item['key'], $enCatalogs[$group])) {
                $skipped++;

                continue;
            }

            $value = $this->stripControlCharacters($item['value']);

            // Nothing left after stripping (control-only or invalid UTF-8): skip
            // rather than store an empty suggestion that would self-clean on approval.
            if ($value === '') {
                $skipped++;

                continue;
            }

            // No-op suggestions: the value already matches what nexus serves.
            $scope = $item['locale'].'/'.$group;
            $effective[$scope] ??= $service->effective($item['locale'], $group);
            if ($value === ($effective[$scope][$item['key']] ?? null)) {
                $skipped++;

                continue;
            }

            $suggestion = TranslationSuggestion::firstOrNew([
                'instance_id' => $instanceId,
                'locale' => $item['locale'],
                'group' => $group,
                'key' => $item['key'],
            ]);

            $valueChanged = ! $suggestion->exists || $suggestion->suggested_value !== $value;

            $suggestion->suggested_value = $value;
            $suggestion->shipped_value = isset($item['shipped_value'])
                ? $this->stripControlCharacters($item['shipped_value'])
                : null;
            $suggestion->app_version = $validated['app_version'] ?? null;

            // A changed value re-enters the review queue; re-sharing the same
            // value never resurrects a suggestion the maintainer rejected.
            if ($valueChanged) {
                $suggestion->status = TranslationSuggestion::STATUS_PENDING;
                $suggestion->reviewed_by = null;
                $suggestion->reviewed_at = null;
            }

            $suggestion->save();
            $accepted++;
        }

        return response()->json([
            'accepted' => $accepted,
            'skipped' => $skipped,
        ]);
    }

    /**
     * Remove control characters (keeping newlines and tabs) from remote input.
     * Preserves Unicode format characters (Cf) such as the bidi marks used to
     * lay out placeholders inside RTL text (ar/he) and ZWJ/ZWNJ. Returns '' when
     * the input is not valid UTF-8 (preg_replace returns null); the caller skips
     * empty results, so a malformed payload never becomes a stored suggestion.
     */
    protected function stripControlCharacters(string $value): string
    {
        return preg_replace('/[^\P{Cc}\n\t]/u', '', $value) ?? '';
    }
}
