<x-app-layout :title="'Blog | Event Schedule'">
    <x-slot name="meta">
        <meta name="description" content="Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.">
        <meta property="og:title" content="Blog | Event Schedule">
        <meta property="og:description" content="Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.">
        <meta property="og:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Blog | Event Schedule">
        <meta name="twitter:description" content="Read the latest news, tips, and insights about event scheduling and ticketing from the Event Schedule team.">
        <meta name="twitter:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="canonical" href="{{ url()->current() }}">
    </x-slot>

    <div class="bg-gray-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="w-full flex flex-col sm:flex-row items-center gap-4 sm:gap-8 mb-4 text-center sm:text-left">
                <a href="https://www.eventschedule.com" target="_blank" class="hover:opacity-80 transition-opacity flex-shrink-0 flex justify-center sm:block">
                    <img class="h-12 w-auto sm:h-14 mb-4 sm:mb-0" src="{{ url('images/light_logo.png') }}" alt="EventSchedule Logo"/>
                </a>
                <div class="w-full h-px bg-gray-600 sm:w-px sm:h-14 sm:bg-gray-600"></div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-2">Blog</h1>
                    <p class="text-base sm:text-xl text-gray-300">
                        Latest news, tips, and insights about event scheduling and ticketing.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 min-h-screen">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <!-- Selected Tag Display -->
            @if(request('tag'))
                <div class="mb-6 bg-white border border-blue-200 rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">Filtered by:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                #{{ request('tag') }}
                            </span>
                        </div>
                        <a href="{{ route('blog.index') }}" 
                           class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Clear filter
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
                                <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6 transition-all duration-300 hover:shadow-lg hover:shadow-blue-100/50 hover:-translate-y-1 hover:border-blue-200 cursor-pointer">
                                    @if($post->featured_image_url)
                                        <div class="mb-4 overflow-hidden rounded-lg">
                                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105">
                                        </div>
                                    @endif
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-x-4 text-xs mb-3">
                                        <div class="flex items-center gap-x-4">
                                            @if($post->published_at)
                                                <time datetime="{{ $post->published_at->toISOString() }}" class="text-gray-500">
                                                    {{ $post->formatted_published_at }}
                                                </time>
                                            @endif
                                            <span class="text-gray-500">{{ $post->reading_time }}</span>
                                        </div>
                                        @if($post->tags)
                                            <div class="flex gap-2 mt-1 sm:mt-0">
                                                @foreach(array_slice($post->tags, 0, 3) as $tag)
                                                    <span class="text-blue-600 group-hover:text-blue-800 transition-colors duration-200">
                                                        #{{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="relative">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900 group-hover:text-blue-600 transition-colors duration-200 mb-3">
                                            {{ $post->title }}
                                        </h3>
                                        <p class="line-clamp-3 text-sm leading-6 text-gray-600 group-hover:text-gray-700 transition-colors duration-200">
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
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts found</h3>
                            <p class="text-gray-600">Check back soon for new content!</p>
                        </div>
                    @endif
                </div>
                <!-- Sidebar -->
                <div class="space-y-6">
                    @if($allTags->count() > 0)
                        <div class="bg-white border border-blue-100 rounded-2xl shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tags</h3>
                            <div class="flex flex-wrap gap-2" id="tags-container">
                                @foreach($allTags->take(20) as $tag)
                                    <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                                       class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-800 hover:bg-blue-100 transition-colors {{ request('tag') == $tag ? 'bg-blue-200 text-blue-900' : '' }}">
                                        #{{ $tag }}
                                    </a>
                                @endforeach
                                @if($allTags->count() > 20)
                                    <div id="hidden-tags" class="hidden flex flex-wrap gap-2">
                                        @foreach($allTags->slice(20) as $tag)
                                            <a href="{{ route('blog.index', ['tag' => $tag]) }}" 
                                               class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-800 hover:bg-blue-100 transition-colors {{ request('tag') == $tag ? 'bg-blue-200 text-blue-900' : '' }}">
                                                #{{ $tag }}
                                            </a>
                                        @endforeach
                                    </div>
                                    <button id="show-more-tags" 
                                            class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors mt-2">
                                        Show more tags
                                    </button>
                                    <button id="show-less-tags" 
                                            class="hidden inline-block px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors mt-2">
                                        Show less
                                    </button>
                                @endif
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
                            Stay updated with the latest tips, news, and insights about event scheduling and ticketing. 
                            Our team shares valuable information to help you make the most of your events.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-[#151B26]">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-8 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            <!-- Per the AAL license, please do not remove the link to Event Schedule -->
            {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
                •
            {!! __('messages.supported_by', ['link' => '<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>']) !!}
        </p>
      </div>
    </footer>


    <script>
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
</x-app-layout> 