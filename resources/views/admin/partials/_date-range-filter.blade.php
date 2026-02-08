{{-- Date Range Filter --}}
@props(['range' => 'last_30_days'])

<div class="flex flex-col sm:flex-row sm:justify-between gap-4">
    <div class="flex gap-2 flex-wrap items-center">
        <div class="min-w-[180px]">
            <select id="date-range"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                <option value="last_7_days" {{ $range === 'last_7_days' ? 'selected' : '' }}>@lang('messages.last_7_days')</option>
                <option value="last_30_days" {{ $range === 'last_30_days' ? 'selected' : '' }}>@lang('messages.last_30_days')</option>
                <option value="last_90_days" {{ $range === 'last_90_days' ? 'selected' : '' }}>@lang('messages.last_90_days')</option>
                <option value="all_time" {{ $range === 'all_time' ? 'selected' : '' }}>@lang('messages.all_time')</option>
            </select>
        </div>
    </div>
</div>

<script {!! nonce_attr() !!}>
    document.getElementById('date-range').addEventListener('change', function() {
        var url = new URL(window.location.href);
        url.searchParams.set('range', this.value);
        window.location.href = url.toString();
    });
</script>
