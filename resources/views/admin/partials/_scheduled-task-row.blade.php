{{-- One scheduled task. A two-line list row rather than a table cell: 34 of 38 rows have nothing
     to say beyond a name and two clauses, and a 6-column table on a phone degrades to horizontal
     scrolling. Styled by the page that includes it (admin/queue, .sys-task). --}}
@php
    $row = $task->row;
    // How the task stands, as the kit's status mark: a dot and the words, at the end of the row.
    // The row used to carry a dot at its start AND a coloured phrase at its end.
    $tone = match ($task->state) {
        'failed', 'never_finished', 'overdue' => 'is-bad',
        'running' => 'is-info',
        'ok' => 'is-on',
        default => '',
    };
    $cadence = \Carbon\CarbonInterval::seconds($task->interval)->cascade()->forHumans(['short' => true, 'parts' => 1]);
    // Hover detail: which rail and which container actually ran this, and how long it took.
    // That is the question the per-rail work exists to answer ("is the worker the thing running my
    // schedule"), and last_via / last_host / last_runtime_seconds were recorded for it but not
    // rendered anywhere. Data, not prose, so it needs no translation key.
    $detail = collect([
        $row?->last_finished_at?->format('Y-m-d H:i:s'),
        $row?->last_via,
        $row?->last_host,
        $row?->last_runtime_seconds === null ? null : rtrim(rtrim((string) $row->last_runtime_seconds, '0'), '.').'s',
    ])->filter()->implode(' · ');
    $since = $task->lastSeenAt?->diffForHumans(null, true, true);
    $label = match ($task->state) {
        'failed' => __('messages.failed'),
        // The age comes from the same anchor state() judged on - the last COMPLETION - not from
        // last_started_at. A run that overruns its expiry lets the mutex lapse and a fresh copy
        // launch, restamping the start, so this row used to read "started 4m ago and never
        // finished" for a task that had produced nothing in three hours: the label contradicted
        // the verdict beside it.
        //
        // A row with no start at all is one a SKIP created, and saying it started is simply false.
        'never_finished' => $row?->last_started_at
            ? __('messages.task_never_finished', [
                'age' => ($row->last_finished_at ?? $row->created_at)->diffForHumans(null, true, true),
            ])
            : __('messages.scheduler_never_ran'),
        // $since cannot be null here: state() only returns 'overdue' when lastSeenAt() is set,
        // and a task that has never run gets 'not_yet_run' instead.
        'overdue' => __('messages.task_overdue_by', ['age' => $since]),
        'running' => __('messages.task_running_for', ['age' => $row?->last_started_at?->diffForHumans(null, true, true) ?? '?']),
        'ok' => __('messages.task_ran_ago', ['age' => $since ?? '?']),
        'not_yet_run' => __('messages.task_not_yet_run'),
        default => '',
    };
@endphp
<div class="sys-task">
    <div class="sys-task-line">
        {{-- dir=ltr so a task name does not bidi-reorder in Arabic or Hebrew. --}}
        <span dir="ltr" class="sys-mono sys-task-name">{{ $task->name }}</span>
        <span class="sys-task-every">{{ __('messages.task_cadence_every', ['interval' => $cadence]) }}</span>
        @if ($label !== '')
        <span class="event-status {{ $tone }}" @if ($detail !== '') title="{{ $detail }}" @endif>{{ $label }}</span>
        @endif
    </div>
    @if ($row?->last_error && $task->state === 'failed')
    <details>
        {{-- The consecutive count is what separates a one-off blip from something genuinely broken,
             and it is the only reason the column is maintained. Bare digits, so no new lang key. --}}
        <summary class="sys-bad"><bdi dir="ltr">{{ Str::limit($row->last_error, 120) }}</bdi>@if ($row->consecutive_failures > 1) <strong>(&times;{{ $row->consecutive_failures }})</strong>@endif</summary>
        <pre class="sys-pre" dir="ltr">{{ $row->last_error }}</pre>
    </details>
    @endif
</div>
