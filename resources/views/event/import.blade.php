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
                    <div class="max-w-xl">
                    
                        <div class="mb-6">
                            <x-input-label for="event_details" :value="__('messages.event_details')" />
                            <textarea id="event_details" 
                                name="event_details" 
                                rows="5"
                                v-model="eventDetails"
                                @input="debouncedPreview"
                                @paste="handlePaste" autofocus
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('event_details')" />
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

                            <style>
                                @keyframes ellipsis {
                                    to {
                                        width: 1.25em;
                                    }
                                }
                            </style>
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
                }
            },

            created() {
                this.debouncedPreview = debounce(this.fetchPreview, 500)
            },

            methods: {
                async fetchPreview() {
                    if (! this.eventDetails.trim()) {
                        this.preview = null
                        return
                    }

                    this.isLoading = true
                    try {
                        console.log('fetching preview');
                        console.log('{{ route("event.import", ["subdomain" => $role->subdomain]) }}');
                        
                        const response = await fetch('{{ route("event.import", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_details: this.eventDetails
                            })
                        })

                        console.log('response', response);

                        const data = await response.json()
                        this.preview = data.preview
                    } catch (error) {
                        console.error('Error fetching preview:', error)
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
                    try {
                        const response = await fetch('{{ route("event.import", ["subdomain" => $role->subdomain]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_details: this.eventDetails
                            })
                        })

                        const data = await response.json()
                        if (data.success) {
                            window.location.href = data.redirect_url
                        }
                    } catch (error) {
                        console.error('Error saving event:', error)
                    }
                }
            }
        }).mount('#event-import-app')
    </script>

</x-app-admin-layout>