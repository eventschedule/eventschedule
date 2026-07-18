{{--
    In-bar EN/HE language toggle for the compact header. Reuses the exact
    ?lang= mechanism from layouts/app-guest.blade.php (the top-right switcher is suppressed
    for non-banner styles). Expects: $role, $isRtl, and optional $onDark.
--}}
@php
    $onDark = $onDark ?? false;
    $supportedLanguages = config('app.supported_languages');
    $targetCode = $role->translation_language_code ?: 'en';
    $targetName = isset($supportedLanguages[$targetCode]) ? __('messages.' . $supportedLanguages[$targetCode]) : strtoupper($targetCode);
    $authoredName = isset($supportedLanguages[$role->language_code]) ? __('messages.' . $supportedLanguages[$role->language_code]) : strtoupper($role->language_code);
@endphp
@if (! request()->embed && $role->offersTranslation())
<div class="flex items-center rounded-full p-1 text-sm flex-shrink-0 {{ $isRtl ? 'flex-row-reverse' : '' }} {{ $onDark ? 'bg-white/10' : 'bg-gray-100 dark:bg-gray-800 shadow-sm' }}" translate="no">
    @php
        $activeClass = $onDark
            ? 'bg-white/20 text-white'
            : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm';
        $inactiveClass = $onDark
            ? 'text-white/60 hover:text-white'
            : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white';
    @endphp
    @if(session()->has('translate') || request()->lang == $targetCode)
        <span class="px-3 py-1 rounded-full font-medium {{ $activeClass }}" title="{{ $targetName }}" aria-label="{{ $targetName }}">{{ strtoupper($targetCode) }}</span>
        <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang={{ $role->language_code }}"
           class="px-3 py-1 rounded-full font-medium transition-all duration-200 {{ $inactiveClass }}"
           title="{{ $authoredName }}" aria-label="{{ $authoredName }}">
            {{ strtoupper($role->language_code) }}
        </a>
    @else
        <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang={{ $targetCode }}"
           class="px-3 py-1 rounded-full font-medium transition-all duration-200 {{ $inactiveClass }}"
           title="{{ $targetName }}" aria-label="{{ $targetName }}">
            {{ strtoupper($targetCode) }}
        </a>
        <span class="px-3 py-1 rounded-full font-medium {{ $activeClass }}" title="{{ $authoredName }}" aria-label="{{ $authoredName }}">{{ strtoupper($role->language_code) }}</span>
    @endif
</div>
@endif
