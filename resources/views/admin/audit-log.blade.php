<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'audit-log'])

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_entries')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalEntries) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.entries_today')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($entriesToday) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 {{ $failedAuthToday > 0 ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-700' }} rounded-full">
                            <svg class="w-6 h-6 {{ $failedAuthToday > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.failed_auth_today')</p>
                        <p class="text-2xl font-bold {{ $failedAuthToday > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($failedAuthToday) }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.unique_ips_today')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($uniqueIpsToday) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.audit_log') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.category')</label>
                    <select name="category" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                        <option value="">@lang('messages.all')</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.from')</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.to')</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@lang('messages.search')</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_audit_log') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-[#4E81FA] text-white rounded-md text-sm font-medium hover:bg-blue-600">
                        @lang('messages.filter')
                    </button>
                    <a href="{{ route('admin.audit_log') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500">
                        @lang('messages.clear')
                    </a>
                </div>
            </div>
        </form>

        {{-- Results --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">@lang('messages.time')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">@lang('messages.user')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">@lang('messages.action')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">@lang('messages.ip_address')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">@lang('messages.details')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $log->created_at->format('M j, Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                @if ($log->user)
                                    {{ $log->user->name }}
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                @php
                                    $actionColor = match(explode('.', $log->action)[0] ?? '') {
                                        'auth' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                        'profile' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                        'api' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                        'schedule' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'event' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
                                        'sale' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                        'admin' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        'stripe' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $actionColor }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap font-mono">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                @if ($log->metadata)
                                    {{ Str::limit($log->metadata, 80) }}
                                @elseif ($log->model_type)
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                @lang('messages.no_audit_log_entries')
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>

</x-app-admin-layout>
