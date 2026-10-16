{{-- One row above everything it narrows: the schedule, the event and the dates. Every figure,
     chart and list under it answers for the same slice (where one cannot, it says so: the cards
     marked "Whole schedule"). The Realtime tab is every page of the schedule over the last half
     hour, so it has neither an event to pick nor dates: only the schedule. --}}
@if ($roles->count() > 1 || $selectedRoleId || $tab !== 'realtime')
<div class="page-filters an-filters">
    @if ($roles->count() > 1)
    <div class="an-filter">
        <label for="role-filter" class="sr-only">{{ __('messages.schedule') }}</label>
        <select id="role-filter" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            <option value="">{{ __('messages.all_schedules') }}</option>
            @foreach ($roles as $role)
                <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}" {{ $selectedRoleId == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    @if ($selectedRoleId && $tab !== 'realtime')
    <div id="event-picker-app" class="an-filter is-event">
        <div class="relative" id="event-selector-dropdown">
            <select @mousedown.prevent="toggleDropdown" @keydown.space.prevent="toggleDropdown" @keydown.enter.prevent="toggleDropdown"
                aria-label="{{ __('messages.event') }}"
                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] cursor-pointer">
                <option v-pre>{{ $selectedEventName }}</option>
            </select>
            <div v-cloak v-if="dropdownOpen" class="absolute z-50 mt-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-96 overflow-y-auto" style="min-width: 300px">
                <div v-if="events.length > 0" class="sticky top-0 bg-white dark:bg-gray-900 p-2 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" ref="searchInput" v-model="searchQuery"
                        @keydown.down.prevent="moveHighlight(1)"
                        @keydown.up.prevent="moveHighlight(-1)"
                        @keydown.enter.prevent="selectHighlighted"
                        @keydown.escape.prevent="closeDropdown"
                        placeholder="{{ __('messages.search_events') }}"
                        class="w-full px-3 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]">
                </div>
                <button v-if="!searchQuery" @click="onEventChange('')"
                    @mouseenter="highlightedIndex = -1"
                    :data-event-idx="-1"
                    type="button"
                    class="w-full flex items-center gap-3 px-3 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="[
                        !selectedEventId ? 'bg-gray-50 dark:bg-gray-700/50' : '',
                        highlightedIndex === -1 ? 'bg-gray-100 dark:bg-gray-700' : '',
                    ]">
                    <span class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">{{ __('messages.all_events') }}</span>
                    </span>
                    <svg v-if="!selectedEventId" class="w-5 h-5 text-[var(--brand-blue)] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
                <button v-for="(event, idx) in filteredEvents" :key="event.id"
                    :data-event-idx="idx"
                    @click="onEventChange(event.id)"
                    @mouseenter="highlightedIndex = idx"
                    type="button"
                    class="w-full flex items-center gap-3 px-3 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    :class="[
                        event.id === selectedEventId ? 'bg-gray-50 dark:bg-gray-700/50' : '',
                        idx === highlightedIndex ? 'bg-gray-100 dark:bg-gray-700' : '',
                    ]">
                    <img v-if="event.image_url" :src="event.image_url" loading="lazy" class="w-10 h-10 rounded object-cover flex-shrink-0">
                    <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium" :title="event.name" v-html="highlightMatch(event.name)"></span>
                        <span v-if="event.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ event.starts_at }}</span>
                    </span>
                    <svg v-if="event.id === selectedEventId" class="w-5 h-5 text-[var(--brand-blue)] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
                <div v-if="events.length > 0 && filteredEvents.length === 0" class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                    {{ __('messages.no_events_found') }}
                </div>
            </div>
        </div>
    </div>
    @endif

    @if ($tab !== 'realtime')
    <div class="an-filter">
        <label for="date-range" class="sr-only">{{ __('messages.date') }}</label>
        <select id="date-range" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            @foreach (['last_7_days', 'last_30_days', 'last_90_days', 'this_month', 'last_month', 'this_year', 'all_time'] as $preset)
            <option value="{{ $preset }}" @selected($range === $preset)>{{ __('messages.'.$preset) }}</option>
            @endforeach
        </select>
    </div>
    @endif
</div>
@endif
