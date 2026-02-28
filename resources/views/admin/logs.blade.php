<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'logs'])

        @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
        </div>
        @endif

        @if (!$fileExists)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">@lang('messages.no_log_file_found')</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_log_file_found_at') <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">storage/logs/laravel.log</code></p>
            </div>
        @else
            @php
                $errorCount = ($levelCounts['ERROR'] ?? 0) + ($levelCounts['CRITICAL'] ?? 0) + ($levelCounts['EMERGENCY'] ?? 0) + ($levelCounts['ALERT'] ?? 0);
            @endphp

            {{-- Alert Banner --}}
            @if ($errorCount > 0 || $fileSize > 100 * 1024 * 1024)
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="ms-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">@lang('messages.log_health_issues')</h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc ps-5 space-y-1">
                                @if ($errorCount > 0)
                                <li>@lang('messages.error_level_entries', ['count' => number_format($errorCount)])</li>
                                @endif
                                @if ($fileSize > 100 * 1024 * 1024)
                                <li>@lang('messages.log_file_over_size', ['size' => number_format($fileSize / 1024 / 1024, 0)])</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.file_size')</h4>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($fileSize / 1024 / 1024, 2) }} MB
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_entries')</h4>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCount) }}</p>
                    @if ($fileSize > 5 * 1024 * 1024)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.from_last_5_mb')</p>
                    @endif
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.errors')</h4>
                    </div>
                    <p class="text-2xl font-bold {{ $errorCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($errorCount) }}</p>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.warnings')</h4>
                    </div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($levelCounts['WARNING'] ?? 0) }}</p>
                </div>
            </div>

            {{-- Repeated Errors --}}
            @if ($repeatedErrors->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.repeated_errors')</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Count</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Level</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Error</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Last Seen</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">First Seen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($repeatedErrors as $error)
                            <tr>
                                <td class="px-4 py-3 text-end align-top">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">{{ $error['count'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm align-top">
                                    @php
                                        $badgeClass = match($error['level']) {
                                            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                            'WARNING' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                            'NOTICE' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                            'INFO' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">{{ $error['level'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    @php
                                        $copyText = '[' . $error['level'] . '] ' . $error['message'] . "\n\n" . 'Occurred ' . $error['count'] . ' times, last seen ' . $error['last_seen'] . ', first seen ' . $error['first_seen'];
                                        if ($error['stack_trace']) {
                                            $copyText .= "\n\nStack trace:\n" . $error['stack_trace'];
                                        }
                                    @endphp
                                    <div class="flex items-start gap-2">
                                        <details class="flex-1 min-w-0">
                                            <summary class="cursor-pointer hover:text-blue-600 dark:hover:text-blue-400">
                                                <span class="font-mono text-xs">{{ Str::limit($error['message'], 150) }}</span>
                                            </summary>
                                            @if ($error['stack_trace'])
                                            <pre class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-3 rounded">{{ $error['stack_trace'] }}</pre>
                                            @endif
                                        </details>
                                        <button type="button" class="js-copy-error flex-shrink-0 inline-flex items-center text-gray-400 hover:text-blue-500 dark:hover:text-blue-400" title="Copy error for Claude" data-copy="{{ e($copyText) }}">
                                            <svg class="w-4 h-4 js-copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <svg class="w-4 h-4 js-check-icon hidden text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">{{ $error['last_seen'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">{{ $error['first_seen'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Filters + Recent Log Entries --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.recent_log_entries')</h3>

                {{-- Filter Bar --}}
                <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap gap-3 mb-4">
                    <select name="level" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        <option value="">@lang('messages.all_levels')</option>
                        @foreach ($levels as $level)
                        <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_messages') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm flex-1 min-w-[200px]">

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 hover:bg-gray-700 dark:hover:bg-gray-500 text-white text-sm font-medium rounded-md">
                        @lang('messages.filter')
                    </button>

                    @if (request('level') || request('search'))
                    <a href="{{ route('admin.logs') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                        @lang('messages.clear')
                    </a>
                    @endif
                </form>

                {{-- Log Entries Table --}}
                @if ($entries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Time</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($entries as $entry)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap align-top">{{ $entry['timestamp'] }}</td>
                                <td class="px-4 py-3 text-sm align-top">
                                    @php
                                        $badgeClass = match($entry['level']) {
                                            'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                            'WARNING' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                            'NOTICE' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                            'INFO' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">{{ $entry['level'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white max-w-2xl">
                                    @php
                                        $isErrorLevel = in_array($entry['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']);
                                        if ($isErrorLevel) {
                                            $copyText = '[' . $entry['level'] . '] [' . $entry['timestamp'] . '] ' . $entry['message'];
                                            if ($entry['context']) {
                                                $prettyContext = json_encode(json_decode($entry['context']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: $entry['context'];
                                                $copyText .= "\n\nContext:\n" . $prettyContext;
                                            }
                                            if ($entry['stack_trace']) {
                                                $copyText .= "\n\nStack trace:\n" . $entry['stack_trace'];
                                            }
                                        }
                                    @endphp
                                    @if ($entry['stack_trace'] || $entry['context'])
                                    <div class="flex items-start gap-2">
                                        <details class="flex-1 min-w-0">
                                            <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                                <span class="font-mono text-xs">{{ Str::limit($entry['message'], 150) }}</span>
                                            </summary>
                                            @if ($entry['context'])
                                            <pre class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap bg-gray-50 dark:bg-gray-900 p-3 rounded">{{ json_encode(json_decode($entry['context']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: $entry['context'] }}</pre>
                                            @endif
                                            @if ($entry['stack_trace'])
                                            <pre class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-3 rounded">{{ $entry['stack_trace'] }}</pre>
                                            @endif
                                        </details>
                                        @if ($isErrorLevel)
                                        <button type="button" class="js-copy-error flex-shrink-0 inline-flex items-center text-gray-400 hover:text-blue-500 dark:hover:text-blue-400" title="Copy error for Claude" data-copy="{{ e($copyText) }}">
                                            <svg class="w-4 h-4 js-copy-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <svg class="w-4 h-4 js-check-icon hidden text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                    @else
                                    <span class="font-mono text-xs">{{ Str::limit($entry['message'], 150) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Showing X of Y --}}
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    @if (request('level') || request('search'))
                        @lang('messages.showing_filtered_entries', ['shown' => number_format($entries->count()), 'total' => number_format($totalCount)])
                    @elseif ($entries->count() < $totalCount)
                        @lang('messages.showing_entries', ['shown' => number_format($entries->count()), 'total' => number_format($totalCount)])
                    @else
                        @lang('messages.n_entries', ['count' => number_format($totalCount)])
                    @endif
                </p>
                @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_log_entries')</p>
                @endif
            </div>

            {{-- Actions --}}
            @if ($fileSize > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-wrap gap-3">
                <a href="{{ route('admin.logs.download') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-600 hover:bg-gray-700 dark:hover:bg-gray-500 text-white text-sm font-medium rounded-md">
                    <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    @lang('messages.download_log')
                </a>
                <form method="POST" action="{{ route('admin.logs.clear') }}" class="js-confirm-form" data-confirm="{{ __('messages.confirm_clear_log') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                        <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        @lang('messages.clear_log')
                    </button>
                </form>
            </div>
            @endif
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });

        document.addEventListener('click', function(e) {
            var button = e.target.closest('.js-copy-error');
            if (!button) return;

            var text = button.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                var copyIcon = button.querySelector('.js-copy-icon');
                var checkIcon = button.querySelector('.js-check-icon');
                copyIcon.classList.add('hidden');
                checkIcon.classList.remove('hidden');
                setTimeout(function() {
                    copyIcon.classList.remove('hidden');
                    checkIcon.classList.add('hidden');
                }, 1500);
            });
        });
    </script>

</x-app-admin-layout>
