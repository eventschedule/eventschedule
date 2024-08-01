<x-app-layout>

    <x-slot name="head">
        <script>
        // Example: Initialize Flatpickr on elements with the class 'datepicker'
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.datepicker', {
                enableTime: true,
                altInput: true,
                time_24hr: "{{ $venue && $venue->use_24_hour_time ? 'true' : 'false' }}",
                altFormat: "{{ $venue && $venue->use_24_hour_time ? 'F j, Y H:i' : 'F j, Y h:i K' }}",
                dateFormat: "Y-m-d H:i:S",
            });
        });
        </script>
    </x-slot>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ $title }}
    </h2>

    <form method="POST"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain, 'subdomain2' => $subdomain2]) }}">

        @csrf

        @if($event->exists)
        @method('put')
        @endif


        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.venue') }}
                        </h2>

                        @if($venue)
                        <div>{{ $venue->name }}</div>
                        @else
                        <div class="mb-6">
                            <x-input-label for="venue_name" :value="__('messages.name') . ' *'" />
                            <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full"
                                :value="old('venue_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="venue_email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 block w-full"
                                :value="old('venue_email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                        </div>
                        @endif

                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            @if($venue && ! $venue->accept_talent_requests)
                            {{ __('messages.vendor') }}
                            @elseif($venue && ! $venue->accept_vendor_requests)
                            {{ __('messages.talent') }}
                            @else
                            {{ __('messages.talent_vendor') }}
                            @endif
                        </h2>

                        @if($talent)
                        <div>{{ $talent->name }}</div>
                        @elseif($vendor)
                        <div>{{ $vendor->name }}</div>
                        @else

                        @if($venue)
                        @if($venue->accept_talent_requests && $venue->accept_vendor_requests)
                        <fieldset>
                            <x-input-label for="role_type" :value="__('messages.type')" />
                            <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                <div class="flex items-center">
                                    <input id="talent" name="role_type" type="radio" value="talent" CHECKED
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="talent"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">{{ __('messages.talent') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="vendor" name="role_type" type="radio" value="vendor"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="vendor"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900">{{ __('messages.vendor') }}</label>
                                </div>
                            </div>
                        </fieldset>
                        @elseif($venue->accept_talent_requests)
                        <input type="hidden" name="role_type" value="talent" />
                        @elseif($venue->accept_vendor_requests)
                        <input type="hidden" name="role_type" value="vendor" />
                        @endif
                        @endif

                        <div class="mb-6">
                            <x-input-label for="role_name" :value="__('messages.name') . ' *'" />
                            <x-text-input id="role_name" name="role_name" type="text" class="mt-1 block w-full"
                                :value="old('role_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('role_name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="role_email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="role_email" name="role_email" type="email" class="mt-1 block w-full"
                                :value="old('role_email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('role_email')" />
                        </div>

                        @endif

                    </div>
                </div>


                @if (($venue && auth()->user()->isMember($venue->subdomain)) || ! $venue)
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.details') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="starts_at" :value="__('messages.date_and_time')" />
                            <x-text-input type="text" id="starts_at" name="starts_at" class="datepicker"
                                :value="old('starts_at', $event->localStartsAt())" />
                            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="duration" :value="__('messages.duration_in_hours')" />
                            <x-text-input type="number" id="duration" name="duration"
                                :value="old('duration', $event->duration)" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="visibility" :value="__('messages.visibility')" />
                            <select id="visibility" name="visibility"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach(['private', 'unlisted', 'public'] as $level)
                                <option value="{{ $level }}" {{ $event->visibility == $level ? 'SELECTED' : '' }}>
                                    {{ __('messages.' . $level) }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('visibility')" />
                        </div>

                    </div>
                </div>
                @endif

            </div>
        </div>



        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                    <x-cancel-button></x-cancel-button>
                </div>

                <div>
                    @if ($event->exists)
                    <x-delete-button
                        :url="route('event.delete', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)])">
                    </x-delete-button>
                    @endif
                </div>
            </div>
        </div>

    </form>

</x-app-layout>
