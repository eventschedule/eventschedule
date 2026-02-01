<div class="mb-4">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" x-model="useAllEvents"
            class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.all_upcoming_events') }}</span>
    </label>
    <p class="text-sm text-gray-500 dark:text-gray-400 {{ is_rtl() ? 'me-6' : 'ms-6' }}">{{ __('messages.auto_populate_upcoming') }}</p>
</div>

<div x-show="!useAllEvents" x-cloak>
    @if ($events->count())
    <div class="space-y-2">
        @foreach ($events as $event)
        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg"
            :class="selectedEventIds.includes({{ $event->id }}) ? 'bg-blue-50 dark:bg-blue-900/20 border-[#4E81FA]' : ''">
            <label class="flex items-center gap-3 cursor-pointer flex-1">
                <input type="checkbox"
                    :checked="selectedEventIds.includes({{ $event->id }})"
                    @change="toggleEvent({{ $event->id }})"
                    class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
                <div>
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 {{ is_rtl() ? 'me-2' : 'ms-2' }}">
                        {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->format('M j, Y') : '' }}
                    </span>
                </div>
            </label>
            <div class="flex gap-1" x-show="selectedEventIds.includes({{ $event->id }})">
                <button type="button" @click="moveEvent(selectedEventIds.indexOf({{ $event->id }}), -1)"
                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Move up">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" @click="moveEvent(selectedEventIds.indexOf({{ $event->id }}), 1)"
                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" title="Move down">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_upcoming_events') }}</p>
    @endif
</div>

{{-- Hidden inputs for event_ids --}}
<template x-if="useAllEvents">
    <input type="hidden" name="event_ids" value="" />
</template>
<template x-if="!useAllEvents">
    <template x-for="eventId in selectedEventIds" :key="eventId">
        <input type="hidden" name="event_ids[]" :value="eventId" />
    </template>
</template>
