@php
    $title = $title ?? null;
    $html = $html ?? null;
    $imageUrl = $imageUrl ?? null;
    $imageAlt = $imageAlt ?? '';
    $classes = trim('bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden');
@endphp

<div class="{{ $classes }}">
    @if($imageUrl)
        <div class="relative aspect-[4/3] bg-gray-100 dark:bg-gray-800">
            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" class="h-full w-full object-cover">
        </div>
    @endif
    <div class="p-6 sm:p-8">
        @if($title)
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h2>
        @endif
        @if($html)
            <div class="mt-4 text-gray-600 dark:text-gray-300 leading-relaxed space-y-4 [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-indigo-600 [&_a]:underline [&_strong]:text-gray-900 dark:[&_strong]:text-gray-100">
                {!! $html !!}
            </div>
        @endif
    </div>
</div>
