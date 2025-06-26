<x-app-layout :title="'Blog | Event Schedule'">
    <x-slot name="meta">
        <meta name="description" content="Read the latest news, tips, and insights about event scheduling and management from the Event Schedule team.">
        <meta property="og:title" content="Blog | Event Schedule">
        <meta property="og:description" content="Read the latest news, tips, and insights about event scheduling and management from the Event Schedule team.">
        <meta property="og:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Blog | Event Schedule">
        <meta name="twitter:description" content="Read the latest news, tips, and insights about event scheduling and management from the Event Schedule team.">
        <meta name="twitter:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="canonical" href="{{ url()->current() }}">
    </x-slot>

    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Blog</h1>
                <p class="mt-2 text-lg leading-8 text-gray-600">
                    Latest news, tips, and insights about event scheduling and management.
                </p>
            </div>
            
            <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <!-- Main content -->
                <div class="lg:col-span-2">
                    @if($posts->count() > 0)
                        <div class="space-y-16">
                            @foreach($posts as $post)
                                <article class="flex max-w-xl flex-col items-start">
                                    @if($post->featured_image_url)
                                        <div class="mb-4">
                                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover rounded-lg">
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center gap-x-4 text-xs">
                                        <time datetime="{{ $post->published_at->toISOString() }}" class="text-gray-500">
                                            {{ $post->formatted_published_at }}
                                        </time>
                                        <span class="text-gray-500">{{ $post->reading_time }}</span>
                                        @if($post->tags)
                                            <div class="flex gap-2">
                                                @foreach(array_slice($post->tags, 0, 3) as $tag)
                                                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" class="text-blue-600 hover:text-blue-800">
                                                        #{{ $tag }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="group relative">
                                        <h3 class="mt-3 text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                                            <a href="{{ route('blog.show', $post->slug) }}">
                                                <span class="absolute inset-0"></span>
                                                {{ $post->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-5 line-clamp-3 text-sm leading-6 text-gray-600">
                                            {{ $post->excerpt }}
                                        </p>
                                    </div>
                                    
                                    <div class="relative mt-8 flex items-center gap-x-4">
                                        <div class="text-sm leading-6">
                                            <p class="font-semibold text-gray-900">
                                                <span class="absolute inset-0"></span>
                                                {{ $post->author_name }}
                                            </p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-16">
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts found</h3>
                            <p class="text-gray-600">Check back soon for new content!</p>
                        </div>
                    @endif
                </div>
                
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-8">
                        <!-- Tags -->
                        @if($allTags->count() > 0)
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($allTags as $tag)
                                        <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                                           class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                            #{{ $tag }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Archives -->
                        @if($archives->count() > 0)
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Archives</h3>
                                <div class="space-y-2">
                                    @foreach($archives as $archive)
                                        <a href="{{ route('blog.index', ['year' => $archive->year, 'month' => $archive->month]) }}" 
                                           class="block text-sm text-gray-600 hover:text-gray-900 transition-colors">
                                            {{ $archive->month_name }} {{ $archive->year }} ({{ $archive->count }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- About -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">About Our Blog</h3>
                            <p class="text-sm text-gray-600">
                                Stay updated with the latest tips, news, and insights about event scheduling and management. 
                                Our team shares valuable information to help you make the most of your events.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 