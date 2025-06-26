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

    <div class="bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:mx-0">
                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-white">Blog</h1>
                <p class="mt-4 text-lg leading-8 text-gray-300">
                    Latest news, tips, and insights about event scheduling and management.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 min-h-screen py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mt-0 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-12 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <!-- Main content -->
                <div class="lg:col-span-2 space-y-8">
                    @if($posts->count() > 0)
                        @foreach($posts as $post)
                            <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-8 flex flex-col h-full">
                                @if($post->featured_image_url)
                                    <div class="mb-4">
                                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover rounded-lg">
                                    </div>
                                @endif
                                <div class="flex items-center gap-x-4 text-xs">
                                    @if($post->published_at)
                                        <time datetime="{{ $post->published_at->toISOString() }}" class="text-gray-500">
                                            {{ $post->formatted_published_at }}
                                        </time>
                                    @endif
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
                            </div>
                        @endforeach
                        <!-- Pagination -->
                        <div class="mt-12">
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
                <div class="space-y-8">
                    @if($allTags->count() > 0)
                        <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($allTags as $tag)
                                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-800 hover:bg-blue-100 transition-colors">
                                        #{{ $tag }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($archives->count() > 0)
                        <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6">
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
                    <div class="bg-blue-100 border border-blue-200 rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">About Our Blog</h3>
                        <p class="text-sm text-blue-900">
                            Stay updated with the latest tips, news, and insights about event scheduling and management. 
                            Our team shares valuable information to help you make the most of your events.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 