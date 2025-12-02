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
                            <x-input-label for="user_id" :value="__('messages.select_user') . ' *'" />
                            <select id="user_id" name="user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="" disabled {{ old('user_id') ? '' : 'selected' }}>{{ __('messages.select_user') }}</option>
                                @foreach ($availableUsers as $availableUser)
                                    <option value="{{ $availableUser->id }}" @selected(old('user_id') == $availableUser->id)>
                                        {{ $availableUser->name }} ({{ $availableUser->email }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
                        </div>

                        @if ($availableUsers->isEmpty())
                            <div class="mb-6 rounded-md bg-blue-50 p-4 text-sm text-blue-900 ring-1 ring-blue-200 dark:bg-blue-900/20 dark:text-blue-100 dark:ring-blue-800/40">
                                {{ __('messages.no_team_members_available') }}
                                <a href="{{ route('settings.users.create') }}" class="font-semibold underline">{{ __('messages.create_user') }}</a>
                                {{ __('messages.to_add_new_team_member') }}
                            </div>
                        @else
                            <p class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                                {{ __('messages.cant_find_user_prompt') }}
                                <a href="{{ route('settings.users.create') }}" class="font-semibold text-[#4E81FA] hover:text-[#3A6BE0]">{{ __('messages.create_user') }}</a>.
                            </p>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="level" :value="__('messages.access_level') . ' *'" />
                            <select id="level" name="level" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="admin" @selected(old('level', 'admin') === 'admin')>{{ __('messages.admin') }}</option>
                                <option value="viewer" @selected(old('level') === 'viewer')>{{ __('messages.viewer') }}</option>
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
