<x-app-admin-layout>
    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                <div class="flex flex-col gap-4 border-b border-gray-100 pb-4 md:flex-row md:items-center md:justify-between dark:border-gray-800">
                    <div>
                        <p class="text-sm font-medium text-indigo-600">{{ __('messages.settings') }}</p>
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ __('messages.user_management') }}</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.user_management_description') }}</p>
                    </div>
                    @if ($canManageRoles)
                        <a href="{{ route('settings.users.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            {{ __('messages.add_user') }}
                        </a>
                    @endif
                </div>

                @if (session('status'))
                    <div class="mt-4 rounded-lg border border-green-100 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('settings.users.index') }}" class="mt-6">
                    <label for="search" class="sr-only">{{ __('messages.search_users') }}</label>
                    <div class="relative">
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="{{ __('messages.search_users_placeholder') }}"
                            class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5a6 6 0 1 0 4.472 10.027l3.25 3.251a.75.75 0 1 0 1.06-1.06l-3.25-3.251A6 6 0 0 0 11 5z" />
                            </svg>
                        </span>
                    </div>
                </form>

                <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 shadow-sm dark:border-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('messages.name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('messages.roles') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('messages.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse ($user->systemRoles as $role)
                                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-sm text-gray-500">{{ __('messages.no_roles_assigned') }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        @if ($canManageRoles)
                                            <a href="{{ route('settings.users.edit', $user) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                                {{ __('messages.edit') }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                        {{ __('messages.no_users_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
