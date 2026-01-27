{{-- Date Range Filter --}}
@props(['range' => 'last_30_days'])

<div class="flex flex-col sm:flex-row sm:justify-between gap-4">
    <div class="flex gap-2 flex-wrap items-center">
        <div class="min-w-[180px]">
            <select id="date-range" onchange="filterByDateRange(this.value)"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                <option value="last_7_days" {{ $range === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="last_30_days" {{ $range === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="last_90_days" {{ $range === 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                <option value="all_time" {{ $range === 'all_time' ? 'selected' : '' }}>All Time</option>
            </select>
        </div>
    </div>
</div>

<script {!! nonce_attr() !!}>
    function filterByDateRange(range) {
        const url = new URL(window.location.href);
        url.searchParams.set('range', range);
        window.location.href = url.toString();
    }
</script>
