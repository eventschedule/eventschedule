<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $pageTitle }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $pageDescription }}</p>
                </div>
                <a
                    href="{{ route('new', ['type' => $roleType]) }}"
                    class="inline-flex items-center gap-2 rounded-full bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ $createLabel }}
                </a>
            </div>

            @if ($roles->isNotEmpty())
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
                                <a
                                    href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}"
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-[#4E81FA] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-[#4E81FA]"
                                >
                                    {{ __('messages.manage') }}
                                </a>
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
                                        @php $owner = $role->members->firstWhere('pivot.level', 'owner'); @endphp
                                        @if ($owner && ($owner->name || $owner->email))
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
                    <a
                        href="{{ route('new', ['type' => $roleType]) }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#3b6ae0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ $createLabel }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
