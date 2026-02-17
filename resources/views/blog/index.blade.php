<x-marketing-layout>
    @if(request('tag'))
        <x-slot name="title">{{ request('tag') }} - Blog | Event Schedule</x-slot>
        <x-slot name="description">Articles about {{ request('tag') }} on the Event Schedule blog.</x-slot>
    @elseif($monthLabel)
        <x-slot name="title">{{ $monthLabel }} - Blog | Event Schedule</x-slot>
        <x-slot name="description">Event Schedule blog posts from {{ $monthLabel }}.</x-slot>
    @else
        <x-slot name="title">Blog | Event Schedule</x-slot>
        <x-slot name="description">Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.</x-slot>
    @endif
    <x-slot name="breadcrumbTitle">Blog</x-slot>

    @if($posts->currentPage() > 1 || $monthLabel)
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

    <style {!! nonce_attr() !!}>
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

    </style>

    <x-slot name="structuredData">
    <!-- Blog ItemList Structured Data -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Event Schedule Blog",
        "description": "Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.",
        "url": "{{ route('blog.index') }}",
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png"
            }
        }
        @if($posts->count() > 0)
        ,"blogPost": [
            @foreach($posts as $index => $post)
            {
                "@type": "BlogPosting",
                "headline": "{{ $post->title }}",
                "description": "{{ $post->excerpt }}",
                "url": "{{ route('blog.show', $post->slug) }}",
                "datePublished": "{{ $post->published_at ? $post->published_at->toISOString() : '' }}",
                "author": {
                    "@type": "Person",
                    "name": "{{ $post->author_name }}"
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
        @endif
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-[#0a0a0f]">
        <!-- Animated gradient orbs - larger and more prominent -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-gradient-to-b from-violet-600/30 via-indigo-600/20 to-transparent rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute -bottom-32 -left-32 w-[500px] h-[500px] bg-gradient-to-r from-fuchsia-600/25 to-pink-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1s;"></div>
            <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] bg-gradient-to-l from-blue-600/20 to-cyan-600/15 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:60px_60px]"></div>

        <div class="relative z-10 py-24 sm:py-32 lg:py-40">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass mb-8">
                    <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    <span class="text-sm text-gray-300">{{ __('messages.news_tips_insights') }}</span>
                </div>

                <!-- Main headline -->
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight mb-6">
                    <span class="text-white">The Event Schedule</span><br>
                    <span class="text-gradient">{{ __('messages.blog') }}</span>
                </h1>

                <!-- Subheadline -->
                <p class="text-xl sm:text-2xl text-gray-400 max-w-2xl mx-auto mb-10">
                    {{ __('messages.blog_hero_subtitle') }}
                </p>

                <!-- Stats or social proof -->
                <div class="flex flex-wrap items-center justify-center gap-8 text-sm text-gray-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-violet-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                        </svg>
                        <span class="text-gray-400">{{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-fuchsia-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-400">{{ $allTags->count() }} {{ __('messages.topics') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom fade -->
        <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-gray-50 dark:from-gray-900 to-transparent"></div>
    </section>

    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <!-- Selected Tag Display -->
            @if(request('tag'))
                <div class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.filtered_by') }}</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-violet-100 dark:bg-violet-900/50 text-violet-800 dark:text-violet-300">
                                #{{ request('tag') }}
                            </span>
                        </div>
                        <a href="{{ route('blog.index') }}"
                           class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('messages.clear_filter') }}
                        </a>
                    </div>
                </div>
            @endif

            <div class="mx-auto mt-0 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <!-- Main content -->
                <div class="lg:col-span-2 space-y-6">
                    @if($posts->count() > 0)
                        @foreach($posts as $post)
                            <a href="{{ route('blog.show', $post->slug) }}" class="block group">
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6 transition-all duration-300 hover:shadow-lg hover:shadow-violet-100/50 dark:hover:shadow-violet-900/20 hover:-translate-y-1 hover:border-violet-200 dark:hover:border-violet-700 cursor-pointer">
                                    @if($post->featured_image_url)
                                        <div class="mb-4 overflow-hidden rounded-xl">
                                            <picture>
                                                <source srcset="{{ webp_path($post->featured_image_url) }}" type="image/webp">
                                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105">
                                            </picture>
                                        </div>
                                    @endif
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-x-4 text-xs mb-3">
                                        <div class="flex items-center gap-x-4">
                                            @if($post->published_at)
                                                <time datetime="{{ $post->published_at->toISOString() }}" class="text-gray-500 dark:text-gray-400">
                                                    {{ $post->formatted_published_at }}
                                                </time>
                                            @endif
                                            <span class="text-gray-500 dark:text-gray-400">{{ $post->reading_time }}</span>
                                        </div>
                                        @if($post->tags)
                                            <div class="flex gap-2 mt-1 sm:mt-0">
                                                @foreach(array_slice($post->tags, 0, 3) as $tag)
                                                    <span class="text-violet-600 dark:text-violet-400 group-hover:text-violet-800 dark:group-hover:text-violet-300 transition-colors duration-200">
                                                        #{{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors duration-200 mb-3">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="line-clamp-3 text-sm leading-6 text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors duration-200">
                                            {{ $post->excerpt }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        <!-- Pagination -->
                        <div class="mt-8">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ __('messages.no_posts_found') }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ __('messages.check_back_soon') }}</p>
                        </div>
                    @endif
                </div>
                <!-- Sidebar -->
                <div class="space-y-6">
                    @if($allTags->count() > 0)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('messages.tags') }}</h3>
                            <div class="flex flex-wrap gap-2" id="tags-container">
                                @foreach($allTags->take(20) as $tag)
                                    <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                                       class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 hover:bg-violet-100 dark:hover:bg-violet-900/50 hover:text-violet-800 dark:hover:text-violet-300 transition-colors {{ request('tag') == $tag ? 'bg-violet-200 dark:bg-violet-900 text-violet-900 dark:text-violet-200' : '' }}">
                                        #{{ $tag }}
                                    </a>
                                @endforeach
                                @if($allTags->count() > 20)
                                    <div id="hidden-tags" class="hidden flex flex-wrap gap-2">
                                        @foreach($allTags->slice(20) as $tag)
                                            <a href="{{ route('blog.index', ['tag' => $tag]) }}"
                                               class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 hover:bg-violet-100 dark:hover:bg-violet-900/50 hover:text-violet-800 dark:hover:text-violet-300 transition-colors {{ request('tag') == $tag ? 'bg-violet-200 dark:bg-violet-900 text-violet-900 dark:text-violet-200' : '' }}">
                                                #{{ $tag }}
                                            </a>
                                        @endforeach
                                    </div>
                                    <button id="show-more-tags"
                                            class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors mt-2">
                                        {{ __('messages.show_more_tags') }}
                                    </button>
                                    <button id="show-less-tags"
                                            class="hidden inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors mt-2">
                                        {{ __('messages.show_less') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!-- About -->
                    <div class="bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900/50 dark:to-indigo-900/50 border border-violet-200 dark:border-violet-800 rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-violet-900 dark:text-violet-100 mb-2">{{ __('messages.about_our_blog') }}</h3>
                        <p class="text-sm text-violet-800 dark:text-violet-200">
                            {{ __('messages.about_our_blog_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            const showMoreBtn = document.getElementById('show-more-tags');
            const showLessBtn = document.getElementById('show-less-tags');
            const hiddenTags = document.getElementById('hidden-tags');

            if (showMoreBtn && showLessBtn && hiddenTags) {
                showMoreBtn.addEventListener('click', function() {
                    hiddenTags.classList.remove('hidden');
                    showMoreBtn.classList.add('hidden');
                    showLessBtn.classList.remove('hidden');
                });

                showLessBtn.addEventListener('click', function() {
                    hiddenTags.classList.add('hidden');
                    showLessBtn.classList.add('hidden');
                    showMoreBtn.classList.remove('hidden');
                });
            }
        });
    </script>
</x-marketing-layout>
