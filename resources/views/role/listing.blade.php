<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $currentView = $selectedView ?? 'grid';
                $canCreate = $canCreate ?? false;
                $authUser = $authUser ?? null;
            @endphp
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between lg:items-center">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $pageTitle }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $pageDescription }}</p>
                </div>
                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <div class="flex items-center justify-end gap-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('messages.layout') }}</span>
                        <div class="inline-flex rounded-full border border-gray-200 bg-gray-100 p-0.5 text-sm font-medium dark:border-gray-700 dark:bg-gray-800">
                            @foreach (['grid', 'list'] as $layout)
                                @php
                                    $isActive = $currentView === $layout;
                                    $url = request()->fullUrlWithQuery(['view' => $layout === 'grid' ? null : $layout]);
                                @endphp
                                <a
                                    href="{{ $url }}"
                                    @class([
                                        'inline-flex items-center gap-1 rounded-full px-3 py-1.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]',
                                        $isActive
                                            ? 'bg-white text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700'
                                            : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white',
                                    ])
                                    @if ($isActive) aria-current="page" @endif
                                >
                                    @if ($layout === 'grid')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <rect x="4" y="4" width="6" height="6" rx="1" />
                                            <rect x="14" y="4" width="6" height="6" rx="1" />
                                            <rect x="4" y="14" width="6" height="6" rx="1" />
                                            <rect x="14" y="14" width="6" height="6" rx="1" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M4 6h.01M7 12h13M4 12h.01M7 18h13M4 18h.01" />
                                        </svg>
                                    @endif
                                    {{ __('messages.' . $layout) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if ($canCreate)
                        <a
                            href="{{ route('new', ['type' => $roleType]) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ $createLabel }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($roles->isNotEmpty())
                @if ($currentView === 'list')
                    <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/20">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ __('messages.name') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ __('messages.phone') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ __('messages.website') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ __('messages.main_contact') }}
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white text-sm dark:divide-gray-700 dark:bg-gray-800">
                                    @foreach ($roles as $role)
                                        @php
                                            $contacts = collect($role->contacts);
                                            $owner = $role->members->firstWhere('pivot.level', 'owner');
                                            $primaryContacts = $contacts->isNotEmpty() ? $contacts : collect([$owner ? ['name' => $owner->name, 'email' => $owner->email] : null])->filter();
                                            $websiteUrl = $role->website;
                                            if ($websiteUrl && ! \Illuminate\Support\Str::startsWith($websiteUrl, ['http://', 'https://'])) {
                                                $websiteUrl = 'https://' . $websiteUrl;
                                            }
                                            @endphp
                                            <tr class="align-top">
                                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    <div class="flex items-start gap-3">
                                                        @if ($role->profile_image_url)
                                                        <img src="{{ $role->profile_image_url }}" alt="" class="h-10 w-10 flex-none rounded-full object-cover" />
                                                    @else
                                                        <span class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-gray-100 text-base font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                                            {{ mb_substr($role->name ?? '', 0, 1) }}
                                                        </span>
                                                    @endif
                                                    <div>
                                                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $role->getDisplayName(false) }}</div>
                                                        @if ($subtitle = $role->shortAddress())
                                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $subtitle }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                @if ($role->phone)
                                                    <a href="tel:{{ $role->phone }}" class="text-[#4E81FA] hover:underline">{{ $role->phone }}</a>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                @if ($role->website)
                                                    <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-[#4E81FA] hover:underline">
                                                        {{ \App\Utils\UrlUtils::clean($role->website) }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                @if ($primaryContacts->isNotEmpty())
                                                    <div class="space-y-2">
                                                        @foreach ($primaryContacts as $contact)
                                                            <div class="space-y-1">
                                                                @if (!empty($contact['name']))
                                                                    <p>{{ $contact['name'] }}</p>
                                                                @endif
                                                                @if (!empty($contact['email']))
                                                                    <a href="mailto:{{ $contact['email'] }}" class="text-[#4E81FA] hover:underline break-words">{{ $contact['email'] }}</a>
                                                                @endif
                                                                @if (!empty($contact['phone']))
                                                                    <a href="tel:{{ $contact['phone'] }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-[#4E81FA]">{{ $contact['phone'] }}</a>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                @php $canManage = $authUser && $authUser->canManageResource($role); @endphp
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if ($canManage)
                                                        <a
                                                            href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-[#4E81FA] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-[#4E81FA]"
                                                        >
                                                            {{ __('messages.manage') }}
                                                        </a>
                                                        <a
                                                            href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}"
                                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-[#4E81FA] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-[#4E81FA]"
                                                        >
                                                            {{ __('messages.edit_' . strtolower($role->type)) }}
                                                        </a>
                                                    @endif
                                                    @if ($role->isClaimed() && $role->getGuestUrl())
                                                        <a
                                                            href="{{ $role->getGuestUrl() }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center gap-2 rounded-full border border-transparent bg-white px-3 py-1.5 text-sm font-medium text-[#4E81FA] shadow-sm transition hover:bg-[#4E81FA]/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-800 dark:text-[#9DB9FF] dark:hover:bg-[#4E81FA]/20"
                                                        >
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18a.75.75 0 01.75.75V11.25" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 13.5L18 6" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6H6a.75.75 0 00-.75.75V18a.75.75 0 00.75.75H17.25A.75.75 0 0018 18v-3" />
                                                            </svg>
                                                            {{ __('messages.view_public_page') }}
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
                @else
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($roles as $role)
                            <div class="flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $role->getDisplayName(false) }}
                                    </h2>
                                    @if ($subtitle = $role->shortAddress())
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @php $canManage = $authUser && $authUser->canManageResource($role); @endphp
                                    @if ($canManage)
                                        <a
                                            href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']) }}"
                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-[#4E81FA] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-[#4E81FA]"
                                        >
                                            {{ __('messages.manage') }}
                                        </a>
                                        <a
                                            href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}"
                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-[#4E81FA] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-[#4E81FA]"
                                        >
                                            {{ __('messages.edit_' . strtolower($role->type)) }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <dl class="mt-6 flex-1 space-y-5 text-sm">
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('messages.address') }}
                                    </dt>
                                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                        @php $address = trim($role->fullAddress(), ' ,'); @endphp
                                        @if ($address)
                                            <p>{{ $address }}</p>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('messages.phone') }}
                                    </dt>
                                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                        @if ($role->phone)
                                            <a href="tel:{{ $role->phone }}" class="text-[#4E81FA] hover:underline">{{ $role->phone }}</a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('messages.website') }}
                                    </dt>
                                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                        @if ($role->website)
                                            @php
                                                $websiteUrl = $role->website;
                                                if (! \Illuminate\Support\Str::startsWith($websiteUrl, ['http://', 'https://'])) {
                                                    $websiteUrl = 'https://' . $websiteUrl;
                                                }
                                            @endphp
                                            <a
                                                href="{{ $websiteUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-[#4E81FA] hover:underline"
                                            >
                                                {{ \App\Utils\UrlUtils::clean($role->website) }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        {{ __('messages.main_contact') }}
                                    </dt>
                                    <dd class="mt-1 text-gray-900 dark:text-gray-100">
                                        @php
                                            $contacts = collect($role->contacts);
                                            $owner = $role->members->firstWhere('pivot.level', 'owner');
                                        @endphp
                                        @if ($contacts->isNotEmpty())
                                            <div class="space-y-3">
                                                @foreach ($contacts as $contact)
                                                    <div class="space-y-1">
                                                        @if (!empty($contact['name']))
                                                            <p>{{ $contact['name'] }}</p>
                                                        @endif
                                                        @if (!empty($contact['email']))
                                                            <a href="mailto:{{ $contact['email'] }}" class="text-[#4E81FA] hover:underline break-words">{{ $contact['email'] }}</a>
                                                        @endif
                                                        @if (!empty($contact['phone']))
                                                            <a href="tel:{{ $contact['phone'] }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-[#4E81FA]">{{ $contact['phone'] }}</a>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($owner && ($owner->name || $owner->email))
                                            <div class="space-y-1">
                                                @if ($owner->name)
                                                    <p>{{ $owner->name }}</p>
                                                @endif
                                                @if ($owner->email)
                                                    <a href="mailto:{{ $owner->email }}" class="text-[#4E81FA] hover:underline">{{ $owner->email }}</a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <div class="mt-6 flex flex-wrap gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                                @if ($role->isClaimed() && $role->getGuestUrl())
                                    <a
                                        href="{{ $role->getGuestUrl() }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-full border border-transparent bg-white px-3 py-1.5 text-sm font-medium text-[#4E81FA] shadow-sm transition hover:bg-[#4E81FA]/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-800 dark:text-[#9DB9FF] dark:hover:bg-[#4E81FA]/20"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18a.75.75 0 01.75.75V11.25" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 13.5L18 6" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6H6a.75.75 0 00-.75.75V18a.75.75 0 00.75.75H17.25A.75.75 0 0018 18v-3" />
                                        </svg>
                                        {{ __('messages.view_public_page') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="mt-12 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#4E81FA]/10">
                        <svg class="h-6 w-6 text-[#4E81FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3.75h15a.75.75 0 01.75.75v15a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75v-15a.75.75 0 01.75-.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h9M7.5 12h9M7.5 16.5h5.25" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $emptyTitle }}</h3>
                    <p class="mt-2 max-w-md text-sm text-gray-600 dark:text-gray-400">{{ $emptyDescription }}</p>
                    @if ($canCreate)
                        <a
                            href="{{ route('new', ['type' => $roleType]) }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ $createLabel }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
