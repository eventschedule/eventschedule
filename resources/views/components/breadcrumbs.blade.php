@props([
    'items' => [],
    'ariaLabel' => __('Breadcrumb'),
])

@php
    $items = collect($items)
        ->map(function ($item) {
            return is_array($item) ? $item : [];
        })
        ->filter(function ($item) {
            return filled($item['label'] ?? null);
        })
        ->values();
@endphp

@if ($items->isNotEmpty())
    <nav aria-label="{{ $ariaLabel }}" {{ $attributes->merge(['class' => 'flex text-sm text-gray-500 dark:text-gray-400']) }}>
        <ol role="list" class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-2">
                    @if ($index > 0)
                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L9.586 11 7.293 8.707a1 1 0 111.414-1.414l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    @endif

                    @php
                        $isCurrent = (bool) ($item['current'] ?? false);
                        $label = $item['label'];
                        $url = $item['url'] ?? null;
                    @endphp

                    @if ($url && ! $isCurrent)
                        <a
                            href="{{ $url }}"
                            class="truncate font-medium text-gray-500 transition hover:text-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            {{ $label }}
                        </a>
                    @else
                        <span
                            class="truncate font-medium text-gray-700 dark:text-gray-200"
                            @if ($isCurrent) aria-current="page" @endif
                        >
                            {{ $label }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
