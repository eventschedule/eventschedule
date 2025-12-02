<x-app-admin-layout>
    @php
        $canManageResources = $canManageResources ?? false;
        $typeStyles = [
            'talent' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-500/20 dark:text-purple-200 dark:ring-purple-400/40',
            'venue' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/20 dark:text-emerald-200 dark:ring-emerald-400/40',
            'curator' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/20 dark:text-amber-200 dark:ring-amber-400/40',
        ];
    @endphp

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('messages.pages') }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.manage_pages_description') }}</p>
                </div>
                @if ($canManageResources)
                    <div class="flex flex-wrap items-center gap-3">
                        <a
                            href="{{ route('new', ['type' => 'talent']) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                            {{ __('messages.new_talent') }}
                        </a>
                        <a
                            href="{{ route('new', ['type' => 'venue']) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                            {{ __('messages.new_venue') }}
                        </a>
                        <a
                            href="{{ route('new', ['type' => 'curator']) }}"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                            </svg>
                            {{ __('messages.new_curator') }}
                        </a>
                    </div>
                @endif
            </div>

            @if ($roles->isNotEmpty())
                <div class="mt-8 -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg dark:ring-white/10">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/60">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">
                                            {{ __('messages.name') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ __('messages.type') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ __('messages.events') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ __('messages.followers') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ __('messages.status') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ __('messages.team') }}
                                        </th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">{{ __('messages.actions') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    @foreach ($roles as $role)
                                        @php
                                            $type = strtolower($role->type);
                                            $badgeClass = $typeStyles[$type] ?? 'bg-gray-100 text-gray-700 ring-gray-500/20 dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-500/30';
                                            $statusLabel = $role->is_unlisted ? __('messages.unlisted') : __('messages.public');
                                            $publicUrl = $role->getGuestUrl();
                                            $displayUrl = $publicUrl ? str_replace(['https://', 'http://'], '', $publicUrl) : $role->subdomain;
                                        @endphp
                                        <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/60">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6">
                                                <div class="flex flex-col">
                                                    <span>{{ $role->getDisplayName(false) }}</span>
                                                    <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $displayUrl }}</span>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClass }}">
                                                    {{ __('messages.' . $type) }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                {{ number_format($role->events_count) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                {{ number_format($role->followers_count) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                {{ $statusLabel }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                {{ number_format($role->team_members_count ?? 0) }}
                                            </td>
                                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if ($canManageResources)
                                                        <a
                                                            href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                                                            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                        >
                                                            {{ __('messages.manage') }}
                                                        </a>
                                                        <a
                                                            href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}"
                                                            class="inline-flex items-center rounded-md bg-[#4E81FA] px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                                                        >
                                                            {{ __('messages.edit') }}
                                                        </a>
                                                    @endif
                                                    @if ($publicUrl)
                                                        <a
                                                            href="{{ $publicUrl }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center rounded-md bg-[#4E81FA] px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                                                        >
                                                            {{ __('messages.view') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-12 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#4E81FA]/10">
                        <svg class="h-6 w-6 text-[#4E81FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3H15a1.5 1.5 0 011.5 1.5V7.5H19.5A1.5 1.5 0 0121 9V19.5A1.5 1.5 0 0119.5 21H9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 3A1.5 1.5 0 003 4.5v15A1.5 1.5 0 004.5 21h10.5A1.5 1.5 0 0016.5 19.5V18" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h6m-6 3h6m-6 3H12" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_pages_listed') }}</h3>
                    <p class="mt-2 max-w-md text-sm text-gray-600 dark:text-gray-400">{{ __('messages.create_first_page_prompt') }}</p>
                    @if ($canManageResources)
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a
                                href="{{ route('new', ['type' => 'talent']) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                                </svg>
                                {{ __('messages.new_talent') }}
                            </a>
                            <a
                                href="{{ route('new', ['type' => 'venue']) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                                </svg>
                                {{ __('messages.new_venue') }}
                            </a>
                            <a
                                href="{{ route('new', ['type' => 'curator']) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                                </svg>
                                {{ __('messages.new_curator') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
