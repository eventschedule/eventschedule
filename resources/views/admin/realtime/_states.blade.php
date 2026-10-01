{{-- Page-level states: tracking switched off, and polling stopped (re-auth lapsed, signed out). --}}
<div v-if="p.state === 'off'" class="ap-card rounded-xl p-8 sm:p-10 flex flex-col items-center text-center">
    <div class="dashboard-icon p-2 rounded-xl bg-gray-100 dark:bg-gray-700 mb-4">
        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" :d="icons.signal" />
        </svg>
    </div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@{{ msg.trackingOffTitle }}</h2>
    <p class="mt-2 max-w-xl text-sm text-gray-600 dark:text-gray-400">@{{ msg.trackingOffBody }}</p>
    {{-- The forward action last (end side in RTL). --}}
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        @if (! config('app.is_nexus') && $realtimeConfig['legalUrl'])
            <x-secondary-link :href="$realtimeConfig['legalUrl']">@{{ msg.mentionPolicy }}</x-secondary-link>
        @endif
        <x-brand-link :href="$realtimeConfig['settingsUrl']">@{{ msg.turnOn }}</x-brand-link>
    </div>
</div>

<div v-if="status === 'reauth' || status === 'stopped'"
     class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <div class="flex-1 min-w-0 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-amber-800 dark:text-amber-200">@{{ status === 'reauth' ? msg.reauth : (stopMessage || msg.stopped) }}</p>
            {{-- A plain anchor with x-brand-link's classes, not the component: it always renders a
                 static href, and Vue keeps the FIRST of two same-named attributes, so a bound one
                 would be dropped along with the filters it carries. --}}
            <a :href="pageHref"
               class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">@{{ status === 'reauth' ? msg.confirmPassword : msg.reload }}</a>
        </div>
    </div>
</div>
