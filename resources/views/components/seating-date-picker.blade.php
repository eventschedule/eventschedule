@props(['action', 'dates' => [], 'current' => null, 'label' => null, 'view' => null])

{{-- Which night of the run this screen is showing.

     The box office, the printed report and the one-date designer are each keyed to a single
     occurrence and none of them had any way to reach a second one - with no ?date= they resolve to
     the series anchor, so a thirty-night run only ever exposed its first night.

     A GET form rather than JS: the whole screen changes with the date (a different map, different
     props, a different report), so this is a navigation. `data-auto-submit` is the app-wide
     delegated change handler in layouts/app.blade.php, which keeps this free of an inline handler
     the CSP would block. A browser discards the action's own query string on a GET submit, so
     `action` must be the bare route and `date` comes from the select. --}}
@if (count($dates) > 1)
<form method="GET" action="{{ $action }}" class="flex items-center gap-2">
    {{-- A GET submit discards whatever query string the action carried, so anything that must
         survive changing night has to be a field. The report's view is the only one so far. --}}
    @if ($view)
        <input type="hidden" name="view" value="{{ $view }}">
    @endif
    <label for="seating-date" class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">
        {{ $label ?: __('messages.date') }}
    </label>
    <select id="seating-date" name="date" data-auto-submit
        class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm">
        @foreach ($dates as $option)
            <option value="{{ $option }}" @selected($option === $current)>
                {{ \Carbon\Carbon::parse($option)->translatedFormat('D, M j, Y') }}
            </option>
        @endforeach
    </select>
    {{-- The delegated handler needs no fallback, but a keyboard user who changes the value with
         the select closed still wants a way to commit it. --}}
    <noscript><button type="submit" class="text-sm text-[var(--brand-blue)] hover:underline">{{ __('messages.view') }}</button></noscript>
</form>
@endif
