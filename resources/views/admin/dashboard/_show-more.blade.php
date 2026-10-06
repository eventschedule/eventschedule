{{-- Opens the rest of BOTH lists: they sit side by side, and one card growing on its own would
     stretch the other over empty space. The page's script does the work; without it the first
     rows are simply what there is. --}}
@if ($count > \App\Services\AdminDashboard::LIST_VISIBLE)
    <button type="button" data-show-more aria-expanded="false"
        data-more="{{ __('messages.show_more') }}" data-less="{{ __('messages.show_less') }}"
        class="inline-flex items-center gap-1 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
        <span data-show-more-label>@lang('messages.show_more')</span>
        <svg class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>
@else
    <span></span>
@endif
