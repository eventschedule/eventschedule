@props(['column', 'sortBy' => '', 'sortDir' => 'asc', 'class' => 'px-3 py-3.5'])

<th scope="col"
    class="{{ $class }} text-start text-sm font-semibold text-gray-900 dark:text-gray-100 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 select-none"
    data-sort="{{ $column }}">
    <div class="flex items-center gap-1">
        {{ $slot }}
        @if($sortBy === $column)
            @if($sortDir === 'asc')
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            @else
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            @endif
        @endif
    </div>
</th>
