{{-- A card whose query failed. AdminDashboard::build() reported it; the rest of the page stands.
     The AP's warning panel: tinted, bordered all round, with the triangle. --}}
<div class="mx-4 sm:mx-5 mb-5 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>@lang('messages.admin_dash_card_failed')</span>
    </p>
</div>
