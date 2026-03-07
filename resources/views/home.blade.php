<x-app-admin-layout>
    <div>
        
        <!-- Get Started Panel -->
        @if($schedules->isEmpty() && $venues->isEmpty() && $curators->isEmpty() && auth()->user()->tickets()->count() === 0 && !is_demo_mode())
        <div class="mb-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                    Welcome {{ auth()->user()->firstName() }}, let's get started
                </h2>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.create_your_first_schedule') }}</p>
            </div>

            <!-- Schedule Types -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Talent -->
                <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-blue-300 dark:hover:border-blue-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ __('messages.talent') }}</h3>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-1">{{ __('messages.talent_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 self-start">{{ __('messages.talent_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.talent_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3"><b><i>Your events</i></b> at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Venue -->
                <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-sky-300 dark:hover:border-sky-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-sky-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-sky-500 dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ __('messages.venue') }}</h3>
                        <p class="text-sm font-medium text-sky-600 dark:text-sky-400 mt-1">{{ __('messages.venue_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 self-start">{{ __('messages.venue_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.venue_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at <b><i>your venue</i></b></p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-sky-600 dark:text-sky-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>

                <!-- Curator -->
                <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex flex-col rounded-xl bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700/50 hover:border-indigo-300 dark:hover:border-indigo-500/50 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/10 overflow-hidden">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-800/20 p-6 flex items-center justify-center">
                        <svg class="w-16 h-16 text-indigo-500 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                        </svg>
                    </div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ __('messages.curator') }}</h3>
                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mt-1">{{ __('messages.curator_tagline') }}</p>
                        <span class="inline-block mt-3 mb-3 px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 self-start">{{ __('messages.curator_best_for') }}</span>
                        <ul class="space-y-2 mb-4 flex-1">
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_1') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_2') }}
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.curator_feature_3') }}
                            </li>
                        </ul>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Various events at various venues</p>
                        <div class="flex items-center gap-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400 group-hover:gap-2 transition-all">
                            {{ __('messages.get_started_cta') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @elseif (config('app.hosted'))
        <div class="mb-6 w-full">
            <form id="feedback-form" class="w-full">
                @csrf
                <div class="relative w-full">
                    <textarea
                        id="feedback-textarea"
                        name="feedback"
                        placeholder="{{ __('messages.feedback_placeholder') }}"
                        class="w-full px-4 py-2 pr-12 pb-10 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 min-h-[135px] sm:min-h-0"
                        rows="2"
                        dir="auto"
                    ></textarea>
                    <button 
                        type="button"
                        id="feedback-submit-btn"
                        class="absolute bottom-2 right-2 p-2 mb-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all opacity-0 pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed"
                        style="transition: opacity 0.2s ease-in-out;"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @endif

        @if($roleIds->isNotEmpty())
        {{-- Customize Button --}}
        <div class="flex justify-end mb-4">
            <button type="button" x-data x-on:click="$dispatch('open-modal', 'customize-dashboard')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-all duration-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('messages.customize_dashboard') }}
            </button>
        </div>

        {{-- Configurable Dashboard Panels --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            @foreach($dashboardConfig['panels'] as $panel)
                @if($panel['visible'])
                    <div class="{{ $panel['size'] === 'full' ? 'lg:col-span-2' : '' }}">
                        @include('home.panels.' . $panel['id'], ['size' => $panel['size']])
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Customize Dashboard Modal --}}
        <x-modal name="customize-dashboard" maxWidth="lg">
            <div x-data='{
                panels: @json($dashboardConfig["panels"]),
                labels: {
                    stats: @json(__("messages.panel_stats")),
                    upcoming_events: @json(__("messages.panel_upcoming_events")),
                    recent_activity: @json(__("messages.panel_recent_activity")),
                    calendar: @json(__("messages.panel_calendar"))
                },
                saving: false,
                sortableInstance: null,
                init() {
                    this.$nextTick(() => {
                        const list = this.$refs.panelList;
                        if (list && typeof Sortable !== "undefined") {
                            this.sortableInstance = Sortable.create(list, {
                                handle: ".drag-handle",
                                animation: 150,
                                ghostClass: "opacity-50",
                                onEnd: (evt) => {
                                    const item = this.panels.splice(evt.oldIndex, 1)[0];
                                    this.panels.splice(evt.newIndex, 0, item);
                                }
                            });
                        }
                    });
                },
                async save() {
                    this.saving = true;
                    try {
                        const response = await fetch(@json(route("home.save_config")), {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]")?.getAttribute("content") || "",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({ panels: this.panels })
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.location.reload();
                        }
                    } catch (e) {
                        this.saving = false;
                    }
                }
            }'>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.customize_dashboard') }}</h3>
                    <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6">
                    <div x-ref="panelList" class="space-y-2">
                        <template x-for="(panel, index) in panels" :key="panel.id">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-gray-200 dark:border-gray-700/50">
                                {{-- Drag Handle --}}
                                <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="5" r="1.5"/><circle cx="15" cy="5" r="1.5"/><circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/><circle cx="9" cy="15" r="1.5"/><circle cx="15" cy="15" r="1.5"/><circle cx="9" cy="20" r="1.5"/><circle cx="15" cy="20" r="1.5"/></svg>
                                </div>

                                {{-- Toggle --}}
                                <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                    <input type="checkbox" x-model="panel.visible" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[#4E81FA] transition-colors"></div>
                                    <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                </label>

                                {{-- Label --}}
                                <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-300" x-text="labels[panel.id]"></span>

                                {{-- Size Selector --}}
                                <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                    <button type="button"
                                        x-on:click="panel.size = 'half'"
                                        :class="panel.size === 'half'
                                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-650'"
                                        class="px-3 py-1 text-xs font-medium transition-all duration-200">
                                        {{ __('messages.size_half') }}
                                    </button>
                                    <div class="w-px bg-gray-200 dark:bg-white/[0.08]"></div>
                                    <button type="button"
                                        x-on:click="panel.size = 'full'"
                                        :class="panel.size === 'full'
                                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_2px_4px_rgba(0,0,0,0.5)]'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-650'"
                                        class="px-3 py-1 text-xs font-medium transition-all duration-200">
                                        {{ __('messages.size_full') }}
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" x-on:click="save()" :disabled="saving" class="px-4 py-2 text-sm font-medium text-white bg-[#4E81FA] rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-all duration-200">
                        <span x-show="!saving">{{ __('messages.save') }}</span>
                        <span x-show="saving" x-cloak>{{ __('messages.saving') }}...</span>
                    </button>
                </div>
            </div>
        </x-modal>
        @endif

    </div>

    @if($roleIds->isNotEmpty() && collect($dashboardConfig['panels'])->where('id', 'stats')->where('visible', true)->isNotEmpty())
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    @endif
    @if($roleIds->isNotEmpty())
    <script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>
    @endif
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackForm = document.getElementById('feedback-form');
            const feedbackTextarea = document.getElementById('feedback-textarea');
            const submitButton = document.getElementById('feedback-submit-btn');
            
            if (feedbackForm && feedbackTextarea && submitButton) {
                // Show/hide submit button based on textarea content
                function toggleSubmitButton() {
                    const hasText = feedbackTextarea.value.trim().length > 0;
                    if (hasText) {
                        submitButton.classList.remove('opacity-0', 'pointer-events-none');
                        submitButton.classList.add('opacity-100');
                    } else {
                        submitButton.classList.add('opacity-0', 'pointer-events-none');
                        submitButton.classList.remove('opacity-100');
                    }
                }
                
                // Listen for input changes
                feedbackTextarea.addEventListener('input', toggleSubmitButton);
                feedbackTextarea.addEventListener('keyup', toggleSubmitButton);
                
                // Handle form submission
                async function submitFeedback() {
                    const feedback = feedbackTextarea.value.trim();
                    if (!feedback) {
                        return;
                    }
                    
                    submitButton.disabled = true;
                    
                    try {
                        const formData = new FormData();
                        formData.append('feedback', feedback);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');
                        
                        const response = await fetch('{{ route("home.feedback") }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            Toastify({
                                text: data.message || @json(__("messages.feedback_submitted")),
                                duration: 3000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#4BB543',
                                }
                            }).showToast();
                            
                            feedbackTextarea.value = '';
                            toggleSubmitButton();
                        } else {
                            Toastify({
                                text: data.message || @json(__("messages.feedback_failed")),
                                duration: 5000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#FF0000',
                                }
                            }).showToast();
                        }
                    } catch (error) {
                        Toastify({
                            text: @json(__("messages.feedback_failed")),
                            duration: 5000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#FF0000',
                            }
                        }).showToast();
                    } finally {
                        submitButton.disabled = false;
                    }
                }
                
                // Handle button click
                submitButton.addEventListener('click', submitFeedback);
                
                // Handle Enter key (Ctrl+Enter or Cmd+Enter to submit)
                feedbackTextarea.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        submitFeedback();
                    }
                });
            }

            // Sparkline chart
            const sparklineCanvas = document.getElementById('sparkline-chart');
            if (sparklineCanvas && typeof Chart !== 'undefined') {
                const sparklineData = @json($sparklineData ?? []);
                const ctx = sparklineCanvas.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 48);
                gradient.addColorStop(0, 'rgba(14, 165, 233, 0.3)');
                gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: sparklineData.map((_, i) => i),
                        datasets: [{
                            data: sparklineData,
                            borderColor: '#0ea5e9',
                            backgroundColor: gradient,
                            borderWidth: 1.5,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: {
                            x: { display: false },
                            y: { display: false, beginAtZero: true }
                        },
                        animation: false,
                    }
                });
            }
        });
    </script>
</x-app-admin-layout>
