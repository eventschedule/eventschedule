@props([
    'name',
    'assetInputName' => null,
    'context' => null,
    'label' => __('Choose from library'),
    'initialUrl' => null,
    'initialAssetId' => null,
    'initialVariantId' => null,
    'help' => null,
])

@php
    $assetInput = $assetInputName ?? ($name . '_asset_id');
    $initialAsset = old($assetInput, $initialAssetId);
    $initialVariant = old($name, $initialVariantId);
    $initialPreview = $initialUrl;
@endphp

<div
    x-data="mediaPicker({
        assetsEndpoint: '{{ route('media.assets.index', [], false) }}',
        uploadEndpoint: '{{ route('media.assets.store', [], false) }}',
        tagsEndpoint: '{{ route('media.tags.index', [], false) }}',
        createTagEndpoint: '{{ route('media.tags.store', [], false) }}',
        variantEndpointTemplate: '{{ route('media.assets.variants.store', ['asset' => '__ASSET__'], false) }}',
        context: @json($context),
        initialAssetId: {{ $initialAsset ? (int) $initialAsset : 'null' }},
        initialVariantId: {{ $initialVariant ? (int) $initialVariant : 'null' }},
        initialUrl: @json($initialPreview ?: ''),
    })"
    class="space-y-3"
>
    <div class="flex items-center gap-4">
        <div class="w-24 h-24 rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
            <img x-show="previewUrl" :src="previewUrl" alt="" class="object-cover w-full h-full" />
            <span x-show="!previewUrl" class="text-xs text-gray-500 dark:text-gray-400">{{ __('No image selected') }}</span>
        </div>
        <div class="space-y-2">
            <input type="hidden" name="{{ $assetInput }}" :value="selectedAssetId ?? ''">
            <input type="hidden" name="{{ $name }}" :value="selectedVariantId ?? ''">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="openModal" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    {{ $label }}
                </button>
                <label class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                    <input type="file" class="hidden" @change="uploadFromPicker($event)" accept="image/*">
                    <span>{{ __('Upload') }}</span>
                </label>
                <button type="button" x-show="previewUrl" @click="clearSelection" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                    {{ __('Clear') }}
                </button>
            </div>
            @if ($help)
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>
            @endif
        </div>
    </div>

    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-gray-900/60" @click="closeModal"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-5xl mx-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Media library') }}</h3>
                <button type="button" class="text-2xl leading-none text-gray-500 hover:text-gray-700" @click="closeModal">&times;</button>
            </div>
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/3 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="px-3 py-1 rounded-full text-xs" :class="activeTag ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200' : 'bg-indigo-600 text-white'" @click="activeTag = null; fetchAssets()">
                            {{ __('All assets') }}
                        </button>
                        <template x-for="tag in tags" :key="tag.id">
                            <button type="button" class="px-3 py-1 rounded-full text-xs" :class="activeTag === tag.slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'" @click="activeTag = tag.slug; fetchAssets()" x-text="tag.name"></button>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1">
                        <template x-for="asset in assets" :key="asset.id">
                            <button type="button" class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" @click="selectAsset(asset)">
                                <img :src="asset.url" class="w-full h-24 object-cover" :alt="asset.original_filename">
                                <div class="px-2 py-1 text-xs text-gray-600 dark:text-gray-300 truncate" x-text="asset.original_filename"></div>
                            </button>
                        </template>
                        <div x-show="isLoading" class="col-span-2 text-center text-sm text-gray-500 dark:text-gray-400 py-6">{{ __('Loading...') }}</div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300" x-show="pagination.total > pagination.per_page">
                        <button type="button" class="px-3 py-1 border border-gray-200 dark:border-gray-700 rounded-md" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">{{ __('Prev') }}</button>
                        <span><span x-text="pagination.current_page"></span> / <span x-text="pagination.last_page"></span></span>
                        <button type="button" class="px-3 py-1 border border-gray-200 dark:border-gray-700 rounded-md" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">{{ __('Next') }}</button>
                    </div>
                </div>
                <div class="flex-1 p-6">
                    <template x-if="currentAsset">
                        <div class="space-y-4">
                            <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-800 rounded-md flex items-center justify-center overflow-hidden">
                                <img x-ref="cropImage" class="max-h-[420px] w-full object-contain" alt="">
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" @click="saveCrop" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500" :disabled="isUploading">
                                    <span x-show="!isUploading">{{ __('Save crop') }}</span>
                                    <span x-show="isUploading">{{ __('Saving...') }}</span>
                                </button>
                                <button type="button" @click="useOriginal" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    {{ __('Use original') }}
                                </button>
                                <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="!currentAsset">
                        <div class="h-full flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Select an asset to crop or upload a new one.') }}
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
