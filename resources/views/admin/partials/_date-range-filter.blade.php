{{-- The period an admin page reports on. It sits in the page's .page-actions, at the end of the
     line that says what the page shows, and reloads the page with ?range= when it changes. It
     used to be a row of its own with nothing else on it. --}}
@props(['range' => 'last_30_days'])

<label for="date-range" class="sr-only">{{ __('messages.panel_period') }}</label>
<select id="date-range" autocomplete="off"
    class="block w-full sm:w-auto min-w-[180px] rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-base">
    <option value="last_7_days" {{ $range === 'last_7_days' ? 'selected' : '' }}>@lang('messages.last_7_days')</option>
    <option value="last_30_days" {{ $range === 'last_30_days' ? 'selected' : '' }}>@lang('messages.last_30_days')</option>
    <option value="last_90_days" {{ $range === 'last_90_days' ? 'selected' : '' }}>@lang('messages.last_90_days')</option>
    <option value="all_time" {{ $range === 'all_time' ? 'selected' : '' }}>@lang('messages.all_time')</option>
</select>

<script {!! nonce_attr() !!}>
    document.getElementById('date-range').addEventListener('change', function() {
        var url = new URL(window.location.href);
        url.searchParams.set('range', this.value);
        window.location.href = url.toString();
    });
</script>
