<x-app-admin-layout>
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
            function generateContent() {
                const topic = document.getElementById('ai_topic').value;

                if (!topic.trim()) {
                    alert('{{ __('messages.please_enter_topic') }}');
                    return;
                }

                // Show loading state
                const generateBtn = document.getElementById('generate_btn');
                const originalText = generateBtn.innerHTML;
                generateBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{ __('messages.generating') }}';
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
                        alert('{{ __('messages.error') }}: ' + data.error);
                    } else {
                        // Fill in the form fields
                        document.getElementById('title').value = data.title || '';
                        document.getElementById('content').value = data.content || '';
                        document.getElementById('excerpt').value = data.excerpt || '';
                        document.getElementById('tags').value = data.tags ? data.tags.join(', ') : '';
                        document.getElementById('meta_title').value = data.meta_title || '';
                        document.getElementById('meta_description').value = data.meta_description || '';

                        // Set featured image if provided
                        if (data.featured_image) {
                            const featuredImageSelect = document.getElementById('featured_image');
                            for (let i = 0; i < featuredImageSelect.options.length; i++) {
                                if (featuredImageSelect.options[i].value === data.featured_image) {
                                    featuredImageSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        }

                        // Show success message
                        Toastify({
                            text: "{{ __('messages.content_generated') }}",
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
                    alert('{{ __('messages.failed_to_generate_content') }}');
                })
                .finally(() => {
                    // Reset button state
                    generateBtn.innerHTML = originalText;
                    generateBtn.disabled = false;
                });
            }
        </script>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'blog'])

    <div class="mb-6"></div>

    <div class="px-4 sm:px-6 lg:px-8 pt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_blog_post') }}</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                    {{ __('messages.create_blog_post_description') }}
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.admin.index') }}"
                   class="block rounded-md bg-gray-600 dark:bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back_to_posts') }}
                </a>
            </div>
        </div>

        @if (! config('services.google.gemini_key'))
        <div class="mt-8">
            <x-gemini-setup-guide />
        </div>
        @else
        <!-- AI Content Generation Section -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                {{ __('messages.ai_content_generation') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('messages.ai_content_generation_description') }}
            </p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_auto]">
                <div>
                    <label for="ai_topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.topic') }} *</label>
                    <textarea id="ai_topic" placeholder="e.g., Event Planning Tips for Beginners" rows="3" dir="auto"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                </div>

                <div class="flex items-end">
                    <button type="button" id="generate_btn" onclick="generateContent()"
                            class="w-48 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ __('messages.generate_content') }}
                    </button>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('blog.store') }}" class="mt-8 space-y-8">
            @csrf

            <div class="bg-white dark:bg-gray-800 shadow ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.title') }} *</label>
                            <div class="mt-2">
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('title')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="sm:col-span-2">
                            <label for="content" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.content') }} *</label>
                            <div class="mt-2">
                                <textarea name="content" id="content" rows="20" required dir="auto"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('content') }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{!! __('messages.html_formatting_help') !!}</p>
                            @error('content')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Excerpt -->
                        <div class="sm:col-span-2">
                            <label for="excerpt" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.excerpt') }}</label>
                            <div class="mt-2">
                                <textarea name="excerpt" id="excerpt" rows="3" maxlength="500" dir="auto"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('excerpt') }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.excerpt_help') }}</p>
                            @error('excerpt')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div class="sm:col-span-2">
                            <label for="tags" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.tags') }}</label>
                            <div class="mt-2">
                                <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                                       placeholder="{{ __('messages.tags_placeholder') }}">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.comma_separated_tags') }}</p>
                            @error('tags')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Featured Image -->
                        <div class="sm:col-span-2">
                            <label for="featured_image" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.featured_image') }}</label>
                            <div class="mt-2">
                                <select name="featured_image" id="featured_image"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option value="">{{ __('messages.no_featured_image') }}</option>
                                    @foreach(\App\Models\BlogPost::getAvailableHeaderImages() as $image => $description)
                                        <option value="{{ $image }}" {{ old('featured_image') == $image ? 'selected' : '' }}>
                                            {{ $description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.featured_image_help') }}</p>
                            @error('featured_image')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Published At -->
                        <div>
                            <label for="published_at" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.publish_date') }}</label>
                            <div class="mt-2">
                                <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.publish_date_help') }}</p>
                            @error('published_at')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Published -->
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-600 bg-white dark:bg-gray-900">
                                <label for="is_published" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    {{ __('messages.publish_this_post') }}
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.save_as_draft') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-white dark:bg-gray-800 shadow ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.seo_settings') }}</h3>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Meta Title -->
                        <div class="sm:col-span-2">
                            <label for="meta_title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.meta_title') }}</label>
                            <div class="mt-2">
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" maxlength="60"
                                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.meta_title_help') }}</p>
                            @error('meta_title')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="sm:col-span-2">
                            <label for="meta_description" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.meta_description') }}</label>
                            <div class="mt-2">
                                <textarea name="meta_description" id="meta_description" rows="3" maxlength="160" dir="auto"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('meta_description') }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.meta_description_help') }}</p>
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
                    {{ __('messages.cancel') }}
                </a>
                <x-brand-button type="submit" class="text-sm px-3 py-2">
                    {{ __('messages.create_post') }}
                </x-brand-button>
            </div>
        </form>
        @endif
    </div>
</x-app-admin-layout>
