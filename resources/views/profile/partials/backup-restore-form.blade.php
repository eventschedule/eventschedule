<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
            </svg>
            {{ __('messages.backup_and_restore') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.backup_description') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    <div id="backup-app" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        {{-- Tab Bar --}}
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6 mt-6">
            <nav class="flex space-x-2 sm:space-x-6" aria-label="Tabs">
                <button type="button" data-tab="export" @click="activeTab = 'export'"
                    :class="activeTab === 'export' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    {{ __('messages.backup_export') }}
                </button>
                <button type="button" data-tab="import" @click="activeTab = 'import'"
                    :class="activeTab === 'import' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                    class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                    {{ __('messages.backup_import') }}
                </button>
            </nav>
        </div>

        {{-- Export Section --}}
        <div v-show="activeTab === 'export'">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.backup_export_description') }}
                @if (config('queue.default') !== 'sync')
                    {{ __('messages.backup_export_email_note') }}
                @endif
            </p>

            @if (config('app.hosted'))
            <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                <div class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('messages.backup_newsletter_exclusion_warning') }}</p>
                </div>
            </div>
            @endif

            <form class="mt-4 space-y-6" @submit.prevent="onExportSubmit">

                <div v-if="roles.length > 0">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.backup_select_schedules') }}</label>
                    <div class="space-y-2">
                        <label v-for="role in roles" :key="role.id" class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="role_ids[]" :value="role.id" v-model="selectedRoleIds"
                                class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                            <span class="text-sm text-gray-700 dark:text-gray-300">@{{ role.name }} <span class="text-gray-400">(@{{ role.type_label }})</span></span>
                        </label>
                    </div>
                </div>

                <div class="mt-3">
                    <x-toggle name="include_images" id="include_images" :checked="false" :label="__('messages.backup_include_images')" :help="__('messages.backup_include_images_help')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button type="submit" v-bind:disabled="exportProcessing || selectedRoleIds.length === 0">
                        {{ __('messages.backup_export') }}
                    </x-primary-button>
                </div>
            </form>

            {{-- Export Status --}}
            <div v-if="exportJob" class="mt-6">
                <div v-if="exportJob.status === 'pending' || exportJob.status === 'processing'" class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                    <svg class="animate-spin h-5 w-5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        {{ __('messages.backup_processing') }}
                        <span v-if="exportJob.progress">@{{ exportJob.progress.current_label }} (@{{ exportJob.progress.current }}/@{{ exportJob.progress.total }})</span>
                    </span>
                    <button type="button" @click="cancelJob('export')" class="ml-auto text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        {{ __('messages.cancel') }}
                    </button>
                </div>

                <div v-if="exportJob.status === 'completed' && exportJob.download_url" class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 dark:text-green-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-green-700 dark:text-green-300">
                        {{ __('messages.backup_export_complete') }}
                        <a :href="exportJob.download_url" :download="'backup-' + new Date().toISOString().slice(0,10) + '.zip'" class="text-[#4E81FA] hover:underline font-medium">{{ __('messages.backup_download_button') }}</a>
                        <span class="text-gray-500 dark:text-gray-400"> ({{ __('messages.backup_expires_in_7_days') }})</span>
                    </span>
                </div>

                <div v-if="exportJob.status === 'failed'" class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span class="text-sm text-red-700 dark:text-red-300">@{{ exportJob.error || exportFailedMessage }}</span>
                </div>
            </div>

            @if (! config('app.hosted'))
            <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                <div class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('messages.backup_pii_warning') }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Import Section --}}
        <div v-show="activeTab === 'import'">
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.backup_import_description') }}</p>

            @if (! config('app.hosted'))
            <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                <div class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ __('messages.backup_mysqldump_recommendation') }}</p>
                </div>
            </div>
            @endif

            <div class="mt-4 space-y-4">
                <div v-if="!importPreview">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.backup_select_file') }}</label>
                    <input type="file" accept=".zip" @change="onFileSelected" ref="fileInput"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-800 file:text-white hover:file:bg-gray-700 dark:file:bg-gray-200 dark:file:text-gray-800 dark:hover:file:bg-white file:cursor-pointer">

                    <div v-show="selectedFile" class="flex items-center gap-4 mt-4">
                        <x-primary-button type="button" @click="uploadFile" v-bind:disabled="!selectedFile || uploadProcessing">
                            {{ __('messages.backup_upload') }}
                        </x-primary-button>
                        <span v-if="uploadProcessing" class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.backup_uploading') }}...</span>
                    </div>

                    <div v-if="uploadError" class="mt-3 flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="text-sm text-red-700 dark:text-red-300">@{{ uploadError }}</span>
                    </div>
                </div>

                {{-- Import Preview --}}
                <div v-if="importPreview" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4">
                    <div class="flex items-start gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('messages.backup_import_preview_notice') }}</p>
                    </div>

                    <div class="space-y-3">
                        <label v-for="(schedule, index) in importPreview" :key="index" class="flex items-start gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg cursor-pointer">
                            <input type="checkbox" :value="index" v-model="selectedImportIndices"
                                class="mt-1 rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">@{{ schedule.name }} <span class="text-gray-400">(@{{ schedule.type }})</span></span>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @{{ schedule.events }} {{ strtolower(__('messages.events')) }}
                                    &middot; @{{ schedule.tickets }} {{ strtolower(__('messages.tickets')) }}
                                    &middot; @{{ schedule.sales }} {{ strtolower(__('messages.sales')) }}
                                    <span v-if="schedule.date_range"> &middot; @{{ schedule.date_range }}</span>
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center gap-4 mt-4">
                        <x-primary-button type="button" @click="startImport" v-bind:disabled="selectedImportIndices.length === 0 || importProcessing">
                            {{ __('messages.backup_start_import') }}
                        </x-primary-button>
                        <button type="button" @click="cancelImport" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('messages.cancel') }}
                        </button>
                    </div>
                </div>

                {{-- Import Status --}}
                <div v-if="importJob">
                    <div v-if="importJob.status === 'pending' || importJob.status === 'processing'" class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <svg class="animate-spin h-5 w-5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-blue-700 dark:text-blue-300">
                            {{ __('messages.backup_processing') }}
                            <span v-if="importJob.progress">@{{ importJob.progress.current_label }} (@{{ importJob.progress.current }}/@{{ importJob.progress.total }})</span>
                        </span>
                        <button type="button" @click="cancelJob('import')" class="ml-auto text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                            {{ __('messages.cancel') }}
                        </button>
                    </div>

                    <div v-if="importJob.status === 'completed'" class="p-3 rounded-lg" :class="importHasErrors ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700'">
                        <div class="flex items-center gap-3">
                            <svg v-if="importHasErrors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 dark:text-green-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm" :class="importHasErrors ? 'text-amber-700 dark:text-amber-300' : 'text-green-700 dark:text-green-300'">
                                @if (config('queue.default') !== 'sync')
                                    {{ __('messages.backup_import_complete') }}
                                @else
                                    {{ __('messages.backup_import_complete_sync') }}
                                @endif
                            </span>
                        </div>
                        <div v-if="importJob.report" class="mt-3 space-y-3">
                            <div v-for="(schedule, sIdx) in importJob.report" :key="sIdx" class="text-sm text-gray-700 dark:text-gray-300">
                                <p v-if="importJob.report.length > 1" class="font-medium text-gray-900 dark:text-gray-100 mb-1">@{{ schedule.name }}</p>
                                <div v-if="schedule.error" class="text-red-600 dark:text-red-400">@{{ schedule.error }}</div>
                                <div v-else class="space-y-1">
                                    <template v-for="entity in ['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters']" :key="entity">
                                        <div v-if="schedule[entity] && (schedule[entity].success > 0 || schedule[entity].failed > 0)" class="flex items-center gap-2">
                                            <span>@{{ formatEntityName(entity) }}:</span>
                                            <span class="text-green-700 dark:text-green-400">@{{ schedule[entity].success }} {{ strtolower(__('messages.backup_imported')) }}</span>
                                            <span v-if="schedule[entity].failed > 0" class="text-red-600 dark:text-red-400">@{{ schedule[entity].failed }} {{ strtolower(__('messages.failed')) }}</span>
                                        </div>
                                    </template>
                                    <div v-if="schedule.subdomain" class="mt-2">
                                        <a :href="scheduleBaseUrl.replace('__SUBDOMAIN__', schedule.subdomain)" class="text-[#4E81FA] hover:underline text-sm font-medium">
                                            {{ __('messages.backup_view_schedule') }} &rarr;
                                        </a>
                                    </div>
                                    <div v-if="schedule.warnings && schedule.warnings.length > 0" class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded">
                                        <div v-for="(warning, wIdx) in schedule.warnings" :key="wIdx" class="flex items-center gap-2 text-amber-700 dark:text-amber-300 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <span>@{{ warning }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="importJob.status === 'failed'" class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-600 dark:text-red-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span class="text-sm text-red-700 dark:text-red-300">@{{ importJob.error || importFailedMessage }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>

@php
$roleData = $editorRoles->map(function($role) {
    return [
        'id' => $role->id,
        'name' => $role->name,
        'type_label' => ucfirst($role->type),
        'checked' => true,
    ];
})->values();
@endphp

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Vue === 'undefined') return;

    var backupEl = document.getElementById('backup-app');
    if (!backupEl) return;

    var roles = @json($roleData);

    var initialExportJobId = {{ $activeExportJobId ?? 'null' }};
    var initialImportJobId = {{ $activeImportJobId ?? 'null' }};

    Vue.createApp({
        data: function() {
            return {
                activeTab: 'export',
                roles: roles,
                selectedRoleIds: roles.map(function(r) { return r.id; }),
                exportProcessing: false,
                exportJob: null,
                exportPollTimer: null,
                selectedFile: null,
                uploadProcessing: false,
                uploadError: null,
                importPreview: null,
                importFilePath: null,
                selectedImportIndices: [],
                importProcessing: false,
                importJob: null,
                importPollTimer: null,
                exportFailedMessage: @json(__('messages.backup_export_failed')),
                importFailedMessage: @json(__('messages.backup_import_failed')),
                scheduleBaseUrl: @json(route('role.view_guest', ['subdomain' => '__SUBDOMAIN__'])),
            };
        },
        mounted: function() {
            if (initialExportJobId) {
                this.exportProcessing = true;
                this.pollExportStatus(initialExportJobId);
            }

            if (initialImportJobId) {
                this.activeTab = 'import';
                this.importProcessing = true;
                this.pollImportStatus(initialImportJobId);
            }
        },
        computed: {
            importHasErrors: function() {
                if (!this.importJob || !this.importJob.report) return false;
                return this.importJob.report.some(function(s) { return s.error; });
            },
        },
        beforeUnmount: function() {
            if (this.exportPollTimer) clearInterval(this.exportPollTimer);
            if (this.importPollTimer) clearInterval(this.importPollTimer);
        },
        methods: {
            onExportSubmit: function() {
                var self = this;
                self.exportProcessing = true;
                self.exportJob = { status: 'pending' };

                fetch('{{ route("backup.export") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        role_ids: self.selectedRoleIds,
                        include_images: document.getElementById('include_images').checked,
                    }),
                })
                .then(function(r) {
                    if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || self.exportFailedMessage); });
                    return r.json();
                })
                .then(function(data) {
                    self.pollExportStatus(data.job_id);
                })
                .catch(function(err) {
                    self.exportJob = { status: 'failed', error: err.message };
                    self.exportProcessing = false;
                });
            },
            pollExportStatus: function(jobId) {
                var self = this;
                self.exportPollTimer = setInterval(function() {
                    fetch('/settings/backup/status/' + jobId, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        self.exportJob = data;
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(self.exportPollTimer);
                            self.exportProcessing = false;
                        }
                    })
                    .catch(function() {
                        clearInterval(self.exportPollTimer);
                        self.exportProcessing = false;
                        self.exportJob = { status: 'failed', error: self.exportFailedMessage };
                    });
                }, 2000);
            },
            onFileSelected: function(e) {
                this.selectedFile = e.target.files[0] || null;
                this.uploadError = null;
            },
            uploadFile: function() {
                if (!this.selectedFile) return;

                var self = this;
                self.uploadProcessing = true;
                self.uploadError = null;

                var formData = new FormData();
                formData.append('file', self.selectedFile);

                fetch('{{ route("backup.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                .then(function(r) {
                    if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || 'Upload failed'); });
                    return r.json();
                })
                .then(function(data) {
                    self.importPreview = data.preview;
                    self.importFilePath = data.file_path;
                    self.selectedImportIndices = data.preview.map(function(_, i) { return i; });
                    self.uploadProcessing = false;
                })
                .catch(function(err) {
                    self.uploadError = err.message;
                    self.uploadProcessing = false;
                });
            },
            startImport: function() {
                var self = this;
                self.importProcessing = true;
                self.importJob = { status: 'pending' };

                fetch('{{ route("backup.confirm") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        file_path: self.importFilePath,
                        selected_indices: self.selectedImportIndices,
                    }),
                })
                .then(function(r) {
                    if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || 'Import failed'); });
                    return r.json();
                })
                .then(function(data) {
                    self.importPreview = null;
                    self.pollImportStatus(data.job_id);
                })
                .catch(function(err) {
                    self.importPreview = null;
                    self.uploadError = err.message;
                    self.importProcessing = false;
                });
            },
            cancelImport: function() {
                if (this.importFilePath) {
                    fetch('{{ route("backup.cancel_upload") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ file_path: this.importFilePath }),
                    });
                }
                this.importPreview = null;
                this.importFilePath = null;
                this.selectedImportIndices = [];
                this.selectedFile = null;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },
            cancelJob: function(type) {
                var self = this;
                var job = type === 'export' ? self.exportJob : self.importJob;
                if (!job) return;

                fetch('/settings/backup/cancel/' + job.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                .then(function(r) {
                    if (!r.ok) return r.json().then(function(d) { throw new Error(d.error || 'Cancel failed'); });
                    return r.json();
                })
                .then(function() {
                    if (type === 'export') {
                        if (self.exportPollTimer) clearInterval(self.exportPollTimer);
                        self.exportJob = null;
                        self.exportProcessing = false;
                    } else {
                        if (self.importPollTimer) clearInterval(self.importPollTimer);
                        self.importJob = null;
                        self.importProcessing = false;
                    }
                })
                .catch(function(err) {
                    if (type === 'export') {
                        self.exportJob = { status: 'failed', error: err.message };
                        self.exportProcessing = false;
                    } else {
                        self.importJob = { status: 'failed', error: err.message };
                        self.importProcessing = false;
                    }
                });
            },
            formatEntityName: function(key) {
                var names = {
                    schedules: @json(__('messages.backup_entity_schedules')),
                    sub_schedules: @json(__('messages.backup_entity_sub_schedules')),
                    events: @json(__('messages.backup_entity_events')),
                    tickets: @json(__('messages.backup_entity_tickets')),
                    sales: @json(__('messages.backup_entity_sales')),
                    promo_codes: @json(__('messages.backup_entity_promo_codes')),
                    newsletters: @json(__('messages.backup_entity_newsletters')),
                };
                return names[key] || key;
            },
            pollImportStatus: function(jobId) {
                var self = this;
                self.importPollTimer = setInterval(function() {
                    fetch('/settings/backup/status/' + jobId, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        self.importJob = data;
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(self.importPollTimer);
                            self.importProcessing = false;
                        }
                    })
                    .catch(function() {
                        clearInterval(self.importPollTimer);
                        self.importProcessing = false;
                        self.importJob = { status: 'failed', error: self.importFailedMessage };
                    });
                }, 2000);
            },
        },
    }).mount('#backup-app');
});
</script>
