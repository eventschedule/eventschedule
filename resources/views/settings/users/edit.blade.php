<x-app-admin-layout>
    <div class="py-10">
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <x-breadcrumbs
                        :items="[
                            ['label' => __('messages.settings'), 'url' => route('settings.index')],
                            ['label' => __('messages.user_management'), 'url' => route('settings.users.index')],
                            ['label' => __('messages.edit_user'), 'current' => true],
                        ]"
                        class="text-xs text-gray-500 dark:text-gray-400"
                    />
                    <p class="text-sm font-medium text-indigo-600">{{ __('messages.user_management') }}</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('messages.edit_user') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('messages.edit_user_description', ['name' => $managedUser->name]) }}
                    </p>
                </div>

                <form method="POST" action="{{ route('settings.users.update', $managedUser) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    @include('settings.users.partials.form', [
                        'managedUser' => $managedUser,
                        'timezones' => $timezones,
                        'languageOptions' => $languageOptions,
                        'availableRoles' => $availableRoles,
                        'canManageRoles' => $canManageRoles,
                        'passwordLabel' => __('messages.password'),
                        'passwordRequired' => false,
                        'submitLabel' => __('messages.save'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-admin-layout>
