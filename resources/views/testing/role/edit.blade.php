<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h1>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Update the minimal role details so automated browser tests can exercise the edit flow.') }}
            </p>

            <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form method="POST" action="{{ route('role.update', ['subdomain' => $role->subdomain]) }}">
                    @csrf
                    @method('PUT')

                    @include('testing.role.partials.form-fields', [
                        'mode' => 'edit',
                        'role' => $role,
                        'user' => $user,
                        'userData' => $userData ?? [],
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-admin-layout>
