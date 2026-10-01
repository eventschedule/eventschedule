{{-- Status, the Show admins switch, and the active filter chips. --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
        <span class="relative inline-flex w-2 h-2" aria-hidden="true">
            {{-- One ping per successful poll, never a looping pulse: a page left open all day
                 should not read as an alert in the corner of the eye. --}}
            <span v-if="status === 'live' && pinging" class="absolute inset-0 rounded-full bg-green-500 opacity-75 motion-safe:animate-ping"></span>
            <span class="relative inline-flex w-2 h-2 rounded-full"
                  :class="status === 'live' ? 'bg-green-500' : (status === 'reconnecting' ? 'bg-amber-500' : 'bg-gray-400')"></span>
        </span>
        <span role="status" aria-live="polite"
              :class="status === 'reconnecting' ? 'text-amber-700 dark:text-amber-400' : ''">@{{ statusWord }}</span>
        <span v-if="status === 'reconnecting'" class="text-amber-700 dark:text-amber-400">@{{ lastUpdatedText }}</span>
    </div>

    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
        <span class="text-sm text-gray-700 dark:text-gray-300">@{{ msg.showAdmins }}</span>
        <button type="button" role="switch" :aria-checked="admins ? 'true' : 'false'" @click="toggleAdmins"
                class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                :class="admins ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700'">
            <span class="sr-only">@{{ msg.showAdmins }}</span>
            <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition-all duration-200"
                  :class="admins ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
        </button>
    </label>
</div>

<div v-if="filterChips.length" class="flex flex-wrap items-center gap-2">
    <span v-for="chip in filterChips" :key="chip.key"
          class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700 ps-3 pe-1 py-1 text-sm">
        <span class="text-gray-500 dark:text-gray-400">@{{ chip.type }}</span>
        <bdi class="font-semibold text-gray-900 dark:text-white">@{{ chip.label }}</bdi>
        <button type="button" @click="clearFilter(chip.key)" :aria-label="fill(msg.removeFilter, chip.type + ' ' + chip.label)"
                class="inline-flex items-center justify-center w-6 h-6 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:text-white dark:hover:bg-gray-600 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </span>
    <button type="button" @click="clearFilters"
            class="ap-secondary-btn inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
        @{{ msg.clearFilters }}
    </button>
</div>
