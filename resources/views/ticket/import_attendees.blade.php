<x-app-admin-layout>
    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        @if ($event && ! $requiresPro)
        <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
        <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
        @endif
        <style {!! nonce_attr() !!}>
            [v-cloak] { display: none !important; }
            .iti { display: block !important; width: 100% !important; }
            .iti:not(.iti--country-only) > .iti__country-container { padding: 0 0 0 4px !important; }
            .dark .iti { --iti-dropdown-bg: rgb(var(--ap-bg)); --iti-hover-color: rgb(var(--ap-border)); --iti-border-color: rgb(var(--ap-border)); --iti-dialcode-color: rgb(var(--ap-ink-2)); --iti-arrow-color: rgb(var(--ap-ink-2)); }
            .dark .iti__dropdown-content, .dark .iti__selected-dial-code { color: rgb(var(--ap-ink-2)); }
            .dark .iti__search-input { background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink-2)); border-color: rgb(var(--ap-border)); }
            .import-shell .page-filters { margin: 0 0 1rem; }
            .import-shell .page-filter select { min-width: 14rem; }
            /* The two ways in (type them, or bring a file) are the kit's tabs, inside the card. */
            .import-tabs.ap-tabs-wrap { margin: 0; padding: 0 1.25rem; }
            .import-pane { padding: 1.25rem; }
            .import-sheet { width: 100%; }
            .import-sheet th { padding: 0 0.5rem 0.5rem 0; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-align: start; text-transform: uppercase; white-space: nowrap; color: rgb(var(--ap-ink-3)); }
            .import-sheet td { padding: 0.25rem 0.5rem 0.25rem 0; vertical-align: top; }
            [dir="rtl"] .import-sheet th { padding: 0 0 0.5rem 0.5rem; }
            [dir="rtl"] .import-sheet td { padding: 0.25rem 0 0.25rem 0.5rem; }
            .import-sheet input:not([type="checkbox"]), .import-sheet select, .import-sheet textarea { min-width: 7rem; }
            .import-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; margin-top: 1rem; border-top: 1px solid rgb(var(--ap-border)); padding-top: 1rem; }
            .import-foot-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1rem; }
            .import-drop { border: 2px dashed rgb(var(--ap-border-strong)); border-radius: 0.75rem; padding: 2rem 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s, background-color 0.2s; }
            .import-drop:hover { background: var(--ap-tint-1); }
            .import-drop.is-over { border-color: var(--brand-blue); background: var(--ap-tint-1); }
        </style>
    </x-slot>

    @php $importRole = $roles->firstWhere('id', $selectedRoleId); @endphp

    {{-- Attendees brought in from somewhere else. It hangs from Sales, so its way back is named
         Sales (it used to be a grey "Back" button where the page's own actions belong). The Vue
         apps (#event-picker-app, #import-attendees-app) and their ids are as they were. --}}
    <div class="page-shell import-shell">
        <x-page-header :title="__('messages.import_attendees')" :lead="__('messages.import_attendees_lead')"
                       :back="route('sales')" :back-label="__('messages.sales')" />

        <div class="page-stack">
        <x-page-flash :keys="['status' => 'success', 'error' => 'error']" />

        {{-- Which schedule, then which of its events. A schedule whose plan has no import has no
             event to pick, so the second select is not drawn for it. --}}
        @if ($roles->count() > 1 || ($selectedRoleId && ! $requiresPro))
        <div class="page-filters">
            @if ($roles->count() > 1)
            <div class="page-filter">
                <label for="role-filter">{{ __('messages.schedule') }}</label>
                <select id="role-filter"
                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-base">
                    @foreach ($roles as $r)
                        <option value="{{ \App\Utils\UrlUtils::encodeId($r->id) }}" {{ $selectedRoleId == $r->id ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if ($selectedRoleId && ! $requiresPro)
            <div class="page-filter">
                <span id="event-picker-label">{{ __('messages.event') }}</span>
                <div id="event-picker-app">
                <div class="relative" id="event-selector-dropdown">
                    <select aria-labelledby="event-picker-label" @mousedown.prevent="toggleDropdown" @keydown.space.prevent="toggleDropdown" @keydown.enter.prevent="toggleDropdown"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-base cursor-pointer">
                        {{-- The event's name is its owner's text, inside a Vue mount: v-pre keeps Vue from compiling it. --}}
                        <option v-pre>{{ $event ? $event->translatedName() : __('messages.select_event_to_begin') }}</option>
                    </select>
                    <div v-cloak v-if="dropdownOpen" class="ap-dropdown absolute z-50 mt-1 rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] max-h-72 overflow-y-auto" style="min-width: 280px">
                        <button v-for="event in events" :key="event.id" @click="onEventChange(event.id)" type="button"
                            class="w-full flex items-center gap-3 px-3 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            :class="event.id === selectedEventId ? 'bg-gray-50 dark:bg-gray-700/50' : ''">
                            <img v-if="event.image_url" :src="event.image_url" alt="" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">@{{ event.name }}</span>
                                <span v-if="event.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ event.starts_at }}</span>
                            </span>
                            <svg v-if="event.id === selectedEventId" class="w-5 h-5 text-[var(--brand-blue)] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <div v-if="!events.length" class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                            {{ __('messages.no_events') }}
                        </div>
                    </div>
                </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if ($requiresPro)
        {{-- Told which schedule it is about, so its button goes to that schedule's plan and not to
             the public pricing page. Only the owner can buy the plan. --}}
        <x-plan-gate tier="pro" :title="__('messages.import_attendees')" :role="$importRole" :subdomain="$importRole?->subdomain"
            :canUpgrade="auth()->user()->id == $importRole?->user_id">
            {{ __('messages.import_attendees_lead') }}
        </x-plan-gate>
        @elseif (! $event)
        <div class="ap-card rounded-xl">
            <x-page-empty :title="__('messages.select_event_to_begin')" :text="$events->isEmpty() ? __('messages.no_events') : null"
                icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </div>
        @else
        <div id="import-attendees-app" v-cloak>
            {{-- Event date picker (recurring events only) --}}
            <div v-if="isRecurring" class="page-filters mb-4">
                <label class="page-filter">
                    <span>{{ __('messages.event_date') }}</span>
                    <input ref="eventDatePicker" type="text"
                        class="w-48 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm">
                </label>
            </div>

            {{-- Tabs --}}
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="ap-tabs-wrap import-tabs">
                    <div class="ap-tabs" role="tablist">
                        <button type="button" class="ap-tab" role="tab" id="import-tab-form" aria-controls="import-pane-form" :aria-selected="tab === 'form' ? 'true' : 'false'" @click="tab = 'form'">
                            {{ __('messages.form_entry') }}
                        </button>
                        <button type="button" class="ap-tab" role="tab" id="import-tab-csv" aria-controls="import-pane-csv" :aria-selected="tab === 'csv' ? 'true' : 'false'" @click="tab = 'csv'">
                            {{ __('messages.upload_csv') }}
                        </button>
                    </div>
                </div>

                {{-- Form tab --}}
                <div v-show="tab === 'form'" class="import-pane" id="import-pane-form" role="tabpanel" aria-labelledby="import-tab-form">
                    <x-page-notice tone="error" class="mb-4" v-if="formErrors.length">
                        <ul class="list-disc list-inside">
                            <li v-for="err in formErrors" :key="err">@{{ err }}</li>
                        </ul>
                    </x-page-notice>

                    <div class="overflow-x-auto">
                        <table class="import-sheet">
                            <thead>
                                <tr>
                                    <th v-if="hasAnyCustomFields" class="w-8"></th>
                                    <th scope="col">{{ __('messages.name') }}</th>
                                    <th scope="col">{{ __('messages.email') }}</th>
                                    <th scope="col" v-if="showPhone">{{ __('messages.phone') }}</th>
                                    <th scope="col" v-if="tickets.length > 1">{{ __('messages.ticket_type') }}</th>
                                    <th scope="col" class="w-28">{{ __('messages.quantity') }}</th>
                                    <th scope="col" v-if="showAmount" class="w-28">{{ __('messages.amount') }}</th>
                                    <th scope="col" v-if="!showAmount" class="w-32">{{ __('messages.status') }}</th>
                                    <th scope="col" v-for="cf in eventCustomFields" :key="'ecf-'+cf.index">
                                        <span>@{{ cf.name }}</span><span v-if="cf.required" class="text-red-500 ms-0.5">*</span>
                                    </th>
                                    <th class="w-16"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="(entry, index) in entries" :key="entry._key">
                                    <tr>
                                        <td v-if="hasAnyCustomFields">
                                            <button v-if="ticketCustomFieldsFor(entry.ticket_id).length" type="button" @click="toggleExpand(index)"
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                                                <svg class="w-4 h-4 transform transition-transform" :class="expanded.includes(index) ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </td>
                                        <td><input v-model="entry.name" type="text" aria-label="{{ __('messages.name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"></td>
                                        <td><input v-model="entry.email" type="email" aria-label="{{ __('messages.email') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm" placeholder="email@example.com"></td>
                                        <td v-if="showPhone">
                                            <input type="tel" aria-label="{{ __('messages.phone') }}" :ref="setPhoneRef" :data-key="entry._key" :required="eventHasPhone"
                                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                        </td>
                                        <td v-if="tickets.length > 1">
                                            <select v-model="entry.ticket_id" aria-label="{{ __('messages.ticket_type') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                <option v-for="t in tickets" :key="t.id" :value="t.id">@{{ t.type }}</option>
                                            </select>
                                        </td>
                                        <td><input v-model.number="entry.quantity" type="number" min="1" aria-label="{{ __('messages.quantity') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"></td>
                                        <td v-if="showAmount"><input v-model="entry.amount" type="number" step="0.01" min="0" aria-label="{{ __('messages.amount') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"></td>
                                        <td v-if="!showAmount">
                                            <select v-model="entry.status" aria-label="{{ __('messages.status') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                <option value="paid">{{ __('messages.paid') }}</option>
                                                <option value="unpaid">{{ __('messages.unpaid') }}</option>
                                            </select>
                                        </td>
                                        <td v-for="cf in eventCustomFields" :key="'ecfv-'+cf.index">
                                            <select v-if="cf.type === 'switch'" v-model="entry.custom_values[cf.index]" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                <option value=""></option>
                                                <option value="Yes">{{ __('messages.yes') }}</option>
                                                <option value="No">{{ __('messages.no') }}</option>
                                            </select>
                                            <input v-else-if="cf.type === 'date'" v-model="entry.custom_values[cf.index]" type="date" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                            <select v-else-if="cf.type === 'dropdown'" v-model="entry.custom_values[cf.index]" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                <option value=""></option>
                                                <option v-for="opt in cf.options" :key="opt" :value="opt">@{{ opt }}</option>
                                            </select>
                                            <textarea v-else-if="cf.type === 'multiline_string'" v-model="entry.custom_values[cf.index]" :required="cf.required" rows="1" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"></textarea>
                                            <input v-else v-model="entry.custom_values[cf.index]" type="text" :required="cf.required" :placeholder="cf.type === 'multiselect' ? commaSeparatedLabel : ''" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                        </td>
                                        <td>
                                            <button v-if="entries.length > 1" type="button" @click="removeRow(index)" class="event-link is-danger">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="expanded.includes(index) && ticketCustomFieldsFor(entry.ticket_id).length">
                                        <td></td>
                                        <td :colspan="visibleColSpan">
                                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.ticket_custom_fields') }}</p>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div v-for="cf in ticketCustomFieldsFor(entry.ticket_id)" :key="'tcf-'+cf.index">
                                                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">
                                                            <span>@{{ cf.name }}</span><span v-if="cf.required" class="text-red-500 ms-0.5">*</span>
                                                        </label>
                                                        <select v-if="cf.type === 'switch'" v-model="entry.ticket_custom_values[cf.index]" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                            <option value=""></option>
                                                            <option value="Yes">{{ __('messages.yes') }}</option>
                                                            <option value="No">{{ __('messages.no') }}</option>
                                                        </select>
                                                        <input v-else-if="cf.type === 'date'" v-model="entry.ticket_custom_values[cf.index]" type="date" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                        <select v-else-if="cf.type === 'dropdown'" v-model="entry.ticket_custom_values[cf.index]" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                            <option value=""></option>
                                                            <option v-for="opt in cf.options" :key="opt" :value="opt">@{{ opt }}</option>
                                                        </select>
                                                        <textarea v-else-if="cf.type === 'multiline_string'" v-model="entry.ticket_custom_values[cf.index]" :required="cf.required" rows="2" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm"></textarea>
                                                        <div v-else-if="cf.type === 'multiselect'" class="space-y-1">
                                                            <label v-for="opt in cf.options" :key="opt" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                                <input type="checkbox" :value="opt" :checked="multiselectContains(entry.ticket_custom_values[cf.index], opt)"
                                                                    @change="toggleMultiselect(entry.ticket_custom_values, cf.index, opt, $event.target.checked)"
                                                                    class="rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                                                @{{ opt }}
                                                            </label>
                                                        </div>
                                                        <input v-else v-model="entry.ticket_custom_values[cf.index]" type="text" :required="cf.required" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-sm">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" @click="addRow()" class="event-link">
                            + {{ __('messages.add_attendee_row') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div class="import-foot">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                @{{ validCount }} {{ __('messages.attendees_to_import') }}
                            </p>
                            <div class="import-foot-actions">
                                <div class="flex items-center gap-3">
                                    <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                        <input type="checkbox" v-model="sendEmails" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                    </label>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer" @click="sendEmails = !sendEmails">{{ __('messages.send_email') }}</span>
                                </div>
                                <x-brand-button @click="submit()" ::disabled="submitting || validCount === 0 || (sendEmails && !hasEmailSettings)">
                                    <span v-if="!submitting">{{ __('messages.save_n_attendees') }}</span>
                                    <span v-else>{{ __('messages.loading') }}...</span>
                                </x-brand-button>
                            </div>
                        </div>
                        @if (! $hasEmailSettings && $emailSettingsRole)
                        <x-page-notice tone="warn" v-if="sendEmails">
                            @if (config('app.hosted'))
                            {{ __('messages.notification_requires_email_settings') }}
                            <x-link href="{{ route('role.edit', ['subdomain' => $emailSettingsRole->subdomain]) }}#integration-tab-email"
                                target="_blank" rel="noopener">{{ __('messages.configure_email_settings') }}</x-link>
                            @else
                            {{-- A selfhosted install has one mailer and no Email Settings tab to link to. --}}
                            {{ __('messages.email_not_configured') }}
                            @endif
                        </x-page-notice>
                        @endif
                    </div>
                </div>

                {{-- CSV tab --}}
                <div v-show="tab === 'csv'" class="import-pane" id="import-pane-csv" role="tabpanel" aria-labelledby="import-tab-csv">
                    <x-page-notice tone="error" class="mb-4" v-if="csvErrors.length">
                        <ul class="list-disc list-inside">
                            <li v-for="err in csvErrors" :key="err">@{{ err }}</li>
                        </ul>
                    </x-page-notice>

                    <input ref="csvFileInput" type="file" accept=".csv,text/csv" @change="handleCsvFileInput" class="hidden">
                    <div v-if="!csvHeaders.length" class="mb-4">
                        <div @click="$refs.csvFileInput.click()"
                            @dragover.prevent="csvDragOver = true"
                            @dragenter.prevent="csvDragOver = true"
                            @dragleave.prevent.self="csvDragOver = false"
                            @drop.prevent="handleCsvDrop"
                            :class="{ 'is-over': csvDragOver }"
                            class="import-drop">
                            <svg class="w-8 h-8 mx-auto text-gray-400 dark:text-gray-500 mb-2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-700 dark:text-gray-300 pointer-events-none">{{ __('messages.drop_csv_or_click') }}</p>
                        </div>
                    </div>

                    <div v-if="csvHeaders.length">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.map_columns') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                            <div v-for="(header, idx) in csvHeaders" :key="'map-'+idx">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">@{{ header }}</div>
                                <select v-model="columnMappings[idx]"
                                    class="w-full text-sm rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    <option value="skip">{{ __('messages.skip_column') }}</option>
                                    <option value="name">{{ __('messages.name') }}</option>
                                    <option value="email">{{ __('messages.email') }}</option>
                                    <option value="phone">{{ __('messages.phone') }}</option>
                                    <option value="ticket_type">{{ __('messages.ticket_type') }}</option>
                                    <option value="quantity">{{ __('messages.quantity') }}</option>
                                    <option value="amount">{{ __('messages.amount') }}</option>
                                    <option value="status">{{ __('messages.status') }}</option>
                                    <option v-for="cf in eventCustomFields" :key="'opt-ecf-'+cf.index" :value="'custom:'+cf.index">
                                        {{ __('messages.event') }}: @{{ cf.name }}
                                    </option>
                                    <option v-for="cf in uniqueTicketCustomFields" :key="'opt-tcf-'+cf.index" :value="'ticket_custom:'+cf.index">
                                        {{ __('messages.ticket') }}: @{{ cf.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        {{-- Clear first, the button that goes on last. --}}
                        <div class="page-form-actions">
                            <button type="button" @click="clearCsv()" class="page-tool">
                                {{ __('messages.clear') }}
                            </button>
                            <x-brand-button @click="applyCsvToForm()">
                                {{ __('messages.next') }}
                            </x-brand-button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CSV preview in its own panel --}}
            <section v-if="tab === 'csv' && csvHeaders.length" class="ap-card rounded-xl page-card is-flush mt-4">
                <div class="page-card-head">
                    <h2 class="page-card-title">{{ __('messages.csv_preview') }}</h2>
                </div>
                <div class="page-scroll max-h-60">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col" v-for="(h, i) in csvHeaders" :key="'hh-'+i">@{{ h }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, ri) in csvRows.slice(0, 5)" :key="'rr-'+ri">
                                <td v-for="(cell, ci) in row" :key="'cc-'+ci">@{{ cell }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="page-card-foot">{{ __('messages.row_count') }}: @{{ csvRows.length }}</div>
            </section>
        </div>
        @endif
        </div>
    </div>

    @if ($selectedRoleId)
    <script {!! nonce_attr() !!}>
        (function() {
            const roleFilter = document.getElementById('role-filter');
            if (roleFilter) {
                const initialRoleValue = roleFilter.value;
                roleFilter.addEventListener('change', function() {
                    if (window._importAttendeesIsDirty && !confirm(@json(__('messages.unsaved_changes_confirm')))) {
                        this.value = initialRoleValue;
                        return;
                    }
                    window._skipUnsavedWarning = true;
                    const url = new URL(window.location.href);
                    if (this.value) {
                        url.searchParams.set('role_id', this.value);
                    } else {
                        url.searchParams.delete('role_id');
                    }
                    url.searchParams.delete('event_id');
                    window.location.href = url.toString();
                });
            }

            // No event to pick on a schedule whose plan has no import: the picker is not drawn.
            if (! document.getElementById('event-picker-app')) {
                return;
            }

            const { createApp } = Vue;

            createApp({
                data() {
                    return {
                        events: @json($events),
                        selectedEventId: @json($event ? \App\Utils\UrlUtils::encodeId($event->id) : ''),
                        dropdownOpen: false,
                    };
                },
                methods: {
                    toggleDropdown() { this.dropdownOpen = !this.dropdownOpen; },
                    closeDropdown() { this.dropdownOpen = false; },
                    onEventChange(eventId) {
                        if (window._importAttendeesIsDirty && !confirm(@json(__('messages.unsaved_changes_confirm')))) {
                            this.closeDropdown();
                            return;
                        }
                        window._skipUnsavedWarning = true;
                        this.selectedEventId = eventId;
                        this.closeDropdown();
                        const url = new URL(window.location.href);
                        if (eventId) {
                            url.searchParams.set('event_id', eventId);
                        } else {
                            url.searchParams.delete('event_id');
                        }
                        window.location.href = url.toString();
                    },
                },
                mounted() {
                    document.addEventListener('click', (e) => {
                        const el = document.getElementById('event-selector-dropdown');
                        if (el && !el.contains(e.target)) this.closeDropdown();
                    });
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') this.closeDropdown();
                    });
                },
            }).mount('#event-picker-app');
        })();
    </script>
    @endif

    @if ($event && ! $requiresPro)
    @php
        $normalizeCustomFields = function ($fields) {
            $i = 1;
            $result = [];
            foreach (($fields ?? []) as $fc) {
                $index = (int) ($fc['index'] ?? $i);
                $i++;
                if ($index >= 1 && $index <= 10) {
                    $type = $fc['type'] ?? 'string';
                    $options = [];
                    if (in_array($type, ['dropdown', 'multiselect'], true)) {
                        $raw = $fc['options'] ?? '';
                        if (is_array($raw)) {
                            $options = array_values(array_filter(array_map('trim', $raw)));
                        } else {
                            $options = array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
                        }
                    }
                    $result[] = [
                        'name' => $fc['name'] ?? '',
                        'index' => $index,
                        'type' => $type,
                        'options' => $options,
                        'required' => (bool) ($fc['required'] ?? false),
                    ];
                }
            }
            return $result;
        };
        $ticketsPayload = [];
        foreach ($tickets as $t) {
            $ticketsPayload[] = [
                'id' => \App\Utils\UrlUtils::encodeId($t->id),
                'raw_id' => $t->id,
                'type' => $t->type ?: __('messages.ticket'),
                'price' => (float) $t->price,
                'price_display' => number_format((float) $t->price, 2),
                'custom_fields' => $normalizeCustomFields($t->custom_fields ?? []),
            ];
        }
        $eventCustomFieldsPayload = $normalizeCustomFields($event->custom_fields ?? []);
        $selectedRoleForCountry = $roles->firstWhere('id', $selectedRoleId);
        $initialCountry = strtolower(
            $selectedRoleForCountry->country_code
            ?? $event->creatorRole->country_code
            ?? 'us'
        );
    @endphp
    <script {!! nonce_attr() !!}>
        (function() {
            const { createApp } = Vue;

            const TICKETS = @json($ticketsPayload);
            const EVENT_CUSTOM_FIELDS = @json($eventCustomFieldsPayload);
            const EVENT_DATES = @json($eventDates);
            const IS_RECURRING = @json((bool) $event->days_of_week);
            const EVENT_HAS_PHONE = @json((bool) $event->require_phone);
            const HAS_EMAIL_SETTINGS = @json((bool) $hasEmailSettings);
            const INITIAL_COUNTRY = @json($initialCountry);
            const UTILS_SCRIPT = '{{ asset('vendor/intl-tel-input/js/utils.js') }}';

            const CSRF = '{{ csrf_token() }}';
            const SUBMIT_URL = '{{ route('sales.import_store') }}';
            const EVENT_ID = '{{ \App\Utils\UrlUtils::encodeId($event->id) }}';

            let keyCounter = 0;
            function newKey() { return ++keyCounter; }

            function emptyEntry(defaultTicketId, defaultStatus) {
                return {
                    _key: newKey(),
                    name: '',
                    email: '',
                    phone: '',
                    ticket_id: defaultTicketId,
                    quantity: 1,
                    amount: '',
                    status: defaultStatus,
                    custom_values: {},
                    ticket_custom_values: {},
                };
            }

            createApp({
                data() {
                    return {
                        tab: 'form',
                        tickets: TICKETS,
                        eventCustomFields: EVENT_CUSTOM_FIELDS,
                        eventDates: EVENT_DATES,
                        isRecurring: IS_RECURRING,
                        eventHasPhone: EVENT_HAS_PHONE,
                        hasEmailSettings: HAS_EMAIL_SETTINGS,
                        fallbackTicketId: TICKETS[0] ? TICKETS[0].id : '',
                        eventDate: EVENT_DATES[0] || '',
                        defaultStatus: 'paid',
                        sendEmails: false,
                        entries: [emptyEntry(TICKETS[0] ? TICKETS[0].id : '', 'paid')],
                        expanded: [],
                        formErrors: [],
                        csvErrors: [],
                        csvHeaders: [],
                        csvRows: [],
                        csvFilename: '',
                        columnMappings: [],
                        csvHasPhone: false,
                        csvHasAmount: false,
                        csvDragOver: false,
                        submitting: false,
                        itiInstances: {},
                        isDirty: false,
                        commaSeparatedLabel: @json(__('messages.comma_separated')),
                    };
                },
                computed: {
                    hasAnyCustomFields() {
                        if (this.eventCustomFields.length) return true;
                        return this.tickets.some(t => t.custom_fields.length);
                    },
                    uniqueTicketCustomFields() {
                        const seen = new Set();
                        const out = [];
                        for (const t of this.tickets) {
                            for (const cf of t.custom_fields) {
                                const key = cf.index + '::' + cf.name.toLowerCase();
                                if (!seen.has(key)) {
                                    seen.add(key);
                                    out.push(cf);
                                }
                            }
                        }
                        return out;
                    },
                    showPhone() {
                        return this.eventHasPhone || this.csvHasPhone || this.entries.some(e => e.phone);
                    },
                    showAmount() {
                        return this.csvHasAmount || this.entries.some(e => e.amount !== '' && e.amount !== null && e.amount !== undefined && Number(e.amount) !== 0);
                    },
                    visibleColSpan() {
                        // name + email + qty + (status OR amount, always 1) + (phone?) + (ticket?) + custom fields + actions
                        let n = 5;
                        if (this.showPhone) n++;
                        if (this.tickets.length > 1) n++;
                        n += this.eventCustomFields.length;
                        return n;
                    },
                    validCount() {
                        return this.entries.filter(e => e.email && this.isValidEmail(e.email)).length;
                    },
                },
                watch: {
                    showPhone(v) {
                        if (v) {
                            this.$nextTick(() => this.refreshPhoneInputs());
                        }
                    },
                    entries: {
                        deep: true,
                        handler() {
                            this.markDirty();
                            if (this.showPhone) {
                                this.$nextTick(() => this.refreshPhoneInputs());
                            }
                        },
                    },
                    fallbackTicketId() { this.markDirty(); },
                    eventDate() { this.markDirty(); },
                    defaultStatus() { this.markDirty(); },
                    sendEmails() { this.markDirty(); },
                },
                methods: {
                    markDirty() {
                        this.isDirty = true;
                        window._importAttendeesIsDirty = true;
                    },
                    isValidEmail(s) {
                        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((s || '').trim());
                    },
                    ticketCustomFieldsFor(ticketId) {
                        const t = this.tickets.find(x => x.id === ticketId);
                        return t ? t.custom_fields : [];
                    },
                    multiselectContains(csv, opt) {
                        if (!csv) return false;
                        return String(csv).split(',').map(s => s.trim()).includes(opt);
                    },
                    normalizePhone(val) {
                        if (!val) return '';
                        const cleaned = String(val).trim();
                        const hasPlus = cleaned.startsWith('+');
                        const digits = cleaned.replace(/\D/g, '');
                        return hasPlus ? '+' + digits : digits;
                    },
                    normalizeCustomValue(cf, val) {
                        if (!cf || val == null) return val;
                        const v = String(val).trim();
                        if (!v) return '';
                        if (cf.type === 'switch') {
                            const lc = v.toLowerCase();
                            if (['yes', 'y', 'true', '1'].includes(lc)) return 'Yes';
                            if (['no', 'n', 'false', '0'].includes(lc)) return 'No';
                            return v;
                        }
                        return v;
                    },
                    toggleMultiselect(bag, idx, opt, checked) {
                        const current = bag[idx] ? String(bag[idx]).split(',').map(s => s.trim()).filter(Boolean) : [];
                        const pos = current.indexOf(opt);
                        if (checked && pos < 0) current.push(opt);
                        if (!checked && pos >= 0) current.splice(pos, 1);
                        bag[idx] = current.join(', ');
                    },
                    toggleExpand(i) {
                        const pos = this.expanded.indexOf(i);
                        if (pos >= 0) this.expanded.splice(pos, 1);
                        else this.expanded.push(i);
                    },
                    addRow() {
                        this.entries.push(emptyEntry(this.fallbackTicketId, this.defaultStatus));
                    },
                    removeRow(i) {
                        const removed = this.entries[i];
                        if (removed && this.itiInstances[removed._key]) {
                            try { this.itiInstances[removed._key].destroy(); } catch (e) {}
                            delete this.itiInstances[removed._key];
                        }
                        this.entries.splice(i, 1);
                        this.expanded = this.expanded.filter(x => x !== i).map(x => x > i ? x - 1 : x);
                    },
                    setPhoneRef(el) {
                        // Vue function refs call with `null` on unmount. When that happens,
                        // sweep any iti instances whose input is no longer in the DOM.
                        if (!el) {
                            this.$nextTick(() => this.pruneDetachedItiInstances());
                            return;
                        }
                        if (!window.intlTelInput) return;
                        const key = el.getAttribute('data-key');
                        if (!key || this.itiInstances[key]) return;
                        const entry = this.entries.find(e => String(e._key) === String(key));
                        if (!entry) return;
                        const iti = window.intlTelInput(el, {
                            utilsScript: UTILS_SCRIPT,
                            initialCountry: INITIAL_COUNTRY,
                            separateDialCode: true,
                            strictMode: true,
                            nationalMode: false,
                            autoPlaceholder: 'off',
                            dropdownContainer: document.body,
                        });
                        const sync = () => { entry.phone = iti.getNumber() || ''; };
                        el.addEventListener('change', sync);
                        el.addEventListener('input', sync);
                        el.addEventListener('countrychange', sync);
                        if (entry.phone) iti.setNumber(entry.phone);
                        this.itiInstances[key] = iti;
                        iti._input = el;
                        this.$nextTick(() => this.matchPhoneHeights());
                    },
                    pruneDetachedItiInstances() {
                        for (const [key, iti] of Object.entries(this.itiInstances)) {
                            const input = iti && iti._input;
                            if (!input || !document.body.contains(input)) {
                                try { iti && iti.destroy(); } catch (e) {}
                                delete this.itiInstances[key];
                            }
                        }
                    },
                    matchPhoneHeights() {
                        // Read the rendered height of a sibling text/email input inside the
                        // entries table and copy it onto every tel input. Scoping to the table
                        // avoids accidentally measuring the event-date picker above the table.
                        const refInput = document.querySelector('#import-attendees-app table input[type=email]')
                            || document.querySelector('#import-attendees-app table input[type=text]');
                        if (!refInput) return;
                        const h = refInput.offsetHeight;
                        if (!h) return;
                        document.querySelectorAll('#import-attendees-app .iti__tel-input').forEach(tel => {
                            tel.style.setProperty('height', h + 'px', 'important');
                        });
                    },
                    refreshPhoneInputs() {
                        // For existing entries that already have iti, re-sync values
                        for (const entry of this.entries) {
                            const iti = this.itiInstances[entry._key];
                            if (iti && entry.phone && iti.getNumber() !== entry.phone) {
                                iti.setNumber(entry.phone);
                            }
                        }
                    },
                    handleCsvFileInput(event) {
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
                    clearCsv() {
                        this.csvFilename = '';
                        this.csvHeaders = [];
                        this.csvRows = [];
                        this.columnMappings = [];
                        this.csvErrors = [];
                        this.csvHasPhone = false;
                        this.csvHasAmount = false;
                        if (this.$refs.csvFileInput) this.$refs.csvFileInput.value = '';
                    },
                    readCsvFile(file) {
                        if (file.size > 5 * 1024 * 1024) {
                            alert(@json(__('messages.file_too_large')));
                            return;
                        }
                        this.csvFilename = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const rows = this.parseCsv(e.target.result);
                            if (rows.length < 2) {
                                this.csvErrors = [@json(__('messages.no_valid_emails'))];
                                return;
                            }
                            this.csvErrors = [];
                            const rawHeaders = rows[0];
                            const rawRows = rows.slice(1).filter(r => r.some(c => c && c.trim()));
                            // Drop columns whose header is blank AND every data row is blank.
                            const keep = rawHeaders.map((h, i) => {
                                if (h && h.trim()) return true;
                                return rawRows.some(r => (r[i] || '').trim());
                            });
                            this.csvHeaders = rawHeaders.filter((_, i) => keep[i]);
                            this.csvRows = rawRows.map(r => r.filter((_, i) => keep[i]));
                            this.columnMappings = this.csvHeaders.map(h => this.autoDetect(h));
                        };
                        reader.readAsText(file);
                    },
                    autoDetect(header) {
                        const h = (header || '').toLowerCase().trim();
                        if (!h) return 'skip';
                        if (h === 'email' || h === 'e-mail' || h.includes('email')) return 'email';
                        if (h === 'name' || h.includes('name')) return 'name';
                        if (h === 'phone' || h === 'tel' || h.includes('phone')) return 'phone';
                        if (h === 'ticket' || h === 'ticket type' || h.includes('ticket type')) return 'ticket_type';
                        if (h === 'quantity' || h === 'qty') return 'quantity';
                        if (h === 'amount' || h === 'price' || h === 'paid') return 'amount';
                        if (h === 'status') return 'status';
                        for (const cf of this.eventCustomFields) {
                            if (cf.name && h === cf.name.toLowerCase()) return 'custom:' + cf.index;
                        }
                        for (const cf of this.uniqueTicketCustomFields) {
                            if (cf.name && h === cf.name.toLowerCase()) return 'ticket_custom:' + cf.index;
                        }
                        return 'skip';
                    },
                    parseCsv(text) {
                        // Strip UTF-8 BOM (Excel's default CSV export).
                        text = text.replace(/^\uFEFF/, '');
                        // Auto-detect delimiter: sample the first line and pick whichever
                        // character appears most often outside quoted runs among , ; and tab.
                        const firstNewline = text.search(/\r?\n/);
                        const sample = firstNewline >= 0 ? text.slice(0, firstNewline) : text;
                        const count = (needle) => {
                            let n = 0, q = false;
                            for (const ch of sample) {
                                if (ch === '"') q = !q;
                                else if (!q && ch === needle) n++;
                            }
                            return n;
                        };
                        const counts = { ',': count(','), ';': count(';'), '\t': count('\t') };
                        const delim = Object.entries(counts).sort((a, b) => b[1] - a[1])[0][0];
                        const rows = [];
                        let current = [];
                        let cell = '';
                        let inQuotes = false;
                        for (let i = 0; i < text.length; i++) {
                            const ch = text[i];
                            const next = text[i + 1];
                            if (inQuotes) {
                                if (ch === '"' && next === '"') { cell += '"'; i++; }
                                else if (ch === '"') inQuotes = false;
                                else cell += ch;
                            } else {
                                if (ch === '"') inQuotes = true;
                                else if (ch === delim) { current.push(cell.trim()); cell = ''; }
                                else if (ch === '\n' || (ch === '\r' && next === '\n')) {
                                    current.push(cell.trim());
                                    if (current.some(c => c)) rows.push(current);
                                    current = []; cell = '';
                                    if (ch === '\r') i++;
                                } else cell += ch;
                            }
                        }
                        if (cell || current.length) {
                            current.push(cell.trim());
                            if (current.some(c => c)) rows.push(current);
                        }
                        return rows;
                    },
                    applyCsvToForm() {
                        this.csvErrors = [];
                        // Clear existing iti instances; refs will re-init on new inputs
                        Object.values(this.itiInstances).forEach(iti => {
                            try { iti.destroy(); } catch (e) {}
                        });
                        this.itiInstances = {};
                        this.csvHasPhone = this.columnMappings.includes('phone');
                        this.csvHasAmount = this.columnMappings.includes('amount');
                        const entries = [];
                        for (const row of this.csvRows) {
                            const entry = emptyEntry(this.fallbackTicketId, this.defaultStatus);
                            let ticketTypeName = '';
                            for (let i = 0; i < this.columnMappings.length; i++) {
                                const target = this.columnMappings[i];
                                const val = (row[i] || '').trim();
                                if (!target || target === 'skip' || !val) continue;
                                if (target === 'name') entry.name = val;
                                else if (target === 'email') entry.email = val.toLowerCase();
                                else if (target === 'phone') entry.phone = this.normalizePhone(val);
                                else if (target === 'ticket_type') ticketTypeName = val;
                                else if (target === 'quantity') entry.quantity = Math.max(1, parseInt(val, 10) || 1);
                                else if (target === 'amount') { const n = parseFloat(val.replace(/[^0-9.\-]/g, '')); entry.amount = isNaN(n) ? '' : n; }
                                else if (target === 'status') {
                                    const lc = val.toLowerCase();
                                    if (lc === 'paid' || lc === 'free') entry.status = 'paid';
                                    else if (lc === 'unpaid') entry.status = 'unpaid';
                                }
                                else if (target.startsWith('custom:')) {
                                    const idx = target.slice(7);
                                    const cf = this.eventCustomFields.find(f => String(f.index) === String(idx));
                                    entry.custom_values[idx] = this.normalizeCustomValue(cf, val);
                                }
                                else if (target.startsWith('ticket_custom:')) {
                                    const idx = target.slice(14);
                                    const cf = this.uniqueTicketCustomFields.find(f => String(f.index) === String(idx));
                                    entry.ticket_custom_values[idx] = this.normalizeCustomValue(cf, val);
                                }
                            }
                            if (ticketTypeName) {
                                const match = this.tickets.find(t => t.type.toLowerCase() === ticketTypeName.toLowerCase());
                                if (match) entry.ticket_id = match.id;
                            }
                            entries.push(entry);
                        }
                        if (!entries.length) {
                            this.csvErrors = [@json(__('messages.no_valid_emails'))];
                            return;
                        }
                        this.entries = entries;
                        this.tab = 'form';
                        this.clearCsv();
                    },
                    submit() {
                        this.formErrors = [];
                        const valid = [];
                        const seen = {};
                        const pushErr = (rowNum, msg) => this.formErrors.push(@json(__('messages.row_error', ['row' => ':row', 'error' => ':error'])).replace(':row', rowNum).replace(':error', msg));
                        this.entries.forEach((entry, i) => {
                            const email = (entry.email || '').trim().toLowerCase();
                            if (!email) return;
                            const rowNum = i + 1;
                            if (!this.isValidEmail(email)) {
                                pushErr(rowNum, @json(__('messages.invalid_email')));
                                return;
                            }
                            if (seen[email]) {
                                pushErr(rowNum, @json(__('messages.duplicate_email')));
                                return;
                            }
                            if (this.eventHasPhone && !(entry.phone || '').trim()) {
                                pushErr(rowNum, @json(__('messages.phone').' '.strtolower(__('messages.required'))));
                                return;
                            }
                            const ticketCfs = this.ticketCustomFieldsFor(entry.ticket_id);
                            const missingCf = [
                                ...this.eventCustomFields.filter(cf => cf.required && !(String(entry.custom_values[cf.index] || '').trim())),
                                ...ticketCfs.filter(cf => cf.required && !(String(entry.ticket_custom_values[cf.index] || '').trim())),
                            ];
                            if (missingCf.length) {
                                pushErr(rowNum, missingCf[0].name + ' ' + @json(strtolower(__('messages.required'))));
                                return;
                            }
                            seen[email] = true;
                            valid.push({ ...entry, email });
                        });

                        if (this.formErrors.length) return;
                        if (!valid.length) return;

                        this.submitting = true;

                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = SUBMIT_URL;
                        form.style.display = 'none';

                        const add = (n, v) => {
                            const i = document.createElement('input');
                            i.type = 'hidden';
                            i.name = n;
                            i.value = v;
                            form.appendChild(i);
                        };

                        add('_token', CSRF);
                        add('event_id', EVENT_ID);
                        add('event_date', this.eventDate);
                        add('ticket_id', this.fallbackTicketId);
                        add('default_status', this.defaultStatus);
                        add('send_emails', this.sendEmails ? '1' : '0');

                        valid.forEach((entry, i) => {
                            add(`entries[${i}][name]`, entry.name || '');
                            add(`entries[${i}][email]`, entry.email);
                            add(`entries[${i}][phone]`, entry.phone || '');
                            add(`entries[${i}][ticket_id]`, entry.ticket_id || this.fallbackTicketId);
                            add(`entries[${i}][quantity]`, entry.quantity || 1);
                            add(`entries[${i}][amount]`, entry.amount === '' || entry.amount === null ? '' : entry.amount);
                            add(`entries[${i}][status]`, entry.status || this.defaultStatus);
                            Object.entries(entry.custom_values || {}).forEach(([k, v]) => {
                                if (v !== '' && v !== null && v !== undefined) add(`entries[${i}][custom_values][${k}]`, v);
                            });
                            Object.entries(entry.ticket_custom_values || {}).forEach(([k, v]) => {
                                if (v !== '' && v !== null && v !== undefined) add(`entries[${i}][ticket_custom_values][${k}]`, v);
                            });
                        });

                        window._skipUnsavedWarning = true;
                        document.body.appendChild(form);
                        form.submit();
                    },
                },
                mounted() {
                    if (this.isRecurring && this.$refs.eventDatePicker && window.flatpickr) {
                        const vm = this;
                        flatpickr(this.$refs.eventDatePicker, {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'M j, Y',
                            defaultDate: this.eventDate,
                            enable: this.eventDates.length ? this.eventDates : undefined,
                            onChange(dates, str) {
                                vm.eventDate = str;
                            },
                        });
                    }
                    // Initialize iti for already-rendered phone inputs
                    if (this.showPhone) {
                        this.$nextTick(() => {
                            document.querySelectorAll('#import-attendees-app input[type=tel][data-key]').forEach(el => {
                                this.setPhoneRef(el);
                            });
                            this.matchPhoneHeights();
                        });
                    }
                    window.addEventListener('resize', () => this.matchPhoneHeights());
                    // Warn on navigation with unsaved changes
                    window._importAttendeesIsDirty = false;
                    window.addEventListener('beforeunload', (e) => {
                        if (this.isDirty && !window._skipUnsavedWarning) {
                            e.preventDefault();
                            e.returnValue = '';
                        }
                    });
                    // Absorb any watcher fires from initial setup (deep entries watcher,
                    // sendEmails watcher, iti sync writing entry.phone) so the dirty flag
                    // starts clean.
                    this.$nextTick(() => {
                        this.$nextTick(() => {
                            this.isDirty = false;
                            window._importAttendeesIsDirty = false;
                        });
                    });
                },
            }).mount('#import-attendees-app');
        })();
    </script>
    @endif
</x-app-admin-layout>
