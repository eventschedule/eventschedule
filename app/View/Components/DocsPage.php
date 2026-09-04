<?php

namespace App\View\Components;

use App\Utils\DocsUtils;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The documentation page shell.
 *
 * A leaf doc page used to hand-roll all of this: the marketing layout plus
 * four slots, a 29-line TechArticle blob, three @includes, a duplicated
 * .text-gradient-docs <style>, a motion-gate <script>, a 23-line hero and a
 * 16-line two-column scaffold. That was copy-pasted across 33 files, so any
 * visual change meant 33 edits.
 *
 * Everything except the page body now comes from config/docs.php via the
 * `page` prop. Overrides exist for the handful of places a page genuinely
 * differs from its manifest entry (the article description is often narrower
 * than the meta description, for instance).
 */
class DocsPage extends Component
{
    public array $page;

    public array $group;

    public array $siblings;

    public array $crumb;

    public function __construct(
        /** Manifest key, e.g. 'gift-cards' or 'selfhost/stripe'. */
        public string $key,
        public ?string $title = null,
        public ?string $description = null,
        /**
         * Point this page's canonical at another URL. Only for a page whose body
         * is a deliberate duplicate of another one (the two Federation pages ship
         * the same partial), so search engines consolidate on one of them instead
         * of picking for themselves. A path or an absolute URL.
         */
        public ?string $canonical = null,
        public ?string $heading = null,
        /** The word(s) in the heading to gradient-accent. Defaults to the last word. */
        public ?string $emphasis = null,
        public ?string $lede = null,
        public ?string $eyebrow = null,
        public ?string $articleHeadline = null,
        public ?string $articleDescription = null,
        public ?string $published = null,
        public ?string $modified = null,
        public ?string $plan = null,
        // Named `with*` so they cannot collide with the `toc` and `cta` slots.
        public bool $withToc = true,
        public bool $withPager = true,
        public bool $withCta = true,
    ) {
        $page = DocsUtils::page($key);

        if ($page === null) {
            throw new \InvalidArgumentException(
                "Unknown docs page '{$key}'. Add it to config/docs.php."
            );
        }

        $this->page = $page;
        $this->group = DocsUtils::group($page['group']) ?? [];
        $this->siblings = DocsUtils::prevNext($key);
        $this->crumb = DocsUtils::breadcrumb($key);
    }

    public function pageTitle(): string
    {
        return $this->title ?? $this->page['title'].' - Event Schedule';
    }

    public function metaDescription(): string
    {
        return $this->description ?? $this->page['blurb'];
    }

    /**
     * Null unless the page declares a canonical, in which case the layout's own
     * self-canonical is overridden with this absolute URL.
     */
    public function canonicalUrl(): ?string
    {
        if ($this->canonical === null || $this->canonical === '') {
            return null;
        }

        return str_starts_with($this->canonical, 'http')
            ? $this->canonical
            : rtrim(config('app.url'), '/').'/'.ltrim($this->canonical, '/');
    }

    public function headingText(): string
    {
        return $this->heading ?? $this->page['title'];
    }

    public function ledeText(): string
    {
        return $this->lede ?? $this->metaDescription();
    }

    public function accent(): string
    {
        return $this->group['accent'] ?? 'guide';
    }

    public function layoutVariant(): string
    {
        return $this->page['layout'] ?? 'standard';
    }

    /**
     * The TechArticle blob every doc page used to carry inline.
     */
    public function structuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'TechArticle',
            'headline' => $this->articleHeadline ?? $this->pageTitle(),
            'description' => $this->articleDescription ?? $this->metaDescription(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Event Schedule',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Event Schedule',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('app.url').'/images/light_logo.png',
                    'width' => 712,
                    'height' => 140,
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current(),
            ],
            'datePublished' => $this->published ?? ($this->page['published'] ?? '2024-01-01'),
            // The sitemap manifest first, config/docs.php only as a fallback.
            //
            // These two dates describe the same page and were maintained independently: the
            // manifest is regenerated from git by `sitemap:lastmod`, `modified` is typed by hand.
            // They had drifted on 34 of the 35 docs pages that carry both, always with the JSON-LD
            // older - /docs/getting-started shipped 2026-02-01 against a <lastmod> of 2026-09-01.
            // Google's stated condition for trusting lastmod is that it agrees with the on-page
            // date signals, so a site-wide disagreement of that size is exactly the "teach Google
            // to ignore lastmod" outcome GenerateSitemapLastmod was written to prevent.
            //
            // An explicit `modified` prop still wins, for a page that genuinely knows better.
            'dateModified' => $this->modified ?? $this->manifestModified() ?? ($this->page['modified'] ?? '2024-01-01'),
        ];
    }

    /**
     * This page's date from config/sitemap_lastmod.php, as a plain Y-m-d, or null.
     *
     * Bracket lookup, never config('sitemap_lastmod.'.$path): the manifest keys are URL paths
     * containing slashes, and dot notation would try to walk them as nested arrays and miss every
     * time. SitemapController::lastmodTag() reads it the same way and says so.
     */
    private function manifestModified(): ?string
    {
        $stamp = (config('sitemap_lastmod') ?: [])['/docs/'.$this->key] ?? null;

        return is_string($stamp) && $stamp !== '' ? substr($stamp, 0, 10) : null;
    }

    public function render(): View
    {
        return view('components.docs-page');
    }
}
