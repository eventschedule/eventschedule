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
                                        v-bind:readonly="savedEvent"
                                        @input="debouncedPreview"
                                        @paste="handlePaste" autofocus {{ config('services.google.gemini_key') ? '' : 'disabled' }}
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('event_details')" />

                                    @if (! config('services.google.gemini_key'))
                                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.gemini_key_required') }}
                                        </div>
                                    @endif

                                    <!-- Show matching event if found -->
                                    <div v-if="preview && preview.event_url" class="mt-4 p-3 text-sm bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-md">
                                        {{ __('messages.similar_event_found') }} - 
                                        <a :href="preview.event_url" 
                                           target="_blank" 
                                           class="underline hover:text-yellow-600 dark:hover:text-yellow-300">
                                            {{ __('messages.view_event') }}
                                        </a>
                                    </div>

                                    <!-- Error message display -->
                                    <div v-if="errorMessage" class="mt-4 p-3 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-md">
                                        @{{ errorMessage }}
                                    </div>

                                    <div v-if="isLoading" class="mt-4 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
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
                                            v-bind:readonly="savedEvent"
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
                                            v-bind:readonly="savedEvent"
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
                                            v-bind:readonly="preview.parsed.venue_id || savedEvent"
                                            placeholder="{{ $role->isCurator() ? $role->city : '' }}"
                                            required
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                    </div>

                                    <div class="pt-4 flex gap-3 justify-end">
                                        <template v-if="savedEvent">
                                            <button @click="handleClear" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                                {{ __('messages.clear') }}
                                            </button>
                                            <button @click="handleEdit" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                                {{ __('messages.edit') }}
                                            </button>
                                            <button @click="handleView" type="button" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors">
                                                {{ __('messages.view') }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <button @click="handleClear" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                                                {{ __('messages.clear') }}
                                            </button>
                                            <button @click="handleSave" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                                                {{ __('messages.save') }}
                                            </button>
                                        </template>
                                    </div>

                                    @if (config('app.debug'))
                                        <pre v-if="preview">@{{ JSON.stringify(preview.parsed, null, 2) }}</pre>
                                    @endif

                                </div>
                            </div>

                            <!-- Right column: Image -->
                            <div v-if="preview" class="hidden lg:block">
                                <div class="relative">
                                    <!-- Image preview -->
                                    <div v-if="preview.parsed.social_image" 
                                         class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                        <img :src="getSocialImageUrl(preview.parsed.social_image)" 
                                             class="object-contain w-full h-full" 
                                             alt="Event preview image">
                                        
                                        <!-- Remove image button -->
                                        <button @click="removeImage" 
                                                type="button"
                                                class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Drop zone -->
                                    <div v-else
                                         @dragover.prevent="dragOver"
                                         @dragleave.prevent="dragLeave"
                                         @drop.prevent="handleDrop"
                                         @click="$refs.fileInput.click()"
                                         :class="['aspect-w-16 aspect-h-9 rounded-lg border-2 border-dashed flex items-center justify-center cursor-pointer', 
                                                  isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600']">
                                        <div class="text-center py-20">
                                            <!-- Show loading spinner when uploading -->
                                            <template v-if="isUploadingImage">
                                                <div class="relative mx-auto w-12 h-12">
                                                    <div class="w-12 h-12 rounded-full bg-blue-500/30"></div>
                                                    <div class="absolute top-0 left-0 w-12 h-12 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
                                                </div>
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ __('messages.uploading') }}...
                                                </p>
                                            </template>
                                            <!-- Default upload icon and text -->
                                            <template v-else>
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ __('messages.drag_drop_image') }}
                                                </p>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Hidden file input -->
                                    <input type="file" 
                                           ref="fileInput"
                                           @change="handleFileSelect"
                                           accept="image/*"
                                           class="hidden">
                                </div>
                            </div>
                        </div>

                        <!-- Mobile image preview -->
                        <div v-if="preview && preview.parsed.social_image" class="lg:hidden mt-6">
                            <div class="relative">
                                <div class="aspect-w-16 aspect-h-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    <img :src="getSocialImageUrl(preview.parsed.social_image)" 
                                         class="object-contain w-full h-full" 
                                         alt="Event preview image">
                                    <!-- Remove image button -->
                                    <button @click="removeImage" 
                                            type="button"
                                            class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
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
                    isUploadingImage: false,
                    errorMessage: null,
                    savedEvent: null,
                    isDragging: false,
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
                    if (this.savedEvent) {
                        window.open(this.savedEvent.edit_url, '_blank');
                    }
                },

                handleView() {
                    if (this.savedEvent) {
                        window.open(this.savedEvent.view_url, '_blank');
                    }
                },

                async handleSave() {
                    this.errorMessage = null;
                    try {
                        // Get the date value from flatpickr
                        const dateInput = document.querySelector('.datepicker')._flatpickr;
                        if (!dateInput.selectedDates[0]) {
                            throw new Error('{{ __("messages.date_required") }}');
                        }

                        var parsed = this.preview.parsed;
                        var talentId = parsed.talent_id ?? 'new_talent';
                        var members = {};

                        if (parsed.talent_id || (parsed.performer_name && parsed.performer_youtube_url)) {
                            members[talentId] = {
                                name: parsed.performer_name,
                                name_en: parsed.performer_name_en,
                                email: parsed.performer_email,
                                youtube_url: parsed.performer_youtube_url ?? '',
                                language_code: '{{ $role->language_code }}',
                            }
                        }

                        const response = await fetch('{{ route("event.import", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                venue_name: parsed.venue_name,
                                venue_name_en: parsed.venue_name_en,
                                venue_address1: document.getElementById('venue_address1').value || "{{ $role->isCurator() ? $role->city : '' }}",
                                venue_address1_en: parsed.venue_address1_en,
                                venue_city: parsed.event_city,
                                venue_city_en: parsed.event_city_en,
                                venue_state: parsed.event_state,
                                venue_state_en: parsed.event_state_en,
                                venue_postal_code: parsed.event_postal_code,
                                venue_country_code: parsed.event_country_code,
                                venue_email: '',
                                venue_id: parsed.venue_id,
                                venue_language_code: '{{ $role->language_code }}',
                                members: members,
                                name: document.getElementById('name').value,
                                name_en: parsed.event_name_en,
                                starts_at: document.getElementById('starts_at').value,
                                duration: parsed.event_duration,
                                description: document.getElementById('event_details').value,
                                social_image: parsed.social_image,
                                registration_url: parsed.registration_url,
                                @if ($role->isCurator())
                                    curators: ['{{ \App\Utils\UrlUtils::encodeId($role->id) }}'],
                                @endif
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
                            Toastify({
                                text: '{{ __("messages.event_created") }}',
                                duration: 3000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#4BB543',
                                }
                            }).showToast();
                            this.savedEvent = data.event;
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
                    this.savedEvent = null;
                    this.$nextTick(() => {
                        document.getElementById('event_details').focus();
                    });
                },

                dragOver(e) {
                    this.isDragging = true
                },

                dragLeave(e) {
                    this.isDragging = false
                },

                async handleDrop(e) {
                    this.isDragging = false
                    const files = e.dataTransfer.files
                    if (files.length > 0) {
                        await this.uploadImage(files[0])
                    }
                },

                async handleFileSelect(e) {
                    const files = e.target.files
                    if (files.length > 0) {
                        await this.uploadImage(files[0])
                    }
                },

                async uploadImage(file) {
                    if (!file.type.startsWith('image/')) {
                        this.errorMessage = '{{ __("messages.invalid_image_type") }}'
                        return
                    }

                    this.isUploadingImage = true
                    const formData = new FormData()
                    formData.append('image', file)
                    formData.append('event_details', this.eventDetails)

                    try {
                        const response = await fetch('{{ route("event.parse", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })

                        const data = await response.json()
                        this.preview = data
                    } catch (error) {
                        console.error('Error uploading image:', error)
                        this.errorMessage = '{{ __("messages.error_uploading_image") }}'
                    } finally {
                        this.isUploadingImage = false
                    }
                },

                removeImage() {
                    if (this.preview && this.preview.parsed) {
                        this.preview.parsed.social_image = null
                    }
                },
            }
        }).mount('#event-import-app')
    </script>

</x-app-admin-layout>