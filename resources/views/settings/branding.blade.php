<x-app-admin-layout>
    @php
        $brandingSettings = $brandingSettings ?? [];

        if ($brandingSettings instanceof \Illuminate\Support\Collection) {
            $brandingSettings = $brandingSettings->toArray();
        } elseif (is_object($brandingSettings)) {
            $brandingSettings = (array) $brandingSettings;
        } elseif (! is_array($brandingSettings)) {
            $brandingSettings = [];
        }

        $languageOptions = $languageOptions ?? [];

        if ($languageOptions instanceof \Illuminate\Support\Collection) {
            $languageOptions = $languageOptions->toArray();
        } elseif (is_object($languageOptions)) {
            $languageOptions = (array) $languageOptions;
        }

        $languageOptions = collect($languageOptions)
            ->filter(fn ($label, $code) => is_string($code) && is_string($label))
            ->toArray();

        $initialLogoAssetId = old('branding_logo_media_asset_id', data_get($brandingSettings, 'logo_media_asset_id'));
        $initialLogoVariantId = old('branding_logo_media_variant_id', data_get($brandingSettings, 'logo_media_variant_id'));
        $initialLogoUrl = data_get($brandingSettings, 'logo_url');

        if ($initialLogoAssetId) {
            $asset = \App\Models\MediaAsset::find($initialLogoAssetId);

            if ($asset) {
                $initialLogoUrl = $asset->url;

                if ($initialLogoVariantId) {
                    $variant = \App\Models\MediaAssetVariant::where('media_asset_id', $asset->id)
                        ->find($initialLogoVariantId);

                    if ($variant) {
                        $initialLogoUrl = $variant->url;
                    }
                }
            }
        }

        $defaultPrimary = config('branding.colors.primary', '#4E81FA');
        $defaultSecondary = config('branding.colors.secondary', '#365FCC');
        $defaultTertiary = config('branding.colors.tertiary', '#3A6BE0');
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6">
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#4E81FA] hover:text-[#365fcc]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('messages.back_to_settings') }}
                        </a>
                    </div>

                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.branding_settings_heading') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.branding_settings_description') }}
                            </p>

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                {{ __('messages.branding_powered_by_notice') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.branding.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label :value="__('messages.branding_logo_label')" />
                                <div class="mt-4 space-y-4">
                                    <x-media-picker
                                        name="branding_logo_media_variant_id"
                                        asset-input-name="branding_logo_media_asset_id"
                                        context="branding-logo"
                                        :initial-url="$initialLogoUrl"
                                        :initial-asset-id="$initialLogoAssetId"
                                        :initial-variant-id="$initialLogoVariantId"
                                        label="{{ __('messages.branding_logo_picker') }}"
                                        help="{{ __('messages.branding_logo_help') }}"
                                    />

                                    <x-input-error class="mt-2" :messages="$errors->get('branding_logo_media_asset_id')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('branding_logo_media_variant_id')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="branding_logo_alt" :value="__('messages.branding_logo_alt_label')" />
                                <x-text-input id="branding_logo_alt" name="branding_logo_alt" type="text"
                                    class="mt-1 block w-full"
                                    :value="old('branding_logo_alt', data_get($brandingSettings, 'logo_alt'))" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.branding_logo_alt_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('branding_logo_alt')" />
                            </div>

                            <div>
                                <x-input-label :value="__('messages.branding_color_help')" />
                                <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-300">
                                    <ul class="list-disc space-y-2 pl-5">
                                        <li>{{ __('messages.branding_primary_color_help') }}</li>
                                        <li>{{ __('messages.branding_secondary_color_help') }}</li>
                                        <li>{{ __('messages.branding_tertiary_color_help') }}</li>
                                    </ul>
                                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('messages.branding_color_contrast_help') }}
                                    </p>
                                </div>

                                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label for="branding_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('messages.branding_primary_color') }}
                                        </label>
                                        <input type="color" id="branding_primary_color" name="branding_primary_color"
                                            class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                            value="{{ old('branding_primary_color', data_get($brandingSettings, 'primary_color')) }}"
                                            data-default-color="{{ $defaultPrimary }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('branding_primary_color')" />
                                    </div>

                                    <div>
                                        <label for="branding_secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('messages.branding_secondary_color') }}
                                        </label>
                                        <input type="color" id="branding_secondary_color" name="branding_secondary_color"
                                            class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                            value="{{ old('branding_secondary_color', data_get($brandingSettings, 'secondary_color')) }}"
                                            data-default-color="{{ $defaultSecondary }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('branding_secondary_color')" />
                                    </div>

                                    <div>
                                        <label for="branding_tertiary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ __('messages.branding_tertiary_color') }}
                                        </label>
                                        <input type="color" id="branding_tertiary_color" name="branding_tertiary_color"
                                            class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                            value="{{ old('branding_tertiary_color', data_get($brandingSettings, 'tertiary_color')) }}"
                                            data-default-color="{{ $defaultTertiary }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('branding_tertiary_color')" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="branding_default_language" :value="__('messages.branding_default_language')" />
                                <select id="branding_default_language" name="branding_default_language"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]">
                                    @foreach ($languageOptions as $code => $label)
                                        <option value="{{ $code }}"
                                            @selected(old('branding_default_language', data_get($brandingSettings, 'default_language')) === $code)>
                                            {{ strtoupper($code) }} — {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.branding_default_language_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('branding_default_language')" />
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <button type="submit" name="reset_branding" value="1" formnovalidate
                                        onclick="return confirm('{{ addslashes(__('messages.branding_reset_confirm')) }}');"
                                        class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        {{ __('messages.branding_reset_button') }}
                                    </button>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                    @if (session('status') === 'branding-settings-updated')
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                            class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.branding_settings_saved') }}</p>
                                    @elseif (session('status') === 'branding-settings-reset')
                                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                            class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.branding_settings_reset') }}</p>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
