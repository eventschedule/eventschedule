{{--
    In-bar EN/HE language toggle for the compact header. Reuses the exact
    ?lang= mechanism from layouts/app-guest.blade.php (the top-right switcher is suppressed
    for non-banner styles). Expects: $role, $isRtl, and optional $onDark.
--}}
@php $onDark = $onDark ?? false; @endphp
@if (! request()->embed && $role->language_code != 'en')
<div class="flex items-center rounded-full p-1 text-sm flex-shrink-0 {{ $isRtl ? 'flex-row-reverse' : '' }} {{ $onDark ? 'bg-white/10' : 'bg-gray-100 dark:bg-gray-800 shadow-sm' }}" translate="no">
    @php
        $activeClass = $onDark
            ? 'bg-white/20 text-white'
            : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm';
        $inactiveClass = $onDark
            ? 'text-white/60 hover:text-white'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white';
    @endphp
    @if(session()->has('translate') || request()->lang == 'en')
        <span class="px-3 py-1 rounded-full font-medium {{ $activeClass }}">EN</span>
        <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang={{ $role->language_code }}"
           class="px-3 py-1 rounded-full font-medium transition-all duration-200 {{ $inactiveClass }}">
            {{ strtoupper($role->language_code) }}
        </a>
    @else
        <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang=en"
           class="px-3 py-1 rounded-full font-medium transition-all duration-200 {{ $inactiveClass }}">
            EN
        </a>
        <span class="px-3 py-1 rounded-full font-medium {{ $activeClass }}">{{ strtoupper($role->language_code) }}</span>
    @endif
</div>
@endif
