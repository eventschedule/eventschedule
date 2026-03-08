@props([
    'name',
    'title',
    'description',
    'fields',
    'endpoint',
    'successCallback',
    'extraDataCallback' => null,
    'checkValuesCallback' => null,
    'showInstructions' => true,
    'errorMessage',
    'partialErrorMessage' => null,
    'promptEndpoint' => null,
    'instructionsLabel' => null,
    'instructionsPlaceholder' => null,
    'savedInstructions' => '',
    'saveInstructionsField' => null,
    'imageEndpoint' => null,
    'imageElements' => [],
])

<x-modal :name="$name" maxWidth="lg">
    <div class="p-6" x-data="aiGenerateModal_{{ Str::camel($name) }}">
        <div class="text-center mb-4">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 mb-3">
                <svg class="w-6 h-6 text-[#4E81FA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $description }}</p>
        </div>

        <div class="mb-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.ai_style_select_elements') }}</p>
            <div class="space-y-2">
                @foreach ($fields as $field)
                <label class="flex items-center gap-2" :class="generationStarted ? 'opacity-60 pointer-events-none' : 'cursor-pointer'">
                    <input type="checkbox" :checked="elements.includes('{{ $field['key'] }}')" @change="toggleElement('{{ $field['key'] }}')" :disabled="generationStarted"
                        class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                    <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $field['label'] }}</span>
                    <span class="flex items-center justify-center gap-1 shrink-0 w-4">
                        <span x-show="fieldsWithValues.includes('{{ $field['key'] }}') && !elementStatus['{{ $field['key'] }}']" x-cloak class="w-2 h-2 rounded-full bg-[#4E81FA]" title="{{ __('messages.has_existing_value') }}"></span>
                        {{-- Per-element status indicators --}}
                        <template x-if="elementStatus['{{ $field['key'] }}'] === 'generating'">
                            <svg class="animate-spin h-4 w-4 text-[#4E81FA]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="elementStatus['{{ $field['key'] }}'] === 'complete'">
                            <svg class="h-4 w-4 text-[#4E81FA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                        <template x-if="elementStatus['{{ $field['key'] }}'] === 'error'">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <button type="button" @click="retryElement('{{ $field['key'] }}')" class="text-xs text-[#4E81FA] hover:underline">{{ __('messages.retry') }}</button>
                            </span>
                        </template>
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        @if ($promptEndpoint)
        <div class="mb-4">
            <button type="button" @click="togglePromptEditor()" class="flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4 transition-transform" :class="promptExpanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>{{ __('messages.ai_view_edit_prompt') }}</span>
            </button>

            <div x-show="promptExpanded" x-cloak class="mt-2">
                <div x-show="promptLoading" class="flex items-center justify-center py-8">
                    <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div x-show="!promptLoading" x-cloak>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">{{ __('messages.ai_prompt_edit_hint') }}</p>
                    <textarea x-model="customPrompt" rows="6" maxlength="5000"
                        class="w-full font-mono text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]"></textarea>
                    <template x-for="imageKey in Object.keys(customImagePrompts)" :key="imageKey">
                        <div class="mt-3">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1" x-text="imagePromptLabels[imageKey] || imageKey"></p>
                            <textarea x-model="customImagePrompts[imageKey]" rows="4" maxlength="5000"
                                class="w-full font-mono text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]"></textarea>
                        </div>
                    </template>
                    <button type="button" x-show="promptEdited" x-cloak @click="resetPrompt()" class="mt-1 text-xs text-[#4E81FA] hover:underline">
                        {{ __('messages.ai_prompt_reset') }}
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if ($showInstructions)
        <div class="mb-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $instructionsLabel ?: __('messages.ai_style_instructions') }}</label>
            <textarea x-model="styleInstructions" maxlength="500" rows="2"
                placeholder="{{ $instructionsPlaceholder ?: __('messages.ai_style_instructions_placeholder') }}"
                class="mt-1 w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg shadow-sm focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]"></textarea>
            @if ($saveInstructionsField)
            <label x-show="styleInstructions.length > 0" x-cloak class="flex items-center gap-2 mt-1.5 cursor-pointer">
                <input type="checkbox" x-model="saveInstructions"
                    class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('messages.ai_save_as_default') }}</span>
            </label>
            @endif
        </div>
        @endif

        <div x-show="hasCheckedWithValue" x-cloak class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
            <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ __('messages.ai_style_replace_warning') }}</span>
            </p>
        </div>

        <div class="flex flex-row gap-3">
            <button type="button" x-on:click="cancelGeneration()"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.cancel') }}
            </button>
            <button type="button" @click="generate()" :disabled="generating || elements.length === 0"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-[#4E81FA] border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-[#3D6FE8] focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50">
                <template x-if="generating">
                    <span class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 ltr:mr-2 rtl:ml-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="generatingText"></span>
                    </span>
                </template>
                <template x-if="!generating">
                    <span class="inline-flex items-center">
                        <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                        {{ __('messages.generate') }}
                    </span>
                </template>
            </button>
        </div>
    </div>
</x-modal>

<script {!! nonce_attr() !!}>
document.addEventListener('alpine:init', function() {
    Alpine.data('aiGenerateModal_{{ Str::camel($name) }}', function() {
        return {
            generating: false,
            generationStarted: false,
            allFields: @json(collect($fields)->pluck('key')->values()),
            defaultElements: @json(collect($fields)->where('has_value', false)->pluck('key')->values()),
            elements: @json(collect($fields)->where('has_value', false)->pluck('key')->values()),
            fieldsWithValues: @json(collect($fields)->where('has_value', true)->pluck('key')->values()),
            styleInstructions: @json($savedInstructions ?? ''),
            saveInstructions: false,
            generatingText: @json(__('messages.generating')),
            elementStatus: {},
            pendingRequests: 0,
            imageElementKeys: @json($imageElements),
            generationId: 0,
            @if ($promptEndpoint)
            customPrompt: '',
            defaultPrompt: '',
            defaultImagePrompts: {},
            customImagePrompts: {},
            imagePromptLabels: { profile_image: '{{ __('messages.profile_image') }}', header_image: '{{ __('messages.header_image') }}', background_image: '{{ __('messages.background_image') }}' },
            promptExpanded: false,
            promptLoading: false,
            promptFetched: false,
            get promptEdited() {
                if (this.customPrompt !== this.defaultPrompt) return true;
                var self = this;
                return Object.keys(this.customImagePrompts).some(function(key) {
                    return self.customImagePrompts[key] !== self.defaultImagePrompts[key];
                });
            },
            @endif
            get hasCheckedWithValue() {
                return this.elements.some(el => this.fieldsWithValues.includes(el));
            },
            init() {
                var self = this;
                window.addEventListener('open-modal', function(e) {
                    if (e.detail === @json($name)) {
                        @if ($checkValuesCallback)
                        var currentValues = window[{!! json_encode($checkValuesCallback) !!}]();
                        if (currentValues) {
                            self.fieldsWithValues = currentValues;
                            self.defaultElements = self.allFields.filter(function(key) {
                                return !currentValues.includes(key);
                            });
                        }
                        @endif
                        self.elements = [...self.defaultElements];
                        self.styleInstructions = @json($savedInstructions ?? '');
                        self.saveInstructions = false;
                        self.elementStatus = {};
                        self.generationStarted = false;
                        self.pendingRequests = 0;
                        self.generating = false;
                        @if ($promptEndpoint)
                        self.customPrompt = '';
                        self.defaultPrompt = '';
                        self.defaultImagePrompts = {};
                        self.customImagePrompts = {};
                        self.promptExpanded = false;
                        self.promptLoading = false;
                        self.promptFetched = false;
                        @endif
                    }
                });
            },
            toggleElement: function(el) {
                var idx = this.elements.indexOf(el);
                if (idx > -1) { this.elements.splice(idx, 1); } else { this.elements.push(el); }
                @if ($promptEndpoint)
                if (this.promptExpanded && !this.promptEdited) {
                    this.fetchPrompt();
                }
                @endif
            },
            cancelGeneration: function() {
                this.generationId++;
                this.generating = false;
                this.generationStarted = false;
                window.dispatchEvent(new CustomEvent('close-modal', { detail: @json($name) }));
            },
            getBaseBody: function() {
                var body = {};

                @if ($showInstructions)
                body.style_instructions = this.styleInstructions;
                if (this.saveInstructions) {
                    body.save_instructions = true;
                }
                @endif

                @if ($promptEndpoint)
                if (this.promptEdited && this.customPrompt) {
                    body.custom_prompt = this.customPrompt;
                }
                @endif

                @if ($extraDataCallback)
                var extraData = window[{!! json_encode($extraDataCallback) !!}]();
                if (extraData) {
                    Object.assign(body, extraData);
                }
                @endif

                return body;
            },
            makeRequest: function(url, body) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                }).then(function(response) {
                    if (response.status === 429) {
                        throw new Error('rate_limit');
                    }
                    return response.json();
                });
            },
            @if ($promptEndpoint)
            togglePromptEditor: function() {
                this.promptExpanded = !this.promptExpanded;
                if (this.promptExpanded && !this.promptEdited) {
                    this.fetchPrompt();
                }
            },
            fetchPrompt: function() {
                var self = this;

                this.promptLoading = true;
                var body = {
                    elements: this.elements
                };

                @if ($extraDataCallback)
                var extraData = window[{!! json_encode($extraDataCallback) !!}]();
                if (extraData) {
                    Object.assign(body, extraData);
                }
                @endif

                this.makeRequest(@json($promptEndpoint), body)
                .then(function(data) {
                    if (data.success) {
                        self.defaultPrompt = data.prompt;
                        self.customPrompt = data.prompt;
                        self.promptFetched = true;

                        if (data.image_prompts) {
                            self.defaultImagePrompts = JSON.parse(JSON.stringify(data.image_prompts));
                            self.customImagePrompts = JSON.parse(JSON.stringify(data.image_prompts));
                        } else {
                            self.defaultImagePrompts = {};
                            self.customImagePrompts = {};
                        }
                    }
                })
                .catch(function() {
                    self.promptLoading = false;
                })
                .finally(function() {
                    self.promptLoading = false;
                });
            },
            resetPrompt: function() {
                this.customPrompt = this.defaultPrompt;
                this.customImagePrompts = JSON.parse(JSON.stringify(this.defaultImagePrompts));
            },
            @endif
            checkComplete: function() {
                if (this.pendingRequests <= 0) {
                    this.generating = false;
                    var hasErrors = Object.values(this.elementStatus).some(function(s) { return s === 'error'; });
                    if (!hasErrors) {
                        this.generationStarted = false;
                        window.dispatchEvent(new CustomEvent('close-modal', { detail: @json($name) }));
                    }
                }
            },
            fireImageRequest: function(imageKey, extraBody) {
                var self = this;
                var genId = self.generationId;
                self.pendingRequests++;
                self.elementStatus[imageKey] = 'generating';

                var body = Object.assign({}, self.getBaseBody(), { image_type: imageKey }, extraBody || {});

                @if ($promptEndpoint)
                delete body.custom_prompt;
                if (self.customImagePrompts[imageKey] && self.defaultImagePrompts[imageKey] && self.customImagePrompts[imageKey] !== self.defaultImagePrompts[imageKey]) {
                    body.custom_prompt = self.customImagePrompts[imageKey];
                }
                @endif

                self.makeRequest(@json($imageEndpoint ?? ''), body)
                .then(function(data) {
                    if (genId !== self.generationId) return;
                    if (data.success) {
                        window[{!! json_encode($successCallback) !!}](data);
                        self.elementStatus[imageKey] = 'complete';
                    } else {
                        self.elementStatus[imageKey] = 'error';
                    }
                })
                .catch(function() {
                    if (genId !== self.generationId) return;
                    self.elementStatus[imageKey] = 'error';
                })
                .finally(function() {
                    if (genId !== self.generationId) return;
                    self.pendingRequests--;
                    self.checkComplete();
                });
            },
            retryElement: function(key) {
                if (!this.imageElementKeys.includes(key)) return;
                if (this.elementStatus[key] === 'generating') return;
                this.generating = true;
                this.generationStarted = true;

                var extraBody = {};
                @if ($name === 'ai-style-generator')
                var accentInput = document.getElementById('accent_color');
                if (accentInput) extraBody.accent_color = accentInput.value;
                @endif

                this.fireImageRequest(key, extraBody);
            },
            generate: function() {
                if (this.elements.length === 0) return;
                this.generating = true;
                this.generationStarted = true;
                this.elementStatus = {};
                var self = this;
                var genId = ++this.generationId;

                var hasImageEndpoint = @json(!empty($imageEndpoint));
                var selectedImages = this.elements.filter(function(el) { return self.imageElementKeys.includes(el); });
                var textElements = this.elements.filter(function(el) { return !self.imageElementKeys.includes(el); });

                // If no image endpoint, use legacy single-request behavior
                if (!hasImageEndpoint) {
                    this.elements.forEach(function(el) { self.elementStatus[el] = 'generating'; });
                    var body = Object.assign({}, this.getBaseBody(), { elements: this.elements });

                    this.makeRequest(@json($endpoint), body)
                    .then(function(data) {
                        if (genId !== self.generationId) return;
                        if (data.success) {
                            window[{!! json_encode($successCallback) !!}](data);
                            self.elements.forEach(function(el) { self.elementStatus[el] = 'complete'; });

                            @if ($partialErrorMessage)
                            if (data.image_error) {
                                alert(@json($partialErrorMessage));
                            }
                            @endif

                            window.dispatchEvent(new CustomEvent('close-modal', { detail: @json($name) }));
                        } else {
                            self.elements.forEach(function(el) { self.elementStatus[el] = 'error'; });
                            alert(data.error || @json($errorMessage));
                        }
                    })
                    .catch(function(err) {
                        if (genId !== self.generationId) return;
                        self.elements.forEach(function(el) { self.elementStatus[el] = 'error'; });
                        if (err.message === 'rate_limit') {
                            alert(@json(__('messages.ai_rate_limit')));
                        } else {
                            alert(@json($errorMessage));
                        }
                    })
                    .finally(function() {
                        if (genId !== self.generationId) return;
                        self.generating = false;
                        self.generationStarted = false;
                    });
                    return;
                }

                // Separate AJAX: text first, then images
                textElements.forEach(function(el) { self.elementStatus[el] = 'generating'; });
                selectedImages.forEach(function(el) { self.elementStatus[el] = 'pending'; });

                var fireImages = function(extraBody) {
                    if (selectedImages.length === 0) {
                        self.checkComplete();
                        return;
                    }
                    selectedImages.forEach(function(imageKey) {
                        self.fireImageRequest(imageKey, extraBody);
                    });
                };

                if (textElements.length > 0) {
                    var body = Object.assign({}, this.getBaseBody(), { elements: textElements });

                    this.makeRequest(@json($endpoint), body)
                    .then(function(data) {
                        if (genId !== self.generationId) return;
                        if (data.success) {
                            window[{!! json_encode($successCallback) !!}](data);
                            textElements.forEach(function(el) { self.elementStatus[el] = 'complete'; });

                            // For style modal: pass generated accent_color to image requests
                            var imageExtra = {};
                            if (data.accent_color) {
                                imageExtra.accent_color = data.accent_color;
                            }
                            fireImages(imageExtra);
                        } else {
                            textElements.forEach(function(el) { self.elementStatus[el] = 'error'; });
                            // Still fire images using form's current values
                            var imageExtra = {};
                            @if ($name === 'ai-style-generator')
                            var accentInput = document.getElementById('accent_color');
                            if (accentInput) imageExtra.accent_color = accentInput.value;
                            @endif
                            fireImages(imageExtra);
                        }
                    })
                    .catch(function(err) {
                        if (genId !== self.generationId) return;
                        textElements.forEach(function(el) { self.elementStatus[el] = 'error'; });
                        if (err.message === 'rate_limit') {
                            alert(@json(__('messages.ai_rate_limit')));
                        }
                        // Still fire images
                        var imageExtra = {};
                        @if ($name === 'ai-style-generator')
                        var accentInput = document.getElementById('accent_color');
                        if (accentInput) imageExtra.accent_color = accentInput.value;
                        @endif
                        fireImages(imageExtra);
                    });
                } else {
                    // Only images selected, no text request needed
                    var imageExtra = {};
                    @if ($name === 'ai-style-generator')
                    var accentInput = document.getElementById('accent_color');
                    if (accentInput) imageExtra.accent_color = accentInput.value;
                    @endif
                    fireImages(imageExtra);
                }
            }
        };
    });
});
</script>
