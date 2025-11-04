@php
    $classes = $classes ?? 'py-6 text-center text-sm text-gray-500 dark:text-gray-400';
    $linkClasses = $linkClasses ?? 'hover:underline font-medium';
    $versionLinkClasses = $versionLinkClasses ?? $linkClasses;
    $link = '<a href="https://www.eventschedule.com" target="_blank" rel="noopener" class="' . $linkClasses . '">EventSchedule</a>';
@endphp
<div class="{{ $classes }}">
    <span class="inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
        <span>{!! str_replace(':link', $link, __('messages.powered_by_eventschedule')) !!}</span>
        @unless (config('app.hosted'))
            <span aria-hidden="true">•</span>
            <a href="{{ config('self-update.repository_types.github.repository_url') }}" target="_blank"
                rel="noopener" class="{{ $versionLinkClasses }}">
                {{ config('self-update.version_installed') }}
            </a>
        @endunless
    </span>
</div>
