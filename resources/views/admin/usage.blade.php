<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'usage'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Anomaly Alert Banner --}}
        @if (count($anomalies) > 0)
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ms-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Usage Anomalies Detected Today</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc ps-5 space-y-1">
                            @foreach ($anomalies as $anomaly)
                            <li>{{ $anomaly['category'] }}: {{ number_format($anomaly['today']) }} today (limit: {{ number_format($anomaly['limit']) }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Summary Metric Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($categorySummaries as $key => $cat)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $cat['label'] }}</h4>
                    @if ($cat['limit'] && $cat['today_total'] > $cat['limit'])
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Over limit</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($cat['period_total']) }}</p>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Today: {{ number_format($cat['today_total']) }}
                    @if ($cat['limit'])
                    / {{ number_format($cat['limit']) }}
                    @endif
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    Avg: {{ $cat['daily_avg'] }}/day
                </div>
            </div>
            @endforeach
        </div>

        {{-- Operation Breakdown Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Operation Breakdown</h3>
            @if (count($operationBreakdown) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Operation</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Period Total</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Daily Avg</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($operationBreakdown as $row)
                        <tr>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">{{ $row['operation'] }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-white">{{ number_format($row['today']) }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-white">{{ number_format($row['period_total']) }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $row['daily_avg'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No usage data for this period.</p>
            @endif
        </div>

        {{-- Top Roles by Usage --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Schedules by Usage</h3>
            @if ($topRolesData->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            @foreach ($categories as $key => $cat)
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $cat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($topRolesData as $roleData)
                        <tr>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('role.view_guest', ['subdomain' => $roleData['subdomain']]) }}" class="text-[#4E81FA] hover:underline" target="_blank">{{ $roleData['subdomain'] }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-gray-900 dark:text-white">{{ number_format($roleData['total']) }}</td>
                            @foreach ($categories as $key => $cat)
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ number_format($roleData['categories'][$key] ?? 0) }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No per-schedule usage data for this period.</p>
            @endif
        </div>

        {{-- Stuck Translation Records --}}
        @php
            $hasStuckRecords = $stuckRoles->count() > 0 || $stuckEvents->count() > 0 || $stuckEventParts->count() > 0 || $stuckEventRoles->count() > 0;
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Stuck Translation Records</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Records with {{ $stuckThreshold }}+ translation attempts that still have untranslated fields.</p>

            @if ($hasStuckRecords)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name / ID</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attempts</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Attempt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($stuckRoles as $record)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Role</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $record->name ?: $record->subdomain }} <span class="text-gray-400">#{{ $record->id }}</span></td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEvents as $record)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Event</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($record->name, 40) }} <span class="text-gray-400">#{{ $record->id }}</span></td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEventParts as $record)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">EventPart</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($record->name, 40) }} <span class="text-gray-400">#{{ $record->id }} (event #{{ $record->event_id }})</span></td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEventRoles as $record)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">EventRole</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">Event #{{ $record->event_id }}, Role #{{ $record->role_id }} <span class="text-gray-400">#{{ $record->id }}</span></td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No stuck translation records found.</p>
            @endif
        </div>
    </div>

</x-app-admin-layout>
