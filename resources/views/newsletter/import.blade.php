<x-app-admin-layout>
    <div class="max-w-4xl mx-auto" x-data="importEmails()">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.import_emails') }}</h2>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Segment Target --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.segments') }}</h3>

            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" x-model="segmentTarget" value="new" class="text-[#4E81FA] focus:ring-[#4E81FA]">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.create_new_segment') }}</span>
                </label>
                <div x-show="segmentTarget === 'new'" x-cloak class="{{ is_rtl() ? 'mr-7' : 'ml-7' }}">
                    <x-text-input x-model="segmentName" type="text" class="block w-full max-w-md" :placeholder="__('messages.name')" />
                </div>

                @if ($manualSegments->count())
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="radio" x-model="segmentTarget" value="existing" class="text-[#4E81FA] focus:ring-[#4E81FA]">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.add_to_existing_segment') }}</span>
                </label>
                <div x-show="segmentTarget === 'existing'" x-cloak class="{{ is_rtl() ? 'mr-7' : 'ml-7' }}">
                    <select x-model="segmentId" class="block w-full max-w-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                        <option value="">{{ __('messages.select_segment') }}</option>
                        @foreach ($manualSegments as $segment)
                            <option value="{{ \App\Utils\UrlUtils::encodeId($segment->id) }}">{{ $segment->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>

        {{-- Import Method Tabs --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex -mb-px">
                    <button @click="tab = 'form'" :class="tab === 'form' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                        {{ __('messages.form_entry') }}
                    </button>
                    <button @click="tab = 'paste'" :class="tab === 'paste' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                        {{ __('messages.paste_emails') }}
                    </button>
                    <button @click="tab = 'csv'" :class="tab === 'csv' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'" class="px-6 py-3 border-b-2 font-medium text-sm transition-colors">
                        {{ __('messages.upload_csv') }}
                    </button>
                </nav>
            </div>

            {{-- Form Tab --}}
            <div x-show="tab === 'form'" class="p-6">
                {{-- Error display --}}
                <template x-if="formErrors.length > 0">
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-2">{{ __('messages.import_validation_failed') }}</p>
                        <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                            <template x-for="error in formErrors" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                {{-- Entry table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                <th class="pb-2 pr-4">{{ __('messages.name') }}</th>
                                <th class="pb-2 pr-4">{{ __('messages.email') }}</th>
                                <th class="pb-2 w-20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(entry, index) in formEntries" :key="index">
                                <tr>
                                    <td class="py-1 pr-2">
                                        <x-text-input x-model="entry.name" type="text" class="w-full" placeholder="{{ __('messages.name') }}" />
                                    </td>
                                    <td class="py-1 pr-2">
                                        <x-text-input x-model="entry.email" type="email" class="w-full" placeholder="email@example.com" />
                                    </td>
                                    <td class="py-1">
                                        <button type="button" @click="removeFormRow(index)" x-show="formEntries.length > 1"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                            {{ __('messages.remove') }}
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" @click="addFormRow()"
                        class="text-sm text-[#4E81FA] hover:text-blue-700">
                        + {{ __('messages.add_row') }}
                    </button>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">{{ __('messages.name_and_email_required') }}</p>

                <div class="flex items-center justify-between mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <span x-text="getValidEntryCount()"></span> {{ __('messages.emails_to_import') }}
                    </p>
                    <button @click="submitForm()" :disabled="submitting || getValidEntryCount() === 0"
                        class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submitting">{{ __('messages.confirm_import') }}</span>
                        <span x-show="submitting">{{ __('messages.loading') }}...</span>
                    </button>
                </div>
            </div>

            {{-- Paste Tab --}}
            <div x-show="tab === 'paste'" x-cloak class="p-6">
                <textarea x-model="pasteText" rows="10"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                    placeholder="John Smith <john@example.com>&#10;Jane Doe <jane@example.com>&#10;bob@example.com, Bob Johnson&#10;carol@example.com, Carol Smith"></textarea>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('messages.import_emails_help') }}</p>

                <div class="flex justify-end mt-4">
                    <button @click="parsePaste()" :disabled="!pasteText.trim()"
                        class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('messages.parse_emails') }}
                    </button>
                </div>
            </div>

            {{-- CSV Tab --}}
            <div x-show="tab === 'csv'" x-cloak class="p-6">
                {{-- Error display for CSV tab --}}
                <template x-if="csvErrors.length > 0">
                    <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-2">{{ __('messages.import_validation_failed') }}</p>
                        <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                            <template x-for="error in csvErrors" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                <div class="mb-4">
                    <input x-ref="csvFileInput" type="file" accept=".csv" @change="handleCsvFile($event); $refs.csvFilename.textContent = $event.target.files[0]?.name || ''" class="hidden" />
                    <div class="flex items-center gap-3">
                        <button type="button" @click="$refs.csvFileInput.click()"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('messages.choose_file') }}
                        </button>
                        <span x-ref="csvFilename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                    </div>
                </div>

                {{-- CSV Preview --}}
                <template x-if="csvPreview.length > 0">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.csv_preview') }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.row_count') }}: <span x-text="csvTotalRows"></span></p>

                        {{-- Column Mapping --}}
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.map_columns') }}</p>
                            <div class="flex gap-4 flex-wrap">
                                <template x-for="(header, index) in csvHeaders" :key="index">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="header"></span>
                                        <select :value="columnMappings[index]" @change="columnMappings[index] = $event.target.value"
                                            class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                                            <option value="skip">{{ __('messages.skip_column') }}</option>
                                            <option value="email">{{ __('messages.email') }}</option>
                                            <option value="name">{{ __('messages.name') }}</option>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Preview Table --}}
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <template x-for="(header, index) in csvHeaders" :key="index">
                                            <th class="px-4 py-2 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase" x-text="header"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="(row, rowIndex) in csvPreview" :key="rowIndex">
                                        <tr>
                                            <template x-for="(cell, cellIndex) in row" :key="cellIndex">
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300" x-text="cell"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button @click="submitCsv()" :disabled="submitting || !hasEmailColumn()"
                                class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">{{ __('messages.confirm_import') }}</span>
                                <span x-show="submitting">{{ __('messages.loading') }}...</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        function importEmails() {
            return {
                tab: 'form',
                segmentTarget: 'new',
                segmentName: '',
                segmentId: '',
                pasteText: '',
                csvHeaders: [],
                csvPreview: [],
                csvAllRows: [],
                csvTotalRows: 0,
                columnMappings: [],
                submitting: false,
                formEntries: [{ name: '', email: '' }],
                formErrors: [],
                csvErrors: [],

                handleCsvFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (file.size > 10 * 1024 * 1024) {
                        alert('{{ __('messages.file_too_large') }}');
                        event.target.value = '';
                        return;
                    }

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

                hasNameColumn() {
                    return this.columnMappings.includes('name');
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
                        if (!name) {
                            this.formErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.name_required') }}'));
                        }
                        if (!email) {
                            this.formErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.email_required') }}'));
                        } else if (!this.isValidEmail(email)) {
                            this.formErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.invalid_email') }}'));
                        } else if (seen[email]) {
                            this.formErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.duplicate_email') }}'));
                        }

                        if (name && email && this.isValidEmail(email) && !seen[email]) {
                            seen[email] = true;
                            entries.push({ email, name });
                        }
                    });

                    if (this.formErrors.length > 0) return;
                    if (entries.length === 0) {
                        alert('{{ __('messages.no_valid_emails') }}');
                        return;
                    }

                    this.doSubmit(entries);
                },

                isValidEmail(str) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str);
                },

                getValidEntryCount() {
                    return this.formEntries.filter(e =>
                        e.email && e.name && this.isValidEmail(e.email.trim())
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
                        alert('{{ __('messages.no_valid_emails') }}');
                        return;
                    }

                    // Populate Form tab and switch to it
                    this.formEntries = entries;
                    this.formErrors = [];
                    this.tab = 'form';
                },

                async submitCsv() {
                    this.csvErrors = [];

                    if (!this.hasEmailColumn()) {
                        this.csvErrors.push('{{ __('messages.email_required') }}');
                        return;
                    }

                    if (!this.hasNameColumn()) {
                        this.csvErrors.push('{{ __('messages.name_required') }}');
                        return;
                    }

                    const emailIdx = this.columnMappings.indexOf('email');
                    const nameIndices = this.columnMappings.reduce((acc, val, idx) => {
                        if (val === 'name') acc.push(idx);
                        return acc;
                    }, []);

                    const entries = [];
                    const seen = {};

                    this.csvAllRows.forEach((row, i) => {
                        const rowNum = i + 1;
                        const email = (row[emailIdx] || '').trim().toLowerCase();
                        const name = nameIndices.map(idx => (row[idx] || '').trim()).filter(Boolean).join(' ');

                        if (!email && !name) return; // Skip empty rows

                        if (!name) {
                            this.csvErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.name_required') }}'));
                        }
                        if (!email) {
                            this.csvErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.email_required') }}'));
                        } else if (!this.isValidEmail(email)) {
                            this.csvErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.invalid_email') }}'));
                        } else if (seen[email]) {
                            this.csvErrors.push(`{{ __('messages.row_error', ['row' => '']) }}`.replace(':row', rowNum).replace(':error', '{{ __('messages.duplicate_email') }}'));
                        }

                        if (email && name && this.isValidEmail(email) && !seen[email]) {
                            seen[email] = true;
                            entries.push({ email, name });
                        }
                    });

                    if (this.csvErrors.length > 0) return;
                    if (!entries.length) {
                        alert('{{ __('messages.no_valid_emails') }}');
                        return;
                    }
                    await this.doSubmit(entries);
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
                    if (this.segmentTarget === 'new' && !this.segmentName.trim()) {
                        alert('{{ __('messages.name') }}');
                        return false;
                    }
                    if (this.segmentTarget === 'existing' && !this.segmentId) {
                        alert('{{ __('messages.select_segment') }}');
                        return false;
                    }
                    return true;
                }
            };
        }
    </script>
</x-app-admin-layout>
