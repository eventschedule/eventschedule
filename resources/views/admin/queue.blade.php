<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* A small button that destroys something: the weight of .page-tool, in red. */
            .page-tool.is-danger {
              border-color: rgba(220, 38, 38, 0.4);
              color: #b91c1c;
            }
            .page-tool.is-danger:hover {
              border-color: #dc2626;
              background: rgba(220, 38, 38, 0.08);
              color: #b91c1c;
            }
            .dark .page-tool.is-danger,
            .dark .page-tool.is-danger:hover {
              border-color: rgba(248, 113, 113, 0.5);
              color: #f87171;
            }
            /* The verdict at the end of a card's head: the kit's status mark, a size up, so
               "Never run" is the first thing read on the card without being a red headline. */
            .sys-verdict {
              font-size: 1rem;
              font-weight: 600;
            }
            .sys-verdict::before {
              width: 0.625rem;
              height: 0.625rem;
            }
            .sys-figure {
              font-size: 1rem;
              font-weight: 600;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink));
            }
            /* The blocks of a card, side by side where there is room. The lines between them
               are drawn as the kit draws a strip's, so they hold wherever the row breaks. */
            .sys-cols {
              display: grid;
              grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
              overflow: hidden;
            }
            .sys-cols > div {
              min-width: 0;
              padding: 1rem 1.25rem;
              box-shadow: -1px 0 0 rgb(var(--ap-border)), 0 -1px 0 rgb(var(--ap-border));
            }
            [dir="rtl"] .sys-cols > div {
              box-shadow: 1px 0 0 rgb(var(--ap-border)), 0 -1px 0 rgb(var(--ap-border));
            }
            .sys-label {
              margin: 0 0 0.5rem;
              font-size: 0.75rem;
              font-weight: 600;
              color: rgb(var(--ap-ink-3));
            }
            .sys-text {
              margin: 0;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink));
            }
            .sys-cols .page-kv > div {
              border-top: 0;
              padding: 0.125rem 0;
            }
            /* The name keeps its line; a long value (a container's host) is the one cut short. */
            .sys-cols .page-kv dt {
              flex: none;
            }
            .sys-cols .page-kv dd {
              min-width: 0;
              overflow: hidden;
              text-overflow: ellipsis;
              font-weight: 500;
            }
            .sys-mono {
              font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
              font-size: 0.8125rem;
            }
            .sys-bad {
              color: #b91c1c;
            }
            .dark .sys-bad {
              color: #f87171;
            }
            .sys-warn {
              color: #b45309;
            }
            .dark .sys-warn {
              color: #fbbf24;
            }
            .sys-quiet {
              font-weight: 400;
              color: rgb(var(--ap-ink-3));
            }
            /* A part of a flush card that is not a table. */
            .sys-block {
              border-top: 1px solid rgb(var(--ap-border));
              padding: 1rem 1.25rem;
            }
            .sys-block-head {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.25rem 0.75rem;
              margin-bottom: 0.5rem;
            }
            .sys-block-head h3 {
              margin: 0;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .sys-block-head span,
            .sys-note {
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            .sys-note {
              margin: 0.5rem 0 0;
            }
            .sys-block .page-kv > div {
              padding: 0.375rem 0;
            }
            .sys-block .page-kv dd {
              display: flex;
              align-items: baseline;
              gap: 0.625rem;
            }
            .sys-rate {
              margin: 0.75rem 0 0;
              border-top: 1px solid rgb(var(--ap-border));
              padding-top: 0.75rem;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
            .sys-rate strong {
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            /* One scheduled task: its name and how often, then how it stands. */
            .sys-rows {
              border-top: 1px solid rgb(var(--ap-border));
            }
            .sys-rows.is-bad {
              background: rgba(239, 68, 68, 0.05);
            }
            .sys-task {
              padding: 0.625rem 1.25rem;
            }
            .sys-task + .sys-task {
              border-top: 1px solid var(--ap-hairline);
            }
            .sys-task-line {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.25rem 0.75rem;
            }
            .sys-task-name {
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink));
            }
            .sys-task-every {
              font-size: 0.75rem;
              white-space: nowrap;
              color: rgb(var(--ap-ink-3));
            }
            .sys-task-line .event-status {
              margin-inline-start: auto;
              white-space: nowrap;
            }
            .sys-task details {
              margin-top: 0.25rem;
            }
            .sys-task summary {
              overflow: hidden;
              font-size: 0.75rem;
              text-overflow: ellipsis;
              white-space: nowrap;
              cursor: pointer;
            }
            .sys-more > summary {
              display: flex;
              align-items: center;
              justify-content: center;
              gap: 0.375rem;
              border-top: 1px solid rgb(var(--ap-border));
              padding: 0.75rem 1.25rem;
              font-size: 0.875rem;
              font-weight: 500;
              list-style: none;
              color: rgb(var(--ap-ink-3));
              cursor: pointer;
              user-select: none;
              transition: background-color 0.2s, color 0.2s;
            }
            .sys-more > summary::-webkit-details-marker {
              display: none;
            }
            .sys-more > summary:hover {
              background: var(--ap-tint-1);
              color: rgb(var(--ap-ink));
            }
            .sys-more > summary svg {
              width: 1rem;
              height: 1rem;
              transition: transform 0.2s;
            }
            .sys-more[open] > summary svg {
              transform: rotate(180deg);
            }
            .sys-pre {
              max-height: 16rem;
              margin: 0.5rem 0 0;
              overflow-y: auto;
              border-radius: 0.5rem;
              padding: 0.75rem;
              background: var(--ap-tint-1);
              font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
              font-size: 0.75rem;
              line-height: 1.5;
              white-space: pre-wrap;
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink-2));
            }
            .sys-foot {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.5rem 1rem;
            }
            /* How the waiting jobs divide: a name, its share, its count. */
            .sys-bars {
              display: grid;
              grid-template-columns: minmax(0, auto) minmax(3rem, 1fr) auto;
              align-items: center;
              gap: 0.625rem 1rem;
            }
            .sys-bar {
              height: 0.5rem;
              overflow: hidden;
              border-radius: 999px;
              background: var(--ap-tint-2);
            }
            .sys-bar > i {
              display: block;
              height: 100%;
              border-radius: 999px;
              background: var(--brand-blue);
            }
            .sys-bars bdi {
              color: rgb(var(--ap-ink-2));
              overflow-wrap: anywhere;
            }
            .sys-bars b {
              font-size: 0.875rem;
              font-weight: 600;
              font-variant-numeric: tabular-nums;
              text-align: end;
              color: rgb(var(--ap-ink));
            }
            .page-table details > summary {
              cursor: pointer;
              overflow-wrap: anywhere;
            }
            .page-table details > summary:hover {
              color: var(--brand-blue);
            }
            .page-table td {
              vertical-align: top;
            }
            .page-table .c-bar {
              min-width: 9rem;
            }
            .page-table .c-bar > div {
              display: flex;
              align-items: center;
              gap: 0.5rem;
            }
            .page-table .c-bar .sys-bar {
              flex: 1;
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'queue'])

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_queue_lead') }}</p>
    </div>

    @php
        $jobsStalled = $oldestJobAge && $oldestJobAge->diffInMinutes(now()) >= \App\Services\AdminAlertService::JOBS_STALLED_MINUTES;
    @endphp

    <div class="page-shell page-stack">
        <x-page-flash :keys="['success' => 'success']" />

        @if ($schedulerStalled || $failedJobsCount > 0 || $jobsStalled)
        <x-page-notice tone="error" :title="__('messages.queue_health_issues')">
            <ul class="mt-1 list-disc ps-5 space-y-1">
                {{-- First: nothing else on this page can drain while the scheduler is down. --}}
                @if ($schedulerStalled)
                <li>{{ __('messages.scheduler_stalled_detail', ['minutes' => $schedulerStaleMinutes]) }}</li>
                @endif
                @if ($failedJobsCount > 0)
                <li>{{ __('messages.n_failed_jobs', ['count' => number_format($failedJobsCount)]) }}</li>
                @endif
                @if ($jobsStalled)
                <li>{{ __('messages.oldest_job_stuck', ['age' => $oldestJobAge->diffForHumans(null, true, true)]) }}</li>
                @endif
            </ul>
        </x-page-notice>
        @endif

        {{-- Scheduler. One card holding summary, exceptions and the full list, so every scheduler
             signal sits together. It answers a different question from the job counts further
             down - is the runner alive, not how much work is waiting - and the page reads
             top-down in that order: is the runner alive, how much has it left to do (the Work
             Waiting card), then the jobs table itself. --}}
        <x-page-card :title="__('messages.scheduler')" flush>
            <x-slot name="aside">
                <span class="event-status sys-verdict {{ $schedulerStalled ? 'is-bad' : 'is-on' }}">
                    @if ($schedulerLastRunAt)
                    {{-- true = DIFF_ABSOLUTE. The second argument is Carbon's $syntax, not "omit the suffix":
                         false yields "5m ago", and the string appends its own, giving "last tick 5m ago ago". --}}
                    {{ __('messages.scheduler_last_tick', ['age' => $schedulerLastRunAt->diffForHumans(null, true, true)]) }}
                    @else
                    {{ __('messages.scheduler_never_ran') }}
                    @endif
                </span>
            </x-slot>

            <div class="sys-cols">
                <div>
                    <p class="sys-label">{{ __('messages.scheduler_rails') }}</p>
                    @if ($schedulerRails->isNotEmpty())
                    <dl class="page-kv">
                        @foreach ($schedulerRails as $rail)
                        <div>
                            <dt><bdi dir="ltr" class="sys-mono">{{ $rail->name }}</bdi></dt>
                            {{-- $rail->at is NULL for an expected rail that has never ticked. That row is
                                 the whole point of showing it - the operator is waiting on a worker that
                                 has not written a heartbeat yet - so it must render, not fatal. --}}
                            @if ($rail->at)
                            <dd class="{{ $rail->stale ? 'sys-bad' : '' }}" title="{{ $rail->at->format('Y-m-d H:i:s') }}">{{ $rail->at->diffForHumans(null, false, true) }}</dd>
                            @else
                            <dd class="sys-bad">{{ __('messages.scheduler_rail_never_seen') }}</dd>
                            @endif
                        </div>
                        @endforeach
                    </dl>
                    @else
                    <p class="sys-text sys-quiet">{{ __('messages.none') }}</p>
                    @endif
                </div>

                <div>
                    <p class="sys-label">{{ __('messages.scheduled_tasks') }}</p>
                    <p class="sys-text">{{ __('messages.scheduled_tasks_reporting', ['ok' => $tasksReporting, 'total' => $scheduledTasks->count()]) }}</p>
                </div>

                <div>
                    <p class="sys-label">{{ __('messages.needs_attention') }}</p>
                    {{-- Green only while the scheduler is ticking: with it stopped nothing below is
                         known, and a green "healthy" beside a red "Never run" said the opposite. --}}
                    <span class="event-status {{ $taskExceptions->count() > 0 ? 'is-bad' : ($schedulerStalled ? '' : 'is-on') }}">{{ trans_choice('messages.scheduled_tasks_needs_attention', $taskExceptions->count(), ['count' => $taskExceptions->count()]) }}</span>
                </div>

                {{-- Which cache store holds the heartbeat, and whether every container can read it.
                     Without this the operator cannot tell a dead worker from an unshared cache -
                     the two look identical everywhere else on this page. --}}
                <div>
                    <p class="sys-label">{{ __('messages.scheduler_runtime') }}</p>
                    <dl class="page-kv">
                        <div>
                            <dt>{{ __('messages.scheduler_cache_store') }}</dt>
                            <dd><bdi dir="ltr" class="sys-mono">{{ $cacheStore }}</bdi></dd>
                        </div>
                        <div>
                            <dt>{{ __('messages.scheduler_cache_shared') }}</dt>
                            {{-- Amber only when an unshared store is actually causing something. A
                                 single-container selfhost on the `file` driver is perfectly healthy,
                                 and colouring "No" as a warning there would nag every such install
                                 forever about a condition that costs it nothing. --}}
                            <dd class="{{ $cacheHidesScheduler ? 'sys-warn' : '' }}">{{ $cacheStoreIsShared ? __('messages.yes') : __('messages.no') }}</dd>
                        </div>
                        @if ($lastTaskHost)
                        <div>
                            <dt>{{ __('messages.scheduler_running_on') }}</dt>
                            <dd title="{{ $lastTaskHost }}{{ $lastTaskRail ? ' · '.$lastTaskRail : '' }}"><bdi dir="ltr" class="sys-mono">{{ $lastTaskHost }}</bdi></dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- The diagnosis the runbook used to send you to another document for. Only rendered
                 when the evidence is unambiguous: the store is per-container AND the database says
                 tasks are still completing. --}}
            @if ($cacheHidesScheduler)
            <div class="sys-block">
                <x-page-notice tone="warn">{{ __('messages.scheduler_cache_not_shared_detail', ['store' => $cacheStore]) }}</x-page-notice>
            </div>
            @endif

            {{-- Failures render whatever the heartbeat says. SchedulerHealth::state() lets 'failed'
                 survive the stall suppression on purpose - a recorded failure is real data - so
                 hiding it behind the stalled branch would leave the summary above saying "2 tasks
                 need attention" with nothing on screen to act on. --}}
            @if ($taskExceptions->isNotEmpty())
            <div class="sys-rows is-bad">
                @foreach ($taskExceptions as $task)
                    @include('admin.partials._scheduled-task-row', ['task' => $task])
                @endforeach
            </div>
            @endif

            @if ($schedulerHttpRailOnly)
            {{-- The /translate_data rail dispatches no ScheduledTask* events, so the list below can
                 never fill on this install. Say so, rather than showing an empty list that reads as
                 a bug. Blue, not amber: nothing is wrong here. --}}
            <div class="sys-block">
                <x-page-notice tone="info">{{ __('messages.scheduled_tasks_http_rail') }}</x-page-notice>
            </div>
            @elseif ($schedulerStalled)
            <div class="sys-block">
                <p class="sys-text sys-quiet">{{ __('messages.scheduled_tasks_unknown_while_stalled') }}</p>
            </div>
            @elseif ($scheduledTasks->isNotEmpty())
            <details class="sys-more">
                <summary>
                    <span>{{ __('messages.scheduled_tasks_show_all', ['count' => $scheduledTasks->count()]) }}</span>
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="sys-rows">
                    @foreach ($scheduledTasks as $task)
                        @include('admin.partials._scheduled-task-row', ['task' => $task])
                    @endforeach
                </div>
            </details>
            @endif
        </x-page-card>

        @include('admin.partials._work-backlog')

        {{-- The jobs table in figures: one strip, as on the Logs and Audit Log pages. --}}
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($pendingJobsCount) }}</div>
                <div class="page-stat-label">{{ __('messages.pending_jobs') }}</div>
                @if ($pendingByQueue->count() > 0)
                <div class="page-stat-sub">
                    @foreach ($pendingByQueue as $queueRow)<bdi dir="ltr">{{ $queueRow->queue }}: {{ number_format($queueRow->count) }}</bdi>@if (! $loop->last) &middot; @endif @endforeach
                </div>
                @endif
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $failedJobsCount > 0 ? 'is-bad' : '' }}">{{ number_format($failedJobsCount) }}</div>
                <div class="page-stat-label">{{ __('messages.failed_jobs') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($jobBatchesCount) }}</div>
                <div class="page-stat-label">{{ __('messages.job_batches') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $jobsStalled ? 'is-bad' : '' }}">{{ $oldestJobAge ? $oldestJobAge->diffForHumans(null, false, true) : __('messages.none') }}</div>
                <div class="page-stat-label">{{ __('messages.oldest_pending_job') }}</div>
            </div>
        </div>

        @if ($pendingByClass->count() > 0)
        <x-page-card :title="__('messages.pending_jobs_by_class')">
            <div class="sys-bars">
                @foreach ($pendingByClass as $className => $count)
                <bdi dir="ltr" class="sys-mono">{{ $className }}</bdi>
                <div class="sys-bar"><i style="width: {{ min(100, ($count / $pendingByClass->first()) * 100) }}%"></i></div>
                <b>{{ number_format($count) }}</b>
                @endforeach
            </div>
        </x-page-card>
        @endif

        {{-- What acts on a whole list sits in that list's head. The three buttons used to share
             a card of their own above both lists, two of them in red capitals. Each still asks
             first: data-confirm on the form, which the layout's one handler reads. --}}
        <x-page-card :title="__('messages.failed_jobs')" flush>
            @if ($failedJobsCount > 0)
            <x-slot name="aside">
                <form method="POST" action="{{ route('admin.queue.clear-failed') }}" data-confirm="{{ __('messages.confirm_delete_all_failed', ['count' => number_format($failedJobsCount)]) }}">
                    @csrf
                    <button type="submit" class="page-tool is-danger">{{ __('messages.clear_all_failed') }}</button>
                </form>
                <form method="POST" action="{{ route('admin.queue.retry-all') }}" data-confirm="{{ __('messages.confirm_retry_all_failed', ['count' => number_format($failedJobsCount)]) }}">
                    @csrf
                    <button type="submit" class="page-tool">{{ __('messages.retry_all_failed') }}</button>
                </form>
            </x-slot>
            @endif

            @if ($failedJobs->count() > 0)
            <table class="page-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.job_class') }}</th>
                        <th scope="col">{{ __('messages.queue') }}</th>
                        <th scope="col">{{ __('messages.exception') }}</th>
                        <th scope="col">{{ __('messages.failed_at') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($failedJobs as $job)
                    <tr>
                        <td class="c-main c-strong c-mono"><bdi dir="ltr">{{ $job->class_name }}</bdi></td>
                        <td class="c-quiet"><bdi dir="ltr">{{ $job->queue }}</bdi></td>
                        <td class="c-main c-mono">
                            <details>
                                <summary><bdi dir="ltr">{{ Str::limit($job->exception_excerpt, 110) }}</bdi></summary>
                                <pre class="sys-pre" dir="ltr">{{ $job->exception }}</pre>
                            </details>
                        </td>
                        <td class="c-date" title="{{ $job->failed_at->format('Y-m-d H:i:s') }}">{{ $job->failed_at->diffForHumans() }}</td>
                        <td class="c-actions">
                            <form method="POST" action="{{ route('admin.queue.retry', $job->uuid) }}">
                                @csrf
                                <button type="submit" class="event-link">{{ __('messages.retry') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.queue.delete', $job->uuid) }}" data-confirm="{{ __('messages.are_you_sure') }}">
                                @csrf
                                <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <x-page-empty :title="__('messages.no_failed_jobs')" compact />
            @endif
        </x-page-card>

        <x-page-card :title="__('messages.pending_jobs')" flush>
            @if ($pendingJobsCount > 0)
            <x-slot name="aside">
                <form method="POST" action="{{ route('admin.queue.flush-pending') }}" data-confirm="{{ __('messages.confirm_flush_pending', ['count' => number_format($pendingJobsCount)]) }}">
                    @csrf
                    <button type="submit" class="page-tool is-danger">{{ __('messages.flush_pending') }}</button>
                </form>
            </x-slot>
            @endif

            @if ($pendingJobsTable->count() > 0)
            <table class="page-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.job_class') }}</th>
                        <th scope="col">{{ __('messages.queue') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.attempts') }}</th>
                        <th scope="col">{{ __('messages.created_at') }}</th>
                        <th scope="col">{{ __('messages.available_at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingJobsTable as $job)
                    <tr>
                        <td class="c-main c-strong c-mono"><bdi dir="ltr">{{ $job->class_name }}</bdi></td>
                        <td class="c-quiet"><bdi dir="ltr">{{ $job->queue }}</bdi></td>
                        <td class="c-num" data-label="{{ __('messages.attempts') }}">{{ $job->attempts }}</td>
                        <td class="c-date" data-label="{{ __('messages.created_at') }}" title="{{ $job->created_at->format('Y-m-d H:i:s') }}">{{ $job->created_at->diffForHumans() }}</td>
                        <td class="c-date" data-label="{{ __('messages.available_at') }}" title="{{ $job->available_at->format('Y-m-d H:i:s') }}">{{ $job->available_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <x-page-empty :title="__('messages.no_pending_jobs')" compact />
            @endif
        </x-page-card>

        @if ($jobBatches->count() > 0)
        <x-page-card :title="__('messages.job_batches')" flush>
            <div class="page-scroll">
                <table class="page-table is-wide">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.total') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.pending') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.failed') }}</th>
                            <th scope="col">{{ __('messages.progress') }}</th>
                            <th scope="col">{{ __('messages.created') }}</th>
                            <th scope="col">{{ __('messages.finished') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jobBatches as $batch)
                        <tr>
                            <td class="c-main c-strong"><bdi>{{ $batch->name ?? '-' }}</bdi></td>
                            <td class="c-num" data-label="{{ __('messages.total') }}">{{ number_format($batch->total_jobs) }}</td>
                            <td class="c-num" data-label="{{ __('messages.pending') }}">{{ number_format($batch->pending_jobs) }}</td>
                            <td class="c-num {{ $batch->failed_jobs > 0 ? 'sys-bad' : '' }}" data-label="{{ __('messages.failed') }}">{{ number_format($batch->failed_jobs) }}</td>
                            <td class="c-bar">
                                <div>
                                    <div class="sys-bar"><i style="width: {{ $batch->progress }}%"></i></div>
                                    <span class="c-quiet"><bdi dir="ltr">{{ $batch->progress }}%</bdi></span>
                                </div>
                            </td>
                            <td class="c-date" data-label="{{ __('messages.created') }}" title="{{ $batch->created_at->format('Y-m-d H:i:s') }}">{{ $batch->created_at->diffForHumans() }}</td>
                            <td class="c-date" @if ($batch->finished_at) data-label="{{ __('messages.finished') }}" title="{{ $batch->finished_at->format('Y-m-d H:i:s') }}" @endif>@if ($batch->finished_at){{ $batch->finished_at->diffForHumans() }}@endif</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-page-card>
        @endif
    </div>

</x-app-admin-layout>
