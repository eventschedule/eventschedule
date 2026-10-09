<x-marketing-layout :hp="true">
    @php
        $postKey = $post->categoryKey();
        $postSection = $post->categoryName();
        $postSectionUrl = route('blog.category', $postKey);
        [$postOpening, $postRest] = $post->openingAndRest();
        $postSections = $post->sections();
        $postFaq = $post->faqItems();
        // "Updated" is worth a reader's eye only when it is a different day.
        $postUpdated = $post->published_at && $post->updated_at && $post->updated_at->gt($post->published_at->copy()->addDay())
            ? $post->updated_at
            : null;
    @endphp
    {{-- Both bounded in the model: the brand suffix only when it still fits in 60 characters,
         and the description cut at a word boundary to 160. See BlogPost::pageTitle(). --}}
    <x-slot name="title">{{ $post->pageTitle() }}</x-slot>
    <x-slot name="description">{{ $post->pageDescription() }}</x-slot>
    @if($post->noindex)
    {{-- An admin kept this post out of the index (blog admin list). follow, so its links still
         count; SitemapController leaves it out of sitemap-blog-*.xml to match. --}}
    <x-slot name="robots">noindex, follow</x-slot>
    @endif
    {{-- blog_url() and the stored slug, not url()->current(): slugs match case-insensitively, so
         /For-Solo-Artists rendered this post too and called itself the canonical. --}}
    <x-slot name="canonical">{{ blog_url('/'.$post->slug) }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $post->title }}</x-slot>
    <x-slot name="breadcrumbSection">{{ $postSection }}</x-slot>
    <x-slot name="breadcrumbSectionUrl">{{ $postSectionUrl }}</x-slot>
    <x-slot name="ogType">article</x-slot>
    {{-- The 1200x600 JPEG twin, not the 1.9 MB PNG: see BlogPost::socialImageUrl(). --}}
    @if($post->socialImageUrl())
    <x-slot name="socialImage">{{ $post->socialImageUrl() }}</x-slot>
    @endif

    <x-slot name="headMeta">
    @if($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toISOString() }}">
    @endif
    @if($post->updated_at ?: $post->published_at)
        <meta property="article:modified_time" content="{{ ($post->updated_at ?: $post->published_at)->toISOString() }}">
    @endif
    <meta property="article:section" content="{{ $postSection }}">
    @if($post->tags)
        @foreach($post->tags as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif
    <meta property="article:publisher" content="https://www.facebook.com/appeventschedule">
    </x-slot>

    <x-slot name="structuredData">
    @php
        // Built as arrays and emitted with SeoUtils::jsonLd: a Blade echo tag HTML-escapes but does
        // NOT JSON-escape, so a double quote in a post title used to invalidate the whole block.
        //
        // author is the Organization, not a Person: "Event Schedule Team" was never a real byline,
        // and sharing the layout's Organization @id lets the two nodes merge.
        $postUrl = blog_url('/'.$post->slug);
        $postingPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            '@id' => $postUrl.'#post',
            'url' => $postUrl,
            'headline' => $post->title,
            'description' => $post->pageDescription(),
            'image' => $post->socialImageUrl() ?: config('app.url').'/images/social/blog.jpg',
            'author' => \App\Utils\SeoUtils::organizationRef(),
            'publisher' => \App\Utils\SeoUtils::organization(),
            'isPartOf' => ['@id' => blog_url().'#blog'],
            'articleSection' => $postSection,
            'inLanguage' => 'en',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $postUrl,
            ],
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => ["[itemprop='headline']", "[itemprop='description']"],
            ],
            'wordCount' => $post->wordCount(),
        ];

        if ($post->published_at) {
            $postingPayload['datePublished'] = $post->published_at->toISOString();
            $postingPayload['dateModified'] = ($post->updated_at ?: $post->published_at)->toISOString();
        }

        if ($post->tags) {
            $postingPayload['keywords'] = implode(', ', $post->tags);
        }

        $faqPayload = $postFaq ? [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
            ], $postFaq),
        ] : null;
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd($postingPayload) !!}
    </script>
    @if ($faqPayload)
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd($faqPayload) !!}
    </script>
    @endif
    </x-slot>

    @include('blog.partials.styles')

    <article class="blog-article">
        <div class="hp-hero-sky" aria-hidden="true"></div>
        <div class="blog-body">
            <div>
            <header class="blog-head">
                <nav class="blog-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('blog.index') }}">{{ __('messages.blog') }}</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ $postSectionUrl }}">{{ $postSection }}</a>
                </nav>

                <h1 itemprop="headline" class="blog-title">{{ $post->title }}</h1>

                @if($post->excerpt)
                    <p itemprop="description" class="blog-dek">{{ $post->excerpt }}</p>
                @endif

                <p class="blog-meta">
                    @if($post->published_at)
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->formatted_published_at }}</time>
                    @endif
                    @if($postUpdated)
                        <span>Updated <time datetime="{{ $postUpdated->toDateString() }}">{{ $postUpdated->format('F j, Y') }}</time></span>
                    @endif
                    <span>{{ $post->reading_time }}</span>
                </p>
            </header>

                {{-- The opening answers the search, so it stands first: the picture and, under a
                     laptop's width, the contents list come after it. --}}
                @if(trim($postOpening) !== '')
                    <div class="blog-prose is-opening">
                        {!! $postOpening !!}
                    </div>
                @endif

                {{-- Only between two parts of a post: after a post with nothing below it, the
                     picture would be the last thing on the page. --}}
                @if($post->featured_image_url && trim($postRest) !== '')
                    <figure class="blog-figure">
                        <picture>
                            <source srcset="{{ webp_path($post->featured_image_url) }}" type="image/webp">
                            {{-- Decoration: the picture is one of a stock set and says nothing the title does not. --}}
                            <img src="{{ $post->featured_image_url }}" alt="" width="1536" height="768" loading="lazy" decoding="async">
                        </picture>
                    </figure>
                @endif

                @if(count($postSections) >= 4)
                    <details class="blog-toc blog-toc-inline">
                        <summary>In this post</summary>
                        <ol>
                            @foreach($postSections as $section)
                                <li><a href="#{{ $section['id'] }}">{{ $section['text'] }}</a></li>
                            @endforeach
                        </ol>
                    </details>
                @endif

                @if(trim($postRest) !== '')
                    <div class="blog-prose">
                        {!! $postRest !!}
                    </div>
                @endif

                @if($subAudienceInfo)
                    {{-- Under a laptop's width the rail is gone, so the audience's own page is offered here. --}}
                    <aside class="blog-plug is-inline" aria-label="Event Schedule for {{ $subAudienceInfo->parent_title }}">
                        <span class="hp-kicker">For {{ $subAudienceInfo->parent_title }}</span>
                        <h2>Event Schedule for {{ $subAudienceInfo->parent_title }}</h2>
                        <p>See how {{ $subAudienceInfo->sub_audience_name }} and others set up their schedule.</p>
                        <a href="{{ marketing_url('/' . $subAudienceInfo->parent_page) }}" class="hp-more">
                            See the page
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>
                    </aside>
                @endif
            </div>

            <aside class="blog-rail">
                @if(count($postSections) >= 4)
                    <nav class="blog-toc" aria-label="In this post">
                        <span class="blog-toc-title">In this post</span>
                        <ol>
                            @foreach($postSections as $section)
                                <li><a href="#{{ $section['id'] }}">{{ $section['text'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <div class="blog-plug">
                    @if($subAudienceInfo)
                        <span class="hp-kicker">For {{ $subAudienceInfo->parent_title }}</span>
                        <h2>Event Schedule for {{ $subAudienceInfo->parent_title }}</h2>
                        <p>See how {{ $subAudienceInfo->sub_audience_name }} and others set up their schedule.</p>
                        <a href="{{ marketing_url('/' . $subAudienceInfo->parent_page) }}" class="hp-more">
                            See the page
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>
                    @else
                        <span class="hp-kicker">Event Schedule</span>
                        <h2>One page for every event you run</h2>
                        <p>A calendar people can follow, tickets with no platform fee, and email to the people who come.</p>
                        <a href="{{ marketing_url('/features') }}" class="hp-more">
                            See what it does
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </article>

    @if($relatedPosts->count() > 0)
        <section class="blog-related" aria-labelledby="blog-related-title">
            <div class="blog-wrap">
                <div class="blog-related-head">
                    <div>
                        <span class="hp-kicker">Keep reading</span>
                        <h2 id="blog-related-title">{{ $subAudienceInfo ? 'More for '.$subAudienceInfo->parent_title : 'More on '.mb_strtolower($postSection) }}</h2>
                    </div>
                    <a href="{{ $postSectionUrl }}" class="hp-more">
                        {{ $subAudienceInfo ? 'Every kind of event' : 'Every post in this section' }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                    </a>
                </div>
                <div class="blog-grid">
                    @foreach($relatedPosts as $relatedPost)
                        @include('blog.partials.card', ['card' => $relatedPost, 'cardLead' => false, 'cardHeading' => 'h3', 'cardSection' => false])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-marketing.hp-finale lead="A page for your events, free registration, and tickets with no platform fee.">
        Put your next event on a page of its own
    </x-marketing.hp-finale>
</x-marketing-layout>
