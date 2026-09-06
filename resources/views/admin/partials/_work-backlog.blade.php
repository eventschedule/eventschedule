{{-- How much work is WAITING. The scheduler card above answers whether the runner is alive, and
     the bento below counts the jobs table - and almost none of this work goes through that table,
     because app:translate and its siblings do their work inline inside the scheduled run. So this
     panel sat missing between them: a healthy scheduler and an empty queue, with thousands of rows
     untranslated and the only count of them on /admin/usage, a page about AI spend. --}}
@php
    $translation = $workBacklog['translation'];
    $measuredAt = \Illuminate\Support\Carbon::createFromTimestamp($workBacklog['measured_at'])
        ->setTimezone(config('app.timezone'));
    $confirmed = \App\Services\WorkBacklog::translationPending($translation);
    $recheck = \App\Services\WorkBacklog::translationRecheck($translation);
    $hours = \App\Services\WorkBacklog::hoursToClear($confirmed, $translationRate);
    $cooling = (int) collect($translation)->sum('cooling_off');
    $total = $confirmed + collect($workBacklog['entries'])->sum('count');

    // Cadence straight from the live schedule, so it cannot drift from routes/console.php. Null
    // on the http rail, which dispatches no per-task events and so has no row here.
    $cadence = function (string $name) use ($scheduledTasks) {
        $task = $scheduledTasks->firstWhere('name', $name);

        return $task
            ? __('messages.task_cadence_every', [
                'interval' => \Carbon\CarbonInterval::seconds($task->interval)->cascade()->forHumans(['short' => true, 'parts' => 1]),
            ])
            : null;
    };
@endphp
<div class="ap-card rounded-xl shadow overflow-hidden">
    <div class="p-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <svg aria-hidden="true" class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.work_waiting')</h2>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ trans_choice('messages.work_waiting_total', $total, ['count' => number_format($total)]) }}
        </p>
    </div>

    <p class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">@lang('messages.work_waiting_description')</p>

    {{-- Translations, broken out by pass: it is the largest backlog on the platform and the only
         one whose rows cost money to clear, so it gets the rate and the estimate. --}}
    <div class="border-t border-gray-200 dark:border-gray-700 p-5">
        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mb-3">
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.translation_backlog')</h4>
            <span dir="ltr" class="font-mono text-xs text-gray-500 dark:text-gray-400">app-translate</span>
            @if ($translateCadence = $cadence('app-translate'))
            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $translateCadence }}</span>
            @endif
        </div>

        <div class="space-y-1">
            @foreach ($translation as $pass)
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 text-sm">
                <span class="text-gray-700 dark:text-gray-300">{{ $pass['label'] }}</span>
                <span class="flex items-baseline gap-2 whitespace-nowrap">
                    @if ($pass['never_attempted'] > 0)
                    <span class="text-xs text-amber-600 dark:text-amber-400">{{ __('messages.translation_never_attempted_short', ['count' => number_format($pass['never_attempted'])]) }}</span>
                    @endif
                    <span class="{{ $pass['pending'] > 0 ? 'font-medium text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ number_format($pass['pending']) }}</span>
                </span>
            </div>
            @endforeach
        </div>

        {{-- The re-check figure is deliberately apart from the counts above. These are schedules
             the roles pass must open to be sure - SQL cannot see inside their JSON columns - and
             the run parks them for free. Counted as pending they held the total permanently above
             zero, which reads as a broken cron rather than as a caveat. --}}
        @if ($recheck > 0)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            {{ trans_choice('messages.translation_recheck_note', $recheck, ['count' => number_format($recheck)]) }}
        </p>
        @endif

        {{-- Named, not subtracted. These rows are still waiting - they come back once the cutoff
             passes - but they are provably not what the next run will attempt, so an estimate that
             treats them as drainable is one that never arrives. --}}
        @if ($cooling > 0)
        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
            {{ trans_choice('messages.translation_cooling_off_note', $cooling, ['count' => number_format($cooling)]) }}
        </p>
        @endif

        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-white/[0.06] text-sm">
            @if ($translationRate)
            <p class="text-gray-500 dark:text-gray-400">
                <span title="{{ $translationRate->at->format('Y-m-d H:i:s') }}">{{ __('messages.translation_last_run', [
                    'count' => number_format($translationRate->translated),
                    'seconds' => rtrim(rtrim(number_format($translationRate->seconds, 1), '0'), '.').'s',
                ]) }}</span>
                @if ($translationRate->perHour > 0)
                <span aria-hidden="true"> &middot; </span>{{ __('messages.translation_rate_per_hour', ['count' => number_format($translationRate->perHour)]) }}
                @endif
                @if ($hours !== null)
                <span aria-hidden="true"> &middot; </span><span class="font-medium text-gray-900 dark:text-white">{{ __('messages.translation_time_to_clear', [
                    'duration' => \Carbon\CarbonInterval::minutes(max(1, (int) round($hours * 60)))->cascade()->forHumans(['parts' => 1]),
                ]) }}</span>
                @endif
            </p>
            @elseif (\App\Services\WorkBacklog::rateUnavailableReason() === 'unshared_cache')
            {{-- Not "never measured": the run summary is written by whichever container ran the
                 command, so a per-container cache store hides a perfectly healthy cron from this
                 page. Same distinction the scheduler card's cache-store row exists to draw. --}}
            <p class="text-gray-500 dark:text-gray-400">@lang('messages.translation_rate_unshared_cache', ['store' => $cacheStore])</p>
            @else
            <p class="text-gray-400 dark:text-gray-500">@lang('messages.translation_rate_unknown')</p>
            @endif

            {{-- A rate is a record and stays true; an ETA is a claim about the future. When the
                 task behind it is failed or overdue - which the scheduler card directly above is
                 already saying - printing a clearing time would read as reassurance at exactly the
                 wrong moment. --}}
            @if ($translationRate && $confirmed > 0 && ! $translationRate->taskHealthy)
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.translation_eta_suppressed')</p>
            @endif
        </div>
    </div>

    {{-- Everything else that has a countable backlog. One line each: the count is the whole
         message, and a task that drains itself needs no more than a number beside its name. --}}
    @if (! empty($workBacklog['entries']))
    <div class="border-t border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-white/[0.06]">
        @foreach ($workBacklog['entries'] as $entry)
        <div class="px-5 py-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $entry['label'] }}</span>
            <span class="flex items-baseline gap-3 whitespace-nowrap ms-auto">
                <span dir="ltr" class="font-mono text-xs text-gray-500 dark:text-gray-400">{{ $entry['task'] }}</span>
                @if ($entryCadence = $cadence($entry['task']))
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $entryCadence }}</span>
                @endif
                <span class="text-sm w-12 text-end {{ $entry['count'] > 0 ? 'font-medium text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ number_format($entry['count']) }}</span>
            </span>
        </div>
        @endforeach
    </div>
    @endif

    @if ($translationRate?->budgetReached)
    <div class="border-t border-gray-200 dark:border-gray-700 p-5">
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
            <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.48 0l-7.1 12.25A2 2 0 004.99 19z"/>
                </svg>
                <span>@lang('messages.translation_budget_reached')</span>
            </p>
        </div>
    </div>
    @endif

    {{-- These counts are unindexed scans, so the page serves a cached measurement rather than
         re-running them on every reload. Saying so - and offering the button - is what keeps that
         honest: without it the nav's Refresh appears to do nothing for five minutes. --}}
    <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-3 flex flex-wrap items-center justify-between gap-3">
        <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $measuredAt->format('Y-m-d H:i:s') }}">
            {{ __('messages.work_waiting_measured', ['age' => $measuredAt->diffForHumans(null, true, true)]) }}
        </span>
        <form method="POST" action="{{ route('admin.queue.remeasure') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-[var(--brand-blue)] hover:underline">@lang('messages.work_waiting_remeasure')</button>
        </form>
    </div>
</div>
