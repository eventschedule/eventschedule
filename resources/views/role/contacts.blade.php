<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('messages.contacts') }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.contacts_help') }}</p>
                </div>
            </div>

            @php
                $typeOrder = $typeOrder ?? [];
            @endphp

            @if ($hasContacts)
                @foreach ($typeOrder as $type)
                    @php
                        $contacts = collect(($contactsByType ?? collect())->get($type, []));
                        $label = $typeLabels[$type] ?? ucfirst($type);
                    @endphp

                    @if ($contacts->isNotEmpty())
                        <div class="mt-12">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</h2>

                            <div class="mt-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg dark:ring-white/10">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                                        <tr>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.name') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.email') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.phone') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.role') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        @foreach ($contacts as $entry)
                                            @php
                                                $role = $entry['role'];
                                                $contact = $entry['contact'];
                                                $roleUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
                                            @endphp
                                            <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/60">
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['name']))
                                                        {{ $contact['name'] }}
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['email']))
                                                        <a href="mailto:{{ $contact['email'] }}" class="text-[#4E81FA] hover:underline break-words">{{ $contact['email'] }}</a>
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['phone']))
                                                        <a href="tel:{{ $contact['phone'] }}" class="text-[#4E81FA] hover:underline">{{ $contact['phone'] }}</a>
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    <div class="flex flex-col">
                                                        <a href="{{ $roleUrl }}" class="font-semibold text-[#4E81FA] hover:underline">
                                                            {{ $role->getDisplayName(false) }}
                                                        </a>
                                                        <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.' . $role->type) }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="mt-12 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#4E81FA]/10">
                        <svg class="h-6 w-6 text-[#4E81FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V5.25a.75.75 0 01.75-.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h9M7.5 12h5.25" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_contacts_added') }}</h3>
                    <p class="mt-2 max-w-md text-sm text-gray-600 dark:text-gray-400">{{ __('messages.contacts_help') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
