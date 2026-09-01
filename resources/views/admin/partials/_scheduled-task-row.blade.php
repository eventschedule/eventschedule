{{-- One scheduled task. A two-line list row rather than a table cell: 34 of 38 rows have nothing
     to say beyond a dot and two clauses, and a 6-column table on a phone degrades to horizontal
     scrolling. Mirrors the needs-attention row idiom. --}}
@php
    $row = $task->row;
    $dot = match ($task->state) {
        'failed', 'never_finished', 'overdue' => 'bg-red-500 dark:bg-red-400',
        'running' => 'bg-blue-500 dark:bg-blue-400',
        'ok' => 'bg-green-500 dark:bg-green-400',
        default => 'bg-gray-300 dark:bg-gray-600',
    };
    $ageClass = in_array($task->state, ['failed', 'never_finished', 'overdue'], true)
        ? 'text-red-600 dark:text-red-400'
        : 'text-gray-500 dark:text-gray-400';
    $cadence = \Carbon\CarbonInterval::seconds($task->interval)->cascade()->forHumans(['short' => true, 'parts' => 1]);
    $since = $task->lastSeenAt?->diffForHumans(null, true, true);
    $label = match ($task->state) {
        'failed' => __('messages.failed'),
        'never_finished' => __('messages.task_never_finished', ['age' => $row?->last_started_at?->diffForHumans(null, true, true) ?? '?']),
        // A task can be overdue having NEVER run, in which case there is no age to report and
        // the placeholder would render the literal "overdue by ?" at the operator.
        'overdue' => $since === null
            ? __('messages.scheduler_never_ran')
            : __('messages.task_overdue_by', ['age' => $since]),
        'running' => __('messages.task_running_for', ['age' => $row?->last_started_at?->diffForHumans(null, true, true) ?? '?']),
        'ok' => __('messages.task_ran_ago', ['age' => $since ?? '?']),
        'not_yet_run' => __('messages.task_not_yet_run'),
        default => '',
    };
@endphp
<div class="px-5 py-3">
    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
        <span class="inline-block w-2 h-2 rounded-full shrink-0 self-center {{ $dot }}"></span>
        {{-- dir=ltr so a task name does not bidi-reorder in Arabic or Hebrew. --}}
        <span dir="ltr" class="font-mono text-sm text-gray-900 dark:text-white break-all">{{ $task->name }}</span>
        <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
            {{ __('messages.task_cadence_every', ['interval' => $cadence]) }}
        </span>
        @if ($label !== '')
        <span class="text-sm {{ $ageClass }} whitespace-nowrap ms-auto"
              @if ($row?->last_finished_at) title="{{ $row->last_finished_at->format('Y-m-d H:i:s') }}" @endif>{{ $label }}</span>
        @endif
    </div>
    @if ($row?->last_error && $task->state === 'failed')
    <details class="mt-1">
        <summary class="cursor-pointer text-xs text-red-600 dark:text-red-400 truncate">{{ Str::limit($row->last_error, 120) }}</summary>
        <pre class="mt-2 text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap break-words max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-3 rounded">{{ $row->last_error }}</pre>
    </details>
    @endif
</div>
