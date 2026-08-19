<?php

namespace App\Models;

use App\Utils\MarkdownUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * An operator-authored legal document that replaces the built-in page shipped
 * with the app (issue #116). Different jurisdictions need different policies -
 * GDPR, PAIA, POPIA - so an install can either point at a policy hosted
 * elsewhere (`url`) or write one here (`content`).
 *
 * Resolution order, implemented by policy_url() and LegalController:
 *   url -> content -> the built-in page.
 *
 * This is operator-only data, never editable by individual schedule owners, and
 * like `settings` it is not part of a schedule backup.
 */
class LegalDocument extends Model
{
    public const PRIVACY = 'privacy';

    public const TERMS = 'terms';

    public const COOKIES = 'cookies';

    public const TYPES = [self::PRIVACY, self::TERMS, self::COOKIES];

    /** Public path each document is served from. */
    public const PATHS = [
        self::PRIVACY => '/privacy',
        self::TERMS => '/terms-of-service',
        self::COOKIES => '/cookie-policy',
    ];

    /**
     * The named route each path is registered as, for the one caller that must not
     * build these URLs against the request host - see policy_url().
     *
     * On a nexus these routes carry a domain (Route::domain(_base_domain())), so
     * route() resolves them onto the marketing host whatever host the visitor is on;
     * url() would not. Registered under all three branches of routes/web.php, so the
     * names always exist and route() cannot throw.
     */
    public const ROUTES = [
        self::PRIVACY => 'marketing.privacy',
        self::TERMS => 'marketing.terms',
        self::COOKIES => 'marketing.cookie_policy',
    ];

    /**
     * The page shipped with the app for each type. `cookies` has none - the
     * cookie disclosure lives inside the privacy policy today - so an install
     * that has not written one has no cookie policy page at all.
     */
    public const BUILTIN_VIEWS = [
        self::PRIVACY => 'marketing.privacy',
        self::TERMS => 'marketing.terms',
    ];

    private const INDEX_CACHE_KEY = 'legal_documents_index';

    /** Per-request memo over the cache read. Dropped by flush(). */
    private static ?array $indexMemo = null;

    protected $fillable = [
        'type',
        'url',
        'content',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Guarded on isDirty so a save that did not load `content` (a
            // partial select, a forceFill of just `url`) cannot null the
            // rendered HTML out from under the public page.
            if ($model->isDirty('content')) {
                // Tables on: a cookie list and a data-retention schedule are tables.
                $html = MarkdownUtils::convertToHtml($model->content, tables: true);

                // Normalized to null when it renders to nothing (a comment-only
                // draft purifies to a bare newline), so index() cannot report a
                // blank document as published and point every consent link at it.
                $model->content_html = blank($html) ? null : $html;
            }
        });

        static::saved(fn ($model) => static::flush($model->type));
        static::deleted(fn ($model) => static::flush($model->type));
    }

    /**
     * Which documents are overridden, without loading any document body.
     *
     * Read on every page that links to a policy - up to eight times on a guest
     * ticket page - so it carries a per-request memo on top of the cache, for the
     * reason PlatformCurrency::code() gives: Cache::get() reaches the store on
     * every call, and on a database cache store that is a query each time.
     *
     * Measured on content_html, NOT content: HTML Purifier can strip an input to
     * nothing, and reporting "has content" for a document that renders as an empty
     * page would point every consent link on the install at that empty page.
     *
     * @return array<string, array{url: ?string, has_content: bool}>
     */
    public static function index(): array
    {
        if (self::$indexMemo !== null) {
            return self::$indexMemo;
        }

        try {
            return self::$indexMemo = Cache::rememberForever(self::INDEX_CACHE_KEY, function () {
                return static::query()
                    ->selectRaw("type, url, LENGTH(COALESCE(content_html, '')) as content_length")
                    ->get()
                    ->mapWithKeys(fn ($row) => [$row->type => [
                        'url' => $row->url ?: null,
                        'has_content' => $row->content_length > 0,
                    ]])
                    ->all();
            });
        } catch (\Throwable $e) {
            // Table or cache backend unavailable (e.g. during a deploy before
            // migrations run). Fail open so public pages still render against the
            // built-in documents - but report it, because failing open here means
            // every operator-authored policy silently reverts to ours while
            // /admin/legal keeps showing the saved text, and nobody would notice.
            report($e);

            return self::$indexMemo = [];
        }
    }

    /**
     * The rendered body and edit date, loaded only when a policy page is served.
     *
     * Both come from one cached payload so the "last updated" stamp does not cost
     * an extra uncached query on every render, and so it is covered by the same
     * fail-open guard as the body.
     *
     * @return array{html: ?string, updated_at: ?\Illuminate\Support\Carbon}
     */
    public static function rendered(string $type): array
    {
        $empty = ['html' => null, 'updated_at' => null];

        if (! in_array($type, self::TYPES, true)) {
            return $empty;
        }

        try {
            $payload = Cache::rememberForever(self::htmlCacheKey($type), function () use ($type) {
                $row = static::query()->where('type', $type)->first(['content_html', 'updated_at']);

                return [
                    'html' => $row?->content_html ?: null,
                    'updated_at' => $row?->updated_at?->toIso8601String(),
                ];
            });
        } catch (\Throwable $e) {
            report($e);

            return $empty;
        }

        return [
            'html' => $payload['html'] ?? null,
            // Re-hydrated rather than cached as a Carbon: the file/database cache
            // stores this serialized, and a serialized Carbon is both larger and
            // version-fragile.
            'updated_at' => isset($payload['updated_at'])
                ? \Illuminate\Support\Carbon::parse($payload['updated_at'])
                : null,
        ];
    }

    public static function flush(?string $type = null): void
    {
        self::$indexMemo = null;

        Cache::forget(self::INDEX_CACHE_KEY);

        foreach ($type ? [$type] : self::TYPES as $one) {
            Cache::forget(self::htmlCacheKey($one));
        }
    }

    private static function htmlCacheKey(string $type): string
    {
        return 'legal_document_html_'.$type;
    }
}
