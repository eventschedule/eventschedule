<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'queue'])

        @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
        @endif

        {{-- Alert Banner --}}
        @if ($failedJobsCount > 0 || ($oldestJobAge && $oldestJobAge->diffInMinutes(now()) > 60))
        <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ms-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">@lang('messages.queue_health_issues')</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <ul class="list-disc ps-5 space-y-1">
                            @if ($failedJobsCount > 0)
                            <li>@lang('messages.n_failed_jobs', ['count' => number_format($failedJobsCount)])</li>
                            @endif
                            @if ($oldestJobAge && $oldestJobAge->diffInMinutes(now()) > 60)
                            <li>@lang('messages.oldest_job_stuck', ['age' => $oldestJobAge->diffForHumans(null, true, true)])</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Health Overview Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.pending_jobs')</h4>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($pendingJobsCount) }}</p>
                @if ($pendingByQueue->count() > 0)
                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                    @foreach ($pendingByQueue as $queueRow)
                    <div>{{ $queueRow->queue }}: {{ number_format($queueRow->count) }}</div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.failed_jobs')</h4>
                </div>
                <p class="text-2xl font-bold {{ $failedJobsCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($failedJobsCount) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.job_batches')</h4>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($jobBatchesCount) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.oldest_pending_job')</h4>
                </div>
                @if ($oldestJobAge)
                <p class="text-2xl font-bold {{ $oldestJobAge->diffInMinutes(now()) > 60 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $oldestJobAge->diffForHumans(null, false, true) }}</p>
                @else
                <p class="text-2xl font-bold text-gray-900 dark:text-white">@lang('messages.none')</p>
                @endif
            </div>
        </div>

        {{-- Pending Jobs Breakdown --}}
        @if ($pendingByClass->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.pending_jobs_by_class')</h3>
            <div class="space-y-3">
                @foreach ($pendingByClass as $className => $count)
                <div class="flex items-center">
                    <div class="w-48 flex-shrink-0">
                        <span class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $className }}</span>
                    </div>
                    <div class="flex-1 mx-4">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                            <div class="bg-blue-500 h-4 rounded-full" style="width: {{ min(100, ($count / $pendingByClass->first()) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="w-16 text-end">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($count) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Bulk Actions Bar --}}
        @if ($failedJobsCount > 0 || $pendingJobsCount > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-wrap gap-3">
            @if ($failedJobsCount > 0)
            <form method="POST" action="{{ route('admin.queue.retry-all') }}" class="js-confirm-form" data-confirm="Retry all {{ number_format($failedJobsCount) }} failed jobs?">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                    @lang('messages.retry_all_failed')
                </button>
            </form>
            <form method="POST" action="{{ route('admin.queue.clear-failed') }}" class="js-confirm-form" data-confirm="Permanently delete all {{ number_format($failedJobsCount) }} failed jobs?">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                    @lang('messages.clear_all_failed')
                </button>
            </form>
            @endif
            @if ($pendingJobsCount > 0)
            <form method="POST" action="{{ route('admin.queue.flush-pending') }}" class="js-confirm-form" data-confirm="Permanently delete all {{ number_format($pendingJobsCount) }} pending jobs? This cannot be undone.">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                    @lang('messages.flush_pending')
                </button>
            </form>
            @endif
        </div>
        @endif

        {{-- Failed Jobs Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.failed_jobs')</h3>
            @if ($failedJobs->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.job_class')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.queue')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.exception')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.failed_at')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($failedJobs as $job)
                        <tr>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">{{ $job->class_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $job->queue }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white max-w-md">
                                <details>
                                    <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 truncate max-w-md">{{ $job->exception_excerpt }}</summary>
                                    <pre class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-3 rounded">{{ $job->exception }}</pre>
                                </details>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" title="{{ $job->failed_at->format('Y-m-d H:i:s') }}">{{ $job->failed_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-sm whitespace-nowrap">
                                <div class="flex gap-3">
                                    <form method="POST" action="{{ route('admin.queue.retry', $job->uuid) }}">
                                        @csrf
                                        <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">@lang('messages.retry')</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.queue.delete', $job->uuid) }}">
                                        @csrf
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 font-medium">@lang('messages.delete')</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_failed_jobs')</p>
            @endif
        </div>

        {{-- Pending Jobs Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.pending_jobs')</h3>
            @if ($pendingJobsTable->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.job_class')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.queue')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.attempts')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.created_at')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.available_at')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($pendingJobsTable as $job)
                        <tr>
                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">{{ $job->class_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $job->queue }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-white">{{ $job->attempts }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" title="{{ $job->created_at->format('Y-m-d H:i:s') }}">{{ $job->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" title="{{ $job->available_at->format('Y-m-d H:i:s') }}">{{ $job->available_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_pending_jobs')</p>
            @endif
        </div>

        {{-- Job Batches Table --}}
        @if ($jobBatches->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.job_batches')</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.name')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.total')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.pending')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.failed')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.progress')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.created')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.finished')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($jobBatches as $batch)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $batch->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-white">{{ number_format($batch->total_jobs) }}</td>
                            <td class="px-4 py-3 text-sm text-end text-gray-900 dark:text-white">{{ number_format($batch->pending_jobs) }}</td>
                            <td class="px-4 py-3 text-sm text-end {{ $batch->failed_jobs > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($batch->failed_jobs) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden min-w-[80px]">
                                        <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $batch->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $batch->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" title="{{ $batch->created_at->format('Y-m-d H:i:s') }}">{{ $batch->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                @if ($batch->finished_at)
                                <span title="{{ $batch->finished_at->format('Y-m-d H:i:s') }}">{{ $batch->finished_at->diffForHumans() }}</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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
    </script>

</x-app-admin-layout>
