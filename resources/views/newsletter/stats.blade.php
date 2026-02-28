<x-app-admin-layout>
    <x-slot name="head">
        <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $newsletter->subject }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.sent') }}: {{ $newsletter->sent_at ? $newsletter->sent_at->format(get_use_24_hour_time($role) ? 'M j, Y H:i' : 'M j, Y g:i A') : '-' }}
                </p>
            </div>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.sent') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($newsletter->sent_count) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.opens') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($newsletter->open_count) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $newsletter->sent_count > 0 ? round(($newsletter->open_count / $newsletter->sent_count) * 100, 1) . '%' : '-' }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.clicks') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($newsletter->click_count) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $newsletter->sent_count > 0 ? round(($newsletter->click_count / $newsletter->sent_count) * 100, 1) . '%' : '-' }}
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.failed') }}</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($newsletter->recipients()->where('status', 'failed')->count()) }}</p>
            </div>
        </div>

        {{-- A/B Test Comparison --}}
        @if ($abTest && $abTest->newsletters->count() >= 2)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.ab_test_results') }}</h3>
            <div class="grid grid-cols-2 gap-6">
                @foreach ($abTest->newsletters->whereIn('ab_variant', ['A', 'B']) as $variant)
                <div class="p-4 rounded-lg {{ $abTest->winner_variant === $variant->ab_variant ? 'bg-green-50 dark:bg-green-900/20 border-2 border-green-500' : 'bg-gray-50 dark:bg-gray-700' }}">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-gray-900 dark:text-gray-100">{{ __('messages.variant') }} {{ $variant->ab_variant }}</h4>
                        @if ($abTest->winner_variant === $variant->ab_variant)
                        <span class="text-xs font-bold text-green-700 dark:text-green-300 uppercase">{{ __('messages.winner') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.subject') }}: {{ $variant->subject }}</p>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $variant->sent_count }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.sent') }}</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $variant->sent_count > 0 ? round(($variant->open_count / $variant->sent_count) * 100, 1) : 0 }}%</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.open_rate') }}</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $variant->sent_count > 0 ? round(($variant->click_count / $variant->sent_count) * 100, 1) : 0 }}%</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.click_rate') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.opens_over_time') }}</h3>
                <canvas id="openChart"></canvas>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.clicks_over_time') }}</h3>
                <canvas id="clickChart"></canvas>
            </div>
        </div>

        {{-- Top Clicked Links --}}
        @if ($topLinks->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.top_clicked_links') }}</h3>
            <div class="space-y-3">
                @foreach ($topLinks as $link)
                <div class="flex justify-between items-center">
                    <a href="{{ $link->url }}" target="_blank" class="text-sm text-[#4E81FA] hover:text-blue-700 truncate {{ is_rtl() ? 'ms-4' : 'me-4' }}" style="max-width: 80%;">{{ $link->url }}</a>
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">{{ $link->click_count }} {{ __('messages.clicks') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recipients Table --}}
        <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 px-6 py-4 border-b border-gray-200 dark:border-gray-700">{{ __('messages.recipients') }}</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('messages.email') }}</th>
                        <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('messages.status') }}</th>
                        <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('messages.opened') }}</th>
                        <th class="px-6 py-3 {{ is_rtl() ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('messages.clicked') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recipients as $recipient)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $recipient->email }}</td>
                        <td class="px-6 py-3 text-sm">
                            @php
                                $rStatusColors = [
                                    'sent' => 'text-green-600 dark:text-green-400',
                                    'pending' => 'text-yellow-600 dark:text-yellow-400',
                                    'failed' => 'text-red-600 dark:text-red-400',
                                    'bounced' => 'text-red-600 dark:text-red-400',
                                ];
                            @endphp
                            <span class="{{ $rStatusColors[$recipient->status] ?? 'text-gray-500' }}">{{ $recipient->status }}</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $recipient->opened_at ? $recipient->opened_at->format(get_use_24_hour_time($role) ? 'M j, H:i' : 'M j, g:i A') : '-' }}
                            @if ($recipient->open_count > 1)
                            <span class="text-xs text-gray-400">({{ $recipient->open_count }}x)</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $recipient->clicked_at ? $recipient->clicked_at->format(get_use_24_hour_time($role) ? 'M j, H:i' : 'M j, g:i A') : '-' }}
                            @if ($recipient->click_count > 1)
                            <span class="text-xs text-gray-400">({{ $recipient->click_count }}x)</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('messages.no_recipients') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-3">
                {{ $recipients->links() }}
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        function initCharts() {
            const isDarkMode = document.documentElement.classList.contains('dark') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !document.documentElement.classList.contains('light'));

            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#2d2d30' : '#E5E7EB';

            const openData = @json($openTimeline);
            const clickData = @json($clickTimeline);

            // Opens chart
            const openCtx = document.getElementById('openChart').getContext('2d');
            new Chart(openCtx, {
                type: 'line',
                data: {
                    labels: openData.map(d => d.date),
                    datasets: [{
                        label: @json(__('messages.opens')),
                        data: openData.map(d => d.count),
                        borderColor: '#4E81FA',
                        backgroundColor: 'rgba(78, 129, 250, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, precision: 0 }, grid: { color: gridColor }, beginAtZero: true },
                    }
                }
            });

            // Clicks chart
            const clickCtx = document.getElementById('clickChart').getContext('2d');
            new Chart(clickCtx, {
                type: 'line',
                data: {
                    labels: clickData.map(d => d.date),
                    datasets: [{
                        label: @json(__('messages.clicks')),
                        data: clickData.map(d => d.count),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: textColor }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, precision: 0 }, grid: { color: gridColor }, beginAtZero: true },
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>
</x-app-admin-layout>
