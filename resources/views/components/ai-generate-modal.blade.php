@props([
    'name',
    'title',
    'description',
    'fields',
    'endpoint',
    'successCallback',
    'extraDataCallback' => null,
    'showInstructions' => true,
    'slowGeneration' => false,
    'errorMessage',
    'partialErrorMessage' => null,
])

<x-modal :name="$name" maxWidth="md">
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
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" :checked="elements.includes('{{ $field['key'] }}')" @change="toggleElement('{{ $field['key'] }}')"
                        class="rounded border-gray-300 dark:border-gray-600 text-[#4E81FA] focus:ring-[#4E81FA]">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $field['label'] }}</span>
                    @if ($field['has_value'])
                    <span class="w-2 h-2 rounded-full bg-[#4E81FA]" title="{{ __('messages.has_existing_value') }}"></span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>

        @if ($showInstructions)
        <div class="mb-4">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.ai_style_instructions') }}</label>
            <textarea x-model="styleInstructions" maxlength="500" rows="2"
                placeholder="{{ __('messages.ai_style_instructions_placeholder') }}"
                class="mt-1 w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA]"></textarea>
        </div>
        @endif

        <p class="text-xs text-amber-600 dark:text-amber-400 mb-4 flex items-start gap-1">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ __('messages.ai_style_replace_warning') }}
        </p>

        <div class="flex flex-row gap-3">
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.cancel') }}
            </button>
            <button type="button" @click="generate()" :disabled="generating || elements.length === 0"
                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-[#3D6FE8] focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50">
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
            elements: @json(collect($fields)->where('has_value', false)->pluck('key')->values()),
            styleInstructions: '',
            generatingText: @json($slowGeneration ? __('messages.generating_this_may_take_a_minute') : __('messages.generating')),
            toggleElement: function(el) {
                var idx = this.elements.indexOf(el);
                if (idx > -1) { this.elements.splice(idx, 1); } else { this.elements.push(el); }
            },
            generate: function() {
                if (this.elements.length === 0) return;
                this.generating = true;
                var self = this;

                var body = {
                    elements: this.elements
                };

                @if ($showInstructions)
                body.style_instructions = this.styleInstructions;
                @endif

                @if ($extraDataCallback)
                var extraData = window[{!! json_encode($extraDataCallback) !!}]();
                if (extraData) {
                    Object.assign(body, extraData);
                }
                @endif

                fetch(@json($endpoint), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        window[{!! json_encode($successCallback) !!}](data);

                        @if ($partialErrorMessage)
                        if (data.image_error) {
                            alert(@json($partialErrorMessage));
                        }
                        @endif

                        window.dispatchEvent(new CustomEvent('close-modal', { detail: @json($name) }));
                    } else {
                        alert(data.error || @json($errorMessage));
                    }
                })
                .catch(function() {
                    alert(@json($errorMessage));
                })
                .finally(function() {
                    self.generating = false;
                });
            }
        };
    });
});
</script>
