<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* A small button that destroys something: the weight of .page-tool, in red. */
            .page-tool.is-danger {
              border-color: rgba(220, 38, 38, 0.4);
              color: #b91c1c;
            }
            .page-tool.is-danger:hover {
              border-color: #dc2626;
              background: rgba(220, 38, 38, 0.08);
              color: #b91c1c;
            }
            .dark .page-tool.is-danger,
            .dark .page-tool.is-danger:hover {
              border-color: rgba(248, 113, 113, 0.5);
              color: #f87171;
            }
            .tr-table td {
              vertical-align: top;
            }
            .tr-table .c-key {
              width: 22%;
            }
            .tr-table .c-check {
              width: 2.5rem;
            }
            .tr-chips {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              gap: 0.25rem 0.375rem;
              margin-top: 0.375rem;
            }
            .tr-chips .event-chip {
              display: inline-flex;
              align-items: center;
              gap: 0.25rem;
              margin-inline-start: 0;
            }
            .tr-chips .event-chip svg {
              width: 0.75rem;
              height: 0.75rem;
            }
            /* What ships today beside what was suggested. */
            .sg-pair {
              display: grid;
              grid-template-columns: repeat(2, minmax(0, 1fr));
              gap: 0.5rem;
            }
            .sg-box {
              border: 1px solid transparent;
              border-radius: 0.5rem;
              padding: 0.625rem;
              background: var(--ap-tint-1);
            }
            .sg-box.is-new {
              border-color: rgba(34, 197, 94, 0.35);
              background: rgba(34, 197, 94, 0.08);
            }
            .sg-box > span {
              display: block;
              margin-bottom: 0.25rem;
              font-size: 0.6875rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .sg-box.is-new > span {
              color: #15803d;
            }
            .dark .sg-box.is-new > span {
              color: #4ade80;
            }
            .sg-box p {
              margin: 0;
              white-space: pre-wrap;
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink));
            }
            .sg-box p + p {
              margin-top: 0.25rem;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            .tr-foot,
            .tr-bar {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.5rem 1rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .tr-foot {
              border-top: 1px solid rgb(var(--ap-border));
              padding: 0.75rem 1.25rem;
            }
            .tr-bar {
              padding: 0.75rem 1.25rem;
              font-size: 0.875rem;
              font-weight: 500;
              color: rgb(var(--ap-ink-2));
            }
            .tr-state {
              padding: 3rem 1.5rem;
              text-align: center;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
            @media (max-width: 639.98px) {
              .sg-pair {
                grid-template-columns: minmax(0, 1fr);
              }
              .tr-table td.c-pair {
                flex: 1 1 100%;
              }
              /* The tick box rides beside the key. */
              .tr-table .c-main {
                flex: 1 1 0;
              }
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'translations'])

    {{--
        Suggestion values are untrusted remote input: they are only ever
        rendered through Vue interpolation (escaped), and the MSG-object
        pattern keeps translatable UI text out of Vue's template compiler.
        So the page's head is the kit's title row written out (the page-header
        component would print its title as a text node inside the mount): this
        page hangs from Translations, and the way back names it.
    --}}
    <div id="suggestions-app" v-cloak>

        <div class="page-top">
            <div class="page-top-text">
                <a href="{{ route('admin.translations') }}" class="page-back">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    <span>@{{ msg.back }}</span>
                </a>
                <h2 class="page-title">@{{ msg.title }}</h2>
                <p class="page-lead">@{{ msg.intro }}</p>
            </div>
            <div class="page-actions">
                <button type="button" @click="copyApprovedAsPhp" :disabled="!canCopyApproved"
                    :title="canCopyApproved ? msg.copyApprovedAsPhp : msg.copyApprovedHint" class="page-tool">
                    <svg v-if="!copiedPhp" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                    </svg>
                    <svg v-else class="text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    @{{ copiedPhp ? msg.copied : msg.copyApprovedAsPhp }}
                </button>
            </div>
        </div>

        <div class="page-shell page-stack">
            {{-- Filters --}}
            <div class="page-filters" style="margin-bottom: 0">
                <label class="page-filter">
                    <span>@{{ msg.status }}</span>
                    <select id="sg-status" v-model="statusFilter" @change="loadData"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                        <option value="pending">@{{ msg.pending }}</option>
                        <option value="approved">@{{ msg.approved }}</option>
                        <option value="rejected">@{{ msg.rejected }}</option>
                        <option value="all">@{{ msg.all }}</option>
                    </select>
                </label>
                <label class="page-filter">
                    <span>@{{ msg.language }}</span>
                    <select id="sg-locale" v-model="localeFilter" @change="loadData"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                        <option value="">@{{ msg.allLanguages }}</option>
                        <option v-for="l in locales" :key="l.code" :value="l.code">@{{ l.label }}</option>
                    </select>
                </label>
                <label class="page-filter">
                    <span>@{{ msg.file }}</span>
                    <select id="sg-group" v-model="groupFilter" @change="loadData"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                        <option value="">@{{ msg.allFiles }}</option>
                        <option v-for="g in groups" :key="g" :value="g">@{{ g }}</option>
                    </select>
                </label>
                <label class="page-filter is-grow">
                    <span>@{{ msg.search }}</span>
                    <input id="sg-search" v-model.trim="searchQuery" type="search" autocomplete="off"
                        placeholder="{{ __('messages.search_keys_and_text') }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                </label>
            </div>

            {{-- What acts on every ticked row. Destroying first, forward action last. --}}
            <div v-if="selectedCount > 0" class="ap-card rounded-xl tr-bar">
                <span>@{{ msg.nSelected.replace(':count', selectedCount) }}</span>
                <div class="page-actions">
                    <button type="button" @click="bulkReview('reject')" :disabled="acting" class="page-tool is-danger">@{{ msg.rejectSelected }}</button>
                    <x-brand-button size="sm" @click="bulkReview('approve')" v-bind:disabled="acting">@{{ msg.approveSelected }}</x-brand-button>
                </div>
            </div>

            {{-- Table --}}
            <div class="ap-card rounded-xl overflow-hidden">
                <div v-if="loading" class="tr-state" role="status">
                    <svg class="animate-spin h-8 w-8 mx-auto text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3">@{{ msg.loading }}</p>
                </div>

                <div v-else-if="loadError" class="page-card">
                    <x-page-notice tone="error">
                        @{{ msg.loadFailed }}
                        <x-slot name="action"><button type="button" @click="loadData" class="page-tool">@{{ msg.tryAgain }}</button></x-slot>
                    </x-page-notice>
                </div>

                <div v-else-if="filteredGroups.length === 0" class="page-empty">
                    <svg class="page-empty-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
                    </svg>
                    <h3>@{{ msg.noSuggestions }}</h3>
                    <p>@{{ msg.noSuggestionsHint }}</p>
                </div>

                <template v-else>
                    <table class="page-table tr-table">
                        <thead>
                            <tr>
                                <th scope="col" class="c-check">
                                    <input type="checkbox" ref="selectAll" :checked="allVisibleSelected" @change="toggleSelectAll"
                                        :aria-label="msg.nSelected.replace(':count', '')"
                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                </th>
                                <th scope="col" class="c-key">@{{ msg.key }}</th>
                                <th scope="col">@{{ msg.shippedText }} / @{{ msg.suggestedText }}</th>
                                <th scope="col">@{{ msg.status }}</th>
                                <th scope="col"><span class="sr-only">@{{ msg.actions }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in pagedGroups" :key="row.hash">
                                <td>
                                    <input type="checkbox" v-model="selected[row.hash]" :aria-label="row.key"
                                        :disabled="row.status !== 'pending'"
                                        class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] disabled:opacity-40">
                                </td>
                                <td class="c-main">
                                    <span class="c-mono c-wrap c-strong" dir="ltr">@{{ row.key }}</span>
                                    <div class="tr-chips">
                                        <span class="event-chip">@{{ row.locale }} &middot; @{{ row.group }}</span>
                                        <span class="event-chip" :title="msg.suggestedByNInstalls.replace(':count', row.instance_count)">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                            @{{ row.instance_count }}
                                        </span>
                                        <span v-if="row.app_versions.length" class="event-chip" :title="msg.appVersions" dir="ltr">@{{ row.app_versions.join(', ') }}</span>
                                    </div>
                                    <div v-if="row.warnings.html || hasQualityWarning(row) || isOutdated(row)" class="tr-chips">
                                        <span v-if="row.warnings.html" class="event-status is-bad" :title="msg.htmlWarning">HTML</span>
                                        <span v-if="hasQualityWarning(row)" class="event-status is-warn" :title="qualityWarningText(row)">@{{ qualityWarningLabel(row) }}</span>
                                        <span v-if="isOutdated(row)" class="event-status is-warn" :title="msg.mayBeOutdated">@{{ msg.outdated }}</span>
                                    </div>
                                </td>
                                <td class="c-pair">
                                    <div class="sg-pair">
                                        <div class="sg-box">
                                            <span>@{{ msg.shippedText }}</span>
                                            <p dir="auto">@{{ row.nexus_shipped || row.en }}</p>
                                            <p v-if="row.nexus_override" dir="auto">@{{ msg.currentOverride }}: @{{ row.nexus_override }}</p>
                                        </div>
                                        <div class="sg-box is-new">
                                            <span>@{{ msg.suggestedText }}</span>
                                            <template v-if="editingHash !== row.hash">
                                                <p dir="auto">@{{ row.suggested }}</p>
                                            </template>
                                            <template v-else>
                                                <textarea v-model="editDraft" rows="3" dir="auto" ref="editArea"
                                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm"></textarea>
                                                <div class="mt-2 flex justify-end gap-2">
                                                    <button type="button" @click="cancelEdit" class="page-tool">@{{ msg.cancel }}</button>
                                                    <x-brand-button size="sm" @click="approveRow(row, editDraft)" v-bind:disabled="acting">@{{ msg.approve }}</x-brand-button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="event-status" :class="{ 'is-warn': row.status === 'pending', 'is-on': row.status === 'approved' }">@{{ statusLabel(row.status) }}</span>
                                </td>
                                <td class="c-actions">
                                    <template v-if="row.status === 'pending' && editingHash !== row.hash">
                                        <button type="button" @click="rejectRow(row)" :disabled="acting" class="event-link is-danger">@{{ msg.reject }}</button>
                                        <button type="button" @click="startEdit(row)" :disabled="acting" class="event-link">@{{ msg.editAndApprove }}</button>
                                        <button type="button" @click="approveRow(row)" :disabled="acting" class="event-link">@{{ msg.approve }}</button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- Pagination --}}
                    <div class="tr-foot">
                        <span aria-live="polite">@{{ showingText }}</span>
                        <div class="page-actions">
                            <button type="button" @click="page > 1 && page--" :disabled="page === 1" class="page-tool">@{{ msg.previous }}</button>
                            <button type="button" @click="page < totalPages && page++" :disabled="page >= totalPages" class="page-tool">@{{ msg.next }}</button>
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
            'back' => __('messages.translations'),
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
            'outdated' => __('messages.suggestion_outdated'),
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
                        // r.redirected, not just r.ok: this asks for text/plain, so expectsJson()
                        // is false and a lapsed admin re-auth window answers with a 302 to the
                        // confirm-password page. fetch follows it and hands back a 200 full of
                        // HTML, which .blob() happily accepts - so without this guard the login
                        // page lands in the clipboard under a green "Copied", and gets pasted
                        // into a lang file. Adding X-Requested-With would NOT help: Accept is
                        // text/plain, so acceptsAnyContentType() and wantsJson() are both false.
                        var fetched = fetch(url, { headers: { 'Accept': 'text/plain' } })
                            .then(function (r) { if (!r.ok || r.redirected) throw r; return r.blob(); });
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
                        }).catch(function () {
                            // Was an empty catch, which is how a lapsed re-auth window managed to
                            // look like a successful copy. Same alert the other actions use.
                            alert(self.msg.actionFailed);
                        });
                    },
                },
            }).mount('#suggestions-app');
        });
    </script>
</x-app-admin-layout>
