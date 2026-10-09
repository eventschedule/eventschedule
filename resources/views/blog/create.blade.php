<x-app-admin-layout>
    @php
        $hasAi = config('services.google.gemini_key') || config('services.openai.api_key');
        $field = 'block w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $help = 'mt-1 text-sm text-gray-500 dark:text-gray-400';
    @endphp

    @include('admin.partials._navigation', ['active' => 'blog'])

    {{-- A new post. The form is always here: only the AI draft needs a key, and without one the
         card above the form says how to add it. The whole page used to be that card. --}}
    <div class="page-shell">
        <div class="page-head">
            <div class="min-w-0">
                <a href="{{ route('blog.admin.index') }}" class="page-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                    <span>{{ __('messages.blog_posts') }}</span>
                </a>
                <p class="page-lead">{{ __('messages.blog_create_lead') }}</p>
            </div>
        </div>

        <div class="page-stack">
            @include('blog.admin._writer', ['writerTopic' => '', 'writerExcept' => null])

            <form method="POST" action="{{ route('blog.store') }}" class="page-stack">
                @csrf
                {{-- What a generated post knows about itself: the search it answers and the
                     questions it ends on (its FAQPage data). --}}
                <input type="hidden" name="primary_query" id="primary_query" value="{{ old('primary_query') }}">
                <input type="hidden" name="faq" id="faq" value="{{ old('faq') }}">

                <x-page-card>
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="title" :value="__('messages.title').' *'" />
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required dir="auto" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('messages.content').' *'" />
                            <textarea name="content" id="content" rows="20" required dir="auto" class="{{ $field }}">{{ old('content') }}</textarea>
                            <p class="{{ $help }}">{!! __('messages.html_formatting_help') !!}</p>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="excerpt" :value="__('messages.excerpt')" />
                            <textarea name="excerpt" id="excerpt" rows="3" maxlength="500" dir="auto" class="{{ $field }}">{{ old('excerpt') }}</textarea>
                            <p class="{{ $help }}">{{ __('messages.excerpt_help') }}</p>
                            <x-input-error :messages="$errors->get('excerpt')" class="mt-2" />
                        </div>

                        <div class="max-w-xs">
                            <x-input-label for="category" :value="__('messages.blog_section')" />
                            <select name="category" id="category" class="{{ $field }}">
                                @foreach(\App\Models\BlogPost::CATEGORIES as $key => $section)
                                    <option value="{{ $key }}" {{ old('category', 'planning') == $key ? 'selected' : '' }}>{{ $section['name'] }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tags" :value="__('messages.tags')" />
                            <input type="text" name="tags" id="tags" value="{{ old('tags') }}" dir="auto" placeholder="{{ __('messages.tags_placeholder') }}" class="{{ $field }}">
                            <p class="{{ $help }}">{{ __('messages.comma_separated_tags') }}</p>
                            <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="featured_image" :value="__('messages.featured_image')" />
                            <select name="featured_image" id="featured_image" class="{{ $field }}">
                                <option value="">{{ __('messages.no_featured_image') }}</option>
                                @foreach(\App\Models\BlogPost::getAvailableHeaderImages() as $image => $description)
                                    <option value="{{ $image }}" {{ old('featured_image') == $image ? 'selected' : '' }}>{{ $description }}</option>
                                @endforeach
                            </select>
                            <p class="{{ $help }}">{{ __('messages.featured_image_help') }}</p>
                            <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
                        </div>

                        <div class="max-w-xs">
                            <x-input-label for="published_at" :value="__('messages.publish_date')" />
                            <input type="text" name="published_at" id="published_at" value="{{ old('published_at') }}" autocomplete="off" class="{{ $field }}">
                            <p class="{{ $help }}">{{ __('messages.publish_date_help') }}</p>
                            <x-input-error :messages="$errors->get('published_at')" class="mt-2" />
                        </div>

                        <div>
                            <x-toggle name="is_published" :label="__('messages.publish_this_post')" :checked="(bool) old('is_published')" />
                        </div>
                    </div>
                </x-page-card>

                <x-page-card :title="__('messages.seo_settings')">
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="meta_title" :value="__('messages.meta_title')" />
                            <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" maxlength="60" dir="auto" class="{{ $field }}">
                            <p class="{{ $help }}">{{ __('messages.meta_title_help') }}</p>
                            <x-input-error :messages="$errors->get('meta_title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="meta_description" :value="__('messages.meta_description')" />
                            <textarea name="meta_description" id="meta_description" rows="3" maxlength="160" dir="auto" class="{{ $field }}">{{ old('meta_description') }}</textarea>
                            <p class="{{ $help }}">{{ __('messages.meta_description_help') }}</p>
                            <x-input-error :messages="$errors->get('meta_description')" class="mt-2" />
                        </div>
                    </div>
                </x-page-card>

                <div class="page-form-actions">
                    <x-secondary-link :href="route('blog.admin.index')">{{ __('messages.cancel') }}</x-secondary-link>
                    <x-brand-button type="submit">{{ __('messages.create_post') }}</x-brand-button>
                </div>
            </form>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            // The publish date, in the format the server reads. It was a native datetime box.
            flatpickr('#published_at', {
                allowInput: true,
                enableTime: true,
                time_24hr: true,
                altInput: true,
                altFormat: 'M j, Y H:i',
                dateFormat: 'Y-m-d H:i',
            });
        });
    </script>
</x-app-admin-layout>
