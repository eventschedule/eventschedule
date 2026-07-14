<x-app-admin-layout>
    <style {!! nonce_attr() !!}>[v-cloak] { display: none; }</style>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'translations'])

        {{--
            Suggestion values are untrusted remote input: they are only ever
            rendered through Vue interpolation (escaped), and the MSG-object
            pattern keeps translatable UI text out of Vue's template compiler.
        --}}
        <div id="suggestions-app" class="space-y-4" v-cloak>

            {{-- Toolbar --}}
            <div class="ap-card rounded-xl p-4 sm:p-6">
                <div class="mb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@{{ msg.title }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.intro }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <a href="{{ route('admin.translations') }}"
                            class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">
                            @{{ msg.back }}
                        </a>
                        <button type="button" @click="copyApprovedAsPhp" :disabled="!canCopyApproved"
                            :title="canCopyApproved ? msg.copyApprovedAsPhp : msg.copyApprovedHint"
                            class="inline-flex items-center px-4 py-3 text-base font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-transparent hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg v-if="!copiedPhp" class="w-5 h-5 me-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                            </svg>
                            <svg v-else class="w-5 h-5 me-1.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            @{{ copiedPhp ? msg.copied : msg.copyApprovedAsPhp }}
                        </button>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="w-full lg:w-44">
                        <label for="sg-status" class="sr-only">@{{ msg.status }}</label>
                        <select id="sg-status" v-model="statusFilter" @change="loadData"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option value="pending">@{{ msg.pending }}</option>
                            <option value="approved">@{{ msg.approved }}</option>
                            <option value="rejected">@{{ msg.rejected }}</option>
                            <option value="all">@{{ msg.all }}</option>
                        </select>
                    </div>
                    <div class="w-full lg:w-56">
                        <label for="sg-locale" class="sr-only">@{{ msg.language }}</label>
                        <select id="sg-locale" v-model="localeFilter" @change="loadData"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option value="">@{{ msg.allLanguages }}</option>
                            <option v-for="l in locales" :key="l.code" :value="l.code">@{{ l.label }}</option>
                        </select>
                    </div>
                    <div class="w-full lg:w-44">
                        <label for="sg-group" class="sr-only">@{{ msg.file }}</label>
                        <select id="sg-group" v-model="groupFilter" @change="loadData"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <option value="">@{{ msg.allFiles }}</option>
                            <option v-for="g in groups" :key="g" :value="g">@{{ g }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label for="sg-search" class="sr-only">@{{ msg.search }}</label>
                        <input id="sg-search" v-model.trim="searchQuery" type="search" autocomplete="off"
                            placeholder="{{ __('messages.search_keys_and_text') }}"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    </div>
                </div>
            </div>

            {{-- Bulk action bar --}}
            <div v-if="selectedCount > 0" class="ap-card rounded-xl p-3 flex items-center justify-between gap-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@{{ msg.nSelected.replace(':count', selectedCount) }}</span>
                <div class="flex gap-2">
                    <button type="button" @click="bulkReview('reject')" :disabled="acting"
                        class="px-4 py-2 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 disabled:opacity-50">
                        @{{ msg.rejectSelected }}
                    </button>
                    <button type="button" @click="bulkReview('approve')" :disabled="acting"
                        class="px-4 py-2 text-sm font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 disabled:opacity-50">
                        @{{ msg.approveSelected }}
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="ap-card rounded-xl shadow overflow-hidden">
                <div v-if="loading" class="p-12 text-center" role="status">
                    <svg class="animate-spin h-8 w-8 mx-auto text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">@{{ msg.loading }}</p>
                </div>

                <div v-else-if="loadError" class="p-6">
                    <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-center justify-between gap-4">
                        <span class="text-sm text-red-700 dark:text-red-300">@{{ msg.loadFailed }}</span>
                        <button type="button" @click="loadData"
                            class="px-3 py-1.5 text-sm font-medium rounded-lg border border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-200">
                            @{{ msg.tryAgain }}
                        </button>
                    </div>
                </div>

                <div v-else-if="filteredGroups.length === 0" class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">@{{ msg.noSuggestions }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ msg.noSuggestionsHint }}</p>
                </div>

                <template v-else>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 w-10">
                                        <input type="checkbox" ref="selectAll" :checked="allVisibleSelected" @change="toggleSelectAll"
                                            :aria-label="msg.nSelected.replace(':count', '')"
                                            class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    </th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">@{{ msg.key }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@{{ msg.shippedText }} / @{{ msg.suggestedText }}</th>
                                    <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">@{{ msg.status }}</th>
                                    <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-56">@{{ msg.actions }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="row in pagedGroups" :key="row.hash" class="align-top">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" v-model="selected[row.hash]" :aria-label="row.key"
                                            :disabled="row.status !== 'pending'"
                                            class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] disabled:opacity-40">
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs text-gray-900 dark:text-gray-100 break-all" dir="ltr">@{{ row.key }}</span>
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-400">@{{ row.locale }} &middot; @{{ row.group }}</span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                                :title="msg.suggestedByNInstalls.replace(':count', row.instance_count)">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                                </svg>
                                                @{{ row.instance_count }}
                                            </span>
                                            <span v-if="row.app_versions.length" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-400"
                                                :title="msg.appVersions">@{{ row.app_versions.join(', ') }}</span>
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span v-if="row.warnings.html" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300" :title="msg.htmlWarning">HTML</span>
                                            <span v-if="hasQualityWarning(row)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300" :title="qualityWarningText(row)">@{{ qualityWarningLabel(row) }}</span>
                                            <span v-if="isOutdated(row)" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300" :title="msg.mayBeOutdated">@{{ msg.outdated }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <div class="rounded-lg bg-gray-50 dark:bg-[#252526] p-2.5">
                                                <p class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">@{{ msg.shippedText }}</p>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words" dir="auto">@{{ row.nexus_shipped || row.en }}</p>
                                                <p v-if="row.nexus_override" class="mt-1 text-xs text-gray-400 dark:text-gray-500 whitespace-pre-wrap break-words" dir="auto">@{{ msg.currentOverride }}: @{{ row.nexus_override }}</p>
                                            </div>
                                            <div class="rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-900 p-2.5">
                                                <p class="text-[10px] uppercase tracking-wider text-green-600 dark:text-green-400 mb-1">@{{ msg.suggestedText }}</p>
                                                <template v-if="editingHash !== row.hash">
                                                    <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words" dir="auto">@{{ row.suggested }}</p>
                                                </template>
                                                <template v-else>
                                                    <textarea v-model="editDraft" rows="3" dir="auto" ref="editArea"
                                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm"></textarea>
                                                    <div class="mt-2 flex justify-end gap-2">
                                                        <button type="button" @click="cancelEdit"
                                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200">@{{ msg.cancel }}</button>
                                                        <button type="button" @click="approveRow(row, editDraft)" :disabled="acting"
                                                            class="px-3 py-1.5 text-xs font-medium rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 disabled:opacity-50">@{{ msg.approve }}</button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="{
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300': row.status === 'pending',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': row.status === 'approved',
                                            'bg-gray-100 text-gray-800 dark:bg-[#2d2d30] dark:text-gray-300': row.status === 'rejected',
                                        }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            @{{ statusLabel(row.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-end whitespace-nowrap">
                                        <template v-if="row.status === 'pending' && editingHash !== row.hash">
                                            <button type="button" @click="rejectRow(row)" :disabled="acting"
                                                class="text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 me-3 disabled:opacity-50">@{{ msg.reject }}</button>
                                            <button type="button" @click="startEdit(row)" :disabled="acting"
                                                class="text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 me-3 disabled:opacity-50">@{{ msg.editAndApprove }}</button>
                                            <button type="button" @click="approveRow(row)" :disabled="acting"
                                                class="text-sm font-medium text-[var(--brand-blue)] hover:underline disabled:opacity-50">@{{ msg.approve }}</button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400" aria-live="polite">@{{ showingText }}</p>
                        <div class="flex gap-2">
                            <button type="button" @click="page > 1 && page--" :disabled="page === 1"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                @{{ msg.previous }}
                            </button>
                            <button type="button" @click="page < totalPages && page++" :disabled="page >= totalPages"
                                class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d2d30] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                @{{ msg.next }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @php
        // Built here rather than inline in @json(): Blade's directive-argument
        // parser mis-handles large multi-line arrays.
        $trSuggestionMsg = [
            'title' => __('messages.translation_suggestions'),
            'intro' => __('messages.translation_suggestions_intro'),
            'back' => __('messages.back'),
            'key' => __('messages.translation_key'),
            'shippedText' => __('messages.shipped_text'),
            'suggestedText' => __('messages.suggested_text'),
            'currentOverride' => __('messages.customized'),
            'status' => __('messages.status'),
            'actions' => __('messages.actions'),
            'language' => __('messages.language'),
            'search' => __('messages.search'),
            'pending' => __('messages.pending'),
            'approved' => __('messages.approved'),
            'rejected' => __('messages.rejected'),
            'all' => __('messages.all'),
            'allLanguages' => __('messages.all_languages'),
            'approve' => __('messages.approve'),
            'reject' => __('messages.reject'),
            'editAndApprove' => __('messages.edit_and_approve'),
            'cancel' => __('messages.cancel'),
            'nSelected' => __('messages.n_selected'),
            'approveSelected' => __('messages.approve_selected'),
            'rejectSelected' => __('messages.reject_selected'),
            'rejectConfirm' => __('messages.reject_suggestions_confirm'),
            'approveConfirm' => __('messages.approve_suggestions_confirm'),
            'copyApprovedAsPhp' => __('messages.copy_approved_as_php'),
            'copyApprovedHint' => __('messages.copy_approved_hint'),
            'copied' => __('messages.copied'),
            'file' => __('messages.translation_file'),
            'allFiles' => __('messages.all_files'),
            'suggestedByNInstalls' => __('messages.suggested_by_n_installs'),
            'appVersions' => __('messages.app_versions'),
            'loading' => __('messages.loading'),
            'loadFailed' => __('messages.translations_load_failed'),
            'tryAgain' => __('messages.try_again'),
            'noSuggestions' => __('messages.no_suggestions_found'),
            'noSuggestionsHint' => __('messages.no_suggestions_hint'),
            'previous' => __('messages.previous'),
            'next' => __('messages.next'),
            'showingXOfY' => __('messages.showing_x_of_y'),
            'placeholderWarning' => __('messages.translation_placeholder_warning'),
            'pluralWarning' => __('messages.translation_plural_warning'),
            'htmlWarning' => __('messages.html_warning'),
            'mayBeOutdated' => __('messages.may_be_outdated'),
            'outdated' => __('messages.may_be_outdated'),
            'actionFailed' => __('messages.an_error_occurred'),
        ];
        $trSuggestionLocales = collect(config('app.supported_languages'))
            ->map(fn ($name, $code) => ['code' => $code, 'label' => ucfirst(__('messages.'.$name)).' ('.$code.')'])
            ->values();
    @endphp
    <script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var MSG = @json($trSuggestionMsg);
            var LOCALES = @json($trSuggestionLocales);
            var GROUPS = @json(\App\Services\TranslationOverrideService::GROUPS);
            var URLS = {
                data: @json(route('admin.translations.suggestions.data')),
                bulk: @json(route('admin.translations.suggestions.bulk')),
                approve: @json(route('admin.translations.suggestions.approve', ['hash' => '__HASH__'])),
                reject: @json(route('admin.translations.suggestions.reject', ['hash' => '__HASH__'])),
                export: @json(route('admin.translations.suggestions.export')),
            };
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
                        statusFilter: 'pending',
                        localeFilter: '',
                        groupFilter: '',
                        searchQuery: '',
                        activeQuery: '',
                        searchTimer: null,
                        groupsList: [],
                        selected: {},
                        editingHash: null,
                        editDraft: '',
                        loading: true,
                        loadError: false,
                        acting: false,
                        page: 1,
                        perPage: 50,
                        copiedPhp: false,
                    };
                },
                computed: {
                    filteredGroups() {
                        if (!this.activeQuery) return this.groupsList;
                        var q = this.activeQuery;
                        return this.groupsList.filter(function (r) { return r._search.indexOf(q) !== -1; });
                    },
                    pagedGroups() {
                        return this.filteredGroups.slice((this.page - 1) * this.perPage, this.page * this.perPage);
                    },
                    totalPages() {
                        return Math.max(1, Math.ceil(this.filteredGroups.length / this.perPage));
                    },
                    showingText() {
                        return this.msg.showingXOfY
                            .replace(':shown', this.pagedGroups.length)
                            .replace(':total', this.filteredGroups.length);
                    },
                    selectedHashes() {
                        var selected = this.selected;
                        return Object.keys(selected).filter(function (hash) { return selected[hash]; });
                    },
                    selectedCount() {
                        return this.selectedHashes.length;
                    },
                    selectablePending() {
                        return this.filteredGroups.filter(function (r) { return r.status === 'pending'; });
                    },
                    allVisibleSelected() {
                        var selected = this.selected;
                        return this.selectablePending.length > 0
                            && this.selectablePending.every(function (r) { return selected[r.hash]; });
                    },
                    canCopyApproved() {
                        // A lang file is per (locale, file), so both must be chosen
                        // to produce paste-ready lines for a single file.
                        return this.statusFilter === 'approved' && this.localeFilter !== '' && this.groupFilter !== '' && this.filteredGroups.length > 0;
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
                    selectedCount() {
                        this.syncIndeterminate();
                    },
                    // allVisibleSelected can change without selectedCount changing
                    // (e.g. broadening the search reveals more pending rows).
                    allVisibleSelected() {
                        this.syncIndeterminate();
                    },
                    filteredGroups() {
                        if (this.page > this.totalPages) this.page = this.totalPages;
                    },
                },
                mounted() {
                    this.loadData();
                },
                methods: {
                    loadData() {
                        var self = this;
                        this.loading = true;
                        this.loadError = false;
                        var params = new URLSearchParams();
                        if (this.statusFilter) params.set('status', this.statusFilter);
                        if (this.localeFilter) params.set('locale', this.localeFilter);
                        if (this.groupFilter) params.set('group', this.groupFilter);
                        fetch(URLS.data + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                            .then(function (r) { if (!r.ok) throw r; return r.json(); })
                            .then(function (data) {
                                self.groupsList = Object.freeze(data.rows.map(function (r) {
                                    r._search = (r.key + '\n' + (r.suggested || '') + '\n' + (r.nexus_shipped || '') + '\n' + (r.shipped || '')).toLowerCase();
                                    return Object.freeze(r);
                                }));
                                self.selected = {};
                                self.editingHash = null;
                                self.page = 1;
                                self.loading = false;
                            })
                            .catch(function () {
                                self.loading = false;
                                self.loadError = true;
                            });
                    },
                    statusLabel(status) {
                        if (status === 'pending') return this.msg.pending;
                        if (status === 'approved') return this.msg.approved;
                        return this.msg.rejected;
                    },
                    hasQualityWarning(row) {
                        var quality = row.warnings.quality;
                        return quality && ((quality.placeholders && quality.placeholders.length) || quality.plural);
                    },
                    qualityWarningLabel(row) {
                        var quality = row.warnings.quality;
                        if (quality.placeholders && quality.placeholders.length) return quality.placeholders.join(', ');
                        return '| |';
                    },
                    qualityWarningText(row) {
                        var quality = row.warnings.quality;
                        var parts = [];
                        if (quality.placeholders && quality.placeholders.length) {
                            parts.push(this.msg.placeholderWarning.replace(':tokens', quality.placeholders.join(', ')));
                        }
                        if (quality.plural) {
                            parts.push(this.msg.pluralWarning);
                        }
                        return parts.join(' ');
                    },
                    isOutdated(row) {
                        return row.shipped !== null && row.nexus_shipped !== null && row.shipped !== row.nexus_shipped;
                    },
                    startEdit(row) {
                        var self = this;
                        this.editingHash = row.hash;
                        this.editDraft = row.suggested;
                        this.$nextTick(function () {
                            var area = self.$refs.editArea;
                            var el = Array.isArray(area) ? area[0] : area;
                            if (el) el.focus();
                        });
                    },
                    cancelEdit() {
                        this.editingHash = null;
                        this.editDraft = '';
                    },
                    applyLocalStatus(hashes, status) {
                        // When a status filter is active, reviewed rows leave the
                        // list; otherwise they stay with their new status.
                        var set = {};
                        hashes.forEach(function (hash) { set[hash] = true; });
                        if (this.statusFilter !== 'all') {
                            this.groupsList = Object.freeze(this.groupsList.filter(function (r) { return !set[r.hash]; }));
                        } else {
                            this.groupsList = Object.freeze(this.groupsList.map(function (r) {
                                return set[r.hash] ? Object.freeze(Object.assign({}, r, { status: status })) : r;
                            }));
                        }
                        var selected = this.selected;
                        hashes.forEach(function (hash) { delete selected[hash]; });
                    },
                    approveRow(row, editedValue) {
                        var self = this;
                        if (this.acting) return;
                        this.acting = true;
                        var body = editedValue !== undefined && editedValue !== row.suggested ? { value: editedValue } : {};
                        postJson(URLS.approve.replace('__HASH__', row.hash), body)
                            .then(function () {
                                self.acting = false;
                                self.cancelEdit();
                                self.applyLocalStatus([row.hash], 'approved');
                            })
                            .catch(function () {
                                self.acting = false;
                                alert(self.msg.actionFailed);
                            });
                    },
                    rejectRow(row) {
                        var self = this;
                        if (this.acting) return;
                        if (!confirm(this.msg.rejectConfirm.replace(':count', 1))) return;
                        this.acting = true;
                        postJson(URLS.reject.replace('__HASH__', row.hash), {})
                            .then(function () {
                                self.acting = false;
                                self.applyLocalStatus([row.hash], 'rejected');
                            })
                            .catch(function () {
                                self.acting = false;
                                alert(self.msg.actionFailed);
                            });
                    },
                    bulkReview(action) {
                        var self = this;
                        var hashes = this.selectedHashes;
                        if (!hashes.length || this.acting) return;
                        var confirmText = action === 'approve' ? this.msg.approveConfirm : this.msg.rejectConfirm;
                        if (!confirm(confirmText.replace(':count', hashes.length))) return;

                        // The server caps each request at 100, so process the full
                        // selection in sequential batches rather than silently dropping.
                        var batches = [];
                        for (var i = 0; i < hashes.length; i += 100) {
                            batches.push(hashes.slice(i, i + 100));
                        }
                        var status = action === 'approve' ? 'approved' : 'rejected';
                        this.acting = true;

                        var runBatch = function (index) {
                            if (index >= batches.length) {
                                self.acting = false;
                                return;
                            }
                            postJson(URLS.bulk, { action: action, hashes: batches[index] })
                                .then(function () {
                                    self.applyLocalStatus(batches[index], status);
                                    runBatch(index + 1);
                                })
                                .catch(function () {
                                    self.acting = false;
                                    alert(self.msg.actionFailed);
                                });
                        };
                        runBatch(0);
                    },
                    syncIndeterminate() {
                        var self = this;
                        this.$nextTick(function () {
                            if (self.$refs.selectAll) {
                                self.$refs.selectAll.indeterminate = self.selectedCount > 0 && !self.allVisibleSelected;
                            }
                        });
                    },
                    toggleSelectAll() {
                        var next = !this.allVisibleSelected;
                        var selected = this.selected;
                        this.selectablePending.forEach(function (r) {
                            if (next) {
                                selected[r.hash] = true;
                            } else {
                                delete selected[r.hash];
                            }
                        });
                    },
                    copyApprovedAsPhp() {
                        var self = this;
                        if (!this.canCopyApproved || !navigator.clipboard) return;
                        // Use the server endpoint: it scopes to the selected file,
                        // exports the live override value, and omits reverted keys -
                        // avoiding the group-mixing and stale-value pitfalls of
                        // rebuilding the lines client-side.
                        var url = URLS.export + '?locale=' + encodeURIComponent(this.localeFilter) + '&group=' + encodeURIComponent(this.groupFilter);
                        var fetched = fetch(url, { headers: { 'Accept': 'text/plain' } })
                            .then(function (r) { if (!r.ok) throw r; return r.blob(); });
                        // Pass the fetch promise INTO the clipboard write (ClipboardItem
                        // deferred promise) so the write stays tied to the click gesture -
                        // a plain writeText after an awaited fetch is rejected on Safari.
                        var write;
                        if (window.ClipboardItem) {
                            write = navigator.clipboard.write([new ClipboardItem({ 'text/plain': fetched })]);
                        } else {
                            write = fetched.then(function (b) { return b.text(); })
                                .then(function (text) { return navigator.clipboard.writeText(text); });
                        }
                        write.then(function () {
                            self.copiedPhp = true;
                            setTimeout(function () { self.copiedPhp = false; }, 1500);
                        }).catch(function () {});
                    },
                },
            }).mount('#suggestions-app');
        });
    </script>
</x-app-admin-layout>
