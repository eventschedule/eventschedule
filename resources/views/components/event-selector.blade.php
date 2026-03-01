<div class="relative" id="event-selector-dropdown">
    <button @click="toggleDropdown" type="button" tabindex="0"
        class="w-full flex items-center gap-3 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 shadow-sm focus:border-[#4E81FA] focus:ring-2 focus:ring-[#4E81FA] focus:outline-none text-start">
        <template v-if="selectedEvent">
            <img v-if="selectedEvent.image_url" :src="selectedEvent.image_url" class="w-10 h-10 rounded object-cover flex-shrink-0">
            <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">@{{ selectedEvent.name }}</span>
                <span v-if="selectedEvent.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ selectedEvent.starts_at }}</span>
            </span>
        </template>
        <span v-else class="flex-1 text-gray-500 dark:text-gray-400 text-sm">{{ $placeholder ?? __('messages.select_event') }}</span>
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 ms-auto" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4" />
        </svg>
    </button>
    <div v-if="dropdownOpen" class="absolute z-50 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-lg max-h-72 overflow-y-auto">
        <button v-for="event in events" :key="event.id" @click="onEventChange(event.id)" type="button"
            class="w-full flex items-center gap-3 px-3 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            :class="event.id === selectedEventId ? 'bg-gray-50 dark:bg-gray-600/50' : ''">
            <img v-if="event.image_url" :src="event.image_url" class="w-10 h-10 rounded object-cover flex-shrink-0">
            <span v-else class="w-10 h-10 rounded bg-gray-100 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <span class="flex-1 min-w-0">
                <span class="block truncate text-gray-900 dark:text-gray-100 text-sm font-medium">@{{ event.name }}</span>
                <span v-if="event.starts_at" class="block truncate text-gray-500 dark:text-gray-400 text-xs">@{{ event.starts_at }}</span>
            </span>
            <svg v-if="event.id === selectedEventId" class="w-5 h-5 text-[#4E81FA] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </button>
    </div>
</div>
