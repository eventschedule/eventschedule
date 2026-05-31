{{--
    Marketing sitemap hreflang alternates are intentionally disabled.

    Marketing pages are English-only for SEO: the page bodies are not translated, so the layout
    (layouts/marketing.blade.php) canonicalizes every ?lang= variant onto the clean English URL
    and emits no hreflang language alternates. This partial is kept as a no-op so the many
    @include('partials.sitemap-hreflang', ...) calls in sitemap.blade.php keep working without
    edits. Restore the per-language <xhtml:link rel="alternate" hreflang="..."> rows here (one
    x-default, one en, and one per non-en config('app.supported_languages') as ?lang= variants)
    once marketing content is genuinely translated.
--}}
