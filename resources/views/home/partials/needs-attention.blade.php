@php
    // Aggregated to-do rows from HomeController::getPendingActionItems(). Only rendered
    // when there is something pending (guarded by the caller).
    $total = $pendingActionItems->sum('count');
    $visibleItems = $pendingActionItems->take(8);
    $hiddenItems = $pendingActionItems->slice(8);

    $styles = [
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-500/10', 'text' => 'text-blue-600 dark:text-blue-400'],
        'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-500/10', 'text' => 'text-purple-600 dark:text-purple-400'],
        'green' => ['bg' => 'bg-green-50 dark:bg-green-500/10', 'text' => 'text-green-600 dark:text-green-400'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10', 'text' => 'text-amber-600 dark:text-amber-400'],
    ];

    // Outline (stroke) icons per type, matching the AP design system.
    $icons = [
        'requests' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />',
        'fan_content' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />',
        'polls' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
        'carpool' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
    ];
@endphp

<div class="ap-card rounded-xl overflow-hidden mb-4">
    <div class="dashboard-panel-header px-5 py-4 flex items-center justify-between">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.needs_attention') }}</h3>
        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 text-xs font-semibold rounded-full bg-[var(--brand-blue)] text-white">{{ number_format($total) }}</span>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06]">
        @foreach ($visibleItems as $item)
            @include('home.partials.needs-attention-row', ['item' => $item])
        @endforeach

        @if ($hiddenItems->isNotEmpty())
            <details class="group divide-y divide-gray-100 dark:divide-white/[0.06]">
                <summary class="flex items-center justify-center gap-1.5 px-5 py-3 cursor-pointer select-none list-none text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 [&::-webkit-details-marker]:hidden">
                    <span>{{ __('messages.pending_action_show_more', ['count' => $hiddenItems->count()]) }}</span>
                    <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </summary>
                @foreach ($hiddenItems as $item)
                    @include('home.partials.needs-attention-row', ['item' => $item])
                @endforeach
            </details>
        @endif
    </div>
</div>
