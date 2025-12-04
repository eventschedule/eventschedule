<x-app-admin-layout>
    @php
        $homeSettings = $homeSettings ?? [];
        if ($homeSettings instanceof \Illuminate\Support\Collection) {
            $homeSettings = $homeSettings->toArray();
        }

        $layoutOptions = $layoutOptions ?? [];
        $selectedLayout = old('home_layout', $homeSettings['layout'] ?? \App\Support\HomePageSettings::LAYOUT_FULL);
        $selectedHeroAlignment = old('home_hero_alignment', $homeSettings['hero_alignment'] ?? \App\Support\HomePageSettings::HERO_ALIGN_CENTER);
        $showDefaultHeroText = filter_var(old('home_hero_show_default_text', $homeSettings['hero_show_default_text'] ?? true), FILTER_VALIDATE_BOOLEAN);

        $initialHeroImage = $initialHeroImage ?? ['asset_id' => null, 'variant_id' => null, 'url' => null];
        $initialHeroAssetId = old('home_hero_media_asset_id', $initialHeroImage['asset_id'] ?? null);
        $initialHeroVariantId = old('home_hero_media_variant_id', $initialHeroImage['variant_id'] ?? null);
        $initialHeroImageUrl = $initialHeroImage['url'] ?? null;

        $initialAsideImage = $initialAsideImage ?? ['asset_id' => null, 'variant_id' => null, 'url' => null];
        $initialAssetId = old('home_aside_media_asset_id', $initialAsideImage['asset_id'] ?? null);
        $initialVariantId = old('home_aside_media_variant_id', $initialAsideImage['variant_id'] ?? null);
        $initialImageUrl = $initialAsideImage['url'] ?? null;
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.home_settings_heading'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.home_settings_heading') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.home_settings_description') }}
                        </p>
                    </div>

                    <section>

                        @if (session('status') === 'home-settings-updated')
                            <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/40 dark:text-green-200">
                                {{ __('messages.home_settings_saved') }}
                            </div>
                        @endif

                        <form method="post" action="{{ route('settings.home.update') }}" class="mt-6 space-y-8">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label :value="__('messages.home_layout_label')" />
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.home_layout_help') }}
                                </p>
                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                    @foreach($layoutOptions as $value => $option)
                                        <label class="relative block">
                                            <input type="radio" name="home_layout" value="{{ $value }}" class="peer sr-only" @checked($selectedLayout === $value)>
                                            <div class="h-full rounded-lg border border-gray-200 bg-white p-4 text-left shadow-sm transition peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[#4E81FA] peer-checked:border-[#4E81FA] peer-checked:ring-2 peer-checked:ring-[#4E81FA] dark:border-gray-700 dark:bg-gray-900/60 dark:peer-checked:border-[#4E81FA]">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $option['label'] ?? $value }}</p>
                                                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $option['description'] ?? '' }}</p>
                                                    </div>
                                                    <span class="text-gray-300 dark:text-gray-600">
                                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                        </svg>
                                                    </span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('home_layout')" />
                            </div>

                            <div class="space-y-4">
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="home_hero_title" :value="__('messages.home_hero_title_label')" />
                                        <x-text-input id="home_hero_title" name="home_hero_title" type="text" class="mt-1 block w-full" :value="old('home_hero_title', $homeSettings['hero_title'] ?? '')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_title')" />
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <x-input-label for="home_hero_alignment" :value="__('messages.home_hero_alignment_label')" />
                                            <select id="home_hero_alignment" name="home_hero_alignment" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                                                @foreach(\App\Support\HomePageSettings::allowedHeroAlignments() as $alignment)
                                                    <option value="{{ $alignment }}" @selected($selectedHeroAlignment === $alignment)>
                                                        {{ __('messages.home_hero_alignment_option_' . $alignment) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">{{ __('messages.home_hero_alignment_help') }}</p>
                                            <x-input-error class="mt-2" :messages="$errors->get('home_hero_alignment')" />
                                        </div>

                                        <div>
                                            <x-input-label for="home_hero_show_default_text" :value="__('messages.home_hero_show_default_text_label')" />
                                            <label class="mt-2 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                                <input type="hidden" name="home_hero_show_default_text" value="0">
                                                <input id="home_hero_show_default_text" type="checkbox" name="home_hero_show_default_text" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]" @checked($showDefaultHeroText)>
                                                <span>
                                                    {{ __('messages.home_hero_show_default_text_help') }}
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <x-input-label :value="__('messages.home_hero_image_label')" />
                                        <x-media-picker
                                            name="home_hero_media_variant_id"
                                            asset-input-name="home_hero_media_asset_id"
                                            context="home-hero"
                                            :initial-url="$initialHeroImageUrl"
                                            :initial-asset-id="$initialHeroAssetId"
                                            :initial-variant-id="$initialHeroVariantId"
                                            label="{{ __('messages.home_hero_image_picker') }}"
                                            help="{{ __('messages.home_hero_image_help') }}"
                                        />
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_media_asset_id')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_media_variant_id')" />
                                    </div>

                                    <div>
                                        <x-input-label for="home_hero_image_alt" :value="__('messages.home_hero_image_alt_label')" />
                                        <x-text-input id="home_hero_image_alt" name="home_hero_image_alt" type="text" class="mt-1 block w-full" :value="old('home_hero_image_alt', $homeSettings['hero_image_alt'] ?? '')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_image_alt')" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="home_hero_markdown" :value="__('messages.home_hero_markdown_label')" />
                                    <textarea id="home_hero_markdown" name="home_hero_markdown" rows="6"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:border-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('home_hero_markdown', $homeSettings['hero_markdown'] ?? '') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('home_hero_markdown')" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <x-input-label for="home_hero_cta_label" :value="__('messages.home_hero_cta_label')" />
                                        <x-text-input id="home_hero_cta_label" name="home_hero_cta_label" type="text" class="mt-1 block w-full" :value="old('home_hero_cta_label', $homeSettings['hero_cta_label'] ?? '')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_cta_label')" />
                                    </div>
                                    <div>
                                        <x-input-label for="home_hero_cta_url" :value="__('messages.home_hero_cta_url')" />
                                        <x-text-input id="home_hero_cta_url" name="home_hero_cta_url" type="text" class="mt-1 block w-full" :value="old('home_hero_cta_url', $homeSettings['hero_cta_url'] ?? '')" />
                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.home_hero_cta_help') }}</p>
                                        <x-input-error class="mt-2" :messages="$errors->get('home_hero_cta_url')" />
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="home_aside_title" :value="__('messages.home_aside_title_label')" />
                                    <x-text-input id="home_aside_title" name="home_aside_title" type="text" class="mt-1 block w-full" :value="old('home_aside_title', $homeSettings['aside_title'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('home_aside_title')" />
                                </div>

                                <div>
                                    <x-input-label for="home_aside_markdown" :value="__('messages.home_aside_markdown_label')" />
                                    <textarea id="home_aside_markdown" name="home_aside_markdown" rows="6"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:border-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('home_aside_markdown', $homeSettings['aside_markdown'] ?? '') }}</textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('home_aside_markdown')" />
                                </div>

                                <div class="space-y-4">
                                    <x-input-label :value="__('messages.home_aside_image_label')" />
                                    <x-media-picker
                                        name="home_aside_media_variant_id"
                                        asset-input-name="home_aside_media_asset_id"
                                        context="home-aside"
                                        :initial-url="$initialImageUrl"
                                        :initial-asset-id="$initialAssetId"
                                        :initial-variant-id="$initialVariantId"
                                        label="{{ __('messages.home_aside_image_picker') }}"
                                        help="{{ __('messages.home_aside_image_help') }}"
                                    />
                                    <x-input-error class="mt-2" :messages="$errors->get('home_aside_media_asset_id')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('home_aside_media_variant_id')" />
                                </div>

                                <div>
                                    <x-input-label for="home_aside_image_alt" :value="__('messages.home_aside_image_alt_label')" />
                                    <x-text-input id="home_aside_image_alt" name="home_aside_image_alt" type="text" class="mt-1 block w-full" :value="old('home_aside_image_alt', $homeSettings['aside_image_alt'] ?? '')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('home_aside_image_alt')" />
                                </div>
                            </div>

                            <div>
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
