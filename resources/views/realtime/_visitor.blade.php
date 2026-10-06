{{-- One visitor, inside a v-for over `v`: a country, a device type, the page and how long. That is
     the whole row, by design (App\Services\ScheduleRealtime::visitorRow()). Nothing here opens, and
     nothing names anyone.

     $now is a Blade flag, decided where the partial is included: a row under "Right now" counts
     time on the page, a row under "Earlier" says when they left. --}}
<span class="flex items-center gap-3 min-w-0">
    <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700" aria-hidden="true">
        <span v-if="v.country" class="iti__flag" :class="'iti__' + v.country.toLowerCase()"></span>
    </span>
    <span class="min-w-0">
        <span class="flex items-center gap-1.5 min-w-0 text-sm font-medium {{ $now ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
            <span class="truncate">@{{ countryName(v.country) }}</span>
            <svg class="md:hidden w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" role="img" :aria-label="msg.device[v.device]"><path stroke-linecap="round" stroke-linejoin="round" :d="deviceIcons[v.device] || deviceIcons.other" /></svg>
        </span>
        <span class="hidden md:flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="deviceIcons[v.device] || deviceIcons.other" /></svg>
            @{{ msg.device[v.device] }}
        </span>
        <span class="md:hidden block truncate text-xs text-gray-600 dark:text-gray-300 mt-0.5">@{{ v.page }}</span>
        <span class="md:hidden block truncate text-xs text-gray-500 dark:text-gray-400">@{{ kindLine(v) }}</span>
    </span>
</span>
<span class="hidden md:block min-w-0">
    <span class="block truncate text-sm {{ $now ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">@{{ v.page }}</span>
    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">@{{ kindLine(v) }}</span>
</span>
<span class="text-end">
    @if ($now)
        <span class="inline-flex items-center gap-1.5 text-sm font-medium tabular-nums text-gray-900 dark:text-white">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>@{{ duration(v.seconds) }}
        </span>
    @else
        <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">@{{ leftAgo(v.left_ago) }}</span>
    @endif
</span>
