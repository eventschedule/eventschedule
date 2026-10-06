{{-- The calm reading of what Needs attention raises when something is wrong: the queue, custom
     domains on hosted, and how many accounts there are. One line, each number beside its label. --}}
@if ($system)
    <p class="flex flex-wrap gap-x-6 gap-y-1 px-1 text-xs text-gray-500 dark:text-gray-400">
        <span class="whitespace-nowrap">
            @lang('messages.queue')
            <x-link :href="route('admin.queue')" class="text-gray-700 dark:text-gray-300">{{ mb_strtolower(__('messages.waiting')) }} {{ number_format($system['jobs_waiting']) }} &middot; {{ mb_strtolower(__('messages.failed')) }} {{ number_format($system['jobs_failed']) }}</x-link>
        </span>
        @if ($system['domains'] && \Illuminate\Support\Facades\Route::has('admin.domains'))
            <span class="whitespace-nowrap">
                @lang('messages.domains')
                <x-link :href="route('admin.domains')" class="text-gray-700 dark:text-gray-300">{{ number_format($system['domains']['total']) }} &middot; {{ mb_strtolower(__('messages.pending')) }} {{ number_format($system['domains']['pending']) }}</x-link>
            </span>
        @endif
        <span class="whitespace-nowrap">@lang('messages.admin_dash_accounts_total') {{ number_format($system['accounts']) }}</span>
    </p>
@endif
