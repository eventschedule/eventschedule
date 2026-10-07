@props([
    'column',
    'sortBy' => '',
    'sortDir' => 'asc',
])

{{-- A column heading the list can be sorted by (partials/admin-page-styles, .page-sort): a real
     button, so a keyboard reaches it, carrying data-sort for the page's own script to read, and
     aria-sort on the cell so a screen reader hears which way the list runs. The old
     <x-sortable-header> was a table cell with a click handler. --}}
@php $sorted = $sortBy === $column; @endphp
<th scope="col" {{ $attributes }} @if ($sorted) aria-sort="{{ $sortDir === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <button type="button" class="page-sort" data-sort="{{ $column }}">
        {{ $slot }}
        @if ($sorted)
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDir === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}" />
        </svg>
        @endif
    </button>
</th>
