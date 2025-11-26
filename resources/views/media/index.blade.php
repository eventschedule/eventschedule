<x-app-admin-layout>
    @php
        $canManageMedia = auth()->user()?->hasPermission('resources.manage');
    @endphp
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Media library') }}</h1>
    </x-slot>

    <div
        x-data="mediaLibraryPage({
            assetsEndpoint: '{{ route('media.assets.index', [], false) }}',
            uploadEndpoint: '{{ route('media.assets.store', [], false) }}',
            tagsEndpoint: '{{ route('media.tags.index', [], false) }}',
            createTagEndpoint: '{{ route('media.tags.store', [], false) }}',
            deleteTagTemplate: '{{ route('media.tags.destroy', ['tag' => '__ID__'], false) }}',
            syncTagsTemplate: '{{ route('media.assets.tags.sync', ['asset' => '__ID__'], false) }}',
            deleteAssetTemplate: '{{ route('media.assets.destroy', ['asset' => '__ID__'], false) }}',
            canManage: @json($canManageMedia),
        })"
        x-init="init()"
        class="space-y-6"
    >
        <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Manage assets') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Upload new media, add tags and review usage across your workspace.') }}</p>
                </div>
                @if ($canManageMedia)
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100 cursor-pointer">
                            <input type="file" class="hidden" @change="handleUpload($event)" accept="image/*">
                            <span>{{ __('Upload') }}</span>
                        </label>
                        <button type="button" @click="promptCreateTag" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('New tag') }}
                        </button>
                    </div>
                @endif
            </div>

            <div class="mt-4 max-w-md">
                <label class="sr-only" for="media-library-search">{{ __('Search media') }}</label>
                <input
                    type="search"
                    id="media-library-search"
                    x-model="search"
                    @input.debounce.400ms="pagination.current_page = 1; fetchAssets(1)"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="{{ __('Search media...') }}"
                >
            </div>

            <div class="flex flex-wrap gap-2" x-show="tags.length" x-cloak>
                <button
                    type="button"
                    @click="activeTag = null; fetchAssets()"
                    :class="activeTag === null ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                    class="px-3 py-1 rounded-full text-sm"
                >
                    {{ __('All assets') }}
                </button>
                <template x-for="tag in tags" :key="tag.id">
                    <div
                        class="inline-flex items-center gap-1 rounded-full text-sm"
                        :class="activeTag === tag.slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200'"
                    >
                        <button
                            type="button"
                            class="px-3 py-1"
                            @click="activeTag = tag.slug; fetchAssets()"
                            x-text="tag.name"
                        ></button>
                        @if ($canManageMedia)
                            <button
                                type="button"
                                class="px-2 py-1 text-xs"
                                :class="activeTag === tag.slug ? 'text-white/80 hover:text-white' : 'text-gray-500 hover:text-red-600 dark:hover:text-red-400'"
                                @click.stop="removeTag(tag)"
                            >
                                <span aria-hidden="true">&times;</span>
                                <span class="sr-only">{{ __('Remove tag') }}</span>
                            </button>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg p-6">
            <template x-if="isLoading">
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">{{ __('Loading assets...') }}</div>
            </template>
            <template x-if="!isLoading && assets.length === 0">
                <div class="text-center py-12 text-gray-500 dark:text-gray-400">{{ __('No media uploaded yet.') }}</div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" x-show="!isLoading && assets.length" x-cloak>
                <template x-for="asset in assets" :key="asset.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden flex flex-col">
                        <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-800">
                            <img :src="asset.url" class="object-cover w-full h-full" :alt="asset.original_filename">
                        </div>
                        <div class="p-4 space-y-2 flex-1 flex flex-col">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="asset.original_filename"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatDimensions(asset)"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400" x-text="formatUsage(asset.usage_count)"></div>
                            <div class="flex flex-wrap gap-1" x-show="asset.tags.length">
                                <template x-for="tag in asset.tags" :key="tag.id">
                                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs" x-text="tag.name"></span>
                                </template>
                            </div>
                            <div class="mt-auto flex items-center gap-2 pt-2">
                                @if ($canManageMedia)
                                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-500" @click="openTagEditor(asset)">{{ __('Edit tags') }}</button>
                                @endif
                                <button type="button" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-500" @click="openDetails(asset)">{{ __('Details') }}</button>
                                @if ($canManageMedia)
                                    <button type="button" class="ml-auto text-sm text-red-600 hover:text-red-500" @click="deleteAsset(asset)">{{ __('Delete') }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-between items-center" x-show="pagination.total > pagination.per_page" x-cloak>
                <button type="button" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-md" :disabled="pagination.current_page <= 1" @click="changePage(pagination.current_page - 1)">
                    {{ __('Previous') }}
                </button>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="pagination.current_page"></span>
                    /
                    <span x-text="pagination.last_page"></span>
                </div>
                <button type="button" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 rounded-md" :disabled="pagination.current_page >= pagination.last_page" @click="changePage(pagination.current_page + 1)">
                    {{ __('Next') }}
                </button>
            </div>
        </div>

        <!-- Tag editor modal -->
        <div x-show="showTagModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-gray-900/60" @click="closeTagEditor"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Edit tags') }}</h3>
                <div class="space-y-2 max-h-52 overflow-y-auto">
                    <template x-if="!tags.length">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No tags yet. Create one below to get started.') }}</p>
                    </template>
                    <template x-for="tag in tags" :key="tag.id">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                            <input type="checkbox" :value="tag.id" @change="toggleTag(tag.id)" :checked="selectedTagIds.includes(tag.id)">
                            <span x-text="tag.name"></span>
                        </label>
                    </template>
                </div>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <label for="new-tag-name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Add a new tag') }}</label>
                    <div class="flex items-center gap-2">
                        <input
                            id="new-tag-name"
                            type="text"
                            x-model="newTagName"
                            @keydown.enter.prevent="createTagFromModal"
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="{{ __('New tag name') }}"
                        >
                        <button
                            type="button"
                            class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!newTagName || !newTagName.trim() || isCreatingTag"
                            @click="createTagFromModal"
                        >
                            {{ __('Add') }}
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200" @click="closeTagEditor">{{ __('Cancel') }}</button>
                    <button type="button" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm" @click="saveTags">{{ __('Save') }}</button>
                </div>
            </div>
        </div>

        <!-- Asset in use modal -->
        <div x-show="showUsageWarning" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center">
            <div class="absolute inset-0 bg-gray-900/60" @click="cancelForceDelete"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-lg p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Asset in use') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300" x-text="pendingDeletionMessage || '{{ __('This asset is currently in use and cannot be deleted.') }}'"></p>
                <div class="space-y-2">
                    <template x-if="pendingDeletionUsages.length">
                        <div class="space-y-2">
                            <template x-for="usage in pendingDeletionUsages" :key="usage.id">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-md px-3 py-2">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="usage.display_name || usage.type || '{{ __('Record') }}'"></div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="usage.context_label"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!pendingDeletionUsages.length">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No usage details available.') }}</p>
                    </template>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Removing the asset will clear it from every location where it is currently used.') }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200" @click="cancelForceDelete">{{ __('Cancel') }}</button>
                    <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-500" @click="confirmForceDelete">{{ __('Remove usage & delete') }}</button>
                </div>
            </div>
        </div>

        <!-- Asset details drawer -->
        <div x-show="showDetails" x-cloak class="fixed inset-0 z-40 flex">
            <div class="flex-1 bg-gray-900/60" @click="closeDetails"></div>
            <div class="w-full max-w-md bg-white dark:bg-gray-900 shadow-xl overflow-y-auto p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Asset details') }}</h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeDetails">&times;</button>
                </div>
                <template x-if="detailAsset">
                    <div class="space-y-3">
                        <img :src="detailAsset.url" class="rounded-lg w-full object-cover" :alt="detailAsset.original_filename">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="detailAsset.original_filename"></h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="formatDimensions(detailAsset)"></p>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Variants') }}</h5>
                            <template x-if="!detailAsset.variants.length">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No cropped versions yet.') }}</p>
                            </template>
                            <div class="space-y-2" x-show="detailAsset.variants.length">
                                <template x-for="variant in detailAsset.variants" :key="variant.id">
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                                        <img :src="variant.url" class="w-full object-cover" :alt="variant.label ?? detailAsset.original_filename">
                                        <div class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300 flex items-center justify-between">
                                            <span x-text="variant.label || \"{{ __('Crop') }}\""></span>
                                            <span x-text="variant.width + '×' + variant.height"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ __('Used in') }}</h5>
                            <template x-if="!detailAsset.usages.length">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('No usages recorded yet.') }}</p>
                            </template>
                            <div class="space-y-2" x-show="detailAsset.usages.length" x-cloak>
                                <template x-for="usage in detailAsset.usages" :key="usage.id">
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-md px-3 py-2">
                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="usage.display_name || usage.type || '{{ __('Record') }}'"></div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="usage.context_label"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div>
                            @if ($canManageMedia)
                                <button type="button" class="inline-flex items-center justify-center rounded-md border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50" @click="deleteAsset(detailAsset)">
                                    {{ __('Delete asset') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-admin-layout>
