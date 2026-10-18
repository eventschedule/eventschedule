{{-- "Publish new events automatically" for one standing calendar sync (CalendarArrival), with the
     row's status lines: what the last read did, and why reading stopped if something stopped it.
     Shown while the direction reads the calendar ("from" or "both"); the script in
     role/partials/calendar-publish-script shows and hides it with the radios, warns while it is on,
     and tells the save bar what Save will change. Saved by RoleController::update() behind the
     provider's own marker, for the owner. --}}
@php
    $arrivalSettings = \App\Services\CalendarArrival::settings($role);
    $publishesNow = \App\Services\CalendarArrival::publishes($role, $provider);
    $readsNow = in_array($direction, ['from', 'both'], true);
    $lastPull = $arrivalSettings->{"{$provider}_last_pull"};
    $stoppedReason = $arrivalSettings->{"{$provider}_stopped_reason"};
    $stoppedAt = $arrivalSettings->{"{$provider}_stopped_at"};
    // Held entries were read in (they are in "added"); left out is what was not read in at all.
    $leftOutLast = $lastPull ? (int) ($lastPull['private'] ?? 0) + (int) ($lastPull['declined'] ?? 0) + (int) ($lastPull['not_an_event'] ?? 0) + (int) ($lastPull['cancelled'] ?? 0) : 0;
    // The drafts already waiting, which turning the switch on leaves as they are.
    $waitingNow = \App\Services\CalendarArrival::tablesReady()
        ? \App\Models\CalendarImportHold::where('role_id', $role->id)->whereNull('decided_at')->where('was_public', false)
            ->whereHas('event', fn ($q) => $q->where('import_source', $provider)->where('is_draft', true))->count()
        : 0;
@endphp

@if ($stoppedReason && ! $readsNow)
<div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
    <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
    <p class="text-sm text-amber-800 dark:text-amber-200">
        {{ $stoppedReason === 'transfer'
            ? __('messages.calendar_stopped_transfer')
            : __($provider === \App\Services\CalendarArrival::CALDAV ? 'messages.calendar_stopped_admin_caldav' : 'messages.calendar_stopped_admin', ['date' => $stoppedAt ? $stoppedAt->copy()->setTimezone($role->captureTimezone())->translatedFormat('M j') : '']) }}
    </p>
</div>
@endif

<div class="space-y-3" data-calendar-publish="{{ $provider }}" data-waiting="{{ $waitingNow }}" @if (! $readsNow) hidden @endif>
    <x-toggle :name="$provider.'_publish_on_arrival'" :id="$provider.'-publish-on-arrival'"
        :label="__('messages.publish_on_arrival')" :checked="$publishesNow"
        :help="__('messages.publish_on_arrival_help')"
        data-calendar-publish-toggle="{{ $provider }}" data-saved="{{ $publishesNow ? '1' : '0' }}" />
    <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20" data-calendar-publish-warning="{{ $provider }}" @if (! $publishesNow) hidden @endif>
        <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.publish_on_arrival_warning') }}</p>
    </div>
    @if ($lastPull && $readsNow && ! empty($lastPull['at']))
    <p class="event-hint">{{ __('messages.calendar_last_read', [
        'time' => \Carbon\Carbon::parse($lastPull['at'])->setTimezone($role->captureTimezone())->translatedFormat('M j').', '.\Carbon\Carbon::parse($lastPull['at'])->setTimezone($role->captureTimezone())->format(get_use_24_hour_time($role) ? 'H:i' : 'g:i A'),
        'added' => number_format((int) ($lastPull['created'] ?? 0)),
        'left' => number_format($leftOutLast),
    ]) }}</p>
    @endif
</div>
