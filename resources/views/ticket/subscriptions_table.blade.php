@php
    $subscriptions = collect($subscriptions ?? []);
    $totalVisits = $subscriptions->sum(fn ($s) => count($s['usages']));
    $statusStyles = [
        'active' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        'expired' => 'bg-gray-100 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300',
        'used_up' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    ];
    $statusLabels = [
        'active' => __('messages.subscription_active'),
        'expired' => __('messages.subscription_expired'),
        'used_up' => __('messages.subscription_used_up'),
    ];
@endphp

<div class="ap-card rounded-xl overflow-hidden">
    <div class="flex flex-wrap items-center gap-x-6 gap-y-1 p-4 border-b border-gray-200 dark:border-[#2d2d30] text-sm">
        <span class="text-gray-700 dark:text-gray-300"><span class="font-semibold">{{ $subscriptions->count() }}</span> {{ __('messages.subscriptions') }}</span>
        <span class="text-gray-700 dark:text-gray-300"><span class="font-semibold">{{ $totalVisits }}</span> {{ __('messages.visits_redeemed') }}</span>
    </div>

    <div class="divide-y divide-gray-200 dark:divide-[#2d2d30]">
        @foreach ($subscriptions as $sub)
            <details class="group">
                <summary class="flex items-center gap-4 p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-[#252526] transition-colors list-none">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $sub['name'] }}</div>
                        <a href="mailto:{{ $sub['email'] }}" class="text-xs text-gray-500 dark:text-gray-400 truncate hover:underline">{{ $sub['email'] }}</a>
                    </div>
                    <div class="hidden sm:block text-sm text-gray-600 dark:text-gray-300 w-40 truncate">{{ $sub['ticket_type'] }}</div>
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200 w-20 text-end">{{ $sub['limit_label'] }}</div>
                    <div class="hidden md:block text-sm text-gray-500 dark:text-gray-400 w-28 text-end">{{ $sub['expires_at'] ?? '—' }}</div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusStyles[$sub['status']] ?? '' }}">{{ $statusLabels[$sub['status']] ?? $sub['status'] }}</span>
                </summary>

                <div class="px-4 pb-4 ps-12">
                    @if (count($sub['usages']) > 0)
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    <th class="text-start font-medium pb-1">{{ __('messages.event') }}</th>
                                    <th class="text-start font-medium pb-1">{{ __('messages.date') }}</th>
                                    <th class="text-start font-medium pb-1">{{ __('messages.time') }}</th>
                                    <th class="text-start font-medium pb-1">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 dark:text-gray-300">
                                @foreach ($sub['usages'] as $usage)
                                    <tr class="border-t border-gray-100 dark:border-[#2d2d30]">
                                        <td class="py-1.5 pe-4">{{ $usage['event'] }}</td>
                                        <td class="py-1.5 pe-4 whitespace-nowrap">{{ $usage['date'] }}</td>
                                        <td class="py-1.5 pe-4 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $usage['time'] }}</td>
                                        <td class="py-1.5 whitespace-nowrap">
                                            @if (($usage['kind'] ?? 'attended') === 'booked')
                                                <span class="inline-block rounded-full bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 text-xs font-medium text-[var(--brand-blue)]">{{ __('messages.booked') }}</span>
                                            @else
                                                <span class="inline-block rounded-full bg-green-50 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-400">{{ __('messages.attended') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_visits_yet') }}</p>
                    @endif

                    <div class="mt-3">
                        <a href="{{ $sub['ticket_url'] }}" target="_blank" class="text-sm text-[var(--brand-blue)] hover:underline">{{ __('messages.view_ticket') }}</a>
                    </div>
                </div>
            </details>
        @endforeach
    </div>
</div>
