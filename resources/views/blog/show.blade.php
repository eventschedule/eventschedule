<x-app-layout :title="$post->meta_title . ' | Event Schedule'">
    <x-slot name="meta">
        <meta name="description" content="{{ $post->meta_description }}">
        <meta property="og:title" content="{{ $post->meta_title }}">
        <meta property="og:description" content="{{ $post->meta_description }}">
        <meta property="og:image" content="{{ $post->featured_image_url ?: config('app.url') . '/images/background.jpg' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta property="og:type" content="article">
        @if($post->published_at)
            <meta property="article:published_time" content="{{ $post->published_at->toISOString() }}">
        @endif
        <meta property="article:author" content="{{ $post->author_name }}">
        @if($post->tags)
            @foreach($post->tags as $tag)
                <meta property="article:tag" content="{{ $tag }}">
            @endforeach
        @endif
        <meta name="twitter:title" content="{{ $post->meta_title }}">
        <meta name="twitter:description" content="{{ $post->meta_description }}">
        <meta name="twitter:image" content="{{ $post->featured_image_url ?: config('app.url') . '/images/background.jpg' }}">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="canonical" href="{{ url()->current() }}">
        
        <!-- Structured Data -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BlogPosting",
            "headline": "{{ $post->title }}",
            "description": "{{ $post->meta_description }}",
            "image": "{{ $post->featured_image_url ?: config('app.url') . '/images/background.jpg' }}",
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
    </x-slot>

    <div class="bg-white">
        <article class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-12">
            <!-- Breadcrumb -->
            <nav class="mb-8" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('blog.index') }}" class="hover:text-gray-700">Blog</a></li>
                    <li>
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </li>
                    <li class="text-gray-900">{{ $post->title }}</li>
                </ol>
            </nav>

            <!-- Header -->
            <header class="mb-8">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl mb-4">
                    {{ $post->title }}
                </h1>
                
                <div class="flex items-center gap-x-4 text-sm text-gray-500 mb-6">
                    @if($post->published_at)
                        <time datetime="{{ $post->published_at->toISOString() }}">
                            {{ $post->formatted_published_at }}
                        </time>
                    @endif
                    <span>{{ $post->reading_time }}</span>
                    <span>{{ $post->view_count }} views</span>
                </div>

                @if($post->featured_image_url)
                    <div class="mb-8">
                        <img src="{{ $post->featured_image_url }}" 
                             alt="{{ $post->title }}" 
                             class="w-full h-64 md:h-96 object-cover rounded-lg">
                    </div>
                @endif

                @if($post->excerpt)
                    <p class="text-xl text-gray-600 leading-relaxed mb-6">
                        {{ $post->excerpt }}
                    </p>
                @endif

                @if($post->tags)
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                               class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                #{{ $tag }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </header>

            <!-- Content -->
            <div class="prose prose-lg max-w-none">
                {!! $post->content !!}
            </div>

            <!-- Author -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-lg">
                                {{ substr($post->author_name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">{{ $post->author_name }}</p>
                        <p class="text-sm text-gray-500">Event Schedule Team</p>
                    </div>
                </div>
            </div>

            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
                <div class="mt-16 pt-8 border-t border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $relatedPost)
                            <article class="group">
                                @if($relatedPost->featured_image_url)
                                    <div class="mb-4">
                                        <img src="{{ $relatedPost->featured_image_url }}" 
                                             alt="{{ $relatedPost->title }}" 
                                             class="w-full h-32 object-cover rounded-lg">
                                    </div>
                                @endif
                                
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-600 mb-2">
                                    <a href="{{ route('blog.show', $relatedPost->slug) }}">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h3>
                                
                                <div class="flex items-center gap-x-4 text-xs text-gray-500 mb-2">
                                    <time datetime="{{ $relatedPost->published_at->toISOString() }}">
                                        {{ $relatedPost->formatted_published_at }}
                                    </time>
                                    <span>{{ $relatedPost->reading_time }}</span>
                                </div>
                                
                                <p class="text-sm text-gray-600 line-clamp-2">
                                    {{ $relatedPost->excerpt }}
                                </p>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Back to Blog -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <a href="{{ route('blog.index') }}" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Blog
                </a>
            </div>
        </article>
    </div>
</x-app-layout> 