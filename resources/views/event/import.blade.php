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
                                @paste="handlePaste"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('event_details')" />
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

                        const data = await response.json()
                        this.preview = data.preview
                    } catch (error) {
                        console.error('Error fetching preview:', error)
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