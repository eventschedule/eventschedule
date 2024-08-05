<x-app-layout>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.add_event') }}
    </h2>

    <form method="GET" action="{{ route('event.create', ['subdomain' => $subdomain]) }}">

        @if (request()->date)
        <input type="hidden" name="date" value="{{ request()->date }}"/>
        @endif

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ $header }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="role_email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="role_email" name="role_email" type="email" class="mt-1 block w-full"
                                :value="old('role_email')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('role_email')" />
                        </div>

                    </div>
                </div>

            </div>
        </div>



        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex gap-4 items-center justify-between">
                <div class="flex gap-4">
                    <x-primary-button>{{ __('messages.search') }}</x-primary-button>

                    <x-cancel-button></x-cancel-button>
                </div>
            </div>
        </div>

    </form>

</x-app-layout>