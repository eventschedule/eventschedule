<x-app-admin-layout>
    {{-- Admin Navigation Tabs --}}
    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <div class="flex justify-between items-center">
            <nav class="-mb-px flex space-x-8">
                <a href="{{ route('admin.dashboard') }}"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                    Dashboard
                </a>
                @if (config('app.hosted') || config('app.is_nexus'))
                <a href="{{ route('admin.plans') }}"
                    class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                    Plans
                </a>
                @endif
                <a href="{{ route('blog.admin.index') }}"
                    class="whitespace-nowrap border-b-2 border-[#4E81FA] px-1 pb-4 text-base font-medium text-[#4E81FA]">
                    Blog
                </a>
            </nav>
            <button onclick="window.location.reload()" class="mb-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 pt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">Edit Blog Post</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                    Update your blog post. Use the rich text editor for content and fill in SEO details.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.admin.index') }}"
                   class="block rounded-md bg-gray-600 dark:bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 dark:hover:bg-gray-600 transition-colors">
                    Back to Posts
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('blog.update', $blogPost) }}" class="mt-8 space-y-8">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 shadow ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Title *</label>
                            <div class="mt-2">
                                <input type="text" name="title" id="title" value="{{ old('title', $blogPost->title) }}" required
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('title')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="sm:col-span-2">
                            <label for="content" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Content *</label>
                            <div class="mt-2">
                                <textarea name="content" id="content" rows="20" required
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('content', $blogPost->content) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Use HTML tags for formatting. You can use &lt;h1&gt;, &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, etc.</p>
                            @error('content')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Excerpt -->
                        <div class="sm:col-span-2">
                            <label for="excerpt" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Excerpt</label>
                            <div class="mt-2">
                                <textarea name="excerpt" id="excerpt" rows="3" maxlength="500"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Leave empty to auto-generate from content (max 500 characters)</p>
                            @error('excerpt')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="sm:col-span-2">
                            <label for="tags" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Tags</label>
                            <div class="mt-2">
                                <input type="text" name="tags" id="tags" value="{{ old('tags', $blogPost->tags ? implode(', ', $blogPost->tags) : '') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                       placeholder="tag1, tag2, tag3">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Comma-separated tags</p>
                            @error('tags')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Featured Image -->
                        <div class="sm:col-span-2">
                            <label for="featured_image" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Featured Image</label>
                            @if($blogPost->featured_image_url)
                                <div class="mt-2 mb-4">
                                    <img src="{{ $blogPost->featured_image_url }}" alt="Current featured image" class="w-32 h-32 object-cover rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Current image</p>
                                </div>
                            @endif
                            <div class="mt-2">
                                <select name="featured_image" id="featured_image"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option value="">No featured image</option>
                                    @foreach(\App\Models\BlogPost::getAvailableHeaderImages(! $blogPost->exists) as $image => $description)
                                        <option value="{{ $image }}" {{ old('featured_image', $blogPost->featured_image) == $image ? 'selected' : '' }}>
                                            {{ $description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Select a header image that best represents your blog post</p>
                            @error('featured_image')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Publish Date</label>
                            <div class="mt-2">
                                <input type="datetime-local" name="published_at" id="published_at"
                                       value="{{ old('published_at', $blogPost->published_at ? $blogPost->published_at->format('Y-m-d\TH:i') : '') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Leave empty to publish immediately</p>
                            @error('published_at')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Published -->
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $blogPost->is_published) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-600 bg-white dark:bg-gray-900">
                                <label for="is_published" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    Publish this post
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Uncheck to save as draft</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-white dark:bg-gray-800 shadow ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4">SEO Settings</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Meta Title -->
                        <div class="sm:col-span-2">
                            <label for="meta_title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Meta Title</label>
                            <div class="mt-2">
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $blogPost->meta_title) }}" maxlength="60"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Leave empty to use the post title (max 60 characters)</p>
                            @error('meta_title')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="sm:col-span-2">
                            <label for="meta_description" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">Meta Description</label>
                            <div class="mt-2">
                                <textarea name="meta_description" id="meta_description" rows="3" maxlength="160"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('meta_description', $blogPost->meta_description) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Leave empty to use the excerpt (max 160 characters)</p>
                            @error('meta_description')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('blog.admin.index') }}"
                   class="rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                    Update Post
                </button>
            </div>
        </form>
    </div>
</x-app-admin-layout>
