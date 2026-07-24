{{--
    Compact header: a slim full-width row at the top of the page (small avatar +
    name only). Action cluster on the right; the schedule's description and social/contact
    links render beneath (below-bar partial). Expects from the parent scope: $role,
    $accentColor, $contrastColor, $isRtl, $event.
--}}
@php
    $minName = $role->translatedName();
    $eventLayout = $role->event_layout ?? 'calendar';
@endphp
<div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm w-full border-b border-gray-200 dark:border-gray-700 shadow-md {{ $isRtl ? 'rtl' : '' }}"
     dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="container mx-auto px-5">
    <header id="schedule-header" class="relative z-10 flex flex-col gap-3 py-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Left: small avatar + name --}}
        <div class="flex items-center gap-3 min-w-0">
            @if ($role->profile_image_url)
            <img class="flex-shrink-0 w-10 h-10 rounded-lg object-cover bg-white dark:bg-gray-800" src="{{ $role->profile_image_url }}" alt="{{ $minName }}">
            @endif
            <h1 class="text-xl md:text-2xl font-semibold leading-tight text-[#151B26] dark:text-gray-100 truncate" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
                <x-user-text>{{ $minName }}</x-user-text>
            </h1>
        </div>

        {{-- Right: action cluster --}}
        <div class="flex flex-row flex-wrap items-center gap-2 md:gap-3 {{ $isRtl ? 'lg:justify-start' : 'lg:justify-end' }}">
            @include('role.partials.headers.action-buttons', ['onDark' => false, 'accentColor' => $accentColor, 'contrastColor' => $contrastColor])
            @include('role.partials.headers.lang-toggle-inline', ['onDark' => false])

            {{-- Filters Button (visibility controlled by JS watcher in calendar.blade.php) --}}
            @if(!$event)
            <button id="hero-filters-btn"
                    aria-label="{{ $role->customLabel('filters') }}" title="{{ $role->customLabel('filters') }}"
                    data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                    class="hidden w-11 h-11 items-center justify-center rounded-lg border-2 transition-all duration-200 hover:scale-105 hover:shadow-md flex-shrink-0 relative"
                    style="border-color: {{ $accentColor }}; background-color: transparent; color: inherit; display: none;">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                </svg>
                <span id="hero-filters-badge" class="absolute -top-1 -end-1 min-w-[18px] h-[18px] items-center justify-center text-xs bg-[var(--brand-button-bg)] text-white rounded-full px-1 hidden"></span>
            </button>
            @endif

            {{-- Calendar/List View Toggle (desktop only) --}}
            @if(!$event)
            <div class="hidden md:flex items-center rounded-md shadow-sm flex-shrink-0">
                <button id="toggle-list-btn"
                        data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                        class="w-11 h-11 flex items-center justify-center rounded-s-md border-2 transition-all duration-200 {{ $eventLayout !== 'list' ? 'hover:scale-105 hover:shadow-md' : 'text-gray-900 dark:text-white' }}"
                        style="border-color: {{ $accentColor }}; {{ $eventLayout !== 'list' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3,4H7V8H3V4M9,5V7H21V5H9M3,10H7V14H3V10M9,11V13H21V11H9M3,16H7V20H3V16M9,17V19H21V17H9"/></svg>
                </button>
                <button id="toggle-calendar-btn"
                        data-accent="{{ $accentColor }}" data-contrast="{{ $contrastColor }}"
                        class="w-11 h-11 flex items-center justify-center rounded-e-md border-2 border-s-0 transition-all duration-200 {{ $eventLayout !== 'calendar' ? 'hover:scale-105 hover:shadow-md' : 'text-gray-900 dark:text-white' }}"
                        style="border-color: {{ $accentColor }}; {{ $eventLayout !== 'calendar' ? 'background-color: ' . $accentColor . '; color: ' . $contrastColor : '' }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/></svg>
                </button>
            </div>
            @endif
        </div>
    </header>
    @include('role.partials.headers.below-bar', ['onDark' => false])
    </div>
</div>
