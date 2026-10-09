<x-marketing-layout :hp="true">
    @php
        // What this page is: the front page, a section, or a filtered list (a tag, a search, a
        // month). The first two are pages in their own right; a filtered list is not.
        $isFiltered = $tag !== null || $search !== '' || $monthLabel !== null;
        $sectionName = $category ? $categories[$category]['name'] : null;
        $blogPage = $posts->currentPage();

        // Page 1 keeps the clean URL; page 2+ names itself with the paginator's own URL, which is
        // byte-identical to the hrefs it emits. A filtered list is noindex and names itself too:
        // it used to be noindex AND canonical to the front page, two signals that disagree.
        $blogBase = $category ? route('blog.category', $category) : route('blog.index');
        $blogCanonical = $isFiltered ? url()->full() : ($blogPage > 1 ? $posts->url($blogPage) : $blogBase);

        $blogTitleSuffix = $blogPage > 1 ? ' - Page '.$blogPage : '';
        $blogDescSuffix = $blogPage > 1 ? ' Page '.$blogPage.'.' : '';

        if ($search !== '') {
            $blogHeading = 'Posts about “'.$search.'”';
            $blogTitle = 'Search: '.$search.' - Blog';
            $blogDescription = 'Posts on the Event Schedule blog that mention '.$search.'.';
            $blogLine = null;
        } elseif ($tag !== null) {
            $blogHeading = 'Posts tagged '.$tag;
            $blogTitle = $tag.' - Blog';
            $blogDescription = 'Articles about '.$tag.' on the Event Schedule blog.';
            $blogLine = null;
        } elseif ($monthLabel) {
            $blogHeading = 'Posts from '.$monthLabel;
            $blogTitle = $monthLabel.' - Blog';
            $blogDescription = 'Event Schedule blog posts from '.$monthLabel.'.';
            $blogLine = null;
        } elseif ($category) {
            $blogHeading = $sectionName;
            $blogTitle = $sectionName.' - Blog';
            $blogLine = $categories[$category]['line'];
            $blogDescription = $blogLine.' From the Event Schedule blog.';
        } else {
            $blogHeading = 'The Event Schedule blog';
            $blogTitle = 'Blog';
            $blogLine = 'Practical notes on running events: selling tickets, filling the room and keeping your calendar in step.';
            $blogDescription = 'Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.';
        }

        // A section with a post or two is not yet a page worth a chip of its own.
        $chipFloor = \App\Models\BlogPost::SECTION_MIN_POSTS;
    @endphp
    <x-slot name="title">{{ $blogTitle }}{{ $blogTitleSuffix }} | Event Schedule</x-slot>
    <x-slot name="description">{{ $blogDescription }}{{ $blogDescSuffix }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $sectionName ?? 'Blog' }}</x-slot>
    <x-slot name="canonical">{{ $blogCanonical }}</x-slot>

    {{-- Out of the index: a filtered list, a section too thin to be a page yet, and a page past the last one. --}}
    @if($isFiltered || ($category && ($counts[$category] ?? 0) < $chipFloor) || ($blogPage > 1 && $posts->isEmpty()))
        <x-slot name="robots">noindex, follow</x-slot>
    @endif

    <x-slot name="headMeta">
    @if($posts->currentPage() > 1)
        <link rel="prev" href="{{ $posts->previousPageUrl() }}">
    @endif
    @if($posts->hasMorePages())
        <link rel="next" href="{{ $posts->nextPageUrl() }}">
    @endif
    </x-slot>

    <x-slot name="structuredData">
    @php
        // Built as an array and emitted with SeoUtils::jsonLd: a Blade echo tag HTML-escapes but does
        // NOT JSON-escape, so a double quote in a post title used to invalidate the whole block.
        $blogPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            '@id' => blog_url().'#blog',
            'name' => 'Event Schedule Blog',
            'description' => 'Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.',
            // The blog entity always lives at page 1; mainEntityOfPage is what ties it to the
            // page actually being served, so it has to follow the canonical.
            'url' => route('blog.index'),
            'inLanguage' => 'en',
            'mainEntityOfPage' => [
                '@type' => 'CollectionPage',
                '@id' => $blogCanonical,
            ],
            'publisher' => \App\Utils\SeoUtils::organization(),
        ];

        // Not $post: @php shares the view scope, and the card loop below has its own variable.
        foreach (collect($lead ? [$lead] : [])->concat($posts->items()) as $listed) {
            $entry = [
                '@type' => 'BlogPosting',
                'headline' => $listed->title,
                'url' => route('blog.show', $listed->slug),
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => route('blog.show', $listed->slug),
                ],
                'image' => $listed->socialImageUrl() ?: config('app.url').'/images/social/blog.jpg',
                'author' => \App\Utils\SeoUtils::organizationRef(),
            ];
            if ($listed->excerpt) {
                $entry['description'] = $listed->excerpt;
            }
            if ($listed->published_at) {
                $entry['datePublished'] = $listed->published_at->toISOString();
                $entry['dateModified'] = ($listed->updated_at ?: $listed->published_at)->toISOString();
            }
            $blogPayload['blogPost'][] = $entry;
        }
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd($blogPayload) !!}
    </script>
    </x-slot>

    @include('blog.partials.styles')

    <section class="blog-top">
        <div class="hp-hero-sky" aria-hidden="true"></div>
        <div class="blog-wrap">
            <div class="blog-top-row">
                <div>
                    @if($category || $isFiltered)
                        <nav class="blog-crumbs" aria-label="Breadcrumb">
                            <a href="{{ route('blog.index') }}">{{ __('messages.blog') }}</a>
                            <span aria-hidden="true">/</span>
                        </nav>
                    @else
                        <span class="hp-kicker">{{ __('messages.news_tips_insights') }}</span>
                    @endif

                    <h1>{{ $blogHeading }}@if($blogPage > 1)<span class="sr-only">, page {{ $blogPage }}</span>@endif</h1>
                    @if($blogLine)
                        <p class="hp-lead">{{ $blogLine }}</p>
                    @endif
                </div>

                {{-- A GET that changes nothing and stores nothing, so it carries no honeypot. --}}
                <form class="blog-search" method="get" action="{{ route('blog.index') }}" role="search">
                    <label for="blog-q" class="sr-only">Search the blog</label>
                    <input id="blog-q" type="search" name="q" value="{{ $search }}" maxlength="80" placeholder="Search {{ $total }} posts" autocomplete="off">
                    <button type="submit" class="hp-btn hp-btn-ghost is-small is-still">Search</button>
                </form>
            </div>

            <nav class="blog-chips" aria-label="Sections">
                <a href="{{ route('blog.index') }}" class="blog-chip {{ ! $category && ! $isFiltered ? 'is-on' : '' }}" @if(! $category && ! $isFiltered) aria-current="page" @endif>All</a>
                {{-- One order in the markup. Where the row scrolls sideways (a phone) the stylesheet
                     moves the chip that is on to stand right after "All", so it is never off screen. --}}
                @foreach($categories as $key => $section)
                    @if(($counts[$key] ?? 0) >= $chipFloor || $category === $key)
                        <a href="{{ route('blog.category', $key) }}" class="blog-chip {{ $category === $key ? 'is-on' : '' }}" @if($category === $key) aria-current="page" @endif>{{ $section['name'] }}</a>
                    @endif
                @endforeach
            </nav>
        </div>
    </section>

    <section class="blog-list">
        <div class="blog-wrap">
            @if($isFiltered && $posts->total() > 0)
                <p class="blog-note">
                    <span>{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}</span>
                    <a href="{{ $blogBase }}" class="hp-inline">{{ $search !== '' ? 'Clear search' : __('messages.clear_filter') }}</a>
                </p>
            @elseif($blogPage > 1 && $posts->count() > 0)
                <p class="blog-note"><span>Page {{ $blogPage }} of {{ $posts->lastPage() }}</span></p>
            @endif

            @if($directory)
                <div class="blog-directory">
                    @foreach($directory as $group)
                        <section>
                            <h2>{{ $group['title'] }}</h2>
                            <ul>
                                @foreach($group['posts'] as $entry)
                                    <li><a href="{{ route('blog.show', $entry['slug']) }}">{{ $entry['name'] }}</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            @elseif($posts->count() > 0)
                @if($lead)
                    @include('blog.partials.card', ['card' => $lead, 'cardLead' => true, 'cardHeading' => 'h2'])
                @endif

                <div class="blog-grid">
                    @foreach($posts as $listedPost)
                        @include('blog.partials.card', ['card' => $listedPost, 'cardLead' => false, 'cardHeading' => 'h2', 'cardSection' => $category === null])
                    @endforeach
                </div>

                <div class="blog-pages">
                    {{ $posts->onEachSide(1)->links('blog.partials.pagination') }}
                </div>
            @else
                <div class="blog-empty">
                    <h2>{{ __('messages.no_posts_found') }}</h2>
                    <p>{{ $isFiltered ? 'Try another word, or pick a section above.' : __('messages.check_back_soon') }}</p>
                    @if($isFiltered)
                        <p><a href="{{ $blogBase }}" class="hp-inline">{{ $search !== '' ? 'Clear search' : __('messages.clear_filter') }}</a></p>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <x-marketing.hp-finale lead="A page for your events, free registration, and tickets with no platform fee.">
        Put your next event on a page of its own
    </x-marketing.hp-finale>
</x-marketing-layout>
