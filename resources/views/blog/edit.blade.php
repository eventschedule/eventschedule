<x-app-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Edit Blog Post</h1>
                <p class="mt-2 text-sm text-gray-700">
                    Update your blog post. Use the rich text editor for content and fill in SEO details.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.admin.index') }}" 
                   class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Back to Posts
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('blog.update', $blogPost) }}" class="mt-8 space-y-8">
            @csrf
            @method('PUT')
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Title *</label>
                            <div class="mt-2">
                                <input type="text" name="title" id="title" value="{{ old('title', $blogPost->title) }}" required
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="sm:col-span-2">
                            <label for="content" class="block text-sm font-medium leading-6 text-gray-900">Content *</label>
                            <div class="mt-2">
                                <textarea name="content" id="content" rows="20" required
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('content', $blogPost->content) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Use HTML tags for formatting. You can use &lt;h1&gt;, &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, etc.</p>
                            @error('content')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Excerpt -->
                        <div class="sm:col-span-2">
                            <label for="excerpt" class="block text-sm font-medium leading-6 text-gray-900">Excerpt</label>
                            <div class="mt-2">
                                <textarea name="excerpt" id="excerpt" rows="3" maxlength="500"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Leave empty to auto-generate from content (max 500 characters)</p>
                            @error('excerpt')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="sm:col-span-2">
                            <label for="tags" class="block text-sm font-medium leading-6 text-gray-900">Tags</label>
                            <div class="mt-2">
                                <input type="text" name="tags" id="tags" value="{{ old('tags', $blogPost->tags ? implode(', ', $blogPost->tags) : '') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                       placeholder="tag1, tag2, tag3">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Comma-separated tags</p>
                            @error('tags')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Featured Image -->
                        <div class="sm:col-span-2">
                            <label for="featured_image" class="block text-sm font-medium leading-6 text-gray-900">Featured Image</label>
                            @if($blogPost->featured_image_url)
                                <div class="mt-2 mb-4">
                                    <img src="{{ $blogPost->featured_image_url }}" alt="Current featured image" class="w-32 h-32 object-cover rounded-lg">
                                    <p class="mt-1 text-sm text-gray-500">Current image</p>
                                </div>
                            @endif
                            <div class="mt-2">
                                <select name="featured_image" id="featured_image"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option value="">No featured image</option>
                                    @foreach(\App\Models\BlogPost::getAvailableHeaderImages() as $image => $description)
                                        <option value="{{ $image }}" {{ old('featured_image', $blogPost->featured_image) == $image ? 'selected' : '' }}>
                                            {{ $description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Select a header image that best represents your blog post</p>
                            @error('featured_image')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Author Name -->
                        <div>
                            <label for="author_name" class="block text-sm font-medium leading-6 text-gray-900">Author Name</label>
                            <div class="mt-2">
                                <input type="text" name="author_name" id="author_name" value="{{ old('author_name', $blogPost->author_name) }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('author_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium leading-6 text-gray-900">Publish Date</label>
                            <div class="mt-2">
                                <input type="datetime-local" name="published_at" id="published_at" 
                                       value="{{ old('published_at', $blogPost->published_at ? $blogPost->published_at->format('Y-m-d\TH:i') : '') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Leave empty to publish immediately</p>
                            @error('published_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Published -->
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $blogPost->is_published) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600">
                                <label for="is_published" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                                    Publish this post
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Uncheck to save as draft</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">SEO Settings</h3>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Meta Title -->
                        <div class="sm:col-span-2">
                            <label for="meta_title" class="block text-sm font-medium leading-6 text-gray-900">Meta Title</label>
                            <div class="mt-2">
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $blogPost->meta_title) }}" maxlength="60"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Leave empty to use the post title (max 60 characters)</p>
                            @error('meta_title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="sm:col-span-2">
                            <label for="meta_description" class="block text-sm font-medium leading-6 text-gray-900">Meta Description</label>
                            <div class="mt-2">
                                <textarea name="meta_description" id="meta_description" rows="3" maxlength="160"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('meta_description', $blogPost->meta_description) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Leave empty to use the excerpt (max 160 characters)</p>
                            @error('meta_description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('blog.admin.index') }}" 
                   class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    Update Post
                </button>
            </div>
        </form>
    </div>
</x-app-admin-layout> 