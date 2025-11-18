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

        $defaultPrimary = config('branding.colors.primary', '#1F2937');
        $defaultSecondary = config('branding.colors.secondary', '#111827');
        $defaultTertiary = config('branding.colors.tertiary', '#374151');

        $normalizeHex = static function ($value, string $fallback) {
            $normalized = \App\Support\ColorUtils::normalizeHexColor($value);

            return $normalized ?? $fallback;
        };

        $defaultPrimary = $normalizeHex($defaultPrimary, '#1F2937');
        $defaultSecondary = $normalizeHex($defaultSecondary, '#111827');
        $defaultTertiary = $normalizeHex($defaultTertiary, '#374151');

        $defaultColors = [
            'primary' => $defaultPrimary,
            'secondary' => $defaultSecondary,
            'tertiary' => $defaultTertiary,
        ];

        $currentColors = [
            'primary' => $normalizeHex(old('branding_primary_color', data_get($brandingSettings, 'primary_color')), $defaultPrimary),
            'secondary' => $normalizeHex(old('branding_secondary_color', data_get($brandingSettings, 'secondary_color')), $defaultSecondary),
            'tertiary' => $normalizeHex(old('branding_tertiary_color', data_get($brandingSettings, 'tertiary_color')), $defaultTertiary),
        ];

        $colorPalettes = collect($colorPalettes ?? [])
            ->filter(fn ($palette) => is_array($palette) && isset($palette['colors']) && is_array($palette['colors']))
            ->values()
            ->map(function ($palette, $index) use ($normalizeHex, $defaultColors) {
                $colors = $palette['colors'];

                $palette['colors'] = [
                    'primary' => $normalizeHex($colors['primary'] ?? null, $defaultColors['primary']),
                    'secondary' => $normalizeHex($colors['secondary'] ?? null, $defaultColors['secondary']),
                    'tertiary' => $normalizeHex($colors['tertiary'] ?? null, $defaultColors['tertiary']),
                ];

                $palette['key'] = is_string($palette['key'] ?? null) && $palette['key'] !== ''
                    ? $palette['key']
                    : 'palette-' . $index;

                $palette['label'] = is_string($palette['label'] ?? null) && $palette['label'] !== ''
                    ? $palette['label']
                    : $palette['key'];

                $palette['description'] = is_string($palette['description'] ?? null)
                    ? $palette['description']
                    : '';

                return $palette;
            })
            ->toArray();
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.branding_settings_heading'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <p class="text-sm font-medium text-indigo-600">{{ __('messages.settings') }}</p>
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.branding_settings_heading') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.branding_settings_description') }}
                        </p>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                            {{ __('messages.branding_powered_by_notice') }}
                        </p>
                    </div>

                    <section>

                        <form method="post" action="{{ route('settings.branding.update') }}" class="mt-6 space-y-6"
                            x-data="brandingColorForm({
                                colors: @js($currentColors),
                                defaults: @js($defaultColors),
                                palettes: @js($colorPalettes),
                            })"
                            x-init="init()">
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

                                <div class="mt-4 space-y-6">
                                    <div>
                                        <x-input-label :value="__('messages.branding_palette_label')" />
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.branding_palette_description') }}
                                        </p>

                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            <template x-for="palette in palettes" :key="palette.key">
                                                <button type="button"
                                                    class="relative flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left transition hover:border-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--brand-primary)] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:border-gray-700 dark:bg-gray-900/60 dark:hover:border-gray-600 dark:focus-visible:ring-offset-gray-900"
                                                    :class="isSelected(palette.key) ? 'border-[color:var(--brand-primary)] ring-2 ring-[color:var(--brand-primary)] ring-offset-2 ring-offset-white dark:ring-offset-gray-900' : ''"
                                                    :aria-pressed="isSelected(palette.key)"
                                                    @click.prevent="applyPalette(palette.key)"
                                                    @keydown.enter.prevent="applyPalette(palette.key)">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="palette.label"></p>
                                                            <p x-cloak x-show="palette.description" class="mt-1 text-xs text-gray-600 dark:text-gray-400" x-text="palette.description"></p>
                                                        </div>
                                                        <svg x-cloak x-show="isSelected(palette.key)" class="h-5 w-5 text-[color:var(--brand-primary)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 111.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>

                                                    <div class="flex gap-2" aria-hidden="true">
                                                        <template x-for="colorKey in ['primary', 'secondary', 'tertiary']" :key="`${palette.key}-${colorKey}`">
                                                            <div class="h-8 w-full rounded-md border border-black/10 dark:border-white/10"
                                                                :style="palette.colors[colorKey] ? `background-color: ${palette.colors[colorKey]};` : ''"
                                                                :title="palette.colors[colorKey] ?? ''"></div>
                                                        </template>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <div>
                                            <label for="branding_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_primary_color') }}
                                            </label>
                                            <input type="color" id="branding_primary_color" name="branding_primary_color"
                                                class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ $currentColors['primary'] }}"
                                                data-default-color="{{ $defaultPrimary }}"
                                                x-model="colors.primary"
                                                x-on:input="handleColorInput('primary', $event.target.value)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_primary_color')" />
                                        </div>

                                        <div>
                                            <label for="branding_secondary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_secondary_color') }}
                                            </label>
                                            <input type="color" id="branding_secondary_color" name="branding_secondary_color"
                                                class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ $currentColors['secondary'] }}"
                                                data-default-color="{{ $defaultSecondary }}"
                                                x-model="colors.secondary"
                                                x-on:input="handleColorInput('secondary', $event.target.value)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_secondary_color')" />
                                        </div>

                                        <div>
                                            <label for="branding_tertiary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ __('messages.branding_tertiary_color') }}
                                            </label>
                                            <input type="color" id="branding_tertiary_color" name="branding_tertiary_color"
                                                class="mt-2 h-12 w-full cursor-pointer rounded-md border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
                                                value="{{ $currentColors['tertiary'] }}"
                                                data-default-color="{{ $defaultTertiary }}"
                                                x-model="colors.tertiary"
                                                x-on:input="handleColorInput('tertiary', $event.target.value)" />
                                            <x-input-error class="mt-2" :messages="$errors->get('branding_tertiary_color')" />
                                        </div>
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
    @once
        <script {!! nonce_attr() !!}>
        window.brandingColorForm = function ({ colors = {}, defaults = {}, palettes = [] } = {}) {
            return {
                colors: colors,
                defaults: defaults,
                palettes: palettes,
                selectedPalette: null,
                init() {
                    this.defaults = this.resolveDefaults(this.defaults);
                    this.colors = this.resolveInitialColors(this.colors);
                    this.palettes = this.preparePalettes(this.palettes);
                    this.selectedPalette = this.findMatchingPaletteKey();
                },
                resolveDefaults(defaults) {
                    const source = (defaults && typeof defaults === 'object') ? defaults : {};

                    return {
                        primary: this.normalizeHex(source.primary) ?? '#1F2937',
                        secondary: this.normalizeHex(source.secondary) ?? '#111827',
                        tertiary: this.normalizeHex(source.tertiary) ?? '#374151',
                    };
                },
                resolveInitialColors(colors) {
                    const normalized = this.normalizeColorMap(colors);

                    return {
                        primary: normalized.primary ?? this.defaults.primary,
                        secondary: normalized.secondary ?? this.defaults.secondary,
                        tertiary: normalized.tertiary ?? this.defaults.tertiary,
                    };
                },
                preparePalettes(palettes) {
                    if (!Array.isArray(palettes)) {
                        return [];
                    }

                    return palettes
                        .map((palette) => {
                            const paletteObject = (palette && typeof palette === 'object') ? palette : {};
                            const paletteColors = (paletteObject.colors && typeof paletteObject.colors === 'object')
                                ? paletteObject.colors
                                : {};
                            const normalizedColors = this.normalizeColorMap(paletteColors);

                            const key = typeof paletteObject.key === 'string' ? paletteObject.key : '';
                            const label = typeof paletteObject.label === 'string' ? paletteObject.label : '';
                            const description = typeof paletteObject.description === 'string'
                                ? paletteObject.description
                                : '';

                            const normalizedKey = key.trim();
                            const normalizedLabel = label.trim();
                            const normalizedDescription = description.trim();

                            return {
                                key: normalizedKey,
                                label: normalizedLabel !== '' ? normalizedLabel : normalizedKey,
                                description: normalizedDescription,
                                colors: {
                                    primary: normalizedColors.primary ?? this.defaults.primary,
                                    secondary: normalizedColors.secondary ?? this.defaults.secondary,
                                    tertiary: normalizedColors.tertiary ?? this.defaults.tertiary,
                                },
                            };
                        })
                        .filter((palette) => palette.key !== '');
                },
                normalizeColorMap(map) {
                    const source = (map && typeof map === 'object') ? map : {};
                    const normalized = {};

                    ['primary', 'secondary', 'tertiary'].forEach((key) => {
                        const value = source[key] ?? null;
                        const normalizedValue = this.normalizeHex(value);

                        if (normalizedValue) {
                            normalized[key] = normalizedValue;
                        }
                    });

                    return normalized;
                },
                normalizeHex(value) {
                    if (typeof value !== 'string') {
                        return null;
                    }

                    let trimmed = value.trim();

                    if (trimmed === '') {
                        return null;
                    }

                    if (!trimmed.startsWith('#')) {
                        trimmed = `#${trimmed}`;
                    }

                    if (!/^#[0-9A-Fa-f]{6}$/.test(trimmed)) {
                        return null;
                    }

                    return trimmed.toUpperCase();
                },
                applyPalette(key) {
                    const palette = this.palettes.find((item) => item.key === key);

                    if (!palette) {
                        return;
                    }

                    this.colors.primary = palette.colors.primary;
                    this.colors.secondary = palette.colors.secondary;
                    this.colors.tertiary = palette.colors.tertiary;
                    this.selectedPalette = key;
                },
                findMatchingPaletteKey() {
                    const match = this.palettes.find((palette) => {
                        if (!palette || !palette.colors) {
                            return false;
                        }

                        return ['primary', 'secondary', 'tertiary'].every((colorKey) => {
                            const paletteValue = palette.colors[colorKey] ?? '';
                            const currentValue = this.colors[colorKey] ?? '';

                            return paletteValue.toUpperCase() === currentValue.toUpperCase();
                        });
                    });

                    return match ? match.key : null;
                },
                isSelected(key) {
                    return this.selectedPalette === key;
                },
                handleColorInput(key, value) {
                    const normalized = this.normalizeHex(value);

                    if (normalized) {
                        this.colors[key] = normalized;
                    }

                    this.selectedPalette = this.findMatchingPaletteKey();
                },
            };
        };
        </script>
    @endonce
    </div>
</x-app-admin-layout>
