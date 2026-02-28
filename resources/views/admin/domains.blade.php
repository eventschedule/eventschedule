<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'domains'])

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Custom Domains --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCustomDomains) }}</p>
                    </div>
                </div>
            </div>

            {{-- Direct Mode --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.custom_domain_mode_direct')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($directCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Active --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.domain_active')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeCount) }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.domain_pending')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($pendingCount) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search and Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <form method="GET" action="{{ route('admin.domains') }}" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px] relative">
                    <x-input-label for="search" :value="__('messages.search')" />
                    <x-text-input id="search" name="search" type="text" class="mt-1 block w-full"
                        :value="request('search')" :placeholder="__('messages.search_domains')" autocomplete="off" data-subdomain-autocomplete />
                    <div data-subdomain-dropdown class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <div>
                    <x-input-label for="mode" :value="__('messages.custom_domain_mode')" />
                    <select id="mode" name="mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                        <option value="">@lang('messages.all')</option>
                        <option value="redirect" {{ request('mode') === 'redirect' ? 'selected' : '' }}>@lang('messages.custom_domain_mode_redirect')</option>
                        <option value="direct" {{ request('mode') === 'direct' ? 'selected' : '' }}>@lang('messages.custom_domain_mode_direct')</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="status" :value="__('messages.status')" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                        <option value="">@lang('messages.all')</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>@lang('messages.domain_pending')</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>@lang('messages.domain_active')</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>@lang('messages.domain_failed')</option>
                    </select>
                </div>
                <x-primary-button type="submit">
                    @lang('messages.search')
                </x-primary-button>
            </form>
        </div>

        {{-- Domains Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.schedule')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.custom_domain')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.custom_domain_mode')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.status')</th>
                            <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.do_status')</th>
                            <th class="px-6 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">@lang('messages.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($roles as $role)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $role->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->subdomain }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $role->custom_domain_host ?? parse_url($role->custom_domain, PHP_URL_HOST) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $role->custom_domain }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($role->custom_domain_mode === 'direct')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                        @lang('messages.custom_domain_mode_direct')
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        @lang('messages.custom_domain_mode_redirect')
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($role->custom_domain_mode === 'direct')
                                    @if ($role->custom_domain_status === 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            @lang('messages.domain_active')
                                        </span>
                                    @elseif ($role->custom_domain_status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            @lang('messages.domain_pending')
                                        </span>
                                    @elseif ($role->custom_domain_status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            @lang('messages.domain_failed')
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($role->custom_domain_mode === 'direct' && $role->custom_domain_host)
                                    @php $doPhase = $doStatuses[$role->custom_domain_host]['phase'] ?? null; @endphp
                                    @if ($doPhase)
                                        <span class="text-xs font-mono text-gray-600 dark:text-gray-400">{{ $doPhase }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($role->custom_domain_mode === 'direct')
                                    <form method="POST" action="{{ route('admin.domains.reprovision', $role) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-[#4E81FA] hover:underline"
                                            onclick="return confirm('Re-provision this domain?')">
                                            @lang('messages.reprovision')
                                        </button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.domains.remove', $role) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-600 hover:underline"
                                            onclick="return confirm('Remove this domain configuration?')">
                                            @lang('messages.remove')
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                @lang('messages.no_results_found')
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($roles->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>

    @include('admin.partials._subdomain-autocomplete')
</x-app-admin-layout>
