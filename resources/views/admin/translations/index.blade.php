<x-app-admin-layout>
    <style {!! nonce_attr() !!}>[v-cloak] { display: none; }</style>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'translations'])

        {{--
            CSP/Vue note: this page edits the very messages.* strings it renders,
            and Vue compiles every text node inside the mount as a template. All
            text inside #translations-app therefore comes from the MSG object
            (passed via @json) and Vue's own interpolation - never from @lang/__()
            text nodes. Blade output is only safe in HTML attributes here.
        --}}
        <div id="translations-app" class="space-y-4" v-cloak>

            {{-- Toolbar --}}
            <div class="ap-card rounded-xl p-4 sm:p-6">
                <div class="mb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@{{ msg.title }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.intro }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if (config('app.is_nexus'))
                            <a href="{{ route('admin.translations.suggestions') }}"
                                class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                                @{{ msg.reviewSuggestions }}
                                <span v-if="pendingSuggestions > 0"
                                    class="ms-1.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">@{{ pendingSuggestions }}</span>
                            </a>
                        @else
                            <button v-if="unsharedCount > 0" type="button" @click="openShareModal"
                                class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                                <svg class="w-5 h-5 me-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                                </svg>
                                @{{ msg.shareCount.replace(':count', unsharedCount) }}
                            </button>
                        @endif
                        <button type="button" @click="copyAsPhp" :disabled="!hasOverrides"
                            :title="hasOverrides ? msg.copyAsPhp : msg.noCustomizationsToCopy"
                            class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="!copiedPhp" class="w-5 h-5 me-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                            </svg>
                            <svg v-else class="w-5 h-5 me-1.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            @{{ copiedPhp ? msg.copied : msg.copyAsPhp }}
                        </button>
                    </div>
                </div>

                {{-- Demo mode notice --}}
                <div v-if="isDemo" class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <span>@{{ msg.demoDisabled }}</span>
                    </p>
                </div>

                {{-- Selectors --}}
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="w-full lg:w-56">
                        <label for="tr-locale" class="sr-only">@{{ msg.language }}</label>
                        <select id="tr-locale" v-model="locale" @change="onScopeChange"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option v-for="l in locales" :key="l.code" :value="l.code">@{{ l.label }}</option>
                        </select>
                    </div>
                    <div class="w-full lg:w-48">
                        <label for="tr-file" class="sr-only">@{{ msg.file }}</label>
                        <select id="tr-file" v-model="group" @change="onScopeChange"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option v-for="g in groups" :key="g" :value="g">@{{ g }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="tr-search" class="sr-only">@{{ msg.search }}</label>
                        <input id="tr-search" v-model.trim="searchQuery" type="search" autocomplete="off"
                            placeholder="{{ __('messages.search_keys_and_text') }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    </div>
                    <div class="w-full lg:w-64">
                        <label for="tr-status" class="sr-only">@{{ msg.status }}</label>
                        <select id="tr-status" v-model="statusFilter"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option value="all">@{{ msg.allKeys }} (@{{ rows.length }})</option>
                            <option value="customized">@{{ msg.customized }} (@{{ customizedCount }})</option>
                            <option value="missing">@{{ msg.missingTranslations }} (@{{ missingCount }})</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="ap-card rounded-xl shadow overflow-hidden">
                {{-- Loading --}}
                <div v-if="loading" class="p-12 text-center" role="status">
                    <svg class="animate-spin h-8 w-8 mx-auto text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.loading }}</p>
                </div>

                {{-- Load error --}}
                <div v-else-if="loadError" class="p-6">
                    <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-center justify-between gap-4">
                        <span class="text-sm text-red-700 dark:text-red-300">@{{ msg.loadFailed }}</span>
                        <button type="button" @click="loadData"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200">
                            @{{ msg.tryAgain }}
                        </button>
                    </div>
                </div>

                {{-- Empty --}}
                <div v-else-if="filteredRows.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">@{{ msg.noMatchingKeys }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.noMatchingKeysHint }}</p>
                    <button v-if="searchQuery || statusFilter !== 'all'" type="button" @click="clearFilters"
                        class="mt-4 px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                        @{{ msg.clear }}
                    </button>
                </div>

                {{-- Rows --}}
                <template v-else>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/4">@{{ msg.key }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-[35%]">@{{ msg.originalText }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@{{ msg.yourTranslation }}</th>
                                    <th class="px-6 py-3 w-12"><span class="sr-only">@{{ msg.actions }}</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="row in pagedRows" :key="row.key" class="align-top">
                                    <td class="px-6 py-4 text-sm">
                                        <span class="font-mono text-xs text-gray-900 dark:text-gray-100 break-all" dir="ltr">@{{ row.key }}</span>
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span v-if="isUnsaved(row)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">@{{ msg.unsaved }}</span>
                                            <span v-else-if="row.override !== null" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">@{{ msg.customized }}</span>
                                            <span v-if="isMissing(row)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300">@{{ msg.missing }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap break-words" dir="auto">@{{ row.en }}</p>
                                        <p v-if="locale !== 'en' && row.shipped" class="mt-1 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap break-words" dir="auto">@{{ row.shipped }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <textarea :rows="rowsFor(row)" dir="auto"
                                            :value="draftValue(row)" @input="setDraft(row.key, $event.target.value)"
                                            :placeholder="row.shipped || row.en"
                                            :aria-label="msg.yourTranslation + ': ' + row.key"
                                            :aria-describedby="warnings[row.key] ? 'warn-' + row.key : null"
                                            :disabled="isDemo"
                                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm resize-y disabled:opacity-60"></textarea>
                                        <div v-if="warnings[row.key]" :id="'warn-' + row.key"
                                            class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-2">
                                            <p class="text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                                <span>@{{ warningText(row.key) }}</span>
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-end">
                                        <button v-if="row.override !== null && !isDemo" type="button" @click="revertRow(row)"
                                            :aria-label="msg.revertToDefault + ': ' + row.key" :title="msg.revertToDefault"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#2d2d30] transition-all duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400" aria-live="polite">@{{ showingText }}</p>
                        <div class="flex gap-2">
                            <button type="button" @click="prevPage" :disabled="page === 1"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                @{{ msg.previous }}
                            </button>
                            <button type="button" @click="nextPage" :disabled="page >= totalPages"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                @{{ msg.next }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            @unless (config('app.is_nexus'))
                {{-- Community sharing --}}
                <div class="ap-card rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">@{{ msg.communitySharing }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">@{{ msg.communitySharingHelp }}</p>
                    <div class="flex items-center gap-3">
                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                            <input type="checkbox" :checked="autoShare" @change="toggleAutoShare" :disabled="isDemo" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.autoShare }}</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 ms-14">@{{ msg.autoShareHelp }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">@{{ msg.consentDisclosure }}</p>
                </div>
            @endunless

            {{-- Spacer so the fixed dock never covers the last card (the mount
                 root's own attributes are not compiled by Vue, so this cannot
                 be a :class binding on #translations-app) --}}
            <div v-if="dockVisible" class="h-16" aria-hidden="true"></div>

            {{-- Bottom action dock --}}
            <div v-if="dockVisible"
                class="fixed bottom-0 start-0 end-0 lg:start-72 z-40 border-t border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#1e1e1e] shadow-lg px-4 sm:px-6 lg:px-8 py-3"
                role="region" aria-live="polite">
                {{-- Unsaved changes --}}
                <div v-if="dockState === 'save'" class="flex items-center justify-between gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.nUnsavedChanges.replace(':count', unsavedCount) }}</span>
                    <div class="flex items-center gap-3">
                        <span v-if="saveError" class="text-sm text-red-600 dark:text-red-400">@{{ msg.saveFailed }}</span>
                        <button type="button" @click="save" :disabled="saving || isDemo"
                            class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="saving" class="animate-spin w-4 h-4 me-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            @{{ msg.save }}
                        </button>
                    </div>
                </div>
                {{-- After-save share prompt --}}
                <div v-else-if="dockState === 'prompt'" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            @{{ msg.savedNChanges.replace(':count', lastSavedCount) }} @{{ msg.shareQuestion }}
                        </p>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">@{{ msg.consentDisclosure }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="notNow" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-200">@{{ msg.notNow }}</button>
                        <button type="button" @click="alwaysShare"
                            class="px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">@{{ msg.alwaysShare }}</button>
                        <button type="button" @click="shareNow"
                            class="px-3 py-2 text-sm font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">@{{ msg.shareNow }}</button>
                    </div>
                </div>
                {{-- Transient notice --}}
                <div v-else-if="dockState === 'notice'" class="flex items-center justify-between gap-4">
                    <p class="text-sm" :class="notice.tone === 'error' ? 'text-red-600 dark:text-red-400' : 'text-green-700 dark:text-green-400'">@{{ notice.text }}</p>
                    <button v-if="notice.retry" type="button" @click="retryNotice"
                        class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                        @{{ msg.tryAgain }}
                    </button>
                </div>
            </div>

            @unless (config('app.is_nexus'))
                {{-- Share modal --}}
                <div v-if="showShareModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                    @click.self="closeShareModal" role="dialog" aria-modal="true" :aria-label="msg.shareModalTitle">
                    <div class="bg-white dark:bg-[#1e1e1e] rounded-xl shadow-xl max-w-3xl w-full mx-4 max-h-[85vh] flex flex-col">
                        <div class="flex items-center justify-between px-5 pt-5 pb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@{{ msg.shareModalTitle }}</h3>
                            <button ref="shareModalClose" type="button" @click="closeShareModal" :aria-label="msg.close"
                                class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="px-5 overflow-y-auto flex-1">
                            <div v-if="shareLoading" class="py-8 text-center" role="status">
                                <svg class="animate-spin h-6 w-6 mx-auto text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                            <div v-else-if="shareRows.length === 0" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">@{{ msg.noUnsharedChanges }}</div>
                            <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                <li v-for="item in shareRows" :key="item.hash" class="py-3 flex gap-3">
                                    <input type="checkbox" v-model="item.checked" :id="'share-' + item.hash"
                                        class="mt-1 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    <label :for="'share-' + item.hash" class="min-w-0 flex-1 cursor-pointer">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-mono text-xs text-gray-900 dark:text-gray-100" dir="ltr">@{{ item.key }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-400">@{{ item.locale }} &middot; @{{ item.group }}</span>
                                        </div>
                                        <div class="mt-1 grid sm:grid-cols-[1fr,auto,1fr] items-center gap-2 text-sm">
                                            <span class="text-gray-500 dark:text-gray-400 line-clamp-2" dir="auto">@{{ item.before }}</span>
                                            <svg class="w-4 h-4 text-gray-400 rtl:rotate-180 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                            <span class="text-gray-900 dark:text-gray-100 line-clamp-2" dir="auto">@{{ item.after }}</span>
                                        </div>
                                    </label>
                                </li>
                            </ul>
                            <div v-if="shareError" class="my-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <span>@{{ msg.shareUnreachable }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">@{{ msg.consentDisclosure }}</p>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="closeShareModal"
                                    class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                                    @{{ msg.cancel }}
                                </button>
                                <button type="button" @click="shareSelected" :disabled="sharing || checkedShareCount === 0"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="sharing" class="animate-spin w-4 h-4 me-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    @{{ shareError ? msg.tryAgain : msg.shareSelectedCount.replace(':count', checkedShareCount) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endunless
        </div>
    </div>

    @php
        // Built here rather than inline in @json(): Blade's directive-argument
        // parser mis-handles large multi-line arrays.
        $trEditorMsg = [
            'title' => __('messages.translations'),
            'intro' => __('messages.translations_intro'),
            'file' => __('messages.translation_file'),
            'key' => __('messages.translation_key'),
            'originalText' => __('messages.original_text'),
            'yourTranslation' => __('messages.your_translation'),
            'allKeys' => __('messages.all_keys'),
            'customized' => __('messages.customized'),
            'missingTranslations' => __('messages.missing_translations'),
            'missing' => __('messages.missing'),
            'unsaved' => __('messages.unsaved'),
            'nUnsavedChanges' => __('messages.n_unsaved_changes'),
            'previous' => __('messages.previous'),
            'next' => __('messages.next'),
            'revertToDefault' => __('messages.revert_to_default'),
            'revertConfirm' => __('messages.revert_translation_confirm'),
            'copyAsPhp' => __('messages.copy_as_php'),
            'copied' => __('messages.copied'),
            'noCustomizationsToCopy' => __('messages.no_customizations_to_copy'),
            'placeholderWarning' => __('messages.translation_placeholder_warning'),
            'pluralWarning' => __('messages.translation_plural_warning'),
            'savedNChanges' => __('messages.saved_n_changes'),
            'savedAndShared' => __('messages.saved_and_shared_n_changes'),
            'saveFailed' => __('messages.save_translations_failed'),
            'loadFailed' => __('messages.translations_load_failed'),
            'tryAgain' => __('messages.try_again'),
            'noMatchingKeys' => __('messages.no_matching_keys'),
            'noMatchingKeysHint' => __('messages.no_matching_keys_hint'),
            'showingXOfY' => __('messages.showing_x_of_y'),
            'clear' => __('messages.clear'),
            'save' => __('messages.save'),
            'loading' => __('messages.loading'),
            'actions' => __('messages.actions'),
            'language' => __('messages.language'),
            'search' => __('messages.search'),
            'status' => __('messages.status'),
            'cancel' => __('messages.cancel'),
            'close' => __('messages.close'),
            'communitySharing' => __('messages.community_sharing'),
            'communitySharingHelp' => __('messages.community_sharing_help'),
            'shareQuestion' => __('messages.share_with_community_question'),
            'shareNow' => __('messages.share_now'),
            'alwaysShare' => __('messages.always_share'),
            'notNow' => __('messages.not_now'),
            'shareCount' => __('messages.share_count'),
            'shareModalTitle' => __('messages.share_translations_title'),
            'consentDisclosure' => __('messages.share_consent_disclosure'),
            'shareSelectedCount' => __('messages.share_selected_count'),
            'sharedThankYou' => __('messages.shared_n_thank_you'),
            'shareUnreachable' => __('messages.share_unreachable'),
            'noUnsharedChanges' => __('messages.no_unshared_changes'),
            'autoShare' => __('messages.auto_share_translations'),
            'autoShareHelp' => __('messages.auto_share_translations_help'),
            'reviewSuggestions' => __('messages.review_suggestions'),
            'unsavedConfirm' => __('messages.unsaved_changes_confirm'),
            'demoDisabled' => __('messages.demo_mode_settings_disabled'),
        ];
        $trEditorLocales = collect(config('app.supported_languages'))
            ->map(fn ($name, $code) => ['code' => $code, 'label' => ucfirst(__('messages.'.$name)).' ('.$code.')'])
            ->values();
    @endphp
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var MSG = @json($trEditorMsg);
            var LOCALES = @json($trEditorLocales);
            var GROUPS = @json(\App\Services\TranslationOverrideService::GROUPS);
            var URLS = {
                data: @json(route('admin.translations.data')),
                save: @json(route('admin.translations.save')),
                revert: @json(route('admin.translations.revert')),
                unshared: @json(route('admin.translations.unshared')),
                share: @json(route('admin.translations.share')),
                autoShare: @json(route('admin.translations.auto_share')),
            };
            var IS_NEXUS = @json((bool) config('app.is_nexus'));
            var CSRF = document.querySelector('meta[name="csrf-token"]').content;

            function postJson(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify(body || {}),
                }).then(function (r) {
                    if (!r.ok) throw r;
                    return r.json();
                });
            }

            Vue.createApp({
                data() {
                    return {
                        msg: MSG,
                        locales: LOCALES,
                        groups: GROUPS,
                        isNexus: IS_NEXUS,
                        isDemo: false,
                        locale: 'en',
                        group: 'messages',
                        prevLocale: 'en',
                        prevGroup: 'messages',
                        rows: [],
                        drafts: {},
                        warnings: {},
                        searchQuery: '',
                        activeQuery: '',
                        searchTimer: null,
                        statusFilter: 'all',
                        page: 1,
                        perPage: 50,
                        loading: true,
                        loadError: false,
                        saving: false,
                        saveError: false,
                        autoShare: @json((bool) $autoShare),
                        unsharedCount: 0,
                        pendingSuggestions: @json((int) $pendingSuggestions),
                        lastSavedCount: 0,
                        lastSavedHashes: [],
                        promptVisible: false,
                        promptDismissed: sessionStorage.getItem('es-tr-share-not-now') === '1',
                        notice: null,
                        noticeTimer: null,
                        showShareModal: false,
                        shareRows: [],
                        shareLoading: false,
                        sharing: false,
                        shareError: false,
                        copiedPhp: false,
                    };
                },
                computed: {
                    filteredRows() {
                        var self = this;
                        var list = this.rows;
                        if (this.statusFilter === 'customized') {
                            list = list.filter(function (r) { return r.override !== null || self.isDirtyKey(r.key); });
                        } else if (this.statusFilter === 'missing') {
                            list = list.filter(function (r) { return self.isMissing(r); });
                        }
                        if (this.activeQuery) {
                            var q = this.activeQuery;
                            list = list.filter(function (r) {
                                if (r._search.indexOf(q) !== -1) return true;
                                var current = self.drafts[r.key] !== undefined ? self.drafts[r.key] : (r.override || '');
                                return current.toLowerCase().indexOf(q) !== -1;
                            });
                        }
                        return list;
                    },
                    pagedRows() {
                        return this.filteredRows.slice((this.page - 1) * this.perPage, this.page * this.perPage);
                    },
                    totalPages() {
                        return Math.max(1, Math.ceil(this.filteredRows.length / this.perPage));
                    },
                    showingText() {
                        return this.msg.showingXOfY
                            .replace(':shown', this.pagedRows.length)
                            .replace(':total', this.filteredRows.length);
                    },
                    customizedCount() {
                        var self = this;
                        return this.rows.filter(function (r) { return r.override !== null || self.isDirtyKey(r.key); }).length;
                    },
                    missingCount() {
                        var self = this;
                        return this.rows.filter(function (r) { return self.isMissing(r); }).length;
                    },
                    hasOverrides() {
                        return this.rows.some(function (r) { return r.override !== null; });
                    },
                    unsavedCount() {
                        var self = this;
                        return Object.keys(this.drafts).filter(function (key) { return self.isDirtyKey(key); }).length;
                    },
                    checkedShareCount() {
                        return this.shareRows.filter(function (r) { return r.checked; }).length;
                    },
                    dockState() {
                        if (this.unsavedCount > 0) return 'save';
                        if (this.promptVisible) return 'prompt';
                        if (this.notice) return 'notice';
                        return null;
                    },
                    dockVisible() {
                        return this.dockState !== null;
                    },
                },
                watch: {
                    searchQuery(value) {
                        var self = this;
                        clearTimeout(this.searchTimer);
                        this.searchTimer = setTimeout(function () {
                            self.activeQuery = value.toLowerCase();
                            self.page = 1;
                        }, 150);
                    },
                    statusFilter() {
                        this.page = 1;
                    },
                    filteredRows(rows) {
                        if (this.page > this.totalPages) this.page = this.totalPages;
                    },
                },
                mounted() {
                    var self = this;
                    this.loadData();
                    window.addEventListener('beforeunload', function (e) {
                        if (self.unsavedCount > 0) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && self.showShareModal) self.closeShareModal();
                    });
                },
                methods: {
                    loadData() {
                        var self = this;
                        this.loading = true;
                        this.loadError = false;
                        fetch(URLS.data + '?locale=' + encodeURIComponent(this.locale) + '&group=' + encodeURIComponent(this.group), {
                            headers: { 'Accept': 'application/json' },
                        })
                            .then(function (r) { if (!r.ok) throw r; return r.json(); })
                            .then(function (data) {
                                self.rows = Object.freeze(data.rows.map(function (r) {
                                    r._search = (r.key + '\n' + (r.en || '') + '\n' + (r.shipped || '')).toLowerCase();
                                    return Object.freeze(r);
                                }));
                                self.drafts = {};
                                self.warnings = {};
                                self.page = 1;
                                self.searchQuery = '';
                                self.activeQuery = '';
                                self.autoShare = data.autoShare;
                                self.unsharedCount = data.unsharedCount;
                                self.isDemo = data.isDemo;
                                if (data.pendingSuggestions !== null) self.pendingSuggestions = data.pendingSuggestions;
                                self.prevLocale = self.locale;
                                self.prevGroup = self.group;
                                self.loading = false;
                            })
                            .catch(function () {
                                self.loading = false;
                                self.loadError = true;
                            });
                    },
                    onScopeChange() {
                        if (this.unsavedCount > 0 && !confirm(this.msg.unsavedConfirm)) {
                            this.locale = this.prevLocale;
                            this.group = this.prevGroup;
                            return;
                        }
                        this.loadData();
                    },
                    savedValueOf(key) {
                        var row = this.rows.find(function (r) { return r.key === key; });
                        return row && row.override !== null ? row.override : '';
                    },
                    isDirtyKey(key) {
                        return this.drafts[key] !== undefined && this.drafts[key] !== this.savedValueOf(key);
                    },
                    draftValue(row) {
                        return this.drafts[row.key] !== undefined ? this.drafts[row.key] : (row.override !== null ? row.override : '');
                    },
                    setDraft(key, value) {
                        this.drafts[key] = value;
                        if (this.warnings[key]) delete this.warnings[key];
                    },
                    isUnsaved(row) {
                        return this.isDirtyKey(row.key);
                    },
                    isMissing(row) {
                        return this.locale !== 'en' && !row.shipped;
                    },
                    rowsFor(row) {
                        var value = this.draftValue(row) || row.shipped || row.en || '';
                        return value.length > 90 || value.indexOf('\n') !== -1 ? 3 : 1;
                    },
                    warningText(key) {
                        var warning = this.warnings[key];
                        if (!warning) return '';
                        var parts = [];
                        if (warning.placeholders && warning.placeholders.length) {
                            parts.push(this.msg.placeholderWarning.replace(':tokens', warning.placeholders.join(', ')));
                        }
                        if (warning.plural) {
                            parts.push(this.msg.pluralWarning);
                        }
                        return parts.join(' ');
                    },
                    rebuildRows(applied) {
                        // Mirror the server's self-cleaning rule: empty or
                        // shipped-equal values remove the override.
                        this.rows = Object.freeze(this.rows.map(function (r) {
                            if (applied[r.key] === undefined) return r;
                            var value = applied[r.key];
                            var next = Object.assign({}, r, {
                                override: (value === '' || value === r.shipped) ? null : value,
                                shared_at: null,
                            });
                            next._search = (next.key + '\n' + (next.en || '') + '\n' + (next.shipped || '')).toLowerCase();
                            return Object.freeze(next);
                        }));
                    },
                    save() {
                        var self = this;
                        if (this.saving || this.unsavedCount === 0) return;
                        var values = {};
                        Object.keys(this.drafts).forEach(function (key) {
                            if (self.isDirtyKey(key)) values[key] = self.drafts[key];
                        });
                        this.saving = true;
                        this.saveError = false;
                        postJson(URLS.save, { locale: this.locale, group: this.group, values: values })
                            .then(function (data) {
                                self.saving = false;
                                self.rebuildRows(values);
                                Object.keys(values).forEach(function (key) { delete self.drafts[key]; });
                                self.warnings = data.warnings || {};
                                self.unsharedCount = data.unsharedCount;
                                self.lastSavedCount = data.saved + data.removed;
                                self.lastSavedHashes = data.savedHashes || [];
                                self.afterSave(data);
                            })
                            .catch(function () {
                                self.saving = false;
                                self.saveError = true;
                            });
                    },
                    afterSave(data) {
                        if (this.isNexus || data.saved === 0) {
                            this.showNotice(this.msg.savedNChanges.replace(':count', this.lastSavedCount));
                        } else if (this.autoShare) {
                            this.showNotice(this.msg.savedAndShared.replace(':count', this.lastSavedCount));
                            this.unsharedCount = 0;
                        } else if (!this.promptDismissed) {
                            this.promptVisible = true;
                        } else {
                            this.showNotice(this.msg.savedNChanges.replace(':count', this.lastSavedCount));
                        }
                    },
                    revertRow(row) {
                        var self = this;
                        if (!confirm(this.msg.revertConfirm)) return;
                        postJson(URLS.revert, { locale: this.locale, group: this.group, keys: [row.key] })
                            .then(function () {
                                self.rebuildRows(Object.fromEntries([[row.key, '']]));
                                delete self.drafts[row.key];
                                delete self.warnings[row.key];
                            })
                            .catch(function () {
                                self.showNotice(self.msg.saveFailed, 'error');
                            });
                    },
                    showNotice(text, tone, retry) {
                        var self = this;
                        this.promptVisible = false;
                        this.notice = { text: text, tone: tone || 'success', retry: retry || null };
                        clearTimeout(this.noticeTimer);
                        if (!retry) {
                            this.noticeTimer = setTimeout(function () { self.notice = null; }, 5000);
                        }
                    },
                    retryNotice() {
                        var retry = this.notice && this.notice.retry;
                        this.notice = null;
                        if (retry) retry();
                    },
                    shareRequest(hashes) {
                        var body = hashes && hashes.length ? { hashes: hashes } : {};
                        return postJson(URLS.share, body);
                    },
                    shareNow() {
                        var self = this;
                        var hashes = this.lastSavedHashes;
                        this.promptVisible = false;
                        this.shareRequest(hashes)
                            .then(function (data) { self.handleShareResult(data); })
                            .catch(function () {
                                self.showNotice(self.msg.shareUnreachable, 'error', function () { self.shareNow(); });
                            });
                    },
                    alwaysShare() {
                        var self = this;
                        this.promptVisible = false;
                        postJson(URLS.autoShare, { enabled: true })
                            .then(function () {
                                self.autoShare = true;
                                return self.shareRequest(null);
                            })
                            .then(function (data) { self.handleShareResult(data); })
                            .catch(function () {
                                self.showNotice(self.msg.shareUnreachable, 'error', function () { self.alwaysShare(); });
                            });
                    },
                    notNow() {
                        sessionStorage.setItem('es-tr-share-not-now', '1');
                        this.promptDismissed = true;
                        this.promptVisible = false;
                        this.showNotice(this.msg.savedNChanges.replace(':count', this.lastSavedCount));
                    },
                    handleShareResult(data) {
                        this.unsharedCount = data.remaining;
                        if (data.failed) {
                            var self = this;
                            this.showNotice(this.msg.shareUnreachable, 'error', function () { self.shareNow(); });
                        } else {
                            this.showNotice(this.msg.sharedThankYou.replace(':count', data.shared));
                        }
                    },
                    openShareModal() {
                        var self = this;
                        this.showShareModal = true;
                        this.shareError = false;
                        this.shareLoading = true;
                        this.shareRows = [];
                        fetch(URLS.unshared, { headers: { 'Accept': 'application/json' } })
                            .then(function (r) { if (!r.ok) throw r; return r.json(); })
                            .then(function (data) {
                                self.shareRows = data.rows.map(function (r) {
                                    r.checked = true;
                                    return r;
                                });
                                self.shareLoading = false;
                                self.$nextTick(function () {
                                    if (self.$refs.shareModalClose) self.$refs.shareModalClose.focus();
                                });
                            })
                            .catch(function () {
                                self.shareLoading = false;
                                self.shareError = true;
                            });
                    },
                    closeShareModal() {
                        this.showShareModal = false;
                        this.shareError = false;
                    },
                    shareSelected() {
                        var self = this;
                        var hashes = this.shareRows.filter(function (r) { return r.checked; }).map(function (r) { return r.hash; });
                        if (!hashes.length || this.sharing) return;
                        this.sharing = true;
                        this.shareError = false;
                        this.shareRequest(hashes)
                            .then(function (data) {
                                self.sharing = false;
                                // Keep the count in sync even on a mid-way failure:
                                // already-sent chunks were stamped shared. Retrying
                                // is safe - the server skips ids already shared.
                                self.unsharedCount = data.remaining;
                                if (data.failed) {
                                    self.shareError = true;
                                    return;
                                }
                                self.closeShareModal();
                                self.handleShareResult(data);
                            })
                            .catch(function () {
                                self.sharing = false;
                                self.shareError = true;
                            });
                    },
                    toggleAutoShare() {
                        var self = this;
                        var next = !this.autoShare;
                        this.autoShare = next;
                        postJson(URLS.autoShare, { enabled: next })
                            .catch(function () {
                                self.autoShare = !next;
                                self.showNotice(self.msg.saveFailed, 'error');
                            });
                    },
                    phpEscape(value) {
                        return String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
                    },
                    copyAsPhp() {
                        var self = this;
                        var lines = this.rows
                            .filter(function (r) { return r.override !== null; })
                            .map(function (r) { return "    '" + self.phpEscape(r.key) + "' => '" + self.phpEscape(r.override) + "',"; });
                        if (!lines.length || !navigator.clipboard) return;
                        navigator.clipboard.writeText(lines.join('\n')).then(function () {
                            self.copiedPhp = true;
                            setTimeout(function () { self.copiedPhp = false; }, 1500);
                        }).catch(function () {});
                    },
                    clearFilters() {
                        this.searchQuery = '';
                        this.activeQuery = '';
                        this.statusFilter = 'all';
                        this.page = 1;
                    },
                    prevPage() {
                        if (this.page > 1) this.page--;
                    },
                    nextPage() {
                        if (this.page < this.totalPages) this.page++;
                    },
                },
            }).mount('#translations-app');
        });
    </script>
</x-app-admin-layout>
