<x-app-admin-layout>

    <x-slot name="head">
        <script>
            function onChange() {
                var value = $('#venue_id').find(':selected').val();
                var no_email = $('#no_email').is(':checked');        

                if (value) {
                    $('#email_div').hide();
                    $('#primary_button').text("{{ __('messages.next') }}");
                    $('#venue_email').removeAttr('required');
                } else {
                    $('#email_div').show();
                    if (no_email) {
                        $('#primary_button').text("{{ __('messages.next') }}");                    
                        $('#venue_email').removeAttr('required');
                    } else {
                        $('#primary_button').text("{{ __('messages.search') }}");                    
                        $('#venue_email').attr('required', 'required');
                    }
                }
            }

            $(document).ready(function() {
                $('#no_email').on('change', onChange);
            });
        </script>
    </x-slot>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.add_event') }}
    </h2>

    <form method="GET" action="{{ route('event.create', ['subdomain' => $subdomain]) }}">

        @if (request()->role_id)
        <input type="hidden" name="role_id" value="{{ request()->role_id }}" />
        @elseif (request()->role_email)
        <input type="hidden" name="role_email" value="{{ request()->role_email }}" />
        @endif

        @if (request()->date)
        <input type="hidden" name="date" value="{{ request()->date }}" />
        @endif

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.venue') }}
                        </h2>

                        @if (count($venues))
                        <div class="mb-6">
                            <select id="venue_id" name="venue_id"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                onchange="onChange()" required>
                                <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                @foreach ($venues as $venue)
                                <option value="{{ App\Utils\UrlUtils::encodeId($venue->id) }}">{{ $venue->getDisplayName() }}</option>
                                @endforeach
                                <option value="">{{ __('messages.search_create') }}</option>
                            </select>
                        </div>
                        @endif

                        <div id="email_div" class="{{ count($venues) ? 'hidden' : '' }}">
                            <div class="mb-6">
                                <x-input-label for="venue_email" :value="__('messages.email') . ' *'" />
                                <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 block w-full"
                                    :value="old('venue_email')" autofocus/>
                                <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                            </div>

                            <div class="mb-6">
                                <x-checkbox id="no_email" name="no_email" label="{{ __('messages.private_space_no_email') }}"
                                    checked="{{ old('no_email', false) }}"
                                    data-custom-attribute="value"/>
                                <x-input-error class="mt-2" :messages="$errors->get('no_email')" />
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button id="primary_button">{{ __(count($venues) ? 'messages.next' : 'messages.search') }}</x-primary-button>
                    <x-cancel-button></x-cancel-button>
                </div>
            </div>
        </div>

    </form>

</x-app-admin-layout>