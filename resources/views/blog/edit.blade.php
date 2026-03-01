<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'blog'])

    <div class="mb-6"></div>

    <div class="px-4 sm:px-6 lg:px-8 pt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.edit_blog_post') }}</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                    {{ __('messages.edit_blog_post_description') }}
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('blog.admin.index') }}"
                   class="block rounded-md bg-gray-600 dark:bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back_to_posts') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('blog.update', $blogPost->encodeId()) }}" class="mt-8 space-y-8">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 shadow ring-1 ring-black/5 dark:ring-white/10 sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Title -->
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.title') }} *</label>
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
                            <label for="content" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.content') }} *</label>
                            <div class="mt-2">
                                <textarea name="content" id="content" rows="20" required dir="auto"
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('content', $blogPost->content) }}</textarea>
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
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('excerpt', $blogPost->excerpt) }}</textarea>
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
                                <input type="text" name="tags" id="tags" value="{{ old('tags', $blogPost->tags ? implode(', ', $blogPost->tags) : '') }}"
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
                            @if($blogPost->featured_image_url)
                                <div class="mt-2 mb-4">
                                    <img src="{{ $blogPost->featured_image_url }}" alt="{{ __('messages.current_image') }}" class="w-32 h-32 object-cover rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.current_image') }}</p>
                                </div>
                            @endif
                            <div class="mt-2">
                                <select name="featured_image" id="featured_image"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    <option value="">{{ __('messages.no_featured_image') }}</option>
                                    @foreach(\App\Models\BlogPost::getAvailableHeaderImages(! $blogPost->exists) as $image => $description)
                                        <option value="{{ $image }}" {{ old('featured_image', $blogPost->featured_image) == $image ? 'selected' : '' }}>
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
                                <input type="datetime-local" name="published_at" id="published_at"
                                       value="{{ old('published_at', $blogPost->published_at ? $blogPost->published_at->format('Y-m-d\TH:i') : '') }}"
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
                                <input type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $blogPost->is_published) ? 'checked' : '' }}
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
                                <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $blogPost->meta_title) }}" maxlength="60"
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
                                          class="block w-full rounded-md border-0 py-1.5 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('meta_description', $blogPost->meta_description) }}</textarea>
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
                   class="js-cancel-btn rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    {{ __('messages.cancel') }}
                </a>
                <x-brand-button type="submit" class="text-sm px-3 py-2">
                    {{ __('messages.update_post') }}
                </x-brand-button>
            </div>
        </form>
    </div>
</x-app-admin-layout>
