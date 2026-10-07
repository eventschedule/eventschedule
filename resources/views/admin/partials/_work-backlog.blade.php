{{-- How much work is WAITING. The scheduler card above answers whether the runner is alive, and
     the strip below counts the jobs table - and almost none of this work goes through that table,
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
<x-page-card :title="__('messages.work_waiting')" :lead="__('messages.work_waiting_description')" flush>
    <x-slot name="aside">
        <span class="sys-figure">{{ trans_choice('messages.work_waiting_total', $total, ['count' => number_format($total)]) }}</span>
    </x-slot>

    {{-- Translations, broken out by pass: it is the largest backlog on the platform and the only
         one whose rows cost money to clear, so it gets the rate and the estimate. --}}
    <div class="sys-block" style="border-top: 0">
        <div class="sys-block-head">
            <h3>{{ __('messages.translation_backlog') }}</h3>
            <span dir="ltr" class="sys-mono">app-translate</span>
            @if ($translateCadence = $cadence('app-translate'))
            <span>{{ $translateCadence }}</span>
            @endif
        </div>

        <dl class="page-kv">
            @foreach ($translation as $pass)
            <div>
                <dt>{{ $pass['label'] }}</dt>
                <dd>
                    @if ($pass['never_attempted'] > 0)
                    <span class="sys-note sys-warn" style="margin: 0">{{ __('messages.translation_never_attempted_short', ['count' => number_format($pass['never_attempted'])]) }}</span>
                    @endif
                    <span class="{{ $pass['pending'] > 0 ? '' : 'sys-quiet' }}">{{ number_format($pass['pending']) }}</span>
                </dd>
            </div>
            @endforeach
        </dl>

        {{-- The re-check figure is deliberately apart from the counts above. These are schedules
             the roles pass must open to be sure - SQL cannot see inside their JSON columns - and
             the run parks them for free. Counted as pending they held the total permanently above
             zero, which reads as a broken cron rather than as a caveat. --}}
        @if ($recheck > 0)
        <p class="sys-note">{{ trans_choice('messages.translation_recheck_note', $recheck, ['count' => number_format($recheck)]) }}</p>
        @endif

        {{-- Named, not subtracted. These rows are still waiting - they come back once the cutoff
             passes - but they are provably not what the next run will attempt, so an estimate that
             treats them as drainable is one that never arrives. --}}
        @if ($cooling > 0)
        <p class="sys-note sys-warn">{{ trans_choice('messages.translation_cooling_off_note', $cooling, ['count' => number_format($cooling)]) }}</p>
        @endif

        <div class="sys-rate">
            @if ($translationRate)
            <span title="{{ $translationRate->at->format('Y-m-d H:i:s') }}">{{ __('messages.translation_last_run', [
                'count' => number_format($translationRate->translated),
                'seconds' => rtrim(rtrim(number_format($translationRate->seconds, 1), '0'), '.').'s',
            ]) }}</span>
            @if ($translationRate->perHour > 0)
            <span aria-hidden="true"> &middot; </span>{{ __('messages.translation_rate_per_hour', ['count' => number_format($translationRate->perHour)]) }}
            @endif
            @if ($hours !== null)
            <span aria-hidden="true"> &middot; </span><strong>{{ __('messages.translation_time_to_clear', [
                'duration' => \Carbon\CarbonInterval::minutes(max(1, (int) round($hours * 60)))->cascade()->forHumans(['parts' => 1]),
            ]) }}</strong>
            @endif
            @elseif (\App\Services\WorkBacklog::rateUnavailableReason() === 'unshared_cache')
            {{-- Not "never measured": the run summary is written by whichever container ran the
                 command, so a per-container cache store hides a perfectly healthy cron from this
                 page. Same distinction the scheduler card's cache-store row exists to draw. --}}
            {{ __('messages.translation_rate_unshared_cache', ['store' => $cacheStore]) }}
            @else
            <span class="sys-quiet">{{ __('messages.translation_rate_unknown') }}</span>
            @endif

            {{-- A rate is a record and stays true; an ETA is a claim about the future. When the
                 task behind it is failed or overdue - which the scheduler card directly above is
                 already saying - printing a clearing time would read as reassurance at exactly the
                 wrong moment. --}}
            @if ($translationRate && $confirmed > 0 && ! $translationRate->taskHealthy)
            <p class="sys-note">{{ __('messages.translation_eta_suppressed') }}</p>
            @endif
        </div>
    </div>

    {{-- Everything else that has a countable backlog. One row each: the count is the whole
         message, and a task that drains itself needs no more than a number beside its name. --}}
    @if (! empty($workBacklog['entries']))
    <div class="sys-rows">
        <table class="page-table">
            {{-- The rows say what they are; the headings are there for a screen reader. --}}
            <thead class="sr-only">
                <tr>
                    <th scope="col">{{ __('messages.name') }}</th>
                    <th scope="col">{{ __('messages.scheduled_tasks') }}</th>
                    <th scope="col">{{ __('messages.frequency') }}</th>
                    <th scope="col">{{ __('messages.pending') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($workBacklog['entries'] as $entry)
                <tr>
                    <td class="c-main">{{ $entry['label'] }}</td>
                    <td class="c-quiet c-mono"><bdi dir="ltr">{{ $entry['task'] }}</bdi></td>
                    <td class="c-quiet">@if ($entryCadence = $cadence($entry['task'])){{ $entryCadence }}@endif</td>
                    <td class="c-num {{ $entry['count'] > 0 ? 'c-strong' : 'sys-quiet' }}">{{ number_format($entry['count']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if ($translationRate?->budgetReached)
    <div class="sys-block">
        <x-page-notice tone="warn">{{ __('messages.translation_budget_reached') }}</x-page-notice>
    </div>
    @endif

    {{-- These counts are unindexed scans, so the page serves a cached measurement rather than
         re-running them on every reload. Saying so - and offering the button - is what keeps that
         honest: without it the nav's Refresh appears to do nothing for five minutes. --}}
    <x-slot name="foot">
        <div class="sys-foot">
            <span title="{{ $measuredAt->format('Y-m-d H:i:s') }}">{{ __('messages.work_waiting_measured', ['age' => $measuredAt->diffForHumans(null, true, true)]) }}</span>
            <form method="POST" action="{{ route('admin.queue.remeasure') }}">
                @csrf
                <button type="submit" class="page-tool">{{ __('messages.work_waiting_remeasure') }}</button>
            </form>
        </div>
    </x-slot>
</x-page-card>
