{{-- Tracking switched off: what it is and how to turn it on, in the Now card's place. The first of
     three siblings in realtime-top (v-if, then the loading card and the Now card). --}}
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
