{{--
    Calendar deletion policy control (When an event is deleted in your calendar: keep / hide / delete).

    Shared by the Google and Outlook integration tabs. $provider ('google' | 'microsoft') namespaces
    the container/warning ids for the two copies; the radios deliberately share
    name="calendar_delete_action" so they act as ONE group and the main role form persists the value
    (the column is fillable). A small script (further down edit.blade.php) keeps the two copies in
    visual sync, toggles the warning, and shows each copy only when its provider syncs inbound.
--}}
@php($provider = $provider ?? 'google')
@php($inbound = $provider === 'microsoft' ? $role->syncsFromMicrosoft() : $role->syncsFromGoogle())

<div id="{{ $provider }}-delete-action-group" data-delete-action-group class="mt-6 {{ $inbound ? '' : 'hidden' }}">
    <x-input-label :value="__('messages.calendar_delete_action')" />
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.calendar_delete_action_help') }}</p>

    <div class="mt-2 space-y-2">
        @foreach ([
            'ignore' => ['calendar_delete_keep', 'calendar_delete_keep_desc'],
            'cancel' => ['calendar_delete_hide', 'calendar_delete_hide_desc'],
            'delete' => ['calendar_delete_remove', 'calendar_delete_remove_desc'],
        ] as $value => $keys)
            <label class="flex items-center">
                <input type="radio"
                       name="calendar_delete_action"
                       value="{{ $value }}"
                       {{ $role->calendarDeleteAction() === $value ? 'checked' : '' }}
                       class="border-gray-300 dark:border-gray-700 focus:ring-[var(--brand-blue)] h-4 w-4">
                <div class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                    <div class="font-medium">{{ __('messages.'.$keys[0]) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.'.$keys[1]) }}</div>
                </div>
            </label>
        @endforeach
    </div>

    <div data-delete-action-warning
         class="mt-3 flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg {{ $role->calendarDeleteAction() === 'delete' ? '' : 'hidden' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.calendar_delete_warning') }}</p>
    </div>
</div>
