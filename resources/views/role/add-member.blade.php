<x-app-admin-layout>

    <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 sm:truncate sm:text-2xl sm:tracking-tight">
        {{ $title }}
    </h2>

    <form method="post" action="{{ route('role.store_member', ['subdomain' => $role->subdomain]) }}"
        class="mt-6 space-y-6">
        @csrf
        @method('post')

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.details') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="name" :value="__('messages.name') . ' *'" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="email" :value="__('messages.email') . ' *'" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email')" required autocomplete="username" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        </div>

                        <div class="mb-6">
                            <x-input-label for="level" :value="__('messages.access_level') . ' *'" />
                            <select id="level" name="level" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="admin" @selected(old('level', 'admin') === 'admin')>{{ __('messages.admin') }}</option>
                                <option value="owner" @selected(old('level') === 'owner')>{{ __('messages.owner') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('level')" />
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                <x-cancel-button></x-cancel-button>
            </div>
        </div>

    </form>

</x-app-admin-layout>
