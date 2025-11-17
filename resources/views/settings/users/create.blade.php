<x-app-admin-layout>
    <div class="py-10">
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <p class="text-sm font-medium text-indigo-600">{{ __('messages.user_management') }}</p>
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('messages.create_user') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.create_user_description') }}</p>
                </div>

                @php($blankUser = new \App\Models\User([
                    'timezone' => config('app.timezone'),
                    'language_code' => app()->getLocale(),
                ]))

                <form method="POST" action="{{ route('settings.users.store') }}" class="space-y-6">
                    @csrf
                    @include('settings.users.partials.form', [
                        'managedUser' => $blankUser,
                        'timezones' => $timezones,
                        'languageOptions' => $languageOptions,
                        'availableRoles' => $availableRoles,
                        'canManageRoles' => $canManageRoles,
                        'passwordLabel' => __('messages.password'),
                        'passwordRequired' => true,
                        'submitLabel' => __('messages.save'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-admin-layout>
