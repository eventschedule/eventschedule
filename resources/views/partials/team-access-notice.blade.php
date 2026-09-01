{{--
    Why this exists: when User::planAllowsTeamAccess() drops a schedule, every list scoped by
    Event::managedBy()/scannableBy() comes back empty and the page falls through to its ordinary
    empty state - on /sales that reads "No sales found. Create events to start selling tickets.",
    which is false twice over for a team member who is simply not permitted to see rows that do
    exist, and which sent one customer round three rounds of support email.

    Expects $roles = auth()->user()->planBlockedRoles(). Renders nothing when that is empty, so
    callers can include it unconditionally.

    x-user-text, not a bare {{ }}: schedule names are user-controlled and the AP mounts Vue with
    the runtime template compiler, so an unguarded name containing a Vue mustache would be
    compiled and executed rather than printed.
--}}
@if (($roles ?? collect())->isNotEmpty())
    <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <p class="text-sm text-amber-700 dark:text-amber-300">
            <x-user-text>{{ __('messages.team_access_blocked', ['schedules' => $roles->pluck('name')->join(', ')]) }}</x-user-text>
        </p>
    </div>
@endif
