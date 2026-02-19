<a href="{{ route('boost.show', ['hash' => $campaign->hashedId()]) }}"
   class="block bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
    <div class="p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $campaign->event?->translatedName() ?? __('messages.deleted_event') }}</h3>
            @php
                $statusColors = [
                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'pending_payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                    'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    'paused' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                    'completed' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'cancelled' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                ];
            @endphp
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$campaign->status] ?? $statusColors['draft'] }}">
                {{ __('messages.boost_status_' . $campaign->status) }}
            </span>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $campaign->role->name }}</p>

        <div class="grid grid-cols-3 gap-3 text-center">
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($campaign->impressions) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.impressions') }}</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($campaign->clicks) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.clicks') }}</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.spent') }}</p>
            </div>
        </div>

        @if ($campaign->isActive() || $campaign->isPaused())
        <div class="mt-3">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $campaign->getBudgetUtilization() }}%"></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend ?? 0, 2) }} / {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }}</p>
        </div>
        @endif
    </div>
</a>
