<x-app-layout>

    <x-slot name="head">
        <script>
            function onSelectChange() {
                var value = $('#venue_id').find(':selected').val();
                if (value) {
                    $('#email_div').hide();
                    $('#primary_button').text("{{ __('messages.next') }}");
                    $('#venue_email').removeAttr('required');
                } else {
                    $('#email_div').show();
                    $('#primary_button').text("{{ __('messages.search') }}");
                    $('#venue_email').attr('required', 'required');
                }
            }
            onSelectChange();
        </script>
    </x-slot>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.add_event') }}
    </h2>

    <form method="GET" action="{{ route('event.create', ['subdomain' => $subdomain]) }}">

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
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                onchange="onSelectChange()">
                                @foreach ($venues as $venue)
                                <option value="{{ App\Utils\UrlUtils::encodeId($venue->id) }}">{{ $venue->name }}</option>
                                @endforeach
                                <option value="">{{ __('messages.other') }}</option>
                            </select>
                        </div>
                        @endif

                        <div id="email_div" class="mb-6 {{ count($venues) ? 'hidden' : '' }}">
                            <x-input-label for="venue_email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 block w-full"
                                :value="old('venue_email')" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
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

</x-app-layout>