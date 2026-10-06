{{-- A tab's header on a phone, where the tabs are an accordion: the same icon, name and line of
     summary the sidebar shows. $settingsSection is the tab's id (profile/edit, $settingsTabs). --}}
@php
    $header = $settingsTabs[$settingsSection];
    $headerSummary = $settingsTabSummaries[$settingsSection];
@endphp
<button type="button" class="mobile-section-header" data-section="{{ $settingsSection }}">
    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 shrink-0" aria-hidden="true">
            @foreach ($header['icon'] as $iconPath)
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            @endforeach
        </svg>
        <span class="section-nav-text">
            <span>{{ $header['label'] }}</span>
            <span class="section-nav-summary {{ $headerSummary['empty'] ? 'is-empty' : '' }}"><bdi>{{ $headerSummary['text'] }}</bdi></span>
        </span>
        <span class="section-nav-dot" data-dirty-dot="{{ $settingsSection }}" hidden></span>
    </span>
    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
</button>
