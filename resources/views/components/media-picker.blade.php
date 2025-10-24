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
    $hasPreview = filled($initialPreview);
@endphp

<div
    data-media-picker
    data-assets-endpoint="{{ route('media.assets.index', [], false) }}"
    data-upload-endpoint="{{ route('media.assets.store', [], false) }}"
    data-tags-endpoint="{{ route('media.tags.index', [], false) }}"
    data-delete-tag-template="{{ route('media.tags.destroy', ['tag' => '__ID__'], false) }}"
    data-variant-endpoint-template="{{ route('media.assets.variants.store', ['asset' => '__ASSET__'], false) }}"
    data-context="{{ e($context ?? '') }}"
    data-initial-asset-id="{{ $initialAsset ? (int) $initialAsset : '' }}"
    data-initial-variant-id="{{ $initialVariant ? (int) $initialVariant : '' }}"
    data-initial-url="{{ $initialPreview ?: '' }}"
    data-label-all="{{ __('All assets') }}"
    class="space-y-3"
>
    <div class="flex items-center gap-4">
        <div class="w-24 h-24 rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
            <img
                data-role="preview-image"
                src="{{ $initialPreview ?: '' }}"
                alt=""
                class="object-cover w-full h-full {{ $hasPreview ? '' : 'hidden' }}"
            >
            <span data-role="placeholder" class="text-xs text-gray-500 dark:text-gray-400 {{ $hasPreview ? 'hidden' : '' }}">
                {{ __('No image selected') }}
            </span>
        </div>
        <div class="space-y-2">
            <input type="hidden" name="{{ $assetInput }}" value="{{ $initialAsset ? (int) $initialAsset : '' }}" data-role="asset-input">
            <input type="hidden" name="{{ $name }}" value="{{ $initialVariant ? (int) $initialVariant : '' }}" data-role="variant-input">
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    data-action="open"
                >
                    {{ $label }}
                </button>
                <label class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                    <input type="file" class="hidden" data-role="file-input" accept="image/*">
                    <span>{{ __('Upload') }}</span>
                </label>
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 {{ $hasPreview ? '' : 'hidden' }}"
                    data-action="clear"
                >
                    {{ __('Clear') }}
                </button>
            </div>
            @if ($help)
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>
            @endif
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden" data-role="modal">
        <div class="absolute inset-0 bg-gray-900/60" data-action="close"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-5xl mx-4">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Media library') }}</h3>
                <button type="button" class="text-2xl leading-none text-gray-500 hover:text-gray-700" data-action="close">&times;</button>
            </div>
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/3 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 p-6 space-y-4">
                    <div class="flex flex-wrap gap-2" data-role="tag-filter" hidden></div>
                    <div class="grid grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-1" data-role="asset-grid"></div>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6" data-role="loading" hidden>{{ __('Loading...') }}</div>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6" data-role="empty" hidden>{{ __('No media uploaded yet.') }}</div>
                    <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300" data-role="pagination" hidden>
                        <button type="button" class="px-3 py-1 border border-gray-200 dark:border-gray-700 rounded-md" data-action="prev">{{ __('Prev') }}</button>
                        <span><span data-role="page-current">1</span> / <span data-role="page-total">1</span></span>
                        <button type="button" class="px-3 py-1 border border-gray-200 dark:border-gray-700 rounded-md" data-action="next">{{ __('Next') }}</button>
                    </div>
                </div>
                <div class="flex-1 p-6" data-role="editor">
                    <div class="h-full flex items-center justify-center text-sm text-gray-500 dark:text-gray-400" data-role="selection-placeholder">
                        {{ __('Select an asset to crop or upload a new one.') }}
                    </div>
                    <div class="space-y-4 hidden" data-role="editor-content">
                        <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-800 rounded-md flex items-center justify-center overflow-hidden">
                            <img data-role="crop-image" class="max-h-[420px] w-full object-contain" alt="">
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-70 disabled:cursor-not-allowed"
                                data-action="save-crop"
                            >
                                <span data-role="save-label">{{ __('Save crop') }}</span>
                                <span data-role="saving-label" class="hidden">{{ __('Saving...') }}</span>
                            </button>
                            <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800" data-action="use-original">
                                {{ __('Use original') }}
                            </button>
                            <button type="button" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100" data-action="cancel">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
