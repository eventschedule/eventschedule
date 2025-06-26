<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            function generateContent() {
                const topic = document.getElementById('ai_topic').value;
                
                if (!topic.trim()) {
                    alert('Please enter a topic for AI generation');
                    return;
                }
                
                // Show loading state
                const generateBtn = document.getElementById('generate_btn');
                const originalText = generateBtn.innerHTML;
                generateBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';
                generateBtn.disabled = true;
                
                fetch('{{ route("blog.generate-content") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        topic: topic
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        // Fill in the form fields
                        document.getElementById('title').value = data.title || '';
                        document.getElementById('content').value = data.content || '';
                        document.getElementById('excerpt').value = data.excerpt || '';
                        document.getElementById('tags').value = data.tags ? data.tags.join(', ') : '';
                        document.getElementById('meta_title').value = data.meta_title || '';
                        document.getElementById('meta_description').value = data.meta_description || '';
                        
                        // Show success message
                        Toastify({
                            text: "Content generated successfully!",
                            duration: 3000,
                            position: 'center',
                            style: {
                                background: '#4BB543',
                            }
                        }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to generate content. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    generateBtn.innerHTML = originalText;
                    generateBtn.disabled = false;
                });
            }
        </script>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Create Blog Post</h1>
                <p class="mt-2 text-sm text-gray-700">
                    Create a new blog post. Use AI to generate content or write it manually.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.admin.index') }}" 
                   class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Back to Posts
                </a>
            </div>
        </div>

        <!-- AI Content Generation Section -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🤖 AI Content Generation</h3>
            <p class="text-sm text-gray-600 mb-4">
                Let AI help you create engaging blog content. Enter a topic and AI will generate professional content with automatically varied lengths.
            </p>
            
            @if (! config('services.google.gemini_key'))
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-800">
                                <strong>Gemini API Key Required:</strong> Add GEMINI_API_KEY= to the .env file to use AI content generation.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="ai_topic" class="block text-sm font-medium text-gray-700">Topic *</label>
                    <input type="text" id="ai_topic" placeholder="e.g., Event Planning Tips for Beginners" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           {{ config('services.google.gemini_key') ? '' : 'disabled' }}>
                </div>
                
                <div class="flex items-end">
                    <button type="button" id="generate_btn" onclick="generateContent()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
                            {{ config('services.google.gemini_key') ? '' : 'disabled' }}>
                        Generate Content
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('blog.store') }}" class="mt-8 space-y-8">
            @csrf
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900">Title *</label>
                            <div class="mt-2">
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
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
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('content') }}</textarea>
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
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('excerpt') }}</textarea>
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
                                <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
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
                            <div class="mt-2">
                                <select name="featured_image" id="featured_image"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option value="">No featured image</option>
                                    @foreach(\App\Models\BlogPost::getAvailableHeaderImages() as $image => $description)
                                        <option value="{{ $image }}" {{ old('featured_image') == $image ? 'selected' : '' }}>
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
                                <input type="text" name="author_name" id="author_name" value="{{ old('author_name', 'Event Schedule Team') }}"
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
                                <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
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
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
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
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" maxlength="60"
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
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('meta_description') }}</textarea>
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
                    Create Post
                </button>
            </div>
        </form>
    </div>
</x-app-admin-layout> 