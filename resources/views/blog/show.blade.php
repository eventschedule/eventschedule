<x-marketing-layout>
    <x-slot name="title">{{ $post->meta_title }} | Event Schedule</x-slot>
    <x-slot name="description">{{ $post->meta_description }}</x-slot>
    @if($post->tags)
    <x-slot name="keywords">{{ implode(', ', $post->tags) }}, event scheduling, ticketing</x-slot>
    @endif

    <!-- Additional Meta Tags -->
    <meta property="og:type" content="article">
    @if($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toISOString() }}">
    @endif
    <meta property="article:modified_time" content="{{ $post->updated_at->toISOString() }}">
    <meta property="article:author" content="{{ $post->author_name }}">
    @if($post->tags)
        @foreach($post->tags as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endif

    <!-- BlogPosting Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "{{ $post->title }}",
        "description": "{{ $post->meta_description }}",
        "image": "{{ $post->featured_image_url ?: config('app.url') . '/images/social/home.png' }}",
        "author": {
            "@type": "Person",
            "name": "{{ $post->author_name }}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png"
            }
        },
        "datePublished": "{{ $post->published_at ? $post->published_at->toISOString() : '' }}",
        "dateModified": "{{ $post->updated_at->toISOString() }}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
        @if($post->tags)
        ,"keywords": "{{ implode(', ', $post->tags) }}"
        @endif
    }
    </script>

    <!-- BreadcrumbList Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ config('app.url') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Blog",
                "item": "{{ route('blog.index') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "{{ $post->title }}",
                "item": "{{ url()->current() }}"
            }
        ]
    }
    </script>

    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }

        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Override any existing hover styles that might conflict */
        .prose a:hover,
        .prose-lg a:hover {
            text-decoration: underline !important;
        }
    </style>

    <!-- Full-width Hero Header -->
    <header class="relative overflow-hidden bg-[#0a0a0f]">
        <!-- Animated gradient orbs - larger and more prominent -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-gradient-to-b from-violet-600/30 via-indigo-600/20 to-transparent rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute -bottom-32 -left-32 w-[400px] h-[400px] bg-gradient-to-r from-fuchsia-600/25 to-pink-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
            <div class="absolute -bottom-32 -right-32 w-[400px] h-[400px] bg-gradient-to-l from-blue-600/20 to-cyan-600/15 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:60px_60px]"></div>

        <div class="relative z-10 py-16 sm:py-20 lg:py-24">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <!-- Breadcrumb -->
                <nav class="mb-8" aria-label="Breadcrumb">
                    <ol class="flex items-center justify-center space-x-2 text-sm">
                        <li>
                            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1 text-gray-400 hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                                {{ __('messages.blog') }}
                            </a>
                        </li>
                        <li>
                            <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </li>
                        <li class="text-gray-500 truncate max-w-xs">{{ __('messages.article') }}</li>
                    </ol>
                </nav>

                <!-- Tags above title -->
                @if($post->tags)
                    <div class="flex flex-wrap gap-2 justify-center mb-6">
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                               class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-violet-500/20 text-violet-300 hover:bg-violet-500/30 transition-colors border border-violet-500/30">
                                #{{ $tag }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Title -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white mb-6 leading-tight">
                    {{ $post->title }}
                </h1>

                <!-- Meta info -->
                <div class="flex items-center justify-center gap-4 text-sm text-gray-400 mb-8">
                    @if($post->published_at)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <time datetime="{{ $post->published_at->toISOString() }}">
                                {{ $post->formatted_published_at }}
                            </time>
                        </div>
                    @endif
                    <span class="text-gray-600">•</span>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-fuchsia-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $post->reading_time }}</span>
                    </div>
                </div>

                <!-- Excerpt -->
                @if($post->excerpt)
                    <p class="text-lg sm:text-xl text-gray-300 leading-relaxed max-w-2xl mx-auto">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Bottom fade -->
        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-50 dark:from-gray-900 to-transparent"></div>
    </header>

    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen pb-12">
        <article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 -mt-4">

            <!-- CTA Card -->
            <div class="mb-8 overflow-visible relative z-20">
                @if($subAudienceInfo)
                    @php
                        // Color mapping for parent audiences
                        $colorMap = [
                            'for-musicians' => ['from' => 'cyan-600', 'to' => 'teal-600', 'shadow' => 'cyan-500/25'],
                            'for-bars' => ['from' => 'amber-500', 'to' => 'amber-600', 'shadow' => 'amber-500/25'],
                            'for-restaurants' => ['from' => 'rose-500', 'to' => 'rose-600', 'shadow' => 'rose-500/25'],
                            'for-nightclubs' => ['from' => 'fuchsia-600', 'to' => 'pink-600', 'shadow' => 'fuchsia-500/25'],
                            'for-djs' => ['from' => 'indigo-600', 'to' => 'purple-600', 'shadow' => 'indigo-500/25'],
                            'for-comedians' => ['from' => 'amber-500', 'to' => 'orange-500', 'shadow' => 'amber-500/25'],
                            'for-music-venues' => ['from' => 'cyan-600', 'to' => 'cyan-700', 'shadow' => 'cyan-500/25'],
                            'for-theaters' => ['from' => 'rose-600', 'to' => 'purple-600', 'shadow' => 'rose-500/25'],
                            'for-comedy-clubs' => ['from' => 'pink-500', 'to' => 'pink-600', 'shadow' => 'pink-500/25'],
                            'for-breweries-and-wineries' => ['from' => 'amber-500', 'to' => 'orange-500', 'shadow' => 'amber-500/25'],
                            'for-art-galleries' => ['from' => 'fuchsia-600', 'to' => 'purple-600', 'shadow' => 'fuchsia-500/25'],
                            'for-community-centers' => ['from' => 'emerald-500', 'to' => 'emerald-600', 'shadow' => 'emerald-500/25'],
                            'for-circus-acrobatics' => ['from' => 'fuchsia-500', 'to' => 'fuchsia-600', 'shadow' => 'fuchsia-500/25'],
                            'for-magicians' => ['from' => 'violet-600', 'to' => 'violet-700', 'shadow' => 'violet-500/25'],
                            'for-spoken-word' => ['from' => 'rose-500', 'to' => 'rose-600', 'shadow' => 'rose-500/25'],
                            'for-dance-groups' => ['from' => 'fuchsia-600', 'to' => 'pink-600', 'shadow' => 'fuchsia-500/25'],
                            'for-theater-performers' => ['from' => 'purple-600', 'to' => 'purple-700', 'shadow' => 'purple-500/25'],
                            'for-food-trucks-and-vendors' => ['from' => 'orange-500', 'to' => 'orange-600', 'shadow' => 'orange-500/25'],
                            'for-fitness-and-yoga' => ['from' => 'emerald-500', 'to' => 'teal-500', 'shadow' => 'emerald-500/25'],
                            'for-visual-artists' => ['from' => 'fuchsia-500', 'to' => 'pink-500', 'shadow' => 'fuchsia-500/25'],
                            'for-workshop-instructors' => ['from' => 'amber-500', 'to' => 'orange-500', 'shadow' => 'amber-500/25'],
                            'for-farmers-markets' => ['from' => 'emerald-500', 'to' => 'green-500', 'shadow' => 'emerald-500/25'],
                            'for-hotels-and-resorts' => ['from' => 'sky-500', 'to' => 'blue-500', 'shadow' => 'sky-500/25'],
                            'for-libraries' => ['from' => 'amber-600', 'to' => 'amber-700', 'shadow' => 'amber-500/25'],
                            'for-webinars' => ['from' => 'teal-600', 'to' => 'cyan-600', 'shadow' => 'teal-500/25'],
                            'for-live-concerts' => ['from' => 'rose-500', 'to' => 'amber-500', 'shadow' => 'rose-500/25'],
                            'for-online-classes' => ['from' => 'emerald-500', 'to' => 'teal-500', 'shadow' => 'emerald-500/25'],
                            'for-virtual-conferences' => ['from' => 'sky-500', 'to' => 'blue-500', 'shadow' => 'sky-500/25'],
                            'for-live-qa-sessions' => ['from' => 'violet-500', 'to' => 'purple-500', 'shadow' => 'violet-500/25'],
                            'for-watch-parties' => ['from' => 'indigo-500', 'to' => 'cyan-500', 'shadow' => 'indigo-500/25'],
                        ];
                        $colors = $colorMap[$subAudienceInfo->parent_page] ?? ['from' => 'violet-500', 'to' => 'purple-500', 'shadow' => 'violet-500/25'];
                    @endphp
                    <a href="{{ marketing_url('/' . $subAudienceInfo->parent_page) }}" class="block group">
                        <div class="bg-gradient-to-r from-{{ $colors['from'] }} to-{{ $colors['to'] }} rounded-2xl p-6 md:p-8 shadow-md transition-all duration-300 group-hover:shadow-lg group-hover:shadow-{{ $colors['shadow'] }} group-hover:scale-[1.02]">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-xl md:text-2xl font-bold text-white mb-2">
                                        Learn more about Event Schedule for {{ $subAudienceInfo->parent_title }}
                                    </h3>
                                    <p class="text-white/90 text-base md:text-lg">
                                        See how {{ $subAudienceInfo->sub_audience_name }} and others are using Event Schedule
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-semibold px-5 py-3 rounded-xl transition-colors">
                                        {{ __('messages.learn_more') }}
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @else
                    <a href="{{ marketing_url('/') }}" class="block group">
                        <div class="bg-gradient-to-r from-violet-500 via-purple-500 to-fuchsia-500 rounded-2xl p-6 shadow-md transition-all duration-300 group-hover:shadow-lg group-hover:shadow-violet-500/25 group-hover:scale-[1.02]">
                            <div class="text-center">
                                <p class="text-white text-lg font-medium">
                                    {!! str_replace(':link', '<span class="font-bold underline">eventschedule.com</span>',  __('messages.try_event_schedule')) !!}
                                </p>
                            </div>
                        </div>
                    </a>
                @endif
            </div>

            <!-- Featured Image -->
            @if($post->featured_image_url)
                <div class="mb-8">
                    <img src="{{ $post->featured_image_url }}"
                         alt="{{ $post->title }}"
                         class="w-full h-64 sm:h-80 lg:h-96 object-cover rounded-2xl shadow-lg">
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-8">
                <!-- Content -->
                <div class="prose prose-lg dark:prose-invert max-w-none" style="font-size: 1.125rem;">
                    <style>
                        .prose-lg p { margin-bottom: 2rem !important; }
                        .prose-lg h1 { font-size: 2.5rem !important; font-weight: 800 !important; margin-top: 3rem !important; margin-bottom: 2rem !important; line-height: 1.1 !important; }
                        .prose-lg h2 { font-size: 2rem !important; font-weight: 700 !important; margin-top: 2.5rem !important; margin-bottom: 1.5rem !important; line-height: 1.15 !important; }
                        .prose-lg h3 { font-size: 1.5rem !important; font-weight: 600 !important; margin-top: 2rem !important; margin-bottom: 1rem !important; }
                        .prose-lg h4 { font-size: 1.25rem !important; font-weight: 600 !important; margin-top: 1.5rem !important; margin-bottom: 0.75rem !important; }
                        .prose-lg ol, .prose-lg ul { margin-bottom: 2rem !important; }
                        .prose-lg li { margin-bottom: 0.5rem !important; }
                        .prose-lg ul { list-style-type: disc !important; padding-left: 2rem !important; }
                        .prose-lg ul li { display: list-item !important; }
                        .dark .prose-lg { color: #e5e7eb; }
                        .dark .prose-lg h1, .dark .prose-lg h2, .dark .prose-lg h3, .dark .prose-lg h4 { color: #fff; }
                        .dark .prose-lg a { color: #a78bfa; }
                        .dark .prose-lg strong { color: #fff; }
                    </style>
                    {!! \App\Utils\MarkdownUtils::sanitizeHtml($post->content) !!}
                </div>
                <!-- Related Posts -->
                @if($relatedPosts->count() > 0)
                    <div class="mt-16 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">{{ __('messages.related_posts') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @foreach($relatedPosts as $relatedPost)
                                <article class="group">
                                    @if($relatedPost->featured_image_url)
                                        <div class="mb-4">
                                            <img src="{{ $relatedPost->featured_image_url }}"
                                                 alt="{{ $relatedPost->title }}"
                                                 class="w-full h-32 object-cover rounded-xl">
                                        </div>
                                    @endif
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors mb-2">
                                        <a href="{{ route('blog.show', $relatedPost->slug) }}">
                                            {{ $relatedPost->title }}
                                        </a>
                                    </h3>
                                    <div class="flex items-center gap-x-4 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        <time datetime="{{ $relatedPost->published_at->toISOString() }}">
                                            {{ $relatedPost->formatted_published_at }}
                                        </time>
                                        <span>{{ $relatedPost->reading_time }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $relatedPost->excerpt }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
                <!-- Back to Blog -->
                <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('blog.index') }}"
                       class="inline-flex items-center text-violet-600 dark:text-violet-400 hover:text-violet-800 dark:hover:text-violet-300 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        {{ __('messages.back_to_blog') }}
                    </a>
                </div>
            </div>
        </article>
    </div>

</x-marketing-layout>
