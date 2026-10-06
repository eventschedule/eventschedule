{{-- Page views, not visitors: it counts people who have not accepted cookies too, and never
     overstates who can be seen. No live dot: this page does not update itself. Shares the row with
     the alert chips when there are any, pushed to its end. --}}
@if ($realtimeRecentViews !== null)
    <p class="{{ $adminAlerts->isNotEmpty() ? 'sm:ms-auto ' : '' }}text-sm text-gray-600 dark:text-gray-400">
        {{ trans_choice('messages.realtime_recent_views', $realtimeRecentViews, ['count' => number_format($realtimeRecentViews)]) }}
        &middot;
        <x-link :href="route('admin.realtime')">@lang('messages.realtime_view')</x-link>
    </p>
@endif
