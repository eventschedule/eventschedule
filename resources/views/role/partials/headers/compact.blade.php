{{--
    Compact header: a slim full-width row at the top of the page (small avatar +
    name only). Action cluster on the right; the schedule's description and social/contact
    links render beneath (below-bar partial). Expects from the parent scope: $role,
    $accentColor, $contrastColor, $isRtl, $event, $headActions.
--}}
@php
    $minName = $role->translatedName();
@endphp
{{-- relative z-20 puts the bar on the same layer as the announcement bar (guest-banner), above
     the content container's z-10 stacking context - otherwise the container paints over the bar's
     shadow, and any negative offset inside it paints over the bar itself. --}}
<div id="gp-header" class="relative z-20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm w-full border-b border-gray-200 dark:border-gray-700 shadow-md {{ $isRtl ? 'rtl' : '' }}"
     dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="container mx-auto px-5">
    <header id="schedule-header" class="relative z-10 flex flex-col gap-3 py-4 lg:flex-row lg:items-center lg:justify-between">
        {{-- Left: small avatar + name --}}
        <div class="flex items-center gap-3 min-w-0">
            @if ($role->profile_image_url)
            <img class="flex-shrink-0 w-10 h-10 rounded-lg object-cover bg-white dark:bg-gray-800" src="{{ $role->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH) }}" width="40" height="40" alt="{{ $minName }}">
            @endif
            <h1 class="text-xl md:text-2xl font-semibold leading-tight text-[#151B26] dark:text-gray-100 truncate" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
                <x-user-text>{{ $minName }}</x-user-text>
            </h1>
        </div>

        {{-- Right: action cluster --}}
        <div class="flex flex-row flex-wrap items-center gap-2 md:gap-3 {{ $isRtl ? 'lg:justify-start' : 'lg:justify-end' }}">
            @include('role.partials.headers.action-buttons', ['actionPart' => 'all'])
            @include('role.partials.headers.lang-toggle-inline', ['onDark' => false])

            {{-- The list's tools: the filter and the view switch, as in the banner. --}}
            @include('role.partials.headers.tools')
        </div>
    </header>
    @include('role.partials.headers.below-bar', ['onDark' => false])
    </div>
</div>
