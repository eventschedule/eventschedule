<x-app-admin-layout>

    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>

            <div class="flex items-center text-end">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_from_eventbrite') }}
                </h2>
            </div>
        @else
            <div class="flex items-center">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_from_eventbrite') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" class="js-back-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('messages.back') }}
                </button>
            </div>
        @endif
    </div>

    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>

    <style {!! nonce_attr() !!}>
        #eventbrite-app {
            visibility: hidden;
        }
        #eventbrite-app.loaded {
            visibility: visible;
        }
    </style>

    <div id="eventbrite-app">
        {{-- Step 1: Connect --}}
        <div v-if="step === 'connect'">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="max-w-lg">
                    <label for="eventbrite-token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('messages.eventbrite_token_label') }}
                    </label>
                    <div class="relative">
                        <input
                            :type="showToken ? 'text' : 'password'"
                            id="eventbrite-token"
                            v-model="token"
                            class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] pe-10"
                            placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxx"
                            @keyup.enter="connectToEventbrite"
                        >
                        <button type="button" @click="showToken = !showToken" class="absolute inset-y-0 {{ is_rtl() ? 'start-0 ps-3' : 'end-0 pe-3' }} flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg v-if="!showToken" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.eventbrite_token_help') }}
                    </p>

                    <div v-if="errorMessage" class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-300" v-text="errorMessage"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" @click="connectToEventbrite" :disabled="connecting || !token.trim()" class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-b from-[#5A8DFF] to-[#4E81FA] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:from-[#4E81FA] hover:to-[#3D6FE8] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm">
                            <svg v-if="connecting" class="animate-spin {{ is_rtl() ? 'ms-2' : 'me-2' }} h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span v-text="connecting ? connectingText : connectText"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Select Events --}}
        <div v-if="step === 'select'">
            {{-- Connected banner --}}
            <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-green-800 dark:text-green-300" v-text="connectedAsText"></span>
                    </div>
                    <button type="button" @click="disconnect" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline">
                        {{ __('messages.eventbrite_disconnect') }}
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                {{-- Header with toggle and select all --}}
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA] dark:bg-gray-900">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.eventbrite_select_all') }}</span>
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showPastEvents = false" :class="!showPastEvents ? 'bg-[#4E81FA] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200">
                            {{ __('messages.eventbrite_upcoming_only') }}
                        </button>
                        <button type="button" @click="showPastEvents = true" :class="showPastEvents ? 'bg-[#4E81FA] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all duration-200">
                            {{ __('messages.eventbrite_show_past') }}
                        </button>
                    </div>
                </div>

                {{-- Event list --}}
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    <template v-for="event in filteredEvents" :key="event.eventbrite_id">
                        <label class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                            <input type="checkbox" :value="event.eventbrite_id" v-model="selectedIds" class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA] dark:bg-gray-900 flex-shrink-0">
                            <div v-if="event.image_url" class="flex-shrink-0">
                                <img :src="event.image_url" class="w-16 h-12 object-cover rounded-lg" :alt="event.name">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" v-text="event.name"></span>
                                    <span v-if="event.status === 'live'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Live</span>
                                    <span v-else-if="event.status === 'draft'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Draft</span>
                                    <span v-else-if="event.status === 'completed' || event.status === 'ended'" class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Ended</span>
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span v-if="event.start_local" v-text="formatDate(event.start_local)"></span>
                                    <span v-if="event.venue" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                        <span v-text="event.venue.name"></span>
                                    </span>
                                    <span v-if="event.is_online" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                                        Online
                                    </span>
                                    <span v-if="event.tickets && event.tickets.length" class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" /></svg>
                                        <span v-text="event.tickets.length + ' ' + (event.tickets.length === 1 ? ticketSingular : ticketPlural)"></span>
                                    </span>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>

                {{-- No events matching filter --}}
                <div v-if="filteredEvents.length === 0" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.eventbrite_no_events') }}
                </div>
            </div>

            {{-- Import button --}}
            <div class="mt-4 flex {{ is_rtl() ? 'justify-start' : 'justify-end' }}">
                <button type="button" @click="startImport" :disabled="selectedIds.length === 0" class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-b from-[#5A8DFF] to-[#4E81FA] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:from-[#4E81FA] hover:to-[#3D6FE8] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm">
                    <span v-text="importButtonText"></span>
                </button>
            </div>
        </div>

        {{-- Step 3: Importing --}}
        <div v-if="step === 'importing'">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                {{-- Progress bar --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" v-text="importProgressText"></span>
                        <span class="text-sm text-gray-500 dark:text-gray-400" v-text="Math.round((importProgress / importTotal) * 100) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-[#4E81FA] h-2.5 rounded-full transition-all duration-300" :style="{ width: Math.round((importProgress / importTotal) * 100) + '%' }"></div>
                    </div>
                </div>

                {{-- Event status list --}}
                <div class="space-y-3">
                    <template v-for="event in selectedEvents" :key="event.eventbrite_id">
                        <div class="flex items-center gap-3 p-3 rounded-lg" :class="importResults[event.eventbrite_id] ? (importResults[event.eventbrite_id].success ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10') : (importingId === event.eventbrite_id ? 'bg-blue-50 dark:bg-blue-900/10' : 'bg-gray-50 dark:bg-gray-800')">
                            {{-- Status icon --}}
                            <div class="flex-shrink-0">
                                {{-- Importing spinner --}}
                                <svg v-if="importingId === event.eventbrite_id" class="animate-spin h-5 w-5 text-[#4E81FA]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{-- Success check --}}
                                <svg v-else-if="importResults[event.eventbrite_id] && importResults[event.eventbrite_id].success" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{-- Error X --}}
                                <svg v-else-if="importResults[event.eventbrite_id] && !importResults[event.eventbrite_id].success" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{-- Waiting --}}
                                <div v-else class="h-5 w-5 rounded-full border-2 border-gray-300 dark:border-gray-600"></div>
                            </div>

                            {{-- Event info --}}
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" v-text="event.name"></span>
                            </div>

                            {{-- Action links --}}
                            <div v-if="importResults[event.eventbrite_id] && importResults[event.eventbrite_id].success" class="flex items-center gap-3 flex-shrink-0">
                                <a :href="importResults[event.eventbrite_id].view_url" class="text-sm text-[#4E81FA] hover:text-[#3D6FE8] font-medium">{{ __('messages.view') }}</a>
                                <a :href="importResults[event.eventbrite_id].edit_url" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-medium">{{ __('messages.edit') }}</a>
                            </div>
                            <div v-else-if="importResults[event.eventbrite_id] && !importResults[event.eventbrite_id].success" class="flex-shrink-0">
                                <span class="text-sm text-red-600 dark:text-red-400">{{ __('messages.failed') }}</span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Done button --}}
                <div v-if="importComplete" class="mt-6 flex flex-col items-center gap-3">
                    <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ __('messages.eventbrite_import_complete') }}</p>
                    <a href="{{ $role->getGuestUrl() }}" class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-b from-[#5A8DFF] to-[#4E81FA] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:from-[#4E81FA] hover:to-[#3D6FE8] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.view_schedule') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            if (e.target.closest('.js-back-btn')) {
                history.back();
            }
        });

        const { createApp } = Vue;

        const app = createApp({
            data() {
                return {
                    step: 'connect',
                    token: '',
                    showToken: false,
                    connecting: false,
                    errorMessage: '',
                    userName: '',
                    events: [],
                    selectedIds: [],
                    showPastEvents: false,
                    importingId: null,
                    importProgress: 0,
                    importTotal: 0,
                    importResults: {},
                    importComplete: false,
                    lastVenue: null,
                    connectingText: @json(__('messages.eventbrite_connecting')),
                    connectText: @json(__('messages.eventbrite_connect')),
                    ticketSingular: @json(__('messages.ticket')),
                    ticketPlural: @json(__('messages.tickets')),
                };
            },
            computed: {
                selectAll: {
                    get() {
                        return this.filteredEvents.length > 0
                            && this.filteredEvents.every(e => this.selectedIds.includes(e.eventbrite_id));
                    },
                    set(val) {
                        const filteredIds = this.filteredEvents.map(e => e.eventbrite_id);
                        if (val) {
                            const combined = new Set([...this.selectedIds, ...filteredIds]);
                            this.selectedIds = [...combined];
                        } else {
                            this.selectedIds = this.selectedIds.filter(id => !filteredIds.includes(id));
                        }
                    },
                },
                filteredEvents() {
                    if (this.showPastEvents) {
                        return this.events;
                    }
                    return this.events.filter(e => e.is_upcoming);
                },
                selectedEvents() {
                    return this.events.filter(e => this.selectedIds.includes(e.eventbrite_id));
                },
                connectedAsText() {
                    return @json(__('messages.eventbrite_connected_as', ['name' => ':name'])).replace(':name', this.userName);
                },
                importButtonText() {
                    return @json(__('messages.eventbrite_import_selected', ['count' => ':count'])).replace(':count', this.selectedIds.length);
                },
                importProgressText() {
                    return @json(__('messages.eventbrite_import_progress', ['current' => ':current', 'total' => ':total']))
                        .replace(':current', this.importProgress)
                        .replace(':total', this.importTotal);
                },
            },
            methods: {
                async connectToEventbrite() {
                    if (!this.token.trim() || this.connecting) return;

                    this.connecting = true;
                    this.errorMessage = '';

                    try {
                        const response = await fetch(@json(route('event.eventbrite_connect', ['subdomain' => $role->subdomain])), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ token: this.token }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            this.errorMessage = data.error || @json(__('messages.eventbrite_connection_failed'));
                            return;
                        }

                        this.userName = data.user_name;
                        this.events = data.events;
                        this.step = 'select';
                    } catch (error) {
                        this.errorMessage = @json(__('messages.eventbrite_connection_failed'));
                    } finally {
                        this.connecting = false;
                    }
                },

                disconnect() {
                    this.step = 'connect';
                    this.events = [];
                    this.selectedIds = [];
                    this.userName = '';
                    this.errorMessage = '';
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr.replace(' ', 'T'));
                    return date.toLocaleDateString(undefined, {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    });
                },

                async startImport() {
                    if (this.selectedIds.length === 0) return;

                    this.step = 'importing';
                    this.importTotal = this.selectedIds.length;
                    this.importProgress = 0;
                    this.importResults = {};
                    this.importComplete = false;
                    this.lastVenue = null;

                    for (const event of this.selectedEvents) {
                        this.importingId = event.eventbrite_id;

                        try {
                            const body = {
                                token: this.token,
                                eventbrite_id: event.eventbrite_id,
                                name: event.name,
                                start_local: event.start_local,
                                duration: event.duration,
                                category_id: event.category_id,
                                currency: event.currency,
                                image_url: event.image_url,
                                is_online: event.is_online,
                                tickets: event.tickets,
                            };

                            // Handle venue deduplication
                            if (event.venue) {
                                if (this.lastVenue && this.lastVenue.name === event.venue.name
                                    && (this.lastVenue.city || '') === (event.venue.city || '')) {
                                    body.venue_id = this.lastVenue.id;
                                } else {
                                    body.venue = event.venue;
                                }
                            }

                            const response = await fetch(@json(route('event.eventbrite_import', ['subdomain' => $role->subdomain])), {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(body),
                            });

                            const data = await response.json();

                            if (response.ok && data.success) {
                                this.importResults = { ...this.importResults, [event.eventbrite_id]: {
                                    success: true,
                                    view_url: data.event.view_url,
                                    edit_url: data.event.edit_url,
                                }};

                                // Track venue for deduplication
                                if (data.venue) {
                                    this.lastVenue = data.venue;
                                }
                            } else {
                                this.importResults = { ...this.importResults, [event.eventbrite_id]: {
                                    success: false,
                                    error: data.error,
                                }};
                            }
                        } catch (error) {
                            this.importResults = { ...this.importResults, [event.eventbrite_id]: {
                                success: false,
                                error: error.message,
                            }};
                        }

                        this.importProgress++;
                        this.importingId = null;
                    }

                    this.importComplete = true;
                },
            },
        });

        app.mount('#eventbrite-app');
        document.getElementById('eventbrite-app').classList.add('loaded');
    </script>

</x-app-admin-layout>
