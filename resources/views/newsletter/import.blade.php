<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        <style {!! nonce_attr() !!}>
            /* Until the page's script has run, the panes that are not showing stay out of sight. */
            #import-emails-app [v-cloak] {
              display: none;
            }
            /* The three ways in, as the kit's tabs inside the card they switch. */
            .news-card-tabs {
              margin: 0;
              padding: 0 1.25rem;
            }
            .news-import-pane {
              padding: 1.25rem;
            }
            .news-choice {
              display: flex;
              align-items: center;
              gap: 0.75rem;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
              cursor: pointer;
            }
            .news-choice-body {
              max-width: 28rem;
              margin-inline-start: 1.75rem;
            }
            .news-field-error {
              margin: 0.25rem 0 0;
              font-size: 0.8125rem;
              color: #b91c1c;
            }
            .dark .news-field-error {
              color: #f87171;
            }
            /* The rows being typed: a name, an address, and the way to take the row away. */
            .news-entry {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              gap: 0.5rem;
              margin-bottom: 0.5rem;
            }
            .news-entry > input {
              flex: 1 1 12rem;
              min-width: 0;
            }
            .news-entry > button {
              flex: none;
              min-width: 4rem;
              text-align: start;
            }
            /* The only row there is cannot be taken away: its button keeps its place in the
               line from a tablet up, and gives its room to the fields on a phone. */
            .news-entry > button.is-off {
              visibility: hidden;
            }
            .news-entry-head {
              display: flex;
              gap: 0.5rem;
              margin-bottom: 0.375rem;
              font-size: 0.75rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .news-entry-head > span {
              flex: 1 1 12rem;
            }
            .news-entry-head > i {
              flex: none;
              min-width: 4rem;
            }
            @media (max-width: 639.98px) {
              .news-entry-head {
                display: none;
              }
              .news-entry {
                margin-bottom: 0.875rem;
              }
              .news-entry > button.is-off {
                display: none;
              }
            }
            .news-drop {
              border: 2px dashed rgb(var(--ap-border-strong));
              border-radius: 0.625rem;
              padding: 1.5rem;
              text-align: center;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
              cursor: pointer;
              transition: border-color 0.2s, background-color 0.2s;
            }
            .news-drop:hover,
            .news-drop.is-over {
              border-color: var(--brand-blue);
              background: var(--ap-tint-1);
            }
            .news-drop svg {
              width: 2rem;
              height: 2rem;
              margin: 0 auto 0.5rem;
              color: rgb(var(--ap-ink-4));
              pointer-events: none;
            }
            .news-map {
              display: grid;
              grid-template-columns: repeat(auto-fill, minmax(min(100%, 12rem), 1fr));
              gap: 0.75rem;
            }
            .news-map > div {
              min-width: 0;
              border: 1px solid rgb(var(--ap-border));
              border-radius: 0.625rem;
              padding: 0.875rem;
              background: var(--ap-tint-1);
            }
            .news-map p {
              margin: 0;
              overflow: hidden;
              text-overflow: ellipsis;
              white-space: nowrap;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
            }
            .news-map p:first-child {
              font-size: 0.75rem;
              font-weight: 600;
              letter-spacing: 0.04em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .news-map select {
              width: 100%;
              margin-top: 0.625rem;
              font-size: 0.875rem;
            }
            .news-preview {
              max-height: 15rem;
              margin: 1rem 0 0;
              border: 1px solid rgb(var(--ap-border));
              border-radius: 0.625rem;
              overflow: auto;
            }
            /* The preview is a file's own columns, as many as it has: it scrolls in its box on a
               phone too, where the kit would stack a list's rows. */
            .news-preview .page-table {
              display: table;
            }
            .news-preview .page-table tbody {
              display: table-row-group;
            }
            .news-preview .page-table thead {
              visibility: visible;
              position: static;
              width: auto;
              height: auto;
              overflow: visible;
              clip-path: none;
            }
            .news-preview .page-table tr {
              display: table-row;
              padding: 0;
            }
            .news-preview .page-table th,
            .news-preview .page-table td {
              border-top: 1px solid rgb(var(--ap-border));
              padding: 0.5rem 0.75rem;
              white-space: nowrap;
            }
            .news-preview .page-table thead th {
              border-top: 0;
            }
            .news-import-foot {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem;
              margin-top: 1.25rem;
            }
            .news-import-foot p {
              margin: 0;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
        </style>
    </x-slot>

    {{-- The Import emails tab: which segment the addresses go into, then the addresses, typed,
         pasted or read from a CSV file. It is one Vue island (it was the portal's last Alpine
         component of its size): nothing somebody typed elsewhere is printed inside the mount, and
         the segments' names reach it as data. --}}
    <div class="page-shell">
        @include('newsletter.partials._section', ['tab' => 'import'])

        <div class="page-head">
            <p class="page-lead">{{ __('messages.newsletter_import_lead') }}</p>
        </div>

        <div id="import-emails-app" class="page-stack">
            {{-- Segment target --}}
            <section class="ap-card rounded-xl page-card">
                <div class="page-card-head">
                    <h2 class="page-card-title">{{ __('messages.select_segment') }}</h2>
                </div>

                <div class="space-y-3">
                    <label class="news-choice">
                        <input type="radio" v-model="segmentTarget" value="new" class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <span>{{ __('messages.create_new_segment') }}</span>
                    </label>
                    <div v-show="segmentTarget === 'new'" class="news-choice-body">
                        <input type="text" v-model="segmentName" dir="auto" autocomplete="off" aria-label="{{ __('messages.name') }}" placeholder="{{ __('messages.name') }}"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        <p v-if="segmentError && segmentTarget === 'new'" class="news-field-error" role="alert">@{{ segmentError }}</p>
                    </div>

                    <template v-if="manualSegments.length">
                        <label class="news-choice">
                            <input type="radio" v-model="segmentTarget" value="existing" class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span>{{ __('messages.add_to_existing_segment') }}</span>
                        </label>
                        <div v-show="segmentTarget === 'existing'" v-cloak class="news-choice-body">
                            <select v-model="segmentId" aria-label="{{ __('messages.select_segment') }}" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <option value="">{{ __('messages.select_segment') }}</option>
                                <option v-for="segment in manualSegments" :key="segment.id" :value="segment.id">@{{ segment.name }}</option>
                            </select>
                            <p v-if="segmentError && segmentTarget === 'existing'" class="news-field-error" role="alert">@{{ segmentError }}</p>
                        </div>
                    </template>
                </div>
            </section>

            {{-- The three ways in --}}
            <section class="ap-card rounded-xl page-card is-flush">
                <div class="ap-tabs-wrap news-card-tabs">
                    <div class="ap-tabs" role="tablist">
                        <button type="button" class="ap-tab" role="tab" id="import-tab-form" aria-controls="import-pane-form" :aria-selected="tab === 'form' ? 'true' : 'false'" @click="tab = 'form'">{{ __('messages.form_entry') }}</button>
                        <button type="button" class="ap-tab" role="tab" id="import-tab-paste" aria-controls="import-pane-paste" :aria-selected="tab === 'paste' ? 'true' : 'false'" @click="tab = 'paste'">{{ __('messages.paste_emails') }}</button>
                        <button type="button" class="ap-tab" role="tab" id="import-tab-csv" aria-controls="import-pane-csv" :aria-selected="tab === 'csv' ? 'true' : 'false'" @click="tab = 'csv'">{{ __('messages.upload_csv') }}</button>
                    </div>
                </div>

                {{-- Form tab --}}
                <div v-show="tab === 'form'" id="import-pane-form" role="tabpanel" aria-labelledby="import-tab-form" class="news-import-pane">
                    <x-page-notice v-if="formErrors.length > 0" v-cloak tone="error" class="news-notice" :title="__('messages.import_validation_failed')">
                        <ul class="news-notice-list">
                            <li v-for="error in formErrors" :key="error">@{{ error }}</li>
                        </ul>
                    </x-page-notice>

                    <div class="news-entry-head" aria-hidden="true">
                        <span>{{ __('messages.name') }}</span>
                        <span>{{ __('messages.email') }}</span>
                        <i></i>
                    </div>
                    <div v-for="(entry, index) in formEntries" :key="index" class="news-entry">
                        <input type="text" v-model="entry.name" dir="auto" autocomplete="off" aria-label="{{ __('messages.name') }}" placeholder="{{ __('messages.name') }}"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        <input type="email" v-model="entry.email" dir="auto" autocomplete="off" aria-label="{{ __('messages.email') }}" placeholder="email@example.com"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        <button type="button" class="event-link is-danger" :class="{ 'is-off': formEntries.length < 2 }" @click="removeFormRow(index)">{{ __('messages.remove') }}</button>
                    </div>

                    <button type="button" class="event-link" @click="addFormRow()">+ {{ __('messages.add_row') }}</button>

                    <p class="page-card-lead mt-3">{{ __('messages.email_required_name_optional') }}</p>

                    <div class="news-import-foot">
                        <p><span>@{{ getValidEntryCount() }}</span> {{ __('messages.emails_to_import') }}</p>
                        <x-brand-button type="button" @click="submitForm()" v-bind:disabled="submitting || getValidEntryCount() === 0">
                            <span v-if="! submitting">{{ __('messages.confirm_import') }}</span>
                            <span v-else v-cloak>{{ __('messages.loading') }}...</span>
                        </x-brand-button>
                    </div>
                </div>

                {{-- Paste tab --}}
                <div v-show="tab === 'paste'" v-cloak id="import-pane-paste" role="tabpanel" aria-labelledby="import-tab-paste" class="news-import-pane">
                    <textarea v-model="pasteText" rows="10" dir="auto" aria-label="{{ __('messages.paste_emails') }}"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                        placeholder="John Smith <john@example.com>&#10;Jane Doe <jane@example.com>&#10;bob@example.com, Bob Johnson&#10;carol@example.com, Carol Smith"></textarea>

                    <div class="news-import-foot">
                        <p>{{ __('messages.import_emails_help') }}</p>
                        <x-brand-button type="button" @click="parsePaste()" v-bind:disabled="! pasteText.trim()">
                            {{ __('messages.parse_emails') }}
                        </x-brand-button>
                    </div>
                </div>

                {{-- CSV tab --}}
                <div v-show="tab === 'csv'" v-cloak id="import-pane-csv" role="tabpanel" aria-labelledby="import-tab-csv" class="news-import-pane">
                    <x-page-notice v-if="csvErrors.length > 0" tone="error" class="news-notice" :title="__('messages.import_validation_failed')">
                        <ul class="news-notice-list">
                            <li v-for="error in csvErrors" :key="error">@{{ error }}</li>
                        </ul>
                    </x-page-notice>

                    <input ref="csvFileInput" type="file" accept=".csv" @change="handleCsvFile($event)" class="hidden" />
                    <div class="news-drop" :class="{ 'is-over': csvDragOver }" role="button" tabindex="0"
                        @click="$refs.csvFileInput.click()"
                        @keydown.enter.prevent="$refs.csvFileInput.click()"
                        @keydown.space.prevent="$refs.csvFileInput.click()"
                        @dragover.prevent="csvDragOver = true"
                        @dragenter.prevent="csvDragOver = true"
                        @dragleave.prevent.self="csvDragOver = false"
                        @drop.prevent="handleCsvDrop($event)">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span v-if="! csvFilename">{{ __('messages.drop_csv_or_click') }}</span>
                        <bdi v-else>@{{ csvFilename }}</bdi>
                    </div>

                    {{-- Column mapping --}}
                    <div v-if="csvHeaders.length > 0">
                        <div class="page-subhead">
                            <h2>{{ __('messages.map_columns') }}</h2>
                            <p>{{ __('messages.row_count') }}: <span>@{{ csvTotalRows }}</span></p>
                        </div>

                        <div class="news-map">
                            <div v-for="(header, index) in csvHeaders" :key="index">
                                <p>@{{ header || '-' }}</p>
                                <p :title="(csvPreview[0] && csvPreview[0][index]) || ''">@{{ (csvPreview[0] && csvPreview[0][index]) || '-' }}</p>
                                <select :value="columnMappings[index]" @change="columnMappings[index] = $event.target.value" :aria-label="header"
                                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                    <option value="skip">{{ __('messages.skip_column') }}</option>
                                    <option value="email">{{ __('messages.email') }}</option>
                                    <option value="name">{{ __('messages.name') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="news-preview">
                            <table class="page-table">
                                <thead>
                                    <tr>
                                        <th v-for="(h, i) in csvHeaders" :key="'hh-' + i" scope="col">@{{ h }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, ri) in csvPreview" :key="'rr-' + ri">
                                        <td v-for="(cell, ci) in row" :key="'cc-' + ci">@{{ cell }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="page-form-actions">
                            <x-brand-button type="button" @click="loadCsvIntoForm()" v-bind:disabled="! hasEmailColumn()">
                                {{ __('messages.next') }}
                            </x-brand-button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Built here and handed over as one plain variable: with a comma inside its brackets the
         @json directive reads the rest as its own options and drops the escaping of tags, and a
         segment named with an opening comment or script tag ended the script block early. --}}
    @php
        $manualSegmentOptions = $manualSegments->map(fn ($segment) => ['id' => \App\Utils\UrlUtils::encodeId($segment->id), 'name' => $segment->name])->values()->all();
        // "Row :row: :error" with both places kept for the page to fill. It was asked for with
        // an empty row, so the number was gone before the page could put it in ("Row : ...").
        $rowErrorWords = __('messages.row_error', ['row' => ':row', 'error' => ':error']);
    @endphp
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            Vue.createApp({
                data() {
                    return {
                        tab: 'form',
                        segmentTarget: 'new',
                        segmentName: '',
                        segmentId: '',
                        // The schedule's manual segments, as data: a name printed into the mount
                        // would be compiled as a template.
                        manualSegments: @json($manualSegmentOptions),
                        pasteText: '',
                        csvHeaders: [],
                        csvPreview: [],
                        csvAllRows: [],
                        csvTotalRows: 0,
                        csvFilename: '',
                        csvDragOver: false,
                        columnMappings: [],
                        submitting: false,
                        formEntries: [{ name: '', email: '' }],
                        formErrors: [],
                        csvErrors: [],
                        segmentError: '',
                    };
                },
                methods: {
                    handleCsvFile(event) {
                        const file = event.target.files[0];
                        if (file) this.readCsvFile(file);
                    },
                    handleCsvDrop(event) {
                        this.csvDragOver = false;
                        const file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
                        if (!file) return;
                        if (!/\.csv$/i.test(file.name) && file.type !== 'text/csv') return;
                        this.readCsvFile(file);
                    },
                    readCsvFile(file) {
                        if (file.size > 10 * 1024 * 1024) {
                            alert(@json(__('messages.file_too_large')));
                            return;
                        }
                        this.csvFilename = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const text = e.target.result;
                            const rows = this.parseCsv(text);
                            if (rows.length < 2) return;

                            this.csvHeaders = rows[0];
                            this.csvAllRows = rows.slice(1).filter(row => row.some(cell => cell.trim()));
                            this.csvTotalRows = this.csvAllRows.length;
                            this.csvPreview = this.csvAllRows.slice(0, 5);

                            // Auto-detect column mappings
                            this.columnMappings = this.csvHeaders.map(header => {
                                const h = header.toLowerCase().trim();
                                if (h.includes('email') || h === 'e-mail') return 'email';
                                if (h.includes('name') || h === 'first_name' || h === 'last_name') return 'name';
                                return 'skip';
                            });
                        };
                        reader.readAsText(file);
                    },

                    parseCsv(text) {
                        const rows = [];
                        let current = [];
                        let cell = '';
                        let inQuotes = false;

                        for (let i = 0; i < text.length; i++) {
                            const ch = text[i];
                            const next = text[i + 1];

                            if (inQuotes) {
                                if (ch === '"' && next === '"') {
                                    cell += '"';
                                    i++;
                                } else if (ch === '"') {
                                    inQuotes = false;
                                } else {
                                    cell += ch;
                                }
                            } else {
                                if (ch === '"') {
                                    inQuotes = true;
                                } else if (ch === ',') {
                                    current.push(cell.trim());
                                    cell = '';
                                } else if (ch === '\n' || (ch === '\r' && next === '\n')) {
                                    current.push(cell.trim());
                                    if (current.some(c => c)) rows.push(current);
                                    current = [];
                                    cell = '';
                                    if (ch === '\r') i++;
                                } else {
                                    cell += ch;
                                }
                            }
                        }
                        // Last row
                        if (cell || current.length) {
                            current.push(cell.trim());
                            if (current.some(c => c)) rows.push(current);
                        }

                        return rows;
                    },

                    hasEmailColumn() {
                        return this.columnMappings.includes('email');
                    },

                    addFormRow() {
                        this.formEntries.push({ name: '', email: '' });
                    },

                    removeFormRow(index) {
                        this.formEntries.splice(index, 1);
                    },

                    submitForm() {
                        this.formErrors = [];
                        const entries = [];
                        const seen = {};

                        this.formEntries.forEach((entry, i) => {
                            const email = (entry.email || '').trim().toLowerCase();
                            const name = (entry.name || '').trim();

                            // Skip completely empty rows
                            if (!email && !name) return;

                            const rowNum = i + 1;
                            if (!email) {
                                this.formErrors.push(@json($rowErrorWords).replace(':row', rowNum).replace(':error', @json(__('messages.email_required'))));
                            } else if (!this.isValidEmail(email)) {
                                this.formErrors.push(@json($rowErrorWords).replace(':row', rowNum).replace(':error', @json(__('messages.invalid_email'))));
                            } else if (seen[email]) {
                                this.formErrors.push(@json($rowErrorWords).replace(':row', rowNum).replace(':error', @json(__('messages.duplicate_email'))));
                            }

                            if (email && this.isValidEmail(email) && !seen[email]) {
                                seen[email] = true;
                                entries.push({ email, name });
                            }
                        });

                        if (this.formErrors.length > 0) return;
                        if (entries.length === 0) {
                            alert(@json(__('messages.no_valid_emails')));
                            return;
                        }

                        this.doSubmit(entries);
                    },

                    isValidEmail(str) {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str);
                    },

                    getValidEntryCount() {
                        return this.formEntries.filter(e =>
                            e.email && this.isValidEmail(e.email.trim())
                        ).length;
                    },

                    parsePaste() {
                        const entries = [];
                        const seen = {};
                        const lines = this.pasteText.split(/\r?\n/);

                        for (const line of lines) {
                            const trimmed = line.trim();
                            if (!trimmed) continue;

                            let email = null;
                            let name = '';

                            // Try "Name <email>" format
                            const angleMatch = trimmed.match(/^(.+?)\s*<([^>]+)>/);
                            if (angleMatch) {
                                name = angleMatch[1].trim();
                                email = angleMatch[2].trim().toLowerCase();
                            } else {
                                // Split by comma - try "email, Name" format
                                const parts = trimmed.split(',').map(p => p.trim());
                                if (parts.length === 2 && this.isValidEmail(parts[0]) && !this.isValidEmail(parts[1])) {
                                    email = parts[0].toLowerCase();
                                    name = parts[1];
                                } else if (this.isValidEmail(parts[0])) {
                                    email = parts[0].toLowerCase();
                                    name = '';
                                }
                            }

                            if (email && !seen[email]) {
                                seen[email] = true;
                                entries.push({ email, name });
                            }
                        }

                        if (entries.length === 0) {
                            alert(@json(__('messages.no_valid_emails')));
                            return;
                        }

                        // Populate Form tab and switch to it
                        this.formEntries = entries;
                        this.formErrors = [];
                        this.tab = 'form';
                    },

                    loadCsvIntoForm() {
                        this.csvErrors = [];

                        if (!this.hasEmailColumn()) {
                            this.csvErrors.push(@json(__('messages.email_required')));
                            return;
                        }

                        const emailIdx = this.columnMappings.indexOf('email');
                        const nameIndices = this.columnMappings.reduce((acc, val, idx) => {
                            if (val === 'name') acc.push(idx);
                            return acc;
                        }, []);

                        const entries = [];
                        this.csvAllRows.forEach(row => {
                            const email = (row[emailIdx] || '').trim().toLowerCase();
                            const name = nameIndices.map(idx => (row[idx] || '').trim()).filter(Boolean).join(' ');
                            if (!email && !name) return;
                            entries.push({ name, email });
                        });

                        if (!entries.length) {
                            alert(@json(__('messages.no_valid_emails')));
                            return;
                        }

                        this.formEntries = entries;
                        this.formErrors = [];
                        this.tab = 'form';
                        this.clearCsvFile();
                    },

                    async doSubmit(entries) {
                        if (!this.validateSegment()) return;

                        this.submitting = true;

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('newsletter.import.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}';
                        form.style.display = 'none';

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_token';
                        csrf.value = '{{ csrf_token() }}';
                        form.appendChild(csrf);

                        const addField = (name, value) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            form.appendChild(input);
                        };

                        addField('segment_target', this.segmentTarget);
                        if (this.segmentTarget === 'new') {
                            addField('segment_name', this.segmentName);
                        } else {
                            addField('segment_id', this.segmentId);
                        }

                        entries.forEach((entry, i) => {
                            addField(`entries[${i}][email]`, entry.email);
                            addField(`entries[${i}][name]`, entry.name);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    },

                    validateSegment() {
                        this.segmentError = '';
                        if (this.segmentTarget === 'new' && !this.segmentName.trim()) {
                            this.segmentError = @json(__('messages.name_required'));
                            return false;
                        }
                        if (this.segmentTarget === 'existing' && !this.segmentId) {
                            this.segmentError = @json(__('messages.select_segment'));
                            return false;
                        }
                        return true;
                    },

                    clearCsvFile() {
                        this.$refs.csvFileInput.value = '';
                        this.csvFilename = '';
                        this.csvHeaders = [];
                        this.csvPreview = [];
                        this.csvAllRows = [];
                        this.csvTotalRows = 0;
                        this.columnMappings = [];
                        this.csvErrors = [];
                    }
                },
            }).mount('#import-emails-app');
        });
    </script>
</x-app-admin-layout>
