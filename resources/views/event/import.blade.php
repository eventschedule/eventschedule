<x-app-admin-layout>
    <script src="{{ asset('js/vue.global.prod.js') }}"></script>

    <h2 class="pt-2 my-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.import_event') }}
    </h2>

    <form method="post"
        action="{{ route('event.import', ['subdomain' => $role->subdomain]) }}"
        enctype="multipart/form-data"
        id="event-import-app">

        @csrf

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-none">
                        <!-- Main desktop grid -->
                        <div class="lg:grid lg:grid-cols-2 lg:gap-6">
                            <!-- Left column: Textarea and Form -->
                            <div>
                                <!-- Textarea section -->
                                <div class="mb-4">
                                    <x-input-label for="event_details" :value="__('messages.event_details')" />
                                    <textarea id="event_details" 
                                        name="event_details" 
                                        rows="4"
                                        v-model="eventDetails"
                                        @input="debouncedPreview"
                                        @paste="handlePaste" autofocus {{ config('services.google.gemini_key') ? '' : 'disabled' }}
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('event_details')" />

                                    @if (! config('services.google.gemini_key'))
                                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.gemini_key_required') }}
                                        </div>
                                    @endif

                                    <!-- Error message display -->
                                    <div v-if="errorMessage" class="mt-4 p-3 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-md">
                                        @{{ errorMessage }}
                                    </div>

                                    <div v-if="isLoading" class="mt-2 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="relative">
                                            <div class="w-4 h-4 rounded-full bg-blue-500/30"></div>
                                            <div class="absolute top-0 left-0 w-4 h-4 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></div>
                                        </div>
                                        <div class="inline-flex items-center">
                                            <span class="animate-pulse">{{ __('messages.loading') }}</span>
                                            <span class="ml-1 inline-flex animate-[ellipsis_1.5s_steps(4,end)_infinite]">...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form fields -->
                                <div v-if="preview" class="space-y-4">
                                    <div>
                                        <x-input-label for="name" :value="__('messages.event_name')" />
                                        <x-text-input id="name" 
                                            name="name" 
                                            type="text" 
                                            class="mt-1 block w-full" 
                                            :value="old('name')"
                                            v-model="preview.parsed.event_name" 
                                            required 
                                            autofocus />
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>

                                    <div>
                                        <x-input-label for="starts_at" :value="__('messages.date_and_time')" />
                                        <x-text-input id="starts_at" 
                                            name="starts_at" 
                                            type="text" 
                                            class="datepicker mt-1 block w-full" 
                                            :value="old('starts_at')"
                                            required 
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                                    </div>

                                    <div>
                                        <x-input-label for="venue_address1" :value="__('messages.address')" />
                                        <x-text-input id="venue_address1" 
                                            name="venue_address1" 
                                            type="text" 
                                            class="mt-1 block w-full" 
                                            :value="old('venue_address1')"
                                            v-model="preview.parsed.event_address" 
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                    </div>

                                    <div class="pt-4 flex gap-3 justify-end">
                                        <button @click="handleClear" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                            {{ __('messages.clear') }}
                                        </button>
                                        <button @click="handleSave" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                            {{ __('messages.save') }}
                                        </button>
                                    </div>

                                </div>
                            </div>

                            <!-- Right column: Image -->
                            <div v-if="preview && preview.parsed.social_image" class="hidden lg:block">
                                <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <img :src="getSocialImageUrl(preview.parsed.social_image)" 
                                         class="object-contain w-full h-full" 
                                         alt="Event preview image">
                                </div>
                            </div>
                        </div>

                        <!-- Mobile image preview -->
                        <div v-if="preview && preview.parsed.social_image" class="lg:hidden mt-6">
                            <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img :src="getSocialImageUrl(preview.parsed.social_image)" 
                                     class="object-contain w-full h-full" 
                                     alt="Event preview image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

    <script>
        const { createApp } = Vue

        function debounce(fn, delay) {
            let timeoutId
            return function (...args) {
                clearTimeout(timeoutId)
                timeoutId = setTimeout(() => fn.apply(this, args), delay)
            }
        }

        createApp({
            data() {
                return {
                    eventDetails: '',
                    preview: null,
                    isLoading: false,
                    errorMessage: null,
                }
            },

            created() {
                this.debouncedPreview = debounce(this.fetchPreview, 500)
            },

            methods: {
                async fetchPreview() {
                    if (! this.eventDetails.trim()) {
                        this.preview = null;
                        return;
                    }

                    this.isLoading = true;
                    this.preview = null;
                    this.errorMessage = null;
                    try {
                        const response = await fetch('{{ route("event.parse", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_details: this.eventDetails,
                                preview: true
                            })
                        });

                        // Handle HTTP error responses before trying to parse JSON
                        if (!response.ok) {
                            if (response.status === 405) {
                                throw new Error('Invalid request method');
                            }
                            if (response.status === 404) {
                                throw new Error('Resource not found');
                            }
                            if (response.status === 403) {
                                throw new Error('Permission denied');
                            }
                            if (response.status === 401) {
                                throw new Error('Unauthorized');
                            }
                            if (response.status === 500) {
                                throw new Error('Server error');
                            }
                        }

                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            throw new Error('Invalid response from server');
                        }

                        if (!response.ok) {
                            // Handle validation errors
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat();
                                throw new Error(errorMessages.join('\n'));
                            }
                            // Handle other types of errors
                            throw new Error(data.message || data.error || 'An unexpected error occurred');
                        }

                        this.preview = data;
                        
                        // Initialize datepicker after preview is loaded
                        this.$nextTick(() => {
                            flatpickr('.datepicker', {
                                allowInput: true,
                                enableTime: true,
                                altInput: true,
                                time_24hr: "{{ $role && $role->use_24_hour_time ? 'true' : 'false' }}",
                                altFormat: "{{ $role && $role->use_24_hour_time ? 'M j, Y • H:i' : 'M j, Y • h:i K' }}",
                                dateFormat: "Y-m-d H:i:S",
                                defaultDate: this.preview.parsed.event_date_time
                            });
                            // Prevent keyboard input as per edit view
                            const f = document.querySelector('.datepicker')._flatpickr;
                            f._input.onkeydown = () => false;
                        })
                    } catch (error) {
                        console.error('Error fetching preview:', error)
                        this.errorMessage = error.message || 'An error occurred while fetching the preview';
                    } finally {
                        this.isLoading = false
                    }
                },

                handlePaste(event) {
                    // Prevent the debounced preview from firing
                    event.preventDefault()
                    // Get the pasted text
                    const pastedText = event.clipboardData.getData('text')
                    // Update the model manually
                    this.eventDetails = pastedText
                    // Call preview immediately
                    this.fetchPreview()
                },

                handleEdit() {
                    this.preview = null
                    this.$nextTick(() => {
                        document.getElementById('event_details').focus()
                    })
                },

                async handleSave() {
                    this.errorMessage = null;
                    try {
                        // Get the date value from flatpickr
                        const dateInput = document.querySelector('.datepicker')._flatpickr;
                        if (!dateInput.selectedDates[0]) {
                            throw new Error('{{ __("messages.date_required") }}');
                        }

                        const response = await fetch('{{ route("event.import", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_details: this.eventDetails,
                                name: document.getElementById('name').value,
                                starts_at: dateInput.selectedDates[0].toISOString(),
                                venue_address1: document.getElementById('venue_address1').value
                            })
                        });

                        // Handle HTTP error responses before trying to parse JSON
                        if (!response.ok) {
                            if (response.status === 405) {
                                throw new Error('Invalid request method');
                            }
                            if (response.status === 404) {
                                throw new Error('Resource not found');
                            }
                            if (response.status === 403) {
                                throw new Error('Permission denied');
                            }
                            if (response.status === 401) {
                                throw new Error('Unauthorized');
                            }
                            if (response.status === 500) {
                                throw new Error('Server error');
                            }
                        }

                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            throw new Error('Invalid response from server');
                        }

                        if (!response.ok) {
                            // Handle validation errors
                            if (data.errors) {
                                const errorMessages = Object.values(data.errors).flat();
                                throw new Error(errorMessages.join('\n'));
                            }
                            // Handle other types of errors
                            throw new Error(data.message || data.error || 'An unexpected error occurred');
                        }

                        if (data.success) {
                            window.location.href = data.redirect_url;
                        }
                    } catch (error) {
                        console.error('Error saving event:', error);
                        this.errorMessage = error.message || 'An error occurred while saving the event';
                    }
                },

                getYouTubeEmbedUrl(url) {
                    // Extract video ID from various YouTube URL formats
                    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                    const match = url.match(regExp);
                    const videoId = match && match[2].length === 11 ? match[2] : null;
                    
                    return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
                },

                getSocialImageUrl(path) {
                    // Extract filename from /tmp/event_XXXXX.jpg path
                    const filename = path.split('/').pop().replace('event_', '');
                    return `{{ route('event.tmp_image', ['filename' => '']) }}/${filename}`;
                },

                handleClear() {
                    this.eventDetails = '';
                    this.preview = null;
                    this.$nextTick(() => {
                        document.getElementById('event_details').focus();
                    });
                },
            }
        }).mount('#event-import-app')
    </script>

</x-app-admin-layout>