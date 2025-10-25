@php
    $classes = $classes ?? 'py-6 text-center text-sm text-gray-500 dark:text-gray-400';
    $linkClasses = $linkClasses ?? 'hover:underline font-medium';
    $link = '<a href="https://www.eventschedule.com" target="_blank" rel="noopener" class="' . $linkClasses . '">EventSchedule</a>';
@endphp
<div class="{{ $classes }}">
    {!! str_replace(':link', $link, __('messages.powered_by_eventschedule')) !!}
</div>
