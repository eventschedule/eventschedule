@php
    $wrapperClass = $wrapperClass ?? '';
@endphp

<div class="inline-flex rounded-full border border-gray-200 bg-gray-100 p-0.5 text-sm font-medium dark:border-gray-700 dark:bg-gray-800 {{ $wrapperClass }}">
    <a href="{{ route($calendarRouteName, $listViewParams) }}"
       class="@class([
           'inline-flex items-center gap-1 rounded-full px-3 py-1.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
           'bg-white text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700' => $isListView,
           'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' => ! $isListView,
       ])"
       @if($isListView) aria-current="page" @endif
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M4 6h.01M7 12h13M4 12h.01M7 18h13M4 18h.01" />
        </svg>
        {{ __('messages.list') }}
    </a>
    <a href="{{ route($calendarRouteName, $calendarViewParams) }}"
       class="@class([
           'inline-flex items-center gap-1 rounded-full px-3 py-1.5 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
           'bg-white text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 dark:bg-gray-900 dark:text-gray-100 dark:ring-gray-700' => ! $isListView,
           'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white' => $isListView,
       ])"
       @unless($isListView) aria-current="page" @endunless
    >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25C3 7.007 3.993 6 5.25 6h13.5C19.507 6 20.5 7.007 20.5 8.25v9.5A1.25 1.25 0 0119.25 19H4.75A1.75 1.75 0 013 17.25v-9z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4.75A1.75 1.75 0 019.75 3h4.5A1.75 1.75 0 0116 4.75V6" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5h17.5" />
        </svg>
        {{ __('messages.calendar') }}
    </a>
</div>
