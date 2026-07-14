<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TranslationOverride;
use App\Models\TranslationSuggestion;
use App\Services\AuditService;
use App\Services\TranslationOverrideService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin translation manager: lets instance admins customize the app's UI
 * strings and share improvements with the nexus app, and (on nexus only)
 * review suggestions shared by other installs.
 */
class AdminTranslationController extends Controller
{
    public function __construct(protected TranslationOverrideService $service) {}

    /**
     * The translation editor page.
     */
    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        return view('admin.translations.index', [
            'autoShare' => (bool) Setting::get('translations_auto_share'),
            'pendingSuggestions' => config('app.is_nexus') ? TranslationSuggestion::pending()->count() : 0,
        ]);
    }

    /**
     * The full key catalog for a locale/file as JSON for the editor.
     */
    public function data(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $validated = $this->validateScope($request);
        $locale = $validated['locale'];
        $group = $validated['group'];

        // Surface any hand-made override files in the editor (and protect
        // them from being lost on the next publish). Skipped in demo mode so a
        // read (GET) never writes for the demo user.
        if (! is_demo_mode()) {
            $this->service->adoptFileOverrides($locale, $group);
        }

        $enCatalog = $this->service->readShipped('en', $group);
        $shipped = $locale === 'en' ? $enCatalog : $this->service->readShipped($locale, $group);
        $overrides = TranslationOverride::where('locale', $locale)
            ->where('group', $group)
            ->get()
            ->keyBy('key');

        $rows = [];
        foreach ($enCatalog as $key => $enValue) {
            $override = $overrides->get($key);
            $rows[] = [
                'key' => $key,
                'en' => $enValue,
                'shipped' => $shipped[$key] ?? null,
                'override' => $override?->value,
                'shared_at' => $override?->shared_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'rows' => $rows,
            'autoShare' => (bool) Setting::get('translations_auto_share'),
            'unsharedCount' => TranslationOverride::unshared()->count(),
            'pendingSuggestions' => config('app.is_nexus') ? TranslationSuggestion::pending()->count() : null,
            'isDemo' => is_demo_mode(),
        ]);
    }

    /**
     * Save a batch of edited values. Null/empty values revert their keys.
     */
    public function save(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $validated = array_merge($this->validateScope($request), $request->validate([
            'values' => ['required', 'array', 'max:500'],
            'values.*' => ['nullable', 'string', 'max:5000'],
        ]));

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $result = $this->service->saveOverrides(
            $validated['locale'],
            $validated['group'],
            $validated['values'],
            auth()->id()
        );

        AuditService::log(
            AuditService::ADMIN_TRANSLATIONS_UPDATE,
            auth()->id(),
            TranslationOverride::class,
            null,
            null,
            ['keys' => array_slice(array_keys($validated['values']), 0, 50)],
            "Edited {$result['saved']} and reverted {$result['removed']} {$validated['locale']}/{$validated['group']} translations",
        );

        if (! config('app.is_nexus') && Setting::get('translations_auto_share')) {
            $service = $this->service;
            dispatch(function () use ($service) {
                try {
                    $service->shareToNexus();
                } catch (\Throwable $e) {
                    report($e);
                }
            })->afterResponse();
        }

        return response()->json([
            'saved' => $result['saved'],
            'removed' => $result['removed'],
            'warnings' => $result['warnings'],
            'savedHashes' => $result['savedHashes'],
            'unsharedCount' => TranslationOverride::unshared()->count(),
        ]);
    }

    /**
     * Remove overrides, restoring the shipped translations.
     */
    public function revert(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $validated = array_merge($this->validateScope($request), $request->validate([
            'keys' => ['required', 'array', 'min:1', 'max:500'],
            'keys.*' => ['string', 'max:191'],
        ]));

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $removed = $this->service->revert($validated['locale'], $validated['group'], $validated['keys']);

        AuditService::log(
            AuditService::ADMIN_TRANSLATIONS_REVERT,
            auth()->id(),
            TranslationOverride::class,
            null,
            null,
            ['keys' => array_slice($validated['keys'], 0, 50)],
            "Reverted {$removed} {$validated['locale']}/{$validated['group']} translations",
        );

        return response()->json(['removed' => $removed]);
    }

    /**
     * All unshared customizations app-wide, for the share modal.
     */
    public function unshared(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_if(config('app.is_nexus'), 404);

        $shipped = [];
        $rows = TranslationOverride::unshared()
            ->orderBy('locale')
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(function ($override) use (&$shipped) {
                $scope = $override->locale.'/'.$override->group;
                $shipped[$scope] ??= $this->service->readShipped($override->locale, $override->group);

                return [
                    'hash' => UrlUtils::encodeId($override->id),
                    'locale' => $override->locale,
                    'group' => $override->group,
                    'key' => $override->key,
                    'before' => $shipped[$scope][$override->key] ?? null,
                    'after' => $override->value,
                ];
            });

        return response()->json(['rows' => $rows]);
    }

    /**
     * Share unshared customizations with the nexus app. Explicit admin action.
     */
    public function share(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_if(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'hashes' => ['nullable', 'array', 'max:'.TranslationOverrideService::SHARE_MAX_ITEMS],
            'hashes.*' => ['string'],
        ]);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $ids = null;
        if (isset($validated['hashes'])) {
            $ids = collect($validated['hashes'])
                ->map(fn ($hash) => UrlUtils::decodeId($hash))
                ->filter()
                ->all();
        }

        $result = $this->service->shareToNexus($ids);

        AuditService::log(
            AuditService::ADMIN_TRANSLATIONS_SHARE,
            auth()->id(),
            TranslationOverride::class,
            null,
            null,
            null,
            "Shared {$result['shared']} translation improvements with the nexus app".($result['failed'] ? ' (stopped on a failed request)' : ''),
        );

        return response()->json($result);
    }

    /**
     * Toggle the auto-share setting.
     */
    public function autoShare(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_if(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        Setting::set('translations_auto_share', $validated['enabled'] ? '1' : '0');

        AuditService::log(
            AuditService::ADMIN_TRANSLATIONS_AUTO_SHARE,
            auth()->id(),
            null,
            null,
            null,
            ['enabled' => $validated['enabled']],
            'Toggled translation auto-share',
        );

        return response()->json(['enabled' => (bool) $validated['enabled']]);
    }

    /**
     * The suggestion review queue page (nexus only).
     */
    public function suggestions(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        abort_unless(config('app.is_nexus'), 404);

        return view('admin.translations.suggestions');
    }

    /**
     * Suggestions grouped by (locale, file, key, suggested value) as JSON.
     */
    public function suggestionsData(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_unless(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'all'])],
            'locale' => ['nullable', Rule::in(array_keys(config('app.supported_languages')))],
            'group' => ['nullable', Rule::in(TranslationOverrideService::GROUPS)],
        ]);

        $status = $validated['status'] ?? 'pending';

        $query = TranslationSuggestion::query()->orderByDesc('id')->limit(5000);
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (! empty($validated['locale'])) {
            $query->where('locale', $validated['locale']);
        }
        if (! empty($validated['group'])) {
            $query->where('group', $validated['group']);
        }

        $shipped = [];
        $overrides = [];
        $rows = $query->get()
            ->groupBy(fn ($s) => $s->locale.'|'.$s->group.'|'.$s->key.'|'.$s->suggested_value)
            ->map(function ($suggestions) use (&$shipped, &$overrides) {
                $latest = $suggestions->sortByDesc('id')->first();
                $scope = $latest->locale.'/'.$latest->group;
                $shipped[$scope] ??= $this->service->readShipped($latest->locale, $latest->group);
                $shipped['en/'.$latest->group] ??= $this->service->readShipped('en', $latest->group);
                $overrides[$scope] ??= $this->service->overridesFor($latest->locale, $latest->group);

                $en = $shipped['en/'.$latest->group][$latest->key] ?? null;
                $nexusShipped = $shipped[$scope][$latest->key] ?? null;
                $reference = $nexusShipped ?? $en ?? '';

                return [
                    'hash' => UrlUtils::encodeId($latest->id),
                    'locale' => $latest->locale,
                    'group' => $latest->group,
                    'key' => $latest->key,
                    'en' => $en,
                    'shipped' => $latest->shipped_value,
                    'suggested' => $latest->suggested_value,
                    'instance_count' => $suggestions->pluck('instance_id')->unique()->count(),
                    'app_versions' => $suggestions->pluck('app_version')->filter()->unique()->values(),
                    'status' => $latest->status,
                    'nexus_shipped' => $nexusShipped,
                    'nexus_override' => $overrides[$scope][$latest->key] ?? null,
                    'warnings' => [
                        'quality' => $this->service->qualityWarnings($reference, $latest->suggested_value),
                        'html' => $this->introducesHtml($reference, $latest->suggested_value),
                    ],
                ];
            })
            ->values();

        return response()->json(['rows' => $rows]);
    }

    /**
     * Approve a suggestion (optionally with an edited value): applies it as a
     * live nexus override and approves identical pending suggestions.
     */
    public function approveSuggestion(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_unless(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'value' => ['nullable', 'string', 'max:5000'],
        ]);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $suggestion = TranslationSuggestion::findOrFail(UrlUtils::decodeId($hash));

        $approved = $this->applyReview($suggestion, TranslationSuggestion::STATUS_APPROVED, $validated['value'] ?? null);

        return response()->json(['approved' => $approved]);
    }

    /**
     * Reject a suggestion and identical pending suggestions.
     */
    public function rejectSuggestion(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_unless(config('app.is_nexus'), 404);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $suggestion = TranslationSuggestion::findOrFail(UrlUtils::decodeId($hash));

        $rejected = $this->applyReview($suggestion, TranslationSuggestion::STATUS_REJECTED);

        return response()->json(['rejected' => $rejected]);
    }

    /**
     * Approve or reject a batch of suggestions.
     */
    public function bulkSuggestions(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_unless(config('app.is_nexus'), 404);

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'hashes' => ['required', 'array', 'min:1', 'max:100'],
            'hashes.*' => ['string'],
        ]);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_settings_disabled')], 403);
        }

        $status = $validated['action'] === 'approve'
            ? TranslationSuggestion::STATUS_APPROVED
            : TranslationSuggestion::STATUS_REJECTED;

        $processed = 0;
        foreach ($validated['hashes'] as $hash) {
            $suggestion = TranslationSuggestion::find(UrlUtils::decodeId($hash));
            if ($suggestion) {
                $processed += $this->applyReview($suggestion, $status);
            }
        }

        return response()->json(['processed' => $processed]);
    }

    /**
     * Approved values for a locale/file as paste-ready lang-file lines.
     */
    public function exportApproved(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        abort_unless(config('app.is_nexus'), 404);

        $validated = $this->validateScope($request);

        $keys = TranslationSuggestion::where('locale', $validated['locale'])
            ->where('group', $validated['group'])
            ->where('status', TranslationSuggestion::STATUS_APPROVED)
            ->pluck('key')
            ->unique();

        // The nexus override holds the live (possibly maintainer-edited) value.
        $pairs = array_intersect_key(
            $this->service->overridesFor($validated['locale'], $validated['group']),
            array_flip($keys->all())
        );

        return response($this->service->exportPhp($pairs)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Apply a review decision to a suggestion and every pending suggestion with
     * the same locale/file/key and suggested value (they are one queue row).
     */
    protected function applyReview(TranslationSuggestion $suggestion, string $status, ?string $editedValue = null): int
    {
        $processed = 0;
        $shouldPublish = false;

        \DB::transaction(function () use ($suggestion, $status, $editedValue, &$processed, &$shouldPublish) {
            $matching = TranslationSuggestion::where('locale', $suggestion->locale)
                ->where('group', $suggestion->group)
                ->where('key', $suggestion->key)
                ->where('suggested_value', $suggestion->suggested_value)
                ->where(function ($query) use ($suggestion) {
                    $query->pending()->orWhere('id', $suggestion->id);
                })
                ->get();

            if ($status === TranslationSuggestion::STATUS_APPROVED) {
                // Defer the file write until after the transaction commits, so a
                // rollback can never leave a published override with no DB row.
                $result = $this->service->saveOverrides(
                    $suggestion->locale,
                    $suggestion->group,
                    [$suggestion->key => $editedValue ?? $suggestion->suggested_value],
                    auth()->id(),
                    publish: false,
                );
                $shouldPublish = $result['changed'];
            }

            TranslationSuggestion::whereIn('id', $matching->pluck('id'))->update([
                'status' => $status,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $processed = $matching->count();
        });

        if ($shouldPublish) {
            $this->service->publish($suggestion->locale, $suggestion->group);
        }

        AuditService::log(
            $status === TranslationSuggestion::STATUS_APPROVED
                ? AuditService::ADMIN_TRANSLATION_SUGGESTION_APPROVE
                : AuditService::ADMIN_TRANSLATION_SUGGESTION_REJECT,
            auth()->id(),
            TranslationSuggestion::class,
            $suggestion->id,
            null,
            ['key' => $suggestion->key, 'locale' => $suggestion->locale, 'value' => $editedValue ?? $suggestion->suggested_value],
            ucfirst($status)." {$processed} suggestion(s) for {$suggestion->locale}/{$suggestion->group}",
        );

        return $processed;
    }

    /**
     * Whether a suggested value carries risky HTML. Surfaced as a review warning
     * (never blocking; approval also sanitizes via TranslationOverrideService).
     * Dangerous constructs are flagged unconditionally - the shipped reference is
     * trusted and never contains them, so comparing against it would miss a
     * <script> injected into a key that already ships with benign markup (e.g. a
     * footer with <b>). New markup absent from the reference is also flagged.
     */
    protected function introducesHtml(string $reference, string $value): bool
    {
        // Same detector the sanitizer uses, so the warning and the stripping agree.
        if ($this->service->containsDangerousHtml($value)) {
            return true;
        }

        // Any newly-introduced tag markup (even if otherwise safe) is worth a hint.
        return str_contains($value, '<') && ! str_contains($reference, '<');
    }

    protected function validateScope(Request $request): array
    {
        return $request->validate([
            'locale' => ['required', Rule::in(array_keys(config('app.supported_languages')))],
            'group' => ['required', Rule::in(TranslationOverrideService::GROUPS)],
        ]);
    }
}
