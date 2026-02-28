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
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">@lang('messages.usage_anomalies_detected')</h3>
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
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">@lang('messages.over_limit')</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($cat['period_total']) }}</p>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    @lang('messages.today'): {{ number_format($cat['today_total']) }}
                    @if ($cat['limit'])
                    / {{ number_format($cat['limit']) }}
                    @endif
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                    @lang('messages.avg_per_day', ['avg' => $cat['daily_avg']])
                </div>
            </div>
            @endforeach
        </div>

        {{-- Operation Breakdown Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.operation_breakdown')</h3>
            @if (count($operationBreakdown) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.operation')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.today')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.period_total')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.daily_avg')</th>
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
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_usage_data')</p>
            @endif
        </div>

        {{-- Top Roles by Usage --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_schedules_by_usage')</h3>
            @if ($topRolesData->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.total')</th>
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
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_schedule_usage_data')</p>
            @endif
        </div>

        {{-- Stuck Translation Records --}}
        @php
            $hasStuckRecords = $stuckRoles->count() > 0 || $stuckEvents->count() > 0 || $stuckEventParts->count() > 0 || $stuckEventRoles->count() > 0;
        @endphp
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.stuck_translation_records')</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">@lang('messages.stuck_translation_description', ['threshold' => $stuckThreshold])</p>

            @if ($hasStuckRecords)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name / ID</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source Lang</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Missing Fields</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Content Preview</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attempts</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Attempt</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($stuckRoles as $record)
                        @php
                            $missingFields = [];
                            if (!empty($record->name) && is_null($record->name_en)) $missingFields[] = 'name_en';
                            if (!empty($record->description) && is_null($record->description_en)) $missingFields[] = 'description_en';
                            if (!empty($record->address1) && is_null($record->address1_en)) $missingFields[] = 'address1_en';
                            if (!empty($record->city) && is_null($record->city_en)) $missingFields[] = 'city_en';
                            if (!empty($record->state) && is_null($record->state_en)) $missingFields[] = 'state_en';
                            if (!empty($record->request_terms) && is_null($record->request_terms_en)) $missingFields[] = 'request_terms_en';
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Schedule</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('role.view_guest', ['subdomain' => $record->subdomain]) }}" class="text-[#4E81FA] hover:underline" target="_blank">{{ $record->name ?: $record->subdomain }}</a>
                                <span class="text-gray-400">#{{ $record->id }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ strtoupper($record->language_code ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ implode(', ', $missingFields) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" title="{{ $record->name ?: $record->description }}">{{ \Illuminate\Support\Str::limit($record->name ?: $record->description, 50) }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <button class="js-retry-translation text-xs text-[#4E81FA] hover:underline" data-type="role" data-id="{{ $record->id }}">@lang('messages.retry')</button>
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEvents as $record)
                        @php
                            $missingFields = [];
                            if (!empty($record->name) && is_null($record->name_en)) $missingFields[] = 'name_en';
                            if (!empty($record->description) && is_null($record->description_en)) $missingFields[] = 'description_en';
                            $langCode = $record->venue?->language_code;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">Event</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($record->name, 40) }} <span class="text-gray-400">#{{ $record->id }}</span></td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ strtoupper($langCode ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ implode(', ', $missingFields) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" title="{{ $record->name ?: $record->description }}">{{ \Illuminate\Support\Str::limit($record->name ?: $record->description, 50) }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <button class="js-retry-translation text-xs text-[#4E81FA] hover:underline" data-type="event" data-id="{{ $record->id }}">@lang('messages.retry')</button>
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEventParts as $record)
                        @php
                            $missingFields = [];
                            if (!empty($record->name) && is_null($record->name_en)) $missingFields[] = 'name_en';
                            if (!empty($record->description) && is_null($record->description_en)) $missingFields[] = 'description_en';
                            $langCode = $record->event?->venue?->language_code;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">EventPart</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($record->name, 40) }} <span class="text-gray-400">#{{ $record->id }} (event #{{ $record->event_id }})</span></td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ strtoupper($langCode ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ implode(', ', $missingFields) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" title="{{ $record->name ?: $record->description }}">{{ \Illuminate\Support\Str::limit($record->name ?: $record->description, 50) }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <button class="js-retry-translation text-xs text-[#4E81FA] hover:underline" data-type="event_part" data-id="{{ $record->id }}">@lang('messages.retry')</button>
                            </td>
                        </tr>
                        @endforeach
                        @foreach ($stuckEventRoles as $record)
                        @php
                            $missingFields = [];
                            if ($record->event && !empty($record->event->name) && is_null($record->name_translated)) $missingFields[] = 'name_translated';
                            if ($record->event && !empty($record->event->description) && is_null($record->description_translated)) $missingFields[] = 'description_translated';
                            $langCode = $record->role?->language_code;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">EventRole</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ $record->event?->name ? \Illuminate\Support\Str::limit($record->event->name, 25) : 'Event #' . $record->event_id }}
                                @if($record->role?->subdomain)
                                    <a href="{{ route('role.view_guest', ['subdomain' => $record->role->subdomain]) }}" class="text-[#4E81FA] hover:underline" target="_blank">@ {{ $record->role->subdomain }}</a>
                                @else
                                    <span class="text-gray-400">@ Role #{{ $record->role_id }}</span>
                                @endif
                                <span class="text-gray-400">#{{ $record->id }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ strtoupper($langCode ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ implode(', ', $missingFields) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($record->event?->name, 50) }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $record->translation_attempts >= $stuckThreshold * 2 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                    {{ $record->translation_attempts }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-end text-gray-500 dark:text-gray-400">{{ $record->last_translated_at ? $record->last_translated_at->diffForHumans() : 'Never' }}</td>
                            <td class="px-4 py-3 text-sm text-end">
                                <button class="js-retry-translation text-xs text-[#4E81FA] hover:underline" data-type="event_role" data-id="{{ $record->id }}">@lang('messages.retry')</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <script {!! nonce_attr() !!}>
                document.addEventListener('click', function(e) {
                    var button = e.target.closest('.js-retry-translation');
                    if (!button) return;
                    retryTranslation(button.getAttribute('data-type'), parseInt(button.getAttribute('data-id')), button);
                });

                function retryTranslation(type, id, button) {
                    const originalText = button.textContent;
                    button.textContent = '...';
                    button.disabled = true;

                    fetch('{{ route("admin.translation.retry") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ type: type, id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.textContent = @json(__("messages.done"));
                            button.classList.remove('text-[#4E81FA]');
                            button.classList.add('text-green-600', 'dark:text-green-400');
                            // Optionally remove the row after a short delay
                            setTimeout(() => {
                                button.closest('tr').remove();
                            }, 1000);
                        } else {
                            button.textContent = 'Error';
                            button.classList.remove('text-[#4E81FA]');
                            button.classList.add('text-red-600', 'dark:text-red-400');
                            setTimeout(() => {
                                button.textContent = originalText;
                                button.classList.remove('text-red-600', 'dark:text-red-400');
                                button.classList.add('text-[#4E81FA]');
                                button.disabled = false;
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        button.textContent = 'Error';
                        button.classList.remove('text-[#4E81FA]');
                        button.classList.add('text-red-600', 'dark:text-red-400');
                        setTimeout(() => {
                            button.textContent = originalText;
                            button.classList.remove('text-red-600', 'dark:text-red-400');
                            button.classList.add('text-[#4E81FA]');
                            button.disabled = false;
                        }, 2000);
                    });
                }
            </script>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_stuck_translations')</p>
            @endif
        </div>
    </div>

</x-app-admin-layout>
